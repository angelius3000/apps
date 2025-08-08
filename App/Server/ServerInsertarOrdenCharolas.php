<?php
include("../../Connections/ConDB.php");

$CHAROLASID = mysqli_real_escape_string($conn, $_POST['CHAROLASID']);
$Cantidad = mysqli_real_escape_string($conn, $_POST['Cantidad']);

$sql = "INSERT INTO ordenes_charolas (CHAROLASID, Cantidad, STATUSID) VALUES ('$CHAROLASID', '$Cantidad', 1)";
if (!mysqli_query($conn, $sql)) {
    die('Error: ' . mysqli_error($conn));
}

$msg = array('ORDENCHAROLAID' => mysqli_insert_id($conn));
echo json_encode($msg);

mysqli_close($conn);
?>
