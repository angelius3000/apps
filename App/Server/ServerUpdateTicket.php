<?php
include("../../Connections/ConDB.php");

if (!isset($_SESSION)) {
    session_start();
}

$tipoUsuarioActual = strtolower(trim((string)($_SESSION['TipoDeUsuario'] ?? '')));
$esAdmin = in_array($tipoUsuarioActual, ['soporte it', 'administrador'], true);
$usuarioSesion = (int)($_SESSION['USUARIOID'] ?? 0);

$TICKETIDEditar = (int)($_POST['TICKETIDEditar'] ?? 0);
$set = array("FechaActualizacion = NOW()");

if (isset($_POST['TituloEditar'])) {
    $TituloEditar = mysqli_real_escape_string($conn, $_POST['TituloEditar']);
    $set[] = "Titulo = '$TituloEditar'";
}
if (isset($_POST['DescripcionEditar'])) {
    $DescripcionEditar = mysqli_real_escape_string($conn, $_POST['DescripcionEditar']);
    $set[] = "Descripcion = '$DescripcionEditar'";
}
if (isset($_POST['PrioridadEditar'])) {
    $PrioridadEditar = mysqli_real_escape_string($conn, $_POST['PrioridadEditar']);
    $set[] = "Prioridad = '$PrioridadEditar'";
}
if (isset($_POST['CategoriaEditar'])) {
    $CategoriaEditar = mysqli_real_escape_string($conn, $_POST['CategoriaEditar']);
    $set[] = "Categoria = '$CategoriaEditar'";
}

if ($esAdmin) {
    if (isset($_POST['StatusEditar']) && $_POST['StatusEditar'] !== '') {
        $StatusEditar = mysqli_real_escape_string($conn, $_POST['StatusEditar']);
        $set[] = "STATUS = '$StatusEditar'";
        if ($StatusEditar === 'Cerrado') {
            $set[] = "FechaCierre = NOW()";
        } else {
            $set[] = "FechaCierre = NULL";
        }
    }

    if (isset($_POST['USUARIOID_ASIGNADOEditar'])) {
        if ($_POST['USUARIOID_ASIGNADOEditar'] === '') {
            $set[] = "USUARIOID_ASIGNADO = NULL";
        } else {
            $set[] = "USUARIOID_ASIGNADO = '" . (int)$_POST['USUARIOID_ASIGNADOEditar'] . "'";
        }
    }

    $sql = "UPDATE tickets SET " . implode(', ', $set) . " WHERE TICKETID = '$TICKETIDEditar'";
} else {
    $sql = "UPDATE tickets SET " . implode(', ', $set) . " WHERE TICKETID = '$TICKETIDEditar' AND USUARIOID_CREADOR = '$usuarioSesion'";
}

if (!mysqli_query($conn, $sql)) {
    die('Error: ' . mysqli_error($conn));
}

$msg = array('success' => true);
echo json_encode($msg);
