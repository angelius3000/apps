<?php include("includes/HeaderScripts.php");

$pageTitle = 'Edison - Apps';

if (!usuarioTieneAccesoSeccion('aplicaciones')) {
    header("Location: index.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

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
                    <!-- <input class="form-control" type="text" placeholder="Type here..." aria-label="Search"> -->
                </form>
                <a href="#" class="toggle-search"><i class="material-icons">close</i></a>
            </div>

            <?php include("includes/MenuHeader.php") ?>

            <div class="app-content">
                <div class="content-wrapper">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col">
                                <div class="page-description text-center">
                                    <h1>Aplicaciones</h1>
                                    <div class="row justify-content-center mt-4">
                                        <?php
                                        $permisosSecciones = $_SESSION['PermisosSecciones'] ?? [];
                                        $seccionesVisibles = $_SESSION['SeccionesVisibles'] ?? [];
                                        $apps = [
                                            [
                                                'slug' => 'conteo',
                                                'ruta' => 'Conteo.php',
                                                'icono' => 'assessment',
                                                'nombre' => 'Conteo',
                                            ],
                                            [
                                                'slug' => 'reparto',
                                                'ruta' => 'Repartos.php',
                                                'icono' => 'local_shipping',
                                                'nombre' => 'Reparto',
                                            ],
                                            [
                                                'slug' => 'charolas',
                                                'ruta' => 'charolas.php',
                                                'icono' => 'view_day',
                                                'nombre' => 'Charolas',
                                            ],
                                            [
                                                'slug' => 'materialpendiente',
                                                'ruta' => 'MaterialPendiente.php',
                                                'icono' => 'pending_actions',
                                                'nombre' => 'Material Pendiente',
                                            ],
                                        ];

                                        foreach ($apps as $app) {
                                            $slug = $app['slug'];
                                            $mostrar = !isset($permisosSecciones[$slug]) || (int)$permisosSecciones[$slug] === 1;

                                            if (isset($seccionesVisibles[$slug]) && (int)$seccionesVisibles[$slug] !== 1) {
                                                $mostrar = false;
                                            }

                                            if (!$mostrar) {
                                                continue;
                                            }
                                            ?>
                                            <div class="col-auto mb-3">
                                                <a href="<?php echo htmlspecialchars($app['ruta'], ENT_QUOTES, 'UTF-8'); ?>" class="card app-card text-decoration-none text-dark bg-transparent border-0">
                                                    <div class="card-body">
                                                        <i class="material-icons-two-tone" style="font-size:72px;"><?php echo htmlspecialchars($app['icono'], ENT_QUOTES, 'UTF-8'); ?></i>
                                                        <h5 class="mt-1"><?php echo htmlspecialchars($app['nombre'], ENT_QUOTES, 'UTF-8'); ?></h5>
                                                    </div>
                                                </a>
                                            </div>
                                        <?php } ?>
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

    <script src="App/js/AppCambiarContrasena.js"></script>

</body>

</html>
