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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
        
        /* Estilos añadidos para la barra superior azul gradiente */
        .navbar-info {
            background: linear-gradient(135deg, #1e88e5, #2d5f7e ) !important;
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
            background: linear-gradient(135deg, #1e88e5 0%, #2d5f7e 100%) !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2) !important;
            margin: -8px -8px 0 -8px !important;
            padding: 15px !important;
        }
        
        .brand-link h4 {
            color: white !important;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
            margin: 0 !important;
        }
        
        /* User panel con fondo azul */
        .user-panel {
            background: linear-gradient(135deg, #1e88e5 0%, #2d5f7e 100%) !important;
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
        
        .nav-sidebar > .nav-item > .nav-link {
            background: linear-gradient(135deg, #1e88e5 0%, #2d5f7e 100%) !important;
            color: white !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            border-left: 3px solid rgba(255, 255, 255, 0.3) !important;
            margin-bottom: 2px;
        }
        
        /* Borde blanco más intenso SOLO para el elemento activo */
        .nav-sidebar > .nav-item > .nav-link.active {
            background: linear-gradient(135deg, #1565c0 0%, #1e4e7a 100%) !important;
            border-left: 8px solid #ffffff !important;
        }
        
        .nav-sidebar > .nav-item > .nav-link:hover {
            background: linear-gradient(135deg, #1976d2 0%, #23527c 100%) !important;
            border-left: 5px solid rgba(255, 255, 255, 0.7) !important;
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
    </style>

    <!-- CHART -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">

    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-info navbar-light">
        <!-- Left navbar links -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars text-white"></i></a>
            </li>
        </ul>

        <!-- Right navbar links -->
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
                            <span class="initials-circle img-circle elevation-3" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; font-size: 20px; background-color: #007bff; color: white;">
                                <?php echo substr(ucwords($nombres_sesion_usuario), 0, 1) . substr(ucwords($apellidos_sesion_usuario), 0, 1); ?>
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
                <a href="<?=APP_URL;?>/admin" class="brand-link">
                    <h4 class="text-center p-0 m-0"><b>SIGI</b></h4>
                    <small class="text-center"></small>
                </a>  
            </div>
            
            <!-- Sidebar user panel (optional) -->
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="image">
                    <span class="img-circle elevation-2" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; font-size: 18px; background-color: #007bff; color: white;">
                        <?php echo substr(ucwords($nombres_sesion_usuario), 0, 1) . substr(ucwords($apellidos_sesion_usuario), 0, 1); ?>
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
                    
                    // Mostrar información para debugging (puedes eliminar esto después)
                    // echo "<!-- Ruta actual: " . $ruta_actual . " -->";
                    // echo "<!-- Página actual: " . $pagina_actual . " -->";
                    
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
<!-- MÓDULO DE NOTAS (DOCENTES + ADMINISTRADORES) -->
<!-- =================================================== -->
<?php if (
    (isset($_SESSION['es_docente']) && $_SESSION['es_docente']) 
    || (isset($_SESSION['rol_id']) && ($_SESSION['rol_id'] == 1 || $_SESSION['rol_id'] == 2))
): ?>
<li class="nav-item">
    <a href="#" class="nav-link <?= $es_notas ? 'active' : '' ?>">
        <i class="nav-icon fas fa-chalkboard-teacher"></i>
        <p>
            Módulo de Notas
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
            <a href="<?=APP_URL;?>/admin/notas/notas_estudiantes.php" class="nav-link <?= (strpos($ruta_actual, 'notas_estudiantes') !== false) ? 'active' : '' ?>">
                <i class="nav-icon fas fa-user-edit"></i>
                <p>Gestión Individual</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="<?=APP_URL;?>/admin/notas/generar_boleta.php" class="nav-link <?= (strpos($ruta_actual, 'generar_boleta') !== false) ? 'active' : '' ?>">
                <i class="nav-icon fas fa-file-pdf"></i>
                <p>Generar Boletas</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="<?=APP_URL;?>/admin/notas/historial_notas.php" class="nav-link <?= (strpos($ruta_actual, 'historial_notas') !== false) ? 'active' : '' ?>">
                <i class="nav-icon fas fa-history"></i>
                <p>Historial de Cambios</p>
            </a>
        </li>
        <!-- 🔹 Nuevo Submenú: Progreso de Notas -->
        <li class="nav-item">
            <a href="<?=APP_URL;?>/admin/notas/ver_progreso_notas.php" class="nav-link <?= (strpos($ruta_actual, 'ver_progreso_notas') !== false) ? 'active' : '' ?>">
                <i class="nav-icon fas fa-chart-line"></i>
                <p>Progreso de Notas</p>
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
                            <a href="<?=APP_URL;?>/admin/notas" 
                               class="nav-link <?= $es_notas ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-book"></i>
                                <p>Notas y Horarios</p>
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