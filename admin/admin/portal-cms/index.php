<?php
// admin/admin/portal-cms/index.php - VERSIÓN SIN MÓDULO NOTAS NI LOGOUT

// ============================================
// CONFIGURACIÓN DE RUTAS ABSOLUTAS
// ============================================
$base_path = dirname(dirname(dirname(__DIR__))); // Ruta base del proyecto
$base_url = 'http://' . $_SERVER['HTTP_HOST'] . '/sige/centeno/';
$portal_url = $base_url . 'index.php';

// ============================================
// INICIO DE SESIÓN SEGURO
// ============================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============================================
// COMPATIBILIDAD DE SESIÓN
// ============================================
require_once __DIR__ . '/session-compat.php';

// ============================================
// VERIFICACIÓN DE SESIÓN
// ============================================
if (!verificarSesionCMS()) {
    // Redirigir al sistema principal
    header('Location: ' . $portal_url);
    exit;
}

// ============================================
// OBTENER INFORMACIÓN DEL USUARIO
// ============================================
$nombre_usuario = obtenerNombreUsuario();
$email_usuario = isset($_SESSION['sesion_email']) ? $_SESSION['sesion_email'] : 
                (isset($_SESSION['email_sesion']) ? $_SESSION['email_sesion'] : 
                'usuario@dominio.com');

// ============================================
// CONEXIÓN A BASE DE DATOS
// ============================================
$con = new mysqli("localhost", "root", "", "sige");
if ($con->connect_error) {
    die("Error de conexión a la base de datos: " . $con->connect_error);
}

// ============================================
// FUNCIONES DE ESTADÍSTICAS
// ============================================
function contarSeguro($con, $tabla) {
    $result = mysqli_query($con, "SELECT COUNT(*) as total FROM $tabla");
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        return $row['total'] ?? 0;
    }
    return 0;
}

// Obtener estadísticas
$stats = [
    'noticias' => contarSeguro($con, 'tblposts'),
    'carrusel' => contarSeguro($con, 'carrusel'),
    'equipo' => contarSeguro($con, 'equipo_quienes_somos'),
    'social' => contarSeguro($con, 'social_media')
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CMS Portal Escolar - Panel de Administración</title>
    
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
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .admin-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 15px 0;
            margin-bottom: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .card-module {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            height: 100%;
            border-left: 4px solid var(--primary-color);
        }
        
        .card-module:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .module-icon {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 15px;
        }
        
        .btn-admin {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-admin:hover {
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(26, 75, 140, 0.3);
        }
        
        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.05);
            transition: transform 0.2s;
        }
        
        .stats-card:hover {
            transform: translateY(-3px);
        }
        
        .welcome-alert {
            border-left: 4px solid var(--primary-color);
        }
        
        .user-info-badge {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            padding: 5px 15px;
            font-size: 0.85rem;
            margin-left: 10px;
        }
        
        .quick-action-box {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .quick-action-box:hover {
            border-color: var(--primary-color);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        
        .badge-system {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #28a745;
            color: white;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 0.6rem;
        }
        
        .access-badge {
            background: linear-gradient(45deg, #1a4b8c, #2d68c4);
            color: white;
            padding: 3px 10px;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: 500;
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
                        <i class="fas fa-cogs"></i> CMS Portal Escolar
                    </h1>
                    <div class="d-flex align-items-center">
                        <small>
                            <i class="fas fa-user-shield"></i>
                            <strong><?php echo htmlspecialchars($nombre_usuario); ?></strong>
                            <span class="access-badge ms-2">
                                <i class="fas fa-key"></i> Acceso Administrativo
                            </span>
                        </small>
                    </div>
                </div>
                <div>
                    <a href="<?php echo $portal_url; ?>" target="_blank" class="btn btn-light btn-sm">
                        <i class="fas fa-external-link-alt"></i> Ver Portal Público
                    </a>
                </div>
            </div>
        </div>
    </header>
    
    <div class="container">
        <!-- Mensaje de bienvenida -->
        <div class="alert alert-success welcome-alert mb-4">
            <div class="d-flex align-items-center">
                <i class="fas fa-user-shield fa-2x me-3"></i>
                <div>
                    <h5 class="alert-heading mb-1">Panel de Administración - Portal Escolar</h5>
                    <p class="mb-0">
                        Sesión activa como <strong><?php echo htmlspecialchars($nombre_usuario); ?></strong> 
                        <span class="badge bg-primary ms-2">Administrador</span>
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Estadísticas -->
        <h5 class="mb-3"><i class="fas fa-chart-bar text-primary me-2"></i> Estadísticas del Contenido</h5>
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Noticias</h6>
                            <h3 class="mb-0"><?php echo $stats['noticias']; ?></h3>
                        </div>
                        <i class="fas fa-newspaper fa-2x text-primary"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Carrusel</h6>
                            <h3 class="mb-0"><?php echo $stats['carrusel']; ?></h3>
                        </div>
                        <i class="fas fa-images fa-2x text-success"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Miembros</h6>
                            <h3 class="mb-0"><?php echo $stats['equipo']; ?></h3>
                        </div>
                        <i class="fas fa-users fa-2x text-info"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Redes</h6>
                            <h3 class="mb-0"><?php echo $stats['social']; ?></h3>
                        </div>
                        <i class="fas fa-share-alt fa-2x text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Secciones del Portal -->
        <h5 class="mb-3"><i class="fas fa-th-large text-primary me-2"></i> Gestión de Contenido</h5>
        <div class="row">
            <!-- CARRUSEL (Sistema independiente) -->
            <div class="col-md-4 mb-4">
                <div class="card card-module position-relative">
                    <div class="card-body text-center">
                        <span class="badge-system">Completo</span>
                        <div class="module-icon"><i class="fas fa-images"></i></div>
                        <h5 class="card-title">Carrusel Principal</h5>
                        <p class="card-text text-muted small">Imágenes del inicio del portal</p>
                        <a href="carrusel/index.php" class="btn btn-admin">
                            <i class="fas fa-cog me-1"></i> Administrar
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- QUIÉNES SOMOS (Sistema independiente) -->
            <div class="col-md-4 mb-4">
                <div class="card card-module position-relative">
                    <div class="card-body text-center">
                        <span class="badge-system">Completo</span>
                        <div class="module-icon"><i class="fas fa-users"></i></div>
                        <h5 class="card-title">Quiénes Somos</h5>
                        <p class="card-text text-muted small">Equipo y colaboradores</p>
                        <a href="quienes-somos/index.php" class="btn btn-admin">
                            <i class="fas fa-cog me-1"></i> Administrar
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- NOTICIAS (Usar Editor Universal) -->
            <div class="col-md-4 mb-4">
                <div class="card card-module">
                    <div class="card-body text-center">
                        <div class="module-icon"><i class="fas fa-newspaper"></i></div>
                        <h5 class="card-title">Noticias</h5>
                        <p class="card-text text-muted small">Publicaciones del portal</p>
                        <a href="noticias" class="btn btn-admin">
                            <i class="fas fa-edit me-1"></i> Gestionar
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- ACADÉMICO (Usar Editor Universal) -->
            <div class="col-md-4 mb-4">
                <div class="card card-module">
                    <div class="card-body text-center">
                        <div class="module-icon"><i class="fas fa-graduation-cap"></i></div>
                        <h5 class="card-title">Académico</h5>
                        <p class="card-text text-muted small">Recursos académicos</p>
                        <a href="noticias=academico" class="btn btn-admin">
                            <i class="fas fa-edit me-1"></i> Gestionar
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- CALENDARIO (Usar Editor Universal) -->
            <div class="col-md-4 mb-4">
                <div class="card card-module">
                    <div class="card-body text-center">
                        <div class="module-icon"><i class="fas fa-calendar-alt"></i></div>
                        <h5 class="card-title">Calendario</h5>
                        <p class="card-text text-muted small">Eventos y fechas</p>
                        <a href="noticias=calendario" class="btn btn-admin">
                            <i class="fas fa-edit me-1"></i> Gestionar
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- FOOTER (Usar Editor Universal) -->
            <div class="col-md-4 mb-4">
                <div class="card card-module">
                    <div class="card-body text-center">
                        <div class="module-icon"><i class="fas fa-shoe-prints"></i></div>
                        <h5 class="card-title">Pie de Página</h5>
                        <p class="card-text text-muted small">Redes sociales y enlaces</p>
                        <a href="noticias=footer" class="btn btn-admin">
                            <i class="fas fa-edit me-1"></i> Gestionar
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Acceso Rápido -->
        <div class="row mt-5">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="fas fa-bolt text-warning me-2"></i> Acceso Rápido</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <div class="quick-action-box">
                                    <i class="fas fa-plus-circle fa-2x text-primary mb-2"></i>
                                    <h6>Nuevo Slide</h6>
                                    <a href="carrusel/crear.php" class="btn btn-sm btn-admin w-100 mt-2">
                                        Agregar al Carrusel
                                    </a>
                                </div>
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <div class="quick-action-box">
                                    <i class="fas fa-user-plus fa-2x text-success mb-2"></i>
                                    <h6>Nuevo Miembro</h6>
                                    <a href="quienes-somos/equipo_crear.php" class="btn btn-sm btn-admin w-100 mt-2">
                                        Agregar al Equipo
                                    </a>
                                </div>
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <div class="quick-action-box">
                                    <i class="fas fa-newspaper fa-2x text-info mb-2"></i>
                                    <h6>Nueva Noticia</h6>
                                    <a href="editor-universal.php?seccion=noticias" class="btn btn-sm btn-outline-success w-100 mt-2">
                                        Crear Publicación
                                    </a>
                                </div>
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <div class="quick-action-box">
                                    <i class="fas fa-edit fa-2x text-warning mb-2"></i>
                                    <h6>Editor Universal</h6>
                                    <a href="editor-universal.php" class="btn btn-sm btn-outline-primary w-100 mt-2">
                                        Abrir Todas las Secciones
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Información de Sistemas -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="fas fa-info-circle text-primary me-2"></i> Información del Sistema</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6><i class="fas fa-check-circle text-success me-2"></i> Sistemas Completos</h6>
                                <ul class="mb-0">
                                    <li><strong>Carrusel:</strong> Gestión completa de slides con SweetAlert2</li>
                                    <li><strong>Quiénes Somos:</strong> Gestión de equipo y colaboradores</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6><i class="fas fa-edit text-primary me-2"></i> Editor Universal</h6>
                                <ul class="mb-0">
                                    <li><strong>Noticias:</strong> Gestión de publicaciones</li>
                                    <li><strong>Académico:</strong> Recursos y programas educativos</li>
                                    <li><strong>Calendario:</strong> Eventos institucionales</li>
                                    <li><strong>Pie de Página:</strong> Configuración de redes sociales</li>
                                </ul>
                            </div>
                        </div>
                        <hr class="my-3">
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-shield-alt me-2"></i>
                            <strong>Acceso Restringido:</strong> Este panel es exclusivo para administradores del sistema. 
                            Las funciones están integradas en el sistema principal de gestión escolar.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('CMS Portal Escolar - Panel Administrativo');
            console.log('Usuario: <?php echo $nombre_usuario; ?>');
            console.log('Tipo de Acceso: Administrador');
            
            // Verificar que los enlaces funcionen
            function verificarEnlace(url) {
                fetch(url, { method: 'HEAD' })
                    .then(response => {
                        if (!response.ok) {
                            console.warn(`⚠️ Enlace no accesible: ${url}`);
                        }
                    })
                    .catch(error => {
                        console.error(`❌ Error en enlace ${url}:`, error);
                    });
            }
            
            // Verificar enlaces importantes
            verificarEnlace('carrusel/index.php');
            verificarEnlace('quienes-somos/index.php');
            verificarEnlace('editor-universal.php');
        });
    </script>
</body>
</html>