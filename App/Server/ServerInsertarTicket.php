<?php
include("../../Connections/ConDB.php");

if (!isset($_SESSION)) {
    session_start();
}

$Titulo = mysqli_real_escape_string($conn, $_POST['Titulo'] ?? '');
$Descripcion = mysqli_real_escape_string($conn, $_POST['Descripcion'] ?? '');
$Prioridad = mysqli_real_escape_string($conn, $_POST['Prioridad'] ?? 'Media');
$Categoria = mysqli_real_escape_string($conn, $_POST['Categoria'] ?? 'Otros');
$USUARIOID_CREADOR = (int)($_SESSION['USUARIOID'] ?? 0);

$rutaAdjunto = '';

$directorioAdjuntos = dirname(__DIR__) . '/Uploads/Soporte';
if (!is_dir($directorioAdjuntos)) {
    @mkdir($directorioAdjuntos, 0775, true);
}

if (isset($_FILES['ImagenTicket']) && isset($_FILES['ImagenTicket']['tmp_name']) && $_FILES['ImagenTicket']['tmp_name'] !== '') {
    $mime = (string)($_FILES['ImagenTicket']['type'] ?? '');
    if (strpos($mime, 'image/') === 0) {
        $extension = strtolower(pathinfo((string)$_FILES['ImagenTicket']['name'], PATHINFO_EXTENSION));
        if ($extension === '') {
            $extension = 'png';
        }

        $nombreArchivo = 'ticket_' . date('Ymd_His') . '_' . mt_rand(1000, 9999) . '.' . preg_replace('/[^a-z0-9]/', '', $extension);
        $destino = $directorioAdjuntos . '/' . $nombreArchivo;

        if (@move_uploaded_file($_FILES['ImagenTicket']['tmp_name'], $destino)) {
            $rutaAdjunto = 'App/Uploads/Soporte/' . $nombreArchivo;
        }
    }
} elseif (!empty($_POST['ImagenTicketPegada'])) {
    $imagenPegada = (string)$_POST['ImagenTicketPegada'];
    if (preg_match('/^data:image\/(png|jpe?g|gif|webp);base64,/', $imagenPegada, $coincidencias)) {
        $tipo = strtolower($coincidencias[1]);
        if ($tipo === 'jpeg') {
            $tipo = 'jpg';
        }

        $base64 = substr($imagenPegada, strpos($imagenPegada, ',') + 1);
        $binario = base64_decode($base64, true);
        if ($binario !== false) {
            $nombreArchivo = 'ticket_' . date('Ymd_His') . '_' . mt_rand(1000, 9999) . '.' . $tipo;
            $destino = $directorioAdjuntos . '/' . $nombreArchivo;
            if (@file_put_contents($destino, $binario) !== false) {
                $rutaAdjunto = 'App/Uploads/Soporte/' . $nombreArchivo;
            }
        }
    }
}

if ($rutaAdjunto !== '') {
    $Descripcion .= "\n\nAdjunto: " . $rutaAdjunto;
}

$hoy = date('Ymd');
$folio = 'SOP-' . $hoy . '-0001';

$sqlFolio = "SELECT Folio FROM tickets WHERE Folio LIKE 'SOP-$hoy-%' ORDER BY Folio DESC LIMIT 1";
$resultadoFolio = mysqli_query($conn, $sqlFolio);
if ($resultadoFolio && mysqli_num_rows($resultadoFolio) > 0) {
    $filaFolio = mysqli_fetch_assoc($resultadoFolio);
    $folioActual = (string)$filaFolio['Folio'];
    $consecutivo = (int)substr($folioActual, -4) + 1;
    $folio = 'SOP-' . $hoy . '-' . str_pad((string)$consecutivo, 4, '0', STR_PAD_LEFT);
}
if ($resultadoFolio) {
    mysqli_free_result($resultadoFolio);
}

$USUARIOID_ASIGNADO = 'NULL';
$AutoAsignado = 0;

$sqlAdmin = "SELECT usuarios.USUARIOID
    FROM usuarios
    LEFT JOIN tipodeusuarios ON usuarios.TIPODEUSUARIOID = tipodeusuarios.TIPODEUSUARIOID
    WHERE LOWER(tipodeusuarios.TipoDeUsuario) IN ('soporte it', 'administrador') AND usuarios.Deshabilitado = 0
    ORDER BY usuarios.USUARIOID ASC LIMIT 1";

$resultadoAdmin = mysqli_query($conn, $sqlAdmin);
if ($resultadoAdmin && mysqli_num_rows($resultadoAdmin) > 0) {
    $filaAdmin = mysqli_fetch_assoc($resultadoAdmin);
    $USUARIOID_ASIGNADO = (int)$filaAdmin['USUARIOID'];
    $AutoAsignado = 1;
}
if ($resultadoAdmin) {
    mysqli_free_result($resultadoAdmin);
}

$sql = "INSERT INTO tickets (Folio, Titulo, Descripcion, Prioridad, Categoria, STATUS, USUARIOID_CREADOR, USUARIOID_ASIGNADO, AutoAsignado)
VALUES ('$folio', '$Titulo', '$Descripcion', '$Prioridad', '$Categoria', 'Abierto', '$USUARIOID_CREADOR', " . ($USUARIOID_ASIGNADO === 'NULL' ? 'NULL' : "'$USUARIOID_ASIGNADO'") . ", '$AutoAsignado')";

if (!mysqli_query($conn, $sql)) {
    die('Error: ' . mysqli_error($conn));
}

$last_id = mysqli_insert_id($conn);
$msg = array('success' => true, 'TICKETID' => $last_id, 'Folio' => $folio);

echo json_encode($msg);
