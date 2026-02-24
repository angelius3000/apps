<?php

include("../../Connections/ConDB.php");

$ClienteSian = isset($_POST['CLIENTESIAN']) ? trim($_POST['CLIENTESIAN']) : '';

if ($ClienteSian != "") {
    $ClienteSian = mysqli_real_escape_string($conn, $ClienteSian);
    $sql = "SELECT * FROM clientes WHERE clientes.CLIENTESIAN = '$ClienteSian'";
    $status = mysqli_query($conn, $sql) or die("database error:" . mysqli_error($conn));
    $row = mysqli_fetch_array($status);

    $msg = array(
        'CLIENTEID' => $row['CLIENTEID'],
        'CLIENTESIAN' => $row['CLIENTESIAN'],
        'NombreCliente' => $row['NombreCliente']
    );
} else {
    $msg = array('NombreCliente' => NULL);
}

echo json_encode($msg);
