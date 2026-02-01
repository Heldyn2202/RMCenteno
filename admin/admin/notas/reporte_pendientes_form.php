<?php
// reporte_pendientes_form.php
session_start();
require_once('../../app/config.php');
include('../../admin/layout/parte1.php');

// Función para redondear notas al entero más cercano (10.5 → 11, 10.4 → 10)
function redondearNota($nota) {
    if ($nota == '-' || !is_numeric($nota)) {
        return $nota;
    }
    // Redondear usando round() con PHP_ROUND_HALF_UP para redondear 0.5 hacia arriba
    return round(floatval($nota), 0, PHP_ROUND_HALF_UP);
}

// Obtener filtro si existe
$grado_filtro = isset($_GET['grado_filtro']) ? $_GET['grado_filtro'] : '';

// **CONSULTA MODIFICADA - BUSCAR EN RECUPERACIONES CON TIPO PENDIENTE**
$sql_estudiantes = "
    SELECT DISTINCT
        r.id_estudiante,
        r.id_materia,
        r.id_seccion,
        r.tipo,
        e.cedula,
        CONCAT(e.apellidos, ', ', e.nombres) as nombre_completo,
        e.genero,
        g.grado as nombre_grado,
        s.nombre_seccion,
        m.nombre_materia,
        mp.estado as estado_pendiente,
        mp.fecha_registro as fecha_pendiente,
        mp.id_pendiente
    FROM recuperaciones r
    INNER JOIN estudiantes e ON e.id_estudiante = r.id_estudiante
    INNER JOIN secciones s ON s.id_seccion = r.id_seccion
    INNER JOIN grados g ON g.id_grado = s.id_grado
    INNER JOIN materias m ON m.id_materia = r.id_materia
    LEFT JOIN materias_pendientes mp ON 
        mp.id_estudiante = r.id_estudiante 
        AND mp.id_materia = r.id_materia 
        AND mp.id_seccion = r.id_seccion
        AND mp.estado = 'pendiente'
    WHERE r.tipo = 'PENDIENTE'
    GROUP BY r.id_estudiante, r.id_materia, r.id_seccion
";

// Aplicar filtro si existe
if (!empty($grado_filtro) && $grado_filtro != '0') {
    $sql_estudiantes .= " AND g.grado = :grado";
}

$sql_estudiantes .= " ORDER BY e.apellidos, e.nombres, g.grado, s.nombre_seccion";

$stmt_estudiantes = $pdo->prepare($sql_estudiantes);
if (!empty($grado_filtro) && $grado_filtro != '0') {
    $stmt_estudiantes->bindParam(':grado', $grado_filtro, PDO::PARAM_STR);
}
$stmt_estudiantes->execute();
$estudiantes_pendientes = $stmt_estudiantes->fetchAll(PDO::FETCH_ASSOC);

// Obtener lista de grados disponibles para el filtro
$sql_grados = "
    SELECT DISTINCT g.grado as nombre_grado
    FROM recuperaciones r
    INNER JOIN secciones s ON s.id_seccion = r.id_seccion
    INNER JOIN grados g ON g.id_grado = s.id_grado
    WHERE r.tipo = 'PENDIENTE'
    ORDER BY g.id_grado ASC
";
$stmt_grados = $pdo->query($sql_grados);
$grados = $stmt_grados->fetchAll(PDO::FETCH_ASSOC);

// Array para almacenar todos los estudiantes con sus materias y notas
$estudiantes_completos = [];

foreach ($estudiantes_pendientes as $est) {
    // **BUSCAR TODAS LAS RECUPERACIONES PENDIENTES PARA ESTE ESTUDIANTE, MATERIA Y SECCIÓN**
    $sql_recuperaciones = "
        SELECT 
            r.intento,
            r.calificacion,
            r.fecha_registro,
            r.observaciones,
            r.tipo
        FROM recuperaciones r
        WHERE r.id_estudiante = :id_estudiante
        AND r.id_materia = :id_materia
        AND r.id_seccion = :id_seccion
        AND r.tipo = 'PENDIENTE'
        ORDER BY r.fecha_registro DESC
    ";
    
    $stmt_recup = $pdo->prepare($sql_recuperaciones);
    $stmt_recup->bindParam(':id_estudiante', $est['id_estudiante']);
    $stmt_recup->bindParam(':id_materia', $est['id_materia']);
    $stmt_recup->bindParam(':id_seccion', $est['id_seccion']);
    $stmt_recup->execute();
    $recuperaciones_data = $stmt_recup->fetchAll(PDO::FETCH_ASSOC);
    
    // Verificar si hay registros
    if (count($recuperaciones_data) == 0) {
        continue; // Saltar si no hay recuperaciones PENDIENTE
    }
    
    // Organizar por momento (M01, M02, M03, M04) - SOLO TIPO PENDIENTE
    $notas_momentos = [];
    $ultima_nota = '-';
    $ultimo_intento = 0;
    $ultima_fecha = '';
    $hay_notas = false;
    
    foreach ($recuperaciones_data as $recup_data) {
        $momento_num = $recup_data['intento'];
        if ($momento_num >= 1 && $momento_num <= 4) {
            $hay_notas = true;
            // Tomar la primera nota (la más reciente por fecha) para cada momento
            if (!isset($notas_momentos[$momento_num])) {
                $notas_momentos[$momento_num] = $recup_data;
            }
            
            // Guardar la nota más reciente para nota definitiva
            if ($ultima_nota == '-' || strtotime($recup_data['fecha_registro']) > strtotime($ultima_fecha)) {
                $ultima_nota = $recup_data['calificacion'];
                $ultimo_intento = $momento_num;
                $ultima_fecha = $recup_data['fecha_registro'];
            }
        }
    }
    
    // Si no hay notas en los momentos 1-4, continuar
    if (!$hay_notas) {
        continue;
    }
    
    // Completar con '-' los momentos faltantes con valores redondeados
    $momentos_completos = [];
    $momentos_originales = [];
    for ($i = 1; $i <= 4; $i++) {
        if (isset($notas_momentos[$i])) {
            $nota_original = $notas_momentos[$i]['calificacion'];
            $nota_redondeada = redondearNota($nota_original);
            
            $momentos_completos[$i] = [
                'calificacion' => $nota_redondeada,
                'calificacion_original' => $nota_original,
                'fecha_registro' => $notas_momentos[$i]['fecha_registro'],
                'observaciones' => $notas_momentos[$i]['observaciones']
            ];
            
            $momentos_originales[$i] = $nota_original;
        } else {
            $momentos_completos[$i] = [
                'calificacion' => '-',
                'calificacion_original' => '-',
                'fecha_registro' => '-',
                'observaciones' => 'Sin registro'
            ];
        }
    }
    
    // Obtener profesor asignado a esta materia y sección
    $sql_profesor = "
        SELECT CONCAT(p.nombres, ' ', p.apellidos) as nombre_profesor
        FROM asignaciones_profesor ap
        INNER JOIN profesores p ON p.id_profesor = ap.id_profesor
        WHERE ap.id_materia = :id_materia
        AND ap.id_seccion = :id_seccion
        AND ap.estado = '1'
        LIMIT 1
    ";
    
    $stmt_profesor = $pdo->prepare($sql_profesor);
    $stmt_profesor->bindParam(':id_materia', $est['id_materia']);
    $stmt_profesor->bindParam(':id_seccion', $est['id_seccion']);
    $stmt_profesor->execute();
    $profesor = $stmt_profesor->fetch(PDO::FETCH_ASSOC);
    
    // NOTA DEFINITIVA: ÚLTIMA NOTA REGISTRADA (más reciente por fecha)
    $nota_definitiva = '-';
    $nota_definitiva_original = '-';
    
    if ($ultima_nota != '-') {
        $nota_definitiva_original = $ultima_nota;
        $nota_definitiva = redondearNota($ultima_nota);
    }
    
    // Formatear nota definitiva mostrada (ya redondeada) con dos dígitos
    if (is_numeric($nota_definitiva)) {
        // Mostrar como entero con dos dígitos
        $nota_definitiva = str_pad((int)$nota_definitiva, 2, '0', STR_PAD_LEFT);
    }
    
    // **DETERMINAR ESTADO BASADO EN LA ÚLTIMA NOTA REDONDEADA**
    $estado_final = 'PENDIENTE';
    if ($nota_definitiva != '-' && is_numeric($nota_definitiva)) {
        if (intval($nota_definitiva) >= 10) {
            $estado_final = 'APROBADO';
        } else {
            $estado_final = 'REPROBADO';
        }
    }
    
    // Verificar si aún está en materias_pendientes
    $en_materias_pendientes = !empty($est['estado_pendiente']) && $est['estado_pendiente'] == 'pendiente';
    
    // Formatear género completo
    $genero_completo = 'No especificado';
    if (strtoupper($est['genero']) == 'MASCULINO' || $est['genero'] == 'M' || $est['genero'] == 'Hombre' || strpos(strtoupper($est['genero']), 'MASC') !== false) {
        $genero_completo = 'Masculino';
    } elseif (strtoupper($est['genero']) == 'FEMENINO' || $est['genero'] == 'F' || $est['genero'] == 'Mujer' || strpos(strtoupper($est['genero']), 'FEM') !== false) {
        $genero_completo = 'Femenino';
    }
    
    // **AGREGAR AL ARRAY - MOSTRAR TODOS LOS QUE TENGAN RECUPERACIONES PENDIENTES**
    $estudiantes_completos[] = [
        'id_pendiente' => $est['id_pendiente'] ?? 0,
        'id_estudiante' => $est['id_estudiante'],
        'cedula' => $est['cedula'],
        'nombre_completo' => $est['nombre_completo'],
        'genero' => $genero_completo,
        'nombre_grado' => $est['nombre_grado'],
        'nombre_seccion' => $est['nombre_seccion'],
        'nombre_materia' => $est['nombre_materia'],
        'profesor_nombre' => $profesor ? $profesor['nombre_profesor'] : 'Sin asignar',
        'fecha_registro' => $est['fecha_pendiente'] ?? date('Y-m-d H:i:s'),
        'estado_pendiente' => $en_materias_pendientes ? 'pendiente' : 'finalizado',
        'en_materias_pendientes' => $en_materias_pendientes,
        'momentos' => $momentos_completos,
        'momentos_originales' => $momentos_originales,
        'nota_definitiva' => $nota_definitiva,
        'nota_definitiva_original' => $nota_definitiva_original,
        'estado_final' => $estado_final,
        'ultima_nota' => $ultima_nota,
        'ultimo_intento' => $ultimo_intento,
        'ultima_fecha' => $ultima_fecha
    ];
}
?>

<div class="content-wrapper">
    <div class="content">
        <div class="container-fluid">
            <div class="content-header">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-dark">
                            <i class="fas fa-file-pdf"></i> Reporte de Materias Pendientes
                        </h1>
                    </div>
                    <div class="col-sm-6 text-right">
                        <button type="button" class="btn btn-success" id="exportarPDF">
                            <i class="fas fa-file-pdf"></i> Exportar a PDF
                        </button>
                        <button type="button" class="btn btn-info" id="exportarExcel">
                            <i class="fas fa-file-excel"></i> Exportar a Excel
                        </button>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <!-- Formulario de Filtros -->
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-filter"></i> Filtros de Búsqueda</h3>
                        </div>
                        <div class="card-body">
                            <form method="get" action="" id="filtroForm">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="grado_filtro"><i class="fas fa-graduation-cap"></i> Filtrar por Grado:</label>
                                            <select name="grado_filtro" id="grado_filtro" class="form-control">
                                                <option value="0">Todos los grados</option>
                                                <?php foreach ($grados as $grado): ?>
                                                    <option value="<?= $grado['nombre_grado'] ?>" 
                                                        <?= ($grado_filtro == $grado['nombre_grado']) ? 'selected' : '' ?>>
                                                        <?= $grado['nombre_grado'] ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="estudiante_filtro"><i class="fas fa-user"></i> Buscar Estudiante:</label>
                                            <input type="text" id="estudiante_filtro" class="form-control" placeholder="Nombre, apellido o cédula...">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>&nbsp;</label>
                                            <div class="d-flex gap-2">
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fas fa-search"></i> Aplicar Filtros
                                                </button>
                                                <a href="?" class="btn btn-secondary">
                                                    <i class="fas fa-redo"></i> Limpiar Filtros
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Lista de Estudiantes con Materias Pendientes -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-users"></i> Estudiantes con Materias Pendientes
                                <?php if (count($estudiantes_completos) > 0): ?>
                                    <span class="badge badge-primary ml-2"><?= count($estudiantes_completos) ?> registros</span>
                                <?php endif; ?>
                            </h3>
                        </div>
                        <div class="card-body">
                            <?php if (count($estudiantes_completos) > 0): ?>
                                <table id="tablaEstudiantes" class="table table-bordered table-hover table-striped" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Cédula</th>
                                            <th>Nombre Completo</th>
                                            <th>Género</th>
                                            <th>Grado/Sección</th>
                                            <th>Materia Pendiente</th>
                                            <th>Notas por Momentos</th>
                                            <th>Última Nota</th>
                                            <th>Profesor</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($estudiantes_completos as $index => $est): ?>
                                            <tr>
                                                <td><?= $index + 1 ?></td>
                                                <td>
                                                    <strong><?= htmlspecialchars($est['cedula']) ?></strong>
                                                    <!-- Eliminado los badges de estado en la cédula -->
                                                </td>
                                                <td>
                                                    <strong><?= htmlspecialchars($est['nombre_completo']) ?></strong>
                                                </td>
                                                <td>
                                                    <?php 
                                                    $genero = $est['genero'];
                                                    $badge_class = '';
                                                    
                                                    if ($genero == 'Masculino') {
                                                        $badge_class = 'badge-primary';
                                                    } elseif ($genero == 'Femenino') {
                                                        $badge_class = 'badge-pink';
                                                    } else {
                                                        $badge_class = 'badge-secondary';
                                                    }
                                                    ?>
                                                    <span class="badge <?= $badge_class ?>">
                                                        <?= htmlspecialchars($genero) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-info">
                                                        <?= htmlspecialchars($est['nombre_grado'] . ' - ' . $est['nombre_seccion']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-danger">
                                                        <?= htmlspecialchars($est['nombre_materia']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="small">
                                                        <?php foreach ($est['momentos'] as $momento_num => $momento): ?>
                                                            <?php 
                                                            $nota = $momento['calificacion'];
                                                            $nota_original = $momento['calificacion_original'];
                                                            $title_text = '';
                                                            
                                                            // Formatear nota con dos dígitos si es numérica
                                                            if (is_numeric($nota)) {
                                                                $nota = str_pad((int)$nota, 2, '0', STR_PAD_LEFT);
                                                            }
                                                            
                                                            if ($nota == '-') {
                                                                $title_text = 'Sin registro';
                                                            } elseif (is_numeric($nota)) {
                                                                $title_text = 'Nota: ' . $nota;
                                                                if (!empty($momento['observaciones']) && $momento['observaciones'] != 'Sin registro') {
                                                                    $title_text .= ' | ' . $momento['observaciones'];
                                                                }
                                                            }
                                                            
                                                            // Marcar si es la última nota registrada
                                                            $es_ultima = false;
                                                            if ($est['ultima_nota'] != '-' && $nota_original == $est['ultima_nota']) {
                                                                $es_ultima = true;
                                                            }
                                                            
                                                            // O si es el momento con la fecha más reciente
                                                            if ($momento['fecha_registro'] != '-' && $momento['fecha_registro'] == $est['ultima_fecha']) {
                                                                $es_ultima = true;
                                                            }
                                                            
                                                            $badge_class = $es_ultima ? 'badge-light border border-primary' : 'badge-light';
                                                            ?>
                                                            <span class="badge <?= $badge_class ?> mr-1 mb-1" title="<?= htmlspecialchars($title_text) ?>">
                                                                M<?= str_pad($momento_num, 2, '0', STR_PAD_LEFT) ?>: 
                                                                <?= $nota ?>
                                                                <?php if ($es_ultima): ?>
                                                                    <small class="text-primary">*</small>
                                                                <?php endif; ?>
                                                            </span>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <?php if (is_numeric($est['nota_definitiva'])): ?>
                                                        <strong class="<?= $est['estado_final'] == 'APROBADO' ? 'text-success' : ($est['estado_final'] == 'REPROBADO' ? 'text-danger' : 'text-warning') ?>">
                                                            <?= $est['nota_definitiva'] ?>
                                                        </strong>
                                                        <div class="small text-muted">
                                                            <?= !empty($est['ultima_fecha']) && $est['ultima_fecha'] != '-' ? date('d/m/y', strtotime($est['ultima_fecha'])) : '' ?>
                                                        </div>
                                                    <?php else: ?>
                                                        <span class="text-muted"><?= $est['nota_definitiva'] ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="small">
                                                        <strong><?= htmlspecialchars($est['profesor_nombre']) ?></strong>
                                                    </div>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-info btn-detalle" 
                                                            data-id="<?= $est['id_pendiente'] ?>"
                                                            data-estudiante="<?= htmlspecialchars($est['nombre_completo']) ?>"
                                                            data-cedula="<?= htmlspecialchars($est['cedula']) ?>"
                                                            data-materia="<?= htmlspecialchars($est['nombre_materia']) ?>"
                                                            data-grado="<?= htmlspecialchars($est['nombre_grado'] . ' - ' . $est['nombre_seccion']) ?>"
                                                            data-profesor="<?= htmlspecialchars($est['profesor_nombre']) ?>"
                                                            data-momentos='<?= json_encode($est['momentos']) ?>'
                                                            data-momentos-originales='<?= json_encode($est['momentos_originales']) ?>'
                                                            data-notadef="<?= $est['nota_definitiva'] ?>"
                                                            data-notadef-original="<?= $est['nota_definitiva_original'] ?>"
                                                            data-estado="<?= $est['estado_final'] ?>"
                                                            data-fecha="<?= date('d/m/Y', strtotime($est['fecha_registro'])) ?>"
                                                            data-ultimafecha="<?= !empty($est['ultima_fecha']) && $est['ultima_fecha'] != '-' ? date('d/m/Y', strtotime($est['ultima_fecha'])) : '-' ?>"
                                                            data-en-pendientes="<?= $est['en_materias_pendientes'] ? '1' : '0' ?>"
                                                            data-ultimo-intento="<?= $est['ultimo_intento'] ?>"
                                                            title="Ver detalles">
                                                        <i class="fas fa-eye"></i> Ver
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <div class="alert alert-success text-center">
                                    <i class="fas fa-check-circle fa-2x mb-3"></i>
                                    <h4>¡No hay materias pendientes!</h4>
                                    <p class="mb-0">No se encontraron estudiantes con materias pendientes con los filtros aplicados.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</div>

<!-- Modal para detalles -->
<div class="modal fade" id="modalDetalles" tabindex="-1" role="dialog" aria-labelledby="modalDetallesLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title" id="modalDetallesLabel">
                    <i class="fas fa-info-circle"></i> Detalles Completos - Materia Pendiente
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6><i class="fas fa-user"></i> Información del Estudiante</h6>
                        <table class="table table-sm">
                            <tr>
                                <th width="40%">Nombre:</th>
                                <td id="detalleNombre"></td>
                            </tr>
                            <tr>
                                <th>Cédula:</th>
                                <td id="detalleCedula"></td>
                            </tr>
                            <tr>
                                <th>Grado/Sección:</th>
                                <td id="detalleGrado"></td>
                            </tr>
                            <tr>
                                <th>Género:</th>
                                <td id="detalleGenero"></td>
                            </tr>
                            <tr>
                                <th>Estado en sistema:</th>
                                <td id="detalleEstadoSistema"></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6><i class="fas fa-book"></i> Información Académica</h6>
                        <table class="table table-sm">
                            <tr>
                                <th width="40%">Materia:</th>
                                <td id="detalleMateria"></td>
                            </tr>
                            <tr>
                                <th>Profesor:</th>
                                <td id="detalleProfesor"></td>
                            </tr>
                            <tr>
                                <th>Estado actual:</th>
                                <td id="detalleEstado"></td>
                            </tr>
                            <tr>
                                <th>Última evaluación:</th>
                                <td id="detalleUltimaFecha"></td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <div class="row mt-3">
                    <div class="col-md-12">
                        <h6><i class="fas fa-chart-line"></i> Calificaciones por Momentos</h6>
                        <table class="table table-sm table-bordered">
                            <thead class="thead-light">
                                <tr>
                                    <th>Momento</th>
                                    <th>Nota</th>
                                    <th>Fecha</th>
                                    <th>Observación</th>
                                </tr>
                            </thead>
                            <tbody id="detalleCalificaciones">
                                <!-- Aquí se insertarán las filas dinámicamente -->
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3" class="text-right bg-primary text-white">Última Nota Registrada:</th>
                                    <td id="detalleNotaDef" class="text-center bg-primary text-white font-weight-bold"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" onclick="imprimirDetalle()">
                    <i class="fas fa-print"></i> Imprimir
                </button>
            </div>
        </div>
    </div>
</div>

<!-- DataTables CSS y JS -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.0.1/css/buttons.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap4.min.css">

<script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.3/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.0.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.0.1/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.0.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.0.1/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap4.min.js"></script>

<script>
// Inicializar DataTable
$(document).ready(function() {
    var table = $('#tablaEstudiantes').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.11.3/i18n/es_es.json"
        },
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false,
        "pageLength": 25,
        "order": [[0, 'asc']],
        "buttons": [
            {
                extend: 'excel',
                text: '<i class="fas fa-file-excel"></i> Excel',
                className: 'btn btn-success btn-sm'
            },
            {
                extend: 'pdf',
                text: '<i class="fas fa-file-pdf"></i> PDF',
                className: 'btn btn-danger btn-sm'
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print"></i> Imprimir',
                className: 'btn btn-info btn-sm'
            }
        ],
        "dom": "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
               "<'row'<'col-sm-12'tr>>" +
               "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        "initComplete": function() {
            // Aplicar búsqueda personalizada
            $('#estudiante_filtro').on('keyup', function() {
                table.search(this.value).draw();
            });
        }
    });
    
    // Añadir botones a la tabla
    table.buttons().container().appendTo('#tablaEstudiantes_wrapper .col-md-6:eq(0)');
    
    // Evento para botones de detalle
    $(document).on('click', '.btn-detalle', function() {
        var estudiante = $(this).data('estudiante');
        var cedula = $(this).data('cedula');
        var materia = $(this).data('materia');
        var grado = $(this).data('grado');
        var profesor = $(this).data('profesor');
        var momentos = $(this).data('momentos');
        var momentosOriginales = $(this).data('momentos-originales');
        var notadef = $(this).data('notadef');
        var notadefOriginal = $(this).data('notadef-original');
        var estado = $(this).data('estado');
        var ultimafecha = $(this).data('ultimafecha');
        var enPendientes = $(this).data('en-pendientes');
        var ultimoIntento = $(this).data('ultimo-intento');
        
        // Extraer género del nombre del badge
        var generoText = $(this).closest('tr').find('td:nth-child(4) .badge').text().trim();
        
        // Estado en sistema
        var estadoSistemaText = enPendientes == '1' ? 'En materias pendientes' : 'Finalizado (aprobado)';
        
        // Llenar información básica
        $('#detalleNombre').text(estudiante);
        $('#detalleCedula').text(cedula);
        $('#detalleGrado').text(grado);
        $('#detalleMateria').text(materia);
        $('#detalleProfesor').text(profesor || 'Sin asignar');
        $('#detalleGenero').text(generoText);
        $('#detalleEstadoSistema').text(estadoSistemaText);
        $('#detalleUltimaFecha').text(ultimafecha !== '-' ? ultimafecha : 'Sin fecha');
        
        // Mostrar estado actual con color
        var estadoHtml = '';
        if (estado === 'APROBADO') {
            estadoHtml = '<span class="badge badge-success">APROBADO</span>';
        } else if (estado === 'REPROBADO') {
            estadoHtml = '<span class="badge badge-danger">REPROBADO</span>';
        } else if (estado === 'PENDIENTE') {
            estadoHtml = '<span class="badge badge-warning">PENDIENTE</span>';
        }
        $('#detalleEstado').html(estadoHtml);
        
        // Limpiar tabla de calificaciones
        $('#detalleCalificaciones').empty();
        
        // Agregar momentos M01-M04 (SOLO TIPO PENDIENTE)
        for (var i = 1; i <= 4; i++) {
            var momento = momentos[i];
            var momentoOriginal = momentosOriginales ? momentosOriginales[i] : null;
            
            if (momento) {
                var notaRedondeada = momento.calificacion;
                var fecha = momento.fecha_registro;
                var observacion = momento.observaciones;
                
                // Formatear nota con dos dígitos si es numérica
                if (notaRedondeada !== '-' && !isNaN(parseFloat(notaRedondeada))) {
                    notaRedondeada = String(parseInt(notaRedondeada)).padStart(2, '0');
                }
                
                // Formatear fecha
                if (fecha && fecha !== '-' && fecha !== '' && fecha !== null) {
                    try {
                        var fechaObj = new Date(fecha);
                        fecha = fechaObj.toLocaleDateString('es-ES');
                    } catch (e) {
                        fecha = '-';
                    }
                } else {
                    fecha = '-';
                }
                
                // Si no hay observación y hay nota numérica
                if ((!observacion || observacion === '' || observacion === 'Sin registro') && !isNaN(parseFloat(notaRedondeada))) {
                    observacion = parseFloat(notaRedondeada) >= 10 ? 'Aprobado' : 'Reprobado';
                }
                
                // Marcar si es el último intento
                var esUltima = (ultimoIntento == i);
                var filaClass = esUltima ? 'bg-light' : '';
                
                var fila = '<tr class="' + filaClass + '">';
                fila += '<td>M' + (i < 10 ? '0' + i : i) + (esUltima ? ' <span class="badge badge-primary">Última</span>' : '') + '</td>';
                
                if (notaRedondeada === '-') {
                    fila += '<td class="text-center">-</td>';
                } else {
                    fila += '<td class="text-center font-weight-bold">' + notaRedondeada + '</td>';
                }
                
                fila += '<td class="text-center">' + fecha + '</td>';
                fila += '<td class="text-center">' + (observacion || 'Sin registro') + '</td>';
                fila += '</tr>';
                
                $('#detalleCalificaciones').append(fila);
            }
        }
        
        // Llenar nota definitiva (ya viene con dos dígitos desde PHP)
        $('#detalleNotaDef').html(notadef);
        
        // Mostrar modal
        $('#modalDetalles').modal('show');
    });
    
    // Exportar a Excel
    $('#exportarExcel').on('click', function() {
        table.button('.buttons-excel').trigger();
    });
    
    // Exportar a PDF (usa el mismo PDF que ya tienes)
    $('#exportarPDF').on('click', function() {
        let grado = $('#grado_filtro').val();
        let url = 'reporte_pendientes_pdf.php?grado=' + encodeURIComponent(grado);
        window.open(url, '_blank');
    });
});

// Función para imprimir detalle
function imprimirDetalle() {
    var contenido = document.getElementById('modalDetalles').innerHTML;
    var ventana = window.open('', '_blank', 'width=800,height=600');
    ventana.document.write('<html><head><title>Detalles Materia Pendiente</title>');
    ventana.document.write('<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">');
    ventana.document.write('<style>body { padding: 20px; } .table { border-collapse: collapse; width: 100%; } th, td { border: 1px solid #ddd; padding: 8px; }</style>');
    ventana.document.write('</head><body>');
    ventana.document.write('<h2>Reporte de Materia Pendiente</h2>');
    ventana.document.write(contenido);
    ventana.document.write('</body></html>');
    ventana.document.close();
    ventana.print();
}
</script>

<style>
.badge-pink {
    background-color: #e83e8c;
    color: white;
}
.table td, .table th {
    vertical-align: middle;
}
.dataTables_wrapper .dataTables_filter input {
    border: 1px solid #ced4da;
    border-radius: 0.25rem;
    padding: 0.375rem 0.75rem;
}
.badge-light {
    background-color: #f8f9fa;
    color: #212529;
    border: 1px solid #dee2e6;
}
.mb-1 {
    margin-bottom: 0.25rem;
}
</style>

<?php include('../../admin/layout/parte2.php'); ?>