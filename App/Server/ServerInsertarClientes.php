<?php

include("../../Connections/ConDB.php");
include("../../includes/Funciones.php");

header('Content-Type: application/json');

$clienteSianInput = isset($_POST['CLIENTESIAN']) ? trim($_POST['CLIENTESIAN']) : '';
$clcSianInput = isset($_POST['CLCSIAN']) ? trim($_POST['CLCSIAN']) : '';

if ($clienteSianInput === '' && $clcSianInput === '') {
    http_response_code(400);
    echo json_encode([
        'error' => 'Captura al menos uno de los números de cliente (CLIENTESIAN o CLCSIAN).'
    ]);
    mysqli_close($conn);
    exit;
}

$CLIENTESIAN = ($clienteSianInput !== '')
    ? "'" . mysqli_real_escape_string($conn, $clienteSianInput) . "'"
    : "NULL";
$CLCSIAN = ($clcSianInput !== '')
    ? "'" . mysqli_real_escape_string($conn, $clcSianInput) . "'"
    : "NULL";

$NombreCliente = isset($_POST['NombreCliente']) ? mysqli_real_escape_string($conn, $_POST['NombreCliente']) : NULL;

$EmailCliente = isset($_POST['EmailCliente']) ? mysqli_real_escape_string($conn, $_POST['EmailCliente']) : NULL;
$TelefonoCliente = isset($_POST['TelefonoCliente']) ? mysqli_real_escape_string($conn, $_POST['TelefonoCliente']) : NULL;
$NombreContacto = isset($_POST['NombreContacto']) ? mysqli_real_escape_string($conn, $_POST['NombreContacto']) : NULL;
$DireccionCliente = isset($_POST['DireccionCliente']) ? mysqli_real_escape_string($conn, $_POST['DireccionCliente']) : NULL;
$ColoniaCliente = isset($_POST['ColoniaCliente']) ? mysqli_real_escape_string($conn, $_POST['ColoniaCliente']) : NULL;
$CiudadCliente = isset($_POST['CiudadCliente']) ? mysqli_real_escape_string($conn, $_POST['CiudadCliente']) : NULL;
$EstadoCliente = isset($_POST['EstadoCliente']) ? mysqli_real_escape_string($conn, $_POST['EstadoCliente']) : NULL;

$sql = "INSERT INTO clientes (CLIENTESIAN,CLCSIAN, NombreCliente, EmailCliente, TelefonoCliente, NombreContacto, DireccionCliente, ColoniaCliente, CiudadCliente, EstadoCliente) VALUES ($CLIENTESIAN, $CLCSIAN, '$NombreCliente', '$EmailCliente', '$TelefonoCliente', '$NombreContacto', '$DireccionCliente', '$ColoniaCliente', '$CiudadCliente', '$EstadoCliente')";

if (!mysqli_query($conn, $sql)) {
    die('Error: ' . mysqli_error($conn));
}

$last_id = mysqli_insert_id($conn);

$msg = array('CLIENTEID' => $last_id);

// send data as json format
echo json_encode($msg);

mysqli_close($conn);
