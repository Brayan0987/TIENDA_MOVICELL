<?php
namespace App\Core;

final class Mailer
{
    /**
     * Enviar correo HTML simple (sin adjuntos). Usa PHPMailer+SMTP si está configurado,
     * o cae de vuelta a mail() y finalmente a guardar en disco.
     */
    public static function sendHtml(string $to, string $subject, string $html): bool
    {
        // Preferir PHPMailer si está disponible y MAIL_USE_SMTP está activado
        if (defined('MAIL_USE_SMTP') && MAIL_USE_SMTP && class_exists('\PHPMailer\PHPMailer\PHPMailer')) {
            try {
                $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
                // SMTP
                $mail->isSMTP();
                $mail->Host = MAIL_SMTP_HOST;
                $mail->SMTPAuth = true;
                $mail->Username = MAIL_SMTP_USER;
                $mail->Password = MAIL_SMTP_PASS;
                $mail->SMTPSecure = MAIL_SMTP_SECURE ?: '';
                $mail->Port = MAIL_SMTP_PORT;

                $mail->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);
                $mail->addAddress($to);
                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body = $html;

                $mail->send();
                error_log('Mailer::sendHtml - PHPMailer sent to ' . $to);
                return true;
            } catch (\Throwable $e) {
                error_log('Mailer::sendHtml - PHPMailer error: ' . $e->getMessage());
                // continue to fallback
            }
        }

        // Try PHP mail()
        $from = defined('MAIL_FROM_ADDRESS') ? MAIL_FROM_ADDRESS : 'no-reply@localhost';
        $headers = [];
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-type: text/html; charset=UTF-8';
        $headers[] = 'From: ' . $from;
        $headers[] = 'X-Mailer: PHP/' . phpversion();
        $headersStr = implode("\r\n", $headers);

        $ok = false;
        try {
            $ok = @mail($to, $subject, $html, $headersStr);
        } catch (\Throwable $t) {
            error_log('Mailer::sendHtml - mail() threw: ' . $t->getMessage());
            $ok = false;
        }

        if ($ok) {
            error_log('Mailer::sendHtml - mail() sent to ' . $to);
            return true;
        }

        // fallback: save to storage/mails as .html file
        $storageDir = __DIR__ . '/../../storage/mails';
        if (!is_dir($storageDir)) {
            @mkdir($storageDir, 0777, true);
        }
        $fname = $storageDir . '/mail_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.html';
        $content = "To: $to\nSubject: $subject\n\n" . $html;
        @file_put_contents($fname, $content);
        error_log('Mailer::sendHtml - mail saved to ' . $fname);
        return true;
    }

    /**
     * Genera un PDF de la factura (si mPDF está disponible) y envía el correo con adjunto.
     * SOLO devuelve true si el email fue enviado exitosamente.
     */
    public static function sendInvoice(string $to, string $subject, string $html, ?int $orderId = null): bool
    {
        $storageDir = __DIR__ . '/../../storage/mails';
        if (!is_dir($storageDir)) {
            @mkdir($storageDir, 0777, true);
        }

        $attachmentPath = null;
        $filename = 'factura_' . ($orderId ? $orderId : 'x') . '_' . date('Ymd_His');

        // Intentar generar PDF con mPDF
        if (class_exists('\Mpdf\Mpdf')) {
            try {
                $mpdf = new \Mpdf\Mpdf();
                $mpdf->WriteHTML($html);
                $attachmentPath = $storageDir . '/' . $filename . '.pdf';
                $mpdf->Output($attachmentPath, \Mpdf\Output\Destination::FILE);
                error_log('✅ Mailer::sendInvoice - PDF generated: ' . $attachmentPath);
            } catch (\Throwable $e) {
                error_log('❌ Mailer::sendInvoice - mPDF error: ' . $e->getMessage());
                $attachmentPath = null;
            }
        }

        // Si no se generó PDF, guardar HTML como .html y usarlo como adjunto
        if (empty($attachmentPath)) {
            $attachmentPath = $storageDir . '/' . $filename . '.html';
            @file_put_contents($attachmentPath, $html);
            error_log('ℹ️ Mailer::sendInvoice - HTML invoice saved to: ' . $attachmentPath);
        }

        // **INTENTO 1: PHPMailer + SMTP (PRINCIPAL)**
        // Debug: Verificar si estamos listos para SMTP
        error_log('🔍 DEBUG - MAIL_USE_SMTP defined: ' . (defined('MAIL_USE_SMTP') ? 'YES' : 'NO'));
        error_log('🔍 DEBUG - MAIL_USE_SMTP value: ' . (defined('MAIL_USE_SMTP') && MAIL_USE_SMTP ? 'true' : 'false'));
        error_log('🔍 DEBUG - PHPMailer class exists: ' . (class_exists('\PHPMailer\PHPMailer\PHPMailer') ? 'YES' : 'NO'));
        
        if (defined('MAIL_USE_SMTP') && MAIL_USE_SMTP && class_exists('\PHPMailer\PHPMailer\PHPMailer')) {
            try {
                error_log('⏳ Mailer::sendInvoice - Intentando PHPMailer SMTP...');
                
                $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
                $mail->isSMTP();
                $mail->Host = MAIL_SMTP_HOST;
                $mail->SMTPAuth = true;
                $mail->Username = MAIL_SMTP_USER;
                $mail->Password = MAIL_SMTP_PASS;
                $mail->SMTPSecure = MAIL_SMTP_SECURE ?: '';
                $mail->Port = MAIL_SMTP_PORT;
                $mail->SMTPDebug = 0;
                $mail->Timeout = 30;
                $mail->CharSet = 'UTF-8';

                error_log('ℹ️ SMTP Config: Host=' . MAIL_SMTP_HOST . ':' . MAIL_SMTP_PORT . 
                         ', User=' . MAIL_SMTP_USER . ', Secure=' . (MAIL_SMTP_SECURE ?: 'none'));

                $mail->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);
                $mail->addAddress($to);
                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body = $html;
                $mail->AltBody = strip_tags($html);
                
                // Headers para mejorar entrega
                $mail->addCustomHeader('X-Priority', '3');
                $mail->addCustomHeader('X-Mailer', 'MOVIL CELL Facturación');
                $mail->addCustomHeader('List-Unsubscribe', '<mailto:' . MAIL_FROM_ADDRESS . '?subject=unsubscribe>');
                
                if ($attachmentPath && file_exists($attachmentPath)) {
                    $mail->addAttachment($attachmentPath);
                    error_log('ℹ️ Adjunto agregado: ' . $attachmentPath);
                }
                
                error_log('⏳ Conectando a SMTP...');
                $mail->send();
                error_log('✅ PHPMailer SMTP enviado exitosamente a: ' . $to);
                return true;
                
            } catch (\Throwable $e) {
                error_log('❌ PHPMailer SMTP falló: ' . $e->getMessage());
                error_log('   Código error: ' . $e->getCode());
                // Continuar al siguiente intento
            }
        } else {
            error_log('⚠️ PHPMailer SMTP no disponible - Saltando a siguiente método');
        }

        // **INTENTO 2: PHP mail() nativa**
        error_log('⏳ Mailer::sendInvoice - Intentando PHP mail() nativa...');
        
        $headers = [];
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-type: text/html; charset=UTF-8';
        $headers[] = 'From: ' . (defined('MAIL_FROM_ADDRESS') ? MAIL_FROM_ADDRESS : 'no-reply@localhost');
        $headers[] = 'X-Mailer: MOVIL CELL';
        $headersStr = implode("\r\n", $headers);

        try {
            $ok = @mail($to, $subject, $html, $headersStr);
            if ($ok) {
                error_log('✅ PHP mail() enviado exitosamente a: ' . $to);
                return true;
            } else {
                error_log('❌ PHP mail() retornó false');
            }
        } catch (\Throwable $t) {
            error_log('❌ PHP mail() lanzó excepción: ' . $t->getMessage());
        }

        // **FALLBACK: Email no se envió**
        error_log('⚠️ Mailer::sendInvoice - NO SE PUDO ENVIAR EMAIL. Factura guardada en: ' . $attachmentPath);
        error_log('⚠️ Razón: SMTP y mail() fallaron. Usuario debe descargar manualmente o revisar configuración.');
        return false;
    }
}
