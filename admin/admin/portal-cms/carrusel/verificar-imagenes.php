<?php
// admin/admin/portal-cms/carrusel/verificar-imagenes.php

// ================= CONEXIÓN MANUAL =================
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sige";

$con = new mysqli($servername, $username, $password, $dbname);
if ($con->connect_error) {
    die("Error de conexión: " . $con->connect_error);
}
// ====================================================

session_start();

// Verificar login temporal
if (!isset($_SESSION['portal_admin_logged_in'])) {
    header('Location: index.php');
    exit;
}

// Obtener todos los slides
$query = "SELECT * FROM carrusel ORDER BY fecha_creacion DESC";
$result = mysqli_query($con, $query);

// Verificar permisos de carpeta
$directorio_uploads = '../../../../uploads/carrusel/';
$directorio_existe = file_exists($directorio_uploads);
$directorio_escribible = is_writable($directorio_uploads);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificar Imágenes - Carrusel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .table td, .table th { vertical-align: middle; }
        .existe-si { background-color: #d4edda; }
        .existe-no { background-color: #f8d7da; }
        .permiso-ok { color: #28a745; }
        .permiso-error { color: #dc3545; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1 class="mb-4">
            <i class="fas fa-search"></i> Verificador de Imágenes del Carrusel
        </h1>
        
        <!-- Información del sistema -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Información del Sistema</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Ruta absoluta del sitio:</strong><br>
                        <code><?php echo $_SERVER['DOCUMENT_ROOT']; ?></code></p>
                        
                        <p><strong>Directorio de imágenes:</strong><br>
                        <code><?php echo realpath($directorio_uploads) ?: $directorio_uploads; ?></code></p>
                        
                        <p><strong>Directorio existe:</strong>
                        <?php if ($directorio_existe): ?>
                            <span class="badge bg-success">SÍ</span>
                        <?php else: ?>
                            <span class="badge bg-danger">NO</span>
                        <?php endif; ?>
                        </p>
                        
                        <p><strong>Permisos de escritura:</strong>
                        <?php if ($directorio_escribible): ?>
                            <span class="badge bg-success">SÍ</span>
                        <?php else: ?>
                            <span class="badge bg-danger">NO</span>
                        <?php endif; ?>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>URL base para imágenes:</strong><br>
                        <code>/heldyn/centeno/uploads/carrusel/</code></p>
                        
                        <p><strong>Ejemplo de URL completa:</strong><br>
                        <code>http://<?php echo $_SERVER['HTTP_HOST']; ?>/heldyn/centeno/uploads/carrusel/nombre_imagen.jpg</code></p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Tabla de verificación -->
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">Verificación de Slides</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Título</th>
                                <th>Archivo en BD</th>
                                <th>Existe en Servidor</th>
                                <th>Ruta Física</th>
                                <th>Tamaño</th>
                                <th>URL Accesible</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($slide = mysqli_fetch_assoc($result)): 
                                $ruta_fisica = $directorio_uploads . $slide['imagen_path'];
                                $existe = file_exists($ruta_fisica);
                                $tamano = $existe ? filesize($ruta_fisica) : 0;
                                $url = '/heldyn/centeno/uploads/carrusel/' . $slide['imagen_path'];
                                $url_completa = 'http://' . $_SERVER['HTTP_HOST'] . $url;
                            ?>
                            <tr class="<?php echo $existe ? 'existe-si' : 'existe-no'; ?>">
                                <td><?php echo $slide['id']; ?></td>
                                <td><?php echo htmlspecialchars($slide['titulo']); ?></td>
                                <td>
                                    <code><?php echo htmlspecialchars($slide['imagen_path']); ?></code>
                                </td>
                                <td>
                                    <?php if ($existe): ?>
                                        <span class="badge bg-success">SÍ</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">NO</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small><?php echo $ruta_fisica; ?></small>
                                </td>
                                <td>
                                    <?php if ($existe): ?>
                                        <?php echo round($tamano / 1024, 2); ?> KB
                                    <?php else: ?>
                                        <span class="text-muted">--</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($existe): ?>
                                        <a href="<?php echo $url_completa; ?>" 
                                           target="_blank" 
                                           class="btn btn-sm btn-primary"
                                           onclick="testImage('<?php echo $url_completa; ?>', event)">
                                            <i class="fas fa-external-link-alt"></i> Probar
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">No disponible</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge <?php echo $slide['activo'] ? 'bg-success' : 'bg-secondary'; ?>">
                                        <?php echo $slide['activo'] ? 'Activo' : 'Inactivo'; ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Soluciones -->
        <div class="card mt-4">
            <div class="card-header bg-warning">
                <h5 class="mb-0">Soluciones y Recomendaciones</h5>
            </div>
            <div class="card-body">
                <h6>Si las imágenes NO existen en el servidor:</h6>
                <ol>
                    <li><strong>Crear el directorio:</strong>
                        <code>mkdir -p <?php echo $directorio_uploads; ?></code>
                    </li>
                    <li><strong>Establecer permisos:</strong>
                        <code>chmod 755 <?php echo $directorio_uploads; ?></code> o
                        <code>chmod 777 <?php echo $directorio_uploads; ?></code> (para pruebas)
                    </li>
                    <li><strong>Cambiar propietario (Linux):</strong>
                        <code>chown www-data:www-data <?php echo $directorio_uploads; ?></code>
                    </li>
                </ol>
                
                <h6>Si las imágenes existen pero no se muestran:</h6>
                <ol>
                    <li>Verifica que la URL en el portal principal sea:
                        <code>/heldyn/centeno/uploads/carrusel/nombre_imagen.jpg</code>
                    </li>
                    <li>Verifica que el archivo .htaccess no esté bloqueando el acceso</li>
                    <li>Prueba acceder directamente a la URL desde el navegador</li>
                </ol>
                
                <div class="alert alert-info mt-3">
                    <h6><i class="fas fa-lightbulb"></i> Para probar en el portal:</h6>
                    <p>Abre la consola del navegador (F12) y pega este código:</p>
                    <pre class="bg-dark text-white p-2 rounded">
// Verificar rutas de imágenes
document.querySelectorAll('img').forEach(img => {
    if (img.src.includes('carrusel')) {
        console.log('Imagen:', img.src, ' - Cargada:', img.complete);
        if (!img.complete) {
            console.error('Error cargando:', img.src);
        }
    }
});</pre>
                </div>
                
                <div class="text-center mt-4">
                    <a href="index.php" class="btn btn-primary">
                        <i class="fas fa-arrow-left"></i> Volver al Carrusel
                    </a>
                    <button onclick="location.reload()" class="btn btn-secondary">
                        <i class="fas fa-sync-alt"></i> Actualizar Verificación
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function testImage(url, event) {
            event.preventDefault();
            
            // Mostrar carga
            const btn = event.target.closest('button');
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Probando...';
            btn.disabled = true;
            
            // Crear imagen de prueba
            const img = new Image();
            img.onload = function() {
                btn.innerHTML = '<i class="fas fa-check"></i> OK';
                btn.className = 'btn btn-sm btn-success';
                
                // Mostrar mensaje
                alert('✅ La imagen se carga correctamente desde:\n' + url);
                
                // Restaurar después de 2 segundos
                setTimeout(() => {
                    btn.innerHTML = originalHtml;
                    btn.className = 'btn btn-sm btn-primary';
                    btn.disabled = false;
                }, 2000);
            };
            
            img.onerror = function() {
                btn.innerHTML = '<i class="fas fa-times"></i> Error';
                btn.className = 'btn btn-sm btn-danger';
                
                // Mostrar error
                alert('❌ No se puede cargar la imagen desde:\n' + url + 
                      '\n\nPosibles causas:\n' +
                      '1. La ruta es incorrecta\n' +
                      '2. El archivo no existe\n' +
                      '3. Permisos insuficientes\n' +
                      '4. Configuración del servidor');
                
                // Restaurar después de 2 segundos
                setTimeout(() => {
                    btn.innerHTML = originalHtml;
                    btn.className = 'btn btn-sm btn-primary';
                    btn.disabled = false;
                }, 2000);
            };
            
            img.src = url;
        }
    </script>
</body>
</html>