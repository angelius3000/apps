<?php

include("../../Connections/ConDB.php");

header('Content-Type: application/json');

function responderError(string $mensaje, int $codigo = 500): void
{
    http_response_code($codigo);
    echo json_encode(['success' => false, 'message' => $mensaje]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderError('Método no permitido.', 405);
}

if (!$conn) {
    responderError('No se pudo conectar a la base de datos.');
}

function asegurarTablaMaterialPendiente(mysqli $conn): bool
{
    $sqlCrearTabla = "CREATE TABLE IF NOT EXISTS MaterialPendiente (
        MaterialPendienteID INT NOT NULL AUTO_INCREMENT,
        NumeroFactura VARCHAR(100) NOT NULL,
        Sku VARCHAR(100) NOT NULL,
        Cliente VARCHAR(255) NOT NULL,
        Cantidad INT NOT NULL DEFAULT 0,
        Fecha DATE NOT NULL,
        Surtidor VARCHAR(255) DEFAULT NULL,
        Vendedor VARCHAR(255) DEFAULT NULL,
        Aduana VARCHAR(255) DEFAULT NULL,
        OtroProducto TINYINT(1) NOT NULL DEFAULT 0,
        DescripcionMP VARCHAR(255) NOT NULL,
        FechaMP DATE NOT NULL,
        FechaDP DATE DEFAULT NULL,
        Otro VARCHAR(255) DEFAULT NULL,
        PRIMARY KEY (MaterialPendienteID),
        INDEX idx_materialpendiente_factura (NumeroFactura),
        INDEX idx_materialpendiente_sku (Sku)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    return @mysqli_query($conn, $sqlCrearTabla) === true;
}

if (!asegurarTablaMaterialPendiente($conn)) {
    responderError('No se pudo preparar la tabla de material pendiente. Intenta nuevamente.');
}

function obtenerTextoCatalogo(mysqli $conn, string $tabla, string $columnaId, string $columnaTexto, int $id): string
{
    $valor = '';
    $stmt = @mysqli_prepare(
        $conn,
        "SELECT $columnaTexto FROM $tabla WHERE $columnaId = ? LIMIT 1"
    );

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $texto);

        if (mysqli_stmt_fetch($stmt) && $texto !== null) {
            $valor = trim((string) $texto);
        }

        mysqli_stmt_close($stmt);
    }

    return $valor;
}

function normalizarTexto(?string $valor): string
{
    return trim((string) $valor);
}

$numeroFactura = normalizarTexto($_POST['NumeroFacturaPendiente'] ?? '');
$clienteId = isset($_POST['RazonSocialPendiente']) ? (int) $_POST['RazonSocialPendiente'] : 0;
$vendedorId = isset($_POST['VendedorPendiente']) ? (int) $_POST['VendedorPendiente'] : 0;
$vendedorOtro = normalizarTexto($_POST['VendedorPendienteOtro'] ?? '');
$aduanaId = isset($_POST['AduanaPendiente']) ? (int) $_POST['AduanaPendiente'] : 0;
$aduanaOtro = normalizarTexto($_POST['AduanaPendienteOtro'] ?? '');
$surtidorValor = normalizarTexto($_POST['SurtidorPendiente'] ?? '');
$nombreCliente = normalizarTexto($_POST['NombreClientePendiente'] ?? '');
$productos = $_POST['productos'] ?? [];

if ($numeroFactura === '' || $clienteId <= 0 || empty($productos)) {
    responderError('Captura el número de documento, el cliente y al menos una partida pendiente.', 400);
}

$clienteNombre = obtenerTextoCatalogo($conn, 'clientes', 'CLIENTEID', 'NombreCliente', $clienteId);

$VENDEDOR_OTRO_ID = 22;
if ($vendedorId === $VENDEDOR_OTRO_ID) {
    $vendedorNombre = $vendedorOtro;
} else {
    $vendedorNombre = obtenerTextoCatalogo($conn, 'vendedor', 'vendedorID', 'NombreVendedor', $vendedorId);
}

if ($vendedorNombre === '') {
    $vendedorNombre = $vendedorOtro;
}

$ADUANA_OTRO_ID = 4;
if ($aduanaId === $ADUANA_OTRO_ID) {
    $aduanaNombre = $aduanaOtro;
} else {
    $aduanaNombre = obtenerTextoCatalogo($conn, 'aduana', 'AduanaID', 'NombreAduana', $aduanaId);
}

if ($aduanaNombre === '') {
    $aduanaNombre = $aduanaOtro;
}

if ($surtidorValor !== '' && ctype_digit($surtidorValor)) {
    $almacenistaNombre = obtenerTextoCatalogo($conn, 'almacenista', 'AlmacenistaID', 'NombreAlmacenista', (int) $surtidorValor);
    if ($almacenistaNombre !== '') {
        $surtidorValor = $almacenistaNombre;
    }
}

$fechaActual = date('Y-m-d');

$productosValidos = [];
foreach ($productos as $producto) {
    if (!is_array($producto)) {
        continue;
    }

    $sku = normalizarTexto($producto['sku'] ?? '');
    $descripcion = normalizarTexto($producto['descripcion'] ?? '');
    $cantidad = isset($producto['cantidad']) ? (int) $producto['cantidad'] : 0;
    $esOtro = !empty($producto['otro']);

    if ($sku === '' || $descripcion === '' || $cantidad <= 0) {
        continue;
    }

    $productosValidos[] = [
        'sku' => $sku,
        'descripcion' => $descripcion,
        'cantidad' => $cantidad,
        'otro' => $esOtro ? '1' : '0'
    ];
}

if (empty($productosValidos)) {
    responderError('Agrega al menos una partida pendiente válida.', 400);
}

mysqli_begin_transaction($conn);

$stmt = mysqli_prepare(
    $conn,
    'INSERT INTO MaterialPendiente (NumeroFactura, Sku, Cliente, Cantidad, Fecha, Surtidor, Vendedor, Aduana, OtroProducto, DescripcionMP, FechaMP, FechaDP, Otro) '
        . 'VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
);

if (!$stmt) {
    mysqli_rollback($conn);
    responderError('No se pudo preparar la inserción de material pendiente.');
}

$fechaMP = $fechaActual;
$fechaDP = null;
$otroCampo = $nombreCliente !== '' ? $nombreCliente : null;
$clienteParaGuardar = $clienteNombre !== '' ? $clienteNombre : $nombreCliente;

$insertados = 0;

foreach ($productosValidos as $producto) {
    mysqli_stmt_bind_param(
        $stmt,
        'sssisssssssss',
        $numeroFactura,
        $producto['sku'],
        $clienteParaGuardar,
        $producto['cantidad'],
        $fechaActual,
        $surtidorValor,
        $vendedorNombre,
        $aduanaNombre,
        $producto['otro'],
        $producto['descripcion'],
        $fechaMP,
        $fechaDP,
        $otroCampo
    );

    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        mysqli_rollback($conn);
        responderError('No se pudo guardar la información capturada.');
    }

    $insertados++;
}

mysqli_stmt_close($stmt);
mysqli_commit($conn);

echo json_encode([
    'success' => true,
    'inserted' => $insertados
]);
