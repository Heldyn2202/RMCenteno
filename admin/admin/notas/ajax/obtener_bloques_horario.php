<?php
/**
 * Endpoint AJAX para obtener bloques de un horario
 * GET: id_horario
 */

// Incluir configuración
$configPath = dirname(__DIR__) . '/../../app/config.php';
if (file_exists($configPath)) {
    include($configPath);
} else {
    include(__DIR__ . '/../../../app/config.php');
}

// Verificar que $pdo existe
if (!isset($pdo)) {
    echo json_encode([
        'success' => false,
        'message' => 'Error de conexión a la base de datos',
        'data' => []
    ]);
    exit;
}

header('Content-Type: application/json');

try {
    $id_horario = isset($_GET['id_horario']) ? (int)$_GET['id_horario'] : 0;
    
    if ($id_horario <= 0) {
        echo json_encode(['success' => false, 'message' => 'Horario no válido', 'data' => []]);
        exit;
    }
    
    // Detectar PK
    $cols = $pdo->query("SHOW COLUMNS FROM horario_detalle")->fetchAll(PDO::FETCH_COLUMN);
    $pk = null;
    foreach (['id_detalle','id_horario_detalle','id'] as $c) { 
        if (in_array($c, $cols, true)) { 
            $pk = $c; 
            break; 
        } 
    }
    
    if (!$pk) {
        echo json_encode(['success' => false, 'message' => 'No se pudo detectar la clave primaria', 'data' => []]);
        exit;
    }
    
    // Obtener bloques del horario
    $sql = "SELECT hd.$pk as id_bloque,
                   hd.dia_semana,
                   TIME_FORMAT(hd.hora_inicio, '%H:%i') as hora_inicio,
                   TIME_FORMAT(hd.hora_fin, '%H:%i') as hora_fin,
                   hd.id_materia,
                   hd.id_profesor,
                   m.nombre_materia,
                   CONCAT(p.nombres,' ',p.apellidos) AS profesor_nombre
            FROM horario_detalle hd
            JOIN materias m ON m.id_materia = hd.id_materia
            LEFT JOIN profesores p ON p.id_profesor = hd.id_profesor
            WHERE hd.id_horario = ?
            ORDER BY FIELD(hd.dia_semana, 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'), hd.hora_inicio";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_horario]);
    
    $bloques = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $bloques,
        'count' => count($bloques)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage(),
        'data' => []
    ]);
}
?>



