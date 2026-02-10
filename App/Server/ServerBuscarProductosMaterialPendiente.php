<?php

include("../../Connections/ConDB.php");

header('Content-Type: application/json; charset=utf-8');

function responder(array $respuesta, int $codigo = 200): void
{
    http_response_code($codigo);
    echo json_encode($respuesta);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    responder(['results' => [], 'pagination' => ['more' => false]], 405);
}

if (!$conn) {
    responder(['results' => [], 'pagination' => ['more' => false]], 500);
}

$termino = trim((string) ($_GET['term'] ?? ''));
$pagina = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$pagina = $pagina > 0 ? $pagina : 1;
$tamanoPagina = 40;
$offset = ($pagina - 1) * $tamanoPagina;

if (mb_strlen($termino) < 3) {
    responder(['results' => [], 'pagination' => ['more' => false]]);
}

$terminoLike = '%' . $termino . '%';
$limiteConsulta = $tamanoPagina + 1;

$sql = 'SELECT PRODUCTOSID, Sku, Descripcion
        FROM productos
        WHERE Sku LIKE ? OR Descripcion LIKE ?
        ORDER BY Sku ASC, Descripcion ASC
        LIMIT ? OFFSET ?';

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    responder(['results' => [], 'pagination' => ['more' => false]], 500);
}

mysqli_stmt_bind_param($stmt, 'ssii', $terminoLike, $terminoLike, $limiteConsulta, $offset);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);

$productos = [];
if ($resultado instanceof mysqli_result) {
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $productos[] = [
            'id' => isset($fila['PRODUCTOSID']) ? (int) $fila['PRODUCTOSID'] : 0,
            'sku' => isset($fila['Sku']) ? trim((string) $fila['Sku']) : '',
            'descripcion' => isset($fila['Descripcion']) ? trim((string) $fila['Descripcion']) : ''
        ];
    }
    mysqli_free_result($resultado);
}

mysqli_stmt_close($stmt);

$tieneMas = count($productos) > $tamanoPagina;
if ($tieneMas) {
    array_pop($productos);
}

$respuesta = array_map(function ($producto) {
    $texto = '';

    if ($producto['sku'] !== '' && $producto['descripcion'] !== '') {
        $texto = $producto['sku'] . ' - ' . $producto['descripcion'];
    } elseif ($producto['sku'] !== '') {
        $texto = $producto['sku'];
    } else {
        $texto = $producto['descripcion'];
    }

    return [
        'id' => $producto['id'],
        'text' => $texto,
        'sku' => $producto['sku'],
        'descripcion' => $producto['descripcion']
    ];
}, $productos);

responder([
    'results' => $respuesta,
    'pagination' => [
        'more' => $tieneMas
    ]
]);
