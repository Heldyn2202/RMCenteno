<?php
session_start();
require_once('../../app/config.php');

$grado = $_GET['grado'] ?? '';
$nivel = $_GET['nivel'] ?? '';

if (empty($grado) || empty($nivel)) {
    echo json_encode(['status' => 'error', 'message' => 'Parámetros incompletos']);
    exit;
}

// Obtener secciones con materias pendientes para el grado específico
$sql = "
    SELECT DISTINCT 
        s.id_seccion,
        s.nombre_seccion,
        g.grado,
        g.nivel,
        COUNT(DISTINCT mp.id_materia) as total_materias,
        COUNT(DISTINCT mp.id_estudiante) as total_estudiantes,
        GROUP_CONCAT(DISTINCT CONCAT(p.nombres, ' ', p.apellidos) SEPARATOR ', ') as profesores
    FROM materias_pendientes mp
    INNER JOIN secciones s ON mp.id_seccion = s.id_seccion
    INNER JOIN grados g ON s.id_grado = g.id_grado
    LEFT JOIN asignaciones_profesor ap ON mp.id_materia = ap.id_materia AND mp.id_seccion = ap.id_seccion
    LEFT JOIN profesores p ON ap.id_profesor = p.id_profesor
    WHERE s.estado = 'activo'
      AND (mp.estado = 'pendiente' OR mp.estado IS NULL OR mp.estado = '')
      AND g.grado = :grado
      AND g.nivel = :nivel
      AND ap.estado = 1
    GROUP BY s.id_seccion, s.nombre_seccion, g.grado, g.nivel
    ORDER BY s.nombre_seccion
";

$stmt = $pdo->prepare($sql);
$stmt->execute([':grado' => $grado, ':nivel' => $nivel]);
$secciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($secciones)) {
    echo json_encode(['status' => 'warning', 'message' => 'No se encontraron secciones']);
    exit;
}

echo json_encode(['status' => 'success', 'data' => $secciones]);