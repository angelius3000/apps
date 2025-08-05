<?php include("includes/HeaderScripts.php");

if ($_SESSION['TIPOUSUARIO'] != 1) {
    header("Location: index.php");
}

$query_TipoDeUsuario = "SELECT * FROM tipodeusuarios";
$TipoDeUsuario = mysqli_query($conn, $query_TipoDeUsuario) or die(mysqli_error($conn));
$totalRows_TipoDeUsuario = mysqli_num_rows($TipoDeUsuario);

$query_clientes = "SELECT * FROM clientes";
$clientes = mysqli_query($conn, $query_clientes) or die(mysqli_error($conn));
$totalRows_clientes = mysqli_num_rows($clientes);


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
                                <div class="page-description">
                                    <h2>Usuarios</h2>

                                </div>
                            </div>
                        </div>


                        <div class="row">
                            <div class="col">
                                <button type="button" class="btn btn-sm btn-primary waves-effect width-md waves-light" data-bs-toggle="modal" data-bs-target="#ModalAgregarUsuarios"><i class="material-icons-two-tone">add</i> Agregar Usuario</button>

                            </div>
                        </div>
                        <br>
                        <div class="row">
                            <table id="UsuariosDT" class="table table-striped" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>email</th>
                                        <th>Tipo de usuario</th>
                                        <th>Empresa</th>
                                        <th></th>
                                        <th></th>
                                    </tr>
                                </thead>
                            </table>
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

    <?php include("App/Modales/ModalesUsuarios.php") ?>

    <!-- Javascripts -->
    <script src="assets/plugins/jquery/jquery-3.5.1.min.js"></script>
    <script src="assets/plugins/bootstrap/js/popper.min.js"></script>
    <script src="assets/plugins/bootstrap/js/bootstrap.min.js"></script>
    <script src="assets/plugins/perfectscroll/perfect-scrollbar.min.js"></script>
    <script src="assets/plugins/pace/pace.min.js"></script>
    <script src="assets/plugins/highlight/highlight.pack.js"></script>

    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.js"></script>

    <script src="assets/js/main.min.js"></script>
    <script src="assets/js/custom.js"></script>

    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/responsive/2.2.7/js/dataTables.responsive.min.js"></script>

    <script src="assets/js/pages/datatables.js"></script>
    <script src="assets/js/select2.min.js" integrity="sha512-9p/L4acAjbjIaaGXmZf0Q2bV42HetlCLbv8EP0z3rLbQED2TAFUlDvAezy7kumYqg5T8jHtDdlm1fgIsr5QzKg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/parsley.js/2.9.2/parsley.js" integrity="sha512-Fq/wHuMI7AraoOK+juE5oYILKvSPe6GC5ZWZnvpOO/ZPdtyA29n+a5kVLP4XaLyDy9D1IBPYzdFycO33Ijd0Pg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <script src="App/js/AppUsuarios.js"></script>
    <script src="App/js/AppCambiarContrasena.js"></script>

</body>

</html>