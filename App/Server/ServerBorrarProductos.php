<?php

include("../../Connections/ConDB.php");

$productosId = isset($_POST['PRODUCTOSID']) ? (int)$_POST['PRODUCTOSID'] : 0;

if ($productosId <= 0) {
    http_response_code(400);
    echo json_encode([
        'error' => 'Producto inválido.'
    ]);
    mysqli_close($conn);
    exit;
}

$sql = "DELETE FROM productos WHERE PRODUCTOSID = $productosId";

if (!mysqli_query($conn, $sql)) {
    die('Error: ' . mysqli_error($conn));
}

echo json_encode(array('PRODUCTOSID' => $productosId));
