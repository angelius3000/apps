$(document).ready(function() {
  var tooltipTriggerList = [].slice.call(
    document.querySelectorAll('[data-bs-toggle="tooltip"]')
  );
  var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });

  $('#ModalAgregarClientes').on('shown.bs.modal', function () {
    $('#CLIENTEID').select2({
      dropdownParent: $('#ModalAgregarClientes'), // Ajuste importante
      placeholder: 'Selecciona cliente',
      allowClear: true,
      width: '100%' // Asegura que ocupe todo el ancho del contenedor
    });
  });

  $("body").tooltip({ selector: '[data-toggle="tooltip"]' });

  var dataTableClientesDT = $("#ClientesDT").DataTable({
    // Tabla General de Usuarios

    dom: "Bifrtip",
    buttons: ["excelHtml5", "pdfHtml5", "pageLength"],
    processing: true,
    serverSide: true,
    responsive: true,
    pageLength: 100,
    columnDefs: [
      { className: "text-end NumerosSIAN", targets: [0] }, // Alinear al centro las columnas 1 y 2
    ],
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
      url: "App/Datatables/Clientes-grid-data.php", // json datasource
      type: "post",
    },
    lengthChange: true, // añade la lista desplegable
    order: [[1, "ASC"]],
  });

  // Para Agregar Usuarios
  $("#ValidacionAgregarClientes").on("submit", function(e) {
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
        url: "App/Server/ServerInsertarClientes.php",
        data: dataString,
        dataType: "json",
        success: function(response) {
          // Reescribe la Datatable y le da refresh

          console.log(response.CLIENTEID);

          dataTableClientesDT.columns.adjust().draw();
        },
      }).done(function() {});

      $("#ModalAgregarClientes").modal("toggle");
    }
  });

  $("#ValidacionEditarClientes").on("submit", function(e) {
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
        url: "App/Server/ServerUpdateClientes.php",
        data: dataString,
        dataType: "json",
        success: function(response) {
          // Reescribe la Datatable y le da refresh

          dataTableClientesDT.columns.adjust().draw();
        },
      }).done(function() {});

      $("#ModalEditarClientes").modal("toggle");
    }
  });

  // Deshabilitar Usuario

  $("body").on("click", "#BorrarCliente", function() {
    var CLIENTEID = $("input#CLIENTEIDDeshabilitar").val();

    var dataString = "CLIENTEID=" + CLIENTEID;

    console.log(dataString);

    // ajax
    $.ajax({
      //async: false,
      type: "POST",
      url: "App/Server/ServerBorrarClientes.php",
      data: dataString,
      dataType: "json",
      success: function(response) {
        dataTableClientesDT.columns.adjust().draw();
      },
    }).done(function() {});

    $("#ModalDeshabilitarClientes").modal("toggle");
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

  // Para que los clientes no se puedan clonar , mandamos el pedido para ver si existen

  $("#EmailCliente, #EmailClienteEditar").on("keyup", function() {
    var ValorEmail = $(this).val();

    // ajax
    $.ajax({
      //async: false,
      type: "POST",
      url: "App/Server/ServerInfoClientesChecarEmailSiExiste.php",
      data: "EmailCliente=" + ValorEmail,
      dataType: "json",
      success: function(response) {
        // Reescribe la Datatable y le da refresh

        if (response.NombreCliente != null) {
          // Mandar el modal de que ya existe el email

          $("#ModalYaExiste").modal("show");

          // Quitamos el modal que genero el email

          $("#ModalAgregarClientes").modal("hide");

          // Mandamos la informacion al nuevo modal

          $("#NumeroDeClienteSIANYaExiste").text(response.CLIENTESIAN);
          $("#NombreClienteYaExiste").text(response.NombreCliente);
          $("#EmailClienteYaExiste").text(response.EmailCliente);
          $("#TelefonoClienteYaExiste").text(response.TelefonoCliente);
          $("#NombreContactoYaExiste").text(response.NombreContacto);
          $("#DireccionClienteYaExiste").text(response.DireccionCliente);
          $("#ColoniaClienteYaExiste").text(response.ColoniaCliente);
          $("#CiudadClienteYaExiste").text(response.CiudadCliente);
          $("#EstadoClienteYaExiste").text(response.EstadoCliente);
        }
      },
    }).done(function() {});
  });

  // Para que los clientes no se puedan clonar , en SIAN

  var typingTimer; // Timer identifier
  var doneTypingInterval = 1000; // Tiempo en milisegundos (1 segundo)
  var $input = $("#CLIENTESIAN, #CLIENTESIANEditar");
  var ValorClienteSIAN;

  // Evento keyup en el input
  $input.on("keyup", function() {
    ValorClienteSIAN = $(this).val();

    clearTimeout(typingTimer);
    typingTimer = setTimeout(doneTyping, doneTypingInterval);
  });

  // Evento keydown en el input (opcional, para cancelar el temporizador si se vuelve a escribir antes de que termine)
  $input.on("keydown", function() {
    clearTimeout(typingTimer);
  });

  // Evento blur en el input
  $input.on("blur", function() {
    clearTimeout(typingTimer);
    doneTyping();
  });

  // Función que se llama cuando el usuario deja de escribir
  function doneTyping() {
    if (ValorClienteSIAN !== "" && ValorClienteSIAN !== "0") {
      $.ajax({
        //async: false,
        type: "POST",
        url: "App/Server/ServerInfoClientesChecarSIANSiExiste.php",
        data: "CLIENTESIAN=" + ValorClienteSIAN,
        dataType: "json",
        success: function(response) {
          // Reescribe la Datatable y le da refresh

          if (response.NombreCliente != null) {
            // Mandar el modal de que ya existe el email

            $("#ModalYaExiste").modal("show");

            // Quitamos el modal que genero el email

            $("#ModalAgregarClientes").modal("hide");

            // Mandamos la informacion al nuevo modal

            $("#NumeroDeClienteSIANYaExiste").text(response.CLIENTESIAN);
            $("#NombreClienteYaExiste").text(response.NombreCliente);
            $("#EmailClienteYaExiste").text(response.EmailCliente);
            $("#TelefonoClienteYaExiste").text(response.TelefonoCliente);
            $("#NombreContactoYaExiste").text(response.NombreContacto);
            $("#DireccionClienteYaExiste").text(response.DireccionCliente);
            $("#ColoniaClienteYaExiste").text(response.ColoniaCliente);
            $("#CiudadClienteYaExiste").text(response.CiudadCliente);
            $("#EstadoClienteYaExiste").text(response.EstadoCliente);
          }
        },
      }).done(function() {});
    }
  }

  // Disparo el modal #ModalAgregarClientes cuano cierro #ModalEmailYaExiste

  $("#ModalYaExiste").on("hidden.bs.modal", function() {
    $("#ModalAgregarClientes").modal("show");

    // Limpia la forma del modal

    $("#ValidacionAgregarClientes")[0].reset();
  });
});

function TomarDatosParaModalClientes(val) {
  $.ajax({
    type: "POST",
    url: "App/Server/ServerInfoClientesParaModal.php",
    dataType: "json",
    data: "ID=" + val,
    success: function(response) {
      // Para el Modal de editar

      $("input#CLIENTEIDEditar").val(response.CLIENTEID);

      $("input#CLIENTESIANEditar").val(response.CLIENTESIAN);
      $("input#CLCSIANEditar").val(response.CLCSIAN);

      $("input#NombreClienteEditar").val(response.NombreCliente);
      $("input#EmailClienteEditar").val(response.EmailCliente);
      $("input#TelefonoClienteEditar").val(response.TelefonoCliente);
      $("input#NombreContactoEditar").val(response.NombreContacto);
      $("input#DireccionClienteEditar").val(response.DireccionCliente);
      $("input#ColoniaClienteEditar").val(response.ColoniaCliente);
      $("input#CiudadClienteEditar").val(response.CiudadCliente);
      $("input#EstadoClienteEditar").val(response.EstadoCliente);

      //Para modal de Borrar

      $("#NombreClienteBorrar").text(
        response.CLIENTESIAN + " " + response.NombreCliente
      );

      $("input#CLIENTEIDDeshabilitar").val(response.CLIENTEID);
    },
  });
}
