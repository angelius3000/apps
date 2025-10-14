<?php

include("../../Connections/ConDB.php");
// Definir La table de la base de datos

$IDDeUsuario = $_POST['ID'];

$sql = "SELECT * FROM usuarios WHERE usuarios.USUARIOID = $IDDeUsuario";
$status = mysqli_query($conn, $sql) or die("database error:" . mysqli_error($conn));
$row = mysqli_fetch_array($status);

$permisos = array();
$resultadoPermisos = mysqli_query($conn, "SELECT SECCIONID, PuedeVer FROM usuario_secciones WHERE USUARIOID = " . (int)$IDDeUsuario);
if ($resultadoPermisos) {
    while ($permiso = mysqli_fetch_assoc($resultadoPermisos)) {
        $permisos[(int)$permiso['SECCIONID']] = (int)$permiso['PuedeVer'];
    }
    mysqli_free_result($resultadoPermisos);
}

$msg = array(
    'USUARIOID' => $row['USUARIOID'],
    'PrimerNombre' => $row['PrimerNombre'],
    'SegundoNombre' => $row['SegundoNombre'],
    'ApellidoPaterno' => $row['ApellidoPaterno'],
    'ApellidoMaterno' => $row['ApellidoMaterno'],
    'Email' => $row['email'],
    'Telefono' => $row['Telefono'],
    'CLIENTEID' => $row['CLIENTEID'],
    'TIPODEUSUARIOID' => $row['TIPODEUSUARIOID'],
    'SeccionInicioID' => $row['SECCIONINICIOID'],
    'Permisos' => $permisos,
    'Deshabilitado' => (int)$row['Deshabilitado']
);

// send data as json format
echo json_encode($msg);
