<?php
ob_start();
include('../../app/config.php');
include('../../admin/layout/parte1.php');

// Obtener el periodo académico activo
$gestion_activa = null;
try {
    $sql_gestion = "SELECT * FROM gestiones WHERE estado = 1 ORDER BY desde DESC LIMIT 1";
    $query_gestion = $pdo->prepare($sql_gestion);
    $query_gestion->execute();
    $gestion_activa = $query_gestion->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['mensaje'] = "Error al obtener el periodo académico: " . $e->getMessage();
    $_SESSION['mensaje_tipo'] = "error";
}

if (!$gestion_activa) {
    $_SESSION['mensaje'] = "No hay un periodo académico activo configurado.";
    $_SESSION['mensaje_tipo'] = "warning";
    header('Location: index.php');
    exit();
}

// Obtener todas las secciones disponibles
$secciones = [];
try {
    $sql_secciones = "SELECT s.id_seccion, s.nombre_seccion, s.turno, 
                     g.grado, g.trayecto, g.trimestre
                     FROM secciones s
                     JOIN grados g ON s.id_grado = g.id_grado
                     WHERE s.id_gestion = :id_gestion AND s.estado = 1
                     ORDER BY g.grado, s.nombre_seccion";
    $query_secciones = $pdo->prepare($sql_secciones);
    $query_secciones->bindParam(':id_gestion', $gestion_activa['id_gestion'], PDO::PARAM_INT);
    $query_secciones->execute();
    $secciones = $query_secciones->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['mensaje'] = "Error al obtener las secciones: " . $e->getMessage();
    $_SESSION['mensaje_tipo'] = "error";
}

// Variables para almacenar los datos seleccionados
$id_seccion = isset($_GET['id_seccion']) ? (int)$_GET['id_seccion'] : null;
$estudiantes = [];
$seccion_seleccionada = null;

// Si se seleccionó una sección
if ($id_seccion) {
    // Buscar la sección seleccionada
    foreach ($secciones as $seccion) {
        if ($seccion['id_seccion'] == $id_seccion) {
            $seccion_seleccionada = $seccion;
            break;
        }
    }
    
    if ($seccion_seleccionada) {
        // Obtener estudiantes de la sección
        try {
            $sql_estudiantes = "SELECT e.id_estudiante, e.nombres, e.apellidos, e.cedula, e.genero, 
                               e.fecha_nacimiento, e.direccion
                               FROM estudiantes e
                               JOIN inscripciones i ON e.id_estudiante = i.id_estudiante
                               WHERE i.id_seccion = :id_seccion 
                               AND i.id_gestion = :id_gestion
                               AND i.estado = 1
                               ORDER BY e.apellidos, e.nombres";
            $query_estudiantes = $pdo->prepare($sql_estudiantes);
            $query_estudiantes->bindParam(':id_seccion', $id_seccion, PDO::PARAM_INT);
            $query_estudiantes->bindParam(':id_gestion', $gestion_activa['id_gestion'], PDO::PARAM_INT);
            $query_estudiantes->execute();
            $estudiantes = $query_estudiantes->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $_SESSION['mensaje'] = "Error al obtener los estudiantes: " . $e->getMessage();
            $_SESSION['mensaje_tipo'] = "error";
        }
    } else {
        $_SESSION['mensaje'] = "La sección seleccionada no existe o no está activa.";
        $_SESSION['mensaje_tipo'] = "warning";
    }
}
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Listados de Estudiantes</h1>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Generar listado de estudiantes</h3>
                            <div class="card-tools">
                                <span class="badge bg-primary">
                                    Periodo: <?= date('Y', strtotime($gestion_activa['desde'])) ?>-<?= date('Y', strtotime($gestion_activa['hasta'])) ?>
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <form method="get" action="" id="form_listado">
                                <div class="row">
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label for="select_seccion">Seleccione un grado:</label>
                                            <select name="id_seccion" id="select_seccion" class="form-control select2" required>
                                                <option value="">Seleccione una sección</option>
                                                <?php foreach ($secciones as $seccion): ?>
                                                    <option value="<?= $seccion['id_seccion'] ?>" 
                                                        <?= ($id_seccion == $seccion['id_seccion']) ? 'selected' : '' ?>>
                                                        <?= $seccion['grado'] ?> - Sección <?= $seccion['nombre_seccion'] ?>
                                                        <?= ($seccion['trayecto']) ? "| Trayecto: ".$seccion['trayecto'] : '' ?>
                                                        <?= ($seccion['trimestre']) ? "| Trim: ".$seccion['trimestre'] : '' ?>
                                                        | Turno: <?= ($seccion['turno'] == 'M') ? 'Mañana' : (($seccion['turno'] == 'T') ? 'Tarde' : 'Noche') ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group" style="margin-top: 32px;">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-search"></i> Buscar
                                            </button>
                                            <?php if ($id_seccion && $seccion_seleccionada): ?>
                                              <a href="vista_previa_pdf.php?id_seccion=<?= $id_seccion ?>" class="btn btn-danger">
                                               <i class="fas fa-file-pdf"></i> Exportar PDF
                                              </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($id_seccion && $seccion_seleccionada): ?>
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-info">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-users"></i> Listado de Estudiantes - 
                                <?= $seccion_seleccionada['grado'] ?> - Sección <?= $seccion_seleccionada['nombre_seccion'] ?>
                                <?= ($seccion_seleccionada['trayecto']) ? "| Trayecto: ".$seccion_seleccionada['trayecto'] : '' ?>
                                <?= ($seccion_seleccionada['trimestre']) ? "| Trim: ".$seccion_seleccionada['trimestre'] : '' ?>
                                | Turno: <?= ($seccion_seleccionada['turno'] == 'M') ? 'Mañana' : (($seccion_seleccionada['turno'] == 'T') ? 'Tarde' : 'Noche') ?>
                            </h3>
                            <div class="card-tools">
                                <span class="badge bg-info">Total: <?= count($estudiantes) ?> estudiantes</span>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-hover">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th width="5%">N°</th>
                                            <th width="30%">Apellidos y Nombres</th>
                                            <th width="15%">C.I.</th>
                                            <th width="10%">Género</th>
                                            <th width="20%">Fecha Nac.</th>
                                            <th width="20%">Dirección</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($estudiantes)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center">No hay estudiantes inscritos en esta sección</td>
                                        </tr>
                                        <?php else: ?>
                                            <?php foreach ($estudiantes as $index => $estudiante): ?>
                                            <tr>
                                                <td class="text-center"><?= $index + 1 ?></td>
                                                <td><?= htmlspecialchars($estudiante['apellidos'] . ' ' . $estudiante['nombres']) ?></td>
                                                <td class="text-center"><?= htmlspecialchars($estudiante['cedula']) ?></td>
                                                <td class="text-center"><?= ucfirst(htmlspecialchars($estudiante['genero'])) ?></td>
                                                <td class="text-center"><?= date('d/m/Y', strtotime($estudiante['fecha_nacimiento'])) ?></td>
                                                <td><?= htmlspecialchars($estudiante['direccion']) ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
include('../../admin/layout/parte2.php');
include('../../layout/mensajes.php');
?>

<script>
$(document).ready(function() {
    $('.select2').select2({
        theme: 'bootstrap4',
        placeholder: "Seleccione una sección",
        allowClear: true
    });
    
    <?php if (isset($_SESSION['mensaje'])): ?>
        Swal.fire({
            title: '<?= $_SESSION['mensaje_titulo'] ?? "Aviso" ?>',
            text: '<?= $_SESSION['mensaje'] ?>',
            icon: '<?= $_SESSION['mensaje_tipo'] ?? "info" ?>',
            confirmButtonText: 'Aceptar'
        });
        <?php 
        unset($_SESSION['mensaje']);
        unset($_SESSION['mensaje_tipo']);
        unset($_SESSION['mensaje_titulo']);
        ?>
    <?php endif; ?>
});
</script>