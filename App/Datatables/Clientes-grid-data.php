<?php
include("../../Connections/ConDB.php");

$requestData = $_REQUEST;

$columns = array(
    0 => 'CLIENTESIAN',
    1 => 'NombreCliente',
);

$sql = "SELECT * FROM clientes";
$query = mysqli_query($conn, $sql) or die("Clientes-grid-data.php: get employees");
$totalData = mysqli_num_rows($query);
$totalFiltered = $totalData;

$sql = "SELECT * FROM clientes WHERE 1=1 ";

if (!empty($requestData['search']['value'])) {
    $search_words = explode(' ', $requestData['search']['value']);
    $sql_words = array();
    foreach ($search_words as $word) {
        $sql_words[] = "(
            clientes.CLIENTESIAN LIKE '%" . $word . "%' OR
            clientes.CLCSIAN LIKE '%" . $word . "%' OR
            clientes.NombreCliente LIKE '%" . $word . "%'
        )";
    }
    $sql .= " AND " . implode(' AND ', $sql_words);
}

$query = mysqli_query($conn, $sql) or die("employee-grid-data.php: get employees");
$totalFiltered = mysqli_num_rows($query);

$sql .= " ORDER BY " . $columns[$requestData['order'][0]['column']] . " " . $requestData['order'][0]['dir'] . " LIMIT " . $requestData['start'] . " ," . $requestData['length'];
$query = mysqli_query($conn, $sql) or die("employee-grid-data.php: get employees");

$data = array();
while ($row = mysqli_fetch_array($query)) {
    $NumeroDeCredito = $row["CLCSIAN"] != NULL ? ' - ' . $row["CLCSIAN"] : '';

    $nestedData = array();
    $nestedData[] = '<a data-toggle="tooltip" data-bs-placement="bottom" title="Número de CONTADO - Número de CRÉDITO" data-html="true">' . $row["CLIENTESIAN"] . ' ' . $NumeroDeCredito . '</a>';
    $nestedData[] = '<strong >' . $row["NombreCliente"] . '</strong>';
    $nestedData[] = '
    <button type="button" class="btn btn-sm btn-primary waves-effect width-md waves-light" data-bs-toggle="modal" data-bs-target="#ModalEditarClientes" onclick="TomarDatosParaModalClientes(' . $row["CLIENTEID"] . ')"><i class="mdi mdi-pencil"></i>Editar</button>
    <button type="button" class="btn btn-sm btn-danger waves-effect width-md waves-light" data-bs-toggle="modal" data-bs-target="#ModalDeshabilitarClientes" onclick="TomarDatosParaModalClientes(' . $row["CLIENTEID"] . ')"><i class="mdi mdi-pencil"></i>Borrar</button>';
    $data[] = $nestedData;
}

$json_data = array(
    "draw" => intval($requestData['draw']),
    "recordsTotal" => intval($totalData),
    "recordsFiltered" => intval($totalFiltered),
    "data" => $data
);

echo json_encode($json_data);
