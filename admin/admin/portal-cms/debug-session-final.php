<?php
session_start();
echo "<h2>🔍 DIAGNÓSTICO FINAL DE SESIÓN</h2>";

echo "<h3>Estado actual:</h3>";
echo "<p><strong>admin_login:</strong> " . (isset($_SESSION['admin_login']) ? '✅ TRUE' : '❌ FALSE') . "</p>";
echo "<p><strong>usuario_id:</strong> " . (isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : 'NO') . "</p>";
echo "<p><strong>Nombre:</strong> " . (isset($_SESSION['usuario']) ? $_SESSION['usuario'] : 'NO') . "</p>";

echo "<h3>Todas las variables:</h3>";
echo "<pre>";
if (empty($_SESSION)) {
    echo "SESIÓN VACÍA";
} else {
    foreach ($_SESSION as $key => $value) {
        echo "<strong>" . htmlspecialchars($key) . ":</strong> ";
        if (is_bool($value)) {
            echo $value ? 'TRUE' : 'FALSE';
        } elseif (empty($value)) {
            echo "(VACÍO)";
        } else {
            echo htmlspecialchars($value);
        }
        echo "\n";
    }
}
echo "</pre>";

echo "<h3>Acciones de prueba:</h3>";
echo '<div style="display: flex; gap: 10px; flex-wrap: wrap;">';
echo '<a href="debug-session-final.php?action=set_cms" class="btn btn-success">Forzar Sesión CMS</a>';
echo '<a href="debug-session-final.php?action=clear_all" class="btn btn-danger">Limpiar TODAS las sesiones</a>';
echo '<a href="index.php" class="btn btn-primary">Ir al Dashboard</a>';
echo '<a href="logout.php" class="btn btn-warning">Logout Normal</a>';
echo '<a href="../../login/login.php" class="btn btn-info">Ir al Login SIGE</a>';
echo '</div>';

// Acciones de debug
if (isset($_GET['action'])) {
    if ($_GET['action'] == 'set_cms') {
        $_SESSION['admin_login'] = true;
        $_SESSION['usuario'] = 'Usuario Debug';
        $_SESSION['usuario_id'] = 999;
        $_SESSION['sesion_email'] = 'debug@test.com';
        $_SESSION['portal_admin_logged_in'] = true;
        $_SESSION['portal_admin_user'] = 'Usuario Debug';
        
        echo '<div class="alert alert-success mt-3">✅ Sesión CMS forzada</div>';
        header('Refresh: 2; url=index.php');
    } elseif ($_GET['action'] == 'clear_all') {
        session_unset();
        session_destroy();
        session_start();
        
        echo '<div class="alert alert-info mt-3">✅ Todas las sesiones limpiadas</div>';
        header('Refresh: 2; url=../../login/login.php');
    }
}
?>