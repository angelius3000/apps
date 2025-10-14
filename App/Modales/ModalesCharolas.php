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
                            <?php if ($statusVerificadoId !== null) { ?>
                                <option value="<?php echo htmlspecialchars((string) $statusVerificadoId, ENT_QUOTES, 'UTF-8'); ?>"<?php echo !$puedeAsignarVerificado ? ' data-requires-privilegios="true"' : ''; ?>>
                                    <?php echo htmlspecialchars($statusVerificadoNombre, ENT_QUOTES, 'UTF-8'); ?><?php if (!$puedeAsignarVerificado) { ?> (solo supervisor, auditor o administrador)<?php } ?>
                                </option>
                            <?php } ?>
                            <?php if ($statusAuditadoId !== null) { ?>
                                <option value="<?php echo htmlspecialchars((string) $statusAuditadoId, ENT_QUOTES, 'UTF-8'); ?>"<?php echo !$puedeAsignarAuditado ? ' data-requires-auditor="true"' : ''; ?>>
                                    <?php echo htmlspecialchars($statusAuditadoNombre, ENT_QUOTES, 'UTF-8'); ?><?php if (!$puedeAsignarAuditado) { ?> (solo auditor)<?php } ?>
                                </option>
                            <?php } ?>
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
