<?php
/**
 * Script de diagnóstico para rutas de facturas
 * Uso: php tools/diagnostico_facturas.php
 */

session_start();

// Simular usuario logueado para pruebas
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'user';

echo "═══════════════════════════════════════════════════════════\n";
echo "🔧 DIAGNÓSTICO DE RUTAS DE FACTURAS\n";
echo "═══════════════════════════════════════════════════════════\n\n";

// Verificar archivos necesarios
echo "✓ Verificando archivos...\n";
$files = [
    'App/Controllers/InvoiceController.php' => 'Controlador de facturas',
    'App/Core/InvoiceGenerator.php' => 'Generador de facturas',
    'Public/index.php' => 'Router principal',
];

foreach ($files as $file => $desc) {
    $exists = file_exists(__DIR__ . '/../' . $file);
    echo ($exists ? '✓' : '✗') . " {$desc}: {$file}\n";
}

echo "\n✓ Verificando clases y métodos...\n";

// Verificar que InvoiceController existe
if (class_exists('App\\Controllers\\InvoiceController')) {
    echo "✓ Clase InvoiceController encontrada\n";
    $ref = new ReflectionClass('App\\Controllers\\InvoiceController');
    $methods = $ref->getMethods(ReflectionMethod::IS_PUBLIC);
    echo "  Métodos públicos:\n";
    foreach ($methods as $method) {
        if ($method->getDeclaringClass()->getName() === 'App\\Controllers\\InvoiceController') {
            echo "    • {$method->getName()}()\n";
        }
    }
} else {
    echo "✗ Clase InvoiceController NO encontrada\n";
}

echo "\n✓ Verificando rutas en index.php...\n";
$indexContent = file_get_contents(__DIR__ . '/../Public/index.php');
$routes = [
    '/factura/ver' => 'Ver factura',
    '/factura/descargar' => 'Descargar PDF',
    '/factura/reenviar' => 'Reenviar por email',
];

foreach ($routes as $route => $desc) {
    $found = strpos($indexContent, "'{$route}'") !== false || strpos($indexContent, "\"{$route}\"") !== false;
    echo ($found ? '✓' : '✗') . " {$desc}: {$route}\n";
}

echo "\n✓ Verificando variables de sesión...\n";
echo "  \$_SESSION['user_id'] = " . ($_SESSION['user_id'] ?? 'NO DEFINIDA') . "\n";
echo "  \$_SESSION['user_role'] = " . ($_SESSION['user_role'] ?? 'NO DEFINIDA') . "\n";

echo "\n✓ URLs para prueba (reemplaza ID con un pedido real):\n";
echo "  📖 Ver: http://localhost/TIENDA_MOVICELL/Public/index.php?r=/factura/ver&id=20\n";
echo "  📥 Descargar: http://localhost/TIENDA_MOVICELL/Public/index.php?r=/factura/descargar&id=20\n";

echo "\n═══════════════════════════════════════════════════════════\n";
echo "✅ DIAGNÓSTICO COMPLETADO\n";
echo "═══════════════════════════════════════════════════════════\n";
