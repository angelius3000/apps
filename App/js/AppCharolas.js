$(document).ready(function() {
  $('#CHAROLASID').select2({
    placeholder: 'Selecciona charola',
    allowClear: true,
    width: '100%'
  });

  function cargarOrdenes() {
    $.ajax({
      url: 'App/Server/ServerInfoOrdenesCharolas.php',
      dataType: 'json',
      success: function(response) {
        var tbody = $('#TablaOrdenesCharolas tbody');
        tbody.empty();

        $.each(response, function(index, item) {
          var opciones = '';
          $.each(statusOptions, function(i, status) {
            var seleccionado = status.STATUSID == item.STATUSID ? 'selected' : '';
            opciones += '<option value="' + status.STATUSID + '" ' + seleccionado + '>' + status.Status + '</option>';
          });

          var fila = '<tr>' +
            '<td>' + item.SkuCharolas + '</td>' +
            '<td>' + item.DescripcionCharolas + '</td>' +
            '<td>' + item.Cantidad + '</td>' +
            '<td><select class="form-select status-select" data-order="' + item.ORDENCHAROLAID + '">' + opciones + '</select></td>' +
            '</tr>';
          tbody.append(fila);
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

  $('#TablaOrdenesCharolas').on('change', '.status-select', function() {
    var orderId = $(this).data('order');
    var statusId = $(this).val();

    $.ajax({
      type: 'POST',
      url: 'App/Server/ServerUpdateOrdenCharolas.php',
      data: { ORDENCHAROLAID: orderId, STATUSID: statusId },
      dataType: 'json',
      success: function() {
        cargarOrdenes();
      }
    });
  });
});
