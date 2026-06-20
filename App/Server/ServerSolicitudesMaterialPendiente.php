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
$accion = strtolower(trim($_POST['accion'] ?? $_GET['accion'] ?? 'listar'));

if ($accion === 'eliminar') {
    $id = isset($_POST['id']) ? (int) $_POST['id'] : (int) ($_GET['id'] ?? 0);

    if ($id <= 0) {
        responder(['success' => false, 'message' => 'Solicitud inválida.'], 400);
    }

    if ($tipo === 'clientes') {
        $stmtEliminar = mysqli_prepare($conn, 'DELETE FROM Solicitud_Clientes WHERE SolicitudClienteID = ? LIMIT 1');
    } elseif ($tipo === 'productos') {
        $stmtEliminar = mysqli_prepare($conn, 'DELETE FROM Solicitud_Productos WHERE SolicitudProductoID = ? LIMIT 1');
    } else {
        responder(['success' => false, 'message' => 'Tipo de solicitud inválido.'], 400);
    }

    if (!$stmtEliminar) {
        responder(['success' => false, 'message' => 'No se pudo preparar la eliminación de la solicitud.'], 500);
    }

    mysqli_stmt_bind_param($stmtEliminar, 'i', $id);
    $eliminado = mysqli_stmt_execute($stmtEliminar);
    mysqli_stmt_close($stmtEliminar);

    if (!$eliminado) {
        responder(['success' => false, 'message' => 'No se pudo eliminar la solicitud.'], 500);
    }

    responder(['success' => true]);
}

if ($tipo === 'clientes') {
    $rs = mysqli_query($conn, "SELECT SolicitudClienteID id, NumeroCliente valor, FechaSolicitud fecha FROM Solicitud_Clientes WHERE Atendida = 0 ORDER BY SolicitudClienteID DESC");
} elseif ($tipo === 'productos') {
    $rs = mysqli_query($conn, "SELECT SolicitudProductoID id, SKU valor, FechaSolicitud fecha FROM Solicitud_Productos WHERE Atendida = 0 ORDER BY SolicitudProductoID DESC");
} else responder(['success'=>false,'message'=>'Tipo de solicitud inválido.'],400);
$records=[]; if ($rs instanceof mysqli_result) { while($r=mysqli_fetch_assoc($rs)) $records[]=$r; }
responder(['success'=>true,'records'=>$records]);
