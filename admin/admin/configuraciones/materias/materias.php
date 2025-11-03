<?php

// Ajustar rutas según tu estructura
include ('../../../app/config.php');
include ('../../../admin/layout/parte1.php');

// Obtener todos los grados para el formulario
$sql_grados = "SELECT * FROM grados WHERE estado = 1";
$query_grados = $pdo->prepare($sql_grados);
$query_grados->execute();
$grados = $query_grados->fetchAll(PDO::FETCH_ASSOC);

// Obtener todas las materias
$sql_materias = "SELECT m.*, g.grado FROM materias m INNER JOIN grados g ON m.id_grado = g.id_grado WHERE m.estado = 1";
$query_materias = $pdo->prepare($sql_materias);
$query_materias->execute();
$materias = $query_materias->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="content-wrapper">
    <div class="content">
        <div class="container">
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">Materias</h1>
                        </div>
                        <div class="col-sm-6">
                            <button type="button" class="btn btn-success float-right" data-toggle="modal" data-target="#modal-create-materia">
                                <i class="fas fa-plus"></i> Nueva Materia
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Listado de Materias</h3>
                        </div>
                        <div class="card-body">
                            <table id="tabla_materias" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>N°</th>
                                        <th>Nombre de la Materia</th>
                                        <th>Grado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($materias as $index => $materia): ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td><?= htmlspecialchars($materia['nombre_materia']) ?></td>
                                            <td><?= htmlspecialchars($materia['grado']) ?></td>
                                            <td>
                                                <button class="btn btn-warning btn-sm edit-materia-btn"
                                                        data-id="<?= $materia['id_materia'] ?>"
                                                        data-nombre="<?= htmlspecialchars($materia['nombre_materia']) ?>"
                                                        data-grado="<?= $materia['id_grado'] ?>">
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
    </div>
</div>

<div class="modal fade" id="modal-create-materia">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form-create-materia" action="guardar_materia.php" method="post">
                <div class="modal-header bg-success">
                    <h4 class="modal-title">Nueva Materia</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="nombre_materia">Nombre de la Materia</label>
                        <input type="text" name="nombre_materia" class="form-control" id="nombre_materia" required>
                    </div>
                    <div class="form-group">
                        <label for="id_grado">Grado</label>
                        <select name="id_grado" class="form-control" id="id_grado" required>
                            <option value="">Seleccionar Grado</option>
                            <?php foreach ($grados as $grado): ?>
                                <option value="<?= $grado['id_grado'] ?>"><?= htmlspecialchars($grado['grado']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-edit-materia">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form-edit-materia" action="actualizar_materia.php" method="post">
                <div class="modal-header bg-warning">
                    <h4 class="modal-title">Editar Materia</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_materia" id="edit_materia_id">
                    <div class="form-group">
                        <label for="edit_nombre_materia">Nombre de la Materia</label>
                        <input type="text" name="nombre_materia" class="form-control" id="edit_nombre_materia" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_id_grado">Grado</label>
                        <select name="id_grado" class="form-control" id="edit_id_grado" required>
                            <option value="">Seleccionar Grado</option>
                            <?php foreach ($grados as $grado): ?>
                                <option value="<?= $grado['id_grado'] ?>"><?= htmlspecialchars($grado['grado']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../../../admin/layout/parte2.php';
?>

<script>
$(document).ready(function() {
    // Inicializar DataTable para materias
    $('#tabla_materias').DataTable({
        responsive: true,
        autoWidth: false,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json'
        }
    });

    // Manejar clic en botón de editar materia
    $('.edit-materia-btn').click(function() {
        var id = $(this).data('id');
        var nombre = $(this).data('nombre');
        var grado = $(this).data('grado');

        $('#edit_materia_id').val(id);
        $('#edit_nombre_materia').val(nombre);
        $('#edit_id_grado').val(grado);

        $('#modal-edit-materia').modal('show');
    });
});
</script>