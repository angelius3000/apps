<?php
if (!isset($_SESSION)) {
    session_start();
}

$tiposPermitidos = [1, 5, 9];
if (!in_array((int)($_SESSION['TIPOUSUARIO'] ?? 0), $tiposPermitidos, true)) {
    http_response_code(403);
    exit('No tienes permisos para descargar este reporte.');
}

include('../../Connections/ConDB.php');

if (!$conn) {
    http_response_code(500);
    exit('No se pudo conectar a la base de datos.');
}

@mysqli_query($conn, 'CREATE TABLE IF NOT EXISTS incidencias (IncidenciaID INT NOT NULL AUTO_INCREMENT, Fecha TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, Folio VARCHAR(100) NOT NULL, Cantidad DECIMAL(12,2) NOT NULL, SKU VARCHAR(100) NOT NULL, Descripcion VARCHAR(255) NOT NULL, Marca VARCHAR(255) DEFAULT NULL, PrecioUnitario DECIMAL(12,2) NOT NULL, Responsable VARCHAR(255) NOT NULL, Total DECIMAL(14,2) NOT NULL, CreadoPor VARCHAR(255) NOT NULL, Comentarios TEXT DEFAULT NULL, PRIMARY KEY (IncidenciaID), INDEX idx_incidencias_folio (Folio), INDEX idx_incidencias_sku (SKU)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
@mysqli_query($conn, 'ALTER TABLE incidencias CHANGE COLUMN Vendedor Responsable VARCHAR(255) NOT NULL');

$fechaInicio = trim((string)($_GET['fecha_inicio'] ?? ''));
$fechaFin = trim((string)($_GET['fecha_fin'] ?? ''));
$folio = trim((string)($_GET['folio'] ?? ''));
$sku = trim((string)($_GET['sku'] ?? ''));
$responsable = trim((string)($_GET['responsable'] ?? ''));
$creadoPor = trim((string)($_GET['creado_por'] ?? ''));

$condiciones = [];
$tipos = '';
$valores = [];
if ($fechaInicio !== '') { $condiciones[] = 'Fecha >= ?'; $tipos .= 's'; $valores[] = $fechaInicio . ' 00:00:00'; }
if ($fechaFin !== '') { $condiciones[] = 'Fecha < DATE_ADD(?, INTERVAL 1 DAY)'; $tipos .= 's'; $valores[] = $fechaFin; }
foreach (['Folio' => $folio, 'SKU' => $sku, 'Responsable' => $responsable, 'CreadoPor' => $creadoPor] as $columna => $valor) {
    if ($valor !== '') { $condiciones[] = $columna . ' LIKE ?'; $tipos .= 's'; $valores[] = '%' . $valor . '%'; }
}

$sql = 'SELECT Fecha, Folio, Cantidad, SKU, Descripcion, Marca, PrecioUnitario, Responsable, Total, CreadoPor, Comentarios FROM incidencias';
if ($condiciones) { $sql .= ' WHERE ' . implode(' AND ', $condiciones); }
$sql .= ' ORDER BY Fecha DESC, IncidenciaID DESC';
$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) { http_response_code(500); exit('No se pudo preparar el reporte.'); }
if ($valores) { mysqli_stmt_bind_param($stmt, $tipos, ...$valores); }
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);

header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
header('Content-Disposition: attachment; filename=incidencias_' . date('Ymd_His') . '.xls');
$escapeXml = static fn($valor): string => htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
echo '<?xml version="1.0" encoding="UTF-8"?><?mso-application progid="Excel.Sheet"?>';
echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"><Worksheet ss:Name="Incidencias"><Table><Row>';
foreach (['Fecha', 'Folio', 'Cantidad', 'SKU', 'Descripción', 'Marca', 'Precio Unitario', 'Responsable', 'Total', 'Creado por:', 'Comentarios'] as $encabezado) { echo '<Cell><Data ss:Type="String">' . $escapeXml($encabezado) . '</Data></Cell>'; }
echo '</Row>';
if (!$resultado || mysqli_num_rows($resultado) === 0) {
    echo '<Row><Cell ss:MergeAcross="10"><Data ss:Type="String">No hay incidencias que coincidan con los filtros seleccionados.</Data></Cell></Row>';
} else {
    while ($fila = mysqli_fetch_assoc($resultado)) {
        echo '<Row>';
        foreach (['Fecha', 'Folio', 'Cantidad', 'SKU', 'Descripcion', 'Marca', 'PrecioUnitario', 'Responsable', 'Total', 'CreadoPor', 'Comentarios'] as $columna) {
            $valor = $fila[$columna] ?? '';
            if ($columna === 'Fecha' && $valor !== '') { $valor = date('d/m/Y H:i', strtotime((string)$valor)); }
            $tipo = in_array($columna, ['Cantidad', 'PrecioUnitario', 'Total'], true) ? 'Number' : 'String';
            echo '<Cell><Data ss:Type="' . $tipo . '">' . $escapeXml($valor) . '</Data></Cell>';
        }
        echo '</Row>';
    }
}
echo '</Table></Worksheet></Workbook>';
mysqli_stmt_close($stmt);
