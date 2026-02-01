<?php
// admin/admin/portal-cms/quienes-somos/index.php

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

// Obtener información principal
$query = "SELECT * FROM quienes_somos WHERE id = 1";
$result = mysqli_query($con, $query);
$quienes_somos = mysqli_fetch_assoc($result);

if (!$quienes_somos) {
    // Crear registro si no existe
    $insert = "INSERT INTO quienes_somos (titulo) VALUES ('Quiénes Somos')";
    mysqli_query($con, $insert);
    $quienes_somos = ['id' => 1, 'titulo' => 'Quiénes Somos'];
}

// Obtener equipo
$query = "SELECT * FROM equipo_quienes_somos ORDER BY orden, nombre";
$result = mysqli_query($con, $query);
$equipo = [];
if ($result) {
    $equipo = mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Obtener configuración del footer
$footerQuery = "SELECT * FROM footer_config WHERE id = 1";
$footerResult = mysqli_query($con, $footerQuery);
$footer_config = mysqli_fetch_assoc($footerResult);

if (!$footer_config) {
    // Crear registro si no existe
    $insert = "INSERT INTO footer_config (titulo_izquierda) VALUES ('Portal Escolar')";
    mysqli_query($con, $insert);
    $footer_config = [
        'id' => 1,
        'titulo_izquierda' => 'Portal Escolar',
        'direccion' => 'Calle Principal #123, Colonia Centro. Código Postal 12345',
        'email' => 'info@colegioejemplo.edu',
        'telefono' => '+123 456 7890',
        'titulo_derecha' => 'Portal Escolar',
        'descripcion_derecha' => 'El Portal Escolar es la plataforma oficial de comunicación e información educativa, dedicada a promover la innovación y el desarrollo integral en el ámbito educativo. Nuestro compromiso es brindar recursos, herramientas y contenidos de calidad para fortalecer el proceso de enseñanza-aprendizaje de nuestra comunidad educativa.',
        'derechos_autor' => '© [año] Portal Escolar | Institución Educativa',
        'creditos' => 'Desarrollado para la comunidad educativa'
    ];
}

// Manejar mensajes
$mensaje = isset($_SESSION['mensaje']) ? $_SESSION['mensaje'] : '';
$mensaje_tipo = isset($_SESSION['mensaje_tipo']) ? $_SESSION['mensaje_tipo'] : '';
unset($_SESSION['mensaje']);
unset($_SESSION['mensaje_tipo']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiénes Somos - CMS Portal</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Animate.css para animaciones -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <!-- Summernote CSS -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs5.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #1a4b8c;
            --secondary-color: #2d68c4;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }
        
        .header-cms {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 20px 0;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            margin-bottom: 20px;
        }
        
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 2px solid #e9ecef;
            font-weight: 600;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            padding: 10px 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(26, 75, 140, 0.4);
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
        }
        
        .section-title {
            color: var(--primary-color);
            border-left: 4px solid var(--primary-color);
            padding-left: 15px;
            margin-bottom: 20px;
        }
        
        .preview-img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #dee2e6;
            transition: all 0.3s ease;
        }
        
        .team-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid var(--primary-color);
        }
        
        .badge-activo {
            background-color: var(--success-color);
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .badge-activo:hover {
            transform: scale(1.05);
            box-shadow: 0 3px 10px rgba(40, 167, 69, 0.3);
        }
        
        .badge-inactivo {
            background-color: var(--danger-color);
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .badge-inactivo:hover {
            transform: scale(1.05);
            box-shadow: 0 3px 10px rgba(220, 53, 69, 0.3);
        }
        
        .tab-content {
            padding: 20px;
            background: white;
            border: 1px solid #dee2e6;
            border-top: none;
            border-radius: 0 0 8px 8px;
        }
        
        .nav-tabs .nav-link.active {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-color: var(--primary-color);
            font-weight: 600;
        }
        
        .file-input-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
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
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            min-height: 150px;
            text-align: center;
        }
        
        .file-input-label:hover {
            background-color: #e9ecef;
            border-color: var(--primary-color);
            transform: translateY(-2px);
        }
        
        .upload-icon {
            font-size: 2rem;
            color: var(--primary-color);
            margin-bottom: 10px;
        }
        
        .info-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid var(--primary-color);
            margin-bottom: 20px;
        }
        
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        
        .action-buttons .btn {
            padding: 4px 8px;
            font-size: 0.85rem;
            transition: all 0.3s ease;
        }
        
        .action-buttons .btn:hover {
            transform: translateY(-2px);
        }
        
        
        
        .preview-footer {
            background: linear-gradient(135deg, var(--primary-color), #1a2238);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-top: 25px;
            box-shadow: 0 10px 30px rgba(26, 75, 140, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.1);
            position: relative;
            overflow: hidden;
        }
        
        .preview-footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #28a745, #17a2b8, #1a4b8c);
            border-radius: 15px 15px 0 0;
        }
        
        .preview-footer h5 {
            color: white;
            margin-bottom: 20px;
            font-weight: 700;
            position: relative;
            padding-bottom: 10px;
            border-bottom: 2px solid rgba(255, 255, 255, 0.2);
        }
        
        .preview-footer h5::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 60px;
            height: 2px;
            background: #28a745;
        }
        
        .preview-footer a {
            color: #80d0ff !important;
            text-decoration: none;
            transition: all 0.3s ease;
            border-bottom: 1px dotted transparent;
        }
        
        .preview-footer a:hover {
            color: #ffffff !important;
            border-bottom: 1px dotted #ffffff;
        }
        
        .footer-bottom-preview {
            background: linear-gradient(135deg, #0d1930, #1a2238);
            color: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 0 0 15px 15px;
            margin-top: -5px;
            font-size: 0.9rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .footer-form-section {
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 25px;
            margin-bottom: 25px;
            background: #ffffff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            border: 1px solid #dee2e6;
            transition: all 0.3s ease;
        }
        
        .footer-form-section:hover {
            border-color: var(--primary-color);
            box-shadow: 0 8px 20px rgba(26, 75, 140, 0.1);
            transform: translateY(-2px);
        }
        
        .footer-form-section h5 {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .footer-form-section h5 i {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 1.1rem;
            box-shadow: 0 4px 10px rgba(26, 75, 140, 0.3);
        }
        
        /* Efecto de brillo en inputs del footer */
        .footer-form-section input:focus,
        .footer-form-section textarea:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(26, 75, 140, 0.25);
            transform: translateY(-2px);
            transition: all 0.3s ease;
        }
        
        /* Badge para tags de variables */
        .variable-tag {
            background: linear-gradient(135deg, #ffc107, #ff9800);
            color: #000;
            border: none;
            padding: 5px 12px;
            border-radius: 20px;
            font-family: 'Courier New', monospace;
            font-size: 0.85rem;
            font-weight: 600;
            box-shadow: 0 3px 8px rgba(255, 152, 0, 0.3);
        }
        
        /* Botón de guardar footer mejorado */
        #btn-guardar-footer {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            padding: 12px 30px;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            font-size: 1rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(26, 75, 140, 0.4);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        #btn-guardar-footer:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(26, 75, 140, 0.6);
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
        }
        
        #btn-guardar-footer:active {
            transform: translateY(1px);
            box-shadow: 0 3px 10px rgba(26, 75, 140, 0.4);
        }
        
        #btn-guardar-footer i {
            margin-right: 8px;
            font-size: 1.1rem;
        }
        
        /* Botón de guardar información mejorado */
        #btn-guardar-info {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            padding: 12px 30px;
            font-weight: 600;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(26, 75, 140, 0.4);
            transition: all 0.3s ease;
        }
        
        #btn-guardar-info:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(26, 75, 140, 0.6);
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
        }
        
        /* Indicador de progreso */
        .progress-indicator {
            height: 4px;
            background: linear-gradient(90deg, #28a745, #17a2b8, #1a4b8c);
            border-radius: 2px;
            margin-top: 10px;
            animation: progress-animation 2s ease-in-out infinite;
            background-size: 200% 100%;
        }
        
        @keyframes progress-animation {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        /* Animación para mensajes */
        .swal2-popup {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            border-radius: 15px !important;
        }
        
        .swal2-confirm {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)) !important;
            border: none !important;
            font-weight: 600 !important;
            padding: 10px 25px !important;
            border-radius: 8px !important;
        }
        
        .swal2-confirm:hover {
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color)) !important;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(26, 75, 140, 0.4) !important;
        }
        
        /* Mensaje de éxito */
        .mensaje-exito {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            animation: slideInRight 0.5s ease-out;
        }
        
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        /* Scroll suave para las pestañas */
        html {
            scroll-behavior: smooth;
        }
        
        /* Mejoras para las pestañas */
        .nav-tabs .nav-link {
            color: var(--primary-color);
            border: 1px solid #dee2e6;
            border-bottom: none;
            border-radius: 8px 8px 0 0;
            margin-right: 5px;
            padding: 10px 20px;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .nav-tabs .nav-link:hover {
            background-color: #f0f0f0;
            transform: translateY(-2px);
        }
        
        .nav-tabs .nav-link i {
            margin-right: 8px;
        }
        
        /* Efectos para imágenes de vista previa */
        .img-preview-container {
            position: relative;
            overflow: hidden;
            border-radius: 8px;
        }
        
        .img-preview-container img {
            transition: all 0.5s ease;
        }
        
        .img-preview-container:hover img {
            transform: scale(1.05);
        }
        
        /* Efecto de carga para imágenes */
        .img-loading {
            filter: blur(5px);
            transition: filter 0.5s ease;
        }
        
        .img-loaded {
            filter: blur(0);
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header-cms">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-users"></i> Quiénes Somos 
                    </h1>
                    <p class="mb-0">Administra la información institucional, equipo y pie de página</p>
                </div>
                <div>
                    <a href="../index.php" class="btn btn-light btn-sm">
                        <i class="fas fa-home"></i> Panel Principal
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="container">
        <!-- NOTA: Eliminamos el alert tradicional, usaremos solo SweetAlert2 -->
        
        <!-- Tabs de navegación -->
        <ul class="nav nav-tabs" id="quienesSomosTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="info-tab" data-bs-toggle="tab" data-bs-target="#info" type="button">
                    <i class="fas fa-info-circle"></i> Información Principal
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="equipo-tab" data-bs-toggle="tab" data-bs-target="#equipo" type="button">
                    <i class="fas fa-user-tie"></i> Equipo Directivo
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="footer-tab" data-bs-toggle="tab" data-bs-target="#footer" type="button">
                    <i class="fas fa-shoe-prints"></i> Pie de Página
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="vista-tab" data-bs-toggle="tab" data-bs-target="#vista" type="button">
                    <i class="fas fa-eye"></i> Vista Previa
                </button>
            </li>
        </ul>
        
        <div class="tab-content" id="quienesSomosTabsContent">
            <!-- TAB 1: Información Principal -->
            <div class="tab-pane fade show active" id="info" role="tabpanel">
                <h3 class="section-title">Información Institucional</h3>
                
                <form method="POST" action="guardar_info.php" enctype="multipart/form-data" id="formInfoPrincipal">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="titulo" class="form-label">Título Principal <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="titulo" name="titulo" 
                                       value="<?php echo htmlspecialchars($quienes_somos['titulo'] ?? ''); ?>"
                                       required maxlength="200">
                            </div>
                            
                            <div class="mb-3">
                                <label for="contenido" class="form-label">Contenido Principal</label>
                                <textarea class="form-control" id="contenido" name="contenido" rows="10"><?php echo htmlspecialchars($quienes_somos['contenido'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="mision" class="form-label">Misión</label>
                                        <textarea class="form-control" id="mision" name="mision" rows="5"><?php echo htmlspecialchars($quienes_somos['mision'] ?? ''); ?></textarea>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="vision" class="form-label">Visión</label>
                                        <textarea class="form-control" id="vision" name="vision" rows="5"><?php echo htmlspecialchars($quienes_somos['vision'] ?? ''); ?></textarea>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="valores" class="form-label">Valores (separados por comas)</label>
                                <textarea class="form-control" id="valores" name="valores" rows="3"><?php echo htmlspecialchars($quienes_somos['valores'] ?? ''); ?></textarea>
                                <small class="text-muted">Ej: Respeto, Excelencia, Responsabilidad, Solidaridad</small>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <!-- Imagen actual -->
                            <div class="mb-4">
                                <label class="form-label">Imagen Principal</label>
                                <?php if (!empty($quienes_somos['imagen_principal'])): 
                                    $ruta_imagen = '/heldyn/centeno/uploads/quienes-somos/' . $quienes_somos['imagen_principal'];
                                ?>
                                    <div class="text-center mb-3 img-preview-container">
                                        <img src="<?php echo $ruta_imagen; ?>" 
                                             alt="Imagen actual" 
                                             class="preview-img"
                                             id="current-image-preview"
                                             onerror="this.src='https://placehold.co/400x200/1a4b8c/white?text=Imagen+no+disponible'">
                                        <p class="small mt-2 mb-0">
                                            <strong>Archivo:</strong> <?php echo htmlspecialchars($quienes_somos['imagen_principal']); ?>
                                        </p>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center mb-3">
                                        <img src="https://placehold.co/400x200/1a4b8c/white?text=Sin+imagen" 
                                             alt="Sin imagen" 
                                             class="preview-img"
                                             id="current-image-preview">
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Subir nueva imagen -->
                                <div class="mb-3">
                                    <label class="form-label">Cambiar imagen (opcional)</label>
                                    <div class="file-input-wrapper">
                                        <input type="file" id="imagen_principal" name="imagen_principal" accept="image/*">
                                        <label for="imagen_principal" class="file-input-label">
                                            <div class="upload-icon">
                                                <i class="fas fa-camera"></i>
                                            </div>
                                            <span>Haz clic para cambiar</span>
                                            <small class="text-muted d-block mt-2">JPG, PNG, WEBP (max 5MB)</small>
                                            <small class="text-muted">Tamaño recomendado: 800x400 px</small>
                                        </label>
                                    </div>
                                    <div class="progress-indicator mt-2 d-none" id="image-upload-progress"></div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="imagen_principal_alt" class="form-label">Texto alternativo de imagen</label>
                                    <input type="text" class="form-control" id="imagen_principal_alt" name="imagen_principal_alt"
                                           value="<?php echo htmlspecialchars($quienes_somos['imagen_principal_alt'] ?? ''); ?>"
                                           placeholder="Descripción de la imagen para accesibilidad">
                                </div>
                            </div>
                            
                            <div class="info-box">
                                <h6><i class="fas fa-lightbulb"></i> Recomendaciones:</h6>
                                <ul class="mb-0 small">
                                    <li>Usa imágenes de alta calidad</li>
                                    <li>Máximo 5MB por imagen</li>
                                    <li>Formato recomendado: JPG o PNG</li>
                                    <li>Describe bien las imágenes para accesibilidad</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between mt-4">
                        <a href="#" class="btn btn-outline-secondary" onclick="history.back()">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                        <button type="submit" class="btn btn-primary" id="btn-guardar-info">
                            <i class="fas fa-save"></i> Guardar Información
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- TAB 2: Equipo Directivo -->
            <div class="tab-pane fade" id="equipo" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="section-title mb-0">Equipo Directivo</h3>
                    <a href="equipo_crear.php" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus-circle"></i> Nuevo Miembro
                    </a>
                </div>
                
                <?php if (empty($equipo)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-user-tie fa-4x text-muted mb-3"></i>
                        <h4>No hay miembros del equipo</h4>
                        <p class="text-muted">Agrega los miembros del equipo directivo de tu institución.</p>
                        <a href="equipo_crear.php" class="btn btn-primary">
                            <i class="fas fa-plus-circle"></i> Agregar Primer Miembro
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th width="80">Foto</th>
                                    <th>Nombre</th>
                                    <th>Cargo</th>
                                    <th width="100">Orden</th>
                                    <th width="100">Estado</th>
                                    <th width="150">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($equipo as $miembro): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($miembro['imagen'])): 
                                            $ruta_foto = '/heldyn/centeno/uploads/quienes-somos/equipo/' . $miembro['imagen'];
                                        ?>
                                            <img src="<?php echo $ruta_foto; ?>" 
                                                 alt="<?php echo htmlspecialchars($miembro['nombre']); ?>"
                                                 class="team-img"
                                                 onerror="this.src='https://placehold.co/80x80/1a4b8c/white?text=Usuario'">
                                        <?php else: ?>
                                            <img src="https://placehold.co/80x80/1a4b8c/white?text=Usuario" 
                                                 alt="Sin foto" 
                                                 class="team-img">
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($miembro['nombre']); ?></strong>
                                        <?php if (!empty($miembro['email'])): ?>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($miembro['email']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($miembro['cargo']); ?></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <button onclick="cambiarOrden(<?php echo $miembro['id']; ?>, 'subir', '<?php echo addslashes($miembro['nombre']); ?>')" 
                                                    class="btn btn-sm btn-outline-secondary" title="Subir">
                                                <i class="fas fa-arrow-up"></i>
                                            </button>
                                            <span class="fw-bold"><?php echo $miembro['orden']; ?></span>
                                            <button onclick="cambiarOrden(<?php echo $miembro['id']; ?>, 'bajar', '<?php echo addslashes($miembro['nombre']); ?>')" 
                                                    class="btn btn-sm btn-outline-secondary" title="Bajar">
                                                <i class="fas fa-arrow-down"></i>
                                            </button>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge rounded-pill <?php echo $miembro['activo'] ? 'badge-activo' : 'badge-inactivo'; ?>"
                                              onclick="toggleEstadoEquipo(<?php echo $miembro['id']; ?>, <?php echo $miembro['activo']; ?>, '<?php echo addslashes($miembro['nombre']); ?>')">
                                            <?php echo $miembro['activo'] ? 'Activo' : 'Inactivo'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="equipo_editar.php?id=<?php echo $miembro['id']; ?>" 
                                               class="btn btn-warning btn-sm" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button onclick="confirmarEliminarEquipo(<?php echo $miembro['id']; ?>, '<?php echo addslashes($miembro['nombre']); ?>')" 
                                                    class="btn btn-danger btn-sm" title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- TAB 3: FOOTER EDITABLE -->
            <div class="tab-pane fade" id="footer" role="tabpanel">
                <h3 class="section-title">Pie de Página </h3>
                <p class="text-muted mb-4">Edita la información que aparece en el pie de página de todas las páginas del portal.</p>
                
                <form method="POST" action="guardar_footer.php" id="formFooter">
                    <div class="info-box mb-4">
                        <i class="fas fa-info-circle"></i>
                        <strong>Información importante:</strong> Los cambios se reflejarán en todas las páginas del portal.
                        Usa <span class="variable-tag">[año]</span> para que se reemplace automáticamente por el año actual.
                    </div>
                    
                    <div class="footer-form-section">
                        <h5 class="mb-3"><i class="fas fa-align-left"></i> Sección Izquierda</h5>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="titulo_izquierda" class="form-label">Título Izquierdo <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="titulo_izquierda" name="titulo_izquierda" 
                                           value="<?php echo htmlspecialchars($footer_config['titulo_izquierda'] ?? 'Portal Escolar'); ?>"
                                           required maxlength="200">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="direccion" class="form-label">Dirección Completa</label>
                                    <textarea class="form-control" id="direccion" name="direccion" rows="3"><?php echo htmlspecialchars($footer_config['direccion'] ?? 'Calle Principal #123, Colonia Centro. Código Postal 12345'); ?></textarea>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="email" class="form-label">Correo Electrónico</label>
                                            <input type="email" class="form-control" id="email" name="email" 
                                                   value="<?php echo htmlspecialchars($footer_config['email'] ?? 'info@colegioejemplo.edu'); ?>"
                                                   placeholder="ejemplo@escuela.edu">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="telefono" class="form-label">Teléfono</label>
                                            <input type="text" class="form-control" id="telefono" name="telefono" 
                                                   value="<?php echo htmlspecialchars($footer_config['telefono'] ?? '+123 456 7890'); ?>"
                                                   placeholder="Ej: +51 123 456 789">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="footer-form-section">
                        <h5 class="mb-3"><i class="fas fa-align-right"></i> Sección Derecha</h5>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="titulo_derecha" class="form-label">Título Derecho</label>
                                    <input type="text" class="form-control" id="titulo_derecha" name="titulo_derecha" 
                                           value="<?php echo htmlspecialchars($footer_config['titulo_derecha'] ?? 'Portal Escolar'); ?>"
                                           maxlength="200">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="descripcion_derecha" class="form-label">Descripción</label>
                                    <textarea class="form-control" id="descripcion_derecha" name="descripcion_derecha" rows="5"><?php echo htmlspecialchars($footer_config['descripcion_derecha'] ?? 'El Portal Escolar es la plataforma oficial de comunicación e información educativa, dedicada a promover la innovación y el desarrollo integral en el ámbito educativo. Nuestro compromiso es brindar recursos, herramientas y contenidos de calidad para fortalecer el proceso de enseñanza-aprendizaje de nuestra comunidad educativa.'); ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="footer-form-section">
                        <h5 class="mb-3"><i class="fas fa-layer-group"></i> Sección Inferior</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="derechos_autor" class="form-label">Texto de Derechos de Autor</label>
                                    <input type="text" class="form-control" id="derechos_autor" name="derechos_autor" 
                                           value="<?php echo htmlspecialchars($footer_config['derechos_autor'] ?? '© [año] Portal Escolar | Institución Educativa'); ?>"
                                           placeholder="Ej: © [año] Nombre de la Institución">
                                    <small class="text-muted">Usa <span class="variable-tag">[año]</span> para el año actual</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="creditos" class="form-label">Texto de Créditos</label>
                                    <input type="text" class="form-control" id="creditos" name="creditos" 
                                           value="<?php echo htmlspecialchars($footer_config['creditos'] ?? 'Desarrollado para la comunidad educativa'); ?>"
                                           placeholder="Ej: Desarrollado por...">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Vista previa del footer -->
                    <div class="mt-4">
                        <h5 class="mb-3"><i class="fas fa-eye"></i> Vista Previa del pie de página</h5>
                        <div class="preview-footer">
                            <div class="row">
                                <div class="col-md-6">
                                    <h5 id="previewTituloIzquierda"><?php echo htmlspecialchars($footer_config['titulo_izquierda'] ?? 'Portal Escolar'); ?></h5>
                                    <div id="previewDatosContacto">
                                        <?php if (!empty($footer_config['direccion'])): ?>
                                            <p><strong>Dirección:</strong> <?php echo nl2br(htmlspecialchars($footer_config['direccion'])); ?></p>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($footer_config['email'])): ?>
                                            <p><strong>Contacto:</strong> <a href="mailto:<?php echo htmlspecialchars($footer_config['email']); ?>"><?php echo htmlspecialchars($footer_config['email']); ?></a></p>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($footer_config['telefono'])): ?>
                                            <p><strong>Teléfono:</strong> <a href="tel:<?php echo htmlspecialchars($footer_config['telefono']); ?>"><?php echo htmlspecialchars($footer_config['telefono']); ?></a></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h5 id="previewTituloDerecha"><?php echo htmlspecialchars($footer_config['titulo_derecha'] ?? 'Portal Escolar'); ?></h5>
                                    <div id="previewDescripcion">
                                        <p><?php echo nl2br(htmlspecialchars($footer_config['descripcion_derecha'] ?? 'Descripción del portal escolar...')); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="footer-bottom-preview">
                            <div class="row">
                                <div class="col-md-6">
                                    <span id="previewDerechos">
                                        <?php 
                                        $derechos = str_replace('[año]', date('Y'), $footer_config['derechos_autor'] ?? '© [año] Portal Escolar | Institución Educativa');
                                        echo htmlspecialchars($derechos);
                                        ?>
                                    </span>
                                </div>
                                <div class="col-md-6 text-end">
                                    <span id="previewCreditos"><?php echo htmlspecialchars($footer_config['creditos'] ?? 'Desarrollado para la comunidad educativa'); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between mt-4">
                        <a href="../index.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Volver al Panel
                        </a>
                        <button type="submit" class="btn btn-primary" id="btn-guardar-footer">
                            <i class="fas fa-rocket"></i> Actualizar Footer
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- TAB 4: Vista Previa -->
            <div class="tab-pane fade" id="vista" role="tabpanel">
                <h3 class="section-title">Vista Previa</h3>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Esta es una vista previa de cómo se verá la información en el sitio web.
                </div>
                
                <div class="card">
                    <div class="card-body">
                        <!-- Vista previa similar a la página real -->
                        <h2 class="text-primary mb-4"><?php echo htmlspecialchars($quienes_somos['titulo'] ?? 'Quiénes Somos'); ?></h2>
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <?php if (!empty($quienes_somos['imagen_principal'])): 
                                    $ruta_vista = '/heldyn/centeno/uploads/quienes-somos/' . $quienes_somos['imagen_principal'];
                                ?>
                                    <img src="<?php echo $ruta_vista; ?>" 
                                         class="img-fluid rounded mb-3" 
                                         alt="<?php echo htmlspecialchars($quienes_somos['imagen_principal_alt'] ?? ''); ?>"
                                         style="max-height: 300px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 300px;">
                                        <i class="fas fa-image fa-3x text-muted"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <div class="content-preview">
                                    <?php echo $quienes_somos['contenido'] ?? '<p class="text-muted">No hay contenido aún...</p>'; ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Misión y Visión -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card h-100">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="mb-0"><i class="fas fa-bullseye"></i> Misión</h5>
                                    </div>
                                    <div class="card-body">
                                        <?php if (!empty($quienes_somos['mision'])): ?>
                                            <p><?php echo nl2br(htmlspecialchars($quienes_somos['mision'])); ?></p>
                                        <?php else: ?>
                                            <p class="text-muted">No se ha definido la misión</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card h-100">
                                    <div class="card-header bg-info text-white">
                                        <h5 class="mb-0"><i class="fas fa-eye"></i> Visión</h5>
                                    </div>
                                    <div class="card-body">
                                        <?php if (!empty($quienes_somos['vision'])): ?>
                                            <p><?php echo nl2br(htmlspecialchars($quienes_somos['vision'])); ?></p>
                                        <?php else: ?>
                                            <p class="text-muted">No se ha definido la visión</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Valores -->
                        <?php if (!empty($quienes_somos['valores'])): ?>
                            <div class="card mb-4">
                                <div class="card-header bg-success text-white">
                                    <h5 class="mb-0"><i class="fas fa-heart"></i> Valores</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <?php 
                                        $valores = explode(',', $quienes_somos['valores']);
                                        foreach ($valores as $valor): 
                                            $valor_limpio = trim($valor);
                                            if (!empty($valor_limpio)):
                                        ?>
                                            <div class="col-md-3 mb-2">
                                                <div class="border rounded p-3 text-center">
                                                    <i class="fas fa-check-circle text-success mb-2"></i>
                                                    <h6 class="mb-0"><?php echo htmlspecialchars($valor_limpio); ?></h6>
                                                </div>
                                            </div>
                                        <?php 
                                            endif;
                                        endforeach; 
                                        ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Equipo -->
                        <h4 class="mt-5 mb-3">Equipo Directivo</h4>
                        <div class="row">
                            <?php if (!empty($equipo)): 
                                $equipo_activos = array_filter($equipo, function($e) { return $e['activo']; });
                            ?>
                                <?php foreach (array_slice($equipo_activos, 0, 3) as $miembro): ?>
                                <div class="col-md-4 mb-4">
                                    <div class="card text-center h-100">
                                        <div class="card-body">
                                            <?php if (!empty($miembro['imagen'])): 
                                                $ruta_foto_vista = '/heldyn/centeno/uploads/quienes-somos/equipo/' . $miembro['imagen'];
                                            ?>
                                                <img src="<?php echo $ruta_foto_vista; ?>" 
                                                     class="rounded-circle mb-3" 
                                                     alt="<?php echo htmlspecialchars($miembro['nombre']); ?>"
                                                     style="width: 120px; height: 120px; object-fit: cover; border: 3px solid var(--primary-color);">
                                            <?php else: ?>
                                                <div class="rounded-circle mb-3 mx-auto d-flex align-items-center justify-content-center" 
                                                     style="width: 120px; height: 120px; background: #e9ecef; color: #6c757d; border: 3px solid var(--primary-color);">
                                                    <i class="fas fa-user fa-3x"></i>
                                                </div>
                                            <?php endif; ?>
                                            <h5><?php echo htmlspecialchars($miembro['nombre']); ?></h5>
                                            <h6 class="text-muted"><?php echo htmlspecialchars($miembro['cargo']); ?></h6>
                                            <p class="small"><?php echo htmlspecialchars(substr($miembro['descripcion'] ?? '', 0, 100)); ?>...</p>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="col-12">
                                    <p class="text-muted text-center">No hay miembros del equipo aún</p>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Vista previa del footer -->
                        <h4 class="mt-5 mb-3">Pie de Página</h4>
                        <div class="preview-footer mt-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <h5><?php echo htmlspecialchars($footer_config['titulo_izquierda'] ?? 'Portal Escolar'); ?></h5>
                                    <div>
                                        <?php if (!empty($footer_config['direccion'])): ?>
                                            <p><strong>Dirección:</strong> <?php echo nl2br(htmlspecialchars($footer_config['direccion'])); ?></p>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($footer_config['email'])): ?>
                                            <p><strong>Contacto:</strong> <a href="mailto:<?php echo htmlspecialchars($footer_config['email']); ?>"><?php echo htmlspecialchars($footer_config['email']); ?></a></p>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($footer_config['telefono'])): ?>
                                            <p><strong>Teléfono:</strong> <a href="tel:<?php echo htmlspecialchars($footer_config['telefono']); ?>"><?php echo htmlspecialchars($footer_config['telefono']); ?></a></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h5><?php echo htmlspecialchars($footer_config['titulo_derecha'] ?? 'Portal Escolar'); ?></h5>
                                    <div>
                                        <p><?php echo nl2br(htmlspecialchars($footer_config['descripcion_derecha'] ?? 'Descripción del portal escolar...')); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="footer-bottom-preview">
                            <div class="row">
                                <div class="col-md-6">
                                    <?php 
                                    $derechos = str_replace('[año]', date('Y'), $footer_config['derechos_autor'] ?? '© [año] Portal Escolar | Institución Educativa');
                                    echo htmlspecialchars($derechos);
                                    ?>
                                </div>
                                <div class="col-md-6 text-end">
                                    <?php echo htmlspecialchars($footer_config['creditos'] ?? 'Desarrollado para la comunidad educativa'); ?>
                                </div>
                            </div>
                        </div>
                        
                        
        <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery (NECESARIO PARA SUMMERNOTE Y SWEETALERT2) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Summernote JS -->
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/lang/summernote-es-ES.min.js"></script>
    
    <script>
        // Inicializar Summernote
        $(document).ready(function() {
            $('#contenido').summernote({
                height: 300,
                lang: 'es-ES',
                toolbar: [
                    ['style', ['bold', 'italic', 'underline', 'clear']],
                    ['font', ['strikethrough', 'superscript', 'subscript']],
                    ['fontsize', ['fontsize']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['height', ['height']],
                    ['insert', ['link', 'picture', 'video']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ]
            });
            
            $('#mision, #vision').summernote({
                height: 150,
                lang: 'es-ES',
                toolbar: [
                    ['style', ['bold', 'italic', 'underline']],
                    ['para', ['ul', 'ol']],
                    ['view', ['fullscreen']]
                ]
            });
        });
        
        // ==============================================
        // MOSTRAR MENSAJES DEL BACKEND CON SWEETALERT2
        // ==============================================
        document.addEventListener('DOMContentLoaded', function() {
            <?php if (!empty($mensaje)): ?>
                const mensaje = `<?php echo addslashes($mensaje); ?>`;
                const tipo = '<?php echo $mensaje_tipo; ?>';
                
                // Solo mostrar si hay mensaje y no es vacío
                if (mensaje && mensaje.trim() !== '') {
                    // Configurar según el tipo de mensaje
                    let config = {
                        title: 'Información',
                        html: mensaje,
                        timer: 4000,
                        timerProgressBar: true,
                        showConfirmButton: true,
                        position: 'center',
                        toast: false,
                        showClass: {
                            popup: 'animate__animated animate__fadeInDown'
                        },
                        hideClass: {
                            popup: 'animate__animated animate__fadeOutUp'
                        }
                    };
                    
                    // Personalizar según el tipo
                    switch(tipo) {
                        case 'error':
                            config.icon = 'error';
                            config.title = '❌ Error';
                            config.background = '#f8d7da';
                            config.color = '#721c24';
                            config.confirmButtonColor = '#dc3545';
                            break;
                        case 'warning':
                            config.icon = 'warning';
                            config.title = '⚠️ Advertencia';
                            config.background = '#fff3cd';
                            config.color = '#856404';
                            config.confirmButtonColor = '#ffc107';
                            break;
                        case 'success':
                            config.icon = 'success';
                            config.title = '✅ Éxito';
                            config.background = '#d4edda';
                            config.color = '#155724';
                            config.confirmButtonColor = '#28a745';
                            break;
                        default:
                            config.icon = 'info';
                            config.title = 'ℹ️ Información';
                            config.background = '#d1ecf1';
                            config.color = '#0c5460';
                            config.confirmButtonColor = '#17a2b8';
                    }
                    
                    // Mostrar SweetAlert
                    Swal.fire(config);
                }
            <?php endif; ?>
            
            // Actualizar vista previa del footer en tiempo real
            const formFooter = document.getElementById('formFooter');
            if (formFooter) {
                const inputs = formFooter.querySelectorAll('input, textarea');
                
                inputs.forEach(input => {
                    input.addEventListener('input', function() {
                        updateFooterPreview();
                    });
                });
                
                // Inicializar vista previa
                updateFooterPreview();
            }
        });
        
        // ==============================================
        // FUNCIÓN PARA ACTUALIZAR VISTA PREVIA DEL FOOTER
        // ==============================================
        function updateFooterPreview() {
            const tituloIzquierda = document.getElementById('titulo_izquierda')?.value || 'Portal Escolar';
            const direccion = document.getElementById('direccion')?.value || '';
            const email = document.getElementById('email')?.value || '';
            const telefono = document.getElementById('telefono')?.value || '';
            const tituloDerecha = document.getElementById('titulo_derecha')?.value || 'Portal Escolar';
            const descripcion = document.getElementById('descripcion_derecha')?.value || '';
            const derechos = document.getElementById('derechos_autor')?.value || '© [año] Portal Escolar | Institución Educativa';
            const creditos = document.getElementById('creditos')?.value || 'Desarrollado para la comunidad educativa';
            
            // Actualizar vista previa
            const previewTituloIzquierda = document.getElementById('previewTituloIzquierda');
            const previewTituloDerecha = document.getElementById('previewTituloDerecha');
            const previewDescripcion = document.getElementById('previewDescripcion');
            const previewDerechos = document.getElementById('previewDerechos');
            const previewCreditos = document.getElementById('previewCreditos');
            const previewDatosContacto = document.getElementById('previewDatosContacto');
            
            if (previewTituloIzquierda) previewTituloIzquierda.textContent = tituloIzquierda;
            if (previewTituloDerecha) previewTituloDerecha.textContent = tituloDerecha;
            if (previewDescripcion) previewDescripcion.innerHTML = `<p>${descripcion.replace(/\n/g, '<br>')}</p>`;
            
            // Reemplazar [año] por el año actual
            const derechosActual = derechos.replace('[año]', new Date().getFullYear());
            if (previewDerechos) previewDerechos.textContent = derechosActual;
            if (previewCreditos) previewCreditos.textContent = creditos;
            
            // Actualizar datos de contacto
            if (previewDatosContacto) {
                let html = '';
                if (direccion) html += `<p><strong>Dirección:</strong> ${direccion.replace(/\n/g, '<br>')}</p>`;
                if (email) html += `<p><strong>Contacto:</strong> <a href="mailto:${email}">${email}</a></p>`;
                if (telefono) html += `<p><strong>Teléfono:</strong> <a href="tel:${telefono}">${telefono}</a></p>`;
                previewDatosContacto.innerHTML = html;
            }
        }
        
        // ==============================================
        // VALIDACIÓN Y CONFIRMACIÓN MEJORADA PARA QUIÉNES SOMOS
        // ==============================================
        document.getElementById('formInfoPrincipal')?.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const form = this;
            const titulo = document.getElementById('titulo').value.trim();
            
            // Validación básica
            if (!titulo) {
                Swal.fire({
                    title: 'Título requerido',
                    text: 'Debes ingresar un título para la página.',
                    icon: 'warning',
                    confirmButtonColor: '#1a4b8c',
                    willClose: () => {
                        document.getElementById('titulo').focus();
                    }
                });
                return;
            }
            
            // Verificar imagen seleccionada
            const imagenInput = document.getElementById('imagen_principal');
            let tieneImagenNueva = false;
            let nombreImagen = '';
            let imagenError = '';
            
            if (imagenInput && imagenInput.files.length > 0) {
                tieneImagenNueva = true;
                nombreImagen = imagenInput.files[0].name;
                const file = imagenInput.files[0];
                const fileSize = file.size / 1024 / 1024; // en MB
                const fileExt = nombreImagen.split('.').pop().toLowerCase();
                const allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                
                // Validar extensión
                if (!allowedExt.includes(fileExt)) {
                    imagenError = 'Formato no válido. Solo JPG, PNG, GIF o WEBP.';
                }
                
                // Validar tamaño (5MB)
                if (fileSize > 5) {
                    imagenError = 'La imagen es muy pesada (máx. 5MB).';
                }
            }
            
            // Si hay error en la imagen, mostrar alerta
            if (imagenError) {
                Swal.fire({
                    title: 'Error en la imagen',
                    text: imagenError,
                    icon: 'error',
                    confirmButtonColor: '#1a4b8c'
                });
                return;
            }
            
            // Obtener valores para mostrar en la confirmación
            const contenido = document.getElementById('contenido').value.trim();
            const mision = document.getElementById('mision').value.trim();
            const vision = document.getElementById('vision').value.trim();
            const valores = document.getElementById('valores').value.trim();
            
            // Crear mensaje de confirmación mejorado
            let confirmMessage = `
                <div class="text-start">
                    <div class="card border-0 bg-light mb-3">
                        <div class="card-body">
                            <h6 class="card-title text-primary mb-2">
                                <i class="fas fa-check-circle me-2"></i>Confirmar Cambios
                            </h6>
                            <p class="mb-2"><strong>Título:</strong> ${titulo}</p>
                            
                            ${contenido.length > 0 ? `
                                <p class="mb-2">
                                    <strong>Contenido:</strong> 
                                    <span class="text-muted">${contenido.length > 50 ? contenido.substring(0, 50) + '...' : contenido}</span>
                                </p>
                            ` : ''}
                            
                            ${tieneImagenNueva ? `
                                <div class="alert alert-warning p-2 mb-2">
                                    <i class="fas fa-image me-2"></i>
                                    <strong>Nueva imagen:</strong> ${nombreImagen}
                                </div>
                            ` : ''}
                            
                            <hr class="my-2">
                            
                            <div class="row small text-muted">
                                <div class="col-6">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Los cambios se publicarán inmediatamente
                                </div>
                                <div class="col-6 text-end">
                                    <i class="fas fa-clock me-1"></i>
                                    ${new Date().toLocaleString()}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Mostrar confirmación mejorada con SweetAlert2
            Swal.fire({
                title: '¿Guardar cambios?',
                html: confirmMessage,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#1a4b8c',
                cancelButtonColor: '#6c757d',
                confirmButtonText: `
                    <div class="d-flex align-items-center justify-content-center">
                        <i class="fas fa-save me-2"></i>
                        Guardar Cambios
                    </div>
                `,
                cancelButtonText: `
                    <div class="d-flex align-items-center justify-content-center">
                        <i class="fas fa-times me-2"></i>
                        Cancelar
                    </div>
                `,
                width: '600px',
                backdrop: true,
                allowOutsideClick: false,
                showClass: {
                    popup: 'animate__animated animate__fadeInDown'
                },
                hideClass: {
                    popup: 'animate__animated animate__fadeOutUp'
                },
                customClass: {
                    popup: 'shadow-lg rounded-3',
                    confirmButton: 'btn-lg',
                    cancelButton: 'btn-lg'
                },
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return new Promise((resolve) => {
                        // Enviar formulario mediante AJAX
                        const formData = new FormData(form);
                        
                        fetch('guardar_info.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.text())
                        .then(() => {
                            // Mostrar mensaje de éxito espectacular
                            Swal.fire({
                                title: '<i class="fas fa-check-circle text-success fa-3x mb-3"></i>',
                                html: `
                                    <div class="text-center">
                                        <h4 class="text-success mb-3">¡Guardado Exitosamente!</h4>
                                        <p class="mb-4">La información se ha actualizado correctamente.</p>
                                        <div class="alert alert-success">
                                            <i class="fas fa-sync-alt me-2"></i>
                                            <strong>Cambios aplicados</strong>
                                        </div>
                                    </div>
                                `,
                                icon: 'success',
                                confirmButtonColor: '#28a745',
                                timer: 3000,
                                timerProgressBar: true,
                                showConfirmButton: false,
                                willClose: () => {
                                    // Recargar la página manteniendo el hash
                                    window.location.href = 'index.php#info';
                                }
                            });
                            resolve();
                        })
                        .catch(error => {
                            Swal.fire({
                                title: 'Error',
                                text: 'Hubo un problema al guardar: ' + error,
                                icon: 'error',
                                confirmButtonColor: '#dc3545'
                            });
                            resolve(false);
                        });
                    });
                }
            }).then((result) => {
                if (result.dismiss === Swal.DismissReason.cancel) {
                    Swal.fire({
                        title: 'Cambios cancelados',
                        text: 'No se realizaron modificaciones',
                        icon: 'info',
                        confirmButtonColor: '#6c757d',
                        timer: 1500,
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false
                    });
                }
            });
        });
        
        // ==============================================
        // PREVISUALIZACIÓN MEJORADA DE IMAGEN
        // ==============================================
        document.getElementById('imagen_principal')?.addEventListener('change', function(e) {
            const file = e.target.files[0];
            const preview = document.getElementById('current-image-preview');
            const progress = document.getElementById('image-upload-progress');
            
            if (file) {
                const fileSize = file.size / 1024 / 1024; // en MB
                const fileName = file.name;
                const fileExt = fileName.split('.').pop().toLowerCase();
                const allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                
                // Validar extensión
                if (!allowedExt.includes(fileExt)) {
                    Swal.fire({
                        title: 'Formato no compatible',
                        html: `
                            <div class="text-start">
                                <p>El archivo <strong>${fileName}</strong> no es compatible.</p>
                                <div class="alert alert-warning p-2">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Formatos aceptados:</strong> JPG, PNG, GIF, WEBP
                                </div>
                            </div>
                        `,
                        icon: 'error',
                        confirmButtonColor: '#dc3545'
                    });
                    this.value = ''; // Limpiar input
                    return;
                }
                
                // Validar tamaño
                if (fileSize > 5) {
                    Swal.fire({
                        title: 'Imagen demasiado pesada',
                        html: `
                            <div class="text-start">
                                <p>La imagen <strong>${fileName}</strong> es muy grande.</p>
                                <div class="alert alert-warning p-2">
                                    <i class="fas fa-weight-hanging me-2"></i>
                                    <strong>Tamaño:</strong> ${fileSize.toFixed(2)} MB<br>
                                    <strong>Máximo permitido:</strong> 5 MB
                                </div>
                                <p class="small text-muted">Recomendación: Comprime la imagen antes de subirla.</p>
                            </div>
                        `,
                        icon: 'warning',
                        confirmButtonColor: '#ffc107'
                    });
                    this.value = ''; // Limpiar input
                    return;
                }
                
                // Mostrar progreso
                if (progress) {
                    progress.classList.remove('d-none');
                }
                
                // Mostrar previsualización
                const reader = new FileReader();
                reader.onload = function(e) {
                    // Animación de cambio de imagen
                    preview.style.opacity = '0';
                    setTimeout(() => {
                        preview.src = e.target.result;
                        preview.style.transition = 'opacity 0.5s ease';
                        preview.style.opacity = '1';
                        
                        // Ocultar progreso
                        if (progress) {
                            setTimeout(() => {
                                progress.classList.add('d-none');
                            }, 500);
                        }
                        
                        // Mostrar mensaje de vista previa
                        Swal.fire({
                            title: 'Vista previa activada',
                            text: 'La nueva imagen se mostrará en la vista previa',
                            icon: 'success',
                            confirmButtonColor: '#1a4b8c',
                            timer: 1500,
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false
                        });
                    }, 300);
                }
                reader.readAsDataURL(file);
            }
        });
        
        // ==============================================
        // MEJORAS PARA LA SECCIÓN DE FOOTER
        // ==============================================
        document.getElementById('formFooter')?.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const form = this;
            const tituloIzquierda = document.getElementById('titulo_izquierda').value.trim();
            
            if (!tituloIzquierda) {
                Swal.fire({
                    title: 'Título requerido',
                    text: 'El título izquierdo es obligatorio',
                    icon: 'warning',
                    confirmButtonColor: '#1a4b8c',
                    willClose: () => {
                        document.getElementById('titulo_izquierda').focus();
                    }
                });
                return;
            }
            
            // Obtener valores para mostrar en la confirmación
            const email = document.getElementById('email').value.trim();
            const telefono = document.getElementById('telefono').value.trim();
            const tituloDerecha = document.getElementById('titulo_derecha').value.trim();
            const direccion = document.getElementById('direccion').value.trim();
            const descripcion = document.getElementById('descripcion_derecha').value.trim();
            
            // Crear mensaje de confirmación profesional
            let confirmMessage = `
                <div class="text-start">
                    <div class="card border-0 bg-primary text-white mb-3">
                        <div class="card-body">
                            <h6 class="card-title mb-2">
                                <i class="fas fa-shoe-prints me-2"></i>Confirmar Cambios en Footer
                            </h6>
                            <p class="mb-1 small">Los cambios afectarán todas las páginas del portal</p>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card border-primary mb-3">
                                <div class="card-header bg-transparent border-primary">
                                    <h6 class="mb-0"><i class="fas fa-align-left me-2"></i>Sección Izquierda</h6>
                                </div>
                                <div class="card-body">
                                    <p class="mb-1"><strong>Título:</strong> ${tituloIzquierda}</p>
                                    ${direccion ? `<p class="mb-1"><strong>Dirección:</strong><br><small>${direccion.substring(0, 50)}${direccion.length > 50 ? '...' : ''}</small></p>` : ''}
                                    ${email ? `<p class="mb-1"><strong>Email:</strong> ${email}</p>` : ''}
                                    ${telefono ? `<p class="mb-1"><strong>Teléfono:</strong> ${telefono}</p>` : ''}
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card border-info mb-3">
                                <div class="card-header bg-transparent border-info">
                                    <h6 class="mb-0"><i class="fas fa-align-right me-2"></i>Sección Derecha</h6>
                                </div>
                                <div class="card-body">
                                    ${tituloDerecha ? `<p class="mb-1"><strong>Título:</strong> ${tituloDerecha}</p>` : ''}
                                    ${descripcion ? `<p class="mb-1"><strong>Descripción:</strong><br><small>${descripcion.substring(0, 60)}${descripcion.length > 60 ? '...' : ''}</small></p>` : ''}
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-info p-3">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-info-circle fa-2x me-3"></i>
                            <div>
                                <strong class="d-block">Impacto de los cambios:</strong>
                                <small>El pie de página se actualizará en todas las páginas del portal inmediatamente.</small>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Mostrar confirmación profesional
            Swal.fire({
                title: '<span class="text-primary">¿Actualizar Footer?</span>',
                html: confirmMessage,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#1a4b8c',
                cancelButtonColor: '#6c757d',
                confirmButtonText: `
                    <div class="d-flex align-items-center justify-content-center">
                        <i class="fas fa-rocket me-2"></i>
                        Sí, Actualizar Todo
                    </div>
                `,
                cancelButtonText: `
                    <div class="d-flex align-items-center justify-content-center">
                        <i class="fas fa-ban me-2"></i>
                        Cancelar
                    </div>
                `,
                width: '750px',
                backdrop: 'rgba(0,0,0,0.7)',
                allowOutsideClick: false,
                showClass: {
                    popup: 'animate__animated animate__zoomIn'
                },
                hideClass: {
                    popup: 'animate__animated animate__zoomOut'
                },
                customClass: {
                    popup: 'shadow-lg border-primary',
                    header: 'pb-0',
                    confirmButton: 'btn-lg px-4',
                    cancelButton: 'btn-lg px-4'
                },
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return new Promise((resolve) => {
                        const formData = new FormData(form);
                        
                        fetch('guardar_footer.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.text())
                        .then(() => {
                            // Mostrar éxito espectacular
                            Swal.fire({
                                title: '<i class="fas fa-check-circle text-success fa-3x mb-3"></i>',
                                html: `
                                    <div class="text-center">
                                        <h4 class="text-success mb-3">¡Footer Actualizado!</h4>
                                        <p class="mb-4">Los cambios se han aplicado exitosamente a todas las páginas.</p>
                                        <div class="alert alert-success">
                                            <i class="fas fa-sync-alt me-2"></i>
                                            <strong>Sincronización completa</strong>
                                        </div>
                                    </div>
                                `,
                                icon: 'success',
                                confirmButtonColor: '#28a745',
                                timer: 3000,
                                timerProgressBar: true,
                                showConfirmButton: false,
                                willClose: () => {
                                    window.location.href = 'index.php#footer';
                                }
                            });
                            resolve();
                        })
                        .catch(error => {
                            Swal.fire({
                                title: 'Error de Actualización',
                                text: 'Hubo un problema: ' + error,
                                icon: 'error',
                                confirmButtonColor: '#dc3545'
                            });
                            resolve(false);
                        });
                    });
                }
            }).then((result) => {
                if (result.dismiss === Swal.DismissReason.cancel) {
                    Swal.fire({
                        title: 'Operación Cancelada',
                        text: 'El footer permanece sin cambios',
                        icon: 'info',
                        confirmButtonColor: '#6c757d',
                        timer: 1500,
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false
                    });
                }
            });
        });
        
        // ==============================================
        // FUNCIONES PARA EQUIPO DIRECTO
        // ==============================================
        
        function toggleEstadoEquipo(id, estadoActual, nombre) {
            const nuevoEstado = estadoActual ? 'inactivar' : 'activar';
            
            Swal.fire({
                title: '¿Cambiar estado?',
                html: `¿Estás seguro de ${nuevoEstado} a <strong>${nombre}</strong>?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: estadoActual ? '#ffc107' : '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: `Sí, ${nuevoEstado}`,
                cancelButtonText: 'Cancelar',
                showLoaderOnConfirm: false,
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    // Redirigir manteniendo la pestaña
                    window.location.href = `equipo_estado.php?id=${id}&hash=equipo`;
                }
            });
        }
        
        function confirmarEliminarEquipo(id, nombre) {
            Swal.fire({
                title: '¿Eliminar miembro?',
                html: `¿Estás seguro de eliminar a <strong>${nombre}</strong>?<br>
                      Esta acción también eliminará su foto si existe.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                showLoaderOnConfirm: false,
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    // Redirigir manteniendo la pestaña
                    window.location.href = `equipo_eliminar.php?id=${id}&hash=equipo`;
                }
            });
        }
        
        function cambiarOrden(id, accion, nombre) {
            Swal.fire({
                title: `¿${accion === 'subir' ? 'Subir' : 'Bajar'} posición?`,
                text: `El miembro "${nombre}" se moverá ${accion === 'subir' ? 'una posición hacia arriba' : 'una posición hacia abajo'}.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#1a4b8c',
                cancelButtonColor: '#6c757d',
                confirmButtonText: `Sí, ${accion === 'subir' ? 'subir' : 'bajar'}`,
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Redirigir manteniendo la pestaña
                    window.location.href = `equipo_orden.php?accion=${accion}&id=${id}&hash=equipo`;
                }
            });
        }
        
        // ==============================================
        // MANTENER POSICIÓN DE PESTAÑAS AL RECARGAR
        // ==============================================
        document.addEventListener('DOMContentLoaded', function() {
            // Activar la pestaña basada en el hash de la URL
            const hash = window.location.hash;
            if (hash) {
                const tab = document.querySelector(`[data-bs-target="${hash}"]`);
                if (tab) {
                    const tabInstance = new bootstrap.Tab(tab);
                    tabInstance.show();
                }
            }
            
            // Manejar clics en las pestañas para actualizar el hash
            document.querySelectorAll('[data-bs-toggle="tab"]').forEach(tab => {
                tab.addEventListener('shown.bs.tab', function(event) {
                    const target = event.target.getAttribute('data-bs-target');
                    if (target) {
                        window.location.hash = target;
                    }
                });
            });
        });
    </script>
</body>
</html>