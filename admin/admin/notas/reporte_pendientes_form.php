<?php
// reporte_pendientes_form.php
session_start();
require_once('../../app/config.php');
include('../../admin/layout/parte1.php');

// Obtener lista de grados disponibles
$sql_grados = "
    SELECT DISTINCT s.id_grado, g.grado
    FROM secciones s
    LEFT JOIN grados g ON g.id_grado = s.id_grado
    WHERE EXISTS (
        SELECT 1 FROM materias_pendientes mp 
        WHERE mp.id_seccion = s.id_seccion 
        AND mp.estado = 'pendiente'
    )
    ORDER BY s.id_grado ASC
";

// Si no existe tabla grados, usamos solo id_grado
$sql_check = "SHOW TABLES LIKE 'grados'";
$stmt_check = $pdo->query($sql_check);

if ($stmt_check->rowCount() > 0) {
    $stmt_grados = $pdo->prepare($sql_grados);
    $stmt_grados->execute();
    $grados = $stmt_grados->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Solo obtenemos id_grado
    $sql_grados_simple = "
        SELECT DISTINCT id_grado 
        FROM secciones 
        WHERE EXISTS (
            SELECT 1 FROM materias_pendientes mp 
            WHERE mp.id_seccion = secciones.id_seccion 
            AND mp.estado = 'pendiente'
        )
        ORDER BY id_grado ASC
    ";
    $stmt_grados = $pdo->prepare($sql_grados_simple);
    $stmt_grados->execute();
    $grados = $stmt_grados->fetchAll(PDO::FETCH_ASSOC);
}
?>

<div class="content-wrapper">
    <div class="content">
        <div class="container-fluid">
            <div class="content-header">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-dark">
                            <i class="fas fa-file-pdf"></i> Reporte de Materias Pendientes
                        </h1>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 offset-md-3">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Generar Reporte PDF</h3>
                        </div>
                        <div class="card-body">
                            <form action="reportes_pendientes_pdf.php" method="get" target="_blank">
                                <div class="form-group">
                                    <label for="grado"><i class="fas fa-graduation-cap"></i> Filtrar por Grado:</label>
                                    <select name="grado" id="grado" class="form-control">
                                        <option value="0">Todos los grados</option>
                                        <?php foreach ($grados as $grado): ?>
                                            <option value="<?= $grado['id_grado'] ?>">
                                                <?= isset($grado['nombre']) ? $grado['nombre'] : "Grado " . $grado['id_grado'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="text-muted">Seleccione un grado específico o "Todos" para reporte completo</small>
                                </div>
                                
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> 
                                    <strong>Información:</strong> El reporte incluirá:
                                    <ul class="mb-0 mt-2">
                                        <li>Datos del estudiante (cédula, nombre, sexo)</li>
                                        <li>Grado y sección</li>
                                        <li>Materia pendiente</li>
                                        <li>Notas por momentos (I, II, III)</li>
                                        <li>Nota definitiva</li>
                                        <li>Profesor asignado</li>
                                    </ul>
                                </div>
                                
                                <div class="form-group text-center mt-4">
                                    <button type="submit" class="btn btn-success btn-lg">
                                        <i class="fas fa-file-pdf"></i> Generar Reporte
                                    </button>
                                    <a href="index.php" class="btn btn-secondary btn-lg">
                                        <i class="fas fa-times"></i> Cancelar
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Estadísticas -->
                    <div class="card card-info mt-4">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-chart-bar"></i> Estadísticas</h3>
                        </div>
                        <div class="card-body">
                            <?php
                            // Contar total de pendientes
                            $sql_total = "
                                SELECT COUNT(*) as total 
                                FROM materias_pendientes 
                                WHERE estado = 'pendiente'
                            ";
                            $stmt_total = $pdo->query($sql_total);
                            $total = $stmt_total->fetch(PDO::FETCH_ASSOC);
                            
                            // Contar por grado
                            $sql_por_grado = "
                                SELECT 
                                    s.id_grado,
                                    COUNT(*) as cantidad,
                                    g.grado as nombre_grado
                                FROM materias_pendientes mp
                                INNER JOIN secciones s ON s.id_seccion = mp.id_seccion
                                LEFT JOIN grados g ON g.id_grado = s.id_grado
                                WHERE mp.estado = 'pendiente'
                                GROUP BY s.id_grado
                                ORDER BY s.id_grado
                            ";
                            $stmt_grado = $pdo->query($sql_por_grado);
                            $por_grado = $stmt_grado->fetchAll(PDO::FETCH_ASSOC);
                            ?>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info-box bg-success">
                                        <span class="info-box-icon"><i class="fas fa-users"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Total Pendientes</span>
                                            <span class="info-box-number"><?= $total['total'] ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-box bg-primary">
                                        <span class="info-box-icon"><i class="fas fa-graduation-cap"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Grados con pendientes</span>
                                            <span class="info-box-number"><?= count($por_grado) ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <h5 class="mt-4">Distribución por Grado:</h5>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Grado</th>
                                            <th>Cantidad</th>
                                            <th>Porcentaje</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($por_grado as $item): ?>
                                            <tr>
                                                <td>
                                                    <?= isset($item['nombre_grado']) ? $item['nombre_grado'] : "Grado " . $item['id_grado'] ?>
                                                </td>
                                                <td>
                                                    <span class="badge badge-primary"><?= $item['cantidad'] ?></span>
                                                </td>
                                                <td>
                                                    <div class="progress">
                                                        <div class="progress-bar" role="progressbar" 
                                                             style="width: <?= ($item['cantidad'] / $total['total']) * 100 ?>%" 
                                                             aria-valuenow="<?= ($item['cantidad'] / $total['total']) * 100 ?>" 
                                                             aria-valuemin="0" aria-valuemax="100">
                                                        </div>
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

<?php include('../../admin/layout/parte2.php'); ?>