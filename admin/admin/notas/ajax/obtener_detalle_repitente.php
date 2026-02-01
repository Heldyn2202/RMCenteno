<?php
session_start();
require_once('../config.php');

if (!isset($_POST['id_estudiante']) || !isset($_POST['id_materia']) || !isset($_POST['id_seccion'])) {
    echo '<div class="alert alert-danger">Datos insuficientes</div>';
    exit;
}

$id_estudiante = $_POST['id_estudiante'];
$id_materia = $_POST['id_materia'];
$id_seccion = $_POST['id_seccion'];

// Obtener información completa del estudiante repitente - CORREGIDO
$sql_estudiante = "
    SELECT 
        ea.*,
        e.*,
        m.nombre_materia,
        s.nombre_seccion,
        g.grado,
        g.nivel,
        DATE_FORMAT(e.fecha_nacimiento, '%d/%m/%Y') as fecha_nacimiento_formateada,
        e.numeros_telefonicos as telefono,      -- CAMBIADO
        e.correo_electronico as correo          -- CAMBIADO
    FROM estudiantes_aplazados ea
    INNER JOIN estudiantes e ON ea.id_estudiante = e.id_estudiante
    INNER JOIN materias m ON ea.id_materia = m.id_materia
    INNER JOIN secciones s ON ea.id_seccion = s.id_seccion
    INNER JOIN grados g ON s.id_grado = g.id_grado
    WHERE ea.id_estudiante = :id_estudiante
      AND ea.id_materia = :id_materia
      AND ea.id_seccion = :id_seccion
    LIMIT 1
";

$stmt = $pdo->prepare($sql_estudiante);
$stmt->execute([
    ':id_estudiante' => $id_estudiante,
    ':id_materia' => $id_materia,
    ':id_seccion' => $id_seccion
]);

$estudiante = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$estudiante) {
    echo '<div class="alert alert-danger">Estudiante no encontrado</div>';
    exit;
}

// Obtener historial de recuperaciones
$sql_historial = "
    SELECT 
        r.intento,
        r.calificacion,
        r.observaciones,
        r.fecha_registro
    FROM recuperaciones r
    WHERE r.id_estudiante = :id_estudiante
      AND r.id_materia = :id_materia
      AND r.id_seccion = :id_seccion
      AND r.tipo = 'pendiente'
    ORDER BY r.intento
";

$stmt_h = $pdo->prepare($sql_historial);
$stmt_h->execute([
    ':id_estudiante' => $id_estudiante,
    ':id_materia' => $id_materia,
    ':id_seccion' => $id_seccion
]);

$historial = $stmt_h->fetchAll(PDO::FETCH_ASSOC);
?>

<div data-estudiante-id="<?= $id_estudiante ?>" data-materia-id="<?= $id_materia ?>">
    <!-- Información personal -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card border-danger">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <div class="mb-3">
                                <i class="fas fa-user-graduate fa-4x text-danger"></i>
                            </div>
                            <h5 class="text-danger font-weight-bold"><?= htmlspecialchars($estudiante['apellidos'] . ', ' . $estudiante['nombres']) ?></h5>
                            <p class="mb-0"><strong>Cédula:</strong> <?= htmlspecialchars($estudiante['cedula']) ?></p>
                        </div>
                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong><i class="fas fa-calendar"></i> Fecha de Nacimiento:</strong><br>
                                    <?= $estudiante['fecha_nacimiento_formateada'] ?></p>
                                    <p><strong><i class="fas fa-phone"></i> Teléfono:</strong><br>
                                    <?= htmlspecialchars($estudiante['telefono'] ?? 'No registrado') ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong><i class="fas fa-envelope"></i> Correo:</strong><br>
                                    <?= htmlspecialchars($estudiante['correo'] ?? 'No registrado') ?></p>
                                    <p><strong><i class="fas fa-map-marker-alt"></i> Dirección:</strong><br>
                                    <?= htmlspecialchars($estudiante['direccion'] ?? 'No registrada') ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Información académica -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card border-warning">
                <div class="card-header bg-warning text-white">
                    <h6 class="mb-0"><i class="fas fa-graduation-cap"></i> Información Académica</h6>
                </div>
                <div class="card-body">
                    <p><strong>Grado - Sección:</strong><br>
                    <?= htmlspecialchars($estudiante['grado'] . ' - ' . $estudiante['nivel']) ?> (<?= htmlspecialchars($estudiante['nombre_seccion']) ?>)</p>
                    <p><strong>Materia Reprobada:</strong><br>
                    <?= htmlspecialchars($estudiante['nombre_materia']) ?></p>
                    <p><strong>Nota Final:</strong><br>
                    <span class="badge badge-danger" style="font-size: 1.2em; padding: 8px 15px;">
                        <?= $estudiante['nota_final'] ?>/20
                    </span></p>
                    <p><strong>Intentos Completados:</strong><br>
                    <?= $estudiante['intentos_completados'] ?> de 4 momentos</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-info">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="fas fa-calendar-alt"></i> Información del Aplazo</h6>
                </div>
                <div class="card-body">
                    <p><strong>Fecha de Aplazo:</strong><br>
                    <?= date('d/m/Y H:i:s', strtotime($estudiante['fecha_aplazado'])) ?></p>
                    <p><strong>Estado Actual:</strong><br>
                    <?php if ($estudiante['estado'] == 'pendiente'): ?>
                        <span class="badge badge-warning">PENDIENTE DE REINSCRIPCIÓN</span>
                    <?php elseif ($estudiante['estado'] == 'reinscrito'): ?>
                        <span class="badge badge-info">REINSCRITO</span>
                    <?php elseif ($estudiante['estado'] == 'aprobado'): ?>
                        <span class="badge badge-success">APROBADO POSTERIORMENTE</span>
                    <?php else: ?>
                        <span class="badge badge-secondary"><?= strtoupper($estudiante['estado']) ?></span>
                    <?php endif; ?>
                    </p>
                    <p><strong>Motivo:</strong><br>
                    <small class="text-muted"><?= htmlspecialchars($estudiante['motivo']) ?></small></p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Historial de intentos -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-history"></i> Historial de Intentos de Recuperación</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($historial)): ?>
                        <p class="text-muted text-center">No hay historial de recuperaciones registrado.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Momento</th>
                                        <th>Nota</th>
                                        <th>Resultado</th>
                                        <th>Fecha</th>
                                        <th>Observaciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($historial as $h): ?>
                                        <tr class="<?= $h['calificacion'] >= 10 ? 'table-success' : 'table-danger' ?>">
                                            <td><strong><?= $h['intento'] ?>°</strong></td>
                                            <td class="font-weight-bold"><?= $h['calificacion'] ?>/20</td>
                                            <td>
                                                <?php if ($h['calificacion'] >= 10): ?>
                                                    <span class="badge badge-success">APROBADO</span>
                                                <?php else: ?>
                                                    <span class="badge badge-danger">REPROBADO</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= date('d/m/Y H:i', strtotime($h['fecha_registro'])) ?></td>
                                            <td><small><?= htmlspecialchars($h['observaciones'] ?? '') ?></small></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>