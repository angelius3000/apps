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

function obtenerNombreBaseDatos(mysqli $conn, ?string $actual): string
{
    if (!empty($actual)) {
        return (string) $actual;
    }

    $resultado = mysqli_query($conn, 'SELECT DATABASE()');
    if ($resultado instanceof mysqli_result) {
        $fila = mysqli_fetch_row($resultado);
        mysqli_free_result($resultado);
        if ($fila && isset($fila[0])) {
            return (string) $fila[0];
        }
    }

    return '';
}

function asegurarColumnaActivo(mysqli $conn, string $baseDatos, string $tabla, string $columna, string $sqlAlter): void
{
    if ($baseDatos === '') {
        return;
    }

    $stmtColumna = mysqli_prepare(
        $conn,
        'SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ? LIMIT 1'
    );

    if (!$stmtColumna) {
        return;
    }

    mysqli_stmt_bind_param($stmtColumna, 'sss', $baseDatos, $tabla, $columna);
    mysqli_stmt_execute($stmtColumna);
    mysqli_stmt_store_result($stmtColumna);

    if (mysqli_stmt_num_rows($stmtColumna) === 0) {
        @mysqli_query($conn, $sqlAlter);
    }

    mysqli_stmt_close($stmtColumna);
}

$nombreBaseDatos = obtenerNombreBaseDatos($conn, $dbname ?? '');
asegurarColumnaActivo($conn, $nombreBaseDatos, 'facturamp', 'ActivoFMP', "ALTER TABLE facturamp ADD COLUMN ActivoFMP TINYINT(1) NOT NULL DEFAULT 1 AFTER AduanaFMP");
asegurarColumnaActivo($conn, $nombreBaseDatos, 'materialpendiente', 'ActivoMP', "ALTER TABLE materialpendiente ADD COLUMN ActivoMP TINYINT(1) NOT NULL DEFAULT 1 AFTER FechaMP");

$folio = isset($_POST['folio']) ? (int) $_POST['folio'] : 0;

if ($folio <= 0) {
    responderError('Folio inválido.', 400);
}

$stmtFactura = mysqli_prepare($conn, 'SELECT DocumentoFMP FROM facturamp WHERE FacturaMPID = ? LIMIT 1');

if (!$stmtFactura) {
    responderError('No se pudo obtener la información del folio.');
}

mysqli_stmt_bind_param($stmtFactura, 'i', $folio);
mysqli_stmt_execute($stmtFactura);
mysqli_stmt_bind_result($stmtFactura, $documento);

if (!mysqli_stmt_fetch($stmtFactura)) {
    mysqli_stmt_close($stmtFactura);
    responderError('No se encontró el folio solicitado.', 404);
}

mysqli_stmt_close($stmtFactura);
$documento = (string) $documento;

mysqli_begin_transaction($conn);

$stmtActualizarPartidas = mysqli_prepare($conn, 'UPDATE materialpendiente SET ActivoMP = 0 WHERE DocumentoMP = ?');
if (!$stmtActualizarPartidas) {
    mysqli_rollback($conn);
    responderError('No se pudo inhabilitar la información del folio.');
}

mysqli_stmt_bind_param($stmtActualizarPartidas, 's', $documento);

if (!mysqli_stmt_execute($stmtActualizarPartidas)) {
    mysqli_stmt_close($stmtActualizarPartidas);
    mysqli_rollback($conn);
    responderError('No se pudo inhabilitar la información del folio.');
}

mysqli_stmt_close($stmtActualizarPartidas);

$stmtActualizarFactura = mysqli_prepare($conn, 'UPDATE facturamp SET ActivoFMP = 0 WHERE FacturaMPID = ? LIMIT 1');
if (!$stmtActualizarFactura) {
    mysqli_rollback($conn);
    responderError('No se pudo inhabilitar el folio.');
}

mysqli_stmt_bind_param($stmtActualizarFactura, 'i', $folio);

if (!mysqli_stmt_execute($stmtActualizarFactura)) {
    mysqli_stmt_close($stmtActualizarFactura);
    mysqli_rollback($conn);
    responderError('No se pudo inhabilitar el folio.');
}

mysqli_stmt_close($stmtActualizarFactura);

mysqli_commit($conn);

echo json_encode([
    'success' => true,
    'folio' => $folio
]);
