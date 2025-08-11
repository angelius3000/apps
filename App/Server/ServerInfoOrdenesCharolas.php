<?php
include("../../Connections/ConDB.php");


$query = "SELECT oc.ORDENCHAROLAID, oc.CHAROLASID, oc.Cantidad, oc.STATUSID, s.Status, c.SkuCharolas, c.DescripcionCharolas
          FROM ordenes_charolas oc
          JOIN charolas c ON c.CHAROLASID = oc.CHAROLASID
          JOIN status s ON s.STATUSID = oc.STATUSID
          ORDER BY oc.ORDENCHAROLAID DESC";
$result = mysqli_query($conn, $query) or die(mysqli_error($conn));
$ordenes = array();
while ($row = mysqli_fetch_assoc($result)) {
    $detalles = array();
    $sqlDetalles = "SELECT mp.SkuMP, mp.DescripcionMP, mp.TipoMP, cc.CANTIDAD
                    FROM cantidadcharolas cc
                    INNER JOIN materiaprimacharolas mp ON cc.MATERIAPRIMAID = mp.MATERIAPRIMAID
                    WHERE cc.CHAROLASID = " . $row['CHAROLASID'];
    $resultDetalles = mysqli_query($conn, $sqlDetalles) or die(mysqli_error($conn));
    while ($det = mysqli_fetch_assoc($resultDetalles)) {
        $detalles[] = array(
            'SkuMP' => $det['SkuMP'],
            'DescripcionMP' => $det['DescripcionMP'],
            'TipoMP' => $det['TipoMP'],
            'Cantidad' => intval($det['CANTIDAD']) * intval($row['Cantidad'])
        );
    }
    $row['Detalles'] = $detalles;
    $ordenes[] = $row;
}
echo json_encode($ordenes);

mysqli_close($conn);
?>
