<?php

include("../../Connections/ConDB.php");


$CLIENTEID = mysqli_real_escape_string($conn, $_POST['CLIENTEIDClonar']);
$NumeroDeFactura = mysqli_real_escape_string($conn, $_POST['NumeroDeFacturaClonar']);
$Calle = mysqli_real_escape_string($conn, $_POST['CalleClonar']);
$NumeroEXT = mysqli_real_escape_string($conn, $_POST['NumeroEXTClonar']);
$Colonia = mysqli_real_escape_string($conn, $_POST['ColoniaClonar']);
$CP = mysqli_real_escape_string($conn, $_POST['CPClonar']);
$Ciudad = mysqli_real_escape_string($conn, $_POST['CiudadClonar']);
$Estado = mysqli_real_escape_string($conn, $_POST['EstadoClonar']);
$Receptor = mysqli_real_escape_string($conn, $_POST['ReceptorClonar']);
$TelefonoDeReceptor = mysqli_real_escape_string($conn, $_POST['TelefonoDeReceptorClonar']);
$TelefonoAlternativo = mysqli_real_escape_string($conn, $_POST['TelefonoAlternativoClonar']);
$Comentarios = mysqli_real_escape_string($conn, $_POST['ComentariosClonar']);

$EnlaceGoogleMaps = !empty($_POST['EnlaceGoogleMapsClonar']) ? mysqli_real_escape_string($conn, $_POST['EnlaceGoogleMapsClonar']) : NULL;

$USUARIOID = mysqli_real_escape_string($conn, $_POST['USUARIOIDClonar']);

// Construye la consulta SQL de forma dinámica
if ($EnlaceGoogleMaps !== NULL) {
    $sql = "INSERT INTO repartos (USUARIOID, CLIENTEID, NumeroDeFactura, Calle, NumeroEXT, Colonia, CP, Ciudad, Estado, Receptor, TelefonoDeReceptor, TelefonoAlternativo, Comentarios, STATUSID, EnlaceMapaGoogle) 
            VALUES ('$USUARIOID', '$CLIENTEID', '$NumeroDeFactura', '$Calle', '$NumeroEXT', '$Colonia', '$CP', '$Ciudad', '$Estado', '$Receptor', '$TelefonoDeReceptor', '$TelefonoAlternativo', '$Comentarios', '1', '$EnlaceGoogleMaps')";
} else {
    $sql = "INSERT INTO repartos (USUARIOID, CLIENTEID, NumeroDeFactura, Calle, NumeroEXT, Colonia, CP, Ciudad, Estado, Receptor, TelefonoDeReceptor, TelefonoAlternativo, Comentarios, STATUSID) 
            VALUES ('$USUARIOID', '$CLIENTEID', '$NumeroDeFactura', '$Calle', '$NumeroEXT', '$Colonia', '$CP', '$Ciudad', '$Estado', '$Receptor', '$TelefonoDeReceptor', '$TelefonoAlternativo', '$Comentarios', '1')";
}

if (!mysqli_query($conn, $sql)) {
    die('Error: ' . mysqli_error($conn));
}

$last_id = mysqli_insert_id($conn);

$msg = array('REPARTOID' => $last_id);

// send data as json format
echo json_encode($msg);

mysqli_close($conn);
