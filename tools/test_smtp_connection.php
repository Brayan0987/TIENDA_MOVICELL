<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../App/Core/config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

$mail = new PHPMailer(true);

try {
    // Enable verbose output
    $mail->SMTPDebug = SMTP::DEBUG_SERVER;
    $mail->Debugoutput = function($str, $level) {
        echo "[SMTP DEBUG] $str\n";
    };

    $mail->isSMTP();
    $mail->Host = MAIL_SMTP_HOST;
    $mail->SMTPAuth = true;
    $mail->Username = MAIL_SMTP_USER;
    $mail->Password = MAIL_SMTP_PASS;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = MAIL_SMTP_PORT;

    echo "=== Intentando conectar a SMTP ===\n";
    echo "Host: " . MAIL_SMTP_HOST . "\n";
    echo "Puerto: " . MAIL_SMTP_PORT . "\n";
    echo "Usuario: " . MAIL_SMTP_USER . "\n";
    echo "Cifrado: TLS (STARTTLS)\n\n";

    $mail->setFrom(MAIL_SMTP_USER, 'Test');
    $mail->addAddress('jhuertaslambrano@gmail.com');
    $mail->isHTML(false);
    $mail->Subject = 'Test SMTP Connection';
    $mail->Body = 'Testing SMTP connection from XAMPP';

    $mail->send();
    echo "\n✓ Correo enviado exitosamente\n";
} catch (Exception $e) {
    echo "\n✗ Error: " . $e->getMessage() . "\n";
    echo "Excepción: " . get_class($e) . "\n";
}
