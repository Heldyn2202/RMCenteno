
<?php
// admin/admin/portal-cms/quienes-somos/colaborador_editar.php

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
    header('Location: index.php#colaboradores');
    exit;
}

$id = intval($_GET['id']);

// Obtener datos del colaborador
$query = "SELECT * FROM colaboradores_quienes_somos WHERE id = $id";
$result = mysqli_query($con, $query);
$colaborador = mysqli_fetch_assoc($result);

if (!$colaborador) {
    $_SESSION['mensaje_tipo'] = 'error';
    $_SESSION['mensaje'] = 'Colaborador no encontrado';
    header('Location: index.php#colaboradores');
    exit;
}

$mensaje = '';
$error = '';
$form_data = $colaborador;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recibir y validar datos
    $nombre = mysqli_real_escape_string($con, $_POST['nombre'] ?? '');
    $descripcion = mysqli_real_escape_string($con, $_POST['descripcion'] ?? '');
    $url = mysqli_real_escape_string($con, $_POST['url'] ?? '');
    $orden = intval($_POST['orden'] ?? $colaborador['orden']);
    $activo = isset($_POST['activo']) ? 1 : 0;
    
    // Validaciones
    if (empty($nombre)) {
        $error = 'El nombre es obligatorio';
    } elseif ($orden < 1) {
        $error = 'El orden debe ser un número positivo';
    }
    
    if (empty($error)) {
        // Manejar logo
        $nombre_logo = $colaborador['logo']; // Mantener el actual por defecto
        
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === 0) {
            $archivo = $_FILES['logo'];
            $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
            $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
            
            // Validar tipo de archivo
            if (!in_array($extension, $extensiones_permitidas)) {
                $error = 'Formato de imagen no permitido. Use JPG, PNG, GIF, WEBP o SVG.';
            }
            
            // Validar tamaño (máximo 5MB)
            elseif ($archivo['size'] > 5 * 1024 * 1024) {
                $error = 'El logo es demasiado grande. Máximo 5MB.';
            }
            
            if (empty($error)) {
                // Crear directorio si no existe
                $directorio_uploads = '../../../../uploads/quienes-somos/colaboradores/';
                if (!file_exists($directorio_uploads)) {
                    mkdir($directorio_uploads, 0777, true);
                }
                
                // Eliminar logo anterior si existe
                if (!empty($colaborador['logo'])) {
                    $ruta_anterior = $directorio_uploads . $colaborador['logo'];
                    if (file_exists($ruta_anterior)) {
                        unlink($ruta_anterior);
                    }
                }
                
                // Generar nombre único
                $nombre_logo = uniqid() . '_' . time() . '.' . $extension;
                $ruta_completa = $directorio_uploads . $nombre_logo;
                
                // Mover archivo
                if (!move_uploaded_file($archivo['tmp_name'], $ruta_completa)) {
                    $error = 'Error al subir el logo. Verifica permisos.';
                }
            }
        }
        
        if (empty($error)) {
            // Actualizar en la base de datos
            $query = "UPDATE colaboradores_quienes_somos SET 
                      nombre = '$nombre',
                      descripcion = '$descripcion',
                      logo = '$nombre_logo',
                      url = '$url',
                      orden = $orden,
                      activo = $activo
                      WHERE id = $id";
            
            if (mysqli_query($con, $query)) {
                $_SESSION['mensaje_tipo'] = 'success';
                $_SESSION['mensaje'] = 'Colaborador actualizado exitosamente';
                header('Location: index.php#colaboradores');
                exit;
            } else {
                $error = 'Error al actualizar: ' . mysqli_error($con);
            }
        }
    }
    
    // Guardar datos para mostrar en caso de error
    $form_data = [
        'nombre' => $nombre,
        'descripcion' => $descripcion,
        'url' => $url,
        'orden' => $orden,
        'activo' => $activo,
        'logo' => $nombre_logo
    ];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Colaborador - Quiénes Somos</title>
    
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
        
        .current-logo {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .current-img {
            width: 200px;
            height: 150px;
            object-fit: contain;
            padding: 10px;
            border: 3px solid var(--primary-color);
            border-radius: 8px;
            background: white;
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
            width: 200px;
            height: 150px;
            object-fit: contain;
            padding: 10px;
            border: 3px solid #28a745;
            border-radius: 8px;
            background: white;
            margin: 0 auto 10px;
            display: block;
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
        
        .info-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid var(--primary-color);
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="header-cms">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h4 mb-0">
                        <i class="fas fa-handshake"></i> Editar Colaborador #<?php echo $id; ?>
                    </h1>
                </div>
                <div>
                    <a href="index.php#colaboradores" class="btn btn-light btn-sm">
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
            
            <!-- Logo actual -->
            <div class="current-logo">
                <label class="form-label">Logo Actual</label>
                <?php if (!empty($colaborador['logo'])): 
                    $ruta_logo = '/heldyn/centeno/uploads/quienes-somos/colaboradores/' . $colaborador['logo'];
                ?>
                    <img src="<?php echo $ruta_logo; ?>" 
                         alt="<?php echo htmlspecialchars($colaborador['nombre']); ?>"
                         class="current-img"
                         onerror="this.src='https://placehold.co/200x150/1a4b8c/white?text=Logo'">
                    <p class="small mb-0">
                        <strong>Archivo:</strong> <?php echo htmlspecialchars($colaborador['logo']); ?>
                    </p>
                <?php else: ?>
                    <img src="https://placehold.co/200x150/1a4b8c/white?text=Sin+logo" 
                         alt="Sin logo" 
                         class="current-img">
                    <p class="small text-muted mb-0">No hay logo actual</p>
                <?php endif; ?>
                
                <div class="mt-2">
                    <span class="status-badge <?php echo $colaborador['activo'] ? 'status-active' : 'status-inactive'; ?>">
                        <i class="fas fa-circle fa-xs"></i> 
                        <?php echo $colaborador['activo'] ? 'Activo' : 'Inactivo'; ?>
                    </span>
                </div>
            </div>
            
            <form method="POST" enctype="multipart/form-data" id="formEditarColaborador">
                <input type="hidden" name="id" value="<?php echo $id; ?>">
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="nombre" class="form-label requerido">Nombre de la Institución</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" 
                                   required maxlength="200"
                                   value="<?php echo htmlspecialchars($form_data['nombre']); ?>"
                                   placeholder="Ej: Universidad Nacional de Educación">
                        </div>
                        
                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" 
                                      rows="4" maxlength="500"
                                      placeholder="Descripción breve de la institución colaboradora"><?php echo htmlspecialchars($form_data['descripcion']); ?></textarea>
                            <small class="text-muted">Máximo 500 caracteres</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="url" class="form-label">URL del Sitio Web</label>
                            <input type="url" class="form-control" id="url" name="url" 
                                   value="<?php echo htmlspecialchars($form_data['url']); ?>"
                                   placeholder="https://www.ejemplo.edu.pe">
                            <small class="text-muted">Ej: https://www.universidad.edu.pe (opcional)</small>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
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
                                    <label class="form-check-label" for="activo">Colaborador Activo</label>
                                    <small class="text-muted d-block">Los inactivos no se mostrarán</small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Cambiar logo -->
                        <div class="mb-4">
                            <label class="form-label">Cambiar Logo (opcional)</label>
                            <div class="info-box mb-3">
                                <small>
                                    <i class="fas fa-info-circle"></i> 
                                    Si subes un nuevo logo, el anterior será reemplazado. 
                                    Deja vacío para mantener el logo actual.
                                </small>
                            </div>
                            
                            <div class="file-input-wrapper">
                                <input type="file" id="logo" name="logo" accept="image/*">
                                <label for="logo" class="file-input-label">
                                    <div class="upload-icon">
                                        <i class="fas fa-image"></i>
                                    </div>
                                    <span>Haz clic para cambiar logo</span>
                                    <small class="text-muted d-block mt-2">JPG, PNG, SVG (max 5MB)</small>
                                    <small class="text-muted">Recomendado: 300x200 px</small>
                                </label>
                            </div>
                            
                            <div id="previewContainer" class="preview-container">
                                <p class="text-center mb-2"><strong>Vista previa de nuevo logo:</strong></p>
                                <img id="logoPreview" class="preview-img" src="" alt="Vista previa">
                            </div>
                        </div>
                        
                        <div class="info-box">
                            <h6><i class="fas fa-lightbulb"></i> Formatos recomendados:</h6>
                            <ul class="mb-0 small">
                                <li>PNG con transparencia para mejor calidad</li>
                                <li>SVG para logos vectoriales (escalable)</li>
                                <li>JPG para fotos o imágenes complejas</li>
                                <li>Tamaño máximo: 5MB</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between mt-4">
                    <button type="button" class="btn btn-danger" onclick="confirmarEliminar()">
                        <i class="fas fa-trash"></i> Eliminar Colaborador
                    </button>
                    <div>
                        <a href="index.php#colaboradores" class="btn btn-outline-secondary">
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
        // Vista previa de logo
        document.getElementById('logo').addEventListener('change', function(e) {
            const previewContainer = document.getElementById('previewContainer');
            const previewLogo = document.getElementById('logoPreview');
            
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    previewLogo.src = e.target.result;
                    previewContainer.style.display = 'block';
                }
                
                reader.readAsDataURL(this.files[0]);
                
                // Validar tamaño
                if (this.files[0].size > 5 * 1024 * 1024) {
                    Swal.fire({
                        title: 'Logo demasiado grande',
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
        document.getElementById('formEditarColaborador').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const nombre = document.getElementById('nombre').value.trim();
            
            if (!nombre) {
                Swal.fire({
                    title: 'Nombre requerido',
                    text: 'El nombre de la institución es obligatorio',
                    icon: 'warning',
                    confirmButtonColor: '#1a4b8c',
                    willClose: () => {
                        document.getElementById('nombre').focus();
                    }
                });
                return;
            }
            
            // Validar logo si se seleccionó
            const logoInput = document.getElementById('logo');
            if (logoInput.files && logoInput.files[0]) {
                const logo = logoInput.files[0];
                const extensionesPermitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
                const extension = logo.name.split('.').pop().toLowerCase();
                
                if (!extensionesPermitidas.includes(extension)) {
                    Swal.fire({
                        title: 'Formato no válido',
                        html: `El formato <strong>${extension}</strong> no está permitido.<br>
                              Formatos aceptados: JPG, PNG, GIF, WEBP, SVG.`,
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
                        <p>¿Estás seguro de actualizar los datos del colaborador?</p>
                        <div class="alert alert-info py-2">
                            <strong>${nombre}</strong>
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
                        document.getElementById('formEditarColaborador').submit();
                    });
                }
            });
        });
        
        // Confirmar eliminación
        function confirmarEliminar() {
            const nombre = "<?php echo addslashes($colaborador['nombre']); ?>";
            
            Swal.fire({
                title: '¿Eliminar colaborador?',
                html: `<div class="text-start">
                        <p>¿Estás seguro de eliminar a <strong>${nombre}</strong>?</p>
                        <div class="alert alert-warning py-2">
                            <i class="fas fa-exclamation-triangle"></i>
                            Esta acción no se puede deshacer. Se eliminarán todos los datos incluyendo el logo.
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
                        window.location.href = `colaborador_eliminar.php?id=<?php echo $id; ?>`;
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