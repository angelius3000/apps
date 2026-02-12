$(document).ready(function() {
  var labelsGrupo = {
    aduanas: 'Personal de aduanas',
    vendedor: 'Vendedor',
    surtidor: 'Surtidor',
    almacenista: 'Almacenista'
  };

  var tablas = {};

  function recargarTabla(tipo) {
    if (tablas[tipo]) {
      tablas[tipo].ajax.reload(null, false);
    }
  }

  function recargarTodas() {
    Object.keys(tablas).forEach(function(tipo) {
      recargarTabla(tipo);
    });
  }

  $('.personal-dt').each(function() {
    var $tabla = $(this);
    var tipo = $tabla.data('tipo');

    tablas[tipo] = $tabla.DataTable({
      processing: true,
      responsive: true,
      pageLength: 25,
      ajax: {
        url: 'App/Server/ServerPersonal.php',
        type: 'POST',
        data: function(d) {
          d.action = 'list';
          d.tipo = tipo;
        },
        dataSrc: 'data'
      },
      columns: [
        {
          data: 'Nombre',
          render: function(data) {
            return $('<div>').text(data || '').html();
          }
        },
        { data: 'Badge', orderable: false, searchable: false },
        { data: 'Acciones', orderable: false, searchable: false }
      ],
      language: {
        search: 'Búsqueda:',
        lengthMenu: 'Mostrar _MENU_ filas',
        zeroRecords: 'Sin información',
        info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
        paginate: {
          first: 'Primera',
          last: 'Última',
          next: 'Siguiente',
          previous: 'Anterior'
        },
        infoEmpty: 'Sin registros',
        infoFiltered: '(filtrado de _MAX_ registros)'
      },
      order: [[0, 'asc']]
    });
  });

  $('body').on('click', '.btn-agregar-personal', function() {
    var tipo = $(this).data('tipo');
    $('#TipoPersonalAgregar').val(tipo);
    $('#NombrePersonalAgregar').val('');
    $('#ModalAgregarPersonalLabel').text('Agregar - ' + (labelsGrupo[tipo] || 'Personal'));
    $('#ModalAgregarPersonal').modal('show');
  });

  $('#FormAgregarPersonal').on('submit', function(e) {
    e.preventDefault();

    $.ajax({
      type: 'POST',
      url: 'App/Server/ServerPersonal.php',
      dataType: 'json',
      data: {
        action: 'add',
        tipo: $('#TipoPersonalAgregar').val(),
        nombre: $('#NombrePersonalAgregar').val()
      },
      success: function(response) {
        if (response.success) {
          $('#ModalAgregarPersonal').modal('hide');
          recargarTabla($('#TipoPersonalAgregar').val());
        }
      }
    });
  });

  $('body').on('click', '.btn-editar-personal', function() {
    var id = $(this).data('id');
    var tipo = $(this).data('tipo');

    $.ajax({
      type: 'POST',
      url: 'App/Server/ServerPersonal.php',
      dataType: 'json',
      data: {
        action: 'get',
        id: id
      },
      success: function(response) {
        if (!response || !response.success || !response.item) {
          return;
        }

        $('#PersonalIDEditar').val(response.item.PERSONALID);
        $('#TipoPersonalEditar').val(tipo);
        $('#NombrePersonalEditar').val(response.item.Nombre || '');
        $('#ModalEditarPersonalLabel').text('Editar - ' + (labelsGrupo[tipo] || 'Personal'));
        $('#ModalEditarPersonal').modal('show');
      }
    });
  });

  $('#FormEditarPersonal').on('submit', function(e) {
    e.preventDefault();

    $.ajax({
      type: 'POST',
      url: 'App/Server/ServerPersonal.php',
      dataType: 'json',
      data: {
        action: 'update',
        id: $('#PersonalIDEditar').val(),
        nombre: $('#NombrePersonalEditar').val()
      },
      success: function(response) {
        if (response.success) {
          $('#ModalEditarPersonal').modal('hide');
          recargarTabla($('#TipoPersonalEditar').val());
        }
      }
    });
  });

  $('body').on('click', '.btn-estado-personal', function() {
    var id = $(this).data('id');
    var tipo = $(this).data('tipo');
    var nombre = $(this).data('nombre');
    var deshabilitado = parseInt($(this).data('deshabilitado'), 10) === 1;

    $('#PersonalIDEstado').val(id);
    $('#TipoPersonalEstado').val(tipo);
    $('#TextoCambiarEstadoPersonal').text((deshabilitado ? '¿Habilitar a ' : '¿Inhabilitar a ') + nombre + '?');
    $('#BtnConfirmarCambiarEstadoPersonal').text(deshabilitado ? 'Habilitar' : 'Inhabilitar');
    $('#ModalCambiarEstadoPersonal').modal('show');
  });

  $('#BtnConfirmarCambiarEstadoPersonal').on('click', function() {
    var tipo = $('#TipoPersonalEstado').val();

    $.ajax({
      type: 'POST',
      url: 'App/Server/ServerPersonal.php',
      dataType: 'json',
      data: {
        action: 'toggle',
        id: $('#PersonalIDEstado').val()
      },
      success: function(response) {
        if (response.success) {
          $('#ModalCambiarEstadoPersonal').modal('hide');
          recargarTabla(tipo);
        }
      }
    });
  });

  $('body').on('click', '.btn-eliminar-personal', function() {
    var id = $(this).data('id');
    var tipo = $(this).data('tipo');
    var nombre = $(this).data('nombre');

    $('#PersonalIDEliminar').val(id);
    $('#TipoPersonalEliminar').val(tipo);
    $('#TextoEliminarPersonal').text('¿Eliminar definitivamente a ' + nombre + '?');
    $('#ModalEliminarPersonal').modal('show');
  });

  $('#BtnConfirmarEliminarPersonal').on('click', function() {
    var tipo = $('#TipoPersonalEliminar').val();

    $.ajax({
      type: 'POST',
      url: 'App/Server/ServerPersonal.php',
      dataType: 'json',
      data: {
        action: 'delete',
        id: $('#PersonalIDEliminar').val()
      },
      success: function(response) {
        if (response.success) {
          $('#ModalEliminarPersonal').modal('hide');
          recargarTabla(tipo);
        }
      }
    });
  });

  recargarTodas();
});
