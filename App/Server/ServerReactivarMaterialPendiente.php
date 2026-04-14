<?php

include("../../Connections/ConDB.php");

header('Content-Type: application/json');

if (!isset($_SESSION)) {
    session_start();
}

function responderError(string $mensaje, int $codigo = 400): void
{
    http_response_code($codigo);
    echo json_encode(['success' => false, 'message' => $mensaje]);
    exit;
}

function obtenerTipoUsuarioActual(mysqli $conn): string
{
    $tipoSesion = strtolower(trim((string) ($_SESSION['TipoDeUsuario'] ?? '')));
    if ($tipoSesion !== '') {
        return $tipoSesion;
    }

    $tipoUsuarioSesionId = (int) ($_SESSION['TIPOUSUARIO'] ?? 0);
    if ($tipoUsuarioSesionId <= 0) {
        return '';
    }

    $consulta = mysqli_prepare(
        $conn,
        'SELECT TipoDeUsuario FROM tipodeusuarios WHERE TIPODEUSUARIOID = ? LIMIT 1'
    );

    if (!$consulta) {
        return '';
    }

    mysqli_stmt_bind_param($consulta, 'i', $tipoUsuarioSesionId);
    mysqli_stmt_execute($consulta);
    mysqli_stmt_bind_result($consulta, $tipoUsuarioRecuperado);

    $tipo = '';
    if (mysqli_stmt_fetch($consulta)) {
        $tipo = strtolower(trim((string) $tipoUsuarioRecuperado));
    }

    mysqli_stmt_close($consulta);

    return $tipo;
}

function obtenerNombreBaseDatos(mysqli $conn, ?string $actual): string
{
    if (!empty($actual)) {
        return (string) $actual;
    }

    $resultado = mysqli_query($conn, 'SELECT DATABASE()');
    if ($resultado instanceof mysqli_result) {
        $fila = mysqli_fetch_row($resultado);
        mysqli_free_result($resultado);
        if ($fila && isset($fila[0])) {
            return (string) $fila[0];
        }
    }

    return '';
}

function asegurarColumnaActivo(mysqli $conn, string $baseDatos, string $tabla, string $columna, string $sqlAlter): void
{
    if ($baseDatos === '') {
        return;
    }

    $stmtColumna = mysqli_prepare(
        $conn,
        'SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ? LIMIT 1'
    );

    if (!$stmtColumna) {
        return;
    }

    mysqli_stmt_bind_param($stmtColumna, 'sss', $baseDatos, $tabla, $columna);
    mysqli_stmt_execute($stmtColumna);
    mysqli_stmt_store_result($stmtColumna);

    if (mysqli_stmt_num_rows($stmtColumna) === 0) {
        @mysqli_query($conn, $sqlAlter);
    }

    mysqli_stmt_close($stmtColumna);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderError('Método no permitido.', 405);
}

if (!$conn) {
    responderError('No se pudo conectar a la base de datos.', 500);
}

$usuarioSesion = trim((string) ($_SESSION['Username'] ?? ''));
if ($usuarioSesion === '') {
    responderError('La sesión expiró. Inicia sesión nuevamente.', 401);
}

$tipoUsuarioActual = obtenerTipoUsuarioActual($conn);
if ($tipoUsuarioActual !== 'soporte it') {
    responderError('No cuentas con permisos para reactivar registros.', 403);
}

$nombreBaseDatos = obtenerNombreBaseDatos($conn, $dbname ?? '');
asegurarColumnaActivo($conn, $nombreBaseDatos, 'facturamp', 'ActivoFMP', "ALTER TABLE facturamp ADD COLUMN ActivoFMP TINYINT(1) NOT NULL DEFAULT 1 AFTER AduanaFMP");
asegurarColumnaActivo($conn, $nombreBaseDatos, 'materialpendiente', 'ActivoMP', "ALTER TABLE materialpendiente ADD COLUMN ActivoMP TINYINT(1) NOT NULL DEFAULT 1 AFTER FechaMP");

$folio = isset($_POST['folio']) ? (int) $_POST['folio'] : 0;
if ($folio <= 0) {
    responderError('Folio inválido.', 400);
}

$stmtFactura = mysqli_prepare($conn, 'SELECT DocumentoFMP FROM facturamp WHERE FacturaMPID = ? LIMIT 1');
if (!$stmtFactura) {
    responderError('No se pudo consultar el folio solicitado.', 500);
}

mysqli_stmt_bind_param($stmtFactura, 'i', $folio);
mysqli_stmt_execute($stmtFactura);
mysqli_stmt_bind_result($stmtFactura, $documento);

if (!mysqli_stmt_fetch($stmtFactura)) {
    mysqli_stmt_close($stmtFactura);
    responderError('No se encontró el folio solicitado.', 404);
}

mysqli_stmt_close($stmtFactura);
$documento = (string) $documento;

mysqli_begin_transaction($conn);

$stmtReactivarFactura = mysqli_prepare($conn, 'UPDATE facturamp SET ActivoFMP = 1 WHERE FacturaMPID = ? LIMIT 1');
if (!$stmtReactivarFactura) {
    mysqli_rollback($conn);
    responderError('No se pudo reactivar el folio.', 500);
}

mysqli_stmt_bind_param($stmtReactivarFactura, 'i', $folio);
$okFactura = mysqli_stmt_execute($stmtReactivarFactura);
mysqli_stmt_close($stmtReactivarFactura);

if (!$okFactura) {
    mysqli_rollback($conn);
    responderError('No se pudo reactivar el folio.', 500);
}

$stmtReactivarPartidas = mysqli_prepare($conn, 'UPDATE materialpendiente SET ActivoMP = 1 WHERE DocumentoMP = ?');
if (!$stmtReactivarPartidas) {
    mysqli_rollback($conn);
    responderError('No se pudieron reactivar las partidas del documento.', 500);
}

mysqli_stmt_bind_param($stmtReactivarPartidas, 's', $documento);
$okPartidas = mysqli_stmt_execute($stmtReactivarPartidas);
$partidasAfectadas = mysqli_stmt_affected_rows($stmtReactivarPartidas);
mysqli_stmt_close($stmtReactivarPartidas);

if (!$okPartidas) {
    mysqli_rollback($conn);
    responderError('No se pudieron reactivar las partidas del documento.', 500);
}

mysqli_commit($conn);

echo json_encode([
    'success' => true,
    'folio' => $folio,
    'documento' => $documento,
    'partidasReactivadas' => max(0, (int) $partidasAfectadas),
]);
