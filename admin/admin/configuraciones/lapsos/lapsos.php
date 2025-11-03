<?php
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

// Obtener lapsos
$sql_lapsos = "SELECT * FROM lapsos WHERE id_gestion = :id_gestion ORDER BY fecha_inicio";
$query_lapsos = $pdo->prepare($sql_lapsos);
$query_lapsos->bindParam(':id_gestion', $gestion_activa['id_gestion']);
$query_lapsos->execute();
$lapsos = $query_lapsos->fetchAll(PDO::FETCH_ASSOC);
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

<!-- Modal Crear -->
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

<!-- Modal Editar -->
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
    // DataTable completamente en español
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

    // Editar lapso
    $('.edit-btn').click(function() {
        $('#edit_id').val($(this).data('id'));
        $('#edit_nombre').val($(this).data('nombre'));
        $('#edit_inicio').val($(this).data('inicio'));
        $('#edit_fin').val($(this).data('fin'));
        $('#modal-edit').modal('show');
    });

    // Validar fechas
    $('#form-create, #form-edit').submit(function(e) {
        const inicio = new Date($(this).find('input[name="fecha_inicio"]').val());
        const fin = new Date($(this).find('input[name="fecha_fin"]').val());
        if (fin < inicio) {
            alert('⚠️ La fecha de fin no puede ser anterior a la de inicio.');
            e.preventDefault();
            return false;
        }
        return true;
    });
});
</script>
