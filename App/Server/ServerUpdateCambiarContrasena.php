<?php

include("../../Connections/ConDB.php");

$Password = mysqli_real_escape_string($conn, $_POST['Contrasena']);
$Password = SHA1($Password);

$USUARIOID = mysqli_real_escape_string($conn, $_POST['USUARIOIDCambioContrasena']);

// Build the base query
$sql = "UPDATE usuarios SET 
    Password = '$Password'
    WHERE USUARIOID = '$USUARIOID'";

if (!mysqli_query($conn, $sql)) {
    die('Error: ' . mysqli_error($conn));
}

$msg = array('USUARIOID' => $USUARIOID);

// send data as json format
echo json_encode($msg);
