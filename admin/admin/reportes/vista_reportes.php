<?php  
include('../../app/config.php');  
include('../../admin/layout/parte1.php');  

// Obtener el periodo académico activo
$sql_gestiones = "SELECT * FROM gestiones WHERE estado = 1 ORDER BY desde DESC LIMIT 1";  
$query_gestiones = $pdo->prepare($sql_gestiones);  
$query_gestiones->execute();  
$gestion_activa = $query_gestiones->fetch(PDO::FETCH_ASSOC);  

// Obtener los grados disponibles para el periodo académico activo
$sql_grados = "SELECT DISTINCT g.id_grado, g.grado FROM inscripciones i INNER JOIN grados g ON i.grado = g.id_grado WHERE i.id_gestion = :id_gestion";  
$query_grados = $pdo->prepare($sql_grados);  
$query_grados->bindParam(':id_gestion', $gestion_activa['id_gestion']);
$query_grados->execute();  
$grados = $query_grados->fetchAll(PDO::FETCH_ASSOC);  

// Inicializar variables
$grado_seleccionado = isset($_POST['grado']) ? $_POST['grado'] : null;

// Consulta para obtener la lista de estudiantes inscritos filtrados por grado y periodo académico activo
$sql_inscripciones = "SELECT i.id, i.id_estudiante, i.id_gestion, i.nivel_id, g.grado, i.nombre_seccion, i.id_seccion, i.turno_id, i.talla_camisa, i.talla_pantalon, i.talla_zapatos, e.nombres, e.apellidos  
                      FROM inscripciones i  
                      INNER JOIN estudiantes e ON i.id_estudiante = e.id_estudiante
                      INNER JOIN grados g ON i.grado = g.id_grado
                      WHERE i.id_gestion = :id_gestion";
                      
if ($grado_seleccionado) {
    $sql_inscripciones .= " AND g.grado = :grado";
}

$stmt = $pdo->prepare($sql_inscripciones);
$stmt->bindParam(':id_gestion', $gestion_activa['id_gestion']);

if ($grado_seleccionado) {
    $stmt->bindParam(':grado', $grado_seleccionado);
}

$stmt->execute();  
$inscripciones = $stmt->fetchAll(PDO::FETCH_ASSOC);  
?>  

<!-- Content Wrapper. Contains page content -->  
<div class="content-wrapper">  
    <br>  
    <div class="content">  
        <div class="container-fluid">  
            <div class="content-header">  
                <div class="container-fluid">  
                    <div class="row mb-2">  
                        <div class="col-sm-6">  
                            <h3 class="m-0">Matrícula Escolar del Periodo Académico <?= date('Y', strtotime($gestion_activa['desde'])) . '-' . date('Y', strtotime($gestion_activa['hasta'])); ?></h3>  
                        </div><!-- /.col -->  
                        <div class="col-sm-6">  
                            <ol class="breadcrumb float-sm-right">  
                                <li class="breadcrumb-item"><a href="#">Inicio</a></li>  
                                <li class="breadcrumb-item"><a href="<?=APP_URL;?>/admin/reportes">Reportes</a></li>  
                                <li class="breadcrumb-item active">Matrícula Escolar</li>  
                            </ol>  
                        </div><!-- /.col -->  
                    </div><!-- /.row -->  
                </div><!-- /.container-fluid -->  
            </div>  
            <br>  
            <div class="callout border-primary">
     <fieldset>
     <form method="GET" action="generar_reportes.php">
    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label for="grado">Seleccionar Grado</label>
                <select id="grado" name="grado" class="form-control" required>
                    <option value="">Seleccionar</option>
                    <?php foreach ($grados as $grado): ?>
                        <option value="<?= htmlspecialchars($grado['grado']); ?>" <?= ($grado_seleccionado == $grado['grado']) ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($grado['grado']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-group" style="margin-top: 32px;">
                <button type="submit" class="btn btn-info">Generar reporte</button>
            </div>
        </div>
    </div>
</form>
            
        </fieldset>
		</div>
            <div class="row">  
                <div class="col-md-12">  
                    <div class="card card-outline card-primary">  
                        <div class="card-body">  
                            <!-- Botón general para generar el reporte en la parte superior derecha -->
                            <div class="text-right mb-3">
                            <a href="generar_reportes.php?id_gestion=<?= $gestion_activa['id_gestion']; ?>&grado=<?= $grado_seleccionado; ?>" class="btn btn-info">Matrícula Escolar General</a></div>
                            <table id="example1" class="table table-striped table-bordered table-hover table-sm">  
                                <thead>  
                                    <tr>  
                                        <th><center>Estudiante</center></th>  
                                        <th><center>Nivel</center></th>  
                                        <th><center>Grado</center></th>  
                                        <th><center>Sección</center></th>  
                                        <th><center>Turno</center></th>  
                                        <th><center>Talla Camisa</center></th>  
                                        <th><center>Talla Pantalón</center></th>  
                                        <th><center>Talla Zapatos</center></th>  
                                    </tr>  
                                </thead>  
                                <tbody>  
                                    <?php foreach ($inscripciones as $inscripcion): ?>  
                                    <tr>  
                                        <td><?= htmlspecialchars($inscripcion['nombres']) . ' ' . htmlspecialchars($inscripcion['apellidos']); ?></td>  
                                        <td><?= htmlspecialchars($inscripcion['nivel_id']); ?></td>  
                                        <td><?= htmlspecialchars($inscripcion['grado']); ?></td>  
                                        <td><?= htmlspecialchars($inscripcion['nombre_seccion']); ?></td>  
                                        <td><?= htmlspecialchars($inscripcion['turno_id']); ?></td>  
                                        <td><?= htmlspecialchars($inscripcion['talla_camisa']); ?></td>  
                                        <td><?= htmlspecialchars($inscripcion['talla_pantalon']); ?></td>  
                                        <td><?= htmlspecialchars($inscripcion['talla_zapatos']); ?></td>  
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

<?php  
include('../../admin/layout/parte2.php');  
include('../../layout/mensajes.php');  
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
                "lengthMenu": "",
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
        });  
    });  
</script>