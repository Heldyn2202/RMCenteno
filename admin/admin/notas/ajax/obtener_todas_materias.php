<?php
session_start();
require_once('../../app/config.php');

// Obtener TODAS las materias pendientes sin filtros
$sql = "
    SELECT 
        mp.id_pendiente,
        CONCAT(e.nombres, ' ', e.apellidos) as estudiante,
        m.nombre_materia as materia,
        s.nombre_seccion as seccion,
        mp.id_seccion,
        g.grado as anio,
        CONCAT(p.nombres, ' ', p.apellidos) as profesor,
        mp.estado,
        DATE_FORMAT(mp.fecha_registro, '%d/%m/%Y') as fecha
    FROM materias_pendientes mp
    INNER JOIN estudiantes e ON mp.id_estudiante = e.id_estudiante
    INNER JOIN materias m ON mp.id_materia = m.id_materia
    LEFT JOIN secciones s ON mp.id_seccion = s.id_seccion
    LEFT JOIN grados g ON s.id_grado = g.id_grado
    LEFT JOIN asignaciones_profesor ap ON mp.id_materia = ap.id_materia AND mp.id_seccion = ap.id_seccion
    LEFT JOIN profesores p ON ap.id_profesor = p.id_profesor
    WHERE (mp.estado = 'pendiente' OR mp.estado IS NULL OR mp.estado = '')
    ORDER BY mp.fecha_registro DESC
    LIMIT 50
";

$stmt = $pdo->query($sql);
$materias = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener total
$sql_total = "SELECT COUNT(*) as total FROM materias_pendientes WHERE (estado = 'pendiente' OR estado IS NULL OR estado = '')";
$stmt_total = $pdo->query($sql_total);
$total = $stmt_total->fetch(PDO::FETCH_ASSOC);

echo json_encode([
    'status' => 'success',
    'data' => $materias,
    'total' => $total['total'] ?? 0
]);