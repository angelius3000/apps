<?php

include("../../Connections/ConDB.php");
include("../../includes/MandarEmail.php");
// Definir La table de la base de datos

$EmailDeUsuario = $_POST['email'];

$sql = "SELECT usuarios.HASH FROM usuarios WHERE usuarios.email = '$EmailDeUsuario'";
$status = mysqli_query($conn, $sql) or die("database error:" . mysqli_error($conn));
$row = mysqli_fetch_array($status);

$HASH = $row['HASH'];

RecuperaTuPassword($EmailDeUsuario, $HASH);


$msg = array(
    'Ok' => 'Ok',
    'Email' => $EmailDeUsuario,
);

// send data as json format
echo json_encode($msg);
