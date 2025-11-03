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
        $sql_det = $pdo->prepare("SELECT hd.*, m.nombre_materia, CONCAT(p.nombres,' ',p.apellidos) AS profesor
                                   FROM horario_detalle hd
                                   JOIN materias m ON m.id_materia = hd.id_materia
                                   LEFT JOIN profesores p ON p.id_profesor = hd.id_profesor
                                   WHERE hd.id_horario = ?
                                   ORDER BY FIELD(hd.dia_semana, 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'), hd.hora_inicio");
        $sql_det->execute([$horario['id_horario']]);
        $detalles = $sql_det->fetchAll(PDO::FETCH_ASSOC);
    }
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
                $bloques = [
                    ['07:50:00','08:30:00'],['08:30:00','09:10:00'],['09:10:00','09:50:00'],
                    ['10:10:00','10:50:00'],['10:50:00','11:30:00'],['11:30:00','12:10:00']
                ];
                
                // Debug: mostrar cuántos detalles hay
                // echo "<!-- Debug: Detalles encontrados: " . count($detalles) . " -->";
                ?>
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            Estado: <span class="badge badge-<?= ($horario['estado']??'BORRADOR')==='PUBLICADO'?'success':'secondary' ?>"><?=(htmlspecialchars($horario['estado']??'BORRADOR'))?></span>
                            <?php if(count($detalles) > 0): ?>
                                <small class="ml-2 text-muted">(<?=count($detalles)?> bloques asignados)</small>
                            <?php endif; ?>
                        </div>
                        <div>
                            <a class="btn btn-outline-secondary btn-sm" href="generar_horario_pdf.php?id_horario=<?=$horario['id_horario']?>">Exportar PDF</a>
                            <?php if(($horario['estado']??'BORRADOR')!=='PUBLICADO'): ?>
                                <a class="btn btn-success btn-sm" href="aprobar_publicar.php?id_horario=<?=$horario['id_horario']?>" onclick="return confirm('¿Aprobar y publicar este horario?');">Aprobar y Publicar</a>
                            <?php endif; ?>
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
                                        <td style="min-width: 150px;">
                                            <?php if($detalle): ?>
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
                                                <div class="mt-2">
                                                    <?php 
                                                      $pk = $detalle['id_detalle'] ?? ($detalle['id_horario_detalle'] ?? ($detalle['id'] ?? null));
                                                    ?>
                                                    <?php if($pk): ?>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <a class="btn btn-outline-primary" title="Editar"
                                                           href="editar_bloque.php?id=<?=$pk?>&grado=<?=$id_grado?>&seccion=<?=$id_seccion?>">
                                                           <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a class="btn btn-outline-danger" title="Eliminar"
                                                           href="eliminar_bloque.php?id=<?=$pk?>&grado=<?=$id_grado?>&seccion=<?=$id_seccion?>"
                                                           onclick="return confirm('¿Eliminar este bloque?');">
                                                           <i class="fas fa-trash"></i>
                                                        </a>
                                                    </div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted font-italic">-</span>
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

<?php
include('../../admin/layout/parte2.php');
include('../../layout/mensajes.php');
?>


