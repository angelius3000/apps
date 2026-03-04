$(document).ready(function () {
  var esAdmin = parseInt($("#TicketsDT").data("esAdmin"), 10) === 1;
  var archivoAdjuntoTicket = null;

  function limpiarImagenTicket() {
    archivoAdjuntoTicket = null;
    $("#ImagenTicket").val("");
    $("#ImagenTicketPegada").val("");
    $("#PreviewImagenTicketImg").attr("src", "");
    $("#PreviewImagenTicket").hide();
  }

  function mostrarPreviewImagen(src) {
    $("#PreviewImagenTicketImg").attr("src", src);
    $("#PreviewImagenTicket").show();
  }

  function validarYAsignarImagen(file) {
    if (!file || !file.type || file.type.indexOf("image/") !== 0) {
      return;
    }

    archivoAdjuntoTicket = file;
    $("#ImagenTicketPegada").val("");

    var reader = new FileReader();
    reader.onload = function (e) {
      mostrarPreviewImagen(e.target.result);
    };
    reader.readAsDataURL(file);
  }

  var dataTableTicketsDT = $("#TicketsDT").DataTable({
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
      infoEmpty: "Sin tickets registrados",
      infoFiltered: "(filtrado de _MAX_ registros)",
    },
    processing: "Procesando...",
    loadingRecords: "Cargando...",
    ajax: {
      url: "App/Datatables/Soporte-grid-data.php",
      type: "post",
    },
    columnDefs: [{ orderable: false, targets: [8] }],
    lengthChange: true,
    order: [[7, "DESC"]],
  });

  $("#BtnSeleccionarImagenTicket, #ZonaAdjuntoTicket").on("click", function (e) {
    if (e.target.id === "BtnSeleccionarImagenTicket" || e.target.id === "ZonaAdjuntoTicket") {
      $("#ImagenTicket").trigger("click");
    }
  });

  $("#ImagenTicket").on("change", function () {
    if (this.files && this.files[0]) {
      validarYAsignarImagen(this.files[0]);
    }
  });

  $("#QuitarImagenTicket").on("click", function () {
    limpiarImagenTicket();
  });

  $("#ZonaAdjuntoTicket").on("dragover", function (e) {
    e.preventDefault();
    e.stopPropagation();
    $(this).addClass("bg-light");
  });

  $("#ZonaAdjuntoTicket").on("dragleave", function (e) {
    e.preventDefault();
    e.stopPropagation();
    $(this).removeClass("bg-light");
  });

  $("#ZonaAdjuntoTicket").on("drop", function (e) {
    e.preventDefault();
    e.stopPropagation();
    $(this).removeClass("bg-light");

    var files = e.originalEvent.dataTransfer.files;
    if (files && files.length > 0) {
      validarYAsignarImagen(files[0]);
    }
  });

  $("#Descripcion").on("paste", function (e) {
    var clipboardData = e.originalEvent.clipboardData;
    if (!clipboardData || !clipboardData.items) {
      return;
    }

    for (var i = 0; i < clipboardData.items.length; i++) {
      var item = clipboardData.items[i];
      if (item.type && item.type.indexOf("image/") === 0) {
        var blob = item.getAsFile();
        if (blob) {
          archivoAdjuntoTicket = null;
          var reader = new FileReader();
          reader.onload = function (evt) {
            var base64Data = evt.target.result;
            $("#ImagenTicketPegada").val(base64Data);
            mostrarPreviewImagen(base64Data);
          };
          reader.readAsDataURL(blob);
        }
        break;
      }
    }
  });

  $("#ValidacionAgregarTicket").on("submit", function (e) {
    var form = $(this);
    form.parsley().validate();

    if (form.parsley().isValid()) {
      e.preventDefault();
      var formData = new FormData(form[0]);

      if (archivoAdjuntoTicket) {
        formData.set("ImagenTicket", archivoAdjuntoTicket);
      }

      $.ajax({
        type: "POST",
        url: "App/Server/ServerInsertarTicket.php",
        data: formData,
        processData: false,
        contentType: false,
        dataType: "json",
        success: function () {
          dataTableTicketsDT.ajax.reload(null, false);
        },
      }).done(function () {});

      $("#ModalAgregarTicket").modal("toggle");
      form[0].reset();
      limpiarImagenTicket();
    }
  });

  $("#ValidacionEditarTicket").on("submit", function (e) {
    var form = $(this);
    form.parsley().validate();

    if (form.parsley().isValid()) {
      e.preventDefault();
      var dataString = form.serialize();

      $.ajax({
        type: "POST",
        url: "App/Server/ServerUpdateTicket.php",
        data: dataString,
        dataType: "json",
        success: function () {
          dataTableTicketsDT.ajax.reload(null, false);
        },
      }).done(function () {});

      $("#ModalEditarTicket").modal("toggle");
    }
  });

  $("body").on("click", "#CerrarTicket", function () {
    var dataString =
      "TICKETIDEditar=" +
      $("input#TICKETIDCerrar").val() +
      "&StatusEditar=Cerrado";

    $.ajax({
      type: "POST",
      url: "App/Server/ServerUpdateTicket.php",
      data: dataString,
      dataType: "json",
      success: function () {
        dataTableTicketsDT.ajax.reload(null, false);
      },
    }).done(function () {});

    $("#ModalCerrarTicket").modal("toggle");
  });

  if (!esAdmin) {
    $(".bloque-admin-edicion").hide();
  }
});

function TomarDatosParaModalTicket(val) {
  $.ajax({
    type: "POST",
    url: "App/Server/ServerInfoTicketParaModal.php",
    dataType: "json",
    data: "ID=" + val,
    success: function (response) {
      $("input#TICKETIDEditar").val(response.TICKETID);
      $("input#TituloEditar").val(response.Titulo);
      $("textarea#DescripcionEditar").val(response.Descripcion);
      $("select#PrioridadEditar").val(response.Prioridad).trigger("change");
      $("select#CategoriaEditar").val(response.Categoria).trigger("change");
      $("select#StatusEditar").val(response.STATUS).trigger("change");
      $("select#USUARIOID_ASIGNADOEditar")
        .val(response.USUARIOID_ASIGNADO || "")
        .trigger("change");

      $("input#TICKETIDCerrar").val(response.TICKETID);
      $("#FolioTicketCerrar").text(response.Folio);
    },
  });
}
