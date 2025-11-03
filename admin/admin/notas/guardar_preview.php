<?php
// Guardar datos del formulario en sesión para previsualización
include('../../app/config.php');
if (session_status() === PHP_SESSION_NONE) session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Guardar todos los datos del formulario en sesión
    $_SESSION['horario_data'] = [
        'grado' => $_POST['grado'] ?? null,
        'seccion' => $_POST['seccion'] ?? null,
        'aula' => $_POST['aula'] ?? '',
        'fecha_inicio' => $_POST['fecha_inicio'] ?? '',
        'fecha_fin' => $_POST['fecha_fin'] ?? '',
        'horario' => $_POST['horario'] ?? [],
        'gestion_activa' => $_POST['id_gestion'] ?? null
    ];
    
    echo json_encode(['success' => true]);
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
}
?>



