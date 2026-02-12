<?php
include('../../Connections/ConDB.php');

header('Content-Type: application/json; charset=utf-8');

function responder($payload)
{
    echo json_encode($payload);
    exit;
}

function obtenerTablaPorTipo($tipo)
{
    $mapa = [
        'aduanas' => 'aduana',
        'vendedor' => 'vendedor',
        'surtidor' => 'Surtidor',
        'almacenista' => 'almacenista',
    ];

    return $mapa[$tipo] ?? '';
}

function cargarColumnasTabla($conn, $tabla)
{
    $columnas = [];
    $resultado = @mysqli_query($conn, 'SHOW COLUMNS FROM `' . str_replace('`', '``', $tabla) . '`');

    if ($resultado instanceof mysqli_result) {
        while ($fila = mysqli_fetch_assoc($resultado)) {
            $nombre = (string)($fila['Field'] ?? '');
            if ($nombre !== '') {
                $columnas[] = $nombre;
            }
        }
        mysqli_free_result($resultado);
    }

    return $columnas;
}

function primerMatch(array $columnas, array $candidatas)
{
    foreach ($candidatas as $candidata) {
        foreach ($columnas as $columna) {
            if (strcasecmp($columna, $candidata) === 0) {
                return $columna;
            }
        }
    }

    return '';
}

function resolverConfiguracionTabla($conn, $tipo)
{
    $tabla = obtenerTablaPorTipo($tipo);
    if ($tabla === '') {
        return null;
    }

    $columnas = cargarColumnasTabla($conn, $tabla);
    if (empty($columnas)) {
        return null;
    }

    $columnaId = primerMatch($columnas, [
        'AduanaID',
        'vendedorID',
        'VendedorID',
        'SurtidorID',
        'AlmacenistaID',
        'ID',
    ]);

    if ($columnaId === '') {
        foreach ($columnas as $columna) {
            if (preg_match('/id$/i', $columna)) {
                $columnaId = $columna;
                break;
            }
        }
    }

    $columnaNombre = primerMatch($columnas, [
        'NombreAduana',
        'NombreVendedor',
        'NombreSurtidor',
        'NombreAlmacenista',
        'Nombre',
    ]);

    if ($columnaNombre === '') {
        foreach ($columnas as $columna) {
            if (stripos($columna, 'nombre') !== false) {
                $columnaNombre = $columna;
                break;
            }
        }
    }

    $columnaEstatus = primerMatch($columnas, [
        'Deshabilitado',
        'Inhabilitado',
        'Habilitado',
        'Activo',
    ]);

    if ($columnaId === '' || $columnaNombre === '') {
        return null;
    }

    return [
        'tabla' => $tabla,
        'id' => $columnaId,
        'nombre' => $columnaNombre,
        'estatus' => $columnaEstatus,
    ];
}

function estaActivo($valor, $columnaEstatus)
{
    if ($columnaEstatus === '') {
        return true;
    }

    $valorEntero = (int)$valor;
    if (strcasecmp($columnaEstatus, 'Habilitado') === 0 || strcasecmp($columnaEstatus, 'Activo') === 0) {
        return $valorEntero === 1;
    }

    return $valorEntero === 0;
}

function valorToggle($valorActual, $columnaEstatus)
{
    $actual = (int)$valorActual;

    if (strcasecmp($columnaEstatus, 'Habilitado') === 0 || strcasecmp($columnaEstatus, 'Activo') === 0) {
        return $actual === 1 ? 0 : 1;
    }

    return $actual === 1 ? 0 : 1;
}

$action = isset($_POST['action']) ? trim((string)$_POST['action']) : '';
$tipo = isset($_POST['tipo']) ? trim((string)$_POST['tipo']) : '';
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$nombre = isset($_POST['nombre']) ? trim((string)$_POST['nombre']) : '';

$config = $tipo !== '' ? resolverConfiguracionTabla($conn, $tipo) : null;

if ($action === 'list') {
    if (!$config) {
        responder(['data' => []]);
    }

    $sql = 'SELECT `' . $config['id'] . '` AS RegistroID, `' . $config['nombre'] . '` AS NombreRegistro';
    if ($config['estatus'] !== '') {
        $sql .= ', `' . $config['estatus'] . '` AS EstatusRegistro';
    }
    $sql .= ' FROM `' . $config['tabla'] . '` ORDER BY `' . $config['nombre'] . '` ASC';

    $resultado = mysqli_query($conn, $sql);
    if (!$resultado) {
        responder(['data' => []]);
    }

    $data = [];
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $valorEstatus = $config['estatus'] !== '' ? (int)($fila['EstatusRegistro'] ?? 0) : 0;
        $activo = estaActivo($valorEstatus, $config['estatus']);

        $badge = $activo
            ? '<span class="badge badge-success">Activo</span>'
            : '<span class="badge badge-danger">Inhabilitado</span>';

        $idFila = (int)$fila['RegistroID'];
        $nombreSeguro = htmlspecialchars((string)$fila['NombreRegistro'], ENT_QUOTES, 'UTF-8');
        $tipoSeguro = htmlspecialchars($tipo, ENT_QUOTES, 'UTF-8');

        $botonEstadoClase = $activo ? 'btn-warning' : 'btn-success';
        $botonEstadoTexto = $activo ? 'Inhabilitar' : 'Habilitar';

        $acciones = '';
        $acciones .= '<button type="button" class="btn btn-sm btn-primary waves-effect width-md waves-light btn-editar-personal" data-id="' . $idFila . '" data-tipo="' . $tipoSeguro . '"><i class="mdi mdi-pencil"></i>Editar</button> ';

        if ($config['estatus'] !== '') {
            $acciones .= '<button type="button" class="btn btn-sm ' . $botonEstadoClase . ' waves-effect width-md waves-light btn-estado-personal" data-id="' . $idFila . '" data-tipo="' . $tipoSeguro . '" data-nombre="' . $nombreSeguro . '" data-deshabilitado="' . ($activo ? '0' : '1') . '">' . $botonEstadoTexto . '</button> ';
        }

        $acciones .= '<button type="button" class="btn btn-sm btn-outline-danger waves-effect width-md waves-light btn-eliminar-personal" data-id="' . $idFila . '" data-tipo="' . $tipoSeguro . '" data-nombre="' . $nombreSeguro . '"><i class="mdi mdi-delete"></i>Eliminar</button>';

        $data[] = [
            'PERSONALID' => $idFila,
            'Nombre' => (string)$fila['NombreRegistro'],
            'Badge' => $badge,
            'Acciones' => $acciones,
        ];
    }

    mysqli_free_result($resultado);
    responder(['data' => $data]);
}

if ($action === 'add') {
    if (!$config || $nombre === '') {
        responder(['success' => false]);
    }

    $sql = 'INSERT INTO `' . $config['tabla'] . '` (`' . $config['nombre'] . '`) VALUES (?)';
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        responder(['success' => false]);
    }

    mysqli_stmt_bind_param($stmt, 's', $nombre);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    responder(['success' => (bool)$ok]);
}

if ($action === 'get') {
    if ($id <= 0 || !$config) {
        responder(['success' => false]);
    }

    $sql = 'SELECT `' . $config['id'] . '` AS RegistroID, `' . $config['nombre'] . '` AS NombreRegistro FROM `' . $config['tabla'] . '` WHERE `' . $config['id'] . '` = ? LIMIT 1';
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        responder(['success' => false]);
    }

    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    $fila = mysqli_fetch_assoc($resultado);
    mysqli_stmt_close($stmt);

    if (!$fila) {
        responder(['success' => false]);
    }

    responder([
        'success' => true,
        'item' => [
            'PERSONALID' => (int)$fila['RegistroID'],
            'Nombre' => (string)$fila['NombreRegistro'],
        ],
    ]);
}

if ($action === 'update') {
    if ($id <= 0 || !$config || $nombre === '') {
        responder(['success' => false]);
    }

    $sql = 'UPDATE `' . $config['tabla'] . '` SET `' . $config['nombre'] . '` = ? WHERE `' . $config['id'] . '` = ?';
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        responder(['success' => false]);
    }

    mysqli_stmt_bind_param($stmt, 'si', $nombre, $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    responder(['success' => (bool)$ok]);
}

if ($action === 'toggle') {
    if ($id <= 0 || !$config || $config['estatus'] === '') {
        responder(['success' => false]);
    }

    $sqlActual = 'SELECT `' . $config['estatus'] . '` FROM `' . $config['tabla'] . '` WHERE `' . $config['id'] . '` = ? LIMIT 1';
    $stmtActual = mysqli_prepare($conn, $sqlActual);
    if (!$stmtActual) {
        responder(['success' => false]);
    }

    mysqli_stmt_bind_param($stmtActual, 'i', $id);
    mysqli_stmt_execute($stmtActual);
    $resultadoActual = mysqli_stmt_get_result($stmtActual);
    $filaActual = mysqli_fetch_row($resultadoActual);
    mysqli_stmt_close($stmtActual);

    if (!$filaActual) {
        responder(['success' => false]);
    }

    $nuevoValor = valorToggle((int)$filaActual[0], $config['estatus']);
    $sql = 'UPDATE `' . $config['tabla'] . '` SET `' . $config['estatus'] . '` = ? WHERE `' . $config['id'] . '` = ?';
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        responder(['success' => false]);
    }

    mysqli_stmt_bind_param($stmt, 'ii', $nuevoValor, $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    responder(['success' => (bool)$ok]);
}

if ($action === 'delete') {
    if ($id <= 0 || !$config) {
        responder(['success' => false]);
    }

    $sql = 'DELETE FROM `' . $config['tabla'] . '` WHERE `' . $config['id'] . '` = ?';
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        responder(['success' => false]);
    }

    mysqli_stmt_bind_param($stmt, 'i', $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    responder(['success' => (bool)$ok]);
}

responder(['success' => false]);
