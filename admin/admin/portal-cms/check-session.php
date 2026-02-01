<?php
// admin/admin/portal-cms/check-session.php
session_start();

echo "<h2>🔍 Verificador de Sesión CMS</h2>";

// Verificar todas las variables de sesión posibles
$variables_sige = [
    'usuario_id' => 'ID Usuario SIGE',
    'rol_id' => 'Rol ID',
    'sesion_email' => 'Email',
    'nombre_sesion' => 'Nombre SIGE'
];

$variables_cms = [
    'admin_login' => 'Login CMS',
    'usuario' => 'Nombre CMS',
    'portal_admin_logged_in' => 'Portal Admin',
    'portal_admin_user' => 'Usuario Portal'
];

echo "<h3>Variables SIGE:</h3>";
foreach ($variables_sige as $var => $desc) {
    echo "<p><strong>$desc:</strong> ";
    if (isset($_SESSION[$var])) {
        echo "<span style='color:green'>" . htmlspecialchars(print_r($_SESSION[$var], true)) . "</span>";
    } else {
        echo "<span style='color:red'>NO EXISTE</span>";
    }
    echo "</p>";
}

echo "<h3>Variables CMS:</h3>";
foreach ($variables_cms as $var => $desc) {
    echo "<p><strong>$desc:</strong> ";
    if (isset($_SESSION[$var])) {
        echo "<span style='color:green'>" . htmlspecialchars(print_r($_SESSION[$var], true)) . "</span>";
    } else {
        echo "<span style='color:red'>NO EXISTE</span>";
    }
    echo "</p>";
}

// Verificación de acceso
$tiene_acceso_cms = isset($_SESSION['admin_login']) && $_SESSION['admin_login'] == true;
$tiene_acceso_sige = isset($_SESSION['usuario_id']);

echo "<h3>Estado de acceso:</h3>";
echo "<p><strong>Acceso CMS:</strong> " . ($tiene_acceso_cms ? "✅ PERMITIDO" : "❌ DENEGADO") . "</p>";
echo "<p><strong>Acceso SIGE:</strong> " . ($tiene_acceso_sige ? "✅ PERMITIDO" : "❌ DENEGADO") . "</p>";

if ($tiene_acceso_cms) {
    echo "<div class='alert alert-success'>✅ Puedes acceder al CMS Portal</div>";
    echo "<a href='index.php' class='btn btn-success'>Ir al Dashboard CMS</a>";
} else {
    echo "<div class='alert alert-danger'>❌ No puedes acceder al CMS Portal</div>";
    echo "<a href='session-fix.php' class='btn btn-warning'>Reparar Sesión</a> ";
    echo "<a href='../../login/login.php' class='btn btn-primary'>Ir al Login</a>";
}
?>