<?php

include("../../Connections/ConDB.php");

$USUARIOIDDeshabilitar = mysqli_real_escape_string($conn, $_POST['USUARIOID']);
$nuevoEstado = isset($_POST['Deshabilitado']) ? (int)$_POST['Deshabilitado'] : 1;

$nuevoEstado = $nuevoEstado === 0 ? 0 : 1;

// Build the base query
$sql = "UPDATE usuarios SET
    Deshabilitado = $nuevoEstado
    WHERE USUARIOID = '$USUARIOIDDeshabilitar'";

if (!mysqli_query($conn, $sql)) {
    die('Error: ' . mysqli_error($conn));
}

$msg = array('USUARIOID' => $USUARIOIDDeshabilitar);

// send data as json format
echo json_encode($msg);
