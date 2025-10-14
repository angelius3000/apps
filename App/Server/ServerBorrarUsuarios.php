<?php

include("../../Connections/ConDB.php");

$USUARIOIDBorrar = isset($_POST['USUARIOID']) ? (int)$_POST['USUARIOID'] : 0;

if ($USUARIOIDBorrar <= 0) {
    echo json_encode(['error' => 'ID de usuario inválido']);
    exit;
}

// Eliminar permisos asociados antes de eliminar el usuario
$eliminarPermisos = "DELETE FROM usuario_secciones WHERE USUARIOID = $USUARIOIDBorrar";
if (!mysqli_query($conn, $eliminarPermisos)) {
    die('Error: ' . mysqli_error($conn));
}

$sql = "DELETE FROM usuarios WHERE USUARIOID = $USUARIOIDBorrar";
if (!mysqli_query($conn, $sql)) {
    die('Error: ' . mysqli_error($conn));
}

$msg = array('USUARIOID' => $USUARIOIDBorrar);

echo json_encode($msg);
