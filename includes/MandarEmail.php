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
    $smtpUser = obtenerVariableEntorno('SMTP_USER', 'apps@edison.com.mx');
    $smtpPass = obtenerVariableEntorno('SMTP_PASS', '');
    $smtpDebug = obtenerVariableEntorno('APP_DEBUG', '0');

    if ($smtpPass === '') {
        error_log('PHPMailer CONFIG ERROR: SMTP_PASS no está configurado.');
        return false;
    }

    $resetUrl = 'https://apps.edison.com.mx/RecuperarTuPassword.php?HASH=' . urlencode($Hash);
    $mailBody = '
        <html>
        <body>
            <p>Para recuperar tu contraseña, pulsa el botón:</p>
            <p>
                <a href="' . $resetUrl . '" target="_blank"
                   style="padding:10px 14px;border-radius:6px;background:#0895ca;color:#fff;text-decoration:none;">
                   Recuperar contraseña
                </a>
            </p>
            <p style="font-size:12px;color:#666;">
                Este es un correo automático, no respondas a este mensaje.
            </p>
        </body>
        </html>';

    $estrategiasSmtp = array(
        array('seguridad' => PHPMailer::ENCRYPTION_STARTTLS, 'puerto' => 587, 'label' => 'STARTTLS-587'),
        array('seguridad' => PHPMailer::ENCRYPTION_SMTPS, 'puerto' => 465, 'label' => 'SMTPS-465'),
    );

    foreach ($estrategiasSmtp as $estrategia) {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = 'mail.edison.com.mx';
            $mail->Port       = $estrategia['puerto'];
            $mail->SMTPAuth   = true;
            $mail->SMTPSecure = $estrategia['seguridad'];
            $mail->Username   = $smtpUser;
            $mail->Password   = $smtpPass;

            // Habilitar debug a error_log (Plesk)
            $mail->SMTPDebug = $smtpDebug === '1' ? 2 : 0;
            $mail->Debugoutput = function ($str, $level) {
                error_log("PHPMailer [$level]: $str");
            };

            // Algunos servidores cPanel/Plesk usan certificados autofirmados.
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,
                ),
            );

            $mail->Timeout = 10;

            $mail->setFrom('apps@edison.com.mx', 'Edison Apps');
            $mail->addAddress($email);
            $mail->Subject = 'Recupera tu contraseña';
            $mail->CharSet = 'UTF-8';
            $mail->isHTML(true);
            $mail->Body = $mailBody;
            $mail->AltBody = "Recupera tu contraseña aquí: $resetUrl";

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log('PHPMailer ERROR [' . $estrategia['label'] . ']: ' . $mail->ErrorInfo);
            error_log('PHPMailer EXCEPTION [' . $estrategia['label'] . ']: ' . $e->getMessage());
        }
    }

    return false;
}

function EnviarNotificacionSolicitudMaterialPendiente(array $numerosCliente, array $skusProductos, string $documento = '')
{
    $numerosCliente = array_values(array_unique(array_filter(array_map('trim', $numerosCliente))));
    $skusProductos = array_values(array_unique(array_filter(array_map('trim', $skusProductos))));

    if (empty($numerosCliente) && empty($skusProductos)) {
        return true;
    }

    $smtpUser = obtenerVariableEntorno('SMTP_USER', 'apps@edison.com.mx');
    $smtpPass = obtenerVariableEntorno('SMTP_PASS', '');
    $smtpDebug = obtenerVariableEntorno('APP_DEBUG', '0');

    if ($smtpPass === '') {
        error_log('PHPMailer CONFIG ERROR: SMTP_PASS no está configurado para notificación de solicitudes de material pendiente.');
        return false;
    }

    $documentoHtml = htmlspecialchars($documento, ENT_QUOTES, 'UTF-8');
    $clientesHtml = '';
    foreach ($numerosCliente as $numeroCliente) {
        $clientesHtml .= '<li>' . htmlspecialchars($numeroCliente, ENT_QUOTES, 'UTF-8') . '</li>';
    }

    $productosHtml = '';
    foreach ($skusProductos as $skuProducto) {
        $productosHtml .= '<li>' . htmlspecialchars($skuProducto, ENT_QUOTES, 'UTF-8') . '</li>';
    }

    $mailBody = '<html><body>'
        . '<p>Se generó una nueva solicitud desde la sección de Material Pendiente.</p>'
        . ($documentoHtml !== '' ? '<p><strong>Documento:</strong> ' . $documentoHtml . '</p>' : '')
        . (!empty($numerosCliente) ? '<p><strong>Clientes solicitados:</strong></p><ul>' . $clientesHtml . '</ul>' : '')
        . (!empty($skusProductos) ? '<p><strong>Productos solicitados (SKU):</strong></p><ul>' . $productosHtml . '</ul>' : '')
        . '<p style="font-size:12px;color:#666;">Este es un correo automático, no respondas a este mensaje.</p>'
        . '</body></html>';

    $altBody = "Se generó una nueva solicitud desde Material Pendiente.";
    if ($documento !== '') {
        $altBody .= "\nDocumento: " . $documento;
    }
    if (!empty($numerosCliente)) {
        $altBody .= "\nClientes solicitados: " . implode(', ', $numerosCliente);
    }
    if (!empty($skusProductos)) {
        $altBody .= "\nProductos solicitados (SKU): " . implode(', ', $skusProductos);
    }

    $estrategiasSmtp = array(
        array('seguridad' => PHPMailer::ENCRYPTION_STARTTLS, 'puerto' => 587, 'label' => 'STARTTLS-587'),
        array('seguridad' => PHPMailer::ENCRYPTION_SMTPS, 'puerto' => 465, 'label' => 'SMTPS-465'),
    );

    foreach ($estrategiasSmtp as $estrategia) {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = 'mail.edison.com.mx';
            $mail->Port       = $estrategia['puerto'];
            $mail->SMTPAuth   = true;
            $mail->SMTPSecure = $estrategia['seguridad'];
            $mail->Username   = $smtpUser;
            $mail->Password   = $smtpPass;
            $mail->SMTPDebug = $smtpDebug === '1' ? 2 : 0;
            $mail->Debugoutput = function ($str, $level) {
                error_log("PHPMailer [$level]: $str");
            };
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,
                ),
            );
            $mail->Timeout = 10;

            $mail->setFrom('apps@edison.com.mx', 'Edison Apps');
            $mail->addAddress('sistemas@edison.com.mx');
            $mail->Subject = 'Nueva solicitud en Material Pendiente';
            $mail->CharSet = 'UTF-8';
            $mail->isHTML(true);
            $mail->Body = $mailBody;
            $mail->AltBody = $altBody;

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log('PHPMailer ERROR solicitud material pendiente [' . $estrategia['label'] . ']: ' . $mail->ErrorInfo);
            error_log('PHPMailer EXCEPTION solicitud material pendiente [' . $estrategia['label'] . ']: ' . $e->getMessage());
        }
    }

    return false;
}

// RecuperaTuPassword('sistemas@edison.com.mx', '123123123');
// RecuperaTuPassword('aguevara@studioa.com.mx', '123123123');

// Funcion para el Olvidaste tu password
