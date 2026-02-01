<?php
// admin/admin/portal-cms/quienes-somos/guardar_info.php

session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sige";

$con = new mysqli($servername, $username, $password, $dbname);
if ($con->connect_error) {
    die("Error de conexión: " . $con->connect_error);
}

// Verificar login
if (!isset($_SESSION['portal_admin_logged_in'])) {
    header('Location: ../index.php');
    exit;
}

// Procesar datos del formulario
$titulo = mysqli_real_escape_string($con, $_POST['titulo'] ?? '');
$contenido = mysqli_real_escape_string($con, $_POST['contenido'] ?? '');
$mision = mysqli_real_escape_string($con, $_POST['mision'] ?? '');
$vision = mysqli_real_escape_string($con, $_POST['vision'] ?? '');
$valores = mysqli_real_escape_string($con, $_POST['valores'] ?? '');
$imagen_principal_alt = mysqli_real_escape_string($con, $_POST['imagen_principal_alt'] ?? '');

// Ruta absoluta para guardar imágenes
$base_path = $_SERVER['DOCUMENT_ROOT'] . '/heldyn/centeno/';
$upload_dir = $base_path . 'uploads/quienes-somos/';

// Crear directorio si no existe
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Manejar la imagen
$imagen_principal = null;
$hasImageError = false;
$imageErrorMessage = '';

if (isset($_FILES['imagen_principal']) && $_FILES['imagen_principal']['error'] === UPLOAD_ERR_OK) {
    $file_name = $_FILES['imagen_principal']['name'];
    $file_tmp = $_FILES['imagen_principal']['tmp_name'];
    $file_size = $_FILES['imagen_principal']['size'];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    if (in_array($file_ext, $allowed_extensions)) {
        if ($file_size <= $max_size) {
            // Generar nombre único
            $new_file_name = uniqid() . '.' . $file_ext;
            $full_path = $upload_dir . $new_file_name;
            
            // Mover archivo
            if (move_uploaded_file($file_tmp, $full_path)) {
                $imagen_principal = $new_file_name;
                
                // Eliminar imagen anterior si existe
                $query_old = "SELECT imagen_principal FROM quienes_somos WHERE id = 1";
                $result_old = mysqli_query($con, $query_old);
                if ($old_img = mysqli_fetch_assoc($result_old)) {
                    if (!empty($old_img['imagen_principal'])) {
                        $old_file_path = $upload_dir . $old_img['imagen_principal'];
                        if (file_exists($old_file_path)) {
                            unlink($old_file_path);
                        }
                    }
                }
            } else {
                $hasImageError = true;
                $imageErrorMessage = 'Error al subir la imagen. Verifique permisos del servidor.';
            }
        } else {
            $hasImageError = true;
            $imageErrorMessage = 'La imagen es demasiado grande (máx. 5MB). Tamaño actual: ' . round($file_size / 1024 / 1024, 2) . ' MB';
        }
    } else {
        $hasImageError = true;
        $imageErrorMessage = 'Formato de imagen no válido. Use JPG, PNG, GIF o WEBP. Formato detectado: .' . $file_ext;
    }
} elseif (isset($_FILES['imagen_principal']) && $_FILES['imagen_principal']['error'] !== UPLOAD_ERR_NO_FILE) {
    $hasImageError = true;
    $imageErrorMessage = 'Error en la subida de la imagen. Código: ' . $_FILES['imagen_principal']['error'];
}

// Si hay error en la imagen, redirigir con mensaje
if ($hasImageError) {
    $_SESSION['mensaje'] = '❌ ' . $imageErrorMessage;
    $_SESSION['mensaje_tipo'] = 'error';
    header('Location: index.php#info');
    exit;
}

// Preparar la consulta SQL
if ($imagen_principal) {
    $query = "UPDATE quienes_somos SET 
              titulo = '$titulo',
              contenido = '$contenido',
              mision = '$mision',
              vision = '$vision',
              valores = '$valores',
              imagen_principal = '$imagen_principal',
              imagen_principal_alt = '$imagen_principal_alt',
              fecha_actualizacion = NOW()
              WHERE id = 1";
} else {
    $query = "UPDATE quienes_somos SET 
              titulo = '$titulo',
              contenido = '$contenido',
              mision = '$mision',
              vision = '$vision',
              valores = '$valores',
              imagen_principal_alt = '$imagen_principal_alt',
              fecha_actualizacion = NOW()
              WHERE id = 1";
}

// Ejecutar la consulta
if (mysqli_query($con, $query)) {
    // Construir mensaje de éxito con emojis y HTML
    $successMessage = '<div class="text-start">';
    $successMessage .= '<div class="d-flex align-items-center mb-2">';
    $successMessage .= '<i class="fas fa-check-circle text-success me-2 fa-lg"></i>';
    $successMessage .= '<h5 class="mb-0 text-success">¡Información Guardada!</h5>';
    $successMessage .= '</div>';
    
    $successMessage .= '<div class="alert alert-success p-3">';
    $successMessage .= '<div class="d-flex">';
    $successMessage .= '<div class="flex-shrink-0">';
    $successMessage .= '<i class="fas fa-save fa-2x"></i>';
    $successMessage .= '</div>';
    $successMessage .= '<div class="flex-grow-1 ms-3">';
    $successMessage .= '<p class="mb-1"><strong>Título actualizado:</strong> ' . htmlspecialchars($titulo) . '</p>';
    
    if ($imagen_principal) {
        $successMessage .= '<p class="mb-0"><strong>Nueva imagen:</strong> ' . htmlspecialchars($imagen_principal) . '</p>';
    } else {
        $successMessage .= '<p class="mb-0"><i class="fas fa-info-circle me-1"></i>Imagen mantenida sin cambios</p>';
    }
    
    $successMessage .= '</div>';
    $successMessage .= '</div>';
    $successMessage .= '</div>';
    
    $successMessage .= '<div class="small text-muted">';
    $successMessage .= '<i class="fas fa-clock me-1"></i> Actualizado: ' . date('d/m/Y H:i:s');
    $successMessage .= '</div>';
    $successMessage .= '</div>';
    
    $_SESSION['mensaje'] = $successMessage;
    $_SESSION['mensaje_tipo'] = 'success';
    
} else {
    // Construir mensaje de error con emojis y HTML
    $errorMessage = '<div class="text-start">';
    $errorMessage .= '<div class="d-flex align-items-center mb-2">';
    $errorMessage .= '<i class="fas fa-exclamation-triangle text-danger me-2 fa-lg"></i>';
    $errorMessage .= '<h5 class="mb-0 text-danger">Error al Guardar</h5>';
    $errorMessage .= '</div>';
    
    $errorMessage .= '<div class="alert alert-danger p-3">';
    $errorMessage .= '<div class="d-flex">';
    $errorMessage .= '<div class="flex-shrink-0">';
    $errorMessage .= '<i class="fas fa-times-circle fa-2x"></i>';
    $errorMessage .= '</div>';
    $errorMessage .= '<div class="flex-grow-1 ms-3">';
    $errorMessage .= '<p class="mb-1"><strong>Error de base de datos:</strong></p>';
    $errorMessage .= '<p class="mb-0">' . htmlspecialchars(mysqli_error($con)) . '</p>';
    $errorMessage .= '</div>';
    $errorMessage .= '</div>';
    $errorMessage .= '</div>';
    
    $errorMessage .= '<div class="small text-muted">';
    $errorMessage .= '<i class="fas fa-lightbulb me-1"></i> Verifique la conexión a la base de datos';
    $errorMessage .= '</div>';
    $errorMessage .= '</div>';
    
    $_SESSION['mensaje'] = $errorMessage;
    $_SESSION['mensaje_tipo'] = 'error';
}

// Mantener en la pestaña de información principal
header('Location: index.php#info');
exit;
?>