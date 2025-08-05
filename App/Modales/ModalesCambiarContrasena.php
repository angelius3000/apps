<div class="modal" id="ModalCambiarContrasena">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cambiar contraseña</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="btn-close"></button>
            </div>
            <form data-parsley-validate class="forms-sample" id="ValidacionEditarContrasena">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-lg-12 col-md-12 col-sm-12">

                                            <div class="row">

                                                <div class="col-lg-6 col-sm-12 mb-4">

                                                    <label for="Contrasena" class="form-label">Nueva Contraseña</label>
                                                    <input type="text" class="form-control" id="Contrasena" autocomplete="off" placeholder="" name="Contrasena">

                                                </div>

                                                <div class="col-lg-6 col-sm-12 mb-4">

                                                    <label for="ConfirmarContrasena" class="form-label">Confirmar contraseña</label>
                                                    <input type="text" class="form-control" id="ConfirmarContrasena" autocomplete="off" placeholder="" name="ConfirmarContrasena" required data-parsley-equalto="#Contrasena">

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


                    <input type="hidden" id="USUARIOIDCambioContrasena" name="USUARIOIDCambioContrasena" value="<?php echo $_SESSION['USUARIOID']; ?>">


                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">
                        Cambiar contraseña</button>
                </div>
            </form>
        </div>
    </div>
</div>