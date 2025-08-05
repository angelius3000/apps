<?php

include("../../Connections/ConDB.php");

$TIPODEUSUARIOIDEditar = mysqli_real_escape_string($conn, $_POST['TIPODEUSUARIOIDEditar']);
$PrimerNombreEditar = mysqli_real_escape_string($conn, $_POST['PrimerNombreEditar']);
$SegundoNombreEditar = mysqli_real_escape_string($conn, $_POST['SegundoNombreEditar']);
$ApellidoPaternoEditar = mysqli_real_escape_string($conn, $_POST['ApellidoPaternoEditar']);
$ApellidoMaternoEditar = mysqli_real_escape_string($conn, $_POST['ApellidoMaternoEditar']);
$emailEditar = mysqli_real_escape_string($conn, $_POST['emailEditar']);
$TelefonoEditar = mysqli_real_escape_string($conn, $_POST['TelefonoEditar']);
$USUARIOIDEditar = mysqli_real_escape_string($conn, $_POST['USUARIOIDEditar']);
// $CLIENTEIDEditar = isset($_POST['CLIENTEID']) ? mysqli_real_escape_string($conn, $_POST['CLIENTEIDEditar']) : 0;

// Build the base query
$sql = "UPDATE usuarios SET 
    PrimerNombre = '$PrimerNombreEditar',
    SegundoNombre = '$SegundoNombreEditar',
    ApellidoPaterno = '$ApellidoPaternoEditar',
    ApellidoMaterno = '$ApellidoMaternoEditar',
    email = '$emailEditar',
    Telefono = '$TelefonoEditar',
    TIPODEUSUARIOID = '$TIPODEUSUARIOIDEditar'
    -- CLIENTEID = '$CLIENTEIDEditar'
    WHERE USUARIOID = '$USUARIOIDEditar'";

if (!mysqli_query($conn, $sql)) {
    die('Error: ' . mysqli_error($conn));
}

$msg = array('USUARIOID' => $USUARIOIDEditar);

// send data as json format
echo json_encode($msg);
