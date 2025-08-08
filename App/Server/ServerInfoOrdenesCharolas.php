<?php
include("../../Connections/ConDB.php");

$query = "SELECT oc.ORDENCHAROLAID, oc.Cantidad, oc.STATUSID, s.Status, c.SkuCharolas, c.DescripcionCharolas
          FROM ordenes_charolas oc
          JOIN charolas c ON c.CHAROLASID = oc.CHAROLASID
          JOIN status s ON s.STATUSID = oc.STATUSID
          ORDER BY oc.ORDENCHAROLAID DESC";
$result = mysqli_query($conn, $query) or die(mysqli_error($conn));
$ordenes = array();
while ($row = mysqli_fetch_assoc($result)) {
    $ordenes[] = $row;
}
echo json_encode($ordenes);

mysqli_close($conn);
?>
