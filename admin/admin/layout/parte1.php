<?php
// admin/layout/parte1.php
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// RUTA CORRECTA 
$config_path = __DIR__ . '/../../app/config.php';
require_once $config_path;

// Inicializar variables CON VALORES POR DEFECTO
$nombres_sesion_usuario = 'Usuario';
$apellidos_sesion_usuario = 'Sistema';
$rol_sesion_usuario = 'Administrador';
$email_sesion = '';
$id_rol_sesion_usuario = 0;

if(isset($_SESSION['sesion_email'])) {
    $email_sesion = $_SESSION['sesion_email'];
    
    try {
        // PRIMERO: Verificar solo en la tabla usuarios
        $check_user = $pdo->prepare("SELECT * FROM usuarios WHERE email = :email AND estado = '1'");
        $check_user->bindParam(':email', $email_sesion, PDO::PARAM_STR);
        $check_user->execute();
        
        if($check_user->rowCount() == 0) {
            session_destroy();
            header('Location: ' . APP_URL . '/login');
            exit();
        }
        
        // OBTENER DATOS BÁSICOS DEL USUARIO
        $user_basic = $pdo->prepare("SELECT u.id_usuario, u.email, u.rol_id, r.nombre_rol 
                                   FROM usuarios u 
                                   LEFT JOIN roles r ON r.id_rol = u.rol_id 
                                   WHERE u.email = :email");
        $user_basic->bindParam(':email', $email_sesion, PDO::PARAM_STR);
        $user_basic->execute();
        $basic_data = $user_basic->fetch(PDO::FETCH_ASSOC);
        
        if($basic_data) {
            $email_sesion = $basic_data['email'];
            $id_rol_sesion_usuario = $basic_data['rol_id'];
            $rol_sesion_usuario = $basic_data['nombre_rol'] ?? 'Administrador';
            
            // INTENTAR OBTENER DATOS DE PERSONA (si existe la relación)
            try {
                $persona_query = $pdo->prepare("SELECT nombres, apellidos FROM personas WHERE usuario_id = :usuario_id");
                $persona_query->bindParam(':usuario_id', $basic_data['id_usuario'], PDO::PARAM_INT);
                $persona_query->execute();
                $persona_data = $persona_query->fetch(PDO::FETCH_ASSOC);
                
                if($persona_data) {
                    $nombres_sesion_usuario = $persona_data['nombres'] ?? 'Usuario';
                    $apellidos_sesion_usuario = $persona_data['apellidos'] ?? 'Sistema';
                } else {
                    // Si no hay datos en personas, usar el email como nombre
                    $nombres_sesion_usuario = explode('@', $email_sesion)[0];
                    $apellidos_sesion_usuario = '';
                }
                
            } catch (PDOException $e) {
                // Si falla la consulta de personas, usar valores por defecto
                $nombres_sesion_usuario = explode('@', $email_sesion)[0];
                $apellidos_sesion_usuario = '';
            }
            
            // Guardar en sesión
            $_SESSION['rol_sesion_usuario'] = $rol_sesion_usuario;
            $_SESSION['nombres_sesion_usuario'] = $nombres_sesion_usuario;
            $_SESSION['apellidos_sesion_usuario'] = $apellidos_sesion_usuario;
        }

    } catch (PDOException $e) {
        // En caso de error, usar valores por defecto
        $nombres_sesion_usuario = explode('@', $email_sesion)[0];
        $apellidos_sesion_usuario = '';
        $rol_sesion_usuario = 'Usuario';
    }
    
} else {
    header('Location: ' . APP_URL . '/login');
    exit();
}

// Resto del código para permisos...
$url = $_SERVER["PHP_SELF"];
$conta = strlen($url);
$rest = substr($url, 18, $conta);

$sql_roles_permisos = "SELECT * FROM roles_permisos as rolper 
                   INNER JOIN permisos as per ON per.id_permiso = rolper.permiso_id 
                   INNER JOIN roles as rol ON rol.id_rol = rolper.rol_id 
                   WHERE rolper.estado = '1'";
$query_roles_permisos = $pdo->prepare($sql_roles_permisos);
$query_roles_permisos->execute();
$roles_permisos = $query_roles_permisos->fetchAll(PDO::FETCH_ASSOC);
$contadorpermiso = 1;
foreach ($roles_permisos as $roles_permiso){
    if($id_rol_sesion_usuario == $roles_permiso['rol_id']){
        if($rest == $roles_permiso['url']){
            $contadorpermiso = $contadorpermiso + 3;
        }
    }
}

// Generar iniciales para el avatar
$firstInitial = substr(ucwords($nombres_sesion_usuario), 0, 1);
$lastInitial = substr(ucwords($apellidos_sesion_usuario), 0, 1);
$initials = $firstInitial . $lastInitial;

// Generar color consistente para el avatar
$colors = ['#FF6B6B', '#4ECDC4', '#45B7D1', '#FFA07A', '#98D8C8', '#F06292', '#7986CB', '#9575CD', '#64B5F6', '#4DB6AC', '#81C784', '#FFD54F', '#FF8A65', '#A1887F'];
$colorIndex = (ord($firstInitial) + ord($lastInitial)) % count($colors);
$bgColor = $colors[$colorIndex];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?=APP_NAME;?></title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="<?=APP_URL;?>/public/plugins/fontawesome-free/css/all.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="<?=APP_URL;?>/public/dist/css/adminlte.min.css">

    <!-- jQuery -->
    <script src="<?=APP_URL;?>/public/plugins/jquery/jquery.min.js"></script>

    <!-- Sweetaler2 -->
    <!-- SweetAlert2 - Usar versión local en lugar de CDN -->
    <link rel="stylesheet" href="<?=APP_URL;?>/public/plugins/sweetalert2/sweetalert2.min.css?v=<?=time()?>">
    <script src="<?=APP_URL;?>/public/plugins/sweetalert2/sweetalert2.all.min.js?v=<?=time()?>" onerror="
        console.error('Error al cargar SweetAlert2 local. Intentando desde CDN...');
        var link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = 'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css';
        document.head.appendChild(link);
        var script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/sweetalert2@11';
        document.head.appendChild(script);
    "></script>
    <script>
    // Verificar que SweetAlert2 se cargó después de que la página cargue completamente
    window.addEventListener('load', function() {
        setTimeout(function() {
            if (typeof Swal === 'undefined') {
                console.error('SweetAlert2 no está disponible. Verifica que el archivo se cargó correctamente.');
            } else {
                console.log('SweetAlert2 cargado correctamente');
            }
        }, 100);
    });
    </script>
    
    <!-- Toastr para notificaciones -->
    <link rel="stylesheet" href="<?=APP_URL;?>/public/plugins/toastr/toastr.min.css">
    <script src="<?=APP_URL;?>/public/plugins/toastr/toastr.min.js"></script>

    <!-- Iconos de bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Datatables -->
    <link rel="stylesheet" href="<?=APP_URL;?>/public/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="<?=APP_URL;?>/public/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="<?=APP_URL;?>/public/plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
    <style>
        .sidebar-dark-yellow .nav-sidebar>.nav-item>.nav-link.active, 
        .sidebar-light-yellow .nav-sidebar>.nav-item>.nav-link.active {
            color:white;
        }
        
        /* ========== COLOR PRINCIPAL DEL SISTEMA ========== */
        /* COLOR AZUL PRINCIPAL: #0d6efd (sin degradado) */
        /* ================================================= */
        
        /* Estilos añadidos para la barra superior azul */
        .navbar-info {
            background: #0d6efd !important; /* COLOR PRINCIPAL - Barra superior */
        }
        
        .navbar-info .navbar-nav .nav-link {
            color: white !important;
        }
        
        .navbar-info .dropdown-menu {
            background-color: white;
        }
        
        .navbar-info .dropdown-header {
            color: #333 !important;
        }
        
        /* Estilos específicos para el área SIGI y menús principales */
        .brand-link {
            background: #0d6efd !important; /* COLOR PRINCIPAL - Brand link */
            border-bottom: 1px solid rgba(255, 255, 255, 0.2) !important;
            margin: -8px -8px 0 -8px !important;
            padding: 15px !important;
        }
        
        .brand-link h4 {
            color: white !important;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
            margin: 0 !important;
            font-size: 1.5rem;
        }
        
        .brand-link small {
            color: rgba(255, 255, 255, 0.9) !important;
            font-size: 0.85rem;
        }
        
        /* ========== COLOR DEL MENÚ LATERAL ========== */
        /* User panel con fondo azul */
        .user-panel {
            background: #0d6efd !important; /* COLOR PRINCIPAL - Panel de usuario */
            margin: 0 -8px !important;
            padding: 15px 15px 15px 15px !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .user-panel .info a {
            color: white !important;
            font-weight: bold;
        }
        
        .user-panel .info small {
            color: rgba(255, 255, 255, 0.9) !important;
            font-size: 0.85em;
        }
        
        /* Avatar circular con iniciales */
        .initials-circle {
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            font-weight: bold;
            text-transform: uppercase;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }
        
        /* ========== COLOR DE LOS CUADROS DEL SUBMENÚ ========== */
        .nav-sidebar > .nav-item > .nav-link {
            background: #0d6efd !important; /* COLOR PRINCIPAL - Items del menú */
            color: white !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            border-left: 3px solid rgba(255, 255, 255, 0.3) !important;
            margin-bottom: 2px;
        }
        
        /* Borde blanco más intenso SOLO para el elemento activo */
        .nav-sidebar > .nav-item > .nav-link.active {
            background: #0b5ed7 !important; /* COLOR PRINCIPAL - Versión más oscura para activo */
            border-left: 8px solid #ffffff !important;
        }
        
        .nav-sidebar > .nav-item > .nav-link:hover {
            background: #0c63e4 !important; /* COLOR PRINCIPAL - Versión intermedia para hover */
            border-left: 5px solid rgba(255, 255, 255, 0.7) !important;
        }
        
        /* ========== COLOR DE LOS SUBMENÚS DESPLEGABLES ========== */
        .nav-treeview .nav-item .nav-link {
            background: #e9ecef !important; /* COLOR SECUNDARIO - Submenús desplegables */
            color: #495057 !important;
            border-left: 3px solid #dee2e6 !important;
        }
        
        .nav-treeview .nav-item .nav-link.active {
            background: #dae0e5 !important; /* COLOR SECUNDARIO - Submenús activos */
            border-left: 8px solid #0d6efd !important; /* COLOR PRINCIPAL - Borde azul */
        }
        
        .nav-treeview .nav-item .nav-link:hover {
            background: #d1d7dc !important; /* COLOR SECUNDARIO - Submenús hover */
        }
        
        /* Ajustes para la imagen del usuario */
        .img-circle.elevation-2 {
            border: 2px solid rgba(255, 255, 255, 0.3);
        }
        
        /* Estilo para el badge de notificaciones de chat */
        .chat-notification-badge {
            position: absolute;
            top: 6px;
            right: 10px;
            background-color: #e74a3b;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            font-weight: bold;
        }
        
        .nav-item.with-badge {
            position: relative;
        }

        /* Estilo para el título cuando está oculto */
        .navbar-brand-text.hidden-title {
            display: none;
        }
    </style>

    <!-- CHART -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">

    <!-- Navbar Unificada - TÍTULO COMPLETO EN TODAS LAS VENTANAS -->
    <nav class="main-header navbar navbar-expand navbar-info navbar-light" style="background: linear-gradient(135deg, #0d6efd 0%, #4dabf7 100%) !important; padding: 17px 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button">
                    <i class="fas fa-bars text-white"></i>
                </a>
            </li>
            <!-- TÍTULO COMPLETO - SE MUESTRA EN TODAS LAS VENTANAS -->
            <li class="nav-item">
                <div class="navbar-brand-text ml-3">
                    <h4 class="m-0 text-white" style="font-weight: 800; text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.3); font-size: 1.5rem; line-height: 1.2;">
                        <i class="fas fa-graduation-cap mr-2"></i>Sistema Integral de Gestión de Inscripciones y Carga de Notas
                    </h4>
                    <small class="text-white" style="font-size: 0.95rem; font-weight: 600; text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);">
                        Panel de control administrativo - Gestión educativa integral
                    </small>
                </div>
            </li>
        </ul>

        <!-- Right navbar links - SIEMPRE VISIBLE -->
        <ul class="navbar-nav ml-auto">
            <!-- Enlace rápido al chat -->
            <li class="nav-item">
                <a class="nav-link text-white" href="<?=APP_URL;?>/app/controllers/chat.php" title="Chat Interno">
                    <i class="fas fa-comments mr-1"></i>
                    <span class="badge badge-danger notification-counter" id="chatNotificationCounter" style="display: none;">0</span>
                </a>
            </li>
            
            <li class="nav-item dropdown">
                <a class="nav-link text-white" data-toggle="dropdown" aria-expanded="true" href="javascript:void(0)">
                    <span>
                        <div class="d-felx badge-pill">
                            <span class="fa fa-user mr-2"></span>
                            <span><b><?=htmlspecialchars($nombres_sesion_usuario . ' ' . $apellidos_sesion_usuario);?></b></span>
                            <span class="fa fa-angle-down ml-2"></span>
                        </div>
                    </span>
                </a>
                <div class="dropdown-menu" aria-labelledby="account_settings" style="left: -2.5em;">
                    <h4 class="dropdown-header">
                        <div class="d-flex justify-content-center">
                            <span class="initials-circle img-circle elevation-3" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; font-size: 20px; background-color: <?=$bgColor;?>; color: white;">
                                <?=$initials;?>
                            </span>
                        </div>
                        <br>
                        <i class="fa fa-user"></i>
                        <?php echo htmlspecialchars($nombres_sesion_usuario . ' ' . $apellidos_sesion_usuario); ?>
                        <br>
                        <small class="text-muted"><?php echo htmlspecialchars($rol_sesion_usuario); ?></small>
                    </h4>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="<?=APP_URL;?>/app/controllers/chat.php">
                        <i class="fas fa-comments me-2"></i> Chat Interno
                    </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="javascript:void(0)" id="logout-button">
                        <span class="fas fa-sign-out-alt"></span> Cerrar sesión
                    </a>
                </div>
            </li>
        </ul>
        <script>  
            document.getElementById("logout-button").addEventListener("click", function(event) {  
                event.preventDefault();
                
                Swal.fire({  
                    title: '¿Estás seguro?',  
                    text: "¿Quieres cerrar sesión?",  
                    icon: 'warning',  
                    showCancelButton: true,  
                    confirmButtonColor: '#3085d6',  
                    cancelButtonColor: '#d33',  
                    confirmButtonText: 'Sí, cerrar sesión!',  
                    cancelButtonText: 'Cancelar'  
                }).then((result) => {  
                    if (result.isConfirmed) {  
                        window.location.href = "<?=APP_URL;?>/login/login.php";  
                    }  
                });  
            });
        </script>
    </nav>
    <!-- /.navbar -->

    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-primary elevation-6">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="dropdown">
                
            
            <!-- Sidebar user panel (optional) -->
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="image">
                    <span class="img-circle elevation-2" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; font-size: 18px; background-color: <?=$bgColor;?>; color: white;">
                        <?=$initials;?>
                    </span>
                </div>
                <div class="info">
                    <a href="#" class="d-block"><?=htmlspecialchars($nombres_sesion_usuario);?></a>
                    <small class="text-light"><?php echo htmlspecialchars($rol_sesion_usuario); ?></small>
                </div>
            </div>

            <!-- Sidebar Menu -->
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                    <?php
                    // Detectar página actual de manera SIMPLE y EFECTIVA
                    $ruta_actual = $_SERVER['REQUEST_URI'];
                    $pagina_actual = basename($_SERVER['PHP_SELF']);
                    
                    // Detección SIMPLIFICADA - usando solo la parte de la ruta
                    $es_inicio = ($pagina_actual == 'index.php' && strpos($ruta_actual, '/admin') !== false) || 
                                $ruta_actual == APP_URL . '/admin' ||
                                $ruta_actual == APP_URL . '/admin/' ||
                                trim($ruta_actual, '/') == trim(APP_URL . '/admin', '/');
                    
                    // Para las demás páginas, buscamos directamente en la ruta
                    $es_representantes = strpos($ruta_actual, 'representantes') !== false;
                    $es_estudiantes = strpos($ruta_actual, 'estudiantes') !== false;
                    $es_profesores = strpos($ruta_actual, 'profesores') !== false;
                    $es_reportes = strpos($ruta_actual, 'reportes') !== false;
                    $es_notas = strpos($ruta_actual, 'notas') !== false;
                    $es_configuraciones = strpos($ruta_actual, 'configuraciones') !== false;
                    $es_chat = strpos($ruta_actual, 'chat') !== false;
                    ?>
                    
                    <li class="nav-item">
                        <a href="<?=APP_URL;?>/admin" 
                           class="nav-link <?= $es_inicio ? 'active' : '' ?>">
                            <i class="nav-icon fas fa-home"></i>
                            <p>Inicio</p>
                        </a>
                    </li>
                    
                    <!-- CHAT INTERNO - DISPONIBLE PARA TODOS LOS USUARIOS -->
                    <li class="nav-item with-badge">
                        <a href="<?=APP_URL;?>/app/controllers/chat.php" 
                           class="nav-link <?= $es_chat ? 'active' : '' ?>">
                            <i class="nav-icon fas fa-comments"></i>
                            <p>Chat Interno</p>
                            <span class="chat-notification-badge" id="sidebarChatNotification" style="display: none;">0</span>
                        </a>
                    </li>
                    
        <!-- =================================================== -->
        <!-- MÓDULO DE NOTAS Y HORARIOS UNIFICADO -->
        <!-- =================================================== -->
        <?php if (
            (isset($_SESSION['es_docente']) && $_SESSION['es_docente']) 
            || (isset($_SESSION['rol_id']) && ($_SESSION['rol_id'] == 1 || $_SESSION['rol_id'] == 2))
        ): ?>
        <li class="nav-item <?= (strpos($ruta_actual, 'notas') !== false) ? 'menu-open' : '' ?>">
            <a href="#" class="nav-link <?= $es_notas ? 'active' : '' ?>">
                <i class="nav-icon fas fa-chalkboard-teacher"></i>
                <p>
                    Módulo de Notas y Horarios
                    <i class="right fas fa-angle-left"></i>
                </p>
            </a>
            <ul class="nav nav-treeview">
                <li class="nav-item">
                    <a href="<?=APP_URL;?>/admin/notas/carga_notas_seccion.php" class="nav-link <?= (strpos($ruta_actual, 'carga_notas_seccion') !== false) ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-users"></i>
                        <p>Carga Masiva por Sección</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?=APP_URL;?>/admin/notas/crear_horarios.php" class="nav-link <?= (strpos($ruta_actual, 'crear_horarios') !== false) ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-calendar-plus"></i>
                        <p>Crear Horarios</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?=APP_URL;?>/admin/notas/horarios_consolidados.php" class="nav-link <?= (strpos($ruta_actual, 'horarios_consolidados') !== false) ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-calendar-check"></i>
                        <p>Horarios Consolidados</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?=APP_URL;?>/admin/notas/ver_progreso_notas.php" class="nav-link <?= (strpos($ruta_actual, 'ver_progreso_notas') !== false) ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-chart-line"></i>
                        <p>Progreso de Notas</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?=APP_URL;?>/admin/notas/historial_notas.php" class="nav-link <?= (strpos($ruta_actual, 'historial_notas') !== false) ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-history"></i>
                        <p>Historial de Cambios</p>
                    </a>
                </li>
            </ul>
        </li>
        <?php endif; ?>



                   <!-- DIRECTOR ACADÉMICO, SECRETARÍA y ADMINISTRADOR -->
<?php if(in_array($rol_sesion_usuario, ["ADMINISTRADOR", "DIRECTOR ACADÉMICO"])): ?>
    <li class="nav-item">
        <a href="<?=APP_URL;?>/admin/representantes" 
           class="nav-link <?= $es_representantes ? 'active' : '' ?>">
            <i class="nav-icon fas fa-user-friends"></i>
            <p>Representantes</p>
        </a>
    </li>

    <li class="nav-item">
        <a href="<?=APP_URL;?>/admin/estudiantes" 
           class="nav-link <?= $es_estudiantes ? 'active' : '' ?>">
            <i class="nav-icon fas fa-user-graduate"></i>
            <p>Estudiantes</p>
        </a>
    </li>

    <!-- ============================================== -->
    <!-- SECCIÓN PROFESORES CON SUBMENÚ -->
    <!-- ============================================== -->
    <li class="nav-item <?= (strpos($ruta_actual, 'profesores') !== false || strpos($ruta_actual, 'asignaciones') !== false) ? 'menu-open' : '' ?>">
        <a href="#" class="nav-link <?= (strpos($ruta_actual, 'profesores') !== false || strpos($ruta_actual, 'asignaciones') !== false) ? 'active' : '' ?>">
            <i class="nav-icon fas fa-chalkboard-teacher"></i>
            <p>
                Profesores
                <i class="right fas fa-angle-left"></i>
            </p>
        </a>

        <ul class="nav nav-treeview">
            <li class="nav-item">
                <a href="<?=APP_URL;?>/admin/profesores/listar_profesores.php" 
                   class="nav-link <?= (strpos($ruta_actual, 'listar_profesores') !== false) ? 'active' : '' ?>">
                    <i class="fas fa-list nav-icon"></i>
                    <p>Listado de Profesores</p>
                </a>
            </li>

            <li class="nav-item">
                <a href="<?=APP_URL;?>/admin/asignaciones/listar_asignaciones.php" 
                   class="nav-link <?= (strpos($ruta_actual, 'listar_asignaciones') !== false) ? 'active' : '' ?>">
                    <i class="fas fa-tasks nav-icon"></i>
                    <p>Asignaciones</p>
                </a>
            </li>
        </ul>
    </li>
<?php endif; ?>

        
                    
                    <!-- SOLO DIRECTOR ACADÉMICO y ADMINISTRADOR -->
                    <?php if(in_array($rol_sesion_usuario, ["ADMINISTRADOR", "DIRECTOR ACADÉMICO","DOCENTE"])): ?>
                        <li class="nav-item">
                            <a href="<?=APP_URL;?>/admin/reportes" 
                               class="nav-link <?= $es_reportes ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-chart-bar"></i>
                                <p>Reportes</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?=APP_URL;?>/admin/configuraciones" 
                               class="nav-link <?= $es_configuraciones ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-cogs"></i>
                                <p>Módulo administrativo</p>
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <!-- PROFESOR -->
                    <?php if($rol_sesion_usuario == "PROFESOR"): ?>
                        <li class="nav-item">
                            <a href="<?=APP_URL;?>/admin/mis-cursos" 
                               class="nav-link <?= (strpos($ruta_actual, 'mis-cursos') !== false) ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-book-open"></i>
                                <p>Mis Cursos</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?=APP_URL;?>/admin/mis-notas" 
                               class="nav-link <?= (strpos($ruta_actual, 'mis-notas') !== false) ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-edit"></i>
                                <p>Cargar Notas</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?=APP_URL;?>/admin/mi-horario" 
                               class="nav-link <?= (strpos($ruta_actual, 'mi-horario') !== false) ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-clock"></i>
                                <p>Mi Horario</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?=APP_URL;?>/admin/asistencias" 
                               class="nav-link <?= (strpos($ruta_actual, 'asistencias') !== false) ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-clipboard-check"></i>
                                <p>Registrar Asistencias</p>
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <!-- SUBDIRECTOR -->
                    <?php if($rol_sesion_usuario == "SUBDIRECTOR"): ?>
                        <li class="nav-item">
                            <a href="<?=APP_URL;?>/admin/estadisticas" 
                               class="nav-link <?= (strpos($ruta_actual, 'estadisticas') !== false) ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-chart-line"></i>
                                <p>Estadísticas</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?=APP_URL;?>/admin/calendario" 
                               class="nav-link <?= (strpos($ruta_actual, 'calendario') !== false) ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-calendar-alt"></i>
                                <p>Calendario Académico</p>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <!-- /.sidebar-menu -->
        </div>
        <!-- /.sidebar -->
    </aside>

    <script>
        // Función para cargar notificaciones de chat - VERSIÓN MEJORADA
        function cargarNotificacionesChat() {
            fetch('<?=APP_URL;?>/app/controllers/chat.php?action=get_unread_count', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error en la respuesta');
                }
                return response.json();
            })
            .then(data => {
                if (data.success && data.unread_count > 0) {
                    document.getElementById('chatNotificationCounter').textContent = data.unread_count;
                    document.getElementById('chatNotificationCounter').style.display = 'inline';
                } else {
                    // Ocultar notificaciones si no hay mensajes no leídos
                    document.getElementById('chatNotificationCounter').style.display = 'none';
                }
            })
            .catch(error => {
                console.error('Error cargando notificaciones:', error);
                // Ocultar notificaciones en caso de error
                document.getElementById('chatNotificationCounter').style.display = 'none';
            });
        }

        // Función para actualizar notificaciones en el sidebar - VERSIÓN MEJORADA
        function actualizarNotificacionesSidebar() {
            fetch('<?=APP_URL;?>/app/controllers/chat.php?action=get_unread_count', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error en la respuesta');
                }
                return response.json();
            })
            .then(data => {
                if (data.success && data.unread_count > 0) {
                    const badge = document.getElementById('sidebarChatNotification');
                    badge.textContent = data.unread_count;
                    badge.style.display = 'flex';
                } else {
                    document.getElementById('sidebarChatNotification').style.display = 'none';
                }
            })
            .catch(error => {
                console.error('Error actualizando notificaciones:', error);
                document.getElementById('sidebarChatNotification').style.display = 'none';
            });
        }
        
        // Cargar notificaciones al iniciar
        document.addEventListener('DOMContentLoaded', function() {
            cargarNotificacionesChat();
            actualizarNotificacionesSidebar();
            
            // Actualizar cada 30 segundos
            setInterval(cargarNotificacionesChat, 30000);
            setInterval(actualizarNotificacionesSidebar, 30000);
        });
    </script>