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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderError('Método no permitido.', 405);
}

if (!$conn) {
    responderError('No se pudo conectar a la base de datos.');
}

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

$consulta = "SELECT MaterialPendienteID, CantidadMP FROM materialpendiente WHERE MaterialPendienteID IN ($idsPlaceholders) AND DocumentoMP = ?";
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
    $partidasDisponibles[(int) $fila['MaterialPendienteID']] = (int) $fila['CantidadMP'];
}

mysqli_stmt_close($stmt);

if (count($partidasDisponibles) !== count($idsPartidas)) {
    responderError('Alguna partida ya no está disponible o no pertenece al documento indicado.', 400);
}

mysqli_begin_transaction($conn);

$crearTablaSQL = "CREATE TABLE IF NOT EXISTS materialpendiente_entregas (\n        EntregaID INT NOT NULL AUTO_INCREMENT,\n        MaterialPendienteID INT NOT NULL,\n        FolioID INT NOT NULL,\n        Documento VARCHAR(100) NOT NULL,\n        CantidadEntregada INT NOT NULL,\n        Recibio VARCHAR(255) NOT NULL,\n        AduanaEntrega VARCHAR(255) NOT NULL,\n        FechaEntrega TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,\n        PRIMARY KEY (EntregaID),\n        INDEX idx_entrega_material (MaterialPendienteID),\n        INDEX idx_entrega_documento (Documento)\n    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
@mysqli_query($conn, $crearTablaSQL);

foreach ($partidas as $partida) {
    $partidaId = (int) $partida['id'];
    $cantidadEntregada = (int) $partida['entregar'];
    $pendienteActual = $partidasDisponibles[$partidaId];

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
        'INSERT INTO materialpendiente_entregas (MaterialPendienteID, FolioID, Documento, CantidadEntregada, Recibio, AduanaEntrega) VALUES (?, ?, ?, ?, ?, ?)'
    );

    if ($stmtInsert) {
        mysqli_stmt_bind_param($stmtInsert, 'iisiss', $partidaId, $folio, $documento, $cantidadEntregada, $recibio, $aduanaEntrega);
        mysqli_stmt_execute($stmtInsert);
        mysqli_stmt_close($stmtInsert);
    }
}

mysqli_commit($conn);

echo json_encode([
    'success' => true,
    'message' => 'Entrega registrada correctamente.'
]);
