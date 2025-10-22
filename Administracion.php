<?php
include("includes/HeaderScripts.php");
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

if (!isset($conn) || $conn === false) {
    $mensajesError[] = 'No se pudo establecer conexión con la base de datos. Por favor revisa la configuración.';
} else {
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

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['selected_table'] === $tablaSeleccionada) {
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
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">Administración de bases de datos</h5>
                                        <p class="card-text">Gestiona las tablas disponibles en la base de datos. Puedes editar registros existentes, eliminarlos y restablecer el valor autoincremental cuando sea necesario.</p>

                                        <div class="alert alert-warning" role="alert">
                                            <strong>Advertencia:</strong> Las acciones de esta sección pueden modificar o eliminar información de forma permanente. Revisa cuidadosamente los datos antes de confirmar cualquier cambio.
                                        </div>

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
                                                        <?php if ($columnaAutoIncremental !== null) : ?>
                                                            <form method="post">
                                                                <input type="hidden" name="selected_table" value="<?php echo htmlspecialchars($tablaSeleccionada, ENT_QUOTES, 'UTF-8'); ?>">
                                                                <button type="submit" name="action" value="reset_auto_increment" class="btn btn-outline-secondary btn-sm" data-requires-confirmation="true" data-confirmation-message="¿Seguro que deseas restablecer el valor autoincremental?">
                                                                    Resetear autoincremental
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>
                                                        <form method="post">
                                                            <input type="hidden" name="selected_table" value="<?php echo htmlspecialchars($tablaSeleccionada, ENT_QUOTES, 'UTF-8'); ?>">
                                                            <button type="submit" name="action" value="truncate_table" class="btn btn-outline-danger btn-sm" data-requires-confirmation="true" data-confirmation-message="Esta acción eliminará todos los registros de la tabla seleccionada. ¿Deseas continuar?">
                                                                Vaciar tabla
                                                            </button>
                                                        </form>
                                                    </div>
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
