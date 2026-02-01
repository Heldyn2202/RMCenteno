<?php
session_start();
require_once('../../app/config.php');

$id_materia = $_POST['id_materia'] ?? '';
$id_seccion = $_POST['id_seccion'] ?? '';

if (empty($id_materia) || empty($id_seccion)) {
    echo json_encode(['status' => 'error', 'message' => 'Parámetros incompletos']);
    exit;
}

// Obtener información de la materia
$sql_materia = "
    SELECT 
        m.nombre_materia,
        s.nombre_seccion,
        g.grado,
        g.nivel,
        CONCAT(p.nombres, ' ', p.apellidos) as profesor_completo,
        ap.estado as estado_asignacion,
        COUNT(DISTINCT mp.id_estudiante) as total_estudiantes
    FROM materias_pendientes mp
    INNER JOIN materias m ON mp.id_materia = m.id_materia
    INNER JOIN secciones s ON mp.id_seccion = s.id_seccion
    INNER JOIN grados g ON s.id_grado = g.id_grado
    LEFT JOIN asignaciones_profesor ap ON mp.id_materia = ap.id_materia AND mp.id_seccion = ap.id_seccion
    LEFT JOIN profesores p ON ap.id_profesor = p.id_profesor
    WHERE mp.id_materia = :id_materia 
      AND mp.id_seccion = :id_seccion
      AND (mp.estado = 'pendiente' OR mp.estado IS NULL OR mp.estado = '')
    GROUP BY m.nombre_materia, s.nombre_seccion, g.grado, g.nivel, p.nombres, p.apellidos, ap.estado
";

$stmt_m = $pdo->prepare($sql_materia);
$stmt_m->execute([':id_materia' => $id_materia, ':id_seccion' => $id_seccion]);
$materia_info = $stmt_m->fetch(PDO::FETCH_ASSOC);

if (!$materia_info) {
    echo json_encode(['status' => 'error', 'message' => 'Materia no encontrada']);
    exit;
}

// Obtener estudiantes con sus intentos
$sql_estudiantes = "
    SELECT 
        mp.id_estudiante,
        CONCAT(e.nombres, ' ', e.apellidos) as estudiante_completo,
        e.cedula,
        COALESCE(MAX(CASE WHEN r.tipo = 'pendiente' THEN r.intento END), 0) as ultimo_intento,
        COALESCE(MAX(CASE WHEN r.tipo = 'pendiente' THEN r.calificacion END), 0) as ultima_nota,
        mp.estado as estado_pendiente,
        mp.fecha_registro
    FROM materias_pendientes mp
    INNER JOIN estudiantes e ON mp.id_estudiante = e.id_estudiante
    LEFT JOIN recuperaciones r ON mp.id_estudiante = r.id_estudiante 
        AND mp.id_materia = r.id_materia 
        AND mp.id_seccion = r.id_seccion 
        AND r.tipo = 'pendiente'
    WHERE mp.id_materia = :id_materia 
      AND mp.id_seccion = :id_seccion
      AND (mp.estado = 'pendiente' OR mp.estado IS NULL OR mp.estado = '')
    GROUP BY mp.id_estudiante, e.nombres, e.apellidos, e.cedula, mp.estado, mp.fecha_registro
    ORDER BY e.apellidos, e.nombres
";

$stmt_e = $pdo->prepare($sql_estudiantes);
$stmt_e->execute([':id_materia' => $id_materia, ':id_seccion' => $id_seccion]);
$estudiantes_raw = $stmt_e->fetchAll(PDO::FETCH_ASSOC);

// Procesar estudiantes
$estudiantes = [];
$estudiantes_por_momento = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 'aprobados' => 0, 'en_proceso' => 0, 'aplazados' => 0];

foreach ($estudiantes_raw as $est) {
    $ultimo_intento = $est['ultimo_intento'] ?? 0;
    $ultima_nota = $est['ultima_nota'] ?? 0;
    $nota_redondeada = round($ultima_nota, 0, PHP_ROUND_HALF_UP);
    
    // Determinar estado del estudiante
    $estado = 'en_proceso';
    $proximo_intento = $ultimo_intento + 1;
    
    if ($nota_redondeada >= 10) {
        $estado = 'aprobado';
        $proximo_intento = 0;
    } elseif ($ultimo_intento == 4 && $nota_redondeada < 10) {
        $estado = 'aplazado';
        $proximo_intento = 0;
    }
    
    $estudiantes[] = [
        'id_estudiante' => $est['id_estudiante'],
        'estudiante_completo' => $est['estudiante_completo'],
        'cedula' => $est['cedula'],
        'ultimo_intento' => $ultimo_intento,
        'ultima_nota' => $ultima_nota,
        'nota_redondeada' => $nota_redondeada,
        'proximo_intento' => $proximo_intento,
        'estado' => $estado,
        'fecha_registro' => $est['fecha_registro']
    ];
    
    // Contar por momento
    if ($estado == 'aprobado') {
        $estudiantes_por_momento['aprobados']++;
    } elseif ($estado == 'aplazado') {
        $estudiantes_por_momento['aplazados']++;
    } else {
        $estudiantes_por_momento['en_proceso']++;
        if ($proximo_intento >= 1 && $proximo_intento <= 4) {
            $estudiantes_por_momento[$proximo_intento]++;
        }
    }
}

// Preparar respuesta
$response = [
    'status' => 'success',
    'data' => [
        'materia' => [
            'id_materia' => $id_materia,
            'id_seccion' => $id_seccion,
            'nombre_materia' => $materia_info['nombre_materia'],
            'nombre_seccion' => $materia_info['nombre_seccion'],
            'grado' => $materia_info['grado'],
            'nivel' => $materia_info['nivel'],
            'profesor_completo' => $materia_info['profesor_completo'] ?? 'No asignado',
            'estado_asignacion' => $materia_info['estado_asignacion'] ?? 0,
            'total_estudiantes' => $materia_info['total_estudiantes'],
            'estudiantes_por_momento' => $estudiantes_por_momento
        ],
        'estudiantes' => $estudiantes
    ]
];

echo json_encode($response);