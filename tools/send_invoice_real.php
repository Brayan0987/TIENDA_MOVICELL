<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../App/Core/config.php';

use App\Core\Mailer;
use App\Models\PedidoModel;
use App\Models\DetallePedidoModel;

// Destinatario de prueba solicitado por el usuario
$recipient = 'jhuertaslambrano@gmail.com';

try {
    $pedidoModel = new PedidoModel();
    $detalleModel = new DetallePedidoModel();

    // Obtener último pedido (orden más reciente)
    $pedidos = $pedidoModel->obtenerPedidos();
    if (empty($pedidos)) {
        echo "No hay pedidos en la base de datos.\n";
        exit(1);
    }

    $latest = $pedidos[0];
    $orderId = $latest['id_pedido'] ?? ($latest['id'] ?? null);
    if (!$orderId) {
        echo "No se pudo determinar el ID del pedido más reciente.\n";
        exit(1);
    }

    // Obtener datos completos del pedido
    $order = $pedidoModel->obtenerPedidoPorId($orderId);
    $items = $detalleModel->obtenerDetallesPedido($orderId);

    $html = '<h2>Factura - Pedido #' . intval($orderId) . '</h2>';
    $html .= '<p>Fecha: ' . htmlspecialchars($order['fecha'] ?? '') . '</p>';
    $html .= '<p>Cliente: ' . htmlspecialchars($order['nombre'] ?? '') . '</p>';
    $html .= '<p>Teléfono: ' . htmlspecialchars($order['telefono'] ?? '') . '</p>';
    $html .= '<p>Correo del cliente registrado: ' . htmlspecialchars($order['correo'] ?? '') . '</p>';
    $html .= '<p>Dirección: ' . htmlspecialchars($order['direccion'] ?? '') . '</p>';
    $html .= '<h3>Productos</h3><ul>';
    foreach ($items as $it) {
        $name = $it['producto_nombre'] ?? ($it['nombre'] ?? $it['descripcion'] ?? 'Producto');
        $qty = intval($it['cantidad'] ?? 1);
        $price = number_format($it['precio_unitario'] ?? ($it['precio'] ?? 0), 0, ',', '.');
        $html .= '<li>' . htmlspecialchars($name) . ' x ' . $qty . ' - $' . $price . '</li>';
    }
    $html .= '</ul>';
    $html .= '<p>Total: $' . number_format($order['total'] ?? 0, 0, ',', '.') . '</p>';

    echo "Enviando factura del pedido #$orderId a $recipient usando configuración en App/Core/config.php...\n";
    $ok = Mailer::sendInvoice($recipient, 'Factura - Pedido #' . $orderId, $html, (int)$orderId);
    echo $ok ? "Mailer::sendInvoice devolvió true\n" : "Mailer::sendInvoice devolvió false\n";
} catch (Throwable $t) {
    echo 'Error: ' . $t->getMessage() . "\n";
    echo $t->getTraceAsString() . "\n";
    exit(1);
}
