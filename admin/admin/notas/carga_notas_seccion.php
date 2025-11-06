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

// 🔹 Preparación para detectar "tercer lapso" por posición
$lapsos_ids_ordered = array_column($lapsos, 'id_lapso'); // mantiene el orden por fecha_inicio
$lapso_pos = null;
if (!empty($lapsos_ids_ordered) && isset($_GET['lapso'])) {
    $lapso_pos = array_search(intval($_GET['lapso']), $lapsos_ids_ordered);
}
$es_tercer_lapso = ($lapso_pos !== false && $lapso_pos === 2); // índice 2 => 3er lapso en orden

// 🔹 Variables para filtros
$id_seccion_filtro = $_GET['seccion'] ?? null;
$id_materia_filtro = $_GET['materia'] ?? null;
$id_lapso_filtro   = $_GET['lapso'] ?? null;

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

include('../../admin/layout/parte1.php');
?>

<!-- Mostrar mensajes de sesión (success / error) con SweetAlert2 -->
<?php if (!empty($_SESSION['mensaje_exito']) || !empty($_SESSION['success_message'])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'success',
                title: 'Éxito',
                html: `<?= addslashes(htmlspecialchars($_SESSION['mensaje_exito'] ?? $_SESSION['success_message'])) ?>`,
                confirmButtonText: 'Aceptar'
            });
        });
    </script>
    <?php unset($_SESSION['mensaje_exito'], $_SESSION['success_message']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['error_message']) || !empty($_SESSION['error'])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                html: `<?= addslashes(htmlspecialchars($_SESSION['error_message'] ?? $_SESSION['error'])) ?>`,
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
                                                <?php foreach ($lapsos as $lapso): ?>
                                                    <option value="<?= $lapso['id_lapso'] ?>" <?= ($id_lapso_filtro == $lapso['id_lapso']) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($lapso['nombre_lapso']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
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
                
                // Obtener estudiantes inscritos en la sección
                $sql_estudiantes = "
                SELECT e.id_estudiante, e.cedula, e.nombres, e.apellidos
                FROM estudiantes e
                JOIN inscripciones i ON e.id_estudiante = i.id_estudiante
                WHERE i.id_seccion = :id_seccion 
  AND i.estado = 'activo' 
  AND e.estatus = 'Activo'

                ORDER BY e.apellidos, e.nombres";
                
                $query_estudiantes = $pdo->prepare($sql_estudiantes);
                $query_estudiantes->bindParam(':id_seccion', $id_seccion_filtro);
                $query_estudiantes->execute();
                $estudiantes = $query_estudiantes->fetchAll(PDO::FETCH_ASSOC);
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

                                <!-- BOTÓN: Gestionar Recuperaciones (solo si es 3er lapso/posición 2) -->
                                <?php if ($es_tercer_lapso): ?>
                                    <a href="recuperaciones.php?seccion=<?= $id_seccion_filtro ?>&materia=<?= $id_materia_filtro ?>&lapso=<?= $id_lapso_filtro ?>"
                                       class="" title="">
                                       <i class=""></i> 
                                    </a>
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
                                        <tr class="fila-estudiante" data-cedula="<?= htmlspecialchars($estudiante['cedula']) ?>" data-nombre="<?= htmlspecialchars($estudiante['apellidos'] . ' ' . $estudiante['nombres']) ?>">
                                            <td><?= $contador++ ?></td>
                                            <td class="cedula-estudiante"><?= htmlspecialchars($estudiante['cedula']) ?></td>
                                            <td class="nombre-estudiante"><?= htmlspecialchars($estudiante['apellidos'] . ', ' . $estudiante['nombres']) ?></td>
                                            <td class="text-center">
                                                <span class="nota-actual" id="nota-actual-<?= $estudiante['id_estudiante'] ?>">
                                                    <?php if($nota_actual !== '' && $nota_actual !== null): ?>
                                                        <span class="badge badge-light" style="font-size:14px;">
                                                            <?= number_format($nota_actual, 2) ?>
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
                                                           oninput="actualizarNotaActual(this, <?= $estudiante['id_estudiante'] ?>)">
                                                    <div class="input-group-append">
                                                        <button type="button" class="btn btn-outline-secondary" onclick="limpiarNota(this, <?= $estudiante['id_estudiante'] ?>)" title="Limpiar nota">
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
                                                       id="observacion-<?= $estudiante['id_estudiante'] ?>">
                                            </td>

                                            <td class="text-center estado-nota">
                                                <span class="badge <?= $estado_clase ?>" id="estado-<?= $estudiante['id_estudiante'] ?>">
                                                    <?= $estado_texto ?>
                                                </span>
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
                                                <a href="recuperaciones.php?seccion=<?= $id_seccion_filtro ?>&materia=<?= $id_materia_filtro ?>&lapso=<?= $id_lapso_filtro ?>" 
                                                   class="btn btn-warning btn-lg mr-3" title="Gestionar Recuperaciones">
                                                    <i class="fas fa-book-open"></i> Gestionar Recuperaciones
                                                </a>
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

<script>
// --- Inicialización única (conserva todas tus funciones y validaciones) ---
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar resumen
    actualizarResumen();

    // Inicializar estado de observaciones para cada estudiante
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

    // Validación de rango al escribir (delegada)
    document.addEventListener('input', function(e) {
        if (e.target.classList && e.target.classList.contains('nota-input')) {
            let valor = parseFloat(e.target.value);
            if (isNaN(valor)) {
                // allow empty
            } else if (valor < 0) {
                e.target.value = 0;
            } else if (valor > 20) {
                e.target.value = 20;
            }
            actualizarResumen();
        }
    });

    // 🔒 BLOQUEO AUTOMÁTICO + ACTUALIZAR PLACEHOLDERS
    const selectMateria = document.getElementById('select_materia');
    const notaInputs = document.querySelectorAll('.nota-input');

    function actualizarBloqueo() {
        if (!selectMateria) return;
        const materiaSeleccionada = selectMateria.value.trim() !== '';
        notaInputs.forEach(input => {
            input.disabled = !materiaSeleccionada;
            if (!materiaSeleccionada) {
                input.placeholder = 'Seleccione una materia primero';
                input.classList.add('bg-light');
            } else {
                input.placeholder = '0.00';
                input.classList.remove('bg-light');
            }
        });
    }

    if (selectMateria) {
        actualizarBloqueo();
        selectMateria.addEventListener('change', actualizarBloqueo);
    }

    // Mostrar advertencia si intenta escribir o hacer clic sin materia
    // Usamos delegación sobre la tabla para cubrir clicks sobre inputs deshabilitados
    const tabla = document.getElementById('tabla-estudiantes');
    if (tabla) {
        tabla.addEventListener('click', function(evt) {
            const target = evt.target;
            const selectVal = (selectMateria && selectMateria.value) ? selectMateria.value.trim() : '';
            const clickedNotaInput = target.closest('.nota-input') || target.closest('.input-group') || target.closest('.input-group-append') || target.classList.contains('nota-input');
            if (!selectVal && clickedNotaInput) {
                evt.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Seleccione una materia',
                    text: 'Debe seleccionar una materia antes de ingresar notas.',
                    confirmButtonText: 'Entendido'
                });
                return;
            }
        });
    }

    // Además, si por alguna razón inputs no están deshabilitados, también añadimos click directo
    notaInputs.forEach(input => {
        input.addEventListener('click', function() {
            if (selectMateria && !selectMateria.value.trim()) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Seleccione una materia',
                    text: 'Debe seleccionar una materia antes de ingresar notas.',
                    confirmButtonText: 'Entendido'
                });
            }
        });
    });
});

// ---------------- Funciones existentes (sin cambios lógicos) ----------------
function limpiarBusqueda() {
    const buscador = document.getElementById('buscador-estudiantes');
    if (buscador) buscador.value = '';
    const filas = document.querySelectorAll('.fila-estudiante');
    filas.forEach(fila => fila.style.display = '');
}

function limpiarNota(button, idEstudiante) {
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
        
        console.log('Nota original:', notaOriginal, 'Valor actual:', valor, 'Hay cambio:', hayCambio);
        
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
        notaActualElement.innerHTML = `<span class="badge badge-light" style="font-size:14px;">${nota.toFixed(2)}</span>`;
        if (nota >= 10) {
            estadoElement.className = 'badge badge-success';
            estadoElement.textContent = 'Aprobado';
        } else {
            estadoElement.className = 'badge badge-danger';
            estadoElement.textContent = 'Reprobado';
        }
    } else {
        notaActualElement.innerHTML = '<span class="text-muted"><em>Sin nota</em></span>';
        estadoElement.className = 'badge badge-warning';
        estadoElement.textContent = 'Pendiente';
    }

    // Actualizar estado de la observación
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
        const valor = input.value;
        if (valor !== '') {
            const nota = parseFloat(valor);
            totalNotas += nota;
            notasConValor++;
            if (nota >= 10) aprobados++; else reprobados++;
            const notaOriginal = input.getAttribute('data-original') ?? '';
            if (valor !== notaOriginal) cambios++;
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
        const valor = input.value;
        if (valor !== '') {
            const nota = parseFloat(valor);
            if (!(nota >= 0 && nota <= 20)) {
                const cedula = input.closest('tr').querySelector('.cedula-estudiante').textContent;
                errores.push(`Cédula ${cedula}: Nota fuera de rango (${valor})`);
            }
        }
    });

    if (errores.length > 0) {
        Swal.fire({
            icon: 'error',
            title: 'Errores de Validación',
            html: `Se encontraron ${errores.length} error(es):<br><small>${errores.slice(0,5).join('<br>')}${errores.length>5?'<br>... y '+(errores.length-5)+' más':''}</small>`,
            confirmButtonText: 'Entendido'
        });
    } else {
        const totalConNota = Array.from(document.querySelectorAll('.nota-input')).filter(input => input.value !== '').length;
        const cambios = Array.from(document.querySelectorAll('.nota-input')).filter(input => input.value !== input.getAttribute('data-original')).length;
        Swal.fire({
            icon: 'success',
            title: '✓ Validación Exitosa',
            html: `Todas las notas son válidas:<br><strong>${totalConNota}</strong> notas cargadas de <strong>${document.querySelectorAll('.nota-input').length}</strong> estudiantes<br><strong>${cambios}</strong> cambios pendientes de guardar`,
            confirmButtonText: 'Continuar'
        });
    }
}

function confirmarGuardado() {
    const cambios = Array.from(document.querySelectorAll('.nota-input')).filter(input => {
        const notaOriginal = input.getAttribute('data-original') ?? '';
        return input.value !== notaOriginal;
    }).length;

    if (cambios === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Sin cambios para guardar',
            text: 'No hay cambios pendientes en las notas.',
            confirmButtonText: 'Entendido'
        });
        return;
    }

    Swal.fire({
        title: '¿Confirmar guardado de notas?',
        html: `Se guardarán <strong>${cambios}</strong> cambios en las notas.<br><br><small>Esta acción actualizará las notas en el sistema.</small>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, guardar notas',
        cancelButtonText: 'Cancelar',
        allowOutsideClick: false
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Guardando notas...',
                text: 'Por favor espere mientras se procesan las notas',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });
            document.getElementById('form-carga-masiva').submit();
        }
    });
}

// Actualizar cuando se escribe en el input (delegado en todo el documento)
document.addEventListener('input', function(e) {
    if (e.target.classList && e.target.classList.contains('nota-input')) {
        const match = e.target.name.match(/\[(\d+)\]/);
        if (match) {
            const idEstudiante = match[1];
            actualizarNotaActual(e.target, idEstudiante);
        }
    }
});
</script>