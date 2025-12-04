<?php  
include ('../app/config.php');  
include ('../admin/layout/parte1.php');  
include ('../app/controllers/roles/listado_de_roles.php');  
include ('../app/controllers/usuarios/listado_de_usuarios.php');  
include ('../app/controllers/niveles/listado_de_niveles.php');  
include ('../app/controllers/grados/listado_de_grados.php');  
include ('../app/controllers/administrativos/listado_de_administrativos.php');  
include ('../app/controllers/representantes/listado_de_representantes.php');  
include ('../app/controllers/estudiantes/listado_de_estudiantes.php');  
include ('../app/controllers/estudiantes/reporte_estudiantes.php');  
include ('../app/controllers/secciones/listado_de_secciones.php');  
include ('../app/controllers/estudiantes/lista_inscripcion.php');  

// Funciones auxiliares
function getPeriodoEscolarActivo($pdo) {  
    $sql = "SELECT * FROM gestiones WHERE estado = '1' LIMIT 1";  
    $stmt = $pdo->prepare($sql);  
    $stmt->execute();  
    return $stmt->fetch(PDO::FETCH_ASSOC);  
}  

function getInscripcionesByGestion($pdo, $id_gestion) {  
    $sql = "SELECT * FROM inscripciones WHERE id_gestion = :id_gestion";  
    $stmt = $pdo->prepare($sql);  
    $stmt->bindParam(':id_gestion', $id_gestion, PDO::PARAM_INT);  
    $stmt->execute();  
    return $stmt->fetchAll(PDO::FETCH_ASSOC);  
}

// Consultas para estudiantes registrados pero no inscritos - CORREGIDA
function getEstudiantesNoInscritos($pdo, $id_gestion_activa) {
    $sql = "SELECT e.* 
            FROM estudiantes e 
            LEFT JOIN inscripciones i ON e.id_estudiante = i.id_estudiante AND i.id_gestion = :id_gestion
            WHERE e.estatus = 'activo' AND i.id IS NULL";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_gestion', $id_gestion_activa, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Consulta para inscripciones por año/grado - CORREGIDA
function getInscripcionesPorGrado($pdo, $id_gestion_activa) {
    $sql = "SELECT g.grado, COUNT(i.id) as total
            FROM inscripciones i 
            JOIN grados g ON i.grado = g.id_grado 
            WHERE i.id_gestion = :id_gestion 
            GROUP BY g.grado 
            ORDER BY g.grado";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_gestion', $id_gestion_activa, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// CONSULTAS ESPECÍFICAS PARA DOCENTES - CON MANEJO DE ERRORES
function getDatosDocente($pdo, $id_usuario) {
    if (!$id_usuario) return ['nombre' => 'Docente'];
    
    try {
        $sql = "SELECT * FROM usuarios WHERE id_usuario = :id_usuario";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: ['nombre' => 'Docente'];
    } catch (PDOException $e) {
        return ['nombre' => 'Docente'];
    }
}

function getCursosDocente($pdo, $id_usuario) {
    if (!$id_usuario) return ['total_cursos' => 0];
    
    try {
        // Verificar si la tabla cursos existe
        $stmt = $pdo->query("SHOW TABLES LIKE 'cursos'");
        if (!$stmt->fetch()) {
            return ['total_cursos' => 0];
        }
        
        $sql = "SELECT COUNT(*) as total_cursos FROM cursos WHERE id_docente = :id_docente AND estado = '1'";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id_docente', $id_usuario, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: ['total_cursos' => 0];
    } catch (PDOException $e) {
        return ['total_cursos' => 0];
    }
}

function getEstudiantesDocente($pdo, $id_usuario) {
    if (!$id_usuario) return ['total_estudiantes' => 0];
    
    try {
        // Verificar si las tablas necesarias existen
        $stmt = $pdo->query("SHOW TABLES LIKE 'cursos'");
        if (!$stmt->fetch()) {
            return ['total_estudiantes' => 0];
        }
        
        $sql = "SELECT COUNT(DISTINCT e.id_estudiante) as total_estudiantes 
                FROM estudiantes e 
                JOIN inscripciones i ON e.id_estudiante = i.id_estudiante 
                JOIN cursos c ON i.grado = c.grado 
                WHERE c.id_docente = :id_docente AND c.estado = '1'";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id_docente', $id_usuario, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: ['total_estudiantes' => 0];
    } catch (PDOException $e) {
        return ['total_estudiantes' => 0];
    }
}

function getCalificacionesPendientes($pdo, $id_usuario) {
    if (!$id_usuario) return ['pendientes' => 0];
    
    try {
        // Verificar si las tablas necesarias existen
        $stmt = $pdo->query("SHOW TABLES LIKE 'actividades'");
        if (!$stmt->fetch()) {
            return ['pendientes' => 0];
        }
        
        $sql = "SELECT COUNT(*) as pendientes 
                FROM actividades a 
                LEFT JOIN calificaciones c ON a.id_actividad = c.id_actividad 
                WHERE a.id_docente = :id_docente AND c.id_calificacion IS NULL";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id_docente', $id_usuario, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: ['pendientes' => 0];
    } catch (PDOException $e) {
        return ['pendientes' => 0];
    }
}

// Consultas seguras (solo para tablas que existen)
$gestion_activa = getPeriodoEscolarActivo($pdo);
$id_gestion_activa = $gestion_activa ? $gestion_activa['id_gestion'] : null;

// Obtener estudiantes no inscritos
$estudiantes_no_inscritos = [];
if ($id_gestion_activa) {
    $estudiantes_no_inscritos = getEstudiantesNoInscritos($pdo, $id_gestion_activa);
}
$contador_no_inscritos = count($estudiantes_no_inscritos);

// Obtener inscripciones por grado del año actual
$inscripciones_por_grado = [];
if ($id_gestion_activa) {
    $inscripciones_por_grado = getInscripcionesPorGrado($pdo, $id_gestion_activa);
}

// Obtener datos específicos para docentes
if ($rol_sesion_usuario == "DOCENTE") {
    // Obtener el ID del usuario de forma segura
    $id_usuario_sesion = $_SESSION['id_usuario'] ?? $_SESSION['id'] ?? $_SESSION['user_id'] ?? null;
    
    $datos_docente = getDatosDocente($pdo, $id_usuario_sesion);
    $cursos_docente = getCursosDocente($pdo, $id_usuario_sesion);
    $estudiantes_docente = getEstudiantesDocente($pdo, $id_usuario_sesion);
    $calificaciones_pendientes = getCalificacionesPendientes($pdo, $id_usuario_sesion);
}

?>  

<!-- Content Wrapper. Contains page content -->  
<div class="content-wrapper">  
    <div class="container">  
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-dark">Panel de Control</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="./index.php?page=home" class="text-info">Inicio</a></li>
                            <li class="breadcrumb-item active">Panel de Control</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        
        <hr>
        
        <!-- VISTA PARA DOCENTES -->
        <?php if ($rol_sesion_usuario == "DOCENTE") { ?>
            <div class="docente-welcome">
                <div class="row">
                    <div class="col-md-8">
                        <h3>Bienvenido, Prof. <?php echo htmlspecialchars($datos_docente['nombre'] ?? 'Docente'); ?></h3>
                        <p class="mb-0">Es un placer tenerle de vuelta. Aquí tiene un resumen de sus actividades.</p>
                    </div>
                    <div class="col-md-4 text-right">
                        <i class="fas fa-chalkboard-teacher fa-3x"></i>
                    </div>
                </div>
            </div>

            <div class="docente-stats">
                <div class="stat-card">
                    <i class="fas fa-book"></i>
                    <div class="stat-number"><?php echo $cursos_docente['total_cursos'] ?? 0; ?></div>
                    <div class="stat-label">Cursos Asignados</div>
                </div>
                
                <div class="stat-card info">
                    <i class="fas fa-user-graduate"></i>
                    <div class="stat-number"><?php echo $estudiantes_docente['total_estudiantes'] ?? 0; ?></div>
                    <div class="stat-label">Estudiantes</div>
                </div>
                
                <div class="stat-card info">
                    <i class="fas fa-tasks"></i>
                    <div class="stat-number"><?php echo $calificaciones_pendientes['pendientes'] ?? 0; ?></div>
                    <div class="stat-label">Calificaciones Pendientes</div>
                </div>
            </div>

            <div class="docente-dashboard">
                <div class="activities-card">
                    <h5><i class="fas fa-calendar-check"></i> Accesos Rápidos</h5>
                    <div class="d-grid gap-2 mt-3">
                        <a href="?page=mis_cursos" class="btn btn-primary btn-sm">
                            <i class="fas fa-book mr-1"></i> Mis Cursos
                        </a>
                        <a href="?page=calificaciones" class="btn btn-success btn-sm">
                            <i class="fas fa-edit mr-1"></i> Calificar
                        </a>
                        <a href="?page=asistencias" class="btn btn-info btn-sm">
                            <i class="fas fa-clipboard-check mr-1"></i> Asistencias
                        </a>
                    </div>
                </div>
                
                <div style="display: grid; gap: 15px;">
                    <div class="stat-card" style="text-align: center;">
                        <i class="fas fa-info-circle fa-2x"></i>
                        <h5>Periodo Actual</h5>
                        <p class="mb-1">
                            <?php if ($gestion_activa) {  
                                $año_inicio = date('Y', strtotime($gestion_activa['desde']));  
                                $año_fin = date('Y', strtotime($gestion_activa['hasta']));  
                                echo "{$año_inicio}-{$año_fin}";  
                            } else {  
                                echo "No activo";  
                            } ?>
                        </p>
                        <small class="text-muted">Año Escolar</small>
                    </div>
                </div>
            </div>
            
            <hr>
        <?php } ?>
        
        <!-- Vista para el administrador -->  
        <?php if ($rol_sesion_usuario == "ADMINISTRADOR") { ?>  
            <div class="row"> 
                 
               <!-- Tarjeta 1: Periodo Escolar CON MARGEN -->
        <div class="col-lg-3 col-6 mb-4 margen-dashboard">  
            <div class="card card-dashboard" style="border-radius: 10px; overflow: hidden; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); border: none; height: 100%;">  
                <div class="card-body" style="padding: 1.5rem; position: relative;">  
                    <div class="image-background">
                        <img src="<?=APP_URL;?>/admin/logos/calendar-bg.png" alt="Calendario" style="width: 80px; height: 80px; opacity: 0.1;">
                    </div>
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <?php  
                            if ($gestion_activa) {  
                                $año_inicio = date('Y', strtotime($gestion_activa['desde']));  
                                $año_fin = date('Y', strtotime($gestion_activa['hasta']));  
                                echo "<h3 style='color: #3c8dbc; font-weight: 700; margin-bottom: 0.5rem;'>{$año_inicio}-{$año_fin}</h3>";  
                            } else {  
                                echo "<h3 style='color: #6c757d; font-weight: 700; margin-bottom: 0.5rem;'>No activo</h3>";  
                            }  
                            ?>  
                            <p class="text-muted mb-2">Periodo escolar</p>
                            <?php if ($gestion_activa): ?>
                                <span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i>Activo</span>
                            <?php else: ?>
                                <span class="badge badge-danger"><i class="fas fa-exclamation-circle mr-1"></i>Inactivo</span>
                            <?php endif; ?>
                        </div>  
                        <div class="icon-dashboard" style="background: linear-gradient(135deg, rgba(60, 141, 188, 0.1) 0%, rgba(45, 95, 126, 0.1) 100%); width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-calendar-alt" style="color: #3c8dbc; font-size: 1.5rem;"></i>  
                        </div>  
                    </div>
                </div>  
                <a href="<?=APP_URL;?>/admin/configuraciones/gestion" class="card-footer custom-btn text-white text-center" style="text-decoration: none; display: block; padding: 0.75rem;">  
                    Gestionar <i class="fas fa-arrow-circle-right ml-1"></i>  
                </a>  
            </div>  
        </div>  

                <!-- Tarjeta 2: Estudiantes Activos -->
                <div class="col-lg-3 col-6 mb-4">   
                    <div class="card card-dashboard" style="border-radius: 10px; overflow: hidden; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); border: none; height: 100%;">  
                        <div class="card-body" style="padding: 1.5rem; position: relative;">  
                            <div class="image-background">
                                <img src="<?=APP_URL;?>/admin/logos/students-bg.png" alt="Estudiantes" style="width: 80px; height: 80px; opacity: 0.1;">
                            </div>
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <?php  
                                    $estudiantes_activos = array_filter($estudiantes, function($estudiante) {
                                        return $estudiante['estatus'] === 'activo'; 
                                    });
                                    $contador_estudiantes = count($estudiantes_activos);  
                                    ?>  
                                    <h3 style="color: #3c8dbc; font-weight: 700; margin-bottom: 0.5rem;"><?=$contador_estudiantes;?></h3>  
                                    <p class="text-muted mb-2">Estudiantes activos</p>
                                    <span class="badge badge-info">Total en sistema</span>
                                </div>  
                                <div class="icon-dashboard" style="background: linear-gradient(135deg, rgba(60, 141, 188, 0.1) 0%, rgba(45, 95, 126, 0.1) 100%); width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-user-graduate" style="color: #3c8dbc; font-size: 1.5rem;"></i>  
                                </div>  
                            </div>
                        </div>  
                        <a href="<?=APP_URL;?>/admin/estudiantes" class="card-footer custom-btn text-white text-center" style="text-decoration: none; display: block; padding: 0.75rem;">  
                            Ver todos <i class="fas fa-arrow-circle-right ml-1"></i>  
                        </a>  
                    </div>  
                </div>

                <!-- Tarjeta 3: Representantes -->
                <div class="col-lg-3 col-6 mb-4">  
                    <div class="card card-dashboard" style="border-radius: 10px; overflow: hidden; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); border: none; height: 100%;">  
                        <div class="card-body" style="padding: 1.5rem; position: relative;">  
                            <div class="image-background">
                                <img src="<?=APP_URL;?>/admin/logos/parents-bg.png" alt="Representantes" style="width: 80px; height: 80px; opacity: 0.1;">
                            </div>
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <?php  
                                    $representantes_activos = array_filter($representantes, function($representante) {
                                        return $representante['estatus'] === 'Activo'; 
                                    });
                                    $contador_representantes = count($representantes_activos);  
                                    ?>  
                                    <h3 style="color: #3c8dbc; font-weight: 700; margin-bottom: 0.5rem;"><?=$contador_representantes;?></h3>  
                                    <p class="text-muted mb-2">Representantes</p>
                                    <div class="progress" style="height: 6px; width: 100px; background-color: #e9ecef;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: 85%;" aria-valuenow="85" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>  
                                <div class="icon-dashboard" style="background: linear-gradient(135deg, rgba(60, 141, 188, 0.1) 0%, rgba(45, 95, 126, 0.1) 100%); width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-users" style="color: #3c8dbc; font-size: 1.5rem;"></i>  
                                </div>  
                            </div>
                        </div>  
                        <a href="<?=APP_URL;?>/admin/representantes" class="card-footer custom-btn text-white text-center" style="text-decoration: none; display: block; padding: 0.75rem;">  
                            Ver todos <i class="fas fa-arrow-circle-right ml-1"></i>  
                        </a>  
                    </div>  
                </div>

                <!-- Tarjeta 4: Administrativos -->
                <div class="col-lg-3 col-6 mb-4">  
                    <div class="card card-dashboard" style="border-radius: 10px; overflow: hidden; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); border: none; height: 100%;">  
                        <div class="card-body" style="padding: 1.5rem; position: relative;">  
                            <div class="image-background">
                                <img src="<?=APP_URL;?>/admin/logos/admin-bg.png" alt="Administrativos" style="width: 80px; height: 80px; opacity: 0.1;">
                            </div>
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <?php  
                                    $contador_administrativos = count($administrativos);  
                                    ?>  
                                    <h3 style="color: #3c8dbc; font-weight: 700; margin-bottom: 0.5rem;"><?=$contador_administrativos;?></h3>  
                                    <p class="text-muted mb-2">Administrativos</p>
                                    <small class="text-muted"><?=count($roles)?> roles activos</small>
                                </div>  
                                <div class="icon-dashboard" style="background: linear-gradient(135deg, rgba(60, 141, 188, 0.1) 0%, rgba(45, 95, 126, 0.1) 100%); width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-user-cog" style="color: #3c8dbc; font-size: 1.5rem;"></i>
                                </div>  
                            </div>
                        </div>  
                        <a href="<?=APP_URL;?>/admin/administrativos" class="card-footer custom-btn text-white text-center" style="text-decoration: none; display: block; padding: 0.75rem;">  
                            Ver todos <i class="fas fa-arrow-circle-right ml-1"></i>  
                        </a>  
                    </div>  
                </div>  

                 <!-- Tarjeta 5: Inscripciones Activas CON MARGEN -->
        <div class="col-lg-3 col-6 mb-4 margen-dashboard">  
            <div class="card card-dashboard" style="border-radius: 10px; overflow: hidden; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); border: none; height: 100%;">  
                <div class="card-body" style="padding: 1.5rem; position: relative;">  
                    <div class="image-background">
                        <img src="<?=APP_URL;?>/admin/logos/inscriptions-bg.png" alt="Inscripciones" style="width: 80px; height: 80px; opacity: 0.1;">
                    </div>
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <?php  
                            $inscripciones = $id_gestion_activa ? getInscripcionesByGestion($pdo, $id_gestion_activa) : [];  
                            $contador_inscripciones = count($inscripciones);  
                            ?>  
                            <h3 style="color: #3c8dbc; font-weight: 700; margin-bottom: 0.5rem;"><?=$contador_inscripciones;?></h3>  
                            <p class="text-muted mb-2">Inscripciones activas</p>
                            <span class="badge badge-success">Periodo actual</span>
                        </div>  
                        <div class="icon-dashboard" style="background: linear-gradient(135deg, rgba(60, 141, 188, 0.1) 0%, rgba(45, 95, 126, 0.1) 100%); width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-clipboard-list" style="color: #3c8dbc; font-size: 1.5rem;"></i>  
                        </div>  
                    </div>
                </div>  
                <a href="<?=APP_URL;?>/admin/estudiantes/Lista_de_inscripcion.php" class="card-footer custom-btn text-white text-center" style="text-decoration: none; display: block; padding: 0.75rem;">  
                    Gestionar <i class="fas fa-arrow-circle-right ml-1"></i>  
                </a>  
            </div>  
        </div>

                <!-- Tarjeta 6: Estudiantes No Inscritos -->
                <div class="col-lg-3 col-6 mb-4">  
                    <div class="card card-dashboard" style="border-radius: 10px; overflow: hidden; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); border: none; height: 100%;">  
                        <div class="card-body" style="padding: 1.5rem; position: relative;">  
                            <div class="image-background">
                                <img src="<?=APP_URL;?>/admin/logos/warning-bg.png" alt="Atención" style="width: 80px; height: 80px; opacity: 0.1;">
                            </div>
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h3 style="color: #3c8dbc; font-weight: 700; margin-bottom: 0.5rem;"><?=$contador_no_inscritos;?></h3>  
                                    <p class="text-muted mb-2">Estudiantes sin inscripción</p>
                                    <span class="badge badge-warning">Requieren atención</span>
                                </div>  
                                <div class="icon-dashboard" style="background: linear-gradient(135deg, rgba(60, 141, 188, 0.1) 0%, rgba(45, 95, 126, 0.1) 100%); width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-exclamation-triangle" style="color: #3c8dbc; font-size: 1.5rem;"></i>  
                                </div>  
                            </div>
                        </div>  
                        <a href="<?=APP_URL;?>/admin/estudiantes" class="card-footer custom-btn text-white text-center" style="text-decoration: none; display: block; padding: 0.75rem;">  
                            Inscribir <i class="fas fa-arrow-circle-right ml-1"></i>  
                        </a>  
                    </div>  
                </div>

                <!-- Tarjeta 7: Grados -->
                <div class="col-lg-3 col-6 mb-4">   
                    <div class="card card-dashboard" style="border-radius: 10px; overflow: hidden; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); border: none; height: 100%;">  
                        <div class="card-body" style="padding: 1.5rem; position: relative;">  
                            <div class="image-background">
                                <img src="<?=APP_URL;?>/admin/logos/grades-bg.png" alt="Grados" style="width: 80px; height: 80px; opacity: 0.1;">
                            </div>
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <?php  
                                    $contador_grados = count($grados);  
                                    ?>  
                                    <h3 style="color: #3c8dbc; font-weight: 700; margin-bottom: 0.5rem;"><?=$contador_grados;?></h3>  
                                    <p class="text-muted mb-2">Años/Grados activos</p>
                                    <small class="text-muted">Configurados en sistema</small>
                                </div>  
                                <div class="icon-dashboard" style="background: linear-gradient(135deg, rgba(60, 141, 188, 0.1) 0%, rgba(45, 95, 126, 0.1) 100%); width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-graduation-cap" style="color: #3c8dbc; font-size: 1.5rem;"></i>  
                                </div>  
                            </div>
                        </div>  
                        <a href="<?=APP_URL;?>/admin/configuraciones/grados" class="card-footer custom-btn text-white text-center" style="text-decoration: none; display: block; padding: 0.75rem;">  
                            Configurar <i class="fas fa-arrow-circle-right ml-1"></i>  
                        </a>  
                    </div>  
                </div>  

                <!-- Tarjeta 8: Secciones -->
                <div class="col-lg-3 col-6 mb-4">   
                    <div class="card card-dashboard" style="border-radius: 10px; overflow: hidden; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); border: none; height: 100%;">  
                        <div class="card-body" style="padding: 1.5rem; position: relative;">  
                            <div class="image-background">
                                <img src="<?=APP_URL;?>/admin/logos/sections-bg.png" alt="Secciones" style="width: 80px; height: 80px; opacity: 0.1;">
                            </div>
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <?php  
                                    $contador_secciones = count($secciones);  
                                    ?>  
                                    <h3 style="color: #3c8dbc; font-weight: 700; margin-bottom: 0.5rem;"><?=$contador_secciones;?></h3>  
                                    <p class="text-muted mb-2">Secciones activas</p>
                                    <small class="text-muted">Disponibles para inscripción</small>
                                </div>  
                                <div class="icon-dashboard" style="background: linear-gradient(135deg, rgba(60, 141, 188, 0.1) 0%, rgba(45, 95, 126, 0.1) 100%); width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-chalkboard" style="color: #3c8dbc; font-size: 1.5rem;"></i>  
                                </div>  
                            </div>
                        </div>  
                        <a href="<?=APP_URL;?>/admin/configuraciones/secciones" class="card-footer custom-btn text-white text-center" style="text-decoration: none; display: block; padding: 0.75rem;">  
                            Configurar <i class="fas fa-arrow-circle-right ml-1"></i>  
                        </a>  
                    </div>  
                </div>  
            </div>  
            <hr>
                
            <!-- Nueva Sección: Alertas del Sistema -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card" style="border-radius: 10px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); border: none;">
                        <div class="card-header bg-white" style="border-bottom: 1px solid #eaeaea; border-radius: 10px 10px 0 0 !important;">
                            <h5 class="card-title mb-0" style="color: #3c8dbc; font-weight: 600;">
                                <i class="fas fa-bell mr-2"></i>Alertas del Sistema
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush">
                                <a href="<?=APP_URL;?>/admin/estudiantes" class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">Inscripciones pendientes</h6>
                                        <small class="text-muted">Hoy</small>
                                    </div>
                                    <p class="mb-1"><?=$contador_no_inscritos?> estudiantes registrados pero no inscritos</p>
                                    <small class="text-primary">Inscribir ahora</small>
                                </a>
                                <a href="<?=APP_URL;?>/admin/estudiantes" class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">Documentación incompleta</h6>
                                        <small class="text-muted">Revisar</small>
                                    </div>
                                    <p class="mb-1">Verificar documentos de estudiantes recién registrados</p>
                                    <small class="text-warning">Revisar documentos</small>
                                </a>
                                <a href="<?=APP_URL;?>/admin/representantes" class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">Representantes por verificar</h6>
                                        <small class="text-muted">Pendiente</small>
                                    </div>
                                    <p class="mb-1">Validar información de representantes registrados</p>
                                    <small class="text-info">Verificar datos</small>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <hr>
            
            <!-- Sección de Gráficos Mejorada -->
            <div class="row">
                <!-- Gráfico de estudiantes registrados -->
                <div class="col-md-8">
                    <div class="card card-outline card-primary">
                        <div class="card-header" style="background: linear-gradient(135deg, #3c8dbc 0%, #2d5f7e 100%);">
                            <h3 class="card-title" style="color: white; margin: 0;">
                                <i class="fas fa-chart-bar mr-2"></i>Estudiantes Registrados por Mes
                            </h3>
                        </div>
                        <div class="card-body">
                            <div>
                                <canvas id="myChart2"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Nuevo Gráfico: Inscripciones por Año/Grado -->
                <div class="col-md-4">
                    <div class="card card-outline card-primary">
                        <div class="card-header" style="background: linear-gradient(135deg, #3c8dbc 0%, #2d5f7e 100%);">
                            <h3 class="card-title" style="color: white; margin: 0;">
                                <i class="fas fa-chart-pie mr-2"></i>Inscripciones por Año
                            </h3>
                        </div>
                        <div class="card-body">
                            <div>
                                <canvas id="inscripcionesGradoChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>
        <!-- /.row -->
    </div><!-- /.container-fluid -->
</div>
<!-- /.content -->

<?php
// Datos para el gráfico de estudiantes por mes
$enero = 0; $febrero = 0; $marzo = 0; $abril = 0; $mayo = 0; $junio = 0; $julio = 0;
$agosto = 0; $septiembre = 0; $octubre = 0; $noviembre = 0; $diciembre = 0;
foreach ($reportes_estudiantes as $reportes_estudiante){
    $fecha = $reportes_estudiante['created_at'];
    $fecha = strtotime($fecha);
    $mes = date("m",$fecha);
    if($mes == "01") $enero = $enero + 1;
    if($mes == "02") $febrero = $febrero + 1;
    if($mes == "03") $marzo = $marzo + 1;
    if($mes == "04") $abril = $abril + 1;
    if($mes == "05") $mayo = $mayo + 1;
    if($mes == "06") $junio = $junio + 1;
    if($mes == "07") $julio = $julio + 1;
    if($mes == "08") $agosto = $agosto + 1;
    if($mes == "09") $septiembre = $septiembre + 1;
    if($mes == "10") $octubre = $octubre + 1;
    if($mes == "11") $noviembre = $noviembre + 1;
    if($mes == "12") $diciembre = $diciembre + 1;
}
$reporte_meses = $enero.",".$febrero.",".$marzo.",".$abril.",".$mayo.",".$junio.",".$julio.",".$agosto.",".$septiembre.",".$octubre.",".$noviembre.",".$diciembre;
?>

<script>
    // Gráfico de estudiantes por mes
    var meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio','Julio',
        'Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
    var datos =[<?=$reporte_meses;?>];
    const ctx2 = document.getElementById('myChart2');
    new Chart(ctx2, {
        type: 'bar',
        data: {
            labels: meses,
            datasets: [{
                label: 'Estudiantes registrados',
                data: datos,
                borderWidth: 2,
                backgroundColor: 'rgba(60, 141, 188, 0.7)',
                borderColor: 'rgba(60, 141, 188, 1)',
                borderRadius: 5,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        drawBorder: false
                    },
                    ticks: {
                        precision: 0
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.7)',
                    titleFont: {
                        size: 14
                    },
                    bodyFont: {
                        size: 13
                    },
                    padding: 10,
                    cornerRadius: 5
                }
            }
        }
    });
    
    // Gráfico de inscripciones por año/grado - CORREGIDO
    const inscripcionesGradoCtx = document.getElementById('inscripcionesGradoChart');
    <?php if(count($inscripciones_por_grado) > 0): ?>
    new Chart(inscripcionesGradoCtx, {
        type: 'doughnut',
        data: {
           labels: [
<?php 
foreach($inscripciones_por_grado as $grado) {
    echo "'" . $grado['grado'] . "',";
}
?>
],
            datasets: [{
                data: [
                    <?php 
                    foreach($inscripciones_por_grado as $grado) {
                        echo $grado['total'] . ",";
                    }
                    ?>
                ],
                backgroundColor: [
                    'rgba(60, 141, 188, 0.8)',
                    'rgba(60, 188, 141, 0.8)',
                    'rgba(188, 141, 60, 0.8)',
                    'rgba(141, 60, 188, 0.8)',
                    'rgba(188, 60, 141, 0.8)',
                    'rgba(60, 188, 188, 0.8)',
                    'rgba(141, 188, 60, 0.8)',
                    'rgba(188, 60, 188, 0.8)'
                ],
                borderWidth: 1,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true,
                    }
                }
            },
            cutout: '60%'
        }
    });
    <?php else: ?>
    // Mostrar mensaje si no hay datos
    inscripcionesGradoCtx.getContext('2d').font = '16px Arial';
    inscripcionesGradoCtx.getContext('2d').fillStyle = '#6c757d';
    inscripcionesGradoCtx.getContext('2d').textAlign = 'center';
    inscripcionesGradoCtx.getContext('2d').fillText('No hay datos disponibles', inscripcionesGradoCtx.width/2, inscripcionesGradoCtx.height/2);
    <?php endif; ?>
</script>

<?php
include ('../admin/layout/parte2.php');
include ('../layout/mensajes.php');
?>

<style>
    /* Estilos adicionales para mejorar la apariencia */
    .card-dashboard {
        transition: all 0.3s ease;
    }
    
    .icon-dashboard {
        transition: all 0.3s ease;
    }
    
    .card-dashboard:hover .icon-dashboard {
        transform: scale(1.1);
    }
    
    .list-group-item {
        border: none;
        border-bottom: 1px solid #eaeaea;
        transition: all 0.2s ease;
    }
    
    .list-group-item:hover {
        background-color: #f8f9fa;
    }
    
    /* Nuevos estilos para botones azul más oscuro */
    .card-footer.custom-btn {
        background-color: #9ec5fe !important;
        color: #052c65 !important;
        border: none;
        transition: all 0.3s ease;
        font-weight: 600;
    }
    
    .card-footer.custom-btn:hover {
        background-color: #6ea8fe !important;
        color: #031633 !important;
    }
    
    /* Imágenes de fondo transparentes */
    .image-background {
        position: absolute;
        bottom: 10px;
        right: 10px;
        opacity: 0.08;
        z-index: 0;
    }
    
    .card-body {
        position: relative;
        z-index: 1;
    }
    
    /* Colores Bootstrap para badges */
    .badge-success {
        background-color: #28a745;
    }
    
    .badge-danger {
        background-color: #dc3545;
    }
    
    .badge-warning {
        background-color: #ffc107;
        color: #212529;
    }
    
    .badge-info {
        background-color: #17a2b8;
    }
    
    /* ESTILOS PARA LA VISTA DE DOCENTES */
    .docente-welcome {
        background: linear-gradient(135deg, #3c8dbc 0%, #2d5f7e 100%);
        color: white;
        padding: 2rem;
        border-radius: 10px;
        margin-bottom: 2rem;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }
    
    .docente-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .stat-card {
        background: white;
        padding: 1.5rem;
        border-radius: 10px;
        text-align: center;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        border: 1px solid #eaeaea;
        transition: all 0.3s ease;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }
    
    .stat-card.info {
        border-top: 4px solid #3c8dbc;
    }
    
    .stat-card i {
        font-size: 2.5rem;
        color: #3c8dbc;
        margin-bottom: 1rem;
    }
    
    .stat-number {
        font-size: 2rem;
        font-weight: 700;
        color: #3c8dbc;
        margin-bottom: 0.5rem;
    }
    
    .stat-label {
        color: #6c757d;
        font-weight: 500;
    }
    
    .docente-dashboard {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .activities-card {
        background: white;
        padding: 1.5rem;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        border: 1px solid #eaeaea;
    }
    
    .activities-card h5 {
        color: #3c8dbc;
        font-weight: 600;
        margin-bottom: 1rem;
    }
    
    @media (max-width: 768px) {
        .docente-dashboard {
            grid-template-columns: 1fr;
        }
        
        .docente-stats {
            grid-template-columns: 1fr;
        }
    }
</style>

<script>
    $(function () {
        $('.knob').knob({
            draw: function () {
                if (this.$.data('skin') == 'tron') {
                    var a   = this.angle(this.cv)  // Angle
                        ,
                        sa  = this.startAngle          // Previous start angle
                        ,
                        sat = this.startAngle         // Start angle
                        ,
                        ea                            // Previous end angle
                        ,
                        eat = sat + a                 // End angle
                        ,
                        r   = true

                    this.g.lineWidth = this.lineWidth

                    this.o.cursor
                    && (sat = eat - 0.3)
                    && (eat = eat + 0.3)

                    if (this.o.displayPrevious) {
                        ea = this.startAngle + this.angle(this.value)
                        this.o.cursor
                        && (sa = ea - 0.3)
                        && (ea = ea + 0.3)
                        this.g.beginPath()
                        this.g.strokeStyle = this.previousColor
                        this.g.arc(this.xy, this.xy, this.radius - this.lineWidth, sa, ea, false)
                        this.g.stroke()
                    }

                    this.g.beginPath()
                    this.g.strokeStyle = r ? this.o.fgColor : this.fgColor
                    this.g.arc(this.xy, this.xy, this.radius - this.lineWidth, sat, eat, false)
                    this.g.stroke()

                    this.g.lineWidth = 2
                    this.g.beginPath()
                    this.g.strokeStyle = this.o.fgColor
                    this.g.arc(this.xy, this.xy, this.radius - this.lineWidth + 1 + this.lineWidth * 2 / 3, 0, 2 * Math.PI, false)
                    this.g.stroke()

                    return false
                }
            }
        })
    });
</script>

<script>
        window.onload = () => {
            setTimeout(() => {
                document.getElementsByTagName('body')[0].style.backgroundColor = 'white';
            }, 3000);
        }
        function refrescarPagina() {
            location.reload();
        }
    </script>