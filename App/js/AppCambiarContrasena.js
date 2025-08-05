$(document).ready(function() {
  $("#ValidacionEditarContrasena").on("submit", function(e) {
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
        url: "App/Server/ServerUpdateCambiarContrasena.php",
        data: dataString,
        dataType: "json",
        success: function(response) {
          // Reescribe la Datatable y le da refresh

          alert("Tu contraseña ha sido cambiada exitosamente");
        },
      }).done(function() {
        // Redireccionar a otra pagina
        //  window.location.href = "index.php";
      });

      $("#ModalCambiarContrasena").modal("toggle");
    }
  });
});
