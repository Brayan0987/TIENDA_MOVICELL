<?php
/**
 * Script para ver los últimos logs de error_log
 */

// Diferentes ubicaciones posibles de error_log
$posiblesLogs = [
    ini_get('error_log'),
    'C:\xampp\php\logs\php_error_log',
    'C:\xampp\apache\logs\error.log',
    dirname(__DIR__) . '/logs/php_error.log',
];

echo "<h2>📋 Buscando Logs de PHP</h2>\n";
echo "<p>Última ejecución de /factura/reenviar</p>\n";
echo "<hr>\n";

$logEncontrado = false;

foreach ($posiblesLogs as $logPath) {
    if (!$logPath) continue;
    
    if (file_exists($logPath)) {
        echo "<h3>✅ Archivo encontrado: " . htmlspecialchars($logPath) . "</h3>\n";
        
        $lines = file($logPath);
        $ultimasLineas = array_slice($lines, -50); // Últimas 50 líneas
        
        echo "<pre style='background: #f0f0f0; padding: 10px; overflow: auto; max-height: 400px;'>";
        foreach ($ultimasLineas as $line) {
            echo htmlspecialchars($line);
        }
        echo "</pre>\n";
        
        $logEncontrado = true;
        break;
    }
}

if (!$logEncontrado) {
    echo "<p>❌ No se encontraron logs de PHP</p>\n";
    echo "<p>Ubicaciones buscadas:</p>\n";
    echo "<ul>\n";
    foreach ($posiblesLogs as $path) {
        echo "<li>" . htmlspecialchars($path) . "</li>\n";
    }
    echo "</ul>\n";
}

?>
