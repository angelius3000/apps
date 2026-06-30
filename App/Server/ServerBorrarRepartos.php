<?php

include("../../Connections/ConDB.php");

$REPARTOID = mysqli_real_escape_string($conn, $_POST['REPARTOID']);

$consultaEstatus = mysqli_query($conn, "SELECT status.Status FROM repartos LEFT JOIN status ON status.STATUSID = repartos.STATUSID WHERE repartos.REPARTOID = '$REPARTOID' LIMIT 1");
$repartoActual = $consultaEstatus ? mysqli_fetch_assoc($consultaEstatus) : null;

if (!$repartoActual || strcasecmp(trim((string) ($repartoActual['Status'] ?? '')), 'Registrado') !== 0) {
    http_response_code(409);
    echo json_encode(array(
        'error' => 'Solo se pueden eliminar repartos con estatus Registrado.'
    ));
    exit;
}

// Build the base query
$sql = "DELETE FROM repartos
    WHERE REPARTOID = '$REPARTOID'";

if (!mysqli_query($conn, $sql)) {
    die('Error: ' . mysqli_error($conn));
}

$msg = array('REPARTOID' => $REPARTOID);

// send data as json format
echo json_encode($msg);
