<?php

include("../../Connections/ConDB.php");

$ID = (int)($_POST['ID'] ?? 0);

$sql = "SELECT t.*, 
        CONCAT_WS(' ', u1.PrimerNombre, u1.ApellidoPaterno) AS CreadoPor,
        CONCAT_WS(' ', u2.PrimerNombre, u2.ApellidoPaterno) AS AsignadoA
        FROM tickets t
        LEFT JOIN usuarios u1 ON t.USUARIOID_CREADOR = u1.USUARIOID
        LEFT JOIN usuarios u2 ON t.USUARIOID_ASIGNADO = u2.USUARIOID
        WHERE t.TICKETID = $ID";
$status = mysqli_query($conn, $sql) or die("database error:" . mysqli_error($conn));
$row = mysqli_fetch_array($status);

$msg = array(
    'TICKETID' => $row['TICKETID'],
    'Folio' => $row['Folio'],
    'Titulo' => $row['Titulo'],
    'Descripcion' => $row['Descripcion'],
    'Prioridad' => $row['Prioridad'],
    'Categoria' => $row['Categoria'],
    'STATUS' => $row['STATUS'],
    'USUARIOID_CREADOR' => $row['USUARIOID_CREADOR'],
    'USUARIOID_ASIGNADO' => $row['USUARIOID_ASIGNADO'],
    'CreadoPor' => $row['CreadoPor'],
    'AsignadoA' => $row['AsignadoA'] ?? ''
);

echo json_encode($msg);
