$(document).ready(function() {
  var $modal = $('#ModalAgregarPendiente');
  var $container = $('#ProductosPendientesContainer');
  var templateHtml = $('#ProductoPendienteRowTemplate').html();
  var productosDisponibles = $container.data('productos-disponibles') === 1 || $container.data('productos-disponibles') === '1';
  var $selectCliente = $('#RazonSocialPendiente');

  function obtenerIndiceMaximo() {
    var indiceMaximo = -1;
    $container.find('.producto-pendiente-item').each(function() {
      var indice = parseInt($(this).attr('data-index'), 10);
      if (!isNaN(indice) && indice > indiceMaximo) {
        indiceMaximo = indice;
      }
    });
    return indiceMaximo;
  }

  var indiceActual = obtenerIndiceMaximo();

  function inicializarSelect2($elemento) {
    if (!$elemento.length) {
      return;
    }

    $elemento.select2({
      dropdownParent: $modal,
      placeholder: $elemento.data('placeholder') || 'Selecciona producto',
      allowClear: true,
      width: '100%'
    });
  }

  function inicializarSelectCliente() {
    if (!$selectCliente.length) {
      return;
    }

    if ($selectCliente.hasClass('select2-hidden-accessible')) {
      return;
    }

    $selectCliente.select2({
      dropdownParent: $modal,
      placeholder: $selectCliente.data('placeholder') || 'Selecciona cliente',
      allowClear: true,
      width: '100%'
    });
  }

  function actualizarBotonesEliminar() {
    var total = $container.find('.producto-pendiente-item').length;
    var debeMostrar = total > 1;

    $container.find('.eliminar-producto-pendiente').each(function() {
      $(this).toggleClass('d-none', !debeMostrar);
    });
  }

  function agregarNuevaPartida() {
    if (!templateHtml) {
      return;
    }

    indiceActual += 1;
    var nuevoHtml = templateHtml.replace(/__INDEX__/g, indiceActual);
    var $nuevoElemento = $(nuevoHtml);

    $container.append($nuevoElemento);
    inicializarSelect2($nuevoElemento.find('.select2-producto'));
    actualizarBotonesEliminar();
  }

  $modal.on('shown.bs.modal', function() {
    inicializarSelectCliente();

    if (productosDisponibles) {
      $container.find('.select2-producto').each(function() {
        if (!$(this).hasClass('select2-hidden-accessible')) {
          inicializarSelect2($(this));
        }
      });
      actualizarBotonesEliminar();
    }
  });

  $('#AgregarPartidaPendiente').on('click', function() {
    if (!productosDisponibles) {
      return;
    }
    agregarNuevaPartida();
  });

  $container.on('click', '.eliminar-producto-pendiente', function() {
    if (!productosDisponibles) {
      return;
    }

    var $fila = $(this).closest('.producto-pendiente-item');
    if ($container.find('.producto-pendiente-item').length > 1) {
      if ($fila.length) {
        if ($fila.find('.select2-producto').length) {
          $fila.find('.select2-producto').val(null).trigger('change');
        }
        $fila.remove();
        actualizarBotonesEliminar();
      }
    }
  });

  $modal.on('hidden.bs.modal', function() {
    var $formulario = $('#FormularioAgregarPendiente');
    if ($formulario.length) {
      $formulario[0].reset();
    }

    if ($selectCliente.length && $selectCliente.hasClass('select2-hidden-accessible')) {
      $selectCliente.val(null).trigger('change');
    }

    if (productosDisponibles) {
      $container.find('.producto-pendiente-item').each(function(index) {
        if (index === 0) {
          $(this).attr('data-index', '0');
          var $select = $(this).find('.select2-producto');
          if ($select.length) {
            $select.val(null).trigger('change');
          }
          var $inputCantidad = $(this).find('input[type="number"]');
          if ($inputCantidad.length) {
            $inputCantidad.val('');
          }
        } else {
          var $select = $(this).find('.select2-producto');
          if ($select.length) {
            $select.val(null).trigger('change');
          }
          $(this).remove();
        }
      });

      indiceActual = obtenerIndiceMaximo();
      actualizarBotonesEliminar();
    }
  });
});
