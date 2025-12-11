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
    $sqlCrearTabla = "CREATE TABLE IF NOT EXISTS materialpendiente (
        MaterialPendienteID INT NOT NULL AUTO_INCREMENT,
        DocumentoMP VARCHAR(100) NOT NULL,
        RazonSocialMP VARCHAR(255) NOT NULL,
        VendedorMP VARCHAR(255) DEFAULT NULL,
        SurtidorMP VARCHAR(255) DEFAULT NULL,
        ClienteMP VARCHAR(255) NOT NULL,
        AduanaMP VARCHAR(255) DEFAULT NULL,
        SkuMP VARCHAR(100) NOT NULL,
        DescripcionMP VARCHAR(255) NOT NULL,
        CantidadMP INT NOT NULL DEFAULT 0,
        FechaMP TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (MaterialPendienteID),
        INDEX idx_materialpendiente_documento (DocumentoMP),
        INDEX idx_materialpendiente_sku (SkuMP)
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

$razonSocial = $clienteNombre !== '' ? $clienteNombre : 'Cliente #' . $clienteId;

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

$productosValidos = [];
foreach ($productos as $producto) {
    if (!is_array($producto)) {
        continue;
    }

    $sku = normalizarTexto($producto['sku'] ?? '');
    $descripcion = normalizarTexto($producto['descripcion'] ?? '');
    $cantidad = isset($producto['cantidad']) ? (int) $producto['cantidad'] : 0;

    if ($sku === '' || $descripcion === '' || $cantidad <= 0) {
        continue;
    }

    $productosValidos[] = [
        'sku' => $sku,
        'descripcion' => $descripcion,
        'cantidad' => $cantidad
    ];
}

if (empty($productosValidos)) {
    responderError('Agrega al menos una partida pendiente válida.', 400);
}

mysqli_begin_transaction($conn);

$stmt = mysqli_prepare(
    $conn,
    'INSERT INTO materialpendiente (DocumentoMP, RazonSocialMP, VendedorMP, SurtidorMP, ClienteMP, AduanaMP, SkuMP, DescripcionMP, CantidadMP) '
        . 'VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)' 
);

if (!$stmt) {
    mysqli_rollback($conn);
    responderError('No se pudo preparar la inserción de material pendiente.');
}

$clienteParaGuardar = $nombreCliente !== '' ? $nombreCliente : $razonSocial;

$insertados = 0;

foreach ($productosValidos as $producto) {
    $otroProducto = $producto['otro'] === '1' ? 1 : 0;

    mysqli_stmt_bind_param(
        $stmt,
        'ssssssssi',
        $numeroFactura,
        $razonSocial,
        $vendedorNombre,
        $surtidorValor,
        $clienteParaGuardar,
        $aduanaNombre,
        $producto['sku'],
        $producto['descripcion'],
        $producto['cantidad']
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
