<?php

include("../../Connections/ConDB.php");

header('Content-Type: application/json');

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

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    responderError('Método no permitido.', 405);
}

if (!$conn) {
    responderError('No se pudo conectar a la base de datos.', 500);
}

$tipoUsuarioActual = obtenerTipoUsuarioActual($conn);
if ($tipoUsuarioActual !== 'soporte it') {
    responderError('No cuentas con permisos para consultar registros eliminados.', 403);
}

$nombreBaseDatos = obtenerNombreBaseDatos($conn, $dbname ?? '');
asegurarColumnaActivo($conn, $nombreBaseDatos, 'facturamp', 'ActivoFMP', "ALTER TABLE facturamp ADD COLUMN ActivoFMP TINYINT(1) NOT NULL DEFAULT 1 AFTER AduanaFMP");
asegurarColumnaActivo($conn, $nombreBaseDatos, 'materialpendiente', 'ActivoMP', "ALTER TABLE materialpendiente ADD COLUMN ActivoMP TINYINT(1) NOT NULL DEFAULT 1 AFTER FechaMP");

$sql = "SELECT f.FacturaMPID, f.FechaFMP, f.DocumentoFMP, f.RazonSocialFMP, f.ClienteFMP,
        (SELECT COUNT(*) FROM materialpendiente mp WHERE mp.DocumentoMP = f.DocumentoFMP AND mp.ActivoMP = 0) AS PartidasInactivas
    FROM facturamp f
    WHERE f.ActivoFMP = 0
    ORDER BY f.FacturaMPID DESC";

$resultado = mysqli_query($conn, $sql);

if (!$resultado) {
    responderError('No se pudieron obtener los registros eliminados.', 500);
}

$registros = [];
while ($fila = mysqli_fetch_assoc($resultado)) {
    $registros[] = [
        'folio' => isset($fila['FacturaMPID']) ? (int) $fila['FacturaMPID'] : 0,
        'fecha' => (string) ($fila['FechaFMP'] ?? ''),
        'documento' => (string) ($fila['DocumentoFMP'] ?? ''),
        'razonSocial' => (string) ($fila['RazonSocialFMP'] ?? ''),
        'cliente' => (string) ($fila['ClienteFMP'] ?? ''),
        'partidasInactivas' => isset($fila['PartidasInactivas']) ? (int) $fila['PartidasInactivas'] : 0,
    ];
}

mysqli_free_result($resultado);

echo json_encode([
    'success' => true,
    'records' => $registros,
]);
