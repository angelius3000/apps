(function ($) {
  'use strict';
  var $form = $('#FormularioIncidencia');
  var $cantidad = $('#IncidenciaCantidad');
  var $precio = $('#IncidenciaPrecio');
  var $total = $('#IncidenciaTotal');
  var $error = $('#IncidenciaError');

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
  $('#ModalIncidencia').on('hidden.bs.modal', function () { $form[0].reset(); actualizarTotal(); $error.addClass('d-none').text(''); });
}(jQuery));
