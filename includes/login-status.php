<?php
// heldyn/centeno/includes/login-status.php
session_start();

function getLoginButton() {
    $html = '';
    
    if (isset($_SESSION['admin_login']) && $_SESSION['admin_login'] == true) {
        // Usuario logueado en CMS Portal
        $nombre = isset($_SESSION['usuario']) ? $_SESSION['usuario'] : 'Administrador';
        $html = '
        <div class="dropdown">
            <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="fas fa-user me-2"></i>' . htmlspecialchars($nombre) . '
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="admin/admin/portal-cms/index.php">
                    <i class="fas fa-cogs"></i> Panel de Control
                </a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="admin/admin/portal-cms/logout.php">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión CMS
                </a></li>
            </ul>
        </div>';
    } elseif (isset($_SESSION['usuario_id'])) {
        // Usuario logueado en SIGE
        $nombre = isset($_SESSION['nombre_sesion']) ? $_SESSION['nombre_sesion'] : 'Usuario';
        $html = '
        <div class="dropdown">
            <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="fas fa-user me-2"></i>' . htmlspecialchars($nombre) . '
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="admin/index.php?page=home">
                    <i class="fas fa-tachometer-alt"></i> Dashboard SIGE
                </a></li>
                <li><a class="dropdown-item" href="admin/admin/portal-cms/index.php">
                    <i class="fas fa-cogs"></i> CMS Portal
                </a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="admin/logout.php">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </a></li>
            </ul>
        </div>';
    } else {
        // No está logueado
        $html = '
        <a href="admin/login/login.php" class="btn btn-primary">
            <i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión
        </a>';
    }
    
    return $html;
}
?>