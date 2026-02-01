<?php
// admin/admin/portal-cms/logout.php
session_start();

// ============================================
// CONFIGURACIÓN DE RUTAS
// ============================================
$base_url = 'http://' . $_SERVER['HTTP_HOST'] . '/heldyn/centeno/';
$login_url = $base_url . 'admin/login/login.php';

// ============================================
// ELIMINAR TODAS LAS VARIABLES DE SESIÓN DEL CMS
// ============================================
$variables_eliminar = [
    // Variables del CMS Portal
    'admin_login', 
    'usuario', 
    'role', 
    'login_time',
    'portal_admin_logged_in', 
    'portal_admin_user',
    
    // Variables de sesión generales que puedan existir
    'login_id',
    'email_sesion',
    'nombre_sesion', 
    'rol_nombre_sesion',
    'rol_sesion_usuario',
    'nombres_sesion_usuario',
    'apellidos_sesion_usuario',
    
    // Mensajes
    'mensaje',
    'icono',
    'titulo'
];

foreach ($variables_eliminar as $variable) {
    if (isset($_SESSION[$variable])) {
        unset($_SESSION[$variable]);
    }
}

// IMPORTANTE: NO eliminar estas variables del sistema SIGE:
// - usuario_id, rol_id, sesion_email (se mantienen para SIGE)

// ============================================
// DESTRUIR Y REINICIAR SESIÓN LIMPIAMENTE
// ============================================
session_write_close(); // Cierra la sesión actual
session_start(); // Inicia nueva sesión

// Establecer mensaje de confirmación
$_SESSION['mensaje'] = "✅ Sesión cerrada correctamente del CMS Portal";
$_SESSION['icono'] = "success";
$_SESSION['titulo'] = "Sesión cerrada";

// ============================================
// REDIRECCIÓN AL LOGIN
// ============================================
header('Location: ' . $login_url);
exit;
?>