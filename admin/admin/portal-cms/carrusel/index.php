<?php
// admin/admin/portal-cms/carrusel/index.php - VERSIÓN CON SWEETALERT2

// ================= CONEXIÓN MANUAL =================
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sige";

// Crear conexión
$con = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($con->connect_error) {
    die("Error de conexión a la base de datos: " . $con->connect_error);
}

// Configurar charset
$con->set_charset("utf8");
// ====================================================

// Sesión simple (por ahora)
session_start();

// Verificar autenticación básica - TEMPORAL
if (!isset($_SESSION['portal_admin_logged_in'])) {
    // Sistema de login temporal para pruebas
    if (!isset($_GET['admin_pass'])) {
        die('<div style="padding: 50px; text-align: center;">
                <h2>Acceso al CMS del Portal</h2>
                <p>Para acceder al panel de administración:</p>
                <form method="GET">
                    <input type="password" name="admin_pass" placeholder="Contraseña temporal" style="padding: 10px; margin: 10px;">
                    <button type="submit" style="padding: 10px 20px; background: #1a4b8c; color: white; border: none;">Acceder</button>
                </form>
                <p><small>Usa cualquier contraseña por ahora (será reemplazado)</small></p>
            </div>');
    } else {
        $_SESSION['portal_admin_logged_in'] = true;
        $_SESSION['portal_admin_user'] = 'Administrador Portal';
        header('Location: index.php');
        exit;
    }
}

// ================= CÓDIGO DEL CARRUSEL =================
// Obtener todos los slides del carrusel
$query = "SELECT * FROM carrusel ORDER BY fecha_creacion DESC";
$result = mysqli_query($con, $query);
$slides = [];
if ($result) {
    $slides = mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Manejar activar/desactivar
if (isset($_GET['toggle'])) {
    $id = intval($_GET['toggle']);
    $toggleQuery = "UPDATE carrusel SET activo = NOT activo WHERE id = $id";
    mysqli_query($con, $toggleQuery);
    
    $_SESSION['mensaje_tipo'] = 'success';
    $_SESSION['mensaje'] = 'Estado del slide actualizado correctamente';
    header('Location: index.php');
    exit;
}

// Manejar eliminación
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    
    // Primero obtener la ruta de la imagen para eliminarla
    $imgQuery = "SELECT imagen_path, titulo FROM carrusel WHERE id = $id";
    $imgResult = mysqli_query($con, $imgQuery);
    $slide_titulo = '';
    
    if ($imgResult && $imgRow = mysqli_fetch_assoc($imgResult)) {
        $slide_titulo = $imgRow['titulo'];
        // Eliminar archivo físico si existe
        $ruta_imagen = '../../../../uploads/carrusel/' . $imgRow['imagen_path'];
        if (file_exists($ruta_imagen)) {
            unlink($ruta_imagen);
        }
    }
    
    // Eliminar de la BD
    $deleteQuery = "DELETE FROM carrusel WHERE id = $id";
    mysqli_query($con, $deleteQuery);
    
    $_SESSION['mensaje_tipo'] = 'success';
    $_SESSION['mensaje'] = 'Slide "' . htmlspecialchars($slide_titulo) . '" eliminado correctamente';
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Carrusel - Portal CMS</title>
    
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
            transition: transform 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-3px);
        }
        
        .card-img-top {
            height: 150px;
            object-fit: cover;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }
        
        .badge-activo {
            background-color: #28a745;
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .badge-activo:hover {
            background-color: #1e7e34;
            transform: scale(1.05);
        }
        
        .badge-inactivo {
            background-color: #dc3545;
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .badge-inactivo:hover {
            background-color: #bd2130;
            transform: scale(1.05);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .table th {
            background-color: var(--primary-color);
            color: white;
            border: none;
        }
        
        .table td {
            vertical-align: middle;
        }
        
        .preview-img {
            width: 80px;
            height: 50px;
            object-fit: cover;
            border-radius: 5px;
            border: 1px solid #ddd;
            transition: transform 0.3s ease;
        }
        
        .preview-img:hover {
            transform: scale(1.5);
            z-index: 10;
            position: relative;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
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
        
        .login-temp {
            position: fixed;
            top: 10px;
            right: 10px;
            background: #ffc107;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.8rem;
            z-index: 1000;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .slide-count {
            background: var(--primary-color);
            color: white;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        
        .empty-state {
            padding: 60px 20px;
            text-align: center;
            color: #6c757d;
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.5;
        }
    </style>
</head>
<body>
    <!-- Indicador de login temporal -->
    <div class="login-temp">
        <i class="fas fa-exclamation-triangle"></i> Login Temporal
    </div>
    
    <!-- Header -->
    <div class="header-cms">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-images"></i> Gestión de Carrusel
                    </h1>
                    <p class="mb-0">Administra las diapositivas del carrusel principal</p>
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
        <!-- Barra de acciones -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-0">Diapositivas del Carrusel</h5>
                        <p class="text-muted mb-0">
                            Total: <span class="slide-count"><?php echo count($slides); ?></span> Diapositivas | 
                            Activos: <span class="badge bg-success"><?php echo count(array_filter($slides, function($s) { return $s['activo']; })); ?></span>
                        </p>
                    </div>
                    <div>
                        <a href="crear.php" class="btn btn-primary">
                            <i class="fas fa-plus-circle"></i> Nueva Diapositiva
                        </a>
                        <a href="../index.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Tabla de slides -->
        <div class="card">
            <div class="card-body p-0">
                <?php if (count($slides) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th width="80">Imagen</th>
                                    <th>Título</th>
                                    <th>Fechas</th>
                                    <th width="100">Estado</th>
                                    <th width="140">Fecha Creación</th>
                                    <th width="180">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($slides as $slide): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($slide['imagen_path'])): ?>
                                            <?php
                                            // Verificar si la imagen existe físicamente
                                            $ruta_fisica = '../../../../uploads/carrusel/' . $slide['imagen_path'];
                                            $imagen_existe = file_exists($ruta_fisica);
                                            $ruta_imagen = '/heldyn/centeno/uploads/carrusel/' . htmlspecialchars($slide['imagen_path']);
                                            ?>
                                            <img src="<?php echo $imagen_existe ? $ruta_imagen : 'https://placehold.co/80x50/1a4b8c/white?text=Imagen+no+disponible'; ?>" 
                                                 alt="Preview" 
                                                 class="preview-img"
                                                 title="<?php echo $imagen_existe ? 'Haz clic para ampliar' : 'Imagen no encontrada en el servidor'; ?>">
                                        <?php else: ?>
                                            <img src="https://placehold.co/80x50/1a4b8c/white?text=Sin+Imagen" 
                                                 alt="Sin imagen" 
                                                 class="preview-img">
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($slide['titulo']); ?></strong>
                                        <?php if (!empty($slide['descripcion'])): ?>
                                            <br><small class="text-muted"><?php echo substr(htmlspecialchars($slide['descripcion']), 0, 50); ?>...</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small>
                                            <strong>Inicio:</strong> <?php echo $slide['fecha_inicio'] ? date('d/m/Y', strtotime($slide['fecha_inicio'])) : '--'; ?><br>
                                            <strong>Fin:</strong> <?php echo $slide['fecha_fin'] ? date('d/m/Y', strtotime($slide['fecha_fin'])) : '--'; ?>
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge rounded-pill <?php echo $slide['activo'] ? 'badge-activo' : 'badge-inactivo'; ?> status-toggle"
                                              data-id="<?php echo $slide['id']; ?>"
                                              data-titulo="<?php echo htmlspecialchars($slide['titulo']); ?>"
                                              data-activo="<?php echo $slide['activo']; ?>">
                                            <?php echo $slide['activo'] ? 'Activo' : 'Inactivo'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small>
                                            <?php echo date('d/m/Y', strtotime($slide['fecha_creacion'])); ?><br>
                                            <?php echo date('H:i', strtotime($slide['fecha_creacion'])); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="editar.php?id=<?php echo $slide['id']; ?>" 
                                               class="btn btn-warning btn-sm"
                                               title="Editar slide">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            
                                            <button onclick="confirmarEliminacion(<?php echo $slide['id']; ?>, '<?php echo addslashes($slide['titulo']); ?>')" 
                                                    class="btn btn-danger btn-sm"
                                                    title="Eliminar Diapositiva">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            
                                            <a href="/heldyn/centeno/" 
                                               target="_blank"
                                               class="btn btn-info btn-sm"
                                               title="Ver en sitio web">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-images"></i>
                        <h4>No hay Diapositivas en el carrusel</h4>
                        <p class="text-muted mb-4">Crea tu primera Diapositiva para que aparezca en la página principal del portal.</p>
                        <a href="crear.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-plus-circle"></i> Crear Primera Diapositiva
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Información -->
        <div class="alert alert-info mt-4">
            <div class="d-flex">
                <div class="me-3">
                    <i class="fas fa-info-circle fa-2x"></i>
                </div>
                <div>
                    <h5 class="alert-heading">Información importante:</h5>
                    <ul class="mb-0">
                        <li>Los slides <strong class="text-success">activos</strong> aparecerán en la página principal del portal.</li>
                        <li>Las fechas definen el período de visualización del slide (deja vacío para mostrar siempre).</li>
                        <li><strong>Recomendado:</strong> Máximo 5 slides activos a la vez para mejor rendimiento.</li>
                        <li><strong>Dimensiones:</strong> 1200x500 px para mejor calidad.</li>
                        <li><strong class="text-warning">Nota:</strong> Sistema de login temporal. Se integrará con el sistema principal.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        // Función para confirmar eliminación con SweetAlert2
        function confirmarEliminacion(id, titulo) {
            Swal.fire({
                title: '¿Eliminar slide?',
                html: `<div class="text-start">
                        <p>¿Estás seguro de eliminar el slide?</p>
                        <div class="alert alert-danger py-2">
                            <strong>${titulo}</strong>
                        </div>
                        <p class="text-danger small">
                            <i class="fas fa-exclamation-triangle"></i> Esta acción eliminará:
                            <ul class="small">
                                <li>El registro de la base de datos</li>
                                <li>La imagen asociada</li>
                            </ul>
                            <strong>¡Esta acción no se puede deshacer!</strong>
                        </p>
                       </div>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-trash"></i> Sí, eliminar',
                cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
                reverseButtons: true,
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return new Promise((resolve) => {
                        setTimeout(() => {
                            resolve();
                        }, 1000);
                    });
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `?eliminar=${id}`;
                }
            });
        }

        // Función para cambiar estado con SweetAlert2
        document.querySelectorAll('.status-toggle').forEach(badge => {
            badge.addEventListener('click', function(e) {
                e.preventDefault();
                const id = this.getAttribute('data-id');
                const titulo = this.getAttribute('data-titulo');
                const estadoActual = this.getAttribute('data-activo') === '1';
                const nuevoEstado = estadoActual ? 'inactivo' : 'activo';
                const accion = estadoActual ? 'desactivar' : 'activar';
                
                Swal.fire({
                    title: `¿${accion.charAt(0).toUpperCase() + accion.slice(1)} slide?`,
                    html: `<div class="text-start">
                            <p>¿Estás seguro de <strong>${accion}</strong> el slide?</p>
                            <div class="alert ${estadoActual ? 'alert-warning' : 'alert-success'} py-2">
                                <strong>${titulo}</strong>
                            </div>
                            <p class="small">
                                Los slides <strong>${nuevoEstado}s</strong> ${estadoActual ? 'no se mostrarán' : 'se mostrarán'} en el portal web.
                            </p>
                           </div>`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: estadoActual ? '#ffc107' : '#28a745',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: `<i class="fas fa-${estadoActual ? 'ban' : 'check'}"></i> Sí, ${accion}`,
                    cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
                    reverseButtons: true,
                    showLoaderOnConfirm: true,
                    preConfirm: () => {
                        return new Promise((resolve) => {
                            setTimeout(() => {
                                resolve();
                            }, 1000);
                        });
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = `?toggle=${id}`;
                    }
                });
            });
        });

        // Mostrar mensajes de sesión con SweetAlert2
        <?php if (isset($_SESSION['mensaje'])): ?>
        Swal.fire({
            title: '<?php echo isset($_SESSION['mensaje_tipo']) && $_SESSION['mensaje_tipo'] == 'error' ? "Error" : "¡Éxito!"; ?>',
            text: '<?php echo addslashes($_SESSION['mensaje']); ?>',
            icon: '<?php echo isset($_SESSION['mensaje_tipo']) ? $_SESSION['mensaje_tipo'] : "success"; ?>',
            confirmButtonColor: '#1a4b8c',
            timer: 3000,
            timerProgressBar: true,
            showConfirmButton: false
        });
        <?php 
        unset($_SESSION['mensaje']);
        unset($_SESSION['mensaje_tipo']);
        endif; 
        ?>

        // Agrandar imagen al hacer clic
        document.querySelectorAll('.preview-img').forEach(img => {
            img.addEventListener('click', function(e) {
                e.stopPropagation();
                Swal.fire({
                    imageUrl: this.src,
                    imageAlt: this.alt,
                    imageWidth: 400,
                    imageHeight: 250,
                    showCloseButton: true,
                    showConfirmButton: false,
                    background: 'transparent',
                    backdrop: 'rgba(0,0,0,0.8)'
                });
            });
        });

        // Verificar si hay slides activos y mostrar advertencia si hay muchos
        document.addEventListener('DOMContentLoaded', function() {
            const slidesActivos = document.querySelectorAll('.badge-activo').length;
            if (slidesActivos > 5) {
                Swal.fire({
                    title: 'Muchos slides activos',
                    text: `Tienes ${slidesActivos} slides activos. Se recomienda un máximo de 5 para mejor rendimiento.`,
                    icon: 'warning',
                    confirmButtonColor: '#1a4b8c',
                    timer: 5000,
                    timerProgressBar: true,
                    showConfirmButton: true
                });
            }
        });
    </script>
</body>
</html>