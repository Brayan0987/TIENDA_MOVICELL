<?php
/**
 * Script para probar envío de email directamente
 * Acceso: http://localhost/TIENDA_MOVICELL/tools/test_email_direct.php
 */

session_start();

// Simular que estamos logueados
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 7; // nando
    $_SESSION['user_role'] = 'user';
}

require_once dirname(__DIR__) . '/App/Core/config.php';
require_once dirname(__DIR__) . '/vendor/autoload.php';

use App\Core\Db;
use App\Core\Mailer;
use App\Core\InvoiceGenerator;

$id_pedido = 20; // Usar pedido 20 del usuario nando

echo "<h2>📧 Test Directo de Envío de Email</h2>\n";
echo "<p>ID Pedido: <strong>$id_pedido</strong></p>\n";
echo "<p>Usuario ID: <strong>" . $_SESSION['user_id'] . "</strong></p>\n";
echo "<hr>\n";

try {
    // Conectar BD
    $db = Db::conn();
    echo "✅ Conexión BD exitosa\n<br>\n";

    // Obtener pedido
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
        die("❌ Pedido no encontrado\n");
    }

    echo "✅ Pedido encontrado\n<br>\n";
    echo "<strong>Cliente:</strong> " . htmlspecialchars($order['nombre']) . "\n<br>\n";
    echo "<strong>Correo:</strong> " . htmlspecialchars($order['correo']) . "\n<br>\n";
    echo "<strong>Estado:</strong> " . htmlspecialchars($order['estado_nombre']) . "\n<br>\n";
    echo "<hr>\n";

    // Obtener detalles
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
    $stmt->bind_param('i', $id_pedido);
    $stmt->execute();
    $result = $stmt->get_result();
    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
    $stmt->close();

    echo "✅ Detalles obtenidos: " . count($items) . " productos\n<br><br>\n";

    // Generar HTML
    $html = InvoiceGenerator::generateInvoiceHtml($order, $items);
    echo "✅ HTML generado (" . strlen($html) . " bytes)\n<br><br>\n";

    // Enviar email
    $correo = $order['correo'];
    $subject = 'Factura Pedido #' . $id_pedido . ' - MOVIL CELL (Test)';

    echo "📧 <strong>Enviando email a: $correo</strong>\n<br>\n";
    echo "Asunto: <strong>$subject</strong>\n<br><br>\n";

    $resultado = Mailer::sendInvoice($correo, $subject, $html, $id_pedido);

    echo "Resultado: <strong>" . ($resultado ? 'true' : 'false') . "</strong>\n<br>\n";

    if ($resultado) {
        echo "✅ <strong>Email enviado exitosamente</strong>\n<br>\n";
    } else {
        echo "⚠️ <strong>sendInvoice devolvió false</strong>\n<br>\n";
    }

    // Revisar carpeta de storage
    echo "<hr>\n";
    echo "<h3>📁 Archivos generados en storage/mails/:</h3>\n";
    $storageDir = dirname(__DIR__) . '/storage/mails';
    if (is_dir($storageDir)) {
        $files = array_diff(scandir($storageDir), ['.', '..']);
        $files = array_reverse(array_values($files)); // Mostrar más recientes primero
        
        if (empty($files)) {
            echo "<p>No hay archivos</p>\n";
        } else {
            echo "<ul>\n";
            foreach (array_slice($files, 0, 10) as $file) {
                $path = $storageDir . '/' . $file;
                $size = filesize($path);
                $time = date('Y-m-d H:i:s', filemtime($path));
                echo "<li><strong>$file</strong> - " . round($size / 1024, 2) . " KB - $time</li>\n";
            }
            echo "</ul>\n";
        }
    }

} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n<br>\n";
    echo "<pre>" . $e->getTraceAsString() . "</pre>\n";
}

?>
