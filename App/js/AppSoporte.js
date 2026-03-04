$(document).ready(function () {
  var esAdmin = parseInt($("#TicketsDT").data("esAdmin"), 10) === 1;

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

  $("#ValidacionAgregarTicket").on("submit", function (e) {
    var form = $(this);
    form.parsley().validate();

    if (form.parsley().isValid()) {
      e.preventDefault();
      var dataString = form.serialize();

      $.ajax({
        type: "POST",
        url: "App/Server/ServerInsertarTicket.php",
        data: dataString,
        dataType: "json",
        success: function () {
          dataTableTicketsDT.ajax.reload(null, false);
        },
      }).done(function () {});

      $("#ModalAgregarTicket").modal("toggle");
      form[0].reset();
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
