<?php
require_once('../../app/config.php');

// Verificar permisos de administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'Administrador') {
    die("Acceso denegado");
}

$mensaje = '';
$tipo_mensaje = '';

// Procesar subida de logo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['logo'])) {
    $archivo = $_FILES['logo'];
    
    if ($archivo['error'] === UPLOAD_ERR_OK) {
        // Validar tipo de archivo
        $tipo_permitido = ['image/png', 'image/jpeg', 'image/jpg'];
        $extension_permitida = ['png', 'jpg', 'jpeg'];
        
        $tipo_archivo = $archivo['type'];
        $nombre_archivo = $archivo['name'];
        $extension = strtolower(pathinfo($nombre_archivo, PATHINFO_EXTENSION));
        
        if (in_array($tipo_archivo, $tipo_permitido) && in_array($extension, $extension_permitida)) {
            // Directorio de destino
            $directorio_logo = __DIR__ . '/../../login/logos/';
            
            // Crear directorio si no existe
            if (!is_dir($directorio_logo)) {
                mkdir($directorio_logo, 0755, true);
            }
            
            // Nombre del archivo (siempre logo.png)
            $nombre_destino = 'logo.png';
            $ruta_destino = $directorio_logo . $nombre_destino;
            
            // Si es JPEG, convertir a PNG
            if ($extension === 'jpg' || $extension === 'jpeg') {
                $imagen = imagecreatefromjpeg($archivo['tmp_name']);
                if ($imagen) {
                    imagepng($imagen, $ruta_destino);
                    imagedestroy($imagen);
                    $mensaje = 'Logo subido exitosamente y convertido a PNG.';
                    $tipo_mensaje = 'success';
                } else {
                    $mensaje = 'Error al procesar la imagen JPEG.';
                    $tipo_mensaje = 'error';
                }
            } else {
                // Mover el archivo PNG
                if (move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
                    $mensaje = 'Logo subido exitosamente.';
                    $tipo_mensaje = 'success';
                } else {
                    $mensaje = 'Error al mover el archivo.';
                    $tipo_mensaje = 'error';
                }
            }
        } else {
            $mensaje = 'Formato de archivo no válido. Solo se permiten PNG, JPG y JPEG.';
            $tipo_mensaje = 'error';
        }
    } else {
        $mensaje = 'Error al subir el archivo.';
        $tipo_mensaje = 'error';
    }
}

// Verificar si existe el logo
$ruta_logo = __DIR__ . '/../../login/logos/logo.png';
$logo_existe = file_exists($ruta_logo);
$logo_valido = false;
$tamano_logo = 0;

if ($logo_existe) {
    $tamano_logo = filesize($ruta_logo);
    // Verificar si es un puntero LFS
    if ($tamano_logo < 500) {
        $contenido = @file_get_contents($ruta_logo, false, null, 0, 200);
        if ($contenido && stripos($contenido, 'git-lfs') !== false) {
            $logo_valido = false;
            $mensaje_lfs = 'El archivo logo.png existe pero es un puntero de Git LFS. Necesitas subir el archivo real.';
        } else {
            $logo_valido = true;
        }
    } else {
        // Verificar que sea una imagen válida
        $handle = @fopen($ruta_logo, 'rb');
        if ($handle) {
            $header = @fread($handle, 8);
            @fclose($handle);
            if ($header && (substr($header, 0, 4) === "\x89\x50\x4E\x47" || substr($header, 0, 3) === "\xFF\xD8\xFF")) {
                $logo_valido = true;
            }
        }
    }
} else {
    $mensaje_lfs = 'El archivo logo.png no existe. Por favor, súbelo.';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subir Logo para PDF</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }
        .mensaje {
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .mensaje.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .mensaje.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .mensaje.warning {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        .info {
            background-color: #e7f3ff;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .info strong {
            display: block;
            margin-bottom: 5px;
        }
        form {
            margin: 30px 0;
        }
        .form-group {
            margin: 20px 0;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        input[type="file"] {
            width: 100%;
            padding: 10px;
            border: 2px dashed #007bff;
            border-radius: 5px;
            background-color: #f8f9fa;
        }
        button {
            background-color: #007bff;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #0056b3;
        }
        .logo-preview {
            margin: 20px 0;
            text-align: center;
        }
        .logo-preview img {
            max-width: 200px;
            max-height: 200px;
            border: 2px solid #ddd;
            border-radius: 5px;
            padding: 10px;
        }
        .btn-back {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .btn-back:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📷 Subir Logo para PDF</h1>
        
        <?php if ($mensaje): ?>
            <div class="mensaje <?php echo $tipo_mensaje; ?>">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php endif; ?>
        
        <div class="info">
            <strong>📋 Estado del Logo:</strong>
            <?php if ($logo_existe): ?>
                <?php if ($logo_valido): ?>
                    <span style="color: green;">✓ Logo válido encontrado</span><br>
                    <span>Tamaño: <?php echo number_format($tamano_logo); ?> bytes</span>
                <?php else: ?>
                    <span style="color: orange;">⚠ <?php echo $mensaje_lfs; ?></span>
                <?php endif; ?>
            <?php else: ?>
                <span style="color: red;">✗ Logo no encontrado</span><br>
                <span><?php echo $mensaje_lfs; ?></span>
            <?php endif; ?>
        </div>
        
        <?php if ($logo_existe && $logo_valido): ?>
            <div class="logo-preview">
                <strong>Vista previa del logo actual:</strong><br><br>
                <img src="../../login/logos/logo.png?v=<?php echo time(); ?>" alt="Logo">
            </div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="logo">Seleccionar archivo de logo (PNG, JPG o JPEG):</label>
                <input type="file" id="logo" name="logo" accept="image/png,image/jpeg,image/jpg" required>
                <small style="color: #666;">Tamaño recomendado: mínimo 200x200 píxeles. Se recomienda formato PNG con fondo transparente.</small>
            </div>
            
            <button type="submit">📤 Subir Logo</button>
        </form>
        
        <a href="horarios_consolidados.php" class="btn-back">← Volver a Horarios Consolidados</a>
    </div>
</body>
</html>

