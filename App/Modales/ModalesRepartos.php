<style>
.reparto-layout-modal .modal-dialog {
    max-width: 1180px;
}

.reparto-layout-modal .reparto-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 20px;
}

.reparto-layout-modal .reparto-column {
    min-width: 0;
}

.reparto-layout-modal .reparto-field-full,
.reparto-layout-modal .reparto-field-full .form-control,
.reparto-layout-modal .reparto-field-full .select2,
.reparto-layout-modal .reparto-field-full .select2-container {
    width: 100% !important;
    min-width: 0;
}

.reparto-layout-modal .reparto-dual-field {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;
}

.reparto-layout-modal .reparto-help-text {
    color: #98A2B3;
    font-size: 0.9rem;
    margin-bottom: 12px;
}

.reparto-layout-modal .reparto-mini-mapa {
    width: 100%;
    height: 260px;
    border: 1px solid #d0d5dd;
    border-radius: 10px;
    background: #f8f9fb;
}

.reparto-layout-modal .reparto-comentarios {
    min-height: 140px;
}

@media (max-width: 1199.98px) {
    .reparto-layout-modal .reparto-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .reparto-layout-modal .reparto-column:last-child {
        grid-column: 1 / -1;
    }
}

@media (max-width: 767.98px) {
    .reparto-layout-modal .reparto-grid {
        grid-template-columns: 1fr;
    }

    .reparto-layout-modal .reparto-column:last-child {
        grid-column: auto;
    }
}
</style>

<div class="modal reparto-layout-modal" id="ModalAgregarReparto">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Agregar reparto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="btn-close"></button>
            </div>
            <form method="post" data-parsley-validate class="forms-sample" id="ValidacionAgregarRepartos">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="reparto-grid">
                                        <div class="reparto-column reparto-column-left">
                                            <div class="mb-4 reparto-field-full">
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
                                            <div class="mb-4 reparto-field-full">
                                                <label for="NumeroDeFactura" class="form-label">Número de Factura</label>
                                                <input type="text" class="form-control" id="NumeroDeFactura" autocomplete="off" name="NumeroDeFactura" required>
                                            </div>
                                            <div class="mb-4 reparto-field-full">
                                                <label for="CalleNumero" class="form-label">Calle y número</label>
                                                <input type="text" class="form-control" id="CalleNumero" autocomplete="off" required>
                                            </div>
                                            <input type="hidden" id="Calle" name="Calle">
                                            <input type="hidden" id="NumeroEXT" name="NumeroEXT">
                                            <div class="mb-4 reparto-field-full">
                                                <label for="Colonia" class="form-label">Colonia</label>
                                                <input type="text" class="form-control" id="Colonia" autocomplete="off" name="Colonia" required>
                                            </div>
                                            <div class="mb-4 reparto-field-full">
                                                <label for="CP" class="form-label">Código Postal</label>
                                                <input type="text" class="form-control" id="CP" autocomplete="off" name="CP" required>
                                            </div>
                                            <div class="mb-4 reparto-field-full">
                                                <label for="Ciudad" class="form-label">Ciudad</label>
                                                <input type="text" class="form-control" id="Ciudad" autocomplete="off" name="Ciudad" required>
                                            </div>
                                            <div class="mb-0 reparto-field-full">
                                                <label for="Estado" class="form-label">Estado</label>
                                                <input type="text" class="form-control" id="Estado" autocomplete="off" name="Estado" required>
                                            </div>
                                        </div>

                                        <div class="reparto-column">
                                            <div class="mb-2 reparto-field-full">
                                                <label for="EnlaceGoogleMaps" class="form-label">Enlace Google Maps</label>
                                                <input type="text" class="form-control" id="EnlaceGoogleMaps" autocomplete="off" name="EnlaceGoogleMaps">
                                            </div>
                                            <p class="reparto-help-text">El enlace se genera automáticamente con la dirección capturada.</p>
                                            <div class="mb-0 reparto-field-full">
                                                <label for="MiniMapaReparto" class="form-label">Mini mapa</label>
                                                <iframe
                                                    id="MiniMapaReparto"
                                                    class="reparto-mini-mapa"
                                                    src="about:blank"
                                                    title="Mini mapa de ubicación"
                                                    loading="lazy"
                                                    referrerpolicy="no-referrer-when-downgrade"></iframe>
                                            </div>
                                        </div>

                                        <div class="reparto-column">
                                            <div class="mb-4 reparto-field-full">
                                                <label for="Receptor" class="form-label">Receptor</label>
                                                <input type="text" class="form-control" id="Receptor" autocomplete="off" name="Receptor" required>
                                            </div>
                                            <div class="reparto-dual-field mb-4">
                                                <div>
                                                    <label for="TelefonoDeReceptor" class="form-label">Teléfono del Receptor</label>
                                                    <input type="text" class="form-control" id="TelefonoDeReceptor" autocomplete="off" name="TelefonoDeReceptor" required placeholder="Número sin guiones ni espacios" data-parsley-pattern="^\d{10}$">
                                                </div>
                                                <div>
                                                    <label for="TelefonoAlternativo" class="form-label">Teléfono Alternativo</label>
                                                    <input type="text" class="form-control" id="TelefonoAlternativo" autocomplete="off" name="TelefonoAlternativo" data-parsley-pattern="^\d{10}$">
                                                </div>
                                            </div>
                                            <div class="mb-0 reparto-field-full">
                                                <label for="Comentarios" class="form-label">Comentarios</label>
                                                <textarea class="form-control reparto-comentarios" id="Comentarios" name="Comentarios" rows="4"></textarea>
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
<div class="modal reparto-layout-modal" id="ModalEditarReparto">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar reparto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="btn-close"></button>
            </div>
            <form method="post" data-parsley-validate class="forms-sample" id="ValidacionEditarRepartos">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="reparto-grid">
                                        <div class="reparto-column reparto-column-left">
                                            <div class="mb-4 reparto-field-full">
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
                                            <div class="mb-4 reparto-field-full">
                                                <label for="NumeroDeFacturaEditar" class="form-label">Número de Factura</label>
                                                <input type="text" class="form-control" id="NumeroDeFacturaEditar" autocomplete="off" name="NumeroDeFacturaEditar" required>
                                            </div>
                                            <div class="mb-4 reparto-field-full">
                                                <label for="CalleNumeroEditar" class="form-label">Calle y número</label>
                                                <input type="text" class="form-control" id="CalleNumeroEditar" autocomplete="off" required>
                                            </div>
                                            <input type="hidden" id="CalleEditar" name="CalleEditar">
                                            <input type="hidden" id="NumeroEXTEditar" name="NumeroEXTEditar">
                                            <div class="mb-4 reparto-field-full">
                                                <label for="ColoniaEditar" class="form-label">Colonia</label>
                                                <input type="text" class="form-control" id="ColoniaEditar" autocomplete="off" name="ColoniaEditar" required>
                                            </div>
                                            <div class="mb-4 reparto-field-full">
                                                <label for="CPEditar" class="form-label">Código Postal</label>
                                                <input type="text" class="form-control" id="CPEditar" autocomplete="off" name="CPEditar" required>
                                            </div>
                                            <div class="mb-4 reparto-field-full">
                                                <label for="CiudadEditar" class="form-label">Ciudad</label>
                                                <input type="text" class="form-control" id="CiudadEditar" autocomplete="off" name="CiudadEditar" required>
                                            </div>
                                            <div class="mb-0 reparto-field-full">
                                                <label for="EstadoEditar" class="form-label">Estado</label>
                                                <input type="text" class="form-control" id="EstadoEditar" autocomplete="off" name="EstadoEditar" required>
                                            </div>
                                        </div>

                                        <div class="reparto-column">
                                            <div class="mb-2 reparto-field-full">
                                                <label for="EnlaceGoogleMapsEditar" class="form-label">Enlace Google Maps</label>
                                                <input type="text" class="form-control" id="EnlaceGoogleMapsEditar" autocomplete="off" name="EnlaceGoogleMapsEditar">
                                            </div>
                                            <p class="reparto-help-text">El enlace se genera automáticamente con la dirección capturada.</p>
                                            <div class="mb-0 reparto-field-full">
                                                <label for="MiniMapaRepartoEditar" class="form-label">Mini mapa</label>
                                                <iframe id="MiniMapaRepartoEditar" class="reparto-mini-mapa" src="about:blank" title="Mini mapa de ubicación" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                                            </div>
                                        </div>

                                        <div class="reparto-column">
                                            <div class="mb-4 reparto-field-full">
                                                <label for="ReceptorEditar" class="form-label">Receptor</label>
                                                <input type="text" class="form-control" id="ReceptorEditar" autocomplete="off" name="ReceptorEditar" required>
                                            </div>
                                            <div class="reparto-dual-field mb-4">
                                                <div>
                                                    <label for="TelefonoDeReceptorEditar" class="form-label">Teléfono del Receptor</label>
                                                    <input type="text" class="form-control" id="TelefonoDeReceptorEditar" autocomplete="off" name="TelefonoDeReceptorEditar" required>
                                                </div>
                                                <div>
                                                    <label for="TelefonoAlternativoEditar" class="form-label">Teléfono Alternativo</label>
                                                    <input type="text" class="form-control" id="TelefonoAlternativoEditar" autocomplete="off" name="TelefonoAlternativoEditar">
                                                </div>
                                            </div>
                                            <div class="mb-0 reparto-field-full">
                                                <label for="ComentariosEditar" class="form-label">Comentarios</label>
                                                <textarea class="form-control reparto-comentarios" id="ComentariosEditar" name="ComentariosEditar" rows="4"></textarea>
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
<div class="modal reparto-layout-modal" id="ModalClonarReparto">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Clonar reparto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="btn-close"></button>
            </div>
            <form method="post" data-parsley-validate class="forms-sample" id="ValidacionClonarRepartos">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="reparto-grid">
                                        <div class="reparto-column reparto-column-left">
                                            <div class="mb-4 reparto-field-full">
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
                                            <div class="mb-4 reparto-field-full">
                                                <label for="NumeroDeFacturaClonar" class="form-label">Número de Factura</label>
                                                <input type="text" class="form-control" id="NumeroDeFacturaClonar" autocomplete="off" name="NumeroDeFacturaClonar" required>
                                            </div>
                                            <div class="mb-4 reparto-field-full">
                                                <label for="CalleNumeroClonar" class="form-label">Calle y número</label>
                                                <input type="text" class="form-control" id="CalleNumeroClonar" autocomplete="off" required>
                                            </div>
                                            <input type="hidden" id="CalleClonar" name="CalleClonar">
                                            <input type="hidden" id="NumeroEXTClonar" name="NumeroEXTClonar">
                                            <div class="mb-4 reparto-field-full">
                                                <label for="ColoniaClonar" class="form-label">Colonia</label>
                                                <input type="text" class="form-control" id="ColoniaClonar" autocomplete="off" name="ColoniaClonar" required>
                                            </div>
                                            <div class="mb-4 reparto-field-full">
                                                <label for="CPClonar" class="form-label">Código Postal</label>
                                                <input type="text" class="form-control" id="CPClonar" autocomplete="off" name="CPClonar" required>
                                            </div>
                                            <div class="mb-4 reparto-field-full">
                                                <label for="CiudadClonar" class="form-label">Ciudad</label>
                                                <input type="text" class="form-control" id="CiudadClonar" autocomplete="off" name="CiudadClonar" required>
                                            </div>
                                            <div class="mb-0 reparto-field-full">
                                                <label for="EstadoClonar" class="form-label">Estado</label>
                                                <input type="text" class="form-control" id="EstadoClonar" autocomplete="off" name="EstadoClonar" required>
                                            </div>
                                        </div>

                                        <div class="reparto-column">
                                            <div class="mb-2 reparto-field-full">
                                                <label for="EnlaceGoogleMapsClonar" class="form-label">Enlace Google Maps</label>
                                                <input type="text" class="form-control" id="EnlaceGoogleMapsClonar" autocomplete="off" name="EnlaceGoogleMapsClonar">
                                            </div>
                                            <p class="reparto-help-text">El enlace se genera automáticamente con la dirección capturada.</p>
                                            <div class="mb-0 reparto-field-full">
                                                <label for="MiniMapaRepartoClonar" class="form-label">Mini mapa</label>
                                                <iframe id="MiniMapaRepartoClonar" class="reparto-mini-mapa" src="about:blank" title="Mini mapa de ubicación" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                                            </div>
                                        </div>

                                        <div class="reparto-column">
                                            <div class="mb-4 reparto-field-full">
                                                <label for="ReceptorClonar" class="form-label">Receptor</label>
                                                <input type="text" class="form-control" id="ReceptorClonar" autocomplete="off" name="ReceptorClonar" required>
                                            </div>
                                            <div class="reparto-dual-field mb-4">
                                                <div>
                                                    <label for="TelefonoDeReceptorClonar" class="form-label">Teléfono del Receptor</label>
                                                    <input type="text" class="form-control" id="TelefonoDeReceptorClonar" autocomplete="off" name="TelefonoDeReceptorClonar" required>
                                                </div>
                                                <div>
                                                    <label for="TelefonoAlternativoClonar" class="form-label">Teléfono Alternativo</label>
                                                    <input type="text" class="form-control" id="TelefonoAlternativoClonar" autocomplete="off" name="TelefonoAlternativoClonar">
                                                </div>
                                            </div>
                                            <div class="mb-0 reparto-field-full">
                                                <label for="ComentariosClonar" class="form-label">Comentarios</label>
                                                <textarea class="form-control reparto-comentarios" id="ComentariosClonar" name="ComentariosClonar" rows="4"></textarea>
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
            <form method="post" data-parsley-validate class="forms-sample" id="ValidacionAgregarRepartos">
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
