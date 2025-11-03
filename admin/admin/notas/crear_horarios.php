<?php
// Incluir configuraciones y procesar todo ANTES de cualquier output
include('../../app/config.php');
require_once('../../app/controllers/horarios/ValidadorHorarios.php');

// 1. Obtener datos necesarios de la base de datos

// Obtener periodo académico activo
$gestion_activa = $pdo->query("SELECT * FROM gestiones WHERE estado = 1 ORDER BY desde DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);

// Obtener datos para formulario
$grados = $pdo->query(
    "SELECT * FROM grados
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
// Pre-cargar secciones para mostrar opciones iniciales (y como fallback si falla AJAX)
// - Filtrar por gestión activa si existe la columna id_gestion
// - Normalizar turnos a M/T
$colsSecciones = $pdo->query("SHOW COLUMNS FROM secciones")->fetchAll(PDO::FETCH_COLUMN);
$hasGestionSecc = in_array('id_gestion', $colsSecciones, true);
$sqlSeccBase = "SELECT id_seccion, nombre_seccion, turno FROM secciones WHERE estado = 1";
if ($hasGestionSecc && $gestion_activa) {
    $sqlSeccBase .= " AND id_gestion = " . (int)$gestion_activa['id_gestion'];
}
$rowsSecc = $pdo->query($sqlSeccBase)->fetchAll(PDO::FETCH_ASSOC);
// Si no hay en la gestión activa, tomar sin filtro de gestión
if (!$rowsSecc) {
    $rowsSecc = $pdo->query("SELECT id_seccion, nombre_seccion, turno FROM secciones WHERE estado = 1")->fetchAll(PDO::FETCH_ASSOC);
}
// Normalizar y ordenar A(M), A(T), ...
$map = [];
foreach ($rowsSecc as $r) {
    $letra = strtoupper(trim($r['nombre_seccion']));
    $t = strtoupper(trim((string)($r['turno'] ?? '')));
    if (in_array($t, ['M','MAÑANA','MANANA','MATUTINO','AM','MORNING'])) { $t = 'M'; }
    elseif (in_array($t, ['T','TARDE','VESPERTINO','PM','AFTERNOON'])) { $t = 'T'; } else { continue; }
    if (!isset($map[$letra][$t])) { $map[$letra][$t] = (int)$r['id_seccion']; }
}
$ordenLetras = ['A','B','C','D','E','F','G','H','I','J','K','L','M'];
$orderedSecc = [];
foreach ($ordenLetras as $L) {
    if (isset($map[$L]['M'])) $orderedSecc[] = [$L,'M',$map[$L]['M']];
    if (isset($map[$L]['T'])) $orderedSecc[] = [$L,'T',$map[$L]['T']];
}
$otras = array_diff(array_keys($map), $ordenLetras);
sort($otras);
foreach ($otras as $L) {
    if (isset($map[$L]['M'])) $orderedSecc[] = [$L,'M',$map[$L]['M']];
    if (isset($map[$L]['T'])) $orderedSecc[] = [$L,'T',$map[$L]['T']];
}

// Obtener materias con información de asociación a grados (incluye grados_materias)
$materias = $pdo->query("
    SELECT DISTINCT m.*, 
           GROUP_CONCAT(DISTINCT gm.id_grado) as grados_asociados
    FROM materias m
    LEFT JOIN grados_materias gm ON m.id_materia = gm.id_materia
    WHERE m.estado = 1
    GROUP BY m.id_materia
    ORDER BY m.nombre_materia
")->fetchAll(PDO::FETCH_ASSOC);
$profesores = $pdo->query("SELECT * FROM profesores WHERE estado = 1 ORDER BY apellidos, nombres")->fetchAll(PDO::FETCH_ASSOC);

// Helper: asegurar columnas para flujo de aprobación/publicación si faltan
// ------------------------------------------------------------------------
try {
    $cols = $pdo->query("SHOW COLUMNS FROM horarios")->fetchAll(PDO::FETCH_COLUMN);
    $needEstado = !in_array('estado', $cols);
    $needAprobadoPor = !in_array('aprobado_por', $cols);
    $needAprobadoEn = !in_array('aprobado_en', $cols);
    if ($needEstado) {
        $pdo->exec("ALTER TABLE horarios ADD COLUMN estado VARCHAR(20) NOT NULL DEFAULT 'BORRADOR'");
    }
    if ($needAprobadoPor) {
        $pdo->exec("ALTER TABLE horarios ADD COLUMN aprobado_por INT NULL");
    }
    if ($needAprobadoEn) {
        $pdo->exec("ALTER TABLE horarios ADD COLUMN aprobado_en DATETIME NULL");
    }
} catch (Exception $e) {
    // Silencioso: si no hay permisos, el sistema continúa sin interrumpir la creación
}

// 2. Procesar el formulario si se envió
// ------------------------------------
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['grado'])) {
    if (session_status() === PHP_SESSION_NONE) session_start();
    
    // Instanciar validador completo
    $validador = new ValidadorHorarios($pdo);
    
    // Asegurar que la sección seleccionada corresponde al grado elegido
    // Si el usuario eligió una sección global (mismo nombre/turno pero de otro grado),
    // la remapeamos a la id_seccion del grado actual.
    try {
        $gradoSeleccionado = isset($_POST['grado']) ? (int)$_POST['grado'] : 0;
        $seccionSeleccionada = isset($_POST['seccion']) ? (int)$_POST['seccion'] : 0;
        if ($gradoSeleccionado > 0 && $seccionSeleccionada > 0) {
            $stmtSeccSel = $pdo->prepare("SELECT id_grado, nombre_seccion, turno FROM secciones WHERE id_seccion = ?");
            $stmtSeccSel->execute([$seccionSeleccionada]);
            $seccSel = $stmtSeccSel->fetch(PDO::FETCH_ASSOC);

            if ($seccSel) {
                $turnoRaw = strtoupper(trim((string)$seccSel['turno']));
                $turnoNorm = in_array($turnoRaw, ['M','MAÑANA','MANANA','MATUTINO','AM','MORNING']) ? 'M' : (in_array($turnoRaw, ['T','TARDE','VESPERTINO','PM','AFTERNOON']) ? 'T' : '');

                if ((int)$seccSel['id_grado'] !== $gradoSeleccionado && $turnoNorm !== '') {
                    // Buscar una sección con el mismo nombre y turno en el grado seleccionado
                    $sqlBuscar = "SELECT id_seccion, turno FROM secciones WHERE id_grado = :g AND nombre_seccion = :n AND estado = 1";
                    // Preferir gestión activa si existe la columna
                    $colsSecc = $pdo->query("SHOW COLUMNS FROM secciones")->fetchAll(PDO::FETCH_COLUMN);
                    $hasGestionCol = in_array('id_gestion', $colsSecc, true);
                    if ($hasGestionCol && isset($gestion_activa['id_gestion'])) {
                        $sqlBuscar .= " AND id_gestion = :idg";
                    }
                    $stmtBuscar = $pdo->prepare($sqlBuscar);
                    $stmtBuscar->bindValue(':g', $gradoSeleccionado, PDO::PARAM_INT);
                    $stmtBuscar->bindValue(':n', $seccSel['nombre_seccion']);
                    if (isset($gestion_activa['id_gestion']) && $hasGestionCol) {
                        $stmtBuscar->bindValue(':idg', (int)$gestion_activa['id_gestion'], PDO::PARAM_INT);
                    }
                    $stmtBuscar->execute();
                    $candidatas = $stmtBuscar->fetchAll(PDO::FETCH_ASSOC);

                    $idRemapeada = 0;
                    foreach ($candidatas as $c) {
                        $tRaw = strtoupper(trim((string)$c['turno']));
                        $tNorm = in_array($tRaw, ['M','MAÑANA','MANANA','MATUTINO','AM','MORNING']) ? 'M' : (in_array($tRaw, ['T','TARDE','VESPERTINO','PM','AFTERNOON']) ? 'T' : '');
                        if ($tNorm === $turnoNorm) {
                            $idRemapeada = (int)$c['id_seccion'];
                            break;
                        }
                    }

                    // Si no encontramos en la gestión activa, intentar sin filtro de gestión
                    if ($idRemapeada === 0) {
                        $stmtBuscar = $pdo->prepare("SELECT id_seccion, turno FROM secciones WHERE id_grado = :g AND nombre_seccion = :n AND estado = 1");
                        $stmtBuscar->bindValue(':g', $gradoSeleccionado, PDO::PARAM_INT);
                        $stmtBuscar->bindValue(':n', $seccSel['nombre_seccion']);
                        $stmtBuscar->execute();
                        $candidatas = $stmtBuscar->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($candidatas as $c) {
                            $tRaw = strtoupper(trim((string)$c['turno']));
                            $tNorm = in_array($tRaw, ['M','MAÑANA','MANANA','MATUTINO','AM','MORNING']) ? 'M' : (in_array($tRaw, ['T','TARDE','VESPERTINO','PM','AFTERNOON']) ? 'T' : '');
                            if ($tNorm === $turnoNorm) {
                                $idRemapeada = (int)$c['id_seccion'];
                                break;
                            }
                        }
                    }

                    if ($idRemapeada > 0) {
                        $_POST['seccion'] = (string)$idRemapeada; // Remapear antes de validar
                    }
                }
            }
        }
    } catch (Throwable $e) {
        // No interrumpir el flujo si algo falla; el validador mostrará el error correspondiente
    }

    // Validar horario completo con todas las validaciones
    $horario_data = $_POST['horario'] ?? [];
    $es_valido = $validador->validarHorarioCompleto(
        $gestion_activa['id_gestion'],
        $_POST['grado'],
        $_POST['seccion'],
        $horario_data,
        $_POST['aula'] ?? null,
        null // id_horario_excluir (null para nuevos horarios)
    );
    
    if (!$es_valido) {
        // Mostrar todos los errores de validación
        $errores = $validador->getErrores();
        $_SESSION['mensaje'] = '<strong>Errores de validación detectados:</strong><br>' . 
                                implode('<br>• ', $errores);
        $_SESSION['icono'] = 'error';
        ?><script>window.history.back();</script><?php
        exit;
    }
    // Insertar horario principal
    $stmt = $pdo->prepare("INSERT INTO horarios 
                          (id_gestion, id_grado, id_seccion, aula, fecha_inicio, fecha_fin) 
                          VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $gestion_activa['id_gestion'],
        $_POST['grado'],
        $_POST['seccion'],
        $_POST['aula'] ?? null,
        $_POST['fecha_inicio'] ?? null,
        $_POST['fecha_fin'] ?? null
    ]);
    $id_horario = $pdo->lastInsertId();

    // Insertar detalles del horario (si existen)
    if (!empty($_POST['horario'])) {
        $stmt_detalle = $pdo->prepare("INSERT INTO horario_detalle 
                                      (id_horario, dia_semana, hora_inicio, hora_fin, id_materia, id_profesor) 
                                      VALUES (?, ?, ?, ?, ?, ?)");
        
        foreach ($_POST['horario'] as $dia => $bloques) {
            foreach ($bloques as $hora_inicio => $bloque) {
                // Solo insertar si hay materia seleccionada (valor no vacío)
                $id_materia = !empty($bloque['materia']) ? (int)$bloque['materia'] : null;
                $id_profesor = !empty($bloque['profesor']) ? (int)$bloque['profesor'] : null;
                
                if ($id_materia && $id_materia > 0) {
                    // Si profesor está vacío o es 0, usar NULL
                    if (!$id_profesor || $id_profesor <= 0) {
                        $id_profesor = null;
                    }
                    
                    // Normalizar horas al formato TIME completo (HH:MM:SS)
                    $hora_inicio = $bloque['hora_inicio'] ?? '';
                    $hora_fin = $bloque['hora_fin'] ?? '';
                    
                    // Si no tiene segundos, agregarlos
                    if (strlen($hora_inicio) == 5) {
                        $hora_inicio .= ':00';
                    }
                    if (strlen($hora_fin) == 5) {
                        $hora_fin .= ':00';
                    }
                    
                    $stmt_detalle->execute([
                        $id_horario,
                        $dia,
                        $hora_inicio,
                        $hora_fin,
                        $id_materia,
                        $id_profesor
                    ]);
                }
            }
        }
    }

    // Guardar en sesión para mostrar mensaje
    $_SESSION['mensaje'] = 'Horario creado exitosamente. Puedes verlo en "Horarios Consolidados" o descargar el PDF.';
    $_SESSION['icono'] = 'success';
    
    // Redirigir a vista consolidada con los parámetros
    header("Location: horarios_consolidados.php?grado=" . $_POST['grado'] . "&seccion=" . $_POST['seccion']);
    exit();
}

// 3. Incluir cabecera HTML (después de todo el procesamiento)
// ----------------------------------------------------------
include('../../admin/layout/parte1.php');
?>

<!-- Contenido HTML -->
<div class="content-wrapper">
    <div class="content">
        <div class="container-fluid">
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h3 class="m-0">Creación de Horarios Escolares</h3>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="#">Inicio</a></li>
                                <li class="breadcrumb-item"><a href="<?= APP_URL ?>/admin/reportes">Reportes</a></li>
                                <li class="breadcrumb-item active">Horarios Escolares</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Formulario Principal -->
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Configuración del Horario</h3>
                </div>
                <form method="POST">
                    <div class="card-body">
                        <!-- Sección de Configuración Básica -->
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Período Académico</label>
                                    <input type="text" class="form-control" readonly 
                                           value="<?= $gestion_activa ? 'Desde: '.date('d/m/Y', strtotime($gestion_activa['desde'])).' Hasta: '.date('d/m/Y', strtotime($gestion_activa['hasta'])) : 'No hay período activo' ?>">
                                    <?php if($gestion_activa): ?>
                                    <input type="hidden" name="id_gestion" value="<?= $gestion_activa['id_gestion'] ?>">
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="grado">Grado</label>
                                    <select id="grado" name="grado" class="form-control" required>
                                        <option value="">Seleccionar Grado</option>
                                        <?php foreach($grados as $g): ?>
                                        <option value="<?= $g['id_grado'] ?>"><?= htmlspecialchars($g['grado']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="seccion">Sección/Turno</label>
                                    <select id="seccion" name="seccion" class="form-control" required>
                                        <option value="">Seleccionar Sección</option>
                                        <?php foreach($orderedSecc as $it): ?>
                                            <option value="<?= htmlspecialchars($it[2]) ?>"><?= htmlspecialchars($it[0] . ' (' . $it[1] . ')') ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Información Adicional -->
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Aula</label>
                                    <input type="text" name="aula" class="form-control" placeholder="Ej: Aula 101">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Fecha Inicio</label>
                                    <input type="date" name="fecha_inicio" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Fecha Fin</label>
                                    <input type="date" name="fecha_fin" class="form-control">
                                </div>
                            </div>
                        </div>

                        <!-- Horario Semanal -->
                        <div class="table-responsive mt-4">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Hora</th>
                                        <th>Lunes</th>
                                        <th>Martes</th>
                                        <th>Miércoles</th>
                                        <th>Jueves</th>
                                        <th>Viernes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $horarios = [
                                        ['07:50', '08:30'], ['08:30', '09:10'], ['09:10', '09:50'],
                                        ['10:10', '10:50'], ['10:50', '11:30'], ['11:30', '12:10']
                                    ];
                                    
                                    foreach($horarios as $bloque): 
                                        list($inicio, $fin) = $bloque;
                                    ?>
                                    <tr>
                                        <td><?= "$inicio - $fin" ?></td>
                                        <?php foreach(['Lunes','Martes','Miércoles','Jueves','Viernes'] as $dia): ?>
                                        <td>
                                            <select name="horario[<?= $dia ?>][<?= $inicio ?>][materia]" class="form-control mb-2 materia-select" data-grado="<?= $g['id_grado'] ?? '' ?>" style="font-size: 12px;">
                                                <option value="" data-placeholder="true">-- Materia --</option>
                                                <?php foreach($materias as $m): 
                                                    // Obtener grados asociados (del id_grado directo + grados_materias)
                                                    $grados_mat = [$m['id_grado']];
                                                    if (!empty($m['grados_asociados'])) {
                                                        $grados_mat = array_merge($grados_mat, explode(',', $m['grados_asociados']));
                                                    }
                                                    $grados_mat_str = implode(',', array_filter($grados_mat));
                                                ?>
                                                <option value="<?= $m['id_materia'] ?>" 
                                                        data-grado-mat="<?= $m['id_grado'] ?>"
                                                        data-grados-asociados="<?= $grados_mat_str ?>">
                                                    <?= htmlspecialchars($m['nombre_materia']) ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <select name="horario[<?= $dia ?>][<?= $inicio ?>][profesor]" class="form-control profesor-select" style="font-size: 12px;"
                                                    data-dia="<?= $dia ?>" data-inicio="<?= $inicio ?>" data-fin="<?= $fin ?>">
                                                <option value="" data-placeholder="true">-- Profesor --</option>
                                                <?php foreach($profesores as $p): ?>
                                                <option value="<?= $p['id_profesor'] ?>">
                                                    <?= htmlspecialchars($p['nombres'].' '.$p['apellidos']) ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <input type="hidden" name="horario[<?= $dia ?>][<?= $inicio ?>][hora_inicio]" value="<?= $inicio ?>">
                                            <input type="hidden" name="horario[<?= $inicio ?>][<?= $dia ?>][hora_fin]" value="<?= $fin ?>">
                                        </td>
                                        <?php endforeach; ?>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar horario
                        </button>
                        <button type="button" id="previsualizar" class="btn btn-success">
                            <i class="fas fa-eye"></i> Previsualizar
                        </button>
                        <button type="button" id="rellenarPrueba" class="btn btn-info">
                            <i class="fas fa-magic"></i> Datos de Prueba
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Previsualización -->
<div class="modal fade" id="previewModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Previsualización del Horario</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <iframe id="previewFrame" style="width:100%;height:500px;border:none;"></iframe>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript -->
<script>
$(function() {
    // Filtrar materias según el grado seleccionado
    function cargarSeccionesDeGrado(gradoId){
        let hayMateriasParaGrado = false;
        
        if (gradoId) {
            // Primero contar cuántas materias hay para este grado
            $('.materia-select').first().find('option').each(function() {
                const $option = $(this);
                if ($option.val() === '') return;
                
                const matGrado = $option.data('grado-mat');
                const gradosAsociados = $option.data('grados-asociados');
                
                // Verificar si la materia pertenece al grado seleccionado
                const perteneceAGrado = (
                    !matGrado || 
                    matGrado == gradoId || 
                    (gradosAsociados && gradosAsociados.includes(gradoId))
                );
                
                if (perteneceAGrado) {
                    hayMateriasParaGrado = true;
                }
            });
            
            // Filtrar materias por grado (o mostrar todas si no hay específicas)
            $('.materia-select').each(function() {
                const $select = $(this);
                const currentValue = $select.val();
                
                $select.find('option').each(function() {
                    const $option = $(this);
                    const matGrado = $option.data('grado-mat');
                    const gradosAsociados = $option.data('grados-asociados');
                    
                    if ($option.val() === '') {
                        return; // Mantener el placeholder
                    }
                    
                    // Verificar si pertenece al grado (o mostrar todas si no hay específicas)
                    const perteneceAGrado = (
                        !matGrado || 
                        matGrado == gradoId || 
                        (gradosAsociados && gradosAsociados.includes(gradoId))
                    );
                    
                    if (!hayMateriasParaGrado || perteneceAGrado) {
                        // Si no hay materias específicas del grado, mostrar todas
                        // O si esta materia pertenece al grado, mostrarla
                        $option.show();
                    } else {
                        $option.hide();
                        // Si el valor seleccionado no coincide con el grado, limpiar selección
                        if (currentValue == $option.val()) {
                            $select.val('').trigger('change');
                        }
                    }
                });
            });
            
            // No recargar Sección/Turno por grado: mostrar lista global pre-cargada
            $('#seccion').prop('disabled', false);
        } else {
            $('.materia-select option').show();
            $('#seccion').prop('disabled', false);
        }
    }

    $('#grado').on('change input click', function() { cargarSeccionesDeGrado($(this).val()); });

    // Inicializar solo filtrado de materias (Sección/Turno ya viene pre-cargado globalmente)
    const gradoInicial = $('#grado').val();
    $('#seccion').prop('disabled', false);
    cargarSeccionesDeGrado(gradoInicial);
    
    // Asegurar que cuando se selecciona una opción, se muestre correctamente
    $('.materia-select, .profesor-select').on('change', function() {
        const $select = $(this);
        const selectedValue = $select.val();
        const $selectedOption = $select.find('option:selected');
        const isPlaceholder = $selectedOption.data('placeholder') === true;
        
        // Cambiar estilo según si hay selección válida o es placeholder
        if (selectedValue && selectedValue !== '' && !isPlaceholder) {
            $select.css({
                'color': '#333',
                'font-weight': 'normal',
                'background-color': '#fff',
                'font-style': 'normal'
            });
            $select.removeClass('text-muted');
        } else {
            // Es placeholder o está vacío
            $select.css({
                'color': '#6c757d',
                'font-style': 'italic'
            });
        }
    });
    
    // Prevenir que el placeholder se envíe como valor válido
    $('form').on('submit', function(e) {
        // Limpiar valores vacíos antes de enviar
        $('.materia-select, .profesor-select').each(function() {
            const $select = $(this);
            const $selectedOption = $select.find('option:selected');
            
            // Si es placeholder, forzar valor vacío
            if ($selectedOption.data('placeholder') === true || $select.val() === '') {
                $select.val('').prop('selectedIndex', 0);
            }
        });
    });
    
    // Inicializar estilos para selects vacíos
    $('.materia-select, .profesor-select').each(function() {
        if (!$(this).val()) {
            $(this).css({
                'color': '#6c757d',
                'font-style': 'italic'
            });
        }
    });
    
    // Rellenar datos de prueba
    $('#rellenarPrueba').click(function() {
        // Seleccionar primer grado y sección válidos
        const firstGrado = $('#grado option').eq(1).val();
        const firstSeccion = $('#seccion option').eq(1).val();
        if (firstGrado) $('#grado').val(firstGrado);
        if (firstSeccion) $('#seccion').val(firstSeccion);
        
        // Establecer fechas
        const hoy = new Date().toISOString().split('T')[0];
        const mesSiguiente = new Date();
        mesSiguiente.setMonth(mesSiguiente.getMonth() + 1);
        const fin = mesSiguiente.toISOString().split('T')[0];
        
        $('[name="aula"]').val('Aula 101');
        $('[name="fecha_inicio"]').val(hoy);
        $('[name="fecha_fin"]').val(fin);
        
        // Rellenar algunos bloques
        const materiaVal = $('.materia-select option:not(:first)').first().val() || '';
        const profesorVal = $('.profesor-select option:not(:first)').first().val() || '';
        // Llenar el primer bloque de cada día con la primera materia/profesor disponibles
        ['Lunes','Martes','Miércoles','Jueves','Viernes'].forEach((dia) => {
            const bloqueMateria = $(`select[name^="horario[${dia}]"]`).first();
            if (bloqueMateria.length) {
                if (materiaVal) bloqueMateria.val(materiaVal);
                const bloqueProfesor = bloqueMateria.next('.profesor-select');
                if (profesorVal && bloqueProfesor.length) bloqueProfesor.val(profesorVal);
            }
        });
        
        toastr.success('Datos de prueba cargados');
    });
    
    // Validación de disponibilidad de profesor al seleccionar
    $(document).on('change', '.profesor-select', function(){
        const $sel = $(this);
        const profId = $sel.val();
        if (!profId) return; // sin profesor => sin validación remota
        const dia  = $sel.data('dia');
        const hi   = $sel.data('inicio');
        const hf   = $sel.data('fin');
        $.get('api_check_profesor.php', { profesor: profId, dia: dia, hi: hi, hf: hf }, function(resp){
            if (resp && resp.ok && resp.ocupado) {
                let msg = 'El profesor ya está asignado en este bloque.';
                if (resp.conflictos && resp.conflictos.length) {
                    const c = resp.conflictos[0];
                    msg += `\n${c.nombre_materia} · ${c.grado} ${c.nombre_seccion} · Aula ${c.aula} · ${c.hora_inicio.substr(0,5)}-${c.hora_fin.substr(0,5)}`;
                }
                toastr.warning(msg);
                $sel.val('');
            }
        }, 'json');
    });

    // Previsualización
    $('#previsualizar').click(function() {
        if($('#grado').val() === '' || $('#seccion').val() === '') {
            toastr.error('Seleccione un grado y sección primero');
            return;
        }
        
        // Guardar datos en sesión vía AJAX antes de mostrar preview
        $.ajax({
            url: 'guardar_preview.php',
            method: 'POST',
            data: $('form').serialize(),
            success: function(response) {
                $('#previewModal').modal('show');
                $('#previewFrame').attr('src', 'previsualizar_horario.php');
            },
            error: function() {
                toastr.error('Error al preparar la previsualización');
            }
        });
    });
    
    // Descargar PDF
    $('#descargarPdf').click(function() {
        window.open('generar_horario_pdf.php?' + $('form').serialize(), '_blank');
    });
});
</script>

<?php
// Incluir pie de página
include('../../admin/layout/parte2.php');
include('../../layout/mensajes.php');
?>