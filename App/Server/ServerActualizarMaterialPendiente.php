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

function asegurarTablaMaterialPendiente(mysqli $conn, string $baseDatos): bool
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
        ActivoMP TINYINT(1) NOT NULL DEFAULT 1,
        PRIMARY KEY (MaterialPendienteID),
        INDEX idx_materialpendiente_documento (DocumentoMP),
        INDEX idx_materialpendiente_sku (SkuMP)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    $resultado = @mysqli_query($conn, $sqlCrearTabla) === true;

    if ($baseDatos === '') {
        return $resultado;
    }

    $stmtColumna = mysqli_prepare(
        $conn,
        'SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ? LIMIT 1'
    );

    if (!$stmtColumna) {
        return $resultado;
    }

    $tabla = 'materialpendiente';
    $columna = 'ActivoMP';
    mysqli_stmt_bind_param($stmtColumna, 'sss', $baseDatos, $tabla, $columna);
    mysqli_stmt_execute($stmtColumna);
    mysqli_stmt_store_result($stmtColumna);

    if (mysqli_stmt_num_rows($stmtColumna) === 0) {
        @mysqli_query($conn, "ALTER TABLE materialpendiente ADD COLUMN ActivoMP TINYINT(1) NOT NULL DEFAULT 1 AFTER FechaMP");
    }

    mysqli_stmt_close($stmtColumna);

    return $resultado;
}

function asegurarTablaFacturaMP(mysqli $conn, string $baseDatos): bool
{
    $sqlCrearTablaFactura = "CREATE TABLE IF NOT EXISTS facturamp (
        FacturaMPID INT NOT NULL AUTO_INCREMENT,
        FechaFMP TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        DocumentoFMP VARCHAR(100) NOT NULL,
        RazonSocialFMP VARCHAR(255) NOT NULL,
        VendedorFMP VARCHAR(255) DEFAULT NULL,
        SurtidorFMP VARCHAR(255) DEFAULT NULL,
        ClienteFMP VARCHAR(255) NOT NULL,
        AduanaFMP VARCHAR(255) DEFAULT NULL,
        ActivoFMP TINYINT(1) NOT NULL DEFAULT 1,
        PRIMARY KEY (FacturaMPID),
        INDEX idx_facturamp_documento (DocumentoFMP)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    $resultado = @mysqli_query($conn, $sqlCrearTablaFactura) === true;

    if ($baseDatos === '') {
        return $resultado;
    }

    $stmtColumna = mysqli_prepare(
        $conn,
        'SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ? LIMIT 1'
    );

    if (!$stmtColumna) {
        return $resultado;
    }

    $tabla = 'facturamp';
    $columna = 'ActivoFMP';
    mysqli_stmt_bind_param($stmtColumna, 'sss', $baseDatos, $tabla, $columna);
    mysqli_stmt_execute($stmtColumna);
    mysqli_stmt_store_result($stmtColumna);

    if (mysqli_stmt_num_rows($stmtColumna) === 0) {
        @mysqli_query($conn, "ALTER TABLE facturamp ADD COLUMN ActivoFMP TINYINT(1) NOT NULL DEFAULT 1 AFTER AduanaFMP");
    }

    mysqli_stmt_close($stmtColumna);

    return $resultado;
}

$nombreBaseDatos = $dbname ?? '';

if (!asegurarTablaMaterialPendiente($conn, $nombreBaseDatos)) {
    responderError('No se pudo preparar la tabla de material pendiente. Intenta nuevamente.');
}

if (!asegurarTablaFacturaMP($conn, $nombreBaseDatos)) {
    responderError('No se pudo preparar la tabla de facturas de material pendiente. Intenta nuevamente.');
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

$folio = isset($_POST['FolioPendiente']) ? (int) $_POST['FolioPendiente'] : 0;

if ($folio <= 0) {
    responderError('No se identificó el folio a editar.', 400);
}

$stmtFacturaActual = mysqli_prepare(
    $conn,
    'SELECT DocumentoFMP FROM facturamp WHERE FacturaMPID = ? AND ActivoFMP = 1 LIMIT 1'
);

if (!$stmtFacturaActual) {
    responderError('No se pudo obtener el folio solicitado.');
}

mysqli_stmt_bind_param($stmtFacturaActual, 'i', $folio);
mysqli_stmt_execute($stmtFacturaActual);
mysqli_stmt_bind_result($stmtFacturaActual, $documentoAnterior);

if (!mysqli_stmt_fetch($stmtFacturaActual)) {
    mysqli_stmt_close($stmtFacturaActual);
    responderError('No se encontró el folio solicitado.', 404);
}

mysqli_stmt_close($stmtFacturaActual);
$documentoAnterior = (string) $documentoAnterior;

$numeroFactura = normalizarTexto($_POST['NumeroFacturaPendiente'] ?? '');
$usarOtraRazonSocial = isset($_POST['OtraRazonSocialPendiente']) && $_POST['OtraRazonSocialPendiente'] === '1';
$clienteId = $usarOtraRazonSocial ? 0 : (isset($_POST['RazonSocialPendiente']) ? (int) $_POST['RazonSocialPendiente'] : 0);
$numeroClienteManual = normalizarTexto($_POST['NumeroClientePendienteOtro'] ?? '');
$razonSocialManual = normalizarTexto($_POST['RazonSocialPendienteOtra'] ?? '');
$usarOtroVendedor = isset($_POST['OtroVendedorPendiente']) && $_POST['OtroVendedorPendiente'] === '1';
$vendedorId = isset($_POST['VendedorPendiente']) ? (int) $_POST['VendedorPendiente'] : 0;
$vendedorOtro = normalizarTexto($_POST['VendedorPendienteOtro'] ?? '');
$aduanaId = isset($_POST['AduanaPendiente']) ? (int) $_POST['AduanaPendiente'] : 0;
$aduanaOtro = normalizarTexto($_POST['AduanaPendienteOtro'] ?? '');
$surtidorValor = normalizarTexto($_POST['SurtidorPendiente'] ?? '');
$nombreCliente = normalizarTexto($_POST['NombreClientePendiente'] ?? '');
$productos = $_POST['productos'] ?? [];

if (!is_array($productos)) {
    responderError('Los productos enviados no tienen el formato esperado.', 400);
}

if ($numeroFactura === '' || empty($productos)) {
    responderError('Captura el número de documento y al menos una partida pendiente.', 400);
}

if ($usarOtraRazonSocial) {
    if ($numeroClienteManual === '' || $razonSocialManual === '') {
        responderError('Captura el número de cliente y su razón social.', 400);
    }
} elseif ($clienteId <= 0) {
    responderError('Selecciona una razón social de la lista o captura una nueva.', 400);
}

if ($usarOtroVendedor) {
    $vendedorId = 0;
}

if ($usarOtroVendedor && $vendedorOtro === '') {
    responderError('Captura el nombre del vendedor.', 400);
}

if (!$usarOtroVendedor && $vendedorId <= 0) {
    responderError('Selecciona un vendedor o captura uno nuevo.', 400);
}

$clienteNombre = '';

if ($usarOtraRazonSocial) {
    $clienteNombre = $razonSocialManual;
    $razonSocial = $razonSocialManual . ' (Cliente #' . $numeroClienteManual . ')';
} else {
    $clienteNombre = obtenerTextoCatalogo($conn, 'clientes', 'CLIENTEID', 'NombreCliente', $clienteId);
    $razonSocial = $clienteNombre !== '' ? $clienteNombre : 'Cliente #' . $clienteId;
}

$vendedorNombre = '';
if ($usarOtroVendedor) {
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

$clienteParaGuardar = $nombreCliente !== ''
    ? $nombreCliente
    : ($usarOtraRazonSocial
        ? ($numeroClienteManual !== '' ? $numeroClienteManual : $razonSocialManual)
        : $razonSocial);

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

$stmtActualizar = mysqli_prepare(
    $conn,
    'UPDATE facturamp SET DocumentoFMP = ?, RazonSocialFMP = ?, VendedorFMP = ?, SurtidorFMP = ?, ClienteFMP = ?, AduanaFMP = ?, ActivoFMP = 1 WHERE FacturaMPID = ? LIMIT 1'
);

if (!$stmtActualizar) {
    mysqli_rollback($conn);
    responderError('No se pudo preparar la actualización del folio.');
}

mysqli_stmt_bind_param(
    $stmtActualizar,
    'ssssssi',
    $numeroFactura,
    $razonSocial,
    $vendedorNombre,
    $surtidorValor,
    $clienteParaGuardar,
    $aduanaNombre,
    $folio
);

if (!mysqli_stmt_execute($stmtActualizar)) {
    mysqli_stmt_close($stmtActualizar);
    mysqli_rollback($conn);
    responderError('No se pudo actualizar el folio.');
}

mysqli_stmt_close($stmtActualizar);

$stmtEliminar = mysqli_prepare($conn, 'DELETE FROM materialpendiente WHERE DocumentoMP = ?');

if (!$stmtEliminar) {
    mysqli_rollback($conn);
    responderError('No se pudo preparar la limpieza de partidas.');
}

mysqli_stmt_bind_param($stmtEliminar, 's', $documentoAnterior);

if (!mysqli_stmt_execute($stmtEliminar)) {
    mysqli_stmt_close($stmtEliminar);
    mysqli_rollback($conn);
    responderError('No se pudo actualizar la información capturada.');
}

mysqli_stmt_close($stmtEliminar);

$stmtInsertar = mysqli_prepare(
    $conn,
    'INSERT INTO materialpendiente (DocumentoMP, RazonSocialMP, VendedorMP, SurtidorMP, ClienteMP, AduanaMP, SkuMP, DescripcionMP, CantidadMP, ActivoMP) '
        . 'VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)'
);

if (!$stmtInsertar) {
    mysqli_rollback($conn);
    responderError('No se pudo preparar la inserción de material pendiente.');
}

$actualizados = 0;

foreach ($productosValidos as $producto) {
    mysqli_stmt_bind_param(
        $stmtInsertar,
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

    if (!mysqli_stmt_execute($stmtInsertar)) {
        mysqli_stmt_close($stmtInsertar);
        mysqli_rollback($conn);
        responderError('No se pudo actualizar la información capturada.');
    }

    $actualizados++;
}

mysqli_stmt_close($stmtInsertar);

if ($documentoAnterior !== $numeroFactura) {
    $stmtActualizarDocumento = mysqli_prepare(
        $conn,
        'UPDATE materialpendiente_entregas SET Documento = ? WHERE FolioID = ?'
    );

    if ($stmtActualizarDocumento) {
        mysqli_stmt_bind_param($stmtActualizarDocumento, 'si', $numeroFactura, $folio);
        @mysqli_stmt_execute($stmtActualizarDocumento);
        mysqli_stmt_close($stmtActualizarDocumento);
    }
}

mysqli_commit($conn);

echo json_encode([
    'success' => true,
    'updated' => $actualizados,
    'folio' => $folio
]);
