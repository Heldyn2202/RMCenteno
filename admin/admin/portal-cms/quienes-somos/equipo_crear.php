<?php
// admin/admin/portal-cms/quienes-somos/equipo_crear.php

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

// Obtener siguiente orden disponible
$query = "SELECT MAX(orden) as max_orden FROM equipo_quienes_somos";
$result = mysqli_query($con, $query);
$row = mysqli_fetch_assoc($result);
$siguiente_orden = $row['max_orden'] ? $row['max_orden'] + 1 : 1;

$mensaje = '';
$error = '';
$form_data = [
    'nombre' => '',
    'cargo' => '',
    'descripcion' => '',
    'email' => '',
    'telefono' => '',
    'orden' => $siguiente_orden,
    'activo' => 1
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recibir y validar datos
    $nombre = mysqli_real_escape_string($con, $_POST['nombre'] ?? '');
    $cargo = mysqli_real_escape_string($con, $_POST['cargo'] ?? '');
    $descripcion = mysqli_real_escape_string($con, $_POST['descripcion'] ?? '');
    $email = mysqli_real_escape_string($con, $_POST['email'] ?? '');
    $telefono = mysqli_real_escape_string($con, $_POST['telefono'] ?? '');
    $orden = intval($_POST['orden'] ?? $siguiente_orden);
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
        $nombre_imagen = '';
        
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
            // Insertar en la base de datos
            $query = "INSERT INTO equipo_quienes_somos (nombre, cargo, descripcion, imagen, email, telefono, orden, activo) 
                      VALUES ('$nombre', '$cargo', '$descripcion', '$nombre_imagen', '$email', '$telefono', $orden, $activo)";
            
            if (mysqli_query($con, $query)) {
                $_SESSION['mensaje_tipo'] = 'success';
                $_SESSION['mensaje'] = 'Miembro del equipo creado exitosamente';
                header('Location: index.php#equipo');
                exit;
            } else {
                $error = 'Error al guardar: ' . mysqli_error($con);
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
        'activo' => $activo
    ];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Miembro - Quiénes Somos</title>
    
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
        
        .preview-container {
            width: 100%;
            max-width: 300px;
            margin: 0 auto;
            display: none;
        }
        
        .preview-img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid var(--primary-color);
            margin: 0 auto 15px;
            display: block;
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
            padding: 20px;
            background-color: #f8f9fa;
            border: 3px dashed #dee2e6;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            min-height: 120px;
            text-align: center;
        }
        
        .file-input-label:hover {
            background-color: #e9ecef;
            border-color: var(--primary-color);
        }
        
        .upload-icon {
            font-size: 1.5rem;
            color: var(--primary-color);
            margin-bottom: 10px;
        }
        
        .requerido:after {
            content: " *";
            color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="header-cms">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h4 mb-0">
                        <i class="fas fa-user-plus"></i> Nuevo Miembro del Equipo
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
            
            <form method="POST" enctype="multipart/form-data" id="formCrearEquipo">
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
                        
                        <!-- Foto de perfil -->
                        <div class="mb-4">
                            <label class="form-label">Foto de Perfil (opcional)</label>
                            <div class="file-input-wrapper">
                                <input type="file" id="imagen" name="imagen" accept="image/*">
                                <label for="imagen" class="file-input-label">
                                    <div class="upload-icon">
                                        <i class="fas fa-camera"></i>
                                    </div>
                                    <span>Haz clic para subir foto</span>
                                    <small class="text-muted d-block mt-2">JPG, PNG (max 5MB)</small>
                                    <small class="text-muted">Recomendado: 400x400 px</small>
                                </label>
                            </div>
                            
                            <div id="previewContainer" class="preview-container mt-3">
                                <p class="text-center mb-2"><strong>Vista previa:</strong></p>
                                <img id="imagePreview" class="preview-img" src="" alt="Vista previa">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between mt-4">
                    <a href="index.php#equipo" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Miembro
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
        document.getElementById('formCrearEquipo').addEventListener('submit', function(e) {
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
                title: '¿Crear miembro del equipo?',
                html: `<div class="text-start">
                        <p>¿Estás seguro de crear el siguiente miembro?</p>
                        <div class="alert alert-info py-2">
                            <strong>${nombre}</strong><br>
                            <small>${cargo}</small>
                        </div>
                       </div>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#1a4b8c',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-save"></i> Sí, crear',
                cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return new Promise((resolve) => {
                        // Enviar formulario
                        document.getElementById('formCrearEquipo').submit();
                    });
                }
            });
        });
    </script>
</body>
</html>