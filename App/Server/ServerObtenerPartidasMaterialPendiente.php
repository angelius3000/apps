<?php

include("../../Connections/ConDB.php");

header('Content-Type: application/json');

function responderError(string $mensaje, int $codigo = 500): void
{
    http_response_code($codigo);
    echo json_encode(['success' => false, 'message' => $mensaje]);
    exit;
}

function asegurarTablaEntregas(mysqli $conn, string $baseDatos): void
{
    $crearTablaSQL = "CREATE TABLE IF NOT EXISTS materialpendiente_entregas (\n        EntregaID INT NOT NULL AUTO_INCREMENT,\n        MaterialPendienteID INT NOT NULL,\n        FolioID INT NOT NULL,\n        Documento VARCHAR(100) NOT NULL,\n        CantidadEntregada INT NOT NULL,\n        Recibio VARCHAR(255) NOT NULL,\n        AduanaEntrega VARCHAR(255) NOT NULL,\n        SkuEntrega VARCHAR(100) DEFAULT NULL,\n        DescripcionEntrega VARCHAR(255) DEFAULT NULL,\n        FechaEntrega TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,\n        PRIMARY KEY (EntregaID),\n        INDEX idx_entrega_material (MaterialPendienteID),\n        INDEX idx_entrega_documento (Documento)\n    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    @mysqli_query($conn, $crearTablaSQL);

    $columnasRequeridas = [
        'SkuEntrega' => "ALTER TABLE materialpendiente_entregas ADD COLUMN SkuEntrega VARCHAR(100) DEFAULT NULL AFTER AduanaEntrega",
        'DescripcionEntrega' => "ALTER TABLE materialpendiente_entregas ADD COLUMN DescripcionEntrega VARCHAR(255) DEFAULT NULL AFTER SkuEntrega",
    ];

    foreach ($columnasRequeridas as $columna => $sqlAlter) {
        $stmtColumna = mysqli_prepare(
            $conn,
            'SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ? LIMIT 1'
        );

        if ($stmtColumna) {
            $tabla = 'materialpendiente_entregas';
            mysqli_stmt_bind_param($stmtColumna, 'sss', $baseDatos, $tabla, $columna);
            mysqli_stmt_execute($stmtColumna);
            mysqli_stmt_store_result($stmtColumna);

            if (mysqli_stmt_num_rows($stmtColumna) === 0) {
                @mysqli_query($conn, $sqlAlter);
            }

            mysqli_stmt_close($stmtColumna);
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    responderError('Método no permitido.', 405);
}

if (!$conn) {
    responderError('No se pudo conectar a la base de datos.');
}

$nombreBaseDatos = $dbname ?? '';
asegurarTablaEntregas($conn, $nombreBaseDatos);
if ($nombreBaseDatos !== '') {
    $stmtColumna = mysqli_prepare(
        $conn,
        'SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ? LIMIT 1'
    );

    if ($stmtColumna) {
        $tabla = 'facturamp';
        $columna = 'ActivoFMP';
        mysqli_stmt_bind_param($stmtColumna, 'sss', $nombreBaseDatos, $tabla, $columna);
        mysqli_stmt_execute($stmtColumna);
        mysqli_stmt_store_result($stmtColumna);
        if (mysqli_stmt_num_rows($stmtColumna) === 0) {
            @mysqli_query($conn, "ALTER TABLE facturamp ADD COLUMN ActivoFMP TINYINT(1) NOT NULL DEFAULT 1 AFTER AduanaFMP");
        }
        mysqli_stmt_close($stmtColumna);
    }

    $stmtColumna = mysqli_prepare(
        $conn,
        'SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ? LIMIT 1'
    );

    if ($stmtColumna) {
        $tabla = 'materialpendiente';
        $columna = 'ActivoMP';
        mysqli_stmt_bind_param($stmtColumna, 'sss', $nombreBaseDatos, $tabla, $columna);
        mysqli_stmt_execute($stmtColumna);
        mysqli_stmt_store_result($stmtColumna);
        if (mysqli_stmt_num_rows($stmtColumna) === 0) {
            @mysqli_query($conn, "ALTER TABLE materialpendiente ADD COLUMN ActivoMP TINYINT(1) NOT NULL DEFAULT 1 AFTER FechaMP");
        }
        mysqli_stmt_close($stmtColumna);
    }
}

$folio = isset($_GET['folio']) ? (int) $_GET['folio'] : 0;

if ($folio <= 0) {
    responderError('Folio inválido.', 400);
}

$stmtFactura = mysqli_prepare(
    $conn,
    'SELECT FacturaMPID, FechaFMP, DocumentoFMP, RazonSocialFMP, VendedorFMP, SurtidorFMP, ClienteFMP, AduanaFMP ' .
        'FROM facturamp WHERE FacturaMPID = ? AND ActivoFMP = 1 LIMIT 1'
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
    'SELECT MaterialPendienteID, SkuMP, DescripcionMP, CantidadMP FROM materialpendiente WHERE DocumentoMP = ? AND ActivoMP = 1 ORDER BY MaterialPendienteID ASC'
);

if (!$stmtPartidas) {
    responderError('No se pudo obtener las partidas pendientes.');
}

mysqli_stmt_bind_param($stmtPartidas, 's', $documento);
mysqli_stmt_execute($stmtPartidas);
mysqli_stmt_bind_result($stmtPartidas, $partidaId, $sku, $descripcion, $cantidad);

$partidas = [];
while (mysqli_stmt_fetch($stmtPartidas)) {
    $partidas[] = [
        'id' => (int) $partidaId,
        'sku' => $sku,
        'descripcion' => $descripcion,
        'cantidad' => (int) $cantidad
    ];
}

mysqli_stmt_close($stmtPartidas);

$entregas = [];

$stmtEntregas = mysqli_prepare(
    $conn,
    'SELECT EntregaID, MaterialPendienteID, CantidadEntregada, Recibio, AduanaEntrega, FechaEntrega, SkuEntrega, DescripcionEntrega FROM materialpendiente_entregas WHERE FolioID = ? ORDER BY FechaEntrega DESC, EntregaID DESC'
);

if ($stmtEntregas) {
    mysqli_stmt_bind_param($stmtEntregas, 'i', $folio);
    mysqli_stmt_execute($stmtEntregas);
    mysqli_stmt_bind_result($stmtEntregas, $entregaId, $materialIdEntrega, $cantidadEntrega, $recibioEntrega, $aduanaEntrega, $fechaEntregaDb, $skuEntrega, $descripcionEntrega);

    while (mysqli_stmt_fetch($stmtEntregas)) {
        $fechaEntregaFormateada = '';
        if (!empty($fechaEntregaDb)) {
            $marcaEntrega = strtotime((string) $fechaEntregaDb);
            if ($marcaEntrega !== false) {
                $fechaEntregaFormateada = date('d/m/y H:i', $marcaEntrega);
            }
        }

        $entregas[] = [
            'id' => (int) $entregaId,
            'partidaId' => (int) $materialIdEntrega,
            'cantidad' => (int) $cantidadEntrega,
            'recibio' => $recibioEntrega,
            'aduana' => $aduanaEntrega,
            'fecha' => $fechaEntregaFormateada,
            'sku' => $skuEntrega,
            'descripcion' => $descripcionEntrega,
        ];
    }

    mysqli_stmt_close($stmtEntregas);
}

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
    'entregas' => $entregas,
]);
