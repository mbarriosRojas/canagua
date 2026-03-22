<?php

/**
 * Helper para envío de correos (recuperación de clave, etc.)
 * Si está configurado SMTP (MAIL_SMTP_HOST), usa PHPMailer; si no, usa mail() de PHP.
 */
class MailHelper {

    /**
     * Envía un correo de texto plano.
     * @param string $to Email del destinatario
     * @param string $subject Asunto
     * @param string $body Cuerpo del mensaje (texto plano)
     * @return bool true si se envió correctamente
     */
    public static function send($to, $subject, $body) {
        $from = Config::get('mail.from');
        $fromName = Config::get('mail.from_name');
        $smtpHost = Config::get('mail.smtp_host');

        if ($smtpHost !== '' && $smtpHost !== null) {
            return self::sendViaSmtp($to, $subject, $body, $from, $fromName);
        }

        return self::sendViaMail($to, $subject, $body, $from, $fromName);
    }

    /**
     * Envía el correo con la nueva clave al usuario.
     * @param string $to Email del usuario
     * @param string $nombre Nombre del usuario (para personalizar)
     * @param string $nuevaClave Clave en texto plano (solo para este correo)
     * @return bool
     */
    public static function sendRecoveryPassword($to, $nombre, $nuevaClave) {
        $appName = Config::getAppName();
        $subject = "Recuperación de contraseña - {$appName}";
        $body = "Hola " . trim($nombre) . ",\n\n";
        $body .= "Se ha generado una nueva contraseña para tu cuenta en {$appName}.\n\n";
        $body .= "Tu nueva contraseña es: " . $nuevaClave . "\n\n";
        $body .= "Te recomendamos cambiarla después de iniciar sesión por seguridad.\n\n";
        $body .= "Saludos,\n{$appName}";
        return self::send($to, $subject, $body);
    }

    /**
     * Envío vía PHPMailer SMTP.
     */
    private static function sendViaSmtp($to, $subject, $body, $from, $fromName) {
        $autoload = __DIR__ . '/../../vendor/autoload.php';
        if (!is_file($autoload)) {
            error_log('MailHelper: vendor/autoload.php no encontrado; ejecute composer update.');
            return false;
        }
        require_once $autoload;

        if (!class_exists(\PHPMailer\PHPMailer\PHPMailer::class)) {
            error_log('MailHelper: PHPMailer no instalado. Ejecute en la raíz del proyecto: composer update');
            return false;
        }

        try {
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = Config::get('mail.smtp_host');
            $mail->SMTPAuth   = true;
            $mail->Username   = Config::get('mail.smtp_user');
            $mail->Password   = Config::get('mail.smtp_pass');
            $mail->SMTPSecure = Config::get('mail.smtp_secure') ?: 'tls';
            $mail->Port       = Config::get('mail.smtp_port');
            $mail->CharSet    = 'UTF-8';
            $mail->setFrom($from, $fromName);
            $mail->addAddress($to);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->isHTML(false);
            $mail->send();
            return true;
        } catch (\Exception $e) {
            error_log('MailHelper SMTP: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Envío vía mail() de PHP (fallback cuando no hay SMTP configurado).
     */
    private static function sendViaMail($to, $subject, $body, $from, $fromName) {
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/plain; charset=UTF-8',
            'From: ' . self::formatAddress($from, $fromName),
            'X-Mailer: PHP/' . phpversion()
        ];
        $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
        return @mail($to, $encodedSubject, $body, implode("\r\n", $headers));
    }

    private static function formatAddress($email, $name = null) {
        $email = trim($email);
        if (empty($name)) {
            return $email;
        }
        return '"' . str_replace(['"', "\r", "\n"], ['', '', ''], $name) . '" <' . $email . '>';
    }
}
