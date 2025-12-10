$(document).ready(function() {
  var $modal = $('#ModalAgregarPendiente');
  var $container = $('#ProductosPendientesContainer');
  var templateHtml = $('#ProductoPendienteRowTemplate').html();
  var productosDisponibles = $container.data('productos-disponibles') === 1 || $container.data('productos-disponibles') === '1';
  var $selectClientes = $('.select-cliente');
  var $selectVendedores = $('.select-vendedor');
  var $selectAduana = $('#AduanaPendiente');
  var $inputSurtidor = $('#SurtidorPendiente');
  var $selectAlmacenista = $('#SurtidorPendienteAlmacenista');
  var $checkboxOtroSurtidor = $('#OtroSurtidorPendiente');
  var $checkboxAlmacenista = $('#AlmacenistaPendiente');
  var $vendedorPendienteOtroContainer = $('#VendedorPendienteOtroContainer');
  var $inputVendedorPendienteOtro = $('#VendedorPendienteOtro');
  var $aduanaPendienteOtroContainer = $('#AduanaPendienteOtroContainer');
  var $inputAduanaPendienteOtro = $('#AduanaPendienteOtro');
  var VENDEDOR_OTRO_ID = '22';
  var ADUANA_OTRO_ID = '4';

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
      width: '100%',
      minimumResultsForSearch: 0,
      minimumInputLength: 3
    });
  }

  function inicializarSelectCliente($elemento) {
    if (!$elemento.length) {
      return;
    }

    if ($elemento.hasClass('select2-hidden-accessible')) {
      return;
    }

    $elemento.select2({
      dropdownParent: $modal,
      placeholder: $elemento.data('placeholder') || 'Selecciona cliente',
      allowClear: true,
      width: '100%',
      minimumResultsForSearch: 0,
      minimumInputLength: 2
    });
  }

  function inicializarSelectVendedor($elemento) {
    if (!$elemento.length) {
      return;
    }

    if ($elemento.hasClass('select2-hidden-accessible')) {
      return;
    }

    $elemento.select2({
      dropdownParent: $modal,
      placeholder: $elemento.data('placeholder') || 'Selecciona vendedor',
      allowClear: true,
      width: '100%',
      minimumResultsForSearch: 0
    });
  }

  function inicializarSelectAduana($elemento) {
    if (!$elemento.length) {
      return;
    }

    if ($elemento.hasClass('select2-hidden-accessible')) {
      return;
    }

    $elemento.select2({
      dropdownParent: $modal,
      placeholder: $elemento.data('placeholder') || 'Selecciona aduana',
      allowClear: true,
      width: '100%',
      minimumResultsForSearch: 0
    });
  }

  function inicializarSelectAlmacenista($elemento) {
    if (!$elemento.length) {
      return;
    }

    if ($elemento.hasClass('select2-hidden-accessible')) {
      return;
    }

    $elemento.select2({
      dropdownParent: $modal,
      placeholder: $elemento.data('placeholder') || 'Selecciona almacenista',
      allowClear: true,
      width: '100%',
      minimumResultsForSearch: 0
    });
  }

  function obtenerContenedorSelectAlmacenista() {
    if (!$selectAlmacenista.length) {
      return $();
    }

    var instancia = $selectAlmacenista.data('select2');
    if (instancia && instancia.$container && instancia.$container.length) {
      return instancia.$container;
    }

    return $selectAlmacenista.next('.select2');
  }

  function mostrarContenedorAlmacenista(debeMostrar) {
    var $contenedor = obtenerContenedorSelectAlmacenista();

    if ($contenedor.length) {
      $contenedor.toggleClass('d-none', !debeMostrar);
    }
  }

  function esVendedorOtro() {
    var valorSeleccionado = ($selectVendedores.val() || '').toString();
    return valorSeleccionado === VENDEDOR_OTRO_ID;
  }

  function obtenerNombreVendedor() {
    if (esVendedorOtro() && $inputVendedorPendienteOtro.length) {
      return ($inputVendedorPendienteOtro.val() || '').trim();
    }

    var textoSeleccionado = $selectVendedores.find('option:selected').text() || '';
    return textoSeleccionado.trim();
  }

  function esAduanaOtro() {
    var valorSeleccionado = ($selectAduana.val() || '').toString();
    return valorSeleccionado === ADUANA_OTRO_ID;
  }

  function actualizarCampoAduanaOtro() {
    if (!$aduanaPendienteOtroContainer.length || !$inputAduanaPendienteOtro.length) {
      return;
    }

    var mostrarCampo = esAduanaOtro();
    $aduanaPendienteOtroContainer.toggleClass('d-none', !mostrarCampo);

    if (mostrarCampo) {
      $inputAduanaPendienteOtro.prop('required', true);
    } else {
      $inputAduanaPendienteOtro.prop('required', false).val('');
    }
  }

  function actualizarCampoSurtidor() {
    if (!$inputSurtidor.length) {
      return;
    }

    var usarAlmacenista = $checkboxAlmacenista.is(':checked');
    var permitirEdicion = $checkboxOtroSurtidor.is(':checked');
    var nombreVendedor = obtenerNombreVendedor();

    if (usarAlmacenista && $selectAlmacenista.length) {
      $checkboxOtroSurtidor.prop('checked', false).prop('disabled', true);
      $inputSurtidor.prop('required', false).prop('readonly', true).val('').addClass('d-none').attr('name', '');

      $selectAlmacenista.removeClass('d-none').prop('disabled', false).prop('required', true).attr('name', 'SurtidorPendiente');
      inicializarSelectAlmacenista($selectAlmacenista);
      mostrarContenedorAlmacenista(true);
      return;
    }

    if ($selectAlmacenista.length) {
      $selectAlmacenista.val(null).trigger('change');
      $selectAlmacenista.prop('required', false).attr('name', '').addClass('d-none');
      mostrarContenedorAlmacenista(false);
      $checkboxOtroSurtidor.prop('disabled', false);
    }

    $inputSurtidor.removeClass('d-none').attr('name', 'SurtidorPendiente').prop('required', true);

    if (permitirEdicion) {
      $inputSurtidor.prop('readonly', false);
      $inputSurtidor.val('');
      $inputSurtidor.trigger('focus');
    } else {
      $inputSurtidor.prop('readonly', true);
      $inputSurtidor.val(nombreVendedor);
    }
  }

  function actualizarCampoVendedorOtro() {
    if (!$vendedorPendienteOtroContainer.length || !$inputVendedorPendienteOtro.length) {
      return;
    }

    var mostrarCampo = esVendedorOtro();
    $vendedorPendienteOtroContainer.toggleClass('d-none', !mostrarCampo);

    if (mostrarCampo) {
      $inputVendedorPendienteOtro.prop('required', true);
    } else {
      $inputVendedorPendienteOtro.prop('required', false).val('');
    }
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
    $selectClientes.each(function() {
      inicializarSelectCliente($(this));
    });

    $selectVendedores.each(function() {
      inicializarSelectVendedor($(this));
    });

    inicializarSelectAduana($selectAduana);

    inicializarSelectAlmacenista($selectAlmacenista);

    if (productosDisponibles) {
      $container.find('.select2-producto').each(function() {
        if (!$(this).hasClass('select2-hidden-accessible')) {
          inicializarSelect2($(this));
        }
      });
      actualizarBotonesEliminar();
    }

    actualizarCampoVendedorOtro();
    actualizarCampoSurtidor();
    actualizarCampoAduanaOtro();
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

    $selectClientes.each(function() {
      var $cliente = $(this);
      if ($cliente.hasClass('select2-hidden-accessible')) {
        $cliente.val(null).trigger('change');
      }
    });

    $selectVendedores.each(function() {
      var $vendedor = $(this);
      if ($vendedor.hasClass('select2-hidden-accessible')) {
        $vendedor.val(null).trigger('change');
      }
    });

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

    if ($checkboxOtroSurtidor.length) {
      $checkboxOtroSurtidor.prop('checked', false).prop('disabled', false);
    }

    if ($inputSurtidor.length) {
      $inputSurtidor.prop('readonly', true).val('').removeClass('d-none').attr('name', 'SurtidorPendiente').prop('required', true);
    }

    if ($selectAlmacenista.length) {
      $selectAlmacenista.val(null).trigger('change');
      $selectAlmacenista.prop('required', false).attr('name', '').addClass('d-none');
      mostrarContenedorAlmacenista(false);
    }

    if ($checkboxAlmacenista.length) {
      $checkboxAlmacenista.prop('checked', false);
    }

    if ($vendedorPendienteOtroContainer.length) {
      $vendedorPendienteOtroContainer.addClass('d-none');
    }

    if ($inputVendedorPendienteOtro.length) {
      $inputVendedorPendienteOtro.prop('required', false).val('');
    }

    if ($selectAduana.length) {
      $selectAduana.val(null).trigger('change');
    }

    if ($aduanaPendienteOtroContainer.length) {
      $aduanaPendienteOtroContainer.addClass('d-none');
    }

    if ($inputAduanaPendienteOtro.length) {
      $inputAduanaPendienteOtro.prop('required', false).val('');
    }
  });

  $selectVendedores.on('change', function() {
    actualizarCampoVendedorOtro();
    actualizarCampoSurtidor();
  });

  $inputVendedorPendienteOtro.on('input', function() {
    if (esVendedorOtro()) {
      actualizarCampoSurtidor();
    }
  });

  $checkboxOtroSurtidor.on('change', function() {
    actualizarCampoSurtidor();
  });

  $checkboxAlmacenista.on('change', function() {
    actualizarCampoSurtidor();
  });

  $selectAduana.on('change', function() {
    actualizarCampoAduanaOtro();
  });
});
