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

$skuEscapado = mysqli_real_escape_string($conn, $sku);
$consultaSkuDuplicado = mysqli_query($conn, "SELECT PRODUCTOSID FROM productos WHERE Sku = '$skuEscapado' LIMIT 1");

if ($consultaSkuDuplicado && mysqli_num_rows($consultaSkuDuplicado) > 0) {
    http_response_code(409);
    echo json_encode([
        'error' => 'Ya existe un producto registrado con el SKU capturado.'
    ]);
    mysqli_free_result($consultaSkuDuplicado);
    mysqli_close($conn);
    exit;
}

if ($consultaSkuDuplicado) {
    mysqli_free_result($consultaSkuDuplicado);
}

$skuSql = "'" . $skuEscapado . "'";
$descripcionSql = ($descripcion !== '') ? "'" . mysqli_real_escape_string($conn, $descripcion) . "'" : "NULL";
$marcaSql = ($marcaProductos !== '') ? "'" . mysqli_real_escape_string($conn, $marcaProductos) . "'" : "NULL";

$sql = "INSERT INTO productos (Sku, Descripcion, MarcaProductos) VALUES ($skuSql, $descripcionSql, $marcaSql)";

if (!mysqli_query($conn, $sql)) {
    die('Error: ' . mysqli_error($conn));
}

$lastId = mysqli_insert_id($conn);

@mysqli_query($conn, "CREATE TABLE IF NOT EXISTS Solicitud_Productos (SolicitudProductoID INT NOT NULL AUTO_INCREMENT, SKU VARCHAR(100) NOT NULL, Atendida TINYINT(1) NOT NULL DEFAULT 0, FechaSolicitud TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, FechaAtencion TIMESTAMP NULL DEFAULT NULL, PRIMARY KEY (SolicitudProductoID), INDEX idx_solicitud_producto_estado (Atendida), INDEX idx_solicitud_producto_sku (SKU)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$skuEscapadoActualizar = mysqli_real_escape_string($conn, $sku);
$descripcionEscapadaActualizar = mysqli_real_escape_string($conn, $descripcion !== '' ? $descripcion : 'SOLICITADO');
@mysqli_query($conn, "UPDATE Solicitud_Productos SET Atendida = 1, FechaAtencion = NOW() WHERE SKU = '$skuEscapadoActualizar' AND Atendida = 0");
@mysqli_query($conn, "UPDATE materialpendiente SET DescripcionMP = '$descripcionEscapadaActualizar' WHERE SkuMP = '$skuEscapadoActualizar' AND DescripcionMP = 'SOLICITADO'");

echo json_encode(array('PRODUCTOSID' => $lastId));

mysqli_close($conn);
