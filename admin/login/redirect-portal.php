<?php
// admin/login/redirect-portal.php
// Script para redirigir inteligentemente según el estado de sesión
session_start();

$base_url = 'http://' . $_SERVER['HTTP_HOST'] . '/heldyn/centeno/';

// Verificar tipo de sesión
if (isset($_SESSION['admin_login']) && $_SESSION['admin_login'] == true) {
    // Sesión CMS Portal activa
    header('Location: ' . $base_url . 'admin/admin/portal-cms/index.php');
    exit;
} elseif (isset($_SESSION['usuario_id'])) {
    // Sesión SIGE activa
    header('Location: ' . $base_url . 'admin/index.php?page=home');
    exit;
} else {
    // No hay sesión, ir al login
    header('Location: ' . $base_url . 'admin/login/login.php');
    exit;
}
?>