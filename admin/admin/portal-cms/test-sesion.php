<?php
session_start();
echo "<h2>🔍 Diagnóstico de Sesión para CMS Portal</h2>";
echo "<h4>Ruta: " . __DIR__ . "</h4>";

echo "<h3>Variables de Sesión:</h3>";
echo "<pre>";
if (empty($_SESSION)) {
    echo "SESIÓN VACÍA";
} else {
    print_r($_SESSION);
}
echo "</pre>";

echo "<h3>Cookie de Sesión:</h3>";
echo "<pre>";
print_r(session_get_cookie_params());
echo "</pre>";

echo "<h3>Opciones:</h3>";
echo "<a href='test-sesion.php?force=1' class='btn btn-primary'>Forzar Sesión CMS</a> ";
echo "<a href='index.php' class='btn btn-success'>Ir al Dashboard</a> ";
echo "<a href='../../login/login.php' class='btn btn-info'>Ir al Login</a> ";
echo "<a href='logout.php' class='btn btn-danger'>Cerrar Sesión</a>";

// Forzar sesión para testing
if (isset($_GET['force'])) {
    $_SESSION['admin_login'] = true;
    $_SESSION['usuario'] = 'Usuario de Prueba';
    $_SESSION['usuario_id'] = 999;
    $_SESSION['portal_admin_logged_in'] = true;
    $_SESSION['portal_admin_user'] = 'Usuario de Prueba';
    $_SESSION['sesion_email'] = 'test@example.com';
    $_SESSION['rol_id'] = 1;
    
    echo "<div class='alert alert-success mt-3'>✅ Sesión forzada exitosamente</div>";
    echo "<script>setTimeout(() => { window.location.href = 'index.php'; }, 1500);</script>";
}
?>