<?php

include("../../Connections/ConDB.php");

@mysqli_query($conn, "ALTER TABLE repartos ADD COLUMN ClienteSolicitadoReparto VARCHAR(100) DEFAULT NULL AFTER CLIENTEID");

// Get values from form
$clienteIdPost = trim((string) ($_POST['CLIENTEIDEditar'] ?? ''));
$numeroClienteSolicitado = trim((string) ($_POST['NumeroClienteSolicitadoRepartoEditar'] ?? ''));
if (strpos($clienteIdPost, 'solicitar:') === 0) {
    $numeroClienteSolicitado = trim(substr($clienteIdPost, strlen('solicitar:')));
    $clienteIdPost = '0';
}
$CLIENTEID = mysqli_real_escape_string($conn, $clienteIdPost);
$ClienteSolicitadoReparto = $numeroClienteSolicitado !== ''
    ? "'" . mysqli_real_escape_string($conn, $numeroClienteSolicitado) . "'"
    : 'NULL';
$NumeroDeFactura = mysqli_real_escape_string($conn, $_POST['NumeroDeFacturaEditar']);
$Calle = mysqli_real_escape_string($conn, $_POST['CalleEditar']);
$NumeroEXT = mysqli_real_escape_string($conn, $_POST['NumeroEXTEditar']);
$Colonia = mysqli_real_escape_string($conn, $_POST['ColoniaEditar']);
$CP = mysqli_real_escape_string($conn, $_POST['CPEditar']);
$Ciudad = mysqli_real_escape_string($conn, $_POST['CiudadEditar']);
$Estado = mysqli_real_escape_string($conn, $_POST['EstadoEditar']);
$Receptor = mysqli_real_escape_string($conn, $_POST['ReceptorEditar']);
$TelefonoDeReceptor = mysqli_real_escape_string($conn, $_POST['TelefonoDeReceptorEditar']);
$TelefonoAlternativo = mysqli_real_escape_string($conn, $_POST['TelefonoAlternativoEditar']);
$Comentarios = mysqli_real_escape_string($conn, $_POST['ComentariosEditar']);
$USUARIOID = mysqli_real_escape_string($conn, $_POST['USUARIOID']);
$REPARTOID = mysqli_real_escape_string($conn, $_POST['REPARTOIDEditar']);

$EnlaceGoogleMaps = !empty($_POST['EnlaceGoogleMapsEditar']) ? mysqli_real_escape_string($conn, $_POST['EnlaceGoogleMapsEditar']) : NULL;

$consultaEstatus = mysqli_query($conn, "SELECT status.Status FROM repartos LEFT JOIN status ON status.STATUSID = repartos.STATUSID WHERE repartos.REPARTOID = '$REPARTOID' LIMIT 1");
$repartoActual = $consultaEstatus ? mysqli_fetch_assoc($consultaEstatus) : null;

if (!$repartoActual || strcasecmp(trim((string) ($repartoActual['Status'] ?? '')), 'Registrado') !== 0) {
    http_response_code(409);
    echo json_encode(array(
        'error' => 'Solo se pueden editar repartos con estatus Registrado.'
    ));
    exit;
}

if ($EnlaceGoogleMaps !== NULL) {

    // Build the base query
    $sql = "UPDATE repartos SET
    CLIENTEID = '$CLIENTEID',
    ClienteSolicitadoReparto = $ClienteSolicitadoReparto,
    NumeroDeFactura = '$NumeroDeFactura',
    Calle = '$Calle',
    NumeroEXT = '$NumeroEXT',
    Colonia = '$Colonia',
    CP = '$CP',
    Ciudad = '$Ciudad',
    Estado = '$Estado',
    Receptor = '$Receptor',
    TelefonoDeReceptor = '$TelefonoDeReceptor',
    TelefonoAlternativo = '$TelefonoAlternativo',
    Comentarios = '$Comentarios',
    EnlaceMapaGoogle = '$EnlaceGoogleMaps'
    WHERE REPARTOID = '$REPARTOID'";
} else {

    // Build the base query
    $sql = "UPDATE repartos SET
    CLIENTEID = '$CLIENTEID',
    ClienteSolicitadoReparto = $ClienteSolicitadoReparto,
    NumeroDeFactura = '$NumeroDeFactura',
    Calle = '$Calle',
    NumeroEXT = '$NumeroEXT',
    Colonia = '$Colonia',
    CP = '$CP',
    Ciudad = '$Ciudad',
    Estado = '$Estado',
    Receptor = '$Receptor',
    TelefonoDeReceptor = '$TelefonoDeReceptor',
    TelefonoAlternativo = '$TelefonoAlternativo',
    Comentarios = '$Comentarios',
    EnlaceMapaGoogle = NULL
    WHERE REPARTOID = '$REPARTOID'";
}


if (!mysqli_query($conn, $sql)) {
    die('Error: ' . mysqli_error($conn));
}

$msg = array('USUARIOID' => $USUARIOIDEditar);

// send data as json format
echo json_encode($msg);
