<?php

include("../../Connections/ConDB.php");
// Definir La table de la base de datos

$EmailDeCliente = $_POST['EmailCliente'];

if ($EmailDeCliente != "") {


    $sql = "SELECT * FROM clientes WHERE clientes.EmailCliente = '$EmailDeCliente'";
    $status = mysqli_query($conn, $sql) or die("database error:" . mysqli_error($conn));
    $row = mysqli_fetch_array($status);

    $msg = array(
        'CLIENTEID' => $row['CLIENTEID'],
        'CLIENTESIAN' => $row['CLIENTESIAN'],
        'NombreCliente' => $row['NombreCliente'],
        'EmailCliente' => $row['EmailCliente'],
        'TelefonoCliente' => $row['TelefonoCliente'],
        'NombreContacto' => $row['NombreContacto'],
        'DireccionCliente' => $row['DireccionCliente'],
        'ColoniaCliente' => $row['ColoniaCliente'],
        'CiudadCliente' => $row['CiudadCliente'],
        'EstadoCliente' => $row['EstadoCliente']
    );
} else {

    $msg = array(

        'NombreCliente' => NULL,

    );
}


// send data as json format
echo json_encode($msg);
