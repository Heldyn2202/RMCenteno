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
                        <h1 class="m-0 text-dark">Lista de estudiantes registrados<?= $nombre_representante ? ' de ' . $nombre_representante : '' ?></h1>    
                        </div><!-- /.col -->
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="<?=APP_URL;?>/admin" class="text-info">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="<?=APP_URL;?>/admin/estudiantes" class="text-info">Estudiantes</a></li>
                                <li class="breadcrumb-item active">Lista de estudiantes</li>
                            </ol>
                        </div><!-- /.col -->
                    </div><!-- /.row -->
                </div><!-- /.container-fluid -->
            </div>
            
            <!-- Filtro de estudiantes -->
            <div class="card card-info shadow-sm border-0 mb-4">
                <div class="card-header py-2">
                    <h5 class="m-0"><i class="fas fa-filter mr-2"></i>Filtros de Búsqueda</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="">
                        <div class="row align-items-end">
                            <div class="form-group col-md-4 mb-0">
                                <label for="estatus" class="form-label small font-weight-bold text-muted">Estatus</label>
                                <select name="estatus" id="estatus" class="form-control select2">
                                    <option value="activo" <?= $estatus === 'activo' ? 'selected' : '' ?>>Activos</option>
                                    <option value="inactivo" <?= $estatus === 'inactivo' ? 'selected' : '' ?>>Inactivos</option>
                                </select>
                            </div>
                            <div class="form-group col-md-2 mb-0">
                                <label class="form-label small font-weight-bold text-muted d-block">&nbsp;</label>
                                <button type="submit" class="btn btn-info btn-block shadow-sm">
                                    <i class="fa fa-filter mr-1"></i> Filtrar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="content">
                <div class="container">
                    <div class="row">
                        <br>
                        <div class="col-md-12">
                            <div class="card card-outline card-info shadow border-0">
                                <div class="card-header bg-white border-bottom-0 py-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h5 class="m-0 text-dark">
                                                <i class="fas fa-users mr-2"></i>
                                                Estudiantes Registrados
                                            </h5>
                                        </div>
                                        <div class="card-tools">
                                            <a href="../estudiantes/create.php?id_representante=<?= $id_representante ?>" class="btn btn-info shadow-sm">  
                                                <i class="fas fa-plus mr-1"></i> Registrar nuevo estudiante  
                                            </a>
                                        </div>  
                                    </div>
                                </div>  
                                <div class="card-body pt-0 pb-2 px-3">
                                    <div class="table-responsive">
                                        <table id="example1" class="table table-hover table-striped">  
                                            <colgroup>
                                                <col width="5%">
                                                <col width="25%">
                                                <col width="15%">
                                                <col width="15%">
                                                <col width="15%">
                                                <col width="10%">
                                                <col width="15%">
                                            </colgroup>
                                            <thead class="thead-light">  
                                                <tr>  
                                                    <th class="text-center">N°</th>  
                                                    <th class="text-center">Nombres y Apellidos</th>  
                                                    <th class="text-center">Cédula</th>  
                                                    <th class="text-center">Cédula Escolar</th>  
                                                    <th class="text-center">Fecha de Nacimiento</th>  
                                                    <th class="text-center">Estatus</th>  
                                                    <th class="text-center">Acciones</th>  
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
                                                <td class="text-center align-middle">
                                                    <span class="font-weight-bold text-dark"><?= $contador_estudiantes; ?></span>
                                                </td>  
                                                <td class="align-middle">
                                                    <div class="d-flex align-items-center">
                                                        <div class="img-circle bg-info text-white d-flex align-items-center justify-content-center mr-3 shadow-sm" 
                                                             style="width: 40px; height: 40px; font-size: 0.9rem; font-weight: bold;">
                                                            <?= strtoupper(substr($estudiante['nombres'] ?? '', 0, 1) . substr($estudiante['apellidos'] ?? '', 0, 1)) ?>
                                                        </div>
                                                        <div>
                                                            <b class="text-dark"><?= $estudiante['nombres'] . " " . $estudiante['apellidos']; ?></b>
                                                        </div>
                                                    </div>
                                                </td>  
                                                <td class="text-center align-middle">
                                                    <span class="font-weight-bold text-dark"><?= $cedula_formateada; ?></span>
                                                </td>  
                                                <td class="text-center align-middle">
                                                    <span class="font-weight-bold text-dark"><?= $cedula_escolar; ?></span>
                                                </td>  
                                                <td class="text-center align-middle">
                                                    <span class="font-weight-bold text-dark"><?= $fechaNacimiento; ?></span>
                                                </td>   
                                                <td class="text-center align-middle">  
                                                    <?php if (strtolower($estudiante['estatus']) == "activo") { ?>  
                                                        <span class="badge badge-success p-2">ACTIVO</span>  
                                                    <?php } else { ?>  
                                                        <span class="badge badge-danger p-2">INACTIVO</span>  
                                                    <?php } ?>  
                                                </td>  
                                                <td class="text-center align-middle">    
                                                    <div class="btn-group btn-group-sm shadow-sm">
                                                        <!-- Botón Generar Carnet con Modal -->
                                                        <button onclick="abrirModalCarnet(<?= $estudiante['id_estudiante']; ?>)" 
                                                                class="btn btn-info" 
                                                                title="Generar Carnet">
                                                            <i class="fas fa-id-card"></i>
                                                        </button>

                                                        <?php if (strtolower($estudiante['estatus']) == "activo") { ?>  
                                                        <a href="inscribir.php?id=<?= $id_estudiante; ?>"title="Inscribir Estudiante" type="button" class="btn btn-info btn-sm"><i class="bi bi-plus-square"></i></a>  
                                                        <?php } ?>  
                                                        
                                                        <a href="show.php?id=<?= $estudiante['id_estudiante']; ?>" 
                                                           class="btn btn-info" 
                                                           title="Ver Detalles">
                                                            <i class="fas fa-eye"></i>
                                                        </a>  
                                                        
                                                        <a href="edit.php?id=<?= $estudiante['id_estudiante']; ?>" 
                                                           class="btn btn-info" 
                                                           title="Editar">
                                                            <i class="fas fa-edit"></i>
                                                        </a>  
                                                        
                                                        <button class="btn <?= strtolower($estudiante['estatus']) === 'activo' ? 'btn-info' : 'btn-info'; ?>" 
                                                                onclick="confirmarAccion(event, '<?= $estudiante['id_estudiante']; ?>', '<?= $estudiante['estatus']; ?>')"  
                                                                title="<?= strtolower($estudiante['estatus']) === 'activo' ? 'Inhabilitar' : 'Habilitar'; ?>">  
                                                            <?= strtolower($estudiante['estatus']) === 'activo' ? '<i class="fas fa-user-times"></i>' : '<i class="fas fa-user-plus"></i>'; ?>  
                                                        </button>  
                                                    </div>
                                                </td>
                                            </tr>  
                                            <?php  
                                                } // Fin del foreach  
                                            } else {  
                                                echo "<tr><td colspan='7' class='text-center py-4 text-muted'><i class='fas fa-user-graduate fa-2x mb-3'></i><br>No se encontraron estudiantes.</td></tr>";  
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
            
            <!-- Modal para generar carnet -->
            <div class="modal fade" id="modalCarnet" tabindex="-1" aria-labelledby="modalCarnetLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header bg-info text-white">
                            <h5 class="modal-title" id="modalCarnetLabel"><i class='fas fa-id-card mr-2'></i> Generar Carnet Estudiantil</h5>
                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <form id="formCarnet" method="post" action="generar_carnet_pdf.php" target="_blank">
                                <input type="hidden" name="id_estudiante" id="id_estudiante_carnet">
                                <div class="form-group">
                                    <label for="fecha_vencimiento">Fecha de Vencimiento:</label>
                                    <input type="date" class="form-control" id="fecha_vencimiento" name="fecha_vencimiento" required>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-info" onclick="generarCarnet()">Generar Carnet</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Formulario oculto para cambiar estado -->
            <form action="<?= APP_URL; ?>/app/controllers/estudiantes/delete.php"  
                  method="post" 
                  id="formEstadoEstudiante"
                  style="display: none;">  
                <input type="hidden" name="id_estudiante" id="id_estudiante_estado">  
                <input type="hidden" name="action" id="action_estado">  
            </form>  
        </div>
    </div>
</div>

<script>
function abrirModalCarnet(idEstudiante) {
    $('#id_estudiante_carnet').val(idEstudiante);
    // Establecer fecha por defecto (1 año desde hoy)
    var fecha = new Date();
    fecha.setFullYear(fecha.getFullYear() + 1);
    var fechaFormateada = fecha.toISOString().split('T')[0];
    $('#fecha_vencimiento').val(fechaFormateada);
    $('#modalCarnet').modal('show');
}

function generarCarnet() {
    $('#formCarnet').submit();
    $('#modalCarnet').modal('hide');
}

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
            document.getElementById('action_estado').value = (estatusLower === 'activo' ? 'inhabilitar' : 'habilitar');  
            document.getElementById('id_estudiante_estado').value = idEstudiante;
            document.getElementById('formEstadoEstudiante').submit(); // Enviar el formulario  
        }  
    });  

    return false; // Mantener el retorno falso para evitar el envío automático  
} 
    // Mostrar el mensaje de éxito o error si existe
    <?php if (isset($message)): ?>
        Swal.fire({
            icon: 'success', // Cambia a 'error' si es un mensaje de error
            title: 'Éxito',
            text: '<?= $message; ?>',
            confirmButtonText: 'Aceptar'
        });
    <?php endif; ?>
    
    // Inicializar select2
    $(document).ready(function(){
        $('.select2').select2({
            width: '100%',
            placeholder: "Seleccione una opción",
            allowClear: true,
            theme: 'bootstrap4'
        });
    });
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
        }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');  
    });  
</script>

<style>
.table th {
    font-weight: 600;
    font-size: 0.875rem;
    background-color: #f8f9fa;
    border-top: 1px solid #dee2e6;
}
.badge {
    font-size: 0.75rem;
}
.btn-group-sm > .btn {
    padding: 0.25rem 0.5rem;
}
.img-circle {
    border-radius: 50%;
}
.dataTables_wrapper .dataTables_filter input {
    border: 1px solid #ced4da;
    border-radius: 0.25rem;
}
.card {
    border-radius: 0.5rem;
}
.shadow-sm {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
}
.bg-info {
    background-color: #17a2b8 !important;
}
.btn-info {
    background-color: #17a2b8;
    border-color: #17a2b8;
}
.btn-info:hover {
    background-color: #138496;
    border-color: #117a8b;
}
.table-hover tbody tr:hover {
    background-color: rgba(23, 162, 184, 0.05);
}
.badge-success {
    background-color: #28a745;
}
.badge-danger {
    background-color: #dc3545;
}
.modal-header {
    border-radius: 0;
}
.alert-info {
    background-color: #17a2b8;
    border-color: #17a2b8;
}
</style>