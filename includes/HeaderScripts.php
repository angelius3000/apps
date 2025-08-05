<?php require_once('Connections/ConDB.php');

//initialize the session

if (!isset($_SESSION)) {
    session_start();
}

if (!isset($_SESSION['Username'])) { // Script para Sacar al usuario si no tiene el permiso 

    // Need the functions:
    require('includes/login_functions.inc.php');
    redirect_user();
}

$colname_UsuarioDeLogIn = "-1";
if (isset($_SESSION['Username'])) {
    $colname_UsuarioDeLogIn = $_SESSION['Username'];
}

$query_UsuarioDeLogIn = "SELECT * 
FROM usuarios 
WHERE usuarios.email = '$colname_UsuarioDeLogIn'";
$UsuarioDeLogIn = mysqli_query($conn, $query_UsuarioDeLogIn) or die(mysqli_error($conn));
$row_UsuarioDeLogIn = mysqli_fetch_assoc($UsuarioDeLogIn);
$totalRows_UsuarioDeLogIn = mysqli_num_rows($UsuarioDeLogIn);
