<?php
/**
 * Script de prueba para enviar factura por correo
 * Uso: http://localhost/TIENDA_MOVICELL/tools/test_resend_invoice.php?id=20
 */

require_once dirname(__DIR__) . '/Public/index.php'; // Carga configuración y autoloader

use App\Core\Db;
use App\Core\Mailer;
use App\Core\InvoiceGenerator;

$id_pedido = intval($_GET['id'] ?? 0);

if (!$id_pedido) {
    die('❌ Falta el parámetro ?id=XX');
}

echo "<h2>🧪 Test Envío de Factura por Email</h2>\n";
echo "<p>ID Pedido: <strong>$id_pedido</strong></p>\n";

// Conectar BD
try {
    $db = Db::conn();
    echo "✅ Conexión a BD exitosa\n<br/>";
} catch (Exception $e) {
    die("❌ Error BD: " . $e->getMessage());
}

// Obtener datos del pedido
$stmt = $db->prepare('SELECT p.*, e.estado as estado_nombre, u.nombre, u.correo, u.telefono
                      FROM pedido p
                      LEFT JOIN estado e ON p.id_estado = e.id_estado
                      LEFT JOIN roles_usuario ru ON p.id_usuario_rol = ru.id_usuario_rol
                      LEFT JOIN usuario u ON ru.id_usuario = u.id_usuario
                      WHERE p.id_pedido = ?');
$stmt->bind_param('i', $id_pedido);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();
$stmt->close();

if (!$order) {
    die("❌ Pedido no encontrado");
}

echo "✅ Pedido encontrado\n<br/>";
echo "Cliente: <strong>" . htmlspecialchars($order['nombre'] ?? 'N/A') . "</strong><br/>";
echo "Correo: <strong>" . htmlspecialchars($order['correo'] ?? 'N/A') . "</strong><br/>";
echo "<br/>";

// Obtener detalles del pedido
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
                      WHERE dp.id_pedido = ?
                      ORDER BY dp.id_detalle');
$stmt->bind_param('i', $id_pedido);
$stmt->execute();
$result = $stmt->get_result();
$items = [];
while ($row = $result->fetch_assoc()) {
    $items[] = $row;
}
$stmt->close();

echo "✅ Detalles obtenidos (" . count($items) . " producto(s))\n<br/>";

// Generar HTML
try {
    $html = InvoiceGenerator::generateInvoiceHtml($order, $items);
    echo "✅ HTML generado\n<br/>";
} catch (Exception $e) {
    die("❌ Error generando HTML: " . $e->getMessage());
}

// Enviar email
echo "📧 Intentando enviar email a: <strong>" . htmlspecialchars($order['correo']) . "</strong>\n<br/>";
$to = $order['correo'] ?? '';
$subject = 'Factura Pedido #' . $id_pedido . ' - MOVIL CELL';

$result = Mailer::sendInvoice($to, $subject, $html, $id_pedido);

if ($result) {
    echo "✅ <strong>sendInvoice() devolvió true</strong>\n<br/>";
} else {
    echo "❌ <strong>sendInvoice() devolvió false</strong>\n<br/>";
}

// Revisar si el archivo se guardó
$storageDir = dirname(__DIR__) . '/storage/mails';
if (is_dir($storageDir)) {
    $files = scandir($storageDir);
    echo "<br/><h3>📁 Archivos en storage/mails/:</h3>\n";
    echo "<ul>\n";
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            $filePath = $storageDir . '/' . $file;
            $size = filesize($filePath);
            echo "<li>$file (" . round($size / 1024, 2) . " KB)</li>\n";
        }
    }
    echo "</ul>\n";
} else {
    echo "<p>⚠️ storage/mails/ no existe</p>\n";
}

// Revisar error_log
echo "<br/><h3>📋 Últimas líneas de error_log:</h3>\n";
$logFile = ini_get('error_log');
if ($logFile && file_exists($logFile)) {
    $lines = file($logFile);
    $lastLines = array_slice($lines, -10);
    echo "<pre style='background: #f0f0f0; padding: 10px; overflow: auto;'>";
    foreach ($lastLines as $line) {
        echo htmlspecialchars($line);
    }
    echo "</pre>\n";
} else {
    echo "<p>No se encontró error_log</p>\n";
}

echo "<hr/><p><a href=''>🔄 Recargar</a></p>\n";
?>
