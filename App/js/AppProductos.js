$(document).ready(function() {
  var dataTableProductosDT = $("#ProductosDT").DataTable({
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
        previous: "Anterior"
      },
      infoEmpty: "Sin productos registrados",
      infoFiltered: "(filtrado de _MAX_ registros)"
    },
    ajax: {
      url: "App/Datatables/Productos-grid-data.php",
      type: "post"
    },
    lengthChange: true,
    order: [[0, "ASC"]]
  });

  $("#ValidacionAgregarProductos").on("submit", function(e) {
    var form = $(this);

    form.parsley().validate();
    if (!form.parsley().isValid()) {
      return;
    }

    e.preventDefault();

    $.ajax({
      type: "POST",
      url: "App/Server/ServerInsertarProductos.php",
      data: form.serialize(),
      dataType: "json",
      success: function() {
        dataTableProductosDT.columns.adjust().draw();
      }
    });

    $("#ModalAgregarProductos").modal("toggle");
    form[0].reset();
  });

  $("#ValidacionEditarProductos").on("submit", function(e) {
    var form = $(this);

    form.parsley().validate();
    if (!form.parsley().isValid()) {
      return;
    }

    e.preventDefault();

    $.ajax({
      type: "POST",
      url: "App/Server/ServerUpdateProductos.php",
      data: form.serialize(),
      dataType: "json",
      success: function() {
        dataTableProductosDT.columns.adjust().draw();
      }
    });

    $("#ModalEditarProductos").modal("toggle");
  });

  $("body").on("click", "#BorrarProducto", function() {
    var PRODUCTOSID = $("input#PRODUCTOSIDBorrar").val();

    $.ajax({
      type: "POST",
      url: "App/Server/ServerBorrarProductos.php",
      data: "PRODUCTOSID=" + PRODUCTOSID,
      dataType: "json",
      success: function() {
        dataTableProductosDT.columns.adjust().draw();
      }
    });

    $("#ModalBorrarProductos").modal("toggle");
  });
});

function TomarDatosParaModalProductos(val) {
  $.ajax({
    type: "POST",
    url: "App/Server/ServerInfoProductosParaModal.php",
    dataType: "json",
    data: "ID=" + val,
    success: function(response) {
      $("input#PRODUCTOSIDEditar").val(response.PRODUCTOSID);
      $("input#SkuEditar").val(response.Sku);
      $("input#DescripcionEditar").val(response.Descripcion);
      $("input#MarcaProductosEditar").val(response.MarcaProductos);

      $("#NombreProductoBorrar").text((response.Sku || "") + " - " + (response.Descripcion || ""));
      $("input#PRODUCTOSIDBorrar").val(response.PRODUCTOSID);
    }
  });
}
