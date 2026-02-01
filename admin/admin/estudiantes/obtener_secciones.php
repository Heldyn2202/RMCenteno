<?php
include('../../app/config.php');

// Validar parámetros
if (!isset($_GET['turno']) || !isset($_GET['grado'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Faltan parámetros requeridos (turno y grado).']);
    exit;
}

$turno = $_GET['turno'];
$grado = $_GET['grado'];

try {
    // Obtener el periodo académico activo
    $sql_gestiones = "SELECT * FROM gestiones WHERE estado = 1 ORDER BY desde DESC LIMIT 1";  
    $query_gestiones = $pdo->prepare($sql_gestiones);  
    $query_gestiones->execute();  
    $gestion_activa = $query_gestiones->fetch(PDO::FETCH_ASSOC);
    
    if (!$gestion_activa) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'No hay periodo académico activo.']);
        exit;
    }
    
    $id_gestion_activa = $gestion_activa['id_gestion'];
    
    // Consulta para obtener las secciones
    $sql = "SELECT id_seccion, nombre_seccion, capacidad, cupo_actual 
            FROM secciones 
            WHERE turno = :turno 
            AND id_grado = :grado 
            AND id_gestion = :id_gestion 
            AND estado = 1
            ORDER BY nombre_seccion";
    
    $query = $pdo->prepare($sql);
    $query->bindParam(':turno', $turno);
    $query->bindParam(':grado', $grado);
    $query->bindParam(':id_gestion', $id_gestion_activa);
    $query->execute();
    $secciones = $query->fetchAll(PDO::FETCH_ASSOC);
    
    header('Content-Type: application/json');
    echo json_encode($secciones);
    
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Error al obtener secciones: ' . $e->getMessage()]);
}
?>