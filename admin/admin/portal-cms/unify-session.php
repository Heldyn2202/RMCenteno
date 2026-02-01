<?php
// admin/admin/portal-cms/unify-session.php
// Para sincronizar sesiones entre SIGE y CMS Portal

session_start();

function unifySessions() {
    // Si existe sesión SIGE, crear sesión CMS
    if (isset($_SESSION['usuario_id']) && !isset($_SESSION['admin_login'])) {
        $_SESSION['admin_login'] = true;
        $_SESSION['portal_admin_logged_in'] = true;
        
        // Obtener nombre para CMS
        if (isset($_SESSION['nombre_sesion'])) {
            $_SESSION['usuario'] = $_SESSION['nombre_sesion'];
            $_SESSION['portal_admin_user'] = $_SESSION['nombre_sesion'];
        } elseif (isset($_SESSION['sesion_email'])) {
            $_SESSION['usuario'] = $_SESSION['sesion_email'];
            $_SESSION['portal_admin_user'] = $_SESSION['sesion_email'];
        }
    }
    
    // Si existe sesión CMS, crear sesión SIGE básica
    if (isset($_SESSION['admin_login']) && !isset($_SESSION['usuario_id'])) {
        $_SESSION['usuario_id'] = 1; // Valor temporal
        if (isset($_SESSION['sesion_email'])) {
            $_SESSION['sesion_email'] = $_SESSION['usuario'];
        }
    }
}

// Ejecutar unificación
unifySessions();

// Redirigir al dashboard
header('Location: index.php');
exit;
?>