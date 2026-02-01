<?php
include ('../../config.php');

$response = ['existe' => false];

// Verificar si se recibió el parámetro cédula
if (isset($_GET['cedula'])) {
    $cedula = $_GET['cedula'];
    $id_usuario = isset($_GET['id_usuario']) ? $_GET['id_usuario'] : 0;
    
    // Depuración
    error_log("Cédula recibida para validar: " . $cedula);
    
    // NUEVA LÓGICA: Buscar en la tabla PERSONAS (no en profesores)
    $sql = "SELECT p.id_persona, p.usuario_id, u.email 
            FROM personas p 
            LEFT JOIN usuarios u ON p.usuario_id = u.id_usuario 
            WHERE p.ci = ? AND p.estado = '1'";
    $query = $pdo->prepare($sql);
    
    try {
        $query->execute([$cedula]);
        $persona = $query->fetch(PDO::FETCH_ASSOC);
        
        if ($persona) {
            // Si estamos editando, ignorar si es el mismo usuario
            if ($id_usuario > 0 && $persona['usuario_id'] == $id_usuario) {
                $response['existe'] = false;
                error_log("Cédula pertenece al mismo usuario en edición");
            } else {
                $response['existe'] = true;
                $response['detalles'] = [
                    'id_persona' => $persona['id_persona'],
                    'usuario_id' => $persona['usuario_id'],
                    'email' => $persona['email']
                ];
                error_log("Cédula encontrada en la tabla personas: " . $cedula);
            }
        } else {
            // También verificar en usuarios si es administrador sin persona
            $sql_usuario = "SELECT id_usuario FROM usuarios WHERE ci = ? AND estado = '1'";
            $query_usuario = $pdo->prepare($sql_usuario);
            $query_usuario->execute([$cedula]);
            $usuario = $query_usuario->fetch(PDO::FETCH_ASSOC);
            
            if ($usuario) {
                if ($id_usuario > 0 && $usuario['id_usuario'] == $id_usuario) {
                    $response['existe'] = false;
                } else {
                    $response['existe'] = true;
                    $response['detalles'] = ['usuario_id' => $usuario['id_usuario']];
                    error_log("Cédula encontrada en tabla usuarios: " . $cedula);
                }
            } else {
                error_log("Cédula NO encontrada en la base de datos: " . $cedula);
            }
        }
    } catch (PDOException $e) {
        error_log("Error en la consulta: " . $e->getMessage());
        $response['error'] = 'Error en la base de datos';
        $response['detalles_error'] = $e->getMessage();
    }
} else {
    error_log("No se recibió parámetro cédula");
    $response['error'] = 'No se recibió cédula';
}

// Establecer cabeceras JSON
header('Content-Type: application/json; charset=utf-8'); 

// Enviar respuesta
echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit;
?>