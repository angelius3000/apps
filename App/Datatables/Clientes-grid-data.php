<?php
include("../../Connections/ConDB.php");
// include("../../includes/Funciones.php");

// if (!isset($_SESSION)) {
//     session_start();
// }

// storing  request (ie, get/post) global array to a variable  
$requestData = $_REQUEST;

$columns = array(
    // datatable column index  => database column name
    0 => 'CLIENTESIAN',
    1 => 'NombreCliente',
    2 => 'EmailCliente',
    3 => 'TelefonoCliente',
    4 => 'NombreContacto',
    5 => 'DireccionCliente',
    6 => 'ColoniaCliente',
    7 => 'CiudadCliente',
    8 => 'EstadoCliente',
);

// getting total number records without any search
$sql = "SELECT * FROM clientes";
$query = mysqli_query($conn, $sql) or die("Clientes-grid-data.php: get employees");
$totalData = mysqli_num_rows($query);
$totalFiltered = $totalData;  // when there is no search parameter then total number rows = total number filtered rows.

$sql = "SELECT * FROM clientes
WHERE 1=1 ";

if (!empty($requestData['search']['value'])) {
    $search_words = explode(' ', $requestData['search']['value']);
    $sql_words = array();
    foreach ($search_words as $word) {
        $sql_words[] = "(
            clientes.CLIENTESIAN LIKE '%" . $word . "%' OR
            clientes.CLCSIAN LIKE '%" . $word . "%' OR
            clientes.NombreCliente LIKE '%" . $word . "%' OR
            clientes.EmailCliente LIKE '%" . $word . "%' OR
            clientes.TelefonoCliente LIKE '%" . $word . "%' OR
            clientes.NombreContacto LIKE '%" . $word . "%' OR
            clientes.DireccionCliente LIKE '%" . $word . "%' OR
            clientes.ColoniaCliente LIKE '%" . $word . "%' OR
            clientes.CiudadCliente LIKE '%" . $word . "%' OR
            clientes.EstadoCliente LIKE '%" . $word . "%'
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

    if (!empty($row["CLIENTEID"])) {
        $Empresa = $row["NombreCliente"];
    } else {
        $Empresa = 'Edison';
    }

    if ($row["CLCSIAN"] != NULL) {

        $NumeroDeCredito = ' - ' . $row["CLCSIAN"];
    } else {

        $NumeroDeCredito = '';
    }

    $nestedData = array();

    $nestedData[] = '<a data-toggle="tooltip" data-bs-placement="bottom"  title="Número de CONTADO - Número de CRÉDITO" data-html="true">' . $row["CLIENTESIAN"] . ' ' . $NumeroDeCredito . '</a>';
    $nestedData[] = '<strong >' . $row["NombreCliente"] . '</strong>';
    $nestedData[] = $row["EmailCliente"];
    $nestedData[] = $row["TelefonoCliente"];
    $nestedData[] = $row["NombreContacto"];
    $nestedData[] = $row["DireccionCliente"];
    $nestedData[] = $row["ColoniaCliente"];
    $nestedData[] = $row["CiudadCliente"];
    $nestedData[] = $row["EstadoCliente"];
    $nestedData[] = '

    <button type="button" class="btn btn-sm btn-primary waves-effect width-md waves-light" data-bs-toggle="modal" data-bs-target="#ModalEditarClientes" onclick="TomarDatosParaModalClientes(' . $row["CLIENTEID"] . ')"><i class="mdi mdi-pencil"></i>Editar</button>

    <button type="button" class="btn btn-sm btn-danger waves-effect width-md waves-light" data-bs-toggle="modal" data-bs-target="#ModalDeshabilitarClientes" onclick="TomarDatosParaModalClientes(' . $row["CLIENTEID"] . ')"><i class="mdi mdi-pencil"></i>Borrar</button>';

    $data[] = $nestedData;
}

$json_data = array(
    "draw" => intval($requestData['draw']),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw. 
    "recordsTotal"    => intval($totalData),  // total number of records
    "recordsFiltered" => intval($totalFiltered), // total number of records after searching, if there is no searching then totalFiltered = totalData
    "data"            => $data

);

echo json_encode($json_data);  // send data as json format
