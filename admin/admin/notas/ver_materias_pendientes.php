<?php
session_start();
require_once('../../app/config.php');
include('../../admin/layout/parte1.php');

$id_seccion = $_GET['seccion'] ?? null;

if (!$id_seccion) {
    echo "<script>alert('Datos incompletos.'); window.location.href='seleccion_materia_pendiente.php';</script>";
    exit;
}

// Obtener info de sección
$sql_seccion = "SELECT s.nombre_seccion, g.grado, g.nivel 
                FROM secciones s 
                INNER JOIN grados g ON s.id_grado = g.id_grado 
                WHERE s.id_seccion = :id_seccion";
$stmt_s = $pdo->prepare($sql_seccion);
$stmt_s->execute([':id_seccion' => $id_seccion]);
$seccion_info = $stmt_s->fetch(PDO::FETCH_ASSOC);

// Formatear el grado
$grado = $seccion_info['grado'] ?? '';
$grado_display = $grado;
if (strpos($grado, 'AÑO') !== false) {
    $grado_display = trim(str_replace('AÑO', '', $grado));
}

// Obtener materias pendientes para esta sección
$sql_materias = "
    SELECT DISTINCT 
        m.id_materia,
        m.nombre_materia,
        COUNT(DISTINCT mp.id_estudiante) as total_estudiantes
    FROM materias_pendientes mp
    INNER JOIN materias m ON mp.id_materia = m.id_materia
    WHERE mp.id_seccion = :id_seccion 
      AND mp.estado = 'pendiente'
    GROUP BY m.id_materia, m.nombre_materia
    ORDER BY m.nombre_materia
";

$stmt = $pdo->prepare($sql_materias);
$stmt->execute([':id_seccion' => $id_seccion]);
$materias = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="content-wrapper">
    <div class="content">
        <div class="container-fluid">
            <div class="content-header">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h3 class="m-0 text-warning">
                            <i class="fas fa-tasks"></i> Materias Pendientes
                        </h3>
                        <h6 class="text-muted">
                            Año/Sección: <strong>
                                <?= htmlspecialchars("{$seccion_info['nivel']} - $grado_display - Sección {$seccion_info['nombre_seccion']}") ?>
                            </strong>
                        </h6>
                    </div>
                    <div class="col-sm-6 text-right">
                        <a href="seleccion_materia_pendiente.php" class="btn btn-dark">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                    </div>
                </div>
            </div>

            <div class="card card-outline card-warning shadow-sm">
                <div class="card-header bg-warning text-white">
                    <h5 class="mb-0"><i class="fas fa-book"></i> Lista de Materias Pendientes</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($materias)): ?>
                        <div class="alert alert-success text-center">
                            <i class="fas fa-check-circle"></i> ¡No hay materias pendientes en esta sección!
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($materias as $materia): ?>
                                <div class="col-md-4 mb-3">
                                    <div class="card border-warning h-100">
                                        <div class="card-body">
                                            <h5 class="card-title text-warning">
                                                <i class="fas fa-book"></i> 
                                                <?= htmlspecialchars($materia['nombre_materia']) ?>
                                            </h5>
                                            <p class="card-text">
                                                <span class="badge badge-warning">
                                                    <i class="fas fa-users"></i> 
                                                    <?= $materia['total_estudiantes'] ?> estudiante(s)
                                                </span>
                                            </p>
                                            <a href="recuperaciones_pendientes.php?seccion=<?= $id_seccion ?>&materia=<?= $materia['id_materia'] ?>" 
                                               class="btn btn-warning btn-block">
                                               <i class="fas fa-edit"></i> Gestionar Recuperaciones
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('../../admin/layout/parte2.php'); ?>