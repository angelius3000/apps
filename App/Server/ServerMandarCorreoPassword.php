<?php

include("../../Connections/ConDB.php");
include("../../includes/MandarEmail.php");

header('Content-Type: application/json; charset=utf-8');
// Definir La table de la base de datos

$erroresConexion = isset($connectionError) && $connectionError !== null;
if ($erroresConexion) {
    http_response_code(500);
    echo json_encode(array(
        'Ok' => 'Error',
        'Mensaje' => 'No se pudo conectar a la base de datos.',
    ));
    exit;
}

function asegurarColumnasReset($conn)
{
    $columnasNecesarias = array(
        'reset_hash' => "ALTER TABLE usuarios ADD COLUMN reset_hash VARCHAR(255) NULL",
        'reset_expires_at' => "ALTER TABLE usuarios ADD COLUMN reset_expires_at DATETIME NULL",
        'reset_used' => "ALTER TABLE usuarios ADD COLUMN reset_used TINYINT(1) NOT NULL DEFAULT 0",
    );

    foreach ($columnasNecesarias as $columna => $sqlAlter) {
        $existeColumna = mysqli_query($conn, "SHOW COLUMNS FROM usuarios LIKE '$columna'");
        if ($existeColumna instanceof mysqli_result) {
            $tieneColumna = mysqli_num_rows($existeColumna) > 0;
            mysqli_free_result($existeColumna);

            if (!$tieneColumna) {
                @mysqli_query($conn, $sqlAlter);
            }
        }
    }
}

asegurarColumnasReset($conn);

$EmailDeUsuario = mysqli_real_escape_string($conn, $_POST['email']);

$resetHash = bin2hex(random_bytes(32));

$sql = "UPDATE usuarios SET
    reset_hash = '$resetHash',
    reset_expires_at = DATE_ADD(NOW(), INTERVAL 60 MINUTE),
    reset_used = 0
  WHERE email = '$EmailDeUsuario'";

if (!mysqli_query($conn, $sql)) {
    http_response_code(500);
    echo json_encode(array(
        'Ok' => 'Error',
        'Mensaje' => 'No se pudo procesar la solicitud de recuperación.',
    ));
    exit;
}

if (mysqli_affected_rows($conn) === 0) {
    http_response_code(400);
    echo json_encode(array(
        'Ok' => 'Error',
        'Mensaje' => 'No existe una cuenta registrada con ese correo.',
    ));
    exit;
}

$mailSent = RecuperaTuPassword($EmailDeUsuario, $resetHash);

if (!$mailSent) {
    echo json_encode(array(
        'Ok' => 'Advertencia',
        'Email' => $EmailDeUsuario,
        'Mensaje' => 'No se pudo enviar el correo de recuperación, pero tu solicitud fue registrada. Inténtalo más tarde.',
    ));
    exit;
}

echo json_encode(array(
    'Ok' => 'Ok',
    'Email' => $EmailDeUsuario,
    'Mensaje' => 'Mandaste un correo a ' . $EmailDeUsuario . ' para poder seleccionar una nueva contraseña',
));
