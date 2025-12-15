<?php

include("../../Connections/ConDB.php");
include("../../includes/MandarEmail.php");

header('Content-Type: application/json; charset=utf-8');
// Definir La table de la base de datos

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
