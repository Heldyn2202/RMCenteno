<?php
// admin/admin/portal-cms/session-fix.php
session_start();

echo "<h2>🔧 Reparador de Sesiones</h2>";

// Lista de variables que deben existir para el CMS
$variables_requeridas = [
    'admin_login' => true,
    'usuario' => 'Nombre del Usuario',
    'portal_admin_logged_in' => true,
    'portal_admin_user' => 'Nombre del Usuario'
];

// Verificar qué variables existen
echo "<h3>Estado actual:</h3>";
foreach ($variables_requeridas as $variable => $valor_esperado) {
    if (isset($_SESSION[$variable])) {
        echo "<p style='color:green'>✅ $variable: " . htmlspecialchars(print_r($_SESSION[$variable], true)) . "</p>";
    } else {
        echo "<p style='color:red'>❌ $variable: NO EXISTE</p>";
    }
}

// Botón para reparar
if (isset($_GET['fix'])) {
    // Obtener nombre del usuario de cualquier variable disponible
    $nombre_usuario = 'Administrador';
    if (isset($_SESSION['nombre_sesion']) && !empty($_SESSION['nombre_sesion'])) {
        $nombre_usuario = $_SESSION['nombre_sesion'];
    } elseif (isset($_SESSION['sesion_email'])) {
        $nombre_usuario = $_SESSION['sesion_email'];
    }
    
    // Establecer todas las variables requeridas
    $_SESSION['admin_login'] = true;
    $_SESSION['usuario'] = $nombre_usuario;
    $_SESSION['portal_admin_logged_in'] = true;
    $_SESSION['portal_admin_user'] = $nombre_usuario;
    $_SESSION['role'] = isset($_SESSION['rol_id']) ? $_SESSION['rol_id'] : 1;
    $_SESSION['login_time'] = time();
    
    echo "<div class='alert alert-success'>✅ Sesión reparada correctamente</div>";
    echo "<p>Redirigiendo al dashboard...</p>";
    echo "<script>setTimeout(() => { window.location.href = 'index.php'; }, 2000);</script>";
}

echo "<h3>Acciones:</h3>";
echo "<a href='session-fix.php?fix=1' class='btn btn-success'>Reparar Sesión CMS</a> ";
echo "<a href='index.php' class='btn btn-primary'>Ir al Dashboard</a> ";
echo "<a href='logout.php' class='btn btn-danger'>Logout</a>";
?>