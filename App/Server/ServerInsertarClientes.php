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

$sql = "INSERT INTO clientes (CLIENTESIAN, CLCSIAN, NombreCliente) VALUES ($CLIENTESIAN, $CLCSIAN, '$NombreCliente')";

if (!mysqli_query($conn, $sql)) {
    die('Error: ' . mysqli_error($conn));
}

$last_id = mysqli_insert_id($conn);

$msg = array('CLIENTEID' => $last_id);

echo json_encode($msg);

mysqli_close($conn);
