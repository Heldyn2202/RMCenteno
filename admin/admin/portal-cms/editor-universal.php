<?php
// C:\xampp\htdocs\heldyn\centeno\admin\admin\portal-cms\editor-universal.php
session_start();
require_once 'session-compat.php'; // Agrega esta línea

// DIAGNÓSTICO
if (!isset($_SESSION['admin_login'])) {
    // RUTA CORREGIDA para tu estructura
    header('Location: ../../login/login.php');
    exit;
}

$seccion = $_GET['seccion'] ?? 'inicio';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editor Universal - Portal Escolar</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #1a4b8c;
            --secondary-color: #2d68c4;
        }
        
        .editor-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 15px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .nav-tabs-editor {
            border-bottom: 3px solid #dee2e6;
        }
        
        .nav-tabs-editor .nav-link {
            border: none;
            color: #495057;
            font-weight: 500;
            padding: 12px 25px;
            border-radius: 8px 8px 0 0;
            margin-right: 5px;
        }
        
        .nav-tabs-editor .nav-link:hover {
            background-color: #f8f9fa;
        }
        
        .nav-tabs-editor .nav-link.active {
            color: var(--primary-color);
            background-color: white;
            border-bottom: 3px solid var(--primary-color);
        }
        
        .editor-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            padding: 25px;
            margin-bottom: 25px;
            min-height: 400px;
        }
        
        .debug-panel {
            background: #f8f9fa;
            border: 1px dashed #dee2e6;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 0.85rem;
        }
    </style>
</head>
<body>
    <!-- Debug info -->
    <div class="debug-panel">
        <strong>Ruta Editor:</strong> <?php echo __DIR__; ?><br>
        <strong>Sección activa:</strong> <?php echo $seccion; ?>
    </div>
    
    <!-- Header -->
    <header class="editor-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0">
                        <i class="fas fa-edit"></i> Editor Universal
                    </h4>
                    <small class="opacity-75">Modifica todas las secciones del portal desde aquí</small>
                </div>
                <div>
                    <a href="index.php" class="btn btn-light btn-sm me-2">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                    <a href="../../../index.php" target="_blank" class="btn btn-outline-light btn-sm">
                        <i class="fas fa-external-link-alt"></i> Ver Portal
                    </a>
                </div>
            </div>
        </div>
    </header>
    
    <!-- Navegación -->
    <div class="container mt-4">
        <ul class="nav nav-tabs nav-tabs-editor" id="seccionesTab">
            <li class="nav-item">
                <a class="nav-link <?php echo $seccion == 'inicio' ? 'active' : ''; ?>" 
                   href="carrusel/index.php">
                   <i class="fas fa-home me-2"></i>Inicio
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $seccion == 'quienes-somos' ? 'active' : ''; ?>" 
                   href="quienes-somos/index.php">
                   <i class="fas fa-users me-2"></i>Quiénes Somos
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $seccion == 'noticias' ? 'active' : ''; ?>" 
                   href="?seccion=noticias">
                   <i class="fas fa-newspaper me-2"></i>Noticias
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $seccion == 'academico' ? 'active' : ''; ?>" 
                   href="?seccion=academico">
                   <i class="fas fa-graduation-cap me-2"></i>Académico
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $seccion == 'calendario' ? 'active' : ''; ?>" 
                   href="?seccion=calendario">
                   <i class="fas fa-calendar-alt me-2"></i>Calendario
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $seccion == 'footer' ? 'active' : ''; ?>" 
                   href="?seccion=footer">
                   <i class="fas fa-shoe-prints me-2"></i>Pie de Página
                </a>
            </li>
        </ul>
        
        <!-- Contenido dinámico -->
        <div class="editor-card mt-4">
            <?php
            // Mensaje según la sección seleccionada
            switch($seccion) {
                case 'inicio':
                    echo '<h4><i class="fas fa-images text-primary"></i> Carrusel Principal</h4>';
                    echo '<p>Aquí puedes gestionar las imágenes del carrusel de inicio.</p>';
                    echo '<div class="alert alert-info">Próximamente: Formulario para subir imágenes</div>';
                    break;
                    
                case 'quienes-somos':
                    echo '<h4><i class="fas fa-university text-primary"></i> Quiénes Somos</h4>';
                    echo '<p>Edita la información institucional y el equipo directivo.</p>';
                    echo '<div class="alert alert-info">Próximamente: Editor de contenido</div>';
                    break;
                    
                case 'noticias':
                    echo '<h4><i class="fas fa-newspaper text-primary"></i> Gestión de Noticias</h4>';
                    echo '<p>Crea, edita y publica noticias para el portal.</p>';
                    echo '<div class="alert alert-info">Próximamente: Listado y formulario de noticias</div>';
                    break;
                    
                case 'academico':
                    echo '<h4><i class="fas fa-graduation-cap text-primary"></i> Área Académica</h4>';
                    echo '<p>Gestiona horarios, documentos y recursos académicos.</p>';
                    echo '<div class="alert alert-info">Próximamente: Gestor de documentos académicos</div>';
                    break;
                    
                case 'calendario':
                    echo '<h4><i class="fas fa-calendar-alt text-primary"></i> Calendario Escolar</h4>';
                    echo '<p>Administra eventos y fechas importantes.</p>';
                    echo '<div class="alert alert-info">Próximamente: Calendario interactivo</div>';
                    break;
                    
                case 'footer':
                    echo '<h4><i class="fas fa-shoe-prints text-primary"></i> Pie de Página</h4>';
                    echo '<p>Configura redes sociales y información de contacto.</p>';
                    echo '<div class="alert alert-info">Próximamente: Configuración del footer</div>';
                    break;
                    
                default:
                    echo '<h4>Selecciona una sección para editar</h4>';
                    echo '<p>Usa las pestañas de arriba para navegar entre las secciones.</p>';
            }
            ?>
            
            <!-- Formulario de prueba -->
            <div class="mt-4">
                <h5>Prueba de funcionalidad</h5>
                <form id="testForm">
                    <div class="mb-3">
                        <label class="form-label">Título de prueba</label>
                        <input type="text" class="form-control" placeholder="Ej: Nueva imagen del carrusel">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea class="form-control" rows="3" placeholder="Descripción..."></textarea>
                    </div>
                    <button type="button" class="btn btn-primary" onclick="alert('Formulario funcionando!')">
                        <i class="fas fa-save"></i> Guardar prueba
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Botones de navegación -->
        <div class="d-flex justify-content-between mt-4">
            <a href="index.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Volver al Dashboard
            </a>
            
            <div>
                <a href="?seccion=noticias" class="btn btn-outline-primary">
                    <i class="fas fa-newspaper"></i> Ir a Noticias
                </a>
                <a href="?seccion=academico" class="btn btn-outline-success ms-2">
                    <i class="fas fa-graduation-cap"></i> Ir a Académico
                </a>
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        $(document).ready(function() {
            console.log('Editor Universal cargado');
            console.log('Sección activa: <?php echo $seccion; ?>');
            
            // Efecto en pestañas
            $('.nav-link').hover(
                function() { $(this).css('transform', 'translateY(-2px)'); },
                function() { $(this).css('transform', 'translateY(0)'); }
            );
            
            // Cargar contenido de sección
            function cargarSeccion(seccion) {
                console.log('Cargando sección:', seccion);
                // Aquí irá la carga AJAX cuando implementemos las secciones reales
            }
            
            // Detectar cambios en las pestañas
            $('#seccionesTab a').on('click', function() {
                var seccion = $(this).attr('href').split('=')[1];
                cargarSeccion(seccion);
            });
        });
    </script>
</body>
</html>