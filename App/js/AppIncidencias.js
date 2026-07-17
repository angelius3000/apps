(function ($) {
  'use strict';
  var $form = $('#FormularioIncidencia');
  var $cantidad = $('#IncidenciaCantidad');
  var $precio = $('#IncidenciaPrecio');
  var $total = $('#IncidenciaTotal');
  var $error = $('#IncidenciaError');
  var $modal = $('#ModalIncidencia');
  var $producto = $('#IncidenciaProducto');
  var $selectores = $('#IncidenciaVendedor, #IncidenciaAduana');
  var $productoSolicitadoSku = $('#IncidenciaProductoSolicitadoSku');

  function inicializarSelector($selector) {
    if (!$selector.length || typeof $.fn.select2 !== 'function' || $selector.hasClass('select2-hidden-accessible')) {
      return;
    }

    $selector.select2({
      dropdownParent: $modal,
      placeholder: $selector.data('placeholder') || 'Selecciona una opción',
      allowClear: true,
      width: '100%',
      minimumResultsForSearch: 0
    });
  }

  function inicializarProducto() {
    if (!$producto.length || typeof $.fn.select2 !== 'function' || $producto.hasClass('select2-hidden-accessible')) {
      return;
    }

    $producto.select2({
      dropdownParent: $modal,
      placeholder: $producto.data('placeholder') || 'Selecciona producto',
      allowClear: true,
      width: '100%',
      minimumResultsForSearch: 0,
      minimumInputLength: 3,
      language: {
        inputTooShort: function (args) {
          var faltantes = args.minimum - (args.input ? args.input.length : 0);
          return 'Ingresa ' + faltantes + ' caracter' + (faltantes === 1 ? '' : 'es') + ' o más para buscar';
        }
      },
      ajax: {
        url: $producto.data('search-url'),
        dataType: 'json',
        delay: 250,
        data: function (params) { return { term: params.term || '', page: params.page || 1 }; },
        processResults: function (data, params) {
          var resultados = data && Array.isArray(data.results) ? data.results : [];
          var termino = (params.term || '').trim();
          if (resultados.length === 0 && termino !== '') {
            resultados.unshift({ id: 'solicitar:' + termino, text: 'Solicitar', sku: termino, solicitado: true });
          }
          return { results: resultados, pagination: { more: !!(data && data.pagination && data.pagination.more) } };
        },
        cache: true
      }
    });
  }

  $selectores.each(function () { inicializarSelector($(this)); });
  inicializarProducto();

  function actualizarTotal() {
    var cantidad = parseFloat($cantidad.val()) || 0;
    var precio = parseFloat($precio.val()) || 0;
    $total.val('$' + (cantidad * precio).toFixed(2));
  }
  $cantidad.add($precio).on('input', actualizarTotal);

  $producto.on('select2:select change', function () {
    var datos = $producto.select2('data');
    var seleccionado = datos && datos.length ? datos[0] : null;
    var sku = seleccionado && seleccionado.solicitado ? (seleccionado.sku || '').trim() : '';
    $productoSolicitadoSku.val(sku);
  });

  $form.on('submit', function (evento) {
    evento.preventDefault();
    $error.addClass('d-none').text('');
    var $boton = $form.find('[type="submit"]').prop('disabled', true);
    $.ajax({ url: 'App/Server/ServerInsertarIncidencia.php', method: 'POST', dataType: 'json', data: $form.serialize() })
      .done(function (respuesta) {
        if (!respuesta || !respuesta.ok) { $error.removeClass('d-none').text((respuesta && respuesta.error) || 'No se pudo guardar la incidencia.'); return; }
        window.location.reload();
      })
      .fail(function (xhr) { var respuesta = xhr.responseJSON; $error.removeClass('d-none').text((respuesta && respuesta.error) || 'No se pudo guardar la incidencia.'); })
      .always(function () { $boton.prop('disabled', false); });
  });
  $modal.on('hidden.bs.modal', function () {
    $form[0].reset();
    $productoSolicitadoSku.val('');
    $producto.val(null).trigger('change');
    $selectores.val(null).trigger('change');
    actualizarTotal();
    $error.addClass('d-none').text('');
  });
}(jQuery));
