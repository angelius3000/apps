<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Instalacion de php Mailer para mandar correos por SMTP ... Se puso el camino completo para que no hubiera problemas cuando se manda desde un Server ... porque es el camino relativo desde esa carpeta ... Pero creo que todas las notificaciones deben de salir asi ... debe de haber una forma de hacerlo Global como la conexion ... pero por ser el primer Script que se hace asi lo mantendremos asi ...

//require '../vendor/autoload.php'; // Si lo mando desde el Link Directo se le quita un Nivel al ../

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function obtenerVariableEntorno($llave, $valorPorDefecto = '')
{
    $valor = getenv($llave);
    if ($valor !== false && $valor !== '') {
        return $valor;
    }

    if (isset($_ENV[$llave]) && $_ENV[$llave] !== '') {
        return $_ENV[$llave];
    }

    if (isset($_SERVER[$llave]) && $_SERVER[$llave] !== '') {
        return $_SERVER[$llave];
    }

    return $valorPorDefecto;
}

function RecuperaTuPassword($email, $Hash)
{
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'mail.edison.com.mx';
        $mail->Port       = 587;
        $mail->SMTPAuth   = true;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

        $smtpUser = obtenerVariableEntorno('SMTP_USER', 'apps@edison.com.mx');
        $smtpPass = obtenerVariableEntorno('SMTP_PASS', '');
        $smtpDebug = obtenerVariableEntorno('APP_DEBUG', '0');

        if ($smtpPass === '') {
            error_log('PHPMailer CONFIG ERROR: SMTP_PASS no está configurado.');
            return false;
        }

        $mail->Username   = $smtpUser;
        $mail->Password   = $smtpPass;

        // Habilitar debug a error_log (Plesk)
        $mail->SMTPDebug = $smtpDebug === '1' ? 2 : 0;
        $mail->Debugoutput = function ($str, $level) {
            error_log("PHPMailer [$level]: $str");
        };

        $mail->Timeout = 10;

        $mail->setFrom('apps@edison.com.mx', 'Edison Apps');
        $mail->AuthType = 'LOGIN';
        $mail->addAddress($email);
        $mail->Subject = 'Recupera tu contraseña';

        $mail->CharSet = 'UTF-8';
        $mail->isHTML(true);

        $resetUrl = 'https://apps.edison.com.mx/RecuperarTuPassword.php?HASH=' . urlencode($Hash);

        $mail->Body = '
        <html>
        <body>
            <p>Para recuperar tu contraseña, pulsa el botón:</p>
            <p>
                <a href="'.$resetUrl.'" target="_blank"
                   style="padding:10px 14px;border-radius:6px;background:#0895ca;color:#fff;text-decoration:none;">
                   Recuperar contraseña
                </a>
            </p>
            <p style="font-size:12px;color:#666;">
                Este es un correo automático, no respondas a este mensaje.
            </p>
        </body>
        </html>';

        $mail->AltBody = "Recupera tu contraseña aquí: $resetUrl";

        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log('PHPMailer ERROR: ' . $mail->ErrorInfo);
        error_log('PHPMailer EXCEPTION: ' . $e->getMessage());
        return false;
    }
}

// RecuperaTuPassword('sistemas@edison.com.mx', '123123123');
// RecuperaTuPassword('aguevara@studioa.com.mx', '123123123');

// Funcion para el Olvidaste tu password
