<?php include("includes/HeaderScripts.php");

$pageTitle = 'Edison - Encuestas';

if (!usuarioTieneAccesoSeccion('encuestas')) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<?php include("includes/Header.php") ?>

<body>
    <div class="app full-width-header align-content-stretch d-flex flex-wrap">
        <div class="app-sidebar">
            <div class="logo logo-sm">
                <a href="main.php"> <img src="App/Graficos/Logo/LogoEdison.png" style="max-width :130px;"> </a>
            </div>

            <?php include("includes/Menu.php") ?>

        </div>
        <div class="app-container">
            <div class="search">
                <form></form>
                <a href="#" class="toggle-search"><i class="material-icons">close</i></a>
            </div>

            <?php include("includes/MenuHeader.php") ?>

            <div class="app-content">
                <div class="content-wrapper">
                    <div class="container-fluid">
                        <div class="row mb-3">
                            <div class="col-lg-8">
                                <div class="page-description">
                                    <h2>Encuestas</h2>
                                    <span>Administra borradores, constructor visual y vista previa para encuestas internas.</span>
                                </div>
                            </div>
                            <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                                <button id="BtnNuevaEncuesta" class="btn btn-primary btn-sm"><i class="material-icons-two-tone">add</i> Crear encuesta</button>
                                <button id="BtnPlantillaEmpleadoMes" class="btn btn-outline-secondary btn-sm">Plantilla Empleado del mes</button>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-body">
                                <div class="row g-2 align-items-end">
                                    <div class="col-md-7">
                                        <label class="form-label">Buscar por nombre</label>
                                        <input type="text" id="FiltroTituloEncuesta" class="form-control" placeholder="Ej. Empleado del mes">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Estado</label>
                                        <select id="FiltroEstadoEncuesta" class="form-select">
                                            <option value="todos">Todos</option>
                                            <option value="borrador">Borrador</option>
                                            <option value="publicada">Publicada</option>
                                            <option value="cerrada">Cerrada</option>
                                            <option value="archivada">Archivada</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2 d-grid">
                                        <button class="btn btn-outline-primary" id="BtnBuscarEncuestas">Buscar</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive mb-4">
                            <table class="table table-striped align-middle" id="TablaEncuestas">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Título</th>
                                        <th>Descripción</th>
                                        <th>Estado</th>
                                        <th>Creada por</th>
                                        <th>Creación</th>
                                        <th>Publicación</th>
                                        <th>Respuestas</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>

                        <div class="card" id="CardEditorEncuesta" style="display:none;">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h4 class="mb-0">Editor de encuesta</h4>
                                    <div>
                                        <button class="btn btn-outline-info btn-sm" id="BtnVistaPrevia">Vista previa</button>
                                        <button class="btn btn-outline-secondary btn-sm" id="BtnCerrarEditor">Cerrar</button>
                                    </div>
                                </div>

                                <input type="hidden" id="EncuestaID" value="0">

                                <div class="row">
                                    <div class="col-lg-8">
                                        <div class="mb-3">
                                            <label class="form-label">Título</label>
                                            <input type="text" id="EncuestaTitulo" class="form-control" maxlength="200" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Descripción</label>
                                            <textarea id="EncuestaDescripcion" class="form-control" rows="2" maxlength="2000"></textarea>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="mb-3">
                                            <label class="form-label">Categoría</label>
                                            <input type="text" id="EncuestaCategoria" class="form-control" maxlength="100">
                                        </div>
                                        <div class="mb-2 form-check">
                                            <input class="form-check-input" type="checkbox" id="EncuestaAnonima">
                                            <label class="form-check-label" for="EncuestaAnonima">Respuesta anónima</label>
                                        </div>
                                        <div class="mb-2 form-check">
                                            <input class="form-check-input" type="checkbox" id="EncuestaUnaRespuesta" checked>
                                            <label class="form-check-label" for="EncuestaUnaRespuesta">Una respuesta por usuario</label>
                                        </div>
                                        <div class="mb-2 form-check">
                                            <input class="form-check-input" type="checkbox" id="EncuestaMultiplesRespuestas">
                                            <label class="form-check-label" for="EncuestaMultiplesRespuestas">Permitir múltiples respuestas</label>
                                        </div>
                                        <div class="mb-2 form-check">
                                            <input class="form-check-input" type="checkbox" id="EncuestaRequiereLogin" checked>
                                            <label class="form-check-label" for="EncuestaRequiereLogin">Restringida a usuarios autenticados</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Fecha inicio</label>
                                        <input type="date" id="EncuestaFechaInicio" class="form-control">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Fecha fin</label>
                                        <input type="date" id="EncuestaFechaFin" class="form-control">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Mensaje de confirmación</label>
                                        <input type="text" id="EncuestaMensajeConfirmacion" class="form-control" maxlength="1000" value="Gracias por responder la encuesta.">
                                    </div>
                                </div>

                                <hr>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="mb-0">Constructor de preguntas</h5>
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-outline-primary" id="BtnAgregarPreguntaTextoCorto">+ Texto corto</button>
                                        <button class="btn btn-sm btn-outline-primary" id="BtnAgregarPreguntaTextoLargo">+ Texto largo</button>
                                        <button class="btn btn-sm btn-outline-primary" id="BtnAgregarPreguntaOpcion">+ Opción múltiple</button>
                                        <button class="btn btn-sm btn-outline-primary" id="BtnAgregarEscalaAgrupada">+ Bloque escala</button>
                                    </div>
                                </div>

                                <div id="ContenedorPreguntas"></div>

                                <div class="mt-4 text-end">
                                    <button class="btn btn-primary" id="BtnGuardarBorrador">Guardar borrador</button>
                                </div>
                            </div>
                        </div>

                        <div class="card mt-4" id="CardVistaPrevia" style="display:none;">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h4 class="mb-0">Vista previa</h4>
                                    <button class="btn btn-outline-secondary btn-sm" id="BtnCerrarVistaPrevia">Cerrar vista previa</button>
                                </div>
                                <div id="VistaPreviaContenido"></div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/plugins/jquery/jquery-3.5.1.min.js"></script>
    <script src="assets/plugins/bootstrap/js/popper.min.js"></script>
    <script src="assets/plugins/bootstrap/js/bootstrap.min.js"></script>
    <script src="assets/plugins/perfectscroll/perfect-scrollbar.min.js"></script>
    <script src="assets/plugins/pace/pace.min.js"></script>
    <script src="assets/plugins/highlight/highlight.pack.js"></script>
    <script src="assets/js/main.min.js"></script>
    <script src="assets/js/custom.js"></script>

    <script src="App/js/AppEncuestas.js"></script>
    <script src="App/js/AppCambiarContrasena.js"></script>
</body>

</html>
