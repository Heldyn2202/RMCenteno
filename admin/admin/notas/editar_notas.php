<?php
ob_start();
include('../../app/config.php');
include('../../admin/layout/parte1.php');

// Obtener parámetros
$id_estudiante = $_GET['id_estudiante'];
$id_grado = $_GET['grado'];
$id_seccion = $_GET['seccion'];

// Obtener información del estudiante
$sql_estudiante = "SELECT e.* FROM estudiantes e WHERE e.id_estudiante = :id_estudiante";
$query_estudiante = $pdo->prepare($sql_estudiante);
$query_estudiante->bindParam(':id_estudiante', $id_estudiante);
$query_estudiante->execute();
$estudiante = $query_estudiante->fetch(PDO::FETCH_ASSOC);

// Obtener información del grado y sección
$sql_grado = "SELECT g.grado as nombre_grado FROM grados g WHERE g.id_grado = :id_grado";
$query_grado = $pdo->prepare($sql_grado);
$query_grado->bindParam(':id_grado', $id_grado);
$query_grado->execute();
$grado = $query_grado->fetch(PDO::FETCH_ASSOC);

$sql_seccion = "SELECT s.nombre_seccion FROM secciones s WHERE s.id_seccion = :id_seccion";
$query_seccion = $pdo->prepare($sql_seccion);
$query_seccion->bindParam(':id_seccion', $id_seccion);
$query_seccion->execute();
$seccion = $query_seccion->fetch(PDO::FETCH_ASSOC);

// Obtener el periodo académico activo
$sql_gestion = "SELECT * FROM gestiones WHERE estado = 1 LIMIT 1";
$query_gestion = $pdo->prepare($sql_gestion);
$query_gestion->execute();
$gestion_activa = $query_gestion->fetch(PDO::FETCH_ASSOC);

// Obtener los lapsos del periodo activo
$sql_lapsos = "SELECT * FROM lapsos WHERE id_gestion = :id_gestion ORDER BY fecha_inicio";
$query_lapsos = $pdo->prepare($sql_lapsos);
$query_lapsos->bindParam(':id_gestion', $gestion_activa['id_gestion']);
$query_lapsos->execute();
$lapsos = $query_lapsos->fetchAll(PDO::FETCH_ASSOC);

// Obtener materias según el grado
$sql_materias = "SELECT * FROM materias WHERE estado = 1 AND nivel_educativo =
                    (SELECT nivel_educativo FROM grados WHERE id_grado = :grado)";
$query_materias = $pdo->prepare($sql_materias);
$query_materias->bindParam(':grado', $id_grado);
$query_materias->execute();
$materias = $query_materias->fetchAll(PDO::FETCH_ASSOC);

// Obtener las notas existentes para el estudiante
$sql_notas = "SELECT ne.*, m.nombre_materia, l.nombre_lapso
                    FROM notas_estudiantes ne
                    JOIN materias m ON ne.id_materia = m.id_materia
                    JOIN lapsos l ON ne.id_lapso = l.id_lapso
                    WHERE ne.id_estudiante = :id_estudiante";
$query_notas = $pdo->prepare($sql_notas);
$query_notas->bindParam(':id_estudiante', $id_estudiante);
$query_notas->execute();
$notas_existentes = $query_notas->fetchAll(PDO::FETCH_ASSOC);

// Organizar las notas por materia y lapso para facilitar el acceso
$notas_organizadas = [];
foreach ($notas_existentes as $nota) {
    $notas_organizadas[$nota['id_materia']][$nota['id_lapso']] = $nota;
}
?>

<div class="content-wrapper">
    <div class="content">
        <div class="container">
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">Gestión de Notas</h1>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Datos del Estudiante</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <p><strong>Nombre:</strong> <?= htmlspecialchars($estudiante['nombres'] . ' ' . $estudiante['apellidos']) ?></p>
                                </div>
                                <div class="col-md-4">
                                    <p><strong>Cédula:</strong> <?= htmlspecialchars($estudiante['cedula'] ?? 'N/A') ?></p>
                                </div>
                                <div class="col-md-4">
                                    <p><strong>Grado/Sección:</strong> <?= htmlspecialchars($grado['nombre_grado'] ?? 'N/A') ?> - <?= htmlspecialchars($seccion['nombre_seccion'] ?? 'N/A') ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Notas por Lapso</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#modal-agregar-notas">
                                    <i class="fas fa-plus"></i> Agregar Notas
                                </button>
                                <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#modal-exportar-notas">
                                    <i class="fas fa-download"></i> Exportar Boleta
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Seleccione el Lapso Académico</label>
                                <select id="select_lapso" class="form-control">
                                    <option value="">Seleccione un lapso</option>
                                    <?php foreach ($lapsos as $lapso): ?>
                                        <option value="<?= $lapso['id_lapso'] ?>">
                                            <?= $lapso['nombre_lapso'] ?> (<?= date('d/m/Y', strtotime($lapso['fecha_inicio'])) ?> - <?= date('d/m/Y', strtotime($lapso['fecha_fin'])) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div id="tabla_notas_container">
                                <div class="alert alert-info">
                                    Seleccione un lapso académico para ver las calificaciones
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-agregar-notas">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="form-agregar-notas" action="guardar_notas.php" method="post">
                <div class="modal-header bg-success">
                    <h4 class="modal-title">Agregar Nuevas Notas</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_estudiante" value="<?= $id_estudiante ?>">
                    <input type="hidden" name="id_grado" value="<?= $id_grado ?>">

                    <div class="form-group">
                        <label>Seleccione el Lapso</label>
                        <select name="id_lapso" class="form-control" required>
                            <option value="">Seleccione un lapso</option>
                            <?php foreach ($lapsos as $lapso): ?>
                                <option value="<?= $lapso['id_lapso'] ?>"><?= $lapso['nombre_lapso'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div id="materias-container">
                        <div class="form-group row materia-item">
                            <div class="col-md-5">
                                <select name="materias[]" class="form-control select-materia" required>
                                    <option value="">Seleccione materia</option>
                                    <?php foreach ($materias as $materia): ?>
                                        <option value="<?= $materia['id_materia'] ?>"><?= $materia['nombre_materia'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-5">
                                <input type="number" name="calificaciones[]" class="form-control" min="0" max="20" step="0.01" placeholder="Calificación (0-20)" required>
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-danger btn-remove-materia" disabled>
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <button type="button" id="btn-add-materia" class="btn btn-primary mt-2">
                        <i class="fas fa-plus"></i> Agregar otra materia
                    </button>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Guardar Notas</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-exportar-notas">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h4 class="modal-title">Exportar Boleta de Calificaciones</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Seleccione el lapso que desea incluir en la boleta:</p>
                <div class="form-group">
                    <?php foreach ($lapsos as $lapso): ?>
                        <div class="custom-control custom-checkbox">
                            <input class="custom-control-input" type="checkbox" id="lapso_<?= $lapso['id_lapso'] ?>" name="lapsos_exportar[]" value="<?= $lapso['id_lapso'] ?>">
                            <label for="lapso_<?= $lapso['id_lapso'] ?>" class="custom-control-label"><?= $lapso['nombre_lapso'] ?></label>
                        </div>
                    <?php endforeach; ?>
                    <div class="custom-control custom-checkbox mt-2">
                        <input class="custom-control-input" type="checkbox" id="final_year" name="incluir_final" value="1">
                        <label for="final_year" class="custom-control-label">Incluir Promedio Final del Año</label>
                    </div>
                </div>
                <input type="hidden" name="id_estudiante" value="<?= $id_estudiante ?>">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" id="btn-generar-boleta" class="btn btn-info">Generar Boleta</button>
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
    // Cargar notas cuando se selecciona un lapso
    $('#select_lapso').change(function() {
        var id_lapso = $(this).val();
        var id_estudiante = <?= $id_estudiante ?>;

        if (id_lapso) {
            $.ajax({
                url: 'cargar_notas_estudiante.php',
                type: 'GET',
                data: {
                    id_estudiante: id_estudiante,
                    id_lapso: id_lapso
                },
                beforeSend: function() {
                    $('#tabla_notas_container').html('<div class="text-center"><i class="fas fa-spinner fa-spin fa-2x"></i><p>Cargando...</p></div>');
                },
                success: function(data) {
                    $('#tabla_notas_container').html(data);
                }
            });
        } else {
            $('#tabla_notas_container').html('<div class="alert alert-info">Seleccione un lapso académico para ver las calificaciones</div>');
        }
    });

    // Agregar nueva fila de materia
    $('#btn-add-materia').click(function() {
        var newRow = `
        <div class="form-group row materia-item">
            <div class="col-md-5">
                <select name="materias[]" class="form-control select-materia" required>
                    <option value="">Seleccione materia</option>
                    <?php foreach ($materias as $materia): ?>
                        <option value="<?= $materia['id_materia'] ?>"><?= $materia['nombre_materia'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-5">
                <input type="number" name="calificaciones[]" class="form-control" min="0" max="20" step="0.01" placeholder="Calificación (0-20)" required>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-danger btn-remove-materia">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>`;
        $('#materias-container').append(newRow);
    });

    // Eliminar fila de materia
    $(document).on('click', '.btn-remove-materia', function() {
        if ($('.materia-item').length > 1) {
            $(this).closest('.materia-item').remove();
        }
    });

    // Validar formulario de notas
    $('#form-agregar-notas').submit(function(e) {
        var valid = true;

        // Validar que al menos una materia tenga calificación
        if ($('select.select-materia').filter(function() {
            return $(this).val() !== '';
        }).length === 0) {
            alert('Debe agregar al menos una materia con calificación');
            valid = false;
        }

        // Validar que las calificaciones estén entre 0 y 20
        $('input[name="calificaciones[]"]').each(function() {
            var val = parseFloat($(this).val());
            if (isNaN(val) || val < 0 || val > 20) {
                alert('Las calificaciones deben estar entre 0 y 20');
                valid = false;
                return false; // Salir del bucle each
            }
        });

        if (!valid) {
            e.preventDefault();
        }
    });

    // Generar boleta de calificaciones
    $('#btn-generar-boleta').click(function() {
        var lapsos_seleccionados = [];
        $('input[name="lapsos_exportar[]"]:checked').each(function() {
            lapsos_seleccionados.push($(this).val());
        });
        var incluir_final = $('input[name="incluir_final"]:checked').val();
        var id_estudiante = <?= $id_estudiante ?>;

        if (lapsos_seleccionados.length > 0 || incluir_final) {
            var url = 'generar_boleta.php?id_estudiante=' + id_estudiante + '&lapsos=' + lapsos_seleccionados.join(',') + '&final=' + (incluir_final ? 1 : 0);
            window.open(url, '_blank');
        } else {
            alert('Seleccione al menos un lapso o la opción de promedio final para generar la boleta.');
        }
    });
});
</script>