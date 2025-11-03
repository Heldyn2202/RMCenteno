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
$id_grado_filtro = isset($_GET['grado']) ? $_GET['grado'] : null; // Inicializa la variable para el filtro de grado
$id_seccion_filtro = isset($_GET['id_seccion']) ? $_GET['id_seccion'] : null; // Inicializa la variable para el filtro de sección
$genero_filtro = isset($_GET['genero']) ? $_GET['genero'] : null; // Inicializa la variable para el filtro de género

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
            $stmt->bindParam(':nombre_seccion', $seccion['nombre_seccion']); // Agregar esta línea
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
                $_SESSION['mensaje'] = "Inscripción registrada con éxito en la sección: " . htmlspecialchars($seccion['nombre_seccion']); // Mensaje de éxito  
                header('Location: Lista_de_inscripcion.php');  
                exit;  
            } else {  
                $_SESSION['mensaje'] = "Error al registrar la inscripción."; // Mensaje de error  
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
JOIN grados g ON i.grado = g.id_grado  -- Unir con la tabla de grados
WHERE i.id_gestion = :id_gestion"; 

// Filtrar por sección, grado y género si se proporciona
$id_seccion_filtro = isset($_GET['id_seccion']) ? $_GET['id_seccion'] : null;
$grado_filtro = isset($_GET['grado']) ? $_GET['grado'] : null; // Cambiado a grado_filtro
$genero_filtro = isset($_GET['genero']) ? $_GET['genero'] : null;

if ($id_seccion_filtro) {  
    $sql_inscripciones .= " AND i.id_seccion = :id_seccion";  
}

if ($grado_filtro) {  
    $sql_inscripciones .= " AND g.id_grado = :grado"; // Cambiado para filtrar por el ID del grado
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
$sql_grados = "SELECT * FROM grados WHERE estado = 1"; // Cambiado para obtener grados activos
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
                            <h1 class="m-0">Lista de Inscripción - Periodo Académico <?= date('Y', strtotime($gestion_activa['desde'])) . '-' . date('Y', strtotime($gestion_activa['hasta'])); ?></h1>  
                        </div><!-- /.col -->  
                        <div class="col-sm-6">  
                            <ol class="breadcrumb float-sm-right">  
                                <li class="breadcrumb-item"><a href="<?= APP_URL; ?>/admin">Dashboard</a></li>  
                                <li class="breadcrumb-item"><a href="<?= APP_URL; ?>/admin/estudiantes">Estudiantes</a></li>  
                                <li class="breadcrumb-item">Lista de Inscripción</li>  
                            </ol>  
                        </div><!-- /.col -->  
                    </div><!-- /.row -->  
                </div><!-- /.container-fluid -->  
            </div>  
            <div class="content">  
                <div class="container">  
                    <div class="row">  
                        <br>  
                        <div class="col-md-12 text-right">  
                            <div class="dropdown-menu">  
                                <a class="dropdown-item" href="<?= APP_URL; ?>/admin/estudiantes/Lista_de_inscripcion.php">Periodo académico actual</a>  
                                <?php foreach ($periodos_anteriores as $periodo): ?>  
                                    <a class="dropdown-item" href="<?= APP_URL; ?>/admin/estudiantes/Lista_de_inscripcion.php?periodo_id=<?= $periodo['id_gestion']; ?>">  
                                        <?= date('Y-m-d', strtotime($periodo['desde'])) . " - " . date('Y-m-d', strtotime($periodo['hasta'])); ?>  
                                    </a>  
                                <?php endforeach; ?>  
                            </div>  
                        </div>  
                    </div> 
                
                    <!-- Filtro de Grado, Sección y Género -->  
                    <div class="row mb-3">  
                        <div class="col-md-12">  
                        <div class="callout border-primary">
                        <fieldset>
                            <form method="GET" action="">  
                                <div class="form-row align-items-center">  
                                    <div class="col-md-3">  
                                        <label for="grado">Filtrar por Grado:</label>  
                                        <select name="grado" id="grado" class="form-control" onchange="cargarSecciones(this.value)">  
                                            <option value="">Todos los Grados</option>  
                                            <?php foreach ($grados as $grado): ?>  
                                                <option value="<?= htmlspecialchars($grado['id_grado']); ?>" <?= ($id_grado_filtro == $grado['id_grado']) ? 'selected' : ''; ?>>  
                                                    <?= htmlspecialchars($grado['grado']); ?>  
                                                </option>  
                                            <?php endforeach; ?>  
                                        </select>  
                                    </div>  
                                    <div class="col-md-3">  
                                        <label for="id_seccion">Filtrar por Sección:</label>  
                                        <select name="id_seccion" id="id_seccion" class="form-control">  
                                            <option value="">Todas las Secciones</option>  
                                            <!-- Las secciones se cargarán aquí mediante AJAX -->
                                        </select>  
                                    </div>  
                                    <div class="col-md-3">  
                                        <label for="genero">Filtrar por Género:</label>  
                                        <select name="genero" id="genero" class="form-control">  
                                            <option value="">Todos los Géneros</option>  
                                            <option value="Masculino" <?= ($genero_filtro == 'Masculino') ? 'selected' : ''; ?>>Masculino</option>  
                                            <option value="Femenino" <?= ($genero_filtro == 'Femenino') ? 'selected' : ''; ?>>Femenino</option>  
                                        </select>  
                                    </div>  
                                    <div class="col-md-3">  
                                        <button type="submit" class="btn btn-primary">Filtrar</button>  
                                    </div>  
                                </div>  
                            </form>  
                            
        </fieldset>
		</div>
                        </div>  
                    </div>   
                    <div class="row">  
                        <div class="col-md-12">  
                            <div class="card card-outline card-primary">  
                                <div class="card-body">  
                                    <h4>Total de Inscripciones: <?= $total_inscripciones; ?></h4> <!-- Contador de inscripciones -->
                                    <table id="example1" class="table table-striped table-bordered table-hover table-sm">  
                                    <thead>  
                                            <tr>  
                                                <th><center>#</center></th> <!-- Contador -->
                                                <th><center>Nombre y Apellido</center></th>  
                                                <th><center>Nivel</center></th>  
                                                <th><center>Grado</center></th>  
                                                <th><center>Sección</center></th>  
                                                <th><center>Turno</center></th>  
                                                <th><center>Talla de camisa</center></th>  
                                                <th><center>Talla de pantalón</center></th>  
                                                <th><center>Talla de zapatos</center></th>  
                                                <th><center>Acciones</center></th>  
                                            </tr>  
                                        </thead>  
                                        <tbody>  
                                            <?php 
                                            $contador_inscripciones = 0; // Inicializa el contador
                                            foreach ($inscripciones as $inscripcion): 
                                                $contador_inscripciones++; // Incrementa el contador
                                            ?>  
                                                <tr>  
                                                    <td style="text-align: center"><?= $contador_inscripciones; ?></td> <!-- Muestra el contador -->
                                                    <td><?= htmlspecialchars($inscripcion['nombres'] . ' ' . $inscripcion['apellidos']); ?></td>  
                                                    <td><?= htmlspecialchars($inscripcion['nivel_id']); ?></td>  
                                                    <td><?= htmlspecialchars($inscripcion['grado']); ?></td>  
                                                    <td><?= htmlspecialchars($inscripcion['nombre_seccion']); ?></td>  
                                                    <td><?= htmlspecialchars($turno_map[$inscripcion['turno_id']]); ?></td> <!-- Muestra "Mañana" o "Tarde" -->
                                                    <td><?= htmlspecialchars($inscripcion['talla_camisa']); ?></td>  
                                                    <td><?= htmlspecialchars($inscripcion['talla_pantalon']); ?></td>  
                                                    <td><?= htmlspecialchars($inscripcion['talla_zapatos']); ?></td>  
                                                    <td style="text-align: center">  
                                                        <div class="btn-group" role="group" aria-label="Basic example">  
                                                            <a href="show_inc.php?id=<?= htmlspecialchars($inscripcion['id_estudiante']); ?>" type="button" class="btn btn- btn-lg"><i class="bi bi-eye"></i></a>
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
                </div><!-- /.container-fluid -->  
            </div>  
            <!-- /.content -->  
        </div>  
        <!-- /.content-wrapper -->  

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
            seccionSelect.innerHTML = '<option value="">Todas las Secciones</option>'; // Reiniciar opciones

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

// Mostrar mensaje de alerta si hay un mensaje en la sesión
<?php if (isset($_SESSION['mensaje'])): ?>
    Swal.fire({
        title: '¡Atención!',
        text: '<?= $_SESSION['mensaje']; ?>',
        icon: '<?= strpos($_SESSION['mensaje'], 'Error') !== false ? 'error' : 'success'; ?>',
        confirmButtonText: 'Aceptar'
    });
    <?php unset($_SESSION['mensaje']); // Limpiar el mensaje después de mostrarlo ?>
<?php endif; ?>
</script>
<script>
    $(function () {
        $("#example1").DataTable({
            "pageLength": 5,
            "language": {
                "emptyTable": "No hay información",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ Estudiantes",
                "infoEmpty": "Mostrando 0 a 0 de 0 Estudiantes",
                "infoFiltered": "(Filtrado de _MAX_ total Estudiantes)",
                "infoPostFix": "",
                "thousands": ",",
                "lengthMenu": "Mostrar _Menú_ Estudiantes",
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
            "responsive": true, "lengthChange": true, "autoWidth": false,
            buttons: [{
                extend: 'collection',
                text: 'Reportes',
                orientation: 'landscape',
                buttons: [{
                    text: 'Copiar',
                    extend: 'copy',
                }, {
                    extend: 'pdf'
                },{
                    extend: 'csv'
                },{
                    extend: 'excel'
                },{
                    text: 'Imprimir',
                    extend: 'print'
                }
                ] 
            },
                {
                    extend: 'colvis',
                    text: 'Visor de columnas',
                    collectionLayout: 'fixed three-column'
                }
            ],
        }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
    });
</script>

