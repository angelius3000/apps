<?php include("includes/HeaderScripts.php");

$pageTitle = 'Edison - Charolas';

// Obtener la lista de charolas disponibles
$query_charolas = "SELECT * FROM charolas";
$charolas = mysqli_query($conn, $query_charolas) or die(mysqli_error($conn));
$totalRows_charolas = mysqli_num_rows($charolas);

// Obtener estatus disponibles para las requisiciones
$query_status = "SELECT * FROM status";
$status = mysqli_query($conn, $query_status) or die(mysqli_error($conn));
$status_options = [];
while ($row_status = mysqli_fetch_assoc($status)) {
    $status_options[] = $row_status;
}
?>
<!DOCTYPE html>
<html lang="en">
<?php include("includes/Header.php"); ?>
<body>
    <div class="app full-width-header align-content-stretch d-flex flex-wrap">
        <div class="app-sidebar">
            <div class="logo logo-sm">
                <a href="main.php"><img src="App/Graficos/Logo/LogoEdison.png" style="max-width :130px;"></a>
            </div>
            <?php include("includes/Menu.php"); ?>
        </div>
        <div class="app-container">
            <?php include("includes/MenuHeader.php"); ?>
            <div class="app-content">
                <div class="content-wrapper">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col">
                                <div class="page-description">
                                    <h1>Charolas</h1>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3">
                                <select class="form-select" id="CHAROLASID" aria-label="Selecciona charola" required>
                                    <option value="">Selecciona charola</option>
                                    <?php while ($row_charolas = mysqli_fetch_assoc($charolas)) { ?>
                                        <option value="<?php echo $row_charolas['CHAROLASID']; ?>">
                                            <?php echo $row_charolas['SkuCharolas'] . ' - ' . $row_charolas['DescripcionCharolas']; ?>
                                        </option>
                                    <?php }
                                    // Reiniciar el puntero de la consulta
                                    mysqli_data_seek($charolas, 0);
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <input type="number" class="form-control" id="CantidadCharolas" placeholder="Cantidad a fabricar">
                            </div>
                            <div class="col-md-3 mb-3">
                                <button type="button" class="btn btn-primary w-100" id="CalcularBtn">Calcular</button>
                            </div>
                        </div>
                        <div class="row mb-4">
                            <div class="col-md-3 ms-auto">
                                <button type="button" class="btn btn-success w-100" id="GenerarRequisicionBtn">Generar requisición</button>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <table class="table" id="TablaMateriaPrima">
                                    <thead>
                                        <tr>
                                            <th>SKU MP</th>
                                            <th>Descripción</th>
                                            <th>Tipo</th>
                                            <th>Cantidad</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="row mt-4">
                            <div class="col">
                                <h5>Requisiciones de armado</h5>
                                <table class="table" id="TablaOrdenesCharolas">
                                    <thead>
                                        <tr>
                                            <th>SKU</th>
                                            <th>Descripción</th>
                                            <th>Cantidad</th>
                                            <th>Estatus</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
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
    <script src="assets/js/select2.min.js" integrity="sha512-9p/L4acAjbjIaaGXmZf0Q2bV42HetlCLbv8EP0z3rLbQED2TAFUlDvAezy7kumYqg5T8jHtDdlm1fgIsr5QzKg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script>
        var statusOptions = <?php echo json_encode($status_options); ?>;
    </script>
    <script src="App/js/AppCharolas.js"></script>
    <script src="App/js/AppCambiarContrasena.js"></script>
</body>
</html>
