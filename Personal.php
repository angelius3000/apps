<?php include("includes/HeaderScripts.php");

if (!usuarioTieneAccesoSeccion('personal')) {
    header("Location: main.php");
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

                        <div class="row">
                            <div class="col">
                                <div class="page-description">
                                    <h2>Personal</h2>
                                    <span>Administra catálogos de personal por área.</span>
                                </div>
                            </div>
                        </div>

                        <?php
                        $gruposPersonal = [
                            'aduanas' => 'Personal de aduanas',
                            'vendedor' => 'Vendedor',
                            'surtidor' => 'Surtidor',
                            'almacenista' => 'Almacenista',
                        ];

                        foreach ($gruposPersonal as $claveGrupo => $nombreGrupo) {
                            ?>
                            <div class="row mt-4">
                                <div class="col">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h5 class="mb-0"><?php echo htmlspecialchars($nombreGrupo, ENT_QUOTES, 'UTF-8'); ?></h5>
                                        <button type="button" class="btn btn-sm btn-primary btn-agregar-personal" data-tipo="<?php echo htmlspecialchars($claveGrupo, ENT_QUOTES, 'UTF-8'); ?>">
                                            <i class="material-icons-two-tone">add</i> Agregar
                                        </button>
                                    </div>
                                    <table id="PersonalDT_<?php echo htmlspecialchars($claveGrupo, ENT_QUOTES, 'UTF-8'); ?>" class="table table-striped personal-dt" style="width:100%" data-tipo="<?php echo htmlspecialchars($claveGrupo, ENT_QUOTES, 'UTF-8'); ?>">
                                        <thead>
                                            <tr>
                                                <th>Nombre</th>
                                                <th>Estatus</th>
                                                <th style="min-width: 260px;"></th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                            <?php
                        }
                        ?>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include("App/Modales/ModalesPersonal.php") ?>

    <script src="assets/plugins/jquery/jquery-3.5.1.min.js"></script>
    <script src="assets/plugins/bootstrap/js/popper.min.js"></script>
    <script src="assets/plugins/bootstrap/js/bootstrap.min.js"></script>
    <script src="assets/plugins/perfectscroll/perfect-scrollbar.min.js"></script>
    <script src="assets/plugins/pace/pace.min.js"></script>
    <script src="assets/plugins/highlight/highlight.pack.js"></script>

    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/responsive/2.2.7/js/dataTables.responsive.min.js"></script>

    <script src="assets/js/main.min.js"></script>
    <script src="assets/js/custom.js"></script>
    <script src="assets/js/pages/datatables.js"></script>

    <script src="App/js/AppPersonal.js"></script>
    <script src="App/js/AppCambiarContrasena.js"></script>
</body>

</html>
