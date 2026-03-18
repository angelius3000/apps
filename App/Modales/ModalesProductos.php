<div class="modal" id="ModalAgregarProductos">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Agregar producto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form data-parsley-validate class="forms-sample" id="ValidacionAgregarProductos">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="Sku" class="form-label">SKU</label>
                                <input type="text" class="form-control" id="Sku" autocomplete="off" placeholder="SKU" name="Sku" maxlength="10" required>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="Descripcion" class="form-label">Descripción</label>
                                <input type="text" class="form-control" id="Descripcion" autocomplete="off" placeholder="Descripción" name="Descripcion" maxlength="200">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="MarcaProductos" class="form-label">Marca</label>
                                <input type="text" class="form-control" id="MarcaProductos" autocomplete="off" placeholder="Marca" name="MarcaProductos" maxlength="100">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal" id="ModalEditarProductos">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar producto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form data-parsley-validate class="forms-sample" id="ValidacionEditarProductos">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="SkuEditar" class="form-label">SKU</label>
                                <input type="text" class="form-control" id="SkuEditar" autocomplete="off" placeholder="SKU" name="SkuEditar" maxlength="10" required>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="DescripcionEditar" class="form-label">Descripción</label>
                                <input type="text" class="form-control" id="DescripcionEditar" autocomplete="off" placeholder="Descripción" name="DescripcionEditar" maxlength="200">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="MarcaProductosEditar" class="form-label">Marca</label>
                                <input type="text" class="form-control" id="MarcaProductosEditar" autocomplete="off" placeholder="Marca" name="MarcaProductosEditar" maxlength="100">
                            </div>
                        </div>
                    </div>

                    <input type="hidden" id="PRODUCTOSIDEditar" name="PRODUCTOSIDEditar">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal" id="ModalBorrarProductos">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Borrar producto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h6>¿Deseas borrar este producto?</h6>
                <p class="mb-0" id="NombreProductoBorrar"></p>
                <input type="hidden" id="PRODUCTOSIDBorrar">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="BorrarProducto">Borrar</button>
            </div>
        </div>
    </div>
</div>
