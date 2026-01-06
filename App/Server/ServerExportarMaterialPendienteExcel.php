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
echo "\xEF\xBB\xBF"; // BOM para UTF-8

echo '<table border="1">';
echo '<thead>';
echo '<tr>';
echo '<th>Folio</th>';
echo '<th>Fecha de registro</th>';
echo '<th>Número de documento</th>';
echo '<th>Razón social</th>';
echo '<th>Vendedor</th>';
echo '<th>Surtidor</th>';
echo '<th>Cliente</th>';
echo '<th>Aduana</th>';
echo '<th>SKU</th>';
echo '<th>Descripción</th>';
echo '<th>Cantidad pendiente</th>';
echo '</tr>';
echo '</thead>';
echo '<tbody>';

if (empty($rows)) {
    echo '<tr><td colspan="11">No hay partidas pendientes registradas.</td></tr>';
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

        $numeroDocumento = htmlspecialchars($fila['DocumentoMP'] ?? '', ENT_QUOTES, 'UTF-8');
        $razonSocial = htmlspecialchars($fila['RazonSocialMP'] ?? '', ENT_QUOTES, 'UTF-8');
        $vendedor = htmlspecialchars($fila['VendedorMP'] ?? '', ENT_QUOTES, 'UTF-8');
        $surtidor = htmlspecialchars($fila['SurtidorMP'] ?? '', ENT_QUOTES, 'UTF-8');
        $cliente = htmlspecialchars($fila['ClienteMP'] ?? '', ENT_QUOTES, 'UTF-8');
        $aduana = htmlspecialchars($fila['AduanaMP'] ?? '', ENT_QUOTES, 'UTF-8');
        $sku = htmlspecialchars($fila['SkuMP'] ?? '', ENT_QUOTES, 'UTF-8');
        $descripcion = htmlspecialchars($fila['DescripcionMP'] ?? '', ENT_QUOTES, 'UTF-8');
        $cantidad = isset($fila['CantidadMP']) ? (int) $fila['CantidadMP'] : 0;

        echo '<tr>';
        echo '<td>' . ($folio > 0 ? $folio : '-') . '</td>';
        echo '<td>' . ($fecha !== '' ? $fecha : '-') . '</td>';
        echo '<td>' . $numeroDocumento . '</td>';
        echo '<td>' . $razonSocial . '</td>';
        echo '<td>' . ($vendedor !== '' ? $vendedor : '-') . '</td>';
        echo '<td>' . ($surtidor !== '' ? $surtidor : '-') . '</td>';
        echo '<td>' . $cliente . '</td>';
        echo '<td>' . ($aduana !== '' ? $aduana : '-') . '</td>';
        echo '<td>' . $sku . '</td>';
        echo '<td>' . $descripcion . '</td>';
        echo '<td>' . $cantidad . '</td>';
        echo '</tr>';
    }
}

echo '</tbody>';
echo '</table>';

exit;

