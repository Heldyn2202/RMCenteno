<?php
include('../../app/config.php');
include('../../admin/layout/parte1.php');

// ===============================
// CONSULTA PRINCIPAL: TRAEMOS TODOS LOS ESTADOS
// ===============================
$sql = "
SELECT 
    ap.id_asignacion,
    CONCAT(p.nombres, ' ', p.apellidos) AS profesor,
    m.nombre_materia,
    CONCAT(gr.grado, ' - ', s.nombre_seccion) AS seccion,
    CONCAT('Periodo ', YEAR(g.desde), ' - ', YEAR(g.hasta)) AS gestion,
    ap.estado,
    DATE_FORMAT(ap.fecha_creacion, '%d/%m/%Y %H:%i') AS fecha
FROM asignaciones_profesor ap
INNER JOIN profesores p ON ap.id_profesor = p.id_profesor
INNER JOIN materias m ON ap.id_materia = m.id_materia
INNER JOIN secciones s ON ap.id_seccion = s.id_seccion
INNER JOIN grados gr ON s.id_grado = gr.id_grado
INNER JOIN gestiones g ON ap.id_gestion = g.id_gestion
ORDER BY p.apellidos, gr.grado, s.nombre_seccion, m.nombre_materia 
-- ^^^^^^ ERROR CORREGIDO: Quitado el 'p.' extra.
";
$query = $pdo->prepare($sql);
$query->execute();
$asignaciones = $query->fetchAll(PDO::FETCH_ASSOC);

// Contamos el total de registros para el contador inicial
$total_registros = count($asignaciones);
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    /* Estilo base para los encabezados */
    #tablaAsignaciones thead th {
        background-color: #a5b4fc !important; 
        color: #1e293b !important;
        text-align: center;
        font-weight: 600;
        border-bottom: 2px solid #818cf8;
        transition: background-color 0.2s ease; 
    }
    
    /* Hover para encabezados */
    #tablaAsignaciones thead th:hover {
        background-color: #b6d4fe !important; 
        cursor: pointer;
    }

    tbody td {
        color: #1f2937;
        vertical-align: middle;
        font-weight: normal;
    }

    .btn-icon {
        border: none !important;
        background: transparent !important;
        color: #374151;
        transition: all 0.25s ease;
        padding: 5px 8px;
        border-radius: 6px;
    }

    .btn-icon:hover {
        background-color: rgba(59, 130, 246, 0.1);
        transform: scale(1.15);
    }

    .btn-status {
        border-radius: 20px;
        font-weight: 500;
        font-size: 13px;
        padding: 4px 14px;
    }

    table.dataTable tbody tr:hover {
        background-color: #f9fafb !important;
        transition: 0.2s ease-in-out;
    }

    .dataTables_wrapper .dataTables_filter input {
        border-radius: 8px;
        border: 1px solid #d1d5db;
        padding: 4px 8px;
    }
    
    .btn-filter.active {
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        opacity: 0.9;
    }
</style>

<div class="content-wrapper">

    <div class="content-header">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <h3 class="m-0" style="color:#1e293b; font-weight:600;">
                <i class="fas fa-list"></i> Listado de Asignaciones de Profesores
            </h3>
            <ol class="breadcrumb float-sm-right m-0" style="background:transparent;">
                <li class="breadcrumb-item"><a href="<?= APP_URL; ?>/admin" style="color:#2563eb;">Inicio</a></li>
                <li class="breadcrumb-item active" style="color:#475569;">Asignaciones</li>
            </ol>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <div class="card shadow-sm" style="border-top: 4px solid #3b82f6;">
                <div class="card-header" style="background-color:#f8fafc;">
                    <h3 class="card-title text-dark m-0" style="font-weight:600;">Asignaciones Registradas</h3>
                </div>

                <div class="card-body">

                    <div class="mb-3">
                        <label class="me-2 fw-semibold">Filtrar por Estatus:</label><br>
                        <button type="button" class="btn btn-success btn-filter active" data-filter="1" id="btn-activos">Activos</button>
                        <button type="button" class="btn btn-danger btn-filter" data-filter="0" id="btn-inactivos">Inactivos</button>
                    </div>

                    <h5 id="contador-registros" class="fw-bold mt-2">Total de Asignaciones Activas: <span class="badge bg-success" id="num-registros"></span></h5>
                    
                    
                    <table id="tablaAsignaciones" class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Profesor</th>
                                <th>Grado / Sección</th>
                                <th>Materia</th>
                                <th>Gestión</th>
                                <th>Fecha Asignación</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($asignaciones as $asig): ?>
                                <?php
                                    // Descripción para el pop-up de SweetAlert
                                    $desc = htmlspecialchars($asig['nombre_materia'] . ' - ' . $asig['seccion']);
                                ?>
                                <tr data-id="<?= $asig['id_asignacion'] ?>" data-estado="<?= $asig['estado'] ?>" data-desc-info="<?= $desc ?>"> 
                                    <td class="text-center"></td> 
                                    <td><?= htmlspecialchars($asig['profesor']) ?></td>
                                    <td><?= htmlspecialchars($asig['seccion']) ?></td>
                                    <td><?= htmlspecialchars($asig['nombre_materia']) ?></td>
                                    <td class="text-center"><?= htmlspecialchars($asig['gestion']) ?></td>
                                    <td class="text-center"><?= htmlspecialchars($asig['fecha']) ?></td>
                                    <td class="text-center">
                                        <?php if ($asig['estado'] == 1): ?>
                                            <button class="btn btn-success btn-sm btn-status" style="border-radius: 20px;">ACTIVO</button>
                                        <?php else: ?>
                                            <button class="btn btn-danger btn-sm btn-status" style="border-radius: 20px;">INACTIVO</button>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="editar_asignacion.php?id=<?= $asig['id_asignacion'] ?>" class="btn-icon" title="Editar">
                                            <i class="fas fa-edit text-warning"></i>
                                        </a>
                                        <?php if ($asig['estado'] == 1): ?>
                                            <button data-id="<?= $asig['id_asignacion'] ?>" class="btn-icon btn-inhabilitar" title="Inhabilitar">
                                                <i class="fas fa-ban text-danger"></i>
                                            </button>
                                        <?php else: ?>
                                            <button data-id="<?= $asig['id_asignacion'] ?>" class="btn-icon btn-reactivar" title="Reactivar">
                                                <i class="fas fa-check text-success"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
    </div>
</div>

<?php include('../../admin/layout/parte2.php'); ?>

<script>
var tabla; // Variable global para DataTables

$(document).ready(function() {
    
    // 1. Inicialización de DataTables
    tabla = $('#tablaAsignaciones').DataTable({
        responsive: true,
        columnDefs: [
            {
                targets: 0, 
                orderable: false, // Deshabilitamos el ordenamiento en la columna #
                className: 'text-center',
                render: function (data, type, row, meta) {
                    // Muestra el índice de la fila visible + 1
                    return meta.row + 1; 
                }
            }
        ],
        language: {
             "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json",
            "info": " ", 
            "infoEmpty": " ",
            "infoFiltered": " ",
            "zeroRecords": "No se encontraron registros coincidentes",
            "lengthMenu": "Mostrar _MENU_ registros",
            "search": "Buscar:",
            "paginate": {
                "first": "Primero",
                "last": "Último",
                "next": "Siguiente",
                "previous": "Anterior"
            },
        },
        // Forzamos el orden por Profesor (índice 1) y luego por Grado/Sección (índice 2)
        order: [[1, 'asc'], [2, 'asc']] 
    });

    // 2. FUNCIÓN PARA EL FILTRO DataTables (para Activos/Inactivos)
    $.fn.dataTable.ext.search.push(
        function(settings, data, dataIndex) {
            var filtro = $('.btn-filter.active').data('filter');
            // Leemos el atributo data-estado de la fila, que es el dato original
            var $tr = tabla.row(dataIndex).nodes().to$(); 
            var estado = $tr.attr('data-estado');
            
            if (estado === String(filtro)) {
                return true;
            }
            return false;
        }
    );
    
    // 3. FUNCIÓN PARA ACTUALIZAR EL CONTADOR
    function actualizarContador() {
        var filtro = $('.btn-filter.active').data('filter');
        var estado_texto;
        var clase_badge;
        
        var count = tabla.rows({ filter: 'applied' }).count(); 
        
        if (filtro === 1) {
            estado_texto = "Activas";
            clase_badge = "bg-success";
        } else {
            estado_texto = "Inactivas";
            clase_badge = "bg-danger";
        }
        
        $('#contador-registros').html(`Total de Asignaciones ${estado_texto}: <span class="badge ${clase_badge}" id="num-registros">${count}</span>`);
    }


    // 4. MANEJO DE LOS BOTONES DE FILTRO Y REFRESH
    $('.btn-filter').on('click', function() {
        $('.btn-filter').removeClass('active');
        $(this).addClass('active');
        tabla.draw();
    });

    // 5. Vincular el evento 'draw' de DataTables para actualizar el contador siempre
    tabla.on('draw', function() {
        actualizarContador();
    });

    // 6. Aplicar el filtro inicial 
    tabla.draw(); 
    
    // 7. Delegación para inhabilitar y reactivar 
    $(document).on('click', '.btn-inhabilitar', function(){
        var $btn = $(this);
        var id = $btn.data('id');
        // Lectura robusta de la descripción desde la fila (TR)
        var $fila = $btn.closest('tr'); 
        var desc = $fila.data('desc-info') || ('Asignación #' + id); 
        inhabilitar(id, desc);
    });

    $(document).on('click', '.btn-reactivar', function(){
        var $btn = $(this);
        var id = $btn.data('id');
        // Lectura robusta de la descripción desde la fila (TR)
        var $fila = $btn.closest('tr');
        var desc = $fila.data('desc-info') || ('Asignación #' + id);
        reactivar(id, desc);
    });
});

/**
 * Función que actualiza el estado visual de la fila y aplica el filtro sin recargar.
 */
function actualizarFilaEstado(id_asignacion, nuevoEstado) {
    
    var $fila = $('tr[data-id="'+id_asignacion+'"]');
    var desc = $fila.data('desc-info'); 
    
    $fila.attr('data-estado', nuevoEstado);

    var $celdaEstado = $fila.find('td').eq(6); 
    var $celdaAcciones = $fila.find('td').eq(7); 
    
    // a) Actualizar la celda de ESTADO
    if (nuevoEstado === 1) {
        $celdaEstado.html('<button class="btn btn-success btn-sm btn-status" style="border-radius: 20px;">ACTIVO</button>');
    } else {
        $celdaEstado.html('<button class="btn btn-danger btn-sm btn-status" style="border-radius: 20px;">INACTIVO</button>');
    }
    
    // b) Reconstruir la celda de ACCIONES
    var htmlAcciones = `
        <a href="editar_asignacion.php?id=${id_asignacion}" class="btn-icon" title="Editar">
            <i class="fas fa-edit text-warning"></i>
        </a>`;
        
    if (nuevoEstado === 1) {
        htmlAcciones += `
        <button data-id="${id_asignacion}" class="btn-icon btn-inhabilitar" title="Inhabilitar">
            <i class="fas fa-ban text-danger"></i>
        </button>`;
    } else {
        htmlAcciones += `
        <button data-id="${id_asignacion}" class="btn-icon btn-reactivar" title="Reactivar">
            <i class="fas fa-check text-success"></i>
        </button>`;
    }
    
    $celdaAcciones.html(htmlAcciones);
    
    // 4. SIMULAR CLIC Y REDIBUJAR para aplicar el filtro
    if (nuevoEstado === 1) {
        $('#btn-activos').click();
    } else { 
        $('#btn-inactivos').click();
    }
}

// Inhabilitar via AJAX (POST)
function inhabilitar(id, desc) {
    Swal.fire({
        icon:'warning',
        title:'¿Inhabilitar asignación?',
        html: `Vas a inhabilitar:<br><strong>${desc}</strong>`, 
        showCancelButton:true,
        confirmButtonText:'Sí, inhabilitar',
        confirmButtonColor:'#d33',
        cancelButtonText:'Cancelar'
    }).then((r)=>{
        if(!r.isConfirmed) return;
        $.ajax({
            url: 'inhabilitar_asignacion.php',
            method: 'POST',
            data: { id_asignacion: id },
            dataType: 'json'
        }).done(function(resp){
            if(resp && resp.status === 'ok') {
                Swal.fire({ icon:'success', title:'Asignación inhabilitada', html: resp.msg }).then(()=>{
                    actualizarFilaEstado(id, 0); 
                });
            } else {
                Swal.fire({ icon:'error', title:'Error', text: (resp && resp.msg) ? resp.msg : 'No se pudo inhabilitar.' });
            }
        }).fail(function(xhr){
            var txt = 'Error del servidor';
            try { txt = JSON.parse(xhr.responseText).msg || txt; } catch(e){}
            Swal.fire({ icon:'error', title:'Error', text: txt });
        });
    });
}

// Reactivar via AJAX (POST)
function reactivar(id, desc) {
    Swal.fire({
        icon:'question',
        title:'¿Reactivar asignación?',
        html: `Vas a reactivar:<br><strong>${desc}</strong>`, 
        showCancelButton:true,
        confirmButtonText:'Sí, reactivar',
        confirmButtonColor:'#3085d6',
        cancelButtonText:'Cancelar'
    }).then((r)=>{
        if(!r.isConfirmed) return;
        $.ajax({
            url: 'reactivar_asignacion.php',
            method: 'POST',
            data: { id_asignacion: id },
            dataType: 'json'
        }).done(function(resp){
            if(resp && resp.status === 'ok') {
                Swal.fire({ icon:'success', title:'Asignación reactivada', html: resp.msg }).then(()=>{
                    actualizarFilaEstado(id, 1); 
                });
            } else {
                Swal.fire({ icon:'error', title:'Error', text: (resp && resp.msg) ? resp.msg : 'No se pudo reactivar.' });
            }
        }).fail(function(xhr){
            var txt = 'Error del servidor';
            try { txt = JSON.parse(xhr.responseText).msg || txt; } catch(e){}
            Swal.fire({ icon:'error', title:'Error', text: txt });
        });
    });
}
</script>