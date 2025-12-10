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
                                                <label for="AduanaPendienteOtro" class="form-label">Nombre de la persona</label>
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
                                                <div class="row producto-pendiente-item align-items-end" data-index="0">
                                                    <div class="col-lg-8 col-sm-12 mb-4">
                                                        <label for="ProductoPendiente-0" class="form-label">Producto pendiente</label>
                                                        <select class="form-select select2-producto" name="productos[0][id]" id="ProductoPendiente-0" data-placeholder="Selecciona producto" <?php echo $hayProductosPendientes ? '' : 'disabled'; ?> required>
                                                            <?php echo $opcionesProductosPendientes; ?>
                                                        </select>
                                                    </div>
                                                    <div class="col-lg-4 col-sm-12 mb-4">
                                                        <label for="CantidadPendiente-0" class="form-label">Cantidad pendiente</label>
                                                        <input type="number" class="form-control" name="productos[0][cantidad]" id="CantidadPendiente-0" min="1" step="1" <?php echo $hayProductosPendientes ? '' : 'disabled'; ?> required>
                                                    </div>
                                                    <div class="col-12 mb-2">
                                                        <button type="button" class="btn btn-outline-danger btn-sm eliminar-producto-pendiente d-none">
                                                            <i class="material-icons-two-tone align-middle">delete</i>
                                                            <span class="align-middle">Eliminar partida</span>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <button type="button" class="btn btn-outline-primary btn-sm" id="AgregarPartidaPendiente" <?php echo $hayProductosPendientes ? '' : 'disabled'; ?>>
                                                <i class="material-icons-two-tone align-middle">add_circle</i>
                                                <span class="align-middle">Agregar partida</span>
                                            </button>
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

<script type="text/template" id="ProductoPendienteRowTemplate">
    <div class="row producto-pendiente-item align-items-end" data-index="__INDEX__">
        <div class="col-lg-8 col-sm-12 mb-4">
            <label for="ProductoPendiente-__INDEX__" class="form-label">Producto pendiente</label>
            <select class="form-select select2-producto" name="productos[__INDEX__][id]" id="ProductoPendiente-__INDEX__" data-placeholder="Selecciona producto" required>
                <?php echo $opcionesProductosPendientes; ?>
            </select>
        </div>
        <div class="col-lg-4 col-sm-12 mb-4">
            <label for="CantidadPendiente-__INDEX__" class="form-label">Cantidad pendiente</label>
            <input type="number" class="form-control" name="productos[__INDEX__][cantidad]" id="CantidadPendiente-__INDEX__" min="1" step="1" required>
        </div>
        <div class="col-12 mb-2">
            <button type="button" class="btn btn-outline-danger btn-sm eliminar-producto-pendiente">
                <i class="material-icons-two-tone align-middle">delete</i>
                <span class="align-middle">Eliminar partida</span>
            </button>
        </div>
    </div>
</script>
