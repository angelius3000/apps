<?php
include("../../Connections/ConDB.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

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

if ($ordenCharolaId <= 0 || $statusIdEntrada === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Los datos enviados no son válidos.']);
    mysqli_close($conn);
    exit;
}

$rolesPermitidos = ['administrador', 'supervisor', 'auditor'];
$tipoUsuarioActual = isset($_SESSION['TipoDeUsuario']) ? strtolower(trim((string) $_SESSION['TipoDeUsuario'])) : '';
$puedeAsignarVerificado = $tipoUsuarioActual !== '' && in_array($tipoUsuarioActual, $rolesPermitidos, true);
$puedeAsignarAuditado = $tipoUsuarioActual === 'auditor';

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
$stmtActualizar = mysqli_prepare($conn, 'UPDATE ordenes_charolas SET STATUSID = ? WHERE ORDENCHAROLAID = ?');

if (!$stmtActualizar) {
    http_response_code(500);
    echo json_encode(['error' => 'No se pudo preparar la actualización de estatus.']);
    mysqli_close($conn);
    exit;
}

mysqli_stmt_bind_param($stmtActualizar, 'ii', $statusId, $ordenCharolaId);

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
    'STATUSID' => $statusId
]);

mysqli_close($conn);
?>
