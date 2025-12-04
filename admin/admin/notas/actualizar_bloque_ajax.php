<?php
include('../../app/config.php');
if (session_status() === PHP_SESSION_NONE) session_start();

header('Content-Type: application/json');

try {
    $id_bloque = isset($_POST['id_bloque']) ? (int)$_POST['id_bloque'] : 0;
    $id_materia = isset($_POST['id_materia']) ? (int)$_POST['id_materia'] : 0;
    $id_profesor = isset($_POST['id_profesor']) && $_POST['id_profesor'] !== '' ? (int)$_POST['id_profesor'] : null;
    $dia_semana = isset($_POST['dia_semana']) ? trim($_POST['dia_semana']) : null;
    $hora_inicio = isset($_POST['hora_inicio']) ? trim($_POST['hora_inicio']) : null;
    $hora_fin = isset($_POST['hora_fin']) ? trim($_POST['hora_fin']) : null;
    
    if ($id_bloque <= 0 || $id_materia <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Datos inválidos'
        ]);
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
        echo json_encode([
            'success' => false,
            'message' => 'No se pudo detectar la clave primaria'
        ]);
        exit;
    }
    
    // Normalizar horas si se proporcionan
    if ($hora_inicio && strlen($hora_inicio) == 5) {
        $hora_inicio .= ':00';
    }
    if ($hora_fin && strlen($hora_fin) == 5) {
        $hora_fin .= ':00';
    }
    
    // Construir query de actualización dinámicamente
    $campos = ['id_materia = :mat'];
    $valores = [':mat' => $id_materia];
    
    if ($id_profesor === null) {
        $campos[] = 'id_profesor = NULL';
    } else {
        $campos[] = 'id_profesor = :prof';
        $valores[':prof'] = $id_profesor;
    }
    
    if ($dia_semana) {
        $campos[] = 'dia_semana = :dia';
        $valores[':dia'] = $dia_semana;
    }
    
    if ($hora_inicio) {
        $campos[] = 'hora_inicio = :hi';
        $valores[':hi'] = $hora_inicio;
    }
    
    if ($hora_fin) {
        $campos[] = 'hora_fin = :hf';
        $valores[':hf'] = $hora_fin;
    }
    
    $valores[':id'] = $id_bloque;
    
    $sql = "UPDATE horario_detalle SET " . implode(', ', $campos) . " WHERE $pk = :id";
    $up = $pdo->prepare($sql);
    
    foreach ($valores as $key => $value) {
        if ($key === ':prof' && $id_profesor === null) {
            continue; // Ya se maneja con NULL en el SQL
        }
        $up->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    
    $up->execute();
    
    echo json_encode([
        'success' => true,
        'message' => 'Bloque actualizado correctamente'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>

