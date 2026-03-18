<?php

include("../../Connections/ConDB.php");

header('Content-Type: application/json');

$productosId = isset($_POST['PRODUCTOSIDEditar']) ? (int)$_POST['PRODUCTOSIDEditar'] : 0;
$sku = isset($_POST['SkuEditar']) ? trim($_POST['SkuEditar']) : '';
$descripcion = isset($_POST['DescripcionEditar']) ? trim($_POST['DescripcionEditar']) : '';
$marcaProductos = isset($_POST['MarcaProductosEditar']) ? trim($_POST['MarcaProductosEditar']) : '';

if ($productosId <= 0 || $sku === '') {
    http_response_code(400);
    echo json_encode([
        'error' => 'Faltan datos obligatorios para actualizar el producto.'
    ]);
    mysqli_close($conn);
    exit;
}

$skuSql = "'" . mysqli_real_escape_string($conn, $sku) . "'";
$descripcionSql = ($descripcion !== '') ? "'" . mysqli_real_escape_string($conn, $descripcion) . "'" : "NULL";
$marcaSql = ($marcaProductos !== '') ? "'" . mysqli_real_escape_string($conn, $marcaProductos) . "'" : "NULL";

$sql = "UPDATE productos SET
    Sku = $skuSql,
    Descripcion = $descripcionSql,
    MarcaProductos = $marcaSql
    WHERE PRODUCTOSID = $productosId";

if (!mysqli_query($conn, $sql)) {
    die('Error: ' . mysqli_error($conn));
}

echo json_encode(array('PRODUCTOSID' => $productosId));
