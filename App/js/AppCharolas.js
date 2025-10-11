$(document).ready(function() {
  $('#CHAROLASID').select2({
    placeholder: 'Selecciona charola',
    allowClear: true,
    width: '100%'
  });

  var tablaOrdenes;

  function obtenerBadge(statusId, orderId) {
    var mandarModal = 'data-bs-toggle="modal" data-bs-target="#ModalCambioStatusCharola" data-order="' + orderId + '" data-status="' + statusId + '"';
    switch (statusId) {
      case '1':
        return '<span class="badge badge-info badge-status" ' + mandarModal + '>Registrada</span>';
      case '2':
        return '<span class="badge badge-warning badge-status" ' + mandarModal + '>En proceso</span>';
      case '3':
        return '<span class="badge badge-success badge-status" ' + mandarModal + '>Terminada</span>';
      case '4':
        return '<span class="badge badge-dark badge-status" ' + mandarModal + '>Entregada</span>';
      case '5':
        return '<span class="badge badge-danger badge-status" ' + mandarModal + '>Cancelada</span>';
      default:
        return '';
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

  function asegurarEncabezadoTabla() {
    var tabla = $('#TablaOrdenesCharolas');
    var thead = tabla.find('thead');
    var encabezado = '<tr>' +
      '<th class="dt-control dtr-control" scope="col"></th>' +
      '<th scope="col">Requisición</th>' +
      '<th scope="col">SKU</th>' +
      '<th scope="col">Descripción</th>' +
      '<th scope="col">Cantidad</th>' +
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
      if (celdas.length !== 6 || !celdas.first().hasClass('dtr-control')) {
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

          var filaSanitizada = {
            ORDENCHAROLAID: row.ORDENCHAROLAID,
            CHAROLASID: row.CHAROLASID,
            SkuCharolas: row.SkuCharolas,
            DescripcionCharolas: row.DescripcionCharolas,
            Cantidad: row.Cantidad,
            STATUSID: row.STATUSID,
            Status: row.Status,
            Detalles: detalles,
            _totalesMateriales: normalizarTotalesMateriales(totales)
          };

          datosTabla.push(filaSanitizada);
        });

        var responsiveDisplayControl = $.fn.dataTable &&
          $.fn.dataTable.Responsive &&
          $.fn.dataTable.Responsive.display &&
          $.fn.dataTable.Responsive.display.control;

        var responsiveDetails = {
          type: 'column',
          target: 'td.dt-control, td.dtr-control',
          renderer: function(api, rowIdx, columns) {
            var data = api.row(rowIdx).data();
            if (!data || !Array.isArray(data.Detalles) || !data.Detalles.length) {
              return '<div class="text-muted">Sin materiales registrados para esta requisición.</div>';
            }

            var totalLargueros = renderMaterial(data, 'Largueros');
            var totalTornilleria = renderMaterial(data, 'Tornilleria');
            var totalJuntaZeta = renderMaterial(data, 'Junta Zeta');
            var totalTraves = renderMaterial(data, 'Traves');

            var html = '<div class="detalle-requisicion">';

            html += '<div class="row g-3 detalle-requisicion__resumen">' +
              '<div class="col-sm-6 col-lg-3"><div class="text-muted text-uppercase small">Requisición</div><div class="fw-semibold">' + escapeHtml(data.ORDENCHAROLAID) + '</div></div>' +
              '<div class="col-sm-6 col-lg-3"><div class="text-muted text-uppercase small">SKU</div><div class="fw-semibold">' + escapeHtml(data.SkuCharolas) + '</div></div>' +
              '<div class="col-sm-12 col-lg-6"><div class="text-muted text-uppercase small">Descripción</div><div class="fw-semibold">' + escapeHtml(data.DescripcionCharolas) + '</div></div>' +
              '<div class="col-sm-6 col-lg-3"><div class="text-muted text-uppercase small">Cantidad</div><div class="fw-semibold">' + escapeHtml(data.Cantidad) + '</div></div>' +
              '<div class="col-sm-6 col-lg-3"><div class="text-muted text-uppercase small">Estatus</div><div class="fw-semibold">' + escapeHtml(obtenerNombreStatus(data.Status, data.STATUSID)) + '</div></div>' +
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
        };

        if (responsiveDisplayControl) {
          responsiveDetails.display = responsiveDisplayControl;
        }

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
              data: null,
              className: 'dt-control dtr-control text-center',
              orderable: false,
              defaultContent: '<span class="dt-control-icon" aria-hidden="true">+</span><span class="visually-hidden">Mostrar detalles</span>'
            },
            { data: 'ORDENCHAROLAID' },
            { data: 'SkuCharolas' },
            { data: 'DescripcionCharolas' },
            { data: 'Cantidad' },
            {
              data: null,
              render: function(data, type, row) {
                return obtenerBadge(row.STATUSID, row.ORDENCHAROLAID);
              }
            }
          ],
          columnDefs: [
            { targets: 0, width: '1%', className: 'dt-control dtr-control text-center', orderable: false },
            { targets: -1, orderable: false }
          ],
          responsive: {
            details: responsiveDetails
          },
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
        $('#TablaOrdenesCharolas').addClass('dtr-inline collapsed dt-responsive');
        actualizarIconosResponsive();

        tablaOrdenes.on('draw', function() {
          actualizarIconosResponsive();
        });

        tablaOrdenes.on('responsive-display', function() {
          actualizarIconosResponsive();
        });

        tablaOrdenes.on('responsive-resize', function() {
          actualizarIconosResponsive();
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
    var orderId = $(this).data('order');
    var statusId = $(this).data('status');
    $('#ORDENCHAROLAIDEditar').val(orderId);
    $('#NuevoStatusCharola').val(statusId);
  });

  $('#FormEditarStatusCharola').on('submit', function(e) {
    e.preventDefault();
    var orderId = $('#ORDENCHAROLAIDEditar').val();
    var statusId = $('#NuevoStatusCharola').val();

    $.ajax({
      type: 'POST',
      url: 'App/Server/ServerUpdateOrdenCharolas.php',
      data: { ORDENCHAROLAID: orderId, STATUSID: statusId },
      dataType: 'json',
      success: function() {
        $('#ModalCambioStatusCharola').modal('hide');
        cargarOrdenes();
      }
    });
  });
});
