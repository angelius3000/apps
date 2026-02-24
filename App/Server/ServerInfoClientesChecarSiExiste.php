<?php

include("../../Connections/ConDB.php");

$IDDeCliente = $_POST['ID'];

$sql = "SELECT * FROM clientes WHERE clientes.CLIENTEID = $IDDeCliente";
$status = mysqli_query($conn, $sql) or die("database error:" . mysqli_error($conn));
$row = mysqli_fetch_array($status);

$msg = array(
    'CLIENTEID' => $row['CLIENTEID'],
    'NombreCliente' => $row['NombreCliente']
);

echo json_encode($msg);
