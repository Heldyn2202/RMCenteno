<?php  
include ('../../app/config.php');  
include ('../../admin/layout/parte1.php');  
include ('../../app/controllers/representantes/listado_de_representantes.php');  
?>  

<!-- Content Wrapper. Contains page content -->  
<div class="content-wrapper">  
    <br>  
    <div class="content">   
        <div class="container">  
            <div class="content-header">  
                <div class="container-fluid">  
                    <div class="row mb-2">  
                        <div class="col-sm-6">  
                            <h1 class="m-0">Listado de Representantes</h1>  
                        </div><!-- /.col -->  
                        <div class="col-sm-6">  
                            <ol class="breadcrumb float-sm-right">  
                                <li class="breadcrumb-item"><a href="#">Inicio</a></li>  
                                <li class="breadcrumb-item"><a href="<?=APP_URL;?>/admin/representantes">Representantes</a></li>  
                                <li class="breadcrumb-item active">Listado de Representantes</li>  
                            </ol>  
                        </div><!-- /.col -->  
                    </div><!-- /.row -->  
                </div><!-- /.container-fluid -->  
            </div>  
            <br>  
            <div class="row">  
                <div class="col-md-12">  
                    <div class="card card-outline card-primary">  
                        <div class="card-header">  
                            <div class="card-tools">  
                                <a href="create.php" class="btn btn-info"><i class="bi bi-plus-square"></i> Registrar Nuevo Representante</a>  
                            </div>  
                        </div>  
                        <div class="card-body">  
                            <table id="example1" class="table table-striped table-bordered table-hover table-sm">  
                                <thead>  
                                    <tr>  
                                        <th><center>#</center></th>  
                                        <th><center>Nombres y Apellidos</center></th>  
                                        <th><center>Cédula</center></th>  
                                        <th><center>Fecha de Nacimiento</center></th>  
                                        <th><center>Correo Electrónico</center></th>  
                                        <th><center>Estatus</center></th>  
                                        <th><center>Acciones</center></th>  
                                    </tr>  
                                </thead>  
                                <tbody>  
                                <?php  
                                if (isset($representantes) && is_array($representantes) && !empty($representantes)) {  
                                    $contador_representantes = 0;  
                                    foreach ($representantes as $representante) {  
                                        $id_representante = $representante['id_representante'];  
                                        $contador_representantes++;  

                                        // Convertimos la fecha de nacimiento al formato DD/MM/YYYY  
                                        $fechaNacimiento = date("d/m/Y", strtotime($representante['fecha_nacimiento']));  

                                        // Formatear la cédula en el formato deseado (XX.XXX.XXX)  
                                        $cedula = $representante['cedula'];  
                                        $cedula_formateada = substr($cedula, 0, -6) . '.' . substr($cedula, -6, 3) . '.' . substr($cedula, -3);  
                                        ?>  
                                        <tr>  
                                            <td style="text-align: center"><?= $contador_representantes; ?></td>  
                                            <td style="text-align: center"><?= $representante['nombres'] . " " . $representante['apellidos']; ?></td>  
                                            <td style="text-align: center"><?= $cedula_formateada; ?></td>  
                                            <td style="text-align: center"><?= $fechaNacimiento; ?></td>  
                                            <td style="text-align: center"><?= $representante['correo_electronico']; ?></td>  
                                            <td style="text-align: center">  
                                                <?php if (strtolower($representante['estatus']) == "activo") { ?>  
                                                    <button class="btn btn-success btn-sm" style="border-radius: 20px" title="Activo">  
                                                        ACTIVO  
                                                    </button>  
                                                <?php } else { ?>  
                                                    <button class="btn btn-danger btn-sm" style="border-radius: 20px" title="Inactivo">  
                                                        INACTIVO  
                                                    </button>  
                                                <?php } ?>  
                                            </td>  
                                            <td>  
                                                <div class="btn-group" role="group" aria-label="Basic example">  
                                                    <a href="show.php?id=<?= $representante['id_representante']; ?>" class="btn btn-btn-sm"><i class="bi bi-eye"></i></a>  
                                                    <a href="edit.php?id=<?= $representante['id_representante']; ?>" class="btn btn-btn-sm"><i class="bi bi-pencil"></i></a>  
                                                    <?php if ($representante['estatus'] === 'Activo') { ?>  
                                                    <a href="javascript:void(0);" class="btn btn-info" onclick="redirectToListaEstudiantes(<?= $representante['id_representante']; ?>, '<?= $representante['nombres'] . ' ' . $representante['apellidos']; ?>');">  
                                                    <i class="bi bi-plus-square"></i>  
                                                    </a>  
                                                    <?php } ?>
                                                    <form action="<?= APP_URL; ?>/app/controllers/representantes/delete.php"   
                                                          onsubmit="return confirmarAccion(event, '<?= $representante['id_representante']; ?>', '<?= $representante['estatus']; ?>')"   
                                                          method="post" id="miFormulario<?= $representante['id_representante']; ?>">  
                                                        <input type="hidden" name="id_representante" value="<?= $representante['id_representante']; ?>">  
                                                        <input type="hidden" name="action" id="action<?= $representante['id_representante']; ?>"   
                                                               value="<?= $representante['estatus'] === 'Activo' ? 'Inhabilitar' : 'Habilitar'; ?>">  
                                                        <button type="submit" class="btn btn-btn-sm"   
                                                                style="border-radius: 0px 5px 5px 0px">  
                                                            <i class="bi bi-lock"></i>  
                                                            <span id="buttonText<?= $representante['id_representante']; ?>">  
                                                                <?= $representante['estatus'] === 'Activo' ? 'Inhabilitar' : 'Habilitar'; ?>  
                                                            </span>  
                                                        </button>  
                                                    </form>
                                                </div>  
                                            </td>  
                                        </tr>  
                                        <?php  
                                    } // Fin del foreach  
                                } else {  
                                    echo "<tr><td colspan='7' style='text-align: center;'>No se encontraron representantes.</td></tr>";  
                                }  
                                ?>  
                                </tbody>  
                            </table>  
                        </div>  
                    </div>  
                </div>  
            </div>  
        </div>  
    </div>  
</div>  

<script>  
function confirmarAccion(event, idRepresentante, estatus) {  
    event.preventDefault(); // Evita que se envíe el formulario hasta que se confirme la acción  

    const mensaje = estatus === 'Activo' ?   
        '¿Estás seguro de que deseas inhabilitar a este representante?' :   
        '¿Estás seguro de que deseas habilitar a este representante?';  
    
    const confirmationTitle = 'Confirmar acción';  
    const actionText = estatus === 'Activo' ? 'Inhabilitar' : 'Habilitar';  

    // Usamos SweetAlert2 para mostrar el modal de confirmación  
    Swal.fire({  
        title: confirmationTitle,  
        text: mensaje,  
        icon: 'question',  
        showDenyButton: true,  
        confirmButtonText: actionText,  
        confirmButtonColor: estatus === 'Activo' ? '#a5161d' : '#28a745',  
        denyButtonText: 'Cancelar',  
        denyButtonColor: '#270a0a',  
    }).then((result) => {  
        if (result.isConfirmed) {  
            // Cambiar la acción en función del estado actual  
            document.getElementById('action' + idRepresentante).value = (estatus === 'Activo' ? 'Inhabilitar' : 'Habilitar');  
            document.getElementById('miFormulario' + idRepresentante).submit(); // Enviar el formulario   
        }  
    });  
    
    return false; // Mantener el retorno falso para evitar el envío automático  
}  
</script>
<script> 

function redirectToListaEstudiantes(idRepresentante, nombreRepresentante) {  
    // Construir la URL con los parámetros  
    var url = '<?= APP_URL; ?>/admin/estudiantes/lista_de_estudiante.php?id_representante=' + idRepresentante;  

    // Crear un formulario temporal para enviar los datos  
    var form = document.createElement('form');  
    form.method = 'POST';  
    form.action = url;  

    // Agregar los campos al formulario  
    var inputIdRepresentante = document.createElement('input');  
    inputIdRepresentante.type = 'hidden';  
    inputIdRepresentante.name = 'id_representante';  
    inputIdRepresentante.value = idRepresentante;  
    form.appendChild(inputIdRepresentante);  

    var inputNombreRepresentante = document.createElement('input');  
    inputNombreRepresentante.type = 'hidden';  
    inputNombreRepresentante.name = 'nombre_representante';  
    inputNombreRepresentante.value = nombreRepresentante;  
    form.appendChild(inputNombreRepresentante);  

    // Agregar el formulario al documento y enviar  
    document.body.appendChild(form);  
    form.submit();  
}  

// Función para redirigir a la página de registro de estudiantes con el id_representante  
function redirectToRegistroEstudiante(idRepresentante) {  
    // Construir la URL con el parámetro id_representante  
    var url = '<?= APP_URL; ?>/admin/estudiantes/create.php?id_representante=' + idRepresentante;  

    // Redirigir a la página de registro de estudiantes  
    window.location.href = url;  
}
</script>  

<?php  
include ('../../admin/layout/parte2.php');  
include ('../../layout/mensajes.php');  
?>  

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script> <!-- Asegúrate de tener SweetAlert2 incluido -->
<script>  
    $(function () {  
        $("#example1").DataTable({
            "pageLength": 15,
            "language": {
                "emptyTable": "No hay información",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ Representantes",
                "infoEmpty": "Mostrando 0 a 0 de 0 Estudiantes",
                "infoFiltered": "(Filtrado de _MAX_ total Representantes)",
                "infoPostFix": "",
                "thousands": ",",
                "lengthMenu": "Mostrar Menú Representantes",
                "loadingRecords": "Cargando...",
                "processing": "Procesando...",
                "search": "Buscador:",
                "zeroRecords": "Sin resultados encontrados",
                "paginate": {
                    "first": "Primero",
                    "last": "Ultimo",
                    "next": "Siguiente",
                    "previous": "Anterior"
                }  
            },  
            "responsive": true,  
            "lengthChange": true,  
            "autoWidth": false,  
            buttons: [
    {
      extend: 'excelHtml5',
      text: '<i class="fas fa-file-excel"></i> ',
      titleAttr: 'Exportar a Excel',
      className: 'btn btn-success',
    },
    {
      extend: 'pdfHtml5',
      text: '<i class="fas fa-file-pdf"></i> ',
      titleAttr: 'Exportar a PDF',
      className: 'btn btn-danger',
      customize: function (doc) {  
                        // Estilo del título  
                        doc.styles.title = {  
                            fontSize: 18,  
                            bold: true,  
                            alignment: 'center',  
                            margin: [0, 0, 0, 20]  
                        };  

                        // Color de la fila de subtítulos  
                        doc.styles.tableHeader = {  
                            fillColor: '#f4d03f', // Color del encabezado  
                            fontSize: 12,  
                            color: 'black'  
                        };  

                        // Asegurarse de que todas las líneas tengan el mismo tamaño  
                        doc.content[1].layout = {  
                            hLineWidth: function () { return 1; },  
                            vLineWidth: function () { return 1; },  
                            hLineColor: function () { return '#000'; },  
                            vLineColor: function () { return '#000'; },  
                            paddingLeft: function () { return 4; },  
                            paddingRight: function () { return 4; }  
                        };  


                        // Filtrar acciones antes de exportar  
                        // Supongamos que las acciones son la última fila en el contenido  
                        // Eliminar la columna de acciones de los datos exportados  
                        const originalData = doc.content[1].table.body; // Accedemos a la tabla  
                        originalData.forEach((row) => {  
                            row.splice(-1, 1); // Eliminar la última columna (acciones)  
                        });  

                        // Agregar fecha de creación en la parte inferior izquierda  
                        var fechaCreacion = 'Fecha de creación: ' + new Date().toLocaleDateString();  
                        doc.content.push({  
                            text: fechaCreacion,  
                            alignment: 'left',  
                            margin: [0, 20, 0, 0]  
                        });  

                        // Espacio para el nombre del encargado en la parte inferior derecha  
                        doc.content.push({  
                            text: 'Encargado de exportar: ____________________',  
                            alignment: 'right',  
                            margin: [0, 40, 0, 0]  
                        });  
                    }  
    },
    {
      extend: 'print',
      text: '<i class="fa fa-print"></i> ',
      titleAttr: 'Imprimir',
      className: 'btn btn-info',
    },
  ],
        }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');  
    });  
</script>