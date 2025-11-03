<?php
include ('../../app/config.php');
include ('../../admin/layout/parte1.php');

// Obtener estudiantes activos
$query_estudiantes = $pdo->prepare("SELECT * FROM estudiantes WHERE estatus = 'activo'");
$query_estudiantes->execute();
$estudiantes = $query_estudiantes->fetchAll(PDO::FETCH_ASSOC);

// Obtener plantillas activas
$query_plantillas = $pdo->prepare("SELECT * FROM plantillas_carnet WHERE estatus = 'activo'");
$query_plantillas->execute();
$plantillas = $query_plantillas->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="content-wrapper">
    <br>
    <div class="content">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Generar Carnets Estudiantiles</h3>
                        </div>
                        <div class="card-body">
                            <form action="../../app/controllers/carnets/generar_carnets.php" method="POST" target="_blank">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Seleccione Plantilla:</label>
                                            <select name="id_plantilla" class="form-control" required>
                                                <option value="">Seleccione</option>
                                                <?php foreach ($plantillas as $plantilla): ?>
                                                    <option value="<?= $plantilla['id_plantilla'] ?>"><?= $plantilla['nombre'] ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Fecha de Vencimiento:</label>
                                            <input type="date" name="fecha_vencimiento" class="form-control" required min="<?= date('Y-m-d') ?>">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Estudiantes:</label>
                                            <select name="estudiantes[]" class="form-control" data-placeholder="Seleccione estudiantes" style="width: 100%;" required>
                                                <?php foreach ($estudiantes as $estudiante): ?>
                                                    <option value="<?= $estudiante['id_estudiante'] ?>" selected>
                                                        <?= $estudiante['nombres'] ?> <?= $estudiante['apellidos'] ?> - <?= $estudiante['cedula'] ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-12">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-id-card"></i> Generar Carnets
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(function () {
        
        // Datatable para plantillas
        $('#tabla-plantillas').DataTable({
            "responsive": true,
            "autoWidth": false,
        });
    });
</script>

<?php include ('../../admin/layout/parte2.php'); ?>