<?php
session_start();
require_once('../../app/config.php');
include('../../admin/layout/parte1.php');
include('../../layout/mensajes.php');

// ==== Obtener historial de estudiantes aprobados ====
$sql_aprobados = "
    SELECT 
        r.id_estudiante,
        CONCAT(e.nombres, ' ', e.apellidos) as estudiante_completo,
        e.cedula,
        m.nombre_materia,
        s.nombre_seccion,
        g.grado,
        g.nivel,
        r.calificacion,
        r.intento,
        r.fecha_registro,
        r.observaciones
    FROM recuperaciones r
    INNER JOIN estudiantes e ON r.id_estudiante = e.id_estudiante
    INNER JOIN materias m ON r.id_materia = m.id_materia
    INNER JOIN secciones s ON r.id_seccion = s.id_seccion
    INNER JOIN grados g ON s.id_grado = g.id_grado
    WHERE r.tipo = 'pendiente'
    AND r.calificacion >= 10
    ORDER BY r.fecha_registro DESC
";

$stmt_aprobados = $pdo->query($sql_aprobados);
$aprobados = $stmt_aprobados->fetchAll(PDO::FETCH_ASSOC);

// Función para redondear notas según las reglas especificadas
function redondearNota($nota) {
    if (floor($nota) == $nota) {
        return $nota;
    }
    
    $decimal = $nota - floor($nota);
    
    if ($decimal >= 0.5) {
        return ceil($nota);
    } else {
        return floor($nota);
    }
}
?>

<style>
:root {
    --azul-suave: #e8f4fe;
    --azul-header: #4a9df5;
}

.card-header-estudiantes {
    background: linear-gradient(135deg, var(--azul-header), #6ab0ff) !important;
    color: white !important;
    font-weight: 600;
    padding: 15px 20px;
}

.table-hover tbody tr:hover {
    background-color: var(--azul-suave) !important;
    transition: all 0.3s ease;
}

.badge-aprobado {
    background-color: #e8f5e9;
    color: #2e7d32;
    border: 1px solid #c8e6c9;
}

.badge-intento {
    background-color: #e3f2fd;
    color: #1565c0;
    border: 1px solid #bbdefb;
}

/* Estilos para DataTables */
.dataTables_wrapper .dataTables_length,
.dataTables_wrapper .dataTables_filter,
.dataTables_wrapper .dataTables_info,
.dataTables_wrapper .dataTables_paginate {
    padding: 10px 0;
}

.dataTables_filter input {
    border: 1px solid #dee2e6 !important;
    border-radius: 4px !important;
    padding: 6px 12px !important;
    margin-left: 10px !important;
    width: 250px !important;
}

.dataTables_length select {
    border: 1px solid #dee2e6 !important;
    border-radius: 4px !important;
    padding: 6px !important;
}

/* Estilo para botones de exportación */
.dt-buttons .btn {
    margin-right: 5px;
    border-radius: 4px;
}

/* Ajustar espaciado del buscador */
.dataTables_filter {
    float: right !important;
    margin-bottom: 10px;
}
</style>

<!-- Incluir CSS de DataTables -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap4.min.css">

<div class="content-wrapper">
    <div class="content">
        <div class="container-fluid">
            <!-- ENCABEZADO -->
            <div class="content-header">
                <div class="row mb-2">
                    <div class="col-sm-8">
                        <div class="d-flex align-items-center">
                            <div class="mr-3">
                                <i class="fas fa-history fa-2x text-primary"></i>
                            </div>
                            <div>
                                <h1 class="m-0 text-primary" style="font-weight: 600;">
                                    <i class="fas fa-trophy"></i> Historial de Estudiantes Aprobados
                                </h1>
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb" style="background-color: transparent; padding-left: 0;">
                                        <li class="breadcrumb-item">
                                            <a href="index.php" class="text-primary">
                                                <i class="fas fa-home"></i> Inicio
                                            </a>
                                        </li>
                                        <li class="breadcrumb-item active text-dark">
                                            Historial de Aprobados
                                        </li>
                                    </ol>
                                </nav>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4 text-right">
                        <a href="seleccion_materia_pendiente.php" class="btn btn-primary">
                            <i class="fas fa-arrow-left"></i> Volver a Pendientes
                        </a>
                    </div>
                </div>
            </div>

            <!-- CONTENIDO PRINCIPAL -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header card-header-estudiantes">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-0">
                                        <i class="fas fa-user-check"></i> Estudiantes que han Aprobado Materias Pendientes
                                        <span class="badge badge-light ml-2"><?= count($aprobados) ?></span>
                                    </h5>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php if (empty($aprobados)): ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-trophy fa-4x text-warning mb-3"></i>
                                    <h4 class="text-warning">No hay registros de aprobados</h4>
                                    <p class="text-muted">Aún no hay estudiantes que hayan aprobado materias pendientes.</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table id="tablaAprobados" class="table table-hover" style="width:100%">
                                        <thead class="bg-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Estudiante</th>
                                                <th>Cédula</th>
                                                <th>Materia</th>
                                                <th>Grado/Sección</th>
                                                <th>Nota</th>
                                                <th>Momento</th>
                                                <th>Fecha</th>
                                                <th>Observaciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($aprobados as $index => $aprobado): 
                                                $nota_redondeada = redondearNota($aprobado['calificacion']);
                                                $fecha_formateada = date('d/m/Y H:i', strtotime($aprobado['fecha_registro']));
                                            ?>
                                                <tr>
                                                    <td><?= $index + 1 ?></td>
                                                    <td>
                                                        <strong><?= htmlspecialchars($aprobado['estudiante_completo']) ?></strong>
                                                    </td>
                                                    <td><?= htmlspecialchars($aprobado['cedula']) ?></td>
                                                    <td><?= htmlspecialchars($aprobado['nombre_materia']) ?></td>
                                                    <td>
                                                        <?= htmlspecialchars($aprobado['grado'] . ' - ' . $aprobado['nivel']) ?><br>
                                                        <small class="text-muted">Sección: <?= htmlspecialchars($aprobado['nombre_seccion']) ?></small>
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-aprobado" style="font-size: 1.1em;">
                                                            <?= $nota_redondeada ?>/20
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-intento">
                                                            <?= $aprobado['intento'] ?>°
                                                        </span>
                                                    </td>
                                                    <td data-order="<?= strtotime($aprobado['fecha_registro']) ?>">
                                                        <?= $fecha_formateada ?>
                                                    </td>
                                                    <td>
                                                        <?php if (!empty($aprobado['observaciones'])): ?>
                                                            <small class="text-muted"><?= htmlspecialchars($aprobado['observaciones']) ?></small>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer text-muted">
                            <small>
                                <i class="fas fa-database"></i> Total de registros: <?= count($aprobados) ?>
                                <?php if (count($aprobados) > 0): ?>
                                    | <i class="fas fa-sort-amount-up"></i> Ordenado por fecha más reciente
                                <?php endif; ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts de DataTables -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>

<script>
$(document).ready(function() {
    $('#tablaAprobados').DataTable({
        "language": {
            "decimal": "",
            "emptyTable": "No hay datos disponibles",
            "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
            "infoEmpty": "Mostrando 0 a 0 de 0 registros",
            "infoFiltered": "(filtrado de _MAX_ registros totales)",
            "infoPostFix": "",
            "thousands": ",",
            "lengthMenu": "Mostrar _MENU_ registros por página",
            "loadingRecords": "Cargando...",
            "processing": "Procesando...",
            "search": "Buscar en todos los campos:",
            "zeroRecords": "No se encontraron registros coincidentes",
            "paginate": {
                "first": "Primera",
                "last": "Última",
                "next": "Siguiente",
                "previous": "Anterior"
            },
            "aria": {
                "sortAscending": ": activar para ordenar ascendente",
                "sortDescending": ": activar para ordenar descendente"
            }
        },
        "pageLength": 25,
        "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Todos"]],
        "order": [[0, 'asc']], // Ordenar por la columna # (índice)
        "responsive": true,
        "dom": 'Bfrtip',
        "buttons": [
            {
                extend: 'excel',
                text: '<i class="fas fa-file-excel"></i> Exportar a Excel',
                className: 'btn btn-success btn-sm',
                exportOptions: {
                    columns: ':visible'
                }
            },
            {
                extend: 'pdf',
                text: '<i class="fas fa-file-pdf"></i> Exportar a PDF',
                className: 'btn btn-danger btn-sm',
                exportOptions: {
                    columns: ':visible'
                }
            }
        ],
        "initComplete": function() {
            // Personalizar diseño
            $('.dataTables_filter input').addClass('form-control-sm');
            $('.dataTables_length select').addClass('form-control-sm');
            
            // Mover los botones al lado del selector de registros
            $('.dt-buttons').addClass('float-left mr-3');
        }
    });
});
</script>

<?php include('../../admin/layout/parte2.php'); ?>