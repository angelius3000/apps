<?php include("includes/HeaderScripts.php");

$pageTitle = 'Edison - Encuestas';

if (!usuarioTieneAccesoSeccion('encuestas')) {
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
                <form></form>
                <a href="#" class="toggle-search"><i class="material-icons">close</i></a>
            </div>

            <?php include("includes/MenuHeader.php") ?>

            <div class="app-content">
                <div class="content-wrapper">
                    <div class="container-fluid">
                        <div class="row justify-content-center">
                            <div class="col-12 col-xl-8">
                                <div class="page-description text-center mb-4">
                                    <h1>Encuestas</h1>
                                    <p class="text-muted mb-0">Aquí estaremos creando encuestas por categorías para el equipo de trabajo.</p>
                                </div>

                                <div class="card border-0 shadow-sm">
                                    <div class="card-body text-center py-5">
                                        <i class="material-icons-two-tone mb-3" style="font-size:72px;">emoji_events</i>
                                        <h4 class="mb-2">Encuesta activa: El empleado del mes</h4>
                                        <p class="text-muted mb-0">Sección inicial creada. En la siguiente iteración afinaremos los detalles de esta encuesta.</p>
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
