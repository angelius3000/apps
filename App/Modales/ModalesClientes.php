<div class="modal" id="ModalAgregarClientes">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Agregar clientes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="btn-close"></button>
            </div>
            <form data-parsley-validate class="forms-sample" id="ValidacionAgregarClientes">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-lg-12 col-md-12 col-sm-12">

                                            <div class="row">
                                                <div class="col-lg-6 col-sm-12 mb-4">
                                                    <label for="CLIENTESIAN" class="form-label">Número de cliente</label>
                                                    <input type="text" class="form-control" id="CLIENTESIAN" autocomplete="off" placeholder="8923" name="CLIENTESIAN" data-parsley-requireclientnumber="#CLCSIAN" data-parsley-requireclientnumber-message="Captura al menos uno de los números de cliente" data-parsley-trigger="keyup change focusout">

                                                </div>
                                                <div class="col-lg-6 col-sm-12 mb-4">
                                                    <label for="CLCSIAN" class="form-label">Número de crédito</label>
                                                    <input type="text" class="form-control" id="CLCSIAN" autocomplete="off" placeholder="107" name="CLCSIAN" data-parsley-requireclientnumber="#CLIENTESIAN" data-parsley-requireclientnumber-message="Captura al menos uno de los números de cliente" data-parsley-trigger="keyup change focusout">

                                                </div>
                                                <div class="col-lg-6 col-sm-12 mb-4">

                                                    <label for="NombreCliente" class="form-label">Nombre</label>
                                                    <input type="text" class="form-control" id="NombreCliente" autocomplete="off" placeholder="Roberto" name="NombreCliente" required>

                                                </div>
                                                <div class="col-lg-6 col-sm-12 mb-4">

                                                    <label for="EmailCliente" class="form-label">Email</label>
                                                    <input type="text" class="form-control" id="EmailCliente" autocomplete="off" placeholder="correo@email.com" name="EmailCliente">

                                                </div>

                                                <div class="col-lg-6 col-sm-12 mb-4">

                                                    <label for="TelefonoCliente" class="form-label">Teléfono cliente</label>
                                                    <input type="text" class="form-control" id="TelefonoCliente" autocomplete="off" placeholder="6561234567" name="TelefonoCliente">

                                                </div>

                                                <div class="col-lg-6 col-sm-12 mb-4">

                                                    <label for="NombreContacto" class="form-label">Contacto</label>
                                                    <input type="text" class="form-control" id="NombreContacto" autocomplete="off" placeholder="Pedro" name="NombreContacto">

                                                </div>

                                                <div class="col-lg-6 col-sm-12 mb-4">

                                                    <label for="DireccionCliente" class="form-label">Calle y número</label>
                                                    <input type="text" class="form-control" id="DireccionCliente" placeholder="Arbolito 1208-A" name="DireccionCliente">

                                                </div>

                                                <div class="col-lg-6 col-sm-12 mb-4">

                                                    <label for="ColoniaCliente" class="form-label">Colonia</label>
                                                    <input type="text" class="form-control" id="ColoniaCliente" placeholder="Col Del Bosque" name="ColoniaCliente">

                                                </div>

                                                <div class="col-lg-6 col-sm-12 mb-4">

                                                    <label for="CiudadCliente" class="form-label">Ciudad</label>
                                                    <input type="text" class="form-control" id="CiudadCliente" placeholder="Monterrey" name="CiudadCliente">

                                                </div>

                                                <div class="col-lg-6 col-sm-12 mb-4">

                                                    <label for="EstadoCliente" class="form-label">Estado</label>
                                                    <input type="text" class="form-control" id="EstadoCliente" placeholder="Nuevo León" name="EstadoCliente">

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
                    <button type="submit" class="btn btn-primary">
                        Agregar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL DE EDICION -->

<div class="modal" id="ModalEditarClientes">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="btn-close"></button>
            </div>
            <form data-parsley-validate class="forms-sample" id="ValidacionEditarClientes">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-lg-12 col-md-12 col-sm-12">
                                            <div class="row">

                                                <div class="col-lg-6 col-sm-12 mb-4">
                                                    <label for="CLIENTESIANEditar" class="form-label">Número de cliente</label>
                                                    <input type="text" class="form-control" id="CLIENTESIANEditar" autocomplete="off" placeholder="" name="CLIENTESIANEditar" data-parsley-requireclientnumber="#CLCSIANEditar" data-parsley-requireclientnumber-message="Captura al menos uno de los números de cliente" data-parsley-trigger="keyup change focusout">

                                                </div>
                                                <div class="col-lg-6 col-sm-12 mb-4">
                                                    <label for="CLCSIANEditar" class="form-label">Número de crédito</label>
                                                    <input type="text" class="form-control" id="CLCSIANEditar" autocomplete="off" placeholder="107" name="CLCSIANEditar" data-parsley-requireclientnumber="#CLIENTESIANEditar" data-parsley-requireclientnumber-message="Captura al menos uno de los números de cliente" data-parsley-trigger="keyup change focusout">

                                                </div>
                                                <div class="col-lg-6 col-sm-12 mb-4">

                                                    <label for="NombreClienteEditar" class="form-label">Nombre</label>
                                                    <input type="text" class="form-control" id="NombreClienteEditar" autocomplete="off" placeholder="" name="NombreClienteEditar" required>

                                                </div>
                                                <div class="col-lg-6 col-sm-12 mb-4">

                                                    <label for="EmailClienteEditar" class="form-label">Email</label>
                                                    <input type="text" class="form-control" id="EmailClienteEditar" autocomplete="off" placeholder="" name="EmailClienteEditar">

                                                </div>
                                                <div class="col-lg-6 col-sm-12 mb-4">

                                                    <label for="TelefonoClienteEditar" class="form-label">Teléfono</label>
                                                    <input type="text" class="form-control" id="TelefonoClienteEditar" autocomplete="off" placeholder="" name="TelefonoClienteEditar">

                                                </div>

                                                <div class="col-lg-6 col-sm-12 mb-4">

                                                    <label for="NombreContactoEditar" class="form-label">Contacto</label>
                                                    <input type="text" class="form-control" id="NombreContactoEditar" autocomplete="off" placeholder="" name="NombreContactoEditar">

                                                </div>

                                                <div class="col-lg-6 col-sm-12 mb-4">

                                                    <label for="DireccionClienteEditar" class="form-label">Calle y numero</label>
                                                    <input type="text" class="form-control" id="DireccionClienteEditar" autocomplete="off" placeholder="" name="DireccionClienteEditar">

                                                </div>

                                                <div class="col-lg-6 col-sm-12 mb-4">

                                                    <label for="ColoniaClienteEditar" class="form-label">Colonia</label>
                                                    <input type="text" class="form-control" id="ColoniaClienteEditar" autocomplete="off" placeholder="" name="ColoniaClienteEditar">

                                                </div>

                                                <div class="col-lg-6 col-sm-12 mb-4">

                                                    <label for="CiudadClienteEditar" class="form-label">Ciudad</label>
                                                    <input type="text" class="form-control" id="CiudadClienteEditar" autocomplete="off" placeholder="" name="CiudadClienteEditar">

                                                </div>

                                                <div class="col-lg-6 col-sm-12 mb-4">

                                                    <label for="EstadoClienteEditar" class="form-label">Estado</label>
                                                    <input type="text" class="form-control" id="EstadoClienteEditar" autocomplete="off" placeholder="" name="EstadoClienteEditar">

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


                    <input type="hidden" id="CLIENTEIDEditar" name="CLIENTEIDEditar">


                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">
                        Editar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL DE BORRADO -->

<div class="modal" id="ModalDeshabilitarClientes">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Borrar cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="btn-close"></button>
            </div>

            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="card">
                            <div class="card-body">

                                Deseas borrar este cliente?

                                <br>
                                <br>
                                <h3 id="NombreClienteBorrar"></h3>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">

                <input type="hidden" id="CLIENTEIDDeshabilitar" name="CLIENTEIDDeshabilitar">


                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" id="BorrarCliente" class="btn btn-danger">
                    Borrar</button>
            </div>

        </div>
    </div>
</div>

<!-- Modal cliente ya existe -->

<div class="modal" id="ModalYaExiste">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">El cliente ya existe</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="btn-close"></button>
            </div>

            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="card">
                            <div class="card-body">

                                <h3>Ya existe un cliente con esa información:</h3>


                                <div class="row">
                                    <div class="col-lg-6 col-sm-12 mb-4">
                                        Numero de cliente SIAN:
                                        <br>
                                        <strong><span id="NumeroDeClienteSIANYaExiste"></span></strong>
                                    </div>
                                    <div class="col-lg-6 col-sm-12 mb-4">
                                        Nombre:
                                        <br>
                                        <strong><span id="NombreClienteYaExiste"></span></strong>
                                    </div>
                                    <div class="col-lg-6 col-sm-12 mb-4">
                                        Correo:
                                        <br>
                                        <strong><span id="EmailClienteYaExiste"></span></strong>
                                    </div>
                                    <div class="col-lg-6 col-sm-12 mb-4">
                                        Teléfono:
                                        <br>
                                        <strong><span id="TelefonoClienteYaExiste"></span></strong>
                                    </div>
                                    <div class="col-lg-6 col-sm-12 mb-4">
                                        Contacto:
                                        <br>
                                        <strong><span id="NombreContactoYaExiste"></span></strong>
                                    </div>
                                    <div class="col-lg-6 col-sm-12 mb-4">
                                        Dirección:
                                        <br>
                                        <strong><span id="DireccionClienteYaExiste"></span></strong>
                                    </div>
                                    <div class="col-lg-6 col-sm-12 mb-4">
                                        Colonia:
                                        <br>
                                        <strong><span id="ColoniaClienteYaExiste"></span></strong>
                                    </div>
                                    <div class="col-lg-6 col-sm-12 mb-4">
                                        Ciudad:
                                        <br>
                                        <strong><span id="CiudadClienteYaExiste"></span></strong>
                                    </div>
                                    <div class="col-lg-6 col-sm-12 mb-4">
                                        Estado:
                                        <br>
                                        <strong><span id="EstadoClienteYaExiste"></span></strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">

                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>

        </div>
    </div>
</div>