<?php
session_start();
require_once __DIR__ . '/../session-compat.php';

// Verificar sesión
if (!verificarSesionCMS()) {
    header('Location: ../../../login/login.php');
    exit;
}

// Verificar ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id = (int)$_GET['id'];

// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "sige");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Obtener la noticia
$sql = "SELECT * FROM tblposts WHERE id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();
$noticia = $resultado->fetch_assoc();

if (!$noticia) {
    header('Location: index.php');
    exit;
}

// Obtener categorías y subcategorías
$categorias = $conexion->query("SELECT * FROM tblcategory ORDER BY CategoryName");
$subcategorias = $conexion->query("SELECT * FROM tblsubcategory ORDER BY Subcategory");

// Procesar formulario
$mensaje = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');
    $categoria_id = $_POST['categoria_id'] ?? '';
    $subcategoria_id = $_POST['subcategoria_id'] ?? '';
    $contenido = trim($_POST['contenido'] ?? '');
    $url_amigable = trim($_POST['url_amigable'] ?? '');
    $estado = isset($_POST['estado']) ? 1 : 0;
    $eliminar_imagen = isset($_POST['eliminar_imagen']);
    
    // Validaciones
    if (empty($titulo)) {
        $error = 'El título es obligatorio';
    } elseif (empty($contenido)) {
        $error = 'El contenido es obligatorio';
    } else {
        // Generar URL amigable si no se proporcionó
        if (empty($url_amigable)) {
            $url_amigable = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $titulo)));
        }
        
        // Manejar la imagen
        $nombre_imagen = $noticia['PostImage'];
        
        if ($eliminar_imagen && $nombre_imagen) {
            // Eliminar imagen existente
            $ruta_imagen = '../../../admin/uploads/post/' . $nombre_imagen;
            if (file_exists($ruta_imagen)) {
                unlink($ruta_imagen);
            }
            $nombre_imagen = '';
        }
        
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
            // Eliminar imagen anterior si existe
            if ($nombre_imagen) {
                $ruta_anterior = '../../../admin/uploads/post/' . $nombre_imagen;
                if (file_exists($ruta_anterior)) {
                    unlink($ruta_anterior);
                }
            }
            
            // Subir nueva imagen
            $extension = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
            $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            if (in_array(strtolower($extension), $extensiones_permitidas)) {
                $nombre_imagen = time() . '_' . uniqid() . '.' . $extension;
                $ruta_destino = '../../../admin/uploads/post/' . $nombre_imagen;
                
                // Asegurar que el directorio existe
                $directorio_destino = dirname($ruta_destino);
                if (!is_dir($directorio_destino)) {
                    mkdir($directorio_destino, 0755, true);
                }
                
                if (!move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta_destino)) {
                    $error = 'Error al subir la imagen. Verifica permisos de escritura en la carpeta uploads/post';
                }
            } else {
                $error = 'Formato de imagen no permitido. Use JPG, PNG, GIF o WebP';
            }
        }
        
        if (empty($error)) {
            // Actualizar en la base de datos
            $sql = "UPDATE tblposts SET 
                    PostTitle = ?, 
                    CategoryId = ?, 
                    SubCategoryId = ?, 
                    PostDetails = ?, 
                    PostUrl = ?, 
                    PostImage = ?, 
                    UpdationDate = NOW(), 
                    Is_Active = ? 
                    WHERE id = ?";
            
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("siisssii", $titulo, $categoria_id, $subcategoria_id, $contenido, $url_amigable, $nombre_imagen, $estado, $id);
            
            if ($stmt->execute()) {
                $_SESSION['mensaje'] = 'Noticia actualizada exitosamente';
                $_SESSION['icono'] = 'success';
                $_SESSION['titulo'] = '¡Éxito!';
                header('Location: index.php');
                exit;
            } else {
                $error = 'Error al actualizar la noticia: ' . $conexion->error;
            }
        }
    }
} else {
    // Cargar datos existentes
    $titulo = $noticia['PostTitle'];
    $categoria_id = $noticia['CategoryId'];
    $subcategoria_id = $noticia['SubCategoryId'];
    $contenido = $noticia['PostDetails'];
    $url_amigable = $noticia['PostUrl'];
    $estado = $noticia['Is_Active'];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Noticia - Portal Escolar</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #1a4b8c;
            --secondary-color: #2d68c4;
        }
        
        body {
            background-color: #f8f9fa;
        }
        
        .admin-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 15px 0;
            margin-bottom: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .form-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            padding: 30px;
            margin-bottom: 30px;
        }
        
        .current-image {
            border: 2px solid #dee2e6;
            border-radius: 8px;
            padding: 10px;
            background-color: #f8f9fa;
            margin-bottom: 15px;
        }
        
        .current-image img,
        .image-preview-container img {
            max-width: 100%;
            max-height: 200px;
            border-radius: 6px;
            object-fit: cover;
        }
        
        .image-preview-container {
            border: 2px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            background-color: #f8f9fa;
            margin-top: 10px;
            display: none; /* Oculto por defecto */
            text-align: center;
        }
        
        .image-preview-container.active {
            display: block;
        }
        
        .preview-info {
            font-size: 0.8rem;
            color: #6c757d;
            margin-top: 5px;
        }
        
        .btn-primary-custom {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            padding: 10px 25px;
            font-weight: 500;
            border-radius: 6px;
            transition: all 0.3s ease;
        }
        
        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(26, 75, 140, 0.3);
        }
        
        .info-box {
            background-color: #e8f0fe;
            border-left: 4px solid var(--primary-color);
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        
        .textarea-simple {
            min-height: 300px;
        }
        
        .upload-info {
            font-size: 0.85rem;
            color: #6c757d;
        }
        
        .file-input-container {
            position: relative;
            overflow: hidden;
            display: inline-block;
            width: 100%;
        }
        
        .file-input-container input[type=file] {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            cursor: pointer;
            height: 100%;
            width: 100%;
        }
        
        .file-input-btn {
            background: #f8f9fa;
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .file-input-btn:hover {
            border-color: var(--primary-color);
            background: #e8f0fe;
        }
        
        .preview-buttons {
            margin-top: 10px;
            display: flex;
            gap: 10px;
            justify-content: center;
        }
        
        .preview-buttons .btn {
            padding: 5px 15px;
            font-size: 0.8rem;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="admin-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h4 mb-1">
                        <i class="fas fa-edit"></i> Editar Noticia
                    </h1>
                    <small>ID: <?php echo $id; ?> | Última modificación: <?php echo date('d/m/Y H:i', strtotime($noticia['UpdationDate'])); ?></small>
                </div>
                <div>
                    <a href="index.php" class="btn btn-light btn-sm me-2">
                        <i class="fas fa-arrow-left"></i> Volver al Listado
                    </a>
                </div>
            </div>
        </div>
    </header>
    
    <div class="container">
        <div class="info-box">
            <h6><i class="fas fa-info-circle text-primary me-2"></i> Editando: "<?php echo htmlspecialchars($noticia['PostTitle']); ?>"</h6>
            <small class="text-muted">Publicada el: <?php echo date('d/m/Y H:i', strtotime($noticia['PostingDate'])); ?></small>
        </div>
        
        <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <?php echo htmlspecialchars($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data" class="form-card" id="formEditar">
            <div class="row">
                <!-- Columna izquierda -->
                <div class="col-lg-8">
                    <!-- Título -->
                    <div class="mb-4">
                        <label for="titulo" class="form-label fw-bold">
                            <i class="fas fa-heading text-primary me-2"></i>Título de la Noticia *
                        </label>
                        <input type="text" class="form-control form-control-lg" id="titulo" name="titulo" 
                               value="<?php echo htmlspecialchars($titulo); ?>" 
                               required placeholder="Ingrese el título de la noticia">
                        <div class="form-text">Máximo 150 caracteres</div>
                    </div>
                    
                    <!-- Contenido -->
                    <div class="mb-4">
                        <label for="contenido" class="form-label fw-bold">
                            <i class="fas fa-align-left text-primary me-2"></i>Contenido *
                        </label>
                        <textarea class="form-control textarea-simple" id="contenido" name="contenido" 
                                  required placeholder="Escriba el contenido de la noticia..."><?php echo htmlspecialchars($contenido); ?></textarea>
                        <div class="form-text"></div>
                    </div>
                    
                    <!-- URL amigable -->
                    <div class="mb-4">
                        <label for="url_amigable" class="form-label fw-bold">
                            <i class="fas fa-link text-primary me-2"></i>URL Amigable
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">/</span>
                            <input type="text" class="form-control" id="url_amigable" name="url_amigable" 
                                   value="<?php echo htmlspecialchars($url_amigable); ?>" 
                                   placeholder="ej: inauguracion-laboratorio-ciencias">
                        </div>
                        <div class="form-text">Se usará para el enlace permanente de la noticia</div>
                    </div>
                </div>
                
                <!-- Columna derecha -->
                <div class="col-lg-4">
                    <!-- Imagen actual -->
                    <?php if ($noticia['PostImage']): ?>
                    <div class="mb-4">
                        <label class="form-label fw-bold">
                            <i class="fas fa-image text-primary me-2"></i>Imagen Actual
                        </label>
                        <div class="current-image">
                            <?php
                            $ruta_imagen = '../../../admin/uploads/post/' . $noticia['PostImage'];
                            if (file_exists($ruta_imagen)):
                            ?>
                            <img src="../../../admin/uploads/post/<?php echo htmlspecialchars($noticia['PostImage']); ?>" 
                                 alt="Imagen actual"
                                 class="img-fluid mb-2"
                                 id="currentImage">
                            <?php else: ?>
                            <div class="alert alert-warning mb-2">
                                <i class="fas fa-exclamation-triangle"></i> 
                                Imagen no encontrada,
                            </div>
                            <?php endif; ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="eliminar_imagen" name="eliminar_imagen">
                                <label class="form-check-label text-danger" for="eliminar_imagen">
                                    <i class="fas fa-trash me-1"></i> Eliminar esta imagen
                                </label>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Nueva imagen con vista previa -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">
                            <i class="fas fa-cloud-upload-alt text-primary me-2"></i>
                            <?php echo $noticia['PostImage'] ? 'Cambiar Imagen' : 'Subir Imagen'; ?>
                        </label>
                        <div class="file-input-container">
                            <div class="file-input-btn" id="fileInputBtn">
                                <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-3"></i>
                                <p class="mb-1">Haz clic para seleccionar una imagen</p>
                                <p class="upload-info mb-0">JPG, PNG, GIF o WebP (máx. 5MB)</p>
                            </div>
                            <input type="file" class="form-control" id="imagen" name="imagen" accept="image/*">
                        </div>
                        
                        <!-- Contenedor de vista previa -->
                        <div class="image-preview-container" id="imagePreviewContainer">
                            <div id="imagePreview"></div>
                            <div class="preview-info" id="imageInfo"></div>
                            <div class="preview-buttons">
                                <button type="button" class="btn btn-outline-danger btn-sm" id="removeImageBtn">
                                    <i class="fas fa-times me-1"></i> Quitar
                                </button>
                            </div>
                        </div>
                        
                        <div class="form-text">Dejar vacío para mantener la imagen actual</div>
                    </div>
                    
                    <!-- Categorías -->
                    <div class="mb-4">
                        <label for="categoria_id" class="form-label fw-bold">
                            <i class="fas fa-tag text-primary me-2"></i>Categoría *
                        </label>
                        <select class="form-select" id="categoria_id" name="categoria_id" required>
                            <option value="">Seleccione una categoría</option>
                            <?php 
                            $categorias->data_seek(0); // Reiniciar puntero
                            while ($categoria = $categorias->fetch_assoc()): 
                            ?>
                            <option value="<?php echo $categoria['id']; ?>" 
                                <?php echo ($categoria_id == $categoria['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($categoria['CategoryName']); ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <!-- Subcategorías -->
                    <div class="mb-4">
                        <label for="subcategoria_id" class="form-label fw-bold">
                            <i class="fas fa-tags text-primary me-2"></i>Subcategoría
                        </label>
                        <select class="form-select" id="subcategoria_id" name="subcategoria_id">
                            <option value="">Seleccione una subcategoría (opcional)</option>
                            <?php 
                            $subcategorias->data_seek(0); // Reiniciar puntero
                            while ($subcategoria = $subcategorias->fetch_assoc()): 
                            ?>
                            <option value="<?php echo $subcategoria['SubCategoryId']; ?>"
                                <?php echo ($subcategoria_id == $subcategoria['SubCategoryId']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($subcategoria['Subcategory']); ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <!-- Estado -->
                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="estado" name="estado" 
                                   <?php echo $estado ? 'checked' : ''; ?>>
                            <label class="form-check-label fw-bold" for="estado">
                                <i class="fas fa-power-off text-primary me-2"></i>Publicada
                            </label>
                        </div>
                        <div class="form-text">Si se desactiva, la noticia quedará como borrador</div>
                    </div>
                    
                    <!-- Botones -->
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary-custom btn-lg">
                            <i class="fas fa-save me-2"></i> Guardar Cambios
                        </button>
                        <a href="index.php" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i> Cancelar
                        </a>
                        <button type="button" class="btn btn-outline-danger" onclick="confirmarEliminacion()">
                            <i class="fas fa-trash me-2"></i> Eliminar Noticia
                        </button>
                    </div>
                    
                    <!-- Información -->
                    <div class="alert alert-warning mt-4">
                        <h6><i class="fas fa-exclamation-triangle me-2"></i> Precaución</h6>
                        <small class="d-block mb-1">• La eliminación de imágenes es permanente</small>
                       
                    </div>
                </div>
            </div>
        </form>
    </div>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        // Confirmar antes de eliminar
        function confirmarEliminacion() {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "Esta acción eliminará permanentemente la noticia. ¡No se puede deshacer!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'eliminar.php?id=<?php echo $id; ?>';
                }
            });
        }
        
        // Confirmar antes de eliminar imagen
        document.getElementById('eliminar_imagen')?.addEventListener('change', function() {
            if (this.checked) {
                Swal.fire({
                    title: '¿Eliminar imagen?',
                    text: "Esta imagen se eliminará permanentemente del servidor",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (!result.isConfirmed) {
                        this.checked = false;
                    }
                });
            }
        });
        
        // Vista previa de imagen
        document.getElementById('imagen').addEventListener('change', function() {
            const file = this.files[0];
            const previewContainer = document.getElementById('imagePreviewContainer');
            const preview = document.getElementById('imagePreview');
            const imageInfo = document.getElementById('imageInfo');
            const fileInputBtn = document.getElementById('fileInputBtn');
            
            if (file) {
                // Validar tipo de archivo
                const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                if (!validTypes.includes(file.type)) {
                    Swal.fire({
                        title: 'Formato no válido',
                        text: 'Solo se permiten imágenes JPG, PNG, GIF o WebP',
                        icon: 'error',
                        confirmButtonText: 'Aceptar'
                    });
                    this.value = '';
                    return;
                }
                
                // Validar tamaño (5MB)
                const maxSize = 5 * 1024 * 1024; // 5MB en bytes
                if (file.size > maxSize) {
                    Swal.fire({
                        title: 'Archivo muy grande',
                        text: 'La imagen no debe superar los 5MB',
                        icon: 'warning',
                        confirmButtonText: 'Aceptar'
                    });
                    this.value = '';
                    return;
                }
                
                // Crear URL para vista previa
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    // Actualizar botón de subida
                    fileInputBtn.innerHTML = `
                        <i class="fas fa-check-circle fa-2x text-success mb-3"></i>
                        <p class="mb-1"><strong>${file.name}</strong></p>
                        <p class="upload-info mb-0">Haz clic para cambiar la imagen</p>
                    `;
                    
                    // Mostrar vista previa
                    preview.innerHTML = `
                        <img src="${e.target.result}" 
                             alt="Vista previa" 
                             class="img-fluid"
                             style="max-height: 200px; object-fit: cover;">
                    `;
                    
                    // Mostrar información del archivo
                    const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
                    imageInfo.innerHTML = `
                        <div><strong>Nombre:</strong> ${file.name}</div>
                        <div><strong>Tamaño:</strong> ${sizeMB} MB</div>
                        <div><strong>Tipo:</strong> ${file.type}</div>
                    `;
                    
                    // Mostrar contenedor de vista previa
                    previewContainer.classList.add('active');
                };
                
                reader.readAsDataURL(file);
            }
        });
        
        // Botón para quitar imagen seleccionada
        document.getElementById('removeImageBtn').addEventListener('click', function() {
            const fileInput = document.getElementById('imagen');
            const previewContainer = document.getElementById('imagePreviewContainer');
            const fileInputBtn = document.getElementById('fileInputBtn');
            
            // Resetear input de archivo
            fileInput.value = '';
            
            // Restaurar botón de subida
            fileInputBtn.innerHTML = `
                <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-3"></i>
                <p class="mb-1">Haz clic para seleccionar una imagen</p>
                <p class="upload-info mb-0">JPG, PNG, GIF o WebP (máx. 5MB)</p>
            `;
            
            // Ocultar vista previa
            previewContainer.classList.remove('active');
        });
        
        // Mostrar información cuando se pasa el mouse sobre la imagen actual
        document.getElementById('currentImage')?.addEventListener('mouseover', function() {
            const tooltip = new bootstrap.Tooltip(this, {
                title: 'Imagen actual de la noticia',
                placement: 'top',
                trigger: 'hover'
            });
            tooltip.show();
        });
        
        // Generar URL amigable automáticamente
        document.getElementById('titulo').addEventListener('blur', function() {
            const urlInput = document.getElementById('url_amigable');
            if (!urlInput.value) {
                const titulo = this.value.toLowerCase()
                    .normalize('NFD').replace(/[\u0300-\u036f]/g, '') // Eliminar acentos
                    .replace(/ñ/g, 'n') // Mantener ñ
                    .replace(/[^a-z0-9]+/g, '-') // Reemplazar caracteres especiales con guiones
                    .replace(/^-+|-+$/g, ''); // Eliminar guiones al inicio y final
                urlInput.value = titulo;
            }
        });
        
        // Contador de caracteres para título
        document.getElementById('titulo').addEventListener('input', function() {
            const count = this.value.length;
            const max = 150;
            
            if (count > max) {
                this.value = this.value.substring(0, max);
            }
        });
        
        // NUEVO: Confirmar antes de guardar cambios
        document.getElementById('formEditar').addEventListener('submit', function(e) {
            e.preventDefault(); // Prevenir envío inmediato
            
            // Obtener el botón de submit
            const submitBtn = this.querySelector('button[type="submit"]');
            
            Swal.fire({
                title: '¿Guardar cambios?',
                html: `
                    <div class="text-start">
                        <p>¿Estás seguro de que deseas guardar los cambios en esta noticia?</p>
                        <div class="alert alert-info p-2 mt-2">
                            <small><i class="fas fa-info-circle me-1"></i> Esta acción actualizará la noticia inmediatamente</small>
                        </div>
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#1a4b8c',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-save me-2"></i> Sí, guardar',
                cancelButtonText: '<i class="fas fa-times me-2"></i> Cancelar',
                reverseButtons: true,
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    // Cambiar texto del botón
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Guardando...';
                    submitBtn.disabled = true;
                    
                    // Enviar formulario después de 500ms para que se vea el cambio
                    return new Promise((resolve) => {
                        setTimeout(() => {
                            // Deshabilitar el evento beforeunload
                            formChanged = false;
                            // Enviar formulario
                            e.target.submit();
                            resolve();
                        }, 500);
                    });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.dismiss === Swal.DismissReason.cancel) {
                    // Si cancela, no hacer nada
                    Swal.fire({
                        title: 'Cancelado',
                        text: 'Los cambios no se guardaron',
                        icon: 'info',
                        timer: 1500,
                        showConfirmButton: false
                    });
                }
            });
        });
        
        // Modificar el evento beforeunload para que no se active después de confirmar
        let formChanged = false;
        const form = document.getElementById('formEditar');
        const inputs = form.querySelectorAll('input, textarea, select');
        
        inputs.forEach(input => {
            input.addEventListener('input', () => formChanged = true);
            input.addEventListener('change', () => formChanged = true);
        });
        
        window.addEventListener('beforeunload', function(e) {
            if (formChanged) {
                e.preventDefault();
                e.returnValue = 'Tienes cambios sin guardar. ¿Seguro que quieres salir?';
                return e.returnValue;
            }
        });
        
        // Mostrar mensajes de sesión
        <?php if (isset($_SESSION['mensaje'])): ?>
        Swal.fire({
            icon: '<?php echo isset($_SESSION['icono']) ? $_SESSION['icono'] : "info"; ?>',
            title: '<?php echo isset($_SESSION['titulo']) ? $_SESSION['titulo'] : "Información"; ?>',
            text: '<?php echo $_SESSION['mensaje']; ?>',
            timer: 3000,
            showConfirmButton: false
        });
        <?php 
        unset($_SESSION['mensaje']);
        unset($_SESSION['icono']);
        unset($_SESSION['titulo']);
        ?>
        <?php endif; ?>
        
        // Inicializar tooltips
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
</body>
</html>

<?php $conexion->close(); ?>