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
    echo json_encode(['error' => 'No se pudo conectar a la base de datos.']);
    exit;
}

$tipoUsuarioActual = isset($_SESSION['TipoDeUsuario']) ? strtolower(trim((string) $_SESSION['TipoDeUsuario'])) : '';
$rolesPermitidos = ['administrador', 'supervisor', 'auditor'];
$puedeCambiarEstatus = $tipoUsuarioActual !== '' && in_array($tipoUsuarioActual, $rolesPermitidos, true);

if (!$puedeCambiarEstatus) {
    http_response_code(403);
    echo json_encode(['error' => 'Solo un administrador, supervisor o auditor puede cambiar el estatus.']);
    mysqli_close($conn);
    exit;
}

$estatusIdEntrada = isset($_POST['STATUSIDEditar']) ? (int) $_POST['STATUSIDEditar'] : 0;
$repartoId = isset($_POST['REPARTOIDEditarStatus']) ? (int) $_POST['REPARTOIDEditarStatus'] : 0;

if ($estatusIdEntrada <= 0 || $repartoId <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Los datos enviados no son válidos.']);
    mysqli_close($conn);
    exit;
}

$surtidores = isset($_POST['Surtidores']) ? trim((string) $_POST['Surtidores']) : '';
$repartidor = isset($_POST['USUARIOIDRepartidor']) ? (int) $_POST['USUARIOIDRepartidor'] : 0;
$motivoDelEstatus = isset($_POST['MotivoDelEstatus']) ? trim((string) $_POST['MotivoDelEstatus']) : '';
$fechaReparto = isset($_POST['FechaReparto']) ? trim((string) $_POST['FechaReparto']) : '';
$horaReparto = isset($_POST['HoraReparto']) ? trim((string) $_POST['HoraReparto']) : '';

$camposActualizar = ['STATUSID = ?'];
$tipos = 'i';
$valores = [$estatusIdEntrada];

if ($repartidor > 0) {
    $camposActualizar[] = 'USUARIOIDRepartidor = ?';
    $tipos .= 'i';
    $valores[] = $repartidor;
}

if ($motivoDelEstatus !== '') {
    $camposActualizar[] = 'MotivoDelEstatus = ?';
    $tipos .= 's';
    $valores[] = $motivoDelEstatus;
}

if ($fechaReparto !== '') {
    $camposActualizar[] = 'FechaReparto = ?';
    $tipos .= 's';
    $valores[] = $fechaReparto;
} else {
    $camposActualizar[] = 'FechaReparto = NULL';
}

if ($horaReparto !== '') {
    $camposActualizar[] = 'HoraReparto = ?';
    $tipos .= 's';
    $valores[] = $horaReparto;
} else {
    $camposActualizar[] = 'HoraReparto = NULL';
}

if ($surtidores !== '') {
    $camposActualizar[] = 'Surtidores = ?';
    $tipos .= 's';
    $valores[] = $surtidores;
}

$sql = 'UPDATE repartos SET ' . implode(', ', $camposActualizar) . ' WHERE REPARTOID = ?';
$tipos .= 'i';
$valores[] = $repartoId;

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'No se pudo preparar la actualización de estatus.']);
    mysqli_close($conn);
    exit;
}

mysqli_stmt_bind_param($stmt, $tipos, ...$valores);

if (!mysqli_stmt_execute($stmt)) {
    http_response_code(500);
    echo json_encode(['error' => 'No se pudo actualizar el estatus del reparto.']);
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    exit;
}

mysqli_stmt_close($stmt);

echo json_encode([
    'REPARTOID' => $repartoId,
    'STATUSID' => $estatusIdEntrada,
]);

mysqli_close($conn);
