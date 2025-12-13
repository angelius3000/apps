$(document).ready(function() {
  var $modal = $('#ModalAgregarPendiente');
  var $container = $('#ProductosPendientesContainer');
  var $selectProductoPendiente = $('#ProductoPendienteSelect');
  var $inputCantidadPendiente = $('#CantidadPendiente');
  var $selectProductoPendienteContainer = $('#ProductoPendienteSelectContainer');
  var $inputCantidadPendienteContainer = $('#CantidadPendienteContainer');
  var $checkboxOtroProducto = $('#OtroProductoPendiente');
  var $otroProductoCampos = $('#OtroProductoPendienteCampos');
  var $inputSkuPendienteOtro = $('#SkuPendienteOtro');
  var $inputDescripcionPendienteOtro = $('#DescripcionPendienteOtro');
  var $inputCantidadPendienteOtro = $('#CantidadPendienteOtro');
  var $tablaBodyPartidas = $('#ProductosPendientesTablaBody');
  var productosDisponibles = $container.data('productos-disponibles') === 1 || $container.data('productos-disponibles') === '1';
  var $selectClientes = $('.select-cliente');
  var $selectRazonSocial = $('#RazonSocialPendiente');
  var $checkboxOtraRazonSocial = $('#OtraRazonSocialPendiente');
  var $razonSocialSelectContainer = $('#RazonSocialPendienteSelectContainer');
  var $numeroClientePendienteContainer = $('#NumeroClientePendienteContainer');
  var $inputNumeroClientePendienteOtro = $('#NumeroClientePendienteOtro');
  var $otraRazonSocialPendienteContainer = $('#OtraRazonSocialPendienteContainer');
  var $inputRazonSocialPendienteOtra = $('#RazonSocialPendienteOtra');
  var $selectVendedores = $('.select-vendedor');
  var $vendedorPendienteSelectContainer = $('#VendedorPendienteSelectContainer');
  var $checkboxOtroVendedor = $('#OtroVendedorPendiente');
  var $selectAduana = $('#AduanaPendiente');
  var $inputSurtidor = $('#SurtidorPendiente');
  var $selectAlmacenista = $('#SurtidorPendienteAlmacenista');
  var $checkboxOtroSurtidor = $('#OtroSurtidorPendiente');
  var $checkboxAlmacenista = $('#AlmacenistaPendiente');
  var $vendedorPendienteOtroContainer = $('#VendedorPendienteOtroContainer');
  var $inputVendedorPendienteOtro = $('#VendedorPendienteOtro');
  var $aduanaPendienteOtroContainer = $('#AduanaPendienteOtroContainer');
  var $inputAduanaPendienteOtro = $('#AduanaPendienteOtro');
  var $inputNombreCliente = $('#NombreClientePendiente');
  var $formularioPendiente = $('#FormularioAgregarPendiente');
  var $tablaMaterialPendiente = $('#TablaMaterialPendiente');
  var $panelDetalle = $('#PanelEntregaMaterialPendiente');
  var $detalleTitulo = $('#DetalleMaterialPendienteTitulo');
  var $detalleInfo = $('#DetalleMaterialPendienteInfo');
  var $detallePartidasBody = $('#DetallePartidasPendientes');
  var $registroEntregasBody = $('#RegistroEntregasBody');
  var $detalleError = $('#DetalleMaterialPendienteError');
  var $detalleExito = $('#DetalleMaterialPendienteExito');
  var $inputFolioEntrega = $('#EntregaFolio');
  var $inputDocumentoEntrega = $('#EntregaDocumento');
  var $inputRecibio = $('#EntregaRecibio');
  var $selectAduanaEntrega = $('#EntregaAduana');
  var $aduanaEntregaOtroContainer = $('#EntregaAduanaOtroContainer');
  var $inputAduanaEntregaOtro = $('#EntregaAduanaOtro');
  var $inputAduanaEntregaTexto = $('#EntregaAduanaTexto');
  var $btnRegistrarEntrega = $('#BtnRegistrarEntrega');
  var $btnEntregarTodo = $('#EntregarDocumentoCompleto');
  var $btnReiniciarEntregas = $('#ReiniciarEntregas');
  var ADUANA_OTRO_ID = '4';
  var partidasPendientes = [];

  function desplazarASeccionEntrega() {
    if (!$panelDetalle.length) {
      return;
    }

    var posicion = $panelDetalle.offset();
    if (!posicion) {
      return;
    }

    $('html, body').animate({ scrollTop: Math.max(posicion.top - 16, 0) }, 400);
  }

  function obtenerConfiguracionIdiomaMinimo() {
    return {
      inputTooShort: function(args) {
        var caracteresFaltantes = args.minimum - (args.input ? args.input.length : 0);
        var sufijo = caracteresFaltantes === 1 ? '' : 'es';
        return 'Ingresa ' + caracteresFaltantes + ' caracter' + sufijo + ' o más para buscar';
      }
    };
  }

  function acortarDescripcion(descripcion) {
    var texto = (descripcion || '').toString().trim();
    var maximo = 90;

    if (texto.length <= maximo) {
      return texto;
    }

    return texto.substring(0, maximo - 1) + '…';
  }

  function escaparHtml(texto) {
    return $('<div>').text(texto == null ? '' : texto).html();
  }

  function enfocarCampo($elemento) {
    if (!$elemento || !$elemento.length || $elemento.is(':disabled') || $elemento.hasClass('d-none')) {
      return;
    }

    if ($modal.length && !$modal.hasClass('show')) {
      return;
    }

    if ($elemento.hasClass('select2-hidden-accessible')) {
      var instancia = $elemento.data('select2');

      if (instancia && typeof $elemento.select2 === 'function') {
        $elemento.select2('open');
        return;
      }
    }

    $elemento.trigger('focus');
  }

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
      minimumInputLength: 3,
      language: obtenerConfiguracionIdiomaMinimo()
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
      minimumInputLength: 2,
      language: obtenerConfiguracionIdiomaMinimo()
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

  function inicializarSelectAduana($elemento, configuracion) {
    if (!$elemento.length) {
      return;
    }

    if ($elemento.hasClass('select2-hidden-accessible')) {
      return;
    }

    var dropdownParent = configuracion && configuracion.dropdownParent ? configuracion.dropdownParent : $modal;

    $elemento.select2({
      dropdownParent: dropdownParent,
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

  function limpiarCamposPartidaPendiente() {
    if ($selectProductoPendiente.length) {
      $selectProductoPendiente.val(null).trigger('change');
    }

    if ($inputCantidadPendiente.length) {
      $inputCantidadPendiente.val('');
    }

    if ($inputSkuPendienteOtro.length) {
      $inputSkuPendienteOtro.val('');
    }

    if ($inputDescripcionPendienteOtro.length) {
      $inputDescripcionPendienteOtro.val('');
    }

    if ($inputCantidadPendienteOtro.length) {
      $inputCantidadPendienteOtro.val('');
    }
  }

  function actualizarRequerimientosPartida() {
    var requierePartida = partidasPendientes.length === 0;
    var esOtroProducto = esOtroProductoActivo();

    if ($selectProductoPendiente.length) {
      $selectProductoPendiente.prop('required', productosDisponibles && !esOtroProducto && requierePartida);
    }

    if ($inputCantidadPendiente.length) {
      $inputCantidadPendiente.prop('required', productosDisponibles && !esOtroProducto && requierePartida);
    }

    if ($inputSkuPendienteOtro.length) {
      $inputSkuPendienteOtro.prop('required', esOtroProducto && requierePartida);
    }

    if ($inputDescripcionPendienteOtro.length) {
      $inputDescripcionPendienteOtro.prop('required', esOtroProducto && requierePartida);
    }

    if ($inputCantidadPendienteOtro.length) {
      $inputCantidadPendienteOtro.prop('required', esOtroProducto && requierePartida);
    }
  }

  function renderizarPartidasPendientes() {
    if (!$tablaBodyPartidas.length) {
      return;
    }

    actualizarRequerimientosPartida();

    $tablaBodyPartidas.empty();

    if (!partidasPendientes.length) {
      $tablaBodyPartidas.append('<tr class="text-muted"><td colspan="3" class="text-center">Agrega partidas para mostrarlas aquí.</td></tr>');
      return;
    }

    partidasPendientes.forEach(function(partida, indice) {
      var $fila = $('<tr class="align-middle"></tr>');
      var $celdaSku = $('<td></td>');
      var $enlaceEditar = $('<a href="#" class="editar-partida"></a>').attr('data-index', indice).text(partida.sku || '');
      var $celdaDescripcion = $('<td class="text-truncate" style="max-width: 420px;"></td>').attr('title', partida.descripcion).text(acortarDescripcion(partida.descripcion));
      var $celdaCantidad = $('<td class="text-end"></td>').text(partida.cantidad);

      $celdaSku.append($enlaceEditar);
      $celdaSku.append($('<input>', { type: 'hidden', name: 'productos[' + indice + '][id]', value: partida.id || '' }));
      $celdaSku.append($('<input>', { type: 'hidden', name: 'productos[' + indice + '][sku]', value: partida.sku || '' }));
      $celdaSku.append($('<input>', { type: 'hidden', name: 'productos[' + indice + '][descripcion]', value: partida.descripcion || '' }));
      $celdaSku.append($('<input>', { type: 'hidden', name: 'productos[' + indice + '][cantidad]', value: partida.cantidad }));
      $celdaSku.append($('<input>', { type: 'hidden', name: 'productos[' + indice + '][otro]', value: partida.esOtro ? '1' : '0' }));

      $fila.append($celdaSku, $celdaDescripcion, $celdaCantidad);
      $tablaBodyPartidas.append($fila);
    });
  }

  function obtenerDatosProductoSeleccionado() {
    var $opcionSeleccionada = $selectProductoPendiente.find('option:selected');

    return {
      id: parseInt($selectProductoPendiente.val(), 10),
      sku: ($opcionSeleccionada.data('sku') || $opcionSeleccionada.text() || '').toString().trim(),
      descripcion: ($opcionSeleccionada.data('descripcion') || $opcionSeleccionada.text() || '').toString().trim()
    };
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

  function agregarPartidaPendiente() {
    var esOtroProducto = esOtroProductoActivo();

    if (!esOtroProducto && !productosDisponibles) {
      return;
    }

    if (esOtroProducto) {
      var skuOtro = ($inputSkuPendienteOtro.val() || '').trim();
      var descripcionOtro = ($inputDescripcionPendienteOtro.val() || '').trim();
      var cantidadOtro = parseInt($inputCantidadPendienteOtro.val(), 10);

      if (!skuOtro || !descripcionOtro || isNaN(cantidadOtro) || cantidadOtro <= 0) {
        return;
      }

      partidasPendientes.push({
        id: null,
        sku: skuOtro,
        descripcion: descripcionOtro,
        cantidad: cantidadOtro,
        esOtro: true
      });
    } else {
      var datosProducto = obtenerDatosProductoSeleccionado();
      var cantidad = parseInt($inputCantidadPendiente.val(), 10);

      if (!datosProducto.id || isNaN(cantidad) || cantidad <= 0) {
        return;
      }

      partidasPendientes.push({
        id: datosProducto.id,
        sku: datosProducto.sku,
        descripcion: datosProducto.descripcion,
        cantidad: cantidad,
        esOtro: false
      });
    }

    renderizarPartidasPendientes();
    limpiarCamposPartidaPendiente();

    if (esOtroProducto && $inputSkuPendienteOtro.length) {
      $inputSkuPendienteOtro.trigger('focus');
    } else if ($selectProductoPendiente.length) {
      $selectProductoPendiente.trigger('focus');
    }
  }

  function esOtroProductoActivo() {
    return $checkboxOtroProducto.length && $checkboxOtroProducto.is(':checked');
  }

  function actualizarModoOtroProducto() {
    if (!$checkboxOtroProducto.length) {
      return;
    }

    var activo = esOtroProductoActivo();

    $otroProductoCampos.toggleClass('d-none', !activo);

    if ($selectProductoPendienteContainer.length) {
      $selectProductoPendienteContainer.toggleClass('d-none', activo);
    }

    if ($inputCantidadPendienteContainer.length) {
      $inputCantidadPendienteContainer.toggleClass('d-none', activo);
    }

    if ($selectProductoPendiente.length) {
      var debeDesactivarSelect = activo || !productosDisponibles;
      $selectProductoPendiente.prop('disabled', debeDesactivarSelect);

      if (debeDesactivarSelect) {
        $selectProductoPendiente.val(null).trigger('change');
      }
    }

    if ($inputCantidadPendiente.length) {
      $inputCantidadPendiente.prop('disabled', activo || !productosDisponibles);
      if (activo) {
        $inputCantidadPendiente.val('');
      }
    }

    if ($inputSkuPendienteOtro.length) {
      $inputSkuPendienteOtro.prop('disabled', !activo);
      if (!activo) {
        $inputSkuPendienteOtro.val('');
      }
    }

    if ($inputDescripcionPendienteOtro.length) {
      $inputDescripcionPendienteOtro.prop('disabled', !activo);
      if (!activo) {
        $inputDescripcionPendienteOtro.val('');
      }
    }

    if ($inputCantidadPendienteOtro.length) {
      $inputCantidadPendienteOtro.prop('disabled', !activo);
      if (!activo) {
        $inputCantidadPendienteOtro.val('');
      }
    }

    renderizarPartidasPendientes();
  }

  function mostrarContenedorAlmacenista(debeMostrar) {
    var $contenedor = obtenerContenedorSelectAlmacenista();

    if ($contenedor.length) {
      $contenedor.toggleClass('d-none', !debeMostrar);
    }
  }

  function esOtraRazonSocial() {
    return $checkboxOtraRazonSocial.length && $checkboxOtraRazonSocial.is(':checked');
  }

  function actualizarCamposOtraRazonSocial() {
    if (!$checkboxOtraRazonSocial.length) {
      return;
    }

    var activo = esOtraRazonSocial();

    if ($razonSocialSelectContainer.length) {
      $razonSocialSelectContainer.toggleClass('d-none', activo);
    }

    if ($selectRazonSocial.length) {
      $selectRazonSocial.prop('required', !activo).prop('disabled', activo);

      if (activo) {
        $selectRazonSocial.val(null).trigger('change');
      }
    }

    if ($numeroClientePendienteContainer.length) {
      $numeroClientePendienteContainer.toggleClass('d-none', !activo);
    }

    if ($otraRazonSocialPendienteContainer.length) {
      $otraRazonSocialPendienteContainer.toggleClass('d-none', !activo);
    }

    if ($inputNumeroClientePendienteOtro.length) {
      $inputNumeroClientePendienteOtro.prop('disabled', !activo).prop('required', activo);

      if (!activo) {
        $inputNumeroClientePendienteOtro.val('');
      }
    }

    if ($inputRazonSocialPendienteOtra.length) {
      $inputRazonSocialPendienteOtra.prop('disabled', !activo).prop('required', activo);

      if (!activo) {
        $inputRazonSocialPendienteOtra.val('');
      }
    }
  }

  function esVendedorOtro() {
    return $checkboxOtroVendedor.length ? $checkboxOtroVendedor.is(':checked') : false;
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

  function esAduanaEntregaOtro() {
    var valorSeleccionado = ($selectAduanaEntrega.val() || '').toString();
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

  function actualizarCampoAduanaEntregaOtro() {
    if (!$aduanaEntregaOtroContainer.length || !$inputAduanaEntregaOtro.length) {
      return;
    }

    var mostrarCampo = esAduanaEntregaOtro();
    $aduanaEntregaOtroContainer.toggleClass('d-none', !mostrarCampo);

    if (mostrarCampo) {
      $inputAduanaEntregaOtro.prop('required', true);
      return;
    }

    $inputAduanaEntregaOtro.prop('required', false).val('');
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

  function obtenerNombreAduanaEntrega() {
    if (esAduanaEntregaOtro() && $inputAduanaEntregaOtro.length) {
      return ($inputAduanaEntregaOtro.val() || '').trim();
    }

    if (!$selectAduanaEntrega.length) {
      return '';
    }

    var textoSeleccionado = $selectAduanaEntrega.find('option:selected').text() || '';
    return textoSeleccionado.trim();
  }

  function actualizarCampoVendedorOtro() {
    if (!$vendedorPendienteOtroContainer.length || !$inputVendedorPendienteOtro.length) {
      return;
    }

    var mostrarCampo = esVendedorOtro();

    $vendedorPendienteOtroContainer.toggleClass('d-none', !mostrarCampo);

    if ($vendedorPendienteSelectContainer.length) {
      $vendedorPendienteSelectContainer.toggleClass('d-none', mostrarCampo);
    }

    if ($selectVendedores.length) {
      if (mostrarCampo) {
        $selectVendedores.val(null).trigger('change');
        $selectVendedores.prop('required', false).prop('disabled', true);
      } else {
        $selectVendedores.prop('disabled', false).prop('required', true);
      }
    }

    if (mostrarCampo) {
      $inputVendedorPendienteOtro.prop('required', true).prop('disabled', false).removeAttr('disabled');
    } else {
      $inputVendedorPendienteOtro.prop('required', false).prop('disabled', true).val('');
    }
  }

  function obtenerCampoProductoDestino() {
    if (esOtroProductoActivo() && $inputSkuPendienteOtro.length) {
      return $inputSkuPendienteOtro;
    }

    return $selectProductoPendiente;
  }

  function mostrarMensajeError(mensaje) {
    var texto = (mensaje || '').toString().trim();
    if (texto === '') {
      texto = 'Ocurrió un problema al guardar el material pendiente.';
    }

    window.alert(texto);
  }

  function prepararModalDetalle(documento, folio) {
    var titulo = 'Entrega de material pendiente';

    if (folio) {
      titulo += ' - Folio #' + folio;
    }

    if ($detalleTitulo.length) {
      $detalleTitulo.text(titulo);
    }

    if ($detalleInfo.length) {
      $detalleInfo.text(documento ? 'Documento: ' + documento : '');
    }

    if ($detalleError.length) {
      $detalleError.addClass('d-none').text('');
    }

    if ($detallePartidasBody.length) {
      $detallePartidasBody.html('<tr class="text-muted"><td colspan="4" class="text-center">Cargando partidas…</td></tr>');
    }
  }

  function renderizarDetalleFactura(factura, documentoFallback, folioFallback) {
    if (!$detalleTitulo.length || !$detalleInfo.length) {
      return;
    }

    var folio = factura && factura.folio ? factura.folio : folioFallback;
    var documento = factura && factura.documento ? factura.documento : documentoFallback;
    var secciones = [];

    if (documento) {
      secciones.push('Documento: ' + documento);
    }

    if (factura && factura.fecha) {
      secciones.push('Fecha: ' + factura.fecha);
    }

    if (factura && factura.razonSocial) {
      secciones.push('Razón Social: ' + factura.razonSocial);
    }

    if (factura && factura.vendedor) {
      secciones.push('Vendedor: ' + factura.vendedor);
    }

    if (factura && factura.surtidor) {
      secciones.push('Surtidor: ' + factura.surtidor);
    }

    if (factura && factura.cliente) {
      secciones.push('Cliente: ' + factura.cliente);
    }

    if (factura && factura.aduana) {
      secciones.push('Aduana: ' + factura.aduana);
    }

    var titulo = 'Entrega de material pendiente';

    if (folio) {
      titulo += ' - Folio #' + folio;
    }

    $detalleTitulo.text(titulo);
    $detalleInfo.text(secciones.join(' • '));
    $inputFolioEntrega.val(folio);
    $inputDocumentoEntrega.val(documento);
  }

  function renderizarDetallePartidas(partidas) {
    if (!$detallePartidasBody.length) {
      return;
    }

    if (!partidas || !partidas.length) {
      $detallePartidasBody.html('<tr class="text-muted"><td colspan="4" class="text-center">El folio no tiene partidas pendientes registradas.</td></tr>');
      return;
    }

    var filas = partidas.map(function(partida) {
      var cantidad = partida && partida.cantidad ? partida.cantidad : 0;
      var partidaId = partida && partida.id ? partida.id : '';
      var inputId = 'entrega-partida-' + partidaId;
      return '<tr>' +
        '<td>' + escaparHtml(partida && partida.sku) + '</td>' +
        '<td>' + escaparHtml(partida && partida.descripcion) + '</td>' +
        '<td class="text-end" data-pendiente="' + cantidad + '">' + cantidad + '</td>' +
        '<td class="text-end">' +
          '<input type="number" class="form-control form-control-sm text-end campo-entregar" min="0" max="' + cantidad + '" step="1" data-id="' + partidaId + '" id="' + inputId + '" value="0">' +
        '</td>' +
        '</tr>';
    }).join('');

    $detallePartidasBody.html(filas);
  }

  function limpiarRegistroEntregasSeleccion() {
    if ($registroEntregasBody.length) {
      $registroEntregasBody.html('<tr class="text-muted"><td colspan="6" class="text-center">Selecciona un folio para ver su historial de entregas.</td></tr>');
    }
  }

  function prepararRegistroEntregas() {
    if ($registroEntregasBody.length) {
      $registroEntregasBody.html('<tr class="text-muted"><td colspan="6" class="text-center">Cargando entregas…</td></tr>');
    }
  }

  function renderizarRegistroEntregas(entregas) {
    if (!$registroEntregasBody.length) {
      return;
    }

    if (!entregas || !entregas.length) {
      $registroEntregasBody.html('<tr class="text-muted"><td colspan="6" class="text-center">No hay entregas registradas para este folio.</td></tr>');
      return;
    }

    var filas = entregas.map(function(entrega) {
      var sku = entrega && entrega.sku ? entrega.sku : '';
      var descripcion = entrega && entrega.descripcion ? entrega.descripcion : '';
      var producto = descripcion ? descripcion : '-';
      
      var cantidad = entrega && entrega.cantidad ? entrega.cantidad : 0;
      var recibio = entrega && entrega.recibio ? entrega.recibio : '-';
      var aduana = entrega && entrega.aduana ? entrega.aduana : '-';
      var fecha = entrega && entrega.fecha ? entrega.fecha : '-';

      return '<tr>' +
        '<td>' + escaparHtml(fecha) + '</td>' +
        '<td>' + escaparHtml(sku || '-') + '</td>' +
        '<td>' + escaparHtml(producto) + '</td>' +
        '<td class="text-end">' + cantidad + '</td>' +
        '<td>' + escaparHtml(recibio) + '</td>' +
        '<td>' + escaparHtml(aduana) + '</td>' +
        '</tr>';
    }).join('');

    $registroEntregasBody.html(filas);
  }

  function mostrarErrorDetalle(mensaje) {
    if ($detalleError.length) {
      $detalleError.removeClass('d-none').text(mensaje || 'Ocurrió un problema al cargar las partidas.');
    }

    if ($detalleExito.length) {
      $detalleExito.addClass('d-none').text('');
    }

    if ($detallePartidasBody.length) {
      $detallePartidasBody.html('<tr class="text-muted"><td colspan="4" class="text-center">No se pudieron cargar las partidas.</td></tr>');
    }

    if ($registroEntregasBody.length) {
      $registroEntregasBody.html('<tr class="text-muted"><td colspan="6" class="text-center">No se pudieron cargar las entregas.</td></tr>');
    }

    if ($btnRegistrarEntrega.length) {
      $btnRegistrarEntrega.prop('disabled', true);
    }
  }

  function limpiarPanelEntrega() {
    if ($detallePartidasBody.length) {
      $detallePartidasBody.html('<tr class="text-muted"><td colspan="4" class="text-center">Selecciona un folio para ver sus partidas.</td></tr>');
    }

    limpiarRegistroEntregasSeleccion();

    if ($detalleInfo.length) {
      $detalleInfo.text('');
    }

    if ($detalleTitulo.length) {
      $detalleTitulo.text('Selecciona un folio para gestionar su entrega');
    }

    if ($detalleError.length) {
      $detalleError.addClass('d-none').text('');
    }

    if ($detalleExito.length) {
      $detalleExito.addClass('d-none').text('');
    }

    if ($inputFolioEntrega.length) {
      $inputFolioEntrega.val('');
    }

    if ($inputDocumentoEntrega.length) {
      $inputDocumentoEntrega.val('');
    }

    if ($inputRecibio.length) {
      $inputRecibio.val('');
    }

    if ($selectAduanaEntrega.length) {
      $selectAduanaEntrega.val(null).trigger('change');
    }

    if ($inputAduanaEntregaOtro.length) {
      $inputAduanaEntregaOtro.val('');
    }

    if ($inputAduanaEntregaTexto.length) {
      $inputAduanaEntregaTexto.val('');
    }

    actualizarCampoAduanaEntregaOtro();

    if ($btnRegistrarEntrega.length) {
      $btnRegistrarEntrega.prop('disabled', true);
    }

    if ($panelDetalle.length) {
      $panelDetalle.addClass('d-none');
    }
  }

  function actualizarHabilitadoEntrega() {
    if (!$detallePartidasBody.length || !$btnRegistrarEntrega.length) {
      return;
    }

    var hayCantidad = false;
    $detallePartidasBody.find('.campo-entregar').each(function() {
      var valor = parseInt($(this).val(), 10);
      if (!isNaN(valor) && valor > 0) {
        hayCantidad = true;
      }
    });

    var aduanaSeleccionada = ($selectAduanaEntrega.val() || '').toString();
    var aduanaSeleccion = obtenerNombreAduanaEntrega();

    if ($inputAduanaEntregaTexto.length) {
      $inputAduanaEntregaTexto.val(aduanaSeleccion);
    }

    var camposRequeridosLlenos = $inputRecibio.val().trim() !== '' && aduanaSeleccionada !== '' && aduanaSeleccion !== '';
    $btnRegistrarEntrega.prop('disabled', !(hayCantidad && camposRequeridosLlenos));
  }

  function prepararPanelDetalle(documento, folio) {
    if ($panelDetalle.length) {
      $panelDetalle.removeClass('d-none');
      desplazarASeccionEntrega();
    }

    if ($detalleError.length) {
      $detalleError.addClass('d-none').text('');
    }

    if ($detalleExito.length) {
      $detalleExito.addClass('d-none').text('');
    }

      $inputRecibio.val('');
      $selectAduanaEntrega.val(null).trigger('change');
      $inputAduanaEntregaOtro.val('');
      $inputAduanaEntregaTexto.val('');
      actualizarCampoAduanaEntregaOtro();
      $btnRegistrarEntrega.prop('disabled', true);

      if ($detallePartidasBody.length) {
        $detallePartidasBody.html('<tr class="text-muted"><td colspan="4" class="text-center">Cargando partidas…</td></tr>');
      }

      prepararRegistroEntregas();

      $inputFolioEntrega.val(folio);
      $inputDocumentoEntrega.val(documento);
    }

  function cargarDetallePartidas(documento, folio) {
    prepararModalDetalle(documento, folio);
    prepararPanelDetalle(documento, folio);

    $.ajax({
      url: 'App/Server/ServerObtenerPartidasMaterialPendiente.php',
      method: 'GET',
      dataType: 'json',
      data: { folio: folio }
    }).done(function(respuesta) {
      if (respuesta && respuesta.success) {
        renderizarDetalleFactura(respuesta.factura || {}, documento, folio);
        renderizarDetallePartidas(respuesta.partidas || []);
        renderizarRegistroEntregas(respuesta.entregas || []);
        actualizarHabilitadoEntrega();
        return;
      }

      var mensaje = respuesta && respuesta.message ? respuesta.message : 'No se pudieron cargar las partidas.';
      mostrarErrorDetalle(mensaje);
    }).fail(function() {
      mostrarErrorDetalle('No se pudieron cargar las partidas.');
    });
  }

  inicializarSelectAduana($selectAduanaEntrega, { dropdownParent: $panelDetalle });

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
      inicializarSelect2($selectProductoPendiente);
    }

    actualizarCampoVendedorOtro();
    actualizarCampoSurtidor();
    actualizarCampoAduanaOtro();
    actualizarModoOtroProducto();
    actualizarCamposOtraRazonSocial();
  });

  $('#AgregarPartidaPendiente').on('click', function() {
    agregarPartidaPendiente();
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
      } else {
        $vendedor.val('');
      }

      $vendedor.prop('disabled', false).prop('required', true);
    });

    if ($vendedorPendienteSelectContainer.length) {
      $vendedorPendienteSelectContainer.removeClass('d-none');
    }

    if ($checkboxOtroVendedor.length) {
      $checkboxOtroVendedor.prop('checked', false);
    }

    partidasPendientes = [];
    renderizarPartidasPendientes();
    limpiarCamposPartidaPendiente();

    if ($checkboxOtroProducto.length) {
      $checkboxOtroProducto.prop('checked', false);
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
      $inputVendedorPendienteOtro.prop('required', false).prop('disabled', true).val('');
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

    if ($checkboxOtraRazonSocial.length) {
      $checkboxOtraRazonSocial.prop('checked', false);
    }

    actualizarCamposOtraRazonSocial();
    actualizarCampoVendedorOtro();
  });

  if ($tablaMaterialPendiente.length) {
    $tablaMaterialPendiente.on('click', '.material-pendiente-row', function() {
      var folio = $(this).data('folio');
      var documento = $(this).data('documento') || '';

      if (!folio) {
        return;
      }

      cargarDetallePartidas(documento, folio);
    });
  }

  $tablaBodyPartidas.on('click', '.editar-partida', function(evento) {
    evento.preventDefault();

    var indice = parseInt($(this).attr('data-index'), 10);

    if (isNaN(indice)) {
      return;
    }

    var partida = partidasPendientes[indice];

    if (!partida) {
      return;
    }

    partidasPendientes.splice(indice, 1);
    renderizarPartidasPendientes();

    if (partida.esOtro) {
      if ($checkboxOtroProducto.length) {
        $checkboxOtroProducto.prop('checked', true);
      }

      actualizarModoOtroProducto();

      $inputSkuPendienteOtro.val(partida.sku);
      $inputDescripcionPendienteOtro.val(partida.descripcion);
      $inputCantidadPendienteOtro.val(partida.cantidad);
      $inputSkuPendienteOtro.trigger('focus');
      return;
    }

    if ($checkboxOtroProducto.length) {
      $checkboxOtroProducto.prop('checked', false);
    }

    actualizarModoOtroProducto();

    $selectProductoPendiente.val(partida.id).trigger('change');
    $inputCantidadPendiente.val(partida.cantidad);
    $inputCantidadPendiente.trigger('focus');
  });

  $checkboxOtroVendedor.on('change', function() {
    actualizarCampoVendedorOtro();
    actualizarCampoSurtidor();

    if ($checkboxOtroVendedor.is(':checked')) {
      enfocarCampo($inputVendedorPendienteOtro);
      return;
    }

    enfocarCampo($selectVendedores);
  });

  $selectVendedores.on('change', function() {
    actualizarCampoVendedorOtro();
    actualizarCampoSurtidor();

    if (esVendedorOtro()) {
      enfocarCampo($inputVendedorPendienteOtro);
      return;
    }

    enfocarCampo($inputNombreCliente);
  });

  $inputVendedorPendienteOtro.on('blur', function() {
    if (esVendedorOtro()) {
      actualizarCampoSurtidor();
    }
  });

  $checkboxOtroSurtidor.on('change', function() {
    actualizarCampoSurtidor();

    if ($checkboxOtroSurtidor.is(':checked')) {
      enfocarCampo($inputSurtidor);
    }
  });

  $checkboxAlmacenista.on('change', function() {
    actualizarCampoSurtidor();

    if ($checkboxAlmacenista.is(':checked')) {
      enfocarCampo($selectAlmacenista);
    }
  });

  $selectAduana.on('change select2:select', function() {
    actualizarCampoAduanaOtro();

    if (esAduanaOtro()) {
      enfocarCampo($inputAduanaPendienteOtro);
      return;
    }

    enfocarCampo(obtenerCampoProductoDestino());
  });

  $selectAduanaEntrega.on('change select2:select', function() {
    actualizarCampoAduanaEntregaOtro();
    actualizarHabilitadoEntrega();

    if (esAduanaEntregaOtro()) {
      enfocarCampo($inputAduanaEntregaOtro);
    }
  });

  $checkboxOtroProducto.on('change', function() {
    actualizarModoOtroProducto();

    if ($checkboxOtroProducto.is(':checked')) {
      enfocarCampo($inputSkuPendienteOtro);
      return;
    }

    enfocarCampo($selectProductoPendiente);
  });

  $detallePartidasBody.on('input', '.campo-entregar', function() {
    var $input = $(this);
    var maximo = parseInt($input.attr('max'), 10);
    var valor = parseInt($input.val(), 10);

    if (isNaN(valor) || valor < 0) {
      valor = 0;
    }

    if (!isNaN(maximo) && valor > maximo) {
      valor = maximo;
    }

    $input.val(valor);
    actualizarHabilitadoEntrega();
  });

  $inputRecibio.on('input', function() {
    actualizarHabilitadoEntrega();
  });

  $inputAduanaEntregaOtro.on('input', function() {
    actualizarHabilitadoEntrega();
  });

  $btnEntregarTodo.on('click', function() {
    $detallePartidasBody.find('.campo-entregar').each(function() {
      var $input = $(this);
      var maximo = parseInt($input.attr('max'), 10);
      if (!isNaN(maximo)) {
        $input.val(maximo);
      }
    });

    actualizarHabilitadoEntrega();
  });

  $btnReiniciarEntregas.on('click', function() {
    limpiarPanelEntrega();
  });

  $('#FormularioEntregaMaterialPendiente').on('submit', function(evento) {
    evento.preventDefault();

    if ($btnRegistrarEntrega.length) {
      $btnRegistrarEntrega.prop('disabled', true);
    }

    if ($detalleError.length) {
      $detalleError.addClass('d-none').text('');
    }

    var folio = $inputFolioEntrega.val();
    var documento = $inputDocumentoEntrega.val();
    var recibio = $inputRecibio.val().trim();
    var aduana = obtenerNombreAduanaEntrega();
    var partidas = [];

    if ($inputAduanaEntregaTexto.length) {
      $inputAduanaEntregaTexto.val(aduana);
    }

    $detallePartidasBody.find('.campo-entregar').each(function() {
      var $input = $(this);
      var valor = parseInt($input.val(), 10);
      var partidaId = parseInt($input.data('id'), 10);

      if (!isNaN(valor) && valor > 0 && !isNaN(partidaId)) {
        partidas.push({ id: partidaId, entregar: valor });
      }
    });

    if (!folio || !documento || partidas.length === 0) {
      mostrarErrorDetalle('Selecciona un folio y captura al menos una cantidad a entregar.');
      return;
    }

    $.ajax({
      url: 'App/Server/ServerRegistrarEntregaMaterialPendiente.php',
      method: 'POST',
      dataType: 'json',
      data: {
        folio: folio,
        documento: documento,
        recibio: recibio,
        aduanaEntrega: aduana,
        partidas: JSON.stringify(partidas)
      }
    }).done(function(respuesta) {
      if (respuesta && respuesta.success) {
        if ($detalleExito.length) {
          $detalleExito.removeClass('d-none').text(respuesta.message || 'Entrega registrada.');
        }

        cargarDetallePartidas(documento, folio);
        return;
      }

      var mensaje = respuesta && respuesta.message ? respuesta.message : 'No se pudo registrar la entrega.';
      mostrarErrorDetalle(mensaje);
    }).fail(function() {
      mostrarErrorDetalle('No se pudo registrar la entrega.');
    }).always(function() {
      actualizarHabilitadoEntrega();
    });
  });

  $selectProductoPendiente.on('change select2:select', function() {
    enfocarCampo($inputCantidadPendiente);
  });

  $selectClientes.on('change select2:select', function() {
    enfocarCampo($selectVendedores);
  });

  $selectRazonSocial.on('change select2:select', function() {
    enfocarCampo($selectVendedores);
  });

  $checkboxOtraRazonSocial.on('change', function() {
    actualizarCamposOtraRazonSocial();

    if (esOtraRazonSocial()) {
      enfocarCampo($inputNumeroClientePendienteOtro);
      return;
    }

    enfocarCampo($selectRazonSocial);
  });

  $selectAlmacenista.on('change select2:select', function() {
    enfocarCampo($inputNombreCliente);
  });

  $inputCantidadPendiente.on('keydown', function(evento) {
    if (evento.key === 'Enter') {
      evento.preventDefault();
      agregarPartidaPendiente();
    }
  });

  $inputCantidadPendienteOtro.on('keydown', function(evento) {
    if (evento.key === 'Enter') {
      evento.preventDefault();
      agregarPartidaPendiente();
    }
  });

  if ($formularioPendiente.length) {
    $formularioPendiente.on('submit', function(evento) {
      evento.preventDefault();

      if (typeof this.checkValidity === 'function' && !this.checkValidity()) {
        if (typeof this.reportValidity === 'function') {
          this.reportValidity();
        }
        return;
      }

      if (partidasPendientes.length === 0) {
        renderizarPartidasPendientes();
        mostrarMensajeError('Agrega al menos una partida pendiente antes de guardar.');
        enfocarCampo(obtenerCampoProductoDestino());
        return;
      }

      var dataString = $formularioPendiente.serialize();

      $.ajax({
        type: 'POST',
        url: 'App/Server/ServerInsertarMaterialPendiente.php',
        data: dataString,
        dataType: 'json'
      }).done(function(respuesta) {
        if (respuesta && respuesta.success) {
          $modal.one('hidden.bs.modal', function() {
            window.location.reload();
          });
          $modal.modal('hide');
          return;
        }

        var mensaje = respuesta && respuesta.message ? respuesta.message : '';
        mostrarMensajeError(mensaje);
      }).fail(function() {
        mostrarMensajeError('No se pudo guardar el material pendiente. Inténtalo nuevamente.');
      });
    });
  }

  limpiarPanelEntrega();
});
