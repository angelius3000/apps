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
                                        <div class="col-auto mb-3">
                                            <a href="Conteo.php" class="card app-card text-decoration-none text-dark bg-transparent border-0">
                                                <div class="card-body">
                                                    <i class="material-icons-two-tone" style="font-size:72px;">assessment</i>
                                                    <h5 class="mt-1">Conteo</h5>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-auto mb-3">
                                            <a href="Repartos.php" class="card app-card text-decoration-none text-dark bg-transparent border-0">
                                                <div class="card-body">
                                                    <i class="material-icons-two-tone" style="font-size:72px;">local_shipping</i>
                                                    <h5 class="mt-1">Reparto</h5>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-auto mb-3">
                                            <a href="charolas.php" class="card app-card text-decoration-none text-dark bg-transparent border-0">
                                                <div class="card-body">
                                                    <i class="material-icons-two-tone" style="font-size:72px;">view_day</i>
                                                    <h5 class="mt-1">Charolas</h5>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-auto mb-3">
                                            <a href="MaterialPendiente.php" class="card app-card text-decoration-none text-dark bg-transparent border-0">
                                                <div class="card-body">
                                                    <i class="material-icons-two-tone" style="font-size:72px;">pending_actions</i>
                                                    <h5 class="mt-1">Material Pendiente</h5>
                                                </div>
                                            </a>
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
