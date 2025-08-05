<?php include("includes/HeaderScripts.php");

$pageTitle = 'Edison - Reparto';

$query_clientes = "SELECT * FROM clientes";
$clientes = mysqli_query($conn, $query_clientes) or die(mysqli_error($conn));
$totalRows_clientes = mysqli_num_rows($clientes);

$query_status = "SELECT * FROM status";
$status = mysqli_query($conn, $query_status) or die(mysqli_error($conn));
$totalRows_status = mysqli_num_rows($status);

$query_Repartidores = "SELECT * FROM usuarios 
                        WHERE TIPODEUSUARIOID = 2 AND Deshabilitado = 0";
$Repartidores = mysqli_query($conn, $query_Repartidores) or die(mysqli_error($conn));
$totalRows_Repartidores = mysqli_num_rows($Repartidores);

// Fecha De hoy
$FechaHoy = date("Y-m-d");

$query_productos = "SELECT * FROM productos";
$productos = mysqli_query($conn, $query_productos) or die(mysqli_error($conn));
$totalRows_productos = mysqli_num_rows($productos);

if ($_SESSION['TIPOUSUARIO'] == 3) {

    $ClaseDeBody = "sidebar-hidden";
    $ClaseDeLogo = "hidden-sidebar-logo";
    $IconoFlecha = "last_page";
} else {

    $ClaseDeBody = "";
    $ClaseDeLogo = "";
    $IconoFlecha = "first_page";
}

$query_TipoDeStatus = "SELECT * FROM status";
$TipoDeStatus = mysqli_query($conn, $query_TipoDeStatus) or die(mysqli_error($conn));
$totalRows_TipoDeStatus = mysqli_num_rows($TipoDeStatus);

$query_Solicitantes = "SELECT * FROM usuarios WHERE usuarios.TIPODEUSUARIOID != 4 AND usuarios.TIPODEUSUARIOID != 2";
$Solicitantes = mysqli_query($conn, $query_Solicitantes) or die(mysqli_error($conn));
$totalRows_Solicitantes = mysqli_num_rows($Solicitantes);


?>

<!DOCTYPE html>
<html lang="en">

<?php include("includes/Header.php") ?>

<body>
    <div class="app full-width-header align-content-stretch d-flex flex-wrap <?php echo $ClaseDeBody; ?>">
        <div class="app-sidebar">
            <div class="logo logo-sm <?php echo $ClaseDeLogo; ?>">
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
                                <div class="page-description">
                                    <h2>Repartos</h2>
                                    <?php // ESTE ES EL QUE IMPRIME LAS SESSIONES VARIABLES
                                    //echo '<pre>' . print_r($_SESSION, TRUE) . '</pre>';
                                    ?>
                                </div>
                            </div>
                        </div>

                        <?php if ($_SESSION['TIPOUSUARIO'] == 1 || $_SESSION['TIPOUSUARIO'] == 3) { ?>

                            <div class="row">
                                <div class="col">
                                    <button type="button" class="btn btn-sm btn-primary waves-effect width-md waves-light" data-bs-toggle="modal" data-bs-target="#ModalAgregarReparto"><i class="material-icons-two-tone">add</i> Agregar Reparto</button>

                                </div>
                            </div>

                        <?php  } ?>

                        <br>
                        <div class="row">

                            <?php if ($_SESSION['TIPOUSUARIO'] == 4) { ?>
                                <table id="RepartosCliente2DT" class="display" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>Folio</th> <!-- (0) -->
                                            <th>Estatus</th> <!-- (1) -->
                                            <th>Dirección</th> <!-- (2) -->
                                            <th>Fecha de registro</th> <!-- (5) -->
                                            <th>Fecha de reparto</th> <!-- (6) -->
                                            <th>Hora de reparto</th> <!-- (7) -->
                                            <th>Solicitante</th> <!-- (8) -->
                                            <th>Receptor</th> <!-- (11) -->
                                            <th>Teléfono receptor</th> <!-- (12) -->
                                            <th>Telefono alternativo</th> <!-- (13) -->
                                            <th>Numero de factura</th> <!-- (14) -->
                                            <th>Comentarios</th> <!-- (15) -->
                                        </tr>
                                    </thead>
                                </table>

                            <?php } elseif ($_SESSION['TIPOUSUARIO'] == 2) { ?>
                                <table id="RepartosRepartidor2DT" class="display" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>Folio</th> <!-- (0) -->
                                            <th>Estatus</th> <!-- (1) -->
                                            <th>Dirección</th> <!-- (2) -->
                                            <th>CP</th> <!-- (10) -->
                                            <th>Receptor</th> <!-- (11) -->
                                            <th>Teléfono receptor</th> <!-- (12) -->
                                            <th>Repartidor</th> <!-- (4) -->
                                            <th>Surtidor</th> <!-- (3) -->
                                            <th>Fecha de registro</th> <!-- (5) -->
                                            <th>Fecha de reparto</th> <!-- (6) -->
                                            <th>Hora de reparto</th> <!-- (7) -->
                                            <th>Solicitante</th> <!-- (8) -->
                                            <th>Cliente</th> <!-- (9) -->
                                            <th>Telefono alternativo</th> <!-- (13) -->
                                            <th>Numero de factura</th> <!-- (14) -->
                                            <th>Comentarios</th> <!-- (15) -->
                                        </tr>
                                    </thead>
                                </table>

                            <?php } else { ?>


                                <div class="row">

                                    <?php if ($_SESSION['TIPOUSUARIO'] != 2) { ?>

                                        <div class="col-lg-4 col-sm-12 mb-4">
                                            <select class="form-select" name="STATUSID" id="STATUSID" aria-label="Default select example" required>
                                                <option selected value="">Selecciona Estatus</option>

                                                <?php while ($row_TipoDeStatus = mysqli_fetch_assoc($TipoDeStatus)) { ?>

                                                    <option value="<?php echo $row_TipoDeStatus['STATUSID']; ?>"><?php echo $row_TipoDeStatus['Status']; ?></option>

                                                <?php }

                                                // Reset the pointer to the beginning
                                                mysqli_data_seek($TipoDeStatus, 0);

                                                ?>

                                            </select>
                                        </div>

                                    <?php } ?>

                                    <div class="col-lg-4 col-sm-12 mb-4">
                                        <select class="form-select" name="Repartidores" id="Repartidores" aria-label="Default select example" required>
                                            <option selected value="">Selecciona Repartidor</option>

                                            <?php while ($row_Repartidores = mysqli_fetch_assoc($Repartidores)) { ?>

                                                <option value="<?php echo $row_Repartidores['USUARIOID']; ?>"><?php echo $row_Repartidores['PrimerNombre'] . ' ' . $row_Repartidores['ApellidoPaterno']; ?></option>

                                            <?php }

                                            // Reset the pointer to the beginning
                                            mysqli_data_seek($Repartidores, 0);

                                            ?>

                                        </select>
                                    </div>

                                    <?php if ($_SESSION['TIPOUSUARIO'] != 2) { ?>

                                        <div class="col-lg-4 col-sm-12 mb-4">
                                            <select class="form-select" name="Solicitantes" id="Solicitantes" aria-label="Default select example" required>
                                                <option selected value="">Selecciona Solicitante</option>

                                                <?php while ($row_Solicitantes = mysqli_fetch_assoc($Solicitantes)) { ?>

                                                    <option value="<?php echo $row_Solicitantes['USUARIOID']; ?>"><?php echo $row_Solicitantes['PrimerNombre'] . ' ' . $row_Solicitantes['ApellidoPaterno']; ?></option>

                                                <?php }

                                                // Reset the pointer to the beginning
                                                mysqli_data_seek($Solicitantes, 0);

                                                ?>

                                            </select>
                                        </div>

                                    <?php } ?>

                                </div>

                                <?php if ($_SESSION['TIPOUSUARIO'] != 2) { ?>
                                    <div class="row">
                                        <div class="col-lg-6 col-sm-12 mb-4 border-right pe-4">
                                            <label for="Fecha" class="form-label">Fecha de Registro</label>
                                            <input class="form-control flatpickr1" id="FechaInicioRegistro" type="text" placeholder="Inicio">
                                            <input class="form-control flatpickr1" id="FechaFinalRegistro" type="text" placeholder="Final">
                                        </div>

                                        <div class="col-lg-6 col-sm-12 mb-4 ps-4">
                                            <label for="FechaInicioReparto" class="form-label">Fecha de Reparto</label>
                                            <input class="form-control flatpickr1" id="FechaInicioReparto" type="text" placeholder="Inicio">
                                            <input class="form-control flatpickr1" id="FechaFinalReparto" type="text" placeholder="Final">
                                        </div>

                                    </div>

                                <?php } ?>

                                <table id="Repartos2DT" class="table table-striped" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>Folio</th> <!-- (0) -->
                                            <th>Estatus</th> <!-- (1) -->
                                            <th>Dirección</th> <!-- (2) -->
                                            <th>Surtidor</th> <!-- (3) -->
                                            <th>Repartidor</th> <!-- (4) -->
                                            <th>Fecha de registro</th> <!-- (5) -->
                                            <th>Fecha de reparto</th> <!-- (6) -->
                                            <th>Hora de reparto</th> <!-- (7) -->
                                            <th>Solicitante</th> <!-- (8) -->
                                            <th>Cliente</th> <!-- (9) -->
                                            <th>CP</th> <!-- (10) -->
                                            <th>Receptor</th> <!-- (11) -->
                                            <th>Teléfono receptor</th> <!-- (12) -->
                                            <th>Telefono alternativo</th> <!-- (13) -->
                                            <th>Numero de factura</th> <!-- (14) -->
                                            <th>Comentarios</th> <!-- (15) -->
                                            <th></th> <!-- Botones (16) -->
                                        </tr>
                                    </thead>
                                </table>

                            <?php } ?>


                        </div>
                        <div class="row">
                            <div class="col">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include("App/Modales/ModalesRepartos.php") ?>

    <!-- Javascripts -->
    <!-- <script src="assets/plugins/jquery/jquery-3.5.1.min.js"></script> -->

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

    <!-- DataTables Buttons JS -->
    <script type="text/javascript" charset="utf8" src="assets/js/dataTables.buttons.min.js"></script>
    <script type="text/javascript" charset="utf8" src="assets/js/buttons.html5.min.js"></script>
    <!-- JSZip for Excel export -->
    <script type="text/javascript" charset="utf8" src="assets/js/jszip.min.js"></script>
    <!-- pdfmake for PDF export -->
    <script type="text/javascript" charset="utf8" src="assets/js/pdfmake.min.js"></script>
    <script type="text/javascript" charset="utf8" src="assets/js/vfs_fonts.js"></script>

    <script type="text/javascript" charset="utf8" src="assets/js/dataTables.responsive.min.js"></script>



    <script src="assets/js/select2.min.js" integrity="sha512-9p/L4acAjbjIaaGXmZf0Q2bV42HetlCLbv8EP0z3rLbQED2TAFUlDvAezy7kumYqg5T8jHtDdlm1fgIsr5QzKg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>


    <script src="assets/js/parsley.js" integrity="sha512-Fq/wHuMI7AraoOK+juE5oYILKvSPe6GC5ZWZnvpOO/ZPdtyA29n+a5kVLP4XaLyDy9D1IBPYzdFycO33Ijd0Pg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <script src="assets/plugins/flatpickr/flatpickr.js"></script>

    <script src="App/js/AppRepartos.js"></script>
    <script src="App/js/AppCambiarContrasena.js"></script>

</body>

</html>