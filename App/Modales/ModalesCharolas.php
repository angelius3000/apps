<div class="modal" id="ModalCambioStatusCharola">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Estatus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="btn-close"></button>
            </div>
            <form id="FormEditarStatusCharola">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="NuevoStatusCharola" class="form-label">Status</label>
                        <select class="form-select" id="NuevoStatusCharola" name="STATUSID" required>
                            <option value="1">Registrada</option>
                            <option value="2">En proceso</option>
                            <option value="3">Terminada</option>
                            <option value="4">Entregada</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" id="ORDENCHAROLAIDEditar" name="ORDENCHAROLAID">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
