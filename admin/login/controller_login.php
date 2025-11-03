<?php  
include('../app/config.php');  

if ($_SERVER['REQUEST_METHOD'] === 'POST') {  
    $email = $_POST['email'];  
    $password = $_POST['password'];  

    $sql = "SELECT * FROM usuarios WHERE email = :email";  
    $query = $pdo->prepare($sql);  
    $query->bindParam(':email', $email);  
    $query->execute();  
    $usuario = $query->fetch(PDO::FETCH_ASSOC);  

    if ($usuario) {  
        if ($usuario['estado'] == '0') {
            session_start();  
            $_SESSION['mensaje'] = "El usuario está inactivo. Contacte al administrador.";  
            $_SESSION['icono'] = "warning";
            header('Location:' . APP_URL . "/login/login.php");  
            exit;  
        } else {
            if (password_verify($password, $usuario['password'])) {  
                session_start();  
                
                // DATOS BÁSICOS DE SESIÓN
                $_SESSION['usuario_id'] = $usuario['id_usuario']; 
                $_SESSION['rol_id'] = $usuario['rol_id'];
                $_SESSION['sesion_email'] = $email;
                
                // ====================================================
                // DETECCIÓN AUTOMÁTICA DE DOCENTES
                // ====================================================
                if ($usuario['rol_id'] == 5) { // DOCENTE (rol_id = 5)
                    // Buscar información adicional en la tabla PROFESORES
                    $sql_profesor = "SELECT p.* FROM profesores p WHERE p.email = ? AND p.estado = 1";
                    $query_profesor = $pdo->prepare($sql_profesor);
                    $query_profesor->execute([$email]);
                    $profesor = $query_profesor->fetch(PDO::FETCH_ASSOC);
                    
                    if ($profesor) {
                        // Guardar datos específicos del docente en sesión
                        $_SESSION['id_profesor'] = $profesor['id_profesor'];
                        $_SESSION['nombre_profesor'] = $profesor['nombres'] . ' ' . $profesor['apellidos'];
                        $_SESSION['es_docente'] = true;
                        $_SESSION['especialidad'] = $profesor['especialidad'];
                        
                        $_SESSION['mensaje'] = "👨‍🏫 Bienvenido Prof. " . $profesor['nombres'] . " al Módulo de Notas";  
                        $_SESSION['icono'] = "success";  
                        
                        // Redirigir directamente al módulo de notas
                        header('Location:' . APP_URL . "/admin/notas/carga_notas_seccion.php");
                        exit;  
                    } else {
                        // Si es docente pero no está en la tabla profesores
                        $_SESSION['mensaje'] = "Usuario docente encontrado, pero falta información en el perfil. Contacte al administrador.";  
                        $_SESSION['icono'] = "warning";
                        header('Location:' . APP_URL . "/admin");
                        exit;
                    }
                }
                
                // ====================================================
                // REDIRECCIÓN PARA OTROS ROLES (FLUJO ORIGINAL)
                // ====================================================
                $_SESSION['mensaje'] = "Bienvenido al sistema SIGE";  
                $_SESSION['icono'] = "success";  
                
                // Redirección inteligente según rol
                if ($usuario['rol_id'] == 1) { // Admin
                    header('Location:' . APP_URL . "/admin");
                } else { // Otros roles (no docentes)
                    header('Location:' . APP_URL . "/admin/notas");
                }
                exit;  
            } else {  
                session_start();  
                $_SESSION['mensaje'] = "Error de Usuario o Contraseña";  
                $_SESSION['icono'] = "error";  
                header('Location:' . APP_URL . "/login/login.php");  
                exit;  
            }  
        }
    } else {  
        session_start();  
        $_SESSION['mensaje'] = "Error de Usuario o Contraseña";  
        $_SESSION['icono'] = "error";  
        header('Location:' . APP_URL . "/login/login.php");  
        exit;  
    }  
} else {  
    header('Location:' . APP_URL . "/login/login.php");  
    exit;  
}  
?>