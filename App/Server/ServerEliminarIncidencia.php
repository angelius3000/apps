<?php
if (!isset($_SESSION)) {
    session_start();
}

include('../../Connections/ConDB.php');
header('Content-Type: application/json; charset=utf-8');

function responderEliminacionIncidencia(array $respuesta, int $codigo = 200): void
{
    http_response_code($codigo);
    echo json_encode($respuesta);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$conn) {
    responderEliminacionIncidencia(['ok' => false, 'error' => 'Solicitud no válida.'], 400);
}

$id = filter_var($_POST['incidenciaId'] ?? null, FILTER_VALIDATE_INT);
if (!$id || $id < 1) {
    responderEliminacionIncidencia(['ok' => false, 'error' => 'La incidencia seleccionada no es válida.'], 422);
}

$stmt = mysqli_prepare($conn, 'DELETE FROM incidencias WHERE IncidenciaID = ? LIMIT 1');
if (!$stmt) {
    responderEliminacionIncidencia(['ok' => false, 'error' => 'No se pudo preparar la eliminación.'], 500);
}
mysqli_stmt_bind_param($stmt, 'i', $id);
if (!mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    responderEliminacionIncidencia(['ok' => false, 'error' => 'No se pudo eliminar la incidencia.'], 500);
}
$eliminadas = mysqli_stmt_affected_rows($stmt);
mysqli_stmt_close($stmt);
if ($eliminadas === 0) {
    responderEliminacionIncidencia(['ok' => false, 'error' => 'La incidencia ya no existe.'], 404);
}

responderEliminacionIncidencia(['ok' => true]);
