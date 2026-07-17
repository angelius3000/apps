(function ($) {
  'use strict';
  var $form = $('#FormularioIncidencia');
  var $cantidad = $('#IncidenciaCantidad');
  var $precio = $('#IncidenciaPrecio');
  var $total = $('#IncidenciaTotal');
  var $error = $('#IncidenciaError');
  var $modal = $('#ModalIncidencia');
  var $producto = $('#IncidenciaProducto');
  var $responsable = $('#IncidenciaResponsable');
  var $responsableOtroContenedor = $('#IncidenciaResponsableOtroContenedor');
  var $responsableOtro = $('#IncidenciaResponsableOtro');
  var $creador = $('#IncidenciaAduana');
  var $creadorOtroContenedor = $('#IncidenciaCreadorOtroContenedor');
  var $creadorOtro = $('#IncidenciaCreadorOtro');
  var $selectores = $responsable.add($creador);
  var $productoSolicitadoSku = $('#IncidenciaProductoSolicitadoSku');
  var $buscador = $('#BuscadorIncidencias');
  var $filas = $('#TablaIncidencias .incidencia-row');
  var $sinResultados = $('#IncidenciasSinResultados');
  var $resumenPaginacion = $('#ResumenPaginacionIncidencias');
  var $paginaAnterior = $('#IncidenciasPaginaAnterior');
  var $paginaSiguiente = $('#IncidenciasPaginaSiguiente');
  var paginaActual = 1;
  var registrosPorPagina = 5;

  function normalizarTexto(texto) {
    return (texto || '').toString().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
  }

  function filtrarIncidencias() {
    var termino = normalizarTexto($buscador.val());
    var coincidencias = $filas.filter(function () { return normalizarTexto($(this).text()).indexOf(termino) !== -1; });
    var total = coincidencias.length;
    var totalPaginas = Math.max(1, Math.ceil(total / registrosPorPagina));
    if (paginaActual > totalPaginas) { paginaActual = totalPaginas; }
    var inicio = (paginaActual - 1) * registrosPorPagina;
    var fin = inicio + registrosPorPagina;

    $filas.addClass('d-none');
    coincidencias.slice(inicio, fin).removeClass('d-none');
    $sinResultados.toggleClass('d-none', total !== 0);
    $resumenPaginacion.text(total === 0 ? 'Mostrando 0 de 0 registros' : 'Mostrando ' + (inicio + 1) + ' a ' + Math.min(fin, total) + ' de ' + total + ' registros');
    $paginaAnterior.prop('disabled', paginaActual <= 1 || total === 0);
    $paginaSiguiente.prop('disabled', paginaActual >= totalPaginas || total === 0);
  }


  function inicializarSelector($selector) {
    if (!$selector.length || typeof $.fn.select2 !== 'function' || $selector.hasClass('select2-hidden-accessible')) {
      return;
    }

    $selector.select2({
      dropdownParent: $modal,
      placeholder: $selector.data('placeholder') || 'Selecciona una opción',
      allowClear: true,
      width: '100%',
      minimumResultsForSearch: 0,
      matcher: $selector.is($responsable) ? filtrarResponsables : undefined,
      language: $selector.is($responsable) ? {
        noResults: function () {
          return '<button type="button" class="btn btn-link p-0 select2-otro-responsable">Otro</button>';
        }
      } : undefined,
      escapeMarkup: $selector.is($responsable) ? function (markup) { return markup; } : undefined
    });
  }

  function filtrarResponsables(params, data) {
    var termino = normalizarTexto(params.term);
    if (data.children) {
      var grupo = $.extend({}, data, true);
      grupo.children = $.map(data.children, function (opcion) { return filtrarResponsables(params, opcion); });
      return grupo.children.length ? grupo : null;
    }
    if (data.id === 'otro') { return null; }
    return termino === '' || normalizarTexto(data.text).indexOf(termino) !== -1 ? data : null;
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

  function actualizarCampoOtro($selector, $contenedor, $campo) {
    var esOtro = $selector.val() === 'otro';
    $contenedor.toggleClass('d-none', !esOtro);
    $campo.prop('required', esOtro);
    if (!esOtro) { $campo.val(''); }
  }

  function actualizarResponsableOtro() {
    actualizarCampoOtro($responsable, $responsableOtroContenedor, $responsableOtro);
  }

  function actualizarCreadorOtro() {
    actualizarCampoOtro($creador, $creadorOtroContenedor, $creadorOtro);
  }

  $selectores.each(function () { inicializarSelector($(this)); });
  inicializarProducto();
  $responsable.on('change select2:select', actualizarResponsableOtro);
  $creador.on('change select2:select', actualizarCreadorOtro);
  function seleccionarResponsableOtro(evento) {
    if (!$(evento.target).closest('.select2-otro-responsable').length) { return; }
    evento.preventDefault();
    evento.stopPropagation();
    $responsable.val('otro').trigger('change');
    $responsable.select2('close');
  }

  document.addEventListener('mousedown', seleccionarResponsableOtro, true);
  document.addEventListener('click', seleccionarResponsableOtro, true);

  function actualizarTotal() {
    var cantidad = parseFloat($cantidad.val()) || 0;
    var precio = parseFloat($precio.val()) || 0;
    $total.val('$' + (cantidad * precio).toFixed(2));
  }
  $cantidad.add($precio).on('input', actualizarTotal);

  $buscador.on('input', function () { paginaActual = 1; filtrarIncidencias(); });
  $paginaAnterior.on('click', function () { if (paginaActual > 1) { paginaActual--; filtrarIncidencias(); } });
  $paginaSiguiente.on('click', function () { paginaActual++; filtrarIncidencias(); });
  filtrarIncidencias();

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
    actualizarResponsableOtro();
    actualizarCreadorOtro();
    actualizarTotal();
    $error.addClass('d-none').text('');
  });
}(jQuery));
