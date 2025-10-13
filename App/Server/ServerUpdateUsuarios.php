<?php

include("../../Connections/ConDB.php");

if (!isset($_SESSION)) {
    session_start();
}

$TIPODEUSUARIOIDEditar = mysqli_real_escape_string($conn, $_POST['TIPODEUSUARIOIDEditar']);
$PrimerNombreEditar = mysqli_real_escape_string($conn, $_POST['PrimerNombreEditar']);
$SegundoNombreEditar = mysqli_real_escape_string($conn, $_POST['SegundoNombreEditar']);
$ApellidoPaternoEditar = mysqli_real_escape_string($conn, $_POST['ApellidoPaternoEditar']);
$ApellidoMaternoEditar = mysqli_real_escape_string($conn, $_POST['ApellidoMaternoEditar']);
$emailEditar = mysqli_real_escape_string($conn, $_POST['emailEditar']);
$TelefonoEditar = mysqli_real_escape_string($conn, $_POST['TelefonoEditar']);
$USUARIOIDEditar = mysqli_real_escape_string($conn, $_POST['USUARIOIDEditar']);
$CLIENTEIDEditar = isset($_POST['CLIENTEIDEditar']) && $_POST['CLIENTEIDEditar'] !== ''
    ? (int)$_POST['CLIENTEIDEditar']
    : null;

// Build the base query
$clienteSql = $CLIENTEIDEditar !== null ? "'" . $CLIENTEIDEditar . "'" : "NULL";

$sql = "UPDATE usuarios SET
    PrimerNombre = '$PrimerNombreEditar',
    SegundoNombre = '$SegundoNombreEditar',
    ApellidoPaterno = '$ApellidoPaternoEditar',
    ApellidoMaterno = '$ApellidoMaternoEditar',
    email = '$emailEditar',
    Telefono = '$TelefonoEditar',
    TIPODEUSUARIOID = '$TIPODEUSUARIOIDEditar',
    CLIENTEID = $clienteSql
    WHERE USUARIOID = '$USUARIOIDEditar'";

if (!mysqli_query($conn, $sql)) {
    die('Error: ' . mysqli_error($conn));
}

$msg = array('USUARIOID' => $USUARIOIDEditar);

if (!empty($_POST['secciones']) && is_array($_POST['secciones'])) {
    $seccionesSeleccionadas = array_map('intval', array_keys($_POST['secciones']));
} else {
    $seccionesSeleccionadas = [];
}

$resultadoSecciones = mysqli_query($conn, "SELECT SECCIONID FROM secciones ORDER BY Orden, Nombre");
if ($resultadoSecciones) {
    $stmtPermiso = mysqli_prepare(
        $conn,
        'INSERT INTO usuario_secciones (USUARIOID, SECCIONID, PuedeVer)
         VALUES (?, ?, ?)
         ON DUPLICATE KEY UPDATE PuedeVer = VALUES(PuedeVer)'
    );

    if ($stmtPermiso) {
        mysqli_stmt_bind_param($stmtPermiso, 'iii', $usuarioIdParam, $seccionIdParam, $puedeVerParam);
        $usuarioIdParam = (int)$USUARIOIDEditar;

        while ($filaSeccion = mysqli_fetch_assoc($resultadoSecciones)) {
            $seccionIdParam = (int)$filaSeccion['SECCIONID'];
            $puedeVerParam = in_array($seccionIdParam, $seccionesSeleccionadas, true) ? 1 : 0;
            mysqli_stmt_execute($stmtPermiso);
        }

        mysqli_stmt_close($stmtPermiso);
    }

    mysqli_free_result($resultadoSecciones);
}

if (isset($_SESSION['USUARIOID']) && (int)$_SESSION['USUARIOID'] === (int)$USUARIOIDEditar) {
    $permisos = [];
    $stmtPermisos = @mysqli_prepare(
        $conn,
        'SELECT s.Slug, COALESCE(us.PuedeVer, 0) as PuedeVer
         FROM secciones s
         LEFT JOIN usuario_secciones us ON us.SECCIONID = s.SECCIONID AND us.USUARIOID = ?
         ORDER BY s.Orden, s.Nombre'
    );

    if ($stmtPermisos) {
        mysqli_stmt_bind_param($stmtPermisos, 'i', $usuarioIdRefresh);
        $usuarioIdRefresh = (int)$USUARIOIDEditar;
        mysqli_stmt_execute($stmtPermisos);
        mysqli_stmt_bind_result($stmtPermisos, $slugPermiso, $puedeVerPermiso);

        while (mysqli_stmt_fetch($stmtPermisos)) {
            $permisos[$slugPermiso] = (int)$puedeVerPermiso;
        }

        mysqli_stmt_close($stmtPermisos);
    }

    $_SESSION['PermisosSecciones'] = $permisos;
}

// send data as json format
echo json_encode($msg);
