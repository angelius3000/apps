(function ($) {
  'use strict';
  var $form = $('#FormularioIncidencia');
  var $cantidad = $('#IncidenciaCantidad');
  var $precio = $('#IncidenciaPrecio');
  var $total = $('#IncidenciaTotal');
  var $error = $('#IncidenciaError');
  var $modal = $('#ModalIncidencia');
  var $selectores = $('#IncidenciaProducto, #IncidenciaVendedor, #IncidenciaAduana');

  function inicializarSelectores() {
    if (typeof $.fn.select2 !== 'function') {
      return;
    }

    $selectores.each(function () {
      var $selector = $(this);
      if ($selector.hasClass('select2-hidden-accessible')) {
        return;
      }

      $selector.select2({
        dropdownParent: $modal,
        placeholder: $selector.data('placeholder') || 'Selecciona una opción',
        allowClear: true,
        width: '100%',
        minimumResultsForSearch: 0
      });
    });
  }

  inicializarSelectores();

  function actualizarTotal() {
    var cantidad = parseFloat($cantidad.val()) || 0;
    var precio = parseFloat($precio.val()) || 0;
    $total.val('$' + (cantidad * precio).toFixed(2));
  }
  $cantidad.add($precio).on('input', actualizarTotal);

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
    $selectores.val(null).trigger('change');
    actualizarTotal();
    $error.addClass('d-none').text('');
  });
}(jQuery));
