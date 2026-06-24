<?php

include("../../Connections/ConDB.php");

function asegurarColumnaClienteSolicitadoReparto(mysqli $conn): void
{
    @mysqli_query($conn, "ALTER TABLE repartos ADD COLUMN ClienteSolicitadoReparto VARCHAR(100) DEFAULT NULL AFTER CLIENTEID");
}

asegurarColumnaClienteSolicitadoReparto($conn);


$clienteIdPost = isset($_POST['CLIENTEID']) ? trim((string) $_POST['CLIENTEID']) : '';
$numeroClienteSolicitado = isset($_POST['NumeroClienteSolicitadoReparto']) ? trim((string) $_POST['NumeroClienteSolicitadoReparto']) : '';

if (strpos($clienteIdPost, 'solicitar:') === 0) {
    $numeroClienteSolicitado = trim(substr($clienteIdPost, strlen('solicitar:')));
    $clienteIdPost = '0';
}

$CLIENTEID = mysqli_real_escape_string($conn, $clienteIdPost);
$ClienteSolicitadoReparto = $numeroClienteSolicitado !== '' ? mysqli_real_escape_string($conn, $numeroClienteSolicitado) : NULL;
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
$ClienteSolicitadoRepartoSql = $ClienteSolicitadoReparto !== NULL ? "'" . $ClienteSolicitadoReparto . "'" : 'NULL';

// Construye la consulta SQL de forma dinámica
if ($EnlaceGoogleMaps !== NULL) {
    $sql = "INSERT INTO repartos (USUARIOID, CLIENTEID, ClienteSolicitadoReparto, NumeroDeFactura, Calle, NumeroEXT, Colonia, CP, Ciudad, Estado, Receptor, TelefonoDeReceptor, TelefonoAlternativo, Comentarios, STATUSID, EnlaceMapaGoogle)
            VALUES ('$USUARIOID', '$CLIENTEID', $ClienteSolicitadoRepartoSql, '$NumeroDeFactura', '$Calle', '$NumeroEXT', '$Colonia', '$CP', '$Ciudad', '$Estado', '$Receptor', '$TelefonoDeReceptor', '$TelefonoAlternativo', '$Comentarios', '1', '$EnlaceGoogleMaps')";
} else {
    $sql = "INSERT INTO repartos (USUARIOID, CLIENTEID, ClienteSolicitadoReparto, NumeroDeFactura, Calle, NumeroEXT, Colonia, CP, Ciudad, Estado, Receptor, TelefonoDeReceptor, TelefonoAlternativo, Comentarios, STATUSID)
            VALUES ('$USUARIOID', '$CLIENTEID', $ClienteSolicitadoRepartoSql, '$NumeroDeFactura', '$Calle', '$NumeroEXT', '$Colonia', '$CP', '$Ciudad', '$Estado', '$Receptor', '$TelefonoDeReceptor', '$TelefonoAlternativo', '$Comentarios', '1')";
}

if (!mysqli_query($conn, $sql)) {
    die('Error: ' . mysqli_error($conn));
}

$last_id = mysqli_insert_id($conn);

if ($numeroClienteSolicitado !== '') {
    @mysqli_query($conn, "CREATE TABLE IF NOT EXISTS Solicitud_Clientes (SolicitudClienteID INT NOT NULL AUTO_INCREMENT, NumeroCliente VARCHAR(100) NOT NULL, Atendida TINYINT(1) NOT NULL DEFAULT 0, FechaSolicitud TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, FechaAtencion TIMESTAMP NULL DEFAULT NULL, PRIMARY KEY (SolicitudClienteID), INDEX idx_solicitud_cliente_estado (Atendida), INDEX idx_solicitud_cliente_numero (NumeroCliente)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $numeroClienteSolicitadoSql = mysqli_real_escape_string($conn, $numeroClienteSolicitado);
    @mysqli_query($conn, "INSERT INTO Solicitud_Clientes (NumeroCliente) VALUES ('$numeroClienteSolicitadoSql')");

    include_once __DIR__ . '/../../includes/MandarEmail.php';
    if (function_exists('EnviarNotificacionSolicitudMaterialPendiente')) {
        EnviarNotificacionSolicitudMaterialPendiente([$numeroClienteSolicitado], [], $NumeroDeFactura);
    }
}

$msg = array('REPARTOID' => $last_id);

// send data as json format
echo json_encode($msg);

mysqli_close($conn);
