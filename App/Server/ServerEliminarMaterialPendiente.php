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

$stmtEliminarEntregas = mysqli_prepare($conn, 'DELETE FROM materialpendiente_entregas WHERE FolioID = ?');
if ($stmtEliminarEntregas) {
    mysqli_stmt_bind_param($stmtEliminarEntregas, 'i', $folio);
    @mysqli_stmt_execute($stmtEliminarEntregas);
    mysqli_stmt_close($stmtEliminarEntregas);
}

$stmtEliminarPartidas = mysqli_prepare($conn, 'DELETE FROM materialpendiente WHERE DocumentoMP = ?');
if (!$stmtEliminarPartidas) {
    mysqli_rollback($conn);
    responderError('No se pudo eliminar la información del folio.');
}

mysqli_stmt_bind_param($stmtEliminarPartidas, 's', $documento);

if (!mysqli_stmt_execute($stmtEliminarPartidas)) {
    mysqli_stmt_close($stmtEliminarPartidas);
    mysqli_rollback($conn);
    responderError('No se pudo eliminar la información del folio.');
}

mysqli_stmt_close($stmtEliminarPartidas);

$stmtEliminarFactura = mysqli_prepare($conn, 'DELETE FROM facturamp WHERE FacturaMPID = ? LIMIT 1');
if (!$stmtEliminarFactura) {
    mysqli_rollback($conn);
    responderError('No se pudo eliminar el folio.');
}

mysqli_stmt_bind_param($stmtEliminarFactura, 'i', $folio);

if (!mysqli_stmt_execute($stmtEliminarFactura)) {
    mysqli_stmt_close($stmtEliminarFactura);
    mysqli_rollback($conn);
    responderError('No se pudo eliminar el folio.');
}

mysqli_stmt_close($stmtEliminarFactura);

mysqli_commit($conn);

echo json_encode([
    'success' => true,
    'folio' => $folio
]);
