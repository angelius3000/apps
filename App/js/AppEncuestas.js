$(document).ready(function() {
  var preguntas = [];

  function escapeHtml(value) {
    return $('<div>').text(value == null ? '' : String(value)).html();
  }

  function normalizarCriteriosParaUI(criterios) {
    if (!Array.isArray(criterios)) return [];
    return criterios.map(function(item) {
      if (item && typeof item === 'object') {
        return item.label || '';
      }
      return item || '';
    });
  }

  function formatearFecha(fecha) {
    if (!fecha) return '';
    return String(fecha).replace('T', ' ').substring(0, 16);
  }

  function estadoBadge(estado) {
    var mapa = {
      borrador: 'badge badge-secondary',
      publicada: 'badge badge-success',
      cerrada: 'badge badge-warning',
      archivada: 'badge badge-dark'
    };
    var cls = mapa[estado] || 'badge badge-light';
    return '<span class="' + cls + '">' + escapeHtml(estado) + '</span>';
  }

  function cargarListado() {
    $.ajax({
      type: 'POST',
      url: 'App/Server/ServerEncuestas.php',
      dataType: 'json',
      data: {
        action: 'list',
        q: $('#FiltroTituloEncuesta').val(),
        estado: $('#FiltroEstadoEncuesta').val()
      },
      success: function(response) {
        var $tbody = $('#TablaEncuestas tbody');
        $tbody.empty();

        if (!response.success || !response.items || !response.items.length) {
          $tbody.append('<tr><td colspan="9" class="text-center text-muted">No hay encuestas registradas.</td></tr>');
          return;
        }

        response.items.forEach(function(item) {
          var acciones = '' +
            '<div class="btn-group btn-group-sm">' +
            '<button class="btn btn-outline-primary btn-editar" data-id="' + item.ENCUESTAID + '">Editar</button>' +
            '<button class="btn btn-outline-secondary btn-duplicar" data-id="' + item.ENCUESTAID + '">Duplicar</button>' +
            '<button class="btn btn-outline-success btn-publicar" data-id="' + item.ENCUESTAID + '">Publicar</button>' +
            '<button class="btn btn-outline-warning btn-cerrar" data-id="' + item.ENCUESTAID + '">Cerrar</button>' +
            '<button class="btn btn-outline-dark btn-archivar" data-id="' + item.ENCUESTAID + '">Archivar</button>' +
            '</div>';

          $tbody.append('<tr>' +
            '<td>' + item.ENCUESTAID + '</td>' +
            '<td>' + escapeHtml(item.Titulo) + '</td>' +
            '<td>' + escapeHtml((item.Descripcion || '').substring(0, 120)) + '</td>' +
            '<td>' + estadoBadge(item.Estado) + '</td>' +
            '<td>' + escapeHtml(item.Creador || '-') + '</td>' +
            '<td>' + escapeHtml(formatearFecha(item.FechaCreacion)) + '</td>' +
            '<td>' + escapeHtml(formatearFecha(item.FechaPublicacion)) + '</td>' +
            '<td>' + (item.TotalRespuestas || 0) + '</td>' +
            '<td>' + acciones + '</td>' +
            '</tr>');
        });
      }
    });
  }

  function abrirEditor() {
    $('#CardEditorEncuesta').show();
    $('#CardVistaPrevia').hide();
    window.scrollTo(0, document.body.scrollHeight);
  }

  function limpiarFormulario() {
    $('#EncuestaID').val('0');
    $('#EncuestaTitulo').val('');
    $('#EncuestaDescripcion').val('');
    $('#EncuestaCategoria').val('');
    $('#EncuestaAnonima').prop('checked', false);
    $('#EncuestaUnaRespuesta').prop('checked', true);
    $('#EncuestaMultiplesRespuestas').prop('checked', false);
    $('#EncuestaRequiereLogin').prop('checked', true);
    $('#EncuestaFechaInicio').val('');
    $('#EncuestaFechaFin').val('');
    $('#EncuestaMensajeConfirmacion').val('Gracias por responder la encuesta.');
    preguntas = [];
    renderPreguntas();
  }

  function crearPreguntaBase(tipo) {
    return {
      id_local: 'q_' + Date.now() + '_' + Math.floor(Math.random() * 1000),
      tipo: tipo,
      titulo: '',
      descripcion: '',
      requerida: false,
      opciones: tipo === 'opcion_multiple' ? ['Opción 1', 'Opción 2'] : [],
      criterios: tipo === 'escala_agrupada' ? ['Criterio 1'] : [],
      opciones_escala: tipo === 'escala_agrupada' ? ['Sobresaliente', 'Destacado', 'Normal'] : [],
      permitir_otras: tipo === 'escala_agrupada'
    };
  }

  function renderPreguntas() {
    var $contenedor = $('#ContenedorPreguntas');
    $contenedor.empty();

    if (!preguntas.length) {
      $contenedor.append('<div class="alert alert-light">Aún no hay preguntas. Usa los botones de arriba para comenzar.</div>');
      return;
    }

    preguntas.forEach(function(pregunta, index) {
      var html = '' +
        '<div class="card mb-3 encuesta-pregunta" data-index="' + index + '">' +
        '<div class="card-body">' +
        '<div class="d-flex justify-content-between align-items-center mb-2">' +
        '<strong>Pregunta #' + (index + 1) + '</strong>' +
        '<div class="btn-group btn-group-sm">' +
        '<button class="btn btn-outline-secondary btn-subir">↑</button>' +
        '<button class="btn btn-outline-secondary btn-bajar">↓</button>' +
        '<button class="btn btn-outline-info btn-duplicar-pregunta">Duplicar</button>' +
        '<button class="btn btn-outline-danger btn-eliminar-pregunta">Eliminar</button>' +
        '</div>' +
        '</div>' +
        '<div class="mb-2"><label class="form-label">Tipo</label>' +
        '<select class="form-select input-tipo">' +
        '<option value="texto_corto">Texto corto</option>' +
        '<option value="texto_largo">Texto largo</option>' +
        '<option value="opcion_multiple">Opción múltiple</option>' +
        '<option value="escala_agrupada">Escala agrupada</option>' +
        '</select></div>' +
        '<div class="mb-2"><label class="form-label">Enunciado</label><input type="text" class="form-control input-titulo" maxlength="500"></div>' +
        '<div class="mb-2"><label class="form-label">Descripción</label><textarea class="form-control input-descripcion" rows="2" maxlength="2000"></textarea></div>' +
        '<div class="form-check mb-2"><input class="form-check-input input-requerida" type="checkbox" id="req_' + pregunta.id_local + '"><label class="form-check-label" for="req_' + pregunta.id_local + '">Obligatoria</label></div>' +
        '<div class="bloque-opciones"></div>' +
        '</div></div>';

      var $card = $(html);
      $card.find('.input-tipo').val(pregunta.tipo);
      $card.find('.input-titulo').val(pregunta.titulo);
      $card.find('.input-descripcion').val(pregunta.descripcion);
      $card.find('.input-requerida').prop('checked', !!pregunta.requerida);

      var $bloqueOpciones = $card.find('.bloque-opciones');

      if (pregunta.tipo === 'opcion_multiple') {
        var bloque = '<div class="encuesta-box-soft"><label class="form-label">Opciones</label>';
        pregunta.opciones.forEach(function(op, opIndex) {
          bloque += '<div class="input-group mb-2">' +
            '<input class="form-control input-opcion" data-opindex="' + opIndex + '" value="' + escapeHtml(op) + '">' +
            '<button class="btn btn-outline-danger btn-eliminar-opcion" data-opindex="' + opIndex + '">X</button>' +
            '</div>';
        });
        bloque += '<button class="btn btn-sm btn-outline-primary btn-agregar-opcion">+ Agregar opción</button></div>';
        $bloqueOpciones.html(bloque);
      }

      if (pregunta.tipo === 'escala_agrupada') {
        var bloqueEscala = '<div class="encuesta-box-soft">' +
          '<label class="form-label">Criterios del bloque</label>';

        (pregunta.criterios || []).forEach(function(criterio, i) {
          bloqueEscala += '<div class="input-group mb-2">' +
            '<input class="form-control input-criterio" data-cindex="' + i + '" value="' + escapeHtml(criterio) + '">' +
            '<button class="btn btn-outline-danger btn-eliminar-criterio" data-cindex="' + i + '">X</button>' +
            '</div>';
        });

        bloqueEscala += '<button class="btn btn-sm btn-outline-primary btn-agregar-criterio mb-3">+ Agregar criterio</button>' +
          '<label class="form-label d-block">Opciones de escala compartida</label>';

        (pregunta.opciones_escala || []).forEach(function(opEscala, oi) {
          bloqueEscala += '<div class="input-group mb-2">' +
            '<input class="form-control input-opcion-escala" data-oeindex="' + oi + '" value="' + escapeHtml(opEscala) + '">' +
            '<button class="btn btn-outline-danger btn-eliminar-opcion-escala" data-oeindex="' + oi + '">X</button>' +
            '</div>';
        });

        bloqueEscala += '<button class="btn btn-sm btn-outline-primary btn-agregar-opcion-escala mb-3">+ Agregar opción de escala</button>' +
          '<div class="form-check"><input class="form-check-input input-permitir-otras" type="checkbox" id="otras_' + pregunta.id_local + '" ' + (pregunta.permitir_otras ? 'checked' : '') + '>' +
          '<label class="form-check-label" for="otras_' + pregunta.id_local + '">Permitir opción Otras con texto</label></div>' +
          '</div>';

        $bloqueOpciones.html(bloqueEscala);
      }

      $contenedor.append($card);
    });
  }

  function recolectarFormulario() {
    var items = [];

    $('#ContenedorPreguntas .encuesta-pregunta').each(function(i) {
      var $card = $(this);
      var tipo = $card.find('.input-tipo').val();
      var titulo = $card.find('.input-titulo').val();
      var descripcion = $card.find('.input-descripcion').val();
      var requerida = $card.find('.input-requerida').is(':checked');

      var item = {
        tipo: tipo,
        titulo: titulo,
        descripcion: descripcion,
        requerida: requerida
      };

      if (tipo === 'opcion_multiple') {
        item.opciones = [];
        $card.find('.input-opcion').each(function() {
          item.opciones.push($(this).val());
        });
      }

      if (tipo === 'escala_agrupada') {
        item.criterios = [];
        item.opciones_escala = [];
        $card.find('.input-criterio').each(function() {
          item.criterios.push($(this).val());
        });
        $card.find('.input-opcion-escala').each(function() {
          item.opciones_escala.push($(this).val());
        });
        item.permitir_otras = $card.find('.input-permitir-otras').is(':checked');
      }

      items.push(item);
    });

    return {
      encuesta_id: parseInt($('#EncuestaID').val(), 10) || 0,
      titulo: $('#EncuestaTitulo').val(),
      descripcion: $('#EncuestaDescripcion').val(),
      categoria: $('#EncuestaCategoria').val(),
      anonima: $('#EncuestaAnonima').is(':checked') ? 1 : 0,
      una_respuesta_por_usuario: $('#EncuestaUnaRespuesta').is(':checked') ? 1 : 0,
      permitir_multiples_respuestas: $('#EncuestaMultiplesRespuestas').is(':checked') ? 1 : 0,
      requiere_login: $('#EncuestaRequiereLogin').is(':checked') ? 1 : 0,
      fecha_inicio: $('#EncuestaFechaInicio').val(),
      fecha_fin: $('#EncuestaFechaFin').val(),
      mensaje_confirmacion: $('#EncuestaMensajeConfirmacion').val(),
      preguntas: items
    };
  }

  function cargarEncuesta(id) {
    $.ajax({
      type: 'POST',
      url: 'App/Server/ServerEncuestas.php',
      dataType: 'json',
      data: { action: 'get', encuesta_id: id },
      success: function(response) {
        if (!response.success || !response.item) {
          alert('No se pudo abrir la encuesta.');
          return;
        }

        var item = response.item;
        $('#EncuestaID').val(item.ENCUESTAID);
        $('#EncuestaTitulo').val(item.Titulo || '');
        $('#EncuestaDescripcion').val(item.Descripcion || '');
        $('#EncuestaCategoria').val(item.Categoria || '');
        $('#EncuestaAnonima').prop('checked', parseInt(item.Anonima, 10) === 1);
        $('#EncuestaUnaRespuesta').prop('checked', parseInt(item.UnaRespuestaPorUsuario, 10) === 1);
        $('#EncuestaMultiplesRespuestas').prop('checked', parseInt(item.PermitirMultiplesRespuestas, 10) === 1);
        $('#EncuestaRequiereLogin').prop('checked', parseInt(item.RequiereLogin, 10) === 1);
        $('#EncuestaFechaInicio').val(item.FechaInicio ? String(item.FechaInicio).substring(0, 10) : '');
        $('#EncuestaFechaFin').val(item.FechaFin ? String(item.FechaFin).substring(0, 10) : '');
        $('#EncuestaMensajeConfirmacion').val(item.MensajeConfirmacion || '');

        preguntas = (item.Preguntas || []).map(function(p) {
          return {
            id_local: 'q_' + Date.now() + '_' + Math.floor(Math.random() * 1000),
            tipo: p.Tipo,
            titulo: p.Titulo,
            descripcion: p.Descripcion,
            requerida: parseInt(p.Requerida, 10) === 1,
            opciones: (p.Opciones || []).map(function(o) { return o.Texto; }),
            criterios: normalizarCriteriosParaUI((p.Configuracion && p.Configuracion.criterios) ? p.Configuracion.criterios : []),
            opciones_escala: (p.Configuracion && p.Configuracion.opciones) ? p.Configuracion.opciones : [],
            permitir_otras: !!(p.Configuracion && p.Configuracion.permitir_otras)
          };
        });

        renderPreguntas();
        abrirEditor();
      }
    });
  }

  function renderVistaPrevia() {
    var data = recolectarFormulario();
    var html = '<div class="encuesta-preview-card"><h3>' + escapeHtml(data.titulo) + '</h3><p class="text-muted">' + escapeHtml(data.descripcion) + '</p></div>';

    (data.preguntas || []).forEach(function(pregunta, i) {
      html += '<div class="encuesta-preview-card"><strong>' + (i + 1) + '. ' + escapeHtml(pregunta.titulo) + (pregunta.requerida ? ' <span class="text-danger">*</span>' : '') + '</strong>';
      if (pregunta.descripcion) {
        html += '<p class="text-muted mb-2">' + escapeHtml(pregunta.descripcion) + '</p>';
      }

      if (pregunta.tipo === 'texto_corto') {
        html += '<input class="form-control" placeholder="Respuesta corta" disabled>';
      } else if (pregunta.tipo === 'texto_largo') {
        html += '<textarea class="form-control" rows="3" placeholder="Respuesta larga" disabled></textarea>';
      } else if (pregunta.tipo === 'opcion_multiple') {
        (pregunta.opciones || []).forEach(function(op) {
          html += '<div class="form-check"><input class="form-check-input" type="radio" disabled><label class="form-check-label">' + escapeHtml(op) + '</label></div>';
        });
      } else if (pregunta.tipo === 'escala_agrupada') {
        html += '<div class="table-responsive"><table class="table table-sm table-bordered encuesta-scale-table"><thead><tr><th>Criterio</th>';
        (pregunta.opciones_escala || []).forEach(function(opEscala) {
          html += '<th>' + escapeHtml(opEscala) + '</th>';
        });
        if (pregunta.permitir_otras) {
          html += '<th>Otras</th>';
        }
        html += '</tr></thead><tbody>';

        (normalizarCriteriosParaUI(pregunta.criterios || []) || []).forEach(function(criterio) {
          html += '<tr><td>' + escapeHtml(criterio) + '</td>';
          (pregunta.opciones_escala || []).forEach(function() {
            html += '<td><input type="radio" disabled></td>';
          });
          if (pregunta.permitir_otras) {
            html += '<td><div class="d-flex align-items-center gap-1"><input type="radio" disabled> <input class="form-control form-control-sm" disabled placeholder="Detalle Otras"></div></td>';
          }
          html += '</tr>';
        });

        html += '</tbody></table></div>';
      }

      html += '</div>';
    });

    $('#VistaPreviaContenido').html(html);
    $('#CardVistaPrevia').show();
    $('html, body').animate({ scrollTop: $('#CardVistaPrevia').offset().top - 60 }, 200);
  }

  $('#BtnBuscarEncuestas').on('click', cargarListado);
  $('#FiltroEstadoEncuesta').on('change', cargarListado);

  $('#BtnNuevaEncuesta').on('click', function() {
    limpiarFormulario();
    abrirEditor();
  });

  $('#BtnPlantillaEmpleadoMes').on('click', function() {
    $.ajax({
      type: 'POST',
      url: 'App/Server/ServerEncuestas.php',
      dataType: 'json',
      data: { action: 'empleado_mes_template' },
      success: function(response) {
        if (!response.success || !response.item) {
          alert('No se pudo cargar la plantilla.');
          return;
        }

        limpiarFormulario();
        var item = response.item;
        $('#EncuestaTitulo').val(item.titulo || '');
        $('#EncuestaDescripcion').val(item.descripcion || '');
        $('#EncuestaCategoria').val(item.categoria || '');
        $('#EncuestaAnonima').prop('checked', parseInt(item.anonima, 10) === 1);
        $('#EncuestaUnaRespuesta').prop('checked', parseInt(item.una_respuesta_por_usuario, 10) === 1);
        $('#EncuestaMultiplesRespuestas').prop('checked', parseInt(item.permitir_multiples_respuestas, 10) === 1);
        $('#EncuestaRequiereLogin').prop('checked', parseInt(item.requiere_login, 10) === 1);
        $('#EncuestaMensajeConfirmacion').val(item.mensaje_confirmacion || '');

        preguntas = (item.preguntas || []).map(function(pregunta) {
          return {
            id_local: 'q_' + Date.now() + '_' + Math.floor(Math.random() * 1000),
            tipo: pregunta.tipo,
            titulo: pregunta.titulo,
            descripcion: pregunta.descripcion || '',
            requerida: !!pregunta.requerida,
            opciones: pregunta.opciones || [],
            criterios: pregunta.criterios || [],
            opciones_escala: pregunta.opciones_escala || [],
            permitir_otras: !!pregunta.permitir_otras
          };
        });

        renderPreguntas();
        abrirEditor();
      }
    });
  });

  $('#BtnCerrarEditor').on('click', function() {
    $('#CardEditorEncuesta').hide();
  });

  $('#BtnAgregarPreguntaTextoCorto').on('click', function() { preguntas.push(crearPreguntaBase('texto_corto')); renderPreguntas(); });
  $('#BtnAgregarPreguntaTextoLargo').on('click', function() { preguntas.push(crearPreguntaBase('texto_largo')); renderPreguntas(); });
  $('#BtnAgregarPreguntaOpcion').on('click', function() { preguntas.push(crearPreguntaBase('opcion_multiple')); renderPreguntas(); });
  $('#BtnAgregarEscalaAgrupada').on('click', function() { preguntas.push(crearPreguntaBase('escala_agrupada')); renderPreguntas(); });

  $('#ContenedorPreguntas').on('click', '.btn-eliminar-pregunta', function() {
    var index = parseInt($(this).closest('.encuesta-pregunta').data('index'), 10);
    preguntas.splice(index, 1);
    renderPreguntas();
  });

  $('#ContenedorPreguntas').on('click', '.btn-duplicar-pregunta', function() {
    var index = parseInt($(this).closest('.encuesta-pregunta').data('index'), 10);
    var clon = $.extend(true, {}, preguntas[index]);
    clon.id_local = 'q_' + Date.now() + '_' + Math.floor(Math.random() * 1000);
    preguntas.splice(index + 1, 0, clon);
    renderPreguntas();
  });

  $('#ContenedorPreguntas').on('click', '.btn-subir', function() {
    var index = parseInt($(this).closest('.encuesta-pregunta').data('index'), 10);
    if (index <= 0) return;
    var temp = preguntas[index - 1];
    preguntas[index - 1] = preguntas[index];
    preguntas[index] = temp;
    renderPreguntas();
  });

  $('#ContenedorPreguntas').on('click', '.btn-bajar', function() {
    var index = parseInt($(this).closest('.encuesta-pregunta').data('index'), 10);
    if (index >= preguntas.length - 1) return;
    var temp = preguntas[index + 1];
    preguntas[index + 1] = preguntas[index];
    preguntas[index] = temp;
    renderPreguntas();
  });

  $('#ContenedorPreguntas').on('change keyup', '.input-tipo, .input-titulo, .input-descripcion, .input-requerida, .input-opcion, .input-criterio, .input-opcion-escala, .input-permitir-otras', function() {
    preguntas = recolectarFormulario().preguntas.map(function(pregunta, idx) {
      pregunta.id_local = (preguntas[idx] && preguntas[idx].id_local) ? preguntas[idx].id_local : ('q_' + Date.now() + '_' + idx);
      return pregunta;
    });
    renderPreguntas();
  });

  $('#ContenedorPreguntas').on('click', '.btn-agregar-opcion', function() {
    var index = parseInt($(this).closest('.encuesta-pregunta').data('index'), 10);
    preguntas[index].opciones.push('Nueva opción');
    renderPreguntas();
  });

  $('#ContenedorPreguntas').on('click', '.btn-eliminar-opcion', function() {
    var $card = $(this).closest('.encuesta-pregunta');
    var index = parseInt($card.data('index'), 10);
    var opIndex = parseInt($(this).data('opindex'), 10);
    preguntas[index].opciones.splice(opIndex, 1);
    renderPreguntas();
  });

  $('#ContenedorPreguntas').on('click', '.btn-agregar-criterio', function() {
    var index = parseInt($(this).closest('.encuesta-pregunta').data('index'), 10);
    preguntas[index].criterios.push('Nuevo criterio');
    renderPreguntas();
  });

  $('#ContenedorPreguntas').on('click', '.btn-eliminar-criterio', function() {
    var $card = $(this).closest('.encuesta-pregunta');
    var index = parseInt($card.data('index'), 10);
    var cindex = parseInt($(this).data('cindex'), 10);
    preguntas[index].criterios.splice(cindex, 1);
    renderPreguntas();
  });

  $('#ContenedorPreguntas').on('click', '.btn-agregar-opcion-escala', function() {
    var index = parseInt($(this).closest('.encuesta-pregunta').data('index'), 10);
    preguntas[index].opciones_escala.push('Nueva escala');
    renderPreguntas();
  });

  $('#ContenedorPreguntas').on('click', '.btn-eliminar-opcion-escala', function() {
    var $card = $(this).closest('.encuesta-pregunta');
    var index = parseInt($card.data('index'), 10);
    var oindex = parseInt($(this).data('oeindex'), 10);
    preguntas[index].opciones_escala.splice(oindex, 1);
    renderPreguntas();
  });

  $('#BtnGuardarBorrador').on('click', function() {
    var payload = recolectarFormulario();

    $.ajax({
      type: 'POST',
      url: 'App/Server/ServerEncuestas.php',
      dataType: 'json',
      data: {
        action: 'save',
        payload: JSON.stringify(payload)
      },
      success: function(response) {
        if (!response.success) {
          alert(response.message || 'No se pudo guardar.');
          return;
        }

        $('#EncuestaID').val(response.encuesta_id || payload.encuesta_id || 0);
        alert(response.message || 'Borrador guardado.');
        cargarListado();
      }
    });
  });

  $('#BtnVistaPrevia').on('click', renderVistaPrevia);
  $('#BtnCerrarVistaPrevia').on('click', function() { $('#CardVistaPrevia').hide(); });

  $('#TablaEncuestas').on('click', '.btn-editar', function() {
    cargarEncuesta($(this).data('id'));
  });

  $('#TablaEncuestas').on('click', '.btn-duplicar', function() {
    $.post('App/Server/ServerEncuestas.php', {
      action: 'duplicate',
      encuesta_id: $(this).data('id')
    }, function(response) {
      if (!response.success) {
        alert(response.message || 'No se pudo duplicar.');
        return;
      }
      cargarListado();
    }, 'json');
  });

  function actualizarEstado(encuestaId, estado) {
    $.post('App/Server/ServerEncuestas.php', {
      action: 'update_status',
      encuesta_id: encuestaId,
      estado: estado
    }, function(response) {
      if (!response.success) {
        alert(response.message || 'No se pudo actualizar estado.');
        return;
      }
      cargarListado();
    }, 'json');
  }

  $('#TablaEncuestas').on('click', '.btn-publicar', function() { actualizarEstado($(this).data('id'), 'publicada'); });
  $('#TablaEncuestas').on('click', '.btn-cerrar', function() { actualizarEstado($(this).data('id'), 'cerrada'); });
  $('#TablaEncuestas').on('click', '.btn-archivar', function() { actualizarEstado($(this).data('id'), 'archivada'); });

  cargarListado();
});
