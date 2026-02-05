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

    $opcionesProductosPendientes .= '<option value="' . $productoId . '" data-sku="' . $skuProductoEscapado . '" data-descripcion="' . $descripcionProductoEscapada . '">' . $textoOpcion . '</option>';
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

    $nombreVendedorNormalizado = strtolower($nombreVendedor);
    $esOpcionOtro = $vendedorId === 22 || $nombreVendedorNormalizado === 'otro';

    if ($esOpcionOtro) {
        continue;
    }

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

$listaMaterialPendiente = [];

function asegurarTablaFacturaMPListado(mysqli $conn): void
{
    $sql = "CREATE TABLE IF NOT EXISTS facturamp (
        FacturaMPID INT NOT NULL AUTO_INCREMENT,
        FechaFMP TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        DocumentoFMP VARCHAR(100) NOT NULL,
        RazonSocialFMP VARCHAR(255) NOT NULL,
        VendedorFMP VARCHAR(255) DEFAULT NULL,
        SurtidorFMP VARCHAR(255) DEFAULT NULL,
        ClienteFMP VARCHAR(255) NOT NULL,
        AduanaFMP VARCHAR(255) DEFAULT NULL,
        PRIMARY KEY (FacturaMPID),
        INDEX idx_facturamp_documento (DocumentoFMP)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    @mysqli_query($conn, $sql);
}

asegurarTablaFacturaMPListado($conn);

$queryMaterialPendiente = "SELECT f.FacturaMPID, f.FechaFMP, f.DocumentoFMP, f.RazonSocialFMP, f.VendedorFMP, f.SurtidorFMP, f.ClienteFMP, f.AduanaFMP, "
    . "(SELECT COUNT(*) FROM materialpendiente mp WHERE mp.DocumentoMP = f.DocumentoFMP) AS PartidasPendientes "
    . "FROM facturamp f ORDER BY f.FacturaMPID DESC";

$resultadoMaterialPendiente = @mysqli_query($conn, $queryMaterialPendiente);

if ($resultadoMaterialPendiente instanceof mysqli_result) {
    while ($rowMaterialPendiente = mysqli_fetch_assoc($resultadoMaterialPendiente)) {
        $listaMaterialPendiente[] = $rowMaterialPendiente;
    }
    mysqli_free_result($resultadoMaterialPendiente);
}

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
                                <a href="App/Server/ServerExportarMaterialPendienteExcel.php" class="btn btn-sm btn-success waves-effect width-md waves-light ms-2">
                                    <i class="material-icons-two-tone">file_download</i>
                                    Exportar Excel
                                </a>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-lg-12">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-3">
                                            <i class="material-icons-two-tone me-2">pending_actions</i>
                                            <div>
                                                Registra y gestiona el material pendiente de entrega para tus clientes.
                                            </div>
                                        </div>
                                        <div class="row align-items-center mb-3">
                                            <label for="BuscadorMaterialPendiente" class="col-sm-2 col-form-label">Buscar</label>
                                            <div class="col-sm-10">
                                                <input type="text" class="form-control" id="BuscadorMaterialPendiente" placeholder="Filtrar por folio, documento, cliente, razón social o aduana">
                                            </div>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table align-middle mb-0 table-hover" id="TablaMaterialPendiente">
                                                <thead>
                                                    <tr>
                                                        <th class="text-muted">Folio</th>
                                                        <th class="text-muted">Fecha</th>
                                                        <th class="text-muted">Número de documento</th>
                                                        <th class="text-muted">Razón Social</th>
                                                        <th class="text-muted">Vendedor</th>
                                                        <th class="text-muted">Surtidor</th>
                                                        <th class="text-muted">Nombre del cliente</th>
                                                        <th class="text-muted">Aduana</th>
                                                        <th class="text-muted text-end">Acciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (empty($listaMaterialPendiente)) : ?>
                                                        <tr>
                                                            <td colspan="9" class="text-center text-muted">No hay material pendiente registrado.</td>
                                                        </tr>
                                                    <?php else : ?>
                                                        <?php foreach ($listaMaterialPendiente as $materialPendiente) : ?>
                                                            <?php
                                                            $folio = isset($materialPendiente['FacturaMPID']) ? (int) $materialPendiente['FacturaMPID'] : '';
                                                            $numeroDocumento = htmlspecialchars($materialPendiente['DocumentoFMP'] ?? '', ENT_QUOTES, 'UTF-8');
                                                            $razonSocial = htmlspecialchars($materialPendiente['RazonSocialFMP'] ?? '', ENT_QUOTES, 'UTF-8');
                                                            $vendedor = htmlspecialchars($materialPendiente['VendedorFMP'] ?? '', ENT_QUOTES, 'UTF-8');
                                                            $surtidor = htmlspecialchars($materialPendiente['SurtidorFMP'] ?? '', ENT_QUOTES, 'UTF-8');
                                                            $cliente = htmlspecialchars($materialPendiente['ClienteFMP'] ?? '', ENT_QUOTES, 'UTF-8');
                                                            $aduana = htmlspecialchars($materialPendiente['AduanaFMP'] ?? '', ENT_QUOTES, 'UTF-8');
                                                            $partidasPendientes = isset($materialPendiente['PartidasPendientes']) ? (int) $materialPendiente['PartidasPendientes'] : 0;
                                                            $fechaRegistro = '';

                                                            $clasesFila = 'material-pendiente-row text-body';
                                                            if ($partidasPendientes > 0) {
                                                                $clasesFila .= ' text-danger';
                                                            }

                                                            if (!empty($materialPendiente['FechaFMP'])) {
                                                                $marcaTemporal = strtotime((string) $materialPendiente['FechaFMP']);
                                                                if ($marcaTemporal !== false) {
                                                                    $fechaRegistro = date('d/m/y H:i', $marcaTemporal);
                                                                }
                                                            }
                                                            ?>
                                                            <tr class="<?php echo $clasesFila; ?>" data-folio="<?php echo $folio; ?>" data-documento="<?php echo $numeroDocumento; ?>" style="cursor: pointer;" role="button">
                                                                <td><?php echo $folio !== '' ? $folio : '-'; ?></td>
                                                                <td><?php echo $fechaRegistro !== '' ? $fechaRegistro : '-'; ?></td>
                                                                <td class="fw-semibold"><?php echo $numeroDocumento; ?></td>
                                                                <td><?php echo $razonSocial; ?></td>
                                                                <td><?php echo $vendedor !== '' ? $vendedor : '-'; ?></td>
                                                                <td><?php echo $surtidor !== '' ? $surtidor : '-'; ?></td>
                                                                <td><?php echo $cliente; ?></td>
                                                                <td><?php echo $aduana !== '' ? $aduana : '-'; ?></td>
                                                                <td class="text-end">
                                                                    <button type="button" class="btn btn-outline-primary btn-sm editar-material-pendiente" data-folio="<?php echo $folio; ?>">
                                                                        <i class="material-icons-two-tone">edit</i>
                                                                    </button>
                                                                    <button type="button" class="btn btn-outline-danger btn-sm eliminar-material-pendiente" data-folio="<?php echo $folio; ?>" data-documento="<?php echo $numeroDocumento; ?>">
                                                                        <i class="material-icons-two-tone">delete</i>
                                                                    </button>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                    <tr id="MaterialPendienteSinResultados" class="d-none">
                                                        <td colspan="9" class="text-center text-muted">No se encontraron resultados para la búsqueda.</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-lg-12">
                                <div class="card d-none" id="PanelEntregaMaterialPendiente">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                                            <div>
                                                <h5 class="mb-1" id="DetalleMaterialPendienteTitulo">Selecciona un folio para gestionar su entrega</h5>
                                                <div class="small text-muted" id="DetalleMaterialPendienteInfo"></div>
                                            </div>
                                            <div class="d-flex gap-2">
                                                <button type="button" class="btn btn-outline-secondary btn-sm" id="EntregarDocumentoCompleto">
                                                    Entregar todo el documento
                                                </button>
                                                <button type="button" class="btn btn-light btn-sm" id="ReiniciarEntregas">
                                                    Limpiar selección
                                                </button>
                                            </div>
                                        </div>

                                        <div class="alert alert-danger d-none" id="DetalleMaterialPendienteError" role="alert"></div>
                                        <div class="alert alert-success d-none" id="DetalleMaterialPendienteExito" role="alert"></div>

                                        <form id="FormularioEntregaMaterialPendiente" class="mt-3">
                                            <input type="hidden" id="EntregaFolio" name="folio" value="">
                                            <input type="hidden" id="EntregaDocumento" name="documento" value="">

                                            <div class="table-responsive mb-3">
                                                <table class="table table-sm mb-0" id="TablaPartidasEntrega">
                                                    <thead>
                                                        <tr>
                                                            <th>SKU</th>
                                                            <th>Descripción</th>
                                                            <th class="text-end">Pendiente</th>
                                                            <th class="text-end" style="width: 180px;">Entregar</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="DetallePartidasPendientes">
                                                        <tr class="text-muted">
                                                            <td colspan="4" class="text-center">Selecciona un folio para ver sus partidas.</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>

                                            <div class="row g-3 mb-3">
                                                <div class="col-lg-4 col-md-6 col-sm-12">
                                                    <label class="form-label" for="EntregaRecibio">Nombre de quien recibe</label>
                                                    <input type="text" class="form-control" id="EntregaRecibio" name="recibio" autocomplete="off" required>
                                                </div>
                                                <div class="col-lg-4 col-md-6 col-sm-12">
                                                    <label class="form-label" for="EntregaAduana">Aduana que entrega</label>
                                                    <select class="form-select select-aduana-entrega" id="EntregaAduana" data-placeholder="Selecciona aduana" <?php echo $hayAduanasPendientes ? '' : 'disabled'; ?> required>
                                                        <?php echo $opcionesAduanasPendientes; ?>
                                                    </select>
                                                    <?php if (!$hayAduanasPendientes) : ?>
                                                        <small class="form-text text-muted">No hay aduanas disponibles para seleccionar.</small>
                                                    <?php endif; ?>
                                                    <div id="EntregaAduanaOtroContainer" class="mt-2 d-none">
                                                        <label for="EntregaAduanaOtro" class="form-label">Nombre de la aduana</label>
                                                        <input type="text" class="form-control" id="EntregaAduanaOtro" autocomplete="off">
                                                    </div>
                                                    <input type="hidden" id="EntregaAduanaTexto" name="aduanaEntrega" value="">
                                                </div>
                                            </div>

                                            <div class="d-flex gap-2 justify-content-end">
                                                <button type="submit" class="btn btn-primary" id="BtnRegistrarEntrega" disabled>Registrar entrega</button>
                                            </div>
                                        </form>

                                        <div class="mt-4">
                                            <div class="d-flex align-items-center mb-2">
                                                <i class="material-icons-two-tone me-2">history</i>
                                                <h6 class="mb-0">Registro de entregas</h6>
                                            </div>
                                            <div class="table-responsive">
                                                <table class="table table-sm mb-0" id="TablaRegistroEntregas">
                                                    <thead>
                                                        <tr>
                                                            <th>Fecha</th>
                                                            <th>SKU</th>
                                                            <th>Producto</th>
                                                            <th class="text-end">Cantidad</th>
                                                            <th>Recibió</th>
                                                            <th>Aduana</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="RegistroEntregasBody">
                                                        <tr class="text-muted">
                                                            <td colspan="6" class="text-center">Selecciona un folio para ver su historial de entregas.</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
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
