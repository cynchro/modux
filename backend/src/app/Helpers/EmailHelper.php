<?php

namespace App\Helpers;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use App\Support\Config;

class EmailHelper
{
    public function sendEmail(
        string $to,
        string $subject,
        string $htmlFileName,
        ?string $fromEmail = null,
        ?string $fromName = null
    ): string {
        $fromEmail ??= Config::get('mail.from_address', 'hello@example.com');
        $fromName  ??= Config::get('mail.from_name', 'App');

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = Config::get('mail.host', 'localhost');
            $mail->SMTPAuth   = true;
            $mail->Username   = Config::get('mail.username', '');
            $mail->Password   = Config::get('mail.password', '');
            $mail->SMTPSecure = Config::get('mail.encryption', PHPMailer::ENCRYPTION_STARTTLS);
            $mail->Port       = Config::get('mail.port', 587);

            $mail->setFrom($fromEmail, $fromName);
            $mail->addAddress($to);
            $mail->addReplyTo($fromEmail, $fromName);

            $backtrace  = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
            $callerFile = $backtrace[1]['file'] ?? '';
            $modulePath = dirname(dirname($callerFile));
            $htmlPath   = realpath($modulePath . '/Emails/' . $htmlFileName);

            if (!$htmlPath || !file_exists($htmlPath)) {
                throw new Exception('Email template not found: ' . $htmlFileName);
            }

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = file_get_contents($htmlPath);

            $mail->send();

            return 'Message sent successfully.';
        } catch (Exception $e) {
            return 'Message could not be sent. Mailer error: ' . $e->getMessage();
        }
    }
}
