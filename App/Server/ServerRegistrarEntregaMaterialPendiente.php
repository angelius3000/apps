<?php

include("../../Connections/ConDB.php");

header('Content-Type: application/json');

function responderError(string $mensaje, int $codigo = 500): void
{
    http_response_code($codigo);
    echo json_encode(['success' => false, 'message' => $mensaje]);
    exit;
}

function referenciarValores(array &$array): array
{
    if (strnatcmp(phpversion(), '5.3') >= 0) {
        $referenciados = [];
        foreach ($array as $key => &$value) {
            $referenciados[$key] = &$value;
        }
        return $referenciados;
    }

    return $array;
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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderError('Método no permitido.', 405);
}

if (!$conn) {
    responderError('No se pudo conectar a la base de datos.');
}

asegurarTablaEntregas($conn, $dbname ?? '');

$folio = isset($_POST['folio']) ? (int) $_POST['folio'] : 0;
$documento = isset($_POST['documento']) ? trim((string) $_POST['documento']) : '';
$recibio = isset($_POST['recibio']) ? trim((string) $_POST['recibio']) : '';
$aduanaEntrega = isset($_POST['aduanaEntrega']) ? trim((string) $_POST['aduanaEntrega']) : '';

if ($folio <= 0 || $documento === '') {
    responderError('Folio o documento inválido.', 400);
}

if ($recibio === '') {
    responderError('Ingresa el nombre de quien recibe el material.', 400);
}

if ($aduanaEntrega === '') {
    responderError('Ingresa el nombre de la aduana que entrega el material.', 400);
}

$partidasJson = isset($_POST['partidas']) ? (string) $_POST['partidas'] : '';
$partidas = json_decode($partidasJson, true);

if (!is_array($partidas) || count($partidas) === 0) {
    responderError('No se recibieron partidas para registrar la entrega.', 400);
}

$idsPartidas = [];
foreach ($partidas as $partida) {
    $partidaId = isset($partida['id']) ? (int) $partida['id'] : 0;
    $cantidadEntregada = isset($partida['entregar']) ? (int) $partida['entregar'] : 0;

    if ($partidaId <= 0 || $cantidadEntregada <= 0) {
        responderError('Cada partida debe incluir un identificador válido y una cantidad mayor a cero.', 400);
    }

    $idsPartidas[] = $partidaId;
}

$idsPlaceholders = implode(',', array_fill(0, count($idsPartidas), '?'));
$tipos = str_repeat('i', count($idsPartidas));

$consulta = "SELECT MaterialPendienteID, CantidadMP, SkuMP, DescripcionMP FROM materialpendiente WHERE MaterialPendienteID IN ($idsPlaceholders) AND DocumentoMP = ?";
$stmt = mysqli_prepare($conn, $consulta);
if (!$stmt) {
    responderError('No se pudieron validar las partidas.');
}

$parametros = array_merge([$tipos . 's'], $idsPartidas, [$documento]);
call_user_func_array('mysqli_stmt_bind_param', array_merge([$stmt], referenciarValores($parametros)));
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);

$partidasDisponibles = [];
while ($fila = mysqli_fetch_assoc($resultado)) {
    $partidasDisponibles[(int) $fila['MaterialPendienteID']] = [
        'cantidad' => (int) $fila['CantidadMP'],
        'sku' => isset($fila['SkuMP']) ? trim((string) $fila['SkuMP']) : '',
        'descripcion' => isset($fila['DescripcionMP']) ? trim((string) $fila['DescripcionMP']) : '',
    ];
}

mysqli_stmt_close($stmt);

if (count($partidasDisponibles) !== count($idsPartidas)) {
    responderError('Alguna partida ya no está disponible o no pertenece al documento indicado.', 400);
}

mysqli_begin_transaction($conn);

foreach ($partidas as $partida) {
    $partidaId = (int) $partida['id'];
    $cantidadEntregada = (int) $partida['entregar'];
    $pendienteActual = $partidasDisponibles[$partidaId]['cantidad'];
    $skuEntrega = $partidasDisponibles[$partidaId]['sku'];
    $descripcionEntrega = $partidasDisponibles[$partidaId]['descripcion'];

    if ($cantidadEntregada > $pendienteActual) {
        mysqli_rollback($conn);
        responderError('La cantidad a entregar no puede exceder la pendiente.', 400);
    }

    $restante = $pendienteActual - $cantidadEntregada;

    if ($restante > 0) {
        $stmtUpdate = mysqli_prepare($conn, 'UPDATE materialpendiente SET CantidadMP = ? WHERE MaterialPendienteID = ? LIMIT 1');
        if ($stmtUpdate) {
            mysqli_stmt_bind_param($stmtUpdate, 'ii', $restante, $partidaId);
            mysqli_stmt_execute($stmtUpdate);
            mysqli_stmt_close($stmtUpdate);
        }
    } else {
        $stmtDelete = mysqli_prepare($conn, 'DELETE FROM materialpendiente WHERE MaterialPendienteID = ? LIMIT 1');
        if ($stmtDelete) {
            mysqli_stmt_bind_param($stmtDelete, 'i', $partidaId);
            mysqli_stmt_execute($stmtDelete);
            mysqli_stmt_close($stmtDelete);
        }
    }

    $stmtInsert = mysqli_prepare(
        $conn,
        'INSERT INTO materialpendiente_entregas (MaterialPendienteID, FolioID, Documento, CantidadEntregada, Recibio, AduanaEntrega, SkuEntrega, DescripcionEntrega) VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );

    if ($stmtInsert) {
        mysqli_stmt_bind_param($stmtInsert, 'iisissss', $partidaId, $folio, $documento, $cantidadEntregada, $recibio, $aduanaEntrega, $skuEntrega, $descripcionEntrega);
        mysqli_stmt_execute($stmtInsert);
        mysqli_stmt_close($stmtInsert);
    }
}

mysqli_commit($conn);

echo json_encode([
    'success' => true,
    'message' => 'Entrega registrada correctamente.'
]);
