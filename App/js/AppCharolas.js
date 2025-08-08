var tablaOrdenes;

function cargarOrdenes() {
  $.ajax({
    url: 'App/Server/ServerInfoOrdenesCharolas.php',
    dataType: 'json',
    success: function(response) {
      tablaOrdenes.clear();
      $.each(response, function(index, item) {
        tablaOrdenes.row.add([
          item.SkuCharolas,
          item.DescripcionCharolas,
          item.Cantidad,
          badgeHtml(item.STATUSID, item.ORDENCHAROLAID)
        ]);
      });
      tablaOrdenes.draw();
    }
  });
}

function badgeHtml(statusId, orderId) {
  var badgeClass = '';
  var text = '';
  if (statusId == 1) {
    badgeClass = 'badge-info';
    text = 'Registrada';
  } else if (statusId == 2) {
    badgeClass = 'badge-warning';
    text = 'En proceso';
  } else if (statusId == 3) {
    badgeClass = 'badge-success';
    text = 'Terminada';
  } else if (statusId == 4) {
    badgeClass = 'badge-dark';
    text = 'Entregada';
  }
  var MandarModal = 'data-bs-toggle="modal" data-bs-target="#ModalCambioStatusCharola" onclick="TomarDatosParaModalCharolas(' + orderId + ',' + statusId + ')"';
  return '<span class="badge ' + badgeClass + '" ' + MandarModal + '>' + text + '</span>';
}

$(document).ready(function() {
  $('#CHAROLASID').select2({
    placeholder: 'Selecciona charola',
    allowClear: true,
    width: '100%'
  });

  tablaOrdenes = $('#TablaOrdenesCharolas').DataTable({
    responsive: true,
    language: {
      search: 'Búsqueda:',
      lengthMenu: 'Mostrar _MENU_ filas',
      zeroRecords: 'Sin información',
      info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
      paginate: {
        first: 'Primera',
        last: 'Última',
        next: 'Siguiente',
        previous: 'Anterior'
      },
      infoEmpty: 'Sin requisiciones registradas',
      infoFiltered: '(filtrado de _MAX_ registros)'
    }
  });

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
});

window.TomarDatosParaModalCharolas = function(orderId, statusId) {
  $('#ORDENCHAROLAIDEditar').val(orderId);
  $('#STATUSIDCharola').val(statusId);
};

$('#FormEditarStatusCharola').on('submit', function(e) {
  e.preventDefault();
  var orderId = $('#ORDENCHAROLAIDEditar').val();
  var statusId = $('#STATUSIDCharola').val();

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
