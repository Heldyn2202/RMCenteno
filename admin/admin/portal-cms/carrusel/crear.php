<?php
// admin/admin/portal-cms/carrusel/crear.php - VERSIÓN CON SWEETALERT2

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

$mensaje = '';
$error = '';
$form_data = [
    'titulo' => '',
    'descripcion' => '',
    'fecha_inicio' => '',
    'fecha_fin' => '',
    'activo' => 1
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recibir y validar datos
    $titulo = mysqli_real_escape_string($con, $_POST['titulo']);
    $descripcion = mysqli_real_escape_string($con, $_POST['descripcion']);
    $fecha_inicio = mysqli_real_escape_string($con, $_POST['fecha_inicio']);
    $fecha_fin = mysqli_real_escape_string($con, $_POST['fecha_fin']);
    $activo = isset($_POST['activo']) ? 1 : 0;
    
    // Guardar datos para mostrar en caso de error
    $form_data = [
        'titulo' => $titulo,
        'descripcion' => $descripcion,
        'fecha_inicio' => $fecha_inicio,
        'fecha_fin' => $fecha_fin,
        'activo' => $activo
    ];
    
    // Validar fechas
    if (!empty($fecha_inicio) && !empty($fecha_fin)) {
        if (strtotime($fecha_fin) < strtotime($fecha_inicio)) {
            $_SESSION['mensaje_tipo'] = 'error';
            $_SESSION['mensaje'] = 'La fecha fin no puede ser anterior a la fecha inicio';
            header('Location: crear.php');
            exit;
        }
    }
    
    // Manejar archivo subido
    $nombre_archivo = '';
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0) {
        $archivo = $_FILES['imagen'];
        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        // Validar tipo de archivo
        if (!in_array($extension, $extensiones_permitidas)) {
            $_SESSION['mensaje_tipo'] = 'error';
            $_SESSION['mensaje'] = 'Formato de imagen no permitido. Use JPG, PNG, GIF o WEBP.';
            header('Location: crear.php');
            exit;
        }
        
        // Validar tamaño (máximo 5MB)
        if ($archivo['size'] > 5 * 1024 * 1024) {
            $_SESSION['mensaje_tipo'] = 'error';
            $_SESSION['mensaje'] = 'La imagen es demasiado grande. Máximo 5MB.';
            header('Location: crear.php');
            exit;
        }
        
        // Crear directorio si no existe
        $directorio_uploads = '../../../../uploads/carrusel/';
        if (!file_exists($directorio_uploads)) {
            mkdir($directorio_uploads, 0777, true);
        }
        
        // Generar nombre único
        $nombre_archivo = uniqid() . '_' . time() . '.' . $extension;
        $ruta_completa = $directorio_uploads . $nombre_archivo;
        
        // Mover archivo
        if (!move_uploaded_file($archivo['tmp_name'], $ruta_completa)) {
            $_SESSION['mensaje_tipo'] = 'error';
            $_SESSION['mensaje'] = 'Error al subir la imagen. Verifica permisos de la carpeta uploads/carrusel/.';
            header('Location: crear.php');
            exit;
        }
    } else {
        // Verificar tipo de error
        if ($_FILES['imagen']['error'] == 1 || $_FILES['imagen']['error'] == 2) {
            $_SESSION['mensaje_tipo'] = 'error';
            $_SESSION['mensaje'] = 'La imagen es demasiado grande. Máximo 5MB.';
        } elseif ($_FILES['imagen']['error'] == 4) {
            $_SESSION['mensaje_tipo'] = 'error';
            $_SESSION['mensaje'] = 'Debe seleccionar una imagen para el slide.';
        } else {
            $_SESSION['mensaje_tipo'] = 'error';
            $_SESSION['mensaje'] = 'Error al subir la imagen. Código: ' . $_FILES['imagen']['error'];
        }
        header('Location: crear.php');
        exit;
    }
    
    // Insertar en BD si no hay errores
    $query = "INSERT INTO carrusel (titulo, descripcion, imagen_path, fecha_inicio, fecha_fin, activo) 
              VALUES ('$titulo', '$descripcion', '$nombre_archivo', '$fecha_inicio', '$fecha_fin', $activo)";
    
    if (mysqli_query($con, $query)) {
        $_SESSION['mensaje_tipo'] = 'success';
        $_SESSION['mensaje'] = 'Slide "' . htmlspecialchars($titulo) . '" creado exitosamente';
        header('Location: index.php');
        exit;
    } else {
        $_SESSION['mensaje_tipo'] = 'error';
        $_SESSION['mensaje'] = 'Error al guardar en la base de datos: ' . mysqli_error($con);
        header('Location: crear.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Slide - Carrusel</title>
    
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
        
        .preview-container {
            width: 100%;
            max-width: 400px;
            margin: 0 auto;
            display: none;
        }
        
        .preview-img {
            width: 100%;
            height: 250px;
            object-fit: contain;
            border-radius: 10px;
            border: 2px dashed #ddd;
            background: #f8f9fa;
            padding: 10px;
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
            padding: 40px 20px;
            background-color: #f8f9fa;
            border: 3px dashed #dee2e6;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            min-height: 200px;
        }
        
        .file-input-label:hover, .file-input-label.dragover {
            background-color: #e9ecef;
            border-color: var(--primary-color);
            border-style: solid;
        }
        
        .upload-icon {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 15px;
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
        
        .image-stats {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin-top: 15px;
        }
        
        .image-stats ul {
            margin-bottom: 0;
        }
    </style>
</head>
<body>
    <div class="header-cms">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h4 mb-0">
                        <i class="fas fa-plus-circle"></i> Crear Nuevo Slide
                    </h1>
                    <p class="mb-0">Añade un nuevo slide al carrusel principal</p>
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
            <form method="POST" enctype="multipart/form-data" id="formCrearSlide">
                <!-- Sección 1: Información básica -->
                <div class="form-section">
                    <h4 class="section-title">
                        <i class="fas fa-info-circle me-2"></i>Información del Slide
                    </h4>
                    
                    <div class="mb-3">
                        <label for="titulo" class="form-label requerido">Título del Slide</label>
                        <input type="text" class="form-control" id="titulo" name="titulo" 
                               required maxlength="200" 
                               value="<?php echo htmlspecialchars($form_data['titulo']); ?>"
                               placeholder="Ej: Bienvenida al Nuevo Año Escolar 2024">
                        <div class="form-text">Título que aparecerá sobre la imagen (máx. 200 caracteres)</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" 
                                  rows="3" maxlength="500" 
                                  placeholder="Descripción breve que aparecerá sobre la imagen del slide"><?php echo htmlspecialchars($form_data['descripcion']); ?></textarea>
                        <div class="form-text">Descripción opcional (máx. 500 caracteres)</div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="fecha_inicio" class="form-label">Fecha de Inicio</label>
                                <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio"
                                       value="<?php echo $form_data['fecha_inicio']; ?>">
                                <div class="form-text">Desde cuándo mostrar este slide (opcional)</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="fecha_fin" class="form-label">Fecha de Fin</label>
                                <input type="date" class="form-control" id="fecha_fin" name="fecha_fin"
                                       value="<?php echo $form_data['fecha_fin']; ?>">
                                <div class="form-text">Hasta cuándo mostrar este slide (opcional)</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Sección 2: Imagen -->
                <div class="form-section">
                    <h4 class="section-title">
                        <i class="fas fa-image me-2"></i>Imagen del Slide
                    </h4>
                    
                    <div class="mb-4">
                        <label class="form-label requerido">Seleccionar Imagen</label>
                        <div class="file-input-wrapper">
                            <input type="file" id="imagen" name="imagen" accept="image/*" required>
                            <label for="imagen" class="file-input-label" id="fileInputLabel">
                                <div class="upload-icon">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                </div>
                                <span class="fs-5">Arrastra o haz clic para subir</span>
                                <span class="text-muted mt-2">Formatos: JPG, PNG, GIF, WEBP (max 5MB)</span>
                                <span class="text-muted">Tamaño recomendado: 1200x500 px</span>
                            </label>
                        </div>
                        
                        <div class="image-stats mt-3">
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
                        
                        <div id="previewContainer" class="preview-container mt-4">
                            <h6 class="text-center mb-3">Vista Previa</h6>
                            <img id="imagePreview" class="preview-img" src="" alt="Vista previa">
                        </div>
                    </div>
                </div>
                
                <!-- Sección 3: Configuración -->
                <div class="form-section">
                    <h4 class="section-title">
                        <i class="fas fa-cog me-2"></i>Configuración
                    </h4>
                    
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" 
                                   id="activo" name="activo" 
                                   <?php echo $form_data['activo'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="activo">
                                <strong>Slide Activo</strong>
                            </label>
                            <div class="form-text">
                                Los slides activos se mostrarán en el portal. 
                                <span class="text-success"><i class="fas fa-check-circle"></i> Recomendado: Activar después de completar todos los datos.</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Botones de acción -->
                <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                    <a href="index.php" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary" id="btnGuardar">
                        <i class="fas fa-save"></i> Guardar Slide
                    </button>
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
        let imagenSeleccionada = null;
        let dragCounter = 0;
        
        // Función para mostrar vista previa de imagen
        function previewImage(input) {
            const previewContainer = document.getElementById('previewContainer');
            const previewImage = document.getElementById('imagePreview');
            const fileName = document.getElementById('fileName');
            const fileSize = document.getElementById('fileSize');
            const fileInputLabel = document.getElementById('fileInputLabel');
            
            if (input.files && input.files[0]) {
                imagenSeleccionada = input.files[0];
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    previewImage.src = e.target.result;
                    previewContainer.style.display = 'block';
                    
                    // Actualizar información del archivo
                    fileName.textContent = imagenSeleccionada.name;
                    fileSize.textContent = (imagenSeleccionada.size / 1024 / 1024).toFixed(2) + ' MB';
                    
                    // Cambiar estilo del label
                    fileInputLabel.innerHTML = `
                        <div class="upload-icon text-success">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <span class="fs-5 text-success">Imagen seleccionada</span>
                        <span class="text-muted mt-2">${imagenSeleccionada.name}</span>
                        <span class="text-muted">Haz clic para cambiar</span>
                    `;
                    fileInputLabel.style.borderColor = '#28a745';
                    fileInputLabel.style.backgroundColor = '#f0fff4';
                }
                
                reader.readAsDataURL(imagenSeleccionada);
            } else {
                previewContainer.style.display = 'none';
                imagenSeleccionada = null;
                fileName.textContent = 'Ningún archivo seleccionado';
                fileSize.textContent = '--';
                
                // Restaurar label original
                fileInputLabel.innerHTML = `
                    <div class="upload-icon">
                        <i class="fas fa-cloud-upload-alt"></i>
                    </div>
                    <span class="fs-5">Arrastra o haz clic para subir</span>
                    <span class="text-muted mt-2">Formatos: JPG, PNG, GIF, WEBP (max 5MB)</span>
                    <span class="text-muted">Tamaño recomendado: 1200x500 px</span>
                `;
                fileInputLabel.style.borderColor = '#dee2e6';
                fileInputLabel.style.backgroundColor = '#f8f9fa';
            }
        }
        
        // Validación del formulario con SweetAlert2
        document.getElementById('formCrearSlide').addEventListener('submit', function(e) {
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
            
            // Validar imagen
            if (!imagenInput.files || !imagenInput.files[0]) {
                Swal.fire({
                    title: 'Imagen requerida',
                    text: 'Debes seleccionar una imagen para el slide.',
                    icon: 'warning',
                    confirmButtonColor: '#1a4b8c'
                });
                return;
            }
            
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
            Swal.fire({
                title: '¿Crear slide?',
                html: `<div class="text-start">
                        <p>¿Estás seguro de crear el siguiente slide?</p>
                        <div class="alert alert-info py-2">
                            <strong>${titulo}</strong>
                        </div>
                        <div class="row small">
                            <div class="col-6">
                                <strong>Imagen:</strong><br>
                                ${imagen.name}<br>
                                ${(imagen.size / 1024 / 1024).toFixed(2)} MB
                            </div>
                            <div class="col-6">
                                <strong>Estado:</strong><br>
                                ${document.getElementById('activo').checked ? 'Activo' : 'Inactivo'}
                            </div>
                        </div>
                       </div>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#1a4b8c',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-save"></i> Sí, crear slide',
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
                            document.getElementById('formCrearSlide').submit();
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
        
        // Configurar fechas por defecto
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            const fechaInicio = document.getElementById('fecha_inicio');
            const fechaFin = document.getElementById('fecha_fin');
            
            fechaInicio.min = today;
            if (!fechaInicio.value) {
                fechaInicio.value = today;
            }
            
            // Establecer fecha fin 30 días después por defecto
            if (!fechaFin.value) {
                const futureDate = new Date();
                futureDate.setDate(futureDate.getDate() + 30);
                fechaFin.min = today;
                fechaFin.value = futureDate.toISOString().split('T')[0];
            }
            
            // Configurar eventos para validación de fechas
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