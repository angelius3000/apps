<?php
include("../../Connections/ConDB.php");

$CharolaID = isset($_POST['CHAROLASID']) ? intval($_POST['CHAROLASID']) : 0;
$Cantidad = isset($_POST['CANTIDAD']) ? intval($_POST['CANTIDAD']) : 0;

$data = array();

if ($CharolaID > 0 && $Cantidad > 0) {
    $sql = "SELECT mp.SkuMP, mp.DescripcionMP, mp.TipoMP, cc.CANTIDAD
            FROM cantidadcharolas cc
            INNER JOIN materiaprimacharolas mp ON cc.MATERIAPRIMAID = mp.MATERIAPRIMAID
            WHERE cc.CHAROLASID = $CharolaID";
    $result = mysqli_query($conn, $sql) or die("database error:" . mysqli_error($conn));

    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = array(
            'SkuMP' => $row['SkuMP'],
            'DescripcionMP' => $row['DescripcionMP'],
            'TipoMP' => $row['TipoMP'],
            'Cantidad' => intval($row['CANTIDAD']) * $Cantidad
        );
    }
}

echo json_encode($data);
