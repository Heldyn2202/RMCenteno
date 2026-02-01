<?php
// admin/admin/portal-cms/carrusel/editar.php - VERSIÓN CON SWEETALERT2

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

// Verificar que se pasó un ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['mensaje_tipo'] = 'error';
    $_SESSION['mensaje'] = 'ID de slide no válido';
    header('Location: index.php');
    exit;
}

$id = intval($_GET['id']);

// Obtener datos del slide actual
$query = "SELECT * FROM carrusel WHERE id = $id";
$result = mysqli_query($con, $query);
$slide = mysqli_fetch_assoc($result);

if (!$slide) {
    $_SESSION['mensaje_tipo'] = 'error';
    $_SESSION['mensaje'] = 'Slide no encontrado';
    header('Location: index.php');
    exit;
}

$error = '';
$slide_data = $slide; // Datos actuales para el formulario

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recibir y validar datos
    $titulo = mysqli_real_escape_string($con, $_POST['titulo']);
    $descripcion = mysqli_real_escape_string($con, $_POST['descripcion']);
    $fecha_inicio = mysqli_real_escape_string($con, $_POST['fecha_inicio']);
    $fecha_fin = mysqli_real_escape_string($con, $_POST['fecha_fin']);
    $activo = isset($_POST['activo']) ? 1 : 0;
    
    // Validar fechas
    if (!empty($fecha_inicio) && !empty($fecha_fin)) {
        if (strtotime($fecha_fin) < strtotime($fecha_inicio)) {
            $_SESSION['mensaje_tipo'] = 'error';
            $_SESSION['mensaje'] = 'La fecha fin no puede ser anterior a la fecha inicio';
            header('Location: editar.php?id=' . $id);
            exit;
        }
    }
    
    // Manejar archivo subido (opcional)
    $nombre_archivo = $slide['imagen_path']; // Mantener la actual por defecto
    
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0) {
        $archivo = $_FILES['imagen'];
        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        // Validar tipo de archivo
        if (!in_array($extension, $extensiones_permitidas)) {
            $_SESSION['mensaje_tipo'] = 'error';
            $_SESSION['mensaje'] = 'Formato de imagen no permitido. Use JPG, PNG, GIF o WEBP.';
            header('Location: editar.php?id=' . $id);
            exit;
        }
        
        // Validar tamaño (máximo 5MB)
        if ($archivo['size'] > 5 * 1024 * 1024) {
            $_SESSION['mensaje_tipo'] = 'error';
            $_SESSION['mensaje'] = 'La imagen es demasiado grande (máximo 5MB)';
            header('Location: editar.php?id=' . $id);
            exit;
        }
        
        // Crear directorio si no existe
        $directorio_uploads = '../../../../uploads/carrusel/';
        if (!file_exists($directorio_uploads)) {
            mkdir($directorio_uploads, 0777, true);
        }
        
        // Eliminar imagen anterior si existe
        if (!empty($slide['imagen_path'])) {
            $ruta_anterior = $directorio_uploads . $slide['imagen_path'];
            if (file_exists($ruta_anterior)) {
                unlink($ruta_anterior);
            }
        }
        
        // Generar nombre único
        $nombre_archivo = uniqid() . '_' . time() . '.' . $extension;
        $ruta_completa = $directorio_uploads . $nombre_archivo;
        
        // Mover archivo
        if (!move_uploaded_file($archivo['tmp_name'], $ruta_completa)) {
            $_SESSION['mensaje_tipo'] = 'error';
            $_SESSION['mensaje'] = 'Error al subir la imagen. Verifica permisos.';
            header('Location: editar.php?id=' . $id);
            exit;
        }
        
        $slide_data['imagen_path'] = $nombre_archivo;
    }
    
    // Actualizar en BD
    $query = "UPDATE carrusel SET 
              titulo = '$titulo',
              descripcion = '$descripcion',
              imagen_path = '$nombre_archivo',
              fecha_inicio = '$fecha_inicio',
              fecha_fin = '$fecha_fin',
              activo = $activo
              WHERE id = $id";
    
    if (mysqli_query($con, $query)) {
        $_SESSION['mensaje_tipo'] = 'success';
        $_SESSION['mensaje'] = 'Slide "' . htmlspecialchars($titulo) . '" actualizado exitosamente';
        header('Location: index.php');
        exit;
    } else {
        $_SESSION['mensaje_tipo'] = 'error';
        $_SESSION['mensaje'] = 'Error al actualizar en la base de datos: ' . mysqli_error($con);
        header('Location: editar.php?id=' . $id);
        exit;
    }
    
    // Actualizar datos para mostrar en el formulario
    $slide_data = [
        'titulo' => $titulo,
        'descripcion' => $descripcion,
        'fecha_inicio' => $fecha_inicio,
        'fecha_fin' => $fecha_fin,
        'activo' => $activo,
        'imagen_path' => $nombre_archivo
    ];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Slide #<?php echo $id; ?> - Carrusel</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <style>
        :root {
            --primary-color: #1a4b8c;
            --secondary-color: #2d68c4;
        }
        
        .header-cms {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 15px 0;
            margin-bottom: 20px;
        }
        
        .form-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.12);
        }
        
        .form-section {
            margin-bottom: 25px;
            padding-bottom: 25px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .form-section:last-child {
            border-bottom: none;
        }
        
        .section-title {
            color: var(--primary-color);
            border-left: 4px solid var(--primary-color);
            padding-left: 15px;
            margin-bottom: 20px;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 10px 25px;
            font-weight: 600;
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .current-image-container {
            position: relative;
            margin-bottom: 25px;
            border-radius: 10px;
            overflow: hidden;
            border: 2px solid #e9ecef;
        }
        
        .current-image-container:hover {
            border-color: var(--primary-color);
        }
        
        .current-image {
            width: 100%;
            height: 250px;
            object-fit: contain;
            background: #f8f9fa;
            padding: 15px;
        }
        
        .image-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.7);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            opacity: 0;
            transition: opacity 0.3s ease;
            color: white;
            padding: 20px;
            text-align: center;
        }
        
        .current-image-container:hover .image-overlay {
            opacity: 1;
        }
        
        .file-input-wrapper {
            position: relative;
            overflow: hidden;
            display: block;
            width: 100%;
        }
        
        .file-input-wrapper input[type=file] {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }
        
        .file-input-label {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 30px 20px;
            background-color: #f8f9fa;
            border: 3px dashed #dee2e6;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            min-height: 150px;
        }
        
        .file-input-label:hover, .file-input-label.dragover {
            background-color: #e9ecef;
            border-color: var(--primary-color);
            border-style: solid;
        }
        
        .upload-icon {
            font-size: 2rem;
            color: var(--primary-color);
            margin-bottom: 10px;
        }
        
        .preview-container {
            width: 100%;
            max-width: 400px;
            margin: 20px auto 0;
            display: none;
        }
        
        .preview-img {
            width: 100%;
            height: 200px;
            object-fit: contain;
            border-radius: 10px;
            border: 2px dashed #ddd;
            background: #f8f9fa;
            padding: 10px;
        }
        
        .requerido:after {
            content: " *";
            color: #dc3545;
        }
        
        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(26, 75, 140, 0.25);
        }
        
        .slide-info-card {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-left: 4px solid var(--primary-color);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
        }
        
        .info-item {
            display: flex;
            margin-bottom: 8px;
        }
        
        .info-label {
            font-weight: 600;
            min-width: 120px;
            color: #495057;
        }
        
        .badge-activo {
            background-color: #28a745;
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.85em;
        }
        
        .badge-inactivo {
            background-color: #dc3545;
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.85em;
        }
        
        .image-info-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
            border-left: 3px solid var(--primary-color);
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header-cms">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h4 mb-0">
                        <i class="fas fa-edit"></i> Editar Slide #<?php echo $id; ?>
                    </h1>
                    <p class="mb-0">Modifica los datos de este slide del carrusel</p>
                </div>
                <div>
                    <a href="index.php" class="btn btn-light btn-sm">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="container">
        <div class="form-container">
            <!-- Información del slide -->
            <div class="slide-info-card">
                <h5 class="mb-3"><i class="fas fa-info-circle text-primary"></i> Información del Slide</h5>
                <div class="info-item">
                    <span class="info-label">ID:</span>
                    <span class="badge bg-dark">#<?php echo $id; ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Creado:</span>
                    <span><?php echo date('d/m/Y H:i', strtotime($slide['fecha_creacion'])); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Estado:</span>
                    <span class="<?php echo $slide['activo'] ? 'badge-activo' : 'badge-inactivo'; ?>">
                        <?php echo $slide['activo'] ? 'Activo' : 'Inactivo'; ?>
                    </span>
                </div>
                <?php if (!empty($slide['imagen_path'])): ?>
                <div class="info-item">
                    <span class="info-label">Archivo:</span>
                    <span class="text-muted"><?php echo htmlspecialchars($slide['imagen_path']); ?></span>
                </div>
                <?php endif; ?>
            </div>
            
            <form method="POST" enctype="multipart/form-data" id="formEditarSlide">
                <!-- Sección 1: Información básica -->
                <div class="form-section">
                    <h4 class="section-title">
                        <i class="fas fa-info-circle me-2"></i>Información del Slide
                    </h4>
                    
                    <div class="mb-3">
                        <label for="titulo" class="form-label requerido">Título del Slide</label>
                        <input type="text" class="form-control" id="titulo" name="titulo" 
                               required maxlength="200" 
                               value="<?php echo htmlspecialchars($slide_data['titulo']); ?>"
                               placeholder="Ej: Bienvenida al Nuevo Año Escolar">
                        <div class="form-text">Título que aparecerá sobre la imagen (máx. 200 caracteres)</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" 
                                  rows="3" maxlength="500" 
                                  placeholder="Descripción breve del slide (aparecerá sobre la imagen)"><?php echo htmlspecialchars($slide_data['descripcion']); ?></textarea>
                        <div class="form-text">Descripción opcional (máx. 500 caracteres)</div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="fecha_inicio" class="form-label">Fecha de Inicio</label>
                                <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio"
                                       value="<?php echo $slide_data['fecha_inicio']; ?>">
                                <div class="form-text">Desde cuándo mostrar este slide (opcional)</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="fecha_fin" class="form-label">Fecha de Fin</label>
                                <input type="date" class="form-control" id="fecha_fin" name="fecha_fin"
                                       value="<?php echo $slide_data['fecha_fin']; ?>">
                                <div class="form-text">Hasta cuándo mostrar este slide (opcional)</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" 
                                   id="activo" name="activo" 
                                   <?php echo $slide_data['activo'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="activo">
                                <strong>Slide Activo</strong>
                            </label>
                            <div class="form-text">
                                Los slides activos se mostrarán en el portal. 
                                <span class="<?php echo $slide_data['activo'] ? 'text-success' : 'text-warning'; ?>">
                                    <i class="fas fa-<?php echo $slide_data['activo'] ? 'check-circle' : 'exclamation-circle'; ?>"></i> 
                                    Actualmente <?php echo $slide_data['activo'] ? 'activo' : 'inactivo'; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Sección 2: Imagen -->
                <div class="form-section">
                    <h4 class="section-title">
                        <i class="fas fa-image me-2"></i>Imagen del Slide
                    </h4>
                    
                    <!-- Imagen actual -->
                    <div class="mb-4">
                        <label class="form-label">Imagen Actual</label>
                        <div class="current-image-container">
                            <?php 
                            // Verificar si la imagen existe físicamente
                            $ruta_fisica = '../../../../uploads/carrusel/' . $slide['imagen_path'];
                            $imagen_existe = file_exists($ruta_fisica);
                            $ruta_imagen = '/heldyn/centeno/uploads/carrusel/' . htmlspecialchars($slide['imagen_path']);
                            ?>
                            
                            <?php if (!empty($slide['imagen_path']) && $imagen_existe): ?>
                                <img src="<?php echo $ruta_imagen; ?>" 
                                     alt="Imagen actual" 
                                     class="current-image"
                                     id="currentImage">
                                <div class="image-overlay">
                                    <i class="fas fa-eye fa-2x mb-3"></i>
                                    <p class="mb-2"><strong>Imagen actual</strong></p>
                                    <p class="small mb-0"><?php echo htmlspecialchars($slide['imagen_path']); ?></p>
                                    <p class="small">Haz clic para ampliar</p>
                                </div>
                            <?php else: ?>
                                <img src="https://placehold.co/400x250/1a4b8c/white?text=Imagen+no+disponible" 
                                     alt="Imagen no disponible" 
                                     class="current-image">
                                <div class="image-overlay">
                                    <i class="fas fa-exclamation-triangle fa-2x mb-3 text-warning"></i>
                                    <p class="mb-2"><strong>Imagen no encontrada</strong></p>
                                    <p class="small">La imagen no existe en el servidor</p>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (!empty($slide['imagen_path'])): ?>
                        <div class="image-info-box">
                            <p class="mb-2"><strong>Archivo actual:</strong> <?php echo htmlspecialchars($slide['imagen_path']); ?></p>
                            <p class="mb-0 text-muted small">
                                <i class="fas fa-info-circle"></i> Si subes una nueva imagen, la actual será reemplazada.
                            </p>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Nueva imagen (opcional) -->
                    <div class="mb-3">
                        <label class="form-label">Cambiar imagen (opcional)</label>
                        <div class="file-input-wrapper">
                            <input type="file" id="imagen" name="imagen" accept="image/*">
                            <label for="imagen" class="file-input-label" id="fileInputLabel">
                                <div class="upload-icon">
                                    <i class="fas fa-exchange-alt"></i>
                                </div>
                                <span class="fs-5">Arrastra o haz clic para cambiar</span>
                                <span class="text-muted mt-2">Deja vacío para mantener la imagen actual</span>
                                <span class="text-muted">Formatos: JPG, PNG, GIF, WEBP (max 5MB)</span>
                            </label>
                        </div>
                        
                        <div class="mt-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Archivo seleccionado:</strong>
                                    <div id="fileName" class="text-muted">Ningún archivo seleccionado</div>
                                </div>
                                <div class="col-md-6">
                                    <strong>Tamaño:</strong>
                                    <div id="fileSize" class="text-muted">--</div>
                                </div>
                            </div>
                        </div>
                        
                        <div id="previewContainer" class="preview-container mt-3">
                            <h6 class="text-center mb-3">Vista Previa de Nueva Imagen</h6>
                            <img id="imagePreview" class="preview-img" src="" alt="Vista previa">
                        </div>
                    </div>
                </div>
                
                <!-- Botones de acción -->
                <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                    <div class="action-buttons">
                        <a href="index.php" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                        <button type="button" class="btn btn-outline-danger" id="btnEliminar">
                            <i class="fas fa-trash"></i> Eliminar Slide
                        </button>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-primary" id="btnGuardar">
                            <i class="fas fa-save"></i> Guardar Cambios
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        // Variables globales
        let nuevaImagen = null;
        
        // Función para mostrar vista previa de imagen
        function previewImage(input) {
            const previewContainer = document.getElementById('previewContainer');
            const previewImage = document.getElementById('imagePreview');
            const fileName = document.getElementById('fileName');
            const fileSize = document.getElementById('fileSize');
            const fileInputLabel = document.getElementById('fileInputLabel');
            
            if (input.files && input.files[0]) {
                nuevaImagen = input.files[0];
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    previewImage.src = e.target.result;
                    previewContainer.style.display = 'block';
                    
                    // Actualizar información del archivo
                    fileName.textContent = nuevaImagen.name;
                    fileSize.textContent = (nuevaImagen.size / 1024 / 1024).toFixed(2) + ' MB';
                    
                    // Cambiar estilo del label
                    fileInputLabel.innerHTML = `
                        <div class="upload-icon text-warning">
                            <i class="fas fa-sync-alt"></i>
                        </div>
                        <span class="fs-5 text-warning">Nueva imagen seleccionada</span>
                        <span class="text-muted mt-2">${nuevaImagen.name}</span>
                        <span class="text-muted">Haz clic para cambiar</span>
                    `;
                    fileInputLabel.style.borderColor = '#ffc107';
                    fileInputLabel.style.backgroundColor = '#fff8e1';
                }
                
                reader.readAsDataURL(nuevaImagen);
            } else {
                previewContainer.style.display = 'none';
                nuevaImagen = null;
                fileName.textContent = 'Ningún archivo seleccionado';
                fileSize.textContent = '--';
                
                // Restaurar label original
                fileInputLabel.innerHTML = `
                    <div class="upload-icon">
                        <i class="fas fa-exchange-alt"></i>
                    </div>
                    <span class="fs-5">Arrastra o haz clic para cambiar</span>
                    <span class="text-muted mt-2">Deja vacío para mantener la imagen actual</span>
                    <span class="text-muted">Formatos: JPG, PNG, GIF, WEBP (max 5MB)</span>
                `;
                fileInputLabel.style.borderColor = '#dee2e6';
                fileInputLabel.style.backgroundColor = '#f8f9fa';
            }
        }
        
        // Confirmar eliminación del slide
        document.getElementById('btnEliminar').addEventListener('click', function() {
            const slideTitulo = document.getElementById('titulo').value || '<?php echo addslashes($slide["titulo"]); ?>';
            
            Swal.fire({
                title: '¿Eliminar slide permanentemente?',
                html: `<div class="text-start">
                        <p>¿Estás seguro de eliminar el slide?</p>
                        <div class="alert alert-danger py-2">
                            <strong>${slideTitulo}</strong>
                        </div>
                        <p class="text-danger small">
                            <i class="fas fa-exclamation-triangle"></i> Esta acción eliminará:
                            <ul class="small">
                                <li>El registro de la base de datos</li>
                                <li>La imagen asociada del servidor</li>
                                <li>Todos los datos del slide</li>
                            </ul>
                            <strong>¡Esta acción no se puede deshacer!</strong>
                        </p>
                       </div>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-trash"></i> Sí, eliminar permanentemente',
                cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
                reverseButtons: true,
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return new Promise((resolve) => {
                        setTimeout(() => {
                            resolve();
                        }, 1000);
                    });
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `index.php?eliminar=<?php echo $id; ?>&titulo=${encodeURIComponent(slideTitulo)}`;
                }
            });
        });
        
        // Validación del formulario con SweetAlert2
        document.getElementById('formEditarSlide').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const titulo = document.getElementById('titulo').value.trim();
            const imagenInput = document.getElementById('imagen');
            const fechaInicio = document.getElementById('fecha_inicio').value;
            const fechaFin = document.getElementById('fecha_fin').value;
            
            // Validar título
            if (!titulo) {
                Swal.fire({
                    title: 'Título requerido',
                    text: 'Debes ingresar un título para el slide.',
                    icon: 'warning',
                    confirmButtonColor: '#1a4b8c',
                    focusConfirm: false,
                    willClose: () => {
                        document.getElementById('titulo').focus();
                    }
                });
                return;
            }
            
            // Si se seleccionó una nueva imagen, validarla
            if (imagenInput.files && imagenInput.files[0]) {
                const imagen = imagenInput.files[0];
                
                // Validar tipo de archivo
                const extensionesPermitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                const extension = imagen.name.split('.').pop().toLowerCase();
                
                if (!extensionesPermitidas.includes(extension)) {
                    Swal.fire({
                        title: 'Formato no válido',
                        html: `El formato <strong>${extension}</strong> no está permitido.<br>
                              Formatos aceptados: JPG, PNG, GIF, WEBP.`,
                        icon: 'error',
                        confirmButtonColor: '#dc3545'
                    });
                    return;
                }
                
                // Validar tamaño (5MB máximo)
                if (imagen.size > 5 * 1024 * 1024) {
                    Swal.fire({
                        title: 'Imagen demasiado grande',
                        html: `La imagen seleccionada pesa <strong>${(imagen.size / 1024 / 1024).toFixed(2)} MB</strong>.<br>
                              El tamaño máximo permitido es 5MB.`,
                        icon: 'error',
                        confirmButtonColor: '#dc3545'
                    });
                    return;
                }
            }
            
            // Validar fechas
            if (fechaInicio && fechaFin) {
                const fechaInicioObj = new Date(fechaInicio);
                const fechaFinObj = new Date(fechaFin);
                
                if (fechaFinObj < fechaInicioObj) {
                    Swal.fire({
                        title: 'Fechas incorrectas',
                        text: 'La fecha fin no puede ser anterior a la fecha inicio.',
                        icon: 'error',
                        confirmButtonColor: '#dc3545'
                    });
                    return;
                }
            }
            
            // Mostrar confirmación antes de enviar
            const cambios = [];
            if (titulo !== '<?php echo addslashes($slide["titulo"]); ?>') cambios.push('Título');
            if (document.getElementById('activo').checked != <?php echo $slide['activo']; ?>) cambios.push('Estado');
            if (imagenInput.files && imagenInput.files[0]) cambios.push('Imagen');
            
            Swal.fire({
                title: '¿Guardar cambios?',
                html: `<div class="text-start">
                        <p>¿Estás seguro de guardar los cambios en el slide?</p>
                        ${cambios.length > 0 ? 
                            `<div class="alert alert-info py-2">
                                <strong>Cambios detectados:</strong>
                                <ul class="mb-0 mt-2">
                                    ${cambios.map(c => `<li>${c}</li>`).join('')}
                                </ul>
                            </div>` 
                            : '<p class="text-muted">No se detectaron cambios importantes.</p>'
                        }
                        ${imagenInput.files && imagenInput.files[0] ? 
                            `<div class="alert alert-warning py-2 small">
                                <i class="fas fa-exclamation-triangle"></i> 
                                <strong>Atención:</strong> Si subes una nueva imagen, la actual será reemplazada.
                            </div>` 
                            : ''
                        }
                       </div>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#1a4b8c',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-save"></i> Sí, guardar cambios',
                cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
                reverseButtons: true,
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return new Promise((resolve) => {
                        // Cambiar texto del botón
                        const btnGuardar = document.getElementById('btnGuardar');
                        btnGuardar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
                        btnGuardar.disabled = true;
                        
                        setTimeout(() => {
                            // Enviar formulario
                            document.getElementById('formEditarSlide').submit();
                        }, 1000);
                    });
                }
            });
        });
        
        // Drag & drop para imágenes
        const fileInputLabel = document.getElementById('fileInputLabel');
        const fileInput = document.getElementById('imagen');
        
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            fileInputLabel.addEventListener(eventName, preventDefaults, false);
        });
        
        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        ['dragenter', 'dragover'].forEach(eventName => {
            fileInputLabel.addEventListener(eventName, highlight, false);
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            fileInputLabel.addEventListener(eventName, unhighlight, false);
        });
        
        function highlight(e) {
            fileInputLabel.classList.add('dragover');
        }
        
        function unhighlight(e) {
            fileInputLabel.classList.remove('dragover');
        }
        
        // Manejar drop de archivos
        fileInputLabel.addEventListener('drop', handleDrop, false);
        
        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            
            if (files.length > 0) {
                fileInput.files = files;
                previewImage(fileInput);
            }
        }
        
        // Configurar fechas
        document.addEventListener('DOMContentLoaded', function() {
            const fechaInicio = document.getElementById('fecha_inicio');
            const fechaFin = document.getElementById('fecha_fin');
            
            // Configurar eventos para validación de fechas
            if (fechaInicio.value) {
                fechaFin.min = fechaInicio.value;
            }
            
            fechaInicio.addEventListener('change', function() {
                fechaFin.min = this.value;
                
                if (fechaFin.value && fechaFin.value < this.value) {
                    fechaFin.value = this.value;
                }
            });
            
            fechaFin.addEventListener('change', function() {
                fechaInicio.max = this.value;
                
                if (fechaInicio.value && fechaInicio.value > this.value) {
                    fechaInicio.value = this.value;
                }
            });
            
            // Configurar evento para preview de imagen
            fileInput.addEventListener('change', function() {
                previewImage(this);
            });
            
            // Mostrar imagen actual ampliada al hacer clic
            const currentImage = document.getElementById('currentImage');
            if (currentImage) {
                currentImage.addEventListener('click', function() {
                    Swal.fire({
                        imageUrl: this.src,
                        imageAlt: this.alt,
                        imageWidth: 600,
                        imageHeight: 400,
                        showCloseButton: true,
                        showConfirmButton: false,
                        background: 'transparent',
                        backdrop: 'rgba(0,0,0,0.8)'
                    });
                });
            }
            
            // Mostrar mensajes de sesión si los hay
            <?php if (isset($_SESSION['mensaje'])): ?>
            Swal.fire({
                title: '<?php echo isset($_SESSION['mensaje_tipo']) && $_SESSION['mensaje_tipo'] == 'error' ? "Error" : "Información"; ?>',
                text: '<?php echo addslashes($_SESSION['mensaje']); ?>',
                icon: '<?php echo isset($_SESSION['mensaje_tipo']) ? $_SESSION['mensaje_tipo'] : "info"; ?>',
                confirmButtonColor: '#1a4b8c'
            });
            <?php 
            unset($_SESSION['mensaje']);
            unset($_SESSION['mensaje_tipo']);
            endif; 
            ?>
        });
    </script>
</body>
</html>