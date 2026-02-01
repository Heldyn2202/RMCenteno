<?php
// admin/admin/portal-cms/session-compat.php
// VERSIÓN CORREGIDA - Sin error de session_start()

// Verificar si la sesión ya está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Verifica si el usuario está logueado en el CMS
 */
function verificarSesionCMS() {
    // Verificar específicamente admin_login
    if (isset($_SESSION['admin_login']) && $_SESSION['admin_login'] === true) {
        return true;
    }
    
    // Si tiene portal_admin_logged_in pero no admin_login, crearlo
    if (isset($_SESSION['portal_admin_logged_in']) && $_SESSION['portal_admin_logged_in'] === true) {
        $_SESSION['admin_login'] = true;
        return true;
    }
    
    // Si tiene usuario_id (de SIGE) y es administrador (rol_id = 1)
    if (isset($_SESSION['usuario_id']) && isset($_SESSION['rol_id']) && $_SESSION['rol_id'] == 1) {
        $_SESSION['admin_login'] = true;
        $_SESSION['portal_admin_logged_in'] = true;
        $_SESSION['usuario'] = isset($_SESSION['nombre_sesion']) && !empty($_SESSION['nombre_sesion']) 
            ? $_SESSION['nombre_sesion'] 
            : 'Administrador';
        $_SESSION['portal_admin_user'] = $_SESSION['usuario'];
        return true;
    }
    
    // Si tiene usuario_id (de SIGE) y es administrador de portal (rol específico para CMS)
    if (isset($_SESSION['usuario_id']) && isset($_SESSION['rol_nombre_sesion'])) {
        $roles_permitidos = ['Administrador', 'Super Administrador', 'Editor Portal'];
        if (in_array($_SESSION['rol_nombre_sesion'], $roles_permitidos)) {
            $_SESSION['admin_login'] = true;
            $_SESSION['portal_admin_logged_in'] = true;
            $_SESSION['usuario'] = isset($_SESSION['nombre_sesion']) ? $_SESSION['nombre_sesion'] : 'Administrador';
            $_SESSION['portal_admin_user'] = $_SESSION['usuario'];
            return true;
        }
    }
    
    return false;
}

/**
 * Obtiene el nombre del usuario para el CMS
 */
function obtenerNombreUsuario() {
    // Prioridad 1: Nombre del CMS
    if (isset($_SESSION['usuario']) && !empty($_SESSION['usuario']) && $_SESSION['usuario'] != 'admin@gmail.com') {
        return $_SESSION['usuario'];
    }
    
    // Prioridad 2: Nombre de SIGE
    if (isset($_SESSION['nombre_sesion']) && !empty($_SESSION['nombre_sesion'])) {
        return $_SESSION['nombre_sesion'];
    }
    
    // Prioridad 3: Portal admin user
    if (isset($_SESSION['portal_admin_user']) && !empty($_SESSION['portal_admin_user'])) {
        return $_SESSION['portal_admin_user'];
    }
    
    // Prioridad 4: Email
    if (isset($_SESSION['sesion_email']) && !empty($_SESSION['sesion_email'])) {
        return $_SESSION['sesion_email'];
    }
    
    return 'Administrador';
}

/**
 * Sincroniza sesiones entre sistemas
 */
function sincronizarSesiones() {
    // Si existe sesión SIGE pero no CMS, crear compatibilidad
    if (isset($_SESSION['usuario_id']) && !isset($_SESSION['admin_login'])) {
        // Solo dar acceso al CMS a administradores (rol_id = 1)
        if (isset($_SESSION['rol_id']) && $_SESSION['rol_id'] == 1) {
            $_SESSION['admin_login'] = true;
            $_SESSION['portal_admin_logged_in'] = true;
            $_SESSION['usuario'] = isset($_SESSION['nombre_sesion']) ? $_SESSION['nombre_sesion'] : 'Administrador';
            $_SESSION['portal_admin_user'] = $_SESSION['usuario'];
        }
    }
    
    // Asegurar que usuario no sea email
    if (isset($_SESSION['usuario']) && filter_var($_SESSION['usuario'], FILTER_VALIDATE_EMAIL)) {
        $_SESSION['usuario'] = 'Administrador';
        $_SESSION['portal_admin_user'] = 'Administrador';
    }
}

// Ejecutar sincronización automática
sincronizarSesiones();

// Función de debug (opcional)
function debugSesion() {
    if (isset($_GET['debug']) && $_GET['debug'] == 'session') {
        echo "<!-- DEBUG SESSION:\n";
        foreach ($_SESSION as $key => $value) {
            if (is_array($value)) {
                echo "  $key: array(" . count($value) . " elementos)\n";
            } else {
                echo "  $key: " . (is_bool($value) ? ($value ? 'true' : 'false') : htmlspecialchars($value)) . "\n";
            }
        }
        echo "  session_status: " . session_status() . "\n";
        echo "-->";
    }
}

// Llamar debug si se solicita
debugSesion();
?>