<?php
include('../../app/config.php');

// Validar parámetro
if (!isset($_GET['id_seccion']) || empty($_GET['id_seccion'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Falta el parámetro id_seccion.']);
    exit;
}

$id_seccion = $_GET['id_seccion'];

try {
    // Consulta para obtener cupos
    $sql = "SELECT capacidad, cupo_actual FROM secciones WHERE id_seccion = :id_seccion";
    $query = $pdo->prepare($sql);
    $query->execute(['id_seccion' => $id_seccion]);
    $seccion = $query->fetch(PDO::FETCH_ASSOC);

    if ($seccion) {
        $cupos_disponibles = $seccion['capacidad'] - $seccion['cupo_actual'];
        header('Content-Type: application/json');
        echo json_encode([
            'cupos_disponibles' => $cupos_disponibles,
            'capacidad' => $seccion['capacidad'],
            'cupo_actual' => $seccion['cupo_actual']
        ]);
    } else {
        header('Content-Type: application/json');
        echo json_encode([
            'error' => 'Sección no encontrada',
            'cupos_disponibles' => 0
        ]);
    }
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'error' => 'Error: ' . $e->getMessage(),
        'cupos_disponibles' => 0
    ]);
}
?>