<?php
include('../../Connections/ConDB.php');

header('Content-Type: application/json; charset=utf-8');

function asegurarTablaPersonal($conn)
{
    mysqli_query(
        $conn,
        "CREATE TABLE IF NOT EXISTS personal (" .
        " PERSONALID INT NOT NULL AUTO_INCREMENT," .
        " TipoPersonal VARCHAR(30) NOT NULL," .
        " Nombre VARCHAR(150) NOT NULL," .
        " Deshabilitado TINYINT(1) NOT NULL DEFAULT 0," .
        " FechaRegistro TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP," .
        " PRIMARY KEY (PERSONALID)," .
        " KEY idx_tipopersonal (TipoPersonal)," .
        " KEY idx_deshabilitado (Deshabilitado)" .
        ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
}

function responder($payload)
{
    echo json_encode($payload);
    exit;
}

asegurarTablaPersonal($conn);

$action = isset($_POST['action']) ? trim((string)$_POST['action']) : '';
$tipo = isset($_POST['tipo']) ? trim((string)$_POST['tipo']) : '';
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$nombre = isset($_POST['nombre']) ? trim((string)$_POST['nombre']) : '';

$tiposPermitidos = ['aduanas', 'vendedor', 'surtidor', 'almacenista'];

if ($action === 'list') {
    if (!in_array($tipo, $tiposPermitidos, true)) {
        responder(['data' => []]);
    }

    $stmt = mysqli_prepare($conn, 'SELECT PERSONALID, TipoPersonal, Nombre, Deshabilitado FROM personal WHERE TipoPersonal = ? ORDER BY Nombre ASC');
    mysqli_stmt_bind_param($stmt, 's', $tipo);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);

    $data = [];
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $deshabilitado = (int)$fila['Deshabilitado'] === 1;
        $badge = $deshabilitado
            ? '<span class="badge badge-danger">Inhabilitado</span>'
            : '<span class="badge badge-success">Activo</span>';

        $idFila = (int)$fila['PERSONALID'];
        $nombreSeguro = htmlspecialchars((string)$fila['Nombre'], ENT_QUOTES, 'UTF-8');
        $tipoSeguro = htmlspecialchars((string)$fila['TipoPersonal'], ENT_QUOTES, 'UTF-8');

        $botonEstadoClase = $deshabilitado ? 'btn-success' : 'btn-warning';
        $botonEstadoTexto = $deshabilitado ? 'Habilitar' : 'Inhabilitar';

        $acciones = '';
        $acciones .= '<button type="button" class="btn btn-sm btn-primary waves-effect width-md waves-light btn-editar-personal" data-id="' . $idFila . '" data-tipo="' . $tipoSeguro . '"><i class="mdi mdi-pencil"></i>Editar</button> ';
        $acciones .= '<button type="button" class="btn btn-sm ' . $botonEstadoClase . ' waves-effect width-md waves-light btn-estado-personal" data-id="' . $idFila . '" data-tipo="' . $tipoSeguro . '" data-nombre="' . $nombreSeguro . '" data-deshabilitado="' . ($deshabilitado ? '1' : '0') . '">' . $botonEstadoTexto . '</button> ';
        $acciones .= '<button type="button" class="btn btn-sm btn-outline-danger waves-effect width-md waves-light btn-eliminar-personal" data-id="' . $idFila . '" data-tipo="' . $tipoSeguro . '" data-nombre="' . $nombreSeguro . '"><i class="mdi mdi-delete"></i>Eliminar</button>';

        $data[] = [
            'PERSONALID' => $idFila,
            'Nombre' => (string)$fila['Nombre'],
            'Badge' => $badge,
            'Acciones' => $acciones,
        ];
    }

    mysqli_stmt_close($stmt);

    responder(['data' => $data]);
}

if ($action === 'add') {
    if (!in_array($tipo, $tiposPermitidos, true) || $nombre === '') {
        responder(['success' => false]);
    }

    $stmt = mysqli_prepare($conn, 'INSERT INTO personal (TipoPersonal, Nombre, Deshabilitado) VALUES (?, ?, 0)');
    mysqli_stmt_bind_param($stmt, 'ss', $tipo, $nombre);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    responder(['success' => (bool)$ok]);
}

if ($action === 'get') {
    if ($id <= 0) {
        responder(['success' => false]);
    }

    $stmt = mysqli_prepare($conn, 'SELECT PERSONALID, TipoPersonal, Nombre, Deshabilitado FROM personal WHERE PERSONALID = ? LIMIT 1');
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    $fila = mysqli_fetch_assoc($resultado);
    mysqli_stmt_close($stmt);

    if (!$fila) {
        responder(['success' => false]);
    }

    responder(['success' => true, 'item' => $fila]);
}

if ($action === 'update') {
    if ($id <= 0 || $nombre === '') {
        responder(['success' => false]);
    }

    $stmt = mysqli_prepare($conn, 'UPDATE personal SET Nombre = ? WHERE PERSONALID = ?');
    mysqli_stmt_bind_param($stmt, 'si', $nombre, $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    responder(['success' => (bool)$ok]);
}

if ($action === 'toggle') {
    if ($id <= 0) {
        responder(['success' => false]);
    }

    $stmt = mysqli_prepare($conn, 'UPDATE personal SET Deshabilitado = CASE WHEN Deshabilitado = 1 THEN 0 ELSE 1 END WHERE PERSONALID = ?');
    mysqli_stmt_bind_param($stmt, 'i', $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    responder(['success' => (bool)$ok]);
}

if ($action === 'delete') {
    if ($id <= 0) {
        responder(['success' => false]);
    }

    $stmt = mysqli_prepare($conn, 'DELETE FROM personal WHERE PERSONALID = ?');
    mysqli_stmt_bind_param($stmt, 'i', $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    responder(['success' => (bool)$ok]);
}

responder(['success' => false]);
