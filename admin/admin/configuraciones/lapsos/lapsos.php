<?php
// PHP: CONEXIÓN Y CARGA DE DATOS

include ('../../../app/config.php');
include ('../../../admin/layout/parte1.php');

// Obtener gestión activa
$sql_gestion = "SELECT * FROM gestiones WHERE estado = 1";
$query_gestion = $pdo->prepare($sql_gestion);
$query_gestion->execute();
$gestion_activa = $query_gestion->fetch(PDO::FETCH_ASSOC);

if (!$gestion_activa) {
    $_SESSION['mensaje'] = "No hay un periodo académico activo configurado";
    header('Location: ' . APP_URL . '/admin');
    exit();
}

// Obtener lapsos para la gestión activa
$sql_lapsos = "SELECT * FROM lapsos WHERE id_gestion = :id_gestion ORDER BY fecha_inicio";
$query_lapsos = $pdo->prepare($sql_lapsos);
$query_lapsos->bindParam(':id_gestion', $gestion_activa['id_gestion']);
$query_lapsos->execute();
$lapsos = $query_lapsos->fetchAll(PDO::FETCH_ASSOC);

// Extraer las fechas de gestión para usarlas en JavaScript
$gestion_inicio_js = $gestion_activa['desde'];
$gestion_fin_js = $gestion_activa['hasta'];
?>

<div class="content-wrapper">
    <div class="content">
        <div class="container-fluid">

            <div class="content-header mb-4">
                <div class="d-flex justify-content-between align-items-center">
                    <h1 class="m-0"><i class="fas fa-calendar-alt"></i> Lapsos Académicos</h1>
                    <button type="button" class="btn btn-success shadow" data-toggle="modal" data-target="#modal-create">
                        <i class="fas fa-plus-circle"></i> Nuevo Lapso
                    </button>
                </div>
                <p class="text-muted mt-2">Periodo activo: 
                    <strong><?= htmlspecialchars($gestion_activa['desde']) ?> - <?= htmlspecialchars($gestion_activa['hasta']) ?></strong>
                </p>
            </div>

            <div class="card shadow-lg border-0 rounded-lg">
                <div class="card-body">
                    <table id="tabla_lapsos" class="table table-striped table-bordered text-center">
                        <thead class="text-white" style="background: linear-gradient(90deg, #007bff, #0056b3);">
                            <tr>
                                <th>#</th>
                                <th>Nombre</th>
                                <th>Fecha Inicio</th>
                                <th>Fecha Fin</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($lapsos as $index => $lapso): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><strong><?= htmlspecialchars($lapso['nombre_lapso']) ?></strong></td>
                                    <td><?= date('d/m/Y', strtotime($lapso['fecha_inicio'])) ?></td>
                                    <td><?= date('d/m/Y', strtotime($lapso['fecha_fin'])) ?></td>
                                    <td>
                                        <button class="btn btn-warning btn-sm edit-btn"
                                                        data-id="<?= $lapso['id_lapso'] ?>"
                                                        data-nombre="<?= htmlspecialchars($lapso['nombre_lapso']) ?>"
                                                        data-inicio="<?= $lapso['fecha_inicio'] ?>"
                                                        data-fin="<?= $lapso['fecha_fin'] ?>">
                                            <i class="fas fa-edit"></i> Editar
                                        </button>
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

<div class="modal fade" id="modal-create">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="form-create" action="guardar_lapso.php" method="post">
                <div class="modal-header text-white" style="background: linear-gradient(90deg, #28a745, #1e7e34);">
                    <h5 class="modal-title"><i class="fas fa-plus-circle"></i> Nuevo Lapso Académico</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_gestion" value="<?= $gestion_activa['id_gestion'] ?>">
                    <div class="form-group">
                        <label>Nombre del Lapso</label>
                        <input type="text" name="nombre_lapso" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Fecha de Inicio</label>
                        <input type="date" name="fecha_inicio" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Fecha de Fin</label>
                        <input type="date" name="fecha_fin" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-edit">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="form-edit" action="actualizar_lapso.php" method="post">
                <div class="modal-header text-white" style="background: linear-gradient(90deg, #ffc107, #d39e00);">
                    <h5 class="modal-title"><i class="fas fa-edit"></i> Editar Lapso Académico</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_lapso" id="edit_id">
                    <div class="form-group">
                        <label>Nombre del Lapso</label>
                        <input type="text" name="nombre_lapso" id="edit_nombre" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Fecha de Inicio</label>
                        <input type="date" name="fecha_inicio" id="edit_inicio" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Fecha de Fin</label>
                        <input type="date" name="fecha_fin" id="edit_fin" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../../admin/layout/parte2.php'; ?>

<script>
$(document).ready(function() {
    
    // --- LÍMITES DE LA GESTIÓN ACTIVA (Pasados desde PHP) ---
    const GESTION_INICIO = new Date('<?= $gestion_inicio_js ?>');
    const GESTION_FIN = new Date('<?= $gestion_fin_js ?>');
    // Para comparación correcta, ajustamos el final de la gestión a medianoche del día siguiente
    // y el inicio a la medianoche de su día, para incluir ambos.
    const GESTION_FIN_INCLUSIVE = new Date(GESTION_FIN.getTime() + (1000 * 60 * 60 * 24) - 1); 

    // Inicialización de DataTables
    $('#tabla_lapsos').DataTable({
        responsive: true,
        autoWidth: false,
        pageLength: 5,
        language: {
            decimal: "",
            emptyTable: "No hay información disponible",
            info: "Mostrando _START_ a _END_ de _TOTAL_ lapsos",
            infoEmpty: "Mostrando 0 a 0 de 0 lapsos",
            infoFiltered: "(filtrado de _MAX_ lapsos totales)",
            lengthMenu: "Mostrar _MENU_ lapsos",
            loadingRecords: "Cargando...",
            processing: "Procesando...",
            search: "Buscar:",
            zeroRecords: "No se encontraron resultados",
            paginate: {
                first: "Primero",
                last: "Último",
                next: "Siguiente",
                previous: "Anterior"
            },
        }
    });

    // Cargar datos en el modal de Edición
    $('.edit-btn').click(function() {
        $('#edit_id').val($(this).data('id'));
        $('#edit_nombre').val($(this).data('nombre'));
        $('#edit_inicio').val($(this).data('inicio'));
        $('#edit_fin').val($(this).data('fin'));
        $('#modal-edit').modal('show');
    });

    /**
     * Validación de Fechas, Duración y Límites de la Gestión
     */
    $('#form-create, #form-edit').submit(function(e) {
        // Prevenimos el envío por defecto para realizar la validación
        e.preventDefault(); 
        
        // Obtenemos los valores de las fechas
        const inicio_val = $(this).find('input[name="fecha_inicio"]').val();
        const fin_val = $(this).find('input[name="fecha_fin"]').val();

        // Convertir a objetos Date (se recomienda usar librerías como Moment.js para mayor robustez)
        const inicio = new Date(inicio_val);
        const fin = new Date(fin_val);
        const form = $(this); // Referencia al formulario

        // --- VALIDACIÓN 1: Dentro de los límites de la Gestión ---
        // La fecha de inicio del lapso debe ser >= al inicio de la gestión
        // La fecha de fin del lapso debe ser <= al fin de la gestión
        if (inicio < GESTION_INICIO || fin > GESTION_FIN) {
            Swal.fire({
                icon: 'error',
                title: 'Límites de la Gestión',
                html: `Las fechas del lapso deben estar dentro del periodo activo:<br> 
                       ${GESTION_INICIO.toLocaleDateString()} hasta ${GESTION_FIN.toLocaleDateString()}.<br>
                       Por favor, verifica las fechas.`,
                confirmButtonColor: '#dc3545'
            });
            return false;
        }


        // --- VALIDACIÓN 2: Fecha de fin no puede ser anterior a la de inicio ---
        if (fin < inicio) {
            Swal.fire({
                icon: 'error',
                title: 'Error de Fechas',
                text: 'La fecha de fin no puede ser anterior a la de inicio.',
                confirmButtonColor: '#dc3545'
            });
            return false;
        }

        // --- VALIDACIÓN 3: Validación de Duración (Mínimo 90 días, Máximo 100 días) ---
        const MIN_DIAS = 90;
        const MAX_DIAS = 100;
        
        // Calcular la diferencia en milisegundos
        const diffTime = Math.abs(fin.getTime() - inicio.getTime());
        
        // Convertir la diferencia de milisegundos a días (redondeado hacia arriba para incluir el día de fin)
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        
        if (diffDays < MIN_DIAS || diffDays > MAX_DIAS) {
            Swal.fire({
                icon: 'warning',
                title: 'Restricción de Duración',
                html: `El lapso debe durar entre ${MIN_DIAS} y ${MAX_DIAS} días.<br>La duración actual es de ${diffDays} días.<br><br>Por favor, ajusta las fechas.`,
                confirmButtonColor: '#ffc107',
                confirmButtonText: 'Entendido'
            });
            return false;
        }

        // Si pasa todas las validaciones, enviamos el formulario manualmente.
        form.unbind('submit').submit(); 
    });
});
</script>