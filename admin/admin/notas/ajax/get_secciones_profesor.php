<?php
// Devuelve JSON con las secciones que un profesor imparte en una gestión (por defecto la gestión activa).


header('Content-Type: application/json; charset=utf-8');
// No mostrar warnings en la salida JSON
ini_set('display_errors', '0');
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

try {
  
    $configPath = __DIR__ . '/../../../app/config.php';
    if (!file_exists($configPath)) {
        http_response_code(500);
        echo json_encode(['error' => 'Archivo de configuración no encontrado', 'path' => $configPath]);
        exit;
    }
    require_once $configPath;

    // comprobar que $pdo esté definido
    if (!isset($pdo) || !$pdo) {
        http_response_code(500);
        echo json_encode(['error' => 'Conexión a base de datos no disponible después de incluir config']);
        exit;
    }

    // parámetros
    $id_profesor = isset($_GET['id_profesor']) ? intval($_GET['id_profesor']) : 0;
    $id_gestion  = isset($_GET['id_gestion']) ? intval($_GET['id_gestion']) : 0;

    // si no se pasa gestión, tomar la gestión activa
    if (!$id_gestion) {
        $qg = $pdo->query("SELECT id_gestion FROM gestiones WHERE estado = 1 LIMIT 1")->fetch(PDO::FETCH_ASSOC);
        $id_gestion = $qg['id_gestion'] ?? 0;
    }

    if (!$id_profesor || !$id_gestion) {
        // devolver array vacío para el frontend
        echo json_encode([]);
        exit;
    }

    $sql = "
        SELECT DISTINCT s.id_seccion, CONCAT(g.grado, ' - ', s.nombre_seccion) AS nombre
        FROM asignaciones_profesor ap
        JOIN secciones s ON s.id_seccion = ap.id_seccion
        JOIN grados g ON g.id_grado = s.id_grado
        WHERE ap.id_profesor = :id_profesor
          AND ap.id_gestion = :id_gestion
          AND ap.estado = 1
          AND s.estado = 1
        ORDER BY g.id_grado, s.nombre_seccion
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':id_profesor' => $id_profesor,
        ':id_gestion'  => $id_gestion
    ]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($rows);
    exit;

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Excepción en servidor', 'detalle' => $e->getMessage()]);
    exit;
}
?>