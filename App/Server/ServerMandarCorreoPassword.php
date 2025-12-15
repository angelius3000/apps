<?php

include("../../Connections/ConDB.php");
include("../../includes/MandarEmail.php");
// Definir La table de la base de datos

$EmailDeUsuario = mysqli_real_escape_string($conn, $_POST['email']);

$resetHash = bin2hex(random_bytes(32));

$sql = "UPDATE usuarios SET
    reset_hash = '$resetHash',
    reset_expires_at = DATE_ADD(NOW(), INTERVAL 60 MINUTE),
    reset_used = 0
  WHERE email = '$EmailDeUsuario'";

if (!mysqli_query($conn, $sql)) {
    die('Error: ' . mysqli_error($conn));
}

if (mysqli_affected_rows($conn) === 0) {
    die('Error: Usuario no encontrado');
}

$mailSent = RecuperaTuPassword($EmailDeUsuario, $resetHash);

if (!$mailSent) {
    http_response_code(500);
    echo json_encode(array(
        'Ok' => 'Error',
        'Mensaje' => 'No se pudo enviar el correo de recuperación. Inténtalo más tarde.',
    ));
    exit;
}

$msg = array(
    'Ok' => 'Ok',
    'Email' => $EmailDeUsuario,
);

// send data as json format
echo json_encode($msg);
