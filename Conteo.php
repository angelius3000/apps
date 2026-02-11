<?php include("includes/HeaderScripts.php");

$pageTitle = 'Edison - Conteo';

if (!usuarioTieneAccesoSeccion('conteo')) {
    header("Location: index.php");
    exit;
}

require_once __DIR__ . '/App/Server/ConteoHelpers.php';

if ($conn instanceof mysqli) {
    establecerZonaHorariaConteo();
    $nombreBaseDatos = $dbname ?? '';
    asegurarTablaConteo($conn, $nombreBaseDatos);
    $fechaActual = obtenerFechaActualConteo();
    asegurarFilasConteo($conn, $fechaActual);
    $registrosConteo = obtenerConteoPorFecha($conn, $fechaActual);
} else {
    $fechaActual = '';
    $registrosConteo = [];
}

$rangosConteo = obtenerRangosHorasConteo();
$registrosIndexados = [];
foreach ($registrosConteo as $registro) {
    $registrosIndexados[$registro['horaInicio']] = $registro;
}

$perfilesConExportacion = [1, 5, 8];
$tipoUsuarioId = isset($_SESSION['TIPOUSUARIO']) ? (int) $_SESSION['TIPOUSUARIO'] : 0;
$puedeExportarConteo = in_array($tipoUsuarioId, $perfilesConExportacion, true);

?>
<!DOCTYPE html>
<html lang="es">

<?php include("includes/Header.php") ?>

<body>
    <div class="app full-width-header align-content-stretch d-flex flex-wrap">
        <div class="app-sidebar">
            <div class="logo logo-sm">
                <a href="main.php"> <img src="App/Graficos/Logo/LogoEdison.png" style="max-width :130px;"> </a>
            </div>

            <?php include("includes/Menu.php") ?>

        </div>
        <div class="app-container">
            <div class="search">
                <form>
                </form>
                <a href="#" class="toggle-search"><i class="material-icons">close</i></a>
            </div>

            <?php include("includes/MenuHeader.php") ?>

            <div class="app-content">
                <div class="content-wrapper">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col">
                                <div class="page-description">
                                    <h1>Conteo</h1>
                                    <p class="text-muted mb-2">Registro diario por hora de personas que visitan la tienda.</p>
                                    <div class="d-flex flex-column flex-md-row gap-2 align-items-md-center">
                                        <span class="badge bg-primary" id="conteoFechaHora" data-timezone="America/Denver">Cargando fecha y hora...</span>
                                        <?php if (!$conn) : ?>
                                            <span class="badge bg-danger">Sin conexión a base de datos</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col">
                                <div class="card">
                                    <div class="card-body">
                                        <?php if ($puedeExportarConteo) : ?>
                                            <form action="App/Server/ServerExportarConteoExcel.php" method="GET" class="row g-3 align-items-end mb-4">
                                                <div class="col-12 col-md-auto">
                                                    <label class="form-label fw-semibold" for="fechaConteoExportar">Fecha para exportar</label>
                                                    <input type="date" class="form-control" id="fechaConteoExportar" name="fecha"
                                                        value="<?php echo htmlspecialchars($fechaActual, ENT_QUOTES, 'UTF-8'); ?>" required>
                                                </div>
                                                <div class="col-12 col-md-auto">
                                                    <button type="submit" class="btn btn-success">Exportar Excel</button>
                                                </div>
                                            </form>
                                        <?php endif; ?>
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-start">
                                                    <div class="d-flex flex-column align-items-start gap-2">
                                                        <button class="btn btn-primary conteo-btn" data-tipo="hombre" data-accion="sumar">+ Hombre</button>
                                                        <button class="btn btn-outline-primary conteo-btn" data-tipo="hombre" data-accion="restar">- Hombre</button>
                                                    </div>
                                                    <div class="d-flex flex-column align-items-start gap-2">
                                                        <button class="btn btn-primary conteo-btn" data-tipo="mujer" data-accion="sumar">+ Mujer</button>
                                                        <button class="btn btn-outline-primary conteo-btn" data-tipo="mujer" data-accion="restar">- Mujer</button>
                                                    </div>
                                                    <div class="d-flex flex-column align-items-start gap-2">
                                                        <button class="btn btn-secondary conteo-btn" data-tipo="pareja" data-accion="sumar">+ Pareja</button>
                                                        <button class="btn btn-outline-secondary conteo-btn" data-tipo="pareja" data-accion="restar">- Pareja</button>
                                                    </div>
                                                    <div class="d-flex flex-column align-items-start gap-2">
                                                        <button class="btn btn-info text-white conteo-btn" data-tipo="familia" data-accion="sumar">+ Familia</button>
                                                        <button class="btn btn-outline-info conteo-btn" data-tipo="familia" data-accion="restar">- Familia</button>
                                                    </div>
                                                    <div class="d-flex flex-column align-items-start gap-2">
                                                        <button class="btn btn-warning conteo-btn" data-tipo="cuadrilla" data-accion="sumar">+ Cuadrilla</button>
                                                        <button class="btn btn-outline-warning conteo-btn" data-tipo="cuadrilla" data-accion="restar">- Cuadrilla</button>
                                                    </div>
                                                    <div id="conteoIndicadorExito" class="d-none text-success fw-semibold align-items-center gap-1 ms-lg-2 mt-2 mt-lg-0" aria-live="polite">
                                                        <span class="fs-5" aria-hidden="true">✓</span>
                                                        <span>Actualizado</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="table-responsive mt-4">
                                            <table class="table table-bordered align-middle" id="conteoTabla" data-fecha="<?php echo htmlspecialchars($fechaActual, ENT_QUOTES, 'UTF-8'); ?>">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th rowspan="2" class="text-center">Hora</th>
                                                        <th colspan="2" class="text-center">Individual</th>
                                                        <th rowspan="2" class="text-center">Pareja</th>
                                                        <th rowspan="2" class="text-center">Familia</th>
                                                        <th rowspan="2" class="text-center">Cuadrilla</th>
                                                        <th rowspan="2" class="text-center">Total</th>
                                                    </tr>
                                                    <tr>
                                                        <th class="text-center">Hombre</th>
                                                        <th class="text-center">Mujer</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($rangosConteo as $rango) : ?>
                                                        <?php
                                                        $registro = $registrosIndexados[$rango['horaInicio']] ?? [
                                                            'hombre' => 0,
                                                            'mujer' => 0,
                                                            'pareja' => 0,
                                                            'familia' => 0,
                                                            'cuadrilla' => 0,
                                                        ];
                                                        $totalFila = $registro['hombre'] + $registro['mujer'] + $registro['pareja'] + $registro['familia'] + $registro['cuadrilla'];
                                                        ?>
                                                        <tr data-hora-inicio="<?php echo htmlspecialchars($rango['horaInicio'], ENT_QUOTES, 'UTF-8'); ?>">
                                                            <td class="text-center fw-semibold"><?php echo htmlspecialchars($rango['etiqueta'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                            <td class="text-center" data-campo="hombre"><?php echo (int) $registro['hombre']; ?></td>
                                                            <td class="text-center" data-campo="mujer"><?php echo (int) $registro['mujer']; ?></td>
                                                            <td class="text-center" data-campo="pareja"><?php echo (int) $registro['pareja']; ?></td>
                                                            <td class="text-center" data-campo="familia"><?php echo (int) $registro['familia']; ?></td>
                                                            <td class="text-center" data-campo="cuadrilla"><?php echo (int) $registro['cuadrilla']; ?></td>
                                                            <td class="text-center fw-bold" data-campo="total"><?php echo (int) $totalFila; ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="alert alert-info mt-3 mb-0" id="conteoMensaje" role="alert" style="display:none;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Javascripts -->
    <script src="assets/plugins/jquery/jquery-3.5.1.min.js"></script>
    <script src="assets/plugins/bootstrap/js/popper.min.js"></script>
    <script src="assets/plugins/bootstrap/js/bootstrap.min.js"></script>
    <script src="assets/plugins/perfectscroll/perfect-scrollbar.min.js"></script>
    <script src="assets/plugins/pace/pace.min.js"></script>
    <script src="assets/plugins/highlight/highlight.pack.js"></script>
    <script src="assets/js/main.min.js"></script>
    <script src="assets/js/custom.js"></script>

    <script src="App/js/AppConteo.js"></script>
</body>

</html>
