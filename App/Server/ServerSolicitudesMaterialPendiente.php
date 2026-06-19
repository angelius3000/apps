<?php
include("../../Connections/ConDB.php");
header('Content-Type: application/json');

function responder($data, int $code = 200): void { http_response_code($code); echo json_encode($data); exit; }
function asegurar(mysqli $conn): void {
    @mysqli_query($conn, "CREATE TABLE IF NOT EXISTS Solicitud_Clientes (
        SolicitudClienteID INT NOT NULL AUTO_INCREMENT,
        NumeroCliente VARCHAR(100) NOT NULL,
        Atendida TINYINT(1) NOT NULL DEFAULT 0,
        FechaSolicitud TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FechaAtencion TIMESTAMP NULL DEFAULT NULL,
        PRIMARY KEY (SolicitudClienteID), INDEX idx_solicitud_cliente_estado (Atendida), INDEX idx_solicitud_cliente_numero (NumeroCliente)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    @mysqli_query($conn, "CREATE TABLE IF NOT EXISTS Solicitud_Productos (
        SolicitudProductoID INT NOT NULL AUTO_INCREMENT,
        SKU VARCHAR(100) NOT NULL,
        Atendida TINYINT(1) NOT NULL DEFAULT 0,
        FechaSolicitud TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FechaAtencion TIMESTAMP NULL DEFAULT NULL,
        PRIMARY KEY (SolicitudProductoID), INDEX idx_solicitud_producto_estado (Atendida), INDEX idx_solicitud_producto_sku (SKU)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}
if (!$conn) responder(['success'=>false,'message'=>'No se pudo conectar a la base de datos.'],500);
asegurar($conn);
$tipo = strtolower(trim($_GET['tipo'] ?? $_POST['tipo'] ?? ''));
if ($tipo === 'clientes') {
    $rs = mysqli_query($conn, "SELECT SolicitudClienteID id, NumeroCliente valor, FechaSolicitud fecha FROM Solicitud_Clientes WHERE Atendida = 0 ORDER BY SolicitudClienteID DESC");
} elseif ($tipo === 'productos') {
    $rs = mysqli_query($conn, "SELECT SolicitudProductoID id, SKU valor, FechaSolicitud fecha FROM Solicitud_Productos WHERE Atendida = 0 ORDER BY SolicitudProductoID DESC");
} else responder(['success'=>false,'message'=>'Tipo de solicitud inválido.'],400);
$records=[]; if ($rs instanceof mysqli_result) { while($r=mysqli_fetch_assoc($rs)) $records[]=$r; }
responder(['success'=>true,'records'=>$records]);
