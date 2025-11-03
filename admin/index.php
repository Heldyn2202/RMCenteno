<?php
// Redirigir al instalador si no existe el archivo de configuración
if (!file_exists('app/config.php')) {
    header('Location: install/');
    exit;
} else {
    header('Location: admin/');
    exit;
}
?>