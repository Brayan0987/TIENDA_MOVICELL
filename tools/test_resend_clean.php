<?php
/**
 * TEST LIMPIO: Replica exactamente lo que hace resend() desde CLI
 * Propósito: Ver todos los logs y debugging en tiempo real
 */

// Incluir autoloader de Composer PRIMERO
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Incluir config
require_once dirname(__DIR__) . '/App/Core/config.php';

// Autoloader de App
spl_autoload_register(function ($class) {
    $prefix   = 'App\\';
    $base_dir = dirname(__DIR__) . '/App/';
    $len      = strlen($prefix);

    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relative_class = substr($class, $len);
    $file           = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║          TEST LIMPIO: Resend Email - Pedido #20               ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

// Simular sesión de admin
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'admin';

// Simular POST
$_POST['id_pedido'] = 20;

echo "► Variables de sesión configuradas\n";
echo "  user_id: " . $_SESSION['user_id'] . "\n";
echo "  user_role: " . $_SESSION['user_role'] . "\n\n";

echo "► POST data simulado\n";
echo "  id_pedido: " . $_POST['id_pedido'] . "\n\n";

// Crear instancia del controller
$controller = new \App\Controllers\InvoiceController();

echo "► Llamando a InvoiceController::resend()...\n\n";
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║                      EJECUTANDO RESEND()                      ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

// Redirigir output JSON
ob_start();
$controller->resend();
$output = ob_get_clean();

echo "\n╔════════════════════════════════════════════════════════════════╗\n";
echo "║                  RESPUESTA JSON DEL SERVIDOR                  ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";
echo $output . "\n\n";

$result = json_decode($output, true);
echo "► Resultado parseado:\n";
echo "  success: " . ($result['success'] ? 'TRUE ✓' : 'FALSE ✗') . "\n";
echo "  message: " . $result['message'] . "\n\n";

if ($result['success']) {
    echo "╔════════════════════════════════════════════════════════════════╗\n";
    echo "║                    ✓ EMAIL ENVIADO EXITOSAMENTE               ║\n";
    echo "║  Revisa la bandeja del email para confirmar entrega.           ║\n";
    echo "╚════════════════════════════════════════════════════════════════╝\n";
} else {
    echo "╔════════════════════════════════════════════════════════════════╗\n";
    echo "║                         ✗ ERROR AL ENVIAR                     ║\n";
    echo "║  Revisa los logs del error arriba para detalles.              ║\n";
    echo "╚════════════════════════════════════════════════════════════════╝\n";
}
?>
