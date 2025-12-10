<?php include("includes/HeaderScripts.php");

$pageTitle = 'Edison - Material Pendiente';

if (!usuarioTieneAccesoSeccion('materialpendiente')) {
    header("Location: main.php");
    exit;
}

$queryProductosPendientes = "SELECT PRODUCTOSID, Sku, Descripcion FROM productos ORDER BY Sku ASC, Descripcion ASC";
$resultadoProductosPendientes = mysqli_query($conn, $queryProductosPendientes);

$listaProductosPendientes = [];
if ($resultadoProductosPendientes instanceof mysqli_result) {
    while ($rowProductoPendiente = mysqli_fetch_assoc($resultadoProductosPendientes)) {
        $listaProductosPendientes[] = $rowProductoPendiente;
    }
    mysqli_free_result($resultadoProductosPendientes);
}

$queryClientesPendientes = "SELECT CLIENTEID, CLIENTESIAN, CLCSIAN, NombreCliente FROM clientes ORDER BY CLIENTESIAN ASC, NombreCliente ASC";
$resultadoClientesPendientes = mysqli_query($conn, $queryClientesPendientes);

$listaClientesPendientes = [];
if ($resultadoClientesPendientes instanceof mysqli_result) {
    while ($rowClientePendiente = mysqli_fetch_assoc($resultadoClientesPendientes)) {
        $listaClientesPendientes[] = $rowClientePendiente;
    }
    mysqli_free_result($resultadoClientesPendientes);
}

$queryVendedoresPendientes = "SELECT vendedorID, NombreVendedor FROM vendedor ORDER BY NombreVendedor ASC";
$resultadoVendedoresPendientes = mysqli_query($conn, $queryVendedoresPendientes);

$listaVendedoresPendientes = [];
if ($resultadoVendedoresPendientes instanceof mysqli_result) {
    while ($rowVendedorPendiente = mysqli_fetch_assoc($resultadoVendedoresPendientes)) {
        $listaVendedoresPendientes[] = $rowVendedorPendiente;
    }
    mysqli_free_result($resultadoVendedoresPendientes);
}

$queryAlmacenistasPendientes = "SELECT AlmacenistaID, NombreAlmacenista FROM almacenista ORDER BY NombreAlmacenista ASC";
$resultadoAlmacenistasPendientes = mysqli_query($conn, $queryAlmacenistasPendientes);

$listaAlmacenistasPendientes = [];
if ($resultadoAlmacenistasPendientes instanceof mysqli_result) {
    while ($rowAlmacenistaPendiente = mysqli_fetch_assoc($resultadoAlmacenistasPendientes)) {
        $listaAlmacenistasPendientes[] = $rowAlmacenistaPendiente;
    }
    mysqli_free_result($resultadoAlmacenistasPendientes);
}

$queryAduanasPendientes = "SELECT AduanaID, NombreAduana FROM aduana ORDER BY NombreAduana ASC";
$resultadoAduanasPendientes = mysqli_query($conn, $queryAduanasPendientes);

$listaAduanasPendientes = [];
if ($resultadoAduanasPendientes instanceof mysqli_result) {
    while ($rowAduanaPendiente = mysqli_fetch_assoc($resultadoAduanasPendientes)) {
        $listaAduanasPendientes[] = $rowAduanaPendiente;
    }
    mysqli_free_result($resultadoAduanasPendientes);
}

$opcionesProductosPendientes = '<option value="">Selecciona producto</option>';
foreach ($listaProductosPendientes as $productoPendiente) {
    $productoId = isset($productoPendiente['PRODUCTOSID']) ? (int) $productoPendiente['PRODUCTOSID'] : 0;
    $skuProducto = isset($productoPendiente['Sku']) ? trim((string) $productoPendiente['Sku']) : '';
    $descripcionProducto = isset($productoPendiente['Descripcion']) ? trim((string) $productoPendiente['Descripcion']) : '';

    $skuProductoEscapado = htmlspecialchars($skuProducto, ENT_QUOTES, 'UTF-8');
    $descripcionProductoEscapada = htmlspecialchars($descripcionProducto, ENT_QUOTES, 'UTF-8');

    if ($skuProductoEscapado !== '') {
        $textoOpcion = $skuProductoEscapado . ' - ' . $descripcionProductoEscapada;
    } else {
        $textoOpcion = $descripcionProductoEscapada;
    }

    $opcionesProductosPendientes .= '<option value="' . $productoId . '">' . $textoOpcion . '</option>';
}

$opcionesClientesPendientes = '<option value="">Selecciona cliente</option>';
foreach ($listaClientesPendientes as $clientePendiente) {
    $clienteId = isset($clientePendiente['CLIENTEID']) ? (int) $clientePendiente['CLIENTEID'] : 0;
    $clienteSian = isset($clientePendiente['CLIENTESIAN']) ? trim((string) $clientePendiente['CLIENTESIAN']) : '';
    $clienteCredito = isset($clientePendiente['CLCSIAN']) ? trim((string) $clientePendiente['CLCSIAN']) : '';
    $nombreCliente = isset($clientePendiente['NombreCliente']) ? trim((string) $clientePendiente['NombreCliente']) : '';

    $clienteSianEscapado = htmlspecialchars($clienteSian, ENT_QUOTES, 'UTF-8');
    $clienteCreditoEscapado = htmlspecialchars($clienteCredito, ENT_QUOTES, 'UTF-8');
    $nombreClienteEscapado = htmlspecialchars($nombreCliente, ENT_QUOTES, 'UTF-8');

    $textoCliente = $clienteSianEscapado;
    if ($clienteCreditoEscapado !== '') {
        $textoCliente .= ' - ' . $clienteCreditoEscapado;
    }
    if ($nombreClienteEscapado !== '') {
        $textoCliente .= ($textoCliente !== '' ? ' - ' : '') . $nombreClienteEscapado;
    }

    if ($textoCliente === '') {
        $textoCliente = 'Cliente #' . $clienteId;
    }

    $opcionesClientesPendientes .= '<option value="' . $clienteId . '">' . $textoCliente . '</option>';
}

$opcionesVendedoresPendientes = '<option value="">Selecciona vendedor</option>';
foreach ($listaVendedoresPendientes as $vendedorPendiente) {
    $vendedorId = isset($vendedorPendiente['vendedorID']) ? (int) $vendedorPendiente['vendedorID'] : 0;
    $nombreVendedor = isset($vendedorPendiente['NombreVendedor']) ? trim((string) $vendedorPendiente['NombreVendedor']) : '';

    $nombreVendedorEscapado = htmlspecialchars($nombreVendedor, ENT_QUOTES, 'UTF-8');

    $textoVendedor = $nombreVendedorEscapado !== '' ? $nombreVendedorEscapado : 'Vendedor #' . $vendedorId;

    $opcionesVendedoresPendientes .= '<option value="' . $vendedorId . '">' . $textoVendedor . '</option>';
}

$opcionesAlmacenistasPendientes = '<option value="">Selecciona almacenista</option>';
foreach ($listaAlmacenistasPendientes as $almacenistaPendiente) {
    $almacenistaId = isset($almacenistaPendiente['AlmacenistaID']) ? (int) $almacenistaPendiente['AlmacenistaID'] : 0;
    $nombreAlmacenista = isset($almacenistaPendiente['NombreAlmacenista']) ? trim((string) $almacenistaPendiente['NombreAlmacenista']) : '';

    $nombreAlmacenistaEscapado = htmlspecialchars($nombreAlmacenista, ENT_QUOTES, 'UTF-8');

    $textoAlmacenista = $nombreAlmacenistaEscapado !== '' ? $nombreAlmacenistaEscapado : 'Almacenista #' . $almacenistaId;

    $opcionesAlmacenistasPendientes .= '<option value="' . $almacenistaId . '">' . $textoAlmacenista . '</option>';
}

$opcionesAduanasPendientes = '<option value="">Selecciona aduana</option>';
foreach ($listaAduanasPendientes as $aduanaPendiente) {
    $aduanaId = isset($aduanaPendiente['AduanaID']) ? (int) $aduanaPendiente['AduanaID'] : 0;
    $nombreAduana = isset($aduanaPendiente['NombreAduana']) ? trim((string) $aduanaPendiente['NombreAduana']) : '';

    $nombreAduanaEscapado = htmlspecialchars($nombreAduana, ENT_QUOTES, 'UTF-8');
    $textoAduana = $nombreAduanaEscapado !== '' ? $nombreAduanaEscapado : 'Aduana #' . $aduanaId;

    $opcionesAduanasPendientes .= '<option value="' . $aduanaId . '">' . $textoAduana . '</option>';
}

$hayProductosPendientes = count($listaProductosPendientes) > 0;
$hayClientesPendientes = count($listaClientesPendientes) > 0;
$hayVendedoresPendientes = count($listaVendedoresPendientes) > 0;
$hayAlmacenistasPendientes = count($listaAlmacenistasPendientes) > 0;
$hayAduanasPendientes = count($listaAduanasPendientes) > 0;

$claseBody = '';
$claseLogo = '';
$iconoFlecha = 'first_page';

if (isset($_SESSION['TIPOUSUARIO']) && (int) $_SESSION['TIPOUSUARIO'] === 3) {
    $claseBody = 'sidebar-hidden';
    $claseLogo = 'hidden-sidebar-logo';
    $iconoFlecha = 'last_page';
}

?>

<!DOCTYPE html>
<html lang="en">

<?php include("includes/Header.php") ?>

<body>
    <div class="app full-width-header align-content-stretch d-flex flex-wrap <?php echo $claseBody; ?>">
        <div class="app-sidebar">
            <div class="logo logo-sm <?php echo $claseLogo; ?>">
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
                                    <h2>Material Pendiente</h2>
                                    <p class="text-muted mb-0">Genera y gestiona los registros de material vendido que aún están pendientes de entrega.</p>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col">
                                <button type="button" class="btn btn-sm btn-primary waves-effect width-md waves-light" data-bs-toggle="modal" data-bs-target="#ModalAgregarPendiente">
                                    <i class="material-icons-two-tone">add</i>
                                    Agregar Pendiente
                                </button>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-lg-12">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <i class="material-icons-two-tone me-2">pending_actions</i>
                                            <div>
                                                Registra y gestiona el material pendiente de entrega para tus clientes.
                                            </div>
                                        </div>
                                        <p class="text-muted mb-0 mt-3">
                                            Esta sección pronto mostrará el listado de facturas con material pendiente. Por ahora, puedes comenzar a capturar la información desde el botón «Agregar Pendiente».
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include("App/Modales/ModalesMaterialPendiente.php") ?>

    <!-- Javascripts -->
    <script src="assets/plugins/jquery/jquery-3.5.1.min.js"></script>
    <script src="assets/plugins/bootstrap/js/popper.min.js"></script>
    <script src="assets/plugins/bootstrap/js/bootstrap.min.js"></script>
    <script src="assets/plugins/perfectscroll/perfect-scrollbar.min.js"></script>
    <script src="assets/plugins/pace/pace.min.js"></script>
    <script src="assets/plugins/highlight/highlight.pack.js"></script>
    <script src="assets/js/main.min.js"></script>
    <script src="assets/js/custom.js"></script>
    <script src="assets/js/select2.min.js" integrity="sha512-9p/L4acAjbjIaaGXmZf0Q2bV42HetlCLbv8EP0z3rLbQED2TAFUlDvAezy7kumYqg5T8jHtDdlm1fgIsr5QzKg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="App/js/AppMaterialPendiente.js"></script>
    <script src="App/js/AppCambiarContrasena.js"></script>

</body>

</html>
