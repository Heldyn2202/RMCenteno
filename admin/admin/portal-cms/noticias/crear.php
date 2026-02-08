<?php
session_start();
require_once __DIR__ . '/../session-compat.php';

// Verificar sesión
if (!verificarSesionCMS()) {
    header('Location: ../../../login/login.php');
    exit;
}

// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "sige");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
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
        $nombre_imagen = '';
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
            $extension = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
            $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            if (in_array(strtolower($extension), $extensiones_permitidas)) {
                $nombre_imagen = time() . '_' . uniqid() . '.' . $extension;
                $ruta_destino = '../../../admin/uploads/post/' . $nombre_imagen;
                
                if (move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta_destino)) {
                    // Imagen subida correctamente
                } else {
                    $error = 'Error al subir la imagen';
                }
            } else {
                $error = 'Formato de imagen no permitido';
            }
        }
        
        if (empty($error)) {
            // Insertar en la base de datos
            $sql = "INSERT INTO tblposts (PostTitle, CategoryId, SubCategoryId, PostDetails, PostUrl, PostImage, PostingDate, UpdationDate, Is_Active) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW(), ?)";
            
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("siisssi", $titulo, $categoria_id, $subcategoria_id, $contenido, $url_amigable, $nombre_imagen, $estado);
            
            if ($stmt->execute()) {
                $_SESSION['mensaje'] = 'Noticia creada exitosamente';
                $_SESSION['icono'] = 'success';
                $_SESSION['titulo'] = '¡Éxito!';
                header('Location: index.php');
                exit;
            } else {
                $error = 'Error al guardar la noticia: ' . $conexion->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Nueva Noticia - Portal Escolar</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- TinyMCE (opcional para editor de texto enriquecido) -->
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    
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
        
        .image-preview {
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            background-color: #f8f9fa;
            cursor: pointer;
            transition: all 0.3s ease;
            min-height: 200px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        
        .image-preview:hover {
            border-color: var(--primary-color);
            background-color: #e8f0fe;
        }
        
        .image-preview img {
            max-width: 100%;
            max-height: 300px;
            border-radius: 6px;
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
        
        .character-count {
            font-size: 0.85rem;
            color: #6c757d;
        }
        
        .character-count.warning {
            color: #ffc107;
        }
        
        .character-count.danger {
            color: #dc3545;
        }
        
        /* Estilos personalizados para SweetAlert2 */
        .swal2-confirm {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)) !important;
            border: none !important;
            padding: 10px 30px !important;
            border-radius: 6px !important;
        }
        
        .swal2-confirm:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(26, 75, 140, 0.3) !important;
        }
        
        .swal2-cancel {
            background-color: #6c757d !important;
            border: none !important;
            padding: 10px 30px !important;
            border-radius: 6px !important;
        }
        
        .swal2-cancel:hover {
            background-color: #5a6268 !important;
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
                        <i class="fas fa-plus-circle"></i> Crear Nueva Noticia
                    </h1>
                    <small>Complete el formulario para publicar una nueva noticia</small>
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
        <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <?php echo htmlspecialchars($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data" class="form-card" id="form-noticia">
            <div class="row">
                <!-- Columna izquierda -->
                <div class="col-lg-8">
                    <!-- Título -->
                    <div class="mb-4">
                        <label for="titulo" class="form-label fw-bold">
                            <i class="fas fa-heading text-primary me-2"></i>Título de la Noticia *
                        </label>
                        <input type="text" class="form-control form-control-lg" id="titulo" name="titulo" 
                               value="<?php echo isset($_POST['titulo']) ? htmlspecialchars($_POST['titulo']) : ''; ?>" 
                               required placeholder="Ingrese el título de la noticia">
                        <div class="character-count mt-1" id="titulo-count">0/150 caracteres</div>
                    </div>
                    
                    <!-- Contenido -->
                    <div class="mb-4">
                        <label for="contenido" class="form-label fw-bold">
                            <i class="fas fa-align-left text-primary me-2"></i>Contenido *
                        </label>
                        <textarea class="form-control" id="contenido" name="contenido" rows="15" 
                                  required placeholder="Escriba el contenido de la noticia..."><?php echo isset($_POST['contenido']) ? htmlspecialchars($_POST['contenido']) : ''; ?></textarea>
                    </div>
                    
                    <!-- URL amigable -->
                    <div class="mb-4">
                        <label for="url_amigable" class="form-label fw-bold">
                            <i class="fas fa-link text-primary me-2"></i>URL Amigable
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">https://tudominio.com/</span>
                            <input type="text" class="form-control" id="url_amigable" name="url_amigable" 
                                   value="<?php echo isset($_POST['url_amigable']) ? htmlspecialchars($_POST['url_amigable']) : ''; ?>" 
                                   placeholder="ej: inauguracion-laboratorio-ciencias">
                        </div>
                        <small class="text-muted">Dejar vacío para generar automáticamente del título</small>
                    </div>
                </div>
                
                <!-- Columna derecha -->
                <div class="col-lg-4">
                    <!-- Imagen -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">
                            <i class="fas fa-image text-primary me-2"></i>Imagen Destacada
                        </label>
                        <div class="image-preview" onclick="document.getElementById('imagen').click();">
                            <input type="file" id="imagen" name="imagen" accept="image/*" class="d-none" onchange="previewImage(event)">
                            <div id="image-placeholder">
                                <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                <p class="text-muted mb-0">Haz clic para subir una imagen</p>
                                <small class="text-muted">JPG, PNG, GIF o WebP (máx. 5MB)</small>
                            </div>
                            <img id="image-preview" class="d-none" alt="Vista previa">
                        </div>
                    </div>
                    
                    <!-- Categorías -->
                    <div class="mb-4">
                        <label for="categoria_id" class="form-label fw-bold">
                            <i class="fas fa-tag text-primary me-2"></i>Categoría *
                        </label>
                        <select class="form-select" id="categoria_id" name="categoria_id" required>
                            <option value="">Seleccione una categoría</option>
                            <?php while ($categoria = $categorias->fetch_assoc()): ?>
                            <option value="<?php echo $categoria['id']; ?>" 
                                <?php echo (isset($_POST['categoria_id']) && $_POST['categoria_id'] == $categoria['id']) ? 'selected' : ''; ?>>
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
                            <?php while ($subcategoria = $subcategorias->fetch_assoc()): ?>
                            <option value="<?php echo $subcategoria['SubCategoryId']; ?>"
                                <?php echo (isset($_POST['subcategoria_id']) && $_POST['subcategoria_id'] == $subcategoria['SubCategoryId']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($subcategoria['Subcategory']); ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <!-- Estado -->
                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="estado" name="estado" 
                                   <?php echo (!isset($_POST['estado']) || $_POST['estado']) ? 'checked' : ''; ?>>
                            <label class="form-check-label fw-bold" for="estado">
                                <i class="fas fa-power-off text-primary me-2"></i>Publicar inmediatamente
                            </label>
                        </div>
                        <small class="text-muted">Si se desactiva, la noticia quedará como borrador</small>
                    </div>
                    
                    <!-- Botones -->
                    <div class="d-grid gap-2">
                        <button type="button" id="btn-guardar" class="btn btn-primary-custom btn-lg">
                            <i class="fas fa-save me-2"></i> Guardar Noticia
                        </button>
                        <button type="reset" class="btn btn-outline-secondary">
                            <i class="fas fa-eraser me-2"></i> Limpiar Formulario
                        </button>
                        <a href="index.php" class="btn btn-outline-danger">
                            <i class="fas fa-times me-2"></i> Cancelar
                        </a>
                    </div>
                    
                    <!-- Información -->
                    <div class="alert alert-info mt-4">
                        <h6><i class="fas fa-info-circle me-2"></i> Información</h6>
                        <small class="d-block mb-1">* Campos obligatorios</small>
                        <small class="d-block mb-1">Las imágenes se redimensionan automáticamente</small>
                        <small class="d-block">La URL amigable mejora el SEO</small>
                    </div>
                </div>
            </div>
        </form>
    </div>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Configurar TinyMCE (si decides usarlo)
        <?php if (file_exists('../../tinymce/tinymce.min.js')): ?>
        tinymce.init({
            selector: '#contenido',
            height: 400,
            menubar: true,
            plugins: [
                'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                'insertdatetime', 'media', 'table', 'code', 'help', 'wordcount'
            ],
            toolbar: 'undo redo | blocks | ' +
                'bold italic forecolor | alignleft aligncenter ' +
                'alignright alignjustify | bullist numlist outdent indent | ' +
                'removeformat | help',
            content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:16px }'
        });
        <?php endif; ?>
        
        // Previsualización de imagen
        function previewImage(event) {
            const reader = new FileReader();
            const imagePreview = document.getElementById('image-preview');
            const imagePlaceholder = document.getElementById('image-placeholder');
            
            reader.onload = function() {
                imagePreview.src = reader.result;
                imagePreview.classList.remove('d-none');
                imagePlaceholder.classList.add('d-none');
            }
            
            if (event.target.files[0]) {
                reader.readAsDataURL(event.target.files[0]);
            }
        }
        
        // Contador de caracteres para título
        document.getElementById('titulo').addEventListener('input', function() {
            const count = this.value.length;
            const countElement = document.getElementById('titulo-count');
            countElement.textContent = `${count}/150 caracteres`;
            
            if (count > 120) {
                countElement.classList.add('warning');
                countElement.classList.remove('danger');
            } else if (count > 140) {
                countElement.classList.remove('warning');
                countElement.classList.add('danger');
            } else {
                countElement.classList.remove('warning', 'danger');
            }
        });
        
        // Generar URL amigable automáticamente
        document.getElementById('titulo').addEventListener('blur', function() {
            const urlInput = document.getElementById('url_amigable');
            if (!urlInput.value) {
                const titulo = this.value.toLowerCase()
                    .normalize('NFD').replace(/[\u0300-\u036f]/g, '') // Eliminar acentos
                    .replace(/[^a-z0-9]+/g, '-') // Reemplazar caracteres especiales con guiones
                    .replace(/^-+|-+$/g, ''); // Eliminar guiones al inicio y final
                urlInput.value = titulo;
            }
        });
        
        // ==================== SWEETALERT2 PARA CONFIRMACIÓN ====================
        
        // Manejar el clic en el botón de guardar
        document.getElementById('btn-guardar').addEventListener('click', function(e) {
            e.preventDefault();
            
            // Validar campos obligatorios antes de mostrar la confirmación
            const titulo = document.getElementById('titulo').value.trim();
            const contenido = document.getElementById('contenido').value.trim();
            const categoria = document.getElementById('categoria_id').value;
            
            if (!titulo) {
                Swal.fire({
                    title: 'Campo requerido',
                    text: 'El título de la noticia es obligatorio',
                    icon: 'warning',
                    confirmButtonText: 'Entendido'
                });
                document.getElementById('titulo').focus();
                return;
            }
            
            if (!contenido) {
                Swal.fire({
                    title: 'Campo requerido',
                    text: 'El contenido de la noticia es obligatorio',
                    icon: 'warning',
                    confirmButtonText: 'Entendido'
                });
                document.getElementById('contenido').focus();
                return;
            }
            
            if (!categoria) {
                Swal.fire({
                    title: 'Campo requerido',
                    text: 'Debe seleccionar una categoría',
                    icon: 'warning',
                    confirmButtonText: 'Entendido'
                });
                document.getElementById('categoria_id').focus();
                return;
            }
            
            // Obtener datos para mostrar en la confirmación
            const estado = document.getElementById('estado').checked ? 'Publicado' : 'Borrador';
            const categoriaNombre = document.getElementById('categoria_id').options[document.getElementById('categoria_id').selectedIndex].text;
            
            // Mostrar confirmación con SweetAlert2
            Swal.fire({
                title: '¿Confirmar creación de noticia?',
                html: `
                    <div class="text-start">
                        <p><strong>Título:</strong> ${titulo.substring(0, 100)}${titulo.length > 100 ? '...' : ''}</p>
                        <p><strong>Categoría:</strong> ${categoriaNombre}</p>
                        <p><strong>Estado:</strong> ${estado}</p>
                        <p><strong>Contenido:</strong> ${contenido.length} caracteres</p>
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-save me-2"></i> Sí, guardar noticia',
                cancelButtonText: '<i class="fas fa-times me-2"></i> Cancelar',
                reverseButtons: true,
                customClass: {
                    confirmButton: 'swal2-confirm',
                    cancelButton: 'swal2-cancel'
                },
                buttonsStyling: false,
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    // Mostrar indicador de carga
                    return new Promise((resolve) => {
                        setTimeout(() => {
                            // Enviar el formulario
                            document.getElementById('form-noticia').submit();
                            resolve();
                        }, 500);
                    });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isDismissed) {
                    // Si cancela, mostrar mensaje
                    Swal.fire({
                        title: 'Cancelado',
                        text: 'La noticia no se ha guardado',
                        icon: 'info',
                        confirmButtonText: 'Continuar editando'
                    });
                }
            });
        });
        
        // También manejar el envío directo del formulario (por si alguien usa Enter)
        document.getElementById('form-noticia').addEventListener('submit', function(e) {
            // Prevenir el envío directo
            e.preventDefault();
            // Disparar el clic en el botón de guardar que mostrará SweetAlert2
            document.getElementById('btn-guardar').click();
        });
        
        // Mostrar mensaje si hay errores en el formulario
        <?php if ($error): ?>
        Swal.fire({
            title: 'Error al guardar',
            text: '<?php echo addslashes($error); ?>',
            icon: 'error',
            confirmButtonText: 'Entendido'
        });
        <?php endif; ?>
        
        // Mostrar mensaje si se está editando una noticia existente
        <?php if (isset($_GET['edit']) && $_GET['edit'] == '1'): ?>
        Swal.fire({
            title: 'Modo de edición',
            text: 'Estás editando una noticia existente. Los cambios se guardarán cuando confirmes.',
            icon: 'info',
            confirmButtonText: 'Entendido'
        });
        <?php endif; ?>
    </script>
</body>
</html>

<?php $conexion->close(); ?>