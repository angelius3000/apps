<?php
include("includes/HeaderScripts.php");
require_once __DIR__ . '/includes/DatabaseBackup.php';
require_once __DIR__ . '/Connections/ConDB.php';

$pageTitle = 'Edison - Administración';

$tipoUsuarioActual = isset($_SESSION['TipoDeUsuario']) ? strtolower(trim((string) $_SESSION['TipoDeUsuario'])) : '';
if ($tipoUsuarioActual !== 'administrador') {
    header("Location: main.php");
    exit;
}

$tablasDisponibles = [];
$tablaSeleccionada = '';
$metadataColumnas = [];
$columnaLlavePrimaria = null;
$columnaAutoIncremental = null;
$mensajesExito = [];
$mensajesError = [];
$registrosTabla = [];
$respaldosDisponibles = [];
$respaldosTablaSeleccionada = [];
$listaSecciones = [];
$tabActivo = 'database';

$tabSolicitado = null;
if (isset($_POST['active_tab'])) {
    $tabSolicitado = (string) $_POST['active_tab'];
} elseif (isset($_GET['tab'])) {
    $tabSolicitado = (string) $_GET['tab'];
}

if ($tabSolicitado !== null && in_array($tabSolicitado, ['database', 'sections'], true)) {
    $tabActivo = $tabSolicitado;
}

if (!isset($conn) || $conn === false) {
    $mensajesError[] = 'No se pudo establecer conexión con la base de datos. Por favor revisa la configuración.';
} else {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $accionGeneral = $_POST['action'] ?? '';

        if ($accionGeneral === 'update_sections_visibility') {
            $tabActivo = 'sections';

            $slugsSeleccionados = [];
            if (isset($_POST['sections_visibility']) && is_array($_POST['sections_visibility'])) {
                foreach (array_keys($_POST['sections_visibility']) as $slugSeleccionado) {
                    $slugFiltrado = strtolower(trim((string) $slugSeleccionado));
                    if ($slugFiltrado !== '') {
                        $slugsSeleccionados[] = $slugFiltrado;
                    }
                }
            }

            $slugsSeleccionadosMap = [];
            if (!empty($slugsSeleccionados)) {
                $slugsSeleccionadosMap = array_fill_keys($slugsSeleccionados, true);
            }

            $consultaSeccionesAccion = @mysqli_query(
                $conn,
                'SELECT SECCIONID, Slug FROM secciones'
            );

            if (!$consultaSeccionesAccion instanceof mysqli_result) {
                $mensajesError[] = 'No se pudieron obtener las secciones registradas para actualizar su visibilidad.';
            } else {
                $stmtActualizarSeccion = @mysqli_prepare(
                    $conn,
                    'UPDATE secciones SET MostrarEnMenu = ? WHERE SECCIONID = ?'
                );

                if (!$stmtActualizarSeccion) {
                    $mensajesError[] = 'No fue posible preparar la consulta para actualizar la visibilidad de las secciones.';
                } else {
                    mysqli_stmt_bind_param($stmtActualizarSeccion, 'ii', $mostrarEnMenuParam, $seccionIdParam);

                    $actualizacionExitosa = true;

                    while ($filaSeccionAccion = mysqli_fetch_assoc($consultaSeccionesAccion)) {
                        $seccionIdParam = (int) $filaSeccionAccion['SECCIONID'];
                        $slugSeccion = strtolower((string) $filaSeccionAccion['Slug']);
                        $mostrarEnMenuParam = isset($slugsSeleccionadosMap[$slugSeccion]) ? 1 : 0;

                        if (!@mysqli_stmt_execute($stmtActualizarSeccion)) {
                            $actualizacionExitosa = false;
                            $mensajesError[] = 'Ocurrió un error al guardar la visibilidad de la sección con slug '
                                . htmlspecialchars($slugSeccion, ENT_QUOTES, 'UTF-8') . ': '
                                . mysqli_stmt_error($stmtActualizarSeccion);
                            break;
                        }
                    }

                    mysqli_stmt_close($stmtActualizarSeccion);

                    if ($actualizacionExitosa) {
                        $mensajesExito[] = 'La visibilidad de las secciones se actualizó correctamente.';
                    }
                }

                mysqli_free_result($consultaSeccionesAccion);
            }

            $configuracionSecciones = [];
            $resultadoVisibilidad = @mysqli_query(
                $conn,
                'SELECT Slug, MostrarEnMenu FROM secciones'
            );

            if ($resultadoVisibilidad instanceof mysqli_result) {
                while ($filaVisibilidad = mysqli_fetch_assoc($resultadoVisibilidad)) {
                    $configuracionSecciones[strtolower((string) $filaVisibilidad['Slug'])] = (int) $filaVisibilidad['MostrarEnMenu'];
                }
                mysqli_free_result($resultadoVisibilidad);
            }

            $_SESSION['SeccionesVisibles'] = $configuracionSecciones;
        } elseif ($accionGeneral === 'create_backup') {
            [$exitoRespaldo, $mensajeRespaldo, $archivoGenerado] = dbBackupCreate($conn);
            if ($exitoRespaldo) {
                $mensajeFinal = $mensajeRespaldo;
                if ($archivoGenerado !== null) {
                    $mensajeFinal .= ' Archivo generado: ' . $archivoGenerado . '.';
                }
                $mensajesExito[] = $mensajeFinal;
            } else {
                $mensajesError[] = $mensajeRespaldo;
            }
        } elseif ($accionGeneral === 'create_table_backup') {
            $tablaParaRespaldo = isset($_POST['selected_table']) ? (string) $_POST['selected_table'] : '';
            [$exitoRespaldoTabla, $mensajeRespaldoTabla, $archivoGeneradoTabla] = dbBackupCreateTable($conn, $tablaParaRespaldo);

            if ($exitoRespaldoTabla) {
                $mensajeFinal = $mensajeRespaldoTabla;
                if ($archivoGeneradoTabla !== null) {
                    $mensajeFinal .= ' Archivo generado: ' . $archivoGeneradoTabla . '.';
                }
                $mensajesExito[] = $mensajeFinal;
            } else {
                $mensajesError[] = $mensajeRespaldoTabla;
            }
        } elseif ($accionGeneral === 'restore_existing_backup') {
            $archivoSeleccionado = $_POST['backup_file'] ?? '';
            $rutaRespaldo = dbBackupResolvePath($archivoSeleccionado);

            if ($rutaRespaldo === null) {
                $mensajesError[] = 'El respaldo seleccionado no es válido o ya no existe.';
            } else {
                [$exitoRestauracion, $mensajeRestauracion] = dbBackupRestoreFromFile($conn, $rutaRespaldo);
                if ($exitoRestauracion) {
                    $mensajeFinal = $mensajeRestauracion;
                    if ($archivoSeleccionado !== '') {
                        $mensajeFinal .= ' Respaldo utilizado: ' . $archivoSeleccionado . '.';
                    }
                    $mensajesExito[] = $mensajeFinal;
                } else {
                    $mensajesError[] = $mensajeRestauracion;
                }
            }
        } elseif ($accionGeneral === 'restore_backup_upload') {
            if (!isset($_FILES['backup_upload'])) {
                $mensajesError[] = 'Selecciona un archivo de respaldo para restaurar.';
            } else {
                [$subidaExitosa, $mensajeSubida, $rutaAlmacenada, $nombreAlmacenado] = dbBackupStoreUploaded($_FILES['backup_upload']);

                if (!$subidaExitosa || $rutaAlmacenada === null) {
                    $mensajesError[] = $mensajeSubida;
                } else {
                    $mensajesExito[] = $mensajeSubida;
                    if ($nombreAlmacenado !== null) {
                        $mensajesExito[] = 'Archivo almacenado como: ' . $nombreAlmacenado . '.';
                    }

                    [$exitoRestauracion, $mensajeRestauracion] = dbBackupRestoreFromFile($conn, $rutaAlmacenada);
                    if ($exitoRestauracion) {
                        $mensajesExito[] = $mensajeRestauracion;
                    } else {
                        $mensajesError[] = $mensajeRestauracion;
                    }
                }
            }
        } elseif ($accionGeneral === 'restore_table_backup') {
            $tablaObjetivo = isset($_POST['selected_table']) ? (string) $_POST['selected_table'] : '';
            $archivoRestaurarTabla = $_POST['backup_file'] ?? '';

            [$exitoRestaurarTabla, $mensajeRestaurarTabla] = dbBackupRestoreTableFromBackup($conn, $tablaObjetivo, $archivoRestaurarTabla);
            if ($exitoRestaurarTabla) {
                $mensajeFinal = $mensajeRestaurarTabla;
                if ($archivoRestaurarTabla !== '') {
                    $mensajeFinal .= ' Respaldo utilizado: ' . $archivoRestaurarTabla . '.';
                }
                $mensajesExito[] = $mensajeFinal;
            } else {
                $mensajesError[] = $mensajeRestaurarTabla;
            }
        } elseif ($accionGeneral === 'delete_backup') {
            $archivoEliminar = $_POST['backup_file'] ?? '';
            [$exitoEliminar, $mensajeEliminar] = dbBackupDeleteFile($archivoEliminar);
            if ($exitoEliminar) {
                $mensajeFinal = $mensajeEliminar;
                if ($archivoEliminar !== '') {
                    $mensajeFinal .= ' Archivo eliminado: ' . $archivoEliminar . '.';
                }
                $mensajesExito[] = $mensajeFinal;
            } else {
                $mensajesError[] = $mensajeEliminar;
            }
        } elseif ($accionGeneral === 'delete_table_backup') {
            $tablaObjetivo = isset($_POST['selected_table']) ? (string) $_POST['selected_table'] : '';
            $archivoEliminarTabla = $_POST['backup_file'] ?? '';

            [$exitoEliminarTabla, $mensajeEliminarTabla] = dbBackupDeleteTableFile($tablaObjetivo, $archivoEliminarTabla);
            if ($exitoEliminarTabla) {
                $mensajeFinal = $mensajeEliminarTabla;
                if ($archivoEliminarTabla !== '') {
                    $mensajeFinal .= ' Archivo eliminado: ' . $archivoEliminarTabla . '.';
                }
                $mensajesExito[] = $mensajeFinal;
            } else {
                $mensajesError[] = $mensajeEliminarTabla;
            }
        }
    }

    $resultadoTablas = @mysqli_query($conn, 'SHOW TABLES');
    if ($resultadoTablas instanceof mysqli_result) {
        while ($filaTabla = mysqli_fetch_row($resultadoTablas)) {
            if (!empty($filaTabla[0])) {
                $tablasDisponibles[] = $filaTabla[0];
            }
        }
        mysqli_free_result($resultadoTablas);
    }

    if (!empty($tablasDisponibles)) {
        $tablaSeleccionada = $_POST['selected_table']
            ?? $_GET['selected_table']
            ?? $tablasDisponibles[0];

        if (!in_array($tablaSeleccionada, $tablasDisponibles, true)) {
            $tablaSeleccionada = $tablasDisponibles[0];
        }

        $consultaDescribe = @mysqli_query($conn, 'DESCRIBE `' . $tablaSeleccionada . '`');
        if ($consultaDescribe instanceof mysqli_result) {
            while ($columna = mysqli_fetch_assoc($consultaDescribe)) {
                $metadataColumnas[$columna['Field']] = $columna;

                if ($columnaLlavePrimaria === null && isset($columna['Key']) && $columna['Key'] === 'PRI') {
                    $columnaLlavePrimaria = $columna['Field'];
                }

                if (
                    $columnaAutoIncremental === null
                    && isset($columna['Extra'])
                    && strpos(strtolower((string) $columna['Extra']), 'auto_increment') !== false
                ) {
                    $columnaAutoIncremental = $columna['Field'];
                }
            }
            mysqli_free_result($consultaDescribe);
        }

        if (
            $_SERVER['REQUEST_METHOD'] === 'POST'
            && isset($_POST['action'], $_POST['selected_table'])
            && $_POST['selected_table'] === $tablaSeleccionada
        ) {
            $accion = $_POST['action'];

            if ($accion === 'update') {
                if ($columnaLlavePrimaria === null) {
                    $mensajesError[] = 'No se encontró una llave primaria para actualizar el registro seleccionado.';
                } else {
                    $datosFila = isset($_POST['row_data']) && is_array($_POST['row_data']) ? $_POST['row_data'] : [];
                    $llaveOriginal = $_POST['original_primary_key_value'] ?? '';

                    if ($llaveOriginal === '') {
                        $mensajesError[] = 'No se proporcionó la llave primaria del registro a actualizar.';
                    } else {
                        $columnasActualizar = [];
                        $valoresActualizar = [];

                        foreach ($metadataColumnas as $nombreColumna => $infoColumna) {
                            if (!array_key_exists($nombreColumna, $datosFila)) {
                                continue;
                            }

                            $permiteNulos = isset($infoColumna['Null']) && strtoupper((string) $infoColumna['Null']) === 'YES';
                            $valorCampo = $datosFila[$nombreColumna];

                            if ($permiteNulos && $valorCampo === '') {
                                $valorNormalizado = null;
                            } else {
                                $valorNormalizado = (string) $valorCampo;
                            }

                            $columnasActualizar[] = '`' . $nombreColumna . '` = ?';
                            $valoresActualizar[] = $valorNormalizado;
                        }

                        if (empty($columnasActualizar)) {
                            $mensajesError[] = 'No se enviaron datos para actualizar en la tabla seleccionada.';
                        } else {
                            $consultaUpdate = 'UPDATE `' . $tablaSeleccionada . '` SET ' . implode(', ', $columnasActualizar)
                                . ' WHERE `' . $columnaLlavePrimaria . '` = ?';

                            $stmtUpdate = @mysqli_prepare($conn, $consultaUpdate);

                            if ($stmtUpdate) {
                                $tipos = str_repeat('s', count($valoresActualizar) + 1);
                                $parametros = [$tipos];

                                foreach ($valoresActualizar as $indice => $valor) {
                                    $parametros[] = &$valoresActualizar[$indice];
                                }

                                $llaveOriginalParam = (string) $llaveOriginal;
                                $parametros[] = &$llaveOriginalParam;

                                $resultadoBind = @call_user_func_array([$stmtUpdate, 'bind_param'], $parametros);

                                if ($resultadoBind) {
                                    if (@mysqli_stmt_execute($stmtUpdate)) {
                                        if (mysqli_stmt_affected_rows($stmtUpdate) > 0) {
                                            $mensajesExito[] = 'El registro se actualizó correctamente.';
                                        } else {
                                            $mensajesError[] = 'No se realizaron cambios en el registro seleccionado.';
                                        }
                                    } else {
                                        $mensajesError[] = 'Ocurrió un error al actualizar el registro: ' . mysqli_stmt_error($stmtUpdate);
                                    }
                                } else {
                                    $mensajesError[] = 'No fue posible preparar los datos para actualizar el registro.';
                                }

                                mysqli_stmt_close($stmtUpdate);
                            } else {
                                $mensajesError[] = 'No fue posible preparar la consulta para actualizar el registro.';
                            }
                        }
                    }
                }
            } elseif ($accion === 'delete') {
                if ($columnaLlavePrimaria === null) {
                    $mensajesError[] = 'No se puede eliminar el registro porque la tabla no tiene una llave primaria definida.';
                } else {
                    $llaveOriginal = $_POST['original_primary_key_value'] ?? '';

                    if ($llaveOriginal === '') {
                        $mensajesError[] = 'No se proporcionó la llave primaria del registro a eliminar.';
                    } else {
                        $consultaDelete = 'DELETE FROM `' . $tablaSeleccionada . '` WHERE `' . $columnaLlavePrimaria . '` = ? LIMIT 1';
                        $stmtDelete = @mysqli_prepare($conn, $consultaDelete);

                        if ($stmtDelete) {
                            $llaveOriginalParam = (string) $llaveOriginal;
                            @mysqli_stmt_bind_param($stmtDelete, 's', $llaveOriginalParam);

                            if (@mysqli_stmt_execute($stmtDelete)) {
                                if (mysqli_stmt_affected_rows($stmtDelete) > 0) {
                                    $mensajesExito[] = 'El registro se eliminó correctamente.';
                                } else {
                                    $mensajesError[] = 'No se encontró el registro que intentaste eliminar.';
                                }
                            } else {
                                $mensajesError[] = 'Ocurrió un error al eliminar el registro: ' . mysqli_stmt_error($stmtDelete);
                            }

                            mysqli_stmt_close($stmtDelete);
                        } else {
                            $mensajesError[] = 'No fue posible preparar la consulta para eliminar el registro.';
                        }
                    }
                }
            } elseif ($accion === 'reset_auto_increment') {
                if ($columnaAutoIncremental === null) {
                    $mensajesError[] = 'La tabla seleccionada no tiene una columna autoincremental para reiniciar.';
                } else {
                    $consultaMaximo = 'SELECT IFNULL(MAX(`' . $columnaAutoIncremental . '`), 0) + 1 AS proximoValor FROM `' . $tablaSeleccionada . '`';
                    $resultadoMaximo = @mysqli_query($conn, $consultaMaximo);

                    if ($resultadoMaximo instanceof mysqli_result) {
                        $filaMaximo = mysqli_fetch_assoc($resultadoMaximo);
                        $siguienteValor = isset($filaMaximo['proximoValor']) ? (int) $filaMaximo['proximoValor'] : 1;
                        mysqli_free_result($resultadoMaximo);

                        $consultaReset = 'ALTER TABLE `' . $tablaSeleccionada . '` AUTO_INCREMENT = ' . $siguienteValor;
                        if (@mysqli_query($conn, $consultaReset)) {
                            $mensajesExito[] = 'El valor autoincremental se restableció correctamente.';
                        } else {
                            $mensajesError[] = 'Ocurrió un error al restablecer el autoincremental: ' . mysqli_error($conn);
                        }
                    } else {
                        $mensajesError[] = 'No se pudo determinar el siguiente valor autoincremental.';
                    }
                }
            } elseif ($accion === 'truncate_table') {
                $consultaTruncate = 'TRUNCATE TABLE `' . $tablaSeleccionada . '`';
                $resultadoTruncate = @mysqli_query($conn, $consultaTruncate);

                if ($resultadoTruncate) {
                    $mensajesExito[] = 'La tabla se vació correctamente.';
                } else {
                    $consultaDeleteTodos = 'DELETE FROM `' . $tablaSeleccionada . '`';
                    if (@mysqli_query($conn, $consultaDeleteTodos)) {
                        if ($columnaAutoIncremental !== null) {
                            @mysqli_query($conn, 'ALTER TABLE `' . $tablaSeleccionada . '` AUTO_INCREMENT = 1');
                        }
                        $mensajesExito[] = 'Se eliminaron todos los registros de la tabla seleccionada.';
                    } else {
                        $mensajesError[] = 'No fue posible vaciar la tabla seleccionada: ' . mysqli_error($conn);
                    }
                }
            }
        }

        $respaldosTablaSeleccionada = dbBackupListTableFiles($tablaSeleccionada);

        $consultaRegistros = 'SELECT * FROM `' . $tablaSeleccionada . '`';
        if ($columnaLlavePrimaria !== null) {
            $consultaRegistros .= ' ORDER BY `' . $columnaLlavePrimaria . '` ASC';
        }
        $consultaRegistros .= ' LIMIT 50';

        $resultadoRegistros = @mysqli_query($conn, $consultaRegistros);
        if ($resultadoRegistros instanceof mysqli_result) {
            while ($filaRegistro = mysqli_fetch_assoc($resultadoRegistros)) {
                $registrosTabla[] = $filaRegistro;
            }
            mysqli_free_result($resultadoRegistros);
        }
    }
}

$respaldosDisponibles = dbBackupListFiles();

if (isset($conn) && $conn !== false) {
    $consultaListadoSecciones = @mysqli_query(
        $conn,
        'SELECT SECCIONID, Nombre, Slug, Ruta, Orden, MostrarEnMenu FROM secciones ORDER BY Orden, Nombre'
    );

    if ($consultaListadoSecciones instanceof mysqli_result) {
        while ($filaSeccion = mysqli_fetch_assoc($consultaListadoSecciones)) {
            $listaSecciones[] = [
                'SECCIONID' => (int) $filaSeccion['SECCIONID'],
                'Nombre' => (string) $filaSeccion['Nombre'],
                'Slug' => (string) $filaSeccion['Slug'],
                'Ruta' => (string) $filaSeccion['Ruta'],
                'Orden' => (int) $filaSeccion['Orden'],
                'MostrarEnMenu' => (int) $filaSeccion['MostrarEnMenu'],
            ];
        }
        mysqli_free_result($consultaListadoSecciones);
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<?php include("includes/Header.php"); ?>

<body>
    <div class="app full-width-header align-content-stretch d-flex flex-wrap">
        <div class="app-sidebar">
            <div class="logo logo-sm">
                <a href="main.php"><img src="App/Graficos/Logo/LogoEdison.png" style="max-width :130px;"></a>
            </div>
            <?php include("includes/Menu.php"); ?>
        </div>
        <div class="app-container">
            <?php include("includes/MenuHeader.php"); ?>
            <div class="app-content">
                <div class="content-wrapper">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col">
                                <div class="page-description">
                                    <h1>Administración</h1>
                                    <p class="text-muted">Gestiona la configuración general del sistema y las herramientas exclusivas para administradores.</p>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <?php if (!empty($mensajesError) || !empty($mensajesExito)) : ?>
                                    <div class="mb-3">
                                        <?php foreach ($mensajesError as $mensaje) : ?>
                                            <div class="alert alert-danger" role="alert">
                                                <?php echo htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8'); ?>
                                            </div>
                                        <?php endforeach; ?>
                                        <?php foreach ($mensajesExito as $mensaje) : ?>
                                            <div class="alert alert-success" role="alert">
                                                <?php echo htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8'); ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <style>
                                    #adminSubsectionsContent .tab-pane {
                                        display: none !important;
                                    }

                                    #adminSubsectionsContent[data-active-tab="database"] .tab-pane[data-tab-panel="database"],
                                    #adminSubsectionsContent[data-active-tab="sections"] .tab-pane[data-tab-panel="sections"] {
                                        display: block !important;
                                    }
                                </style>

                                <ul class="nav nav-tabs mb-4" id="adminSubsections" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link <?php echo $tabActivo === 'database' ? 'active' : ''; ?>" id="database-tab" data-bs-toggle="tab" data-bs-target="#databaseSection" data-tab-value="database" type="button" role="tab" aria-controls="databaseSection" aria-selected="<?php echo $tabActivo === 'database' ? 'true' : 'false'; ?>">
                                            Bases de datos
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link <?php echo $tabActivo === 'sections' ? 'active' : ''; ?>" id="sections-tab" data-bs-toggle="tab" data-bs-target="#sectionsSection" data-tab-value="sections" type="button" role="tab" aria-controls="sectionsSection" aria-selected="<?php echo $tabActivo === 'sections' ? 'true' : 'false'; ?>">
                                            Secciones
                                        </button>
                                    </li>
                                </ul>

                                <div class="tab-content" id="adminSubsectionsContent" data-active-tab="<?php echo htmlspecialchars($tabActivo, ENT_QUOTES, 'UTF-8'); ?>">
                                    <div class="tab-pane fade <?php echo $tabActivo === 'database' ? 'show active' : ''; ?>" id="databaseSection" role="tabpanel" aria-labelledby="database-tab" data-tab-panel="database">
                                        <div class="card mb-4">
                                            <div class="card-body">
                                                <h5 class="card-title">Respaldos de la base de datos</h5>
                                                <p class="card-text">Genera, descarga y restaura respaldos de la base de datos del sistema directamente desde esta sección.</p>

                                                <div class="alert alert-warning" role="alert">
                                                    <strong>Importante:</strong> Restaurar un respaldo reemplazará la información actual por la contenida en el archivo seleccionado.
                                                </div>

                                                <div class="d-flex flex-column flex-lg-row align-items-start gap-3 mb-4">
                                                    <form method="post" class="d-inline">
                                                        <input type="hidden" name="active_tab" value="database">
                                                        <button type="submit" name="action" value="create_backup" class="btn btn-primary btn-sm" data-requires-confirmation="true" data-confirmation-message="Se generará un nuevo archivo de respaldo de la base de datos. ¿Deseas continuar?">
                                                            Crear respaldo
                                                        </button>
                                                    </form>
                                                    <form method="post" enctype="multipart/form-data" class="d-flex flex-column flex-sm-row align-items-start gap-2">
                                                        <input type="hidden" name="active_tab" value="database">
                                                        <div>
                                                            <label for="backup_upload" class="form-label mb-1">Cargar respaldo (.sql)</label>
                                                            <input class="form-control form-control-sm" type="file" id="backup_upload" name="backup_upload" accept=".sql" required>
                                                        </div>
                                                        <div class="pt-sm-4">
                                                            <button type="submit" name="action" value="restore_backup_upload" class="btn btn-outline-primary btn-sm" data-requires-confirmation="true" data-confirmation-message="El contenido del archivo cargado reemplazará los datos actuales. ¿Deseas continuar?">
                                                                Restaurar respaldo cargado
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>

                                                <?php if (empty($respaldosDisponibles)) : ?>
                                                    <p class="text-muted mb-0">Aún no se han generado respaldos en el servidor.</p>
                                                <?php else : ?>
                                                    <div class="table-responsive">
                                                        <table class="table table-sm align-middle mb-0">
                                                            <thead>
                                                                <tr>
                                                                    <th>Archivo</th>
                                                                    <th>Fecha de creación</th>
                                                                    <th>Tamaño</th>
                                                                    <th class="text-nowrap">Acciones</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php foreach ($respaldosDisponibles as $respaldo) : ?>
                                                                    <?php
                                                                    $tamanoKb = 0;
                                                                    if (isset($respaldo['size']) && is_numeric($respaldo['size'])) {
                                                                        $tamanoKb = max((float) $respaldo['size'], 0) / 1024;
                                                                    }
                                                                    $marcaTiempo = isset($respaldo['mtime']) && is_numeric($respaldo['mtime'])
                                                                        ? (int) $respaldo['mtime']
                                                                        : time();
                                                                    ?>
                                                                    <tr>
                                                                        <td class="text-break"><?php echo htmlspecialchars($respaldo['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                                        <td><?php echo date('d/m/Y H:i:s', $marcaTiempo); ?></td>
                                                                        <td><?php echo number_format($tamanoKb, 2); ?> KB</td>
                                                                        <td class="text-nowrap">
                                                                            <div class="d-flex flex-wrap gap-1">
                                                                                <a href="descargar_respaldo.php?file=<?php echo urlencode($respaldo['name']); ?>" class="btn btn-outline-primary btn-sm">Descargar</a>
                                                                                <form method="post" class="d-inline">
                                                                                    <input type="hidden" name="active_tab" value="database">
                                                                                    <input type="hidden" name="backup_file" value="<?php echo htmlspecialchars($respaldo['name'], ENT_QUOTES, 'UTF-8'); ?>">
                                                                                    <button type="submit" name="action" value="restore_existing_backup" class="btn btn-outline-warning btn-sm" data-requires-confirmation="true" data-confirmation-message="Se restaurará la base de datos utilizando este respaldo. ¿Deseas continuar?">
                                                                                        Restaurar
                                                                                    </button>
                                                                                </form>
                                                                                <form method="post" class="d-inline">
                                                                                    <input type="hidden" name="active_tab" value="database">
                                                                                    <input type="hidden" name="backup_file" value="<?php echo htmlspecialchars($respaldo['name'], ENT_QUOTES, 'UTF-8'); ?>">
                                                                                    <button type="submit" name="action" value="delete_backup" class="btn btn-outline-danger btn-sm" data-requires-confirmation="true" data-confirmation-message="¿Deseas eliminar este respaldo? Esta acción no se puede deshacer.">
                                                                                        Eliminar
                                                                                    </button>
                                                                                </form>
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <div class="card">
                                            <div class="card-body">
                                                <h5 class="card-title">Administración de bases de datos</h5>
                                                <p class="card-text">Gestiona las tablas disponibles en la base de datos. Puedes editar registros existentes, eliminarlos y restablecer el valor autoincremental cuando sea necesario.</p>

                                                <div class="alert alert-warning" role="alert">
                                                    <strong>Advertencia:</strong> Las acciones de esta sección pueden modificar o eliminar información de forma permanente. Revisa cuidadosamente los datos antes de confirmar cualquier cambio.
                                                </div>

                                                <?php if (empty($tablasDisponibles)) : ?>
                                                    <div class="alert alert-warning mb-0" role="alert">
                                                        No se encontraron tablas disponibles en la base de datos seleccionada.
                                                    </div>
                                                <?php else : ?>
                                                    <form method="get" class="row g-3 align-items-end mb-4">
                                                        <div class="col-md-6 col-lg-4">
                                                            <label for="selected_table" class="form-label">Tabla</label>
                                                            <select class="form-select" id="selected_table" name="selected_table" onchange="this.form.submit()">
                                                                <?php foreach ($tablasDisponibles as $tabla) : ?>
                                                                    <option value="<?php echo htmlspecialchars($tabla, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $tabla === $tablaSeleccionada ? 'selected' : ''; ?>>
                                                                        <?php echo htmlspecialchars($tabla, ENT_QUOTES, 'UTF-8'); ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                        <div class="col-auto d-none d-md-block">
                                                    <button type="submit" class="btn btn-primary">Ver tabla</button>
                                                </div>
                                            </form>

                                            <?php if (!empty($tablaSeleccionada)) : ?>
                                                <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                                                    <h6 class="mb-0">Registros de "<?php echo htmlspecialchars($tablaSeleccionada, ENT_QUOTES, 'UTF-8'); ?>"</h6>
                                                <div class="d-flex flex-wrap ms-md-auto gap-2">
                                                    <form method="post">
                                                        <input type="hidden" name="active_tab" value="database">
                                                        <input type="hidden" name="selected_table" value="<?php echo htmlspecialchars($tablaSeleccionada, ENT_QUOTES, 'UTF-8'); ?>">
                                                        <button type="submit" name="action" value="create_table_backup" class="btn btn-outline-primary btn-sm" data-requires-confirmation="true" data-confirmation-message="Se generará un respaldo que solo contiene la tabla seleccionada. ¿Deseas continuar?">
                                                            Respaldar tabla
                                                        </button>
                                                    </form>
                                                        <?php if ($columnaAutoIncremental !== null) : ?>
                                                            <form method="post">
                                                                <input type="hidden" name="active_tab" value="database">
                                                                <input type="hidden" name="selected_table" value="<?php echo htmlspecialchars($tablaSeleccionada, ENT_QUOTES, 'UTF-8'); ?>">
                                                                <button type="submit" name="action" value="reset_auto_increment" class="btn btn-outline-secondary btn-sm" data-requires-confirmation="true" data-confirmation-message="¿Seguro que deseas restablecer el valor autoincremental?">
                                                                    Resetear autoincremental
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>
                                                        <form method="post">
                                                            <input type="hidden" name="active_tab" value="database">
                                                            <input type="hidden" name="selected_table" value="<?php echo htmlspecialchars($tablaSeleccionada, ENT_QUOTES, 'UTF-8'); ?>">
                                                            <button type="submit" name="action" value="truncate_table" class="btn btn-outline-danger btn-sm" data-requires-confirmation="true" data-confirmation-message="Esta acción eliminará todos los registros de la tabla seleccionada. ¿Deseas continuar?">
                                                                Vaciar tabla
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>

                                                <div class="mb-4 w-100">
                                                    <h6 class="mb-2">Respaldos de "<?php echo htmlspecialchars($tablaSeleccionada, ENT_QUOTES, 'UTF-8'); ?>"</h6>
                                                    <?php if (empty($respaldosTablaSeleccionada)) : ?>
                                                        <p class="text-muted mb-0">Aún no se han generado respaldos para esta tabla.</p>
                                                    <?php else : ?>
                                                        <div class="table-responsive">
                                                            <table class="table table-sm align-middle mb-0">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Archivo</th>
                                                                        <th>Fecha de creación</th>
                                                                        <th>Tamaño</th>
                                                                        <th class="text-nowrap">Acciones</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php foreach ($respaldosTablaSeleccionada as $respaldoTabla) : ?>
                                                                        <?php
                                                                        $tamanoTablaKb = 0;
                                                                        if (isset($respaldoTabla['size']) && is_numeric($respaldoTabla['size'])) {
                                                                            $tamanoTablaKb = max((float) $respaldoTabla['size'], 0) / 1024;
                                                                        }
                                                                        $marcaTablaTiempo = isset($respaldoTabla['mtime']) && is_numeric($respaldoTabla['mtime'])
                                                                            ? (int) $respaldoTabla['mtime']
                                                                            : time();
                                                                        ?>
                                                                        <tr>
                                                                            <td class="text-break"><?php echo htmlspecialchars($respaldoTabla['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                                            <td><?php echo date('d/m/Y H:i:s', $marcaTablaTiempo); ?></td>
                                                                            <td><?php echo number_format($tamanoTablaKb, 2); ?> KB</td>
                                                                            <td class="text-nowrap">
                                                                                <div class="d-flex flex-wrap gap-1">
                                                                                    <a href="descargar_respaldo.php?scope=table&amp;table=<?php echo urlencode($tablaSeleccionada); ?>&amp;file=<?php echo urlencode($respaldoTabla['name']); ?>" class="btn btn-outline-primary btn-sm">Descargar</a>
                                                                                    <form method="post" class="d-inline">
                                                                                        <input type="hidden" name="active_tab" value="database">
                                                                                        <input type="hidden" name="selected_table" value="<?php echo htmlspecialchars($tablaSeleccionada, ENT_QUOTES, 'UTF-8'); ?>">
                                                                                        <input type="hidden" name="backup_file" value="<?php echo htmlspecialchars($respaldoTabla['name'], ENT_QUOTES, 'UTF-8'); ?>">
                                                                                        <button type="submit" name="action" value="restore_table_backup" class="btn btn-outline-warning btn-sm" data-requires-confirmation="true" data-confirmation-message="Se restaurará la tabla seleccionada utilizando este respaldo. ¿Deseas continuar?">
                                                                                            Restaurar
                                                                                        </button>
                                                                                    </form>
                                                                                    <form method="post" class="d-inline">
                                                                                        <input type="hidden" name="active_tab" value="database">
                                                                                        <input type="hidden" name="selected_table" value="<?php echo htmlspecialchars($tablaSeleccionada, ENT_QUOTES, 'UTF-8'); ?>">
                                                                                        <input type="hidden" name="backup_file" value="<?php echo htmlspecialchars($respaldoTabla['name'], ENT_QUOTES, 'UTF-8'); ?>">
                                                                                        <button type="submit" name="action" value="delete_table_backup" class="btn btn-outline-danger btn-sm" data-requires-confirmation="true" data-confirmation-message="¿Deseas eliminar este respaldo de la tabla? Esta acción no se puede deshacer.">
                                                                                            Eliminar
                                                                                        </button>
                                                                                    </form>
                                                                                </div>
                                                                            </td>
                                                                        </tr>
                                                                    <?php endforeach; ?>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>

                                                <?php if (empty($registrosTabla)) : ?>
                                                    <p class="text-muted mb-0">No se encontraron registros para la tabla seleccionada.</p>
                                                <?php else : ?>
                                                    <?php $accionesDeshabilitadas = $columnaLlavePrimaria === null; ?>
                                                    <?php if ($accionesDeshabilitadas) : ?>
                                                        <div class="alert alert-info">Esta tabla no tiene una llave primaria definida. Solo es posible visualizar los registros.</div>
                                                    <?php endif; ?>
                                                    <p class="text-muted">Mostrando hasta 50 registros. Utiliza el botón "Guardar" para aplicar los cambios en cada fila.</p>
                                                    <div class="table-responsive">
                                                        <table class="table table-sm table-striped align-middle">
                                                            <thead>
                                                                <tr>
                                                                    <?php foreach (array_keys($metadataColumnas) as $nombreColumna) : ?>
                                                                        <th><?php echo htmlspecialchars($nombreColumna, ENT_QUOTES, 'UTF-8'); ?></th>
                                                                    <?php endforeach; ?>
                                                                    <th class="text-nowrap">Acciones</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php foreach ($registrosTabla as $indiceRegistro => $registro) : ?>
                                                                    <?php $formId = 'form-row-' . $indiceRegistro; ?>
                                                                    <tr>
                                                                        <?php foreach ($metadataColumnas as $nombreColumna => $infoColumna) : ?>
                                                                            <?php
                                                                            $valorCelda = $registro[$nombreColumna] ?? '';
                                                                            $tipoColumna = strtolower((string) ($infoColumna['Type'] ?? ''));
                                                                            $esTextoLargo = strpos($tipoColumna, 'text') !== false || strpos($tipoColumna, 'blob') !== false;
                                                                            ?>
                                                                            <td style="min-width: 160px;">
                                                                                <?php if ($esTextoLargo) : ?>
                                                                                    <textarea form="<?php echo htmlspecialchars($formId, ENT_QUOTES, 'UTF-8'); ?>" name="row_data[<?php echo htmlspecialchars($nombreColumna, ENT_QUOTES, 'UTF-8'); ?>]" class="form-control form-control-sm" rows="2"><?php echo htmlspecialchars((string) $valorCelda, ENT_QUOTES, 'UTF-8'); ?></textarea>
                                                                                <?php else : ?>
                                                                                    <input form="<?php echo htmlspecialchars($formId, ENT_QUOTES, 'UTF-8'); ?>" type="text" name="row_data[<?php echo htmlspecialchars($nombreColumna, ENT_QUOTES, 'UTF-8'); ?>]" class="form-control form-control-sm" value="<?php echo htmlspecialchars((string) $valorCelda, ENT_QUOTES, 'UTF-8'); ?>">
                                                                                <?php endif; ?>
                                                                            </td>
                                                                        <?php endforeach; ?>
                                                                        <td class="text-nowrap">
                                                                            <form id="<?php echo htmlspecialchars($formId, ENT_QUOTES, 'UTF-8'); ?>" method="post" class="d-inline">
                                                                                <input type="hidden" name="active_tab" value="database">
                                                                                <input type="hidden" name="selected_table" value="<?php echo htmlspecialchars($tablaSeleccionada, ENT_QUOTES, 'UTF-8'); ?>">
                                                                                <input type="hidden" name="original_primary_key_value" value="<?php echo htmlspecialchars((string) ($columnaLlavePrimaria !== null ? ($registro[$columnaLlavePrimaria] ?? '') : ''), ENT_QUOTES, 'UTF-8'); ?>">
                                                                            </form>
                                                                            <div class="btn-group btn-group-sm" role="group">
                                                                                <button form="<?php echo htmlspecialchars($formId, ENT_QUOTES, 'UTF-8'); ?>" type="submit" name="action" value="update" class="btn btn-primary" data-requires-confirmation="true" data-confirmation-message="¿Deseas guardar los cambios realizados en este registro?" <?php echo $accionesDeshabilitadas ? 'disabled' : ''; ?>>Guardar</button>
                                                                                <button form="<?php echo htmlspecialchars($formId, ENT_QUOTES, 'UTF-8'); ?>" type="submit" name="action" value="delete" class="btn btn-outline-danger" data-requires-confirmation="true" data-confirmation-message="¿Seguro que deseas eliminar este registro? Esta acción no se puede deshacer." <?php echo $accionesDeshabilitadas ? 'disabled' : ''; ?>>Eliminar</button>
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="tab-pane fade <?php echo $tabActivo === 'sections' ? 'show active' : ''; ?>" id="sectionsSection" role="tabpanel" aria-labelledby="sections-tab" data-tab-panel="sections">
                                        <div class="card">
                                            <div class="card-body">
                                                <h5 class="card-title">Control de visibilidad del panel lateral</h5>
                                                <p class="card-text">Selecciona las secciones que deben mostrarse en el menú lateral. Los cambios se aplican para todos los usuarios que tengan permiso de acceder a cada sección.</p>

                                                <?php if (empty($listaSecciones)) : ?>
                                                    <div class="alert alert-warning mb-0" role="alert">
                                                        Aún no se han registrado secciones en el sistema.
                                                    </div>
                                                <?php else : ?>
                                                    <form method="post">
                                                        <input type="hidden" name="action" value="update_sections_visibility">
                                                        <input type="hidden" name="active_tab" value="sections">
                                                        <div class="table-responsive">
                                                            <table class="table table-sm align-middle mb-0">
                                                                <thead>
                                                                    <tr>
                                                                        <th class="text-center" style="width: 120px;">Mostrar</th>
                                                                        <th>Sección</th>
                                                                        <th>Slug</th>
                                                                        <th>Ruta</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php foreach ($listaSecciones as $seccion) : ?>
                                                                        <?php
                                                                        $idElemento = 'sectionVisibility_' . (int) $seccion['SECCIONID'];
                                                                        $estaVisible = (int) $seccion['MostrarEnMenu'] === 1;
                                                                        ?>
                                                                        <tr>
                                                                            <td class="text-center">
                                                                                <div class="form-check form-switch d-inline-flex align-items-center justify-content-center">
                                                                                    <input class="form-check-input" type="checkbox" role="switch" id="<?php echo htmlspecialchars($idElemento, ENT_QUOTES, 'UTF-8'); ?>" name="sections_visibility[<?php echo htmlspecialchars($seccion['Slug'], ENT_QUOTES, 'UTF-8'); ?>]" value="1" <?php echo $estaVisible ? 'checked' : ''; ?>>
                                                                                    <label class="visually-hidden" for="<?php echo htmlspecialchars($idElemento, ENT_QUOTES, 'UTF-8'); ?>">
                                                                                        <?php echo htmlspecialchars($seccion['Nombre'], ENT_QUOTES, 'UTF-8'); ?>
                                                                                    </label>
                                                                                </div>
                                                                            </td>
                                                                            <td>
                                                                                <strong><?php echo htmlspecialchars($seccion['Nombre'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                                                                <div class="text-muted small mb-0">Orden: <?php echo (int) $seccion['Orden']; ?></div>
                                                                            </td>
                                                                            <td class="text-muted">
                                                                                <?php echo htmlspecialchars($seccion['Slug'], ENT_QUOTES, 'UTF-8'); ?>
                                                                            </td>
                                                                            <td class="text-muted">
                                                                                <?php echo htmlspecialchars($seccion['Ruta'], ENT_QUOTES, 'UTF-8'); ?>
                                                                            </td>
                                                                        </tr>
                                                                    <?php endforeach; ?>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                        <div class="d-flex justify-content-end mt-3">
                                                            <button type="submit" class="btn btn-primary" data-requires-confirmation="true" data-confirmation-message="Se actualizará la visibilidad del menú lateral. ¿Deseas guardar los cambios?">Guardar cambios</button>
                                                        </div>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="actionConfirmationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar acción</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p id="actionConfirmationMessage" class="mb-0">¿Deseas continuar con esta acción?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="confirmActionButton">Confirmar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Javascripts -->
    <script src="assets/plugins/jquery/jquery-3.7.1.min.js"></script>
    <script src="assets/plugins/bootstrap/js/popper.min.js"></script>
    <script src="assets/plugins/bootstrap/js/bootstrap.min.js"></script>
    <script src="assets/plugins/perfectscroll/perfect-scrollbar.min.js"></script>
    <script src="assets/plugins/pace/pace.min.js"></script>
    <script src="assets/plugins/highlight/highlight.pack.js"></script>
    <script src="assets/js/main.min.js"></script>
    <script src="assets/js/custom.js"></script>
    <script src="App/js/AppCambiarContrasena.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var adminTabsContainer = document.getElementById('adminSubsections');
            var adminTabsContent = document.getElementById('adminSubsectionsContent');

            if (adminTabsContainer && adminTabsContent) {
                var adminTabButtons = adminTabsContainer.querySelectorAll('[data-bs-toggle="tab"][data-bs-target]');
                var adminTabPanes = adminTabsContent.querySelectorAll('.tab-pane');
                var bootstrapAvailable = typeof bootstrap !== 'undefined' && typeof bootstrap.Tab === 'function';

                var obtenerValorTab = function (button) {
                    return button.getAttribute('data-tab-value') || '';
                };

                var establecerTabActivo = function (tabValue) {
                    if (!tabValue) {
                        return;
                    }
                    adminTabsContent.setAttribute('data-active-tab', tabValue);
                };

                var activarTabManual = function (button) {
                    var targetSelector = button.getAttribute('data-bs-target');
                    if (!targetSelector) {
                        return;
                    }

                    var targetPane = adminTabsContent.querySelector(targetSelector);
                    if (!targetPane) {
                        return;
                    }

                    adminTabButtons.forEach(function (otroBoton) {
                        var esActual = otroBoton === button;
                        otroBoton.classList.toggle('active', esActual);
                        otroBoton.setAttribute('aria-selected', esActual ? 'true' : 'false');
                    });

                    adminTabPanes.forEach(function (pane) {
                        var esObjetivo = pane === targetPane;
                        pane.classList.toggle('show', esObjetivo);
                        pane.classList.toggle('active', esObjetivo);
                    });

                    establecerTabActivo(obtenerValorTab(button));
                };

                if (bootstrapAvailable) {
                    adminTabButtons.forEach(function (button) {
                        button.addEventListener('shown.bs.tab', function () {
                            establecerTabActivo(obtenerValorTab(button));
                        });
                    });
                }

                adminTabButtons.forEach(function (button) {
                    button.addEventListener('click', function (event) {
                        if (!bootstrapAvailable) {
                            event.preventDefault();
                            activarTabManual(button);
                            return;
                        }

                        window.setTimeout(function () {
                            establecerTabActivo(obtenerValorTab(button));
                        }, 0);
                    });
                });

                var botonInicial = adminTabsContainer.querySelector('.nav-link.active[data-tab-value]');
                if (botonInicial) {
                    establecerTabActivo(obtenerValorTab(botonInicial));
                }
            }

            var modalElement = document.getElementById('actionConfirmationModal');
            if (!modalElement || typeof bootstrap === 'undefined' || !bootstrap.Modal) {
                return;
            }

            var messageContainer = modalElement.querySelector('#actionConfirmationMessage');
            var confirmButton = modalElement.querySelector('#confirmActionButton');

            if (!messageContainer || !confirmButton) {
                return;
            }

            var confirmationModal = new bootstrap.Modal(modalElement);
            var pendingButton = null;

            var buttons = document.querySelectorAll('[data-requires-confirmation="true"]');
            buttons.forEach(function (button) {
                button.addEventListener('click', function (event) {
                    if (button.disabled) {
                        return;
                    }

                    event.preventDefault();
                    pendingButton = button;
                    var message = button.getAttribute('data-confirmation-message');
                    messageContainer.textContent = message && message.trim().length > 0
                        ? message
                        : '¿Deseas continuar con esta acción?';
                    confirmationModal.show();
                });
            });

            confirmButton.addEventListener('click', function () {
                if (!pendingButton) {
                    confirmationModal.hide();
                    return;
                }

                var form = pendingButton.form;
                if (form) {
                    if (typeof form.requestSubmit === 'function') {
                        form.requestSubmit(pendingButton);
                    } else {
                        if (pendingButton.name) {
                            var hiddenInput = document.createElement('input');
                            hiddenInput.type = 'hidden';
                            hiddenInput.name = pendingButton.name;
                            hiddenInput.value = pendingButton.value;
                            form.appendChild(hiddenInput);
                        }
                        form.submit();
                    }
                }

                pendingButton = null;
                confirmationModal.hide();
            });

            modalElement.addEventListener('hidden.bs.modal', function () {
                pendingButton = null;
            });
        });
    </script>
</body>

</html>
