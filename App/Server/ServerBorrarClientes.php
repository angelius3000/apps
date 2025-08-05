<?php

include("../../Connections/ConDB.php");

$CLIENTEIDBorrar = mysqli_real_escape_string($conn, $_POST['CLIENTEID']);

// Build the base query
$sql = "DELETE FROM clientes WHERE CLIENTEID = '$CLIENTEIDBorrar'";

if (!mysqli_query($conn, $sql)) {
    die('Error: ' . mysqli_error($conn));
}

$msg = array('CLIENTEID' => $CLIENTEIDBorrar);

// send data as json format
echo json_encode($msg);
