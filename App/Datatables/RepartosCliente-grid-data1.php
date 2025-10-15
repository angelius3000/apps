<?php
include("../../Connections/ConDB.php");
include("../../includes/Funciones.php");

if (!isset($_SESSION)) {
    session_start();
}

$tipoUsuarioActual = isset($_SESSION['TipoDeUsuario']) ? strtolower(trim((string) $_SESSION['TipoDeUsuario'])) : '';
$tiposPermitidosCambioEstatus = ['administrador', 'supervisor', 'auditor'];
$puedeCambiarEstatus = $tipoUsuarioActual !== '' && in_array($tipoUsuarioActual, $tiposPermitidosCambioEstatus, true);

// storing  request (ie, get/post) global array to a variable
$requestData = $_REQUEST;

$CLIENTEID = $_SESSION['CLIENTEID'];

$columns = array(
    // datatable column index  => database column name
    0 => 'REPARTOID',
    1 => 'STATUSID',
    2 => 'USUARIOID',
    3 => 'CLIENTEID',
    4 => 'Fecha',
    5 => 'Calle',
    6 => 'CP',
    7 => 'Receptor',
    8 => 'TelefonoDeReceptor',
    9 => 'TelefonoAlternativo',
    10 => 'NumeroFactura',
    11 => 'Comentarios',
    12 => ''


);

// getting total number records without any search
$sql = "SELECT * FROM repartos
LEFT JOIN usuarios ON usuarios.USUARIOID = repartos.USUARIOID
LEFT JOIN clientes ON clientes.CLIENTEID = repartos.CLIENTEID
LEFT JOIN status ON status.STATUSID = repartos.STATUSID
WHERE repartos.CLIENTEID = '$CLIENTEID'
";
$query = mysqli_query($conn, $sql) or die("Usuario-grid-data.php: get employees");
$totalData = mysqli_num_rows($query);
$totalFiltered = $totalData;  // when there is no search parameter then total number rows = total number filtered rows.

$sql = "SELECT * FROM repartos
LEFT JOIN usuarios ON usuarios.USUARIOID = repartos.USUARIOID
LEFT JOIN clientes ON clientes.CLIENTEID = repartos.CLIENTEID
LEFT JOIN status ON status.STATUSID = repartos.STATUSID
WHERE 1=1 AND repartos.CLIENTEID = '$CLIENTEID' ";

if (!empty($requestData['search']['value'])) {

    $search_words = explode(' ', $requestData['search']['value']);
    $sql_words = array();
    foreach ($search_words as $word) {
        $sql_words[] = "(
            usuarios.PrimerNombre LIKE '%" . $word . "%' OR
            usuarios.SegundoNombre LIKE '%" . $word . "%' OR
            usuarios.ApellidoPaterno LIKE '%" . $word . "%' OR
            usuarios.ApellidoMaterno LIKE '%" . $word . "%' OR
            clientes.NombreCliente LIKE '%" . $word . "%' OR
            repartos.REPARTOID LIKE '%" . $word . "%' OR
            repartos.FechaReparto LIKE '%" . $word . "%' OR
            repartos.Calle LIKE '%" . $word . "%' OR
            repartos.CP LIKE '%" . $word . "%' OR
            repartos.Receptor LIKE '%" . $word . "%' OR
            repartos.TelefonoDeReceptor LIKE '%" . $word . "%' OR
            repartos.TelefonoAlternativo LIKE '%" . $word . "%' OR
            repartos.NumeroDeFactura LIKE '%" . $word . "%' OR
            repartos.Comentarios LIKE '%" . $word . "%' OR
            usuarios.email LIKE '%" . $word . "%'
        )";
    }
    $sql .= " AND " . implode(' AND ', $sql_words);
}

$query = mysqli_query($conn, $sql) or die("employee-grid-data.php: get employees");
$totalFiltered = mysqli_num_rows($query); // when there is a search parameter then we have to modify total number filtered rows as per search result. 

$sql .= " ORDER BY " . $columns[$requestData['order'][0]['column']] . "   " . $requestData['order'][0]['dir'] . "  LIMIT " . $requestData['start'] . " ," . $requestData['length'] . "   ";
/* $requestData['order'][0]['column'] contains colmun index, $requestData['order'][0]['dir'] contains order such as asc/desc  */


$query = mysqli_query($conn, $sql) or die("employee-grid-data.php: get employees");

$data = array();



while ($row = mysqli_fetch_array($query)) {  // preparing an array ... Preparando el Arraigo

    if ($puedeCambiarEstatus) {
        $MandarModal = 'data-bs-toggle="modal" data-bs-target="#ModalCambioStatus" onclick="TomarDatosParaModalRepartos(' . $row["REPARTOID"] . ')"';
    } else {
        $MandarModal = '';
    }

    if ($row["STATUSID"] == 1) {
        $BadgeStatus = '<span class="badge badge-info" ' . $MandarModal . '>Registrado</span>';
    } else if ($row["STATUSID"] == 2) {
        $BadgeStatus = '<span class="badge badge-warning"  ' . $MandarModal . '>En tránsito</span>';
    } else if ($row["STATUSID"] == 3) {
        $BadgeStatus = '<span class="badge badge-dark"  ' . $MandarModal . '>Demorado</span>';
    } else if ($row["STATUSID"] == 4) {
        $BadgeStatus = '<span class="badge badge-secondary"  ' . $MandarModal . '>Surtiendo</span>';
    } else if ($row["STATUSID"] == 5) {
        $BadgeStatus = '<span class="badge badge-success" ' . $MandarModal . '>Entregado</span>';
    } else if ($row["STATUSID"] == 6) {
        $BadgeStatus = '<span class="badge badge-danger"  ' . $MandarModal . '>Cancelado</span>';
    } else if ($row["STATUSID"] == 7) {
        $BadgeStatus = '<span class="badge badge-successParcial"  ' . $MandarModal . '>Entrega Parcial</span>';
    } else if ($row["STATUSID"] == 8) {
        $BadgeStatus = '<span class="badge badge-success"  ' . $MandarModal . '>Recolectado</span>';
    }

    if ($row['USUARIOID'] == $_SESSION['USUARIOID'] || $_SESSION['TIPOUSUARIO'] == '1') {

        $BotonEditar = ' <button type="button" class="btn btn-sm btn-primary waves-effect width-md waves-light" data-bs-toggle="modal" data-bs-target="#ModalEditarReparto" onclick="TomarDatosParaModalRepartos(' . $row["REPARTOID"] . ')"><i class="mdi mdi-pencil"></i>Editar</button>';
    } else {
        $BotonEditar = '';
    }


    if (($row['USUARIOID'] == $_SESSION['USUARIOID']) && ($row['STATUSID'] == '1') || $_SESSION['TIPOUSUARIO'] == '1') {

        $BotonBorrar = '<button type="button" class="btn btn-sm btn-danger waves-effect width-md waves-light" data-bs-toggle="modal" data-bs-target="#ModalBorrarReparto" onclick="TomarDatosParaModalRepartos(' . $row["REPARTOID"] . ')"><i class="mdi mdi-pencil"></i>Borrar</button>';
    } else {
        $BotonBorrar = '';
    }

    $nestedData = array();

    $nestedData[] = '<strong>' . $row["REPARTOID"] . '</strong>';
    $nestedData[] = $BadgeStatus;
    $nestedData[] = $row["PrimerNombre"] . ' ' . $row["SegundoNombre"] . ' ' . $row["ApellidoPaterno"] . ' ' . $row["ApellidoMaterno"];
    $nestedData[] = $row["NombreCliente"];
    $nestedData[] =  SoloFecha($row["FechaDeRegistro"]);
    $nestedData[] = $row["Calle"] . ' ' . $row["NumeroEXT"] . ' ' . $row["Colonia"];
    $nestedData[] = $row["CP"];
    $nestedData[] = $row["Receptor"];
    $nestedData[] = $row["TelefonoDeReceptor"];
    $nestedData[] = $row["TelefonoAlternativo"];
    $nestedData[] = $row["NumeroDeFactura"];
    $nestedData[] = $row["Comentarios"];
    $nestedData[] = $BotonEditar . $BotonBorrar;

    $data[] = $nestedData;
}




$json_data = array(
    "draw" => intval($requestData['draw']),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw. 
    "recordsTotal"    => intval($totalData),  // total number of records
    "recordsFiltered" => intval($totalFiltered), // total number of records after searching, if there is no searching then totalFiltered = totalData
    "data"            => $data,   // total data array,
);

echo json_encode($json_data);  // send data as json format
