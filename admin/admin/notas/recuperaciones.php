<?php
session_start();
require_once('../../app/config.php');
include('../../admin/layout/parte1.php');
include('../../layout/mensajes.php');

// ==== Captura de parámetros ====
$id_seccion = $_GET['seccion'] ?? null;
$id_materia = $_GET['materia'] ?? null;
$id_lapso   = $_GET['lapso'] ?? null;
$tipo       = $_GET['tipo'] ?? null; 

if (!$id_seccion || !$id_materia || !$id_lapso) {
    echo "<script>alert('Datos incompletos.'); window.location.href='carga_notas_seccion.php';</script>";
    exit;
}

// ==== Obtener gestión activa (para mostrarla en la vista y usarla en mensajes) ====
$sql_gestion = "SELECT id_gestion, CONCAT('Periodo ', DATE_FORMAT(desde, '%Y'), ' - ', DATE_FORMAT(hasta, '%Y')) AS nombre_gestion FROM gestiones WHERE estado = 1 LIMIT 1";
$stmt_g = $pdo->prepare($sql_gestion);
$stmt_g->execute();
$gestion_activa_row = $stmt_g->fetch(PDO::FETCH_ASSOC);
$gestion_activa = $gestion_activa_row['nombre_gestion'] ?? 'Gestión no activa';

// ==== Obtener información de la materia ====
$sql_materia = "SELECT nombre_materia FROM materias WHERE id_materia = :id_materia";
$stmt_m = $pdo->prepare($sql_materia);
$stmt_m->execute([':id_materia' => $id_materia]);
$materia_info = $stmt_m->fetch(PDO::FETCH_ASSOC);
$nombre_materia = $materia_info['nombre_materia'] ?? 'Materia Desconocida';

// ==== Determinar tipo de proceso ====
if (!$tipo) {
    $sql_lapso = "SELECT nombre_lapso FROM lapsos WHERE id_lapso = :id_lapso";
    $stmt_lapso = $pdo->prepare($sql_lapso);
    $stmt_lapso->execute([':id_lapso' => $id_lapso]);
    $lapso_info = $stmt_lapso->fetch(PDO::FETCH_ASSOC);
    $nombre_lapso = $lapso_info['nombre_lapso'] ?? '';

    if (stripos($nombre_lapso, 'Tercer Lapso') !== false) {
        $tipo = 'revision';
        $max_intentos = 2;
    } else {
        $tipo = 'pendiente';
        $max_intentos = 4;
    }
} else {
    $max_intentos = ($tipo === 'revision') ? 2 : 4;
}

// ==== Consultar estudiantes según etapa ====
if ($tipo === 'revision') {
    $sql_estudiantes = "
        SELECT 
            e.id_estudiante, 
            e.nombres, 
            e.apellidos, 
            (
                SELECT SUM(n_sum.calificacion) 
                FROM notas_estudiantes n_sum 
                WHERE n_sum.id_estudiante = e.id_estudiante 
                AND n_sum.id_materia = :id_materia
            ) AS suma_lapsos,
            COALESCE(
                (SELECT r.calificacion FROM recuperaciones r 
                 WHERE r.id_estudiante = e.id_estudiante 
                 AND r.id_materia = :id_materia 
                 AND r.id_seccion = :id_seccion 
                 AND r.tipo = 'revision'
                 ORDER BY r.intento DESC LIMIT 1
                 ), 0 
            ) AS nota_actual,
            COALESCE(
                (SELECT MAX(r.intento) FROM recuperaciones r 
                 WHERE r.id_estudiante = e.id_estudiante 
                 AND r.id_materia = :id_materia 
                 AND r.id_seccion = :id_seccion 
                 AND r.tipo = 'revision'), 0
            ) AS intento_actual
        FROM inscripciones i
        INNER JOIN estudiantes e ON e.id_estudiante = i.id_estudiante
        WHERE i.id_seccion = :id_seccion
          AND i.estado = 'activo'
          AND (
              SELECT SUM(n_sum.calificacion) 
              FROM notas_estudiantes n_sum 
              WHERE n_sum.id_estudiante = e.id_estudiante 
              AND n_sum.id_materia = :id_materia
          ) < 30
          AND (
              COALESCE(
                  (SELECT r.calificacion FROM recuperaciones r 
                   WHERE r.id_estudiante = e.id_estudiante 
                   AND r.id_materia = :id_materia 
                   AND r.id_seccion = :id_seccion 
                   AND r.tipo = 'revision'
                   ORDER BY r.intento DESC LIMIT 1
                   ), 0 
              ) < 10
            )
          AND (
              (SELECT COUNT(*) FROM recuperaciones r 
               WHERE r.id_estudiante = e.id_estudiante 
               AND r.id_materia = :id_materia 
               AND r.id_seccion = :id_seccion 
               AND r.tipo = 'revision') < 2
            )
        GROUP BY e.id_estudiante 
        ORDER BY e.apellidos, e.nombres
    ";
} else {
    $sql_estudiantes = "
        SELECT 
            mp.id_estudiante, 
            e.nombres, 
            e.apellidos,
            COALESCE(MAX(CASE WHEN r.tipo = 'pendiente' THEN r.calificacion END), 0) AS nota_actual,
            COALESCE(MAX(CASE WHEN r.tipo = 'pendiente' THEN r.intento END), 0) AS intento_actual
        FROM materias_pendientes mp
        INNER JOIN estudiantes e ON mp.id_estudiante = e.id_estudiante
        LEFT JOIN recuperaciones r 
            ON r.id_estudiante = mp.id_estudiante 
            AND r.id_materia = mp.id_materia
            AND r.tipo = 'pendiente'
        WHERE mp.id_materia = :id_materia AND mp.id_seccion = :id_seccion
        GROUP BY mp.id_estudiante, e.nombres, e.apellidos
        HAVING intento_actual < 4 
        ORDER BY e.apellidos, e.nombres
    ";
}

$stmt = $pdo->prepare($sql_estudiantes);
$stmt->execute([':id_materia' => $id_materia, ':id_seccion' => $id_seccion]);
$estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ==== HISTORIAL: Agrupar por Estudiante ====
$sql_historial = "
    SELECT r.*, e.nombres, e.apellidos, m.nombre_materia
    FROM recuperaciones r
    INNER JOIN estudiantes e ON e.id_estudiante = r.id_estudiante
    INNER JOIN materias m ON m.id_materia = r.id_materia
    WHERE r.id_materia = :id_materia AND r.id_seccion = :id_seccion
    ORDER BY e.apellidos, e.nombres, r.fecha_registro DESC
";
$stmt_h = $pdo->prepare($sql_historial);
$stmt_h->execute([':id_materia'=>$id_materia, ':id_seccion'=>$id_seccion]);
$historial_raw = $stmt_h->fetchAll(PDO::FETCH_ASSOC);

// Agrupamiento de registros por estudiante
$historial_agrupado = [];
foreach ($historial_raw as $registro) {
    $id_estudiante = $registro['id_estudiante'];
    $nombre_completo = htmlspecialchars($registro['nombres'] . ' ' . $registro['apellidos']);

    if (!isset($historial_agrupado[$id_estudiante])) {
        $historial_agrupado[$id_estudiante] = [
            'id_estudiante' => $id_estudiante,
            'nombre_completo' => $nombre_completo,
            'registros' => []
        ];
    }
    // Añadimos el registro al array del estudiante
    $historial_agrupado[$id_estudiante]['registros'][] = $registro;
}
?>

<style>
td.details-control { cursor: pointer; width: 20px; text-align: center; color: #007bff; }
td.details-control i.fa-minus-circle { display: none; }
tr.shown td.details-control i.fa-plus-circle { display: none; }
tr.shown td.details-control i.fa-minus-circle { display: inline; }
.child-row-table { width: 95%; margin: 10px auto; }
.child-row-table th, .child-row-table td { padding: 5px; }
</style>

<div class="content-wrapper">
    <div class="content">
        <div class="container-fluid">
            <div class="content-header">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h3 class="m-0 text-primary">
                            <i class="fas fa-book-open"></i>
                            <?= $tipo == 'revision' ? 'Gestión de Revisión' : 'Gestión de Materias Pendientes' ?>
                        </h3>
                        <h6 class="text-muted">Materia: <strong><?= htmlspecialchars($nombre_materia) ?></strong></h6>
                        <!-- Mostrar gestión activa -->
                        <h6 class="text-muted">Gestión activa: <strong><?= htmlspecialchars($gestion_activa) ?></strong></h6>

                        <?php if ($tipo === 'revision'): ?>
                            <a href="recuperaciones.php?seccion=<?= $id_seccion ?>&materia=<?= $id_materia ?>&lapso=<?= $id_lapso ?>&tipo=pendiente" 
                               class="btn btn-warning btn-sm mt-2">
                               <i class="fas fa-arrow-right"></i> Ir a Materias Pendientes
                            </a>
                        <?php elseif ($tipo === 'pendiente'): ?>
                            <a href="recuperaciones.php?seccion=<?= $id_seccion ?>&materia=<?= $id_materia ?>&lapso=<?= $id_lapso ?>&tipo=revision" 
                               class="btn btn-secondary btn-sm mt-2">
                               <i class="fas fa-arrow-left"></i> Volver a Revisión
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="col-sm-6 text-right">
                        <a href="carga_notas_seccion.php?seccion=<?= $id_seccion ?>&materia=<?= $id_materia ?>&lapso=<?= $id_lapso ?>" 
                            class="btn btn-dark"><i class="fas fa-home"></i> Volver al Inicio</a>
                    </div>
                </div>
            </div>

            <div class="card card-primary card-outline shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-edit"></i> Registrar Notas de <?= $tipo == 'revision' ? 'Revisión (2 intentos)' : 'Materias Pendientes (4 intentos)' ?>
                    </h5>
                </div>
                <div class="card-body">
                    <form id="formRecuperacion" method="post" action="ajax/guardar_recuperacion.php">
                        <input type="hidden" name="id_seccion" value="<?= $id_seccion ?>">
                        <input type="hidden" name="id_materia" value="<?= $id_materia ?>">
                        <input type="hidden" name="id_lapso" value="<?= $id_lapso ?>">
                        <input type="hidden" name="tipo" value="<?= $tipo ?>">

                        <div class="table-responsive">
                            <table class="table table-bordered table-hover text-center">
                                <thead class="bg-info text-white">
                                    <tr>
                                        <th>#</th>
                                        <th>Estudiante</th>
                                        <th>Nota Actual</th>
                                        <th>Momento</th>
                                        <th>Nueva Nota</th>
                                        <th>Observación (obligatoria si registra nota)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($estudiantes)): ?>
                                        <tr><td colspan="6">✅ No hay estudiantes pendientes.</td></tr>
                                    <?php else: $n=1; foreach ($estudiantes as $e): ?>
                                        <?php 
                                            $prox_intento = $e['intento_actual'] + 1;
                                            $default_observ = ($e['intento_actual'] >= 4 && $tipo === 'pendiente') ? 'Repite' : '';
                                        ?>
                                        <tr>
                                            <td><?= $n++ ?></td>
                                            <td class="text-left"><?= htmlspecialchars($e['nombres'].' '.$e['apellidos']) ?></td>
                                            <td>
                                                <?php 
                                                    if ($tipo == 'revision' && $e['intento_actual'] == 0) {
                                                        echo number_format(($e['suma_lapsos'] ?? 0) / 3, 2);
                                                    } else {
                                                        echo $e['nota_actual'] ?? '-';
                                                    }
                                                ?>
                                            </td>
                                            <td><?= $prox_intento ?>°</td>
                                            <td>
                                                <input type="number" step="0.1" min="0" max="20" 
                                                    name="nota[<?= $e['id_estudiante'] ?>]" 
                                                    class="form-control nota-recuperacion" placeholder="Ej: 14" 
                                                    data-estudiante="<?= htmlspecialchars($e['nombres'].' '.$e['apellidos']) ?>">
                                                <input type="hidden" name="intento[<?= $e['id_estudiante'] ?>]" value="<?= $prox_intento ?>">
                                            </td>

                                            <!-- Campo de observación VISIBLE y con nombre 'observaciones[]' -->
                                            <td>
                                                <input type="text" name="observaciones[<?= $e['id_estudiante'] ?>]" 
                                                       class="form-control observacion-recuperacion" 
                                                       placeholder="Observación (obligatoria si registra nota)" 
                                                       value="<?= htmlspecialchars($default_observ) ?>">
                                            </td>
                                        </tr>
                                    <?php endforeach; endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="text-center mt-3">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> Guardar
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card card-outline card-info mt-4 shadow-sm">
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-history"></i> Historial Agrupado por Estudiante</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($historial_agrupado)): ?>
                        <div class="alert alert-warning text-center">No hay registros aún.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table id="tablaHistorialAgrupado" class="table table-striped table-bordered text-center" style="width:100%">
                                <thead class="bg-secondary text-white">
                                    <tr>
                                        <th class="details-control text-center"></th> <th>Estudiante</th>
                                        <th>Materia</th>
                                        <th>Registros</th>
                                        <th>Última Nota</th>
                                        <th>Último Intento</th>
                                        <th>Última Fecha</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($historial_agrupado as $estudiante): 
                                        $ultimo_registro = $estudiante['registros'][0]; 
                                        $registros_count = count($estudiante['registros']);
                                        $estado_clase = ($ultimo_registro['calificacion'] >= 10) ? 'badge-success' : 'badge-danger';
                                        $estado_texto = ($ultimo_registro['calificacion'] >= 10) ? 'Aprobado' : 'Reprobado';
                                    ?>
                                        <tr>
                                            <td class="details-control"><i class="fas fa-plus-circle"></i><i class="fas fa-minus-circle"></i></td>
                                            <td class="text-left"><?= $estudiante['nombre_completo'] ?></td>
                                            <td><?= htmlspecialchars($ultimo_registro['nombre_materia']) ?></td>
                                            <td><?= $registros_count ?></td>
                                            <td>
                                                <?= $ultimo_registro['calificacion'] ?> 
                                                <span class="badge <?= $estado_clase ?>"><?= $estado_texto ?></span>
                                            </td>
                                            <td><?= $ultimo_registro['intento'] ?>° (<?= strtoupper($ultimo_registro['tipo']) ?>)</td>
                                            <td><?= date('d/m/Y', strtotime($ultimo_registro['fecha_registro'])) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <script id="historial-data" type="application/json">
                            <?= json_encode(array_values($historial_agrupado)); // array_values para DataTables ?>
                        </script>
                        
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="../../assets/js/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<script>
// Validación/activación de observación y envío con resumen
$('#formRecuperacion').on('submit', function(e){
    e.preventDefault();

    // Validar: si se ingresó una nota, la observación debe no estar vacía
    let errores = [];
    $('#formRecuperacion .nota-recuperacion').each(function(){
        const val = $(this).val();
        if (val !== '' && val !== null) {
            const id = $(this).attr('name').match(/\[(\d+)\]/)[1];
            const obs = $(`input[name="observaciones[${id}]"]`).val().trim();
            if (obs === '') {
                errores.push('La observación es obligatoria para el estudiante: ' + $(this).data('estudiante'));
            }
        }
    });

    if (errores.length > 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Faltan observaciones',
            html: errores.slice(0,5).join('<br>') + (errores.length>5?'<br>...':''),
            confirmButtonText: 'Corregir'
        });
        return;
    }

    Swal.fire({
        title: '¿Confirmar registro?',
        text: 'Se guardarán las notas ingresadas.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, guardar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if(result.isConfirmed){
            $.ajax({
                url: "ajax/guardar_recuperacion.php",
                method: "POST",
                data: $(this).serialize(),
                dataType: "json",
                success: function(resp){
                    if (resp.status === 'success') {
                        // Mensaje resumido con detalles si vienen
                        let summary = resp.summary || {};
                        let lines = [];
                        if (typeof summary.aprobados !== 'undefined') lines.push(`<strong>${summary.aprobados}</strong> aprobados`);
                        if (typeof summary.reprobados !== 'undefined') lines.push(`<strong>${summary.reprobados}</strong> reprobados`);
                        if (typeof summary.movido_a_pendiente_count !== 'undefined' && summary.movido_a_pendiente_count > 0) {
                            lines.push(`<strong>${summary.movido_a_pendiente_count}</strong> movidos a Materias Pendientes`);
                        }
                        if (typeof summary.aplazados_count !== 'undefined' && summary.aplazados_count > 0) {
                            lines.push(`<strong>${summary.aplazados_count}</strong> estudiantes aplazados (repite año: ${resp.gestion_activa || 'N/A'})`);
                        }

                        const htmlMsg = resp.message + '<br><br>' + (lines.length ? lines.join('<br>') : '');
                        Swal.fire({
                            icon: 'success',
                            title: 'Operación completada',
                            html: htmlMsg,
                            confirmButtonText: 'Aceptar'
                        }).then(() => {
                            if (resp.reload) location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            html: resp.message || 'Ocurrió un error al guardar.'
                        });
                    }
                },
                error: function(){
                    Swal.fire('Error','No se pudo conectar con el servidor.','error');
                }
            });
        }
    });
});

// Función para formatear la fila secundaria con los detalles del estudiante
function format(d) {
    let html = `
    <div style="margin: 0 10px 10px 10px; border: 1px solid #ddd; border-radius: 5px;">
        <h6 class="p-2 bg-light text-secondary mb-0">Detalle Completo de Recuperaciones (${d.nombre_completo})</h6>
        <table class="child-row-table table table-sm table-borderless table-info table-hover">
            <thead class="bg-info text-white">
                <tr>
                    <th>#</th>
                    <th>Fecha</th>
                    <th>Tipo</th>
                    <th>Momento</th>
                    <th>Nota</th>
                    <th>Estado</th>
                    <th>Observación</th>
                </tr>
            </thead>
            <tbody>`;
    
    d.registros.forEach((r, index) => {
        const estado = (r.calificacion >= 10) ? 'Aprobado' : 'Reprobado';
        const estadoClase = (r.calificacion >= 10) ? 'text-success' : 'text-danger';
        const observ = (r.observaciones !== undefined && r.observaciones !== null && r.observaciones !== '') ? r.observaciones : '-';
        
        html += `
            <tr>
                <td>${index + 1}</td>
                <td>${new Date(r.fecha_registro).toLocaleDateString('es-ES')}</td>
                <td><span class="badge badge-secondary">${r.tipo.toUpperCase()}</span></td>
                <td>${r.intento}°</td>
                <td><strong class="${estadoClase}">${r.calificacion}</strong></td>
                <td><span class="badge ${estado === 'Aprobado' ? 'badge-success' : 'badge-danger'}">${estado}</span></td>
                <td>${observ}</td>
            </tr>`;
    });

    html += `
            </tbody>
        </table>
    </div>`;
    return html;
}

$(document).ready(function() {
    const historialArray = JSON.parse($('#historial-data').text());
    const tablaHistorial = $('#tablaHistorialAgrupado').DataTable({
        data: historialArray,
        responsive: true,
        pageLength: 10,
        order: [[1, 'asc']],
        columns: [
            { className: 'details-control', orderable: false, data: null, defaultContent: '<i class="fas fa-plus-circle"></i><i class="fas fa-minus-circle"></i>', searchable: false },
            { data: 'nombre_completo' },
            { data: 'registros.0.nombre_materia' },
            { data: 'registros.length' },
            { data: 'registros.0.calificacion',
              render: function(data, type, row) {
                  const estado = (data >= 10) ? 'Aprobado' : 'Reprobado';
                  const estadoClase = (data >= 10) ? 'badge-success' : 'badge-danger';
                  return `${data} <span class="badge ${estadoClase}">${estado}</span>`;
              }
            },
            { data: 'registros.0.intento', render: function(data, type, row) { return `${data}° (${row.registros[0].tipo.toUpperCase()})`; } },
            { data: 'registros.0.fecha_registro', render: function(data, type, row) { return new Date(data).toLocaleDateString('es-ES'); } },
        ],
        language: {
            search: "🔍 Buscar:",
            lengthMenu: "Mostrar _MENU_ estudiantes",
            info: "Mostrando _START_ a _END_ de _TOTAL_ estudiantes",
            infoEmpty: "Sin datos disponibles",
            zeroRecords: "No se encontraron estudiantes",
            paginate: { first: "Primero", last: "Último", next: "Siguiente →", previous: "← Anterior" },
        }
    });

    $('#tablaHistorialAgrupado tbody').on('click', 'td.details-control', function () {
        const tr = $(this).closest('tr');
        const row = tablaHistorial.row(tr);
        if (row.child.isShown()) {
            row.child.hide();
            tr.removeClass('shown');
        } else {
            row.child(format(row.data())).show();
            tr.addClass('shown');
        }
    });

    // Activar/forzar observación cuando se ingresa nota
    $(document).on('input', '.nota-recuperacion', function(){
        const val = $(this).val();
        const id = $(this).attr('name').match(/\[(\d+)\]/)[1];
        const obsField = $(`input[name="observaciones[${id}]"]`);
        if (val !== '' && val !== null) {
            obsField.prop('required', true);
            obsField.addClass('border-warning');
        } else {
            obsField.prop('required', false);
            obsField.removeClass('border-warning');
        }
    });
});
</script>
<?php include('../../admin/layout/parte2.php'); ?>