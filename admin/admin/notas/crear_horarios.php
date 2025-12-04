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
    
    // Solo bloquear si hay ERRORES (no advertencias)
    if (!$es_valido) {
        // Mostrar todos los errores de validación
        $errores = $validador->getErrores();
        $_SESSION['mensaje'] = '<strong>Errores de validación detectados:</strong><br>' . 
                                implode('<br>• ', $errores);
        $_SESSION['icono'] = 'error';
        ?><script>window.history.back();</script><?php
        exit;
    }
    
    // Si hay advertencias, guardarlas para mostrarlas después de guardar
    $advertencias = $validador->getAdvertencias();
    // Insertar horario principal (con valores por defecto para campos ocultos)
    // Nota: aula, fecha_inicio y fecha_fin son NOT NULL en la BD, por lo que usamos valores por defecto
    $aula = ''; // Cadena vacía para aula
    $fecha_inicio = date('Y-m-d'); // Fecha actual como fecha_inicio
    $fecha_fin = date('Y-m-d', strtotime('+1 year')); // Un año después como fecha_fin
    
    $stmt = $pdo->prepare("INSERT INTO horarios 
                          (id_gestion, id_grado, id_seccion, aula, fecha_inicio, fecha_fin) 
                          VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $gestion_activa['id_gestion'],
        $_POST['grado'],
        $_POST['seccion'],
        $aula,
        $fecha_inicio,
        $fecha_fin
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
    if (!empty($advertencias)) {
        // Si hay advertencias, mostrarlas junto con el mensaje de éxito
        $_SESSION['mensaje'] = 'Horario creado exitosamente. Puedes verlo en "Horarios Consolidados" o descargar el PDF.<br><br>' .
                               '<strong>Advertencias:</strong><br>' . 
                               implode('<br>• ', $advertencias);
        $_SESSION['icono'] = 'warning'; // Icono de advertencia en lugar de éxito
    } else {
        $_SESSION['mensaje'] = 'Horario creado exitosamente. Puedes verlo en "Horarios Consolidados" o descargar el PDF.';
        $_SESSION['icono'] = 'success';
    }
    
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
                                    <select id="seccion" name="seccion" class="form-control" required disabled>
                                        <option value="">Primero seleccione un Grado</option>
                                    </select>
                                    <small class="form-text text-muted" id="seccion-help">Las secciones se cargarán según el grado seleccionado</small>
                                </div>
                            </div>
                        </div>

                        <!-- Información Adicional -->
                        <!-- Campos de Aula, Fecha Inicio y Fecha Fin ocultos según solicitud del usuario -->

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
                                            <select name="horario[<?= $dia ?>][<?= $inicio ?>][materia]" 
                                                    class="form-control mb-2 materia-select" 
                                                    data-grado="<?= $g['id_grado'] ?? '' ?>" 
                                                    data-profesor=""
                                                    data-seccion=""
                                                    data-dia="<?= $dia ?>"
                                                    data-inicio="<?= $inicio ?>"
                                                    style="font-size: 12px;"
                                                    disabled>
                                                <option value="" data-placeholder="true">-- Seleccione primero Profesor --</option>
                                            </select>
                                            <select name="horario[<?= $dia ?>][<?= $inicio ?>][profesor]" 
                                                    class="form-control profesor-select" 
                                                    style="font-size: 12px;"
                                                    data-dia="<?= $dia ?>" 
                                                    data-inicio="<?= $inicio ?>" 
                                                    data-fin="<?= $fin ?>"
                                                    data-seccion=""
                                                    disabled>
                                                <option value="" data-placeholder="true">-- Seleccione primero Sección --</option>
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
                        <button type="button" id="rellenarPrueba" class="btn btn-info">
                            <i class="fas fa-magic"></i> Datos de Prueba
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- JavaScript -->
<script>
// Función helper para mostrar notificaciones con SweetAlert2
function mostrarNotificacion(icono, titulo, mensaje, tiempo = 3000) {
    Swal.fire({
        position: "top-end",
        icon: icono,
        title: titulo,
        text: mensaje,
        showConfirmButton: false,
        timer: tiempo,
        toast: true
    });
}

$(function() {
    // ============================================================
    // VALIDACIONES JERÁRQUICAS PARA HORARIOS
    // ============================================================
    
    let idGradoSeleccionado = null;
    let idSeccionSeleccionada = null;
    
    // ============================================================
    // 1. FILTRO DE SECCIONES POR GRADO
    // ============================================================
    function cargarSeccionesPorGrado(gradoId) {
        // Asegurar que el select existe y es correcto antes de usarlo
        if (!asegurarSelectSeccion()) {
            console.error('No se puede cargar secciones: el campo select no existe');
            return;
        }
        
        const $seccionSelect = $('#seccion');
        const $seccionHelp = $('#seccion-help');
        
        // Verificar que es un select
        if (!$seccionSelect.is('select')) {
            console.error('El campo #seccion no es un select, no se pueden cargar secciones');
            return;
        }
        
        if (!gradoId || gradoId === '') {
            $seccionSelect.html('<option value="">Primero seleccione un Grado</option>').prop('disabled', true);
            if ($seccionHelp.length) {
                $seccionHelp.text('Las secciones se cargarán según el grado seleccionado');
            }
            idGradoSeleccionado = null;
            idSeccionSeleccionada = null;
            limpiarProfesoresYMaterias();
            return;
        }
        
        idGradoSeleccionado = gradoId;
        $seccionSelect.html('<option value="">Cargando secciones...</option>').prop('disabled', true);
        if ($seccionHelp.length) {
            $seccionHelp.text('Cargando secciones del grado seleccionado...');
        }
        
        console.log('Haciendo petición AJAX para cargar secciones. Grado ID:', gradoId);
        $.ajax({
            url: 'ajax/obtener_secciones.php',
            method: 'GET',
            data: { id_grado: gradoId },
            dataType: 'json',
            cache: false,
            success: function(response) {
                console.log('Respuesta recibida de obtener_secciones:', response);
                if (response.success && response.data.length > 0) {
                    let options = '<option value="">Seleccionar Sección</option>';
                    response.data.forEach(function(sec) {
                        options += `<option value="${sec.id}">${sec.nombre}</option>`;
                    });
                    $seccionSelect.html(options).prop('disabled', false);
                    $seccionHelp.text(`Se encontraron ${response.data.length} sección(es) para este grado`);
                } else {
                    $seccionSelect.html('<option value="">No hay secciones disponibles para este grado</option>').prop('disabled', true);
                    $seccionHelp.text('No se encontraron secciones para el grado seleccionado');
                }
                limpiarProfesoresYMaterias();
            },
            error: function(xhr, status, error) {
                console.error('Error AJAX obtener_secciones:', {
                    status: status,
                    error: error,
                    responseText: xhr.responseText,
                    statusCode: xhr.status,
                    url: 'ajax/obtener_secciones.php?id_grado=' + gradoId
                });
                $seccionSelect.html('<option value="">Error al cargar secciones</option>').prop('disabled', true);
                $seccionHelp.text('Error al cargar las secciones. Intente nuevamente. Revisa la consola del navegador.');
                mostrarNotificacion('error', 'Error', 'Error al cargar secciones: ' + error);
                
                // Intentar cargar secciones directamente desde el servidor como fallback
                console.log('Intentando método alternativo...');
            }
        });
    }
    
    // ============================================================
    // 2. FILTRO DE PROFESORES POR SECCIÓN
    // ============================================================
    function cargarProfesoresPorSeccion(seccionId) {
        if (!seccionId || seccionId === '' || !idGradoSeleccionado) {
            limpiarProfesoresYMaterias();
            return;
        }
        
        idSeccionSeleccionada = seccionId;
        
        $.ajax({
            url: 'ajax/obtener_profesores.php',
            method: 'GET',
            data: { id_seccion: seccionId },
            dataType: 'json',
            beforeSend: function() {
                // Mostrar indicador de carga en todos los selects de profesores
                $('.profesor-select').html('<option value="">Cargando profesores...</option>').prop('disabled', true);
            },
            success: function(response) {
                if (response.success) {
                    const profesores = response.data;
                    
                    // Actualizar todos los selects de profesores
                    $('.profesor-select').each(function() {
                const $select = $(this);
                        const valorActual = $select.val();
                        
                        let options = '<option value="" data-placeholder="true">-- Profesor --</option>';
                        profesores.forEach(function(prof) {
                            options += `<option value="${prof.id}">${prof.nombre_completo}</option>`;
                        });
                        
                        $select.html(options);
                        $select.attr('data-seccion', seccionId);
                        
                        // Restaurar valor si aún existe
                        if (valorActual && profesores.some(p => p.id == valorActual)) {
                            $select.val(valorActual);
                        } else {
                            $select.val('');
                        }
                    });
                    
                    if (profesores.length === 0) {
                        mostrarNotificacion('warning', 'Sin profesores', 'No hay profesores asignados a esta sección');
                        $('.profesor-select').prop('disabled', true);
                    } else {
                        mostrarNotificacion('success', 'Profesores cargados', `Se cargaron ${profesores.length} profesor(es) para esta sección`);
                        habilitarProfesores();
                    }
                    
                    // Limpiar materias ya que cambió la sección
                    limpiarMaterias();
                    } else {
                    mostrarNotificacion('error', 'Error', response.message || 'Error al cargar profesores');
                    limpiarProfesoresYMaterias();
                }
            },
            error: function(xhr, status, error) {
                console.error('Error AJAX obtener_profesores:', error, xhr.responseText);
                mostrarNotificacion('error', 'Error', 'Error al cargar profesores: ' + error);
                limpiarProfesoresYMaterias();
            }
        });
    }
    
    // ============================================================
    // 3. FILTRO DE MATERIAS POR PROFESOR Y SECCIÓN
    // ============================================================
    function cargarMateriasPorProfesorYSeccionParaSelect($profSelect, profesorId, seccionId) {
        if (!profesorId || profesorId === '' || !seccionId || seccionId === '') {
            console.warn('cargarMateriasPorProfesorYSeccionParaSelect: parámetros inválidos', {profesorId, seccionId});
            return;
        }
        
        // Encontrar el select de materias asociado a este profesor
        // El materia-select está ANTES del profesor-select en el DOM
        let $matSelect = $profSelect.prev('.materia-select');
        
        // Si no se encuentra con .prev(), intentar buscar por el contenedor padre
        if (!$matSelect.length) {
            const $parent = $profSelect.parent();
            $matSelect = $parent.find('.materia-select').first();
        }
        
        if (!$matSelect.length) {
            console.error('No se encontró el select de materias para este profesor-select');
            console.log('Profesor-select:', $profSelect);
            console.log('Parent:', $profSelect.parent());
            return;
        }
        
        console.log('Cargando materias para profesor:', profesorId, 'sección:', seccionId);
        
        // Mostrar indicador de carga
        $matSelect.html('<option value="">Cargando materias...</option>').prop('disabled', true);
        
        // Obtener el grado seleccionado para filtrar las materias
        const idGrado = $('#grado').val();
        
        if (!idGrado || idGrado === '') {
            console.warn('No se puede cargar materias: no hay grado seleccionado');
            $matSelect.html('<option value="" data-placeholder="true">-- Primero seleccione un Grado --</option>').prop('disabled', true);
            mostrarNotificacion('warning', 'Atención', 'Primero debe seleccionar un Grado');
            return;
        }
        
        $.ajax({
            url: 'ajax/obtener_materias.php',
            method: 'GET',
            data: { 
                id_profesor: profesorId,
                id_seccion: seccionId,
                id_grado: idGrado  // Agregar filtro por grado
            },
            dataType: 'json',
            cache: false,
            success: function(response) {
                console.log('Respuesta AJAX obtener_materias (completa):', response);
                console.log('Tipo de respuesta:', typeof response);
                console.log('response.success:', response ? response.success : 'response es null/undefined');
                
                // Verificar si la respuesta es válida
                if (!response) {
                    console.error('Respuesta vacía o null');
                    $matSelect.html('<option value="" data-placeholder="true">-- Error: Respuesta vacía --</option>').prop('disabled', true);
                    mostrarNotificacion('error', 'Error', 'El servidor no respondió correctamente');
                    return;
                }
                
                // Intentar parsear si es string
                if (typeof response === 'string') {
                    try {
                        response = JSON.parse(response);
                        console.log('Respuesta parseada:', response);
                    } catch (e) {
                        console.error('Error parseando respuesta JSON:', e);
                        $matSelect.html('<option value="" data-placeholder="true">-- Error: Respuesta inválida --</option>').prop('disabled', true);
                        mostrarNotificacion('error', 'Error', 'Error al procesar la respuesta del servidor');
                        return;
                    }
                }
                
                // Verificar si success es true (comparación estricta y flexible)
                const isSuccess = response.success === true || 
                                 response.success === 'true' || 
                                 response.success === 1 ||
                                 (typeof response.success === 'string' && response.success.toLowerCase() === 'true');
                
                console.log('Verificación de success:', {
                    success: response.success,
                    tipo: typeof response.success,
                    isSuccess: isSuccess
                });
                
                if (isSuccess) {
                    const materias = response.data || [];
                    const valorActual = $matSelect.data('last-value') || '';
                    
                    console.log('Materias recibidas:', materias.length, materias);
                    
                    let options = '<option value="" data-placeholder="true">-- Materia --</option>';
                    if (materias.length > 0) {
                        materias.forEach(function(mat) {
                            const gradosAsoc = Array.isArray(mat.grados_asociados) ? mat.grados_asociados : [];
                            options += `<option value="${mat.id}" 
                                data-grado-mat="${mat.id_grado || ''}"
                                data-grados-asociados="${gradosAsoc.join(',')}">${mat.nombre || 'Sin nombre'}</option>`;
                        });
                        $matSelect.prop('disabled', false);
                        console.log(`✅ Se cargaron ${materias.length} materia(s) para este profesor`);
                        mostrarNotificacion('success', 'Materias cargadas', `Se cargaron ${materias.length} materia(s)`);
        } else {
                        options = '<option value="" data-placeholder="true">-- No hay materias asignadas --</option>';
                        $matSelect.prop('disabled', true);
                        mostrarNotificacion('warning', 'Sin materias', 'Este profesor no tiene materias asignadas en esta sección');
                        console.warn('No hay materias para este profesor en esta sección');
                    }
                    
                    $matSelect.html(options);
                    $matSelect.attr('data-profesor', profesorId);
                    $matSelect.attr('data-seccion', seccionId);
                    
                    // Restaurar valor si aún existe
                    if (valorActual && materias.some(m => m.id == valorActual)) {
                        $matSelect.val(valorActual);
        } else {
                        $matSelect.val('');
                    }
                } else {
                    // response.success es false o no existe
                    const errorMsg = response && response.message ? response.message : 'Error desconocido al cargar materias';
                    $matSelect.html('<option value="" data-placeholder="true">-- Error al cargar --</option>').prop('disabled', true);
                    mostrarNotificacion('error', 'Error', errorMsg);
                    console.error('❌ Error en respuesta. success=false o no existe:', {
                        success: response.success,
                        message: response.message,
                        data: response.data,
                        fullResponse: response
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Error AJAX obtener_materias:', {
                    status: status,
                    error: error,
                    statusCode: xhr.status,
                    responseText: xhr.responseText,
                    url: 'ajax/obtener_materias.php?id_profesor=' + profesorId + '&id_seccion=' + seccionId
                });
                
                $matSelect.html('<option value="" data-placeholder="true">-- Error al cargar --</option>').prop('disabled', true);
                
                let errorMsg = 'Error al cargar materias';
                if (xhr.status === 404) {
                    errorMsg = 'El archivo obtener_materias.php no se encontró';
                } else if (xhr.status === 500) {
                    errorMsg = 'Error en el servidor al obtener materias';
                } else if (error) {
                    errorMsg = 'Error: ' + error;
                }
                
                mostrarNotificacion('error', 'Error', errorMsg);
            }
        });
    }
    
    // ============================================================
    // FUNCIONES AUXILIARES
    // ============================================================
    function limpiarProfesoresYMaterias() {
        $('.profesor-select').html('<option value="" data-placeholder="true">-- Seleccione primero Sección --</option>').val('').prop('disabled', true);
        limpiarMaterias();
    }
    
    function limpiarMaterias() {
        $('.materia-select').html('<option value="" data-placeholder="true">-- Seleccione primero Profesor --</option>').val('').prop('disabled', true);
    }
    
    function habilitarProfesores() {
        $('.profesor-select').prop('disabled', false);
    }
    
    function habilitarMaterias() {
        $('.materia-select').prop('disabled', false);
    }
    
    // ============================================================
    // EVENT LISTENERS
    // ============================================================
    
    // El evento change del grado se maneja en inicializarFormulario()
    
    // Cuando cambia la sección
    $('#seccion').on('change', function() {
        const seccionId = $(this).val();
        cargarProfesoresPorSeccion(seccionId);
    });
    
    // Cuando cambia un profesor
    $(document).on('change', '.profesor-select', function() {
        const $profSelect = $(this);
        const profesorId = $profSelect.val();
        const seccionId = idSeccionSeleccionada || $('#seccion').val();
        
        console.log('Profesor cambió:', {profesorId, seccionId});
        
        // Encontrar el select de materias asociado
        let $matSelect = $profSelect.prev('.materia-select');
        if (!$matSelect.length) {
            const $parent = $profSelect.parent();
            $matSelect = $parent.find('.materia-select').first();
        }
        
        // Si no hay profesor seleccionado, limpiar materias
        if (!profesorId || profesorId === '') {
            if ($matSelect.length) {
                $matSelect.html('<option value="" data-placeholder="true">-- Seleccione primero Profesor --</option>').val('').prop('disabled', true);
            }
            return;
        }
        
        // Validar que haya una sección seleccionada
        if (!seccionId || seccionId === '') {
            mostrarNotificacion('warning', 'Atención', 'Primero debe seleccionar una Sección');
            $profSelect.val('');
            if ($matSelect.length) {
                $matSelect.html('<option value="" data-placeholder="true">-- Seleccione primero Sección --</option>').val('').prop('disabled', true);
            }
            return;
        }
        
        // Cargar materias para este profesor específico en esta sección
        cargarMateriasPorProfesorYSeccionParaSelect($profSelect, profesorId, seccionId);
        
        // Validación de disponibilidad existente
        const dia = $profSelect.data('dia');
        const hi = $profSelect.data('inicio');
        const hf = $profSelect.data('fin');
        if (profesorId && dia && hi && hf) {
            // Mostrar indicador de carga mientras se valida
            const $loadingIcon = $('<i class="fas fa-spinner fa-spin" style="margin-left: 5px; color: #007bff;"></i>');
            $profSelect.after($loadingIcon);
            
            // Usar timeout para evitar múltiples peticiones simultáneas
            if ($profSelect.data('validation-timeout')) {
                clearTimeout($profSelect.data('validation-timeout'));
            }
            
            const timeoutId = setTimeout(function() {
                // Log para debugging
                console.log('Validando conflicto:', {
                    profesor: profesorId,
                    dia: dia,
                    hora_inicio: hi,
                    hora_fin: hf
                });
                
                $.ajax({
                    url: 'api_check_profesor.php',
                    method: 'GET',
                    data: { 
                        profesor: profesorId, 
                        dia: dia, 
                        hi: hi, 
                        hf: hf,
                        _: Date.now() // Cache buster
                    },
                    cache: false,
                    timeout: 5000, // Timeout de 5 segundos
                    success: function(resp) {
                        console.log('Respuesta validación:', resp);
                        // Remover indicador de carga
                        $loadingIcon.remove();
                        $profSelect.removeData('validation-timeout');
                        
                if (resp && resp.ok && resp.ocupado) {
                            // Construir mensaje detallado del conflicto (formato exacto como en la imagen)
                            let mensajeDetallado = 'El profesor ya está asignado en este bloque.';
                    if (resp.conflictos && resp.conflictos.length) {
                        const c = resp.conflictos[0];
                                // Formatear hora (sin segundos si los tiene)
                                const horaInicio = c.hora_inicio ? c.hora_inicio.substring(0, 5) : '';
                                // Inferir hora_fin si es inválida (00:00:00 o NULL)
                                let horaFin = c.hora_fin ? c.hora_fin.substring(0, 5) : '';
                                if (!horaFin || horaFin === '00:00') {
                                    const mapaHoras = {
                                        '07:50': '08:30',
                                        '08:30': '09:10',
                                        '09:10': '09:50',
                                        '10:10': '10:50',
                                        '10:50': '11:30',
                                        '11:30': '12:10'
                                    };
                                    horaFin = mapaHoras[horaInicio] || horaFin;
                                }
                                // Obtener nombre del profesor
                                const nombreProfesor = c.profesor_nombre || 'El profesor';
                                // Construir mensaje exacto como en la imagen: "Materia · GRADO SECCIÓN · hora-hora"
                                const materia = c.nombre_materia || 'Materia';
                                const grado = c.grado || '';
                                const seccion = c.nombre_seccion || '';
                                // Formato: "El profesor [Nombre] ya está asignado en este bloque. Matemáticas · CUARTO AÑO B · 08:30-09:10"
                                // Sin campo de aula según solicitud del usuario
                                mensajeDetallado = `El profesor ${nombreProfesor} ya está asignado en este bloque. ${materia} · ${grado} ${seccion} · ${horaInicio}-${horaFin}`;
                            }
                            
                            // Mostrar alerta modal con SweetAlert2 (similar a cuando se guarda)
                            Swal.fire({
                                icon: 'warning',
                                title: 'Conflicto',
                                text: mensajeDetallado,
                                showConfirmButton: true,
                                confirmButtonText: 'Entendido',
                                timer: null, // No cerrar automáticamente
                                allowOutsideClick: true
                            });
                            
                            // Limpiar selección
                    $profSelect.val('');
                    if ($matSelect.length) {
                        $matSelect.html('<option value="" data-placeholder="true">-- Seleccione primero Profesor --</option>').val('').prop('disabled', true);
                    }
                }
                    },
                    error: function(xhr, status, error) {
                        // Remover indicador de carga en caso de error
                        $loadingIcon.remove();
                        $profSelect.removeData('validation-timeout');
                        console.error('Error en validación de conflicto:', error);
                    }
                });
            }, 300); // Debounce de 300ms para evitar peticiones excesivas
            
            $profSelect.data('validation-timeout', timeoutId);
        }
    });

    // ============================================================
    // INICIALIZACIÓN Y ESTILOS
    // ============================================================
    
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
    
    // Inicializar estilos para selects vacíos
    $('.materia-select, .profesor-select').each(function() {
        if (!$(this).val()) {
            $(this).css({
                'color': '#6c757d',
                'font-style': 'italic'
            });
        }
    });
    
    // Función para asegurar que el select de sección existe y es un select, no un input
    function asegurarSelectSeccion() {
        const $seccionField = $('#seccion');
        
        // Verificar si el elemento existe
        if ($seccionField.length === 0) {
            console.error('El campo #seccion no existe en el DOM');
            return false;
        }
        
        // Verificar si es un select, si no lo es, convertirlo
        if ($seccionField.is('input')) {
            console.warn('El campo #seccion es un input, convirtiéndolo a select');
            const $label = $('label[for="seccion"]');
            const $help = $('#seccion-help');
            const $parent = $seccionField.parent();
            const disabled = $seccionField.prop('disabled');
            const value = $seccionField.val();
            
            // Crear nuevo select
            const $newSelect = $('<select>', {
                id: 'seccion',
                name: 'seccion',
                class: 'form-control',
                required: true,
                disabled: disabled
            });
            $newSelect.html('<option value="">Primero seleccione un Grado</option>');
            
            // Reemplazar el input con el select
            $seccionField.replaceWith($newSelect);
            console.log('Campo convertido de input a select');
        }
        
        // Asegurar que es un select
        if (!$('#seccion').is('select')) {
            console.error('El campo #seccion no es un select después de la conversión');
            return false;
        }
        
        return true;
    }
    
    // Función para inicializar cuando la página carga
    function inicializarFormulario() {
        // Primero asegurar que el select existe y es correcto
        if (!asegurarSelectSeccion()) {
            console.error('No se pudo asegurar que el select de sección existe');
            return;
        }
        
        const gradoInicial = $('#grado').val();
        console.log('Inicializando formulario. Grado inicial:', gradoInicial);
        
        if (gradoInicial && gradoInicial !== '') {
            // Hay un grado preseleccionado, cargar secciones
            console.log('Cargando secciones para grado:', gradoInicial);
            cargarSeccionesPorGrado(gradoInicial);
        } else {
            // No hay grado seleccionado, asegurar que el select de sección esté deshabilitado
            $('#seccion').html('<option value="">Primero seleccione un Grado</option>').prop('disabled', true);
        }
    }
    
    // Inicializar cuando el DOM esté listo
    $(document).ready(function() {
        console.log('DOM listo, inicializando formulario...');
        inicializarFormulario();
    });
    
    // También inicializar después de un pequeño delay por si hay otros scripts que interfieren
    setTimeout(function() {
        console.log('Inicialización adicional después de delay...');
        asegurarSelectSeccion();
        const gradoActual = $('#grado').val();
        if (gradoActual && $('#seccion').is(':disabled')) {
            console.log('Reintentando cargar secciones para grado:', gradoActual);
            cargarSeccionesPorGrado(gradoActual);
        }
    }, 500);
    
    // También escuchar cambios manuales del grado
    $('#grado').on('change', function() {
        console.log('Grado cambió a:', $(this).val());
        const gradoId = $(this).val();
        cargarSeccionesPorGrado(gradoId);
    });
    
    // Prevenir que el placeholder se envíe como valor válido
    $('form').on('submit', function(e) {
        e.preventDefault(); // Prevenir envío inmediato
        
        // Validar que se haya seleccionado grado y sección
        if (!$('#grado').val() || !$('#seccion').val()) {
            Swal.fire({
                icon: 'error',
                title: 'Campos requeridos',
                text: 'Debe seleccionar un Grado y una Sección antes de guardar'
            });
            return false;
        }
        
        // Mostrar mensaje de confirmación antes de guardar
        Swal.fire({
            icon: 'question',
            title: '¿Estás seguro?',
            text: '¿Deseas guardar este horario?',
            showCancelButton: true,
            confirmButtonText: 'Sí, guardar',
            cancelButtonText: 'No, cancelar',
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
        // Limpiar valores vacíos antes de enviar
        $('.materia-select, .profesor-select').each(function() {
            const $select = $(this);
            const $selectedOption = $select.find('option:selected');
            
            // Si es placeholder, forzar valor vacío
            if ($selectedOption.data('placeholder') === true || $select.val() === '') {
                $select.val('').prop('selectedIndex', 0);
            }
        });
                
                // Enviar el formulario
                $('form')[0].submit();
            }
            // Si el usuario cancela, no hacer nada (el formulario ya está prevenido)
    });
    
        return false;
    });
    
    // Rellenar datos de prueba con profesores y materias asignadas a la sección
    $('#rellenarPrueba').click(function() {
        const gradoId = $('#grado').val();
        const seccionId = $('#seccion').val();
        
        // Validar que haya grado y sección seleccionados
        if (!gradoId || !seccionId) {
            Swal.fire({
                icon: 'warning',
                title: 'Campos requeridos',
                text: 'Debe seleccionar un Grado y una Sección antes de cargar datos de prueba'
            });
            return;
        }
        
        // Validar que los selects de profesores estén habilitados (sección ya cargada)
        if ($('.profesor-select').first().is(':disabled')) {
            Swal.fire({
                icon: 'warning',
                title: 'Sección no cargada',
                text: 'Primero debe seleccionar una Sección para cargar los profesores disponibles'
            });
            return;
        }
        
        // Mostrar indicador de carga
        Swal.fire({
            title: 'Cargando datos...',
            text: 'Obteniendo profesores y materias asignadas',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        // Obtener profesores de la sección (ya cargados en los selects)
        const profesoresDisponibles = [];
        $('.profesor-select').first().find('option:not([data-placeholder])').each(function() {
            const $option = $(this);
            if ($option.val() && $option.val() !== '') {
                profesoresDisponibles.push({
                    id: $option.val(),
                    nombre: $option.text()
                });
            }
        });
        
        if (profesoresDisponibles.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Sin profesores',
                text: 'No hay profesores disponibles en esta sección'
            });
            return;
        }
        
        // Obtener materias para cada profesor y construir lista de profesores con materias
        const profesoresConMaterias = [];
        let profesoresProcesados = 0;
        
        profesoresDisponibles.forEach(function(profesor) {
        $.ajax({
                url: 'ajax/obtener_materias.php',
                method: 'GET',
                data: {
                    id_profesor: profesor.id,
                    id_seccion: seccionId,
                    id_grado: gradoId
                },
                dataType: 'json',
                success: function(responseMaterias) {
                    if (responseMaterias.success && responseMaterias.data && responseMaterias.data.length > 0) {
                        profesoresConMaterias.push({
                            profesor: profesor,
                            materias: responseMaterias.data
                        });
                    }
                    
                    profesoresProcesados++;
                    
                    // Cuando todos los profesores hayan sido procesados
                    if (profesoresProcesados === profesoresDisponibles.length) {
                        if (profesoresConMaterias.length === 0) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Sin materias',
                                text: 'Los profesores de esta sección no tienen materias asignadas'
                            });
                            return;
                        }
                        
                        // Rellenar bloques aleatoriamente
                        const dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];
                        const horarios = ['07:50', '08:30', '09:10', '10:10', '10:50', '11:30'];
                        
                        // Función para obtener un elemento aleatorio de un array
                        function getRandomElement(arr) {
                            return arr[Math.floor(Math.random() * arr.length)];
                        }
                        
                        // Crear lista de bloques disponibles
                        const bloquesDisponibles = [];
                        dias.forEach(function(dia) {
                            horarios.forEach(function(horaInicio) {
                                bloquesDisponibles.push({ dia: dia, horaInicio: horaInicio });
        });
    });
    
                        // Mezclar aleatoriamente los bloques
                        for (let i = bloquesDisponibles.length - 1; i > 0; i--) {
                            const j = Math.floor(Math.random() * (i + 1));
                            [bloquesDisponibles[i], bloquesDisponibles[j]] = [bloquesDisponibles[j], bloquesDisponibles[i]];
                        }
                        
                        // Rellenar aproximadamente el 60% de los bloques
                        const bloquesARellenar = Math.floor(bloquesDisponibles.length * 0.6);
                        const bloquesSeleccionados = bloquesDisponibles.slice(0, bloquesARellenar);
                        let bloquesRellenados = 0;
                        let delay = 0;
                        
                        bloquesSeleccionados.forEach(function(bloque, index) {
                            setTimeout(function() {
                                // Seleccionar un profesor aleatorio con materias
                                const profesorMaterias = getRandomElement(profesoresConMaterias);
                                const materia = getRandomElement(profesorMaterias.materias);
                                
                                // Encontrar los selects correspondientes
                                const $materiaSelect = $(`select[name="horario[${bloque.dia}][${bloque.horaInicio}][materia]"]`);
                                const $profesorSelect = $(`select[name="horario[${bloque.dia}][${bloque.horaInicio}][profesor]"]`);
                                
                                if ($materiaSelect.length && $profesorSelect.length) {
                                    // Primero seleccionar el profesor
                                    $profesorSelect.val(profesorMaterias.profesor.id).trigger('change');
                                    
                                    // Esperar a que se carguen las materias, luego seleccionar la materia
                                    setTimeout(function() {
                                        $materiaSelect.val(materia.id);
                                        bloquesRellenados++;
                                        
                                        // Cuando se hayan rellenado todos los bloques
                                        if (bloquesRellenados === bloquesSeleccionados.length) {
                                            Swal.close();
                                            mostrarNotificacion('success', 'Éxito', `Datos de prueba cargados: ${bloquesRellenados} bloques asignados con profesores y materias de la sección`);
                                        }
                                    }, 800);
                                } else {
                                    // Si no se encontraron los selects, contar como completado de todas formas
                                    bloquesRellenados++;
                                    if (bloquesRellenados === bloquesSeleccionados.length) {
                                        Swal.close();
                                        mostrarNotificacion('success', 'Éxito', `Datos de prueba cargados: ${bloquesRellenados} bloques asignados`);
                                    }
                                }
                            }, delay);
                            
                            delay += 100; // Espaciar las asignaciones para evitar conflictos
                        });
                    }
                },
                error: function() {
                    profesoresProcesados++;
                    if (profesoresProcesados === profesoresDisponibles.length) {
                        Swal.close();
                        if (profesoresConMaterias.length === 0) {
                            mostrarNotificacion('error', 'Error', 'Error al obtener materias de los profesores');
                        } else {
                            // Continuar con los profesores que sí tienen materias
                            // (el código de arriba ya maneja esto)
                        }
                    }
                }
            });
        });
    });
});
</script>

<?php
// Incluir pie de página
include('../../admin/layout/parte2.php');
include('../../layout/mensajes.php');
?>