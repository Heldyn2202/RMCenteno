<?php
// app/auth.php

function authMiddleware($rolesPermitidos = []) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Verificar si el usuario está logueado
    if (!isset($_SESSION['sesion_email'])) {
        while (ob_get_level()) ob_end_clean();
        header('Location: ' . APP_URL . '/login');
        exit();
    }
    
    // Verificar roles permitidos (si se especificaron)
    if (!empty($rolesPermitidos)) {
        $rolUsuario = $_SESSION['rol_sesion_usuario'] ?? '';
        $accesoPermitido = false;
        
        // Convertir todo a mayúsculas para comparación insensible a mayúsculas/minúsculas
        $rolUsuarioUpper = strtoupper(trim($rolUsuario));
        $rolesPermitidosUpper = array_map(function($rol) {
            return strtoupper(trim($rol));
        }, $rolesPermitidos);
        
        if (!in_array($rolUsuarioUpper, $rolesPermitidosUpper)) {
            while (ob_get_level()) ob_end_clean();
            header('HTTP/1.0 403 Forbidden');
            echo "No tienes permiso para acceder a esta página. Rol requerido: " . implode(", ", $rolesPermitidos);
            exit();
        }
    }
    
    return true;
}