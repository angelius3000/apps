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

$inicioSolicitado = isset($_GET['inicio']) ? trim((string) $_GET['inicio']) : '';
$finSolicitado = isset($_GET['fin']) ? trim((string) $_GET['fin']) : '';
$agrupacion = isset($_GET['agrupacion']) ? trim((string) $_GET['agrupacion']) : 'hora';

if ($agrupacion !== 'hora' && $agrupacion !== 'dia') {
    $agrupacion = 'hora';
}

if ($inicioSolicitado === '' || $finSolicitado === '') {
    $fechaActual = obtenerFechaActualConteo();
    $inicioSolicitado = $fechaActual . 'T08:00';
    $finSolicitado = $fechaActual . 'T19:00';
}

$inicio = DateTime::createFromFormat('Y-m-d\TH:i', $inicioSolicitado);
$fin = DateTime::createFromFormat('Y-m-d\TH:i', $finSolicitado);

if (!$inicio || $inicio->format('Y-m-d\TH:i') !== $inicioSolicitado) {
    header('HTTP/1.1 400 Bad Request');
    echo 'Fecha y hora inicial inválida para exportar.';
    exit;
}

if (!$fin || $fin->format('Y-m-d\TH:i') !== $finSolicitado) {
    header('HTTP/1.1 400 Bad Request');
    echo 'Fecha y hora final inválida para exportar.';
    exit;
}

if ($inicio > $fin) {
    header('HTTP/1.1 400 Bad Request');
    echo 'La fecha y hora inicial debe ser menor o igual a la final.';
    exit;
}

$nombreBaseDatos = $dbname ?? '';
asegurarTablaConteo($conn, $nombreBaseDatos);

$inicioFecha = $inicio->format('Y-m-d');
$finFecha = $fin->format('Y-m-d');

$inicioCursor = clone $inicio;
$inicioCursor->setTime(0, 0, 0);
$finCursor = clone $fin;
$finCursor->setTime(0, 0, 0);

while ($inicioCursor <= $finCursor) {
    asegurarFilasConteo($conn, $inicioCursor->format('Y-m-d'));
    $inicioCursor->modify('+1 day');
}

$stmt = mysqli_prepare(
    $conn,
    'SELECT Fecha, HoraInicio, HoraFin, Hombre, Mujer, Pareja, Familia, Cuadrilla '
    . 'FROM conteo_visitas WHERE Fecha BETWEEN ? AND ? ORDER BY Fecha ASC, HoraInicio ASC'
);

if (!$stmt) {
    header('HTTP/1.1 500 Internal Server Error');
    echo 'No se pudo preparar la consulta de exportación.';
    exit;
}

mysqli_stmt_bind_param($stmt, 'ss', $inicioFecha, $finFecha);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $fechaDb, $horaInicioDb, $horaFinDb, $hombreDb, $mujerDb, $parejaDb, $familiaDb, $cuadrillaDb);

$filas = [];
while (mysqli_stmt_fetch($stmt)) {
    $inicioBloque = DateTime::createFromFormat('Y-m-d H:i:s', $fechaDb . ' ' . $horaInicioDb);
    $finBloque = DateTime::createFromFormat('Y-m-d H:i:s', $fechaDb . ' ' . $horaFinDb);

    if (!$inicioBloque || !$finBloque) {
        continue;
    }

    if ($finBloque <= $inicio || $inicioBloque >= $fin) {
        continue;
    }

    $hombre = (int) $hombreDb;
    $mujer = (int) $mujerDb;
    $pareja = (int) $parejaDb;
    $familia = (int) $familiaDb;
    $cuadrilla = (int) $cuadrillaDb;

    $filas[] = [
        'fecha' => $fechaDb,
        'horaInicio' => $horaInicioDb,
        'horaFin' => $horaFinDb,
        'hombre' => $hombre,
        'mujer' => $mujer,
        'pareja' => $pareja,
        'familia' => $familia,
        'cuadrilla' => $cuadrilla,
        'total' => $hombre + $mujer + $pareja + $familia + $cuadrilla,
    ];
}

mysqli_stmt_close($stmt);

$agrupados = [];
foreach ($filas as $fila) {
    $hora = substr($fila['horaInicio'], 0, 5);
    $clave = $agrupacion === 'dia' ? $fila['fecha'] : $fila['fecha'] . ' ' . $hora;

    if (!isset($agrupados[$clave])) {
        $agrupados[$clave] = [
            'fecha' => $fila['fecha'],
            'hora' => $agrupacion === 'dia' ? '' : $hora,
            'hombre' => 0,
            'mujer' => 0,
            'pareja' => 0,
            'familia' => 0,
            'cuadrilla' => 0,
            'total' => 0,
            'registros' => 0,
        ];
    }

    $agrupados[$clave]['hombre'] += $fila['hombre'];
    $agrupados[$clave]['mujer'] += $fila['mujer'];
    $agrupados[$clave]['pareja'] += $fila['pareja'];
    $agrupados[$clave]['familia'] += $fila['familia'];
    $agrupados[$clave]['cuadrilla'] += $fila['cuadrilla'];
    $agrupados[$clave]['total'] += $fila['total'];
    $agrupados[$clave]['registros']++;
}

ksort($agrupados);
$nombreArchivo = 'conteo_periodo_' . date('Ymd_His') . '.xls';

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
echo '<Cell><Data ss:Type="String">Inicio solicitado</Data></Cell>';
echo '<Cell><Data ss:Type="String">Fin solicitado</Data></Cell>';
echo '<Cell><Data ss:Type="String">Agrupación</Data></Cell>';
echo '</Row>';
echo '<Row>';
echo '<Cell><Data ss:Type="String">' . $escapeXml($inicio->format('Y-m-d H:i')) . '</Data></Cell>';
echo '<Cell><Data ss:Type="String">' . $escapeXml($fin->format('Y-m-d H:i')) . '</Data></Cell>';
echo '<Cell><Data ss:Type="String">' . $escapeXml($agrupacion === 'dia' ? 'Día' : 'Hora') . '</Data></Cell>';
echo '</Row>';
echo '<Row></Row>';

echo '<Row>';
echo '<Cell><Data ss:Type="String">Fecha</Data></Cell>';
echo '<Cell><Data ss:Type="String">Hora</Data></Cell>';
echo '<Cell><Data ss:Type="String">Hombre</Data></Cell>';
echo '<Cell><Data ss:Type="String">Mujer</Data></Cell>';
echo '<Cell><Data ss:Type="String">Pareja</Data></Cell>';
echo '<Cell><Data ss:Type="String">Familia</Data></Cell>';
echo '<Cell><Data ss:Type="String">Cuadrilla</Data></Cell>';
echo '<Cell><Data ss:Type="String">Total</Data></Cell>';
echo '<Cell><Data ss:Type="String">Promedio por registro</Data></Cell>';
echo '</Row>';

$totalGeneral = 0;
$registrosTotales = 0;

foreach ($agrupados as $grupo) {
    $promedioGrupo = $grupo['registros'] > 0 ? $grupo['total'] / $grupo['registros'] : 0;

    echo '<Row>';
    echo '<Cell><Data ss:Type="String">' . $escapeXml($grupo['fecha']) . '</Data></Cell>';
    echo '<Cell><Data ss:Type="String">' . $escapeXml($grupo['hora']) . '</Data></Cell>';
    echo '<Cell><Data ss:Type="Number">' . (int) $grupo['hombre'] . '</Data></Cell>';
    echo '<Cell><Data ss:Type="Number">' . (int) $grupo['mujer'] . '</Data></Cell>';
    echo '<Cell><Data ss:Type="Number">' . (int) $grupo['pareja'] . '</Data></Cell>';
    echo '<Cell><Data ss:Type="Number">' . (int) $grupo['familia'] . '</Data></Cell>';
    echo '<Cell><Data ss:Type="Number">' . (int) $grupo['cuadrilla'] . '</Data></Cell>';
    echo '<Cell><Data ss:Type="Number">' . (int) $grupo['total'] . '</Data></Cell>';
    echo '<Cell><Data ss:Type="Number">' . number_format($promedioGrupo, 2, '.', '') . '</Data></Cell>';
    echo '</Row>';

    $totalGeneral += $grupo['total'];
    $registrosTotales += $grupo['registros'];
}

echo '<Row></Row>';
echo '<Row>';
echo '<Cell><Data ss:Type="String">Total general</Data></Cell>';
echo '<Cell><Data ss:Type="String"></Data></Cell>';
echo '<Cell><Data ss:Type="Number"></Data></Cell>';
echo '<Cell><Data ss:Type="Number"></Data></Cell>';
echo '<Cell><Data ss:Type="Number"></Data></Cell>';
echo '<Cell><Data ss:Type="Number"></Data></Cell>';
echo '<Cell><Data ss:Type="Number"></Data></Cell>';
echo '<Cell><Data ss:Type="Number">' . (int) $totalGeneral . '</Data></Cell>';
echo '<Cell><Data ss:Type="Number">' . ($registrosTotales > 0 ? number_format($totalGeneral / $registrosTotales, 2, '.', '') : '0.00') . '</Data></Cell>';
echo '</Row>';

echo '</Table>';
echo '</Worksheet>';
echo '</Workbook>';
