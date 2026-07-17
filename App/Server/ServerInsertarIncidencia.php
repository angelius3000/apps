<?php
include('../../Connections/ConDB.php');
header('Content-Type: application/json; charset=utf-8');
function responderIncidencia(array $respuesta, int $codigo = 200): void { http_response_code($codigo); echo json_encode($respuesta); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$conn) { responderIncidencia(['ok' => false, 'error' => 'Solicitud no válida.'], 400); }
$folio = trim((string)($_POST['folio'] ?? ''));
$productoId = (int)($_POST['productoId'] ?? 0);
$productoSolicitadoSku = trim((string)($_POST['productoSolicitadoSku'] ?? ''));
$esProductoSolicitado = $productoId === 0 && $productoSolicitadoSku !== '';
$vendedorId = (int)($_POST['vendedorId'] ?? 0);
$aduanaId = (int)($_POST['aduanaId'] ?? 0);
$cantidad = filter_var($_POST['cantidad'] ?? null, FILTER_VALIDATE_FLOAT);
$precio = filter_var($_POST['precioUnitario'] ?? null, FILTER_VALIDATE_FLOAT);
$comentarios = trim((string)($_POST['comentarios'] ?? ''));
if ($folio === '' || (!$productoId && !$esProductoSolicitado) || !$vendedorId || !$aduanaId || $cantidad === false || $cantidad <= 0 || $precio === false || $precio < 0) { responderIncidencia(['ok' => false, 'error' => 'Completa todos los campos obligatorios con valores válidos.'], 422); }
$stmtProducto = mysqli_prepare($conn, 'SELECT Sku, Descripcion, MarcaProductos FROM productos WHERE PRODUCTOSID = ? LIMIT 1');
$stmtVendedor = mysqli_prepare($conn, 'SELECT NombreVendedor FROM vendedor WHERE vendedorID = ? AND Deshabilitado = 0 LIMIT 1');
$stmtAduana = mysqli_prepare($conn, 'SELECT NombreAduana FROM aduana WHERE AduanaID = ? AND Deshabilitado = 0 LIMIT 1');
if (!$stmtProducto || !$stmtVendedor || !$stmtAduana) { responderIncidencia(['ok' => false, 'error' => 'No se pudo validar la información seleccionada.'], 500); }
if ($productoId > 0) {
    mysqli_stmt_bind_param($stmtProducto, 'i', $productoId);
    mysqli_stmt_execute($stmtProducto);
    $producto = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtProducto));
} else {
    $producto = ['Sku' => $productoSolicitadoSku, 'Descripcion' => 'SOLICITADO', 'MarcaProductos' => ''];
}
mysqli_stmt_bind_param($stmtVendedor, 'i', $vendedorId); mysqli_stmt_execute($stmtVendedor); $vendedor = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtVendedor));
mysqli_stmt_bind_param($stmtAduana, 'i', $aduanaId); mysqli_stmt_execute($stmtAduana); $aduana = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtAduana));
mysqli_stmt_close($stmtProducto); mysqli_stmt_close($stmtVendedor); mysqli_stmt_close($stmtAduana);
if (!$producto || !$vendedor || !$aduana) { responderIncidencia(['ok' => false, 'error' => 'Alguna de las opciones seleccionadas ya no está disponible.'], 422); }
$total = round((float)$cantidad * (float)$precio, 2);
if ($esProductoSolicitado) {
    @mysqli_query($conn, 'CREATE TABLE IF NOT EXISTS Solicitud_Productos (SolicitudProductoID INT NOT NULL AUTO_INCREMENT, SKU VARCHAR(100) NOT NULL, Atendida TINYINT(1) NOT NULL DEFAULT 0, FechaSolicitud TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, FechaAtencion TIMESTAMP NULL DEFAULT NULL, SolicitanteNombre VARCHAR(255) NULL, PRIMARY KEY (SolicitudProductoID), INDEX idx_solicitud_producto_estado (Atendida), INDEX idx_solicitud_producto_sku (SKU)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
    $solicitante = trim((string)($_SESSION['NombreDelUsuario'] ?? $_SESSION['Username'] ?? ''));
    $stmtSolicitud = mysqli_prepare($conn, 'INSERT INTO Solicitud_Productos (SKU, SolicitanteNombre) VALUES (?, ?)');
    if ($stmtSolicitud) { mysqli_stmt_bind_param($stmtSolicitud, 'ss', $productoSolicitadoSku, $solicitante); mysqli_stmt_execute($stmtSolicitud); mysqli_stmt_close($stmtSolicitud); }
}
@mysqli_query($conn, 'CREATE TABLE IF NOT EXISTS incidencias (IncidenciaID INT NOT NULL AUTO_INCREMENT, Fecha TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, Folio VARCHAR(100) NOT NULL, Cantidad DECIMAL(12,2) NOT NULL, SKU VARCHAR(100) NOT NULL, Descripcion VARCHAR(255) NOT NULL, Marca VARCHAR(255) DEFAULT NULL, PrecioUnitario DECIMAL(12,2) NOT NULL, Vendedor VARCHAR(255) NOT NULL, Total DECIMAL(14,2) NOT NULL, CreadoPor VARCHAR(255) NOT NULL, Comentarios TEXT DEFAULT NULL, PRIMARY KEY (IncidenciaID), INDEX idx_incidencias_folio (Folio), INDEX idx_incidencias_sku (SKU)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
$stmt = mysqli_prepare($conn, 'INSERT INTO incidencias (Folio, Cantidad, SKU, Descripcion, Marca, PrecioUnitario, Vendedor, Total, CreadoPor, Comentarios) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
if (!$stmt) { responderIncidencia(['ok' => false, 'error' => 'No se pudo preparar el registro.'], 500); }
$sku = (string)$producto['Sku']; $descripcion = (string)$producto['Descripcion']; $marca = (string)($producto['MarcaProductos'] ?? ''); $nombreVendedor = (string)$vendedor['NombreVendedor']; $creadoPor = (string)$aduana['NombreAduana'];
mysqli_stmt_bind_param($stmt, 'sdsssdsdss', $folio, $cantidad, $sku, $descripcion, $marca, $precio, $nombreVendedor, $total, $creadoPor, $comentarios);
if (!mysqli_stmt_execute($stmt)) { responderIncidencia(['ok' => false, 'error' => 'No se pudo guardar la incidencia.'], 500); }
$id = mysqli_insert_id($conn); mysqli_stmt_close($stmt); responderIncidencia(['ok' => true, 'id' => $id]);
