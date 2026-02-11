<?php

include("../../Connections/ConDB.php");
require_once __DIR__ . '/ConteoHelpers.php';

header('Content-Type: application/json');

function responderErrorConsultaConteo(string $mensaje, int $codigo = 400): void
{
    http_response_code($codigo);
    echo json_encode(['success' => false, 'message' => $mensaje]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    responderErrorConsultaConteo('Método no permitido.', 405);
}

if (!$conn) {
    responderErrorConsultaConteo('No se pudo conectar a la base de datos.', 500);
}

establecerZonaHorariaConteo();

$fechaConsulta = isset($_GET['fecha']) ? trim((string) $_GET['fecha']) : '';
if ($fechaConsulta === '') {
    responderErrorConsultaConteo('La fecha es obligatoria.');
}

$formatoFechaValido = DateTime::createFromFormat('Y-m-d', $fechaConsulta);
if (!$formatoFechaValido || $formatoFechaValido->format('Y-m-d') !== $fechaConsulta) {
    responderErrorConsultaConteo('La fecha proporcionada no es válida.');
}

$fechaActual = obtenerFechaActualConteo();
if ($fechaConsulta >= $fechaActual) {
    responderErrorConsultaConteo('Solo puedes consultar días anteriores al actual.');
}

$nombreBaseDatos = $dbname ?? '';
asegurarTablaConteo($conn, $nombreBaseDatos);

$rangos = obtenerRangosHorasConteo();
$registrosExistentes = obtenerConteoPorFecha($conn, $fechaConsulta);

$registrosIndexados = [];
foreach ($registrosExistentes as $registro) {
    $registrosIndexados[$registro['horaInicio']] = $registro;
}

$registrosRespuesta = [];
foreach ($rangos as $rango) {
    $registro = $registrosIndexados[$rango['horaInicio']] ?? [
        'hombre' => 0,
        'mujer' => 0,
        'pareja' => 0,
        'familia' => 0,
        'cuadrilla' => 0,
    ];

    $total = (int) $registro['hombre']
        + (int) $registro['mujer']
        + (int) $registro['pareja']
        + (int) $registro['familia']
        + (int) $registro['cuadrilla'];

    $registrosRespuesta[] = [
        'horaInicio' => $rango['horaInicio'],
        'horaFin' => $rango['horaFin'],
        'etiqueta' => obtenerEtiquetaHoraConteo($rango['horaInicio'], $rango['horaFin']),
        'hombre' => (int) $registro['hombre'],
        'mujer' => (int) $registro['mujer'],
        'pareja' => (int) $registro['pareja'],
        'familia' => (int) $registro['familia'],
        'cuadrilla' => (int) $registro['cuadrilla'],
        'total' => $total,
    ];
}

echo json_encode([
    'success' => true,
    'message' => 'Consulta realizada correctamente.',
    'data' => [
        'fecha' => $fechaConsulta,
        'registros' => $registrosRespuesta,
    ],
]);
