$(document).ready(function() {
  $("#ValidacionRecuperarPassword").on("submit", function(e) {
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
        url: "App/Server/ServerUpdateRecuperarPassword.php",
        data: dataString,
        dataType: "json",
        success: function(response) {
          // Reescribe la Datatable y le da refresh

          alert("Tu contraseña ha sido cambiada exitosamente");
        },
      }).done(function() {
        // Redireccionar a otra pagina

        window.location.href = "index.php";
      });

      $("#ModalEditarUsuarios").modal("toggle");
    }
  });

  // Este script manda el correo de recuperacion al Usuario

  $("#ValidacionMandarCorreoPassword").on("submit", function(e) {
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
        url: "App/Server/ServerMandarCorreoPassword.php",
        data: dataString,
        dataType: "json",
        success: function(response) {
          // Reescribe la Datatable y le da refresh

          alert(
            "Mandaste un correo a " +
              response.Email +
              " para poder seleccionar una nueva contraseña"
          );
        },
      }).done(function() {});
    }
  });
});
