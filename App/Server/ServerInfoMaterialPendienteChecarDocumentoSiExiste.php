<?php

include("../../Connections/ConDB.php");

header('Content-Type: application/json');

function responder(array $respuesta, int $codigo = 200): void
{
    http_response_code($codigo);
    echo json_encode($respuesta);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder(['success' => false, 'message' => 'Método no permitido.'], 405);
}

if (!$conn) {
    responder(['success' => false, 'message' => 'No se pudo conectar a la base de datos.'], 500);
}

$documento = trim((string) ($_POST['DocumentoFMP'] ?? ''));
$folio = isset($_POST['FolioPendiente']) ? (int) $_POST['FolioPendiente'] : 0;

if ($documento === '') {
    responder(['success' => true, 'exists' => false]);
}

$sqlCrearTablaFactura = "CREATE TABLE IF NOT EXISTS facturamp (
    FacturaMPID INT NOT NULL AUTO_INCREMENT,
    FechaFMP TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    DocumentoFMP VARCHAR(100) NOT NULL,
    RazonSocialFMP VARCHAR(255) NOT NULL,
    VendedorFMP VARCHAR(255) DEFAULT NULL,
    SurtidorFMP VARCHAR(255) DEFAULT NULL,
    ClienteFMP VARCHAR(255) NOT NULL,
    AduanaFMP VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY (FacturaMPID),
    INDEX idx_facturamp_documento (DocumentoFMP)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

@mysqli_query($conn, $sqlCrearTablaFactura);

if ($folio > 0) {
    $stmt = mysqli_prepare(
        $conn,
        'SELECT FacturaMPID FROM facturamp WHERE DocumentoFMP = ? AND FacturaMPID <> ? LIMIT 1'
    );
} else {
    $stmt = mysqli_prepare(
        $conn,
        'SELECT FacturaMPID FROM facturamp WHERE DocumentoFMP = ? LIMIT 1'
    );
}

if (!$stmt) {
    responder(['success' => false, 'message' => 'No se pudo validar el documento.'], 500);
}

if ($folio > 0) {
    mysqli_stmt_bind_param($stmt, 'si', $documento, $folio);
} else {
    mysqli_stmt_bind_param($stmt, 's', $documento);
}

mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $folioExistente);

$existe = false;
if (mysqli_stmt_fetch($stmt)) {
    $existe = true;
}

mysqli_stmt_close($stmt);

responder([
    'success' => true,
    'exists' => $existe,
    'folio' => $existe ? (int) $folioExistente : null,
    'documento' => $existe ? $documento : null
]);
