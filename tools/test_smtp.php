<?php
/**
 * Script simple para probar el envío de email por factura
 */

// Cargar configuración
require_once dirname(__DIR__) . '/App/Core/config.php';

echo "=== TEST ENVÍO DE FACTURA ===\n\n";

// Test 1: Verificar constants
echo "1. Verificar constantes:\n";
echo "MAIL_USE_SMTP = " . (defined('MAIL_USE_SMTP') ? (MAIL_USE_SMTP ? 'true' : 'false') : 'NO DEFINIDO') . "\n";
echo "MAIL_SMTP_HOST = " . (defined('MAIL_SMTP_HOST') ? MAIL_SMTP_HOST : 'NO DEFINIDO') . "\n";
echo "MAIL_SMTP_USER = " . (defined('MAIL_SMTP_USER') ? MAIL_SMTP_USER : 'NO DEFINIDO') . "\n";
echo "MAIL_SMTP_PORT = " . (defined('MAIL_SMTP_PORT') ? MAIL_SMTP_PORT : 'NO DEFINIDO') . "\n";
echo "MAIL_FROM_ADDRESS = " . (defined('MAIL_FROM_ADDRESS') ? MAIL_FROM_ADDRESS : 'NO DEFINIDO') . "\n";
echo "\n";

// Test 2: Verificar si PHPMailer está disponible
echo "2. Verificar PHPMailer:\n";
require_once dirname(__DIR__) . '/vendor/autoload.php';
echo "PHPMailer disponible: " . (class_exists('PHPMailer\PHPMailer\PHPMailer') ? 'SI' : 'NO') . "\n";
echo "\n";

// Test 3: Probar conexión SMTP
echo "3. Intentar conexión SMTP:\n";
try {
    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = MAIL_SMTP_HOST;
    $mail->SMTPAuth = true;
    $mail->Username = MAIL_SMTP_USER;
    $mail->Password = MAIL_SMTP_PASS;
    $mail->SMTPSecure = MAIL_SMTP_SECURE ?: '';
    $mail->Port = MAIL_SMTP_PORT;
    $mail->SMTPDebug = 2; // Mostrar debug
    
    echo "Conectando a " . MAIL_SMTP_HOST . ":" . MAIL_SMTP_PORT . "\n";
    $mail->smtpConnect();
    echo "✅ Conexión SMTP exitosa\n";
    $mail->smtpClose();
} catch (\Throwable $e) {
    echo "❌ Error SMTP: " . $e->getMessage() . "\n";
}

?>
