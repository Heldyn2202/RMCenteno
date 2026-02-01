<?php
// datos_del_usuario.php - VERSIÓN SIMPLIFICADA

$id_usuario = $_GET['id'];

// Consulta para obtener datos del usuario
$sql = "SELECT u.*, r.nombre_rol, r.id_rol as rol_id 
        FROM usuarios u 
        INNER JOIN roles r ON u.rol_id = r.id_rol 
        WHERE u.id_usuario = :id_usuario";
$query = $pdo->prepare($sql);
$query->bindParam(':id_usuario', $id_usuario);
$query->execute();

// Usar fetch() en lugar de fetchAll() ya que es un solo usuario
$usuario = $query->fetch(PDO::FETCH_ASSOC);

if ($usuario) {
    // Asignar variables
    $id_usuario = $usuario['id_usuario'];
    $rol_id = $usuario['rol_id']; 
    $email = $usuario['email'];
    $nombre_rol = $usuario['nombre_rol'];
    $fyh_creacion = $usuario['fyh_creacion'];
    $estado = $usuario['estado'];
    $fyh_actualizacion = $usuario['fyh_actualizacion'];
    
    // Obtener datos personales según el rol
    $datos_personales = null;
    $tipo_persona = "";
    
    try {
        switch ($rol_id) {
            case 5: // DOCENTE
                $tipo_persona = "Docente";
                
                // Intentar diferentes nombres de columna
                $nombres_columnas = ['id_usuario', 'usuario_id', 'id_usuarios'];
                
                foreach ($nombres_columnas as $columna) {
                    try {
                        $sql_profesor = "SELECT * FROM profesores WHERE $columna = :id_usuario LIMIT 1";
                        $query_profesor = $pdo->prepare($sql_profesor);
                        $query_profesor->bindParam(':id_usuario', $id_usuario);
                        $query_profesor->execute();
                        $datos_personales = $query_profesor->fetch(PDO::FETCH_ASSOC);
                        
                        if ($datos_personales) break;
                    } catch (PDOException $e) {
                        // Continuar con el siguiente nombre de columna
                        continue;
                    }
                }
                
                // Si aún no se encontró, buscar por email
                if (!$datos_personales) {
                    try {
                        $sql_profesor = "SELECT * FROM profesores WHERE email = :email LIMIT 1";
                        $query_profesor = $pdo->prepare($sql_profesor);
                        $query_profesor->bindParam(':email', $email);
                        $query_profesor->execute();
                        $datos_personales = $query_profesor->fetch(PDO::FETCH_ASSOC);
                    } catch (PDOException $e) {
                        // No hacer nada, dejar $datos_personales como null
                    }
                }
                break;
                
            case 2: // ADMINISTRATIVO
                $tipo_persona = "Administrativo";
                // Similar lógica para administrativos...
                break;
                
            case 3: // ESTUDIANTE
                $tipo_persona = "Estudiante";
                // Similar lógica para estudiantes...
                break;
                
            case 4: // REPRESENTANTE
                $tipo_persona = "Representante";
                // Similar lógica para representantes...
                break;
                
            case 1: // ADMINISTRADOR
                $tipo_persona = "Administrador";
                break;
                
            default:
                $tipo_persona = "Usuario";
                break;
        }
    } catch (PDOException $e) {
        error_log("Error al obtener datos personales: " . $e->getMessage());
        // Continuar sin datos personales
    }
} else {
    // Usuario no encontrado
    echo "<script>
        Swal.fire({
            icon: 'error',
            title: 'Usuario no encontrado',
            text: 'El usuario solicitado no existe en el sistema',
            confirmButtonText: 'Aceptar'
        }).then(() => {
            window.location.href = '" . APP_URL . "/admin/usuarios';
        });
    </script>";
    exit();
}
?>