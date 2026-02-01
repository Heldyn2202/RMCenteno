<?php
session_start();
require_once('verificar_docente.php');

// Determinar rol/permiso: permitir Docente o Administrador
$rol_sesion = $_SESSION['rol_sesion_usuario'] ?? $_SESSION['rol'] ?? null;
$rol_id    = $_SESSION['rol_id'] ?? null;
$es_docente_flag = $_SESSION['es_docente'] ?? false;

if ($es_docente_flag || $rol_id == 5 || (is_string($rol_sesion) && stripos($rol_sesion, 'doc') !== false)) {
    // Si es docente, cargamos el verificador para datos del docente
    require_once('verificar_docente.php');
    $datos_docente = verificarDocente();
    $id_profesor = $datos_docente['id_profesor'];
    $nombre_profesor = $datos_docente['nombre_profesor'];
    $especialidad = $datos_docente['especialidad'] ?? '';
} elseif ($rol_id == 1 || (is_string($rol_sesion) && (stripos($rol_sesion, 'admin') !== false || stripos($rol_sesion, 'administrador') !== false))) {
    // Si es administrador
    $id_profesor = 0;
    $nombre_profesor = $_SESSION['nombres_sesion_usuario'] ?? $_SESSION['nombre'] ?? 'Administrador';
    $especialidad = 'Administrador';
} else {
    // No autorizado
    echo "<script>
        Swal.fire({
            icon: 'error',
            title: 'Acceso denegado',
            text: 'No tienes permisos para acceder a esta sección.'
        }).then(() => { window.location = '../../admin'; });
    </script>";
    exit;
}

// 🔹 Obtener gestión activa
$sql_gestion = "
SELECT 
    id_gestion,
    CONCAT('Periodo ', DATE_FORMAT(desde, '%Y'), ' - ', DATE_FORMAT(hasta, '%Y')) AS nombre_gestion,
    desde,
    hasta,
    estado
FROM gestiones
WHERE estado = 1
LIMIT 1
";
$query_gestion = $pdo->prepare($sql_gestion);
$query_gestion->execute();
$gestion_activa = $query_gestion->fetch(PDO::FETCH_ASSOC);
if (!$gestion_activa) {
    $gestion_activa = ['id_gestion' => 0, 'nombre_gestion' => 'No activa'];
}

// 🔹 Obtener secciones (si es docente: solo las asignadas; si admin: todas)
if (!empty($id_profesor) && $id_profesor > 0) {
    $sql_secciones = "
    SELECT DISTINCT 
        s.id_seccion, 
        s.nombre_seccion,
        g.grado AS nombre_grado,   
        g.id_grado
    FROM secciones s
    INNER JOIN grados g ON s.id_grado = g.id_grado
    INNER JOIN asignaciones_profesor ap ON s.id_seccion = ap.id_seccion
    WHERE ap.id_profesor = :id_profesor 
      AND ap.id_gestion = :id_gestion
      AND ap.estado = 1
      AND s.estado = 1
    ORDER BY g.id_grado, s.nombre_seccion
    ";
    $query_secciones = $pdo->prepare($sql_secciones);
    $query_secciones->bindParam(':id_profesor', $id_profesor);
$query_secciones->bindParam(':id_gestion', $gestion_activa['id_gestion']);
    $query_secciones->execute();
    $secciones_asignadas = $query_secciones->fetchAll(PDO::FETCH_ASSOC);
} else {
    // administrador -> listar todas las secciones activas
    $sql_secciones = "
    SELECT DISTINCT 
        s.id_seccion, 
        s.nombre_seccion,
        g.grado AS nombre_grado,   
        g.id_grado
    FROM secciones s
    INNER JOIN grados g ON s.id_grado = g.id_grado
    WHERE s.estado = 1
    ORDER BY g.id_grado, s.nombre_seccion
    ";
    $query_secciones = $pdo->prepare($sql_secciones);
    $query_secciones->execute();
    $secciones_asignadas = $query_secciones->fetchAll(PDO::FETCH_ASSOC);
}

// 🔹 Obtener lapsos académicos (por gestión)
$sql_lapsos = "SELECT * FROM lapsos WHERE id_gestion = :id_gestion ORDER BY fecha_inicio";
$query_lapsos = $pdo->prepare($sql_lapsos);
$query_lapsos->bindParam(':id_gestion', $gestion_activa['id_gestion']);
$query_lapsos->execute();
$lapsos = $query_lapsos->fetchAll(PDO::FETCH_ASSOC);

// 🔹 Variables para filtros
$id_seccion_filtro = $_GET['seccion'] ?? null;
$id_materia_filtro = $_GET['materia'] ?? null;
$id_lapso_filtro   = $_GET['lapso'] ?? null;

// 🔹 IMPORTANTE: Si se cambió de materia, resetear el lapso seleccionado
$materia_anterior = $_SESSION['ultima_materia_seleccionada'] ?? null;
$seccion_anterior = $_SESSION['ultima_seccion_seleccionada'] ?? null;

// Guardar la materia y sección actual para la próxima comparación
$_SESSION['ultima_materia_seleccionada'] = $id_materia_filtro;
$_SESSION['ultima_seccion_seleccionada'] = $id_seccion_filtro;

// Resetear lapso si cambió la materia o la sección
if (($id_materia_filtro && $materia_anterior && $id_materia_filtro != $materia_anterior) ||
    ($id_seccion_filtro && $seccion_anterior && $id_seccion_filtro != $seccion_anterior)) {
    $id_lapso_filtro = null;
    // También limpiar del GET para que no se muestre en la URL
    unset($_GET['lapso']);
}

// 🔹 Preparación para detectar "tercer lapso" por posición
$lapsos_ids_ordered = array_column($lapsos, 'id_lapso'); // mantiene el orden por fecha_inicio
$lapso_pos = null;
if (!empty($lapsos_ids_ordered) && isset($id_lapso_filtro)) {
    $lapso_pos = array_search(intval($id_lapso_filtro), $lapsos_ids_ordered);
}
$es_tercer_lapso = ($lapso_pos !== false && $lapso_pos === 2); // índice 2 => 3er lapso en orden

// 🔹 Obtener estudiantes inscritos en la sección (se usará para comprobar completitud de lapso)
$estudiantes = [];
$student_count = 0;
$student_ids = [];
if ($id_seccion_filtro) {
    $sql_est_count = "
    SELECT e.id_estudiante, e.cedula, e.nombres, e.apellidos
    FROM estudiantes e
    JOIN inscripciones i ON e.id_estudiante = i.id_estudiante
    WHERE i.id_seccion = :id_seccion 
      AND i.estado = 'activo' 
      AND e.estatus = 'Activo'
    ORDER BY e.apellidos, e.nombres";
    $query_est_count = $pdo->prepare($sql_est_count);
    $query_est_count->bindParam(':id_seccion', $id_seccion_filtro);
    $query_est_count->execute();
    $estudiantes = $query_est_count->fetchAll(PDO::FETCH_ASSOC);
    $student_count = count($estudiantes);
    $student_ids = array_column($estudiantes, 'id_estudiante');
}

// Función local para comprobar si un lapso está "completado" (todas las notas guardadas para los estudiantes de la sección para esa materia)
function lapso_completado_para_seccion_materia($pdo, $id_lapso, $id_materia, $student_ids) {
    if (empty($student_ids) || !$id_lapso || !$id_materia) return false;
    // Preparamos una lista segura de ids
    $placeholders = implode(',', array_fill(0, count($student_ids), '?'));
    $sql = "
        SELECT COUNT(*) as cnt FROM notas_estudiantes
        WHERE id_lapso = ?
          AND id_materia = ?
          AND id_estudiante IN ($placeholders)
          AND calificacion IS NOT NULL
    ";
    $stmt = $pdo->prepare($sql);
    $params = array_merge([$id_lapso, $id_materia], $student_ids);
    $stmt->execute($params);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return intval($row['cnt'] ?? 0) >= count($student_ids);
}

// 🔹 Obtener materias asignadas si hay sección seleccionada
$materias_asignadas = [];
if ($id_seccion_filtro) {
    if (!empty($id_profesor) && $id_profesor > 0) {
        $sql_materias = "
        SELECT 
            m.id_materia, 
            m.nombre_materia, 
            m.abreviatura
        FROM materias m
        INNER JOIN asignaciones_profesor ap ON m.id_materia = ap.id_materia
        WHERE ap.id_profesor = :id_profesor 
          AND ap.id_seccion = :id_seccion
          AND ap.id_gestion = :id_gestion
          AND ap.estado = 1
        ORDER BY m.nombre_materia
        ";
        $query_materias = $pdo->prepare($sql_materias);
        $query_materias->bindParam(':id_profesor', $id_profesor);
        $query_materias->bindParam(':id_seccion', $id_seccion_filtro);
        $query_materias->bindParam(':id_gestion', $gestion_activa['id_gestion']);
        $query_materias->execute();
        $materias_asignadas = $query_materias->fetchAll(PDO::FETCH_ASSOC);
    } else {
        // admin: listar todas las materias (puedes ajustar para filtrar por sección si quieres)
        $sql_materias = "SELECT id_materia, nombre_materia, abreviatura FROM materias WHERE estado = 1 ORDER BY nombre_materia";
        $query_materias = $pdo->prepare($sql_materias);
        $query_materias->execute();
        $materias_asignadas = $query_materias->fetchAll(PDO::FETCH_ASSOC);
    }
}

// 🔹 Construir lista de lapsos a mostrar según reglas:
// - Si no hay sección o no hay materia seleccionada: mostrar solo el primer lapso (evitar que entren al 2/3 sin seleccionar materia/section).
// - Si hay sección & materia: mostrar el primer lapso siempre; mostrar el segundo solo si el primer lapso está completado (todas las notas guardadas para los estudiantes de la sección), y así sucesivamente.
$lapsos_para_mostrar = [];
if (empty($id_seccion_filtro) || empty($id_materia_filtro)) {
    // solo el primer lapso (si existe)
    if (!empty($lapsos)) {
        $lapsos_para_mostrar[] = $lapsos[0];
    }
} else {
    // sección y materia seleccionadas: iterar y permitir avanzar solo si el anterior está completado
    $prev_completed = true; // para el primero
    $num_lapsos = count($lapsos);
    for ($i = 0; $i < $num_lapsos; $i++) {
        $lap = $lapsos[$i];
        if ($i === 0) {
            $lapsos_para_mostrar[] = $lap;
            // comprobar si el primer lapso está completado para permitir el siguiente
            $prev_completed = lapso_completado_para_seccion_materia($pdo, $lap['id_lapso'], $id_materia_filtro, $student_ids);
        } else {
            if ($prev_completed) {
                $lapsos_para_mostrar[] = $lap;
                // actualizar prev_completed usando este lapso
                $prev_completed = lapso_completado_para_seccion_materia($pdo, $lap['id_lapso'], $id_materia_filtro, $student_ids);
            } else {
                // no permitimos mostrar este ni los siguientes
                break;
            }
        }
    }
}

// Determinar si el lapso seleccionado (si es el tercero por posición) está efectivamente completado (para habilitar Recuperaciones)
$lapso_tercer_completado = false;
if ($es_tercer_lapso && !empty($id_materia_filtro) && !empty($id_seccion_filtro)) {
    // verificar que el lapso (GET lapso) tiene todas las notas guardadas
    $lapso_tercer_completado = lapso_completado_para_seccion_materia($pdo, intval($id_lapso_filtro), intval($id_materia_filtro), $student_ids);
}

// 🔹 Obtener lista de estudiantes que están en proceso de "revision" (recuperaciones tipo='revision')
// 🔹 Y también obtener lista de estudiantes en "materias_pendientes" (tabla materias_pendientes)
// Esto se usa para bloquear edición en la UI y para mostrar mensajes específicos.
$estudiantes_en_revision = [];
$estudiantes_en_pendiente = [];
if (!empty($id_seccion_filtro) && !empty($id_materia_filtro)) {
    $stmt_rev = $pdo->prepare("SELECT DISTINCT id_estudiante FROM recuperaciones WHERE id_materia = :id_materia AND id_seccion = :id_seccion AND tipo = 'revision'");
    $stmt_rev->execute([':id_materia' => $id_materia_filtro, ':id_seccion' => $id_seccion_filtro]);
    $estudiantes_en_revision = array_map('intval', array_column($stmt_rev->fetchAll(PDO::FETCH_ASSOC), 'id_estudiante'));

    $stmt_pend = $pdo->prepare("SELECT DISTINCT id_estudiante FROM materias_pendientes WHERE id_materia = :id_materia AND id_seccion = :id_seccion");
    $stmt_pend->execute([':id_materia' => $id_materia_filtro, ':id_seccion' => $id_seccion_filtro]);
    $estudiantes_en_pendiente = array_map('intval', array_column($stmt_pend->fetchAll(PDO::FETCH_ASSOC), 'id_estudiante'));
}

include('../../admin/layout/parte1.php');

// -----------------
// Helper seguro para imprimir HTML en Swal sin mostrar etiquetas escapadas
function safe_swal_html($msg) {
    if ($msg === null) return json_encode('');
    // Permitimos un pequeño conjunto de etiquetas seguras
    $allowed = '<strong><b><br><ul><li><em><i>';
    $clean = strip_tags($msg, $allowed);
    return json_encode($clean, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT);
}
?>

<!-- Mostrar mensajes de sesión (success / error) con SweetAlert2 -->
<?php if (!empty($_SESSION['mensaje_exito']) || !empty($_SESSION['success_message'])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const htmlMsg = <?= safe_swal_html($_SESSION['mensaje_exito'] ?? $_SESSION['success_message']) ?>;
            Swal.fire({
                icon: 'success',
                title: 'Éxito',
                html: htmlMsg,
                confirmButtonText: 'Aceptar'
            });
        });
    </script>
    <?php unset($_SESSION['mensaje_exito'], $_SESSION['success_message']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['error_message']) || !empty($_SESSION['error'])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const htmlMsg = <?= safe_swal_html($_SESSION['error_message'] ?? $_SESSION['error']) ?>;
            Swal.fire({
                icon: 'error',
                title: 'Error',
                html: htmlMsg,
                confirmButtonText: 'Aceptar'
            });
        });
    </script>
    <?php unset($_SESSION['error_message'], $_SESSION['error']); ?>
<?php endif; ?>

<div class="content-wrapper">
    <div class="content">
        <div class="container">
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">Carga Masiva de Notas por Sección</h1>
                        </div>
                        <div class="col-sm-6 text-right">
                            <span class="badge badge-info">
                                <i class="fas fa-user"></i> <?= htmlspecialchars($nombre_profesor) ?>
                            </span>
                            <span class="badge badge-secondary ml-2">
                                Gestión: <?= htmlspecialchars($gestion_activa['nombre_gestion'] ?? 'No activa') ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filtros -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Seleccionar Sección y Materia</h3>
                        </div>
                        <div class="card-body">
                            <form method="get" action="">
                                <div class="row">
                                    <!-- Secciones (ahora muestra: GRADO - SECCIÓN) -->
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Sección Asignada</label>
                                            <select name="seccion" class="form-control" id="select_seccion" required onchange="this.form.submit()">
                                                <option value="">Seleccionar Sección</option>
                                                <?php foreach($secciones_asignadas as $seccion): ?>
                                                    <option value="<?= $seccion['id_seccion'] ?>" <?= ($id_seccion_filtro == $seccion['id_seccion']) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($seccion['nombre_grado']) ?> - <?= htmlspecialchars($seccion['nombre_seccion']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <?php if(empty($secciones_asignadas)): ?>
                                                <small class="text-danger">No tienes secciones asignadas para esta gestión. Contacta al administrador.</small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <!-- Materias -->
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Materia Asignada</label>
                                            <select name="materia" class="form-control" id="select_materia" required 
                                                    <?= empty($materias_asignadas) ? 'disabled' : '' ?>
                                                    onchange="this.form.submit()">
                                                <option value="">Seleccionar Materia</option>
                                                <?php if ($id_seccion_filtro && !empty($materias_asignadas)): ?>
                                                    <?php foreach($materias_asignadas as $materia): ?>
                                                        <option value="<?= $materia['id_materia'] ?>" <?= ($id_materia_filtro == $materia['id_materia']) ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($materia['nombre_materia']) ?>
                                                            <?= !empty($materia['abreviatura']) ? '(' . htmlspecialchars($materia['abreviatura']) . ')' : '' ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </select>
                                            <?php if($id_seccion_filtro && empty($materias_asignadas)): ?>
                                                <small class="text-danger">No tienes materias asignadas para esta sección.</small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <!-- Lapso -->
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Lapso Académico</label>
                                            <select name="lapso" class="form-control" required onchange="this.form.submit()">
                                                <option value="">Seleccionar Lapso</option>
                                                <?php foreach ($lapsos_para_mostrar as $lapso): ?>
                                                    <option value="<?= $lapso['id_lapso'] ?>" <?= ($id_lapso_filtro == $lapso['id_lapso']) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($lapso['nombre_lapso']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <?php if (!empty($id_seccion_filtro) && !empty($id_materia_filtro) && empty($lapsos_para_mostrar)): ?>
                                                <small class="text-warning">No hay lapsos habilitados: completa el lapso anterior para habilitar el siguiente.</small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <!-- Botones -->
                                    <div class="col-md-3" style="margin-top: 32px">
                                        <?php if($id_seccion_filtro && $id_materia_filtro && $id_lapso_filtro): ?>
                                            <button type="submit" class="btn btn-primary">Actualizar Vista</button>
                                        <?php else: ?>
                                            <button type="submit" class="btn btn-primary">Cargar Estudiantes</button>
                                        <?php endif; ?>
                                        <a href="carga_notas_seccion.php" class="btn btn-default">Limpiar</a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mensaje de advertencia -->
            <?php 
            if ($id_seccion_filtro && empty($id_materia_filtro)) {
                echo '<div class="alert alert-warning text-center">⚠️ Debes seleccionar una materia antes de cargar o guardar las notas.</div>';
            }
            ?>

            <!-- Tabla de Estudiantes -->
            <?php 
            if (!empty($id_seccion_filtro) && !empty($id_materia_filtro) && !empty($id_lapso_filtro)): 
                $seccion_info = '';
                $grado_info = '';
                
                foreach($secciones_asignadas as $s) {
                    if($s['id_seccion'] == $id_seccion_filtro) {
                        $seccion_info = $s['nombre_seccion'];
                        $grado_info = $s['nombre_grado'];
                        break;
                    }
                }
                
                $materia_info = '';
                foreach($materias_asignadas as $m) {
                    if($m['id_materia'] == $id_materia_filtro) {
                        $materia_info = $m['nombre_materia'];
                        break;
                    }
                }
                
                $lapso_info = $lapsos[array_search($id_lapso_filtro, array_column($lapsos, 'id_lapso'))]['nombre_lapso'] ?? '';
                
                // Obtener estudiantes inscritos en la sección (ya obtenido arriba en $estudiantes)
                // $estudiantes ya contiene los datos y $student_count la cantidad
            ?>
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-success">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-users"></i> Lista de Estudiantes - 
                                <?= htmlspecialchars("$grado_info - $seccion_info - $materia_info - $lapso_info") ?>
                                <span class="badge badge-primary ml-2"><?= count($estudiantes) ?> estudiantes</span>
                            </h3>
                            <div class="card-tools">
                                <!-- Buscador -->
                                <div class="input-group input-group-sm" style="width: 300px;">
                                    <input type="text" id="buscador-estudiantes" class="form-control" placeholder="Buscar por cédula o nombre...">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-default" onclick="limpiarBusqueda()">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- BOTÓN: Gestionar Recuperaciones (solo si es 3er lapso/posición 2 Y está completado) -->
                                <?php if ($es_tercer_lapso): ?>
                                    <?php if ($lapso_tercer_completado): ?>
                                        <a href="recuperaciones.php?seccion=<?= $id_seccion_filtro ?>&materia=<?= $id_materia_filtro ?>&lapso=<?= $id_lapso_filtro ?>"
                                           class="btn btn-outline-info" title="Gestionar Recuperaciones (habilitado)">
                                           <i class="fas fa-book-open"></i>
                                        </a>
                                    <?php else: ?>
                                        <button class="btn btn-outline-secondary" disabled title="Habilitado cuando estén guardadas todas las notas del tercer lapso">
                                            <i class="fas fa-book-open"></i>
                                        </button>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Resumen de notas -->
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <div class="alert alert-info">
                                        <div class="row text-center">
                                            <div class="col-md-2">
                                                <strong>Total Estudiantes:</strong><br>
                                                <span class="badge badge-primary" style="font-size:16px;"><?= count($estudiantes) ?></span>
                                            </div>
                                            <div class="col-md-2">
                                                <strong>Notas Cargadas:</strong><br>
                                                <span class="badge badge-success" style="font-size:16px;" id="contador-cargadas">0</span>
                                            </div>
                                            <div class="col-md-2">
                                                <strong>Pendientes:</strong><br>
                                                <span class="badge badge-warning" style="font-size:16px;" id="contador-pendientes">0</span>
                                            </div>
                                            <div class="col-md-2">
                                                <strong>Aprobados:</strong><br>
                                                <span class="badge badge-success" style="font-size:16px;" id="contador-aprobados">0</span>
                                            </div>
                                            <div class="col-md-2">
                                                <strong>Reprobados:</strong><br>
                                                <span class="badge badge-danger" style="font-size:16px;" id="contador-reprobados">0</span>
                                            </div>
                                            <div class="col-md-2">
                                                <strong>Promedio:</strong><br>
                                                <span class="badge badge-info" style="font-size:16px;" id="promedio-notas">0.00</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <form id="form-carga-masiva" action="guardar_notas_masivas.php" method="post">
                                <input type="hidden" name="id_seccion" value="<?= htmlspecialchars($id_seccion_filtro) ?>">
                                <input type="hidden" name="id_materia" value="<?= htmlspecialchars($id_materia_filtro) ?>">
                                <input type="hidden" name="id_lapso" value="<?= htmlspecialchars($id_lapso_filtro) ?>">
                                <input type="hidden" name="id_profesor" value="<?= htmlspecialchars($id_profesor) ?>">

                                <table class="table table-bordered table-striped table-hover" id="tabla-estudiantes">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th width="5%">#</th>
                                            <th width="15%">Cédula</th>
                                            <th width="25%">Estudiante</th>
                                            <th width="15%" class="text-center">Nota Actual</th>
                                            <th width="20%" class="text-center">Nueva Nota (0-20)</th>
                                            <th width="20%" class="text-center">Observación (si modificas)</th>
                                            <th width="15%" class="text-center">Estado</th>
                                            <?php if ($es_tercer_lapso): ?>
                                                <th width="15%" class="text-center">Condición Final</th>
                                            <?php endif; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $contador = 1;
                                        // Prepara el string de los 3 primeros lapsos (si existen)
                                        $ids_3_lapsos_str = '';
                                        if (count($lapsos_ids_ordered) >= 3) {
                                            $ids_3 = array_slice($lapsos_ids_ordered, 0, 3);
                                            $ids_3_lapsos_str = implode(',', array_map('intval', $ids_3));
                                        }
                                        foreach ($estudiantes as $estudiante):
                                            // Obtener nota y observación si existe
                                            $sql_nota = "
                                            SELECT calificacion, observaciones FROM notas_estudiantes 
                                            WHERE id_estudiante = :id_estudiante 
                                              AND id_materia = :id_materia 
                                              AND id_lapso = :id_lapso
                                            LIMIT 1";
                                            $query_nota = $pdo->prepare($sql_nota);
                                            $query_nota->bindParam(':id_estudiante', $estudiante['id_estudiante']);
                                            $query_nota->bindParam(':id_materia', $id_materia_filtro);
                                            $query_nota->bindParam(':id_lapso', $id_lapso_filtro);
                                            $query_nota->execute();
                                            $nota_existente = $query_nota->fetch(PDO::FETCH_ASSOC);

                                            $nota_actual = $nota_existente ? $nota_existente['calificacion'] : '';
                                            $observacion_actual = $nota_existente ? $nota_existente['observaciones'] : '';
                                            $estado_clase = $nota_existente ? 'badge-success' : 'badge-warning';
                                            $estado_texto = $nota_existente ? 'Cargada' : 'Pendiente';

                                            // Detectar si el estudiante está en revisión o en pendiente (listas obtenidas arriba)
                                            $esta_en_revision = in_array(intval($estudiante['id_estudiante']), $estudiantes_en_revision, true);
                                            $esta_en_pendiente = in_array(intval($estudiante['id_estudiante']), $estudiantes_en_pendiente, true);

                                            // 🔹 Si es tercer lapso: calcular condición final usando las 3 notas (solo si hay 3)
                                            $condicion_final = '';
                                            if ($es_tercer_lapso && $ids_3_lapsos_str !== '') {
                                                $sql_promedio = "
                                                    SELECT AVG(calificacion) AS promedio, COUNT(calificacion) AS cnt
                                                    FROM notas_estudiantes
                                                    WHERE id_estudiante = :id_estudiante_prom
                                                      AND id_materia = :id_materia_prom
                                                      AND id_lapso IN ($ids_3_lapsos_str)
                                                      AND calificacion IS NOT NULL
                                                ";
                                                $q_prom = $pdo->prepare($sql_promedio);
                                                $q_prom->bindParam(':id_estudiante_prom', $estudiante['id_estudiante']);
                                                $q_prom->bindParam(':id_materia_prom', $id_materia_filtro);
                                                $q_prom->execute();
                                                $row_prom = $q_prom->fetch(PDO::FETCH_ASSOC);
                                                $prom = $row_prom['promedio'] ?? null;
                                                $cnt = intval($row_prom['cnt'] ?? 0);

                                                if ($cnt === 3 && $prom !== null) {
                                                    // Nueva lógica solicitada:
                                                    // - Promedio >= 10 => Aprobado
                                                    // - Promedio < 10  => Revisión (por defecto)
                                                    if (floatval($prom) >= 10) {
                                                        $condicion_final = '<span class="badge badge-success">Aprobado</span>';
                                                    } else {
                                                        // Por defecto mostrar "Revisión"
                                                        $condicion_final = '<span class="badge badge-warning">Revisión</span>';

                                                        // Intentar detectar si ya existe una nota de recuperación (lapso de recuperación).
                                                        // Buscamos un lapso cuya descripción contenga 'recuper' en la gestión activa.
                                                        $sql_lap_rec = "SELECT id_lapso FROM lapsos WHERE id_gestion = :id_gestion AND (nombre_lapso LIKE '%recuper%' OR nombre_lapso LIKE '%recup%') LIMIT 1";
                                                        $q_lap_rec = $pdo->prepare($sql_lap_rec);
                                                        $q_lap_rec->bindParam(':id_gestion', $gestion_activa['id_gestion']);
                                                        $q_lap_rec->execute();
                                                        $lap_rec_row = $q_lap_rec->fetch(PDO::FETCH_ASSOC);

                                                        if ($lap_rec_row && !empty($lap_rec_row['id_lapso'])) {
                                                            $id_lap_rec = $lap_rec_row['id_lapso'];
                                                            // Obtener la nota de recuperación (si existe)
                                                            $sql_rec_nota = "
                                                                SELECT calificacion FROM notas_estudiantes
                                                                WHERE id_estudiante = :id_estudiante_rec
                                                                  AND id_materia = :id_materia_rec
                                                                  AND id_lapso = :id_lapso_rec
                                                                LIMIT 1
                                                            ";
                                                            $q_rec = $pdo->prepare($sql_rec_nota);
                                                            $q_rec->bindParam(':id_estudiante_rec', $estudiante['id_estudiante']);
                                                            $q_rec->bindParam(':id_materia_rec', $id_materia_filtro);
                                                            $q_rec->bindParam(':id_lapso_rec', $id_lap_rec);
                                                            $q_rec->execute();
                                                            $row_rec = $q_rec->fetch(PDO::FETCH_ASSOC);

                                                            if ($row_rec && $row_rec['calificacion'] !== null && $row_rec['calificacion'] !== '') {
                                                                $nota_rec = floatval($row_rec['calificacion']);
                                                                if ($nota_rec >= 10) {
                                                                    // Si aprobó en la recuperación => aprobado final
                                                                    $condicion_final = '<span class="badge badge-success">Aprobado</span>';
                                                                } else {
                                                                    // Si reprobó en recuperación => materia pendiente
                                                                    $condicion_final = '<span class="badge badge-danger">Materia pendiente</span>';
                                                                }
                                                            }
                                                        }
                                                    }
                                                } else {
                                                    // No hay las 3 notas todavía
                                                    $condicion_final = '<span class="badge badge-secondary">Sin datos</span>';
                                                }
                                            }
                                        ?>
                                        <tr class="fila-estudiante" data-cedula="<?= htmlspecialchars($estudiante['cedula']) ?>" data-nombre="<?= htmlspecialchars($estudiante['apellidos'] . ' ' . $estudiante['nombres']) ?>" <?= $esta_en_revision ? 'data-en-revision="1"' : ($esta_en_pendiente ? 'data-en-pendiente="1"' : '') ?>>
                                            <td><?= $contador++ ?></td>
                                            <td class="cedula-estudiante"><?= htmlspecialchars($estudiante['cedula']) ?></td>
                                            <td class="nombre-estudiante"><?= htmlspecialchars($estudiante['apellidos'] . ', ' . $estudiante['nombres']) ?></td>
                                            <td class="text-center">
                                                <span class="nota-actual" id="nota-actual-<?= $estudiante['id_estudiante'] ?>">
                                                    <?php if($nota_actual !== '' && $nota_actual !== null): ?>
                                                        <span class="badge badge-light" style="font-size:14px;">
                                                            <?= number_format(floatval($nota_actual), 2) ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="text-muted"><em>Sin nota</em></span>
                                                    <?php endif; ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <div class="input-group" style="max-width: 200px; margin: 0 auto;">
                                                    <input type="number" name="notas[<?= $estudiante['id_estudiante'] ?>]" 
                                                           class="form-control nota-input text-center"
                                                           min="0" max="20" step="0.01"
                                                           value="<?= ($nota_actual !== '' && $nota_actual !== null) ? htmlspecialchars($nota_actual) : '' ?>" placeholder="0.00"
                                                           data-original="<?= ($nota_actual !== '' && $nota_actual !== null) ? htmlspecialchars($nota_actual) : '' ?>"
                                                           oninput="actualizarNotaActual(this, <?= $estudiante['id_estudiante'] ?>)"
                                                           <?= ($esta_en_revision || $esta_en_pendiente) ? 'disabled' : '' ?>>
                                                    <div class="input-group-append">
                                                        <button type="button" class="btn btn-outline-secondary" onclick="limpiarNota(this, <?= $estudiante['id_estudiante'] ?>)" title="Limpiar nota" <?= ($esta_en_revision || $esta_en_pendiente) ? 'disabled' : '' ?>>
                                                            <i class="fas fa-eraser"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </td>

                                            <td class="text-center">
                                                <input type="text"
                                                       name="observaciones[<?= $estudiante['id_estudiante'] ?>]"
                                                       class="form-control observacion-input text-center"
                                                       placeholder="Motivo del cambio"
                                                       value="<?= htmlspecialchars($observacion_actual) ?>"
                                                       style="max-width: 220px; margin: 0 auto; display: none;"
                                                       id="observacion-<?= $estudiante['id_estudiante'] ?>"
                                                       <?= ($esta_en_revision || $esta_en_pendiente) ? 'disabled' : '' ?>>
                                            </td>

                                            <td class="text-center estado-nota">
                                                <?php if ($esta_en_revision): ?>
                                                    <span class="badge badge-warning" id="estado-<?= $estudiante['id_estudiante'] ?>">En revisión</span>
                                                <?php elseif ($esta_en_pendiente): ?>
                                                    <span class="badge badge-danger" id="estado-<?= $estudiante['id_estudiante'] ?>">Materia pendiente</span>
                                                <?php else: ?>
                                                    <span class="badge <?= $estado_clase ?>" id="estado-<?= $estudiante['id_estudiante'] ?>">
                                                        <?= $estado_texto ?>
                                                    </span>
                                                <?php endif; ?>
                                            </td>

                                            <?php if ($es_tercer_lapso): ?>
                                                <td class="text-center"><?= $condicion_final ?></td>
                                            <?php endif; ?>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>

                                <div class="mt-4 p-3 bg-light rounded">
                                    <div class="row">
                                        <div class="col-md-12 text-center">
                                            <button type="button" class="btn btn-info btn-lg mr-3" onclick="validarNotas()">
                                                <i class="fas fa-check-circle"></i> Validar Notas
                                            </button>

                                            <!-- Botón más visible para gestionar recuperaciones (se añade aquí, grande y destacado) -->
                                            <?php if ($es_tercer_lapso): ?>
                                                <?php if ($lapso_tercer_completado): ?>
                                                    <a href="recuperaciones.php?seccion=<?= $id_seccion_filtro ?>&materia=<?= $id_materia_filtro ?>&lapso=<?= $id_lapso_filtro ?>" 
                                                       class="btn btn-warning btn-lg mr-3" title="Gestionar Recuperaciones">
                                                        <i class="fas fa-book-open"></i> Gestionar Recuperaciones
                                                    </a>
                                                <?php else: ?>
                                                    <button class="btn btn-warning btn-lg mr-3" disabled title="Habilitado cuando estén guardadas todas las notas del tercer lapso">
                                                        <i class="fas fa-book-open"></i> Gestionar Recuperaciones
                                                    </button>
                                                <?php endif; ?>
                                            <?php endif; ?>

                                            <button type="button" class="btn btn-success btn-lg" onclick="confirmarGuardado()">
                                                <i class="fas fa-save"></i> Guardar Todas las Notas
                                            </button>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-md-12 text-center">
                                            <div id="resumen-guardado">
                                                <strong>Resumen:</strong> Se guardarán notas para <strong id="total-a-guardar">0</strong> estudiantes
                                                <br>
                                                <small class="text-muted" id="detalle-cambios"></small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
include('../../admin/layout/parte2.php');
include('../../layout/mensajes.php');
?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
/* Estilos adicionales para los campos bloqueados */
.nota-input-blocked {
    background-color: #f8f9fa !important;
    color: #6c757d !important;
    cursor: pointer !important;
    position: relative;
}

.nota-input-blocked.text-info {
    color: #17a2b8 !important;
    border-color: #17a2b8 !important;
}

.nota-input-blocked.text-warning {
    color: #ffc107 !important;
    border-color: #ffc107 !important;
}

/* Overlay para hacer todo el campo clickeable */
.nota-input-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    cursor: pointer;
    z-index: 10;
    background: transparent;
}

/* Badge para mostrar estado */
.badge-estado-campo {
    position: absolute;
    right: 5px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 0.7em;
    padding: 2px 6px;
    z-index: 5;
}

.badge-revision {
    background-color: #e3f2fd;
    color: #0d47a1;
}

.badge-pendiente {
    background-color: #fff3e0;
    color: #e65100;
}
</style>

<script>
// --- Inicialización única ---
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar resumen
    actualizarResumen();

    // Inicializar estado de observaciones
    document.querySelectorAll('.nota-input').forEach(input => {
        const match = input.name.match(/\[(\d+)\]/);
        if (match) {
            const idEstudiante = match[1];
            actualizarEstadoObservacion(input, idEstudiante);
        }
    });

    // Buscador de estudiantes
    const buscador = document.getElementById('buscador-estudiantes');
    if (buscador) {
        buscador.addEventListener('input', function() {
            const texto = this.value.toLowerCase().trim();
            const filas = document.querySelectorAll('.fila-estudiante');
            if (texto === '') {
                filas.forEach(fila => fila.style.display = '');
                return;
            }
            filas.forEach(fila => {
                const cedula = fila.querySelector('.cedula-estudiante').textContent.toLowerCase();
                const nombre = fila.querySelector('.nombre-estudiante').textContent.toLowerCase();
                if (cedula.includes(texto) || nombre.includes(texto)) {
                    fila.style.display = '';
                } else {
                    fila.style.display = 'none';
                }
            });
        });
    }

    // 🔒 CONFIGURACIÓN DE CAMPOS BLOQUEADOS
    const selectMateria = document.getElementById('select_materia');
    const notaInputs = document.querySelectorAll('.nota-input');

    function configurarCamposBloqueados() {
        if (!selectMateria) return;
        
        const materiaSeleccionada = selectMateria.value.trim() !== '';
        notaInputs.forEach(input => {
            const fila = input.closest('tr');
            const enRevision = fila && fila.getAttribute('data-en-revision') === '1';
            const enPendiente = fila && fila.getAttribute('data-en-pendiente') === '1';
            const inputWrapper = input.parentElement;
            
            // Limpiar overlay anterior si existe
            const overlayExistente = inputWrapper.querySelector('.nota-input-overlay');
            const badgeExistente = inputWrapper.querySelector('.badge-estado-campo');
            if (overlayExistente) overlayExistente.remove();
            if (badgeExistente) badgeExistente.remove();
            
            // Remover clases anteriores
            input.classList.remove('nota-input-blocked', 'text-info', 'text-warning');
            
            if (!materiaSeleccionada) {
                // Caso: sin materia seleccionada
                input.disabled = true;
                input.placeholder = '0.00';
                input.title = 'Debe seleccionar una materia primero';
                input.classList.add('bg-light', 'cursor-default');
            } else if (enRevision || enPendiente) {
                // Caso: en revisión o pendientes - hacerlo clickeable
                input.disabled = true;
                input.placeholder = '0.00';
                input.classList.add('nota-input-blocked');
                
                if (enRevision) {
                    input.classList.add('text-info');
                    input.title = 'Haga clic para ver detalles (En revisión)';
                    // Agregar overlay clickeable
                    const overlay = document.createElement('div');
                    overlay.className = 'nota-input-overlay';
                    overlay.title = 'Haga clic para ver detalles';
                    inputWrapper.style.position = 'relative';
                    inputWrapper.appendChild(overlay);
                    
                    // Agregar badge de estado
                    const badge = document.createElement('span');
                    badge.className = 'badge-estado-campo badge-revision';
                    badge.innerHTML = '<i class="fas fa-sync-alt"></i> Revisión';
                    inputWrapper.appendChild(badge);
                    
                    // Agregar evento al overlay
                    overlay.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        mostrarInformacionEstudiante(fila, 'revision');
                    });
                    
                } else if (enPendiente) {
                    input.classList.add('text-warning');
                    input.title = 'Haga clic para ver detalles (En pendientes)';
                    // Agregar overlay clickeable
                    const overlay = document.createElement('div');
                    overlay.className = 'nota-input-overlay';
                    overlay.title = 'Haga clic para ver detalles';
                    inputWrapper.style.position = 'relative';
                    inputWrapper.appendChild(overlay);
                    
                    // Agregar badge de estado
                    const badge = document.createElement('span');
                    badge.className = 'badge-estado-campo badge-pendiente';
                    badge.innerHTML = '<i class="fas fa-clock"></i> Pendiente';
                    inputWrapper.appendChild(badge);
                    
                    // Agregar evento al overlay
                    overlay.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        mostrarInformacionEstudiante(fila, 'pendiente');
                    });
                }
            } else {
                // Caso: editable normal
                input.disabled = false;
                input.placeholder = '0.00';
                input.title = 'Ingrese la nota (0-20)';
                input.classList.remove('bg-light', 'cursor-default');
            }
        });
    }

    // Función para mostrar información del estudiante
    function mostrarInformacionEstudiante(fila, tipo) {
        const cedula = fila.querySelector('.cedula-estudiante')?.textContent || '';
        const nombre = fila.querySelector('.nombre-estudiante')?.textContent || 'el estudiante';
        const notaActualElement = fila.querySelector('.nota-actual-display') || 
                                  fila.querySelector('#nota-actual-' + fila.dataset.estudianteId + ' .badge') ||
                                  fila.querySelector('.badge-light');
        const notaActual = notaActualElement ? notaActualElement.textContent.trim() : 'N/A';
        
        const icono = tipo === 'revision' ? '🔄' : '⏳';
        const titulo = tipo === 'revision' ? 'En proceso de revisión' : 'En materias pendientes';
        const color = tipo === 'revision' ? '#17a2b8' : '#ffc107';
        const seccion = tipo === 'revision' ? 'Recuperaciones → Gestión de Revisión' : 'Recuperaciones → Materias Pendientes';
        const explicacion = tipo === 'revision' 
            ? 'Esta nota no se puede modificar porque el estudiante ya está en proceso de revisión (segundo momento de revisión).'
            : 'Esta nota no se puede modificar porque el estudiante está en la lista de materias pendientes.';
        
        Swal.fire({
            icon: 'info',
            title: `${icono} ${titulo}`,
            html: `
                <div style="text-align: left;">
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 15px;">
                        <div class="row mb-2">
                            <div class="col-4"><strong><i class="fas fa-id-card"></i> Cédula:</strong></div>
                            <div class="col-8">${cedula}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-4"><strong><i class="fas fa-user"></i> Estudiante:</strong></div>
                            <div class="col-8">${nombre}</div>
                        </div>
                        <div class="row mb-0">
                            <div class="col-4"><strong><i class="fas fa-chart-line"></i> Nota Actual:</strong></div>
                            <div class="col-8"><span class="badge" style="background-color: ${color}; color: white;">${notaActual}</span></div>
                        </div>
                    </div>
                    
                    <div class="alert alert-info" style="border-left: 4px solid ${color};">
                        <i class="fas fa-info-circle"></i> <strong>Información:</strong>
                        <br><small>${explicacion}</small>
                    </div>
                    
                    <div class="alert alert-light border" style="border-left: 4px solid #6c757d;">
                        <i class="fas fa-directions"></i> <strong>Acción requerida:</strong>
                        <br><small>Para gestionar esta situación, diríjase a:</small>
                        <br><strong>${seccion}</strong>
                    </div>
                </div>
            `,
            confirmButtonText: 'Entendido',
            width: '500px',
            customClass: {
                popup: 'animated fadeIn',
                confirmButton: 'btn btn-primary'
            }
        });
    }

    // Inicializar configuración
    if (selectMateria) {
        configurarCamposBloqueados();
        selectMateria.addEventListener('change', configurarCamposBloqueados);
    }

    // 🔵 MANEJADOR DE CLICS DIRECTOS EN LOS INPUTS (por si acaso)
    document.addEventListener('click', function(e) {
        const target = e.target;
        
        // Si el clic es directamente en un input bloqueado
        if (target.classList && target.classList.contains('nota-input-blocked')) {
            e.preventDefault();
            e.stopPropagation();
            
            const fila = target.closest('tr');
            const enRevision = fila && fila.getAttribute('data-en-revision') === '1';
            const enPendiente = fila && fila.getAttribute('data-en-pendiente') === '1';
            
            if (enRevision) {
                mostrarInformacionEstudiante(fila, 'revision');
            } else if (enPendiente) {
                mostrarInformacionEstudiante(fila, 'pendiente');
            }
            return;
        }
        
        // Verificar si el clic fue en un input de nota normal
        const inputNota = target.closest('.nota-input');
        if (inputNota && !inputNota.classList.contains('nota-input-blocked')) {
            const fila = inputNota.closest('tr');
            const materiaSeleccionada = selectMateria && selectMateria.value.trim() !== '';
            
            // Verificar primero si hay materia seleccionada
            if (!materiaSeleccionada) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Materia no seleccionada',
                    html: '<div style="text-align: center;">Debe seleccionar una materia antes de ingresar notas.</div>',
                    confirmButtonText: 'Entendido',
                    customClass: {
                        popup: 'animated fadeIn'
                    }
                });
                return;
            }
            
            // Si está habilitado, permitir el foco
            if (!inputNota.disabled) {
                setTimeout(() => {
                    inputNota.focus();
                    inputNota.select();
                }, 10);
            }
        }
    });

    // Validación de formato al escribir
    document.addEventListener('input', function(e) {
        if (e.target.classList && e.target.classList.contains('nota-input') && 
            !e.target.disabled && !e.target.classList.contains('nota-input-blocked')) {
            
            let raw = e.target.value;
            
            // Permite borrar completamente
            if (raw === '') {
                const match = e.target.name.match(/\[(\d+)\]/);
                if (match) {
                    const idEstudiante = match[1];
                    actualizarNotaActual(e.target, idEstudiante);
                }
                return;
            }
            
            // Solo permitir números y un punto decimal
            raw = raw.replace(/[^\d.]/g, '');
            
            // Limitar a un solo punto
            const parts = raw.split('.');
            if (parts.length > 2) {
                raw = parts[0] + '.' + parts.slice(1).join('');
            }
            
            // Limitar a 2 decimales
            if (raw.includes('.')) {
                const [intPart, decPart] = raw.split('.');
                raw = intPart + '.' + (decPart || '').slice(0, 2);
            }
            
            e.target.value = raw;
            
            // Validar rango 0-20
            const valor = parseFloat(raw);
            if (!isNaN(valor)) {
                if (valor < 0) {
                    e.target.value = '0';
                } else if (valor > 20) {
                    e.target.value = '20';
                }
            }
            
            // Actualizar visualización
            const match = e.target.name.match(/\[(\d+)\]/);
            if (match) {
                const idEstudiante = match[1];
                actualizarNotaActual(e.target, idEstudiante);
            }
            
            actualizarResumen();
        }
    });

    // Manejar evento keydown
    document.addEventListener('keydown', function(e) {
        if (e.target.classList && e.target.classList.contains('nota-input') && 
            !e.target.disabled && !e.target.classList.contains('nota-input-blocked')) {
            
            const allowedKeys = [
                '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '.',
                'Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown',
                'Tab', 'Home', 'End'
            ];
            
            if (!allowedKeys.includes(e.key) && !e.ctrlKey && !e.metaKey) {
                e.preventDefault();
            }
        }
    });
});

// ---------------- Funciones auxiliares ----------------
function limpiarBusqueda() {
    const buscador = document.getElementById('buscador-estudiantes');
    if (buscador) buscador.value = '';
    const filas = document.querySelectorAll('.fila-estudiante');
    filas.forEach(fila => fila.style.display = '');
}

function limpiarNota(button, idEstudiante) {
    // Primero verificar si el estudiante está en revisión o pendientes
    const fila = button.closest('tr');
    const enRevision = fila && fila.getAttribute('data-en-revision') === '1';
    const enPendientes = fila && fila.getAttribute('data-en-pendientes') === '1';
    
    if (enRevision || enPendientes) {
        // Determinar el tipo correcto para el mensaje
        let tipo = '';
        let mensajeTipo = '';
        
        if (enRevision) {
            tipo = 'revisión';
            mensajeTipo = 'revisión';
        } else if (enPendientes) {
            tipo = 'materias pendientes';
            mensajeTipo = 'materias pendientes';
        }
        
        const nombreEstudiante = fila.querySelector('.nombre-estudiante')?.textContent || 'el estudiante';
        const cedula = fila.querySelector('.cedula-estudiante')?.textContent || '';
        
        Swal.fire({
            icon: 'warning',
            title: `Estudiante en ${tipo}`,
            html: `
                <div style="text-align: left;">
                    <div class="row mb-2">
                        <div class="col-4"><strong>Cédula:</strong></div>
                        <div class="col-8">${cedula}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-4"><strong>Estudiante:</strong></div>
                        <div class="col-8">${nombreEstudiante}</div>
                    </div>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> Esta nota no se puede modificar porque el estudiante ya está en proceso de revisión o materia pendiente.
                    </div>
                    ${enRevision ? `<div class="mt-3"><strong>Acción requerida:</strong><br>Para gestionar esta situación, diríjase a:<br><strong>Recuperaciones → Gestión de Revisión</strong></div>` : ''}
                </div>
            `,
            confirmButtonText: 'Entendido',
            customClass: {
                popup: 'animated fadeIn'
            }
        });
        return;
    }
    
    const input = button.closest('.input-group').querySelector('.nota-input');
    input.value = '';
    actualizarNotaActual(input, idEstudiante);
    actualizarResumen();
}

function actualizarEstadoObservacion(input, idEstudiante) {
    const valor = input.value;
    const notaOriginal = input.getAttribute('data-original') ?? '';
    const campoObservacion = document.getElementById('observacion-' + idEstudiante);

    if (campoObservacion) {
        const hayNotaOriginal = notaOriginal !== '' && notaOriginal !== null && notaOriginal !== 'null';
        const valorDiferente = valor !== '' && valor !== notaOriginal;
        const hayCambio = hayNotaOriginal && valorDiferente;
        
        if (hayCambio) {
            campoObservacion.style.display = 'block';
            campoObservacion.required = true;
        } else {
            campoObservacion.style.display = 'none';
            campoObservacion.required = false;
            if (valor === notaOriginal || valor === '') {
                campoObservacion.value = '';
            }
        }
    }
}

function actualizarNotaActual(input, idEstudiante) {
    const notaActualElement = document.getElementById('nota-actual-' + idEstudiante);
    const estadoElement = document.getElementById('estado-' + idEstudiante);
    const valor = input.value;

    if (valor !== '') {
        const nota = parseFloat(valor);
        // Mostrar con 2 decimales en pantalla
        const displayElement = document.querySelector(`#nota-actual-${idEstudiante} .nota-actual-display`);
        if (displayElement) {
            displayElement.textContent = nota.toFixed(2);
        }
        
        notaActualElement.innerHTML = `<span class="badge badge-light" style="font-size:14px;">${nota.toFixed(2)}</span>`;
        if (nota >= 10) {
            estadoElement.className = 'badge badge-success';
            estadoElement.textContent = 'Aprobado';
        } else {
            estadoElement.className = 'badge badge-danger';
            estadoElement.textContent = 'Reprobado';
        }
    } else {
        const displayElement = document.querySelector(`#nota-actual-${idEstudiante} .nota-actual-display`);
        if (displayElement) {
            displayElement.textContent = 'N/A';
        }
        
        notaActualElement.innerHTML = '<span class="text-muted"><em>Sin nota</em></span>';
        estadoElement.className = 'badge badge-warning';
        estadoElement.textContent = 'Pendiente';
    }

    actualizarEstadoObservacion(input, idEstudiante);
    actualizarResumen();
}

function actualizarResumen() {
    let totalNotas = 0;
    let notasConValor = 0;
    let aprobados = 0;
    let reprobados = 0;
    let totalEstudiantes = document.querySelectorAll('.nota-input').length;
    let cambios = 0;

    document.querySelectorAll('.nota-input').forEach(input => {
        if (!input.disabled && !input.classList.contains('nota-input-blocked')) {
            const valor = input.value;
            if (valor !== '') {
                const nota = parseFloat(valor);
                totalNotas += nota;
                notasConValor++;
                if (nota >= 10) aprobados++; else reprobados++;
                const notaOriginal = input.getAttribute('data-original') ?? '';
                if (valor !== notaOriginal) cambios++;
            }
        }
    });

    document.getElementById('contador-cargadas').textContent = notasConValor;
    document.getElementById('contador-pendientes').textContent = totalEstudiantes - notasConValor;
    document.getElementById('contador-aprobados').textContent = aprobados;
    document.getElementById('contador-reprobados').textContent = reprobados;
    document.getElementById('total-a-guardar').textContent = cambios;

    const promedio = notasConValor > 0 ? (totalNotas / notasConValor).toFixed(2) : '0.00';
    document.getElementById('promedio-notas').textContent = promedio;

    const detalleElement = document.getElementById('detalle-cambios');
    if (cambios > 0) {
        detalleElement.innerHTML = `(${cambios} cambios pendientes de guardar)`;
        detalleElement.className = 'text-warning';
    } else {
        detalleElement.innerHTML = `(Sin cambios pendientes)`;
        detalleElement.className = 'text-muted';
    }
}

function validarNotas() {
    let errores = [];
    document.querySelectorAll('.nota-input').forEach(input => {
        if (!input.disabled && !input.classList.contains('nota-input-blocked')) {
            const valor = input.value;
            if (valor !== '') {
                const nota = parseFloat(valor);
                if (isNaN(nota) || nota < 0 || nota > 20) {
                    const cedula = input.closest('tr').querySelector('.cedula-estudiante').textContent;
                    const nombre = input.closest('tr').querySelector('.nombre-estudiante').textContent;
                    errores.push(`${nombre} (${cedula}): Nota inválida (${valor})`);
                }
            }
        }
    });

    if (errores.length > 0) {
        Swal.fire({
            icon: 'error',
            title: 'Errores de Validación',
            html: `Se encontraron ${errores.length} error(es):<br><small>${errores.slice(0,5).join('<br>')}${errores.length>5?'<br>... y '+(errores.length-5)+' más':''}</small>`,
            confirmButtonText: 'Entendido',
            customClass: {
                popup: 'animated fadeIn'
            }
        });
    } else {
        const totalConNota = Array.from(document.querySelectorAll('.nota-input'))
            .filter(input => !input.disabled && !input.classList.contains('nota-input-blocked') && input.value !== '').length;
        const cambios = Array.from(document.querySelectorAll('.nota-input'))
            .filter(input => !input.disabled && !input.classList.contains('nota-input-blocked') && input.value !== input.getAttribute('data-original')).length;
        
        Swal.fire({
            icon: 'success',
            title: '✓ Validación Exitosa',
            html: `Todas las notas son válidas:<br>
                   <strong>${totalConNota}</strong> notas cargadas<br>
                   <strong>${cambios}</strong> cambios pendientes de guardar`,
            confirmButtonText: 'Continuar',
            customClass: {
                popup: 'animated fadeIn'
            }
        });
    }
}

function confirmarGuardado() {
    const cambios = Array.from(document.querySelectorAll('.nota-input'))
        .filter(input => !input.disabled && !input.classList.contains('nota-input-blocked') && input.value !== input.getAttribute('data-original')).length;

    if (cambios === 0) {
        Swal.fire({
            icon: 'info',
            title: 'Sin cambios para guardar',
            html: '<div style="text-align: center;">No hay cambios pendientes en las notas.</div>',
            confirmButtonText: 'Entendido',
            customClass: {
                popup: 'animated fadeIn'
            }
        });
        return;
    }

    Swal.fire({
        title: '¿Confirmar guardado de notas?',
        html: `Se guardarán <strong>${cambios}</strong> cambios en las notas.`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, guardar notas',
        cancelButtonText: 'Cancelar',
        allowOutsideClick: false,
        customClass: {
            popup: 'animated fadeIn'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Redondear notas antes de enviar
            document.querySelectorAll('.nota-input').forEach(input => {
                if (!input.disabled && !input.classList.contains('nota-input-blocked')) {
                    const notaOriginal = input.getAttribute('data-original') ?? '';
                    if (input.value !== '' && input.value !== notaOriginal) {
                        let valor = parseFloat(input.value);
                        if (!isNaN(valor)) {
                            const redondeada = Math.round(valor);
                            input.value = redondeada;
                            const match = input.name.match(/\[(\d+)\]/);
                            if (match) {
                                actualizarNotaActual(input, match[1]);
                            }
                        }
                    }
                }
            });

            Swal.fire({
                title: 'Guardando notas...',
                html: '<div style="text-align: center;"><i class="fas fa-spinner fa-spin fa-2x"></i><br><br>Procesando los cambios...</div>',
                allowOutsideClick: false,
                showConfirmButton: false,
                customClass: {
                    popup: 'animated fadeIn'
                }
            });
            
            // Enviar formulario
            setTimeout(() => {
                document.getElementById('form-carga-masiva').submit();
            }, 500);
        }
    });
}
</script>