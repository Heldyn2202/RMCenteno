<?php
/**
 * Endpoint AJAX para obtener secciones filtradas por grado
 * GET: id_grado
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
    $id_grado = isset($_GET['id_grado']) ? (int)$_GET['id_grado'] : 0;
    
    if ($id_grado <= 0) {
        echo json_encode(['success' => false, 'message' => 'Grado no válido', 'data' => []]);
        exit;
    }
    
    // Obtener gestión activa
    $gestion_activa = $pdo->query("SELECT * FROM gestiones WHERE estado = 1 ORDER BY desde DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    
    // Verificar si la tabla secciones tiene columna id_gestion
    $colsSecciones = $pdo->query("SHOW COLUMNS FROM secciones")->fetchAll(PDO::FETCH_COLUMN);
    $hasGestionSecc = in_array('id_gestion', $colsSecciones, true);
    
    // Construir consulta
    $sql = "SELECT id_seccion, nombre_seccion, turno 
            FROM secciones 
            WHERE estado = 1 AND id_grado = :id_grado";
    
    if ($hasGestionSecc && $gestion_activa) {
        $sql .= " AND id_gestion = :id_gestion";
    }
    
    $sql .= " ORDER BY nombre_seccion, turno";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_grado', $id_grado, PDO::PARAM_INT);
    
    if ($hasGestionSecc && $gestion_activa) {
        $stmt->bindParam(':id_gestion', $gestion_activa['id_gestion'], PDO::PARAM_INT);
    }
    
    $stmt->execute();
    $secciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatear datos para el select
    $formatted = [];
    foreach ($secciones as $sec) {
        $letra = strtoupper(trim($sec['nombre_seccion']));
        $turno = strtoupper(trim((string)($sec['turno'] ?? '')));
        
        // Normalizar turno
        if (in_array($turno, ['M','MAÑANA','MANANA','MATUTINO','AM','MORNING'])) {
            $turno = 'M';
        } elseif (in_array($turno, ['T','TARDE','VESPERTINO','PM','AFTERNOON'])) {
            $turno = 'T';
        } else {
            $turno = $turno ?: 'M';
        }
        
        $formatted[] = [
            'id' => (int)$sec['id_seccion'],
            'nombre' => $letra . ' (' . $turno . ')',
            'nombre_seccion' => $letra,
            'turno' => $turno
        ];
    }
    
    echo json_encode([
        'success' => true,
        'data' => $formatted
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage(),
        'data' => []
    ]);
}
?>

