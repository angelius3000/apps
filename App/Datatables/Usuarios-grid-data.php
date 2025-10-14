<?php
include("../../Connections/ConDB.php");
// include("../../includes/Funciones.php");

// if (!isset($_SESSION)) {
//     session_start();
// }

// storing  request (ie, get/post) global array to a variable  
$requestData = $_REQUEST;

$secciones = [];
$resultSecciones = mysqli_query($conn, "SELECT SECCIONID, Nombre FROM secciones ORDER BY Orden, Nombre");
if ($resultSecciones) {
    while ($seccion = mysqli_fetch_assoc($resultSecciones)) {
        $secciones[] = $seccion;
    }
    mysqli_free_result($resultSecciones);
}

$columns = array(
    // datatable column index  => database column name
    0 => 'usuarios.ApellidoPaterno',
    1 => 'usuarios.email',
    2 => 'tipodeusuarios.TipoDeUsuario',
    3 => 'clientes.NombreCliente'
);

$baseColumns = count($columns);
foreach ($secciones as $indice => $seccion) {
    $columns[$baseColumns + $indice] = 'Permiso' . (int)$seccion['SECCIONID'];
}

$columns[$baseColumns + count($secciones)] = 'usuarios.Deshabilitado';
$columns[$baseColumns + count($secciones) + 1] = 'acciones';

// getting total number records without any search
$sql = "SELECT * FROM usuarios 
        LEFT JOIN tipodeusuarios ON usuarios.TIPODEUSUARIOID = tipodeusuarios.TIPODEUSUARIOID
        LEFT JOIN clientes ON usuarios.CLIENTEID = clientes.CLIENTEID";
$query = mysqli_query($conn, $sql) or die("Usuario-grid-data.php: get employees");
$totalData = mysqli_num_rows($query);
$totalFiltered = $totalData;  // when there is no search parameter then total number rows = total number filtered rows.

$sql = "SELECT * FROM usuarios
LEFT JOIN tipodeusuarios ON usuarios.TIPODEUSUARIOID = tipodeusuarios.TIPODEUSUARIOID
LEFT JOIN clientes ON usuarios.CLIENTEID = clientes.CLIENTEID
WHERE 1=1 ";

if (!empty($requestData['search']['value'])) {
    $search_words = explode(' ', $requestData['search']['value']);
    $sql_words = array();
    foreach ($search_words as $word) {
        $sql_words[] = "(
            usuarios.PrimerNombre LIKE '%" . $word . "%' OR
            usuarios.SegundoNombre LIKE '%" . $word . "%' OR
            usuarios.ApellidoPaterno LIKE '%" . $word . "%' OR
            usuarios.ApellidoMaterno LIKE '%" . $word . "%' OR
            tipodeusuarios.TipoDeUsuario LIKE '%" . $word . "%' OR
            usuarios.email LIKE '%" . $word . "%' OR
            clientes.NombreCliente LIKE '%" . $word . "%'
        )";
    }
    $sql .= " AND " . implode(' AND ', $sql_words);
}

$query = mysqli_query($conn, $sql) or die("employee-grid-data.php: get employees");
$totalFiltered = mysqli_num_rows($query); // when there is a search parameter then we have to modify total number filtered rows as per search result. 

$orderColumnIndex = isset($requestData['order'][0]['column']) ? (int)$requestData['order'][0]['column'] : 0;
$orderColumn = isset($columns[$orderColumnIndex]) ? $columns[$orderColumnIndex] : 'usuarios.ApellidoPaterno';
$orderDir = isset($requestData['order'][0]['dir']) && in_array(strtolower($requestData['order'][0]['dir']), ['asc', 'desc'], true)
    ? $requestData['order'][0]['dir']
    : 'ASC';

$sql .= " ORDER BY " . $orderColumn . "   " . $orderDir . "  LIMIT " . $requestData['start'] . " ," . $requestData['length'] . "   ";
/* $requestData['order'][0]['column'] contains colmun index, $requestData['order'][0]['dir'] contains order such as asc/desc  */

$query = mysqli_query($conn, $sql) or die("employee-grid-data.php: get employees");

$data = array();

while ($row = mysqli_fetch_array($query)) {  // preparing an array ... Preparando el Arraigo


    if ($row["Deshabilitado"] == 0) {
        $BadgeActivo = '<span class="badge badge-success">Activo</span>';
    } else {
        $BadgeActivo = '<span class="badge badge-danger">Inhabilitado</span>';
    }

    $estaDeshabilitado = (int)$row["Deshabilitado"] === 1;
    $textoBotonEstado = $estaDeshabilitado ? 'Habilitar' : 'Deshabilitar';
    $claseBotonEstado = $estaDeshabilitado ? 'btn-success' : 'btn-danger';
    $iconoBotonEstado = $estaDeshabilitado ? 'mdi mdi-account-check' : 'mdi mdi-block-helper';

    if (!empty($row["CLIENTEID"])) {
        $Empresa = $row["NombreCliente"];
    } else {
        $Empresa = 'Edison';
    }


    $nestedData = array();

    $nestedData[] = htmlspecialchars(trim($row["ApellidoPaterno"] . ' ' . $row["ApellidoMaterno"] . ' ' . $row["PrimerNombre"] . ' ' . $row["SegundoNombre"]), ENT_QUOTES, 'UTF-8');
    $nestedData[] = htmlspecialchars($row["email"], ENT_QUOTES, 'UTF-8');
    $nestedData[] = htmlspecialchars($row["TipoDeUsuario"], ENT_QUOTES, 'UTF-8');
    $nestedData[] = htmlspecialchars($Empresa, ENT_QUOTES, 'UTF-8');

    $permisosUsuario = [];
    $consultaPermisos = mysqli_query($conn, "SELECT SECCIONID, PuedeVer FROM usuario_secciones WHERE USUARIOID = " . (int)$row["USUARIOID"]);
    if ($consultaPermisos) {
        while ($permiso = mysqli_fetch_assoc($consultaPermisos)) {
            $permisosUsuario[(int)$permiso['SECCIONID']] = (int)$permiso['PuedeVer'];
        }
        mysqli_free_result($consultaPermisos);
    }

    foreach ($secciones as $seccion) {
        $seccionId = (int)$seccion['SECCIONID'];
        $puedeVer = isset($permisosUsuario[$seccionId]) ? (int)$permisosUsuario[$seccionId] === 1 : true;
        $checkbox = '<div class="form-check form-switch m-0"><input type="checkbox" class="form-check-input usuario-seccion-toggle" data-usuario="' . (int)$row["USUARIOID"] . '" data-seccion="' . $seccionId . '"' . ($puedeVer ? ' checked' : '') . '></div>';
        $nestedData[] = $checkbox;
    }

    $nestedData[] = $BadgeActivo;
    $nestedData[] = '

    <button type="button" class="btn btn-sm btn-primary waves-effect width-md waves-light" data-bs-toggle="modal" data-bs-target="#ModalEditarUsuarios" onclick="TomarDatosParaModalUsuarios(' . $row["USUARIOID"] . ')"><i class="mdi mdi-pencil"></i>Editar</button>

    <button type="button" class="btn btn-sm ' . $claseBotonEstado . ' waves-effect width-md waves-light" data-bs-toggle="modal" data-bs-target="#ModalDeshabilitarUsuarios" onclick="TomarDatosParaModalUsuarios(' . $row["USUARIOID"] . ')"><i class="' . $iconoBotonEstado . '"></i>' . $textoBotonEstado . '</button>

    <button type="button" class="btn btn-sm btn-outline-danger waves-effect width-md waves-light" data-bs-toggle="modal" data-bs-target="#ModalBorrarUsuarios" onclick="TomarDatosParaModalUsuarios(' . $row["USUARIOID"] . ')"><i class="mdi mdi-delete"></i>Eliminar</button>
    ';

    $data[] = $nestedData;
}

$json_data = array(
    "draw" => intval($requestData['draw']),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw. 
    "recordsTotal"    => intval($totalData),  // total number of records
    "recordsFiltered" => intval($totalFiltered), // total number of records after searching, if there is no searching then totalFiltered = totalData
    "data"            => $data  // total data array,

);

echo json_encode($json_data);  // send data as json format
