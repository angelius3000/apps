<div class="modal" id="ModalAgregarTicket">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Levantar ticket</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="btn-close"></button>
            </div>

            <div class="modal-body">
                <form id="ValidacionAgregarTicket" data-parsley-validate>
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="mb-3">
                                <label class="form-label">Título</label>
                                <input type="text" class="form-control" name="Titulo" id="Titulo" required>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="mb-3">
                                <label class="form-label">Prioridad</label>
                                <select class="form-control" name="Prioridad" id="Prioridad" required>
                                    <option value="Baja">Baja</option>
                                    <option value="Media" selected>Media</option>
                                    <option value="Alta">Alta</option>
                                    <option value="Urgente">Urgente</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="mb-3">
                                <label class="form-label">Categoría</label>
                                <select class="form-control" name="Categoria" id="Categoria" required>
                                    <option value="Software">Software</option>
                                    <option value="Hardware">Hardware</option>
                                    <option value="Red">Red</option>
                                    <option value="Correo">Correo</option>
                                    <option value="Accesos">Accesos</option>
                                    <option value="Otros" selected>Otros</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="mb-3">
                                <label class="form-label">Descripción</label>
                                <textarea class="form-control" name="Descripcion" id="Descripcion" rows="4" required></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="ModalEditarTicket">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar ticket</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="btn-close"></button>
            </div>

            <div class="modal-body">
                <form id="ValidacionEditarTicket" data-parsley-validate>
                    <input type="hidden" id="TICKETIDEditar" name="TICKETIDEditar">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="mb-3">
                                <label class="form-label">Título</label>
                                <input type="text" class="form-control" name="TituloEditar" id="TituloEditar" required>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="mb-3">
                                <label class="form-label">Prioridad</label>
                                <select class="form-control" name="PrioridadEditar" id="PrioridadEditar" required>
                                    <option value="Baja">Baja</option>
                                    <option value="Media">Media</option>
                                    <option value="Alta">Alta</option>
                                    <option value="Urgente">Urgente</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="mb-3">
                                <label class="form-label">Categoría</label>
                                <select class="form-control" name="CategoriaEditar" id="CategoriaEditar" required>
                                    <option value="Software">Software</option>
                                    <option value="Hardware">Hardware</option>
                                    <option value="Red">Red</option>
                                    <option value="Correo">Correo</option>
                                    <option value="Accesos">Accesos</option>
                                    <option value="Otros">Otros</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="mb-3">
                                <label class="form-label">Descripción</label>
                                <textarea class="form-control" name="DescripcionEditar" id="DescripcionEditar" rows="4" required></textarea>
                            </div>
                        </div>
                        <div class="col-sm-6 bloque-admin-edicion" <?php echo $esAdmin ? '' : 'style="display:none;"'; ?>>
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select class="form-control" name="StatusEditar" id="StatusEditar">
                                    <option value="Abierto">Abierto</option>
                                    <option value="EnProceso">EnProceso</option>
                                    <option value="EnEspera">EnEspera</option>
                                    <option value="Resuelto">Resuelto</option>
                                    <option value="Cerrado">Cerrado</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6 bloque-admin-edicion" <?php echo $esAdmin ? '' : 'style="display:none;"'; ?>>
                            <div class="mb-3">
                                <label class="form-label">Asignado a</label>
                                <select class="form-control" name="USUARIOID_ASIGNADOEditar" id="USUARIOID_ASIGNADOEditar">
                                    <option value="">Sin asignar</option>
                                    <?php foreach ($usuariosActivos as $usuarioActivo) {
                                        $nombreCompleto = trim(
                                            (string)$usuarioActivo['ApellidoPaterno'] . ' ' .
                                            (string)$usuarioActivo['ApellidoMaterno'] . ' ' .
                                            (string)$usuarioActivo['PrimerNombre'] . ' ' .
                                            (string)$usuarioActivo['SegundoNombre']
                                        );
                                    ?>
                                        <option value="<?php echo (int)$usuarioActivo['USUARIOID']; ?>"><?php echo htmlspecialchars($nombreCompleto, ENT_QUOTES, 'UTF-8'); ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="ModalCerrarTicket">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cerrar ticket</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="btn-close"></button>
            </div>

            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="card">
                            <div class="card-body">
                                ¿Deseas cerrar este ticket?
                                <br>
                                <br>
                                <h3 id="FolioTicketCerrar"></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <input type="hidden" id="TICKETIDCerrar" name="TICKETIDCerrar">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" id="CerrarTicket" class="btn btn-danger">Confirmar</button>
            </div>

        </div>
    </div>
</div>
