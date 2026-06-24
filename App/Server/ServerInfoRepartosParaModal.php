<?php

include("../../Connections/ConDB.php");
include("../../includes/Funciones.php");
@mysqli_query($conn, "ALTER TABLE repartos ADD COLUMN ClienteSolicitadoReparto VARCHAR(100) DEFAULT NULL AFTER CLIENTEID");
// Definir La table de la base de datos

$IDDeReparto = $_POST['ID'];

$sql = "SELECT * FROM repartos
LEFT JOIN clientes ON clientes.CLIENTEID = repartos.CLIENTEID
WHERE repartos.REPARTOID = '$IDDeReparto'";
$status = mysqli_query($conn, $sql) or die("database error:" . mysqli_error($conn));
$row = mysqli_fetch_array($status);


$clienteReparto = trim((string) ($row['NombreCliente'] ?? ''));
if ($clienteReparto === '' && !empty($row['ClienteSolicitadoReparto'])) {
    $clienteReparto = 'Solicitado: ' . $row['ClienteSolicitadoReparto'];
}

$DatosParaBorrarReparto = '<strong>Folio: </strong>' . $row['REPARTOID']
    . '<br> <strong>Fecha: </strong>' . SoloFecha($row['FechaDeRegistro']) .
    '<br><strong>Cliente: </strong> ' . $clienteReparto;

$HoraSinSegundos = substr($row['HoraReparto'], 0, 5);

$msg = array(
    'REPARTOID' => $row['REPARTOID'],
    'USUARIOID' => $row['USUARIOID'],
    'CLIENTEID' => $row['CLIENTEID'],
    'ClienteSolicitadoReparto' => $row['ClienteSolicitadoReparto'],
    'NumeroDeFactura' => $row['NumeroDeFactura'],
    'FechaReparto' => $row['FechaReparto'],
    'HoraReparto' => $HoraSinSegundos,
    'FechaDeRegistro' => $row['FechaDeRegistro'],
    'Calle' => $row['Calle'],
    'NumeroEXT' => $row['NumeroEXT'],
    'Colonia' => $row['Colonia'],
    'CP' => $row['CP'],
    'Ciudad' => $row['Ciudad'],
    'Estado' => $row['Estado'],
    'Receptor' => $row['Receptor'],
    'TelefonoDeReceptor' => $row['TelefonoDeReceptor'],
    'TelefonoAlternativo' => $row['TelefonoAlternativo'],
    'Comentarios' => $row['Comentarios'],
    'STATUSID' => $row['STATUSID'],
    'DatosParaBorrarReparto' => $DatosParaBorrarReparto,
    'Surtidores' => $row['Surtidores'],
    'USUARIOIDRepartidor' => $row['USUARIOIDRepartidor'],
    'EnlaceMapaGoogle' => $row['EnlaceMapaGoogle'],
    'MotivoDelEstatus' => $row['MotivoDelEstatus'],


);

// send data as json format
echo json_encode($msg);
