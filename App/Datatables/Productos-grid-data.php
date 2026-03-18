<?php
include("../../Connections/ConDB.php");

$requestData = $_REQUEST;

$columns = array(
    0 => 'Sku',
    1 => 'Descripcion',
    2 => 'MarcaProductos',
);

$sql = "SELECT * FROM productos";
$query = mysqli_query($conn, $sql) or die("Productos-grid-data.php: get productos");
$totalData = mysqli_num_rows($query);
$totalFiltered = $totalData;

$sql = "SELECT * FROM productos WHERE 1=1 ";

if (!empty($requestData['search']['value'])) {
    $search_words = explode(' ', $requestData['search']['value']);
    $sql_words = array();
    foreach ($search_words as $word) {
        $word = mysqli_real_escape_string($conn, $word);
        $sql_words[] = "(
            productos.Sku LIKE '%" . $word . "%' OR
            productos.Descripcion LIKE '%" . $word . "%' OR
            productos.MarcaProductos LIKE '%" . $word . "%'
        )";
    }
    $sql .= " AND " . implode(' AND ', $sql_words);
}

$query = mysqli_query($conn, $sql) or die("Productos-grid-data.php: get productos");
$totalFiltered = mysqli_num_rows($query);

$sql .= " ORDER BY " . $columns[$requestData['order'][0]['column']] . " " . $requestData['order'][0]['dir'] . " LIMIT " . $requestData['start'] . " ," . $requestData['length'];
$query = mysqli_query($conn, $sql) or die("Productos-grid-data.php: get productos");

$data = array();
while ($row = mysqli_fetch_array($query)) {
    $nestedData = array();
    $nestedData[] = '<strong>' . htmlspecialchars((string)$row["Sku"], ENT_QUOTES, 'UTF-8') . '</strong>';
    $nestedData[] = htmlspecialchars((string)$row["Descripcion"], ENT_QUOTES, 'UTF-8');
    $nestedData[] = htmlspecialchars((string)$row["MarcaProductos"], ENT_QUOTES, 'UTF-8');
    $nestedData[] = '
    <button type="button" class="btn btn-sm btn-primary waves-effect width-md waves-light" data-bs-toggle="modal" data-bs-target="#ModalEditarProductos" onclick="TomarDatosParaModalProductos(' . $row["PRODUCTOSID"] . ')"><i class="mdi mdi-pencil"></i>Editar</button>
    <button type="button" class="btn btn-sm btn-danger waves-effect width-md waves-light" data-bs-toggle="modal" data-bs-target="#ModalBorrarProductos" onclick="TomarDatosParaModalProductos(' . $row["PRODUCTOSID"] . ')"><i class="mdi mdi-delete"></i>Borrar</button>';
    $data[] = $nestedData;
}

$json_data = array(
    "draw" => intval($requestData['draw']),
    "recordsTotal" => intval($totalData),
    "recordsFiltered" => intval($totalFiltered),
    "data" => $data
);

echo json_encode($json_data);
