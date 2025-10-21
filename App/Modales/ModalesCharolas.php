<div class="modal" id="ModalCambioStatusCharola">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Estatus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="btn-close"></button>
            </div>
            <form id="FormEditarStatusCharola">
                <div class="modal-body">
                    <p id="TextoConfirmacionStatus" class="mb-0"></p>
                    <div id="CamposAuditado" class="row g-3 d-none mt-3">
                        <div class="col-md-4">
                            <label for="SalidaAuditado" class="form-label">Salida</label>
                            <input type="text" class="form-control" id="SalidaAuditado" name="SALIDA" autocomplete="off">
                        </div>
                        <div class="col-md-4">
                            <label for="EntradaAuditado" class="form-label">Entrada</label>
                            <input type="text" class="form-control" id="EntradaAuditado" name="ENTRADA" autocomplete="off">
                        </div>
                        <div class="col-md-4">
                            <label for="AlmacenAuditado" class="form-label">Almacén</label>
                            <input type="text" class="form-control" id="AlmacenAuditado" name="ALMACEN" autocomplete="off">
                        </div>
                    </div>
                    <div class="mb-3 d-none mt-3" id="CampoFacturaCharola">
                        <label for="FacturaCharola" class="form-label">Factura</label>
                        <input type="text" class="form-control" id="FacturaCharola" name="FACTURA" autocomplete="off">
                    </div>
                </div>
                <div class="modal-footer flex-column flex-sm-row">
                    <input type="hidden" id="ORDENCHAROLAIDEditar" name="ORDENCHAROLAID">
                    <input type="hidden" id="NuevoStatusCharola" name="STATUSID">
                    <div class="w-100 d-flex justify-content-end gap-2" id="BotonesConfirmacionStatus">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                        <button type="button" class="btn btn-primary" id="BtnConfirmarCambioStatus">Sí</button>
                    </div>
                    <div class="w-100 d-none justify-content-end gap-2" id="BotonesFormularioStatus">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal" id="ModalDetallesCharola">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalles de la charola</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="btn-close"></button>
            </div>
            <div class="modal-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>SKU MP</th>
                            <th>Descripción</th>
                            <th>Tipo</th>
                            <th>Cantidad</th>
                        </tr>
                    </thead>
                    <tbody id="DetalleCharolaTBody"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
