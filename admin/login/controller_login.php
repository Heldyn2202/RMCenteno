<?php  
// controller_login.php - VERSIÓN FINAL FUNCIONAL
include('../app/config.php');  
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {  
    $email = $_POST['email'];  
    $password = $_POST['password'];  

    // Buscar usuario (todos los roles)
    $sql = "SELECT * FROM usuarios WHERE email = :email";  
    $query = $pdo->prepare($sql);  
    $query->bindParam(':email', $email);  
    $query->execute();  
    $usuario = $query->fetch(PDO::FETCH_ASSOC);  

    if ($usuario) {  
        // VERIFICAR SI EL USUARIO ESTÁ ACTIVO
        if ($usuario['estado'] == '0') {
            // Usuario inhabilitado
            $_SESSION['titulo'] = "❌ Cuenta Inhabilitada";
            $_SESSION['mensaje'] = "⚠️ <strong>ACCESO DENEGADO</strong><br><br>Su cuenta de usuario ha sido INHABILITADA.<br><br>• Contacte al administrador del sistema";  
            $_SESSION['icono'] = "error";
            header('Location:' . APP_URL . "/login/login.php");  
            exit;  
        } else {
            if (password_verify($password, $usuario['password'])) {  
                // Si es docente (rol_id = 5), verificar estado en profesores
                if ($usuario['rol_id'] == 5) {
                    // Buscar profesor por email
                    $sql_profesor = "SELECT * FROM profesores WHERE email = :email";
                    $query_profesor = $pdo->prepare($sql_profesor);
                    $query_profesor->bindParam(':email', $email);
                    $query_profesor->execute();
                    $profesor = $query_profesor->fetch(PDO::FETCH_ASSOC);
                    
                    if ($profesor) {
                        // VERIFICAR SI EL PROFESOR ESTÁ ACTIVO
                        if ($profesor['estado'] == 0) {
                            // Profesor inhabilitado - MOSTRAR SWEETALERT
                            $_SESSION['titulo'] = "❌ Profesor Inhabilitado";
                            $_SESSION['mensaje'] = "⚠️ <strong>ACCESO DENEGADO</strong><br><br><strong>PROFESOR:</strong> " . $profesor['nombres'] . " " . $profesor['apellidos'] . "<br><strong>EMAIL:</strong> " . $profesor['email'] . "<br><strong>ESTADO:</strong> INHABILITADO<br><br>Su perfil de profesor ha sido inhabilitado por el administrador.";  
                            $_SESSION['icono'] = "error";
                            header('Location:' . APP_URL . "/login/login.php");  
                            exit;
                        }
                        
                        // PROFESOR ACTIVO - Permitir acceso
                        // Guardar datos del profesor en sesión
                        $_SESSION['id_usuario'] = $usuario['id_usuario']; 
                        $_SESSION['usuario_id'] = $usuario['id_usuario']; 
                        $_SESSION['rol_id'] = $usuario['rol_id'];
                        $_SESSION['sesion_email'] = $email;
                        $_SESSION['login_time'] = time();
                        $_SESSION['id_profesor'] = $profesor['id_profesor'];
                        $_SESSION['nombre_profesor'] = $profesor['nombres'] . ' ' . $profesor['apellidos'];
                        $_SESSION['es_docente'] = true;
                        $_SESSION['especialidad'] = $profesor['especialidad'];
                        $_SESSION['cedula'] = $profesor['cedula'];
                        
                        // Mensaje de bienvenida para docente
                        $_SESSION['titulo'] = "✅ Bienvenido Profesor";
                        $_SESSION['mensaje'] = "👨‍🏫 <strong>¡BIENVENIDO!</strong><br><br>Bienvenido al sistema Prof. " . $profesor['nombres'];  
                        $_SESSION['icono'] = "success";  
                        
                        // Redirigir al dashboard
                        header('Location:' . APP_URL . "/admin");
                        exit;  
                        
                    } else {
                        // Es docente pero no tiene registro en profesores
                        $_SESSION['titulo'] = "⚠️ Perfil Incompleto";
                        $_SESSION['mensaje'] = "⚠️ <strong>PERFIL INCOMPLETO</strong><br><br>Contacte al administrador para completar su registro.";  
                        $_SESSION['icono'] = "warning";
                        
                        // Aún permitir acceso pero con datos básicos
                        $_SESSION['id_usuario'] = $usuario['id_usuario']; 
                        $_SESSION['usuario_id'] = $usuario['id_usuario']; 
                        $_SESSION['rol_id'] = $usuario['rol_id'];
                        $_SESSION['sesion_email'] = $email;
                        $_SESSION['login_time'] = time();
                        $_SESSION['es_docente'] = false;
                        
                        header('Location:' . APP_URL . "/admin");
                        exit;
                    }
                } else {
                    // NO ES DOCENTE (Admin, administrativo, etc.) - Permitir acceso
                    // DATOS BÁSICOS DE SESIÓN
                    $_SESSION['id_usuario'] = $usuario['id_usuario']; 
                    $_SESSION['usuario_id'] = $usuario['id_usuario']; 
                    $_SESSION['rol_id'] = $usuario['rol_id'];
                    $_SESSION['sesion_email'] = $email;
                    $_SESSION['login_time'] = time();
                    
                    // Mensaje según rol
                    $rol_nombre = "";
                    switch($usuario['rol_id']) {
                        case 1: $rol_nombre = "Administrador"; break;
                        case 2: $rol_nombre = "Administrativo"; break;
                        case 3: $rol_nombre = "Estudiante"; break;
                        case 4: $rol_nombre = "Representante"; break;
                        default: $rol_nombre = "Usuario";
                    }
                    
                    // Mensaje de bienvenida para admin/otros roles
                    $_SESSION['titulo'] = "✅ Acceso Concedido";
                    $_SESSION['mensaje'] = "✅ <strong>Bienvenido al Sistema. </strong><br><br> " ;  
                    $_SESSION['icono'] = "success";  
                    
                    // Redirigir al dashboard
                    header('Location:' . APP_URL . "/admin");
                    exit;  
                }
                
            } else {  
                // CONTRASEÑA INCORRECTA
                $_SESSION['titulo'] = "❌ Error de Acceso";
                $_SESSION['mensaje'] = "❌ <strong>CREDENCIALES INCORRECTAS</strong><br><br>Verifique usuario y contraseña.";  
                $_SESSION['icono'] = "error";  
                header('Location:' . APP_URL . "/login/login.php");  
                exit;  
            }  
        }
    } else {  
        // USUARIO NO EXISTE
        $_SESSION['titulo'] = "❌ Usuario no encontrado";
        $_SESSION['mensaje'] = "❌ <strong>USUARIO NO REGISTRADO</strong>";  
        $_SESSION['icono'] = "error";  
        header('Location:' . APP_URL . "/login/login.php");  
        exit;  
    }  
} else {  
    header('Location:' . APP_URL . "/login/login.php");  
    exit;  
}  