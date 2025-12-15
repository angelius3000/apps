<?php

include("../../Connections/ConDB.php");

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
    die('Error: ' . mysqli_error($conn));
}

if (mysqli_affected_rows($conn) === 0) {
    die('Error: El link de recuperación es inválido o ha expirado.');
}

$msg = array('USUARIOID' => $HASHDelUsuario);

// send data as json format
echo json_encode($msg);
