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
    if (!is_string($texto)) {
        if ($texto === null) {
            return '';
        }
        $texto = (string) $texto;
    }

    $texto = strtolower(trim($texto));
    if ($texto === '') {
        return '';
    }

    $texto = strtr($texto, [
        'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u',
        'Á' => 'a', 'É' => 'e', 'Í' => 'i', 'Ó' => 'o', 'Ú' => 'u', 'Ü' => 'u'
    ]);

    return $texto;
}

function obtenerColumnasMateriales($conn)
{
    $columnas = [
        'Largueros' => false,
        'Tornilleria' => false,
        'JuntaZeta' => false,
        'Traves' => false
    ];

    $consulta = mysqli_query($conn, 'SHOW COLUMNS FROM ordenes_charolas');
    if ($consulta instanceof mysqli_result) {
        while ($columna = mysqli_fetch_assoc($consulta)) {
            $nombre = isset($columna['Field']) ? $columna['Field'] : null;
            if ($nombre !== null && array_key_exists($nombre, $columnas)) {
                $columnas[$nombre] = true;
            }
        }
        mysqli_free_result($consulta);
    }

    return $columnas;
}

function calcularTotalesMateriales($detalles, $cantidadOrden)
{
    $totales = [
        'Largueros' => 0,
        'Tornilleria' => 0,
        'JuntaZeta' => 0,
        'Traves' => 0,
    ];

    foreach ($detalles as $detalle) {
        $cantidadBase = (float) $detalle['CANTIDAD'];
        $cantidadTotal = $cantidadBase * (float) $cantidadOrden;
        $tipoNormalizado = normalizarTexto($detalle['TipoMP']);

        if (strpos($tipoNormalizado, 'larguero') !== false) {
            $totales['Largueros'] += (int) round($cantidadTotal);
        } elseif (strpos($tipoNormalizado, 'tornill') !== false || strpos($tipoNormalizado, 'tuer') !== false) {
            $totales['Tornilleria'] += (int) round($cantidadTotal);
        } elseif (strpos($tipoNormalizado, 'junta') !== false && (strpos($tipoNormalizado, 'z') !== false || strpos($tipoNormalizado, 'eta') !== false)) {
            $totales['JuntaZeta'] += (int) round($cantidadTotal);
        } elseif (strpos($tipoNormalizado, 'trave') !== false || strpos($tipoNormalizado, 'trabe') !== false) {
            $totales['Traves'] += (int) round($cantidadTotal);
        }
    }

    return $totales;
}

$columnasDisponibles = obtenerColumnasMateriales($conn);
$columnasMaterialesCompletas = !in_array(false, $columnasDisponibles, true);
$columnasSeleccionadas = '';
$prefijosSeleccionados = [];
foreach ($columnasDisponibles as $columna => $disponible) {
    if ($disponible) {
        $prefijosSeleccionados[] = 'oc.' . $columna;
    }
}
if (!empty($prefijosSeleccionados)) {
    $columnasSeleccionadas = ', ' . implode(', ', $prefijosSeleccionados);
}

$query = "SELECT oc.ORDENCHAROLAID, oc.CHAROLASID, oc.Cantidad, oc.STATUSID, s.Status, c.SkuCharolas, c.DescripcionCharolas" . $columnasSeleccionadas . "
          FROM ordenes_charolas oc
          JOIN charolas c ON c.CHAROLASID = oc.CHAROLASID
          JOIN status s ON s.STATUSID = oc.STATUSID
          ORDER BY oc.ORDENCHAROLAID DESC";
$result = mysqli_query($conn, $query);
if (!$result) {
    http_response_code(500);
    echo json_encode(['error' => mysqli_error($conn)]);
    mysqli_close($conn);
    exit;
}

$ordenes = [];
while ($row = mysqli_fetch_assoc($result)) {
    $detalles = [];
    $sqlDetalles = "SELECT mp.SkuMP, mp.DescripcionMP, mp.TipoMP, cc.CANTIDAD
                    FROM cantidadcharolas cc
                    INNER JOIN materiaprimacharolas mp ON cc.MATERIAPRIMAID = mp.MATERIAPRIMAID
                    WHERE cc.CHAROLASID = " . intval($row['CHAROLASID']);
    $resultDetalles = mysqli_query($conn, $sqlDetalles);
    if (!$resultDetalles) {
        http_response_code(500);
        echo json_encode(['error' => mysqli_error($conn)]);
        mysqli_free_result($result);
        mysqli_close($conn);
        exit;
    }

    $detallesBrutos = [];
    while ($det = mysqli_fetch_assoc($resultDetalles)) {
        $detallesBrutos[] = $det;
        $detalles[] = [
            'SkuMP' => $det['SkuMP'],
            'DescripcionMP' => $det['DescripcionMP'],
            'TipoMP' => $det['TipoMP'],
            'Cantidad' => round((float) $det['CANTIDAD'] * (float) $row['Cantidad'], 4)
        ];
    }
    mysqli_free_result($resultDetalles);

    $totales = calcularTotalesMateriales($detallesBrutos, $row['Cantidad']);

    $valoresOriginales = [];
    foreach ($columnasDisponibles as $clave => $disponible) {
        if ($disponible && isset($row[$clave])) {
            $valoresOriginales[$clave] = (int) $row[$clave];
        }
    }

    if ($columnasMaterialesCompletas) {
        $necesitaActualizacion = false;
        foreach ($totales as $clave => $valor) {
            $valorActual = isset($valoresOriginales[$clave]) ? $valoresOriginales[$clave] : 0;
            if ($valorActual !== (int) $valor) {
                $necesitaActualizacion = true;
                break;
            }
        }
        if ($necesitaActualizacion) {
            $stmt = mysqli_prepare($conn, 'UPDATE ordenes_charolas SET Largueros = ?, Tornilleria = ?, JuntaZeta = ?, Traves = ? WHERE ORDENCHAROLAID = ?');
            if ($stmt) {
                $totalLargueros = (int) $totales['Largueros'];
                $totalTornilleria = (int) $totales['Tornilleria'];
                $totalJuntaZeta = (int) $totales['JuntaZeta'];
                $totalTraves = (int) $totales['Traves'];
                $ordenCharolaId = (int) $row['ORDENCHAROLAID'];
                mysqli_stmt_bind_param($stmt, 'iiiii', $totalLargueros, $totalTornilleria, $totalJuntaZeta, $totalTraves, $ordenCharolaId);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
        }
    }

    $row['_totalesMateriales'] = [
        'Largueros' => (int) $totales['Largueros'],
        'Tornilleria' => (int) $totales['Tornilleria'],
        'JuntaZeta' => (int) $totales['JuntaZeta'],
        'Traves' => (int) $totales['Traves'],
    ];

    foreach ($columnasDisponibles as $clave => $disponible) {
        if ($disponible && array_key_exists($clave, $row)) {
            unset($row[$clave]);
        }
    }

    $row['Detalles'] = $detalles;
    $ordenes[] = $row;
}
mysqli_free_result($result);

echo json_encode($ordenes);

mysqli_close($conn);
?>
