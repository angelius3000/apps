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

$Password = mysqli_real_escape_string($conn, $_POST['Password']);
$Password = sha1($Password);

$HASH = random_num(40);


$sql = "INSERT INTO usuarios (PrimerNombre, SegundoNombre, ApellidoPaterno, ApellidoMaterno, email, Telefono, TIPODEUSUARIOID, CLIENTEID, Password, HASH) VALUES ('$PrimerNombre', '$SegundoNombre', '$ApellidoPaterno', '$ApellidoMaterno', '$email', '$Telefono', '$TIPODEUSUARIOID', '$CLIENTEID', '$Password', '$HASH')";

if (!mysqli_query($conn, $sql)) {
    die('Error: ' . mysqli_error($conn));
}

$last_id = mysqli_insert_id($conn);

$msg = array('USUARIOID' => $last_id);

// send data as json format
echo json_encode($msg);

mysqli_close($conn);
