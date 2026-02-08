<?php
session_start();
include('../../app/config.php');

// Verificar sesión de forma segura
$rol_sesion_usuario = $_SESSION['rol_sesion_usuario'] ?? '';

if (empty($rol_sesion_usuario) || $rol_sesion_usuario != "ADMINISTRADOR") {
    echo "<script>
        Swal.fire({
            icon: 'error',
            title: 'Acceso denegado',
            text: 'No tienes permisos para acceder a esta sección',
            confirmButtonText: 'Aceptar'
        }).then(() => {
            window.location.href='../index.php';
        });
    </script>";
    exit();
}

// Obtener el rol de sesión para usar en la lógica
$rol_sesion_usuario = $_SESSION['rol_sesion_usuario'] ?? '';

// Configuración de paginación
$por_pagina = 20;
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina_actual - 1) * $por_pagina;

// Búsqueda y filtros
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';
$filtro_estado = isset($_GET['estado']) ? $_GET['estado'] : 'pendientes'; // 'pendientes', 'aprobados', 'todos'
$filtro_noticia = isset($_GET['noticia_id']) ? (int)$_GET['noticia_id'] : 0;

// Procesar acciones con SweetAlert2
if(isset($_GET['action'])) {
    $id = intval($_GET['id']);
    
    if($_GET['action'] == 'aprobar') {
        $sql = "UPDATE tblcomments SET status = 1 WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([':id' => $id]);
        
        if($result) {
            $_SESSION['mensaje'] = "Comentario aprobado exitosamente";
            $_SESSION['icono_mensaje'] = "success";
            $_SESSION['titulo_mensaje'] = "¡Aprobado!";
        } else {
            $_SESSION['mensaje'] = "Error al aprobar comentario";
            $_SESSION['icono_mensaje'] = "error";
            $_SESSION['titulo_mensaje'] = "Error";
        }
    } 
    elseif($_GET['action'] == 'desaprobar') {
        $sql = "UPDATE tblcomments SET status = 0 WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([':id' => $id]);
        
        if($result) {
            $_SESSION['mensaje'] = "Comentario desaprobado exitosamente";
            $_SESSION['icono_mensaje'] = "info";
            $_SESSION['titulo_mensaje'] = "¡Desaprobado!";
        } else {
            $_SESSION['mensaje'] = "Error al desaprobar comentario";
            $_SESSION['icono_mensaje'] = "error";
            $_SESSION['titulo_mensaje'] = "Error";
        }
    }
    elseif($_GET['action'] == 'eliminar') {
        $sql = "DELETE FROM tblcomments WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([':id' => $id]);
        
        if($result) {
            $_SESSION['mensaje'] = "Comentario eliminado permanentemente";
            $_SESSION['icono_mensaje'] = "success";
            $_SESSION['titulo_mensaje'] = "¡Eliminado!";
        } else {
            $_SESSION['mensaje'] = "Error al eliminar comentario";
            $_SESSION['icono_mensaje'] = "error";
            $_SESSION['titulo_mensaje'] = "Error";
        }
    }
    
    header('Location: comentarios.php' . (isset($_GET['pagina']) ? '?pagina=' . $_GET['pagina'] : ''));
    exit();
}

// Construir consulta base con filtros
$where = [];
$params = [];
$tipos = [];

if (!empty($busqueda)) {
    $where[] = "(c.name LIKE ? OR c.email LIKE ? OR c.comment LIKE ?)";
    $params[] = "%$busqueda%";
    $params[] = "%$busqueda%";
    $params[] = "%$busqueda%";
}

if ($filtro_estado == 'pendientes') {
    $where[] = "c.status = 0";
} elseif ($filtro_estado == 'aprobados') {
    $where[] = "c.status = 1";
}
// Si es 'todos', no aplicamos filtro de estado

if ($filtro_noticia > 0) {
    $where[] = "c.postId = ?";
    $params[] = $filtro_noticia;
}

$where_sql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : 'WHERE 1=1';

// Contar total de comentarios
$sql_count = "
    SELECT COUNT(*) as total 
    FROM tblcomments c
    LEFT JOIN tblposts p ON c.postId = p.id 
    $where_sql
";
$stmt_count = $pdo->prepare($sql_count);
$stmt_count->execute($params);
$total_comentarios = $stmt_count->fetchColumn();
$total_paginas = ceil($total_comentarios / $por_pagina);

// Obtener comentarios con paginación
$sql = "
    SELECT c.*, p.PostTitle, p.id as post_id
    FROM tblcomments c 
    LEFT JOIN tblposts p ON c.postId = p.id 
    $where_sql
    ORDER BY c.postingDate DESC 
    LIMIT $offset, $por_pagina
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$comentarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener lista de noticias para el filtro
$sql_noticias = "SELECT id, PostTitle FROM tblposts ORDER BY PostingDate DESC LIMIT 100";
$noticias = $pdo->query($sql_noticias)->fetchAll(PDO::FETCH_ASSOC);

// Contar por estados para estadísticas
$sql_pendientes = "SELECT COUNT(*) FROM tblcomments WHERE status = 0";
$total_pendientes = $pdo->query($sql_pendientes)->fetchColumn();

$sql_aprobados = "SELECT COUNT(*) FROM tblcomments WHERE status = 1";
$total_aprobados = $pdo->query($sql_aprobados)->fetchColumn();

$sql_hoy = "SELECT COUNT(*) FROM tblcomments WHERE DATE(postingDate) = CURDATE()";
$total_hoy = $pdo->query($sql_hoy)->fetchColumn();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Moderación de Comentarios</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #1a4b8c;
            --secondary-color: #2d68c4;
            --success-light: #d4edda;
            --success-dark: #28a745;
            --warning-light: #fff3cd;
            --warning-dark: #ffc107;
            --info-light: #d1ecf1;
            --info-dark: #17a2b8;
            --danger-light: #f8d7da;
            --danger-dark: #dc3545;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .admin-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 20px 0;
            margin-bottom: 25px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        }
        
        .card {
            border-radius: 10px;
            border: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-bottom: 20px;
        }
        
        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        
        .badge-estado {
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.85rem;
            border: 1px solid;
        }
        
        .badge-pendiente {
            background-color: #fff9e6;
            color: #b37400;
            border-color: #ffd54f;
        }
        
        .badge-aprobado {
            background-color: #e8f5e9;
            color: #2e7d32;
            border-color: #a5d6a7;
        }
        
        .comentario-card {
            padding: 20px;
            margin-bottom: 15px;
            border-radius: 8px;
            border: 1px solid #eaeaea;
            background: white;
            transition: all 0.3s ease;
        }
        
        .comentario-card:hover {
            border-color: var(--primary-color);
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        .user-avatar {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 20px;
            flex-shrink: 0;
        }
        
        .search-box {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        
        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            margin-bottom: 20px;
            transition: transform 0.3s ease;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
        }
        
        .stats-number {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .stats-label {
            font-size: 0.9rem;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .btn-accion {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.3s ease;
            border: 1px solid;
        }
        
        .btn-aprobar {
            background-color: #e8f5e9;
            color: #2e7d32;
            border-color: #2e7d32;
        }
        
        .btn-aprobar:hover {
            background-color: #2e7d32;
            color: white;
        }
        
        .btn-desaprobar {
            background-color: #fff9e6;
            color: #b37400;
            border-color: #b37400;
        }
        
        .btn-desaprobar:hover {
            background-color: #b37400;
            color: white;
        }
        
        .btn-eliminar {
            background-color: #ffebee;
            color: #c62828;
            border-color: #c62828;
        }
        
        .btn-eliminar:hover {
            background-color: #c62828;
            color: white;
        }
        
        .pagination-custom .page-link {
            color: var(--primary-color);
            border: 1px solid #dee2e6;
            margin: 0 3px;
            border-radius: 6px;
        }
        
        .pagination-custom .page-item.active .page-link {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }
        
        .pagination-custom .page-link:hover {
            background-color: #e8f0fe;
            border-color: var(--primary-color);
        }
        
        .filter-badge {
            cursor: pointer;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            margin-right: 10px;
            margin-bottom: 10px;
            display: inline-block;
            transition: all 0.3s ease;
            border: 1px solid transparent;
        }
        
        .filter-badge.active {
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        
        .filter-badge:not(.active) {
            background-color: #f8f9fa;
            color: #666;
            border-color: #dee2e6;
        }
        
        .filter-badge:not(.active):hover {
            background-color: #e9ecef;
            border-color: var(--primary-color);
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        .comment-text {
            line-height: 1.6;
            color: #333;
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid var(--primary-color);
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="admin-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-2">
                        <i class="fas fa-comments me-2"></i> Moderación de Comentarios
                    </h1>
                    <small>Gestiona los comentarios de los usuarios</small>
                </div>
                <div>
                    <a href="../index.php" class="btn btn-light btn-sm">
                        <i class="fas fa-tachometer-alt me-1"></i> Dashboard
                    </a>
                </div>
            </div>
        </div>
    </header>
    
    <div class="container">
        <!-- Estadísticas -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-number text-primary"><?php echo $total_comentarios; ?></div>
                    <div class="stats-label">Total Comentarios</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-number text-warning"><?php echo $total_pendientes; ?></div>
                    <div class="stats-label">Pendientes</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-number text-success"><?php echo $total_aprobados; ?></div>
                    <div class="stats-label">Aprobados</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-number text-info"><?php echo $total_hoy; ?></div>
                    <div class="stats-label">Hoy</div>
                </div>
            </div>
        </div>
        
        <!-- Filtros y búsqueda -->
        <div class="search-box">
            <form method="GET" class="row g-3">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" class="form-control border-start-0" 
                               name="busqueda" value="<?php echo htmlspecialchars($busqueda); ?>" 
                               placeholder="Buscar por nombre, email o comentario...">
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-search me-1"></i> Buscar
                        </button>
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="estado" onchange="this.form.submit()">
                        <option value="pendientes" <?php echo $filtro_estado === 'pendientes' ? 'selected' : ''; ?>>Pendientes de revisión</option>
                        <option value="aprobados" <?php echo $filtro_estado === 'aprobados' ? 'selected' : ''; ?>>Aprobados</option>
                        <option value="todos" <?php echo $filtro_estado === 'todos' ? 'selected' : ''; ?>>Todos los estados</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="noticia_id" onchange="this.form.submit()">
                        <option value="0">Todas las noticias</option>
                        <?php foreach($noticias as $noticia): ?>
                        <option value="<?php echo $noticia['id']; ?>" 
                            <?php echo $filtro_noticia == $noticia['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars(mb_substr($noticia['PostTitle'], 0, 50)); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
            
            <!-- Filtros rápidos -->
            <div class="mt-3">
                <small class="text-muted d-block mb-2">Filtrar rápido:</small>
                <div>
                    <span class="filter-badge <?php echo $filtro_estado === 'pendientes' ? 'active' : ''; ?>" 
                          onclick="window.location='?busqueda=<?php echo urlencode($busqueda); ?>&estado=pendientes&noticia_id=<?php echo $filtro_noticia; ?>'">
                        Pendientes (<?php echo $total_pendientes; ?>)
                    </span>
                    <span class="filter-badge <?php echo $filtro_estado === 'aprobados' ? 'active' : ''; ?>" 
                          onclick="window.location='?busqueda=<?php echo urlencode($busqueda); ?>&estado=aprobados&noticia_id=<?php echo $filtro_noticia; ?>'">
                        Aprobados (<?php echo $total_aprobados; ?>)
                    </span>
                    <span class="filter-badge <?php echo $filtro_estado === 'todos' ? 'active' : ''; ?>" 
                          onclick="window.location='?busqueda=<?php echo urlencode($busqueda); ?>&estado=todos&noticia_id=<?php echo $filtro_noticia; ?>'">
                        Todos (<?php echo $total_comentarios; ?>)
                    </span>
                    <?php if (!empty($busqueda) || $filtro_noticia > 0): ?>
                    <span class="filter-badge bg-danger text-white" 
                          onclick="window.location='?'">
                        <i class="fas fa-times me-1"></i> Limpiar filtros
                    </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Lista de comentarios -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i> Comentarios
                </h5>
                <small class="text-muted">
                    Mostrando <?php echo min($por_pagina, count($comentarios)); ?> de <?php echo $total_comentarios; ?> comentarios
                </small>
            </div>
            
            <div class="card-body">
                <?php if (count($comentarios) > 0): ?>
                    <div class="row">
                        <?php foreach($comentarios as $comentario): ?>
                        <div class="col-md-12 mb-4">
                            <div class="comentario-card">
                                <div class="row">
                                    <!-- Avatar y info del usuario -->
                                    <div class="col-md-2">
                                        <div class="user-avatar mb-3">
                                            <?php echo strtoupper(substr($comentario['name'], 0, 1)); ?>
                                        </div>
                                        <div class="text-center">
                                            <strong><?php echo htmlspecialchars($comentario['name']); ?></strong>
                                            <br>
                                            <small class="text-muted">
                                                <i class="fas fa-envelope me-1"></i>
                                                <?php echo htmlspecialchars($comentario['email']); ?>
                                            </small>
                                        </div>
                                    </div>
                                    
                                    <!-- Contenido del comentario -->
                                    <div class="col-md-7">
                                        <div class="comment-text mb-3">
                                            <?php echo nl2br(htmlspecialchars($comentario['comment'])); ?>
                                        </div>
                                        
                                        <div class="mt-3">
                                            <?php if(!empty($comentario['PostTitle'])): ?>
                                            <span class="badge bg-light text-dark">
                                                <i class="fas fa-newspaper me-1"></i>
                                                <?php echo htmlspecialchars(mb_substr($comentario['PostTitle'], 0, 60)); ?>
                                            </span>
                                            <?php endif; ?>
                                            
                                            <small class="text-muted ms-3">
                                                <i class="fas fa-calendar-alt me-1"></i>
                                                <?php echo date('d/m/Y H:i', strtotime($comentario['postingDate'])); ?>
                                            </small>
                                        </div>
                                    </div>
                                    
                                    <!-- Estado y acciones -->
                                    <div class="col-md-3">
                                        <div class="text-center mb-3">
                                            <?php if($comentario['status'] == 0): ?>
                                            <span class="badge-estado badge-pendiente">
                                                <i class="fas fa-clock me-1"></i> Pendiente
                                            </span>
                                            <?php else: ?>
                                            <span class="badge-estado badge-aprobado">
                                                <i class="fas fa-check-circle me-1"></i> Aprobado
                                            </span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="d-grid gap-2">
                                            <?php if($comentario['status'] == 0): ?>
                                            <button onclick="aprobarComentario(<?php echo $comentario['id']; ?>, '<?php echo htmlspecialchars(addslashes($comentario['name'])); ?>')" 
                                                    class="btn btn-aprobar btn-accion">
                                                <i class="fas fa-check me-1"></i> Aprobar
                                            </button>
                                            <?php else: ?>
                                            <button onclick="desaprobarComentario(<?php echo $comentario['id']; ?>, '<?php echo htmlspecialchars(addslashes($comentario['name'])); ?>')" 
                                                    class="btn btn-desaprobar btn-accion">
                                                <i class="fas fa-ban me-1"></i> Desaprobar
                                            </button>
                                            <?php endif; ?>
                                            
                                            <button onclick="eliminarComentario(<?php echo $comentario['id']; ?>, '<?php echo htmlspecialchars(addslashes($comentario['name'])); ?>')" 
                                                    class="btn btn-eliminar btn-accion">
                                                <i class="fas fa-trash me-1"></i> Eliminar
                                            </button>
                                            
                                            
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Paginación -->
                    <?php if ($total_paginas > 1): ?>
                    <nav aria-label="Navegación de páginas" class="mt-4">
                        <ul class="pagination pagination-custom justify-content-center mb-0">
                            <!-- Botón anterior -->
                            <li class="page-item <?php echo $pagina_actual == 1 ? 'disabled' : ''; ?>">
                                <a class="page-link" 
                                   href="?pagina=<?php echo $pagina_actual - 1; ?>&busqueda=<?php echo urlencode($busqueda); ?>&estado=<?php echo $filtro_estado; ?>&noticia_id=<?php echo $filtro_noticia; ?>">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                            
                            <!-- Páginas -->
                            <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                                <?php if ($i == 1 || $i == $total_paginas || ($i >= $pagina_actual - 2 && $i <= $pagina_actual + 2)): ?>
                                <li class="page-item <?php echo $i == $pagina_actual ? 'active' : ''; ?>">
                                    <a class="page-link" 
                                       href="?pagina=<?php echo $i; ?>&busqueda=<?php echo urlencode($busqueda); ?>&estado=<?php echo $filtro_estado; ?>&noticia_id=<?php echo $filtro_noticia; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                                <?php elseif ($i == $pagina_actual - 3 || $i == $pagina_actual + 3): ?>
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                                <?php endif; ?>
                            <?php endfor; ?>
                            
                            <!-- Botón siguiente -->
                            <li class="page-item <?php echo $pagina_actual == $total_paginas ? 'disabled' : ''; ?>">
                                <a class="page-link" 
                                   href="?pagina=<?php echo $pagina_actual + 1; ?>&busqueda=<?php echo urlencode($busqueda); ?>&estado=<?php echo $filtro_estado; ?>&noticia_id=<?php echo $filtro_noticia; ?>">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                    <?php endif; ?>
                    
                <?php else: ?>
                    <div class="empty-state">
                        <?php if (!empty($busqueda)): ?>
                        <i class="fas fa-search fa-4x text-muted mb-3"></i>
                        <h4>No se encontraron comentarios</h4>
                        <p class="text-muted mb-4">
                            No hay comentarios que coincidan con tu búsqueda "<?php echo htmlspecialchars($busqueda); ?>"
                        </p>
                        <a href="?" class="btn btn-primary">
                            <i class="fas fa-times me-2"></i> Limpiar búsqueda
                        </a>
                        <?php else: ?>
                        <i class="fas fa-comments fa-4x text-muted mb-3"></i>
                        <h4>No hay comentarios</h4>
                        <p class="text-muted mb-4">
                            No hay comentarios en el sistema para el filtro seleccionado
                        </p>
                        <a href="?estado=pendientes" class="btn btn-primary">
                            <i class="fas fa-eye me-2"></i> Ver pendientes
                        </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        // Confirmar aprobación
        function aprobarComentario(id, nombre) {
            Swal.fire({
                title: '¿Aprobar comentario?',
                html: `
                    <div class="text-start">
                        <p>¿Estás seguro de que deseas aprobar este comentario?</p>
                        <div class="alert alert-success p-3 mt-2">
                            <strong>Usuario:</strong> ${nombre}<br>
                            <small>El comentario será visible para todos los visitantes.</small>
                        </div>
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#2e7d32',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-check me-2"></i> Sí, aprobar',
                cancelButtonText: '<i class="fas fa-times me-2"></i> Cancelar',
                reverseButtons: true,
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `?action=aprobar&id=${id}&pagina=<?php echo $pagina_actual; ?>`;
                }
            });
        }
        
        // Confirmar desaprobación (inhabilitar)
        function desaprobarComentario(id, nombre) {
            Swal.fire({
                title: '¿Desaprobar comentario?',
                html: `
                    <div class="text-start">
                        <p>¿Estás seguro de que deseas desaprobar este comentario?</p>
                        <div class="alert alert-warning p-3 mt-2">
                            <strong>Usuario:</strong> ${nombre}<br>
                            <small>El comentario se ocultará pero no se eliminará permanentemente.</small>
                        </div>
                    </div>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#b37400',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-ban me-2"></i> Sí, desaprobar',
                cancelButtonText: '<i class="fas fa-times me-2"></i> Cancelar',
                reverseButtons: true,
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `?action=desaprobar&id=${id}&pagina=<?php echo $pagina_actual; ?>`;
                }
            });
        }
        
        // Confirmar eliminación
        function eliminarComentario(id, nombre) {
            Swal.fire({
                title: '¿Eliminar comentario?',
                html: `
                    <div class="text-start">
                        <p>¿Estás seguro de que deseas eliminar permanentemente este comentario?</p>
                        <div class="alert alert-danger p-3 mt-2">
                            <strong>Usuario:</strong> ${nombre}<br>
                            <small><i class="fas fa-exclamation-triangle me-1"></i> Esta acción no se puede deshacer.</small>
                        </div>
                    </div>
                `,
                icon: 'error',
                showCancelButton: true,
                confirmButtonColor: '#c62828',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-trash me-2"></i> Sí, eliminar',
                cancelButtonText: '<i class="fas fa-times me-2"></i> Cancelar',
                reverseButtons: true,
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `?action=eliminar&id=${id}&pagina=<?php echo $pagina_actual; ?>`;
                }
            });
        }
        
        // Mostrar mensajes de sesión
        <?php if (isset($_SESSION['mensaje'])): ?>
        Swal.fire({
            icon: '<?php echo isset($_SESSION['icono_mensaje']) ? $_SESSION['icono_mensaje'] : "info"; ?>',
            title: '<?php echo isset($_SESSION['titulo_mensaje']) ? $_SESSION['titulo_mensaje'] : "Información"; ?>',
            text: '<?php echo $_SESSION['mensaje']; ?>',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });
        <?php 
        unset($_SESSION['mensaje']);
        unset($_SESSION['icono_mensaje']);
        unset($_SESSION['titulo_mensaje']);
        ?>
        <?php endif; ?>
        
        // Auto-focus en búsqueda si hay parámetros
        <?php if (!empty($busqueda)): ?>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelector('input[name="busqueda"]').focus();
            document.querySelector('input[name="busqueda"]').select();
        });
        <?php endif; ?>
        
        // Auto-recargar si hay comentarios pendientes
        <?php if ($filtro_estado === 'pendientes' && $total_pendientes > 0): ?>
        setInterval(function() {
            fetch(window.location.href)
                .then(response => response.text())
                .then(html => {
                    // Extraer contador de pendientes de la respuesta
                    var match = html.match(/Pendientes.*?\((\d+)\)/);
                    if (match) {
                        var newCount = parseInt(match[1]);
                        var currentCount = <?php echo $total_pendientes; ?>;
                        if (newCount !== currentCount) {
                            Swal.fire({
                                title: '¡Nuevos comentarios!',
                                text: 'Hay nuevos comentarios pendientes por revisar',
                                icon: 'info',
                                confirmButtonText: 'Actualizar',
                                allowOutsideClick: false
                            }).then(() => {
                                location.reload();
                            });
                        }
                    }
                })
                .catch(error => console.log('Error al verificar:', error));
        }, 30000); // Cada 30 segundos
        <?php endif; ?>
        
        // Inicializar tooltips
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
</body>
</html>