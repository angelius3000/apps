<?php

include("../../Connections/ConDB.php");

header('Content-Type: application/json');

$sku = isset($_POST['Sku']) ? trim($_POST['Sku']) : '';
$descripcion = isset($_POST['Descripcion']) ? trim($_POST['Descripcion']) : '';
$marcaProductos = isset($_POST['MarcaProductos']) ? trim($_POST['MarcaProductos']) : '';

if ($sku === '') {
    http_response_code(400);
    echo json_encode([
        'error' => 'El SKU es obligatorio.'
    ]);
    mysqli_close($conn);
    exit;
}

$skuSql = "'" . mysqli_real_escape_string($conn, $sku) . "'";
$descripcionSql = ($descripcion !== '') ? "'" . mysqli_real_escape_string($conn, $descripcion) . "'" : "NULL";
$marcaSql = ($marcaProductos !== '') ? "'" . mysqli_real_escape_string($conn, $marcaProductos) . "'" : "NULL";

$sql = "INSERT INTO productos (Sku, Descripcion, MarcaProductos) VALUES ($skuSql, $descripcionSql, $marcaSql)";

if (!mysqli_query($conn, $sql)) {
    die('Error: ' . mysqli_error($conn));
}

$lastId = mysqli_insert_id($conn);

echo json_encode(array('PRODUCTOSID' => $lastId));

mysqli_close($conn);
