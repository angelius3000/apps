<div class="modal" id="ModalAgregarPendiente">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Agregar material pendiente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="btn-close"></button>
            </div>
            <form class="forms-sample" id="FormularioAgregarPendiente">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-lg-6 col-sm-12 mb-4">
                                            <label for="NumeroFacturaPendiente" class="form-label">Número de Factura</label>
                                            <input type="text" class="form-control" id="NumeroFacturaPendiente" name="NumeroFacturaPendiente" autocomplete="off" required>
                                        </div>
                                        <div class="col-lg-6 col-sm-12 mb-4">
                                            <label for="RazonSocialPendiente" class="form-label">Razón Social</label>
                                            <select class="form-select select-cliente" id="RazonSocialPendiente" name="RazonSocialPendiente" data-placeholder="Selecciona cliente" <?php echo $hayClientesPendientes ? '' : 'disabled'; ?> required>
                                                <?php echo $opcionesClientesPendientes; ?>
                                            </select>
                                            <?php if (!$hayClientesPendientes) : ?>
                                                <small class="form-text text-muted">No hay clientes disponibles para seleccionar.</small>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-lg-6 col-sm-12 mb-4">
                                            <label for="VendedorPendiente" class="form-label">Vendedor</label>
                                            <select class="form-select select-vendedor" id="VendedorPendiente" name="VendedorPendiente" data-placeholder="Selecciona vendedor" <?php echo $hayVendedoresPendientes ? '' : 'disabled'; ?> required>
                                                <?php echo $opcionesVendedoresPendientes; ?>
                                            </select>
                                            <?php if (!$hayVendedoresPendientes) : ?>
                                                <small class="form-text text-muted">No hay vendedores disponibles para seleccionar.</small>
                                            <?php endif; ?>
                                            <div id="VendedorPendienteOtroContainer" class="mt-2 d-none">
                                                <label for="VendedorPendienteOtro" class="form-label">Nombre del vendedor</label>
                                                <input type="text" class="form-control" id="VendedorPendienteOtro" name="VendedorPendienteOtro" autocomplete="off">
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-sm-12 mb-4">
                                            <label for="SurtidorPendiente" class="form-label">Surtidor</label>
                                            <input type="text" class="form-control" id="SurtidorPendiente" name="SurtidorPendiente" autocomplete="off" required readonly>
                                            <select class="form-select select-almacenista d-none mt-2" id="SurtidorPendienteAlmacenista" data-placeholder="Selecciona almacenista" <?php echo $hayAlmacenistasPendientes ? '' : 'disabled'; ?>>
                                                <?php echo $opcionesAlmacenistasPendientes; ?>
                                            </select>
                                            <?php if (!$hayAlmacenistasPendientes) : ?>
                                                <small class="form-text text-muted">No hay almacenistas disponibles para seleccionar.</small>
                                            <?php endif; ?>
                                            <div class="d-flex flex-wrap gap-3 mt-2">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="AlmacenistaPendiente" <?php echo $hayAlmacenistasPendientes ? '' : 'disabled'; ?>>
                                                    <label class="form-check-label" for="AlmacenistaPendiente">Almacenista</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="OtroSurtidorPendiente">
                                                    <label class="form-check-label" for="OtroSurtidorPendiente">Otro surtidor</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-sm-12 mb-4">
                                            <label for="NombreClientePendiente" class="form-label">Nombre del Cliente</label>
                                            <input type="text" class="form-control" id="NombreClientePendiente" name="NombreClientePendiente" autocomplete="off" placeholder="Nombre del cliente" required>
                                        </div>
                                        <div class="col-lg-6 col-sm-12 mb-4">
                                            <label for="AduanaPendiente" class="form-label">Aduana</label>
                                            <select class="form-select select-aduana" id="AduanaPendiente" name="AduanaPendiente" data-placeholder="Selecciona aduana" <?php echo $hayAduanasPendientes ? '' : 'disabled'; ?> required>
                                                <?php echo $opcionesAduanasPendientes; ?>
                                            </select>
                                            <?php if (!$hayAduanasPendientes) : ?>
                                                <small class="form-text text-muted">No hay aduanas disponibles para seleccionar.</small>
                                            <?php endif; ?>
                                            <div id="AduanaPendienteOtroContainer" class="mt-2 d-none">
                                                <label for="AduanaPendienteOtro" class="form-label">Nombre del revisor</label>
                                                <input type="text" class="form-control" id="AduanaPendienteOtro" name="AduanaPendienteOtro" autocomplete="off">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12">
                                            <h6 class="mb-3">Partidas pendientes</h6>
                                        </div>
                                        <?php if (!$hayProductosPendientes) : ?>
                                            <div class="col-12">
                                                <div class="alert alert-warning" role="alert">
                                                    No se encontraron productos activos para seleccionar. Agrega productos en el catálogo para poder registrar partidas pendientes.
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        <div class="col-12">
                                            <div id="ProductosPendientesContainer" class="productos-pendientes-container" data-productos-disponibles="<?php echo $hayProductosPendientes ? '1' : '0'; ?>">
                                                <div class="row align-items-end">
                                                    <div class="col-lg-8 col-sm-12 mb-4" id="ProductoPendienteSelectContainer">
                                                        <label for="ProductoPendienteSelect" class="form-label">Producto pendiente</label>
                                                        <select class="form-select select2-producto" id="ProductoPendienteSelect" data-placeholder="Selecciona producto" <?php echo $hayProductosPendientes ? '' : 'disabled'; ?> required>
                                                            <?php echo $opcionesProductosPendientes; ?>
                                                        </select>
                                                    </div>
                                                    <div class="col-lg-4 col-sm-12 mb-4" id="CantidadPendienteContainer">
                                                        <label for="CantidadPendiente" class="form-label">Cantidad pendiente</label>
                                                        <input type="number" class="form-control" id="CantidadPendiente" min="1" step="1" <?php echo $hayProductosPendientes ? '' : 'disabled'; ?> required>
                                                    </div>
                                                    <div class="col-12 mb-2">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" id="OtroProductoPendiente">
                                                            <label class="form-check-label" for="OtroProductoPendiente">Otro producto</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-12">
                                                        <div id="OtroProductoPendienteCampos" class="row align-items-end g-3 d-none">
                                                            <div class="col-lg-3 col-md-4 col-sm-12 mb-4">
                                                                <label for="SkuPendienteOtro" class="form-label">SKU</label>
                                                                <input type="text" class="form-control" id="SkuPendienteOtro" name="SkuPendienteOtro" autocomplete="off" style="max-width: 10ch;">
                                                            </div>
                                                            <div class="col-lg-5 col-md-4 col-sm-12 mb-4 d-flex flex-column">
                                                                <label for="DescripcionPendienteOtro" class="form-label">Descripción</label>
                                                                <input type="text" class="form-control" id="DescripcionPendienteOtro" name="DescripcionPendienteOtro" autocomplete="off" style="width: 100%; max-width: 50ch;">
                                                            </div>
                                                            <div class="col-lg-4 col-md-4 col-sm-12 mb-4 d-flex flex-column align-items-lg-end align-items-md-end">
                                                                <label for="CantidadPendienteOtro" class="form-label">Cantidad pendiente</label>
                                                                <input type="number" class="form-control ms-lg-auto ms-md-auto" id="CantidadPendienteOtro" name="CantidadPendienteOtro" min="1" step="1" autocomplete="off" style="width: 100%; max-width: 6ch;">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-12 mb-2">
                                                        <button type="button" class="btn btn-outline-primary btn-sm" id="AgregarPartidaPendiente">
                                                            <i class="material-icons-two-tone align-middle">add_circle</i>
                                                            <span class="align-middle">Agregar partida</span>
                                                        </button>
                                                    </div>
                                                </div>

                                                <div class="table-responsive mt-3">
                                                    <table class="table table-sm" id="ProductosPendientesTabla">
                                                        <thead>
                                                            <tr>
                                                                <th>SKU</th>
                                                                <th>Descripción</th>
                                                                <th class="text-end">Cantidad</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="ProductosPendientesTablaBody">
                                                            <tr class="text-muted">
                                                                <td colspan="3" class="text-center">Agrega partidas para mostrarlas aquí.</td>
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
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary" <?php echo $hayProductosPendientes ? '' : 'disabled'; ?>>Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

