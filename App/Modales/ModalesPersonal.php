<div class="modal fade" id="ModalAgregarPersonal" tabindex="-1" aria-labelledby="ModalAgregarPersonalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ModalAgregarPersonalLabel">Agregar personal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="FormAgregarPersonal">
                <div class="modal-body">
                    <input type="hidden" name="tipo" id="TipoPersonalAgregar">
                    <div class="mb-3">
                        <label for="NombrePersonalAgregar" class="form-label">Nombre</label>
                        <input type="text" class="form-control" name="nombre" id="NombrePersonalAgregar" required maxlength="150">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="ModalEditarPersonal" tabindex="-1" aria-labelledby="ModalEditarPersonalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ModalEditarPersonalLabel">Editar personal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="FormEditarPersonal">
                <div class="modal-body">
                    <input type="hidden" name="id" id="PersonalIDEditar">
                    <input type="hidden" name="tipo" id="TipoPersonalEditar">
                    <div class="mb-3">
                        <label for="NombrePersonalEditar" class="form-label">Nombre</label>
                        <input type="text" class="form-control" name="nombre" id="NombrePersonalEditar" required maxlength="150">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="ModalCambiarEstadoPersonal" tabindex="-1" aria-labelledby="ModalCambiarEstadoPersonalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ModalCambiarEstadoPersonalLabel">Cambiar estatus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="PersonalIDEstado">
                <input type="hidden" id="TipoPersonalEstado">
                <p id="TextoCambiarEstadoPersonal" class="mb-0"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning" id="BtnConfirmarCambiarEstadoPersonal">Confirmar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="ModalEliminarPersonal" tabindex="-1" aria-labelledby="ModalEliminarPersonalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ModalEliminarPersonalLabel">Eliminar personal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="PersonalIDEliminar">
                <input type="hidden" id="TipoPersonalEliminar">
                <p id="TextoEliminarPersonal" class="mb-0"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="BtnConfirmarEliminarPersonal">Eliminar</button>
            </div>
        </div>
    </div>
</div>
