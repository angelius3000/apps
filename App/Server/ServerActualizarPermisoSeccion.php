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

$usuarioId = isset($_POST['USUARIOID']) ? (int)$_POST['USUARIOID'] : 0;
$seccionId = isset($_POST['SECCIONID']) ? (int)$_POST['SECCIONID'] : 0;
$puedeVer = isset($_POST['PuedeVer']) && (int)$_POST['PuedeVer'] === 1 ? 1 : 0;

if ($usuarioId <= 0 || $seccionId <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Parámetros incompletos.',
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

mysqli_stmt_bind_param($stmt, 'iii', $usuarioId, $seccionId, $puedeVer);
$ejecucionCorrecta = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

if (!$ejecucionCorrecta) {
    echo json_encode([
        'success' => false,
        'message' => 'No fue posible actualizar el permiso.',
    ]);
    exit;
}

if (isset($_SESSION['USUARIOID']) && (int)$_SESSION['USUARIOID'] === $usuarioId) {
    $stmtSlug = mysqli_prepare($conn, 'SELECT Slug FROM secciones WHERE SECCIONID = ? LIMIT 1');
    if ($stmtSlug) {
        mysqli_stmt_bind_param($stmtSlug, 'i', $seccionId);
        mysqli_stmt_execute($stmtSlug);
        mysqli_stmt_bind_result($stmtSlug, $slug);
        if (mysqli_stmt_fetch($stmtSlug)) {
            $_SESSION['PermisosSecciones'][$slug] = $puedeVer;
        }
        mysqli_stmt_close($stmtSlug);
    }
}

echo json_encode([
    'success' => true,
]);
