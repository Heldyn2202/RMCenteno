<?php  
ob_start(); // Inicia el buffer de salida  
include('../../app/config.php');  
include('../../admin/layout/parte1.php');  

// Mapeo de turnos
$turno_map = [
    'M' => 'Mañana',
    'T' => 'Tarde'
];

// Inicializar variables de filtro
$id_grado_filtro = isset($_GET['grado']) ? $_GET['grado'] : null;
$id_seccion_filtro = isset($_GET['id_seccion']) ? $_GET['id_seccion'] : null;
$genero_filtro = isset($_GET['genero']) ? $_GET['genero'] : null;

// Manejo de la inserción de inscripciones  
if ($_SERVER['REQUEST_METHOD'] === 'POST') {  
    // Obtener los datos del formulario  
    $nivel_id = $_POST['nivel_id'];  
    $grado = $_POST['grado'];  
    $id_seccion = $_POST['id_seccion'];  
    $turno_id = $_POST['turno_id'];  
    $talla_camisa = $_POST['talla_camisa'];  
    $talla_pantalon = $_POST['talla_pantalon'];  
    $talla_zapatos = $_POST['talla_zapatos'];  
    $id_estudiante = $_POST['id_estudiante'];  

    // Verificar que id_estudiante no sea nulo  
    if (empty($id_estudiante)) {  
        die("Error: El ID del estudiante no está definido.");  
    }  

    // Obtener el periodo académico activo (estado = 1)  
    $sql_gestiones = "SELECT * FROM gestiones WHERE estado = 1 ORDER BY desde DESC LIMIT 1";  
    $query_gestiones = $pdo->prepare($sql_gestiones);  
    $query_gestiones->execute();  
    $gestion_activa = $query_gestiones->fetch(PDO::FETCH_ASSOC);  

    // Verificar si el estudiante ya está inscrito en el periodo activo  
    $sql_verificacion = "SELECT COUNT(*) FROM inscripciones WHERE id_estudiante = :id_estudiante AND id_gestion = :id_gestion";  
    $stmt_verificacion = $pdo->prepare($sql_verificacion);  
    $stmt_verificacion->bindParam(':id_estudiante', $id_estudiante);  
    $stmt_verificacion->bindParam(':id_gestion', $gestion_activa['id_gestion']);  
    $stmt_verificacion->execute();  
    $inscripcion_existente = $stmt_verificacion->fetchColumn();  

    if ($inscripcion_existente > 0) {  
        $_SESSION['mensaje'] = "Error: El estudiante ya está inscrito en este periodo académico.";  
        header('Location: Lista_de_inscripcion.php');  
        exit;  
    }  

    // Consultar la sección para obtener la capacidad y el cupo actual usando id_seccion  
    $sql_cupos = "SELECT capacidad, cupo_actual, nombre_seccion FROM secciones WHERE id_seccion = :id_seccion";  
    $query_cupos = $pdo->prepare($sql_cupos);  
    $query_cupos->bindParam(':id_seccion', $id_seccion);  
    $query_cupos->execute();  
    $seccion = $query_cupos->fetch(PDO::FETCH_ASSOC);  

    if ($seccion) {  
        // Verificar si hay cupos disponibles  
        if ($seccion['cupo_actual'] < $seccion['capacidad']) {  
            // Preparar la consulta de inserción  
            $sql = "INSERT INTO inscripciones (id_gestion, nivel_id, grado, id_seccion, nombre_seccion, turno_id, talla_camisa, talla_pantalon, talla_zapatos, id_estudiante, created_at, updated_at, estado)  
                    VALUES (:id_gestion, :nivel_id, :grado, :id_seccion, :nombre_seccion, :turno_id, :talla_camisa, :talla_pantalon, :talla_zapatos, :id_estudiante, NOW(), NOW(), 'activo')";  

            $stmt = $pdo->prepare($sql);  

            // Vincular los parámetros  
            $stmt->bindParam(':id_gestion', $gestion_activa['id_gestion']);  
            $stmt->bindParam(':nivel_id', $nivel_id);  
            $stmt->bindParam(':grado', $grado);  
            $stmt->bindParam(':id_seccion', $id_seccion);  
            $stmt->bindParam(':nombre_seccion', $seccion['nombre_seccion']);  
            $stmt->bindParam(':turno_id', $turno_id);  
            $stmt->bindParam(':talla_camisa', $talla_camisa);  
            $stmt->bindParam(':talla_pantalon', $talla_pantalon);  
            $stmt->bindParam(':talla_zapatos', $talla_zapatos);  
            $stmt->bindParam(':id_estudiante', $id_estudiante);  

            // Ejecutar la consulta  
            if ($stmt->execute()) {
                // Incrementar el cupo actual  
                $nuevo_cupo_actual = $seccion['cupo_actual'] + 1;  
                $sql_actualizar_cupo = "UPDATE secciones SET cupo_actual = :cupo_actual WHERE id_seccion = :id_seccion";  
                $query_actualizar_cupo = $pdo->prepare($sql_actualizar_cupo);  
                $query_actualizar_cupo->bindParam(':cupo_actual', $nuevo_cupo_actual);  
                $query_actualizar_cupo->bindParam(':id_seccion', $id_seccion);  
                $query_actualizar_cupo->execute();  
                
                // Mostrar el nombre de la sección en el mensaje de éxito  
                $_SESSION['mensaje'] = "Inscripción registrada con éxito en la sección: " . htmlspecialchars($seccion['nombre_seccion']);  
                header('Location: Lista_de_inscripcion.php');  
                exit;  
            } else {  
                $_SESSION['mensaje'] = "Error al registrar la inscripción.";  
                header('Location: Lista_de_inscripcion.php');  
                exit;  
            }  
        } else {  
            // No hay cupos disponibles  
            $_SESSION['mensaje'] = "Error: No hay cupos disponibles en esta sección.";  
            header('Location: Lista_de_inscripcion.php');  
            exit;  
        }  
    } else {  
        $_SESSION['mensaje'] = "Error: Sección no encontrada.";  
        header('Location: Lista_de_inscripcion.php');  
        exit;  
    }  
}  

// Obtener el periodo académico activo (estado = 1)  
$sql_gestiones = "SELECT * FROM gestiones WHERE estado = 1 ORDER BY desde DESC LIMIT 1";  
$query_gestiones = $pdo->prepare($sql_gestiones);  
$query_gestiones->execute();  
$gestion_activa = $query_gestiones->fetch(PDO::FETCH_ASSOC);  

// Obtener las inscripciones que pertenecen al periodo académico activo  
$sql_inscripciones = "SELECT i.*, e.id_estudiante, e.nombres, e.apellidos, e.genero, s.nombre_seccion, g.grado 
FROM inscripciones i  
JOIN estudiantes e ON i.id_estudiante = e.id_estudiante  
JOIN secciones s ON i.id_seccion = s.id_seccion 
JOIN grados g ON i.grado = g.id_grado  
WHERE i.id_gestion = :id_gestion"; 

// Filtrar por sección, grado y género si se proporciona
$id_seccion_filtro = isset($_GET['id_seccion']) ? $_GET['id_seccion'] : null;
$grado_filtro = isset($_GET['grado']) ? $_GET['grado'] : null;
$genero_filtro = isset($_GET['genero']) ? $_GET['genero'] : null;

if ($id_seccion_filtro) {  
    $sql_inscripciones .= " AND i.id_seccion = :id_seccion";  
}

if ($grado_filtro) {  
    $sql_inscripciones .= " AND g.id_grado = :grado";  
}

if ($genero_filtro) {  
    $sql_inscripciones .= " AND e.genero = :genero";  
}

$query_inscripciones = $pdo->prepare($sql_inscripciones);  
$query_inscripciones->bindParam(':id_gestion', $gestion_activa['id_gestion']);  

if ($id_seccion_filtro) {  
    $query_inscripciones->bindParam(':id_seccion', $id_seccion_filtro);  
}

if ($grado_filtro) {  
    $query_inscripciones->bindParam(':grado', $grado_filtro);  
}

if ($genero_filtro) {  
    $query_inscripciones->bindParam(':genero', $genero_filtro);  
}

$query_inscripciones->execute();  
$inscripciones = $query_inscripciones->fetchAll(PDO::FETCH_ASSOC);    

// Contar el número total de inscripciones  
$total_inscripciones = count($inscripciones);  

// Obtener todas las secciones para llenar el select, filtrando por el periodo académico activo
$sql_secciones = "SELECT * FROM secciones WHERE id_gestion = :id_gestion AND estado = 1";  

$query_secciones = $pdo->prepare($sql_secciones);  
$query_secciones->bindParam(':id_gestion', $gestion_activa['id_gestion']);  
$query_secciones->execute();  
$secciones = $query_secciones->fetchAll(PDO::FETCH_ASSOC);  

// Obtener todos los grados para llenar el select  
$sql_grados = "SELECT * FROM grados WHERE estado = 1";  
$query_grados = $pdo->prepare($sql_grados);  
$query_grados->execute();  
$grados = $query_grados->fetchAll(PDO::FETCH_ASSOC);  
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
                            <h1 class="m-0 text-dark">Lista de Inscripción</h1>  
                        </div><!-- /.col -->  
                        <div class="col-sm-6">  
                            <ol class="breadcrumb float-sm-right">  
                                <li class="breadcrumb-item"><a href="<?= APP_URL; ?>/admin" class="text-info">Dashboard</a></li>  
                                <li class="breadcrumb-item"><a href="<?= APP_URL; ?>/admin/estudiantes" class="text-info">Estudiantes</a></li>  
                                <li class="breadcrumb-item active">Lista de Inscripción</li>  
                            </ol>  
                        </div><!-- /.col -->  
                    </div><!-- /.row -->  
                </div><!-- /.container-fluid -->  
            </div>  
            
            <!-- Filtro de Grado, Sección y Género -->  
            <div class="card card-info shadow-sm border-0 mb-4">
                <div class="card-header py-2">
                    <h5 class="m-0"><i class="fas fa-filter mr-2"></i>Filtros de Búsqueda</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="">  
                        <div class="row align-items-end">  
                            <div class="form-group col-md-3 mb-0">  
                                <label for="grado" class="form-label small font-weight-bold text-muted">Grado</label>  
                                <select name="grado" id="grado" class="form-control select2" onchange="cargarSecciones(this.value)">  
                                    <option value="">Todos los Grados</option>  
                                    <?php foreach ($grados as $grado): ?>  
                                        <option value="<?= htmlspecialchars($grado['id_grado']); ?>" <?= ($id_grado_filtro == $grado['id_grado']) ? 'selected' : ''; ?>>  
                                            <?= htmlspecialchars($grado['grado']); ?>  
                                        </option>  
                                    <?php endforeach; ?>  
                                </select>  
                            </div>  
                            <div class="form-group col-md-3 mb-0">  
                                <label for="id_seccion" class="form-label small font-weight-bold text-muted">Sección</label>  
                                <select name="id_seccion" id="id_seccion" class="form-control select2">  
                                    <option value="">Todas las Secciones</option>  
                                    <!-- Las secciones se cargarán aquí mediante AJAX -->
                                </select>  
                            </div>  
                            <div class="form-group col-md-3 mb-0">  
                                <label for="genero" class="form-label small font-weight-bold text-muted">Género</label>  
                                <select name="genero" id="genero" class="form-control select2">  
                                    <option value="">Todos los Géneros</option>  
                                    <option value="Masculino" <?= ($genero_filtro == 'Masculino') ? 'selected' : ''; ?>>Masculino</option>  
                                    <option value="Femenino" <?= ($genero_filtro == 'Femenino') ? 'selected' : ''; ?>>Femenino</option>  
                                </select>  
                            </div>  
                            <div class="form-group col-md-3 mb-0">  
                                <label class="form-label small font-weight-bold text-muted d-block">&nbsp;</label>  
                                <button type="submit" class="btn btn-info btn-block shadow-sm">
                                    <i class="fa fa-filter mr-1"></i> Filtrar
                                </button>  
                            </div>  
                        </div>  
                    </form>  
                </div>
            </div>   
            
            <div class="row">  
                <div class="col-md-12">  
                    <div class="card card-outline card-info shadow border-0">  
                        <div class="card-header bg-white border-bottom-0 py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="m-0 text-dark">
                                        <i class="fas fa-user-graduate mr-2"></i>
                                        Estudiantes Inscritos
                                    </h5>
                                </div>
                            </div>
                        </div>  
                        <div class="card-body pt-0 pb-2 px-3">

                            <div class="table-responsive">
                                <table id="example1" class="table table-hover table-striped">  
                                    <colgroup>
                                        <col width="5%">
                                        <col width="20%">
                                        <col width="10%">
                                        <col width="10%">
                                        <col width="10%">
                                        <col width="10%">
                                        <col width="10%">
                                        <col width="10%">
                                        <col width="10%">
                                        <col width="5%">
                                    </colgroup>
                                    <thead class="thead-light">  
                                        <tr>  
                                            <th class="text-center">#</th>
                                            <th class="text-center">Nombre y Apellido</th>  
                                            <th class="text-center">Nivel</th>  
                                            <th class="text-center">Grado</th>  
                                            <th class="text-center">Sección</th>  
                                            <th class="text-center">Turno</th>  
                                            <th class="text-center">Talla Camisa</th>  
                                            <th class="text-center">Talla Pantalón</th>  
                                            <th class="text-center">Talla Zapatos</th>  
                                            <th class="text-center">Acciones</th>  
                                        </tr>  
                                    </thead>  
                                    <tbody>  
                                        <?php 
                                        $contador_inscripciones = 0;
                                        foreach ($inscripciones as $inscripcion): 
                                            $contador_inscripciones++;
                                        ?>  
                                            <tr>  
                                                <td class="text-center align-middle">
                                                    <span class="font-weight-bold text-dark"><?= $contador_inscripciones; ?></span>
                                                </td>
                                                <td class="align-middle">
                                                    <div class="d-flex align-items-center">
                                                        <div class="img-circle bg-info text-white d-flex align-items-center justify-content-center mr-3 shadow-sm" 
                                                             style="width: 40px; height: 40px; font-size: 0.9rem; font-weight: bold;">
                                                            <?= strtoupper(substr($inscripcion['nombres'] ?? '', 0, 1) . substr($inscripcion['apellidos'] ?? '', 0, 1)) ?>
                                                        </div>
                                                        <div>
                                                            <b class="text-dark"><?= htmlspecialchars($inscripcion['nombres'] . ' ' . $inscripcion['apellidos']); ?></b>
                                                            <br>
                                                            <small class="text-muted">
                                                                <?= htmlspecialchars($inscripcion['genero']); ?>
                                                            </small>
                                                        </div>
                                                    </div>
                                                </td>  
                                                <td class="text-center align-middle">
                                                    <span class="badge badge-info p-2"><?= htmlspecialchars($inscripcion['nivel_id']); ?></span>
                                                </td>  
                                                <td class="text-center align-middle">
                                                    <span class="font-weight-bold text-dark"><?= htmlspecialchars($inscripcion['grado']); ?></span>
                                                </td>  
                                                <td class="text-center align-middle">
                                                    <span class="badge badge-success p-2"><?= htmlspecialchars($inscripcion['nombre_seccion']); ?></span>
                                                </td>  
                                                <td class="text-center align-middle">
                                                    <span class="font-weight-bold text-dark"><?= htmlspecialchars($turno_map[$inscripcion['turno_id']]); ?></span>
                                                </td>  
                                                <td class="text-center align-middle">
                                                    <span class="font-weight-bold text-dark"><?= htmlspecialchars($inscripcion['talla_camisa']); ?></span>
                                                </td>  
                                                <td class="text-center align-middle">
                                                    <span class="font-weight-bold text-dark"><?= htmlspecialchars($inscripcion['talla_pantalon']); ?></span>
                                                </td>  
                                                <td class="text-center align-middle">
                                                    <span class="font-weight-bold text-dark"><?= htmlspecialchars($inscripcion['talla_zapatos']); ?></span>
                                                </td>  
                                                <td class="text-center align-middle">  
                                                    <div class="btn-group btn-group-sm shadow-sm">
                                                        <a href="show_inc.php?id=<?= htmlspecialchars($inscripcion['id_estudiante']); ?>" 
                                                           class="btn btn-info" 
                                                           title="Ver Detalles">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    </div>  
                                                </td>  
                                            </tr>  
                                        <?php endforeach; ?>  
                                    </tbody>  
                                </table>  
                            </div>
                        </div>  
                    </div>  
                </div>  
            </div>  
        </div>  
    </div>  
</div>  

<?php  
include('../../admin/layout/parte2.php');  
include('../../layout/mensajes.php');  
?>

<script>
function cargarSecciones(gradoId) {
    var xhr = new XMLHttpRequest();
    xhr.open("GET", "get_secciones.php?grado_id=" + gradoId, true);
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            var secciones = JSON.parse(xhr.responseText);
            var seccionSelect = document.getElementById("id_seccion");
            seccionSelect.innerHTML = '<option value="">Todas las Secciones</option>';

            secciones.forEach(function(seccion) {
                var option = document.createElement("option");
                option.value = seccion.id_seccion;
                option.text = seccion.nombre_seccion;
                seccionSelect.appendChild(option);
            });
        }
    };
    xhr.send();
}

// Inicializar select2
$(document).ready(function(){
    $('.select2').select2({
        width: '100%',
        placeholder: "Seleccione una opción",
        allowClear: true,
        theme: 'bootstrap4'
    });
});

// Mostrar mensaje de alerta si hay un mensaje en la sesión
<?php if (isset($_SESSION['mensaje'])): ?>
    Swal.fire({
        title: '¡Atención!',
        text: '<?= $_SESSION['mensaje']; ?>',
        icon: '<?= strpos($_SESSION['mensaje'], 'Error') !== false ? 'error' : 'success'; ?>',
        confirmButtonText: 'Aceptar'
    });
    <?php unset($_SESSION['mensaje']); ?>
<?php endif; ?>
</script>

<script>
    $(function () {
        $("#example1").DataTable({
            "pageLength": 10,
            "language": {
                "emptyTable": "No hay información",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ Inscripciones",
                "infoEmpty": "Mostrando 0 a 0 de 0 Inscripciones",
                "infoFiltered": "(Filtrado de _MAX_ total Inscripciones)",
                "infoPostFix": "",
                "thousands": ",",
                "lengthMenu": "Mostrar _MENU_ Inscripciones",
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
            "columnDefs": [
                { "orderable": false, "targets": [9] },
                { "searchable": false, "targets": [9] }
            ],
            initComplete: function() {
                $('.dt-buttons').addClass('btn-group');
                $('.dataTables_filter input').addClass('form-control form-control-sm');
                $('.dataTables_length select').addClass('form-control form-control-sm');
            },
            "dom": '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                   '<"row"<"col-sm-12"tr>>' +
                   '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>'
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
.badge-info {
    background-color: #17a2b8;
}
.alert-info {
    background-color: #17a2b8;
    border-color: #17a2b8;
}
</style>