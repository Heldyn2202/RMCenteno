<?php
include('../../app/config.php');
include('../../admin/layout/parte1.php');

// Datos base
$id_grado = isset($_GET['grado']) ? (int)$_GET['grado'] : 0;
$id_seccion = isset($_GET['seccion']) ? (int)$_GET['seccion'] : 0;

$gestion_activa = $pdo->query("SELECT * FROM gestiones WHERE estado = 1 ORDER BY desde DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
$grados = $pdo->query(
    "SELECT id_grado, grado
     FROM grados
     ORDER BY CASE
        WHEN grado LIKE 'Primer Nivel%' THEN 1
        WHEN grado LIKE 'Segundo Nivel%' THEN 2
        WHEN grado LIKE 'Tercer Nivel%' THEN 3
        WHEN grado LIKE 'Primer Grado%' THEN 4
        WHEN grado LIKE 'Segundo Grado%' THEN 5
        WHEN grado LIKE 'Tercer Grado%' THEN 6
        WHEN grado LIKE 'Cuarto Grado%' THEN 7
        WHEN grado LIKE 'Quinto Grado%' THEN 8
        WHEN grado LIKE 'Sexto Grado%' THEN 9
        ELSE 99
     END, grado"
)->fetchAll(PDO::FETCH_ASSOC);

// Cargar secciones filtradas por grado (solo activas)
$secciones = [];
if ($id_grado) {
    $stmtSecc = $pdo->prepare("SELECT id_seccion, nombre_seccion, turno FROM secciones WHERE id_grado = ? AND estado = 1 ORDER BY nombre_seccion, turno");
    $stmtSecc->execute([$id_grado]);
    $secciones = $stmtSecc->fetchAll(PDO::FETCH_ASSOC);
}

$horario = null;
$detalles = [];

if ($id_grado && $id_seccion && $gestion_activa) {
    $stmt = $pdo->prepare("SELECT * FROM horarios WHERE id_gestion = ? AND id_grado = ? AND id_seccion = ? ORDER BY id_horario DESC LIMIT 1");
    $stmt->execute([$gestion_activa['id_gestion'], $id_grado, $id_seccion]);
    $horario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($horario) {
        $sql_det = $pdo->prepare("SELECT hd.*, m.nombre_materia, CONCAT(p.nombres,' ',p.apellidos) AS profesor, hd.id_profesor
                                   FROM horario_detalle hd
                                   JOIN materias m ON m.id_materia = hd.id_materia
                                   LEFT JOIN profesores p ON p.id_profesor = hd.id_profesor
                                   WHERE hd.id_horario = ?
                                   ORDER BY FIELD(hd.dia_semana, 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'), hd.hora_inicio");
        $sql_det->execute([$horario['id_horario']]);
        $detalles = $sql_det->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Obtener profesores y materias para el modal de edición
$profesores_modal = [];
$materias_modal = [];
if ($id_seccion > 0) {
    // Profesores asignados a esta sección
    $stmtProf = $pdo->prepare("SELECT DISTINCT p.id_profesor, CONCAT(p.nombres,' ',p.apellidos) AS nombre
                                FROM profesores p
                                INNER JOIN asignaciones_profesor ap ON p.id_profesor = ap.id_profesor
                                WHERE ap.id_seccion = ? AND ap.estado = 1 AND p.estado = 1
                                ORDER BY p.apellidos, p.nombres");
    $stmtProf->execute([$id_seccion]);
    $profesores_modal = $stmtProf->fetchAll(PDO::FETCH_ASSOC);
    
    // Todas las materias activas
    $materias_modal = $pdo->query("SELECT id_materia, nombre_materia FROM materias WHERE estado = 1 ORDER BY nombre_materia")->fetchAll(PDO::FETCH_ASSOC);
}
?>

<div class="content-wrapper">
    <div class="content">
        <div class="container-fluid">
            <div class="row mb-3">
                <div class="col-sm-6"><h3>Horario Consolidado por Curso</h3></div>
            </div>
            <form class="card card-body mb-3" method="get">
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Grado</label>
                        <select name="grado" class="form-control" required onchange="this.form.submit()">
                            <option value="">Seleccionar</option>
                            <?php foreach($grados as $g): ?>
                            <option value="<?=$g['id_grado']?>" <?= $id_grado==$g['id_grado']?'selected':'' ?>><?=htmlspecialchars($g['grado'])?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label>Sección</label>
                        <select name="seccion" class="form-control" required <?= $id_grado ? '' : 'disabled' ?>>
                            <option value="">Seleccionar</option>
                            <?php foreach($secciones as $s): ?>
                            <option value="<?=$s['id_seccion']?>" <?= $id_seccion==$s['id_seccion']?'selected':'' ?>><?=htmlspecialchars($s['nombre_seccion'])?> (<?=htmlspecialchars($s['turno'])?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-md-4 align-self-end">
                        <button class="btn btn-primary" type="submit">Ver horario</button>
                    </div>
                </div>
            </form>

            <?php if($horario): ?>
                <?php
                // Organizar los detalles del horario
                $organizado = [];
                foreach ($detalles as $d) {
                    $dia = $d['dia_semana'];
                    $hora_inicio = $d['hora_inicio'];
                    
                    // Normalizar la hora - puede venir en diferentes formatos
                    // TIME de MySQL puede venir como string "07:50:00" o como objeto Time
                    if (is_object($hora_inicio)) {
                        $hora_inicio = $hora_inicio->format('H:i:s');
                    }
                    // Convertir a string si es necesario
                    $hora_inicio = (string)$hora_inicio;
                    
                    // Extraer solo HH:MM de la hora para usar como clave
                    $hora_key = substr($hora_inicio, 0, 5);
                    
                    if (!isset($organizado[$dia])) {
                        $organizado[$dia] = [];
                    }
                    
                    // Guardar con la clave normalizada (HH:MM)
                    // Esto permite encontrar el horario independientemente del formato en BD
                    $organizado[$dia][$hora_key] = $d;
                }
                
                $dias = ['Lunes','Martes','Miércoles','Jueves','Viernes'];
                // Actualizado según la parrilla de la imagen (07:00 AM - 05:10 PM)
                $bloques = [
                    ['07:00:00','07:40:00'],   // Período 1
                    ['07:40:00','08:20:00'],   // Período 2
                    ['08:20:00','09:00:00'],   // Período 3
                    ['09:00:00','09:40:00'],   // Período 4
                    ['09:50:00','10:30:00'],   // Período 5
                    ['10:30:00','11:10:00'],   // Período 6
                    ['11:10:00','11:50:00'],   // Período 7
                    ['11:50:00','12:30:00'],   // Período 8
                    ['01:00:00','01:40:00'],   // Período 9
                    ['01:40:00','02:20:00'],   // Período 10
                    ['02:30:00','03:10:00'],   // Período 11
                    ['03:10:00','03:50:00'],   // Período 12
                    ['03:50:00','04:30:00'],   // Período 13
                    ['04:20:00','05:10:00']    // Período 14
                ];
                
                // Debug: mostrar cuántos detalles hay
                // echo "<!-- Debug: Detalles encontrados: " . count($detalles) . " -->";
                ?>
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <?php 
                            // Detectar si se está editando un bloque
                            $editando_bloque = isset($_GET['editando']) && $_GET['editando'] == '1';
                            $id_bloque_editando = isset($_GET['id_bloque']) ? (int)$_GET['id_bloque'] : 0;
                            
                            if ($editando_bloque && $id_bloque_editando > 0):
                                // Obtener información del bloque que se está editando
                                $cols = $pdo->query("SHOW COLUMNS FROM horario_detalle")->fetchAll(PDO::FETCH_COLUMN);
                                $pk = null;
                                foreach (['id_detalle','id_horario_detalle','id'] as $c) { 
                                    if (in_array($c, $cols, true)) { 
                                        $pk = $c; 
                                        break; 
                                    } 
                                }
                                if ($pk) {
                                    $stmtBloque = $pdo->prepare("SELECT hd.*, CONCAT(p.nombres,' ',p.apellidos) AS profesor_nombre, p.id_profesor
                                                                 FROM horario_detalle hd
                                                                 LEFT JOIN profesores p ON p.id_profesor = hd.id_profesor
                                                                 WHERE hd.$pk = ?");
                                    $stmtBloque->execute([$id_bloque_editando]);
                                    $bloque_editando = $stmtBloque->fetch(PDO::FETCH_ASSOC);
                                }
                            ?>
                                <span class="badge badge-warning">
                                    <i class="fas fa-edit"></i> <strong>en edición</strong>
                                </span>
                                <?php if(isset($bloque_editando) && $bloque_editando && !empty($bloque_editando['profesor_nombre'])): ?>
                                    <small class="ml-2 text-muted">Profesor: <?=htmlspecialchars($bloque_editando['profesor_nombre'])?></small>
                                <?php endif; ?>
                                <?php if($id_seccion > 0 && $id_grado > 0 && $horario): ?>
                                <button type="button" class="btn btn-primary btn-sm ml-2" id="btnEditarHorarios" data-seccion="<?=$id_seccion?>" data-grado="<?=$id_grado?>" data-horario="<?=$horario['id_horario']?>">
                                    <i class="fas fa-edit"></i> Editar Horarios
                                </button>
                                <button type="button" class="btn btn-success btn-sm ml-2" id="btnGuardarHorarios" data-horario="<?=$horario['id_horario']?>" style="display: none;">
                                    <i class="fas fa-save"></i> Guardar Horarios
                                </button>
                                <?php endif; ?>
                            <?php else: ?>
                            Estado: <span class="badge badge-<?= ($horario['estado']??'BORRADOR')==='PUBLICADO'?'success':'secondary' ?>"><?=(htmlspecialchars($horario['estado']??'BORRADOR'))?></span>
                            <?php if(count($detalles) > 0): ?>
                                <small class="ml-2 text-muted">(<?=count($detalles)?> bloques asignados)</small>
                                <?php endif; ?>
                                <?php if($id_seccion > 0 && $id_grado > 0 && $horario): ?>
                                <button type="button" class="btn btn-primary btn-sm ml-2" id="btnEditarHorarios" data-seccion="<?=$id_seccion?>" data-grado="<?=$id_grado?>" data-horario="<?=$horario['id_horario']?>">
                                    <i class="fas fa-edit"></i> Editar Horarios
                                </button>
                                <button type="button" class="btn btn-success btn-sm ml-2" id="btnGuardarHorarios" data-horario="<?=$horario['id_horario']?>" style="display: none;">
                                    <i class="fas fa-save"></i> Guardar Horarios
                                </button>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        <div>
                            <a class="btn btn-outline-secondary btn-sm" href="generar_horario_pdf.php?id_horario=<?=$horario['id_horario']?>&preview=1" target="_blank" id="btnExportarPDF">
                                <i class="fas fa-file-pdf"></i> Exportar PDF
                            </a>
                            <?php if(($horario['estado']??'BORRADOR')!=='PUBLICADO'): ?>
                                <button type="button" class="btn btn-success btn-sm" id="btnAprobarPublicar" data-id-horario="<?=$horario['id_horario']?>">
                                    <i class="fas fa-check-circle"></i> Aprobar y Publicar
                                </button>
                            <?php endif; ?>
                            <button type="button" class="btn btn-danger btn-sm" id="btnEliminarHorario" data-id-horario="<?=$horario['id_horario']?>" data-grado="<?=$horario['id_grado']?>" data-seccion="<?=$horario['id_seccion']?>">
                                <i class="fas fa-trash"></i> Eliminar Horario
                            </button>
                        </div>
                    </div>
                    <div class="card-body table-responsive">
                        <?php if(empty($detalles)): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> El horario existe pero no tiene bloques asignados. 
                                <a href="crear_horarios.php" class="alert-link">Crear nuevo horario con asignaciones</a>
                            </div>
                        <?php else: ?>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Hora</th>
                                    <?php foreach($dias as $d): ?><th><?=$d?></th><?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($bloques as $b): 
                                    $hi=$b[0]; 
                                    $hf=$b[1];
                                    // Normalizar para búsqueda (extraer HH:MM)
                                    $hi_busqueda = substr($hi, 0, 5);
                                ?>
                                <tr>
                                    <td><strong><?=substr($hi,0,5)?> - <?=substr($hf,0,5)?></strong></td>
                                    <?php foreach($dias as $d): 
                                        // Buscar usando la clave normalizada (HH:MM)
                                        $detalle = $organizado[$d][$hi_busqueda] ?? null;
                                    ?>
                                        <td style="min-width: 150px;" class="celda-horario" data-dia="<?=$d?>" data-hora-inicio="<?=$hi_busqueda?>" data-hora-fin="<?=substr($hf,0,5)?>">
                                            <?php if($detalle): ?>
                                                <?php 
                                                  $pk = $detalle['id_detalle'] ?? ($detalle['id_horario_detalle'] ?? ($detalle['id'] ?? null));
                                                ?>
                                                <?php if($pk): ?>
                                                <div class="bloque-horario" data-id="<?=$pk?>" data-materia="<?=$detalle['id_materia']?>" data-profesor="<?=$detalle['id_profesor'] ?? ''?>" data-materia-nombre="<?=htmlspecialchars($detalle['nombre_materia'])?>" data-profesor-nombre="<?=htmlspecialchars($detalle['profesor'] ?? '')?>">
                                                        <div class="mb-1">
                                                            <strong class="text-primary"><?=htmlspecialchars($detalle['nombre_materia'])?></strong>
                                                        </div>
                                                        <?php if(!empty($detalle['profesor']) && trim($detalle['profesor']) !== ''): ?>
                                                        <div class="text-muted small">
                                                            <i class="fas fa-user"></i> <?=htmlspecialchars($detalle['profesor'])?>
                                                        </div>
                                                        <?php else: ?>
                                                        <div class="text-muted small">
                                                            <i class="fas fa-user-slash"></i> Sin profesor asignado
                                                        </div>
                                                        <?php endif; ?>
                                                </div>
                                                <?php else: ?>
                                                <div class="mb-1">
                                                    <strong class="text-primary"><?=htmlspecialchars($detalle['nombre_materia'])?></strong>
                                                </div>
                                                <?php if(!empty($detalle['profesor']) && trim($detalle['profesor']) !== ''): ?>
                                                <div class="text-muted small">
                                                    <i class="fas fa-user"></i> <?=htmlspecialchars($detalle['profesor'])?>
                                                </div>
                                                <?php else: ?>
                                                <div class="text-muted small">
                                                    <i class="fas fa-user-slash"></i> Sin profesor asignado
                                                </div>
                                                <?php endif; ?>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <div class="celda-vacia" style="min-height: 60px;">
                                                <span class="text-muted font-italic">-</span>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php endif; ?>
                    </div>
                </div>
            <?php elseif($id_grado || $id_seccion): ?>
                <div class="alert alert-warning">No se encontró un horario para la combinación seleccionada.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal para agregar profesor a celda vacía -->
<div class="modal fade" id="modalAgregarProfesor" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Agregar Profesor al Horario</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formAgregarProfesor">
                <div class="modal-body">
                    <input type="hidden" id="add_horario_id" name="id_horario">
                    <input type="hidden" id="add_dia" name="dia_semana">
                    <input type="hidden" id="add_hora_inicio" name="hora_inicio">
                    <input type="hidden" id="add_hora_fin" name="hora_fin">
                    <input type="hidden" id="add_grado" name="grado" value="<?=$id_grado?>">
                    <input type="hidden" id="add_seccion" name="seccion" value="<?=$id_seccion?>">
                    
                    <div class="alert alert-info">
                        <strong>Día:</strong> <span id="add_dia_texto"></span><br>
                        <strong>Hora:</strong> <span id="add_hora_texto"></span>
                    </div>
                    
                    <div class="form-group">
                        <label for="add_profesor">Profesor</label>
                        <select id="add_profesor" name="id_profesor" class="form-control" required>
                            <option value="">Seleccionar profesor</option>
                            <?php foreach($profesores_modal as $p): ?>
                                <option value="<?=$p['id_profesor']?>"><?=htmlspecialchars($p['nombre'])?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="add_materia">Materia</label>
                        <select id="add_materia" name="id_materia" class="form-control" required disabled>
                            <option value="">Primero seleccione un profesor</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Agregar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para editar profesores del horario -->
<div class="modal fade" id="modalEditarProfesoresHorario" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Profesores del Horario</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="parrillaEditarHorario">
                    <p class="text-center"><i class="fas fa-spinner fa-spin"></i> Cargando horario...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGuardarHorarioEditado">Guardar Cambios</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para editar bloque -->
<div class="modal fade" id="modalEditarBloque" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Bloque de Horario</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formEditarBloque">
                <div class="modal-body">
                    <input type="hidden" id="edit_bloque_id" name="id_bloque">
                    <input type="hidden" id="edit_grado" name="grado" value="<?=$id_grado?>">
                    <input type="hidden" id="edit_seccion" name="seccion" value="<?=$id_seccion?>">
                    
                    <div class="form-group">
                        <label for="edit_materia">Materia</label>
                        <select id="edit_materia" name="id_materia" class="form-control" required>
                            <option value="">Seleccionar materia</option>
                            <?php foreach($materias_modal as $m): ?>
                                <option value="<?=$m['id_materia']?>"><?=htmlspecialchars($m['nombre_materia'])?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_profesor">Profesor</label>
                        <div class="input-group">
                            <select id="edit_profesor" name="id_profesor" class="form-control">
                                <option value="">Sin profesor</option>
                                <?php foreach($profesores_modal as $p): ?>
                                    <option value="<?=$p['id_profesor']?>"><?=htmlspecialchars($p['nombre'])?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php if($id_seccion > 0): ?>
                            <div class="input-group-append">
                                <a href="../../asignaciones/listar_asignaciones.php" 
                                   class="btn btn-success" 
                                   title="Agregar profesor a esta sección"
                                   target="_blank">
                                    <i class="fas fa-plus"></i>
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
include('../../admin/layout/parte2.php');
include('../../layout/mensajes.php');
?>

<style>
/* Estilos para centrar SweetAlert2 */
.swal2-popup-custom {
    display: flex !important;
    flex-direction: column !important;
    align-items: center !important;
    justify-content: center !important;
    margin: auto !important;
    position: fixed !important;
    top: 50% !important;
    left: 50% !important;
    transform: translate(-50%, -50%) !important;
    z-index: 10000 !important;
}

.swal2-container {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    width: 100% !important;
    height: 100% !important;
    z-index: 9999 !important;
}

.swal2-title-custom {
    font-size: 1.5rem !important;
    font-weight: bold !important;
    color: #d33 !important;
    margin-bottom: 1rem !important;
}

.swal2-html-container-custom {
    font-size: 1rem !important;
    line-height: 1.6 !important;
    color: #333 !important;
}

    .select-profesor-editar.is-invalid {
        border-color: #dc3545;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='none' stroke='%23dc3545' viewBox='0 0 12 12'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right calc(0.375em + 0.1875rem) center;
        background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        padding-right: calc(1.5em + 0.75rem);
    }
</style>

<script>
$(document).ready(function() {
    // Abrir modal de edición cuando se hace clic en el botón editar (bloque existente) - solo si NO está en modo edición
    $(document).on('click', '.btn-editar-bloque', function() {
        if (modoEditarActivo) return; // Si está en modo edición, usar el click directo en el bloque
        
        const bloqueId = $(this).data('id');
        const $bloque = $(this).closest('.bloque-horario');
        
        // Obtener datos del bloque
        const materiaId = $bloque.data('materia');
        const profesorId = $bloque.data('profesor') || '';
        const materiaNombre = $bloque.data('materia-nombre');
        const profesorNombre = $bloque.data('profesor-nombre');
        
        // Llenar el modal
        $('#edit_bloque_id').val(bloqueId);
        $('#edit_materia').val(materiaId);
        $('#edit_profesor').val(profesorId);
        
        // Actualizar URL para mostrar "en edición"
        const urlParams = new URLSearchParams(window.location.search);
        urlParams.set('editando', '1');
        urlParams.set('id_bloque', bloqueId);
        window.history.replaceState({}, '', '?' + urlParams.toString());
        
        // Actualizar el estado en la página
        actualizarEstadoEdicion(bloqueId, profesorNombre);
        
        // Mostrar modal
        $('#modalEditarBloque').modal('show');
    });
    
    // Ya no se necesita hacer clickeable, porque todas las celdas muestran selects directamente
    
    // Función para actualizar el estado "en edición" en la página
    function actualizarEstadoEdicion(bloqueId, profesorNombre) {
        // Esta función ya no se usa, pero la mantenemos por compatibilidad
    }
    
    // Manejar envío del formulario de edición
    $('#formEditarBloque').on('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            id_bloque: $('#edit_bloque_id').val(),
            id_materia: $('#edit_materia').val(),
            id_profesor: $('#edit_profesor').val() || null,
            grado: $('#edit_grado').val(),
            seccion: $('#edit_seccion').val()
        };
        
        // Agregar día y hora si existen (cuando se edita desde el modal de editar profesores)
        if ($('#edit_dia').length > 0) {
            formData.dia_semana = $('#edit_dia').val();
            formData.hora_inicio = $('#edit_hora_inicio').val();
            formData.hora_fin = $('#edit_hora_fin').val();
        }
        
        // Mostrar indicador de carga
        const $submitBtn = $(this).find('button[type="submit"]');
        const originalText = $submitBtn.html();
        $submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');
        
        // Enviar datos vía AJAX
        $.ajax({
            url: 'actualizar_bloque_ajax.php',
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: 'Bloque actualizado correctamente.',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        // Recargar la página
                        window.location.href = 'horarios_consolidados.php?grado=<?=$id_grado?>&seccion=<?=$id_seccion?>&editando=1&id_bloque=' + formData.id_bloque;
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'Error al actualizar el bloque.'
                    });
                    $submitBtn.prop('disabled', false).html(originalText);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al actualizar el bloque. Por favor, intenta de nuevo.'
                });
                $submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });
    
    // Cuando se cierra el modal, quitar el estado de edición de la URL
    $('#modalEditarBloque').on('hidden.bs.modal', function() {
        const urlParams = new URLSearchParams(window.location.search);
        urlParams.delete('editando');
        urlParams.delete('id_bloque');
        window.history.replaceState({}, '', '?' + urlParams.toString());
        
        // Restaurar el estado original
        const $header = $('.card-header');
        const estadoOriginal = '<?php echo ($horario["estado"]??"BORRADOR")==="PUBLICADO"?"success":"secondary"; ?>';
        const estadoTexto = '<?php echo htmlspecialchars($horario["estado"]??"BORRADOR"); ?>';
        let html = 'Estado: <span class="badge badge-' + estadoOriginal + '">' + estadoTexto + '</span>';
        <?php if(count($detalles) > 0): ?>
        html += '<small class="ml-2 text-muted">(<?=count($detalles)?> bloques asignados)</small>';
        <?php endif; ?>
        // Botón agregar profesor eliminado
        $header.find('div:first').html(html);
    });
    
    // Filtrar materias cuando cambia el profesor en el modal
    $('#edit_profesor').on('change', function() {
        const profesorId = $(this).val();
        const seccionId = $('#edit_seccion').val();
        const $materiaSelect = $('#edit_materia');
        const materiaActual = $materiaSelect.val();
        
        if (!profesorId || profesorId === '' || !seccionId || seccionId === '') {
            return;
        }
        
        // Mostrar indicador de carga
        $materiaSelect.html('<option value="">Cargando materias...</option>').prop('disabled', true);
        
        // Obtener materias del profesor en esta sección
        $.ajax({
            url: 'ajax/obtener_materias.php',
            method: 'GET',
            data: {
                id_profesor: profesorId,
                id_seccion: seccionId
            },
            dataType: 'json',
            success: function(response) {
                if (response.success && response.data && response.data.length > 0) {
                    let options = '<option value="">Seleccionar materia</option>';
                    response.data.forEach(function(materia) {
                        const selected = materia.id == materiaActual ? 'selected' : '';
                        options += `<option value="${materia.id}" ${selected}>${materia.nombre}</option>`;
                    });
                    $materiaSelect.html(options).prop('disabled', false);
                    
                    // Si la materia actual está en la lista, mantenerla seleccionada
                    if (materiaActual && response.data.some(m => m.id == materiaActual)) {
                        $materiaSelect.val(materiaActual);
                    }
                } else {
                    $materiaSelect.html('<option value="">-- Este profesor no tiene materias asignadas en esta sección --</option>').prop('disabled', true);
                    Swal.fire({
                        icon: 'warning',
                        title: 'Sin materias',
                        text: 'Este profesor no tiene materias asignadas en esta sección. Puede agregar asignaciones desde el botón "+".'
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Error al cargar materias:', error);
                $materiaSelect.html('<option value="">-- Error al cargar materias --</option>').prop('disabled', true);
            }
        });
    });
    
    // ============================================================
    // MODAL AGREGAR PROFESOR (ya manejado arriba con btn-editar-bloque-vacio)
    // ============================================================
    
    // Cargar materias cuando se selecciona un profesor en el modal de agregar
    $('#add_profesor').on('change', function() {
        const profesorId = $(this).val();
        const seccionId = $('#add_seccion').val();
        const $materiaSelect = $('#add_materia');
        
        if (!profesorId || profesorId === '') {
            $materiaSelect.html('<option value="">Primero seleccione un profesor</option>').prop('disabled', true);
            return;
        }
        
        $materiaSelect.html('<option value="">Cargando materias...</option>').prop('disabled', true);
        
        $.ajax({
            url: 'ajax/obtener_materias.php',
            method: 'GET',
            data: {
                id_profesor: profesorId,
                id_seccion: seccionId
            },
            dataType: 'json',
            success: function(response) {
                if (response.success && response.data && response.data.length > 0) {
                    let options = '<option value="">Seleccionar materia</option>';
                    response.data.forEach(function(materia) {
                        options += '<option value="' + materia.id + '">' + materia.nombre + '</option>';
                    });
                    $materiaSelect.html(options).prop('disabled', false);
                } else {
                    $materiaSelect.html('<option value="">-- Este profesor no tiene materias asignadas --</option>').prop('disabled', true);
                    Swal.fire({
                        icon: 'warning',
                        title: 'Sin materias',
                        text: 'Este profesor no tiene materias asignadas en esta sección.'
                    });
                }
            },
            error: function() {
                $materiaSelect.html('<option value="">-- Error al cargar materias --</option>').prop('disabled', true);
            }
        });
    });
    
    // Guardar nuevo bloque
    $('#formAgregarProfesor').on('submit', function(e) {
        e.preventDefault();
        
        const formData = $(this).serialize();
        const $submitBtn = $(this).find('button[type="submit"]');
        const originalText = $submitBtn.html();
        $submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');
        
            $.ajax({
            url: 'agregar_bloque_horario.php',
                method: 'POST',
            data: formData,
            dataType: 'json',
                success: function(response) {
                    if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: 'Profesor agregado al horario correctamente.',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                    } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'Error al agregar el profesor.'
                    });
                    $submitBtn.prop('disabled', false).html(originalText);
                    }
                },
                error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al agregar el profesor. Por favor, intenta de nuevo.'
                });
                $submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });
    
    
    // ============================================================
    // EDITAR HORARIOS - MODO EDICIÓN EN PARRILLA
    // ============================================================
    let modoEditarActivo = false;
    
    $('#btnEditarHorarios').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const horarioId = $(this).data('horario');
        const seccionId = $(this).data('seccion');
        const gradoId = $(this).data('grado');
        
        if (!horarioId || !seccionId) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudo obtener la información del horario.'
            });
            return;
        }
        
        modoEditarActivo = !modoEditarActivo;
        
        if (modoEditarActivo) {
            $(this).removeClass('btn-primary').addClass('btn-warning').html('<i class="fas fa-edit"></i> en edición');
            $('#btnGuardarHorarios').show();
            
            // Cargar profesores y habilitar edición en la parrilla
            cargarProfesoresParaEdicion(seccionId, function() {
                habilitarEdicionEnParrilla(horarioId, seccionId);
            });
            
            Swal.fire({
                icon: 'info',
                title: 'Modo Edición Activado',
                text: 'Puedes editar los profesores y materias directamente en la parrilla. No olvides guardar los cambios.',
                timer: 3000,
                showConfirmButton: false
            });
        } else {
            // Está desactivando, solo deshabilitar edición (no guardar automáticamente)
            const $btn = $(this);
            $btn.removeClass('btn-warning').addClass('btn-primary').html('<i class="fas fa-edit"></i> Editar Horarios');
            $('#btnGuardarHorarios').hide();
            deshabilitarEdicionEnParrilla();
        }
    });
    
    // Función para cargar profesores y habilitar edición
    function cargarProfesoresParaEdicion(seccionId, callback) {
        $.ajax({
            url: 'ajax/obtener_profesores.php',
            method: 'GET',
            data: { id_seccion: seccionId },
            dataType: 'json',
            success: function(response) {
                if (response.success && response.data) {
                    window.profesoresEdicion = response.data;
                    if (callback) callback();
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al cargar los profesores.'
                });
            }
        });
    }
    
    // Habilitar edición en la parrilla principal - TODAS las celdas muestran selects
    function habilitarEdicionEnParrilla(horarioId, seccionId) {
        $('.celda-horario').each(function() {
            const $celda = $(this);
            const dia = $celda.data('dia');
            const horaInicio = $celda.data('hora-inicio');
            const horaFin = $celda.data('hora-fin');
            const $bloque = $celda.find('.bloque-horario');
            
            let bloqueId = null;
            let materiaId = null;
            let profesorId = '';
            let materiaNombre = '';
            
            if ($bloque.length > 0) {
                // Ya tiene un bloque
                bloqueId = $bloque.data('id');
                materiaId = $bloque.data('materia');
                profesorId = $bloque.data('profesor') || '';
                materiaNombre = $bloque.data('materia-nombre') || '';
            }
            
            // Reemplazar TODO el contenido de la celda con selects (similar a crear_horarios.php)
            // Asegurar que bloqueId sea un número válido o string vacío, nunca null
            // Convertir a número primero para validar
            let bloqueIdStr = '';
            if (bloqueId !== null && bloqueId !== undefined && bloqueId !== '') {
                const bloqueIdNum = parseInt(bloqueId, 10);
                if (!isNaN(bloqueIdNum) && bloqueIdNum > 0) {
                    bloqueIdStr = String(bloqueIdNum);
                }
            }
            let html = '<div class="edicion-bloque" data-bloque-id="' + bloqueIdStr + '" data-dia="' + dia + '" data-hora-inicio="' + horaInicio + '" data-hora-fin="' + horaFin + '">';
            
            // Select de Profesor
            html += '<div class="form-group mb-2">';
            html += '<label class="small" style="font-size: 11px; margin-bottom: 2px;">Profesor</label>';
            html += '<select class="form-control form-control-sm select-profesor-editar" data-bloque="' + bloqueIdStr + '" data-seccion="' + seccionId + '" style="font-size: 12px;">';
            html += '<option value="">-- Seleccione Profesor --</option>';
            if (window.profesoresEdicion) {
                window.profesoresEdicion.forEach(function(prof) {
                    const selected = prof.id == profesorId ? 'selected' : '';
                    html += '<option value="' + prof.id + '" ' + selected + '>' + prof.nombre_completo + '</option>';
                });
            }
            html += '</select>';
            html += '</div>';
            
            // Select de Materia
            html += '<div class="form-group mb-2">';
            html += '<label class="small" style="font-size: 11px; margin-bottom: 2px;">Materia</label>';
            html += '<select class="form-control form-control-sm select-materia-editar" data-bloque="' + bloqueIdStr + '" data-profesor="' + profesorId + '" data-seccion="' + seccionId + '" style="font-size: 12px;" ' + (profesorId ? '' : 'disabled') + '>';
            if (!profesorId) {
                html += '<option value="">-- Seleccione primero Profesor --</option>';
            } else {
                html += '<option value="">-- Seleccione Materia --</option>';
            }
            if (materiaId && materiaNombre) {
                html += '<option value="' + materiaId + '" selected>' + materiaNombre + '</option>';
            }
            html += '</select>';
            html += '</div>';
            html += '</div>';
            
            // Reemplazar todo el contenido de la celda
            $celda.html(html);
            
            // Cargar materias si hay profesor
            if (profesorId) {
                const $materiaSelect = $celda.find('.select-materia-editar');
                $materiaSelect.html('<option value="">Cargando materias...</option>').prop('disabled', true);
                
                $.ajax({
                    url: 'ajax/obtener_materias.php',
                    method: 'GET',
                    data: {
                        id_profesor: profesorId,
                        id_seccion: seccionId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success && response.data && response.data.length > 0) {
                            let options = '<option value="">-- Seleccione Materia --</option>';
                            response.data.forEach(function(materia) {
                                const selected = materia.id == materiaId ? 'selected' : '';
                                options += '<option value="' + materia.id + '" ' + selected + '>' + materia.nombre + '</option>';
                            });
                            $materiaSelect.html(options).prop('disabled', false);
                        } else {
                            $materiaSelect.html('<option value="">-- Sin materias asignadas --</option>').prop('disabled', true);
                        }
                    },
                    error: function() {
                        $materiaSelect.html('<option value="">-- Error al cargar --</option>').prop('disabled', true);
                    }
                });
            }
        });
    }
    
    // Deshabilitar edición en la parrilla
    function deshabilitarEdicionEnParrilla() {
        // Recargar la página para restaurar el estado original
        window.location.reload();
    }
    
    // Función auxiliar para cargar materias (definida antes de usarse)
    function cargarMateriasParaProfesor(profId, secId, $matSelect, matActual) {
        $matSelect.html('<option value="">Cargando materias...</option>').prop('disabled', true);
        
        $.ajax({
            url: 'ajax/obtener_materias.php',
            method: 'GET',
            data: {
                id_profesor: profId,
                id_seccion: secId
            },
            dataType: 'json',
            success: function(responseMaterias) {
                if (responseMaterias.success && responseMaterias.data && responseMaterias.data.length > 0) {
                    let options = '<option value="">-- Seleccione Materia --</option>';
                    responseMaterias.data.forEach(function(materia) {
                        const selected = materia.id == matActual ? 'selected' : '';
                        options += '<option value="' + materia.id + '" ' + selected + '>' + materia.nombre + '</option>';
                    });
                    $matSelect.html(options).prop('disabled', false);
                } else {
                    $matSelect.html('<option value="">-- Sin materias asignadas --</option>').prop('disabled', true);
                }
            },
            error: function() {
                $matSelect.html('<option value="">-- Error al cargar --</option>').prop('disabled', true);
            }
        });
    }
    
    // Cargar materias cuando se selecciona un profesor en edición y validar conflictos
    $(document).on('change', '.select-profesor-editar', function() {
        const profesorId = $(this).val();
        const seccionId = $(this).data('seccion');
        const $edicionBloque = $(this).closest('.edicion-bloque');
        const $materiaSelect = $edicionBloque.find('.select-materia-editar');
        const bloqueId = $edicionBloque.data('bloque-id');
        const dia = $edicionBloque.data('dia');
        const horaInicio = $edicionBloque.data('hora-inicio');
        const horaFin = $edicionBloque.data('hora-fin');
        const horarioId = $('#btnEditarHorarios').data('horario');
        const materiaActual = bloqueId ? $materiaSelect.find('option:selected').val() : '';
        const $selectProfesor = $(this);
        
        // Actualizar el data-profesor para referencia
        $materiaSelect.data('profesor', profesorId);
        
        if (!profesorId || profesorId === '') {
            $materiaSelect.html('<option value="">-- Seleccione primero Profesor --</option>').prop('disabled', true);
            // Limpiar cualquier mensaje de error previo
            $selectProfesor.removeClass('is-invalid');
            return;
        }
        
        // Validar que tengamos todos los datos necesarios
        if (!dia || !horaInicio || !horaFin) {
            console.error('Faltan datos para validar conflicto:', {
                dia: dia,
                horaInicio: horaInicio,
                horaFin: horaFin,
                edicionBloqueData: $edicionBloque.data()
            });
            // Continuar sin validar conflicto, pero cargar materias
            cargarMateriasParaProfesor(profesorId, seccionId, $materiaSelect, materiaActual);
            return;
        }
        
        // Normalizar horas
        let horaInicioFormato = horaInicio;
        let horaFinFormato = horaFin;
        if (horaInicioFormato.length === 5) {
            horaInicioFormato += ':00';
        }
        if (horaFinFormato.length === 5) {
            horaFinFormato += ':00';
        }
        
        // Verificar conflicto en tiempo real
        // Solo verificar conflictos en OTROS horarios o en OTROS bloques del mismo horario
        // Si estamos editando un bloque existente, excluirlo de la validación
        // IMPORTANTE: Si bloqueId está vacío o es 0, significa que es una celda nueva, no hay que excluir nada
        // Convertir a número para asegurar que sea válido
        let idBloqueExcluir = 0;
        // Obtener bloqueId del atributo data-bloque-id del contenedor edicion-bloque
        const bloqueIdFromAttr = $edicionBloque.attr('data-bloque-id');
        if (bloqueIdFromAttr && bloqueIdFromAttr !== '' && bloqueIdFromAttr !== '0') {
            const bloqueIdNum = parseInt(bloqueIdFromAttr, 10);
            if (!isNaN(bloqueIdNum) && bloqueIdNum > 0) {
                idBloqueExcluir = bloqueIdNum;
            }
        }
        // Si no se encontró en el atributo, intentar desde la variable bloqueId
        if (idBloqueExcluir === 0 && bloqueId) {
            const bloqueIdNum = parseInt(bloqueId, 10);
            if (!isNaN(bloqueIdNum) && bloqueIdNum > 0) {
                idBloqueExcluir = bloqueIdNum;
            }
        }
        
        // Debug: mostrar información en consola
        console.log('Validando conflicto:', {
            profesorId: profesorId,
            dia: dia,
            horaInicio: horaInicioFormato,
            horaFin: horaFinFormato,
            horarioId: horarioId,
            idBloqueExcluir: idBloqueExcluir,
            bloqueIdOriginal: bloqueId,
            bloqueIdFromAttr: bloqueIdFromAttr,
            edicionBloqueData: $edicionBloque.data()
        });
        
            $.ajax({
            url: 'ajax/verificar_conflicto_profesor.php',
            method: 'GET',
            data: {
                id_profesor: profesorId,
                dia_semana: dia,
                hora_inicio: horaInicioFormato,
                hora_fin: horaFinFormato,
                id_horario: horarioId, // Para referencia
                id_bloque_excluir: idBloqueExcluir // Excluir solo el bloque actual que estamos editando (si existe)
            },
            dataType: 'json',
            success: function(response) {
                console.log('Respuesta validación:', response);
                
                // Verificar que la respuesta sea válida
                if (!response) {
                    console.error('Respuesta vacía o inválida');
                    cargarMateriasParaProfesor(profesorId, seccionId, $materiaSelect, materiaActual);
                    return;
                }
                
                // Verificar si hay conflicto
                if (response.success === true && response.conflicto === true) {
                    // Hay conflicto - mostrar mensaje y deshabilitar materia
                    $selectProfesor.addClass('is-invalid');
                    
                    // Deshabilitar materia
                    $materiaSelect.html('<option value="">-- Hay conflicto de horario --</option>').prop('disabled', true);
                    
                    // Mostrar notificación con SweetAlert2 (similar a crear_horarios.php)
                    if (typeof Swal !== 'undefined') {
                        // Construir mensaje detallado si hay datos del conflicto
                        let mensajeDetallado = response.message || 'El profesor ya está asignado en este bloque.';
                        
                        if (response.datos) {
                            const c = response.datos;
                            // Formatear hora (sin segundos si los tiene)
                            const horaInicio = c.hora_inicio ? c.hora_inicio.substring(0, 5) : '';
                            // Inferir hora_fin si es inválida (00:00:00 o NULL)
                            let horaFin = c.hora_fin ? c.hora_fin.substring(0, 5) : '';
                            if (!horaFin || horaFin === '00:00') {
                                // Mapa actualizado según la parrilla de la imagen (07:00 AM - 05:10 PM)
                                const mapaHoras = {
                                    '07:00': '07:40',
                                    '07:40': '08:20',
                                    '08:20': '09:00',
                                    '09:00': '09:40',
                                    '09:50': '10:30',
                                    '10:30': '11:10',
                                    '11:10': '11:50',
                                    '11:50': '12:30',
                                    '01:00': '01:40',
                                    '01:40': '02:20',
                                    '02:30': '03:10',
                                    '03:10': '03:50',
                                    '03:50': '04:30',
                                    '04:20': '05:10'
                                };
                                horaFin = mapaHoras[horaInicio] || horaFin;
                            }
                            // Obtener nombre del profesor
                            const nombreProfesor = c.nombre_profesor || c.profesor_nombre || 'El profesor';
                            // Construir mensaje exacto: "El profesor [Nombre] ya está asignado en este bloque. Materia · GRADO SECCIÓN · hora-hora"
                            const materia = c.nombre_materia || 'Materia';
                            const grado = c.grado || '';
                            const seccion = c.nombre_seccion || '';
                            mensajeDetallado = `El profesor ${nombreProfesor} ya está asignado en este bloque. ${materia} · ${grado} ${seccion} · ${horaInicio}-${horaFin}`;
                        }
                        
                        Swal.fire({
                            icon: 'warning',
                            title: 'Conflicto de Horario',
                            html: '<div style="text-align: left; padding: 10px 0;">' + mensajeDetallado + '</div>',
                            showConfirmButton: true,
                            confirmButtonText: 'Entendido',
                            confirmButtonColor: '#3085d6',
                            width: '500px',
                            padding: '2rem',
                            timer: null, // No cerrar automáticamente
                            allowOutsideClick: true,
                            allowEscapeKey: true,
                            backdrop: true,
                            customClass: {
                                popup: 'swal2-popup-custom',
                                title: 'swal2-title-custom',
                                htmlContainer: 'swal2-html-container-custom'
                            }
                        });
                    } else {
                        // Fallback si SweetAlert2 no está cargado
                        alert('Conflicto de horario: ' + (response.message || 'El profesor ya está asignado en este bloque.'));
                    }
                } else {
                    // No hay conflicto - limpiar errores y cargar materias
                    $selectProfesor.removeClass('is-invalid');
                    
                    // Cargar materias
                    cargarMateriasParaProfesor(profesorId, seccionId, $materiaSelect, materiaActual);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error en validación de conflicto:', {
                    status: status,
                    error: error,
                    responseText: xhr.responseText
                });
                // Si falla la validación, permitir continuar pero cargar materias
                $selectProfesor.removeClass('is-invalid');
                $materiaSelect.html('<option value="">Cargando materias...</option>').prop('disabled', true);
                
                $.ajax({
                    url: 'ajax/obtener_materias.php',
                    method: 'GET',
                    data: {
                        id_profesor: profesorId,
                        id_seccion: seccionId
                    },
                    dataType: 'json',
                    success: function(responseMaterias) {
                        if (responseMaterias.success && responseMaterias.data && responseMaterias.data.length > 0) {
                            let options = '<option value="">-- Seleccione Materia --</option>';
                            responseMaterias.data.forEach(function(materia) {
                                const selected = materia.id == materiaActual ? 'selected' : '';
                                options += '<option value="' + materia.id + '" ' + selected + '>' + materia.nombre + '</option>';
                            });
                            $materiaSelect.html(options).prop('disabled', false);
                        } else {
                            $materiaSelect.html('<option value="">-- Sin materias asignadas --</option>').prop('disabled', true);
                        }
                    },
                    error: function() {
                        $materiaSelect.html('<option value="">-- Error al cargar --</option>').prop('disabled', true);
                    }
                });
            }
        });
    });
    
    // ============================================================
    // GUARDAR HORARIOS
    // ============================================================
    $('#btnGuardarHorarios').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        // Mostrar confirmación antes de guardar
        Swal.fire({
            title: '¿Estás seguro de guardar?',
            text: 'Se guardarán todos los cambios realizados en el horario.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, guardar',
            cancelButtonText: 'No, cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                guardarCambiosParrilla();
            }
        });
    });
    
    function guardarCambiosParrilla(callback) {
        const horarioId = $('#btnEditarHorarios').data('horario');
        const cambios = [];
        
        // Recopilar cambios de TODAS las celdas (existentes y nuevas)
        $('.edicion-bloque').each(function() {
            const bloqueId = $(this).data('bloque-id');
            const dia = $(this).data('dia');
            const horaInicio = $(this).data('hora-inicio');
            const horaFin = $(this).data('hora-fin');
            const profesorId = $(this).find('.select-profesor-editar').val() || null;
            const materiaId = $(this).find('.select-materia-editar').val();
            
            // Solo agregar si hay materia seleccionada
            if (materiaId) {
                if (bloqueId && bloqueId !== '') {
                    // Es un bloque existente que se está actualizando
                    cambios.push({
                        tipo: 'actualizar',
                        id_bloque: bloqueId,
                        id_profesor: profesorId,
                        id_materia: materiaId
                    });
                } else {
                    // Es un nuevo bloque que se está agregando
                    cambios.push({
                        tipo: 'agregar',
                        dia_semana: dia,
                        hora_inicio: horaInicio + ':00',
                        hora_fin: horaFin + ':00',
                        id_profesor: profesorId,
                        id_materia: materiaId
                    });
                }
            } else if (bloqueId && bloqueId !== '') {
                // Si había un bloque pero ahora no tiene materia, eliminarlo
                cambios.push({
                    tipo: 'eliminar',
                    id_bloque: bloqueId
                });
            }
        });
        
        if (cambios.length === 0) {
            if (callback) {
                callback();
            } else {
                Swal.fire({
                    icon: 'info',
                    title: 'Sin cambios',
                    text: 'No hay cambios para guardar.'
                });
            }
            return;
        }
        
        // Guardar cambios
        $.ajax({
            url: 'guardar_cambios_horario.php',
                method: 'POST',
                data: {
                id_horario: horarioId,
                cambios: JSON.stringify(cambios)
                },
            dataType: 'json',
                success: function(response) {
                    if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Guardado!',
                        text: 'El horario se ha guardado correctamente.',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        // Recargar la página para mostrar el horario actualizado
                        window.location.reload();
                    });
                    } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'Error al guardar los cambios.'
                    });
                    }
                },
                error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al guardar los cambios. Por favor, intenta de nuevo.'
                });
            }
        });
    }
    
    // Si hay parámetros de edición en la URL, abrir el modal automáticamente
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('editando') === '1' && urlParams.get('id_bloque')) {
        const bloqueId = urlParams.get('id_bloque');
        const $bloque = $('.bloque-horario[data-id="' + bloqueId + '"]');
        if ($bloque.length) {
            $bloque.find('.btn-editar-bloque').trigger('click');
        }
    }
    
    // Si hay parámetros de grado y sección en la URL, asegurar que el formulario esté completo
    const gradoParam = urlParams.get('grado');
    const seccionParam = urlParams.get('seccion');
    if (gradoParam && seccionParam) {
        // Asegurar que el select de sección esté habilitado si hay grado
        const $gradoSelect = $('select[name="grado"]');
        const $seccionSelect = $('select[name="seccion"]');
        if ($gradoSelect.val() && $seccionSelect.prop('disabled')) {
            $seccionSelect.prop('disabled', false);
        }
    }
    
    // Manejar eliminación de horario completo
    $('#btnEliminarHorario').on('click', function() {
        const idHorario = $(this).data('id-horario');
        const grado = $(this).data('grado');
        const seccion = $(this).data('seccion');
        
        Swal.fire({
            icon: 'warning',
            title: '¿Estás seguro?',
            text: 'Esta acción eliminará el horario completo y todos sus bloques. Esta acción no se puede deshacer.',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Mostrar indicador de carga
                Swal.fire({
                    title: 'Eliminando...',
                    text: 'Por favor espera',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Redirigir a la página de eliminación
                window.location.href = 'eliminar_horario.php?id_horario=' + idHorario;
            }
        });
    });
    
    // Manejar aprobación y publicación de horario
    $('#btnAprobarPublicar').on('click', function() {
        const idHorario = $(this).data('id-horario');
        
        Swal.fire({
            icon: 'question',
            title: '¿Aprobar y publicar este horario?',
            text: 'Esta acción cambiará el estado del horario a PUBLICADO.',
            showCancelButton: true,
            confirmButtonText: 'Aceptar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Redirigir a la página de aprobación
                window.location.href = 'aprobar_publicar.php?id_horario=' + idHorario;
            }
        });
    });
});
</script>

