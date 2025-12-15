<?php
// Script de prueba para generar/guardar una factura usando App\Core\Mailer::sendInvoice
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../App/Core/config.php';

use App\Core\Mailer;

try {
    $html = '<h1>Factura de prueba</h1><p>Este es un correo de prueba generado por Mailer::sendInvoice.</p>';
    $ok = Mailer::sendInvoice('test+local@example.com', 'Factura de prueba', $html, 9999);
    echo $ok ? "sendInvoice ejecutado (revisa storage/mails/)\n" : "sendInvoice devolvió false\n";
} catch (Throwable $t) {
    echo 'Error: ' . $t->getMessage() . "\n";
}
