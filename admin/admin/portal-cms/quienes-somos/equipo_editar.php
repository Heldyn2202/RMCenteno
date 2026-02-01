<?php
// admin/admin/portal-cms/quienes-somos/equipo_editar.php

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
    header('Location: ../index.php');
    exit;
}

// Verificar que se pasó un ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['mensaje_tipo'] = 'error';
    $_SESSION['mensaje'] = 'ID no válido';
    header('Location: index.php#equipo');
    exit;
}

$id = intval($_GET['id']);

// Obtener datos del miembro
$query = "SELECT * FROM equipo_quienes_somos WHERE id = $id";
$result = mysqli_query($con, $query);
$miembro = mysqli_fetch_assoc($result);

if (!$miembro) {
    $_SESSION['mensaje_tipo'] = 'error';
    $_SESSION['mensaje'] = 'Miembro no encontrado';
    header('Location: index.php#equipo');
    exit;
}

$mensaje = '';
$error = '';
$form_data = $miembro;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recibir y validar datos
    $nombre = mysqli_real_escape_string($con, $_POST['nombre'] ?? '');
    $cargo = mysqli_real_escape_string($con, $_POST['cargo'] ?? '');
    $descripcion = mysqli_real_escape_string($con, $_POST['descripcion'] ?? '');
    $email = mysqli_real_escape_string($con, $_POST['email'] ?? '');
    $telefono = mysqli_real_escape_string($con, $_POST['telefono'] ?? '');
    $orden = intval($_POST['orden'] ?? $miembro['orden']);
    $activo = isset($_POST['activo']) ? 1 : 0;
    
    // Validaciones
    if (empty($nombre)) {
        $error = 'El nombre es obligatorio';
    } elseif (empty($cargo)) {
        $error = 'El cargo es obligatorio';
    } elseif ($orden < 1) {
        $error = 'El orden debe ser un número positivo';
    }
    
    if (empty($error)) {
        // Manejar imagen de perfil
        $nombre_imagen = $miembro['imagen']; // Mantener la actual por defecto
        
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0) {
            $archivo = $_FILES['imagen'];
            $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
            $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            // Validar tipo de archivo
            if (!in_array($extension, $extensiones_permitidas)) {
                $error = 'Formato de imagen no permitido. Use JPG, PNG, GIF o WEBP.';
            }
            
            // Validar tamaño (máximo 5MB)
            elseif ($archivo['size'] > 5 * 1024 * 1024) {
                $error = 'La imagen es demasiado grande. Máximo 5MB.';
            }
            
            if (empty($error)) {
                // Crear directorio si no existe
                $directorio_uploads = '../../../../uploads/quienes-somos/equipo/';
                if (!file_exists($directorio_uploads)) {
                    mkdir($directorio_uploads, 0777, true);
                }
                
                // Eliminar imagen anterior si existe
                if (!empty($miembro['imagen'])) {
                    $ruta_anterior = $directorio_uploads . $miembro['imagen'];
                    if (file_exists($ruta_anterior)) {
                        unlink($ruta_anterior);
                    }
                }
                
                // Generar nombre único
                $nombre_imagen = uniqid() . '_' . time() . '.' . $extension;
                $ruta_completa = $directorio_uploads . $nombre_imagen;
                
                // Mover archivo
                if (!move_uploaded_file($archivo['tmp_name'], $ruta_completa)) {
                    $error = 'Error al subir la imagen. Verifica permisos.';
                }
            }
        }
        
        if (empty($error)) {
            // Actualizar en la base de datos
            $query = "UPDATE equipo_quienes_somos SET 
                      nombre = '$nombre',
                      cargo = '$cargo',
                      descripcion = '$descripcion',
                      imagen = '$nombre_imagen',
                      email = '$email',
                      telefono = '$telefono',
                      orden = $orden,
                      activo = $activo
                      WHERE id = $id";
            
            if (mysqli_query($con, $query)) {
                $_SESSION['mensaje_tipo'] = 'success';
                $_SESSION['mensaje'] = 'Miembro actualizado exitosamente';
                header('Location: index.php#equipo');
                exit;
            } else {
                $error = 'Error al actualizar: ' . mysqli_error($con);
            }
        }
    }
    
    // Guardar datos para mostrar en caso de error
    $form_data = [
        'nombre' => $nombre,
        'cargo' => $cargo,
        'descripcion' => $descripcion,
        'email' => $email,
        'telefono' => $telefono,
        'orden' => $orden,
        'activo' => $activo,
        'imagen' => $nombre_imagen
    ];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Miembro - Quiénes Somos</title>
    
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
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .current-photo {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .current-img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid var(--primary-color);
            margin-bottom: 10px;
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
            padding: 15px;
            background-color: #f8f9fa;
            border: 3px dashed #dee2e6;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            min-height: 100px;
            text-align: center;
        }
        
        .file-input-label:hover {
            background-color: #e9ecef;
            border-color: var(--primary-color);
        }
        
        .upload-icon {
            font-size: 1.2rem;
            color: var(--primary-color);
            margin-bottom: 8px;
        }
        
        .requerido:after {
            content: " *";
            color: #dc3545;
        }
        
        .preview-container {
            width: 100%;
            max-width: 300px;
            margin: 20px auto 0;
            display: none;
        }
        
        .preview-img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid #28a745;
            margin: 0 auto 10px;
            display: block;
        }
        
        .info-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid var(--primary-color);
            margin-bottom: 20px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .status-active {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-inactive {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="header-cms">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h4 mb-0">
                        <i class="fas fa-user-edit"></i> Editar Miembro #<?php echo $id; ?>
                    </h1>
                </div>
                <div>
                    <a href="index.php#equipo" class="btn btn-light btn-sm">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="container">
        <div class="form-container">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <!-- Foto actual -->
            <div class="current-photo">
                <label class="form-label">Foto Actual</label>
                <?php if (!empty($miembro['imagen'])): 
                    $ruta_foto = '/heldyn/centeno/uploads/quienes-somos/equipo/' . $miembro['imagen'];
                ?>
                    <img src="<?php echo $ruta_foto; ?>" 
                         alt="<?php echo htmlspecialchars($miembro['nombre']); ?>"
                         class="current-img"
                         onerror="this.src='https://placehold.co/150x150/1a4b8c/white?text=Usuario'">
                    <p class="small mb-0">
                        <strong>Archivo:</strong> <?php echo htmlspecialchars($miembro['imagen']); ?>
                    </p>
                <?php else: ?>
                    <img src="https://placehold.co/150x150/1a4b8c/white?text=Sin+foto" 
                         alt="Sin foto" 
                         class="current-img">
                    <p class="small text-muted mb-0">No hay foto actual</p>
                <?php endif; ?>
                
                <div class="mt-2">
                    <span class="status-badge <?php echo $miembro['activo'] ? 'status-active' : 'status-inactive'; ?>">
                        <i class="fas fa-circle fa-xs"></i> 
                        <?php echo $miembro['activo'] ? 'Activo' : 'Inactivo'; ?>
                    </span>
                </div>
            </div>
            
            <form method="POST" enctype="multipart/form-data" id="formEditarEquipo">
                <input type="hidden" name="id" value="<?php echo $id; ?>">
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="nombre" class="form-label requerido">Nombre Completo</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" 
                                   required maxlength="100"
                                   value="<?php echo htmlspecialchars($form_data['nombre']); ?>"
                                   placeholder="Ej: María González Pérez">
                        </div>
                        
                        <div class="mb-3">
                            <label for="cargo" class="form-label requerido">Cargo/Posición</label>
                            <input type="text" class="form-control" id="cargo" name="cargo" 
                                   required maxlength="100"
                                   value="<?php echo htmlspecialchars($form_data['cargo']); ?>"
                                   placeholder="Ej: Directora General">
                        </div>
                        
                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" 
                                      rows="4" maxlength="500"
                                      placeholder="Breve descripción del miembro del equipo"><?php echo htmlspecialchars($form_data['descripcion']); ?></textarea>
                            <small class="text-muted">Máximo 500 caracteres</small>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="email" class="form-label">Correo Electrónico</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($form_data['email']); ?>"
                                   placeholder="ejemplo@escuela.edu">
                        </div>
                        
                        <div class="mb-3">
                            <label for="telefono" class="form-label">Teléfono</label>
                            <input type="text" class="form-control" id="telefono" name="telefono" 
                                   value="<?php echo htmlspecialchars($form_data['telefono']); ?>"
                                   placeholder="Ej: +51 123 456 789">
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="orden" class="form-label">Orden de Visualización</label>
                                    <input type="number" class="form-control" id="orden" name="orden" 
                                           min="1" value="<?php echo $form_data['orden']; ?>">
                                    <small class="text-muted">Menor número = aparece primero</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3 form-check pt-4">
                                    <input type="checkbox" class="form-check-input" id="activo" name="activo" 
                                           <?php echo $form_data['activo'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="activo">Miembro Activo</label>
                                    <small class="text-muted d-block">Los inactivos no se mostrarán</small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Cambiar foto -->
                        <div class="mb-4">
                            <label class="form-label">Cambiar Foto (opcional)</label>
                            <div class="info-box mb-3">
                                <small>
                                    <i class="fas fa-info-circle"></i> 
                                    Si subes una nueva foto, la anterior será reemplazada. 
                                    Deja vacío para mantener la foto actual.
                                </small>
                            </div>
                            
                            <div class="file-input-wrapper">
                                <input type="file" id="imagen" name="imagen" accept="image/*">
                                <label for="imagen" class="file-input-label">
                                    <div class="upload-icon">
                                        <i class="fas fa-camera"></i>
                                    </div>
                                    <span>Haz clic para cambiar foto</span>
                                    <small class="text-muted d-block mt-2">JPG, PNG (max 5MB)</small>
                                    <small class="text-muted">Recomendado: 400x400 px</small>
                                </label>
                            </div>
                            
                            <div id="previewContainer" class="preview-container">
                                <p class="text-center mb-2"><strong>Vista previa de nueva foto:</strong></p>
                                <img id="imagePreview" class="preview-img" src="" alt="Vista previa">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between mt-4">
                    <button type="button" class="btn btn-danger" onclick="confirmarEliminar()">
                        <i class="fas fa-trash"></i> Eliminar Miembro
                    </button>
                    <div>
                        <a href="index.php#equipo" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
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
        // Vista previa de imagen
        document.getElementById('imagen').addEventListener('change', function(e) {
            const previewContainer = document.getElementById('previewContainer');
            const previewImage = document.getElementById('imagePreview');
            
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    previewImage.src = e.target.result;
                    previewContainer.style.display = 'block';
                }
                
                reader.readAsDataURL(this.files[0]);
                
                // Validar tamaño
                if (this.files[0].size > 5 * 1024 * 1024) {
                    Swal.fire({
                        title: 'Imagen demasiado grande',
                        text: 'El tamaño máximo permitido es 5MB',
                        icon: 'error',
                        confirmButtonColor: '#dc3545'
                    });
                    this.value = '';
                    previewContainer.style.display = 'none';
                }
            } else {
                previewContainer.style.display = 'none';
            }
        });
        
        // Validación del formulario
        document.getElementById('formEditarEquipo').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const nombre = document.getElementById('nombre').value.trim();
            const cargo = document.getElementById('cargo').value.trim();
            
            if (!nombre) {
                Swal.fire({
                    title: 'Nombre requerido',
                    text: 'El nombre es obligatorio',
                    icon: 'warning',
                    confirmButtonColor: '#1a4b8c',
                    willClose: () => {
                        document.getElementById('nombre').focus();
                    }
                });
                return;
            }
            
            if (!cargo) {
                Swal.fire({
                    title: 'Cargo requerido',
                    text: 'El cargo es obligatorio',
                    icon: 'warning',
                    confirmButtonColor: '#1a4b8c',
                    willClose: () => {
                        document.getElementById('cargo').focus();
                    }
                });
                return;
            }
            
            // Validar imagen si se seleccionó
            const imagenInput = document.getElementById('imagen');
            if (imagenInput.files && imagenInput.files[0]) {
                const imagen = imagenInput.files[0];
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
            }
            
            // Mostrar confirmación
            Swal.fire({
                title: '¿Guardar cambios?',
                html: `<div class="text-start">
                        <p>¿Estás seguro de actualizar los datos del miembro?</p>
                        <div class="alert alert-info py-2">
                            <strong>${nombre}</strong><br>
                            <small>${cargo}</small>
                        </div>
                       </div>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#1a4b8c',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-save"></i> Sí, guardar',
                cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return new Promise((resolve) => {
                        // Enviar formulario
                        document.getElementById('formEditarEquipo').submit();
                    });
                }
            });
        });
        
        // Confirmar eliminación
        function confirmarEliminar() {
            const nombre = "<?php echo addslashes($miembro['nombre']); ?>";
            
            Swal.fire({
                title: '¿Eliminar miembro?',
                html: `<div class="text-start">
                        <p>¿Estás seguro de eliminar a <strong>${nombre}</strong>?</p>
                        <div class="alert alert-warning py-2">
                            <i class="fas fa-exclamation-triangle"></i>
                            Esta acción no se puede deshacer. Se eliminarán todos los datos incluyendo la foto.
                        </div>
                       </div>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-trash"></i> Sí, eliminar',
                cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return new Promise((resolve) => {
                        window.location.href = `equipo_eliminar.php?id=<?php echo $id; ?>`;
                    });
                }
            });
        }
        
        // Mostrar mensaje si hubo error en el POST
        <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($error)): ?>
        Swal.fire({
            title: 'Error al guardar',
            text: '<?php echo addslashes($error); ?>',
            icon: 'error',
            confirmButtonColor: '#dc3545'
        });
        <?php endif; ?>
    </script>
</body>
</html>
<?php
// Cerrar conexión
mysqli_close($con);
?>