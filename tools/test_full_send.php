<?php
/**
 * Test completo de envío de factura
 */
require_once dirname(__DIR__) . '/App/Core/config.php';
require_once dirname(__DIR__) . '/vendor/autoload.php';

use App\Core\Db;
use App\Core\Mailer;
use App\Core\InvoiceGenerator;

$id_pedido = 20;

echo "=== TEST COMPLETO DE ENVÍO ===\n\n";

// 1. Obtener pedido
$db = Db::conn();
$stmt = $db->prepare('SELECT p.*, e.estado as estado_nombre, u.nombre, u.correo, u.telefono
                      FROM pedido p
                      LEFT JOIN estado e ON p.id_estado = e.id_estado
                      LEFT JOIN roles_usuario ru ON p.id_usuario_rol = ru.id_usuario_rol
                      LEFT JOIN usuario u ON ru.id_usuario = u.id_usuario
                      WHERE p.id_pedido = ?');
$id_pedido_var = $id_pedido;
$stmt->bind_param('i', $id_pedido_var);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();
$stmt->close();

echo "1. Pedido:\n";
echo "   Nombre: " . $order['nombre'] . "\n";
echo "   Correo: " . $order['correo'] . "\n";
echo "   Total: " . $order['total'] . "\n\n";

// 2. Obtener detalles
$stmt = $db->prepare('SELECT dp.*, 
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
                      WHERE dp.id_pedido = ?');
$stmt->bind_param('i', $id_pedido_var);
$stmt->execute();
$result = $stmt->get_result();
$items = [];
while ($row = $result->fetch_assoc()) {
    $items[] = $row;
}
$stmt->close();

echo "2. Detalles: " . count($items) . " productos\n";
foreach ($items as $item) {
    echo "   - " . $item['nombre'] . " (x" . $item['cantidad'] . ")\n";
}
echo "\n";

// 3. Generar HTML
$html = InvoiceGenerator::generateInvoiceHtml($order, $items);
echo "3. HTML generado: " . strlen($html) . " bytes\n\n";

// 4. Enviar email
$correo = $order['correo'];
$subject = 'Factura Pedido #' . $id_pedido . ' - MOVIL CELL (Test Directo)';

echo "4. Enviando email:\n";
echo "   A: $correo\n";
echo "   Asunto: $subject\n";
echo "   MAIL_USE_SMTP: " . (MAIL_USE_SMTP ? 'true' : 'false') . "\n";
echo "   MAIL_SMTP_HOST: " . MAIL_SMTP_HOST . "\n\n";

$resultado = Mailer::sendInvoice($correo, $subject, $html, $id_pedido);

echo "5. Resultado: " . ($resultado ? 'true' : 'false') . "\n\n";

if ($resultado) {
    echo "✅ Email procesado\n";
} else {
    echo "❌ Fallo en sendInvoice\n";
}

// 6. Revisar archivos generados
echo "\n6. Archivos en storage/mails/:\n";
$storageDir = dirname(__DIR__) . '/storage/mails';
if (is_dir($storageDir)) {
    $files = array_diff(scandir($storageDir), ['.', '..']);
    $files = array_reverse(array_values($files));
    
    if (empty($files)) {
        echo "   (Vacío)\n";
    } else {
        foreach (array_slice($files, 0, 5) as $file) {
            $path = $storageDir . '/' . $file;
            $size = filesize($path);
            $time = date('Y-m-d H:i:s', filemtime($path));
            echo "   - $file (" . round($size / 1024, 2) . " KB) - $time\n";
        }
    }
}

?>
