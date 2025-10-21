<?php
include("../../Connections/ConDB.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

function asegurarColumnasAuditado($conn)
{
    $columnas = [
        'Salida' => false,
        'Entrada' => false,
        'Almacen' => false,
    ];

    $definiciones = [
        'Salida' => 'VARCHAR(100) NULL',
        'Entrada' => 'VARCHAR(100) NULL',
        'Almacen' => 'VARCHAR(100) NULL',
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

    foreach ($columnas as $nombre => $presente) {
        if ($presente) {
            continue;
        }

        $definicion = isset($definiciones[$nombre]) ? $definiciones[$nombre] : 'VARCHAR(100) NULL';
        $sqlAlter = sprintf('ALTER TABLE ordenes_charolas ADD COLUMN `%s` %s', $nombre, $definicion);
        if (mysqli_query($conn, $sqlAlter)) {
            $columnas[$nombre] = true;
        }
    }

    return $columnas;
}

function asegurarColumnaFactura($conn)
{
    $columnaFactura = false;

    $consulta = mysqli_query($conn, "SHOW COLUMNS FROM ordenes_charolas LIKE 'Factura'");
    if ($consulta instanceof mysqli_result) {
        $columnaFactura = mysqli_num_rows($consulta) > 0;
        mysqli_free_result($consulta);
    }

    if (!$columnaFactura) {
        if (mysqli_query($conn, 'ALTER TABLE ordenes_charolas ADD COLUMN `Factura` VARCHAR(100) NULL')) {
            $columnaFactura = true;
        }
    }

    return $columnaFactura;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido.']);
    mysqli_close($conn);
    exit;
}

if (!$conn) {
    http_response_code(500);
    $mensajeError = 'No se pudo conectar a la base de datos.';
    if (!empty($connectionError)) {
        $mensajeError .= ' ' . $connectionError;
    }
    echo json_encode(['error' => $mensajeError]);
    exit;
}

$ordenCharolaId = isset($_POST['ORDENCHAROLAID']) ? (int) $_POST['ORDENCHAROLAID'] : 0;
$statusIdEntrada = isset($_POST['STATUSID']) ? trim((string) $_POST['STATUSID']) : '';
$salida = isset($_POST['SALIDA']) ? trim((string) $_POST['SALIDA']) : '';
$entrada = isset($_POST['ENTRADA']) ? trim((string) $_POST['ENTRADA']) : '';
$almacen = isset($_POST['ALMACEN']) ? trim((string) $_POST['ALMACEN']) : '';
$factura = isset($_POST['FACTURA']) ? trim((string) $_POST['FACTURA']) : '';

if ($ordenCharolaId <= 0 || $statusIdEntrada === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Los datos enviados no son válidos.']);
    mysqli_close($conn);
    exit;
}

$rolesPermitidos = ['administrador', 'supervisor', 'auditor'];
$tipoUsuarioActual = isset($_SESSION['TipoDeUsuario']) ? strtolower(trim((string) $_SESSION['TipoDeUsuario'])) : '';
$puedeCambiarEstatus = $tipoUsuarioActual !== '' && in_array($tipoUsuarioActual, $rolesPermitidos, true);
$puedeAsignarVerificado = $puedeCambiarEstatus;
$puedeAsignarAuditado = $tipoUsuarioActual === 'auditor';

if (!$puedeCambiarEstatus) {
    http_response_code(403);
    echo json_encode(['error' => 'Solo un administrador, supervisor o auditor puede cambiar el estatus.']);
    mysqli_close($conn);
    exit;
}

$statusVerificadoId = null;
$nombreStatusVerificado = 'Verificado';
$statusAuditadoId = null;
$nombreStatusAuditado = 'Auditado';
$stmtStatus = @mysqli_prepare($conn, 'SELECT STATUSID FROM status WHERE Status = ? LIMIT 1');
if ($stmtStatus) {
    mysqli_stmt_bind_param($stmtStatus, 's', $nombreStatusVerificado);
    mysqli_stmt_execute($stmtStatus);
    mysqli_stmt_bind_result($stmtStatus, $statusVerificadoIdTmp);
    if (mysqli_stmt_fetch($stmtStatus)) {
        $statusVerificadoId = (int) $statusVerificadoIdTmp;
    }
    mysqli_stmt_close($stmtStatus);
}

$stmtStatusAuditado = @mysqli_prepare($conn, 'SELECT STATUSID FROM status WHERE Status = ? LIMIT 1');
if ($stmtStatusAuditado) {
    mysqli_stmt_bind_param($stmtStatusAuditado, 's', $nombreStatusAuditado);
    mysqli_stmt_execute($stmtStatusAuditado);
    mysqli_stmt_bind_result($stmtStatusAuditado, $statusAuditadoIdTmp);
    if (mysqli_stmt_fetch($stmtStatusAuditado)) {
        $statusAuditadoId = (int) $statusAuditadoIdTmp;
    }
    mysqli_stmt_close($stmtStatusAuditado);
}

if ($statusVerificadoId !== null && (string) $statusVerificadoId === $statusIdEntrada && !$puedeAsignarVerificado) {
    http_response_code(403);
    echo json_encode(['error' => 'Solo un administrador, supervisor o auditor puede asignar el estatus Verificado.']);
    mysqli_close($conn);
    exit;
}

if ($statusAuditadoId !== null && (string) $statusAuditadoId === $statusIdEntrada && !$puedeAsignarAuditado) {
    http_response_code(403);
    echo json_encode(['error' => 'Solo un auditor puede asignar el estatus Auditado.']);
    mysqli_close($conn);
    exit;
}

$statusId = (int) $statusIdEntrada;
$requiereCamposAuditado = $statusAuditadoId !== null && (string) $statusAuditadoId === $statusIdEntrada;
$columnasAuditado = asegurarColumnasAuditado($conn);
$puedePersistirCamposAuditado = !in_array(false, $columnasAuditado, true);
$columnaFacturaDisponible = asegurarColumnaFactura($conn);
$statusEnProcesoId = '2';
$requiereFactura = $statusIdEntrada === $statusEnProcesoId;

if ($requiereCamposAuditado && !$puedePersistirCamposAuditado) {
    http_response_code(500);
    echo json_encode(['error' => 'No se pudieron habilitar los campos de auditoría en la base de datos.']);
    mysqli_close($conn);
    exit;
}

if ($requiereCamposAuditado) {
    if ($salida === '' || $entrada === '' || $almacen === '') {
        http_response_code(400);
        echo json_encode(['error' => 'Los campos Salida, Entrada y Almacén son obligatorios para el estatus Auditado.']);
        mysqli_close($conn);
        exit;
    }
}

if ($requiereFactura && $factura === '') {
    http_response_code(400);
    echo json_encode(['error' => 'El campo Factura es obligatorio para el estatus En proceso.']);
    mysqli_close($conn);
    exit;
}

if ($columnaFacturaDisponible && strlen($factura) > 100) {
    $factura = substr($factura, 0, 100);
}

$camposActualizar = ['STATUSID = ?'];
$tipos = 'i';
$valores = [$statusId];

if ($requiereCamposAuditado) {
    $camposActualizar[] = 'Salida = ?';
    $camposActualizar[] = 'Entrada = ?';
    $camposActualizar[] = 'Almacen = ?';
    $tipos .= 'sss';
    $valores[] = $salida;
    $valores[] = $entrada;
    $valores[] = $almacen;
} elseif ($puedePersistirCamposAuditado) {
    $camposActualizar[] = 'Salida = NULL';
    $camposActualizar[] = 'Entrada = NULL';
    $camposActualizar[] = 'Almacen = NULL';
}

if ($columnaFacturaDisponible) {
    if ($factura !== '') {
        $camposActualizar[] = 'Factura = ?';
        $tipos .= 's';
        $valores[] = $factura;
    } else {
        $camposActualizar[] = 'Factura = NULL';
    }
}

$sqlActualizar = 'UPDATE ordenes_charolas SET ' . implode(', ', $camposActualizar) . ' WHERE ORDENCHAROLAID = ?';
$tipos .= 'i';
$valores[] = $ordenCharolaId;

$stmtActualizar = mysqli_prepare($conn, $sqlActualizar);

if (!$stmtActualizar) {
    http_response_code(500);
    echo json_encode(['error' => 'No se pudo preparar la actualización de estatus.']);
    mysqli_close($conn);
    exit;
}

mysqli_stmt_bind_param($stmtActualizar, $tipos, ...$valores);

if (!mysqli_stmt_execute($stmtActualizar)) {
    http_response_code(500);
    echo json_encode(['error' => 'No se pudo actualizar el estatus de la requisición.']);
    mysqli_stmt_close($stmtActualizar);
    mysqli_close($conn);
    exit;
}

mysqli_stmt_close($stmtActualizar);

echo json_encode([
    'ORDENCHAROLAID' => $ordenCharolaId,
    'STATUSID' => $statusId,
    'Salida' => $requiereCamposAuditado && $puedePersistirCamposAuditado ? $salida : '',
    'Entrada' => $requiereCamposAuditado && $puedePersistirCamposAuditado ? $entrada : '',
    'Almacen' => $requiereCamposAuditado && $puedePersistirCamposAuditado ? $almacen : '',
    'Factura' => $columnaFacturaDisponible ? $factura : ''
]);

mysqli_close($conn);
?>
