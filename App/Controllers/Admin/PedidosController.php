<?php
namespace App\Controllers\Admin;

use App\Core\Db;
use App\Core\Controller;
use App\Core\Security;
use App\Core\Mailer;
use App\Core\InvoiceGenerator;

class PedidosController extends Controller
{
    private $db;

    public function __construct()
    {
        $this->db = Db::conn();
    }

    /**
     * Listar todos los pedidos
     */
    public function index(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        // Verificar que es admin
        if (empty($_SESSION['user_id']) || empty($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            $_SESSION['error'] = 'Acceso denegado. Solo administradores pueden acceder.';
            $this->redirect('/login');
            return;
        }

        // Consultar todos los pedidos con información del cliente
        $sql = "
            SELECT 
                p.id_pedido,
                p.fecha,
                p.total,
                p.id_estado,
                e.estado as estado_nombre,
                u.nombre,
                u.telefono,
                u.correo,
                p.imagen_pago,
                p.direccion,
                p.id_ciudad
            FROM pedido p
            LEFT JOIN estado e ON p.id_estado = e.id_estado
            LEFT JOIN roles_usuario ru ON p.id_usuario_rol = ru.id_usuario_rol
            LEFT JOIN usuario u ON ru.id_usuario = u.id_usuario
            ORDER BY p.fecha DESC
        ";

        $result  = $this->db->query($sql);
        $pedidos = [];

        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $pedidos[] = $row;
            }
        }

        error_log('PedidosController::index() - Total pedidos: ' . count($pedidos));

        // Obtener lista de estados para la vista
        $estados    = [];
        $resEstados = $this->db->query('SELECT id_estado, estado FROM estado ORDER BY id_estado ASC');
        if ($resEstados) {
            while ($r = $resEstados->fetch_assoc()) {
                $estados[] = $r;
            }
        }

        // Cargar vista - pasar como $pedidos y $estados
        include __DIR__ . '/../../../App/Views/Admin/ventas.php';
    }

    /**
     * Ver detalle de un pedido específico
     */
    public function detail(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        // Verificar que es admin
        if (empty($_SESSION['user_id']) || empty($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            $_SESSION['error'] = 'Acceso denegado.';
            $this->redirect('/login');
            return;
        }

        $orderId = (int)($_GET['id'] ?? 0);
        if ($orderId < 1) {
            $_SESSION['error'] = 'ID de pedido inválido.';
            $this->redirect('/admin/ventas');
            return;
        }

        // Consultar pedido con información completa
        $stmt = $this->db->prepare(
            'SELECT p.*, e.estado as estado_nombre, u.nombre, u.correo, u.telefono
             FROM pedido p
             LEFT JOIN estado e ON p.id_estado = e.id_estado
             LEFT JOIN roles_usuario ru ON p.id_usuario_rol = ru.id_usuario_rol
             LEFT JOIN usuario u ON ru.id_usuario = u.id_usuario
             WHERE p.id_pedido = ? LIMIT 1'
        );

        if (!$stmt) {
            $_SESSION['error'] = 'Error en la consulta.';
            $this->redirect('/admin/ventas');
            return;
        }

        $stmt->bind_param('i', $orderId);
        $stmt->execute();
        $result = $stmt->get_result();
        $order  = $result->fetch_assoc();
        $stmt->close();

        if (!$order) {
            $_SESSION['error'] = 'Pedido no encontrado.';
            $this->redirect('/admin/ventas');
            return;
        }

        // Obtener items del pedido con información de productos (incluye nombre_producto)
        $stmtItems = $this->db->prepare(
            'SELECT 
                 dp.*,
                 c.id_producto,
                 pr.nombre AS nombre_producto
             FROM detalle_pedido dp
             LEFT JOIN celulares c ON dp.id_celulares = c.id_celulares
             LEFT JOIN producto  pr ON c.id_producto  = pr.id_producto
             WHERE dp.id_pedido = ?'
        );

        if ($stmtItems) {
            $stmtItems->bind_param('i', $orderId);
            $stmtItems->execute();
            $itemsResult = $stmtItems->get_result();
            $items       = [];
            while ($item = $itemsResult->fetch_assoc()) {
                $items[] = $item;
            }
            $stmtItems->close();
            $order['items'] = $items;
        } else {
            $order['items'] = [];
        }

        // Cargar vista de detalle
        include __DIR__ . '/../../../App/Views/Admin/pedido_detalle.php';
    }

    /**
     * Actualizar estado del pedido (AJAX)
     */
    public function updateStatus(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        set_error_handler(function ($severity, $message, $file, $line) {
            throw new \ErrorException($message, 0, $severity, $file, $line);
        });

        register_shutdown_function(function () {
            $err = error_get_last();
            if ($err && in_array($err['type'] ?? 0, [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
                error_log('PedidosController::updateStatus - shutdown error: ' . json_encode($err));
                if (!headers_sent()) {
                    header('Content-Type: application/json; charset=utf-8');
                    http_response_code(500);
                }
                echo json_encode(['success' => false, 'message' => 'Error interno del servidor (fatal)', 'detail' => $err]);
            }
        });

        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'Método no permitido']);
                return;
            }

            if (session_status() !== PHP_SESSION_ACTIVE) {
                session_start();
            }

            error_log('PedidosController::updateStatus - POST received: ' . json_encode($_POST));
            error_log('PedidosController::updateStatus - SESSION: ' . json_encode([
                'user_id'   => $_SESSION['user_id'] ?? null,
                'user_role' => $_SESSION['user_role'] ?? null
            ]));

            try {
                Security::enforceCsrfPost();
            } catch (\RuntimeException $ex) {
                error_log('PedidosController::updateStatus - CSRF invalid: ' . $ex->getMessage());
                echo json_encode(['success' => false, 'message' => 'Token CSRF inválido']);
                return;
            }

            if (empty($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
                error_log('PedidosController::updateStatus - Access denied for user role: ' . ($_SESSION['user_role'] ?? 'none'));
                echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
                return;
            }

            $orderId  = (int)($_POST['id'] ?? 0);
            $estadoId = (int)($_POST['estado'] ?? 0);

            if ($orderId < 1 || $estadoId < 1) {
                error_log('PedidosController::updateStatus - Invalid data: id=' . $orderId . ' estado=' . $estadoId);
                echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
                return;
            }

            $stmt = $this->db->prepare('UPDATE pedido SET id_estado = ? WHERE id_pedido = ?');
            if (!$stmt) {
                error_log('PedidosController::updateStatus - Prepare failed: ' . $this->db->error);
                echo json_encode(['success' => false, 'message' => 'Error en la consulta']);
                return;
            }
            $stmt->bind_param('ii', $estadoId, $orderId);
            $ok = $stmt->execute();
            if ($stmt->error) {
                error_log('PedidosController::updateStatus - Execute error: ' . $stmt->error);
            }
            $stmt->close();

            if (!$ok) {
                error_log('PedidosController::updateStatus - Update failed');
                echo json_encode(['success' => false, 'message' => 'Fallo al actualizar estado']);
                return;
            }

            $nameStmt     = $this->db->prepare('SELECT estado FROM estado WHERE id_estado = ? LIMIT 1');
            $estadoNombre = '';
            if ($nameStmt) {
                $nameStmt->bind_param('i', $estadoId);
                $nameStmt->execute();
                $res = $nameStmt->get_result();
                $row = $res->fetch_assoc();
                $estadoNombre = $row['estado'] ?? '';
                $nameStmt->close();
            }

            try {
                if (!empty($estadoNombre) && mb_strtolower(trim($estadoNombre), 'UTF-8') === 'entregado') {
                    $stmtOrder = $this->db->prepare(
                        'SELECT p.id_pedido, p.total, p.direccion, p.imagen_pago, ru.id_usuario_rol, u.nombre, u.correo, u.telefono
                         FROM pedido p
                         LEFT JOIN roles_usuario ru ON p.id_usuario_rol = ru.id_usuario_rol
                         LEFT JOIN usuario u ON ru.id_usuario = u.id_usuario
                         WHERE p.id_pedido = ? LIMIT 1'
                    );
                    if ($stmtOrder) {
                        $stmtOrder->bind_param('i', $orderId);
                        $stmtOrder->execute();
                        $resOrder = $stmtOrder->get_result();
                        $order    = $resOrder->fetch_assoc();
                        $stmtOrder->close();

                        if (!empty($order) && !empty($order['correo'])) {
                            $stmtItems = $this->db->prepare(
                                'SELECT dp.*, c.nombre as producto_nombre
                                 FROM detalle_pedido dp
                                 LEFT JOIN celulares c ON dp.id_celulares = c.id_celulares
                                 WHERE dp.id_pedido = ?'
                            );
                            $items = [];
                            if ($stmtItems) {
                                $stmtItems->bind_param('i', $orderId);
                                $stmtItems->execute();
                                $resItems = $stmtItems->get_result();
                                while ($it = $resItems->fetch_assoc()) {
                                    $items[] = $it;
                                }
                                $stmtItems->close();
                            }

                            $html = InvoiceGenerator::generateInvoiceHtml($order, $items);

                            try {
                                Mailer::sendInvoice($order['correo'], 'Factura - Pedido #' . $orderId, $html, (int)$orderId);
                            } catch (\Throwable $e) {
                                error_log('PedidosController::updateStatus - Error sending invoice email: ' . $e->getMessage());
                            }
                        }
                    }
                }
            } catch (\Throwable $e) {
                error_log('PedidosController::updateStatus - Error preparing invoice resend: ' . $e->getMessage());
            }

            echo json_encode(['success' => true, 'estado_nombre' => $estadoNombre, 'estado_id' => $estadoId]);
            return;
        } catch (\Throwable $t) {
            error_log('PedidosController::updateStatus - Exception: ' . $t->getMessage() . '\n' . $t->getTraceAsString());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
            return;
        }
    }

    /**
     * Devuelve la lista de estados para poblar selects en la UI (JSON)
     */
    public function getEstados(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (empty($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
            return;
        }

        $rows = [];
        $res  = $this->db->query('SELECT id_estado, estado FROM estado ORDER BY id_estado ASC');
        if ($res) {
            while ($r = $res->fetch_assoc()) {
                $rows[] = $r;
            }
        }

        echo json_encode(['success' => true, 'data' => $rows]);
    }
}
