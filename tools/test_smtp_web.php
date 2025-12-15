<?php
/**
 * Test de conectividad SMTP desde contexto web
 * Simula exactamente lo que hace sendInvoice()
 */

require_once dirname(__DIR__) . '/App/Core/config.php';
require_once dirname(__DIR__) . '/vendor/autoload.php';

echo "<h2>🔧 Test SMTP desde Contexto Web</h2>\n";
echo "<hr>\n";

// Mostrar config
echo "<h3>Configuración:</h3>\n";
echo "<pre>";
echo "MAIL_USE_SMTP: " . (MAIL_USE_SMTP ? 'true' : 'false') . "\n";
echo "MAIL_SMTP_HOST: " . MAIL_SMTP_HOST . "\n";
echo "MAIL_SMTP_PORT: " . MAIL_SMTP_PORT . "\n";
echo "MAIL_SMTP_USER: " . MAIL_SMTP_USER . "\n";
echo "MAIL_SMTP_SECURE: " . MAIL_SMTP_SECURE . "\n";
echo "MAIL_FROM_ADDRESS: " . MAIL_FROM_ADDRESS . "\n";
echo "PHPMailer disponible: " . (class_exists('PHPMailer\PHPMailer\PHPMailer') ? 'YES' : 'NO') . "\n";
echo "</pre>\n";

// Test 1: Crear instancia
echo "<h3>Test 1: Crear instancia PHPMailer</h3>\n";
try {
    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
    echo "✅ Instancia creada\n";
} catch (\Throwable $e) {
    die("❌ Error: " . $e->getMessage());
}

// Test 2: Configurar SMTP
echo "<h3>Test 2: Configurar SMTP</h3>\n";
try {
    $mail->isSMTP();
    echo "✅ Modo SMTP activado\n";
    
    $mail->Host = MAIL_SMTP_HOST;
    $mail->SMTPAuth = true;
    $mail->Username = MAIL_SMTP_USER;
    $mail->Password = MAIL_SMTP_PASS;
    $mail->SMTPSecure = MAIL_SMTP_SECURE ?: '';
    $mail->Port = MAIL_SMTP_PORT;
    $mail->SMTPDebug = 2; // Mostrar debug
    $mail->Timeout = 10;
    
    echo "✅ Configuración SMTP asignada\n";
} catch (\Throwable $e) {
    die("❌ Error: " . $e->getMessage());
}

// Test 3: Conectar
echo "<h3>Test 3: Intentar conectar a " . MAIL_SMTP_HOST . ":" . MAIL_SMTP_PORT . "</h3>\n";
echo "<pre style='background: #f0f0f0; padding: 10px; max-height: 300px; overflow: auto;'>\n";
try {
    $mail->smtpConnect();
    echo "✅ Conexión exitosa\n";
    $mail->smtpClose();
} catch (\Throwable $e) {
    echo "❌ Error de conexión: " . $e->getMessage() . "\n";
    echo "Code: " . $e->getCode() . "\n";
}
echo "</pre>\n";

// Test 4: Intentar enviar un email de prueba
echo "<h3>Test 4: Intentar enviar email de prueba</h3>\n";
try {
    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = MAIL_SMTP_HOST;
    $mail->SMTPAuth = true;
    $mail->Username = MAIL_SMTP_USER;
    $mail->Password = MAIL_SMTP_PASS;
    $mail->SMTPSecure = MAIL_SMTP_SECURE ?: '';
    $mail->Port = MAIL_SMTP_PORT;
    $mail->Timeout = 10;
    
    $mail->setFrom(MAIL_FROM_ADDRESS, 'MOVI CELL Test');
    $mail->addAddress('jhuertaslambrano@gmail.com');
    $mail->isHTML(true);
    $mail->Subject = 'Test desde /tools/test_smtp_web.php';
    $mail->Body = '<h1>Test</h1><p>Este es un email de prueba desde el contexto web</p>';
    
    echo "Enviando a: jhuertaslambrano@gmail.com\n";
    echo "Asunto: Test desde /tools/test_smtp_web.php\n\n";
    
    $mail->send();
    echo "✅ Email enviado correctamente\n";
} catch (\Throwable $e) {
    echo "❌ Error al enviar: " . $e->getMessage() . "\n";
}

?>
