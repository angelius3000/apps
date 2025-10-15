<?php include("includes/HeaderScripts.php");

$pageTitle = 'Edison - Charolas';

$tieneAcceso = usuarioTieneAccesoSeccion('charolas');
if (!$tieneAcceso) {
    header("Location: main.php");
    exit;
}

$charolas = [];
$charolasError = null;
if ($conn) {
    $query_charolas = "SELECT * FROM charolas";
    $resultadoCharolas = mysqli_query($conn, $query_charolas);
    if ($resultadoCharolas) {
        while ($fila = mysqli_fetch_assoc($resultadoCharolas)) {
            $charolas[] = $fila;
        }
        mysqli_free_result($resultadoCharolas);
    } else {
        $charolasError = mysqli_error($conn);
    }
} else {
    $charolasError = $connectionError ?? 'No se pudo conectar a la base de datos.';
}
$totalRows_charolas = count($charolas);

$statusVerificadoNombre = 'Verificado';
$statusVerificadoId = null;
$mensajeRestriccionVerificado = 'Solo un administrador, supervisor o auditor puede asignar el estatus Verificado.';
$tiposPermitidosCambioEstatus = ['administrador', 'supervisor', 'auditor'];
$tipoUsuarioActual = isset($_SESSION['TipoDeUsuario']) ? strtolower(trim((string) $_SESSION['TipoDeUsuario'])) : '';
$puedeCambiarEstatusCharolas = $tipoUsuarioActual !== '' && in_array($tipoUsuarioActual, $tiposPermitidosCambioEstatus, true);
$puedeAsignarVerificado = $puedeCambiarEstatusCharolas;
$statusAuditadoNombre = 'Auditado';
$statusAuditadoId = null;
$mensajeRestriccionAuditado = 'Solo un auditor puede asignar el estatus Auditado.';
$puedeAsignarAuditado = $tipoUsuarioActual === 'auditor';

if ($conn) {
    $stmtStatusVerificado = @mysqli_prepare($conn, 'SELECT STATUSID FROM status WHERE Status = ? LIMIT 1');
    if ($stmtStatusVerificado) {
        mysqli_stmt_bind_param($stmtStatusVerificado, 's', $statusVerificadoNombre);
        mysqli_stmt_execute($stmtStatusVerificado);
        mysqli_stmt_bind_result($stmtStatusVerificado, $statusVerificadoIdTmp);
        if (mysqli_stmt_fetch($stmtStatusVerificado)) {
            $statusVerificadoId = (int) $statusVerificadoIdTmp;
        }
        mysqli_stmt_close($stmtStatusVerificado);
    }

    $stmtStatusAuditado = @mysqli_prepare($conn, 'SELECT STATUSID FROM status WHERE Status = ? LIMIT 1');
    if ($stmtStatusAuditado) {
        mysqli_stmt_bind_param($stmtStatusAuditado, 's', $statusAuditadoNombre);
        mysqli_stmt_execute($stmtStatusAuditado);
        mysqli_stmt_bind_result($stmtStatusAuditado, $statusAuditadoIdTmp);
        if (mysqli_stmt_fetch($stmtStatusAuditado)) {
            $statusAuditadoId = (int) $statusAuditadoIdTmp;
        }
        mysqli_stmt_close($stmtStatusAuditado);
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
                                    <h1>Charolas</h1>
                                </div>
                            </div>
                        </div>
                        <?php if ($charolasError) { ?>
                            <div class="alert alert-warning" role="alert">
                                <?php echo htmlspecialchars($charolasError, ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                        <?php } ?>
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3">
                                <select class="form-select" id="CHAROLASID" aria-label="Selecciona charola" required <?php echo $charolasError ? 'disabled' : ''; ?>>
                                    <option value="">Selecciona charola</option>
                                    <?php foreach ($charolas as $row_charolas) { ?>
                                        <option value="<?php echo $row_charolas['CHAROLASID']; ?>">
                                            <?php echo $row_charolas['SkuCharolas'] . ' - ' . $row_charolas['DescripcionCharolas']; ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <input type="number" class="form-control" id="CantidadCharolas" placeholder="Cantidad a fabricar" <?php echo $charolasError ? 'disabled' : ''; ?>>
                            </div>
                            <div class="col-md-3 mb-3">
                                <button type="button" class="btn btn-primary w-100" id="CalcularBtn" <?php echo $charolasError ? 'disabled' : ''; ?>>Calcular</button>
                            </div>
                        </div>
                        <div class="row mb-4">
                            <div class="col-md-3 ms-auto">
                                <button type="button" class="btn btn-success w-100" id="GenerarRequisicionBtn" <?php echo $charolasError ? 'disabled' : ''; ?>>Generar requisición</button>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <table class="table" id="TablaMateriaPrima">
                                    <thead>
                                        <tr>
                                            <th>SKU MP</th>
                                            <th>Descripción</th>
                                            <th>Tipo</th>
                                            <th>Cantidad</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="row mt-4">
                            <div class="col">
                                <h5>Requisiciones de armado</h5>
                                <table class="table" id="TablaOrdenesCharolas">
                                    <thead>
                                        <tr>
                                            <th scope="col" class="text-center detalle-control"><span class="visually-hidden">Detalle</span></th>
                                            <th scope="col">Requisición</th>
                                            <th scope="col">SKU</th>
                                            <th scope="col">Descripción</th>
                                            <th scope="col">Cantidad</th>
                                            <th scope="col">Cambiar estatus</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include("App/Modales/ModalesCharolas.php"); ?>

    <script>
        window.charolasConfig = <?php echo json_encode([
            'statusVerificadoId' => $statusVerificadoId,
            'nombreStatusVerificado' => $statusVerificadoNombre,
            'puedeCambiarEstatus' => $puedeCambiarEstatusCharolas,
            'puedeAsignarVerificado' => $puedeAsignarVerificado,
            'mensajeRestriccionVerificado' => $mensajeRestriccionVerificado,
            'statusAuditadoId' => $statusAuditadoId,
            'nombreStatusAuditado' => $statusAuditadoNombre,
            'puedeAsignarAuditado' => $puedeAsignarAuditado,
            'mensajeRestriccionAuditado' => $mensajeRestriccionAuditado,
        ], JSON_UNESCAPED_UNICODE); ?>;
    </script>

    <!-- Javascripts -->
    <script src="assets/plugins/jquery/jquery-3.7.1.min.js"></script>
    <script src="assets/plugins/bootstrap/js/popper.min.js"></script>
    <script src="assets/plugins/bootstrap/js/bootstrap.min.js"></script>
    <script src="assets/plugins/perfectscroll/perfect-scrollbar.min.js"></script>
    <script src="assets/plugins/pace/pace.min.js"></script>
    <script src="assets/plugins/highlight/highlight.pack.js"></script>
    <script type="text/javascript" charset="utf8" src="assets/js/jquery.dataTables.js"></script>
    <script src="assets/js/main.min.js"></script>
    <script src="assets/js/custom.js"></script>
    <script src="assets/js/pages/datatables.js"></script>
    <script type="text/javascript" charset="utf8" src="assets/js/dataTables.buttons.min.js"></script>
    <script type="text/javascript" charset="utf8" src="assets/js/buttons.html5.min.js"></script>
    <script type="text/javascript" charset="utf8" src="assets/js/jszip.min.js"></script>
    <script type="text/javascript" charset="utf8" src="assets/js/pdfmake.min.js"></script>
    <script type="text/javascript" charset="utf8" src="assets/js/vfs_fonts.js"></script>
    <script type="text/javascript" charset="utf8" src="assets/js/dataTables.responsive.min.js"></script>
    <script src="assets/js/select2.min.js"></script>
    <script src="App/js/AppCharolas.js"></script>
    <script src="App/js/AppCambiarContrasena.js"></script>
</body>
</html>
