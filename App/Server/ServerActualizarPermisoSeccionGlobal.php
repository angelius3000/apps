<?php
include("../../Connections/ConDB.php");

if (!isset($_SESSION)) {
    session_start();
}

header('Content-Type: application/json');

if (!$conn) {
    echo json_encode([
        'success' => false,
        'message' => $connectionError ?? 'No se pudo establecer conexión con la base de datos.',
    ]);
    exit;
}

$seccionId = isset($_POST['SECCIONID']) ? (int)$_POST['SECCIONID'] : 0;
$puedeVer = isset($_POST['PuedeVer']) && (int)$_POST['PuedeVer'] === 1 ? 1 : 0;

if ($seccionId <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Parámetros incompletos.',
    ]);
    exit;
}

$usuariosActivos = [];
$resultUsuarios = mysqli_query($conn, "SELECT USUARIOID FROM usuarios");
if ($resultUsuarios) {
    while ($usuario = mysqli_fetch_assoc($resultUsuarios)) {
        $usuariosActivos[] = (int)$usuario['USUARIOID'];
    }
    mysqli_free_result($resultUsuarios);
}

if (empty($usuariosActivos)) {
    echo json_encode([
        'success' => true,
    ]);
    exit;
}

$stmt = mysqli_prepare(
    $conn,
    'INSERT INTO usuario_secciones (USUARIOID, SECCIONID, PuedeVer)
     VALUES (?, ?, ?)
     ON DUPLICATE KEY UPDATE PuedeVer = VALUES(PuedeVer)'
);

if (!$stmt) {
    echo json_encode([
        'success' => false,
        'message' => 'No se pudo preparar la consulta.',
    ]);
    exit;
}

$todoCorrecto = true;
foreach ($usuariosActivos as $usuarioId) {
    mysqli_stmt_bind_param($stmt, 'iii', $usuarioId, $seccionId, $puedeVer);
    if (!mysqli_stmt_execute($stmt)) {
        $todoCorrecto = false;
        break;
    }
}
mysqli_stmt_close($stmt);

if (!$todoCorrecto) {
    echo json_encode([
        'success' => false,
        'message' => 'No fue posible actualizar permisos globales.',
    ]);
    exit;
}

if (isset($_SESSION['USUARIOID']) && in_array((int)$_SESSION['USUARIOID'], $usuariosActivos, true)) {
    $stmtSlug = mysqli_prepare($conn, 'SELECT Slug FROM secciones WHERE SECCIONID = ? LIMIT 1');
    if ($stmtSlug) {
        mysqli_stmt_bind_param($stmtSlug, 'i', $seccionId);
        mysqli_stmt_execute($stmtSlug);
        mysqli_stmt_bind_result($stmtSlug, $slug);
        if (mysqli_stmt_fetch($stmtSlug)) {
            $slugNormalizado = strtolower((string)$slug);
            $_SESSION['PermisosSecciones'][$slugNormalizado] = $puedeVer;
        }
        mysqli_stmt_close($stmtSlug);
    }
}

echo json_encode([
    'success' => true,
]);
