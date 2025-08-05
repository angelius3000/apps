<?php

require '../../vendor/autoload.php';

// Instalacion de php Mailer para mandar correos por SMTP ... Se puso el camino completo para que no hubiera problemas cuando se manda desde un Server ... porque es el camino relativo desde esa carpeta ... Pero creo que todas las notificaciones deben de salir asi ... debe de haber una forma de hacerlo Global como la conexion ... pero por ser el primer Script que se hace asi lo mantendremos asi ...

//require '../vendor/autoload.php'; // Si lo mando desde el Link Directo se le quita un Nivel al ../

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

//Prueba PHP Mailer

function RecuperaTuPassword($email, $Hash)
{
  $mail = new PHPMailer(true);

  try {
    // Configuración del servidor SMTP
    $mail->isSMTP();
    $mail->Host = 'mail.edison.com.mx'; // Reemplaza con la dirección del servidor SMTP
    $mail->Port = 587; // Reemplaza con el puerto SMTP correspondiente
    $mail->SMTPAuth = true;
    $mail->Username = 'notificaciones@edison.com.mx'; // Reemplaza con tu nombre de usuario SMTP
    $mail->Password = 'Ntfccns(2024)*'; // Reemplaza con tu contraseña SMTP

    $mail->setFrom('notificaciones@edison.com.mx', 'Edison Reparto');
    $mail->addAddress($email);
    $mail->Subject = 'Recupera tu contraseña en el sistema Edison Reparto';

    // El contenido dee de ser UTF-8 para que se vean los acentos correctamente
    $mail->CharSet = 'UTF-8';

    // Contenido del mensaje en formato HTML
    $mail->isHTML(true);
    $mail->Body = '
        <html>
        <head>
        <style>
            /* Estilos CSS */
        </style>
        </head>
        <BODY BGCOLOR="White">
        <body>
       
        <div style=" height="40" align="left">

        <font size="3" color="#000000" style="text-decoration:none;">
        <div class="info" Style="align:left;">

        <table width="100%" cellspacing="4" cellpadding="4">
       
        <tr>
        <td><img src="http://reparto.edison.com.mx/App/Graficos/Logo/LogoEdison.png" width="200" alt=""/></td>
        </tr>
        <tr>
        <td align="center" valign="top"><table width="100%" border="0" align="left" cellpadding="0" cellspacing="0">

          <tr>
            <td align="left" valign="top" style="font-family:Arial, Helvetica, sans-serif; font-size:24px; font-weight:normal; color:#30332c; line-height:38px; padding:0px 0px 20px 0px;">Bienvenido</td>
          </tr>
          <tr>
            <td align="left" valign="top" style="font-family:Arial, Helvetica, sans-serif; font-size:16px; font-weight:normal; color:#898989; line-height:30px;">
            
            Hola:
            <br>

            Para recuperar tu contraseña deberás crear una nueva pulsando el botón: 

            </td>
          </tr>
         
          <tr>
            <td align="left" valign="top"><table width="250px" border="0" cellspacing="0" cellpadding="0">
            <tr><td>&nbsp <br></td></tr>
                <tr align="center" >
                <td style="border-radius: 5px;" bgcolor="#0895ca">
             
                    <a href="http://reparto.edison.com.mx/RecuperarTuPassword.php?HASH=' . $Hash . '" target="_blank" style="padding: 8px 12px; border: 1px solid #0895ca;border-radius: 5px;font-family: Helvetica, Arial, sans-serif;font-size: 14px; color: #ffffff; text-decoration: none;font-weight:bold;display: inline-block;">
                        Recupera tu contraseña           
                    </a>
                </td>
                </tr>
                <tr><td>&nbsp <br></td></tr>
                <tr><td>&nbsp <br></td></tr>
            </table></td>
          </tr>
          </table></td>
      </tr>
      </table>
      </div>

        </br>
        <p>-----------------------------------------------------------------------------------------------------------------</p>
        </br>
        <p>(Este es un correo automático por favor no respondas a esta dirección. Cualquier duda contáctanos al correo sistemas@edison.com.mx)</p>
        </font>
        </div>
        </body>
        </html>
        ';

    $mail->send();
  } catch (Exception $e) {
  }
}

// RecuperaTuPassword('sistemas@edison.com.mx', '123123123');
// RecuperaTuPassword('aguevara@studioa.com.mx', '123123123');

// Funcion para el Olvidaste tu password
