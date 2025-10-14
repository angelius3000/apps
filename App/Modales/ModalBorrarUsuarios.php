<div class="modal" id="ModalBorrarUsuarios">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Eliminar usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="btn-close"></button>
            </div>

            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="card">
                            <div class="card-body">

                                ¿Deseas eliminar este usuario de forma permanente?

                                <br>
                                <br>
                                <h3 id="NombreUsuarioBorrar"></h3>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">

                <input type="hidden" id="USUARIOIDBorrar" name="USUARIOIDBorrar">


                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" id="BorrarUsuario" class="btn btn-danger">
                    Eliminar</button>
            </div>

        </div>
    </div>
</div>
