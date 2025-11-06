<?php
session_start();
require_once('../../app/config.php');
include('../../admin/layout/parte1.php');

// Obtener gestión activa
$sql_gestion = "SELECT * FROM gestiones WHERE estado = 1 LIMIT 1";
$query_gestion = $pdo->prepare($sql_gestion);
$query_gestion->execute();
$gestion_activa = $query_gestion->fetch(PDO::FETCH_ASSOC);

// Listar secciones activas
$sql_secciones = "SELECT s.id_seccion, CONCAT(g.grado, ' - ', s.nombre_seccion) AS nombre 
                  FROM secciones s
                  INNER JOIN grados g ON g.id_grado = s.id_grado
                  WHERE s.estado = 1 ORDER BY g.id_grado, s.nombre_seccion";
$query_secciones = $pdo->prepare($sql_secciones);
$query_secciones->execute();
$secciones = $query_secciones->fetchAll(PDO::FETCH_ASSOC);

$id_seccion = $_GET['seccion'] ?? '';
$id_materia = $_GET['materia'] ?? '';

$resultados = [];

if ($id_seccion && $id_materia) {
    // Buscar estudiantes con materia pendiente (tras revisión)
    $sql = "SELECT DISTINCT e.id_estudiante, e.nombres, e.apellidos, n.calificacion,
                   (SELECT COUNT(*) FROM recuperaciones r WHERE r.id_estudiante=e.id_estudiante AND r.id_materia=n.id_materia AND r.tipo='PENDIENTE') AS intentos
            FROM inscripciones i
            INNER JOIN estudiantes e ON e.id_estudiante = i.id_estudiante
            INNER JOIN notas_estudiantes n ON n.id_estudiante = e.id_estudiante
            WHERE i.id_seccion = :id_seccion 
              AND n.id_materia = :id_materia
              AND i.estado = 'activo'
              AND n.calificacion < 10";
    $query = $pdo->prepare($sql);
    $query->execute([':id_seccion'=>$id_seccion, ':id_materia'=>$id_materia]);
    $resultados = $query->fetchAll(PDO::FETCH_ASSOC);
}
?>

<div class="content-wrapper">
    <div class="content">
        <div class="container-fluid">

            <div class="content-header">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h3 class="m-0 text-primary"><i class="fas fa-book"></i> Recuperación - Materias Pendientes</h3>
                    </div>
                </div>
            </div>

            <div class="card card-primary card-outline">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-filter"></i> Filtros</h5>
                </div>
                <form method="get">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-5">
                                <label>Sección</label>
                                <select name="seccion" class="form-control" required>
                                    <option value="">Seleccionar</option>
                                    <?php foreach($secciones as $s): ?>
                                        <option value="<?= $s['id_seccion'] ?>" <?= $id_seccion==$s['id_seccion']?'selected':'' ?>>
                                            <?= htmlspecialchars($s['nombre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label>Materia</label>
                                <select name="materia" class="form-control" required>
                                    <option value="">Seleccionar</option>
                                    <?php
                                    if ($id_seccion) {
                                        $sql_mat = "SELECT m.id_materia, m.nombre_materia
                                                    FROM asignaciones_profesor ap
                                                    INNER JOIN materias m ON m.id_materia = ap.id_materia
                                                    WHERE ap.id_seccion = :id_seccion AND ap.estado = 1";
                                        $stm = $pdo->prepare($sql_mat);
                                        $stm->bindParam(':id_seccion', $id_seccion);
                                        $stm->execute();
                                        foreach ($stm as $mat) {
                                            $sel = ($id_materia == $mat['id_materia']) ? 'selected' : '';
                                            echo "<option value='{$mat['id_materia']}' $sel>".htmlspecialchars($mat['nombre_materia'])."</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-2 text-center">
                                <label>&nbsp;</label><br>
                                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Buscar</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <?php if($id_seccion && $id_materia): ?>
                <div class="card card-success card-outline mt-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-list"></i> Estudiantes con Materia Pendiente</h5>
                    </div>
                    <form id="formPendiente" method="post" action="guardar_recuperacion_pendiente.php">
                        <input type="hidden" name="id_seccion" value="<?= $id_seccion ?>">
                        <input type="hidden" name="id_materia" value="<?= $id_materia ?>">
                        <input type="hidden" name="tipo" value="PENDIENTE">

                        <div class="card-body">
                            <?php if(empty($resultados)): ?>
                                <div class="alert alert-info text-center">✅ No hay estudiantes con materia pendiente.</div>
                            <?php else: ?>
                                <table class="table table-bordered table-hover text-center">
                                    <thead class="bg-primary text-white">
                                        <tr>
                                            <th>#</th>
                                            <th>Estudiante</th>
                                            <th>Nota Final</th>
                                            <th>Intentos</th>
                                            <th>Nota Recuperación</th>
                                            <th>Observaciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $n=1; foreach($resultados as $r): ?>
                                            <tr>
                                                <td><?= $n++ ?></td>
                                                <td class="text-left"><?= htmlspecialchars($r['nombres'].' '.$r['apellidos']) ?></td>
                                                <td><span class="badge badge-danger"><?= $r['calificacion'] ?></span></td>
                                                <td>
                                                    <?= $r['intentos'] ?>/4
                                                    <?= $r['intentos']>=4 ? '<span class="badge badge-dark">Límite</span>' : '' ?>
                                                </td>
                                                <td>
                                                    <?php if($r['intentos'] < 4): ?>
                                                        <input type="number" name="notas[<?= $r['id_estudiante'] ?>]" class="form-control" step="0.01" min="0" max="20" required>
                                                    <?php else: ?>
                                                        <span class="badge badge-secondary">Sin intentos</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><input type="text" name="observaciones[<?= $r['id_estudiante'] ?>]" class="form-control"></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <div class="text-center mt-3">
                                    <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Guardar Notas</button>
                                </div>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<script>
document.getElementById('formPendiente')?.addEventListener('submit', function(e){
    e.preventDefault();
    Swal.fire({
        title: '¿Registrar notas de recuperación?',
        text: 'Esta acción actualizará las calificaciones definitivas si aprueban.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, registrar',
        cancelButtonText: 'Cancelar'
    }).then(result => {
        if(result.isConfirmed){
            this.submit();
        }
    });
});
</script>

<?php include('../../admin/layout/parte2.php'); ?>
