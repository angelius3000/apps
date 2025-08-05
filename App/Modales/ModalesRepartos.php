<div class="modal" id="ModalAgregarReparto">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Agregar reparto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="btn-close"></button>
            </div>
            <form data-parsley-validate class="forms-sample" id="ValidacionAgregarRepartos">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-lg-12 col-md-12 col-sm-12">
                                            <div class="row">
                                                <div class="col-lg-12 col-sm-12 mb-4">
                                                    <label for="CLIENTEID" class="form-label">Cliente ID</label>
                                                    <select class="select2" name="CLIENTEID" id="CLIENTEID" aria-label="Default select example" required>
                                                        <option selected>Selecciona cliente</option>

                                                        <?php while ($row_clientes = mysqli_fetch_assoc($clientes)) { ?>

                                                            <option value="<?php echo $row_clientes['CLIENTEID']; ?>">

                                                                <?php if ($row_clientes["CLCSIAN"] != NULL) {

                                                                    $NumeroDeCredito = " - " . $row_clientes["CLCSIAN"];
                                                                } else {

                                                                    $NumeroDeCredito = " ";
                                                                }

                                                                echo $row_clientes['CLIENTESIAN'] . $NumeroDeCredito . " - " . $row_clientes['NombreCliente']; ?>
                                                            </option>

                                                        <?php }

                                                        // Reset the pointer to the beginning
                                                        mysqli_data_seek($clientes, 0);

                                                        ?>

                                                    </select>
                                                </div>
                                                <div class="col-lg-6 col-sm-12 mb-4">
                                                    <label for="Fecha" class="form-label">Fecha de registro</label>
                                                    <input type="input" class="form-control" id="Fecha" disabled value="<?php echo $FechaHoy; ?> ">
                                                </div>
                                                <div class="col-lg-6 col-sm-12 mb-4">
                                                    <label for="NumeroDeFactura" class="form-label">Número de Factura</label>
                                                    <input type="text" class="form-control" id="NumeroDeFactura" autocomplete="off" name="NumeroDeFactura" required>
                                                </div>

                                                <div class="col-lg-6 col-sm-12 mb-4">
                                                    <label for="Calle" class="form-label">Calle</label>
                                                    <input type="text" class="form-control" id="Calle" autocomplete="off" name="Calle" required>
                                                </div>
                                                <div class="col-lg-6 col-sm-12 mb-4">
                                                    <label for="NumeroEXT" class="form-label">Número Exterior</label>
                                                    <input type="text" class="form-control" id="NumeroEXT" autocomplete="off" name="NumeroEXT" required>
                                                </div>
                                                <div class="col-lg-6 col-sm-12 mb-4">
                                                    <label for="Colonia" class="form-label">Colonia</label>
                                                    <input type="text" class="form-control" id="Colonia" autocomplete="off" name="Colonia" required>
                                                </div>
                                                <div class="col-lg-6 col-sm-12 mb-4">
                                                    <label for="CP" class="form-label">Código Postal</label>
                                                    <input type="text" class="form-control" id="CP" autocomplete="off" name="CP" required>
                                                </div>
                                                <div class="col-lg-6 col-sm-12 mb-4">
                                                    <label for="Ciudad" class="form-label">Ciudad</label>
                                                    <input type="text" class="form-control" id="Ciudad" autocomplete="off" name="Ciudad" required>
                                                </div>
                                                <div class="col-lg-6 col-sm-12 mb-4">
                                                    <label for="Estado" class="form-label">Estado</label>
                                                    <input type="text" class="form-control" id="Estado" autocomplete="off" name="Estado" required>
                                                </div>
                                                <div class="col-lg-12 col-sm-12 mb-4">
                                                    <label for="EnlaceGoogleMaps" class="form-label">Enlace Google Maps</label>
                                                    <input type="text" class="form-control" id="EnlaceGoogleMaps" autocomplete="off" name="EnlaceGoogleMaps">
                                                </div>
                                                <div class="col-lg-12 col-sm-12 mb-4">
                                                    <label for="Receptor" class="form-label">Receptor</label>
                                                    <input type="text" class="form-control" id="Receptor" autocomplete="off" name="Receptor" required>
                                                </div>
                                                <div class="col-lg-6 col-sm-12 mb-4">
                                                    <label for="TelefonoDeReceptor" class="form-label">Teléfono del Receptor</label>
                                                    <input type="text" class="form-control" id="TelefonoDeReceptor" autocomplete="off" name="TelefonoDeReceptor" required placeholder="Número sin guiones ni espacios" data-parsley-pattern="^\d{10}$">
                                                </div>
                                                <div class="col-lg-6 col-sm-12 mb-4">
                                                    <label for="TelefonoAlternativo" class="form-label">Teléfono Alternativo</label>
                                                    <input type="text" class="form-control" id="TelefonoAlternativo" autocomplete="off" name="TelefonoAlternativo" data-parsley-pattern="^\d{10}$">
                                                </div>
                                                <div class="col-lg-12 col-sm-12 mb-4">
                                                    <label for="Comentarios" class="form-label">Comentarios</label>
                                                    <textarea class="form-control" id="Comentarios" name="Comentarios" rows="4"></textarea>
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


                    <input type="hidden" class="form-control" id="USUARIOID" name="USUARIOID" value="<?php echo $_SESSION['USUARIOID']; ?>">

                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Agregar</button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- MODAL DE EDICION -->
<div class="modal" id="ModalEditarReparto">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar reparto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="btn-close"></button>
            </div>
            <form data-parsley-validate class="forms-sample" id="ValidacionEditarRepartos">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-lg-12 col-md-12 col-sm-12">
                                            <div class="row">
                                                <div class="col-lg-12 col-sm-12 mb-4">
                                                    <label for="CLIENTEIDEditar" class="form-label">Cliente ID</label>
                                                    <select class="select2" name="CLIENTEIDEditar" id="CLIENTEIDEditar" aria-label="Default select example" required>
                                                        <option selected>Selecciona cliente</option>

                                                        <?php while ($row_clientes = mysqli_fetch_assoc($clientes)) { ?>

                                                            <option value="<?php echo $row_clientes['CLIENTEID']; ?>">

                                                                <?php
                                                                if ($row_clientes["CLCSIAN"] != NULL) {

                                                                    $NumeroDeCredito = " - " . $row_clientes["CLCSIAN"];
                                                                } else {

                                                                    $NumeroDeCredito = " ";
                                                                }

                                                                echo $row_clientes['CLIENTESIAN'] . $NumeroDeCredito . " - " . $row_clientes['NombreCliente']; ?>

                                                            </option>

                                                        <?php }

                                                        // Reset the pointer to the beginning
                                                        mysqli_data_seek($clientes, 0);

                                                        ?>

                                                    </select>

                                                </div>

                                                <div class="col-lg-6 col-sm-12 mb-4">
                                                    <label for="NumeroDeFacturaEditar" class="form-label">Número de Factura</label>
                                                    <input type="text" class="form-control" id="NumeroDeFacturaEditar" autocomplete="off" name="NumeroDeFacturaEditar" required>
                                                </div>

                                                <div class="col-lg-6 col-sm-12 mb-4">
                                                    <label for="CalleEditar" class="form-label">Calle</label>
                                                    <input type="text" class="form-control" id="CalleEditar" autocomplete="off" name="CalleEditar" required>
                                                </div>
                                                <div class="col-lg-6 col-sm-12 mb-4">
                                                    <label for="NumeroEXTEditar" class="form-label">Número Exterior</label>
                                                    <input type="text" class="form-control" id="NumeroEXTEditar" autocomplete="off" name="NumeroEXTEditar" required>
                                                </div>
                                                <div class="col-lg-6 col-sm-12 mb-4">
                                                    <label for="ColoniaEditar" class="form-label">Colonia</label>
                                                    <input type="text" class="form-control" id="ColoniaEditar" autocomplete="off" name="ColoniaEditar" required>
                                                </div>
                                                <div class="col-lg-6 col-sm-12 mb-4">
                                                    <label for="CPEditar" class="form-label">Código Postal</label>
                                                    <input type="text" class="form-control" id="CPEditar" autocomplete="off" name="CPEditar" required>
                                                </div>
                                                <div class="col-lg-6 col-sm-12 mb-4">
                                                    <label for="CiudadEditar" class="form-label">Ciudad</label>
                                                    <input type="text" class="form-control" id="CiudadEditar" autocomplete="off" name="CiudadEditar" required>
                                                </div>
                                                <div class="col-lg-6 col-sm-12 mb-4">
                                                    <label for="EstadoEditar" class="form-label">Estado</label>
                                                    <input type="text" class="form-control" id="EstadoEditar" autocomplete="off" name="EstadoEditar" required>
                                                </div>

                                                <div class="col-lg-12 col-sm-12 mb-4">
                                                    <label for="EnlaceGoogleMapsEditar" class="form-label">Enlace Google Maps</label>
                                                    <input type="text" class="form-control" id="EnlaceGoogleMapsEditar" autocomplete="off" name="EnlaceGoogleMapsEditar">
                                                </div>

                                                <div class="col-lg-12 col-sm-12 mb-4">
                                                    <label for="ReceptorEditar" class="form-label">Receptor</label>
                                                    <input type="text" class="form-control" id="ReceptorEditar" autocomplete="off" name="ReceptorEditar" required>
                                                </div>
                                                <div class="col-lg-6 col-sm-12 mb-4">
                                                    <label for="TelefonoDeReceptorEditar" class="form-label">Teléfono del Receptor</label>
                                                    <input type="text" class="form-control" id="TelefonoDeReceptorEditar" autocomplete="off" name="TelefonoDeReceptorEditar" required>
                                                </div>
                                                <div class="col-lg-6 col-sm-12 mb-4">
                                                    <label for="TelefonoAlternativoEditar" class="form-label">Teléfono Alternativo</label>
                                                    <input type="text" class="form-control" id="TelefonoAlternativoEditar" autocomplete="off" name="TelefonoAlternativoEditar">
                                                </div>
                                                <div class="col-lg-12 col-sm-12 mb-4">
                                                    <label for="ComentariosEditar" class="form-label">Comentarios</label>
                                                    <textarea class="form-control" id="ComentariosEditar" name="ComentariosEditar" rows="4"></textarea>
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


                    <input type="hidden" class="form-control" id="REPARTOIDEditar" name="REPARTOIDEditar">

                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Editar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL DE BORRADO -->

<div class="modal" id="ModalBorrarReparto">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Borrar reparto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="btn-close"></button>
            </div>

            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="card">
                            <div class="card-body">

                                <h4> Deseas borrar este reparto?</h4>
                                <br>
                                <span id="DatosRepartoParaBorrar"></span>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">

                <input type="hidden" id="REPARTOIDBorrar" name="REPARTOIDBorrar">


                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" id="BorrarReparto" class="btn btn-danger">
                    Borrar</button>
            </div>

        </div>
    </div>
</div>

<!-- MODAL DE CLONACION -->
<div class="modal" id="ModalClonarReparto">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Clonar reparto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="btn-close"></button>
            </div>
            <form data-parsley-validate class="forms-sample" id="ValidacionClonarRepartos">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-lg-12 col-md-12 col-sm-12">
                                            <div class="row">
                                                <div class="col-lg-12 col-sm-12 mb-4">
                                                    <label for="CLIENTEIDClonar" class="form-label">Cliente ID</label>
                                                    <select class="select2" name="CLIENTEIDClonar" id="CLIENTEIDClonar" aria-label="Default select example" required>
                                                        <option selected>Selecciona cliente</option>

                                                        <?php while ($row_clientes = mysqli_fetch_assoc($clientes)) { ?>

                                                            <option value="<?php echo $row_clientes['CLIENTEID']; ?>">

                                                                <?php
                                                                if ($row_clientes["CLCSIAN"] != NULL) {

                                                                    $NumeroDeCredito = " - " . $row_clientes["CLCSIAN"];
                                                                } else {

                                                                    $NumeroDeCredito = " ";
                                                                }

                                                                echo $row_clientes['CLIENTESIAN'] . $NumeroDeCredito . " - " . $row_clientes['NombreCliente']; ?>

                                                            </option>

                                                        <?php }

                                                        // Reset the pointer to the beginning
                                                        mysqli_data_seek($clientes, 0);

                                                        ?>

                                                    </select>

                                                </div>

                                                <div class="col-lg-6 col-sm-12 mb-4">
                                                    <label for="NumeroDeFacturaClonar" class="form-label">Número de Factura</label>
                                                    <input type="text" class="form-control" id="NumeroDeFacturaClonar" autocomplete="off" name="NumeroDeFacturaClonar" required>
                                                </div>

                                                <div class="col-lg-6 col-sm-12 mb-4">
                                                    <label for="CalleClonar" class="form-label">Calle</label>
                                                    <input type="text" class="form-control" id="CalleClonar" autocomplete="off" name="CalleClonar" required>
                                                </div>
                                                <div class="col-lg-6 col-sm-12 mb-4">
                                                    <label for="NumeroEXTClonar" class="form-label">Número Exterior</label>
                                                    <input type="text" class="form-control" id="NumeroEXTClonar" autocomplete="off" name="NumeroEXTClonar" required>
                                                </div>
                                                <div class="col-lg-6 col-sm-12 mb-4">
                                                    <label for="ColoniaClonar" class="form-label">Colonia</label>
                                                    <input type="text" class="form-control" id="ColoniaClonar" autocomplete="off" name="ColoniaClonar" required>
                                                </div>
                                                <div class="col-lg-6 col-sm-12 mb-4">
                                                    <label for="CPClonar" class="form-label">Código Postal</label>
                                                    <input type="text" class="form-control" id="CPClonar" autocomplete="off" name="CPClonar" required>
                                                </div>
                                                <div class="col-lg-6 col-sm-12 mb-4">
                                                    <label for="CiudadClonar" class="form-label">Ciudad</label>
                                                    <input type="text" class="form-control" id="CiudadClonar" autocomplete="off" name="CiudadClonar" required>
                                                </div>
                                                <div class="col-lg-6 col-sm-12 mb-4">
                                                    <label for="EstadoClonar" class="form-label">Estado</label>
                                                    <input type="text" class="form-control" id="EstadoClonar" autocomplete="off" name="EstadoClonar" required>
                                                </div>

                                                <div class="col-lg-12 col-sm-12 mb-4">
                                                    <label for="EnlaceGoogleMapsClonar" class="form-label">Enlace Google Maps</label>
                                                    <input type="text" class="form-control" id="EnlaceGoogleMapsClonar" autocomplete="off" name="EnlaceGoogleMapsClonar">
                                                </div>

                                                <div class="col-lg-12 col-sm-12 mb-4">
                                                    <label for="ReceptorClonar" class="form-label">Receptor</label>
                                                    <input type="text" class="form-control" id="ReceptorClonar" autocomplete="off" name="ReceptorClonar" required>
                                                </div>
                                                <div class="col-lg-6 col-sm-12 mb-4">
                                                    <label for="TelefonoDeReceptorClonar" class="form-label">Teléfono del Receptor</label>
                                                    <input type="text" class="form-control" id="TelefonoDeReceptorClonar" autocomplete="off" name="TelefonoDeReceptorClonar" required>
                                                </div>
                                                <div class="col-lg-6 col-sm-12 mb-4">
                                                    <label for="TelefonoAlternativoClonar" class="form-label">Teléfono Alternativo</label>
                                                    <input type="text" class="form-control" id="TelefonoAlternativoClonar" autocomplete="off" name="TelefonoAlternativoClonar">
                                                </div>
                                                <div class="col-lg-12 col-sm-12 mb-4">
                                                    <label for="ComentariosClonar" class="form-label">Comentarios</label>
                                                    <textarea class="form-control" id="ComentariosClonar" name="ComentariosClonar" rows="4"></textarea>
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


                    <input type="hidden" class="form-control" id="REPARTOIDClonar" name="REPARTOIDClonar">
                    <input type="hidden" class="form-control" id="USUARIOIDClonar" name="USUARIOIDClonar" value="<?php echo $_SESSION['USUARIOID']; ?>">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Clonar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL DE CAMBIO DE STATUS -->

<div class="modal" id="ModalCambioStatus">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Estatus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="btn-close"></button>
            </div>
            <form data-parsley-validate class="forms-sample" id="ValidacionEditarStatus">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-lg-12 col-md-12 col-sm-12">
                                            <div class="row">
                                                <div class="col-lg-12 col-sm-12 mb-4">
                                                    <label for="STATUSIDEditar" class="form-label">Status</label>
                                                    <select class="form-select" name="STATUSIDEditar" id="STATUSIDEditar" aria-label="Default select example" required>


                                                        <?php while ($row_status = mysqli_fetch_assoc($status)) { ?>

                                                            <option value="<?php echo $row_status['STATUSID']; ?>">

                                                                <?php echo $row_status['Status']; ?>

                                                            </option>

                                                        <?php }

                                                        // Reset the pointer to the beginning
                                                        mysqli_data_seek($clientes, 0);

                                                        ?>

                                                    </select>
                                                </div>
                                            </div>

                                            <!--  style="display:none" -->

                                            <div class="row FechasEntregaEscondidos">

                                                <div class="col-lg-12 col-sm-12 mb-4">
                                                    <label for="FechayHoraReparto" class="form-label">Programación</label>

                                                    <input class="form-control flatpickr2" id="FechayHoraReparto" type="text" placeholder="Registra la fecha programada de reparto...">
                                                </div>
                                            </div>

                                            <div class="row StatusEscondidos" style="display:none">
                                                <div class="col-lg-12 col-sm-12 mb-4">
                                                    <label for="MotivoDelEstatus" class="form-label">Motivo del estatus</label>

                                                    <textarea class="form-control" id="MotivoDelEstatus" name="MotivoDelEstatus" maxlength="500" rows="4"></textarea>
                                                </div>
                                            </div>


                                            <div class="row RepartosEscondidos" style="display:none">
                                                <div class="col-lg-12 col-sm-12 mb-4">
                                                    <label for="Surtidores" class="form-label">Surtidor</label>

                                                    <textarea class="form-control" id="Surtidores" name="Surtidores" maxlength="500" rows="4"></textarea>
                                                </div>
                                            </div>


                                            <div class="row RepartosEscondidos" style="display:none">
                                                <div class="col-lg-12 col-sm-12 mb-4">
                                                    <label for="USUARIOIDRepartidor" class="form-label">Repartidor</label>
                                                    <select class="form-select" name="USUARIOIDRepartidor" id="USUARIOIDRepartidor" aria-label="Default select example">

                                                        <option value=""> -
                                                        </option>
                                                        <?php while ($row_Repartidores = mysqli_fetch_assoc($Repartidores)) { ?>

                                                            <option value="<?php echo $row_Repartidores['USUARIOID']; ?>">

                                                                <?php echo $row_Repartidores['ApellidoPaterno'] . ' ' . $row_Repartidores['ApellidoMaterno'] . ' ' . $row_Repartidores['PrimerNombre'] . ' ' . $row_Repartidores['SegundoNombre']; ?>

                                                            </option>

                                                        <?php }

                                                        // Reset the pointer to the beginning
                                                        mysqli_data_seek($clientes, 0);

                                                        ?>

                                                    </select>
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


                    <input type="hidden" class="form-control" id="REPARTOIDEditarStatus" name="REPARTOIDEditarStatus">
                    <input type="hidden" class="form-control" id="FechaReparto" name="FechaReparto">
                    <input type="hidden" class="form-control" id="HoraReparto" name="HoraReparto">


                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Editar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal" id="ModalChecarSelect2">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Checar Select 2</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="btn-close"></button>
            </div>
            <form data-parsley-validate class="forms-sample" id="ValidacionAgregarRepartos">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-lg-12 col-md-12 col-sm-12">
                                            <div class="row">
                                                <div class="col-lg-12 col-sm-12 mb-4">
                                                    <label for="CLIENTEID" class="form-label">Producto</label>
                                                    <select class="select2" name="CLIENTEID" id="CLIENTEID" aria-label="Default select example" required>
                                                        <option selected>Selecciona cliente</option>

                                                        <?php while ($row_productos = mysqli_fetch_assoc($productos)) { ?>

                                                            <option value="<?php echo $row_productos['PRODUCTOSID']; ?>">

                                                                <?php echo $row_productos['Sku'] . " - " . $row_productos['Descripcion'] . " - " . $row_productos['MarcaProductos']; ?>

                                                            </option>

                                                        <?php }

                                                        // Reset the pointer to the beginning
                                                        mysqli_data_seek($clientes, 0);

                                                        ?>

                                                    </select>
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


                    <input type="hidden" class="form-control" id="USUARIOID" name="USUARIOID" value="<?php echo $_SESSION['USUARIOID']; ?>">

                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Agregar</button>
                </div>
            </form>
        </div>
    </div>
</div>