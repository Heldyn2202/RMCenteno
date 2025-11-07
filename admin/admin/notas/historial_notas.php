<?php
require_once('verificar_docente.php');
$datos_docente = verificarDocente(); 
include('../../admin/layout/parte1.php');

// =======================================================
// 1. ESTILO PERSONALIZADO PARA ENCABEZADOS DE TABLA
// =======================================================
?>
<style>
    /* Estilo para reemplazar thead-dark */
    .thead-claro-personalizado {
        background-color: #cfe2ff !important; /* Azul claro para el fondo (Normal) */
        color: #333333; /* Color de texto oscuro para buen contraste */
        border-bottom: 2px solid #b6d4fe; 
    }

    /* ✅ CORRECCIÓN HOVER: Aplicamos el efecto de azul más oscuro al pasar el mouse */
    #tablaHistorialNotas thead th:hover {
        background-color: #b6d4fe !important; /* Azul más oscuro para el hover */
        cursor: pointer; /* Indica que la columna es clickeable/ordenable */
    }
    
    /* Asegura que el color de fondo estático tenga prioridad sobre otras reglas (si las hay) */
    #tablaHistorialNotas thead th {
        background-color: #cfe2ff !important;
    }
</style>

<?php
// =======================================================
// 2. LÓGICA PHP OPTIMIZADA (SIN REFERENCIA A id_gestion)
// =======================================================

// Consulta principal: ELIMINAMOS la referencia a hn.id_gestion
$sql = "SELECT 
            hn.*, 
            e.nombres, 
            e.apellidos, 
            m.nombre_materia, 
            l.nombre_lapso
            -- nombre_gestion ya no se puede obtener directamente de hn
        FROM historial_notas hn
        INNER JOIN estudiantes e ON hn.id_estudiante = e.id_estudiante
        INNER JOIN materias m ON hn.id_materia = m.id_materia
        INNER JOIN lapsos l ON hn.id_lapso = l.id_lapso
        -- INNER JOIN gestiones g ON hn.id_gestion = g.id_gestion -- ELIMINADO: Columna no encontrada
        WHERE hn.estado = 1
        ORDER BY hn.fecha_cambio DESC";
$query = $pdo->prepare($sql);
$query->execute(); 
$historial = $query->fetchAll(PDO::FETCH_ASSOC);

// Extracción de gestiones disponibles para el filtro Select (Mantenida, pero el filtro no funcionará con los datos de la tabla)
try {
    $gestiones_disponibles_query = $pdo->query("SELECT DISTINCT CONCAT(YEAR(desde), ' - ', YEAR(hasta)) AS gestion_nombre FROM gestiones ORDER BY desde DESC");
    $gestiones_disponibles = $gestiones_disponibles_query->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $gestiones_disponibles = []; 
}

$tipo_cambio_colores = [
    'CREACION' => 'success',
    'ACTUALIZACION' => 'warning',
    'MODIFICACION' => 'warning' 
];
$nota_anterior_color = 'secondary';
$nota_nueva_color = 'primary'; 
?>

<div class="content-wrapper">
    <div class="content">
        <div class="container">
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0 text-dark" style="font-weight: 700;">
                                <i class="fas fa-history text-primary"></i> Historial de Cambios en Notas
                            </h1>
                        </div>
                        <div class="col-sm-6 text-right">
                            <span class="badge badge-primary p-2">
                                <i class="fas fa-user-shield"></i> 
                                USUARIO: <?= htmlspecialchars(trim(($datos_docente['nombre_profesor'] ?? '') . ' ' . ($datos_docente['apellido_profesor'] ?? ''))) ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Registro de Auditoría</h3>
                        </div>
                        <div class="card-body">

                            <div class="row mb-4 p-3 border rounded" style="background-color: #f8f9fa;">
                                <div class="col-md-6 mb-2 mb-md-0">
                                    <label class="fw-semibold">Filtro por Tipo de Cambio:</label><br>
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-filter-tipo active" data-filter="todas">
                                        Todas <span class="badge badge-light ml-1"></span>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-success btn-filter-tipo" data-filter="CREACION">
                                        Creación <span class="badge badge-light ml-1"></span>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-warning btn-filter-tipo" data-filter="ACTUALIZACION">
                                        Modificación <span class="badge badge-light ml-1"></span>
                                    </button>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="filtro-gestion" class="fw-semibold">Filtro por Gestión:</label>
                                    <select id="filtro-gestion" class="form-control form-control-sm">
                                        <option value="">-- Todas las Gestiones --</option>
                                        <?php foreach ($gestiones_disponibles as $gestion): ?>
                                            <option value="<?= htmlspecialchars($gestion) ?>"><?= htmlspecialchars($gestion) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <table id="tablaHistorialNotas" class="table table-bordered table-striped table-hover table-sm w-100">
                                <thead class="thead-claro-personalizado"> 
                                    <tr>
                                        <th class="text-center">#</th>
                                        <th style="min-width: 130px;">Fecha/Hora</th>
                                        <th>Estudiante</th>
                                        <th>Materia</th>
                                        <th class="text-center">Lapso</th>
                                        
                                        <th class="text-center">N. Ant.</th>
                                        <th class="text-center">N. Nva.</th>
                                        <th>Obs. Ant.</th>
                                        <th>Obs. Nva.</th>
                                        <th class="text-center">Tipo</th>
                                        <th>Usuario</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $contador = 1; ?>
                                    <?php foreach ($historial as $registro): ?>
                                        <?php
                                            $tipo_cambio = strtoupper($registro['tipo_cambio']);
                                            $badge_color = $tipo_cambio_colores[$tipo_cambio] ?? 'info';
                                            $obs_anterior = trim($registro['observaciones_anterior']);
                                            $obs_nueva = trim($registro['observaciones_nueva']);
                                        ?>
                                        <tr>
                                            <td class="text-center"><?= $contador++ ?></td>
                                            <td><?= htmlspecialchars($registro['fecha_cambio']) ?></td>
                                            <td><?= htmlspecialchars($registro['apellidos'] . ', ' . $registro['nombres']) ?></td>
                                            <td><?= htmlspecialchars($registro['nombre_materia']) ?></td>
                                            <td class="text-center"><?= htmlspecialchars($registro['nombre_lapso']) ?></td>
                                            
                                            <td class="text-center">
                                                <?php if ($registro['calificacion_anterior'] !== null): ?>
                                                    <span class="badge badge-<?= $nota_anterior_color ?>"><?= number_format($registro['calificacion_anterior'], 2) ?></span>
                                                <?php else: ?>
                                                    <em class="text-muted">N/A</em>
                                                <?php endif; ?>
                                            </td>

                                            <td class="text-center">
                                                <span class="badge badge-<?= $nota_nueva_color ?>"><?= number_format($registro['calificacion_nueva'], 2) ?></span>
                                            </td>

                                            <td class="text-muted small">
                                                <?= (!empty($obs_anterior) && $obs_anterior !== 'Sin observación') ? nl2br(htmlspecialchars($obs_anterior)) : '<i class="text-info">Ninguna</i>' ?>
                                            </td>
                                            <td class="small">
                                                <?= (!empty($obs_nueva) && $obs_nueva !== 'Sin observación') ? nl2br(htmlspecialchars($obs_nueva)) : '<i class="text-info">Ninguna</i>' ?>
                                            </td>

                                            <td class="text-center">
                                                <span class="badge badge-<?= $badge_color ?> text-uppercase">
                                                    <?= htmlspecialchars($tipo_cambio) ?>
                                                </span>
                                            </td>

                                            <td class="small">
                                                <?= htmlspecialchars($registro['usuario_cambio']) ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>

                            <?php if (empty($historial)): ?>
                                <div class="alert alert-warning text-center mt-3">
                                    No hay registros de cambios en el historial.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include('../../admin/layout/parte2.php');
include('../../layout/mensajes.php');
?>

<script>
$(document).ready(function() {
    var tablaHistorial;
    
    // 1. Inicialización de DataTables
    tablaHistorial = $('#tablaHistorialNotas').DataTable({
        responsive: true,
        autoWidth: false,
        order: [[1, 'desc']], 
        language: {
            "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json",
            // Se añaden frases clave para máxima compatibilidad
            "zeroRecords": "No se encontraron registros coincidentes",
            "infoEmpty": "Mostrando 0 a 0 de 0 entradas",
            "infoFiltered": "(filtrado de un total de _MAX_ entradas)",
            "lengthMenu": "Mostrar _MENU_ entradas",
            "search": "Buscar:",
            "info": "Mostrando _START_ a _END_ de _TOTAL_ entradas"
        },
    });
    
    // 2. FILTRO GLOBAL PERSONALIZADO (SIN FILTRO DE GESTIÓN)
    $.fn.dataTable.ext.search.push(
        function(settings, data, dataIndex) {
            
            // Filtro 1: Tipo Cambio (Columna 9, ya que eliminamos la 5 - Gestión)
            var filtro_tipo = $('.btn-filter-tipo.active').data('filter');
            // La columna de Tipo (que era 10) ahora es 9
            var tipo_cambio_columna = data[9].toUpperCase().replace(/<[^>]*>/g, '').trim(); 

            if (filtro_tipo !== 'todas' && tipo_cambio_columna !== filtro_tipo) {
                return false;
            }

            // Filtro 2: Gestión (ELIMINADO, la lógica de filtro de gestión también se quita)
            
            return true; 
        }
    );

    // 3. EVENTO: Botones de Tipo Cambio (Filtro instantáneo)
    $('.btn-filter-tipo').on('click', function() {
        $('.btn-filter-tipo').removeClass('active').addClass('btn-outline-primary');
        $(this).removeClass('btn-outline-primary').addClass('active');
        tablaHistorial.draw(); 
    });

    // 4. EVENTO: Select de Gestión (ELIMINADO: No hay gestión que filtrar en el código actual)
    /*
    $('#filtro-gestion').on('change', function() {
        tablaHistorial.draw(); 
    });
    */
    
    // 5. EVENTO: Actualizar Contadores al redibujar
    tablaHistorial.on('draw.dt', function() {
        actualizarContadores();
    });

    // 6. FUNCIÓN CONTADOR 
    function actualizarContadores() {
        var data_filtrada = tablaHistorial.rows({ filter: 'applied' }).data();
        var total = data_filtrada.length;
        var creacion_count = 0;
        var actualizacion_count = 0;

        for (var i = 0; i < total; i++) {
            // Columna de Tipo (Ahora es la 9)
            var tipo = data_filtrada[i][9].toUpperCase().replace(/<[^>]*>/g, '').trim(); 
            
            if (tipo === 'CREACION') {
                creacion_count++;
            } else if (tipo === 'ACTUALIZACION') {
                actualizacion_count++;
            }
        }
        
        $('.btn-filter-tipo[data-filter="todas"] .badge').text(total);
        $('.btn-filter-tipo[data-filter="CREACION"] .badge').text(creacion_count);
        $('.btn-filter-tipo[data-filter="ACTUALIZACION"] .badge').text(actualizacion_count);
    }
    
    tablaHistorial.draw();
});
</script>