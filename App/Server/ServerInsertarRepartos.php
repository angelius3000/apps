<?php

include("../../Connections/ConDB.php");


$CLIENTEID = mysqli_real_escape_string($conn, $_POST['CLIENTEID']);
$NumeroDeFactura = mysqli_real_escape_string($conn, $_POST['NumeroDeFactura']);
$Calle = mysqli_real_escape_string($conn, $_POST['Calle']);
$NumeroEXT = mysqli_real_escape_string($conn, $_POST['NumeroEXT']);
$Colonia = mysqli_real_escape_string($conn, $_POST['Colonia']);
$CP = mysqli_real_escape_string($conn, $_POST['CP']);
$Ciudad = mysqli_real_escape_string($conn, $_POST['Ciudad']);
$Estado = mysqli_real_escape_string($conn, $_POST['Estado']);
$Receptor = mysqli_real_escape_string($conn, $_POST['Receptor']);
$TelefonoDeReceptor = mysqli_real_escape_string($conn, $_POST['TelefonoDeReceptor']);
$TelefonoAlternativo = mysqli_real_escape_string($conn, $_POST['TelefonoAlternativo']);
$Comentarios = mysqli_real_escape_string($conn, $_POST['Comentarios']);

$EnlaceGoogleMaps = !empty($_POST['EnlaceGoogleMaps']) ? mysqli_real_escape_string($conn, $_POST['EnlaceGoogleMaps']) : NULL;

$USUARIOID = mysqli_real_escape_string($conn, $_POST['USUARIOID']);

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
