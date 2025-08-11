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

  function cargarOrdenes() {
    $.ajax({
      url: 'App/Server/ServerInfoOrdenesCharolas.php',
      dataType: 'json',
      success: function(response) {
        if (tablaOrdenes) {
          tablaOrdenes.clear().destroy();
          $('#TablaOrdenesCharolas tbody').empty();
        }

        tablaOrdenes = $('#TablaOrdenesCharolas').DataTable({
          dom: 'Bfrtip',
          buttons: ['excelHtml5', 'pageLength'],
          data: response,
          pageLength: 100,
          order: [1, 'desc'],
          columns: [
            {
              className: 'dtr-control',
              orderable: false,
              data: null,
              defaultContent: ''
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
          responsive: {
            details: {
              type: 'column',
              target: 0,
              renderer: function(api, rowIdx, columns) {
                var data = api.row(rowIdx).data();
                if (!data.Detalles || !data.Detalles.length) {
                  return '';
                }
                var html = '<table class="table table-sm"><thead><tr>' +
                  '<th>SKU MP</th><th>Descripción</th><th>Tipo</th><th>Cantidad</th>' +
                  '</tr></thead><tbody>';
                $.each(data.Detalles, function(i, mp) {
                  html += '<tr>' +
                    '<td>' + mp.SkuMP + '</td>' +
                    '<td>' + mp.DescripcionMP + '</td>' +
                    '<td>' + mp.TipoMP + '</td>' +
                    '<td>' + mp.Cantidad + '</td>' +
                    '</tr>';
                });
                html += '</tbody></table>';
                return html;
              }
            }
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
      }
    });
  }

  cargarOrdenes();

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
