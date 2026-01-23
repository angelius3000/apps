<?php

include("../../Connections/ConDB.php");
require_once __DIR__ . '/ConteoHelpers.php';

if (!isset($_SESSION)) {
    session_start();
}

$perfilesPermitidos = [1, 5, 8];
$tipoUsuarioId = isset($_SESSION['TIPOUSUARIO']) ? (int) $_SESSION['TIPOUSUARIO'] : 0;

if (!in_array($tipoUsuarioId, $perfilesPermitidos, true)) {
    header('HTTP/1.1 403 Forbidden');
    echo 'No tienes permisos para exportar este reporte.';
    exit;
}

if (!$conn) {
    header('HTTP/1.1 500 Internal Server Error');
    echo 'No se pudo conectar a la base de datos.';
    exit;
}

establecerZonaHorariaConteo();

$fechaSolicitada = isset($_GET['fecha']) ? trim((string) $_GET['fecha']) : '';
if ($fechaSolicitada === '') {
    $fechaSolicitada = obtenerFechaActualConteo();
}

$fechaValida = DateTime::createFromFormat('Y-m-d', $fechaSolicitada);
if (!$fechaValida || $fechaValida->format('Y-m-d') !== $fechaSolicitada) {
    header('HTTP/1.1 400 Bad Request');
    echo 'Fecha inválida para exportar.';
    exit;
}

$nombreBaseDatos = $dbname ?? '';
asegurarTablaConteo($conn, $nombreBaseDatos);
asegurarFilasConteo($conn, $fechaSolicitada);

$registros = obtenerConteoPorFecha($conn, $fechaSolicitada);
$rangos = obtenerRangosHorasConteo();
$registrosIndexados = [];
foreach ($registros as $registro) {
    $registrosIndexados[$registro['horaInicio']] = $registro;
}

$nombreArchivo = 'conteo_' . date('Ymd_His') . '.xls';

header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
header('Content-Disposition: attachment; filename=' . $nombreArchivo);

$escapeXml = static function (string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
};

echo '<?xml version="1.0" encoding="UTF-8"?>';
echo '<?mso-application progid="Excel.Sheet"?>';
echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:o="urn:schemas-microsoft-com:office:office" '
    . 'xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">';
echo '<Worksheet ss:Name="Conteo">';
echo '<Table>';

echo '<Row>';
echo '<Cell><Data ss:Type="String">Fecha</Data></Cell>';
echo '<Cell><Data ss:Type="String">Hora</Data></Cell>';
echo '<Cell><Data ss:Type="String">Hombre</Data></Cell>';
echo '<Cell><Data ss:Type="String">Mujer</Data></Cell>';
echo '<Cell><Data ss:Type="String">Pareja</Data></Cell>';
echo '<Cell><Data ss:Type="String">Familia</Data></Cell>';
echo '<Cell><Data ss:Type="String">Cuadrilla</Data></Cell>';
echo '<Cell><Data ss:Type="String">Total</Data></Cell>';
echo '</Row>';

foreach ($rangos as $rango) {
    $registro = $registrosIndexados[$rango['horaInicio']] ?? [
        'hombre' => 0,
        'mujer' => 0,
        'pareja' => 0,
        'familia' => 0,
        'cuadrilla' => 0,
    ];

    $total = $registro['hombre'] + $registro['mujer'] + $registro['pareja'] + $registro['familia'] + $registro['cuadrilla'];

    echo '<Row>';
    echo '<Cell><Data ss:Type="String">' . $escapeXml($fechaSolicitada) . '</Data></Cell>';
    echo '<Cell><Data ss:Type="String">' . $escapeXml($rango['etiqueta']) . '</Data></Cell>';
    echo '<Cell><Data ss:Type="Number">' . (int) $registro['hombre'] . '</Data></Cell>';
    echo '<Cell><Data ss:Type="Number">' . (int) $registro['mujer'] . '</Data></Cell>';
    echo '<Cell><Data ss:Type="Number">' . (int) $registro['pareja'] . '</Data></Cell>';
    echo '<Cell><Data ss:Type="Number">' . (int) $registro['familia'] . '</Data></Cell>';
    echo '<Cell><Data ss:Type="Number">' . (int) $registro['cuadrilla'] . '</Data></Cell>';
    echo '<Cell><Data ss:Type="Number">' . (int) $total . '</Data></Cell>';
    echo '</Row>';
}

echo '</Table>';
echo '</Worksheet>';
echo '</Workbook>';
