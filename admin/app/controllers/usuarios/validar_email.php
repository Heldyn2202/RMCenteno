<?php
include ('../../config.php');

$response = ['existe' => false];

// Verificar si se recibió el parámetro email
if (isset($_GET['email'])) {
    // Decodificar el email correctamente
    $email = urldecode($_GET['email']);
    $id_usuario = isset($_GET['id_usuario']) ? $_GET['id_usuario'] : 0;
    
    // Depuración: ver qué email llega
    error_log("Email recibido para validar: " . $email);
    
    // Validar formato básico de email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['error'] = 'Formato de email inválido';
        $response['existe'] = true; // Para que no pase la validación
        error_log("Formato de email inválido: " . $email);
        
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Buscar el email en la base de datos
    $sql = "SELECT id_usuario, rol_id, estado FROM usuarios WHERE email = ?";
    $query = $pdo->prepare($sql);
    
    try {
        $query->execute([$email]);
        $usuario = $query->fetch(PDO::FETCH_ASSOC);
        
        if ($usuario) {
            // Verificar si el usuario está activo
            if ($usuario['estado'] == '1') {
                // Si estamos editando, ignorar si es el mismo usuario
                if ($id_usuario > 0 && $usuario['id_usuario'] == $id_usuario) {
                    $response['existe'] = false;
                    error_log("Email pertenece al mismo usuario en edición");
                } else {
                    $response['existe'] = true;
                    $response['detalles'] = [
                        'id_usuario' => $usuario['id_usuario'],
                        'rol_id' => $usuario['rol_id']
                    ];
                    error_log("Email encontrado en la base de datos: " . $email);
                }
            } else {
                // Usuario existe pero está inactivo
                $response['existe'] = true;
                $response['inactivo'] = true;
                $response['detalles'] = [
                    'id_usuario' => $usuario['id_usuario'],
                    'estado' => $usuario['estado']
                ];
                error_log("Email encontrado pero usuario inactivo: " . $email);
            }
        } else {
            // También verificar si el email existe en personas (para consistencia)
            $sql_personas = "SELECT p.id_persona, p.email_personal 
                            FROM personas p 
                            WHERE p.email_personal = ? AND p.estado = '1'";
            $query_personas = $pdo->prepare($sql_personas);
            $query_personas->execute([$email]);
            $persona = $query_personas->fetch(PDO::FETCH_ASSOC);
            
            if ($persona) {
                $response['existe'] = true;
                $response['en_personas'] = true;
                error_log("Email encontrado en tabla personas: " . $email);
            } else {
                error_log("Email NO encontrado en la base de datos: " . $email);
            }
        }
    } catch (PDOException $e) {
        error_log("Error en la consulta: " . $e->getMessage());
        $response['error'] = 'Error en la base de datos';
        $response['detalles_error'] = $e->getMessage();
    }
} else {
    error_log("No se recibió parámetro email");
    $response['error'] = 'No se recibió email';
}

// Establecer cabeceras JSON
header('Content-Type: application/json; charset=utf-8');

// Enviar respuesta
echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit;
?>