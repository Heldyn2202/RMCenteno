<?php
/**
 * Endpoint AJAX para obtener materias filtradas por profesor y sección
 * GET: id_profesor, id_seccion
 * Solo muestra materias que el profesor imparte en esa sección específica
 */

// Incluir configuración
$configPath = dirname(__DIR__) . '/../../app/config.php';
if (file_exists($configPath)) {
    include($configPath);
} else {
    // Fallback si la ruta relativa no funciona
    include(__DIR__ . '/../../../app/config.php');
}

// Limpiar cualquier output anterior ANTES de cualquier cosa
while (ob_get_level() > 0) {
    ob_end_clean();
}

// Activar buffer de salida para capturar cualquier error
ob_start();

// Evitar que se muestren errores de PHP en la respuesta JSON
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Verificar que $pdo existe
if (!isset($pdo)) {
    $response = [
        'success' => false,
        'message' => 'Error de conexión a la base de datos',
        'data' => []
    ];
    ob_clean();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

try {
    $id_profesor = isset($_GET['id_profesor']) ? (int)$_GET['id_profesor'] : 0;
    $id_seccion = isset($_GET['id_seccion']) ? (int)$_GET['id_seccion'] : 0;
    $id_grado = isset($_GET['id_grado']) ? (int)$_GET['id_grado'] : 0;
    
    if ($id_profesor <= 0 || $id_seccion <= 0) {
        $response = [
            'success' => false,
            'message' => 'Profesor o sección no válidos',
            'data' => [],
            'debug' => ['id_profesor' => $id_profesor, 'id_seccion' => $id_seccion]
        ];
        if (ob_get_level() > 0) ob_clean();
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Obtener el grado de la sección si no se proporcionó
    if ($id_grado <= 0) {
        $stmtSecc = $pdo->prepare("SELECT id_grado FROM secciones WHERE id_seccion = :id_seccion");
        $stmtSecc->bindParam(':id_seccion', $id_seccion, PDO::PARAM_INT);
        $stmtSecc->execute();
        $seccionData = $stmtSecc->fetch(PDO::FETCH_ASSOC);
        if ($seccionData) {
            $id_grado = (int)$seccionData['id_grado'];
        }
    }
    
    // Verificar que la tabla asignaciones_profesor existe
    $tables = $pdo->query("SHOW TABLES LIKE 'asignaciones_profesor'")->fetchAll();
    if (empty($tables)) {
        $response = [
            'success' => false,
            'message' => 'La tabla asignaciones_profesor no existe en la base de datos',
            'data' => []
        ];
        if (ob_get_level() > 0) ob_clean();
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Verificar que existen asignaciones para este profesor y sección
    $checkSql = "SELECT COUNT(*) as total 
                 FROM asignaciones_profesor 
                 WHERE id_profesor = :id_profesor 
                   AND id_seccion = :id_seccion 
                   AND estado = 1";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->bindParam(':id_profesor', $id_profesor, PDO::PARAM_INT);
    $checkStmt->bindParam(':id_seccion', $id_seccion, PDO::PARAM_INT);
    $checkStmt->execute();
    $checkResult = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    // Obtener materias que el profesor imparte en esta sección específica
    // usando la tabla asignaciones_profesor
    // IMPORTANTE: Si una materia está en asignaciones_profesor para ese profesor y sección,
    // DEBE mostrarse, porque significa que ya fue validada y asignada correctamente.
    // El filtro por grado es opcional para mejorar la UX, pero NO debe excluir materias válidas.
    // Nota: La tabla materias tiene 'codigo' no 'codigo_materia'
    
    // Estrategia: Mostrar TODAS las materias asignadas al profesor en esa sección
    // Si id_grado > 0, intentar priorizar materias del grado, pero no excluir las demás
    // IMPORTANTE: Si una materia está en asignaciones_profesor, debe mostrarse siempre
    $sql = "SELECT DISTINCT 
                m.id_materia,
                m.nombre_materia,
                m.codigo,
                m.id_grado,
                ap.id_asignacion,
                s.id_grado as grado_seccion
            FROM asignaciones_profesor ap
            INNER JOIN materias m ON m.id_materia = ap.id_materia
            INNER JOIN secciones s ON s.id_seccion = ap.id_seccion
            WHERE ap.id_profesor = :id_profesor
              AND ap.id_seccion = :id_seccion
              AND ap.estado = 1
              AND m.estado = 1
            ORDER BY m.nombre_materia";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_profesor', $id_profesor, PDO::PARAM_INT);
    $stmt->bindParam(':id_seccion', $id_seccion, PDO::PARAM_INT);
    $stmt->execute();
    
    $materias = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Si no hay materias, devolver éxito pero con array vacío
    if (empty($materias)) {
        $response = [
            'success' => true,
            'data' => [],
            'count' => 0,
            'message' => 'No hay materias asignadas para este profesor en esta sección',
            'debug' => [
                'asignaciones_encontradas' => (int)$checkResult['total'],
                'id_profesor' => $id_profesor,
                'id_seccion' => $id_seccion
            ]
        ];
        if (ob_get_level() > 0) ob_clean();
        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
    
    // También obtener grados asociados para cada materia
    $formatted = [];
    foreach ($materias as $mat) {
        // Obtener grados asociados adicionales de grados_materias (si la tabla existe)
        $gradosAsoc = [];
        try {
            $stmtGrados = $pdo->prepare("
                SELECT id_grado 
                FROM grados_materias 
                WHERE id_materia = :id_materia
            ");
            $stmtGrados->bindParam(':id_materia', $mat['id_materia'], PDO::PARAM_INT);
            $stmtGrados->execute();
            $gradosAsoc = $stmtGrados->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $e) {
            // Si la tabla grados_materias no existe o hay error, usar solo el id_grado de la materia
            $gradosAsoc = [];
        }
        
        // Combinar grados (solo si id_grado no es null)
        $idGrado = isset($mat['id_grado']) && $mat['id_grado'] ? (int)$mat['id_grado'] : 0;
        $grados = $idGrado > 0 ? array_unique(array_merge([$idGrado], $gradosAsoc)) : $gradosAsoc;
        
        $formatted[] = [
            'id' => (int)$mat['id_materia'],
            'nombre' => $mat['nombre_materia'] ?? 'Sin nombre',
            'codigo' => $mat['codigo'] ?? '',  // La columna se llama 'codigo' no 'codigo_materia'
            'id_grado' => $idGrado,
            'grados_asociados' => array_map('intval', array_filter($grados)),
            'id_asignacion' => isset($mat['id_asignacion']) ? (int)$mat['id_asignacion'] : 0
        ];
    }
    
    $response = [
        'success' => true,
        'data' => $formatted,
        'count' => count($formatted)
    ];
    
    // Limpiar cualquier output antes del JSON
    ob_clean();
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    ob_end_flush();
    exit;
    
} catch (PDOException $e) {
    // Error de base de datos
    $response = [
        'success' => false,
        'message' => 'Error de base de datos: ' . $e->getMessage(),
        'data' => [],
        'error_code' => $e->getCode(),
        'sql_state' => $e->errorInfo[0] ?? null
    ];
    
    ob_clean();
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    ob_end_flush();
    exit;
    
} catch (Exception $e) {
    // Error general
    $response = [
        'success' => false,
        'message' => 'Error: ' . $e->getMessage(),
        'data' => [],
        'error_type' => get_class($e),
        'file' => basename($e->getFile()),
        'line' => $e->getLine()
    ];
    
    ob_clean();
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    ob_end_flush();
    exit;
}
?>

