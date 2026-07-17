<?php
if (!isset($_SESSION)) {
    session_start();
}

include('../../Connections/ConDB.php');
header('Content-Type: application/json; charset=utf-8');

function responderActualizacionIncidencia(array $respuesta, int $codigo = 200): void
{
    http_response_code($codigo);
    echo json_encode($respuesta);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$conn) {
    responderActualizacionIncidencia(['ok' => false, 'error' => 'Solicitud no válida.'], 400);
}

$id = filter_var($_POST['incidenciaId'] ?? null, FILTER_VALIDATE_INT);
$folio = trim((string) ($_POST['folio'] ?? ''));
$cantidad = filter_var($_POST['cantidad'] ?? null, FILTER_VALIDATE_FLOAT);
$precio = filter_var($_POST['precioUnitario'] ?? null, FILTER_VALIDATE_FLOAT);
$productoId = (int) ($_POST['productoId'] ?? 0);
$skuExistente = trim((string) ($_POST['productoSolicitadoSku'] ?? ''));
$descripcionExistente = trim((string) ($_POST['descripcionExistente'] ?? ''));
$marcaExistente = trim((string) ($_POST['marcaExistente'] ?? ''));
$responsable = trim((string) ($_POST['responsableOtro'] ?? ''));
$creadoPor = trim((string) ($_POST['creadorOtro'] ?? ''));
$comentarios = trim((string) ($_POST['comentarios'] ?? ''));

if (!$id || $folio === '' || $cantidad === false || $cantidad <= 0 || $precio === false || $precio < 0 || $responsable === '' || $creadoPor === '') {
    responderActualizacionIncidencia(['ok' => false, 'error' => 'Completa todos los campos obligatorios con valores válidos.'], 422);
}

$sku = $skuExistente;
$descripcion = $descripcionExistente;
$marca = $marcaExistente;
if ($productoId > 0) {
    $stmtProducto = mysqli_prepare($conn, 'SELECT Sku, Descripcion, MarcaProductos FROM productos WHERE PRODUCTOSID = ? LIMIT 1');
    if (!$stmtProducto) {
        responderActualizacionIncidencia(['ok' => false, 'error' => 'No se pudo validar el producto seleccionado.'], 500);
    }
    mysqli_stmt_bind_param($stmtProducto, 'i', $productoId);
    mysqli_stmt_execute($stmtProducto);
    $producto = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtProducto));
    mysqli_stmt_close($stmtProducto);
    if (!$producto) {
        responderActualizacionIncidencia(['ok' => false, 'error' => 'El producto seleccionado ya no está disponible.'], 422);
    }
    $sku = (string) $producto['Sku'];
    $descripcion = (string) $producto['Descripcion'];
    $marca = (string) ($producto['MarcaProductos'] ?? '');
}

if ($sku === '' || $descripcion === '') {
    responderActualizacionIncidencia(['ok' => false, 'error' => 'Selecciona un producto válido.'], 422);
}

$total = round((float) $cantidad * (float) $precio, 2);
$stmt = mysqli_prepare($conn, 'UPDATE incidencias SET Folio = ?, Cantidad = ?, SKU = ?, Descripcion = ?, Marca = ?, PrecioUnitario = ?, Responsable = ?, Total = ?, CreadoPor = ?, Comentarios = ? WHERE IncidenciaID = ?');
if (!$stmt) {
    responderActualizacionIncidencia(['ok' => false, 'error' => 'No se pudo preparar la actualización.'], 500);
}
mysqli_stmt_bind_param($stmt, 'sdsssdsdssi', $folio, $cantidad, $sku, $descripcion, $marca, $precio, $responsable, $total, $creadoPor, $comentarios, $id);
if (!mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    responderActualizacionIncidencia(['ok' => false, 'error' => 'No se pudo actualizar la incidencia.'], 500);
}
$afectadas = mysqli_stmt_affected_rows($stmt);
mysqli_stmt_close($stmt);
if ($afectadas === 0) {
    $stmtExiste = mysqli_prepare($conn, 'SELECT 1 FROM incidencias WHERE IncidenciaID = ? LIMIT 1');
    if (!$stmtExiste) {
        responderActualizacionIncidencia(['ok' => false, 'error' => 'No se pudo comprobar la incidencia.'], 500);
    }
    mysqli_stmt_bind_param($stmtExiste, 'i', $id);
    mysqli_stmt_execute($stmtExiste);
    mysqli_stmt_store_result($stmtExiste);
    $existe = mysqli_stmt_num_rows($stmtExiste) > 0;
    mysqli_stmt_close($stmtExiste);
    if (!$existe) {
        responderActualizacionIncidencia(['ok' => false, 'error' => 'La incidencia ya no existe.'], 404);
    }
}

responderActualizacionIncidencia(['ok' => true]);
