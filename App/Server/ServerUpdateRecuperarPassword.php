<?php

include("../../Connections/ConDB.php");

$Password = mysqli_real_escape_string($conn, $_POST['Password']);
$Password = SHA1($Password);

$HASHDelUsuario = mysqli_real_escape_string($conn, $_POST['HASH']);

// Build the base query
$sql = "UPDATE usuarios SET 
    Password = '$Password'
    WHERE HASH = '$HASHDelUsuario'";

if (!mysqli_query($conn, $sql)) {
    die('Error: ' . mysqli_error($conn));
}

$msg = array('USUARIOID' => $HASHDelUsuario);

// send data as json format
echo json_encode($msg);
