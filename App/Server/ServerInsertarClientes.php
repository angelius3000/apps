<?php

include("../../Connections/ConDB.php");
include("../../includes/Funciones.php");

header('Content-Type: application/json');

$clienteSianInput = isset($_POST['CLIENTESIAN']) ? trim($_POST['CLIENTESIAN']) : '';
$clcSianInput = isset($_POST['CLCSIAN']) ? trim($_POST['CLCSIAN']) : '';

if ($clienteSianInput === '' && $clcSianInput === '') {
    http_response_code(400);
    echo json_encode([
        'error' => 'Captura al menos uno de los números de cliente (CLIENTESIAN o CLCSIAN).'
    ]);
    mysqli_close($conn);
    exit;
}

$CLIENTESIAN = ($clienteSianInput !== '')
    ? "'" . mysqli_real_escape_string($conn, $clienteSianInput) . "'"
    : "NULL";
$CLCSIAN = ($clcSianInput !== '')
    ? "'" . mysqli_real_escape_string($conn, $clcSianInput) . "'"
    : "NULL";

$NombreCliente = isset($_POST['NombreCliente']) ? mysqli_real_escape_string($conn, $_POST['NombreCliente']) : NULL;

$sql = "INSERT INTO clientes (CLIENTESIAN, CLCSIAN, NombreCliente) VALUES ($CLIENTESIAN, $CLCSIAN, '$NombreCliente')";

if (!mysqli_query($conn, $sql)) {
    die('Error: ' . mysqli_error($conn));
}

$last_id = mysqli_insert_id($conn);

$nombreClienteLimpio = trim((string) ($_POST['NombreCliente'] ?? ''));
foreach ([$clienteSianInput, $clcSianInput] as $numeroSolicitud) {
    if ($numeroSolicitud === '') {
        continue;
    }

    @mysqli_query($conn, "CREATE TABLE IF NOT EXISTS Solicitud_Clientes (SolicitudClienteID INT NOT NULL AUTO_INCREMENT, NumeroCliente VARCHAR(100) NOT NULL, Atendida TINYINT(1) NOT NULL DEFAULT 0, FechaSolicitud TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, FechaAtencion TIMESTAMP NULL DEFAULT NULL, PRIMARY KEY (SolicitudClienteID), INDEX idx_solicitud_cliente_estado (Atendida), INDEX idx_solicitud_cliente_numero (NumeroCliente)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $numeroEscapado = mysqli_real_escape_string($conn, $numeroSolicitud);
    @mysqli_query($conn, "UPDATE Solicitud_Clientes SET Atendida = 1, FechaAtencion = NOW() WHERE NumeroCliente = '$numeroEscapado' AND Atendida = 0");

    if ($nombreClienteLimpio !== '') {
        $nombreEscapado = mysqli_real_escape_string($conn, $nombreClienteLimpio);
        @mysqli_query($conn, "UPDATE facturamp SET RazonSocialFMP = '$nombreEscapado', ClienteFMP = '$nombreEscapado' WHERE RazonSocialFMP LIKE '%Cliente #$numeroEscapado%'");
        @mysqli_query($conn, "UPDATE materialpendiente SET RazonSocialMP = '$nombreEscapado', ClienteMP = '$nombreEscapado' WHERE RazonSocialMP LIKE '%Cliente #$numeroEscapado%'");
    }
}

$msg = array('CLIENTEID' => $last_id);

echo json_encode($msg);

mysqli_close($conn);
