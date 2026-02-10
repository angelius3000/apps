<style id="ModalAgregarPendienteStyles">
    #ModalAgregarPendiente .partidas-pendientes-scroll {
        max-height: 320px;
        overflow-y: auto;
        border: 1px solid #e8ebf1;
        border-radius: 0.5rem;
        background-color: #fff;
    }

    #ModalAgregarPendiente .partidas-pendientes-scroll .table {
        margin-bottom: 0;
    }

    @media (max-width: 1199.98px) {
        #ModalAgregarPendiente .partidas-pendientes-scroll {
            max-height: 260px;
        }
    }
</style>

<div class="modal" id="ModalAgregarPendiente">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ModalTituloPendiente">Agregar material pendiente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="btn-close"></button>
            </div>
            <form class="forms-sample d-flex flex-column h-100 modal-pendiente-form" id="FormularioAgregarPendiente">
                <input type="hidden" id="FolioPendiente" name="FolioPendiente" value="">
                <div class="modal-body modal-pendiente-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row g-4">
                                        <div class="col-12 col-xl-6">
                                            <div class="row">
                                        <div class="col-lg-6 col-sm-12 mb-4">
                                            <label for="NumeroFacturaPendiente" class="form-label">Numero de documento</label>
                                            <input type="text" class="form-control" id="NumeroFacturaPendiente" name="NumeroFacturaPendiente" autocomplete="off" required>
                                        </div>
                                        <div class="col-lg-6 col-sm-12 mb-4">
                                            <label for="RazonSocialPendiente" class="form-label">Razón Social</label>
                                            <div id="RazonSocialPendienteSelectContainer">
                                                <select class="form-select select-cliente" id="RazonSocialPendiente" name="RazonSocialPendiente" data-placeholder="Selecciona cliente" <?php echo $hayClientesPendientes ? '' : 'disabled'; ?> required>
                                                    <?php echo $opcionesClientesPendientes; ?>
                                                </select>
                                                <?php if (!$hayClientesPendientes) : ?>
                                                    <small class="form-text text-muted">No hay clientes disponibles para seleccionar.</small>
                                                <?php endif; ?>
                                            </div>
                                            <div class="form-check mt-2">
                                                <input class="form-check-input" type="checkbox" id="OtraRazonSocialPendiente" name="OtraRazonSocialPendiente" value="1">
                                                <label class="form-check-label" for="OtraRazonSocialPendiente">Otra razón social</label>
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-sm-12 mb-4 d-none" id="NumeroClientePendienteContainer">
                                            <label for="NumeroClientePendienteOtro" class="form-label">Número de cliente</label>
                                            <input type="text" class="form-control" id="NumeroClientePendienteOtro" name="NumeroClientePendienteOtro" autocomplete="off">
                                        </div>
                                        <div class="col-lg-6 col-sm-12 mb-4 d-none" id="OtraRazonSocialPendienteContainer">
                                            <label for="RazonSocialPendienteOtra" class="form-label">Razón social</label>
                                            <input type="text" class="form-control" id="RazonSocialPendienteOtra" name="RazonSocialPendienteOtra" autocomplete="off">
                                        </div>
                                        <div class="col-lg-6 col-sm-12 mb-4">
                                            <label for="VendedorPendiente" class="form-label">Vendedor</label>
                                            <div id="VendedorPendienteSelectContainer">
                                                <select class="form-select select-vendedor" id="VendedorPendiente" name="VendedorPendiente" data-placeholder="Selecciona vendedor" <?php echo $hayVendedoresPendientes ? '' : 'disabled'; ?> required>
                                                    <?php echo $opcionesVendedoresPendientes; ?>
                                                </select>
                                                <?php if (!$hayVendedoresPendientes) : ?>
                                                    <small class="form-text text-muted">No hay vendedores disponibles para seleccionar.</small>
                                                <?php endif; ?>
                                            </div>
                                            <div class="form-check mt-2">
                                                <input class="form-check-input" type="checkbox" id="OtroVendedorPendiente" name="OtroVendedorPendiente" value="1">
                                                <label class="form-check-label" for="OtroVendedorPendiente">Otro Vendedor</label>
                                            </div>
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
                                        </div>
                                        <div class="col-12 col-xl-6">
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
                                                                <input type="text" class="form-control w-100" id="SkuPendienteOtro" name="SkuPendienteOtro" autocomplete="off">
                                                            </div>
                                                            <div class="col-lg-6 col-md-5 col-sm-12 mb-4">
                                                                <label for="DescripcionPendienteOtro" class="form-label">Descripción</label>
                                                                <input type="text" class="form-control" id="DescripcionPendienteOtro" name="DescripcionPendienteOtro" autocomplete="off">
                                                            </div>
                                                            <div class="col-lg-3 col-md-3 col-sm-12 mb-4 d-flex flex-column align-items-lg-end align-items-md-end ms-lg-auto ms-md-auto">
                                                                <label for="CantidadPendienteOtro" class="form-label">Cantidad pendiente</label>
                                                                <input type="number" class="form-control w-100" id="CantidadPendienteOtro" name="CantidadPendienteOtro" min="1" step="1" autocomplete="off">
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

                                                <div class="partidas-pendientes-scroll mt-3">
                                                    <div class="table-responsive">
                                                        <table class="table table-sm" id="ProductosPendientesTabla">
                                                        <thead>
                                                            <tr>
                                                                <th>SKU</th>
                                                                <th>Descripción</th>
                                                                <th class="text-end">Cantidad</th>
                                                                <th class="text-end">Acciones</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="ProductosPendientesTablaBody">
                                                            <tr class="text-muted">
                                                                <td colspan="4" class="text-center">Agrega partidas para mostrarlas aquí.</td>
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
                <div class="modal-footer modal-pendiente-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary" id="BtnGuardarPendiente" <?php echo $hayProductosPendientes ? '' : 'disabled'; ?>>Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="ModalDocumentoPendienteDuplicado" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Documento ya registrado</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="btn-close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">
                    El número de documento <strong id="DocumentoPendienteDuplicadoTexto"></strong> ya se encuentra registrado en material pendiente.
                    Debes capturar un número distinto o cerrar el formulario.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" id="BtnCambiarDocumentoPendiente">Cambiar número</button>
                <button type="button" class="btn btn-secondary" id="BtnCerrarDocumentoPendiente">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="ModalPartidasMaterialPendiente" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="DetalleMaterialPendienteTitulo">Partidas pendientes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="btn-close"></button>
            </div>
            <div class="modal-body">
                <div class="small text-muted mb-3" id="DetalleMaterialPendienteInfo"></div>
                <div class="alert alert-danger d-none" id="DetalleMaterialPendienteError" role="alert"></div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>SKU</th>
                                <th>Descripción</th>
                                <th class="text-end">Cantidad</th>
                            </tr>
                        </thead>
                        <tbody id="DetallePartidasPendientes">
                            <tr class="text-muted">
                                <td colspan="3" class="text-center">Selecciona un folio para ver sus partidas.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
