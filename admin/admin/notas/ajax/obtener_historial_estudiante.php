<?php
session_start();
require_once('../../app/config.php');

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
    exit;
}

$id_estudiante = $_POST['id_estudiante'] ?? null;
$id_materia = $_POST['id_materia'] ?? null;
$id_seccion = $_POST['id_seccion'] ?? null;

if (!$id_estudiante || !$id_materia || !$id_seccion) {
    echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
    exit;
}

try {
    $sql = "
        SELECT r.*, DATE_FORMAT(r.fecha_registro, '%d/%m/%Y') as fecha_registro
        FROM recuperaciones r
        WHERE r.id_estudiante = :id_estudiante 
          AND r.id_materia = :id_materia 
          AND r.id_seccion = :id_seccion
          AND r.tipo = 'pendiente'
        ORDER BY r.intento ASC
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':id_estudiante' => $id_estudiante,
        ':id_materia' => $id_materia,
        ':id_seccion' => $id_seccion
    ]);
    
    $historial = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'status' => 'success',
        'historial' => $historial
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error al obtener historial: ' . $e->getMessage()
    ]);
}