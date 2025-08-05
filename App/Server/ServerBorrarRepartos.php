<?php

include("../../Connections/ConDB.php");

$REPARTOID = mysqli_real_escape_string($conn, $_POST['REPARTOID']);

// Build the base query
$sql = "DELETE FROM repartos
    WHERE REPARTOID = '$REPARTOID'";

if (!mysqli_query($conn, $sql)) {
    die('Error: ' . mysqli_error($conn));
}

$msg = array('REPARTOID' => $REPARTOID);

// send data as json format
echo json_encode($msg);
