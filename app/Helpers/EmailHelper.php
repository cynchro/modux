<?php

namespace App\Helpers;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailHelper
{

    function sendEmail($to, $subject, $htmlFileName, $fromEmail = 'from@example.com', $fromName = 'From Name')
    {

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.mailtrap.io';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'a5a6694d8c6356';
            $mail->Password   = '9b158ed0914a47';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 2525;

            $mail->setFrom($fromEmail, $fromName);
            $mail->addAddress($to);
            $mail->addReplyTo($fromEmail, $fromName);

            // Detectar la ruta del controlador que llamó a este helper
            $backtrace = debug_backtrace();
            $callerFile = $backtrace[0]['file'];

            // Asumimos que la estructura es algo como App/Modules/{ModuleName}/Controllers/{ControllerName}.php
            $modulePath = dirname(dirname($callerFile));  // Esto nos lleva al directorio del módulo (ej. App/Modules/Product)

            // Construimos la ruta del archivo HTML en la carpeta Emails del módulo
            $htmlFullPath = realpath($modulePath . '/Emails/' . $htmlFileName);

            // Leer el archivo HTML
            if ($htmlFullPath && file_exists($htmlFullPath)) {
                $body = file_get_contents($htmlFullPath);
            } else {
                throw new Exception('No se pudo encontrar el archivo HTML: ' . $htmlFullPath);
            }

            // Contenido del email
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;

            // Enviar el correo
            $mail->send();
            return 'El mensaje ha sido enviado con éxito';
        } catch (Exception $e) {
            return "El mensaje no pudo ser enviado. Error de Mailer: {$e->getMessage()}";
        }
    }
}
