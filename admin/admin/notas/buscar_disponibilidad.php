<?php
include('../../app/config.php');
include('../../admin/layout/parte1.php');

$gestion_activa = $pdo->query("SELECT * FROM gestiones WHERE estado = 1 ORDER BY desde DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
$profesores = $pdo->query("SELECT id_profesor, CONCAT(nombres,' ',apellidos) AS nombre FROM profesores WHERE estado = 1 ORDER BY apellidos, nombres")->fetchAll(PDO::FETCH_ASSOC);

$resultado = [];
$tipo_busqueda = $_GET['tipo'] ?? 'profesor'; // 'profesor' o 'aula'
$profesor_id = isset($_GET['profesor']) ? (int)$_GET['profesor'] : 0;
$aula = $_GET['aula'] ?? '';
$dia = $_GET['dia'] ?? '';
$hora_ini = $_GET['hora_ini'] ?? '';
$hora_fin = $_GET['hora_fin'] ?? '';

if ($gestion_activa && $dia && $hora_ini && $hora_fin) {
    if ($tipo_busqueda === 'profesor' && $profesor_id) {
        // Buscar disponibilidad de profesor
        $stmt = $pdo->prepare(
            "SELECT hd.*, hd.id_horario, hd.dia_semana, hd.hora_inicio, hd.hora_fin,
                    m.nombre_materia, s.nombre_seccion, g.grado, h.aula,
                    h.id_grado, h.id_seccion,
                    CONCAT(p.nombres,' ',p.apellidos) AS profesor_nombre
             FROM horario_detalle hd
             INNER JOIN horarios h ON h.id_horario = hd.id_horario
             INNER JOIN materias m ON m.id_materia = hd.id_materia
             INNER JOIN secciones s ON s.id_seccion = h.id_seccion
             INNER JOIN grados g ON g.id_grado = h.id_grado
             LEFT JOIN profesores p ON p.id_profesor = hd.id_profesor
             WHERE h.id_gestion = :gestion
             AND (
                 hd.id_profesor = :p OR TRIM(LOWER(p.cedula)) = TRIM(LOWER(:cedula))
             )
             AND TRIM(LOWER(hd.dia_semana)) = TRIM(LOWER(:d))
             AND (
                 (
                   -- segundos inicio del bloque en BD
                   (CASE WHEN TIME_TO_SEC(hd.hora_inicio) IS NULL THEN 0 ELSE TIME_TO_SEC(hd.hora_inicio) END) < TIME_TO_SEC(:hf)
                   AND
                   -- segundos fin del bloque en BD, corrigiendo 00:00 con el fin del siguiente tramo
                   (
                     CASE
                       WHEN TIME_TO_SEC(hd.hora_fin) = 0 THEN (
                         CASE LEFT(hd.hora_inicio,5)
                           WHEN '07:50' THEN TIME_TO_SEC('08:30:00')
                           WHEN '08:30' THEN TIME_TO_SEC('09:10:00')
                           WHEN '09:10' THEN TIME_TO_SEC('09:50:00')
                           WHEN '10:10' THEN TIME_TO_SEC('10:50:00')
                           WHEN '10:50' THEN TIME_TO_SEC('11:30:00')
                           WHEN '11:30' THEN TIME_TO_SEC('12:10:00')
                           ELSE 86400
                         END
                       )
                       ELSE TIME_TO_SEC(hd.hora_fin)
                     END
                   ) > TIME_TO_SEC(:hi)
                 )
             )"
        );
        $stmt->execute([
            ':gestion' => $gestion_activa['id_gestion'],
            ':p' => $profesor_id,
            ':cedula' => $pdo->query("SELECT cedula FROM profesores WHERE id_profesor = " . (int)$profesor_id)->fetchColumn(),
            ':d' => $dia,
            ':hf' => $hora_fin . ':00',
            ':hi' => $hora_ini . ':00'
        ]);
        $conflictos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Revisión adicional en PHP por si TIME/formatos en SQL fallan
        if (empty($conflictos)) {
            $stmtAll = $pdo->prepare(
                "SELECT hd.*, hd.hora_inicio, hd.hora_fin, m.nombre_materia, s.nombre_seccion, g.grado, h.aula,
                        h.id_grado, h.id_seccion,
                        CONCAT(p.nombres,' ',p.apellidos) AS profesor_nombre
                 FROM horario_detalle hd
                 INNER JOIN horarios h ON h.id_horario = hd.id_horario
                 INNER JOIN materias m ON m.id_materia = hd.id_materia
                 INNER JOIN secciones s ON s.id_seccion = h.id_seccion
                 INNER JOIN grados g ON g.id_grado = h.id_grado
                 LEFT JOIN profesores p ON p.id_profesor = hd.id_profesor
                 WHERE h.id_gestion = :gestion
                 AND hd.id_profesor = :p 
                 AND TRIM(LOWER(hd.dia_semana)) = TRIM(LOWER(:d))"
            );
            $stmtAll->execute([
                ':gestion' => $gestion_activa['id_gestion'],
                ':p' => $profesor_id,
                ':d' => $dia,
            ]);
            $bloques = $stmtAll->fetchAll(PDO::FETCH_ASSOC);
            // Función para extraer HH:MM de cadenas como "08:30", "8:30", "08:30 AM"
            $toHM = function($s) {
                if (preg_match('/(\d{1,2}):(\d{2})/', $s, $m)) {
                    return [intval($m[1]), intval($m[2])];
                }
                return [0,0];
            };
            list($hiH,$hiM) = $toHM($hora_ini);
            list($hfH,$hfM) = $toHM($hora_fin);
            $inicioMin = $hiH*60 + $hiM;
            $finMin    = $hfH*60 + $hfM;
            foreach ($bloques as $b) {
                list($biH,$biM) = $toHM($b['hora_inicio']);
                list($bfH,$bfM) = $toHM($b['hora_fin']);
                $biMin = $biH*60 + $biM;
                $bfMin = ($bfH==0 && $bfM==0) ? 24*60 : ($bfH*60 + $bfM); // tratar 00:00 como 24:00
                if ($biMin < $finMin && $bfMin > $inicioMin) {
                    $conflictos[] = $b; // solapa
                }
            }
        }

        // Fallback: si no hay registros por id (posible desalineación de gestión o ids),
        // intentar por nombre completo del profesor e ignorar gestión.
        if (empty($conflictos)) {
            // Obtener nombre completo del profesor seleccionado
            $stmtNombre = $pdo->prepare("SELECT CONCAT(TRIM(nombres),' ',TRIM(apellidos)) AS nombre FROM profesores WHERE id_profesor = ?");
            $stmtNombre->execute([$profesor_id]);
            $nombreProf = $stmtNombre->fetchColumn();
            if ($nombreProf) {
                $stmt = $pdo->prepare(
                    "SELECT hd.*, hd.id_horario, hd.dia_semana, hd.hora_inicio, hd.hora_fin,
                            m.nombre_materia, s.nombre_seccion, g.grado, h.aula,
                            h.id_grado, h.id_seccion,
                            CONCAT(p.nombres,' ',p.apellidos) AS profesor_nombre
                     FROM horario_detalle hd
                     INNER JOIN horarios h ON h.id_horario = hd.id_horario
                     INNER JOIN materias m ON m.id_materia = hd.id_materia
                     INNER JOIN secciones s ON s.id_seccion = h.id_seccion
                     INNER JOIN grados g ON g.id_grado = h.id_grado
                     LEFT JOIN profesores p ON p.id_profesor = hd.id_profesor
                     WHERE TRIM(LOWER(CONCAT(p.nombres,' ',p.apellidos))) = TRIM(LOWER(:nombre))
                     AND TRIM(LOWER(hd.dia_semana)) = TRIM(LOWER(:d))
                     AND (
                         (TIME(hd.hora_inicio) < TIME(:hf) AND TIME(hd.hora_fin) > TIME(:hi))
                         OR (LEFT(hd.hora_inicio,5) < :hf5 AND LEFT(hd.hora_fin,5) > :hi5)
                     )"
                );
                $stmt->execute([
                    ':nombre' => $nombreProf,
                    ':d' => $dia,
                    ':hf' => $hora_fin . ':00',
                    ':hi' => $hora_ini . ':00',
                    ':hf5' => $hora_fin,
                    ':hi5' => $hora_ini
                ]);
                $conflictos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        }
        
        $resultado = [
            'tipo' => 'profesor',
            'ocupado' => !empty($conflictos),
            'conflictos' => $conflictos,
            'mensaje' => empty($conflictos) ? 'El profesor está disponible en ese bloque.' : 'El profesor está ocupado en ese bloque.'
        ];
    } elseif ($tipo_busqueda === 'aula' && $aula) {
        // Buscar disponibilidad de aula
        $stmt = $pdo->prepare(
            "SELECT hd.*, h.id_horario, h.aula, hd.dia_semana, hd.hora_inicio, hd.hora_fin,
                    m.nombre_materia, s.nombre_seccion, g.grado, h.id_grado, h.id_seccion,
                    CONCAT(p.nombres, ' ', p.apellidos) AS profesor_nombre
             FROM horarios h
             INNER JOIN horario_detalle hd ON h.id_horario = hd.id_horario
             INNER JOIN materias m ON m.id_materia = hd.id_materia
             INNER JOIN secciones s ON s.id_seccion = h.id_seccion
             INNER JOIN grados g ON g.id_grado = h.id_grado
             LEFT JOIN profesores p ON p.id_profesor = hd.id_profesor
             WHERE h.id_gestion = :gestion
             AND h.aula = :aula
             AND TRIM(LOWER(hd.dia_semana)) = TRIM(LOWER(:d))
             AND (
                 (
                   (CASE WHEN TIME_TO_SEC(hd.hora_inicio) IS NULL THEN 0 ELSE TIME_TO_SEC(hd.hora_inicio) END) < TIME_TO_SEC(:hf)
                   AND (
                     CASE
                       WHEN TIME_TO_SEC(hd.hora_fin) = 0 THEN (
                         CASE LEFT(hd.hora_inicio,5)
                           WHEN '07:50' THEN TIME_TO_SEC('08:30:00')
                           WHEN '08:30' THEN TIME_TO_SEC('09:10:00')
                           WHEN '09:10' THEN TIME_TO_SEC('09:50:00')
                           WHEN '10:10' THEN TIME_TO_SEC('10:50:00')
                           WHEN '10:50' THEN TIME_TO_SEC('11:30:00')
                           WHEN '11:30' THEN TIME_TO_SEC('12:10:00')
                           ELSE 86400
                         END
                       )
                       ELSE TIME_TO_SEC(hd.hora_fin)
                     END
                   ) > TIME_TO_SEC(:hi)
                 )
             )"
        );
        $stmt->execute([
            ':gestion' => $gestion_activa['id_gestion'],
            ':aula' => $aula,
            ':d' => $dia,
            ':hf' => $hora_fin . ':00',
            ':hi' => $hora_ini . ':00'
        ]);
        $conflictos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (empty($conflictos)) {
            $stmtAll = $pdo->prepare(
                "SELECT hd.*, hd.hora_inicio, hd.hora_fin, m.nombre_materia, s.nombre_seccion, g.grado, h.aula,
                        h.id_grado, h.id_seccion
                 FROM horarios h
                 INNER JOIN horario_detalle hd ON h.id_horario = hd.id_horario
                 INNER JOIN materias m ON m.id_materia = hd.id_materia
                 INNER JOIN secciones s ON s.id_seccion = h.id_seccion
                 INNER JOIN grados g ON g.id_grado = h.id_grado
                 LEFT JOIN profesores p ON p.id_profesor = hd.id_profesor
                 WHERE TRIM(LOWER(CONCAT(p.nombres,' ',p.apellidos))) = TRIM(LOWER(:nombre))
                 AND TRIM(LOWER(hd.dia_semana)) = TRIM(LOWER(:d))"
            );
            $stmtAll->execute([
                ':nombre' => $nombreProf,
                ':d' => $dia,
            ]);
            $bloques = $stmtAll->fetchAll(PDO::FETCH_ASSOC);
            list($hiH,$hiM) = $toHM($hora_ini);
            list($hfH,$hfM) = $toHM($hora_fin);
            $inicioMin = $hiH*60 + $hiM;
            $finMin    = $hfH*60 + $hfM;
            foreach ($bloques as $b) {
                list($biH,$biM) = $toHM($b['hora_inicio']);
                list($bfH,$bfM) = $toHM($b['hora_fin']);
                $biMin = $biH*60 + $biM;
                $bfMin = ($bfH==0 && $bfM==0) ? 24*60 : ($bfH*60 + $bfM);
                if ($biMin < $finMin && $bfMin > $inicioMin) {
                    $conflictos[] = $b;
                }
            }
        }
        
        $resultado = [
            'tipo' => 'aula',
            'ocupado' => !empty($conflictos),
            'conflictos' => $conflictos,
            'mensaje' => empty($conflictos) ? 'El aula está disponible en ese bloque.' : 'El aula está ocupada en ese bloque.'
        ];
    }
}
?>

<div class="content-wrapper">
    <div class="content">
        <div class="container-fluid">
            <div class="row mb-3">
                <div class="col-sm-6"><h3>Disponibilidad de Recursos (Profesores y Aulas)</h3></div>
            </div>
            
            <div class="card card-body mb-3">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link <?= $tipo_busqueda=='profesor'?'active':'' ?>" data-toggle="tab" href="#profesor">Profesor</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $tipo_busqueda=='aula'?'active':'' ?>" data-toggle="tab" href="#aula">Aula</a>
                    </li>
                </ul>
                
                <div class="tab-content mt-3">
                    <!-- Tab Profesor -->
                    <div id="profesor" class="tab-pane <?= $tipo_busqueda=='profesor'?'active':'' ?>">
                        <form method="get">
                            <input type="hidden" name="tipo" value="profesor">
                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label>Profesor</label>
                                    <select class="form-control" name="profesor" required>
                                        <option value="">Seleccionar</option>
                                        <?php foreach($profesores as $p): ?>
                                        <option value="<?=$p['id_profesor']?>" <?= $profesor_id==$p['id_profesor']?'selected':'' ?>><?=htmlspecialchars($p['nombre'])?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-md-2">
                                    <label>Día</label>
                                    <select class="form-control" name="dia" required>
                                        <?php foreach(['Lunes','Martes','Miércoles','Jueves','Viernes'] as $d): ?>
                                        <option <?= $dia===$d?'selected':'' ?>><?=$d?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-md-2">
                                    <label>Hora inicio</label>
                                    <input class="form-control" type="time" name="hora_ini" value="<?=htmlspecialchars($hora_ini)?>" required>
                                </div>
                                <div class="form-group col-md-2">
                                    <label>Hora fin</label>
                                    <input class="form-control" type="time" name="hora_fin" value="<?=htmlspecialchars($hora_fin)?>" required>
                                </div>
                                <div class="form-group col-md-2 align-self-end">
                                    <button class="btn btn-primary" type="submit">Buscar</button>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Tab Aula -->
                    <div id="aula" class="tab-pane <?= $tipo_busqueda=='aula'?'active':'' ?>">
                        <form method="get">
                            <input type="hidden" name="tipo" value="aula">
                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label>Aula</label>
                                    <input class="form-control" type="text" name="aula" value="<?=htmlspecialchars($aula)?>" placeholder="Ej: Aula 101" required>
                                </div>
                                <div class="form-group col-md-2">
                                    <label>Día</label>
                                    <select class="form-control" name="dia" required>
                                        <?php foreach(['Lunes','Martes','Miércoles','Jueves','Viernes'] as $d): ?>
                                        <option <?= $dia===$d?'selected':'' ?>><?=$d?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-md-2">
                                    <label>Hora inicio</label>
                                    <input class="form-control" type="time" name="hora_ini" value="<?=htmlspecialchars($hora_ini)?>" required>
                                </div>
                                <div class="form-group col-md-2">
                                    <label>Hora fin</label>
                                    <input class="form-control" type="time" name="hora_fin" value="<?=htmlspecialchars($hora_fin)?>" required>
                                </div>
                                <div class="form-group col-md-2 align-self-end">
                                    <button class="btn btn-primary" type="submit">Buscar</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <?php if(isset($_GET['debug'])): ?>
                <?php
                // Panel de diagnóstico para entender por qué no aparecen conflictos
                try {
                    $dbg = [];
                    // Datos base profesor
                    $stmtP = $pdo->prepare("SELECT id_profesor, cedula, CONCAT(nombres,' ',apellidos) AS nombre FROM profesores WHERE id_profesor = ?");
                    $stmtP->execute([$profesor_id]);
                    $dbg['profesor'] = $stmtP->fetch(PDO::FETCH_ASSOC);

                    // Registros crudos que se solapan (ignorando gestión) por id_profesor
                    $stmtDbg = $pdo->prepare(
                        "SELECT hd.id_horario, hd.dia_semana, hd.hora_inicio, hd.hora_fin,
                                m.nombre_materia, s.nombre_seccion, h.aula
                         FROM horario_detalle hd
                         INNER JOIN horarios h ON h.id_horario = hd.id_horario
                         INNER JOIN materias m ON m.id_materia = hd.id_materia
                         INNER JOIN secciones s ON s.id_seccion = h.id_seccion
                         LEFT JOIN profesores p ON p.id_profesor = hd.id_profesor
                         WHERE (hd.id_profesor = :p OR TRIM(LOWER(p.cedula)) = TRIM(LOWER(:cedula)))
                         AND TRIM(LOWER(hd.dia_semana)) = TRIM(LOWER(:d))
                         AND (
                             (TIME(hd.hora_inicio) < TIME(:hf) AND TIME(hd.hora_fin) > TIME(:hi))
                             OR (LEFT(hd.hora_inicio,5) < :hf5 AND LEFT(hd.hora_fin,5) > :hi5)
                         )"
                    );
                    $stmtDbg->execute([
                        ':p' => $profesor_id,
                        ':cedula' => $dbg['profesor']['cedula'] ?? null,
                        ':d' => $dia,
                        ':hf' => $hora_fin . ':00',
                        ':hi' => $hora_ini . ':00',
                        ':hf5' => $hora_fin,
                        ':hi5' => $hora_ini
                    ]);
                    $dbg['solapes_por_id'] = $stmtDbg->fetchAll(PDO::FETCH_ASSOC);

                    // Muestra todos los bloques del profesor (por id) para ver qué hay en BD
                    $stmtAllId = $pdo->prepare(
                        "SELECT hd.dia_semana, hd.hora_inicio, hd.hora_fin, m.nombre_materia, s.nombre_seccion
                         FROM horario_detalle hd
                         INNER JOIN horarios h ON h.id_horario = hd.id_horario
                         INNER JOIN materias m ON m.id_materia = hd.id_materia
                         INNER JOIN secciones s ON s.id_seccion = h.id_seccion
                         WHERE hd.id_profesor = :p
                         ORDER BY FIELD(hd.dia_semana,'Lunes','Martes','Miércoles','Jueves','Viernes'), hd.hora_inicio
                         LIMIT 20"
                    );
                    $stmtAllId->execute([':p'=>$profesor_id]);
                    $dbg['bloques_profesor_id_sample'] = $stmtAllId->fetchAll(PDO::FETCH_ASSOC);

                    // Registros crudos por nombre (ignorando gestión) 
                    if (!empty($dbg['profesor']['nombre'])) {
                        $stmtDbg2 = $pdo->prepare(
                            "SELECT hd.id_horario, hd.dia_semana, hd.hora_inicio, hd.hora_fin,
                                    m.nombre_materia, s.nombre_seccion, h.aula,
                                    CONCAT(p.nombres,' ',p.apellidos) AS profesor
                             FROM horario_detalle hd
                             INNER JOIN horarios h ON h.id_horario = hd.id_horario
                             INNER JOIN materias m ON m.id_materia = hd.id_materia
                             INNER JOIN secciones s ON s.id_seccion = h.id_seccion
                             LEFT JOIN profesores p ON p.id_profesor = hd.id_profesor
                             WHERE TRIM(LOWER(CONCAT(p.nombres,' ',p.apellidos))) = TRIM(LOWER(:nombre))
                             AND TRIM(LOWER(hd.dia_semana)) = TRIM(LOWER(:d))
                             AND (
                                 (TIME(hd.hora_inicio) < TIME(:hf) AND TIME(hd.hora_fin) > TIME(:hi))
                                 OR (LEFT(hd.hora_inicio,5) < :hf5 AND LEFT(hd.hora_fin,5) > :hi5)
                             )"
                        );
                        $stmtDbg2->execute([
                            ':nombre' => $dbg['profesor']['nombre'],
                            ':d' => $dia,
                            ':hf' => $hora_fin . ':00',
                            ':hi' => $hora_ini . ':00',
                            ':hf5' => $hora_fin,
                            ':hi5' => $hora_ini
                        ]);
                        $dbg['solapes_por_nombre'] = $stmtDbg2->fetchAll(PDO::FETCH_ASSOC);

                        $stmtAllNm = $pdo->prepare(
                            "SELECT hd.dia_semana, hd.hora_inicio, hd.hora_fin, m.nombre_materia, s.nombre_seccion
                             FROM horario_detalle hd
                             INNER JOIN horarios h ON h.id_horario = hd.id_horario
                             INNER JOIN materias m ON m.id_materia = hd.id_materia
                             INNER JOIN secciones s ON s.id_seccion = h.id_seccion
                             LEFT JOIN profesores p ON p.id_profesor = hd.id_profesor
                             WHERE TRIM(LOWER(CONCAT(p.nombres,' ',p.apellidos))) = TRIM(LOWER(:nombre))
                             ORDER BY FIELD(hd.dia_semana,'Lunes','Martes','Miércoles','Jueves','Viernes'), hd.hora_inicio
                             LIMIT 20"
                        );
                        $stmtAllNm->execute([':nombre'=>$dbg['profesor']['nombre']]);
                        $dbg['bloques_profesor_nombre_sample'] = $stmtAllNm->fetchAll(PDO::FETCH_ASSOC);
                    }
                } catch (Throwable $e) {
                    $dbg = ['error' => $e->getMessage()];
                }
                ?>
                <div class="alert alert-secondary">
                    <small><strong>Debug</strong> (quitar &debug=1 para ocultar)</small>
                    <pre class="mb-0" style="white-space:pre-wrap;word-break:break-word;max-height:260px;overflow:auto;">
<?=htmlspecialchars(json_encode($dbg, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE))?>
                    </pre>
                </div>
            <?php endif; ?>

            <?php if($resultado): ?>
                <div class="alert alert-<?= $resultado['ocupado']?'danger':'success' ?>">
                    <strong><?=htmlspecialchars($resultado['mensaje'])?></strong>
                    
                    <?php if(!empty($resultado['conflictos'])): ?>
                        <hr>
                        <h6>Conflictos encontrados:</h6>
                        <ul class="mb-0">
                            <?php foreach($resultado['conflictos'] as $conflicto): ?>
                                <li>
                                    <?php if($resultado['tipo'] === 'profesor'): ?>
                                        Materia: <strong><?=htmlspecialchars($conflicto['nombre_materia'])?></strong> | 
                                        Profesor: <?=htmlspecialchars($conflicto['profesor_nombre'] ?? 'Sin asignar')?> | 
                                        Grado: <?=htmlspecialchars($conflicto['grado'] ?? '-')?> | 
                                        Sección: <?=htmlspecialchars($conflicto['nombre_seccion'])?> | 
                                        Aula: <?=htmlspecialchars($conflicto['aula'])?> | 
                                        Horario: <?=substr($conflicto['hora_inicio'],0,5)?> - <?=substr($conflicto['hora_fin'],0,5)?>
                                        <?php 
                                          $pk = $conflicto['id_detalle'] ?? ($conflicto['id_horario_detalle'] ?? ($conflicto['id'] ?? null));
                                        ?>
                                        <?php if($pk && !empty($conflicto['id_grado']) && !empty($conflicto['id_seccion'])): ?>
                                            | <a class="badge badge-primary" style="padding:4px 8px;border-radius:12px;" href="editar_bloque.php?id=<?=$pk?>&grado=<?=$conflicto['id_grado']?>&seccion=<?=$conflicto['id_seccion']?>">Editar</a>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        Materia: <strong><?=htmlspecialchars($conflicto['nombre_materia'])?></strong> | 
                                        Profesor: <?=htmlspecialchars($conflicto['profesor_nombre'] ?? 'Sin asignar')?> | 
                                        Grado: <?=htmlspecialchars($conflicto['grado'] ?? '-')?> | 
                                        Sección: <?=htmlspecialchars($conflicto['nombre_seccion'])?> | 
                                        Horario: <?=substr($conflicto['hora_inicio'],0,5)?> - <?=substr($conflicto['hora_fin'],0,5)?>
                                        <?php 
                                          $pk = $conflicto['id_detalle'] ?? ($conflicto['id_horario_detalle'] ?? ($conflicto['id'] ?? null));
                                        ?>
                                        <?php if($pk && !empty($conflicto['id_grado']) && !empty($conflicto['id_seccion'])): ?>
                                            | <a class="badge badge-primary" style="padding:4px 8px;border-radius:12px;" href="editar_bloque.php?id=<?=$pk?>&grado=<?=$conflicto['id_grado']?>&seccion=<?=$conflicto['id_seccion']?>">Editar</a>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
include('../../admin/layout/parte2.php');
include('../../layout/mensajes.php');
?>


