<?php
include('../../app/config.php');

$id_profesor = $_GET['id'];
$nuevo_estado = $_GET['estado'];

try {
    // 1. Obtener datos del profesor
    $sql_get_profesor = "SELECT p.*, u.id_usuario as usuario_id 
                        FROM profesores p 
                        LEFT JOIN usuarios u ON p.email = u.email 
                        WHERE p.id_profesor = :id_profesor";
    $query_get_profesor = $pdo->prepare($sql_get_profesor);
    $query_get_profesor->bindParam(':id_profesor', $id_profesor);
    $query_get_profesor->execute();
    $profesor_data = $query_get_profesor->fetch(PDO::FETCH_ASSOC);
    
    if ($profesor_data) {
        $nombre_profesor = $profesor_data['nombres'] . ' ' . $profesor_data['apellidos'];
        $email_profesor = $profesor_data['email'];
        
        // 2. Inhabilitar/Habilitar el profesor
        $sql_profesor = "UPDATE profesores SET estado = :estado WHERE id_profesor = :id_profesor";
        $query_profesor = $pdo->prepare($sql_profesor);
        $query_profesor->bindParam(':estado', $nuevo_estado);
        $query_profesor->bindParam(':id_profesor', $id_profesor);
        $profesor_actualizado = $query_profesor->execute();
        
        // 3. Actualizar estado del usuario si existe
        if ($profesor_actualizado && !empty($email_profesor)) {
            $sql_usuario = "UPDATE usuarios SET estado = :estado WHERE email = :email";
            $query_usuario = $pdo->prepare($sql_usuario);
            $query_usuario->bindParam(':estado', $nuevo_estado);
            $query_usuario->bindParam(':email', $email_profesor);
            $query_usuario->execute();
        }
        
        // 4. Preparar mensaje según acción
        if ($nuevo_estado == 1) {
            // HABILITAR
            $mensaje = "✅ <strong>PROFESOR HABILITADO</strong><br><br>
                       <div style='background:#e8f5e9; padding:15px; border-radius:8px; border-left:4px solid #28a745;'>
                       <strong>📋 PROFESOR:</strong> {$nombre_profesor}<br>
                       <strong>📧 EMAIL:</strong> {$email_profesor}<br>
                       <strong>📱 ESTADO:</strong> <span style='color:#28a745; font-weight:bold;'>ACTIVADO</span>
                       </div><br>
                       <small>• El profesor puede iniciar sesión normalmente</small>";
        } else {
            // INHABILITAR
            $mensaje = "⚠️ <strong>PROFESOR INHABILITADO</strong><br><br>
                       <div style='background:#fff3cd; padding:15px; border-radius:8px; border-left:4px solid #ffc107;'>
                       <strong>📋 PROFESOR:</strong> {$nombre_profesor}<br>
                       <strong>📧 EMAIL:</strong> {$email_profesor}<br>
                       <strong>📱 ESTADO:</strong> <span style='color:#dc3545; font-weight:bold;'>INHABILITADO</span>
                       </div><br>
                       <small>• El profesor <strong>NO PODRÁ</strong> iniciar sesión</small>";
        }
        
        header("Location: listar_profesores.php?success=" . urlencode($mensaje));
    } else {
        header("Location: listar_profesores.php?error=❌ Profesor no encontrado");
    }
} catch (PDOException $e) {
    error_log("Error en cambiar_estado.php: " . $e->getMessage());
    header("Location: listar_profesores.php?error=❌ Error del sistema");
}