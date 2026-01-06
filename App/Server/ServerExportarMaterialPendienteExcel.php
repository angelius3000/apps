<?php

include("../../Connections/ConDB.php");

if (!$conn) {
    header('HTTP/1.1 500 Internal Server Error');
    echo 'No se pudo conectar a la base de datos.';
    exit;
}

function asegurarTablaMaterialPendiente(mysqli $conn): void
{
    $sqlCrearTabla = "CREATE TABLE IF NOT EXISTS materialpendiente (\n        MaterialPendienteID INT NOT NULL AUTO_INCREMENT,\n        DocumentoMP VARCHAR(100) NOT NULL,\n        RazonSocialMP VARCHAR(255) NOT NULL,\n        VendedorMP VARCHAR(255) DEFAULT NULL,\n        SurtidorMP VARCHAR(255) DEFAULT NULL,\n        ClienteMP VARCHAR(255) NOT NULL,\n        AduanaMP VARCHAR(255) DEFAULT NULL,\n        SkuMP VARCHAR(100) NOT NULL,\n        DescripcionMP VARCHAR(255) NOT NULL,\n        CantidadMP INT NOT NULL DEFAULT 0,\n        FechaMP TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,\n        PRIMARY KEY (MaterialPendienteID),\n        INDEX idx_materialpendiente_documento (DocumentoMP),\n        INDEX idx_materialpendiente_sku (SkuMP)\n    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    @mysqli_query($conn, $sqlCrearTabla);
}

function asegurarTablaFacturaMP(mysqli $conn): void
{
    $sqlCrearTablaFactura = "CREATE TABLE IF NOT EXISTS facturamp (\n        FacturaMPID INT NOT NULL AUTO_INCREMENT,\n        FechaFMP TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,\n        DocumentoFMP VARCHAR(100) NOT NULL,\n        RazonSocialFMP VARCHAR(255) NOT NULL,\n        VendedorFMP VARCHAR(255) DEFAULT NULL,\n        SurtidorFMP VARCHAR(255) DEFAULT NULL,\n        ClienteFMP VARCHAR(255) NOT NULL,\n        AduanaFMP VARCHAR(255) DEFAULT NULL,\n        PRIMARY KEY (FacturaMPID),\n        INDEX idx_facturamp_documento (DocumentoFMP)\n    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    @mysqli_query($conn, $sqlCrearTablaFactura);
}

asegurarTablaMaterialPendiente($conn);
asegurarTablaFacturaMP($conn);

$longitudSkuReferencia = 0;
$queryLongitudSku = "SELECT MAX(CHAR_LENGTH(Sku)) AS MaxSkuLength FROM productos";
$resultadoLongitud = mysqli_query($conn, $queryLongitudSku);

if ($resultadoLongitud instanceof mysqli_result) {
    $filaLongitud = mysqli_fetch_assoc($resultadoLongitud);
    if (isset($filaLongitud['MaxSkuLength'])) {
        $longitudSkuReferencia = (int) $filaLongitud['MaxSkuLength'];
    }
    mysqli_free_result($resultadoLongitud);
}

$query = "SELECT f.FacturaMPID, f.FechaFMP, mp.DocumentoMP, mp.RazonSocialMP, mp.VendedorMP, mp.SurtidorMP, mp.ClienteMP, mp.AduanaMP, mp.SkuMP, mp.DescripcionMP, mp.CantidadMP, mp.FechaMP "
    . "FROM materialpendiente mp "
    . "LEFT JOIN facturamp f ON f.DocumentoFMP = mp.DocumentoMP "
    . "ORDER BY f.FacturaMPID DESC, mp.MaterialPendienteID ASC";

$resultado = mysqli_query($conn, $query);

$rows = [];
if ($resultado instanceof mysqli_result) {
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $rows[] = $fila;
    }
    mysqli_free_result($resultado);
}

$nombreArchivo = 'material_pendiente_' . date('Ymd_His') . '.xls';

header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
header('Content-Disposition: attachment; filename=' . $nombreArchivo);

$escapeXml = static function (string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
};

echo '<?xml version="1.0" encoding="UTF-8"?>';
echo '<?mso-application progid="Excel.Sheet"?>';
echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:o="urn:schemas-microsoft-com:office:office" '
    . 'xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">';
echo '<Worksheet ss:Name="Pendiente">';
echo '<Table>';

echo '<Row>';
echo '<Cell><Data ss:Type="String">Folio</Data></Cell>';
echo '<Cell><Data ss:Type="String">Fecha de registro</Data></Cell>';
echo '<Cell><Data ss:Type="String">Número de documento</Data></Cell>';
echo '<Cell><Data ss:Type="String">Razón social</Data></Cell>';
echo '<Cell><Data ss:Type="String">Vendedor</Data></Cell>';
echo '<Cell><Data ss:Type="String">Surtidor</Data></Cell>';
echo '<Cell><Data ss:Type="String">Cliente</Data></Cell>';
echo '<Cell><Data ss:Type="String">Aduana</Data></Cell>';
echo '<Cell><Data ss:Type="String">SKU</Data></Cell>';
echo '<Cell><Data ss:Type="String">Descripción</Data></Cell>';
echo '<Cell><Data ss:Type="String">Cantidad pendiente</Data></Cell>';
echo '</Row>';

if (empty($rows)) {
    echo '<Row><Cell ss:MergeAcross="10"><Data ss:Type="String">No hay partidas pendientes registradas.</Data></Cell></Row>';
} else {
    foreach ($rows as $fila) {
        $folio = isset($fila['FacturaMPID']) ? (int) $fila['FacturaMPID'] : 0;
        $fechaBase = $fila['FechaFMP'] ?? $fila['FechaMP'] ?? '';
        $fecha = '';
        if (!empty($fechaBase)) {
            $marcaTemporal = strtotime((string) $fechaBase);
            if ($marcaTemporal !== false) {
                $fecha = date('d/m/Y H:i', $marcaTemporal);
            }
        }

        $numeroDocumento = $escapeXml($fila['DocumentoMP'] ?? '');
        $razonSocial = $escapeXml($fila['RazonSocialMP'] ?? '');
        $vendedor = $escapeXml($fila['VendedorMP'] ?? '');
        $surtidor = $escapeXml($fila['SurtidorMP'] ?? '');
        $cliente = $escapeXml($fila['ClienteMP'] ?? '');
        $aduana = $escapeXml($fila['AduanaMP'] ?? '');
        $skuNormalizado = $fila['SkuMP'] ?? '';
        if ($longitudSkuReferencia > 0) {
            $skuNormalizado = trim((string) $skuNormalizado);
            if ($skuNormalizado !== '' && ctype_digit($skuNormalizado) && strlen($skuNormalizado) < $longitudSkuReferencia) {
                $skuNormalizado = str_pad($skuNormalizado, $longitudSkuReferencia, '0', STR_PAD_LEFT);
            }
        }

        $sku = $escapeXml((string) $skuNormalizado);
        $descripcion = $escapeXml($fila['DescripcionMP'] ?? '');
        $cantidad = isset($fila['CantidadMP']) ? (int) $fila['CantidadMP'] : 0;

        echo '<Row>';
        echo '<Cell><Data ss:Type="String">' . ($folio > 0 ? $folio : '-') . '</Data></Cell>';
        echo '<Cell><Data ss:Type="String">' . ($fecha !== '' ? $fecha : '-') . '</Data></Cell>';
        echo '<Cell><Data ss:Type="String">' . $numeroDocumento . '</Data></Cell>';
        echo '<Cell><Data ss:Type="String">' . $razonSocial . '</Data></Cell>';
        echo '<Cell><Data ss:Type="String">' . ($vendedor !== '' ? $vendedor : '-') . '</Data></Cell>';
        echo '<Cell><Data ss:Type="String">' . ($surtidor !== '' ? $surtidor : '-') . '</Data></Cell>';
        echo '<Cell><Data ss:Type="String">' . $cliente . '</Data></Cell>';
        echo '<Cell><Data ss:Type="String">' . ($aduana !== '' ? $aduana : '-') . '</Data></Cell>';
        echo '<Cell><Data ss:Type="String">' . $sku . '</Data></Cell>';
        echo '<Cell><Data ss:Type="String">' . $descripcion . '</Data></Cell>';
        echo '<Cell><Data ss:Type="Number">' . $cantidad . '</Data></Cell>';
        echo '</Row>';
    }
}

echo '</Table>';
echo '</Worksheet>';
echo '</Workbook>';

exit;

