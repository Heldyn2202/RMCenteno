<?php
session_start();
include('../../app/config.php');

// Verificar sesión de forma segura
$rol_sesion_usuario = $_SESSION['rol_sesion_usuario'] ?? '';

if (empty($rol_sesion_usuario) || $rol_sesion_usuario != "ADMINISTRADOR") {
    echo "<script>alert('Acceso denegado'); window.location.href='../index.php';</script>";
    exit();
}

// Obtener el rol de sesión para usar en la lógica
$rol_sesion_usuario = $_SESSION['rol_sesion_usuario'] ?? '';

// Procesar acciones
if(isset($_GET['action'])) {
    $id = intval($_GET['id']);
    
    if($_GET['action'] == 'aprobar') {
        $sql = "UPDATE tblcomments SET status = 1 WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([':id' => $id]);
        
        if($result) {
            $_SESSION['mensaje'] = "Comentario aprobado exitosamente";
            $_SESSION['icono_mensaje'] = "success";
        } else {
            $_SESSION['mensaje'] = "Error al aprobar comentario";
            $_SESSION['icono_mensaje'] = "error";
        }
    } elseif($_GET['action'] == 'rechazar') {
        $sql = "DELETE FROM tblcomments WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([':id' => $id]);
        
        if($result) {
            $_SESSION['mensaje'] = "Comentario rechazado y eliminado";
            $_SESSION['icono_mensaje'] = "success";
        } else {
            $_SESSION['mensaje'] = "Error al eliminar comentario";
            $_SESSION['icono_mensaje'] = "error";
        }
    }
    
    header('Location: comentarios.php');
    exit();
}

// Obtener comentarios pendientes (status = 0)
$sql_pendientes = "
    SELECT c.*, p.PostTitle 
    FROM tblcomments c 
    LEFT JOIN tblposts p ON c.postId = p.id 
    WHERE c.status = 0 
    ORDER BY c.postingDate DESC
";
$stmt = $pdo->prepare($sql_pendientes);
$stmt->execute();
$comentarios_pendientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener comentarios aprobados (status = 1)
$sql_aprobados = "
    SELECT c.*, p.PostTitle 
    FROM tblcomments c 
    LEFT JOIN tblposts p ON c.postId = p.id 
    WHERE c.status = 1 
    ORDER BY c.postingDate DESC 
    LIMIT 50
";
$stmt = $pdo->prepare($sql_aprobados);
$stmt->execute();
$comentarios_aprobados = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Contar totales para estadísticas
$total_pendientes = count($comentarios_pendientes);
$total_aprobados = count($comentarios_aprobados);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Moderación de Comentarios</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- AdminLTE CSS (si lo usas) -->
    <link rel="stylesheet" href="<?=APP_URL;?>/public/css/adminlte.min.css">
    <style>
        .badge-pendiente { background-color: #ffc107; color: #000; }
        .badge-aprobado { background-color: #28a745; color: #fff; }
        .comentario-card { 
            border-left: 4px solid #ccc; 
            padding-left: 15px; 
            margin-bottom: 15px;
            background: #fff;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            border: 1px solid #dee2e6;
        }
        .comentario-card.pendiente { 
            border-left-color: #ffc107; 
            background-color: #fff9e6; 
        }
        .comentario-card.aprobado { 
            border-left-color: #28a745; 
            background-color: #f0fff4; 
        }
        .user-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #1a4b8c 0%, #2d68c4 100%);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 18px;
        }
        .nav-tabs .nav-link.active {
            background-color: #fff;
            border-color: #dee2e6 #dee2e6 #fff;
        }
        .stat-card {
            transition: transform 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    
    <!-- Incluir header de tu sistema -->
    <?php 
    // Incluimos solo las partes necesarias para mantener la sesión
    // Verificamos si el archivo existe antes de incluirlo
    $layout_parte1 = '../../admin/layout/parte1.php';
    if (file_exists($layout_parte1)) {
        include($layout_parte1);
    } else {
        // Si no existe, creamos un header básico
        echo '
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button">
                        <i class="fas fa-bars"></i>
                    </a>
                </li>
            </ul>
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a href="../index.php" class="nav-link">
                        <i class="fas fa-home"></i> Volver al Dashboard
                    </a>
                </li>
            </ul>
        </nav>';
    }
    ?>

    <!-- Incluir sidebar si existe -->
    <?php 
    $layout_sidebar = '../../admin/layout/sidebar.php';
    if (file_exists($layout_sidebar)) {
        include($layout_sidebar);
    }
    ?>

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <section class="content">
            <div class="container-fluid">
                <!-- Encabezado -->
                <div class="content-header">
                    <div class="container-fluid">
                        <div class="row mb-2">
                            <div class="col-sm-6">
                                <h1 class="m-0 text-dark">
                                    <i class="fas fa-comments mr-2"></i>Moderación de Comentarios
                                </h1>
                            </div>
                            <div class="col-sm-6">
                                <ol class="breadcrumb float-sm-right">
                                    <li class="breadcrumb-item"><a href="../index.php">Inicio</a></li>
                                    <li class="breadcrumb-item active">Comentarios</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Mostrar mensajes de sesión -->
                <?php if(isset($_SESSION['mensaje'])): ?>
                <div class="alert alert-<?php echo $_SESSION['icono_mensaje'] == 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show">
                    <?php echo $_SESSION['mensaje']; ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <?php 
                    unset($_SESSION['mensaje']);
                    unset($_SESSION['icono_mensaje']);
                endif; 
                ?>
                
                <!-- Estadísticas -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card stat-card bg-warning text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="card-title">
                                            <i class="fas fa-clock mr-1"></i> Pendientes
                                        </h5>
                                        <h2 class="mb-0"><?php echo $total_pendientes; ?></h2>
                                        <small>Comentarios por revisar</small>
                                    </div>
                                    <i class="fas fa-comments fa-3x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card stat-card bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="card-title">
                                            <i class="fas fa-check-circle mr-1"></i> Aprobados
                                        </h5>
                                        <h2 class="mb-0"><?php echo $total_aprobados; ?></h2>
                                        <small>Comentarios publicados</small>
                                    </div>
                                    <i class="fas fa-thumbs-up fa-3x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Pestañas -->
                <div class="card card-primary card-outline">
                    <div class="card-header p-0 border-bottom-0">
                        <ul class="nav nav-tabs" id="custom-tabs-three-tab" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="pendientes-tab" data-toggle="pill" href="#pendientes" role="tab">
                                    <i class="fas fa-clock mr-1"></i> Pendientes 
                                    <?php if($total_pendientes > 0): ?>
                                    <span class="badge badge-warning ml-1"><?php echo $total_pendientes; ?></span>
                                    <?php endif; ?>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="aprobados-tab" data-toggle="pill" href="#aprobados" role="tab">
                                    <i class="fas fa-check-circle mr-1"></i> Aprobados
                                    <?php if($total_aprobados > 0): ?>
                                    <span class="badge badge-success ml-1"><?php echo $total_aprobados; ?></span>
                                    <?php endif; ?>
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content" id="custom-tabs-three-tabContent">
                            <!-- Tab Pendientes -->
                            <div class="tab-pane fade show active" id="pendientes" role="tabpanel">
                                <?php if($total_pendientes > 0): ?>
                                    <div class="row">
                                        <?php foreach($comentarios_pendientes as $comentario): ?>
                                        <div class="col-md-12 mb-3">
                                            <div class="comentario-card pendiente">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <div class="d-flex align-items-center">
                                                        <div class="user-avatar me-3">
                                                            <?php echo strtoupper(substr($comentario['name'], 0, 1)); ?>
                                                        </div>
                                                        <div>
                                                            <strong><?php echo htmlspecialchars($comentario['name']); ?></strong>
                                                            <br>
                                                            <small class="text-muted">
                                                                <i class="fas fa-envelope me-1"></i> <?php echo htmlspecialchars($comentario['email']); ?>
                                                                <i class="fas fa-calendar-alt ms-3 me-1"></i> <?php echo date('d/m/Y H:i', strtotime($comentario['postingDate'])); ?>
                                                            </small>
                                                        </div>
                                                    </div>
                                                    <span class="badge badge-pendiente">
                                                        <i class="fas fa-clock"></i> Pendiente
                                                    </span>
                                                </div>
                                                
                                                <p class="mb-3"><?php echo nl2br(htmlspecialchars($comentario['comment'])); ?></p>
                                                
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <span class="badge bg-info">
                                                            <i class="fas fa-newspaper me-1"></i>
                                                            <?php echo !empty($comentario['PostTitle']) ? htmlspecialchars($comentario['PostTitle']) : 'Noticia #' . $comentario['postId']; ?>
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <a href="?action=aprobar&id=<?php echo $comentario['id']; ?>" 
                                                           class="btn btn-success btn-sm" 
                                                           onclick="return confirm('¿Aprobar este comentario?')">
                                                            <i class="fas fa-check"></i> Aprobar
                                                        </a>
                                                        <a href="?action=rechazar&id=<?php echo $comentario['id']; ?>" 
                                                           class="btn btn-danger btn-sm" 
                                                           onclick="return confirm('¿Rechazar y eliminar este comentario? Esta acción no se puede deshacer.')">
                                                            <i class="fas fa-trash"></i> Eliminar
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-5">
                                        <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                                        <h3>¡No hay comentarios pendientes!</h3>
                                        <p class="text-muted">Todos los comentarios han sido revisados y aprobados.</p>
                                        <a href="../index.php" class="btn btn-primary">
                                            <i class="fas fa-arrow-left mr-1"></i> Volver al Dashboard
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Tab Aprobados -->
                            <div class="tab-pane fade" id="aprobados" role="tabpanel">
                                <?php if($total_aprobados > 0): ?>
                                    <div class="row">
                                        <?php foreach($comentarios_aprobados as $comentario): ?>
                                        <div class="col-md-12 mb-3">
                                            <div class="comentario-card aprobado">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <div class="d-flex align-items-center">
                                                        <div class="user-avatar me-3">
                                                            <?php echo strtoupper(substr($comentario['name'], 0, 1)); ?>
                                                        </div>
                                                        <div>
                                                            <strong><?php echo htmlspecialchars($comentario['name']); ?></strong>
                                                            <br>
                                                            <small class="text-muted">
                                                                <i class="fas fa-envelope me-1"></i> <?php echo htmlspecialchars($comentario['email']); ?>
                                                                <i class="fas fa-calendar-alt ms-3 me-1"></i> <?php echo date('d/m/Y H:i', strtotime($comentario['postingDate'])); ?>
                                                            </small>
                                                        </div>
                                                    </div>
                                                    <span class="badge badge-aprobado">
                                                        <i class="fas fa-check"></i> Aprobado
                                                    </span>
                                                </div>
                                                
                                                <p class="mb-3"><?php echo nl2br(htmlspecialchars($comentario['comment'])); ?></p>
                                                
                                                <div>
                                                    <span class="badge bg-info">
                                                        <i class="fas fa-newspaper me-1"></i>
                                                        <?php echo !empty($comentario['PostTitle']) ? htmlspecialchars($comentario['PostTitle']) : 'Noticia #' . $comentario['postId']; ?>
                                                    </span>
                                                    <small class="text-muted ms-3">
                                                        <i class="fas fa-check-circle me-1"></i> Aprobado para publicación
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                    
                                    <div class="text-center mt-3">
                                        <p class="text-muted">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            Mostrando <?php echo $total_aprobados; ?> comentarios aprobados
                                        </p>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-5">
                                        <i class="fas fa-comment-slash fa-4x text-muted mb-3"></i>
                                        <h3>No hay comentarios aprobados</h3>
                                        <p class="text-muted">Los comentarios que apruebes aparecerán aquí.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    
    <!-- Footer -->
    <?php 
    $layout_parte2 = '../../admin/layout/parte2.php';
    if (file_exists($layout_parte2)) {
        include($layout_parte2);
    } else {
        echo '
        <footer class="main-footer">
            <div class="float-right d-none d-sm-block">
                <b>Versión</b> 1.0.0
            </div>
            <strong>U.E. Roberto Martinez Centeno</strong> - Sistema de Gestión
        </footer>';
    }
    ?>
</div>

<!-- jQuery -->
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App (si lo usas) -->
<script src="<?=APP_URL;?>/public/js/adminlte.min.js"></script>

<script>
    // Auto-recargar cada 60 segundos para la pestaña de pendientes
    $(document).ready(function() {
        // Función para actualizar contadores
        function actualizarContadores() {
            if ($('#pendientes-tab').hasClass('active')) {
                $.ajax({
                    url: window.location.href,
                    type: 'GET',
                    success: function(data) {
                        // Extraer el número de pendientes de la respuesta
                        var match = data.match(/Pendientes.*?badge badge-warning[^>]*>(\d+)</);
                        if (match) {
                            var newCount = match[1];
                            var currentCount = $('#pendientes-tab .badge').text();
                            if (newCount !== currentCount) {
                                location.reload();
                            }
                        }
                    }
                });
            }
        }
        
        // Actualizar cada 60 segundos
        setInterval(actualizarContadores, 60000);
        
        // Confirmación mejorada para eliminación
        $('a[href*="action=rechazar"]').click(function(e) {
            if(!confirm('¿Estás seguro de eliminar este comentario? Esta acción no se puede deshacer.')) {
                e.preventDefault();
            }
        });
        
        // Inicializar tooltips
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>
</body>
</html>