<?php

include("../../Connections/ConDB.php");

if (!isset($_SESSION)) {
    session_start();
}
@mysqli_query($conn, "ALTER TABLE repartos ADD COLUMN ClienteSolicitadoReparto VARCHAR(100) DEFAULT NULL AFTER CLIENTEID");
@mysqli_query($conn, "CREATE TABLE IF NOT EXISTS Solicitud_Clientes (SolicitudClienteID INT NOT NULL AUTO_INCREMENT, NumeroCliente VARCHAR(100) NOT NULL, Atendida TINYINT(1) NOT NULL DEFAULT 0, FechaSolicitud TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, FechaAtencion TIMESTAMP NULL DEFAULT NULL, SolicitanteNombre VARCHAR(255) NULL, PRIMARY KEY (SolicitudClienteID), INDEX idx_solicitud_cliente_estado (Atendida), INDEX idx_solicitud_cliente_numero (NumeroCliente)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$clienteIdPost = trim((string) ($_POST['CLIENTEIDClonar'] ?? ''));
$numeroClienteSolicitado = trim((string) ($_POST['NumeroClienteSolicitadoRepartoClonar'] ?? ''));
if (strpos($clienteIdPost, 'solicitar:') === 0) {
    $numeroClienteSolicitado = trim(substr($clienteIdPost, strlen('solicitar:')));
    $clienteIdPost = '0';
}
$CLIENTEID = mysqli_real_escape_string($conn, $clienteIdPost);
$ClienteSolicitadoReparto = $numeroClienteSolicitado !== ''
    ? "'" . mysqli_real_escape_string($conn, $numeroClienteSolicitado) . "'"
    : 'NULL';
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
    $sql = "INSERT INTO repartos (USUARIOID, CLIENTEID, ClienteSolicitadoReparto, NumeroDeFactura, Calle, NumeroEXT, Colonia, CP, Ciudad, Estado, Receptor, TelefonoDeReceptor, TelefonoAlternativo, Comentarios, STATUSID, EnlaceMapaGoogle)
            VALUES ('$USUARIOID', '$CLIENTEID', $ClienteSolicitadoReparto, '$NumeroDeFactura', '$Calle', '$NumeroEXT', '$Colonia', '$CP', '$Ciudad', '$Estado', '$Receptor', '$TelefonoDeReceptor', '$TelefonoAlternativo', '$Comentarios', '1', '$EnlaceGoogleMaps')";
} else {
    $sql = "INSERT INTO repartos (USUARIOID, CLIENTEID, ClienteSolicitadoReparto, NumeroDeFactura, Calle, NumeroEXT, Colonia, CP, Ciudad, Estado, Receptor, TelefonoDeReceptor, TelefonoAlternativo, Comentarios, STATUSID)
            VALUES ('$USUARIOID', '$CLIENTEID', $ClienteSolicitadoReparto, '$NumeroDeFactura', '$Calle', '$NumeroEXT', '$Colonia', '$CP', '$Ciudad', '$Estado', '$Receptor', '$TelefonoDeReceptor', '$TelefonoAlternativo', '$Comentarios', '1')";
}

if (!mysqli_query($conn, $sql)) {
    die('Error: ' . mysqli_error($conn));
}

$last_id = mysqli_insert_id($conn);

if ($numeroClienteSolicitado !== '') {
    $numeroSolicitudSql = mysqli_real_escape_string($conn, $numeroClienteSolicitado);
    $solicitante = mysqli_real_escape_string($conn, trim((string) ($_SESSION['NombreDelUsuario'] ?? $_SESSION['Username'] ?? '')));
    @mysqli_query($conn, "INSERT INTO Solicitud_Clientes (NumeroCliente, SolicitanteNombre) VALUES ('$numeroSolicitudSql', '$solicitante')");

    include_once __DIR__ . '/../../includes/MandarEmail.php';
    if (function_exists('EnviarNotificacionSolicitudMaterialPendiente')) {
        EnviarNotificacionSolicitudMaterialPendiente([$numeroClienteSolicitado], [], $NumeroDeFactura);
    }
}

$msg = array('REPARTOID' => $last_id);

// send data as json format
echo json_encode($msg);

mysqli_close($conn);
