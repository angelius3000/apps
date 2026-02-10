$(document).ready(function() {
  var $modal = $('#ModalAgregarPendiente');
  var $modalTitle = $('#ModalTituloPendiente');
  var $btnGuardarPendiente = $('#BtnGuardarPendiente');
  var $inputFolioPendiente = $('#FolioPendiente');
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
  var $inputNumeroFacturaPendiente = $('#NumeroFacturaPendiente');
  var $formularioPendiente = $('#FormularioAgregarPendiente');
  var $modalDocumentoDuplicado = $('#ModalDocumentoPendienteDuplicado');
  var $documentoDuplicadoTexto = $('#DocumentoPendienteDuplicadoTexto');
  var $btnCambiarDocumentoPendiente = $('#BtnCambiarDocumentoPendiente');
  var $btnCerrarDocumentoPendiente = $('#BtnCerrarDocumentoPendiente');
  var $tablaMaterialPendiente = $('#TablaMaterialPendiente');
  var $buscadorMaterialPendiente = $('#BuscadorMaterialPendiente');
  var $filaSinResultados = $('#MaterialPendienteSinResultados');
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
  var ignorarCambioVendedor = false;
  var documentoDuplicadoDetectado = false;
  var accionModalDocumento = '';
  var partidasPendientes = [];
  var modoEdicion = false;

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

  function normalizarTextoBuscador(texto) {
    return (texto || '')
      .toString()
      .toLowerCase()
      .normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '');
  }

  function normalizarTextoSimple(texto) {
    return (texto || '')
      .toString()
      .toLowerCase()
      .replace(/\s+/g, ' ')
      .trim();
  }

  function encontrarOpcionPorTexto($select, texto) {
    if (!$select.length || !texto) {
      return '';
    }

    var textoNormalizado = normalizarTextoSimple(texto);
    var valorEncontrado = '';

    $select.find('option').each(function() {
      var $opcion = $(this);
      var textoOpcion = normalizarTextoSimple($opcion.text());

      if (textoOpcion === textoNormalizado || textoOpcion.indexOf(textoNormalizado) !== -1 || textoNormalizado.indexOf(textoOpcion) !== -1) {
        valorEncontrado = $opcion.val();
        return false;
      }

      return true;
    });

    return valorEncontrado;
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

  function formatearTextoProducto(producto) {
    if (!producto) {
      return '';
    }

    var sku = (producto.sku || '').toString().trim();
    var descripcion = (producto.descripcion || '').toString().trim();

    if (sku && descripcion) {
      return sku + ' - ' + descripcion;
    }

    return sku || descripcion;
  }

  function inicializarSelect2($elemento) {
    if (!$elemento.length || typeof $elemento.select2 !== 'function') {
      return;
    }

    if ($elemento.hasClass('select2-hidden-accessible')) {
      return;
    }

    var searchUrl = ($elemento.data('search-url') || '').toString().trim();

    $elemento.select2({
      dropdownParent: $modal,
      placeholder: $elemento.data('placeholder') || 'Selecciona producto',
      allowClear: true,
      width: '100%',
      minimumResultsForSearch: 0,
      minimumInputLength: 3,
      language: obtenerConfiguracionIdiomaMinimo(),
      ajax: {
        url: searchUrl,
        dataType: 'json',
        delay: 250,
        data: function(params) {
          return {
            term: params.term || '',
            page: params.page || 1
          };
        },
        processResults: function(data, params) {
          params.page = params.page || 1;

          var resultados = [];
          if (data && Array.isArray(data.results)) {
            resultados = data.results.map(function(item) {
              var sku = (item && item.sku ? item.sku : '').toString();
              var descripcion = (item && item.descripcion ? item.descripcion : '').toString();

              return {
                id: item && item.id ? item.id : '',
                text: formatearTextoProducto({ sku: sku, descripcion: descripcion }) || (item && item.text ? item.text : ''),
                sku: sku,
                descripcion: descripcion
              };
            });
          }

          return {
            results: resultados,
            pagination: {
              more: !!(data && data.pagination && data.pagination.more)
            }
          };
        },
        cache: true
      },
      templateResult: function(item) {
        return item.text || '';
      },
      templateSelection: function(item) {
        return item.text || item.id || '';
      },
      escapeMarkup: function(markup) {
        return markup;
      }
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

  function filtrarMaterialPendiente() {
    if (!$tablaMaterialPendiente.length) {
      return;
    }

    var termino = normalizarTextoBuscador($buscadorMaterialPendiente.val());
    var $filas = $tablaMaterialPendiente.find('.material-pendiente-row');

    if (!$filas.length) {
      if ($filaSinResultados.length) {
        $filaSinResultados.addClass('d-none');
      }
      return;
    }

    var filasVisibles = 0;

    $filas.each(function() {
      var $fila = $(this);
      var textoFila = normalizarTextoBuscador($fila.text());
      var coincide = termino === '' || textoFila.indexOf(termino) !== -1;

      $fila.toggle(coincide);

      if (coincide) {
        filasVisibles += 1;
      }
    });

    if (!$filaSinResultados.length) {
      return;
    }

    if (filasVisibles === 0) {
      $filaSinResultados.removeClass('d-none');
    } else {
      $filaSinResultados.addClass('d-none');
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
      $tablaBodyPartidas.append('<tr class="text-muted"><td colspan="4" class="text-center">Agrega partidas para mostrarlas aquí.</td></tr>');
      return;
    }

    partidasPendientes.forEach(function(partida, indice) {
      var $fila = $('<tr class="align-middle"></tr>');
      var $celdaSku = $('<td></td>');
      var $enlaceEditar = $('<a href="#" class="editar-partida"></a>').attr('data-index', indice).text(partida.sku || '');
      var $celdaDescripcion = $('<td class="text-truncate" style="max-width: 420px;"></td>').attr('title', partida.descripcion).text(acortarDescripcion(partida.descripcion));
      var $celdaCantidad = $('<td class="text-end"></td>').text(partida.cantidad);
      var $celdaAcciones = $('<td class="text-end"></td>');
      var $botonEliminar = $('<button type="button" class="btn btn-outline-danger btn-sm eliminar-partida"></button>')
        .attr('data-index', indice)
        .html('<i class="material-icons-two-tone">delete</i>');

      $celdaSku.append($enlaceEditar);
      $celdaSku.append($('<input>', { type: 'hidden', name: 'productos[' + indice + '][id]', value: partida.id || '' }));
      $celdaSku.append($('<input>', { type: 'hidden', name: 'productos[' + indice + '][sku]', value: partida.sku || '' }));
      $celdaSku.append($('<input>', { type: 'hidden', name: 'productos[' + indice + '][descripcion]', value: partida.descripcion || '' }));
      $celdaSku.append($('<input>', { type: 'hidden', name: 'productos[' + indice + '][cantidad]', value: partida.cantidad }));
      $celdaSku.append($('<input>', { type: 'hidden', name: 'productos[' + indice + '][otro]', value: partida.esOtro ? '1' : '0' }));

      $celdaAcciones.append($botonEliminar);
      $fila.append($celdaSku, $celdaCantidad, $celdaAcciones, $celdaDescripcion);
      $tablaBodyPartidas.append($fila);
    });
  }

  function obtenerDatosProductoSeleccionado() {
    var valorSeleccionado = parseInt($selectProductoPendiente.val(), 10);
    var datosSeleccionados = $selectProductoPendiente.hasClass('select2-hidden-accessible') ? $selectProductoPendiente.select2('data') : [];

    var seleccionado = datosSeleccionados && datosSeleccionados.length ? datosSeleccionados[0] : null;

    if (!seleccionado) {
      return {
        id: valorSeleccionado,
        sku: '',
        descripcion: ''
      };
    }

    return {
      id: valorSeleccionado,
      sku: (seleccionado.sku || '').toString().trim(),
      descripcion: (seleccionado.descripcion || seleccionado.text || '').toString().trim()
    };
  }

  function encontrarProductoPorSku(sku, descripcion) {
    if (!$selectProductoPendiente.length || !sku) {
      return null;
    }

    var skuNormalizado = normalizarTextoSimple(sku);
    var descripcionNormalizada = normalizarTextoSimple(descripcion || '');
    var encontrado = null;

    $selectProductoPendiente.find('option').each(function() {
      var $opcion = $(this);
      var skuOpcion = normalizarTextoSimple($opcion.attr('data-sku') || '');
      var descripcionOpcion = normalizarTextoSimple($opcion.attr('data-descripcion') || '');
      var textoOpcion = normalizarTextoSimple($opcion.text());

      var coincideSku = skuOpcion !== '' && skuOpcion === skuNormalizado;
      var coincideTexto = textoOpcion !== '' && textoOpcion.indexOf(skuNormalizado) !== -1;
      var coincideDescripcion = descripcionNormalizada !== '' && descripcionOpcion === descripcionNormalizada;

      if (coincideSku || coincideTexto || coincideDescripcion) {
        encontrado = {
          id: parseInt($opcion.val(), 10) || null,
          sku: $opcion.attr('data-sku') || sku,
          descripcion: $opcion.attr('data-descripcion') || descripcion || $opcion.text()
        };
        return false;
      }

      return true;
    });

    return encontrado;
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
      enfocarCampo($inputSkuPendienteOtro);
    } else if ($selectProductoPendiente.length) {
      enfocarCampo($selectProductoPendiente);
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

  function establecerModoEdicion(activo) {
    modoEdicion = activo;

    if ($modalTitle.length) {
      $modalTitle.text(activo ? 'Editar material pendiente' : 'Agregar material pendiente');
    }

    if ($btnGuardarPendiente.length) {
      $btnGuardarPendiente.text(activo ? 'Guardar cambios' : 'Guardar');
    }

    if (!activo && $inputFolioPendiente.length) {
      $inputFolioPendiente.val('');
    }
  }

  function reiniciarFormularioPendiente() {
    if ($formularioPendiente.length) {
      $formularioPendiente[0].reset();
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

    if ($inputNumeroClientePendienteOtro.length) {
      $inputNumeroClientePendienteOtro.val('');
    }

    if ($inputRazonSocialPendienteOtra.length) {
      $inputRazonSocialPendienteOtra.val('');
    }

    if ($inputNombreCliente.length) {
      $inputNombreCliente.val('');
    }

    actualizarCamposOtraRazonSocial();
    actualizarCampoVendedorOtro();
    actualizarCampoSurtidor();
    actualizarCampoAduanaOtro();
    actualizarModoOtroProducto();
    establecerModoEdicion(false);
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

    var usarAlmacenista = $checkboxAlmacenista.length && typeof $checkboxAlmacenista.is === 'function' ? $checkboxAlmacenista.is(':checked') : false;
    var esOtroVendedorActivo = esVendedorOtro();
    var permitirEdicion =
      esOtroVendedorActivo ||
      ($checkboxOtroSurtidor.length && typeof $checkboxOtroSurtidor.is === 'function' ? $checkboxOtroSurtidor.is(':checked') : false);
    var nombreVendedor = obtenerNombreVendedor();
    var escribiendoVendedorManual =
      $inputVendedorPendienteOtro.length &&
      document.activeElement === $inputVendedorPendienteOtro[0];
    var debeEnfocarSurtidor = permitirEdicion && !esOtroVendedorActivo && !escribiendoVendedorManual;

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
      $inputSurtidor.val(nombreVendedor);
      if (debeEnfocarSurtidor) {
        $inputSurtidor.trigger('focus');
      }
      return;
    }

    $inputSurtidor.prop('readonly', true);
    $inputSurtidor.val(nombreVendedor);
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

    if (ignorarCambioVendedor) {
      return;
    }

    var mostrarCampo = esVendedorOtro();

    $vendedorPendienteOtroContainer.toggleClass('d-none', !mostrarCampo);

    if ($vendedorPendienteSelectContainer.length) {
      $vendedorPendienteSelectContainer.toggleClass('d-none', mostrarCampo);
    }

    if ($selectVendedores.length) {
      if (mostrarCampo) {
        ignorarCambioVendedor = true;
        $selectVendedores.val(null);

        if ($selectVendedores.hasClass('select2-hidden-accessible')) {
          $selectVendedores.trigger('change.select2');
        }

        ignorarCambioVendedor = false;
        $selectVendedores
          .prop('required', false)
          .removeAttr('required')
          .prop('disabled', true)
          .attr('aria-required', 'false');
      } else {
        $selectVendedores
          .prop('disabled', false)
          .prop('required', true)
          .attr('aria-required', 'true');
      }
    }

    if (mostrarCampo) {
      $inputVendedorPendienteOtro
        .prop('required', true)
        .prop('disabled', false)
        .prop('readonly', false)
        .removeAttr('disabled')
        .removeAttr('readonly')
        .removeAttr('aria-disabled');
    } else {
      $inputVendedorPendienteOtro.prop('required', false).prop('disabled', false).val('');
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

  function mostrarAdvertenciaDocumentoDuplicado(documento) {
    documentoDuplicadoDetectado = true;

    if ($documentoDuplicadoTexto.length) {
      $documentoDuplicadoTexto.text(documento || '');
    }

    if ($modalDocumentoDuplicado.length) {
      accionModalDocumento = 'cambiar';
      $modalDocumentoDuplicado.modal('show');
      if ($modal.length) {
        $modal.modal('hide');
      }
      return;
    }

    mostrarMensajeError('El número de documento ya se encuentra registrado. Captura uno diferente.');
  }

  function validarDocumentoDuplicado(documento) {
    if (!documento) {
      documentoDuplicadoDetectado = false;
      return;
    }

    var folio = parseInt($inputFolioPendiente.val(), 10);

    $.ajax({
      type: 'POST',
      url: 'App/Server/ServerInfoMaterialPendienteChecarDocumentoSiExiste.php',
      dataType: 'json',
      data: {
        DocumentoFMP: documento,
        FolioPendiente: isNaN(folio) ? '' : folio
      }
    }).done(function(respuesta) {
      if (respuesta && respuesta.success && respuesta.exists) {
        mostrarAdvertenciaDocumentoDuplicado(documento);
        return;
      }

      documentoDuplicadoDetectado = false;
    });
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

  function actualizarEstadoFilaMaterialPendiente(folio, tienePendientes) {
    if (!$tablaMaterialPendiente.length || !folio) {
      return;
    }

    var $fila = $tablaMaterialPendiente.find('.material-pendiente-row[data-folio="' + folio + '"]');

    if (!$fila.length) {
      return;
    }

    if (tienePendientes) {
      $fila.addClass('text-danger').removeClass('text-body');
      return;
    }

    $fila.removeClass('text-danger').addClass('text-body');
  }

  function renderizarDetallePartidas(partidas) {
    if (!$detallePartidasBody.length) {
      return;
    }

    if (!partidas || !partidas.length) {
      $detallePartidasBody.html('<tr class="text-muted"><td colspan="4" class="text-center">El folio no tiene partidas pendientes registradas.</td></tr>');
      actualizarEstadoFilaMaterialPendiente($inputFolioEntrega.val(), false);
      return;
    }

    actualizarEstadoFilaMaterialPendiente($inputFolioEntrega.val(), true);

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

  function prepararFormularioEdicion(factura, partidas) {
    if (!factura) {
      return;
    }

    establecerModoEdicion(true);

    if ($inputFolioPendiente.length) {
      $inputFolioPendiente.val(factura.folio || '');
    }

    if ($inputNumeroFacturaPendiente.length) {
      $inputNumeroFacturaPendiente.val(factura.documento || '');
    }

    var razonSocial = factura.razonSocial || '';
    var razonSocialSeleccion = encontrarOpcionPorTexto($selectRazonSocial, razonSocial);

    if (razonSocialSeleccion) {
      if ($checkboxOtraRazonSocial.length) {
        $checkboxOtraRazonSocial.prop('checked', false);
      }
      $selectRazonSocial.val(razonSocialSeleccion).trigger('change');
    } else {
      if ($checkboxOtraRazonSocial.length) {
        $checkboxOtraRazonSocial.prop('checked', true);
      }
      if ($inputRazonSocialPendienteOtra.length) {
        $inputRazonSocialPendienteOtra.val(razonSocial);
      }

      if ($inputNumeroClientePendienteOtro.length) {
        var coincidenciaNumero = razonSocial.match(/#\s*(\d+)/);
        $inputNumeroClientePendienteOtro.val(coincidenciaNumero ? coincidenciaNumero[1] : 'N/A');
      }
    }

    var vendedor = factura.vendedor || '';
    var vendedorSeleccion = encontrarOpcionPorTexto($selectVendedores, vendedor);

    if (vendedorSeleccion) {
      if ($checkboxOtroVendedor.length) {
        $checkboxOtroVendedor.prop('checked', false);
      }
      $selectVendedores.val(vendedorSeleccion).trigger('change');
      if ($inputVendedorPendienteOtro.length) {
        $inputVendedorPendienteOtro.val('');
      }
    } else {
      if ($checkboxOtroVendedor.length) {
        $checkboxOtroVendedor.prop('checked', true);
      }
      if ($inputVendedorPendienteOtro.length) {
        $inputVendedorPendienteOtro.val(vendedor);
      }
    }

    var surtidor = factura.surtidor || '';
    var almacenistaSeleccion = encontrarOpcionPorTexto($selectAlmacenista, surtidor);

    if (almacenistaSeleccion) {
      if ($checkboxAlmacenista.length) {
        $checkboxAlmacenista.prop('checked', true);
      }
      $selectAlmacenista.val(almacenistaSeleccion).trigger('change');
    } else {
      if ($checkboxAlmacenista.length) {
        $checkboxAlmacenista.prop('checked', false);
      }

      if ($inputSurtidor.length) {
        $inputSurtidor.val(surtidor);
      }

      if ($checkboxOtroSurtidor.length) {
        var habilitarOtroSurtidor = surtidor !== '' && vendedor !== '' && surtidor !== vendedor;
        $checkboxOtroSurtidor.prop('checked', habilitarOtroSurtidor);
      }
    }

    if ($inputNombreCliente.length) {
      $inputNombreCliente.val(factura.cliente || '');
    }

    var aduana = factura.aduana || '';
    var aduanaSeleccion = encontrarOpcionPorTexto($selectAduana, aduana);

    if (aduanaSeleccion) {
      $selectAduana.val(aduanaSeleccion).trigger('change');
      if ($inputAduanaPendienteOtro.length) {
        $inputAduanaPendienteOtro.val('');
      }
    } else {
      $selectAduana.val(ADUANA_OTRO_ID).trigger('change');
      if ($inputAduanaPendienteOtro.length) {
        $inputAduanaPendienteOtro.val(aduana);
      }
    }

    if ($checkboxOtroProducto.length) {
      $checkboxOtroProducto.prop('checked', false);
    }

    partidasPendientes = (partidas || []).map(function(partida) {
      var sku = partida && partida.sku ? partida.sku : '';
      var descripcion = partida && partida.descripcion ? partida.descripcion : '';
      var cantidad = partida && partida.cantidad ? partida.cantidad : 0;
      var productoEncontrado = productosDisponibles ? encontrarProductoPorSku(sku, descripcion) : null;
      var esOtro = !productoEncontrado;

      return {
        id: productoEncontrado && productoEncontrado.id ? productoEncontrado.id : null,
        sku: productoEncontrado && productoEncontrado.sku ? productoEncontrado.sku : sku,
        descripcion: productoEncontrado && productoEncontrado.descripcion ? productoEncontrado.descripcion : descripcion,
        cantidad: cantidad,
        esOtro: esOtro
      };
    });

    actualizarCamposOtraRazonSocial();
    actualizarCampoVendedorOtro();
    actualizarCampoSurtidor();
    actualizarCampoAduanaOtro();
    actualizarModoOtroProducto();

    if (!almacenistaSeleccion && surtidor && $inputSurtidor.length) {
      $inputSurtidor.val(surtidor);
    }

    renderizarPartidasPendientes();
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

  if ($buscadorMaterialPendiente.length) {
    $buscadorMaterialPendiente.on('input', filtrarMaterialPendiente);
    filtrarMaterialPendiente();
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
    reiniciarFormularioPendiente();
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

    $tablaMaterialPendiente.on('click', '.editar-material-pendiente', function(evento) {
      evento.preventDefault();
      evento.stopPropagation();

      var folio = parseInt($(this).data('folio'), 10);

      if (isNaN(folio) || folio <= 0) {
        return;
      }

      $.ajax({
        url: 'App/Server/ServerObtenerPartidasMaterialPendiente.php',
        method: 'GET',
        dataType: 'json',
        data: { folio: folio }
      }).done(function(respuesta) {
        if (!respuesta || !respuesta.success) {
          var mensajeError = respuesta && respuesta.message ? respuesta.message : 'No se pudo cargar el folio seleccionado.';
          mostrarMensajeError(mensajeError);
          return;
        }

        reiniciarFormularioPendiente();
        establecerModoEdicion(true);

        $modal.one('shown.bs.modal', function() {
          prepararFormularioEdicion(respuesta.factura || {}, respuesta.partidas || []);
        });

        $modal.modal('show');
      }).fail(function() {
        mostrarMensajeError('No se pudo cargar el folio seleccionado.');
      });
    });

    $tablaMaterialPendiente.on('click', '.eliminar-material-pendiente', function(evento) {
      evento.preventDefault();
      evento.stopPropagation();

      var folio = parseInt($(this).data('folio'), 10);
      var documento = $(this).data('documento') || '';

      if (isNaN(folio) || folio <= 0) {
        return;
      }

      var mensaje = '¿Deseas eliminar este registro? Esta acción eliminará el folio' + (documento ? ' "' + documento + '"' : '') + '.';

      if (!window.confirm(mensaje)) {
        return;
      }

      $.ajax({
        url: 'App/Server/ServerEliminarMaterialPendiente.php',
        method: 'POST',
        dataType: 'json',
        data: { folio: folio }
      }).done(function(respuesta) {
        if (respuesta && respuesta.success) {
          window.location.reload();
          return;
        }

        var mensajeError = respuesta && respuesta.message ? respuesta.message : 'No se pudo eliminar el registro.';
        mostrarMensajeError(mensajeError);
      }).fail(function() {
        mostrarMensajeError('No se pudo eliminar el registro.');
      });
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

  $tablaBodyPartidas.on('click', '.eliminar-partida', function(evento) {
    evento.preventDefault();

    var indice = parseInt($(this).attr('data-index'), 10);

    if (isNaN(indice)) {
      return;
    }

    partidasPendientes.splice(indice, 1);
    renderizarPartidasPendientes();
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
    if (ignorarCambioVendedor) {
      return;
    }

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

    var $campoProductoDestino = obtenerCampoProductoDestino();
    enfocarCampo($campoProductoDestino);
  });

  if ($inputNumeroFacturaPendiente.length) {
    $inputNumeroFacturaPendiente.on('input', function() {
      documentoDuplicadoDetectado = false;
    });

    $inputNumeroFacturaPendiente.on('blur', function() {
      var documento = ($inputNumeroFacturaPendiente.val() || '').trim();
      var documentoOriginal = ($inputNumeroFacturaPendiente.data('documento-original') || '').toString().trim();

      if (documento === '' || (documentoOriginal !== '' && documentoOriginal === documento)) {
        documentoDuplicadoDetectado = false;
        return;
      }

      validarDocumentoDuplicado(documento);
    });
  }

  if ($modalDocumentoDuplicado.length) {
    $modalDocumentoDuplicado.on('hidden.bs.modal', function() {
      if (accionModalDocumento === 'cambiar') {
        accionModalDocumento = '';
        if ($modal.length) {
          $modal.modal('show');
        }
      }
    });
  }

  if ($btnCambiarDocumentoPendiente.length) {
    $btnCambiarDocumentoPendiente.on('click', function() {
      accionModalDocumento = 'cambiar';
      if ($modalDocumentoDuplicado.length) {
        $modalDocumentoDuplicado.modal('hide');
      }

      setTimeout(function() {
        enfocarCampo($inputNumeroFacturaPendiente);
      }, 250);
    });
  }

  if ($btnCerrarDocumentoPendiente.length) {
    $btnCerrarDocumentoPendiente.on('click', function() {
      accionModalDocumento = '';
      if ($modalDocumentoDuplicado.length) {
        $modalDocumentoDuplicado.modal('hide');
      }
      if ($modal.length) {
        $modal.modal('hide');
      }
    });
  }

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

      var numeroFactura = ($inputNumeroFacturaPendiente.val() || '').trim();

      if (numeroFactura === '') {
        if (typeof this.reportValidity === 'function') {
          this.reportValidity();
        }
        mostrarMensajeError('Captura el número de documento antes de guardar.');
        enfocarCampo($inputNumeroFacturaPendiente);
        return;
      }

      if (documentoDuplicadoDetectado) {
        mostrarMensajeError('El número de documento ya se encuentra registrado. Captura uno diferente.');
        enfocarCampo($inputNumeroFacturaPendiente);
        return;
      }

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
      var urlDestino = modoEdicion ? 'App/Server/ServerActualizarMaterialPendiente.php' : 'App/Server/ServerInsertarMaterialPendiente.php';

      $.ajax({
        type: 'POST',
        url: urlDestino,
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
