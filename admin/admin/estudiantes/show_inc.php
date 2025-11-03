<?php
// Obtener el ID del estudiante desde la URL
$id_estudiante = isset($_GET['id']) ? $_GET['id'] : null; // Validar que el ID esté presente

if ($id_estudiante === null) {
    die("Error: ID del estudiante no proporcionado.");
}

// Incluir la configuración y otros archivos necesarios
include ('../../app/config.php');
include ('../../admin/layout/parte1.php');
include ('../../app/controllers/estudiantes/datos_del_estudiante.php');

// Incluir el archivo que contiene la lógica para obtener los detalles de la inscripción
include ('../../app/controllers/estudiantes/datos_inscripcion.php'); // Asegúrate de que este archivo existe y contiene la lógica para obtener la inscripción

// Obtener el nombre del grado
if (isset($grado)) {
    $sql_grado = "SELECT grado FROM grados WHERE id_grado = :id_grado";
    $query_grado = $pdo->prepare($sql_grado);
    $query_grado->bindParam(':id_grado', $grado, PDO::PARAM_INT);
    $query_grado->execute();
    $grado_info = $query_grado->fetch(PDO::FETCH_ASSOC);
    $nombre_grado = $grado_info ? $grado_info['grado'] : 'N/A'; // Manejar el caso en que no se encuentre el grado
} else {
    $nombre_grado = 'N/A'; // Si no hay grado definido
}

// Mapeo de turnos
$turno_map = [
    'M' => 'Mañana',
    'T' => 'Tarde'
];

// Obtener el turno correspondiente
$turno_mostrado = isset($turno) && array_key_exists($turno, $turno_map) ? $turno_map[$turno] : 'N/A'; // Manejar el caso en que no se encuentre el turno
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <br>
    <div class="content">
        <div class="container">
            <div class="row">
                <h1 class="text-center mb-4"><?= htmlspecialchars($nombres . " " . $apellidos); ?></h1>  
            </div>
            <br>
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-info">
                        <div class="card-header">
                            <h3 class="card-title"><b>Detalles de Inscripción</b></h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="">ID Gestión</label>
                                        <p class="lead"><?= htmlspecialchars($id_gestion); ?></p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="">Nivel</label>
                                        <p class="lead"><?= htmlspecialchars($nivel); ?></p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="">Grado</label>
                                        <p class="lead"><?= htmlspecialchars($nombre_grado); ?></p> <!-- Mostrar el nombre del grado -->
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="">Sección</label>
                                        <p class="lead"><?= htmlspecialchars($nombre_seccion); ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="">Turno</label>
                                        <p class="lead"><?= htmlspecialchars($turno_mostrado); ?></p> <!-- Mostrar "Mañana" o "Tarde" -->
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="">Talla de Camisa</label>
                                        <p class="lead"><?= htmlspecialchars($talla_camisa); ?></p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="">Talla de Pantalón</label>
                                        <p class="lead"><?= htmlspecialchars($talla_pantalon); ?></p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="">Talla de Zapatos</label>
                                        <p class="lead"><?= htmlspecialchars($talla_zapatos); ?></p>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <center><a href="<?= APP_URL; ?>/admin/estudiantes/lista_de_inscripcion.php" class="btn btn-secondary">Volver</a></center>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->

<?php
include ('../../admin/layout/parte2.php');
include ('../../layout/mensajes.php');
?>