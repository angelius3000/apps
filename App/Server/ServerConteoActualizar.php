<?php

include("../../Connections/ConDB.php");
require_once __DIR__ . '/ConteoHelpers.php';

header('Content-Type: application/json');

function responderErrorConteo(string $mensaje, int $codigo = 400): void
{
    http_response_code($codigo);
    echo json_encode(['success' => false, 'message' => $mensaje]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderErrorConteo('Método no permitido.', 405);
}

if (!$conn) {
    responderErrorConteo('No se pudo conectar a la base de datos.', 500);
}

establecerZonaHorariaConteo();

$nombreBaseDatos = $dbname ?? '';
asegurarTablaConteo($conn, $nombreBaseDatos);

$tipo = isset($_POST['tipo']) ? strtolower(trim((string) $_POST['tipo'])) : '';
$accion = isset($_POST['accion']) ? strtolower(trim((string) $_POST['accion'])) : '';

$tiposPermitidos = ['hombre', 'mujer', 'pareja', 'familia', 'cuadrilla'];
$accionesPermitidas = ['sumar', 'restar'];

if (!in_array($tipo, $tiposPermitidos, true)) {
    responderErrorConteo('Tipo de conteo inválido.');
}

if (!in_array($accion, $accionesPermitidas, true)) {
    responderErrorConteo('Acción inválida.');
}

$horaActual = obtenerHoraActualConteo();
if ($horaActual < 8 || $horaActual > 18) {
    responderErrorConteo('El conteo solo está disponible de 08:00 a 19:00.');
}

$fechaActual = obtenerFechaActualConteo();
asegurarFilasConteo($conn, $fechaActual);

$horaInicio = sprintf('%02d:00:00', $horaActual);

$columna = ucfirst($tipo);
$operador = $accion === 'sumar' ? 1 : -1;

$stmtActualizar = mysqli_prepare(
    $conn,
    "UPDATE conteo_visitas SET $columna = GREATEST($columna + ?, 0) WHERE Fecha = ? AND HoraInicio = ?"
);

if (!$stmtActualizar) {
    responderErrorConteo('No se pudo actualizar el conteo.', 500);
}

mysqli_stmt_bind_param($stmtActualizar, 'iss', $operador, $fechaActual, $horaInicio);
mysqli_stmt_execute($stmtActualizar);
mysqli_stmt_close($stmtActualizar);

$stmtSelect = mysqli_prepare(
    $conn,
    'SELECT HoraInicio, Hombre, Mujer, Pareja, Familia, Cuadrilla FROM conteo_visitas WHERE Fecha = ? AND HoraInicio = ? LIMIT 1'
);

if (!$stmtSelect) {
    responderErrorConteo('No se pudo obtener el conteo actualizado.', 500);
}

mysqli_stmt_bind_param($stmtSelect, 'ss', $fechaActual, $horaInicio);
mysqli_stmt_execute($stmtSelect);
mysqli_stmt_bind_result($stmtSelect, $horaInicioDb, $hombre, $mujer, $pareja, $familia, $cuadrilla);

$registroEncontrado = mysqli_stmt_fetch($stmtSelect);
mysqli_stmt_close($stmtSelect);

if (!$registroEncontrado) {
    responderErrorConteo('No se encontró un registro para la hora actual.', 404);
}

$total = (int) $hombre + (int) $mujer + (int) $pareja + (int) $familia + (int) $cuadrilla;

$mensaje = $accion === 'sumar'
    ? 'Conteo actualizado correctamente.'
    : 'Conteo corregido correctamente.';

echo json_encode([
    'success' => true,
    'message' => $mensaje,
    'data' => [
        'horaInicio' => $horaInicioDb,
        'hombre' => (int) $hombre,
        'mujer' => (int) $mujer,
        'pareja' => (int) $pareja,
        'familia' => (int) $familia,
        'cuadrilla' => (int) $cuadrilla,
        'total' => $total,
    ],
]);
