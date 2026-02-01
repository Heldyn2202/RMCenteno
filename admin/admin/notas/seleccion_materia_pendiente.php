<?php
session_start();
require_once('../../app/config.php');
include('../../admin/layout/parte1.php');
include('../../layout/mensajes.php');

// Obtener la gestión activa actual
$sql_gestion = "SELECT CONCAT('Periodo ', DATE_FORMAT(desde, '%Y'), ' - ', DATE_FORMAT(hasta, '%Y')) AS nombre 
                FROM gestiones WHERE estado = 1 LIMIT 1";
$stmt_g = $pdo->query($sql_gestion);
$gestion_info = $stmt_g->fetch(PDO::FETCH_ASSOC);
$gestion_nombre = $gestion_info['nombre'] ?? 'Gestión no definida';

// ==== OBTENER TODAS LAS MATERIAS PENDIENTES ====
$sql_pendientes = "
    SELECT DISTINCT
        mp.id_pendiente,
        mp.id_estudiante,
        mp.id_materia,
        mp.id_seccion,
        CONCAT(e.nombres, ' ', e.apellidos) as estudiante_completo,
        e.cedula,
        m.nombre_materia,
        s.nombre_seccion,
        g.grado,
        g.nivel,
        CONCAT(p.nombres, ' ', p.apellidos) as profesor_completo,
        ap.estado as estado_asignacion,
        mp.estado as estado_pendiente,
        mp.fecha_registro
    FROM materias_pendientes mp
    INNER JOIN estudiantes e ON mp.id_estudiante = e.id_estudiante
    INNER JOIN materias m ON mp.id_materia = m.id_materia
    INNER JOIN secciones s ON mp.id_seccion = s.id_seccion
    INNER JOIN grados g ON s.id_grado = g.id_grado
    LEFT JOIN asignaciones_profesor ap ON mp.id_materia = ap.id_materia AND mp.id_seccion = ap.id_seccion
    LEFT JOIN profesores p ON ap.id_profesor = p.id_profesor
    WHERE mp.estado IN ('pendiente', 'aprobado', 'aplazado')
    ORDER BY g.nivel, g.grado, s.nombre_seccion, e.apellidos, e.nombres
";

$stmt_pend = $pdo->query($sql_pendientes);
$pendientes = $stmt_pend->fetchAll(PDO::FETCH_ASSOC);

// Obtener las recuperaciones por estudiante y materia
$recuperaciones_data = [];
if (!empty($pendientes)) {
    // Obtener todos los IDs únicos
    $estudiante_ids = array_unique(array_column($pendientes, 'id_estudiante'));
    $materia_ids = array_unique(array_column($pendientes, 'id_materia'));
    $seccion_ids = array_unique(array_column($pendientes, 'id_seccion'));
    
    $estudiante_ids_str = implode(',', $estudiante_ids);
    $materia_ids_str = implode(',', $materia_ids);
    $seccion_ids_str = implode(',', $seccion_ids);
    
    $sql_recuperaciones = "
        SELECT 
            r.id_estudiante,
            r.id_materia,
            r.id_seccion,
            r.intento,
            r.calificacion,
            r.tipo,
            r.fecha_registro
        FROM recuperaciones r
        WHERE r.tipo = 'pendiente'
          AND r.id_estudiante IN ($estudiante_ids_str)
          AND r.id_materia IN ($materia_ids_str)
          AND r.id_seccion IN ($seccion_ids_str)
        ORDER BY r.id_estudiante, r.id_materia, r.id_seccion, r.intento
    ";
    
    $stmt_rec = $pdo->query($sql_recuperaciones);
    $recuperaciones = $stmt_rec->fetchAll(PDO::FETCH_ASSOC);
    
    // Organizar recuperaciones por estudiante-materia-sección
    foreach ($recuperaciones as $rec) {
        $key = $rec['id_estudiante'] . '_' . $rec['id_materia'] . '_' . $rec['id_seccion'];
        if (!isset($recuperaciones_data[$key])) {
            $recuperaciones_data[$key] = [];
        }
        $recuperaciones_data[$key][] = $rec;
    }
}

// Agrupar por grado y sección
$grados_agrupados = [];

foreach ($pendientes as $pend) {
    // 1er nivel: GRADO
    $grado_key = $pend['grado'] . ' - ' . $pend['nivel'];
    
    // 2do nivel: SECCIÓN
    $seccion_key = $pend['nombre_seccion'];
    
    // 3er nivel: MATERIA
    $materia_key = $pend['id_materia'] . '_' . $pend['id_seccion'];
    
    // Inicializar estructura
    if (!isset($grados_agrupados[$grado_key])) {
        $grados_agrupados[$grado_key] = [
            'grado' => $pend['grado'],
            'nivel' => $pend['nivel'],
            'secciones' => [],
            'total_estudiantes' => 0,
            'aprobados' => 0,
            'en_proceso' => 0,
            'aplazados' => 0
        ];
    }
    
    if (!isset($grados_agrupados[$grado_key]['secciones'][$seccion_key])) {
        $grados_agrupados[$grado_key]['secciones'][$seccion_key] = [
            'nombre_seccion' => $seccion_key,
            'materias' => [],
            'total_estudiantes' => 0,
            'aprobados' => 0,
            'en_proceso' => 0,
            'aplazados' => 0
        ];
    }
    
    if (!isset($grados_agrupados[$grado_key]['secciones'][$seccion_key]['materias'][$materia_key])) {
        $grados_agrupados[$grado_key]['secciones'][$seccion_key]['materias'][$materia_key] = [
            'id_materia' => $pend['id_materia'],
            'id_seccion' => $pend['id_seccion'],
            'nombre_materia' => $pend['nombre_materia'],
            'nombre_seccion' => $pend['nombre_seccion'],
            'profesor_completo' => $pend['profesor_completo'] ?? 'No asignado',
            'estado_asignacion' => $pend['estado_asignacion'] ?? 0,
            'estudiantes' => [],
            'total_estudiantes' => 0,
            'estudiantes_por_momento' => [
                1 => 0, 2 => 0, 3 => 0, 4 => 0,
                'aprobados' => 0,
                'en_proceso' => 0,
                'aplazados' => 0
            ],
            'hay_aplazados' => false,
            'hay_aprobados' => false
        ];
    }
    
    // Verificar si este estudiante ya está registrado en esta materia
    $estudiante_existente = false;
    foreach ($grados_agrupados[$grado_key]['secciones'][$seccion_key]['materias'][$materia_key]['estudiantes'] as $est) {
        if ($est['id_estudiante'] == $pend['id_estudiante']) {
            $estudiante_existente = true;
            break;
        }
    }
    
    if ($estudiante_existente) {
        continue; // Saltar estudiante duplicado
    }
    
    // Obtener recuperaciones para este estudiante-materia-sección
    $key_recuperacion = $pend['id_estudiante'] . '_' . $pend['id_materia'] . '_' . $pend['id_seccion'];
    $recuperaciones_est = $recuperaciones_data[$key_recuperacion] ?? [];
    
    // Calcular estado basado en las recuperaciones
    $ultimo_intento = 0;
    $ultima_nota = 0;
    $nota_aprobacion = 0;
    $aprobo_en_algun_momento = false;
    $momento_aprobacion = 0;
    
    if (!empty($recuperaciones_est)) {
        // Encontrar el último intento
        $ultimo_rec = end($recuperaciones_est);
        $ultimo_intento = $ultimo_rec['intento'];
        $ultima_nota = $ultimo_rec['calificacion'];
        
        // Verificar si aprobó en algún momento
        foreach ($recuperaciones_est as $rec) {
            if ($rec['calificacion'] >= 10) {
                $aprobo_en_algun_momento = true;
                $nota_aprobacion = $rec['calificacion'];
                $momento_aprobacion = $rec['intento'];
                break;
            }
        }
    }
    
    // Determinar estado final
    $estado = 'en_proceso';
    $proximo_intento = $ultimo_intento + 1;
    
    if ($aprobo_en_algun_momento) {
        $estado = 'aprobado';
        $proximo_intento = 0;
        $grados_agrupados[$grado_key]['secciones'][$seccion_key]['materias'][$materia_key]['hay_aprobados'] = true;
    } elseif ($ultimo_intento >= 4 && $ultima_nota < 10) {
        $estado = 'aplazado';
        $proximo_intento = 0;
        $grados_agrupados[$grado_key]['secciones'][$seccion_key]['materias'][$materia_key]['hay_aplazados'] = true;
    } elseif ($ultimo_intento > 0 && $ultimo_intento < 4) {
        $estado = 'en_proceso';
    }
    
    // Agregar estudiante
    $estudiante_data = [
        'id_estudiante' => $pend['id_estudiante'],
        'estudiante_completo' => $pend['estudiante_completo'],
        'cedula' => $pend['cedula'],
        'ultimo_intento' => $ultimo_intento,
        'ultima_nota' => $ultima_nota,
        'nota_aprobacion' => $nota_aprobacion,
        'momento_aprobacion' => $momento_aprobacion,
        'nota_redondeada' => round($aprobo_en_algun_momento ? $nota_aprobacion : $ultima_nota, 0, PHP_ROUND_HALF_UP),
        'proximo_intento' => $proximo_intento,
        'estado' => $estado,
        'fecha_registro' => $pend['fecha_registro'],
        'recuperaciones' => $recuperaciones_est
    ];
    
    $grados_agrupados[$grado_key]['secciones'][$seccion_key]['materias'][$materia_key]['estudiantes'][] = $estudiante_data;
    
    // Actualizar contadores
    $grados_agrupados[$grado_key]['secciones'][$seccion_key]['materias'][$materia_key]['total_estudiantes']++;
    
    if ($estado == 'aprobado') {
        $grados_agrupados[$grado_key]['secciones'][$seccion_key]['materias'][$materia_key]['estudiantes_por_momento']['aprobados']++;
    } elseif ($estado == 'aplazado') {
        $grados_agrupados[$grado_key]['secciones'][$seccion_key]['materias'][$materia_key]['estudiantes_por_momento']['aplazados']++;
    } elseif ($estado == 'en_proceso') {
        $grados_agrupados[$grado_key]['secciones'][$seccion_key]['materias'][$materia_key]['estudiantes_por_momento']['en_proceso']++;
        if ($proximo_intento >= 1 && $proximo_intento <= 4) {
            $grados_agrupados[$grado_key]['secciones'][$seccion_key]['materias'][$materia_key]['estudiantes_por_momento'][$proximo_intento]++;
        }
    }
    
    // Actualizar contadores de sección
    $grados_agrupados[$grado_key]['secciones'][$seccion_key]['total_estudiantes']++;
    if ($estado == 'aprobado') {
        $grados_agrupados[$grado_key]['secciones'][$seccion_key]['aprobados']++;
    } elseif ($estado == 'aplazado') {
        $grados_agrupados[$grado_key]['secciones'][$seccion_key]['aplazados']++;
    } elseif ($estado == 'en_proceso') {
        $grados_agrupados[$grado_key]['secciones'][$seccion_key]['en_proceso']++;
    }
    
    // Actualizar contadores de grado
    $grados_agrupados[$grado_key]['total_estudiantes']++;
    if ($estado == 'aprobado') {
        $grados_agrupados[$grado_key]['aprobados']++;
    } elseif ($estado == 'aplazado') {
        $grados_agrupados[$grado_key]['aplazados']++;
    } elseif ($estado == 'en_proceso') {
        $grados_agrupados[$grado_key]['en_proceso']++;
    }
}

// ==== OBTENER HISTORIAL COMPLETO ====
$historial_por_estudiante = [];
$historial_por_materia = [];

if (!empty($pendientes)) {
    // Obtener historial COMPLETO
    $sql_historial = "
        SELECT 
            r.*,
            e.nombres,
            e.apellidos,
            m.nombre_materia,
            s.nombre_seccion,
            g.grado,
            g.nivel
        FROM recuperaciones r
        INNER JOIN estudiantes e ON r.id_estudiante = e.id_estudiante
        INNER JOIN materias m ON r.id_materia = m.id_materia
        INNER JOIN secciones s ON r.id_seccion = s.id_seccion
        INNER JOIN grados g ON s.id_grado = g.id_grado
        WHERE r.tipo = 'pendiente'
          AND r.id_estudiante IN (SELECT id_estudiante FROM materias_pendientes WHERE estado IN ('pendiente', 'aprobado', 'aplazado'))
        ORDER BY r.id_estudiante, r.id_materia, r.id_seccion, r.intento
    ";
    
    $stmt_hist = $pdo->query($sql_historial);
    $historial_completo = $stmt_hist->fetchAll(PDO::FETCH_ASSOC);
    
    // Organizar historial
    foreach ($historial_completo as $hist) {
        $estudiante_id = $hist['id_estudiante'];
        $materia_id = $hist['id_materia'];
        $seccion_id = $hist['id_seccion'];
        
        $key_estudiante_materia = $estudiante_id . '_' . $materia_id . '_' . $seccion_id;
        $key_materia = $materia_id . '_' . $seccion_id;
        
        if (!isset($historial_por_estudiante[$key_estudiante_materia])) {
            $historial_por_estudiante[$key_estudiante_materia] = [];
        }
        
        if (!isset($historial_por_materia[$key_materia])) {
            $historial_por_materia[$key_materia] = [];
        }
        
        $historial_por_estudiante[$key_estudiante_materia][] = [
            'intento' => (int)$hist['intento'],
            'calificacion' => (float)$hist['calificacion'],
            'observaciones' => $hist['observaciones'] ?? '',
            'fecha_registro' => $hist['fecha_registro'],
            'nombre_completo' => trim($hist['nombres'] . ' ' . $hist['apellidos'])
        ];
        
        $historial_por_materia[$key_materia][] = [
            'id_estudiante' => $estudiante_id,
            'nombre_completo' => trim($hist['nombres'] . ' ' . $hist['apellidos']),
            'intento' => (int)$hist['intento'],
            'calificacion' => (float)$hist['calificacion'],
            'observaciones' => $hist['observaciones'] ?? '',
            'fecha_registro' => $hist['fecha_registro'],
            'resultado' => ((float)$hist['calificacion'] >= 10) ? 'aprobado' : 'reprobado'
        ];
    }
}

// Calcular estadísticas generales
$total_materias = 0;
$total_estudiantes = 0;
$estudiantes_aprobados = 0;
$estudiantes_en_proceso = 0;
$estudiantes_aplazados = 0;

foreach ($grados_agrupados as $grado) {
    $total_estudiantes += $grado['total_estudiantes'];
    $estudiantes_aprobados += $grado['aprobados'];
    $estudiantes_en_proceso += $grado['en_proceso'];
    $estudiantes_aplazados += $grado['aplazados'];
    
    foreach ($grado['secciones'] as $seccion) {
        $total_materias += count($seccion['materias']);
    }
}
?>

<style>
:root {
    --primary-blue: #2c80ff;
    --light-blue: #e8f2ff;
    --dark-blue: #1a5cb8;
}

.card-materia {
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    margin-bottom: 15px;
    background: white;
    transition: all 0.3s ease;
}
.card-materia:hover {
    border-color: var(--primary-blue);
    box-shadow: 0 3px 10px rgba(44, 128, 255, 0.1);
}

.estudiante-item {
    padding: 8px 10px;
    border-bottom: 1px solid #f5f5f5;
    font-size: 0.9em;
}
.estudiante-item:last-child {
    border-bottom: none;
}

.estado-badge {
    padding: 3px 8px;
    border-radius: 12px;
    font-size: 0.8em;
    font-weight: 600;
}
.estado-aprobado { background: #d4edda; color: #155724; }
.estado-proceso { background: #fff3cd; color: #856404; }
.estado-aplazado { background: #f8d7da; color: #721c24; }

.momento-badge {
    padding: 2px 6px;
    border-radius: 10px;
    font-size: 0.75em;
    font-weight: 600;
}
.momento-1 { background: #e3f2fd; color: #0d47a1; }
.momento-2 { background: #e8f5e9; color: #1b5e20; }
.momento-3 { background: #fff3e0; color: #e65100; }
.momento-4 { background: #ffebee; color: #b71c1c; }

.btn-gestionar-disabled {
    opacity: 0.6;
    cursor: not-allowed !important;
}

.badge-aprobado-materia {
    background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
    color: white;
    border: none;
}
.badge-aplazado-materia {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    color: white;
    border: none;
}

.nota-aprobacion {
    font-weight: bold;
    color: #28a745;
    background: #d4edda;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 0.9em;
}

.estudiante-scroll {
    overflow-y: auto;
    max-height: 150px;
}

.estudiante-scroll::-webkit-scrollbar {
    width: 5px;
}

.estudiante-scroll::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.estudiante-scroll::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 10px;
}

.estudiante-scroll::-webkit-scrollbar-thumb:hover {
    background: #555;
}
</style>

<div class="content-wrapper">
    <div class="content">
        <div class="container-fluid">
            <!-- Header -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="h3 mb-1">
                                <i class="fas fa-tasks text-primary mr-2"></i>
                                Materias Pendientes - Historial Completo
                            </h1>
                            <p class="text-muted mb-0">
                                Gestión: <?= htmlspecialchars($gestion_nombre) ?> | 
                                <span class="badge bg-primary"><?= $total_estudiantes ?> estudiantes</span>
                                <span class="badge bg-success ml-1"><?= $estudiantes_aprobados ?> aprobados</span>
                                <span class="badge bg-warning ml-1"><?= $estudiantes_en_proceso ?> en proceso</span>
                                <span class="badge bg-danger ml-1"><?= $estudiantes_aplazados ?> aplazados</span>
                            </p>
                        </div>
                        <a href="index.php" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left mr-1"></i> Volver
                        </a>
                    </div>
                </div>
            </div>

            <!-- Estadísticas -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card border-left-primary shadow-sm h-100">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Materias Pendientes
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $total_materias ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-book fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-left-success shadow-sm h-100">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Estudiantes Aprobados
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $estudiantes_aprobados ?></div>
                                    <?php if ($estudiantes_aprobados > 0): ?>
                                        <div class="text-xs text-success">
                                            <i class="fas fa-check-circle mr-1"></i>Con nota registrada
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-left-warning shadow-sm h-100">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        En Proceso
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $estudiantes_en_proceso ?></div>
                                    <div class="text-xs text-muted">
                                        <i class="fas fa-clock mr-1"></i>Momentos restantes
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-clock fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-left-danger shadow-sm h-100">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                        Aplazados
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $estudiantes_aplazados ?></div>
                                    <?php if ($estudiantes_aplazados > 0): ?>
                                        <div class="text-xs text-danger">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>4 momentos usados
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (empty($grados_agrupados)): ?>
                <div class="card text-center py-5">
                    <div class="card-body">
                        <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                        <h4 class="text-success mb-3">¡Todo al día!</h4>
                        <p class="text-muted">No hay registros de materias pendientes.</p>
                    </div>
                </div>
            <?php else: ?>
                <!-- Pestañas por GRADO -->
                <div class="card">
                    <div class="card-header bg-white border-bottom">
                        <ul class="nav nav-tabs card-header-tabs" id="gradosTab" role="tablist">
                            <?php $first_grado = true; ?>
                            <?php foreach ($grados_agrupados as $grado_key => $grado_info): ?>
                                <li class="nav-item">
                                    <button class="nav-link <?= $first_grado ? 'active' : '' ?>" 
                                            id="tab-<?= preg_replace('/[^a-zA-Z0-9]/', '-', $grado_key) ?>" 
                                            data-bs-toggle="tab" 
                                            data-bs-target="#grado-<?= preg_replace('/[^a-zA-Z0-9]/', '-', $grado_key) ?>" 
                                            type="button">
                                        <?= htmlspecialchars($grado_key) ?>
                                        <span class="badge bg-primary ms-1"><?= $grado_info['total_estudiantes'] ?></span>
                                        <?php if ($grado_info['aprobados'] > 0): ?>
                                            <span class="badge bg-success ms-1"><?= $grado_info['aprobados'] ?>✓</span>
                                        <?php endif; ?>
                                        <?php if ($grado_info['aplazados'] > 0): ?>
                                            <span class="badge bg-danger ms-1"><?= $grado_info['aplazados'] ?>✗</span>
                                        <?php endif; ?>
                                    </button>
                                </li>
                                <?php $first_grado = false; ?>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content" id="gradosTabContent">
                            <?php $first_grado = true; ?>
                            <?php foreach ($grados_agrupados as $grado_key => $grado_info): ?>
                                <div class="tab-pane fade <?= $first_grado ? 'show active' : '' ?>" 
                                     id="grado-<?= preg_replace('/[^a-zA-Z0-9]/', '-', $grado_key) ?>">
                                    
                                    <!-- Resumen del grado -->
                                    <div class="alert alert-light mb-4">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <h5 class="mb-2"><?= htmlspecialchars($grado_key) ?></h5>
                                                <p class="mb-0 text-muted">
                                                    Historial completo de materias pendientes
                                                </p>
                                            </div>
                                            <div class="col-md-4 text-right">
                                                <div class="d-flex justify-content-end gap-3">
                                                    <div class="text-center">
                                                        <div class="h4 mb-0 text-primary"><?= $grado_info['total_estudiantes'] ?></div>
                                                        <small class="text-muted">Total</small>
                                                    </div>
                                                    <div class="text-center">
                                                        <div class="h4 mb-0 text-success"><?= $grado_info['aprobados'] ?></div>
                                                        <small class="text-muted">Aprobados</small>
                                                    </div>
                                                    <div class="text-center">
                                                        <div class="h4 mb-0 text-warning"><?= $grado_info['en_proceso'] ?></div>
                                                        <small class="text-muted">En Proceso</small>
                                                    </div>
                                                    <div class="text-center">
                                                        <div class="h4 mb-0 text-danger"><?= $grado_info['aplazados'] ?></div>
                                                        <small class="text-muted">Aplazados</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Acordeón de SECCIONES -->
                                    <div class="accordion" id="accordion-<?= preg_replace('/[^a-zA-Z0-9]/', '-', $grado_key) ?>">
                                        <?php $seccion_index = 0; ?>
                                        <?php foreach ($grado_info['secciones'] as $seccion_key => $seccion_info): ?>
                                            <div class="card mb-3">
                                                <div class="card-header" id="heading-<?= preg_replace('/[^a-zA-Z0-9]/', '-', $grado_key) ?>-<?= $seccion_index ?>">
                                                    <button class="btn btn-link text-decoration-none w-100 text-left" 
                                                            type="button" 
                                                            data-bs-toggle="collapse" 
                                                            data-bs-target="#collapse-<?= preg_replace('/[^a-zA-Z0-9]/', '-', $grado_key) ?>-<?= $seccion_index ?>" 
                                                            aria-expanded="<?= $seccion_index === 0 ? 'true' : 'false' ?>">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <strong>Sección <?= htmlspecialchars($seccion_key) ?></strong>
                                                                <small class="text-muted ms-2">
                                                                    <?= count($seccion_info['materias']) ?> materia(s)
                                                                </small>
                                                            </div>
                                                            <div class="d-flex gap-3">
                                                                <small><span class="text-primary"><?= $seccion_info['total_estudiantes'] ?></span> est.</small>
                                                                <?php if ($seccion_info['aprobados'] > 0): ?>
                                                                    <small><span class="text-success"><?= $seccion_info['aprobados'] ?>✓</span></small>
                                                                <?php endif; ?>
                                                                <?php if ($seccion_info['en_proceso'] > 0): ?>
                                                                    <small><span class="text-warning"><?= $seccion_info['en_proceso'] ?>→</span></small>
                                                                <?php endif; ?>
                                                                <?php if ($seccion_info['aplazados'] > 0): ?>
                                                                    <small><span class="text-danger"><?= $seccion_info['aplazados'] ?>✗</span></small>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </button>
                                                </div>
                                                
                                                <div id="collapse-<?= preg_replace('/[^a-zA-Z0-9]/', '-', $grado_key) ?>-<?= $seccion_index ?>" 
                                                     class="collapse <?= $seccion_index === 0 ? 'show' : '' ?>" 
                                                     aria-labelledby="heading-<?= preg_replace('/[^a-zA-Z0-9]/', '-', $grado_key) ?>-<?= $seccion_index ?>">
                                                    <div class="card-body">
                                                        <!-- MATERIAS dentro de la sección -->
                                                        <div class="row">
                                                            <?php foreach ($seccion_info['materias'] as $materia_key => $materia): 
                                                                $tiene_aplazados = $materia['hay_aplazados'];
                                                                $tiene_aprobados = $materia['hay_aprobados'];
                                                            ?>
                                                                <div class="col-md-6 col-lg-4 mb-4">
                                                                    <div class="card-materia p-3">
                                                                        <!-- Encabezado de materia -->
                                                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                                                            <div>
                                                                                <h6 class="font-weight-bold mb-1"><?= htmlspecialchars($materia['nombre_materia']) ?></h6>
                                                                                <small class="text-muted">
                                                                                    <?php if ($materia['profesor_completo'] != 'No asignado'): ?>
                                                                                        <i class="fas fa-user-tie mr-1"></i>
                                                                                        <?= htmlspecialchars($materia['profesor_completo']) ?>
                                                                                    <?php endif; ?>
                                                                                </small>
                                                                            </div>
                                                                            <div>
                                                                                <?php if ($tiene_aplazados): ?>
                                                                                    <span class="badge badge-aplazado-materia">
                                                                                        <i class="fas fa-exclamation-triangle"></i> Aplazados
                                                                                    </span>
                                                                                <?php elseif ($tiene_aprobados): ?>
                                                                                    <span class="badge badge-aprobado-materia">
                                                                                        <i class="fas fa-check-circle"></i> Aprobados
                                                                                    </span>
                                                                                <?php else: ?>
                                                                                    <span class="badge <?= $materia['estado_asignacion'] == 1 ? 'bg-info' : 'bg-secondary' ?>">
                                                                                        <?= $materia['estado_asignacion'] == 1 ? 'En Proceso' : 'Sin asignar' ?>
                                                                                    </span>
                                                                                <?php endif; ?>
                                                                            </div>
                                                                        </div>
                                                                        
                                                                        <!-- Estadísticas -->
                                                                        <div class="d-flex justify-content-between mb-3">
                                                                            <div class="text-center">
                                                                                <div class="font-weight-bold"><?= $materia['total_estudiantes'] ?></div>
                                                                                <small class="text-muted">Total</small>
                                                                            </div>
                                                                            <div class="text-center">
                                                                                <div class="font-weight-bold text-success"><?= $materia['estudiantes_por_momento']['aprobados'] ?></div>
                                                                                <small class="text-muted">Aprobados</small>
                                                                            </div>
                                                                            <div class="text-center">
                                                                                <div class="font-weight-bold text-warning"><?= $materia['estudiantes_por_momento']['en_proceso'] ?></div>
                                                                                <small class="text-muted">En Proceso</small>
                                                                            </div>
                                                                            <div class="text-center">
                                                                                <div class="font-weight-bold text-danger"><?= $materia['estudiantes_por_momento']['aplazados'] ?></div>
                                                                                <small class="text-muted">Aplazados</small>
                                                                            </div>
                                                                        </div>
                                                                        
                                                                        <!-- Estudiantes -->
                                                                        <div>
                                                                            <small class="text-muted d-block mb-2">Estudiantes:</small>
                                                                            <div class="estudiante-scroll">
                                                                                <?php 
                                                                                $count = 0;
                                                                                foreach ($materia['estudiantes'] as $estudiante): 
                                                                                    if ($count >= 5) break;
                                                                                    
                                                                                    if ($estudiante['estado'] == 'aprobado') {
                                                                                        $icon = '✓';
                                                                                        $color_class = 'estado-aprobado';
                                                                                        $texto_estado = 'Aprobado';
                                                                                        $nota_extra = $estudiante['nota_aprobacion'] > 0 ? 
                                                                                            '<small class="nota-aprobacion ml-1">' . round($estudiante['nota_aprobacion']) . '/20</small>' : '';
                                                                                    } elseif ($estudiante['estado'] == 'aplazado') {
                                                                                        $icon = '✗';
                                                                                        $color_class = 'estado-aplazado';
                                                                                        $texto_estado = 'Aplazado';
                                                                                        $nota_extra = '';
                                                                                    } else {
                                                                                        $icon = '→';
                                                                                        $color_class = 'estado-proceso';
                                                                                        $texto_estado = 'M' . $estudiante['proximo_intento'];
                                                                                        $nota_extra = '';
                                                                                    }
                                                                                ?>
                                                                                    <div class="estudiante-item d-flex justify-content-between align-items-center">
                                                                                        <div class="text-truncate" style="max-width: 60%;">
                                                                                            <small><?= htmlspecialchars($estudiante['estudiante_completo']) ?></small>
                                                                                        </div>
                                                                                        <div class="d-flex align-items-center gap-2">
                                                                                            <?php if ($estudiante['ultima_nota'] > 0 && $estudiante['estado'] != 'aprobado'): ?>
                                                                                                <small class="<?= $estudiante['nota_redondeada'] >= 10 ? 'text-success' : 'text-danger' ?>">
                                                                                                    <?= $estudiante['nota_redondeada'] ?>/20
                                                                                                </small>
                                                                                            <?php endif; ?>
                                                                                            <span class="estado-badge <?= $color_class ?>" title="<?= $texto_estado ?>">
                                                                                                <?= $icon ?>
                                                                                                <?= $nota_extra ?>
                                                                                            </span>
                                                                                        </div>
                                                                                    </div>
                                                                                <?php 
                                                                                    $count++;
                                                                                endforeach; 
                                                                                
                                                                                if ($materia['total_estudiantes'] > 5): ?>
                                                                                    <div class="text-center mt-2">
                                                                                        <small class="text-muted">
                                                                                            ... y <?= $materia['total_estudiantes'] - 5 ?> más
                                                                                        </small>
                                                                                    </div>
                                                                                <?php endif; ?>
                                                                            </div>
                                                                        </div>
                                                                        
                                                                        <!-- Botones -->
                                                                        <div class="d-flex justify-content-between mt-3 pt-3 border-top">
                                                                            <button onclick="verDetalleMateriaCompleto(<?= $materia['id_materia'] ?>, <?= $materia['id_seccion'] ?>, '<?= addslashes($materia['nombre_materia']) ?>', '<?= addslashes($materia['nombre_seccion']) ?>')" 
                                                                                    class="btn btn-outline-primary btn-sm">
                                                                                <i class="fas fa-eye mr-1"></i> Ver Historial
                                                                            </button>
                                                                            
                                                                            <?php if ($tiene_aplazados): ?>
                                                                                <button onclick="mostrarAlertaAplazados()" 
                                                                                        class="btn btn-secondary btn-sm btn-gestionar-disabled">
                                                                                    <i class="fas fa-ban mr-1"></i> No Gestionable
                                                                                </button>
                                                                            <?php elseif ($tiene_aprobados && $materia['estudiantes_por_momento']['en_proceso'] == 0): ?>
                                                                                <button class="btn btn-success btn-sm" disabled>
                                                                                    <i class="fas fa-check mr-1"></i> Todos Aprobados
                                                                                </button>
                                                                            <?php else: ?>
                                                                                <a href="recuperaciones_pendientes.php?seccion=<?= $materia['id_seccion'] ?>&materia=<?= $materia['id_materia'] ?>" 
                                                                                   class="btn btn-primary btn-sm">
                                                                                   <i class="fas fa-edit mr-1"></i> Gestionar
                                                                                </a>
                                                                            <?php endif; ?>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php $seccion_index++; ?>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <?php $first_grado = false; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Información importante -->
                <div class="alert alert-info mt-4">
                    <div class="d-flex">
                        <div class="mr-3">
                            <i class="fas fa-info-circle fa-2x"></i>
                        </div>
                        <div>
                            <h6 class="font-weight-bold mb-2">Sistema de Materias Pendientes - Historial Completo</h6>
                            <p class="mb-2">
                                • <strong>✓ Aprobados:</strong> Se muestra la nota con la que aprobaron (ej: 14/20)<br>
                                • <strong>→ En Proceso:</strong> Aún tienen momentos disponibles (M1-M4)<br>
                                • <strong>✗ Aplazados:</strong> Ya utilizaron sus 4 momentos<br>
                                • <strong>Ver Historial:</strong> Muestra todos los 4 momentos de cada estudiante<br>
                                • <strong>Gestionar:</strong> Solo disponible para materias con estudiantes en proceso
                            </p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Scripts -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<script src="../../assets/js/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Configuración de DataTables en español
const dataTablesSpanish = {
    "decimal": ",",
    "emptyTable": "No hay datos disponibles en la tabla",
    "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
    "infoEmpty": "Mostrando 0 a 0 de 0 registros",
    "infoFiltered": "(filtrado de _MAX_ registros totales)",
    "infoPostFix": "",
    "thousands": ".",
    "lengthMenu": "Mostrar _MENU_ registros",
    "loadingRecords": "Cargando...",
    "processing": "Procesando...",
    "search": "Buscar:",
    "zeroRecords": "No se encontraron registros coincidentes",
    "paginate": {
        "first": "Primero",
        "last": "Último",
        "next": "Siguiente",
        "previous": "Anterior"
    },
    "aria": {
        "sortAscending": ": activar para ordenar ascendente",
        "sortDescending": ": activar para ordenar descendente"
    }
};

// Datos desde PHP
const materiasData = <?= json_encode($grados_agrupados, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
const historialPorEstudiante = <?= json_encode($historial_por_estudiante, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
const historialPorMateria = <?= json_encode($historial_por_materia, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;

// Función para mostrar alerta de estudiantes aplazados
function mostrarAlertaAplazados() {
    Swal.fire({
        icon: 'warning',
        title: 'Materia No Gestionable',
        html: `
            <div class="text-center">
                <i class="fas fa-ban fa-3x text-danger mb-3"></i>
                <p class="mb-3">Esta materia contiene estudiantes <strong class="text-danger">APLAZADOS</strong>.</p>
                <div class="alert alert-warning text-left">
                    <h6><i class="fas fa-exclamation-triangle mr-2"></i> Información:</h6>
                    <p class="mb-0">Los estudiantes aplazados ya utilizaron sus 4 momentos de recuperación. No pueden recibir más calificaciones.</p>
                </div>
            </div>
        `,
        confirmButtonText: 'Entendido',
        confirmButtonColor: '#dc3545',
        width: '500px'
    });
}

// Función para ver detalle COMPLETO con historial
function verDetalleMateriaCompleto(id_materia, id_seccion, nombre_materia, nombre_seccion) {
    // Buscar la materia en los datos
    let materiaEncontrada = null;
    let gradoInfo = null;
    let seccionInfo = null;
    
    for (const [gradoKey, gradoData] of Object.entries(materiasData)) {
        for (const [seccionKey, seccionData] of Object.entries(gradoData.secciones)) {
            for (const [materiaKey, materiaData] of Object.entries(seccionData.materias)) {
                if (materiaData.id_materia == id_materia && materiaData.id_seccion == id_seccion) {
                    materiaEncontrada = materiaData;
                    gradoInfo = gradoData;
                    seccionInfo = seccionData;
                    break;
                }
            }
            if (materiaEncontrada) break;
        }
        if (materiaEncontrada) break;
    }
    
    if (!materiaEncontrada) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se encontró la materia seleccionada',
            confirmButtonText: 'Aceptar'
        });
        return;
    }
    
    const materia = materiaEncontrada;
    
    // Contar estados
    let aprobados = materia.estudiantes.filter(e => e.estado === 'aprobado');
    let enProceso = materia.estudiantes.filter(e => e.estado === 'en_proceso');
    let aplazados = materia.estudiantes.filter(e => e.estado === 'aplazado');
    
    // Obtener historial de esta materia
    const keyMateria = materia.id_materia + '_' + materia.id_seccion;
    const historialMateria = historialPorMateria[keyMateria] || [];
    
    // Construir HTML
    let html = `
        <div class="container-fluid">
            <!-- Encabezado -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="alert alert-primary">
                        <div class="d-flex align-items-center">
                            <div class="mr-3">
                                <i class="fas fa-book fa-2x"></i>
                            </div>
                            <div>
                                <h5 class="mb-1">${nombre_materia}</h5>
                                <p class="mb-0">
                                    <i class="fas fa-chalkboard mr-1"></i>
                                    Sección ${nombre_seccion} | 
                                    <i class="fas fa-user-tie mr-1 ml-2"></i>
                                    ${materia.profesor_completo}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Estadísticas -->
            <div class="row mb-4">
                <div class="col-md-3 text-center">
                    <div class="card border-primary">
                        <div class="card-body py-3">
                            <div class="h3 text-primary">${materia.total_estudiantes}</div>
                            <small class="text-muted">Total Estudiantes</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 text-center">
                    <div class="card border-success">
                        <div class="card-body py-3">
                            <div class="h3 text-success">${aprobados.length}</div>
                            <small class="text-muted">Aprobados</small>
                            ${aprobados.length > 0 ? `<div class="text-xs text-success">Con nota registrada</div>` : ''}
                        </div>
                    </div>
                </div>
                <div class="col-md-3 text-center">
                    <div class="card border-warning">
                        <div class="card-body py-3">
                            <div class="h3 text-warning">${enProceso.length}</div>
                            <small class="text-muted">En Proceso</small>
                            ${enProceso.length > 0 ? `<div class="text-xs text-warning">Momentos restantes</div>` : ''}
                        </div>
                    </div>
                </div>
                <div class="col-md-3 text-center">
                    <div class="card border-danger">
                        <div class="card-body py-3">
                            <div class="h3 text-danger">${aplazados.length}</div>
                            <small class="text-muted">Aplazados</small>
                            ${aplazados.length > 0 ? `<div class="text-xs text-danger">4 momentos usados</div>` : ''}
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Pestañas -->
            <ul class="nav nav-tabs" id="detalleTabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" id="estudiantes-tab" data-bs-toggle="tab" data-bs-target="#estudiantes">
                        <i class="fas fa-users mr-1"></i> Estudiantes (${materia.total_estudiantes})
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="historial-tab" data-bs-toggle="tab" data-bs-target="#historial">
                        <i class="fas fa-history mr-1"></i> Historial General
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="aprobados-tab" data-bs-toggle="tab" data-bs-target="#aprobados">
                        <i class="fas fa-check-circle mr-1"></i> Aprobados (${aprobados.length})
                    </button>
                </li>
            </ul>
            
            <div class="tab-content mt-3">
                <!-- Tab 1: Estudiantes -->
                <div class="tab-pane fade show active" id="estudiantes">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover w-100">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Estudiante</th>
                                    <th>Cédula</th>
                                    <th>Estado</th>
                                    <th>Último Momento</th>
                                    <th>Nota</th>
                                    <th>Nota Aprobación</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
    `;
    
    materia.estudiantes.forEach((est, index) => {
        let estadoBadge = '';
        let notaAprobacion = '';
        
        if (est.estado === 'aprobado') {
            estadoBadge = `<span class="badge bg-success">✓ Aprobado</span>`;
            notaAprobacion = est.nota_aprobacion > 0 ? 
                `<span class="nota-aprobacion">${Math.round(est.nota_aprobacion)}/20</span>` : 
                '<span class="text-muted">N/A</span>';
        } else if (est.estado === 'aplazado') {
            estadoBadge = `<span class="badge bg-danger">✗ Aplazado</span>`;
            notaAprobacion = '<span class="text-muted">-</span>';
        } else {
            estadoBadge = `<span class="badge bg-warning">→ M${est.proximo_intento}</span>`;
            notaAprobacion = '<span class="text-muted">-</span>';
        }
        
        html += `
            <tr>
                <td>${index + 1}</td>
                <td>
                    <i class="fas fa-user-circle mr-2 text-primary"></i>
                    ${est.estudiante_completo}
                </td>
                <td>${est.cedula || 'N/A'}</td>
                <td>${estadoBadge}</td>
                <td>
                    ${est.ultimo_intento > 0 ? 
                        `<span class="badge momento-${est.ultimo_intento}">${est.ultimo_intento}°</span>` : 
                        '<span class="text-muted">-</span>'}
                </td>
                <td class="font-weight-bold ${est.nota_redondeada >= 10 ? 'text-success' : 'text-danger'}">
                    ${est.ultima_nota > 0 ? est.nota_redondeada + '/20' : '-'}
                </td>
                <td>${notaAprobacion}</td>
                <td>
                    <button onclick="verHistorial4Momentos(${est.id_estudiante}, ${materia.id_materia}, ${materia.id_seccion}, '${est.estudiante_completo.replace(/'/g, "\\'")}')" 
                            class="btn btn-sm btn-outline-info">
                        <i class="fas fa-history mr-1"></i> 4 Momentos
                    </button>
                </td>
            </tr>
        `;
    });
    
    html += `
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Tab 2: Historial General -->
                <div class="tab-pane fade" id="historial">
                    ${historialMateria.length > 0 ? `
                        <div class="table-responsive">
                            <table class="table table-sm table-hover w-100">
                                <thead class="table-primary">
                                    <tr>
                                        <th>#</th>
                                        <th>Estudiante</th>
                                        <th>Momento</th>
                                        <th>Nota</th>
                                        <th>Resultado</th>
                                        <th>Fecha</th>
                                    </tr>
                                </thead>
                                <tbody>
                        ` + historialMateria.map((hist, idx) => `
                                    <tr class="${hist.resultado === 'aprobado' ? 'table-success' : 'table-danger'}">
                                        <td>${idx + 1}</td>
                                        <td>${hist.nombre_completo}</td>
                                        <td><span class="badge momento-${hist.intento}">${hist.intento}°</span></td>
                                        <td class="font-weight-bold ${hist.resultado === 'aprobado' ? 'text-success' : 'text-danger'}">
                                            ${Math.round(hist.calificacion)}/20
                                        </td>
                                        <td>
                                            <span class="badge ${hist.resultado === 'aprobado' ? 'bg-success' : 'bg-danger'}">
                                                ${hist.resultado === 'aprobado' ? '✓ Aprobado' : '✗ Reprobado'}
                                            </span>
                                        </td>
                                        <td><small>${new Date(hist.fecha_registro).toLocaleDateString('es-ES')}</small></td>
                                    </tr>
                        `).join('') + `
                                </tbody>
                            </table>
                        </div>
                    ` : `
                        <div class="text-center py-4">
                            <i class="fas fa-history fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Sin historial registrado</h5>
                            <p>No hay recuperaciones registradas para esta materia.</p>
                        </div>
                    `}
                </div>
                
                <!-- Tab 3: Aprobados -->
                <div class="tab-pane fade" id="aprobados">
                    ${aprobados.length > 0 ? `
                        <div class="table-responsive">
                            <table class="table table-sm table-hover w-100">
                                <thead class="table-success">
                                    <tr>
                                        <th>#</th>
                                        <th>Estudiante</th>
                                        <th>Cédula</th>
                                        <th>Nota de Aprobación</th>
                                        <th>Momento Aprobado</th>
                                        <th>Fecha</th>
                                    </tr>
                                </thead>
                                <tbody>
                        ` + aprobados.map((est, idx) => {
                            // Buscar en qué momento aprobó
                            const key = est.id_estudiante + '_' + materia.id_materia + '_' + materia.id_seccion;
                            const historialEst = historialPorEstudiante[key] || [];
                            const momentoAprobado = historialEst.find(h => Math.round(h.calificacion) >= 10);
                            
                            return `
                                    <tr>
                                        <td>${idx + 1}</td>
                                        <td>
                                            <i class="fas fa-user-circle mr-2 text-success"></i>
                                            ${est.estudiante_completo}
                                        </td>
                                        <td>${est.cedula || 'N/A'}</td>
                                        <td class="font-weight-bold text-success">
                                            <span class="nota-aprobacion">${est.nota_redondeada}/20</span>
                                        </td>
                                        <td>
                                            ${momentoAprobado ? 
                                                `<span class="badge momento-${momentoAprobado.intento}">${momentoAprobado.intento}° Momento</span>` : 
                                                '<span class="text-muted">No registrado</span>'}
                                        </td>
                                        <td>
                                            ${momentoAprobado && momentoAprobado.fecha_registro ? 
                                                `<small>${new Date(momentoAprobado.fecha_registro).toLocaleDateString('es-ES')}</small>` : 
                                                '<span class="text-muted">-</span>'}
                                        </td>
                                    </tr>
                            `;
                        }).join('') + `
                                </tbody>
                            </table>
                        </div>
                    ` : `
                        <div class="text-center py-4">
                            <i class="fas fa-check-circle fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Sin estudiantes aprobados</h5>
                            <p>No hay estudiantes aprobados registrados para esta materia.</p>
                        </div>
                    `}
                </div>
            </div>
        </div>
    `;
    
    Swal.fire({
        title: 'Historial Completo',
        html: html,
        width: '1300px',
        confirmButtonText: 'Cerrar',
        confirmButtonColor: '#2c80ff',
        showCancelButton: materia.estudiantes_por_momento.en_proceso > 0,
        cancelButtonText: '<i class="fas fa-edit mr-1"></i> Gestionar Materia',
        cancelButtonColor: '#28a745',
        customClass: { popup: 'animated fadeIn' },
        didOpen: () => {
            // Inicializar tabs
            const triggerTabList = [].slice.call(document.querySelectorAll('#detalleTabs button'));
            triggerTabList.forEach(triggerEl => {
                const tabTrigger = new bootstrap.Tab(triggerEl);
                triggerEl.addEventListener('click', event => {
                    event.preventDefault();
                    tabTrigger.show();
                });
            });
        }
    }).then((result) => {
        if (result.dismiss === Swal.DismissReason.cancel && materia.estudiantes_por_momento.en_proceso > 0) {
            window.location.href = `recuperaciones_pendientes.php?seccion=${materia.id_seccion}&materia=${materia.id_materia}`;
        }
    });
}

// Función para ver historial de 4 momentos
function verHistorial4Momentos(id_estudiante, id_materia, id_seccion, nombre_estudiante) {
    const key = id_estudiante + '_' + id_materia + '_' + id_seccion;
    const historial = historialPorEstudiante[key] || [];
    
    // Ordenar por intento
    historial.sort((a, b) => a.intento - b.intento);
    
    let html = `
        <div class="container-fluid">
            <div class="row mb-4">
                <div class="col-12">
                    <div class="alert alert-info">
                        <h5 class="mb-2"><i class="fas fa-user-graduate mr-2"></i> ${nombre_estudiante}</h5>
                        <p class="mb-0">Historial detallado de los 4 momentos de recuperación</p>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-12">
    `;
    
    if (historial.length === 0) {
        html += `
            <div class="text-center py-4">
                <i class="fas fa-history fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Sin historial registrado</h5>
                <p>Este estudiante no tiene recuperaciones registradas.</p>
            </div>
        `;
    } else {
        // Mostrar los 4 momentos
        for (let i = 1; i <= 4; i++) {
            const momento = historial.find(h => h.intento === i);
            const tieneMomento = !!momento;
            const esAprobado = tieneMomento && Math.round(momento.calificacion) >= 10;
            const nota = tieneMomento ? Math.round(momento.calificacion) : 0;
            
            html += `
                <div class="card mb-3 ${esAprobado ? 'border-success' : tieneMomento ? 'border-danger' : 'border-secondary'}">
                    <div class="card-header ${esAprobado ? 'bg-success text-white' : tieneMomento ? 'bg-danger text-white' : 'bg-secondary text-white'}">
                        <h6 class="mb-0">
                            <i class="fas fa-${esAprobado ? 'check-circle' : tieneMomento ? 'times-circle' : 'question-circle'} mr-2"></i>
                            ${i}° Momento de Recuperación
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <strong>Estado:</strong><br>
                                ${tieneMomento ? 
                                    `<span class="badge ${esAprobado ? 'bg-success' : 'bg-danger'}">
                                        ${esAprobado ? '✓ APROBADO' : '✗ REPROBADO'}
                                    </span>` : 
                                    '<span class="badge bg-secondary">NO PRESENTADO</span>'}
                            </div>
                            <div class="col-md-4">
                                <strong>Nota Obtenida:</strong><br>
                                ${tieneMomento ? 
                                    `<span class="h4 ${esAprobado ? 'text-success' : 'text-danger'}">
                                        ${nota}/20
                                    </span>` : 
                                    '<span class="text-muted">- / 20</span>'}
                            </div>
                            <div class="col-md-4">
                                <strong>Fecha:</strong><br>
                                ${tieneMomento && momento.fecha_registro ? 
                                    new Date(momento.fecha_registro).toLocaleDateString('es-ES') : 
                                    '<span class="text-muted">No aplica</span>'}
                            </div>
                        </div>
                        ${tieneMomento && momento.observaciones ? `
                            <div class="mt-3">
                                <strong>Observaciones:</strong><br>
                                <small class="text-muted">${momento.observaciones}</small>
                            </div>
                        ` : ''}
                    </div>
                </div>
            `;
        }
        
        // Resumen
        const aprobadoEnAlgunMomento = historial.some(h => Math.round(h.calificacion) >= 10);
        const ultimoMomento = historial.length > 0 ? Math.max(...historial.map(h => h.intento)) : 0;
        const totalAprobados = historial.filter(h => Math.round(h.calificacion) >= 10).length;
        
        html += `
            <div class="alert ${aprobadoEnAlgunMomento ? 'alert-success' : ultimoMomento >= 4 ? 'alert-danger' : 'alert-warning'}">
                <h6><i class="fas fa-chart-line mr-2"></i> Resumen Final:</h6>
                <p class="mb-1"><strong>Momentos presentados:</strong> ${historial.length} de 4</p>
                <p class="mb-1"><strong>Momentos aprobados:</strong> ${totalAprobados}</p>
                <p class="mb-1"><strong>Último momento:</strong> ${ultimoMomento > 0 ? ultimoMomento + '°' : 'Ninguno'}</p>
                <p class="mb-0"><strong>Estado final:</strong> 
                    ${aprobadoEnAlgunMomento ? 
                        '<span class="badge bg-success">✓ MATERIA APROBADA</span>' : 
                        ultimoMomento >= 4 ? 
                        '<span class="badge bg-danger">✗ MATERIA APLAZADA</span>' : 
                        '<span class="badge bg-warning">EN PROCESO</span>'}
                </p>
            </div>
        `;
    }
    
    html += `
                </div>
            </div>
        </div>
    `;
    
    Swal.fire({
        title: 'Historial de 4 Momentos',
        html: html,
        width: '700px',
        confirmButtonText: 'Cerrar',
        confirmButtonColor: '#2c80ff'
    });
}

// Inicializar
$(document).ready(function() {
    // Inicializar acordeones
    $('.collapse').on('shown.bs.collapse', function() {
        $(this).prev('.card-header').find('.btn').addClass('active');
    });
    
    $('.collapse').on('hidden.bs.collapse', function() {
        $(this).prev('.card-header').find('.btn').removeClass('active');
    });
});
</script>

<?php include('../../admin/layout/parte2.php'); ?>