<?php  
include ('../../app/config.php');  
include ('../../admin/layout/parte1.php');  
include ('../../app/controllers/estudiantes/listado_de_estudiantes.php');  

// Obtener el estatus enviado por el formulario
$estatus = isset($_GET['estatus']) ? $_GET['estatus'] : 'activo'; // Por defecto, mostrar activos

// Obtener los estudiantes según el estatus
$data = include('../../app/controllers/estudiantes/listado_de_estudiantes.php');  
$estudiantes = $data['estudiantes'];  
$nombre_representante = $data['nombre_representante']; 

// Filtrar estudiantes por estatus
if ($estatus === 'inactivo') {
    $estudiantes_filtrados = array_filter($estudiantes, function($estudiante) {
        return strtolower($estudiante['estatus']) == 'inactivo';
    });
} else {
    $estudiantes_filtrados = array_filter($estudiantes, function($estudiante) {
        return strtolower($estudiante['estatus']) == 'activo';
    });
}

// Contar solo los estudiantes activos
$contador_estudiantes = count(array_filter($estudiantes, function($estudiante) {
    return strtolower($estudiante['estatus']) == 'activo';
}));

// Verificar si hay un mensaje en la sesión
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']); // Limpiar el mensaje después de mostrarlo
}
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
                        <h1 class="m-0">Lista de estudiantes registrados<?= $nombre_representante ? ' de ' . $nombre_representante : '' ?></h1>    
                        </div><!-- /.col -->
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="<?=APP_URL;?>/admin">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="<?=APP_URL;?>/admin/estudiantes">Estudiantes</a></li>
                                <li class="breadcrumb-item">Lista de estudiantes</li>
                            </ol>
                        </div><!-- /.col -->
                    </div><!-- /.row -->
                </div><!-- /.container-fluid -->
            </div>
            <div class="content">
                <div class="container">
                    <div class="row">
                        <br>
                        <div class="col-md-12">
                            <div class="card card-outline card-primary">
                                <div class="card-header">
                                    <div class="card-tools">
                                        <a href="../estudiantes/create.php?id_representante=<?= $id_representante ?>" class="btn btn-primary">  
                                            <i class="bi bi-plus-square"></i> Registrar nuevo estudiante  
                                        </a>
                                    </div>  
                                </div>  
                                <div class="card-body">  
                                    <!-- Título para los botones de filtro -->
                                    <h5>Filtrar por Estatus:</h5>
                                    <form method="GET" action="">
                                        <div class="row mb-3">
                                            <div class="col-md-4">
                                                <button type="submit" name="estatus" value="activo" class="btn btn-success">Activos</button>
                                                <button type="submit" name="estatus" value="inactivo" class="btn btn-danger">Inactivos</button>
                                            </div>
                                        </div>
                                    </form>

                                    <h5>Total de Estudiantes Activos: <?= $contador_estudiantes; ?></h5>

                                    <hr>

                                    <table id="example1" class="table table-striped table-bordered table-hover table-sm custom-table">  
                                        <thead>  
                                            <tr>  
                                                <th><center>#</center></th>  
                                                <th><center>Nombres y Apellidos</center></th>  
                                                <th><center>Cédula</center></th>  
                                                <th><center>Cédula Escolar</center></th>  
                                                <th><center>Fecha de Nacimiento</center></th>  
                                                <th><center>Correo Electrónico</center></th>  
                                                <th><center>Estatus</center></th>  
                                                <th><center>Acciones</center></th>  
                                            </tr>  
                                        </thead>  
                                        <tbody>  
                                        <?php  
                                        if (isset($estudiantes_filtrados) && is_array($estudiantes_filtrados) && !empty($estudiantes_filtrados)) {  
                                            $contador_estudiantes = 0;  
                                            foreach ($estudiantes_filtrados as $estudiante) {  
                                                $id_estudiante = $estudiante['id_estudiante'];  
                                                $contador_estudiantes++;  

                                                // Convertimos la fecha de nacimiento al formato DD/MM/YYYY  
                                                $fechaNacimiento = date("d/m/Y", strtotime($estudiante['fecha_nacimiento']));  

                                                // Obtener la cédula  
                                                $cedula = $estudiante['cedula'] ?? null; // Asignar null si no existe  

                                                // Formatear la cédula en el formato deseado (XX.XXX.XXX)  
                                                if ($cedula) {
                                                    $cedula_formateada = substr($cedula, 0, -6) . '.' . substr($cedula, -6, 3) . '.' . substr($cedula, -3);  
                                                } else {
                                                    $cedula_formateada = 'N/A'; // Asignar 'N/A' si no existe la cédula
                                                }
                                                
                                                // Obtener la cédula escolar  
                                                $cedula_escolar = $estudiante['cedula_escolar'] ?? 'N/A'; // Asignar 'N/A' si no existe  
                                        ?>               
                                        <tr>  
                                            <td style="text-align: center"><?= $contador_estudiantes; ?></td>  
                                            <td style="text-align: center"><?= $estudiante['nombres'] . " " . $estudiante['apellidos']; ?></td>  
                                            <td style="text-align: center"><?= $cedula_formateada; ?></td>  
                                            <td style="text-align: center"><?= $cedula_escolar; ?></td>  
                                            <td style="text-align: center"><?= $fechaNacimiento; ?></td>  
                                            <td style="text-align: center"><?= $estudiante['correo_electronico']; ?></td>  
                                            <td style="text-align: center">  
                                                <?php if (strtolower($estudiante['estatus']) == "activo") { ?>  
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
                                                    <a href="generar_carnet_individual.php?id=<?= $estudiante['id_estudiante']; ?>" class="btn btn-info btn-sm" title="Generar Carnet">
    <i class="fas fa-id-card"></i>
</a>
                                                    <a href="show.php?id=<?= $estudiante['id_estudiante']; ?>" class="btn btn-btn-sm"><i class="bi bi-eye"></i></a>  
                                                    <a href="edit.php?id=<?= $estudiante['id_estudiante']; ?>" class="btn btn-btn-sm"><i class="bi bi-pencil"></i></a>  
                                                    <?php if (strtolower($estudiante['estatus']) == "activo") { ?>  
                                                        <a href="inscribir.php?id=<?= $id_estudiante; ?>" type="button" class="btn btn-primary btn-sm"><i class="bi bi-plus-square"></i></a>  
                                                    <?php } ?>  
                                                    <form action="<?= APP_URL; ?>/app/controllers/estudiantes/delete.php"  
                                                          onsubmit="return confirmarAccion(event, '<?= $estudiante['id_estudiante']; ?>', '<?= $estudiante['estatus']; ?>')"  
                                                          method="post" id="miFormulario<?= $estudiante['id_estudiante']; ?>">  
                                                        <input type="hidden" name="id_estudiante" value="<?= $estudiante['id_estudiante']; ?>">  
                                                        <input type="hidden" name="action" id="action<?= $estudiante['id_estudiante']; ?>"  
                                                               value="<?= strtolower($estudiante['estatus']) === 'activo' ? 'inhabilitar' : 'habilitar'; ?>">  
                                                        <button type="submit" class="btn btn-btn-sm" style="border-radius: 0px 5px 5px 0px">  
                                                            <i class="bi bi-lock"></i>  
                                                            <span id="buttonText<?= $estudiante['id_estudiante']; ?>">  
                                                                <?= strtolower($estudiante['estatus']) === 'activo' ? 'Inhabilitar' : 'Habilitar'; ?>  
                                                            </span>  
                                                        </button>  
                                                    </form>  
                                                </div>  
                                            </td>  
                                        </tr>  
                                        <?php  
                                            } // Fin del foreach  
                                        } else {  
                                            echo "<tr><td colspan='8' style='text-align: center;'>No se encontraron estudiantes.</td></tr>";  
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
            
            <script>  
            function confirmarAccion(event, idEstudiante, estatus) {  
                event.preventDefault(); // Evita que se envíe el formulario hasta que se confirme la acción  

                // Convertir el estatus a minúsculas para la comparación
                const estatusLower = estatus.toLowerCase();
                const mensaje = estatusLower === 'activo' ?  
                    '¿Estás seguro de que deseas inhabilitar a este estudiante?' :  
                    '¿Estás seguro de que deseas habilitar a este estudiante?';  

                const confirmationTitle = 'Confirmar acción';  
                const actionText = estatusLower === 'activo' ? 'Inhabilitar' : 'Habilitar';  

                // Usamos SweetAlert2 para mostrar el modal de confirmación  
                Swal.fire({  
                    title: confirmationTitle,  
                    text: mensaje,  
                    icon: 'question',  
                    showDenyButton: true,  
                    confirmButtonText: actionText,  
                    confirmButtonColor: estatusLower === 'activo' ? '#a5161d' : '#28a745',  
                    denyButtonText: 'Cancelar',  
                    denyButtonColor: '#270a0a',  
                }).then((result) => {  
                    if (result.isConfirmed) {  
                        // Cambiar la acción en función del estado actual  
                        document.getElementById('action' + idEstudiante).value = (estatusLower === 'activo' ? 'inhabilitar' : 'habilitar');  
                        document.getElementById('miFormulario' + idEstudiante).submit(); // Enviar el formulario  
                    }  
                });  

                return false; // Mantener el retorno falso para evitar el envío automático  
            }  
            </script>  

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            <script>
                // Mostrar el mensaje de éxito o error si existe
                <?php if (isset($message)): ?>
                    Swal.fire({
                        icon: 'success', // Cambia a 'error' si es un mensaje de error
                        title: 'Éxito',
                        text: '<?= $message; ?>',
                        confirmButtonText: 'Aceptar'
                    });
                <?php endif; ?>
            </script>

<?php
include ('../../admin/layout/parte2.php');
include ('../../layout/mensajes.php');
?>  
<script>
    $(function () {  
        $("#example1").DataTable({
            "pageLength": 10,
            "language": {
                "emptyTable": "No hay información",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ Estudiantes",
                "infoEmpty": "Mostrando 0 a 0 de 0 Estudiantes",
                "infoFiltered": "(Filtrado de _MAX_ total Estudiantes)",
                "infoPostFix": "",
                "thousands": ",",
                "lengthMenu": "Mostrar Menú Estudiantes",
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