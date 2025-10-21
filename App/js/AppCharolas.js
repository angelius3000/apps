$(document).ready(function() {
  $('#CHAROLASID').select2({
    placeholder: 'Selecciona charola',
    allowClear: true,
    width: '100%'
  });

  var tablaOrdenes;

  var configuracionCharolas = window.charolasConfig || {};
  var statusVerificadoId = configuracionCharolas.statusVerificadoId !== null && configuracionCharolas.statusVerificadoId !== undefined
    ? String(configuracionCharolas.statusVerificadoId)
    : null;
  var nombreStatusVerificado = typeof configuracionCharolas.nombreStatusVerificado === 'string' && configuracionCharolas.nombreStatusVerificado.trim() !== ''
    ? configuracionCharolas.nombreStatusVerificado
    : 'Verificado';
  var puedeCambiarEstatus = !!configuracionCharolas.puedeCambiarEstatus;
  var puedeAsignarVerificado = !!configuracionCharolas.puedeAsignarVerificado;
  var mensajeRestriccionVerificado = typeof configuracionCharolas.mensajeRestriccionVerificado === 'string' && configuracionCharolas.mensajeRestriccionVerificado.trim() !== ''
    ? configuracionCharolas.mensajeRestriccionVerificado
    : 'Solo un administrador, supervisor o auditor puede asignar el estatus Verificado.';
  var nombreStatusVerificadoNormalizado = '';
  var statusAuditadoId = configuracionCharolas.statusAuditadoId !== null && configuracionCharolas.statusAuditadoId !== undefined
    ? String(configuracionCharolas.statusAuditadoId)
    : null;
  var nombreStatusAuditado = typeof configuracionCharolas.nombreStatusAuditado === 'string' && configuracionCharolas.nombreStatusAuditado.trim() !== ''
    ? configuracionCharolas.nombreStatusAuditado
    : 'Auditado';
  var puedeAsignarAuditado = !!configuracionCharolas.puedeAsignarAuditado;
  var mensajeRestriccionAuditado = typeof configuracionCharolas.mensajeRestriccionAuditado === 'string' && configuracionCharolas.mensajeRestriccionAuditado.trim() !== ''
    ? configuracionCharolas.mensajeRestriccionAuditado
    : 'Solo un auditor puede asignar el estatus Auditado.';
  var nombreStatusAuditadoNormalizado = '';
  var $camposAuditado = $('#CamposAuditado');
  var $salidaAuditado = $('#SalidaAuditado');
  var $entradaAuditado = $('#EntradaAuditado');
  var $almacenAuditado = $('#AlmacenAuditado');
  var camposAuditado = [$salidaAuditado, $entradaAuditado, $almacenAuditado];
  var $campoFacturaWrapper = $('#CampoFacturaCharola');
  var $facturaInput = $('#FacturaCharola');
  var statusEnProcesoId = '2';

  function obtenerNombreVerificadoNormalizado() {
    if (!nombreStatusVerificadoNormalizado) {
      nombreStatusVerificadoNormalizado = normalizarTexto(nombreStatusVerificado);
    }
    return nombreStatusVerificadoNormalizado;
  }

  function esStatusVerificado(statusId, statusTexto) {
    var statusIdTexto = statusId !== null && statusId !== undefined ? String(statusId) : '';

    if (statusVerificadoId && statusIdTexto === statusVerificadoId) {
      return true;
    }

    if (statusTexto === undefined || statusTexto === null) {
      return false;
    }

    var nombreNormalizado = obtenerNombreVerificadoNormalizado();
    if (nombreNormalizado === '') {
      return false;
    }

    return normalizarTexto(statusTexto) === nombreNormalizado;
  }

  function obtenerNombreAuditadoNormalizado() {
    if (!nombreStatusAuditadoNormalizado) {
      nombreStatusAuditadoNormalizado = normalizarTexto(nombreStatusAuditado);
    }
    return nombreStatusAuditadoNormalizado;
  }

  function esStatusAuditado(statusId, statusTexto) {
    var statusIdTexto = statusId !== null && statusId !== undefined ? String(statusId) : '';

    if (statusAuditadoId && statusIdTexto === statusAuditadoId) {
      return true;
    }

    if (statusTexto === undefined || statusTexto === null) {
      return false;
    }

    var nombreNormalizado = obtenerNombreAuditadoNormalizado();
    if (nombreNormalizado === '') {
      return false;
    }

    return normalizarTexto(statusTexto) === nombreNormalizado;
  }

  function obtenerBadge(statusId, orderId, statusTexto) {
    var statusIdTexto = statusId !== null && statusId !== undefined ? String(statusId) : '';
    var orderIdTexto = orderId !== null && orderId !== undefined ? String(orderId) : '';
    var claseInteraccion = puedeCambiarEstatus ? ' badge-status' : '';
    var mandarModal = '';

    if (puedeCambiarEstatus) {
      mandarModal = 'data-bs-toggle="modal" data-bs-target="#ModalCambioStatusCharola" data-order="' + escapeHtml(orderIdTexto) + '" data-status="' + escapeHtml(statusIdTexto) + '"';
    }

    if (esStatusVerificado(statusIdTexto, statusTexto)) {
      return '<span class="badge badge-primary' + claseInteraccion + '" ' + mandarModal + '>' + escapeHtml(nombreStatusVerificado) + '</span>';
    }

    if (esStatusAuditado(statusIdTexto, statusTexto)) {
      return '<span class="badge badge-info' + claseInteraccion + '" ' + mandarModal + '>' + escapeHtml(nombreStatusAuditado) + '</span>';
    }

    switch (statusIdTexto) {
      case '1':
        return '<span class="badge badge-info' + claseInteraccion + '" ' + mandarModal + '>Registrada</span>';
      case '2':
        return '<span class="badge badge-warning' + claseInteraccion + '" ' + mandarModal + '>En proceso</span>';
      case '3':
        return '<span class="badge badge-success' + claseInteraccion + '" ' + mandarModal + '>Terminada</span>';
      case '4':
        return '<span class="badge badge-dark' + claseInteraccion + '" ' + mandarModal + '>Entregada</span>';
      case '5':
        return '<span class="badge badge-danger' + claseInteraccion + '" ' + mandarModal + '>Cancelada</span>';
      default: {
        var nombreStatus = obtenerNombreStatus(statusTexto, statusIdTexto);
        if (nombreStatus) {
          return '<span class="badge badge-secondary' + claseInteraccion + '" ' + mandarModal + '>' + escapeHtml(nombreStatus) + '</span>';
        }
        return '';
      }
    }
  }

  function escapeHtml(texto) {
    if (texto === null || texto === undefined) {
      return '';
    }
    return texto
      .toString()
      .replace(/[&<>"']/g, function(caracter) {
        switch (caracter) {
          case '&':
            return '&amp;';
          case '<':
            return '&lt;';
          case '>':
            return '&gt;';
          case '"':
            return '&quot;';
          case '\'':
            return '&#39;';
          default:
            return caracter;
        }
      });
  }

  function normalizarTexto(texto) {
    if (texto === null || texto === undefined) {
      return '';
    }
    return texto
      .toString()
      .toLowerCase()
      .normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '');
  }

  function obtenerNombreStatus(statusTexto, statusId) {
    if (esStatusVerificado(statusId, statusTexto)) {
      return nombreStatusVerificado;
    }

    if (esStatusAuditado(statusId, statusTexto)) {
      return nombreStatusAuditado;
    }

    if (statusTexto && statusTexto.toString().trim() !== '') {
      return statusTexto;
    }

    switch (String(statusId || '')) {
      case '1':
        return 'Registrada';
      case '2':
        return 'En proceso';
      case '3':
        return 'Terminada';
      case '4':
        return 'Entregada';
      case '5':
        return 'Cancelada';
      default:
        return '';
    }
  }

  function calcularTotalesMateriales(detalles) {
    var totales = {
      Largueros: 0,
      Tornilleria: 0,
      JuntaZeta: 0,
      Traves: 0
    };

    if (!Array.isArray(detalles)) {
      return totales;
    }

    detalles.forEach(function(mp) {
      var tipo = normalizarTexto(mp.TipoMP);
      var cantidad = Math.round(parseFloat(mp.Cantidad) || 0);

      if (!cantidad) {
        return;
      }

      if (tipo.includes('larguero')) {
        totales.Largueros += cantidad;
      } else if (tipo.includes('tornill') || tipo.includes('tuer')) {
        totales.Tornilleria += cantidad;
      } else if (tipo.includes('junta') && (tipo.includes('z') || tipo.includes('eta'))) {
        totales.JuntaZeta += cantidad;
      } else if (tipo.includes('trave') || tipo.includes('trabe')) {
        totales.Traves += cantidad;
      }
    });

    return totales;
  }

  function normalizarTotalesMateriales(origen) {
    var totales = {
      Largueros: 0,
      Tornilleria: 0,
      JuntaZeta: 0,
      Traves: 0
    };

    if (!origen || typeof origen !== 'object') {
      return totales;
    }

    Object.keys(totales).forEach(function(clave) {
      var valor = origen[clave];
      totales[clave] = Math.round(parseFloat(valor) || 0);
    });

    return totales;
  }

  function obtenerTotalesFila(row) {
    if (!row || typeof row !== 'object') {
      return null;
    }

    if (row._totalesMateriales && typeof row._totalesMateriales === 'object') {
      return row._totalesMateriales;
    }

    if (row.TotalesMateriales && typeof row.TotalesMateriales === 'object') {
      row._totalesMateriales = normalizarTotalesMateriales(row.TotalesMateriales);
      return row._totalesMateriales;
    }

    if (Array.isArray(row.Detalles) && row.Detalles.length) {
      row._totalesMateriales = calcularTotalesMateriales(row.Detalles);
      return row._totalesMateriales;
    }

    return null;
  }

  function renderMaterial(row, tipo) {
    if (!row) {
      return '0';
    }

    var totales = obtenerTotalesFila(row);

    if (!totales) {
      return '0';
    }

    var claveNormalizada = normalizarTexto(tipo)
      .replace(/\s+/g, '')
      .replace(/\./g, '');

    var clave;
    switch (claveNormalizada) {
      case 'largueros':
        clave = 'Largueros';
        break;
      case 'tornilleria':
      case 'tornillo':
        clave = 'Tornilleria';
        break;
      case 'juntazeta':
      case 'junzeta':
        clave = 'JuntaZeta';
        break;
      case 'traves':
      case 'trabe':
        clave = 'Traves';
        break;
      default:
        clave = '';
    }

    if (!clave || !Object.prototype.hasOwnProperty.call(totales, clave)) {
      return '0';
    }

    return String(totales[clave] || 0);
  }

  if (typeof window !== 'undefined') {
    window.renderMaterial = renderMaterial;
  }

  function construirDetalleRequisicion(data) {
    if (!data || !Array.isArray(data.Detalles) || !data.Detalles.length) {
      return '<div class="text-muted">Sin materiales registrados para esta requisición.</div>';
    }

    var totalLargueros = renderMaterial(data, 'Largueros');
    var totalTornilleria = renderMaterial(data, 'Tornilleria');
    var totalJuntaZeta = renderMaterial(data, 'Junta Zeta');
    var totalTraves = renderMaterial(data, 'Traves');
    var salida = data.Salida ? data.Salida : '—';
    var entrada = data.Entrada ? data.Entrada : '—';
    var almacen = data.Almacen ? data.Almacen : '—';

    var html = '<div class="detalle-requisicion">';

    html += '<div class="row g-3 detalle-requisicion__resumen">' +
      '<div class="col-sm-6 col-lg-3"><div class="text-muted text-uppercase small">Requisición</div><div class="fw-semibold">' + escapeHtml(data.ORDENCHAROLAID) + '</div></div>' +
      '<div class="col-sm-6 col-lg-3"><div class="text-muted text-uppercase small">SKU</div><div class="fw-semibold">' + escapeHtml(data.SkuCharolas) + '</div></div>' +
      '<div class="col-sm-12 col-lg-6"><div class="text-muted text-uppercase small">Descripción</div><div class="fw-semibold">' + escapeHtml(data.DescripcionCharolas) + '</div></div>' +
      '<div class="col-sm-6 col-lg-3"><div class="text-muted text-uppercase small">Cantidad</div><div class="fw-semibold">' + escapeHtml(data.Cantidad) + '</div></div>' +
      '<div class="col-sm-6 col-lg-3"><div class="text-muted text-uppercase small">Estatus</div><div class="fw-semibold">' + escapeHtml(obtenerNombreStatus(data.Status, data.STATUSID)) + '</div></div>' +
      '<div class="col-sm-6 col-lg-3"><div class="text-muted text-uppercase small">Salida</div><div class="fw-semibold">' + escapeHtml(salida) + '</div></div>' +
      '<div class="col-sm-6 col-lg-3"><div class="text-muted text-uppercase small">Entrada</div><div class="fw-semibold">' + escapeHtml(entrada) + '</div></div>' +
      '<div class="col-sm-6 col-lg-3"><div class="text-muted text-uppercase small">Almacén</div><div class="fw-semibold">' + escapeHtml(almacen) + '</div></div>' +
    '</div>';

    html += '<div class="row g-3 detalle-requisicion__totales mt-2">' +
      '<div class="col-sm-6 col-lg-3"><div class="card h-100"><div class="card-body py-2"><div class="text-muted text-uppercase small">Largueros</div><div class="h5 mb-0">' + escapeHtml(totalLargueros) + '</div></div></div></div>' +
      '<div class="col-sm-6 col-lg-3"><div class="card h-100"><div class="card-body py-2"><div class="text-muted text-uppercase small">Tornillería</div><div class="h5 mb-0">' + escapeHtml(totalTornilleria) + '</div></div></div></div>' +
      '<div class="col-sm-6 col-lg-3"><div class="card h-100"><div class="card-body py-2"><div class="text-muted text-uppercase small">Junta zeta</div><div class="h5 mb-0">' + escapeHtml(totalJuntaZeta) + '</div></div></div></div>' +
      '<div class="col-sm-6 col-lg-3"><div class="card h-100"><div class="card-body py-2"><div class="text-muted text-uppercase small">Traves</div><div class="h5 mb-0">' + escapeHtml(totalTraves) + '</div></div></div></div>' +
    '</div>';

    html += '<div class="mt-4 detalle-requisicion__detalle-materiales">' +
      '<h6 class="fw-semibold mb-3">Detalle de materiales</h6>';
    html += '<div class="table-responsive"><table class="table table-hover table-striped align-middle detalle-requisicion__tabla"><thead><tr>' +
      '<th class="text-uppercase small text-muted">SKU MP</th><th class="text-uppercase small text-muted">Descripción</th><th class="text-uppercase small text-muted">Tipo</th><th class="text-uppercase small text-muted">Cantidad</th>' +
      '</tr></thead><tbody>';
    $.each(data.Detalles, function(i, mp) {
      html += '<tr>' +
        '<td>' + escapeHtml(mp.SkuMP) + '</td>' +
        '<td>' + escapeHtml(mp.DescripcionMP) + '</td>' +
        '<td>' + escapeHtml(mp.TipoMP) + '</td>' +
        '<td>' + escapeHtml(mp.Cantidad) + '</td>' +
        '</tr>';
    });
    html += '</tbody></table></div></div>';

    html += '</div>';

    return html;
  }

  function actualizarBotonesDetalle() {
    if (!tablaOrdenes) {
      return;
    }

    tablaOrdenes.rows().every(function() {
      var row = this;
      var $fila = $(row.node());
      var $boton = $fila.find('button.toggle-detalle');
      if (!$boton.length) {
        return;
      }

      var $badge = $boton.find('.toggle-detalle-badge');
      if (row.child.isShown()) {
        $fila.addClass('detalle-abierto');
        $boton.attr('aria-expanded', 'true');
        $badge.text('−').addClass('is-open');
      } else {
        $fila.removeClass('detalle-abierto');
        $boton.attr('aria-expanded', 'false');
        $badge.text('+').removeClass('is-open');
      }
    });
  }

  function asegurarEncabezadoTabla() {
    var tabla = $('#TablaOrdenesCharolas');
    var thead = tabla.find('thead');
    var encabezado = '<tr>' +
      '<th scope="col" class="text-center detalle-control"><span class="visually-hidden">Detalle</span></th>' +
      '<th scope="col">Requisición</th>' +
      '<th scope="col">SKU</th>' +
      '<th scope="col">Descripción</th>' +
      '<th scope="col">Cantidad</th>' +
      '<th scope="col">Salida</th>' +
      '<th scope="col">Entrada</th>' +
      '<th scope="col">Almacén</th>' +
      '<th scope="col">Factura</th>' +
      '<th scope="col">Cambiar estatus</th>' +
    '</tr>';

    if (!thead.length) {
      thead = $('<thead />').appendTo(tabla);
    }

    var fila = thead.find('tr');
    if (!fila.length) {
      thead.append(encabezado);
    } else {
      var celdas = fila.first().children('th');
      if (celdas.length !== 10) {
        thead.html(encabezado);
      }
    }
  }

  function mostrarErrorOrdenes(mensaje) {
    asegurarEncabezadoTabla();
    var columnas = $('#TablaOrdenesCharolas thead tr th').length || 1;
    var contenido = '<tr><td colspan="' + columnas + '" class="text-center text-danger">' + mensaje + '</td></tr>';
    if (tablaOrdenes) {
      tablaOrdenes.clear().destroy();
      tablaOrdenes = null;
    }
    $('#TablaOrdenesCharolas tbody').off('click', '.toggle-detalle');
    $('#TablaOrdenesCharolas tbody').html(contenido);
  }

  function cargarOrdenes() {
    $.ajax({
      url: 'App/Server/ServerInfoOrdenesCharolas.php',
      dataType: 'json',
      success: function(response) {
        if (!Array.isArray(response)) {
          var mensaje = response && response.error ? response.error : 'No se pudo cargar la información de requisiciones.';
          mostrarErrorOrdenes(mensaje);
          return;
        }

        if (tablaOrdenes) {
          tablaOrdenes.clear().destroy();
          $('#TablaOrdenesCharolas tbody').empty();
          tablaOrdenes = null;
        }

        var datosTabla = [];

        response.forEach(function(row) {
          if (!row || typeof row !== 'object') {
            return;
          }

          var detalles = Array.isArray(row.Detalles) ? row.Detalles : [];

          var totales = null;
          if (row._totalesMateriales && typeof row._totalesMateriales === 'object') {
            totales = normalizarTotalesMateriales(row._totalesMateriales);
          } else if (row.TotalesMateriales && typeof row.TotalesMateriales === 'object') {
            totales = normalizarTotalesMateriales(row.TotalesMateriales);
          }

          if (!totales) {
            totales = detalles.length ? calcularTotalesMateriales(detalles) : normalizarTotalesMateriales({});
          }

          var datosDetalle = {
            ORDENCHAROLAID: row.ORDENCHAROLAID,
            CHAROLASID: row.CHAROLASID,
            SkuCharolas: row.SkuCharolas,
            DescripcionCharolas: row.DescripcionCharolas,
            Cantidad: row.Cantidad,
            STATUSID: row.STATUSID,
            Status: row.Status,
            Detalles: detalles,
            Salida: typeof row.Salida === 'string' ? row.Salida : '',
            Entrada: typeof row.Entrada === 'string' ? row.Entrada : '',
            Almacen: typeof row.Almacen === 'string' ? row.Almacen : '',
            Factura: typeof row.Factura === 'string' ? row.Factura : '',
            _totalesMateriales: normalizarTotalesMateriales(totales)
          };

          datosTabla.push({
            datos: datosDetalle,
            ORDENCHAROLAID: row.ORDENCHAROLAID,
            SkuCharolas: row.SkuCharolas,
            DescripcionCharolas: row.DescripcionCharolas,
            Cantidad: row.Cantidad,
            STATUSID: row.STATUSID,
            Salida: datosDetalle.Salida,
            Entrada: datosDetalle.Entrada,
            Almacen: datosDetalle.Almacen,
            Factura: datosDetalle.Factura,
            badgeHtml: obtenerBadge(row.STATUSID, row.ORDENCHAROLAID, row.Status)
          });
        });

        asegurarEncabezadoTabla();

        tablaOrdenes = $('#TablaOrdenesCharolas').DataTable({
          dom: 'Bfrtip',
          buttons: ['excelHtml5', 'pageLength'],
          data: datosTabla,
          pageLength: 100,
          order: [1, 'desc'],
          autoWidth: false,
          columns: [
            {
              data: 'datos',
              className: 'text-center detalle-control align-middle',
              orderable: false,
              render: function() {
                return '<button type="button" class="btn btn-link p-0 toggle-detalle" aria-expanded="false"><span class="toggle-detalle-badge" aria-hidden="true">+</span><span class="visually-hidden">Mostrar detalles</span></button>';
              }
            },
            { data: 'ORDENCHAROLAID' },
            { data: 'SkuCharolas' },
            { data: 'DescripcionCharolas' },
            { data: 'Cantidad' },
            {
              data: 'Salida',
              render: function(data) {
                return escapeHtml(data || '');
              }
            },
            {
              data: 'Entrada',
              render: function(data) {
                return escapeHtml(data || '');
              }
            },
            {
              data: 'Almacen',
              render: function(data) {
                return escapeHtml(data || '');
              }
            },
            {
              data: 'Factura',
              render: function(data) {
                return escapeHtml(data || '');
              }
            },
            {
              data: 'badgeHtml',
              orderable: false,
              searchable: false
            }
          ],
          columnDefs: [
            { targets: 0, width: '1%', orderable: false, searchable: false },
            { targets: -1, orderable: false, searchable: false }
          ],
          language: {
            search: 'Búsqueda:',
            lengthMenu: 'Mostrar _MENU_ filas',
            zeroRecords: 'Sin información',
            info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
            paginate: {
              first: 'Primera',
              last: 'Última',
              next: 'Siguiente',
              previous: 'Anterior',
            },
            infoEmpty: 'Sin requisiciones registradas',
            infoFiltered: '(filtrado de _MAX_ registros)',
          },
        });
        actualizarBotonesDetalle();

        $('#TablaOrdenesCharolas tbody')
          .off('click', '.toggle-detalle')
          .on('click', '.toggle-detalle', function(e) {
            e.preventDefault();
            if (!tablaOrdenes) {
              return;
            }

            var $boton = $(this);
            var tr = $boton.closest('tr');
            var row = tablaOrdenes.row(tr);

            if (row.child.isShown()) {
              row.child.hide();
              tr.removeClass('detalle-abierto');
            } else {
              var dataFila = row.data();
              var detalle = dataFila && dataFila.datos ? dataFila.datos : null;
              var detalleHtml = construirDetalleRequisicion(detalle);
              row.child(detalleHtml, 'detalle-requisicion__child').show();
              tr.addClass('detalle-abierto');
            }

            actualizarBotonesDetalle();
          });

        tablaOrdenes.on('draw', function() {
          actualizarBotonesDetalle();
        });
      },
      error: function(xhr) {
        var mensaje = 'No se pudo cargar la información de requisiciones.';
        if (xhr.responseJSON && xhr.responseJSON.error) {
          mensaje = xhr.responseJSON.error;
        }
        mostrarErrorOrdenes(mensaje);
      }
    });
  }

  cargarOrdenes();

  function actualizarIconosResponsive() {
    if (!tablaOrdenes) {
      return;
    }

    $('#TablaOrdenesCharolas tbody tr').each(function() {
      var $fila = $(this);
      var $icono = $fila.find('td.dt-control .dt-control-icon, td.dtr-control .dt-control-icon');
      if (!$icono.length) {
        return;
      }

      if ($fila.hasClass('parent')) {
        $icono.text('−');
        $icono.addClass('is-open');
      } else {
        $icono.text('+');
        $icono.removeClass('is-open');
      }
    });
  }

  $('#CalcularBtn').on('click', function() {
    var charolaId = $('#CHAROLASID').val();
    var cantidad = $('#CantidadCharolas').val();

    if (charolaId && cantidad && cantidad > 0) {
      $.ajax({
        type: 'POST',
        url: 'App/Server/ServerInfoCharolas.php',
        data: { CHAROLASID: charolaId, CANTIDAD: cantidad },
        dataType: 'json',
        success: function(response) {
          var tbody = $('#TablaMateriaPrima tbody');
          tbody.empty();

          $.each(response, function(index, item) {
            var fila = '<tr>' +
              '<td>' + item.SkuMP + '</td>' +
              '<td>' + item.DescripcionMP + '</td>' +
              '<td>' + item.TipoMP + '</td>' +
              '<td>' + item.Cantidad + '</td>' +
              '</tr>';
            tbody.append(fila);
          });
        }
      });
    }
  });

  $('#GenerarRequisicionBtn').on('click', function() {
    var charolaId = $('#CHAROLASID').val();
    var cantidad = $('#CantidadCharolas').val();

    if (charolaId && cantidad && cantidad > 0) {
      $.ajax({
        type: 'POST',
        url: 'App/Server/ServerInsertarOrdenCharolas.php',
        data: { CHAROLASID: charolaId, Cantidad: cantidad },
        dataType: 'json',
        success: function() {
          $('#CantidadCharolas').val('');
          cargarOrdenes();
        }
      });
    }
  });

  $('#TablaOrdenesCharolas').on('click', '.badge-status', function() {
    if (!puedeCambiarEstatus) {
      return;
    }
    var orderId = $(this).data('order');
    var statusId = $(this).data('status');
    var statusIdTexto = statusId !== undefined && statusId !== null ? String(statusId) : '';
    var datosFila = null;
    if (tablaOrdenes) {
      var filaTabla = tablaOrdenes.row($(this).closest('tr'));
      if (filaTabla && typeof filaTabla.data === 'function') {
        var data = filaTabla.data();
        if (data && typeof data === 'object') {
          datosFila = data.datos && typeof data.datos === 'object' ? data.datos : data;
        }
      }
    }
    if (!datosFila || typeof datosFila !== 'object') {
      datosFila = {};
    }
    $('#ORDENCHAROLAIDEditar').val(orderId);
    $salidaAuditado.val(datosFila.Salida || '');
    $entradaAuditado.val(datosFila.Entrada || '');
    $almacenAuditado.val(datosFila.Almacen || '');
    $facturaInput.val(datosFila.Factura || '');
    $('#NuevoStatusCharola').val(statusIdTexto).trigger('change');
    $('#NuevoStatusCharola').data('valor-inicial', statusIdTexto);
  });

  function actualizarVisibilidadCamposAuditado(valorSeleccionado) {
    var requiereCampos = !!statusAuditadoId && valorSeleccionado === statusAuditadoId;
    if (requiereCampos) {
      $camposAuditado.removeClass('d-none');
      camposAuditado.forEach(function($input) {
        $input.prop('required', true);
      });
    } else {
      $camposAuditado.addClass('d-none');
      camposAuditado.forEach(function($input) {
        $input.prop('required', false);
      });
    }
  }

  function actualizarVisibilidadCampoFactura(valorSeleccionado) {
    var requiereFactura = valorSeleccionado === statusEnProcesoId;
    if (requiereFactura) {
      $campoFacturaWrapper.removeClass('d-none');
      $facturaInput.prop('required', true);
    } else {
      $facturaInput.prop('required', false);
      $campoFacturaWrapper.addClass('d-none');
    }
  }

  $('#NuevoStatusCharola').on('change', function() {
    var valorSeleccionado = $(this).val();
    var valorTexto = valorSeleccionado !== undefined && valorSeleccionado !== null ? String(valorSeleccionado) : '';
    actualizarVisibilidadCamposAuditado(valorTexto);
    actualizarVisibilidadCampoFactura(valorTexto);
  });

  $('#ModalCambioStatusCharola').on('hidden.bs.modal', function() {
    $('#FormEditarStatusCharola')[0].reset();
    $('#NuevoStatusCharola').data('valor-inicial', '');
    actualizarVisibilidadCamposAuditado($('#NuevoStatusCharola').val() || '');
    actualizarVisibilidadCampoFactura($('#NuevoStatusCharola').val() || '');
    $facturaInput.val('');
  });

  actualizarVisibilidadCamposAuditado($('#NuevoStatusCharola').val() || '');
  actualizarVisibilidadCampoFactura($('#NuevoStatusCharola').val() || '');

  $('#FormEditarStatusCharola').on('submit', function(e) {
    e.preventDefault();
    if (!puedeCambiarEstatus) {
      $('#ModalCambioStatusCharola').modal('hide');
      return;
    }
    var orderId = $('#ORDENCHAROLAIDEditar').val();
    var statusId = $('#NuevoStatusCharola').val();
    var valorInicial = $('#NuevoStatusCharola').data('valor-inicial');

    var statusIdTexto = statusId !== undefined && statusId !== null ? String(statusId) : '';

    if (valorInicial !== undefined && statusIdTexto === String(valorInicial)) {
      $('#ModalCambioStatusCharola').modal('hide');
      return;
    }

    if (!puedeAsignarVerificado && statusVerificadoId && statusIdTexto === statusVerificadoId) {
      if (valorInicial !== undefined) {
        $('#NuevoStatusCharola').val(String(valorInicial)).trigger('change');
      } else {
        $('#NuevoStatusCharola').val('').trigger('change');
      }
      window.alert(mensajeRestriccionVerificado);
      return;
    }

    if (!puedeAsignarAuditado && statusAuditadoId && statusIdTexto === statusAuditadoId) {
      if (valorInicial !== undefined) {
        $('#NuevoStatusCharola').val(String(valorInicial)).trigger('change');
      } else {
        $('#NuevoStatusCharola').val('').trigger('change');
      }
      window.alert(mensajeRestriccionAuditado);
      return;
    }

    var requiereCamposAuditado = !!statusAuditadoId && statusIdTexto === statusAuditadoId;
    var datosEnvio = {
      ORDENCHAROLAID: orderId,
      STATUSID: statusIdTexto
    };

    if (requiereCamposAuditado) {
      var salida = ($salidaAuditado.val() || '').trim();
      var entrada = ($entradaAuditado.val() || '').trim();
      var almacen = ($almacenAuditado.val() || '').trim();

      if (!salida || !entrada || !almacen) {
        window.alert('Debes capturar Salida, Entrada y Almacén para guardar el estatus Auditado.');
        return;
      }

      datosEnvio.SALIDA = salida;
      datosEnvio.ENTRADA = entrada;
      datosEnvio.ALMACEN = almacen;
    }

    var factura = ($facturaInput.val() || '').trim();
    if (statusIdTexto === statusEnProcesoId && !factura) {
      window.alert('Debes capturar el número de factura para guardar el estatus En proceso.');
      return;
    }

    if (factura.length > 100) {
      factura = factura.slice(0, 100);
    }

    datosEnvio.FACTURA = factura;

    $.ajax({
      type: 'POST',
      url: 'App/Server/ServerUpdateOrdenCharolas.php',
      data: datosEnvio,
      dataType: 'json',
      success: function(response) {
        if (response && response.error) {
          window.alert(response.error);
          return;
        }
        $('#ModalCambioStatusCharola').modal('hide');
        cargarOrdenes();
      },
      error: function(xhr) {
        var mensaje = 'No se pudo actualizar el estatus.';
        if (xhr && xhr.responseJSON && xhr.responseJSON.error) {
          mensaje = xhr.responseJSON.error;
        }
        window.alert(mensaje);
      }
    });
  });
});
