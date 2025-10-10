<?php
include("../../Connections/ConDB.php");

if (!$conn) {
    http_response_code(500);
    $mensajeError = 'No se pudo conectar a la base de datos.';
    if (!empty($connectionError)) {
        $mensajeError .= ' ' . $connectionError;
    }
    echo json_encode([
        'error' => $mensajeError
    ]);
    exit;
}

function normalizarTexto($texto)
{
    $texto = strtolower(trim($texto));
    $texto = strtr($texto, [
        'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u',
        'Á' => 'a', 'É' => 'e', 'Í' => 'i', 'Ó' => 'o', 'Ú' => 'u', 'Ü' => 'u'
    ]);
    return $texto;
}

$query = "SELECT oc.ORDENCHAROLAID, oc.CHAROLASID, oc.Cantidad, oc.STATUSID, s.Status, c.SkuCharolas, c.DescripcionCharolas
          FROM ordenes_charolas oc
          JOIN charolas c ON c.CHAROLASID = oc.CHAROLASID
          JOIN status s ON s.STATUSID = oc.STATUSID
          ORDER BY oc.ORDENCHAROLAID DESC";
$result = mysqli_query($conn, $query) or die(mysqli_error($conn));
$ordenes = array();
while ($row = mysqli_fetch_assoc($result)) {
    $detalles = array();
    $sqlDetalles = "SELECT mp.SkuMP, mp.DescripcionMP, mp.TipoMP, cc.CANTIDAD
                    FROM cantidadcharolas cc
                    INNER JOIN materiaprimacharolas mp ON cc.MATERIAPRIMAID = mp.MATERIAPRIMAID
                    WHERE cc.CHAROLASID = " . $row['CHAROLASID'];
    $resultDetalles = mysqli_query($conn, $sqlDetalles) or die(mysqli_error($conn));
    $largueros = 0;
    $tornilleria = 0;
    $juntaZeta = 0;
    $traves = 0;
    while ($det = mysqli_fetch_assoc($resultDetalles)) {
        $cantidadTotal = intval($det['CANTIDAD']) * intval($row['Cantidad']);
        $detalles[] = array(
            'SkuMP' => $det['SkuMP'],
            'DescripcionMP' => $det['DescripcionMP'],
            'TipoMP' => $det['TipoMP'],
            'Cantidad' => $cantidadTotal
        );
        $tipoNormalizado = normalizarTexto($det['TipoMP']);
        if (strpos($tipoNormalizado, 'larguero') !== false) {
            $largueros += $cantidadTotal;
        } elseif (strpos($tipoNormalizado, 'tornill') !== false) {
            $tornilleria += $cantidadTotal;
        } elseif (strpos($tipoNormalizado, 'junta') !== false && (strpos($tipoNormalizado, 'z') !== false || strpos($tipoNormalizado, 'eta') !== false)) {
            $juntaZeta += $cantidadTotal;
        } elseif (strpos($tipoNormalizado, 'trave') !== false) {
            $traves += $cantidadTotal;
        }
    }
    $row['Detalles'] = $detalles;
    $row['Largueros'] = $largueros;
    $row['Tornilleria'] = $tornilleria;
    $row['JuntaZeta'] = $juntaZeta;
    $row['Traves'] = $traves;
    $ordenes[] = $row;
}
echo json_encode($ordenes);

mysqli_close($conn);
?>
