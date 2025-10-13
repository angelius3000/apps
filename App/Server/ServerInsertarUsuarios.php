<?php

include("../../Connections/ConDB.php");
include("../../includes/Funciones.php");

$TIPODEUSUARIOID = mysqli_real_escape_string($conn, $_POST['TIPODEUSUARIOID']);
$PrimerNombre = mysqli_real_escape_string($conn, $_POST['PrimerNombre']);
$SegundoNombre = mysqli_real_escape_string($conn, $_POST['SegundoNombre']);
$ApellidoPaterno = mysqli_real_escape_string($conn, $_POST['ApellidoPaterno']);
$ApellidoMaterno = mysqli_real_escape_string($conn, $_POST['ApellidoMaterno']);
$email = mysqli_real_escape_string($conn, $_POST['email']);
$Telefono = mysqli_real_escape_string($conn, $_POST['Telefono']);

$CLIENTEID = isset($_POST['CLIENTEID']) ? mysqli_real_escape_string($conn, $_POST['CLIENTEID']) : 0;
$SeccionInicioID = isset($_POST['SeccionInicioID']) && $_POST['SeccionInicioID'] !== ''
    ? (int)$_POST['SeccionInicioID']
    : null;

$Password = mysqli_real_escape_string($conn, $_POST['Password']);
$Password = sha1($Password);

$HASH = random_num(40);


$seccionInicioSql = $SeccionInicioID !== null ? "'" . $SeccionInicioID . "'" : "NULL";

$sql = "INSERT INTO usuarios (PrimerNombre, SegundoNombre, ApellidoPaterno, ApellidoMaterno, email, Telefono, TIPODEUSUARIOID, CLIENTEID, Password, HASH, SECCIONINICIOID) VALUES ('$PrimerNombre', '$SegundoNombre', '$ApellidoPaterno', '$ApellidoMaterno', '$email', '$Telefono', '$TIPODEUSUARIOID', '$CLIENTEID', '$Password', '$HASH', $seccionInicioSql)";

if (!mysqli_query($conn, $sql)) {
    die('Error: ' . mysqli_error($conn));
}

$last_id = mysqli_insert_id($conn);

$seccionesSeleccionadas = [];
if (isset($_POST['secciones']) && is_array($_POST['secciones'])) {
    $seccionesSeleccionadas = array_map('intval', array_keys($_POST['secciones']));
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
        $usuarioIdParam = (int)$last_id;

        while ($filaSeccion = mysqli_fetch_assoc($resultadoSecciones)) {
            $seccionIdParam = (int)$filaSeccion['SECCIONID'];
            $puedeVerParam = in_array($seccionIdParam, $seccionesSeleccionadas, true) ? 1 : 0;
            mysqli_stmt_execute($stmtPermiso);
        }

        mysqli_stmt_close($stmtPermiso);
    }

    mysqli_free_result($resultadoSecciones);
}

$msg = array('USUARIOID' => $last_id);

// send data as json format
echo json_encode($msg);

mysqli_close($conn);
