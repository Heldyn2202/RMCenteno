<?php
session_start();
include('../../app/config.php');
include('../../admin/layout/parte1.php');

// 🔹 Gestión activa
$sql_gestion = "SELECT * FROM gestiones WHERE estado = 1 LIMIT 1";
$query_gestion = $pdo->prepare($sql_gestion);
$query_gestion->execute();
$gestion_activa = $query_gestion->fetch(PDO::FETCH_ASSOC);

// ----------------------------
// Detectar si el usuario es docente
// ----------------------------
$rol_sesion = $_SESSION['rol_sesion_usuario'] ?? $_SESSION['rol'] ?? null;
$rol_id    = $_SESSION['rol_id'] ?? null;
$es_docente_flag = $_SESSION['es_docente'] ?? false;

// Intentar obtener id_profesor desde la sesión (si tu sistema lo guarda ahí)
$id_profesor = $_SESSION['id_profesor'] ?? null;

// Si es docente y no tenemos id_profesor en sesión, intentar cargar el verificador (si existe)
if (($es_docente_flag || $rol_id == 5 || (is_string($rol_sesion) && stripos($rol_sesion, 'doc') !== false)) && empty($id_profesor)) {
    // intentamos incluir el helper verificar_docente si está disponible en ruta relativa
    // Ajusta las rutas si en tu proyecto el archivo está en otra ubicación
    if (file_exists(__DIR__ . '/../../../verificar_docente.php')) {
        require_once(__DIR__ . '/../../../verificar_docente.php');
    } elseif (file_exists(__DIR__ . '/../../verificar_docente.php')) {
        require_once(__DIR__ . '/../../verificar_docente.php');
    } elseif (file_exists(__DIR__ . '/../../admin/verificar_docente.php')) {
        require_once(__DIR__ . '/../../admin/verificar_docente.php');
    }

    if (function_exists('verificarDocente')) {
        try {
            $datos_docente = verificarDocente();
            if (!empty($datos_docente['id_profesor'])) {
                $id_profesor = $datos_docente['id_profesor'];
                // opcional: mantener en sesión para futuras páginas
                $_SESSION['id_profesor'] = $id_profesor;
            }
        } catch (Throwable $e) {
            // no hacer nada, fallback a mostrar todas las secciones
        }
    }
}

// 🔹 Secciones
// Si es docente y tenemos $id_profesor -> solo traer las secciones asignadas a ese profesor en la gestión activa
if (!empty($id_profesor) && ($es_docente_flag || $rol_id == 5 || (is_string($rol_sesion) && stripos($rol_sesion, 'doc') !== false))) {
    $sql_secciones = "SELECT DISTINCT s.id_seccion, CONCAT(g.grado, ' - ', s.nombre_seccion) AS nombre
                      FROM secciones s
                      INNER JOIN grados g ON g.id_grado = s.id_grado
                      INNER JOIN asignaciones_profesor ap ON ap.id_seccion = s.id_seccion
                      WHERE s.estado = 1
                        AND ap.estado = 1
                        AND ap.id_profesor = :id_profesor
                        AND ap.id_gestion = :id_gestion
                      ORDER BY g.id_grado, s.nombre_seccion";
    $query_secciones = $pdo->prepare($sql_secciones);
    $query_secciones->execute([
        ':id_profesor' => $id_profesor,
        ':id_gestion'  => $gestion_activa['id_gestion'] ?? 0
    ]);
    $secciones = $query_secciones->fetchAll(PDO::FETCH_ASSOC);
} else {
    // administrador u otros roles -> listar todas las secciones activas (comportamiento original)
    $sql_secciones = "SELECT s.id_seccion, CONCAT(g.grado, ' - ', s.nombre_seccion) AS nombre
                      FROM secciones s  
                      INNER JOIN grados g ON g.id_grado = s.id_grado
                      WHERE s.estado = 1
                      ORDER BY g.id_grado, s.nombre_seccion";
    $query_secciones = $pdo->prepare($sql_secciones);
    $query_secciones->execute();
    $secciones = $query_secciones->fetchAll(PDO::FETCH_ASSOC);
}

// 🔹 Lapsos
$sql_lapsos = "SELECT id_lapso, nombre_lapso FROM lapsos WHERE id_gestion = :id_gestion ORDER BY fecha_inicio";
$query_lapsos = $pdo->prepare($sql_lapsos);
$query_lapsos->bindParam(':id_gestion', $gestion_activa['id_gestion']);
$query_lapsos->execute();
$lapsos = $query_lapsos->fetchAll(PDO::FETCH_ASSOC);

// 🔹 Filtros
$id_seccion = $_GET['seccion'] ?? '';
$id_lapsos = $_GET['lapsos'] ?? [];
if (!is_array($id_lapsos)) $id_lapsos = [$id_lapsos];

$resultados = [];

if ($id_seccion && !empty($id_lapsos)) {
    $id_lapsos_str = implode(',', array_map('intval', $id_lapsos));

    $sql = "
    SELECT 
        e.id_estudiante,
        e.nombres,
        e.apellidos,
        COUNT(DISTINCT ap.id_materia) AS total_materias,
        SUM(CASE WHEN EXISTS (
            SELECT 1 FROM notas_estudiantes ne2
            WHERE ne2.id_estudiante = e.id_estudiante 
              AND ne2.id_materia = ap.id_materia 
              AND ne2.id_lapso IN ($id_lapsos_str)
              AND ne2.calificacion IS NOT NULL
        ) THEN 1 ELSE 0 END) AS materias_con_nota
    FROM inscripciones i
    INNER JOIN estudiantes e ON e.id_estudiante = i.id_estudiante
    INNER JOIN secciones s ON s.id_seccion = i.id_seccion
    INNER JOIN asignaciones_profesor ap ON ap.id_seccion = s.id_seccion
    WHERE i.estado = 'activo'
      AND s.estado = 1 
      AND ap.estado = 1
      AND i.id_gestion = :id_gestion
      AND s.id_seccion = :id_seccion
    GROUP BY e.id_estudiante, e.nombres, e.apellidos
    ORDER BY e.nombres, e.apellidos;
    ";

    $query = $pdo->prepare($sql);
    $query->bindParam(':id_gestion', $gestion_activa['id_gestion']);
    $query->bindParam(':id_seccion', $id_seccion);
    $query->execute();
    $resultados = $query->fetchAll(PDO::FETCH_ASSOC);
}
?>

<div class="content-wrapper">
    <div class="content">
        <div class="container-fluid">

            <!-- Encabezado -->
            <div class="content-header">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h3 class="m-0 text-primary"><i class="fas fa-chart-bar"></i> Progreso de Carga de Notas</h3>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= APP_URL ?>/admin">Inicio</a></li>
                            <li class="breadcrumb-item"><a href="<?= APP_URL ?>/admin/notas">Notas</a></li>
                            <li class="breadcrumb-item active">Progreso</li>
                        </ol>
                    </div>
                </div>
            </div>

            <!-- Filtros -->
            <div class="card card-primary card-outline">
                <div class="card-header text-white" style="background: linear-gradient(90deg, #007bff, #00c6ff);">
                    <h5 class="card-title"><i class="fas fa-filter"></i> Filtros de Búsqueda</h5>
                </div>
                <form id="formFiltros" method="get">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <label>Gestión Activa</label>
                                <input type="text" class="form-control" readonly 
                                       value="<?= $gestion_activa ? date('Y', strtotime($gestion_activa['desde'])) . ' - ' . date('Y', strtotime($gestion_activa['hasta'])) : 'Sin gestión activa' ?>">
                            </div>

                            <div class="col-md-4">
                                <label>Sección</label>
                                <select name="seccion" class="form-control" required>
                                    <option value="">Seleccionar</option>
                                    <?php foreach ($secciones as $s): ?>
                                        <option value="<?= $s['id_seccion'] ?>" <?= $id_seccion == $s['id_seccion'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($s['nombre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (!empty($id_profesor) && ($es_docente_flag || $rol_id == 5 || (is_string($rol_sesion) && stripos($rol_sesion,'doc')!==false))): ?>
                                    <small class="text-muted">Mostrando únicamente las secciones que usted imparte en la gestión activa.</small>
                                <?php endif; ?>
                            </div>

                            <!-- Checkboxes de lapsos -->
                            <div class="col-md-4">
                                <label><b>Seleccione el lapso que desea generar la boleta:</b></label><br>
                                <?php foreach ($lapsos as $l): ?>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input chk-lapso" type="checkbox" name="lapsos[]" 
                                               value="<?= $l['id_lapso'] ?>" 
                                               <?= in_array($l['id_lapso'], $id_lapsos) ? 'checked' : '' ?>>
                                        <label class="form-check-label"><?= htmlspecialchars($l['nombre_lapso']) ?></label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="text-center mt-3">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Buscar</button>
                            <a href="ver_progreso_notas.php" class="btn btn-secondary"><i class="fas fa-undo"></i> Limpiar</a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Resultados -->
            <?php if ($id_seccion && !empty($id_lapsos)): ?>
                <div class="card card-success card-outline mt-4">
                    <div class="card-header text-white" 
                         style="background: linear-gradient(90deg, #28a745, #7be495);">
                        <h5 class="card-title mb-0"><i class="fas fa-list"></i> Resultados del Progreso</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($resultados)): ?>
                            <div class="alert alert-warning text-center">
                                <i class="fas fa-exclamation-triangle"></i> No hay estudiantes registrados o notas cargadas.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table id="tablaProgreso" class="table table-bordered table-striped table-hover text-center">
                                    <thead class="text-white" style="background: linear-gradient(90deg, #007bff, #00a6ff);">
                                        <tr>
                                            <th></th>
                                            <th>#</th>
                                            <th>Estudiante</th>
                                            <th>Total Materias</th>
                                            <th>Con Nota</th>
                                            <th>Faltantes</th>
                                            <th>Progreso</th>
                                            <th>Estado</th>
                                            <th>Boleta</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $n=1; foreach ($resultados as $r): 
                                            $faltantes = $r['total_materias'] - $r['materias_con_nota'];
                                            $porcentaje = ($r['materias_con_nota'] / $r['total_materias']) * 100;
                                            $color = $porcentaje == 100 ? 'success' : ($porcentaje >= 50 ? 'warning' : 'danger');
                                            $estado = $porcentaje == 100 ? 'Completo' : 'Incompleto';
                                        ?>
                                            <tr data-id="<?= $r['id_estudiante'] ?>">
                                                <td><button class="btn btn-info btn-sm btn-detalle"><i class="fas fa-plus"></i></button></td>
                                                <td><?= $n++ ?></td>
                                                <td class="text-left"><?= htmlspecialchars($r['nombres'].' '.$r['apellidos']) ?></td>
                                                <td><?= $r['total_materias'] ?></td>
                                                <td class="text-success font-weight-bold"><?= $r['materias_con_nota'] ?></td>
                                                <td class="text-danger font-weight-bold"><?= $faltantes ?></td>
                                                <td>
                                                    <div class="progress" style="height: 20px;">
                                                        <div class="progress-bar bg-<?= $color ?>" style="width: <?= $porcentaje ?>%">
                                                            <?= round($porcentaje,2) ?>%
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><span class="badge badge-<?= $color ?> estado"><?= $estado ?></span></td>
                                                <td>
                                                    <?php if ($porcentaje == 100): ?>
                                                        <a href="generar_boleta.php?id_estudiante=<?= $r['id_estudiante'] ?>&lapsos=<?= implode(',', $id_lapsos) ?>" 
                                                           class="btn btn-success btn-sm btn-boleta" target="_blank" title="Generar Boleta">
                                                           <i class="fas fa-file-pdf"></i>
                                                        </a>
                                                    <?php else: ?>
                                                        <button class="btn btn-secondary btn-sm" disabled title="Notas incompletas">
                                                            <i class="fas fa-file-pdf"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.btn-detalle { padding:2px 6px; font-size:0.75rem; }
</style>

<script>
$(document).ready(function(){
    const tabla = $('#tablaProgreso').DataTable({
        responsive:true,
        autoWidth:false,
        language:{
            decimal: ",",
            thousands: ".",
            processing:     "Procesando...",
            search:         "Buscar:",
            lengthMenu:     "Mostrar _MENU_ registros",
            info:           "Mostrando _START_ a _END_ de _TOTAL_ registros",
            infoEmpty:      "Mostrando 0 a 0 de 0 registros",
            infoFiltered:   "(filtrado de _MAX_ registros en total)",
            loadingRecords: "Cargando...",
            zeroRecords:    "No se encontraron resultados",
            emptyTable:     "No hay datos disponibles en la tabla",
            paginate: {
                first:      "Primero",
                previous:   "Anterior",
                next:       "Siguiente",
                last:       "Último"
            },
            aria: {
                sortAscending:  ": activar para ordenar columna ascendente",
                sortDescending: ": activar para ordenar columna descendente"
            }
        }
    });

    // 🔹 Mostrar/ocultar detalle con profesor y nota
    $('#tablaProgreso').on('click','.btn-detalle',function(){
        const tr = $(this).closest('tr');
        const row = tabla.row(tr);
        const id_estudiante = tr.data('id');
        const lapsos = $('.chk-lapso:checked').map(function(){ return $(this).val(); }).get();

        if(row.child.isShown()){
            row.child.hide();
            $(this).html('<i class="fas fa-plus"></i>').removeClass('btn-danger').addClass('btn-info');
        } else {
            $(this).html('<i class="fas fa-minus"></i>').removeClass('btn-info').addClass('btn-danger');
            $.post('ajax/obtener_detalle_notas.php',{id_estudiante,lapsos},function(data){
                row.child(data).show();
            });
        }
    });

    // 🔹 Actualiza automáticamente al cambiar lapsos
    $('.chk-lapso').on('change', function(){
        $('#formFiltros').submit();
    });
});
</script>

<?php include('../../admin/layout/parte2.php'); include('../../layout/mensajes.php'); ?>