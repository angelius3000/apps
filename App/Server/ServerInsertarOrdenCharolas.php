<?php
include("../../Connections/ConDB.php");
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!$conn) {
    http_response_code(500);
    echo json_encode(['error' => 'No se pudo conectar a la base de datos.']);
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

function asegurarColumnasCreacion($conn)
{
    $columnas = [
        'USUARIOIDCreador' => false,
        'FechaCreacion' => false,
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

    if (!$columnas['USUARIOIDCreador']) {
        if (mysqli_query($conn, 'ALTER TABLE ordenes_charolas ADD COLUMN `USUARIOIDCreador` INT NULL')) {
            $columnas['USUARIOIDCreador'] = true;
        }
    }
    if (!$columnas['FechaCreacion']) {
        if (mysqli_query($conn, 'ALTER TABLE ordenes_charolas ADD COLUMN `FechaCreacion` DATETIME NULL')) {
            $columnas['FechaCreacion'] = true;
        }
    }

    return $columnas;
}

function obtenerDetallesMateriaPrima($conn, $charolasId)
{
    $detalles = [];
    $sqlDetalles = "SELECT mp.SkuMP, mp.DescripcionMP, mp.TipoMP, cc.CANTIDAD
                    FROM cantidadcharolas cc
                    INNER JOIN materiaprimacharolas mp ON cc.MATERIAPRIMAID = mp.MATERIAPRIMAID
                    WHERE cc.CHAROLASID = " . intval($charolasId);
    $result = mysqli_query($conn, $sqlDetalles);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $detalles[] = $row;
        }
        mysqli_free_result($result);
    }
    return $detalles;
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
        $cantidadTotal = (float) $detalle['CANTIDAD'] * (float) $cantidadOrden;
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

$charolasId = isset($_POST['CHAROLASID']) ? intval($_POST['CHAROLASID']) : 0;
$cantidad = isset($_POST['Cantidad']) ? (float) $_POST['Cantidad'] : 0;

if ($charolasId <= 0 || $cantidad <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'La charola y la cantidad son obligatorias.']);
    exit;
}

$columnasDisponibles = obtenerColumnasMateriales($conn);
$columnasCreacion = asegurarColumnasCreacion($conn);
$usuarioCreadorId = isset($_SESSION['USUARIOID']) ? (int) $_SESSION['USUARIOID'] : 0;
$columnasMaterialesDisponibles = !in_array(false, $columnasDisponibles, true);
$detalles = obtenerDetallesMateriaPrima($conn, $charolasId);
$totales = calcularTotalesMateriales($detalles, $cantidad);

if ($columnasMaterialesDisponibles) {
    if ($columnasCreacion['USUARIOIDCreador'] && $columnasCreacion['FechaCreacion']) {
        $stmt = mysqli_prepare($conn, "INSERT INTO ordenes_charolas (CHAROLASID, Cantidad, STATUSID, Largueros, Tornilleria, JuntaZeta, Traves, USUARIOIDCreador, FechaCreacion) VALUES (?, ?, 1, ?, ?, ?, ?, ?, NOW())");
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO ordenes_charolas (CHAROLASID, Cantidad, STATUSID, Largueros, Tornilleria, JuntaZeta, Traves) VALUES (?, ?, 1, ?, ?, ?, ?)");
    }
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['error' => mysqli_error($conn)]);
        exit;
    }
    $totalLargueros = (int) $totales['Largueros'];
    $totalTornilleria = (int) $totales['Tornilleria'];
    $totalJuntaZeta = (int) $totales['JuntaZeta'];
    $totalTraves = (int) $totales['Traves'];
    if ($columnasCreacion['USUARIOIDCreador'] && $columnasCreacion['FechaCreacion']) {
        mysqli_stmt_bind_param($stmt, 'idiiiii', $charolasId, $cantidad, $totalLargueros, $totalTornilleria, $totalJuntaZeta, $totalTraves, $usuarioCreadorId);
    } else {
        mysqli_stmt_bind_param($stmt, 'idiiii', $charolasId, $cantidad, $totalLargueros, $totalTornilleria, $totalJuntaZeta, $totalTraves);
    }
    if (!mysqli_stmt_execute($stmt)) {
        http_response_code(500);
        echo json_encode(['error' => mysqli_stmt_error($stmt)]);
        mysqli_stmt_close($stmt);
        exit;
    }
    mysqli_stmt_close($stmt);
} else {
    if ($columnasCreacion['USUARIOIDCreador'] && $columnasCreacion['FechaCreacion']) {
        $stmt = mysqli_prepare($conn, "INSERT INTO ordenes_charolas (CHAROLASID, Cantidad, STATUSID, USUARIOIDCreador, FechaCreacion) VALUES (?, ?, 1, ?, NOW())");
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO ordenes_charolas (CHAROLASID, Cantidad, STATUSID) VALUES (?, ?, 1)");
    }
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['error' => mysqli_error($conn)]);
        exit;
    }
    if ($columnasCreacion['USUARIOIDCreador'] && $columnasCreacion['FechaCreacion']) {
        mysqli_stmt_bind_param($stmt, 'idi', $charolasId, $cantidad, $usuarioCreadorId);
    } else {
        mysqli_stmt_bind_param($stmt, 'id', $charolasId, $cantidad);
    }
    if (!mysqli_stmt_execute($stmt)) {
        http_response_code(500);
        echo json_encode(['error' => mysqli_stmt_error($stmt)]);
        mysqli_stmt_close($stmt);
        exit;
    }
    mysqli_stmt_close($stmt);
}

$msg = ['ORDENCHAROLAID' => mysqli_insert_id($conn)];
echo json_encode($msg);

mysqli_close($conn);
?>
