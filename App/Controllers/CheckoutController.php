<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\PedidoModel;
use App\Models\DetallePedidoModel;
use App\Core\Mailer;
use App\Core\InvoiceGenerator;

class CheckoutController {
    private $pedidoModel;
    private $detallePedidoModel;

    public function __construct() {
        $this->pedidoModel       = new PedidoModel();
        $this->detallePedidoModel = new DetallePedidoModel();
    }

    /**
     * Mostrar página de checkout
     */
    public function index(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
            header('Location: index.php?r=/cart');
            exit;
        }

        $items = $_SESSION['cart'];

        // Subtotal sin descuento
        $subtotal = array_sum(array_map(function($item) {
            return $item['price'] * $item['quantity'];
        }, $items));

        // Descuento por cupón (si existe)
        $discount = 0.0;
        $total    = $subtotal;

        if (!empty($_SESSION['coupon']) && $subtotal > 0) {
            $c = $_SESSION['coupon'];

            if (($c['tipo'] ?? '') === 'percent') {
                $discount = $subtotal * ((float)$c['valor'] / 100);
            } elseif (($c['tipo'] ?? '') === 'fixed') {
                $discount = (float)$c['valor'];
            }

            if ($discount > $subtotal) {
                $discount = $subtotal;
            }

            $total = $subtotal - $discount;
        }

        $userData = $_SESSION['user'] ?? [];
        $csrf     = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrf;

        // Valores para la vista
        $subtotal_checkout = $subtotal;
        $discount_checkout = $discount;
        $total_checkout    = $total;

        include __DIR__ . '/../Views/Checkout/Index.php';
    }

    /**
     * Procesar el pedido
     */
    public function process(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        // Validar CSRF
        if ($_POST['csrf'] !== ($_SESSION['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'Token de seguridad inválido';
            header('Location: index.php?r=/checkout');
            exit;
        }

        // Validar datos
        $nombre        = htmlspecialchars($_POST['nombre_completo'] ?? '');
        $telefono      = htmlspecialchars($_POST['telefono'] ?? '');
        $direccion     = htmlspecialchars($_POST['direccion'] ?? '');
        $ciudad        = intval($_POST['ciudad'] ?? 0);
        $codigo_postal = htmlspecialchars($_POST['codigo_postal'] ?? '');
        $notas         = htmlspecialchars($_POST['notas'] ?? '');

        if (empty($nombre) || empty($telefono) || empty($direccion) || $ciudad === 0) {
            $_SESSION['error'] = 'Por favor completa todos los campos requeridos';
            header('Location: index.php?r=/checkout');
            exit;
        }

        if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
            $_SESSION['error'] = 'El carrito está vacío';
            header('Location: index.php?r=/checkout');
            exit;
        }

        $items = $_SESSION['cart'];

        // Subtotal sin descuento
        $subtotal = array_sum(array_map(function($item) {
            return $item['price'] * $item['quantity'];
        }, $items));

        // Descuento por cupón (si existe)
        $discount = 0.0;
        $total    = $subtotal;

        if (!empty($_SESSION['coupon']) && $subtotal > 0) {
            $c = $_SESSION['coupon'];

            if (($c['tipo'] ?? '') === 'percent') {
                $discount = $subtotal * ((float)$c['valor'] / 100);
            } elseif (($c['tipo'] ?? '') === 'fixed') {
                $discount = (float)$c['valor'];
            }

            if ($discount > $subtotal) {
                $discount = $subtotal;
            }

            $total = $subtotal - $discount;
        }

        // Obtener id_usuario_rol del usuario autenticado si existe en sesión
        $id_usuario_rol = $_SESSION['user']['id_usuario_rol'] ?? null;

        // Si no tenemos id_usuario_rol pero tenemos user_id, intentar recuperarlo desde BD
        if (empty($id_usuario_rol) && !empty($_SESSION['user_id'])) {
            try {
                $db   = \App\Core\Db::conn();
                $stmt = $db->prepare('SELECT id_usuario_rol FROM roles_usuario WHERE id_usuario = ? LIMIT 1');
                $stmt->bind_param('i', $_SESSION['user_id']);
                $stmt->execute();
                $res  = $stmt->get_result();
                $row  = $res->fetch_assoc();
                $stmt->close();

                if ($row && !empty($row['id_usuario_rol'])) {
                    $id_usuario_rol = (int)$row['id_usuario_rol'];
                    $_SESSION['user']['id_usuario_rol'] = $id_usuario_rol;
                } else {
                    $id_usuario_rol = null;
                }
            } catch (\Throwable $e) {
                error_log('CheckoutController: error buscando id_usuario_rol: ' . $e->getMessage());
                $id_usuario_rol = null;
            }
        }

        // Si aún no hay id_usuario_rol -> requerir login
        if (empty($id_usuario_rol)) {
            $_SESSION['error'] = 'Debes iniciar sesión para realizar una compra';
            header('Location: index.php?r=/login');
            exit;
        }

        // Validar que la ciudad seleccionada existe en la tabla 'ubicacion'
        $ciudadValida = false;
        if ($ciudad > 0) {
            try {
                $db   = \App\Core\Db::conn();
                $stmt = $db->prepare('SELECT 1 FROM ubicacion WHERE id_ciudad = ? LIMIT 1');
                if ($stmt) {
                    $stmt->bind_param('i', $ciudad);
                    $stmt->execute();
                    $res         = $stmt->get_result();
                    $ciudadValida = (bool)$res->fetch_row();
                    $stmt->close();
                } else {
                    error_log('CheckoutController: error preparando consulta ubicacion: ' . $db->error);
                }
            } catch (\Throwable $e) {
                error_log('CheckoutController: excepción comprobando ubicacion: ' . $e->getMessage());
                $ciudadValida = false;
            }
        }

        if (!$ciudadValida) {
            error_log("CheckoutController: ciudad inválida seleccionada: {$ciudad}");
            $_SESSION['error'] = 'Ciudad inválida. Por favor selecciona una ciudad válida desde el menú.';
            header('Location: index.php?r=/checkout');
            exit;
        }

        // --- validar items del carrito antes de crear el pedido ---
        $badKeys = [];
        foreach ($items as $key => $item) {
            $idCel = $item['product_id'] ?? $item['id_celulares'] ?? (is_numeric($key) ? (int)$key : null);
            $qty   = isset($item['quantity']) ? (int)$item['quantity'] : (int)($item['cantidad'] ?? 0);
            $price = isset($item['price']) ? (float)$item['price'] : (float)($item['precio_unitario'] ?? 0);
            if (empty($idCel) || $qty <= 0 || $price <= 0) {
                $badKeys[] = $key;
            }
        }

        if (!empty($badKeys)) {
            error_log('CheckoutController: items inválidos en carrito: ' . implode(',', $badKeys));
            $_SESSION['error'] = 'Hay artículos inválidos en tu carrito. Revísalo antes de pagar.';
            header('Location: index.php?r=/cart');
            exit;
        }

        // Crear pedido (total ya incluye el descuento)
        $pedidoData = [
            'id_usuario_rol'       => $id_usuario_rol,
            'id_estado'            => 1, // Pendiente
            'total'                => $total,
            'id_ciudad'            => $ciudad,
            'id_metodo'            => 4, // Contraentrega
            'direccion'            => $direccion,
            'codigo_postal'        => $codigo_postal,
            'imagen_pago'          => '',
            'descripcion del envio'=> $notas,
        ];

        $id_pedido = $this->pedidoModel->crearPedido($pedidoData);

        if (!$id_pedido) {
            $_SESSION['error'] = 'Error al crear el pedido. Intenta nuevamente.';
            header('Location: index.php?r=/checkout');
            exit;
        }

        // Guardar detalles del pedido y actualizar stock
        foreach ($items as $key => $item) {
            $id_celulares    = $item['product_id'] ?? $item['id_celulares'] ?? (is_numeric($key) ? (int)$key : null);
            $cantidad        = isset($item['quantity']) ? (int)$item['quantity'] : (int)($item['cantidad'] ?? 1);
            $precio_unitario = isset($item['price']) ? (float)$item['price'] : (float)($item['precio_unitario'] ?? 0);

            if (empty($id_celulares) || $cantidad <= 0 || $precio_unitario <= 0) {
                error_log("CheckoutController: saltando item inválido en detalle (key={$key}) id_celulares=" . var_export($id_celulares, true));
                continue;
            }

            $detalleData = [
                'id_pedido'       => $id_pedido,
                'id_celulares'    => $id_celulares,
                'cantidad'        => $cantidad,
                'precio_unitario' => $precio_unitario,
            ];

            $ok = $this->detallePedidoModel->crearDetalle($detalleData);
            if (!$ok) {
                error_log('CheckoutController: fallo insertando detalle para pedido ' . $id_pedido . ' item ' . $id_celulares);
                continue;
            }

            // Restar stock del celular en la tabla celulares (cantidadstock)
            try {
                $db = \App\Core\Db::conn();
                $stmtStock = $db->prepare(
                    'UPDATE celulares
                     SET cantidadstock = GREATEST(cantidadstock - ?, 0)
                     WHERE idcelulares = ?'
                );
                if ($stmtStock) {
                    $stmtStock->bind_param('ii', $cantidad, $id_celulares);
                    $stmtStock->execute();
                    $stmtStock->close();
                } else {
                    error_log('CheckoutController: error preparando UPDATE stock: ' . $db->error);
                }
            } catch (\Throwable $e) {
                error_log('CheckoutController: excepción actualizando stock: ' . $e->getMessage());
            }
        }

        // Enviar factura por correo
        try {
            $order    = $this->pedidoModel->obtenerPedidoPorId($id_pedido);
            $detalles = $this->detallePedidoModel->obtenerDetallesPedido($id_pedido);
            $to       = $order['correo'] ?? ($_SESSION['user']['correo'] ?? '');
            if (!empty($to)) {
                $subject = 'Factura - Pedido #' . $id_pedido;
                $html    = InvoiceGenerator::generateInvoiceHtml($order, $detalles);
                Mailer::sendInvoice($to, $subject, $html, (int)$id_pedido);
            }
        } catch (\Throwable $e) {
            error_log('CheckoutController: error sending invoice email: ' . $e->getMessage());
        }

        // Limpiar carrito (puedes también limpiar el cupón si quieres)
        unset($_SESSION['cart']);
        unset($_SESSION['coupon']);

        $_SESSION['success'] = 'Pedido creado exitosamente. Número de pedido: ' . $id_pedido;

        header('Location: index.php?r=/checkout/success&id=' . $id_pedido);
        exit;
    }

    /**
     * Mostrar página de éxito después de crear pedido
     */
    public function success(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $orderId = (int)($_GET['id'] ?? 0);

        if ($orderId < 1) {
            $_SESSION['error'] = 'ID de pedido inválido.';
            header('Location: index.php?r=/');
            exit;
        }

        // Obtener detalles del pedido
        $order = null;
        try {
            $order = $this->pedidoModel->obtenerPedidoPorId($orderId);
        } catch (\Throwable $e) {
            error_log('CheckoutController::success() - Error obteniendo pedido: ' . $e->getMessage());
        }

        if (!$order) {
            $_SESSION['error'] = 'Pedido no encontrado.';
            header('Location: index.php?r=/');
            exit;
        }

        include __DIR__ . '/../Views/Checkout/Success.php';
    }

    /**
     * Mostrar detalle/factura de un pedido específico
     */
    public function orderDetail(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (empty($_SESSION['user_id'])) {
            header('Location: index.php?r=/login');
            exit;
        }

        $orderId = (int)($_GET['id'] ?? 0);

        if ($orderId < 1) {
            $_SESSION['error'] = 'ID de pedido inválido.';
            header('Location: index.php?r=/panel');
            exit;
        }

        try {
            $order = $this->pedidoModel->obtenerPedidoPorId($orderId);
        } catch (\Throwable $e) {
            error_log('CheckoutController::orderDetail() - Error obteniendo pedido: ' . $e->getMessage());
            $order = null;
        }

        if (!$order) {
            $_SESSION['error'] = 'Pedido no encontrado.';
            header('Location: index.php?r=/panel');
            exit;
        }

        // Verificar que el pedido pertenece al usuario logueado
        try {
            $db   = \App\Core\Db::conn();
            $stmt = $db->prepare('SELECT id_usuario FROM roles_usuario WHERE id_usuario_rol = ? LIMIT 1');
            $stmt->bind_param('i', $order['id_usuario_rol']);
            $stmt->execute();
            $res = $stmt->get_result();
            $row = $res->fetch_assoc();
            $stmt->close();

            if (!$row || $row['id_usuario'] != $_SESSION['user_id']) {
                $_SESSION['error'] = 'No tienes permiso para ver este pedido.';
                header('Location: index.php?r=/panel');
                exit;
            }
        } catch (\Throwable $e) {
            error_log('Error verificando propiedad del pedido: ' . $e->getMessage());
            $_SESSION['error'] = 'Error al verificar el pedido.';
            header('Location: index.php?r=/panel');
            exit;
        }

        // Obtener detalles de productos del pedido
        try {
            $detalles = $this->detallePedidoModel->obtenerDetallesPedido($orderId);
        } catch (\Throwable $e) {
            error_log('CheckoutController::orderDetail() - Error obteniendo detalles: ' . $e->getMessage());
            $detalles = [];
        }

        // Importante: usar la clave 'items' porque la vista la espera así
        $order['items'] = $detalles;

        // Mostrar vista de factura
        include __DIR__ . '/../Views/Checkout/OrderDetail.php';
    }
}
