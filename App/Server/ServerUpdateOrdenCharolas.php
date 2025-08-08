<?php
include("../../Connections/ConDB.php");

$ORDENCHAROLAID = mysqli_real_escape_string($conn, $_POST['ORDENCHAROLAID']);
$STATUSID = mysqli_real_escape_string($conn, $_POST['STATUSID']);

$sql = "UPDATE ordenes_charolas SET STATUSID = '$STATUSID' WHERE ORDENCHAROLAID = '$ORDENCHAROLAID'";
if (!mysqli_query($conn, $sql)) {
    die('Error: ' . mysqli_error($conn));
}

$msg = array('ORDENCHAROLAID' => $ORDENCHAROLAID);
echo json_encode($msg);

mysqli_close($conn);
?>
