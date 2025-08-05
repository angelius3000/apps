<?php

include("../../Connections/ConDB.php");
include("../../includes/Funciones.php");

$CLIENTESIAN = mysqli_real_escape_string($conn, $_POST['CLIENTESIAN']);
$CLCSIAN = isset($_POST['CLCSIAN']) ? mysqli_real_escape_string($conn, $_POST['CLCSIAN']) : NULL;

$NombreCliente = isset($_POST['NombreCliente']) ? mysqli_real_escape_string($conn, $_POST['NombreCliente']) : NULL;

$EmailCliente = isset($_POST['EmailCliente']) ? mysqli_real_escape_string($conn, $_POST['EmailCliente']) : NULL;
$TelefonoCliente = isset($_POST['TelefonoCliente']) ? mysqli_real_escape_string($conn, $_POST['TelefonoCliente']) : NULL;
$NombreContacto = isset($_POST['NombreContacto']) ? mysqli_real_escape_string($conn, $_POST['NombreContacto']) : NULL;
$DireccionCliente = isset($_POST['DireccionCliente']) ? mysqli_real_escape_string($conn, $_POST['DireccionCliente']) : NULL;
$ColoniaCliente = isset($_POST['ColoniaCliente']) ? mysqli_real_escape_string($conn, $_POST['ColoniaCliente']) : NULL;
$CiudadCliente = isset($_POST['CiudadCliente']) ? mysqli_real_escape_string($conn, $_POST['CiudadCliente']) : NULL;
$EstadoCliente = isset($_POST['EstadoCliente']) ? mysqli_real_escape_string($conn, $_POST['EstadoCliente']) : NULL;

$sql = "INSERT INTO clientes (CLIENTESIAN,CLCSIAN, NombreCliente, EmailCliente, TelefonoCliente, NombreContacto, DireccionCliente, ColoniaCliente, CiudadCliente, EstadoCliente) VALUES ('$CLIENTESIAN', '$CLCSIAN', '$NombreCliente', '$EmailCliente', '$TelefonoCliente', '$NombreContacto', '$DireccionCliente', '$ColoniaCliente', '$CiudadCliente', '$EstadoCliente')";

if (!mysqli_query($conn, $sql)) {
    die('Error: ' . mysqli_error($conn));
}

$last_id = mysqli_insert_id($conn);

$msg = array('CLIENTEID' => $last_id);

// send data as json format
echo json_encode($msg);

mysqli_close($conn);
