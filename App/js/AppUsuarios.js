$(document).ready(function() {

  $('#ModalAgregarUsuarios').on('shown.bs.modal', function () {
    $('#CLIENTEID').select2({
      dropdownParent: $('#ModalAgregarUsuarios'), // Ajuste importante
      placeholder: 'Selecciona cliente',
      allowClear: true,
      width: '100%' // Asegura que ocupe todo el ancho del contenedor
    });
  });
  $('#ModalEditarUsuarios').on('shown.bs.modal', function () {
    $('#CLIENTEIDEditar').select2({
      dropdownParent: $('#ModalEditarUsuarios'), // Ajuste importante
      placeholder: 'Selecciona cliente',
      allowClear: true,
      width: '100%' // Asegura que ocupe todo el ancho del contenedor
    });
  });
  
  var dataTableUsuarioDT = $("#UsuariosDT").DataTable({
    // Tabla General de Usuarios

    dom: "Bifrtip",
    buttons: ["excelHtml5", "pdfHtml5", "pageLength"],
    processing: true,
    serverSide: true,
    responsive: true,
    pageLength: 100,
    language: {
      search: "Búsqueda:",
      lengthMenu: "Mostrar _MENU_ filas",
      zeroRecords: "Sin información",
      info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
      paginate: {
        first: "Primera",
        last: "Última",
        next: "Siguiente",
        previous: "Anterior",
      },
      infoEmpty: "Sin rúbricas registradas",
      infoFiltered: "(filtrado de _MAX_ registros)",
    },
    processing: "Procesando...",
    loadingRecords: "Cargando...",
    ajax: {
      url: "App/Datatables/Usuarios-grid-data.php", // json datasource
      type: "post",
    },

    columnDefs: [{ orderable: false, targets: [4, 5] }],

    lengthChange: true, // añade la lista desplegable
    order: [[0, "DESC"]],
  });

  // Para Agregar Usuarios
  $("#ValidacionAgregarUsuario").on("submit", function(e) {
    var form = $(this);

    form.parsley().validate();

    if (form.parsley().isValid()) {
      //prevent Default functionality
      e.preventDefault();

      // data string
      var dataString = form.serialize();

      console.log(dataString);

      // ajax
      $.ajax({
        //async: false,
        type: "POST",
        url: "App/Server/ServerInsertarUsuarios.php",
        data: dataString,
        dataType: "json",
        success: function(response) {
          // Reescribe la Datatable y le da refresh

          console.log(response.USUARIOID);

          dataTableUsuarioDT.columns.adjust().draw();
        },
      }).done(function() {});

      $("#ModalAgregarUsuarios").modal("toggle");
    }
  });

  $("#ValidacionEditarUsuario").on("submit", function(e) {
    var form = $(this);

    form.parsley().validate();

    if (form.parsley().isValid()) {
      //prevent Default functionality
      e.preventDefault();

      // data string
      var dataString = form.serialize();

      console.log(dataString);

      // ajax
      $.ajax({
        //async: false,
        type: "POST",
        url: "App/Server/ServerUpdateUsuarios.php",
        data: dataString,
        dataType: "json",
        success: function(response) {
          // Reescribe la Datatable y le da refresh

          console.log(response.USUARIOID);

          dataTableUsuarioDT.columns.adjust().draw();
        },
      }).done(function() {});

      $("#ModalEditarUsuarios").modal("toggle");
    }
  });

  // Deshabilitar Usuario

  $("body").on("click", "#DeshabilitarUsuario", function() {
    var USUARIOID = $("input#USUARIOIDDeshabilitar").val();

    var dataString = "USUARIOID=" + USUARIOID;

    console.log(dataString);

    // ajax
    $.ajax({
      //async: false,
      type: "POST",
      url: "App/Server/ServerDeshabilitarUsuarios.php",
      data: dataString,
      dataType: "json",
      success: function(response) {
        dataTableUsuarioDT.columns.adjust().draw();
      },
    }).done(function() {});

    $("#ModalDeshabilitarUsuarios").modal("toggle");
  });

  $(document).on("change", "#TIPODEUSUARIOID", function() {
    var TipoDeUsuario = $(this).val();

    if (TipoDeUsuario == 4) {
      $("#ClientesEscondidos").show();

      // Ponerle el parametro "required al select de Clientes"
      $("select#CLIENTEID").attr("required", true);
    } else {
      $("#ClientesEscondidos").hide();
      $("select#CLIENTEID").attr("required", false);
    }
  });
  $(document).on("change", "#TIPODEUSUARIOIDEditar", function() {
    var TipoDeUsuario = $(this).val();

    if (TipoDeUsuario == 4) {
      $("#ClientesEscondidosEditar").show();

      // Ponerle el parametro "required al select de Clientes"
      $("select#CLIENTEIDEditar").attr("required", true);
    } else {
      $("#ClientesEscondidosEditar").hide();
      $("select#CLIENTEIDEditar").attr("required", false);
    }
  });
});

function TomarDatosParaModalUsuarios(val) {
  $.ajax({
    type: "POST",
    url: "App/Server/ServerInfoUsuariosParaModal.php",
    dataType: "json",
    data: "ID=" + val,
    success: function(response) {
      // Para el Modal de editar
      $("input#PrimerNombreEditar").val(response.PrimerNombre);
      $("input#SegundoNombreEditar").val(response.SegundoNombre);
      $("input#ApellidoPaternoEditar").val(response.ApellidoPaterno);
      $("input#ApellidoMaternoEditar").val(response.ApellidoMaterno);
      $("input#emailEditar").val(response.Email);
      $("input#TelefonoEditar").val(response.Telefono);

      $("select#TIPODEUSUARIOIDEditar").val(response.TIPODEUSUARIOID);
      $("select#CLIENTEIDEditar").val(response.CLIENTEID);
      $("input#USUARIOIDEditar").val(response.USUARIOID);

      //Para modal de Borrar

      $("#NombreUsuarioDeshabilitar").text(
        response.PrimerNombre +
          " " +
          response.SegundoNombre +
          " " +
          response.ApellidoPaterno +
          " " +
          response.ApellidoMaterno
      );

      $("input#USUARIOIDDeshabilitar").val(response.USUARIOID);
    },
  });
}
