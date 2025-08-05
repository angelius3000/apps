<?php

include("../../Connections/ConDB.php");

// Get values from form

// STATUSIDEditar=2&MotivoDelEstatus=Aqui%20esta%20el%20motivo%20Ya%20saliendo%20DINAMICAMENTE&Surtidores=&USUARIOIDRepartidor=&REPARTOIDEditarStatus=98&FechaReparto=&HoraReparto=

$STATUSID = mysqli_real_escape_string($conn, $_POST['STATUSIDEditar']);
$REPARTOID = mysqli_real_escape_string($conn, $_POST['REPARTOIDEditarStatus']);
$Surtidores = mysqli_real_escape_string($conn, $_POST['Surtidores']);
$Repartidor = mysqli_real_escape_string($conn, $_POST['USUARIOIDRepartidor']);
$MotivoDelEstatus = mysqli_real_escape_string($conn, $_POST['MotivoDelEstatus']);
$FechaReparto = mysqli_real_escape_string($conn, $_POST['FechaReparto']);
$HoraReparto = mysqli_real_escape_string($conn, $_POST['HoraReparto']);


// Build the base query
$sql = "UPDATE repartos SET 
    STATUSID = '$STATUSID'";

if (!empty($Repartidor)) {
    $sql .= ", USUARIOIDRepartidor = '$Repartidor'";
}

if (!empty($MotivoDelEstatus)) {
    $sql .= ", MotivoDelEstatus = '$MotivoDelEstatus'";
}

if (!empty($FechaReparto)) {
    $sql .= ", FechaReparto = '$FechaReparto'";
} else {
    $sql .= ", FechaReparto = NULL";
}

if (!empty($HoraReparto)) {
    $sql .= ", HoraReparto = '$HoraReparto'";
} else {
    $sql .= ", HoraReparto = NULL";
}

if (!empty($Surtidores)) {
    $sql .= ", Surtidores = '$Surtidores'";
}

$sql .= " WHERE REPARTOID = '$REPARTOID'";

if (!mysqli_query($conn, $sql)) {
    die('Error: ' . mysqli_error($conn));
}

$msg = array('REPARTOID' => $REPARTOID);

// send data as json format
echo json_encode($msg);
