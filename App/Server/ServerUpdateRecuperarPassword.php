<?php

include("../../Connections/ConDB.php");

$erroresConexion = isset($connectionError) && $connectionError !== null;
if ($erroresConexion) {
    http_response_code(500);
    echo json_encode(array(
        'Ok' => 'Error',
        'Mensaje' => 'No se pudo conectar a la base de datos.',
    ));
    exit;
}

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

$Password = mysqli_real_escape_string($conn, $_POST['Password']);
$Password = SHA1($Password);

$HASHDelUsuario = mysqli_real_escape_string($conn, $_POST['HASH']);

// Build the base query
$sql = "UPDATE usuarios SET
    Password = '$Password',
    reset_used = 1,
    reset_hash = NULL,
    reset_expires_at = NULL
    WHERE reset_hash = '$HASHDelUsuario'
    AND reset_used = 0
    AND reset_expires_at >= NOW()";

if (!mysqli_query($conn, $sql)) {
    http_response_code(500);
    echo json_encode(array(
        'Ok' => 'Error',
        'Mensaje' => 'No se pudo actualizar la contraseña. Inténtalo de nuevo.',
    ));
    exit;
}

if (mysqli_affected_rows($conn) === 0) {
    http_response_code(400);
    echo json_encode(array(
        'Ok' => 'Error',
        'Mensaje' => 'El link de recuperación es inválido o ha expirado.',
    ));
    exit;
}

$msg = array('USUARIOID' => $HASHDelUsuario);

// send data as json format
echo json_encode($msg);
