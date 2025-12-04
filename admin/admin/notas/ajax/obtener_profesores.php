<?php
/**
 * Endpoint AJAX para obtener profesores filtrados por sección
 * GET: id_seccion
 * Solo muestra profesores que tienen asignaciones activas en esa sección
 */

// Incluir configuración
$configPath = dirname(__DIR__) . '/../../app/config.php';
if (file_exists($configPath)) {
    include($configPath);
} else {
    // Fallback si la ruta relativa no funciona
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
    $id_seccion = isset($_GET['id_seccion']) ? (int)$_GET['id_seccion'] : 0;
    
    if ($id_seccion <= 0) {
        echo json_encode(['success' => false, 'message' => 'Sección no válida', 'data' => []]);
        exit;
    }
    
    // Obtener profesores que tienen asignaciones activas en esta sección específica
    $sql = "SELECT DISTINCT 
                p.id_profesor,
                p.nombres,
                p.apellidos,
                p.email,
                p.cedula
            FROM profesores p
            INNER JOIN asignaciones_profesor ap ON p.id_profesor = ap.id_profesor
            WHERE ap.id_seccion = :id_seccion
              AND ap.estado = 1
              AND p.estado = 1
            ORDER BY p.apellidos, p.nombres";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_seccion', $id_seccion, PDO::PARAM_INT);
    $stmt->execute();
    
    $profesores = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatear datos
    $formatted = [];
    foreach ($profesores as $prof) {
        $formatted[] = [
            'id' => (int)$prof['id_profesor'],
            'nombre_completo' => trim($prof['nombres'] . ' ' . $prof['apellidos']),
            'nombres' => $prof['nombres'],
            'apellidos' => $prof['apellidos'],
            'email' => $prof['email'] ?? '',
            'cedula' => $prof['cedula'] ?? ''
        ];
    }
    
    echo json_encode([
        'success' => true,
        'data' => $formatted,
        'count' => count($formatted)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage(),
        'data' => []
    ]);
}
?>

