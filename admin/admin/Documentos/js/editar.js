function editarCat(id) {
    var datosFormulario = $("#editCat" + id).serialize();

    $.ajax({
        url: "includes/functions.php",
        type: "POST",
        data: datosFormulario,
        dataType: "json",
        success: function(response) {
            if (response === "correcto") {
                alert("El registro se ha actualizado correctamente");
                setTimeout(function() {
                    location.assign('categorias.php');
                }, 2000);
            } else {
                alert("Ha ocurrido un error al actualizar el registro");
            }
        },
        error: function() {
            alert("Error de comunicacion con el servidor");
        }
    });
}