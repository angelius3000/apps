<?php

include("../../Connections/ConDB.php");

header('Content-Type: application/json');

function responderError(string $mensaje, int $codigo = 500): void
{
    http_response_code($codigo);
    echo json_encode(['success' => false, 'message' => $mensaje]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    responderError('Método no permitido.', 405);
}

if (!$conn) {
    responderError('No se pudo conectar a la base de datos.');
}

$folio = isset($_GET['folio']) ? (int) $_GET['folio'] : 0;

if ($folio <= 0) {
    responderError('Folio inválido.', 400);
}

$stmtFactura = mysqli_prepare(
    $conn,
    'SELECT FacturaMPID, FechaFMP, DocumentoFMP, RazonSocialFMP, VendedorFMP, SurtidorFMP, ClienteFMP, AduanaFMP ' .
        'FROM facturamp WHERE FacturaMPID = ? LIMIT 1'
);

if (!$stmtFactura) {
    responderError('No se pudo obtener la información del folio.');
}

mysqli_stmt_bind_param($stmtFactura, 'i', $folio);
mysqli_stmt_execute($stmtFactura);
mysqli_stmt_bind_result(
    $stmtFactura,
    $facturaId,
    $fechaFolio,
    $documento,
    $razonSocial,
    $vendedor,
    $surtidor,
    $cliente,
    $aduana
);

$registroEncontrado = mysqli_stmt_fetch($stmtFactura);
mysqli_stmt_close($stmtFactura);

if (!$registroEncontrado) {
    responderError('No se encontró información para el folio solicitado.', 404);
}

$fechaFormateada = '';
if (!empty($fechaFolio)) {
    $marcaTemporal = strtotime((string) $fechaFolio);
    if ($marcaTemporal !== false) {
        $fechaFormateada = date('d/m/y H:i', $marcaTemporal);
    }
}

$stmtPartidas = mysqli_prepare(
    $conn,
    'SELECT SkuMP, DescripcionMP, CantidadMP FROM materialpendiente WHERE DocumentoMP = ? ORDER BY MaterialPendienteID ASC'
);

if (!$stmtPartidas) {
    responderError('No se pudo obtener las partidas pendientes.');
}

mysqli_stmt_bind_param($stmtPartidas, 's', $documento);
mysqli_stmt_execute($stmtPartidas);
mysqli_stmt_bind_result($stmtPartidas, $sku, $descripcion, $cantidad);

$partidas = [];
while (mysqli_stmt_fetch($stmtPartidas)) {
    $partidas[] = [
        'sku' => $sku,
        'descripcion' => $descripcion,
        'cantidad' => (int) $cantidad
    ];
}

mysqli_stmt_close($stmtPartidas);

echo json_encode([
    'success' => true,
    'factura' => [
        'folio' => $facturaId,
        'fecha' => $fechaFormateada,
        'documento' => $documento,
        'razonSocial' => $razonSocial,
        'vendedor' => $vendedor,
        'surtidor' => $surtidor,
        'cliente' => $cliente,
        'aduana' => $aduana,
    ],
    'partidas' => $partidas,
]);

