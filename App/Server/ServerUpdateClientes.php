<?php

include("../../Connections/ConDB.php");

$CLIENTESIANEditar = mysqli_real_escape_string($conn, $_POST['CLIENTESIANEditar']);

$CLCSIANEditar = mysqli_real_escape_string($conn, $_POST['CLCSIANEditar']);

$NombreClienteEditar = mysqli_real_escape_string($conn, $_POST['NombreClienteEditar']);
$EmailClienteEditar = mysqli_real_escape_string($conn, $_POST['EmailClienteEditar']);
$TelefonoClienteEditar = mysqli_real_escape_string($conn, $_POST['TelefonoClienteEditar']);
$NombreContactoEditar = mysqli_real_escape_string($conn, $_POST['NombreContactoEditar']);
$DireccionClienteEditar = mysqli_real_escape_string($conn, $_POST['DireccionClienteEditar']);
$ColoniaClienteEditar = mysqli_real_escape_string($conn, $_POST['ColoniaClienteEditar']);
$CiudadClienteEditar = mysqli_real_escape_string($conn, $_POST['CiudadClienteEditar']);
$EstadoClienteEditar = mysqli_real_escape_string($conn, $_POST['EstadoClienteEditar']);

$CLIENTEIDEditar = mysqli_real_escape_string($conn, $_POST['CLIENTEIDEditar']);

// Build the base query
$sql = "UPDATE clientes SET 
    CLIENTESIAN = '$CLIENTESIANEditar',
    CLCSIAN = '$CLCSIANEditar',
    NombreCliente = '$NombreClienteEditar',
    TelefonoCliente = '$TelefonoClienteEditar',
    NombreContacto = '$NombreContactoEditar',
    DireccionCliente = '$DireccionClienteEditar',
    ColoniaCliente = '$ColoniaClienteEditar',
    CiudadCliente = '$CiudadClienteEditar',
    EstadoCliente = '$EstadoClienteEditar'
    WHERE CLIENTEID = '$CLIENTEIDEditar'";

if (!mysqli_query($conn, $sql)) {
    die('Error: ' . mysqli_error($conn));
}

$msg = array('CLIENTEID' => $CLIENTEIDEditar);

// send data as json format
echo json_encode($msg);
