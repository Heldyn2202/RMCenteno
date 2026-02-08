<?php
// portal-cms/noticias/crear_directorio.php
session_start();
require_once __DIR__ . '/../session-compat.php';

echo "<h2>Creando directorio de uploads...</h2>";

// Ruta del directorio donde se guardarán las imágenes
$directorio_destino = '../../../admin/uploads/post/';

// Crear directorio si no existe
if (!is_dir($directorio_destino)) {
    if (mkdir($directorio_destino, 0755, true)) {
        echo "<p style='color: green;'>✅ Directorio creado: $directorio_destino</p>";
        
        // Crear archivo .htaccess para seguridad
        $htaccess_content = <<<HTACCESS
# Denegar acceso a todos los archivos en este directorio
Order Deny,Allow
Deny from all

# Permitir acceso solo a imágenes
<FilesMatch "\.(jpg|jpeg|png|gif|webp)$">
    Order Allow,Deny
    Allow from all
</FilesMatch>

# Configuración adicional de seguridad
Options -Indexes
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
</IfModule>
HTACCESS;
        
        file_put_contents($directorio_destino . '.htaccess', $htaccess_content);
        echo "<p style='color: green;'>✅ Archivo .htaccess creado para seguridad</p>";
    } else {
        echo "<p style='color: red;'>❌ Error al crear el directorio: $directorio_destino</p>";
        echo "<p>Verifica los permisos de escritura en la carpeta admin/</p>";
    }
} else {
    echo "<p style='color: orange;'>⚠️ El directorio ya existe: $directorio_destino</p>";
}

// Verificar permisos
echo "<h3>Verificando permisos:</h3>";
echo "<p>Ruta absoluta: " . realpath($directorio_destino) . "</p>";
echo "<p>Permisos: " . substr(sprintf('%o', fileperms($directorio_destino)), -4) . "</p>";

// Verificar si PHP puede escribir
$archivo_test = $directorio_destino . 'test_write.txt';
if (file_put_contents($archivo_test, 'Test de escritura')) {
    unlink($archivo_test);
    echo "<p style='color: green;'>✅ PHP puede escribir en el directorio</p>";
} else {
    echo "<p style='color: red;'>❌ PHP NO puede escribir en el directorio</p>";
    echo "<p>Solución: Cambia los permisos de la carpeta a 755 o 777 temporalmente</p>";
}

echo "<hr>";
echo "<p><a href='index.php'>Volver al listado de noticias</a></p>";
echo "<p><strong>Nota:</strong> Elimina este archivo después de usarlo por seguridad</p>";
?>