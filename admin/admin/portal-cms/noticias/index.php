<?php
session_start();
require_once __DIR__ . '/../session-compat.php';

// Verificar sesión
if (!verificarSesionCMS()) {
    header('Location: ../../../login/login.php');
    exit;
}

// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "sige");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Obtener noticias con paginación
$por_pagina = 10;
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$inicio = ($pagina > 1) ? ($pagina * $por_pagina) - $por_pagina : 0;

// Contar total de noticias
$sql_total = "SELECT COUNT(*) as total FROM tblposts";
$resultado_total = $conexion->query($sql_total);
$total_noticias = $resultado_total->fetch_assoc()['total'];
$total_paginas = ceil($total_noticias / $por_pagina);

// Obtener noticias para esta página
$sql = "SELECT p.*, c.CategoryName as categoria, s.Subcategory as subcategoria 
        FROM tblposts p 
        LEFT JOIN tblcategory c ON p.CategoryId = c.id 
        LEFT JOIN tblsubcategory s ON p.SubCategoryId = s.SubCategoryId 
        ORDER BY p.PostingDate DESC 
        LIMIT $inicio, $por_pagina";
$resultado = $conexion->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Noticias - Portal Escolar</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
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
        
        .news-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            margin-bottom: 20px;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .news-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .news-image {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }
        
        .news-title {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 10px;
            font-size: 1.1rem;
        }
        
        .news-meta {
            font-size: 0.85rem;
            color: #6c757d;
            margin-bottom: 10px;
        }
        
        .badge-status {
            font-size: 0.75rem;
            padding: 4px 10px;
            border-radius: 20px;
        }
        
        .btn-action {
            padding: 5px 15px;
            font-size: 0.85rem;
            margin-right: 5px;
            border-radius: 6px;
        }
        
        .pagination .page-link {
            color: var(--primary-color);
            border-radius: 6px;
            margin: 0 3px;
        }
        
        .pagination .page-item.active .page-link {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }
        
        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: #6c757d;
        }
        
        .empty-state i {
            font-size: 4rem;
            color: #dee2e6;
            margin-bottom: 20px;
        }
        
        .preview-image {
            width: 80px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
        }
        
        .table-actions {
            white-space: nowrap;
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
                        <i class="fas fa-newspaper"></i> Gestión de Noticias
                    </h1>
                    <small>
                        <i class="fas fa-chart-bar"></i>
                        Total de noticias: <?php echo $total_noticias; ?>
                    </small>
                </div>
                <div>
                    <a href="../index.php" class="btn btn-light btn-sm me-2">
                        <i class="fas fa-arrow-left"></i> Volver al Dashboard
                    </a>
                    <a href="crear.php" class="btn btn-success btn-sm">
                        <i class="fas fa-plus"></i> Nueva Noticia
                    </a>
                </div>
            </div>
        </div>
    </header>
    
    <div class="container">
        <!-- Filtros y búsqueda -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <form method="GET" class="row g-2">
                            <div class="col-md-8">
                                <input type="text" name="buscar" class="form-control" 
                                       placeholder="Buscar por título o contenido..." 
                                       value="<?php echo isset($_GET['buscar']) ? htmlspecialchars($_GET['buscar']) : ''; ?>">
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search"></i> Buscar
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-6 text-end">
                        <a href="" class="">
                            <i class="t"></i> 
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Listado de noticias -->
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-list text-primary me-2"></i>
                    Lista de Noticias
                </h5>
                <span class="badge bg-primary">
                    <?php echo $total_noticias; ?> noticias
                </span>
            </div>
            <div class="card-body p-0">
                <?php if ($resultado->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="80">Imagen</th>
                                <th>Título</th>
                                <th>Categoría</th>
                                <th width="150">Fecha</th>
                                <th width="100">Estado</th>
                                <th width="200" class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($noticia = $resultado->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($noticia['PostImage'])): ?>
                                    <img src="../../../admin/uploads/post/<?php echo htmlspecialchars($noticia['PostImage']); ?>" 
                                         class="preview-image" 
                                         onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iODAiIGhlaWdodD0iNjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHJlY3Qgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgZmlsbD0iI2RkZCIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMTIiIGZpbGw9IiM5OTkiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIj5TaW4gSW1nPC90ZXh0Pjwvc3ZnPg=='"
                                         alt="<?php echo htmlspecialchars($noticia['PostTitle']); ?>">
                                    <?php else: ?>
                                    <span class="text-muted small">Sin imagen</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="news-title">
                                        <?php echo htmlspecialchars($noticia['PostTitle']); ?>
                                    </div>
                                    <div class="news-meta">
                                        <small>
                                            <i class="fas fa-link"></i>
                                            <?php echo htmlspecialchars($noticia['PostUrl']); ?>
                                        </small>
                                    </div>
                                </td>
                                <td>
                                    <?php 
                                    $categoria = htmlspecialchars($noticia['categoria'] ?? 'Sin categoría');
                                    $subcategoria = htmlspecialchars($noticia['subcategoria'] ?? '');
                                    echo $categoria;
                                    if ($subcategoria) {
                                        echo '<br><small class="text-muted">' . $subcategoria . '</small>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <small>
                                        <i class="far fa-calendar"></i>
                                        <?php echo date('d/m/Y', strtotime($noticia['PostingDate'])); ?>
                                    </small>
                                </td>
                                <td>
                                    <span class="badge-status badge <?php echo $noticia['Is_Active'] ? 'bg-success' : 'bg-secondary'; ?>">
                                        <?php echo $noticia['Is_Active'] ? 'Activo' : 'Inactivo'; ?>
                                    </span>
                                </td>
                                <td class="table-actions">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="editar.php?id=<?php echo $noticia['id']; ?>" 
                                           class="btn btn-outline-primary btn-action" 
                                           title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        
                                        <a href="estado.php?id=<?php echo $noticia['id']; ?>&estado=<?php echo $noticia['Is_Active'] ? '0' : '1'; ?>" 
                                           class="btn btn-outline-<?php echo $noticia['Is_Active'] ? 'warning' : 'success'; ?> btn-action"
                                           title="<?php echo $noticia['Is_Active'] ? 'Desactivar' : 'Activar'; ?>">
                                            <i class="fas fa-power-off"></i>
                                        </a>
                                        
                                        <a href="eliminar.php?id=<?php echo $noticia['id']; ?>" 
                                           class="btn btn-outline-danger btn-action"
                                           onclick="return confirm('¿Estás seguro de eliminar esta noticia?');"
                                           title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                        
                                        <a href="../../../noticias.php" 
                                           target="_blank"
                                           class="btn btn-outline-info btn-action"
                                           title="Ver en el portal">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <i class="far fa-newspaper"></i>
                    <h4>No hay noticias publicadas</h4>
                    <p>Comienza creando tu primera noticia</p>
                    <a href="crear.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Crear Primera Noticia
                    </a>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Paginación -->
            <?php if ($total_paginas > 1): ?>
            <div class="card-footer bg-white">
                <nav aria-label="Paginación de noticias">
                    <ul class="pagination justify-content-center mb-0">
                        <li class="page-item <?php echo $pagina <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?pagina=<?php echo $pagina - 1; ?>">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>
                        
                        <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                        <li class="page-item <?php echo $pagina == $i ? 'active' : ''; ?>">
                            <a class="page-link" href="?pagina=<?php echo $i; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                        <?php endfor; ?>
                        
                        <li class="page-item <?php echo $pagina >= $total_paginas ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?pagina=<?php echo $pagina + 1; ?>">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Información adicional -->
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="fas fa-info-circle text-primary me-2"></i> Información</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-2">
                            <strong><i class="fas fa-check-circle text-success me-2"></i> Noticias activas:</strong>
                            <?php 
                            $sql_activas = "SELECT COUNT(*) as activas FROM tblposts WHERE Is_Active = 1";
                            $result_activas = $conexion->query($sql_activas);
                            $activas = $result_activas->fetch_assoc()['activas'];
                            echo $activas;
                            ?>
                        </p>
                        <p class="mb-2">
                            <strong><i class="fas fa-times-circle text-secondary me-2"></i> Noticias inactivas:</strong>
                            <?php echo $total_noticias - $activas; ?>
                        </p>
                        <p class="mb-0">
                            <strong><i class="fas fa-calendar-alt text-info me-2"></i> Última publicación:</strong>
                            <?php 
                            $sql_ultima = "SELECT PostingDate FROM tblposts ORDER BY PostingDate DESC LIMIT 1";
                            $result_ultima = $conexion->query($sql_ultima);
                            if ($row_ultima = $result_ultima->fetch_assoc()) {
                                echo date('d/m/Y H:i', strtotime($row_ultima['PostingDate']));
                            } else {
                                echo 'No hay publicaciones';
                            }
                            ?>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="fas fa-bolt text-warning me-2"></i> Acciones Rápidas</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="crear.php" class="btn btn-success">
                                <i class="fas fa-plus me-2"></i> Crear Nueva Noticia
                            </a>
                            <a href="../../../noticias.php" target="_blank" class="btn btn-outline-primary">
                                <i class="fas fa-external-link-alt me-2"></i> Ver Noticias en el Portal
                            </a>
                            
                        
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        // Mostrar mensajes de sesión
        <?php if (isset($_SESSION['mensaje'])): ?>
        Swal.fire({
            icon: '<?php echo isset($_SESSION['icono']) ? $_SESSION['icono'] : "info"; ?>',
            title: '<?php echo isset($_SESSION['titulo']) ? $_SESSION['titulo'] : "Información"; ?>',
            text: '<?php echo $_SESSION['mensaje']; ?>',
            timer: 3000,
            showConfirmButton: false
        });
        <?php 
        unset($_SESSION['mensaje']);
        unset($_SESSION['icono']);
        unset($_SESSION['titulo']);
        ?>
        <?php endif; ?>
        
        // Confirmación para eliminar
        function confirmarEliminar(url) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "Esta acción no se puede deshacer",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            });
        }
        
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

<?php $conexion->close(); ?>