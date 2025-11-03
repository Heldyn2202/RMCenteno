<?php
require_once('verificar_docente.php');
$datos_docente = verificarDocente();
include('../../admin/layout/parte1.php');

// ✅ Consulta el historial con nombres legibles
$sql = "SELECT hn.*, e.nombres, e.apellidos, m.nombre_materia, l.nombre_lapso
        FROM historial_notas hn
        INNER JOIN estudiantes e ON hn.id_estudiante = e.id_estudiante
        INNER JOIN materias m ON hn.id_materia = m.id_materia
        INNER JOIN lapsos l ON hn.id_lapso = l.id_lapso
        WHERE hn.estado = 1
        ORDER BY hn.fecha_cambio DESC";
$query = $pdo->prepare($sql);
$query->execute();
$historial = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="content-wrapper">
    <div class="content">
        <div class="container">
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">Historial de Cambios en Notas</h1>
                        </div>
                        <div class="col-sm-6 text-right">
                            <!-- ✅ Mostrar nombre completo del docente -->
                            <span class="badge badge-info p-2">
                                <i class="fas fa-user"></i> 
                                <?= htmlspecialchars(trim(($datos_docente['nombre_profesor'] ?? '') . ' ' . ($datos_docente['apellido_profesor'] ?? ''))) ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Registro de Cambios</h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered table-striped table-hover">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Fecha Cambio</th>
                                        <th>Estudiante</th>
                                        <th>Materia</th>
                                        <th>Lapso</th>
                                        <th>Nota Anterior</th>
                                        <th>Nota Nueva</th>
                                        <th>Obs. Anterior</th>
                                        <th>Obs. Nueva</th>
                                        <th>Tipo Cambio</th>
                                        <th>Usuario</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $contador = 1; ?>
                                    <?php foreach ($historial as $registro): ?>
                                        <tr>
                                            <td><?= $contador++ ?></td>
                                            <td><?= htmlspecialchars($registro['fecha_cambio']) ?></td>
                                            <td><?= htmlspecialchars($registro['apellidos'] . ', ' . $registro['nombres']) ?></td>
                                            <td><?= htmlspecialchars($registro['nombre_materia']) ?></td>
                                            <td><?= htmlspecialchars($registro['nombre_lapso']) ?></td>
                                            
                                            <!-- Nota anterior -->
                                            <td class="text-center">
                                                <?php if ($registro['calificacion_anterior'] !== null): ?>
                                                    <span class="badge badge-warning"><?= number_format($registro['calificacion_anterior'], 2) ?></span>
                                                <?php else: ?>
                                                    <em>N/A</em>
                                                <?php endif; ?>
                                            </td>

                                            <!-- Nota nueva -->
                                            <td class="text-center">
                                                <span class="badge badge-success"><?= number_format($registro['calificacion_nueva'], 2) ?></span>
                                            </td>

                                            <!-- Observación anterior -->
                                            <td style="max-width: 220px; word-wrap: break-word;">
                                                <?php if (!empty($registro['observaciones_anterior'])): ?>
                                                    <span class="text-muted"><?= nl2br(htmlspecialchars($registro['observaciones_anterior'])) ?></span>
                                                <?php else: ?>
                                                    <em>Sin observación</em>
                                                <?php endif; ?>
                                            </td>

                                            <!-- Observación nueva -->
                                            <td style="max-width: 220px; word-wrap: break-word;">
                                                <?php if (!empty($registro['observaciones_nueva'])): ?>
                                                    <span class="text-dark"><?= nl2br(htmlspecialchars($registro['observaciones_nueva'])) ?></span>
                                                <?php else: ?>
                                                    <em>Sin observación</em>
                                                <?php endif; ?>
                                            </td>

                                            <!-- ✅ Tipo de cambio en mayúsculas -->
                                            <td class="text-center">
                                                <span class="badge badge-info text-uppercase">
                                                    <?= htmlspecialchars(strtoupper($registro['tipo_cambio'])) ?>
                                                </span>
                                            </td>

                                            <!-- ✅ Usuario (nombre completo del docente o quien hizo el cambio) -->
                                            <td class="text-center">
                                                <span class="badge badge-secondary">
                                                    <?= htmlspecialchars($registro['usuario_cambio']) ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>

                            <?php if (empty($historial)): ?>
                                <div class="alert alert-warning text-center mt-3">
                                    No hay registros de cambios en el historial.
                                </div>
                            <?php endif; ?>
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
