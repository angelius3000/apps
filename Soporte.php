<?php include("includes/HeaderScripts.php");

if (!usuarioTieneAccesoSeccion('soporte')) {
    header("Location: main.php");
    exit;
}

$tipoUsuarioActual = strtolower(trim((string)($_SESSION['TipoDeUsuario'] ?? '')));
$tipoUsuarioSesionId = (int)($_SESSION['TIPOUSUARIO'] ?? 0);

if ($tipoUsuarioActual === '' && $tipoUsuarioSesionId > 0) {
    $consultaTipoUsuario = mysqli_prepare(
        $conn,
        'SELECT TipoDeUsuario FROM tipodeusuarios WHERE TIPODEUSUARIOID = ? LIMIT 1'
    );

    if ($consultaTipoUsuario) {
        mysqli_stmt_bind_param($consultaTipoUsuario, 'i', $tipoUsuarioSesionId);
        mysqli_stmt_execute($consultaTipoUsuario);
        mysqli_stmt_bind_result($consultaTipoUsuario, $tipoUsuarioRecuperado);

        if (mysqli_stmt_fetch($consultaTipoUsuario)) {
            $tipoUsuarioActual = strtolower(trim((string) $tipoUsuarioRecuperado));
        }

        mysqli_stmt_close($consultaTipoUsuario);
    }
}

$esAdmin = in_array($tipoUsuarioActual, ['soporte it', 'administrador'], true);

$usuariosActivos = [];
$consultaUsuarios = mysqli_query(
    $conn,
    "SELECT usuarios.USUARIOID, usuarios.PrimerNombre, usuarios.ApellidoPaterno
     FROM usuarios
     LEFT JOIN tipodeusuarios ON usuarios.TIPODEUSUARIOID = tipodeusuarios.TIPODEUSUARIOID
     WHERE usuarios.Deshabilitado = 0
       AND LOWER(TRIM(tipodeusuarios.TipoDeUsuario)) = 'soporte it'
     ORDER BY usuarios.ApellidoPaterno, usuarios.PrimerNombre"
);

if ($consultaUsuarios) {
    while ($filaUsuario = mysqli_fetch_assoc($consultaUsuarios)) {
        $usuariosActivos[] = $filaUsuario;
    }
    mysqli_free_result($consultaUsuarios);
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
                                    <h2>Soporte</h2>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col">
                                <button type="button" class="btn btn-sm btn-primary waves-effect width-md waves-light" data-bs-toggle="modal" data-bs-target="#ModalAgregarTicket"><i class="material-icons-two-tone">add</i> Levantar Ticket</button>
                            </div>
                        </div>
                        <br>
                        <div class="row">
                            <table id="TicketsDT" data-es-admin="<?php echo $esAdmin ? 1 : 0; ?>" class="table table-striped" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Folio</th>
                                        <th>Título</th>
                                        <th>Prioridad</th>
                                        <th>Categoría</th>
                                        <th>Status</th>
                                        <th>Creado por</th>
                                        <th>Asignado a</th>
                                        <th>Fecha</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include("App/Modales/ModalesSoporte.php") ?>

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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/parsley.js/2.9.2/parsley.js" integrity="sha512-Fq/wHuMI7AraoOK+juE5oYILKvSPe6GC5ZWZnvpOO/ZPdtyA29n+a5kVLP4XaLyDy9D1IBPYzdFycO33Ijd0Pg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="App/js/AppSoporte.js"></script>
</body>

</html>
