<?php
session_start();
echo "<h2>🔍 Diagnóstico de Sesión y Rutas</h2>";

echo "<h3>Variables de Sesión:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h3>Rutas Configuradas:</h3>";
$base_url = 'http://' . $_SERVER['HTTP_HOST'] . '/heldyn/centeno/';
echo "<p>Base URL: $base_url</p>";

$rutas = [
    'CMS Portal' => $base_url . 'admin/admin/portal-cms/index.php',
    'Login SIGE' => $base_url . 'admin/login/login.php',
    'Admin SIGE' => $base_url . 'admin/index.php?page=home',
    'Portal Principal' => $base_url . 'index.php',
    'Logout CMS' => $base_url . 'admin/admin/portal-cms/logout.php'
];

foreach ($rutas as $nombre => $url) {
    echo "<p><strong>$nombre:</strong> <a href='$url' target='_blank'>$url</a></p>";
}

echo "<h3>Acciones:</h3>";
echo "<a href='unify-session.php' class='btn btn-primary'>Unificar Sesiones</a> ";
echo "<a href='index.php' class='btn btn-success'>Ir al Dashboard</a> ";
echo "<a href='logout.php' class='btn btn-danger'>Logout CMS</a> ";
echo "<a href='../../login/login.php' class='btn btn-info'>Ir al Login SIGE</a>";
?>





<?php  
// admin/login/controller_login.php - VERSIÓN COMPLETA CORREGIDA
include('../app/config.php');  

// Iniciar sesión al principio del script para evitar errores
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar si ya está autenticado
if (isset($_SESSION['admin_login']) && $_SESSION['admin_login'] == true) {
    // Si ya está logueado como admin, redirigir al CMS
    header('Location:' . APP_URL . "/admin/admin/portal-cms/index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {  
    $email = trim($_POST['email']);  
    $password = $_POST['password'];  

    // Validar campos
    if (empty($email) || empty($password)) {
        $_SESSION['mensaje'] = "Email y contraseña son requeridos";  
        $_SESSION['icono'] = "error";
        header('Location:' . APP_URL . "/login/login.php");  
        exit;  
    }

    $sql = "SELECT * FROM usuarios WHERE email = :email";  
    $query = $pdo->prepare($sql);  
    $query->bindParam(':email', $email);  
    $query->execute();  
    $usuario = $query->fetch(PDO::FETCH_ASSOC);  

    if ($usuario) {  
        if ($usuario['estado'] == '0') {
            $_SESSION['mensaje'] = "El usuario está inactivo. Contacte al administrador.";  
            $_SESSION['icono'] = "warning";
            header('Location:' . APP_URL . "/login/login.php");  
            exit;  
        } else {
            if (password_verify($password, $usuario['password'])) {  
                // ====================================================
                // 🔧 LIMPIAR SESIONES ANTERIORES PARA EVITAR CONFLICTOS
                // ====================================================
                session_regenerate_id(true);
                
                // ====================================================
                // 🏢 VARIABLES DE SESIÓN PARA SIGE (TU SISTEMA ACTUAL)
                // ====================================================
                $_SESSION['usuario_id'] = $usuario['id_usuario']; 
                $_SESSION['rol_id'] = $usuario['rol_id'];
                $_SESSION['sesion_email'] = $email;
                $_SESSION['nombre_sesion'] = trim($usuario['nombres'] . ' ' . $usuario['apellidos']);
                
                // Obtener nombre del rol si existe tabla roles
                try {
                    $sql_rol = "SELECT rol_nombre FROM roles WHERE id_rol = :rol_id";
                    $query_rol = $pdo->prepare($sql_rol);
                    $query_rol->bindParam(':rol_id', $usuario['rol_id']);
                    $query_rol->execute();
                    $rol = $query_rol->fetch(PDO::FETCH_ASSOC);
                    
                    if ($rol && isset($rol['rol_nombre'])) {
                        $_SESSION['rol_nombre_sesion'] = $rol['rol_nombre'];
                        $_SESSION['rol_sesion_usuario'] = $rol['rol_nombre'];
                    } else {
                        $_SESSION['rol_nombre_sesion'] = 'Usuario';
                        $_SESSION['rol_sesion_usuario'] = 'Usuario';
                    }
                } catch (Exception $e) {
                    $_SESSION['rol_nombre_sesion'] = 'Usuario';
                    $_SESSION['rol_sesion_usuario'] = 'Usuario';
                }
                
                $_SESSION['nombres_sesion_usuario'] = $usuario['nombres'];
                $_SESSION['apellidos_sesion_usuario'] = $usuario['apellidos'];
                
                // ====================================================
                // 🚀 VARIABLES PARA CMS PORTAL (NUEVO SISTEMA)
                // ====================================================
                $_SESSION['admin_login'] = true;
                $_SESSION['usuario'] = trim($usuario['nombres'] . ' ' . $usuario['apellidos']); // Nombre completo
                $_SESSION['role'] = $usuario['rol_id'];
                $_SESSION['login_time'] = time();
                $_SESSION['portal_admin_logged_in'] = true;
                $_SESSION['portal_admin_user'] = trim($usuario['nombres'] . ' ' . $usuario['apellidos']);
                $_SESSION['login_id'] = $usuario['id_usuario'];
                $_SESSION['email_sesion'] = $email;
                
                // ====================================================
                // 📊 REGISTRO DE ACTIVIDAD (OPCIONAL)
                // ====================================================
                try {
                    $fecha_ingreso = date('Y-m-d H:i:s');
                    $ip = $_SERVER['REMOTE_ADDR'];
                    $sql_audit = "INSERT INTO auditoria_login (usuario_id, email, fecha_ingreso, ip_address) 
                                 VALUES (:usuario_id, :email, :fecha_ingreso, :ip)";
                    $query_audit = $pdo->prepare($sql_audit);
                    $query_audit->bindParam(':usuario_id', $usuario['id_usuario']);
                    $query_audit->bindParam(':email', $email);
                    $query_audit->bindParam(':fecha_ingreso', $fecha_ingreso);
                    $query_audit->bindParam(':ip', $ip);
                    $query_audit->execute();
                } catch (Exception $e) {
                    // Silenciar error de auditoría, no es crítico
                }
                
                // ====================================================
                // 👨‍🏫 DETECCIÓN AUTOMÁTICA DE DOCENTES (rol_id = 5)
                // ====================================================
                if ($usuario['rol_id'] == 5) { 
                    try {
                        $sql_profesor = "SELECT p.* FROM profesores p WHERE p.email = ? AND p.estado = 1";
                        $query_profesor = $pdo->prepare($sql_profesor);
                        $query_profesor->execute([$email]);
                        $profesor = $query_profesor->fetch(PDO::FETCH_ASSOC);
                        
                        if ($profesor) {
                            $_SESSION['id_profesor'] = $profesor['id_profesor'];
                            $_SESSION['nombre_profesor'] = trim($profesor['nombres'] . ' ' . $profesor['apellidos']);
                            $_SESSION['es_docente'] = true;
                            $_SESSION['especialidad'] = $profesor['especialidad'] ?? 'No especificada';
                            
                            $_SESSION['mensaje'] = "👨‍🏫 Bienvenido Prof. " . $profesor['nombres'];  
                            $_SESSION['icono'] = "success";  
                            
                            // Redirigir al módulo de notas SIGE
                            header('Location:' . APP_URL . "/admin/notas/carga_notas_seccion.php");
                            exit;  
                        } else {
                            // Docente sin perfil de profesor
                            $_SESSION['es_docente'] = true;
                            $_SESSION['mensaje'] = "Usuario docente encontrado, pero falta información en el perfil. Contacte al administrador.";  
                            $_SESSION['icono'] = "warning";
                            header('Location:' . APP_URL . "/admin/index.php?page=home");
                            exit;
                        }
                    } catch (Exception $e) {
                        // Error al buscar profesor, continuar con flujo normal
                        $_SESSION['es_docente'] = true;
                    }
                }
                
                // ====================================================
                // 🎯 REDIRECCIÓN INTELIGENTE SEGÚN ROL
                // ====================================================
                $_SESSION['mensaje'] = "✅ Bienvenido/a " . $_SESSION['usuario'] . " al sistema " . APP_NAME;  
                $_SESSION['icono'] = "success";
                $_SESSION['titulo'] = "Inicio de sesión exitoso";
                
                // PARA ADMINISTRADORES (rol_id = 1): CMS Portal
                if ($usuario['rol_id'] == 1) { 
                    // Redirigir al CMS Portal - RUTA CORRECTA: admin/admin/portal-cms/
                    header('Location:' . APP_URL . "/admin/portal-cms/index.php");
                    exit;
                } 
                // PARA DOCENTES: Ya redirigió arriba
                // PARA OTROS ROLES: Admin SIGE por defecto
                else {
                    header('Location:' . APP_URL . "/admin/index.php?page=home");
                    exit;
                }
                
            } else {  
                $_SESSION['mensaje'] = "❌ Contraseña incorrecta";  
                $_SESSION['icono'] = "error";  
                $_SESSION['titulo'] = "Error de autenticación";
                header('Location:' . APP_URL . "/login/login.php");  
                exit;  
            }  
        }
    } else {  
        $_SESSION['mensaje'] = "❌ Usuario no encontrado";  
        $_SESSION['icono'] = "error";  
        $_SESSION['titulo'] = "Error de autenticación";
        header('Location:' . APP_URL . "/login/login.php");  
        exit;  
    }  
} else {  
    // Si no es POST, redirigir al login
    header('Location:' . APP_URL . "/login/login.php");  
    exit;  
}  
?>