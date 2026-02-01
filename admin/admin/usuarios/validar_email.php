<?php
include ('../../config.php');

$response = ['existe' => false];

// Verificar si se recibió el parámetro email
if (isset($_GET['email'])) {
    // Decodificar el email correctamente
    $email = urldecode($_GET['email']);
    
    // Depuración: ver qué email llega
    error_log("Email recibido para validar: " . $email);
    
    // Buscar el email en la base de datos
    $sql = "SELECT id_usuario FROM usuarios WHERE email = ? AND estado = '1'";
    $query = $pdo->prepare($sql);
    
    try {
        $query->execute([$email]);
        $usuario = $query->fetch(PDO::FETCH_ASSOC);
        
        if ($usuario) {
            $response['existe'] = true;
            error_log("Email encontrado en la base de datos: " . $email);
        } else {
            error_log("Email NO encontrado en la base de datos: " . $email);
        }
    } catch (PDOException $e) {
        error_log("Error en la consulta: " . $e->getMessage());
        $response['error'] = 'Error en la base de datos';
    }
} else {
    error_log("No se recibió parámetro email");
    $response['error'] = 'No se recibió email';
}

// Establecer cabeceras JSON
header('Content-Type: application/json; charset=utf-8');

// Enviar respuesta
echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>