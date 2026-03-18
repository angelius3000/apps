<?php

include("../../Connections/ConDB.php");

$idProducto = isset($_POST['ID']) ? (int)$_POST['ID'] : 0;

$sql = "SELECT * FROM productos WHERE PRODUCTOSID = $idProducto";
$status = mysqli_query($conn, $sql) or die("database error:" . mysqli_error($conn));
$row = mysqli_fetch_array($status);

if (!$row) {
    echo json_encode([]);
    exit;
}

$msg = array(
    'PRODUCTOSID' => $row['PRODUCTOSID'],
    'Sku' => $row['Sku'],
    'Descripcion' => $row['Descripcion'],
    'MarcaProductos' => $row['MarcaProductos']
);

echo json_encode($msg);
