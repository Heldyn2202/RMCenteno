<?php
require_once('../../app/config.php');
header('Content-Type: application/json');

try {
    $id_estudiante = $_GET['id_estudiante'] ?? null;
    $id_materia = $_GET['id_materia'] ?? null;
    
    if (!$id_estudiante || !$id_materia) {
        throw new Exception('Datos incompletos.');
    }
    
    // Consultar historial de recuperaciones del estudiante para esta materia
    $sql = "
        SELECT 
            tipo,
            intento,
            calificacion,
            fecha_registro,
            observaciones
        FROM recuperaciones 
        WHERE id_estudiante = :id_estudiante 
          AND id_materia = :id_materia
        ORDER BY fecha_registro DESC, intento DESC
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':id_estudiante' => $id_estudiante,
        ':id_materia' => $id_materia
    ]);
    
    $historial = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // También obtener información de materias pendientes si aplica
    $sql_pendiente = "
        SELECT estado 
        FROM materias_pendientes 
        WHERE id_estudiante = :id_estudiante 
          AND id_materia = :id_materia
        LIMIT 1
    ";
    
    $stmt_p = $pdo->prepare($sql_pendiente);
    $stmt_p->execute([
        ':id_estudiante' => $id_estudiante,
        ':id_materia' => $id_materia
    ]);
    
    $pendiente_info = $stmt_p->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'status' => 'success',
        'historial' => $historial,
        'materia_pendiente' => $pendiente_info ? $pendiente_info['estado'] : null,
        'total_intentos' => count($historial)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>