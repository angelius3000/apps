(function ($) {
  var temporizadorIndicadorExito = null;

  function mostrarMensaje(texto, tipo) {
    var $mensaje = $('#conteoMensaje');
    if (!$mensaje.length) {
      return;
    }
    $mensaje.removeClass('alert-info alert-danger alert-success');
    $mensaje.addClass('alert-' + (tipo || 'info'));
    $mensaje.text(texto);
    $mensaje.show();
  }

  function ocultarMensaje() {
    var $mensaje = $('#conteoMensaje');
    if ($mensaje.length) {
      $mensaje.hide();
    }
  }

  function mostrarIndicadorExito() {
    var $indicador = $('#conteoIndicadorExito');
    if (!$indicador.length) {
      return;
    }

    if (temporizadorIndicadorExito) {
      clearTimeout(temporizadorIndicadorExito);
      temporizadorIndicadorExito = null;
    }

    $indicador.removeClass('d-none').addClass('d-flex');

    temporizadorIndicadorExito = setTimeout(function () {
      $indicador.removeClass('d-flex').addClass('d-none');
      temporizadorIndicadorExito = null;
    }, 2000);
  }

  function actualizarFechaHora() {
    var $etiqueta = $('#conteoFechaHora');
    if (!$etiqueta.length) {
      return;
    }
    var opciones = {
      timeZone: $etiqueta.data('timezone') || 'America/Denver',
      year: 'numeric',
      month: '2-digit',
      day: '2-digit',
      hour: '2-digit',
      minute: '2-digit',
      second: '2-digit'
    };

    var ahora = new Date();
    var fechaFormateada = ahora.toLocaleString('es-MX', opciones);
    $etiqueta.text('Fecha y hora (MST): ' + fechaFormateada);
  }

  function obtenerFilaPorHora(horaInicio) {
    return $('#conteoTabla tbody tr[data-hora-inicio="' + horaInicio + '"]');
  }

  function actualizarFila(horaInicio, datos) {
    var $fila = obtenerFilaPorHora(horaInicio);
    if (!$fila.length) {
      return;
    }

    $fila.find('[data-campo="hombre"]').text(datos.hombre);
    $fila.find('[data-campo="mujer"]').text(datos.mujer);
    $fila.find('[data-campo="pareja"]').text(datos.pareja);
    $fila.find('[data-campo="familia"]').text(datos.familia);
    $fila.find('[data-campo="cuadrilla"]').text(datos.cuadrilla);
    $fila.find('[data-campo="total"]').text(datos.total);
  }

  function desactivarBotones(deshabilitar) {
    $('.conteo-btn').prop('disabled', deshabilitar);
  }

  $(function () {
    actualizarFechaHora();
    setInterval(actualizarFechaHora, 1000);

    $('.conteo-btn').on('click', function () {
      var $boton = $(this);
      var tipo = $boton.data('tipo');
      var accion = $boton.data('accion');

      if (!tipo || !accion) {
        return;
      }

      ocultarMensaje();
      desactivarBotones(true);

      $.ajax({
        url: 'App/Server/ServerConteoActualizar.php',
        method: 'POST',
        dataType: 'json',
        data: {
          tipo: tipo,
          accion: accion
        }
      })
        .done(function (respuesta) {
          if (!respuesta || !respuesta.success) {
            mostrarMensaje(respuesta && respuesta.message ? respuesta.message : 'No se pudo actualizar el conteo.', 'danger');
            return;
          }

          if (respuesta.data) {
            actualizarFila(respuesta.data.horaInicio, respuesta.data);
          }

          mostrarIndicadorExito();
        })
        .fail(function () {
          mostrarMensaje('No se pudo conectar con el servidor.', 'danger');
        })
        .always(function () {
          desactivarBotones(false);
        });
    });
  });
})(jQuery);
