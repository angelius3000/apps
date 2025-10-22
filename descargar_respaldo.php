<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/HeaderScripts.php';
require_once __DIR__ . '/includes/DatabaseBackup.php';

$tipoUsuarioActual = isset($_SESSION['TipoDeUsuario'])
    ? strtolower(trim((string) $_SESSION['TipoDeUsuario']))
    : '';

if ($tipoUsuarioActual !== 'administrador') {
    http_response_code(403);
    exit('No tienes permisos para descargar respaldos.');
}

$archivoSolicitado = $_GET['file'] ?? '';
$scope = $_GET['scope'] ?? 'database';

if ($scope === 'table') {
    $tablaSolicitada = $_GET['table'] ?? '';
    $ruta = dbBackupResolveTablePath($tablaSolicitada, $archivoSolicitado);
} else {
    $ruta = dbBackupResolvePath($archivoSolicitado);
}

if ($ruta === null) {
    http_response_code(404);
    exit('El respaldo solicitado no existe.');
}

if (!is_readable($ruta)) {
    http_response_code(404);
    exit('El respaldo solicitado no está disponible para su descarga.');
}

$nombreDescarga = basename($ruta);

header('Content-Type: application/sql');
header('Content-Disposition: attachment; filename="' . $nombreDescarga . '"');
header('Content-Length: ' . (string) filesize($ruta));
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: public');
@set_time_limit(0);
readfile($ruta);
exit;
