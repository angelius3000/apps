<?php

include("../../Connections/ConDB.php");

header('Content-Type: application/json');

$clienteSianEditarInput = isset($_POST['CLIENTESIANEditar']) ? trim($_POST['CLIENTESIANEditar']) : '';
$clcSianEditarInput = isset($_POST['CLCSIANEditar']) ? trim($_POST['CLCSIANEditar']) : '';

if ($clienteSianEditarInput === '' && $clcSianEditarInput === '') {
    http_response_code(400);
    echo json_encode([
        'error' => 'Captura al menos uno de los números de cliente (CLIENTESIAN o CLCSIAN).'
    ]);
    mysqli_close($conn);
    exit;
}

$CLIENTESIANEditar = ($clienteSianEditarInput !== '')
    ? "'" . mysqli_real_escape_string($conn, $clienteSianEditarInput) . "'"
    : "NULL";

$CLCSIANEditar = ($clcSianEditarInput !== '')
    ? "'" . mysqli_real_escape_string($conn, $clcSianEditarInput) . "'"
    : "NULL";

$NombreClienteEditar = mysqli_real_escape_string($conn, $_POST['NombreClienteEditar']);
$CLIENTEIDEditar = mysqli_real_escape_string($conn, $_POST['CLIENTEIDEditar']);

$sql = "UPDATE clientes SET
    CLIENTESIAN = $CLIENTESIANEditar,
    CLCSIAN = $CLCSIANEditar,
    NombreCliente = '$NombreClienteEditar'
    WHERE CLIENTEID = '$CLIENTEIDEditar'";

if (!mysqli_query($conn, $sql)) {
    die('Error: ' . mysqli_error($conn));
}

$msg = array('CLIENTEID' => $CLIENTEIDEditar);

echo json_encode($msg);
