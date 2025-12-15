<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\InvoiceGenerator;
use App\Core\Mailer;
use App\Core\Db;

class InvoiceController extends Controller
{
    /**
     * Descargar factura en PDF
     */
    public function download()
    {
        try {
            $id_pedido = intval($_GET['id'] ?? 0);
            
            if (!$id_pedido) {
                http_response_code(400);
                die('Error: ID de pedido no proporcionado');
            }

            // Verificar sesión
            if (session_status() !== PHP_SESSION_ACTIVE) {
                session_start();
            }
            if (empty($_SESSION['user_id'])) {
                http_response_code(401);
                die('Error: Usuario no autenticado');
            }

            // Verificar que el usuario tiene permiso para ver este pedido
            $this->verificarAcceso($id_pedido);

            // Obtener datos del pedido
            $order = $this->obtenerPedido($id_pedido);
            if (!$order) {
                http_response_code(404);
                die('Error: Pedido no encontrado');
            }

            // Obtener detalles
            $items = $this->obtenerDetallesPedido($id_pedido);

            // Generar HTML
            $html = InvoiceGenerator::generateInvoiceHtml($order, $items);

            // Generar PDF con mPDF
            require_once dirname(__DIR__) . '/../vendor/autoload.php';
            $mpdf = new \Mpdf\Mpdf([
                'tempDir' => dirname(__DIR__) . '/../storage/mails',
                'mode' => 'utf-8',
                'format' => 'A4',
                'margin_left' => 10,
                'margin_right' => 10,
                'margin_top' => 10,
                'margin_bottom' => 10,
            ]);

            $mpdf->WriteHTML($html);

            // Enviar como descarga
            $filename = 'Factura_Pedido_' . $id_pedido . '_' . date('dmY') . '.pdf';
            $mpdf->Output($filename, 'D');
            exit;

        } catch (\Exception $e) {
            http_response_code(500);
            die('Error al generar factura: ' . $e->getMessage());
        }
    }

    /**
     * Ver factura en navegador (embebida)
     */
    public function viewInvoice()
    {
        try {
            $id_pedido = intval($_GET['id'] ?? 0);
            
            if (!$id_pedido) {
                http_response_code(400);
                die('Error: ID de pedido no proporcionado');
            }

            // Verificar sesión
            if (session_status() !== PHP_SESSION_ACTIVE) {
                session_start();
            }
            if (empty($_SESSION['user_id'])) {
                http_response_code(401);
                die('Error: Usuario no autenticado');
            }

            // Verificar que el usuario tiene permiso para ver este pedido
            $this->verificarAcceso($id_pedido);

            // Obtener datos del pedido
            $order = $this->obtenerPedido($id_pedido);
            if (!$order) {
                http_response_code(404);
                die('Error: Pedido no encontrado');
            }

            // Obtener detalles
            $items = $this->obtenerDetallesPedido($id_pedido);

            // Generar HTML
            $html = InvoiceGenerator::generateInvoiceHtml($order, $items);

            // Enviar como HTML
            header('Content-Type: text/html; charset=utf-8');
            echo $html;
            exit;

        } catch (\Exception $e) {
            http_response_code(500);
            die('Error: ' . $e->getMessage());
        }
    }

    /**
     * Reenviar factura por email
     */
    public function resend()
    {
        header('Content-Type: application/json; charset=utf-8');
        
        // Log inicial
        error_log('╔════════════════════════════════════════════════════════════════╗');
        error_log('║                    INICIO: resend()                            ║');
        error_log('╚════════════════════════════════════════════════════════════════╝');
        error_log('POST data: ' . print_r($_POST, true));
        
        try {
            $id_pedido = intval($_POST['id_pedido'] ?? 0);
            error_log('► ID Pedido enviado: ' . $id_pedido);
            
            if (!$id_pedido) {
                http_response_code(400);
                $msg = 'ID inválido';
                error_log('✗ ERROR: ' . $msg);
                echo json_encode(['success' => false, 'message' => $msg]);
                exit;
            }

            // Verificar sesión
            if (session_status() !== PHP_SESSION_ACTIVE) {
                session_start();
            }
            error_log('► Session user_id: ' . ($_SESSION['user_id'] ?? 'NO DEFINIDO'));
            error_log('► Session user_role: ' . ($_SESSION['user_role'] ?? 'NO DEFINIDO'));
            
            if (empty($_SESSION['user_id'])) {
                http_response_code(401);
                $msg = 'Usuario no autenticado';
                error_log('✗ ERROR: ' . $msg);
                echo json_encode(['success' => false, 'message' => $msg]);
                exit;
            }

            // Verificar que el usuario tiene permiso
            error_log('► Verificando acceso a pedido ' . $id_pedido);
            $this->verificarAcceso($id_pedido);
            error_log('✓ Acceso verificado OK');

            // Obtener datos del pedido
            error_log('► Obteniendo datos del pedido...');
            $order = $this->obtenerPedido($id_pedido);
            if (!$order) {
                http_response_code(404);
                $msg = 'Pedido no encontrado';
                error_log('✗ ERROR: ' . $msg);
                echo json_encode(['success' => false, 'message' => $msg]);
                exit;
            }
            error_log('✓ Pedido obtenido: ' . ($order['nombre'] ?? 'N/A'));

            // Validar que el correo existe
            $correo = $order['correo'] ?? '';
            error_log('► Correo obtenido: ' . $correo);
            
            if (empty($correo) || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                http_response_code(400);
                $msg = 'Correo inválido o no disponible: ' . $correo;
                error_log('✗ ERROR: ' . $msg);
                echo json_encode(['success' => false, 'message' => $msg]);
                exit;
            }
            error_log('✓ Correo validado OK');

            // Obtener detalles
            error_log('► Obteniendo detalles del pedido...');
            $items = $this->obtenerDetallesPedido($id_pedido);
            error_log('✓ Detalles obtenidos: ' . count($items) . ' productos');
            foreach ($items as $i => $item) {
                error_log('   [' . ($i+1) . '] ' . ($item['nombre'] ?? 'N/A'));
            }

            // Generar HTML
            error_log('► Generando HTML de factura...');
            $html = InvoiceGenerator::generateInvoiceHtml($order, $items);
            error_log('✓ HTML generado: ' . strlen($html) . ' bytes');

            // Enviar email
            $subject = 'Factura Pedido #' . $id_pedido . ' - MOVIL CELL';
            error_log('╔════════════════════════════════════════════════════════════════╗');
            error_log('║                 LLAMANDO A MAILER::SENDINVOICE()               ║');
            error_log('║ Destinatario: ' . str_pad($correo, 55) . '║');
            error_log('║ Asunto: ' . str_pad($subject, 56) . '║');
            error_log('╚════════════════════════════════════════════════════════════════╝');
            
            $result = Mailer::sendInvoice($correo, $subject, $html, $id_pedido);
            error_log('✓ sendInvoice() retornó: ' . ($result ? 'TRUE ✓' : 'FALSE ✗'));

            if ($result) {
                http_response_code(200);
                $msg = 'Factura reenviada exitosamente a ' . $correo;
                error_log('╔════════════════════════════════════════════════════════════════╗');
                error_log('║                       ✓ ÉXITO                                 ║');
                error_log('║ ' . str_pad($msg, 60) . '║');
                error_log('╚════════════════════════════════════════════════════════════════╝');
                echo json_encode(['success' => true, 'message' => $msg]);
            } else {
                http_response_code(500);
                $msg = 'Error al enviar factura. Revisa los logs del servidor.';
                error_log('╔════════════════════════════════════════════════════════════════╗');
                error_log('║                       ✗ FALLO                                  ║');
                error_log('║ ' . str_pad($msg, 60) . '║');
                error_log('║ sendInvoice() retornó false - Email NO se envió                ║');
                error_log('╚════════════════════════════════════════════════════════════════╝');
                echo json_encode(['success' => false, 'message' => $msg]);
            }
            exit;

        } catch (\Exception $e) {
            http_response_code(500);
            $msg = 'Error: ' . $e->getMessage();
            error_log('╔════════════════════════════════════════════════════════════════╗');
            error_log('║                    ✗ EXCEPCIÓN NO CONTROLADA                  ║');
            error_log('║ ' . str_pad($msg, 60) . '║');
            error_log('║ Stack trace:                                                   ║');
            foreach (explode("\n", $e->getTraceAsString()) as $line) {
                if (trim($line)) {
                    error_log('║ ' . str_pad(trim($line), 60) . '║');
                }
            }
            error_log('╚════════════════════════════════════════════════════════════════╝');
            echo json_encode(['success' => false, 'message' => $msg]);
            exit;
        }
    }

    /**
     * Obtener datos del pedido directamente de la BD
     */
    private function obtenerPedido($id_pedido)
    {
        $db = Db::conn();
        
        // Primero obtener el pedido básico
        $query = 'SELECT * FROM pedido WHERE id_pedido = ?';
        $stmt = $db->prepare($query);
        $stmt->bind_param('i', $id_pedido);
        $stmt->execute();
        $result = $stmt->get_result();
        $order = $result->fetch_assoc();
        $stmt->close();
        
        if (!$order) {
            return null;
        }
        
        // Luego obtener el usuario asociado
        $id_usuario_rol = $order['id_usuario_rol'] ?? null;
        if ($id_usuario_rol) {
            $userQuery = 'SELECT u.nombre, u.correo, u.telefono 
                          FROM roles_usuario ru
                          JOIN usuario u ON ru.id_usuario = u.id_usuario
                          WHERE ru.id_usuario_rol = ?';
            $userStmt = $db->prepare($userQuery);
            $userStmt->bind_param('i', $id_usuario_rol);
            $userStmt->execute();
            $userResult = $userStmt->get_result();
            $usuario = $userResult->fetch_assoc();
            $userStmt->close();
            
            if ($usuario) {
                $order['nombre'] = $usuario['nombre'];
                $order['correo'] = $usuario['correo'];
                $order['telefono'] = $usuario['telefono'];
            }
        }
        
        return $order;
    }

    /**
     * Obtener detalles del pedido
     */
    private function obtenerDetallesPedido($id_pedido)
    {
        $db = Db::conn();
        $query = 'SELECT dp.*, 
                         p.nombre, 
                         p.descripcion,
                         pr.precio,
                         pr.precio as precio_unitario,
                         ic.imagen_url as imagen
                  FROM detalle_pedido dp
                  LEFT JOIN celulares c ON dp.id_celulares = c.id_celulares
                  LEFT JOIN producto p ON c.id_producto = p.id_producto
                  LEFT JOIN precio pr ON c.id_precio = pr.id_precio
                  LEFT JOIN imagenes_celulares ic ON c.id_celulares = ic.id_celulares AND ic.es_principal = 1
                  WHERE dp.id_pedido = ?
                  ORDER BY dp.id_detalle';
        $stmt = $db->prepare($query);
        $stmt->bind_param('i', $id_pedido);
        $stmt->execute();
        $result = $stmt->get_result();
        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
        $stmt->close();
        return $items;
    }

    /**
     * Verificar acceso al pedido (usuario solo ve sus pedidos, admin ve todos)
     */
    private function verificarAcceso($id_pedido)
    {
        $user_id = $_SESSION['user_id'] ?? null;
        $user_role = $_SESSION['user_role'] ?? null;

        // Admins pueden ver todo
        if ($user_role === 'admin') {
            return true;
        }

        // Usuarios normales solo ven sus pedidos
        if ($user_id) {
            // Obtener el usuario_rol asociado al pedido
            $db = Db::conn();
            $stmt = $db->prepare('SELECT id_usuario_rol FROM pedido WHERE id_pedido = ?');
            $stmt->bind_param('i', $id_pedido);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();
            
            if (!$row) {
                http_response_code(404);
                die('Error: Pedido no encontrado');
            }
            
            // Verificar si el usuario actual está asociado a ese usuario_rol
            $id_usuario_rol = $row['id_usuario_rol'];
            $verifyStmt = $db->prepare('SELECT id_usuario FROM roles_usuario WHERE id_usuario_rol = ? AND id_usuario = ?');
            $verifyStmt->bind_param('ii', $id_usuario_rol, $user_id);
            $verifyStmt->execute();
            $verifyResult = $verifyStmt->get_result();
            $found = $verifyResult->fetch_assoc();
            $verifyStmt->close();
            
            if ($found) {
                return true;
            }
        }

        // No tiene permiso
        http_response_code(403);
        die('Error: No tienes permiso para ver este pedido');
    }
}
