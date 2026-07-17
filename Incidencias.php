<?php
include('includes/HeaderScripts.php');

$pageTitle = 'Edison - Incidencias';
if (!usuarioTieneAccesoSeccion('incidencias')) {
    header('Location: main.php');
    exit;
}

$productos = [];
$resultadoProductos = mysqli_query($conn, 'SELECT PRODUCTOSID, Sku, Descripcion, MarcaProductos FROM productos ORDER BY Sku ASC');
if ($resultadoProductos instanceof mysqli_result) {
    while ($fila = mysqli_fetch_assoc($resultadoProductos)) {
        $productos[] = $fila;
    }
    mysqli_free_result($resultadoProductos);
}

$vendedores = [];
$resultadoVendedores = mysqli_query($conn, 'SELECT vendedorID, NombreVendedor FROM vendedor WHERE Deshabilitado = 0 ORDER BY NombreVendedor ASC');
if ($resultadoVendedores instanceof mysqli_result) {
    while ($fila = mysqli_fetch_assoc($resultadoVendedores)) {
        $vendedores[] = $fila;
    }
    mysqli_free_result($resultadoVendedores);
}

$aduanas = [];
$resultadoAduanas = mysqli_query($conn, 'SELECT AduanaID, NombreAduana FROM aduana WHERE Deshabilitado = 0 ORDER BY NombreAduana ASC');
if ($resultadoAduanas instanceof mysqli_result) {
    while ($fila = mysqli_fetch_assoc($resultadoAduanas)) {
        $aduanas[] = $fila;
    }
    mysqli_free_result($resultadoAduanas);
}

@mysqli_query($conn, 'CREATE TABLE IF NOT EXISTS incidencias (IncidenciaID INT NOT NULL AUTO_INCREMENT, Fecha TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, Folio VARCHAR(100) NOT NULL, Cantidad DECIMAL(12,2) NOT NULL, SKU VARCHAR(100) NOT NULL, Descripcion VARCHAR(255) NOT NULL, Marca VARCHAR(255) DEFAULT NULL, PrecioUnitario DECIMAL(12,2) NOT NULL, Vendedor VARCHAR(255) NOT NULL, Total DECIMAL(14,2) NOT NULL, CreadoPor VARCHAR(255) NOT NULL, Comentarios TEXT DEFAULT NULL, PRIMARY KEY (IncidenciaID), INDEX idx_incidencias_folio (Folio), INDEX idx_incidencias_sku (SKU)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
$incidencias = [];
$resultadoIncidencias = mysqli_query($conn, 'SELECT IncidenciaID, Fecha, Folio, Cantidad, SKU, Descripcion, Marca, PrecioUnitario, Vendedor, Total, CreadoPor, Comentarios FROM incidencias ORDER BY Fecha DESC, IncidenciaID DESC');
if ($resultadoIncidencias instanceof mysqli_result) {
    while ($fila = mysqli_fetch_assoc($resultadoIncidencias)) { $incidencias[] = $fila; }
    mysqli_free_result($resultadoIncidencias);
}
?>
<!DOCTYPE html><html lang="es"><?php include('includes/Header.php'); ?>
<body><div class="app full-width-header align-content-stretch d-flex flex-wrap"><div class="app-sidebar"><div class="logo logo-sm"><a href="main.php"><img src="App/Graficos/Logo/LogoEdison.png" style="max-width:130px;"></a></div><?php include('includes/Menu.php'); ?></div>
<div class="app-container"><div class="search"><form></form><a href="#" class="toggle-search"><i class="material-icons">close</i></a></div><?php include('includes/MenuHeader.php'); ?>
<div class="app-content"><div class="content-wrapper"><div class="container-fluid">
 <div class="row"><div class="col"><div class="page-description"><h2>Incidencias</h2><p class="text-muted mb-0">Registra las incidencias detectadas durante la entrega de material a clientes.</p></div></div></div>
 <div class="row"><div class="col"><button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#ModalIncidencia"><i class="material-icons-two-tone">add</i> Nueva incidencia</button></div></div>
 <div class="row mt-3"><div class="col"><div class="card"><div class="card-body"><form action="App/Server/ServerExportarIncidenciasExcel.php" method="get" class="row g-3 align-items-end"><div class="col-md-2"><label class="form-label" for="ReporteFechaInicio">Fecha inicial</label><input type="date" class="form-control" id="ReporteFechaInicio" name="fecha_inicio"></div><div class="col-md-2"><label class="form-label" for="ReporteFechaFin">Fecha final</label><input type="date" class="form-control" id="ReporteFechaFin" name="fecha_fin"></div><div class="col-md-2"><label class="form-label" for="ReporteFolio">Folio</label><input type="text" class="form-control" id="ReporteFolio" name="folio"></div><div class="col-md-2"><label class="form-label" for="ReporteSku">SKU</label><input type="text" class="form-control" id="ReporteSku" name="sku"></div><div class="col-md-2"><label class="form-label" for="ReporteVendedor">Vendedor</label><input type="text" class="form-control" id="ReporteVendedor" name="vendedor"></div><div class="col-md-2"><button type="submit" class="btn btn-success w-100"><i class="material-icons-two-tone">file_download</i> Descargar reporte</button></div></form></div></div></div></div>
 <div class="row mt-4"><div class="col"><div class="card"><div class="card-body"><div class="table-responsive"><table class="table align-middle mb-0 table-hover"><thead><tr><th>Fecha</th><th>Folio</th><th class="text-end">Cantidad</th><th>SKU</th><th>Descripción</th><th>Marca</th><th class="text-end">Precio unitario</th><th>Vendedor</th><th class="text-end">Total</th><th>Creado por:</th><th>Comentarios</th></tr></thead><tbody id="TablaIncidencias">
 <?php if (!$incidencias): ?><tr><td colspan="11" class="text-center text-muted">No hay incidencias registradas.</td></tr><?php else: foreach ($incidencias as $incidencia): ?><tr><td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($incidencia['Fecha'])), ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($incidencia['Folio'], ENT_QUOTES, 'UTF-8') ?></td><td class="text-end"><?= htmlspecialchars($incidencia['Cantidad'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($incidencia['SKU'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($incidencia['Descripcion'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($incidencia['Marca'] ?? '', ENT_QUOTES, 'UTF-8') ?></td><td class="text-end">$<?= number_format((float)$incidencia['PrecioUnitario'], 2) ?></td><td><?= htmlspecialchars($incidencia['Vendedor'], ENT_QUOTES, 'UTF-8') ?></td><td class="text-end">$<?= number_format((float)$incidencia['Total'], 2) ?></td><td><?= htmlspecialchars($incidencia['CreadoPor'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($incidencia['Comentarios'] ?? '', ENT_QUOTES, 'UTF-8') ?></td></tr><?php endforeach; endif; ?>
 </tbody></table></div></div></div></div></div>
</div></div></div></div></div>
<div class="modal fade" id="ModalIncidencia" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content"><form id="FormularioIncidencia"><div class="modal-header"><h5 class="modal-title">Nueva incidencia</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div><div class="modal-body"><div id="IncidenciaError" class="alert alert-danger d-none"></div><div class="row g-3">
<div class="col-md-6"><label class="form-label" for="IncidenciaFolio">Folio</label><input class="form-control" id="IncidenciaFolio" name="folio" required></div>
<div class="col-md-6"><label class="form-label" for="IncidenciaProducto">SKU</label><select class="form-select" id="IncidenciaProducto" name="productoId" data-placeholder="Selecciona producto" data-search-url="App/Server/ServerBuscarProductosMaterialPendiente.php" required><option value="">Selecciona producto</option></select><input type="hidden" id="IncidenciaProductoSolicitadoSku" name="productoSolicitadoSku" value=""></div>
<div class="col-md-4"><label class="form-label" for="IncidenciaCantidad">Cantidad</label><input type="number" min="0.01" step="0.01" class="form-control" id="IncidenciaCantidad" name="cantidad" required></div>
<div class="col-md-4"><label class="form-label" for="IncidenciaPrecio">Precio unitario</label><input type="number" min="0" step="0.01" class="form-control" id="IncidenciaPrecio" name="precioUnitario" required></div>
<div class="col-md-4"><label class="form-label" for="IncidenciaTotal">Total</label><input class="form-control" id="IncidenciaTotal" readonly value="$0.00"></div>
<div class="col-md-6"><label class="form-label" for="IncidenciaVendedor">Vendedor</label><select class="form-select" id="IncidenciaVendedor" name="vendedorId" data-placeholder="Selecciona vendedor" required><option value="">Selecciona vendedor</option><?php foreach ($vendedores as $vendedor): ?><option value="<?= (int)$vendedor['vendedorID'] ?>"><?= htmlspecialchars($vendedor['NombreVendedor'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></div>
<div class="col-md-6"><label class="form-label" for="IncidenciaAduana">Creado por:</label><select class="form-select" id="IncidenciaAduana" name="aduanaId" data-placeholder="Selecciona aduana" required><option value="">Selecciona aduana</option><?php foreach ($aduanas as $aduana): ?><option value="<?= (int)$aduana['AduanaID'] ?>"><?= htmlspecialchars($aduana['NombreAduana'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></div>
<div class="col-12"><label class="form-label" for="IncidenciaComentarios">Comentarios</label><textarea class="form-control" id="IncidenciaComentarios" name="comentarios" rows="3"></textarea></div>
</div></div><div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button><button class="btn btn-primary" type="submit">Guardar incidencia</button></div></form></div></div></div>
<script src="assets/plugins/jquery/jquery-3.5.1.min.js"></script><script src="assets/plugins/bootstrap/js/popper.min.js"></script><script src="assets/plugins/bootstrap/js/bootstrap.min.js"></script><script src="assets/plugins/perfectscroll/perfect-scrollbar.min.js"></script><script src="assets/plugins/pace/pace.min.js"></script><script src="assets/js/main.min.js"></script><script src="assets/js/custom.js"></script><script src="assets/js/select2.min.js"></script><script src="App/js/AppIncidencias.js"></script><script src="App/js/AppCambiarContrasena.js"></script></body></html>
