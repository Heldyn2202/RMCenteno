<?php
session_start();
require_once('../../app/config.php');

// Obtener diagnóstico detallado de todas las materias pendientes
$sql = "
    SELECT 
        mp.id_pendiente,
        CONCAT(e.nombres, ' ', e.apellidos) as estudiante,
        m.nombre_materia,
        s.nombre_seccion,
        g.grado as anio,
        CONCAT(p.nombres, ' ', p.apellidos) as profesor,
        mp.estado,
        DATE_FORMAT(mp.fecha_registro, '%d/%m/%Y %H:%i') as fecha
    FROM materias_pendientes mp
    INNER JOIN estudiantes e ON mp.id_estudiante = e.id_estudiante
    INNER JOIN materias m ON mp.id_materia = m.id_materia
    INNER JOIN secciones s ON mp.id_seccion = s.id_seccion
    INNER JOIN grados g ON s.id_grado = g.id_grado
    LEFT JOIN asignaciones_profesor ap ON mp.id_materia = ap.id_materia AND mp.id_seccion = ap.id_seccion
    LEFT JOIN profesores p ON ap.id_profesor = p.id_profesor
    WHERE (mp.estado = 'pendiente' OR mp.estado IS NULL OR mp.estado = '')
    ORDER BY g.nivel, g.grado, s.nombre_seccion, e.apellidos
    LIMIT 50
";

$stmt = $pdo->query($sql);
$diagnostico = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'status' => 'success',
    'data' => $diagnostico,
    'total' => count($diagnostico)
]);