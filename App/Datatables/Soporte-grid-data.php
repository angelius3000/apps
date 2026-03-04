<?php
include("../../Connections/ConDB.php");

if (!isset($_SESSION)) {
    session_start();
}

$requestData = $_REQUEST;

$tipo = strtolower(trim((string)($_SESSION['TipoDeUsuario'] ?? '')));
$tipoUsuarioSesionId = (int)($_SESSION['TIPOUSUARIO'] ?? 0);

if ($tipo === '' && $tipoUsuarioSesionId > 0) {
    $consultaTipoUsuario = mysqli_prepare(
        $conn,
        'SELECT TipoDeUsuario FROM tipodeusuarios WHERE TIPODEUSUARIOID = ? LIMIT 1'
    );

    if ($consultaTipoUsuario) {
        mysqli_stmt_bind_param($consultaTipoUsuario, 'i', $tipoUsuarioSesionId);
        mysqli_stmt_execute($consultaTipoUsuario);
        mysqli_stmt_bind_result($consultaTipoUsuario, $tipoUsuarioRecuperado);

        if (mysqli_stmt_fetch($consultaTipoUsuario)) {
            $tipo = strtolower(trim((string) $tipoUsuarioRecuperado));
        }

        mysqli_stmt_close($consultaTipoUsuario);
    }
}

$esAdmin = in_array($tipo, ['soporte it', 'administrador'], true);
$usuarioIdSesion = (int)($_SESSION['USUARIOID'] ?? 0);

$columns = array(
    0 => 't.Folio',
    1 => 't.Titulo',
    2 => 't.Prioridad',
    3 => 't.Categoria',
    4 => 't.STATUS',
    5 => 'creador',
    6 => 'asignado',
    7 => 't.FechaCreacion',
    8 => 'acciones'
);

$filtroUsuario = '';
if (!$esAdmin) {
    $filtroUsuario = ' AND t.USUARIOID_CREADOR = ' . $usuarioIdSesion;
}

$sql = "SELECT t.TICKETID
        FROM tickets t
        LEFT JOIN usuarios u1 ON t.USUARIOID_CREADOR = u1.USUARIOID
        LEFT JOIN usuarios u2 ON t.USUARIOID_ASIGNADO = u2.USUARIOID
        WHERE 1=1 " . $filtroUsuario;
$query = mysqli_query($conn, $sql) or die("Soporte-grid-data.php: get tickets");
$totalData = mysqli_num_rows($query);
$totalFiltered = $totalData;

$sql = "SELECT t.*,
        CONCAT_WS(' ', u1.PrimerNombre, u1.ApellidoPaterno) AS creador,
        CONCAT_WS(' ', u2.PrimerNombre, u2.ApellidoPaterno) AS asignado
        FROM tickets t
        LEFT JOIN usuarios u1 ON t.USUARIOID_CREADOR = u1.USUARIOID
        LEFT JOIN usuarios u2 ON t.USUARIOID_ASIGNADO = u2.USUARIOID
        WHERE 1=1 " . $filtroUsuario;

if (!empty($requestData['search']['value'])) {
    $search_words = explode(' ', $requestData['search']['value']);
    $sql_words = array();

    foreach ($search_words as $word) {
        $word = mysqli_real_escape_string($conn, trim($word));
        if ($word === '') {
            continue;
        }

        $sql_words[] = "(
            t.Folio LIKE '%" . $word . "%' OR
            t.Titulo LIKE '%" . $word . "%' OR
            t.Prioridad LIKE '%" . $word . "%' OR
            t.Categoria LIKE '%" . $word . "%' OR
            t.STATUS LIKE '%" . $word . "%' OR
            CONCAT_WS(' ', u1.PrimerNombre, u1.ApellidoPaterno) LIKE '%" . $word . "%' OR
            CONCAT_WS(' ', u2.PrimerNombre, u2.ApellidoPaterno) LIKE '%" . $word . "%'
        )";
    }

    if (!empty($sql_words)) {
        $sql .= " AND " . implode(' AND ', $sql_words);
    }
}

$query = mysqli_query($conn, $sql) or die("Soporte-grid-data.php: filtered tickets");
$totalFiltered = mysqli_num_rows($query);

$orderColumnIndex = isset($requestData['order'][0]['column']) ? (int)$requestData['order'][0]['column'] : 7;
$orderColumn = isset($columns[$orderColumnIndex]) ? $columns[$orderColumnIndex] : 't.FechaCreacion';
$orderDir = isset($requestData['order'][0]['dir']) && in_array(strtolower($requestData['order'][0]['dir']), array('asc', 'desc'), true)
    ? $requestData['order'][0]['dir']
    : 'DESC';

$sql .= " ORDER BY " . $orderColumn . " " . $orderDir . " LIMIT " . (int)$requestData['start'] . "," . (int)$requestData['length'];
$query = mysqli_query($conn, $sql) or die("Soporte-grid-data.php: paginated tickets");

$data = array();

while ($row = mysqli_fetch_array($query)) {
    $nestedData = array();

    $creador = trim((string)$row['creador']);
    $asignado = trim((string)$row['asignado']);

    $nestedData[] = htmlspecialchars($row['Folio'], ENT_QUOTES, 'UTF-8');
    $nestedData[] = htmlspecialchars($row['Titulo'], ENT_QUOTES, 'UTF-8');
    $nestedData[] = htmlspecialchars($row['Prioridad'], ENT_QUOTES, 'UTF-8');
    $nestedData[] = htmlspecialchars($row['Categoria'], ENT_QUOTES, 'UTF-8');
    $nestedData[] = htmlspecialchars($row['STATUS'], ENT_QUOTES, 'UTF-8');
    $nestedData[] = htmlspecialchars($creador, ENT_QUOTES, 'UTF-8');
    $nestedData[] = htmlspecialchars($asignado, ENT_QUOTES, 'UTF-8');
    $nestedData[] = htmlspecialchars((string)$row['FechaCreacion'], ENT_QUOTES, 'UTF-8');

    $acciones = '<button type="button" class="btn btn-sm btn-primary waves-effect width-md waves-light" data-bs-toggle="modal" data-bs-target="#ModalEditarTicket" onclick="TomarDatosParaModalTicket(' . (int)$row['TICKETID'] . ')"><i class="mdi mdi-pencil"></i>Editar</button>';

    if ($esAdmin) {
        $acciones .= ' <button type="button" class="btn btn-sm btn-danger waves-effect width-md waves-light" data-bs-toggle="modal" data-bs-target="#ModalCerrarTicket" onclick="TomarDatosParaModalTicket(' . (int)$row['TICKETID'] . ')"><i class="mdi mdi-lock"></i>Cerrar</button>';
    }

    $nestedData[] = $acciones;
    $data[] = $nestedData;
}

$json_data = array(
    "draw" => intval($requestData['draw']),
    "recordsTotal" => intval($totalData),
    "recordsFiltered" => intval($totalFiltered),
    "data" => $data,
);

echo json_encode($json_data);
