<?php
/**
 * Endpoint para eliminar múltiples bloques de horario a la vez
 * POST: ids (array de id_detalle), grado, seccion
 */
include('../../app/config.php');

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

try {
    $ids = isset($_POST['ids']) ? $_POST['ids'] : [];
    $id_grado = isset($_POST['grado']) ? (int)$_POST['grado'] : 0;
    $id_seccion = isset($_POST['seccion']) ? (int)$_POST['seccion'] : 0;
    
    if (empty($ids) || !is_array($ids)) {
        echo json_encode(['success' => false, 'message' => 'No se proporcionaron IDs válidos']);
        exit;
    }
    
    // Validar que todos los IDs sean números
    $ids = array_map('intval', $ids);
    $ids = array_filter($ids, function($id) {
        return $id > 0;
    });
    
    if (empty($ids)) {
        echo json_encode(['success' => false, 'message' => 'No hay IDs válidos para eliminar']);
        exit;
    }
    
    // Preparar placeholders para la consulta
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    
    // Eliminar los bloques
    $sql = "DELETE FROM horario_detalle WHERE id_detalle IN ($placeholders)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($ids);
    
    $eliminados = $stmt->rowCount();
    
    echo json_encode([
        'success' => true,
        'message' => "Se eliminaron $eliminados bloque(s) exitosamente",
        'eliminados' => $eliminados
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al eliminar bloques: ' . $e->getMessage()
    ]);
}
?>







