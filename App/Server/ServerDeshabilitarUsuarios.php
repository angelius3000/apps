<?php

include("../../Connections/ConDB.php");

$USUARIOIDDeshabilitar = mysqli_real_escape_string($conn, $_POST['USUARIOID']);

// Build the base query
$sql = "UPDATE usuarios SET 
    Deshabilitado = 1
    WHERE USUARIOID = '$USUARIOIDDeshabilitar'";

if (!mysqli_query($conn, $sql)) {
    die('Error: ' . mysqli_error($conn));
}

$msg = array('USUARIOID' => $USUARIOIDDeshabilitar);

// send data as json format
echo json_encode($msg);
