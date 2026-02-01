<?php
session_start();
require_once('../../app/config.php');
include('../../admin/layout/parte1.php');
include('../../layout/mensajes.php');

// ==== Captura de parámetros ====
$id_seccion = $_GET['seccion'] ?? null;
$id_materia = $_GET['materia'] ?? null;

if (!$id_seccion || !$id_materia) {
    echo "<script>alert('Datos incompletos.'); window.location.href='seleccion_materia_pendiente.php';</script>";
    exit;
}

// ==== Obtener información de la materia ====
$sql_materia = "SELECT nombre_materia FROM materias WHERE id_materia = :id_materia";
$stmt_m = $pdo->prepare($sql_materia);
$stmt_m->execute([':id_materia' => $id_materia]);
$materia_info = $stmt_m->fetch(PDO::FETCH_ASSOC);
$nombre_materia = $materia_info['nombre_materia'] ?? 'Materia Desconocida';

// ==== Obtener información del año y sección ====
$sql_seccion = "SELECT s.nombre_seccion, g.grado, g.nivel 
                FROM secciones s 
                INNER JOIN grados g ON s.id_grado = g.id_grado 
                WHERE s.id_seccion = :id_seccion";
$stmt_s = $pdo->prepare($sql_seccion);
$stmt_s->execute([':id_seccion' => $id_seccion]);
$seccion_info = $stmt_s->fetch(PDO::FETCH_ASSOC);

$nombre_seccion = $seccion_info['nombre_seccion'] ?? 'Sección Desconocida';
$grado = $seccion_info['grado'] ?? '';
$nivel = $seccion_info['nivel'] ?? '';

// Formatear el grado para mostrar
$grado_display = $grado;
if (strpos($grado, 'AÑO') !== false) {
    $grado_display = trim(str_replace('AÑO', '', $grado));
}

// ==== Obtener información del profesor asignado ====
$sql_profesor = "
    SELECT p.nombres, p.apellidos 
    FROM asignaciones_profesor ap
    INNER JOIN profesores p ON ap.id_profesor = p.id_profesor
    WHERE ap.id_materia = :id_materia 
      AND ap.id_seccion = :id_seccion
      AND ap.estado = 1
    LIMIT 1
";

$stmt_p = $pdo->prepare($sql_profesor);
$stmt_p->execute([':id_materia' => $id_materia, ':id_seccion' => $id_seccion]);
$profesor_info = $stmt_p->fetch(PDO::FETCH_ASSOC);
$nombre_profesor = $profesor_info ? ($profesor_info['nombres'] . ' ' . $profesor_info['apellidos']) : 'Profesor no asignado';

// ==== Obtener gestión activa ====
$sql_gestion = "SELECT id_gestion, CONCAT('Periodo ', DATE_FORMAT(desde, '%Y'), ' - ', DATE_FORMAT(hasta, '%Y')) AS nombre_gestion FROM gestiones WHERE estado = 1 LIMIT 1";
$stmt_g = $pdo->prepare($sql_gestion);
$stmt_g->execute();
$gestion_activa_row = $stmt_g->fetch(PDO::FETCH_ASSOC);
$gestion_activa = $gestion_activa_row['nombre_gestion'] ?? 'Gestión no activa';
$id_gestion_activa = $gestion_activa_row['id_gestion'] ?? null;

$max_intentos = 4;
$tipo = 'pendiente';

// ==== Consultar estudiantes con materias pendientes (INCLUYENDO APLAZADOS) ====
$sql_estudiantes = "
    SELECT 
        mp.id_estudiante, 
        e.nombres, 
        e.apellidos,
        e.cedula,
        COALESCE(MAX(CASE WHEN r.tipo = 'pendiente' THEN r.calificacion END), 0) AS nota_actual,
        COALESCE(MAX(CASE WHEN r.tipo = 'pendiente' THEN r.intento END), 0) AS intento_actual,
        mp.fecha_registro,
        mp.estado as estado_pendiente
    FROM materias_pendientes mp
    INNER JOIN estudiantes e ON mp.id_estudiante = e.id_estudiante
    LEFT JOIN recuperaciones r 
        ON r.id_estudiante = mp.id_estudiante 
        AND r.id_materia = mp.id_materia
        AND r.tipo = 'pendiente'
    WHERE mp.id_materia = :id_materia 
      AND mp.id_seccion = :id_seccion
      AND (mp.estado = 'pendiente' OR mp.estado = 'aplazado' OR mp.estado IS NULL OR mp.estado = '')
    GROUP BY mp.id_estudiante, e.nombres, e.apellidos, e.cedula, mp.fecha_registro, mp.estado
    HAVING intento_actual < 4 
    ORDER BY e.apellidos, e.nombres
";

$stmt = $pdo->prepare($sql_estudiantes);
$stmt->execute([
    ':id_materia' => $id_materia, 
    ':id_seccion' => $id_seccion
]);
$estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ==== Consultar estudiantes APLAZADOS (reprobados 4 momentos) ====
$sql_aplazados = "
    SELECT 
        ea.id_estudiante,
        e.nombres,
        e.apellidos,
        e.cedula,
        ea.nota_final,
        ea.fecha_aplazado,
        ea.motivo
    FROM estudiantes_aplazados ea
    INNER JOIN estudiantes e ON ea.id_estudiante = e.id_estudiante
    WHERE ea.id_materia = :id_materia 
      AND ea.id_seccion = :id_seccion
      AND ea.id_gestion = :id_gestion
      AND ea.estado = 'pendiente'
    ORDER BY e.apellidos, e.nombres
";

$stmt_aplazados = $pdo->prepare($sql_aplazados);
$stmt_aplazados->execute([
    ':id_materia' => $id_materia, 
    ':id_seccion' => $id_seccion,
    ':id_gestion' => $id_gestion_activa
]);
$estudiantes_aplazados = $stmt_aplazados->fetchAll(PDO::FETCH_ASSOC);

// ==== HISTORIAL: Agrupar por Estudiante (solo pendientes) ====
$sql_historial = "
    SELECT r.*, e.nombres, e.apellidos, m.nombre_materia
    FROM recuperaciones r
    INNER JOIN estudiantes e ON e.id_estudiante = r.id_estudiante
    INNER JOIN materias m ON m.id_materia = r.id_materia
    WHERE r.id_materia = :id_materia 
      AND r.id_seccion = :id_seccion
      AND r.tipo = 'pendiente'
    ORDER BY e.apellidos, e.nombres, r.fecha_registro DESC
";
$stmt_h = $pdo->prepare($sql_historial);
$stmt_h->execute([':id_materia'=>$id_materia, ':id_seccion'=>$id_seccion]);
$historial_raw = $stmt_h->fetchAll(PDO::FETCH_ASSOC);

$historial_agrupado = [];
foreach ($historial_raw as $registro) {
    $id_est = $registro['id_estudiante'];
    if (!isset($historial_agrupado[$id_est])) {
        $historial_agrupado[$id_est] = [
            'estudiante' => $registro['apellidos'] . ', ' . $registro['nombres'],
            'historial' => []
        ];
    }
    $historial_agrupado[$id_est]['historial'][] = $registro;
}

// ==== Calcular estudiantes en momento 4 ====
$estudiantes_momento4 = array_filter($estudiantes, function($est) {
    return ($est['intento_actual'] + 1) == 4;
});

// ==== Verificar si hay mensajes pendientes antes de procesar ====
$show_message = false;
$nombre_estudiante = '';
$nota = 0;
$intento = 1;
$aprobado = false;
$momento4 = false;
$aplazado = false;

if (isset($_GET['registro']) && $_GET['registro'] == 'exitoso' && isset($_SESSION['nota_registrada'])) {
    $show_message = true;
    $nombre_estudiante = $_SESSION['nombre_estudiante'] ?? '';
    $nota = $_SESSION['nota'] ?? 0;
    $intento = $_SESSION['intento'] ?? 1;
    $aprobado = $_SESSION['aprobado'] ?? false;
    $momento4 = $_SESSION['momento4'] ?? false;
    $aplazado = $_SESSION['aplazado'] ?? false;
    
    // Limpiar inmediatamente
    unset($_SESSION['nota_registrada']);
    unset($_SESSION['nombre_estudiante']);
    unset($_SESSION['nota']);
    unset($_SESSION['intento']);
    unset($_SESSION['aprobado']);
    unset($_SESSION['momento4']);
    unset($_SESSION['aplazado']);
}

// ==== Procesar formulario ====
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $pdo->beginTransaction();
        
        $id_estudiante = $_POST['id_estudiante'];
        $calificacion = $_POST['calificacion'];
        $observacion = $_POST['observacion'] ?? '';
        $intento = $_POST['intento'] ?? 1;
        
        // Obtener información del estudiante para mensajes
        $sql_est_info = "SELECT nombres, apellidos FROM estudiantes WHERE id_estudiante = :id_estudiante";
        $stmt_info = $pdo->prepare($sql_est_info);
        $stmt_info->execute([':id_estudiante' => $id_estudiante]);
        $estudiante_info = $stmt_info->fetch(PDO::FETCH_ASSOC);
        $nombre_estudiante = $estudiante_info ? ($estudiante_info['nombres'] . ' ' . $estudiante_info['apellidos']) : 'Estudiante';
        
        // Verificar si ya existe este intento para evitar duplicados
        $sql_check = "
            SELECT COUNT(*) as existe 
            FROM recuperaciones 
            WHERE id_estudiante = :id_estudiante 
            AND id_materia = :id_materia 
            AND id_seccion = :id_seccion 
            AND intento = :intento 
            AND tipo = 'pendiente'
        ";
        
        $stmt_c = $pdo->prepare($sql_check);
        $stmt_c->execute([
            ':id_estudiante' => $id_estudiante,
            ':id_materia' => $id_materia,
            ':id_seccion' => $id_seccion,
            ':intento' => $intento
        ]);
        
        $resultado = $stmt_c->fetch(PDO::FETCH_ASSOC);
        
        if ($resultado['existe'] > 0) {
            throw new Exception("Ya existe un registro para este estudiante en el momento $intento.");
        }
        
        // Insertar nueva nota
        $sql_insert = "
            INSERT INTO recuperaciones (id_estudiante, id_materia, id_seccion, calificacion, tipo, intento, observaciones, fecha_registro)
            VALUES (:id_estudiante, :id_materia, :id_seccion, :calificacion, :tipo, :intento, :observaciones, NOW())
        ";
        
        $stmt_i = $pdo->prepare($sql_insert);
        $stmt_i->execute([
            ':id_estudiante' => $id_estudiante,
            ':id_materia' => $id_materia,
            ':id_seccion' => $id_seccion,
            ':calificacion' => $calificacion,
            ':tipo' => $tipo,
            ':intento' => $intento,
            ':observaciones' => $observacion
        ]);
        
        // Verificar si el estudiante aprobó
        $nota_redondeada = round($calificacion, 0, PHP_ROUND_HALF_UP);
        $aprobado = ($nota_redondeada >= 10);
        
        // ==== SI ES EL 4TO MOMENTO Y NO APROBÓ -> REGISTRAR COMO APLAZADO ====
        if ($intento == 4 && !$aprobado) {
            // 1. Registrar en estudiantes_aplazados
            $sql_aplazado = "
                INSERT INTO estudiantes_aplazados 
                (id_estudiante, id_materia, id_seccion, id_gestion, nota_final, intentos_completados, motivo)
                VALUES (:id_estudiante, :id_materia, :id_seccion, :id_gestion, :nota_final, :intentos, :motivo)
                ON DUPLICATE KEY UPDATE
                nota_final = VALUES(nota_final),
                fecha_aplazado = CURRENT_TIMESTAMP,
                estado = 'pendiente'
            ";
            
            $stmt_a = $pdo->prepare($sql_aplazado);
            $stmt_a->execute([
                ':id_estudiante' => $id_estudiante,
                ':id_materia' => $id_materia,
                ':id_seccion' => $id_seccion,
                ':id_gestion' => $id_gestion_activa,
                ':nota_final' => $nota_redondeada,
                ':intentos' => $intento,
                ':motivo' => "Reprobó los 4 momentos de recuperación en la materia '$nombre_materia'. Nota final: $nota_redondeada/20"
            ]);
            
            // 2. Cambiar estado en materias_pendientes a "aplazado"
            $sql_update_estado = "
                UPDATE materias_pendientes 
                SET estado = 'aplazado' 
                WHERE id_estudiante = :id_estudiante 
                AND id_materia = :id_materia 
                AND id_seccion = :id_seccion
            ";
            $stmt_u = $pdo->prepare($sql_update_estado);
            $stmt_u->execute([
                ':id_estudiante' => $id_estudiante,
                ':id_materia' => $id_materia,
                ':id_seccion' => $id_seccion
            ]);
            
            // 3. Registrar también en el historial de recuperaciones
            $sql_historial_aplazado = "
                INSERT INTO recuperaciones 
                (id_estudiante, id_materia, id_seccion, calificacion, tipo, intento, observaciones, fecha_registro, estado)
                VALUES (:id_estudiante, :id_materia, :id_seccion, :calificacion, 'aplazado', :intento, :observaciones, NOW(), 'aplazado')
            ";
            $stmt_ha = $pdo->prepare($sql_historial_aplazado);
            $stmt_ha->execute([
                ':id_estudiante' => $id_estudiante,
                ':id_materia' => $id_materia,
                ':id_seccion' => $id_seccion,
                ':calificacion' => $calificacion,
                ':intento' => $intento,
                ':observaciones' => $observacion . " | ESTUDIANTE APLAZADO - REPITE AÑO ESCOLAR"
            ]);
        }
        
        if ($aprobado) {
            // Eliminar de materias pendientes si aprobó
            $sql_eliminar = "
                DELETE FROM materias_pendientes 
                WHERE id_estudiante = :id_estudiante 
                AND id_materia = :id_materia 
                AND id_seccion = :id_seccion
            ";
            $stmt_e = $pdo->prepare($sql_eliminar);
            $stmt_e->execute([
                ':id_estudiante' => $id_estudiante,
                ':id_materia' => $id_materia,
                ':id_seccion' => $id_seccion
            ]);
            
            // Si estaba marcado como aplazado, actualizar estado
            $sql_actualizar_aplazado = "
                UPDATE estudiantes_aplazados 
                SET estado = 'aprobado' 
                WHERE id_estudiante = :id_estudiante 
                AND id_materia = :id_materia 
                AND id_seccion = :id_seccion
                AND id_gestion = :id_gestion
            ";
            $stmt_aa = $pdo->prepare($sql_actualizar_aplazado);
            $stmt_aa->execute([
                ':id_estudiante' => $id_estudiante,
                ':id_materia' => $id_materia,
                ':id_seccion' => $id_seccion,
                ':id_gestion' => $id_gestion_activa
            ]);
        }
        
        $pdo->commit();
        
        // Guardar datos en sesión para mostrar el mensaje después
        $_SESSION['nota_registrada'] = true;
        $_SESSION['nombre_estudiante'] = $nombre_estudiante;
        $_SESSION['nota'] = $nota_redondeada;
        $_SESSION['intento'] = $intento;
        $_SESSION['aprobado'] = $aprobado;
        $_SESSION['momento4'] = ($intento == 4);
        $_SESSION['aplazado'] = ($intento == 4 && !$aprobado);
        
        // Redirigir inmediatamente
        $timestamp = time();
        header("Location: recuperaciones_pendientes.php?seccion=$id_seccion&materia=$id_materia&registro=exitoso&id_estudiante=$id_estudiante&timestamp=$timestamp");
        exit();
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['mensaje_error'] = 'No se pudo registrar la nota: ' . $e->getMessage();
        $timestamp = time();
        header("Location: recuperaciones_pendientes.php?seccion=$id_seccion&materia=$id_materia&error=1&id_estudiante=$id_estudiante&timestamp=$timestamp");
        exit();
    }
}

// Mostrar mensajes de error
$error_mostrado = false;
if (isset($_GET['error']) && isset($_SESSION['mensaje_error'])) {
    $error_msg = $_SESSION['mensaje_error'];
    echo "<script>
    $(document).ready(function() {
        Swal.fire({
            title: '❌ Error',
            html: `<div style='text-align: center; padding: 15px;'>
                <div style='font-size: 3em; margin-bottom: 8px; color: #d32f2f;'>⚠️</div>
                <h4 style='color: #d32f2f; margin-bottom: 8px; font-size: 1.1em;'>ERROR AL GUARDAR</h4>
                <div style='background: #ffebee; padding: 10px; border-radius: 6px; border: 2px solid #f44336; margin: 8px 0;'>
                    <p style='margin: 0; color: #d32f2f; font-size: 0.9em;'>$error_msg</p>
                </div>
                <p style='color: #666; margin-top: 8px; font-size: 0.8em;'>
                    Por favor, intente nuevamente
                </p>
            </div>`,
            icon: 'error',
            confirmButtonColor: '#f44336',
            confirmButtonText: 'Reintentar',
            background: '#fff8f8',
            allowOutsideClick: false,
            width: 380,
            heightAuto: false
        }).then(() => {
            window.history.replaceState({}, document.title, window.location.pathname + '?seccion=$id_seccion&materia=$id_materia');
        });
    });
    </script>";
    unset($_SESSION['mensaje_error']);
    $error_mostrado = true;
}
?>

<style>
/* ESTILOS AZULES CLAROS MEJORADOS */
:root {
    --azul-suave: #e8f4fe;
    --azul-claro: #d4e7ff;
    --azul-medio: #89c2f8;
    --azul-oscuro: #4a90e2;
    --azul-texto: #1a73e8;
    --azul-header: #4a9df5;
    --verde-suave: #e8f5e9;
    --verde-claro: #c8e6c9;
    --naranja-suave: #fff3e0;
    --rojo-suave: #ffebee;
}

/* NUEVOS ESTILOS PARA APLAZADOS - MÁS SUAVES */
.estudiante-aplazado {
    background: linear-gradient(135deg, #fff5f5, #ffeaea) !important;
    border-left: 4px solid #ff6b6b !important;
    animation: pulse-aplazado 2s infinite;
}

@keyframes pulse-aplazado {
    0% { box-shadow: 0 0 0 0 rgba(255, 107, 107, 0.4); }
    70% { box-shadow: 0 0 0 10px rgba(255, 107, 107, 0); }
    100% { box-shadow: 0 0 0 0 rgba(255, 107, 107, 0); }
}

.badge-aplazado {
    background: linear-gradient(135deg, #ff6b6b, #ff8e8e) !important;
    color: white !important;
    font-weight: 600;
    padding: 5px 12px;
    border-radius: 15px;
    font-size: 0.8em;
    box-shadow: 0 2px 4px rgba(255, 107, 107, 0.2);
}

.card-aplazados {
    border: 2px solid #ff6b6b;
    background: linear-gradient(135deg, #fffafa, #fff5f5);
    box-shadow: 0 4px 15px rgba(255, 107, 107, 0.1);
}

.card-aplazados .card-header {
    background: linear-gradient(135deg, #ff6b6b, #ff8e8e) !important;
    color: white !important;
    font-weight: 600;
    border-bottom: none;
}

.estado-aplazado {
    color: #d32f2f !important;
    font-weight: bold;
}

.badge-contador-aplazados {
    background: linear-gradient(135deg, #ff4444, #ff6b6b);
    color: white;
    font-weight: bold;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 1em;
    box-shadow: 0 2px 5px rgba(255, 68, 68, 0.3);
}

/* HEADER AZUL CLARO ESPECIAL */
.card-header-estudiantes {
    background: linear-gradient(135deg, var(--azul-header), #6ab0ff) !important;
    border-color: var(--azul-medio) !important;
    color: white !important;
    font-weight: 600;
    padding: 15px 20px;
}

.card-hover:hover {
    transform: translateY(-3px);
    transition: transform 0.3s ease;
    box-shadow: 0 6px 20px rgba(74, 144, 226, 0.15);
}

.bg-azul-suave {
    background-color: var(--azul-suave) !important;
    border-color: var(--azul-claro) !important;
}

.border-azul {
    border-color: var(--azul-medio) !important;
}

.text-azul {
    color: var(--azul-texto) !important;
}

.btn-azul {
    background: linear-gradient(135deg, var(--azul-texto), var(--azul-oscuro));
    border: none;
    color: white;
    font-weight: 500;
    padding: 8px 16px;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.btn-azul:hover {
    background: linear-gradient(135deg, var(--azul-oscuro), #3a7bd5);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(74, 144, 226, 0.25);
    color: white;
}

.badge-momento {
    font-size: 0.75em;
    padding: 4px 10px;
    margin: 1px;
    border-radius: 12px;
    font-weight: 600;
}

.badge-momento-1 { background-color: #e3f2fd; color: #1565c0; border: 1px solid #bbdefb; }
.badge-momento-2 { background-color: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9; }
.badge-momento-3 { background-color: #fff3e0; color: #f57c00; border: 1px solid #ffe0b2; }
.badge-momento-4 { background-color: #ffebee; color: #c62828; border: 1px solid #ffcdd2; }

.estado-aprobado { 
    color: #2e7d32 !important; 
    font-weight: bold;
}
.estado-en-proceso { 
    color: #f57c00 !important; 
    font-weight: bold;
}

.profesor-card {
    background: linear-gradient(135deg, #f8fdff 0%, #e8f4ff 100%);
    border-left: 5px solid var(--azul-texto);
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 20px;
    box-shadow: 0 4px 12px rgba(26, 115, 232, 0.1);
}

.table-estudiantes {
    border-collapse: separate;
    border-spacing: 0;
}

.table-estudiantes thead {
    background: linear-gradient(135deg, var(--azul-suave), var(--azul-claro));
}

.table-estudiantes thead th {
    border: none;
    color: #333 !important; /* Cambiado a negro */
    font-weight: 700 !important;
    padding: 15px 12px;
    border-bottom: 2px solid var(--azul-medio);
}

.table-estudiantes tbody tr {
    transition: all 0.3s ease;
    border-bottom: 1px solid #eef5ff;
}

.table-estudiantes tbody tr:hover {
    background-color: var(--azul-suave) !important;
    transform: translateX(5px);
    box-shadow: 0 3px 10px rgba(74, 144, 226, 0.1);
}

/* SWALERT - CUADRADO DEL MISMO TAMAÑO */
.swal2-popup {
    border-radius: 12px !important;
    padding: 20px !important;
    width: 450px !important;
    max-width: 90% !important;
    min-height: 300px !important;
    display: flex !important;
    flex-direction: column !important;
    justify-content: center !important;
}

.swal2-header {
    margin-bottom: 15px !important;
}

.swal2-title {
    font-size: 1.4em !important;
    color: #1a73e8 !important;
    margin-bottom: 15px !important;
}

.swal2-content {
    padding: 0 10px !important;
}

.swal2-actions {
    margin-top: 20px !important;
}

.swal2-confirm, .swal2-cancel {
    padding: 10px 25px !important;
    border-radius: 8px !important;
    font-weight: 600 !important;
}

.nota-input {
    font-size: 1.3em;
    font-weight: bold;
    text-align: center;
    border: 2px solid var(--azul-medio);
    border-radius: 10px;
    padding: 10px;
    width: 90px;
    background: white;
    transition: all 0.3s ease;
}

.nota-input:focus {
    border-color: var(--azul-texto);
    box-shadow: 0 0 0 3px rgba(26, 115, 232, 0.2);
    outline: none;
}

.historial-item {
    border-left: 4px solid var(--azul-medio);
    padding: 12px 15px;
    margin-bottom: 10px;
    background: linear-gradient(135deg, #f8fdff, #e8f4ff);
    border-radius: 0 8px 8px 0;
    transition: all 0.3s ease;
}

.historial-item:hover {
    transform: translateX(5px);
    box-shadow: 0 3px 8px rgba(74, 144, 226, 0.15);
}

/* Estilo para mensajes importantes */
.mensaje-importante {
    background: linear-gradient(135deg, #ffebee, #ffcdd2);
    border-left: 5px solid #f44336;
    border-radius: 10px;
    padding: 15px;
    margin: 15px 0;
    color: #c62828;
}

.mensaje-exito {
    background: linear-gradient(135deg, #e8f5e9, #c8e6c9);
    border-left: 5px solid #4caf50;
    border-radius: 10px;
    padding: 15px;
    margin: 15px 0;
    color: #2e7d32;
}

.advertencia-momento4 {
    background: linear-gradient(135deg, #fff3e0, #ffe0b2);
    border-left: 5px solid #ff9800;
    border-radius: 12px;
    padding: 15px;
    margin-bottom: 15px;
}

.advertencia-momento4 h5 {
    color: #333 !important;
    font-weight: 700 !important;
}

.advertencia-momento4 p {
    color: #333 !important;
}

/* Estilos para botones en modales */
.modal .btn-azul {
    padding: 10px 24px;
    font-size: 1em;
}

.modal .btn-secondary {
    background: linear-gradient(135deg, #f5f5f5, #e0e0e0);
    border: none;
    color: #666;
    padding: 10px 24px;
    border-radius: 8px;
    font-weight: 500;
}

.estudiante-momento4 {
    background: linear-gradient(135deg, #ffebee, #ffcdd2) !important;
    border-left: 4px solid #f44336 !important;
}

.estudiante-aprobado {
    background: linear-gradient(135deg, #e8f5e9, #c8e6c9) !important;
    border-left: 4px solid #4caf50 !important;
}

/* Estilo para las tarjetas */
.card {
    border-radius: 12px;
    border: 1px solid #e0e0e0;
    overflow: hidden;
}

/* LETRAS EN NEGRITA PARA MODALES DE NOTA */
.modal-header-estudiante {
    background: linear-gradient(135deg, #e3f2fd, #bbdefb);
    color: #1565c0 !important;
    border-bottom: 2px solid #90caf9;
}

.modal-header-estudiante h5 {
    color: #1565c0 !important;
    font-weight: 700 !important;
}

.modal-header-estudiante .close {
    color: #1565c0 !important;
    opacity: 0.8;
}

.modal-header-estudiante .close:hover {
    opacity: 1;
}

/* ESTILO PARA FORMULARIO EN MODAL */
.modal-body-estudiante .form-group label {
    color: #1976d2 !important;
    font-weight: 700 !important;
}

/* TEXTOS EN NEGRITA EN INSTRUCCIONES */
.texto-negrita {
    font-weight: 700 !important;
    color: #333 !important;
}

/* ESPACIO PARA BOTÓN DE HISTORIAL */
.btn-historial {
    background: linear-gradient(135deg, #6a89cc, #4a69bd);
    border: none;
    color: white;
    font-weight: 500;
    padding: 8px 16px;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.btn-historial:hover {
    background: linear-gradient(135deg, #4a69bd, #1e3799);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(74, 105, 189, 0.25);
    color: white;
}

/* NUEVO: Estilo para sección de aplazados - MÁS LIMPIO */
.tabla-aplazados tbody tr {
    background: linear-gradient(135deg, #fffafa, #fff5f5);
    transition: all 0.3s ease;
}

.tabla-aplazados tbody tr:hover {
    background: linear-gradient(135deg, #ffeaea, #ffdede);
    transform: translateX(3px);
    box-shadow: 0 3px 10px rgba(255, 107, 107, 0.1);
}

.tabla-aplazados thead th {
    background: linear-gradient(135deg, #fff5f5, #ffebeb);
    color: #d32f2f !important;
    font-weight: 700 !important;
    border-bottom: 2px solid #ffcccc;
}

/* Estilo para advertencia en modal */
.advertencia-modal {
    background: linear-gradient(135deg, #fff3e0, #ffe0b2);
    border-left: 4px solid #ff9800;
    border-radius: 8px;
    padding: 12px;
    margin: 10px 0;
}

.advertencia-modal h6 {
    color: #333 !important;
    font-weight: 700 !important;
    margin-bottom: 8px;
}

.advertencia-modal p {
    color: #333 !important;
    margin-bottom: 5px;
    font-size: 0.9em;
}

/* ASEGURAR QUE LOS TEXTOS SEAN VISIBLES */
.texto-negrita {
    font-weight: 700 !important;
    color: #333 !important;
}

/* Sobreescribir colores heredados */
.alert .texto-negrita,
.modal .texto-negrita,
.card .texto-negrita {
    color: #333 !important;
}

/* Asegurar visibilidad en todos los contextos */
.modal-body-estudiante .texto-negrita,
.modal-header-estudiante .texto-negrita,
.advertencia-momento4 .texto-negrita {
    color: #333 !important;
}

/* Estilos específicos para tablas */
.table th.texto-negrita {
    color: #333 !important;
    font-weight: 700 !important;
}

/* Estilos para labels en formularios */
.form-group label.texto-negrita {
    color: #333 !important;
}

/* NUEVO: Estilos para alertas de aplazados más suaves */
.alert-aplazado-info {
    background: linear-gradient(135deg, #fff5f5, #ffeaea);
    border: 1px solid #ffcccc;
    border-left: 5px solid #ff6b6b;
    border-radius: 10px;
    padding: 15px;
    margin-bottom: 20px;
}

.alert-aplazado-info h6 {
    color: #d32f2f;
    font-weight: 700;
    margin-bottom: 8px;
}

.alert-aplazado-info p {
    color: #666;
    margin-bottom: 5px;
    font-size: 0.95em;
}

/* Icono para estudiantes aplazados */
.icono-aplazado {
    color: #ff6b6b;
    margin-right: 8px;
}

/* Badge para estado de estudiante */
.badge-estado-aplazado {
    background: linear-gradient(135deg, #ff6b6b, #ff8e8e);
    color: white;
    font-weight: 600;
    padding: 3px 10px;
    border-radius: 15px;
    font-size: 0.8em;
}

/* Estilos para botones de acciones aplazados */
.btn-aplazado-ver {
    background: linear-gradient(135deg, #ff6b6b, #ff8e8e);
    border: none;
    color: white;
    font-weight: 500;
    padding: 6px 12px;
    border-radius: 6px;
    transition: all 0.3s ease;
}

.btn-aplazado-ver:hover {
    background: linear-gradient(135deg, #ff5252, #ff6b6b);
    transform: translateY(-1px);
    box-shadow: 0 3px 8px rgba(255, 107, 107, 0.25);
    color: white;
}

.btn-aplazado-constancia {
    background: linear-gradient(135deg, #ffa726, #ffb74d);
    border: none;
    color: white;
    font-weight: 500;
    padding: 6px 12px;
    border-radius: 6px;
    transition: all 0.3s ease;
}

.btn-aplazado-constancia:hover {
    background: linear-gradient(135deg, #ff9800, #ffa726);
    transform: translateY(-1px);
    box-shadow: 0 3px 8px rgba(255, 167, 38, 0.25);
    color: white;
}
</style>

<!-- MOSTRAR MENSAJE DE ÉXITO INMEDIATAMENTE -->
<?php if ($show_message): ?>
<script>
$(document).ready(function() {
    <?php if ($aplazado): ?>
    Swal.fire({
        title: '⚠️ ¡ESTUDIANTE APLAZADO!',
        html: `<div style='text-align: center; padding: 15px;'>
            <div style='font-size: 3em; margin-bottom: 10px; color: #ff6b6b;'>🚫</div>
            <h4 style='color: #d32f2f; margin-bottom: 8px; font-size: 1.1em;'>¡NOTA REGISTRADA!</h4>
            <p style='font-size: 0.95em; margin-bottom: 8px; color: #333;'>
                <strong style='color: #1565c0;'><?= htmlspecialchars($nombre_estudiante) ?></strong>
            </p>
            <div style='background: #fff5f5; padding: 12px; border-radius: 6px; border: 2px solid #ff6b6b; margin: 8px 0;'>
                <p style='margin: 0; font-size: 0.95em;'><strong>Nota Final:</strong> <span style='font-size: 1.3em; color: #d32f2f; font-weight: bold;'><?= $nota ?>/20</span></p>
                <p style='margin: 4px 0 0 0; font-size: 0.85em;'><strong>Momento:</strong> <span style='color: #ff6b6b;'>4° (Último)</span></p>
            </div>
            <div style='background: #fff3e0; padding: 10px; border-radius: 6px; border: 2px solid #ffa726; margin-top: 8px;'>
                <p style='margin: 0; color: #d84315; font-weight: bold; font-size: 0.9em;'>
                    ⚠️ <strong>ESTUDIANTE REGISTRADO COMO REPITENTE</strong>
                </p>
                <p style='margin: 2px 0 0 0; color: #666; font-size: 0.8em;'>
                    Deberá repetir el año escolar para continuar
                </p>
            </div>
        </div>`,
        icon: 'warning',
        confirmButtonColor: '#ff6b6b',
        confirmButtonText: 'Entendido',
        background: '#fffafa',
        allowOutsideClick: false,
        width: 450,
        heightAuto: false
    }).then(() => {
        window.history.replaceState({}, document.title, window.location.pathname + '?seccion=<?= $id_seccion ?>&materia=<?= $id_materia ?>');
        location.reload();
    });
    <?php elseif ($aprobado): ?>
        <?php if ($momento4): ?>
        Swal.fire({
            title: '🎉 ¡EXCELENTE!',
            html: `<div style='text-align: center; padding: 15px;'>
                <div style='font-size: 3em; margin-bottom: 10px;'>🏆</div>
                <h4 style='color: #2e7d32; margin-bottom: 8px; font-size: 1.1em;'>¡NOTA GUARDADA CON ÉXITO!</h4>
                <p style='font-size: 0.95em; margin-bottom: 8px; color: #333;'>
                    <strong style='color: #1565c0;'><?= htmlspecialchars($nombre_estudiante) ?></strong>
                </p>
                <div style='background: #e8f5e9; padding: 10px; border-radius: 6px; border-left: 4px solid #4caf50; margin: 8px 0;'>
                    <p style='margin: 0; font-size: 0.95em;'><strong>Nota:</strong> <span style='font-size: 1.3em; color: #2e7d32; font-weight: bold;'><?= $nota ?>/20</span></p>
                    <p style='margin: 4px 0 0 0; font-size: 0.85em;'><strong>Momento:</strong> <span style='color: #d84315;'>4° (Último)</span></p>
                </div>
                <p style='color: #388e3c; font-weight: bold; margin-top: 8px; font-size: 0.85em;'>
                    ✅ <strong>¡APROBADO!</strong> - Eliminado de pendientes
                </p>
            </div>`,
            icon: 'success',
            confirmButtonColor: '#4caf50',
            confirmButtonText: '¡Perfecto!',
            background: '#f8fff8',
            allowOutsideClick: false,
            width: 400,
            heightAuto: false
        }).then(() => {
            window.history.replaceState({}, document.title, window.location.pathname + '?seccion=<?= $id_seccion ?>&materia=<?= $id_materia ?>');
            location.reload();
        });
        <?php else: ?>
        Swal.fire({
            title: '✨ ¡MUY BIEN!',
            html: `<div style='text-align: center; padding: 15px;'>
                <div style='font-size: 3em; margin-bottom: 10px;'>🌟</div>
                <h4 style='color: #2e7d32; margin-bottom: 8px; font-size: 1.1em;'>¡NOTA GUARDADA CON ÉXITO!</h4>
                <p style='font-size: 0.95em; margin-bottom: 8px; color: #333;'>
                    <strong style='color: #1565c0;'><?= htmlspecialchars($nombre_estudiante) ?></strong>
                </p>
                <div style='background: #e3f2fd; padding: 10px; border-radius: 6px; border-left: 4px solid #2196f3; margin: 8px 0;'>
                    <p style='margin: 0; font-size: 0.95em;'><strong>Nota:</strong> <span style='font-size: 1.3em; color: #1976d2; font-weight: bold;'><?= $nota ?>/20</span></p>
                    <p style='margin: 4px 0 0 0; font-size: 0.85em;'><strong>Momento:</strong> <span style='color: #0288d1;'><?= $intento ?>°</span></p>
                </div>
                <p style='color: #388e3c; font-weight: bold; margin-top: 8px; font-size: 0.85em;'>
                    ✅ <strong>¡APROBADO!</strong> - Eliminado de pendientes
                </p>
            </div>`,
            icon: 'success',
            confirmButtonColor: '#2196f3',
            confirmButtonText: 'Continuar',
            background: '#f8fdff',
            allowOutsideClick: false,
            width: 380,
            heightAuto: false
        }).then(() => {
            window.history.replaceState({}, document.title, window.location.pathname + '?seccion=<?= $id_seccion ?>&materia=<?= $id_materia ?>');
            location.reload();
        });
        <?php endif; ?>
    <?php else: ?>
    Swal.fire({
        title: '📝 ¡GUARDADO!',
        html: `<div style='text-align: center; padding: 15px;'>
            <div style='font-size: 3em; margin-bottom: 10px; color: #ff9800;'>📋</div>
            <h4 style='color: #f57c00; margin-bottom: 8px; font-size: 1.1em;'>¡NOTA GUARDADA CON ÉXITO!</h4>
            <p style='font-size: 0.95em; margin-bottom: 8px; color: #333;'>
                <strong style='color: #1565c0;'><?= htmlspecialchars($nombre_estudiante) ?></strong>
            </p>
            <div style='background: #fff3e0; padding: 10px; border-radius: 6px; border-left: 4px solid #ff9800; margin: 8px 0;'>
                <p style='margin: 0; font-size: 0.95em;'><strong>Nota:</strong> <span style='font-size: 1.3em; color: #f57c00; font-weight: bold;'><?= $nota ?>/20</span></p>
                <p style='margin: 4px 0 0 0; font-size: 0.85em;'><strong>Momento:</strong> <span style='color: #ff9800;'><?= $intento ?>°</span></p>
                <p style='margin: 4px 0 0 0; color: #d84315; font-size: 0.85em;'>
                    <i class='fas fa-arrow-right'></i> <strong>Próximo:</strong> <?= $intento + 1 ?>°
                </p>
            </div>
            <p style='color: #f57c00; margin-top: 8px; font-weight: bold; font-size: 0.85em;'>
                ⚠️ Continúa en materias pendientes
            </p>
        </div>`,
        icon: 'info',
        confirmButtonColor: '#ff9800',
        confirmButtonText: 'Continuar',
        background: '#fffbf0',
        allowOutsideClick: false,
        width: 360,
        heightAuto: false
    }).then(() => {
        window.history.replaceState({}, document.title, window.location.pathname + '?seccion=<?= $id_seccion ?>&materia=<?= $id_materia ?>');
        location.reload();
    });
    <?php endif; ?>
});
</script>
<?php endif; ?>

<div class="content-wrapper">
    <div class="content">
        <div class="container-fluid">
            <!-- ENCABEZADO -->
            <div class="content-header">
                <div class="row mb-2">
                    <div class="col-sm-8">
                        <div class="d-flex align-items-center">
                            <div class="mr-3">
                                <i class="fas fa-clipboard-check fa-2x text-azul"></i>
                            </div>
                            <div>
                                <h1 class="m-0 text-azul" style="font-weight: 600;">
                                    <i class="fas fa-book-medical"></i> Recuperaciones Pendientes
                                </h1>
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb" style="background-color: transparent; padding-left: 0;">
                                        <li class="breadcrumb-item">
                                            <a href="index.php" class="text-azul">
                                                <i class="fas fa-home"></i> Inicio
                                            </a>
                                        </li>
                                        <li class="breadcrumb-item">
                                            <a href="seleccion_materia_pendiente.php" class="text-azul">
                                                Materias Pendientes
                                            </a>
                                        </li>
                                        <li class="breadcrumb-item active text-dark">
                                            Gestionar Recuperaciones
                                        </li>
                                    </ol>
                                </nav>
                            </div>
                        </div>
                        
                        <!-- Información de la materia -->
                        <div class="profesor-card mt-3">
                            <div class="row">
                                <div class="col-md-4">
                                    <h6 class="text-azul mb-2"><i class="fas fa-book"></i> Materia</h6>
                                    <h5 class="mb-1" style="color: #1565c0;"><?= htmlspecialchars($nombre_materia) ?></h5>
                                    <small class="text-muted">Gestión: <?= htmlspecialchars($gestion_activa) ?></small>
                                </div>
                                <div class="col-md-4">
                                    <h6 class="text-azul mb-2"><i class="fas fa-users"></i> Sección</h6>
                                    <h5 class="mb-1" style="color: #1565c0;">
                                        <?= htmlspecialchars("$grado_display - $nivel") ?>
                                        <small class="text-muted">(<?= htmlspecialchars($nombre_seccion) ?>)</small>
                                    </h5>
                                    <small class="text-muted"><?= count($estudiantes) ?> estudiantes pendientes</small>
                                </div>
                                <div class="col-md-4">
                                    <h6 class="text-azul mb-2"><i class="fas fa-chalkboard-teacher"></i> Profesor</h6>
                                    <h5 class="mb-1" style="color: #1565c0;"><?= htmlspecialchars($nombre_profesor) ?></h5>
                                    <small class="text-muted">Asignado a la materia</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4 text-right">
                        <div class="btn-group" role="group">
                            <a href="seleccion_materia_pendiente.php" class="btn btn-light border">
                                <i class="fas fa-arrow-left"></i> Volver
                            </a>
                            <a href="historial_aprobados.php?seccion=<?= $id_seccion ?>&materia=<?= $id_materia ?>" 
                               class="btn btn-historial">
                                <i class="fas fa-history"></i> Historial
                            </a>
                            <button type="button" class="btn btn-azul" data-toggle="modal" data-target="#instruccionesModal">
                                <i class="fas fa-info-circle"></i> Instrucciones
                            </button>
                            <?php if (!empty($estudiantes_aplazados)): ?>
                            <a href="reporte_recuperaciones.php?seccion=<?= $id_seccion ?>&materia=<?= $id_materia ?>" 
                               class="btn btn-success" target="_blank">
                                <i class="fas fa-file-pdf"></i> Reporte
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- NUEVA SECCIÓN: Estudiantes APLAZADOS - MÁS LIMPIA E INTUITIVA -->
            <?php if (!empty($estudiantes_aplazados)): ?>
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card card-aplazados">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0">
                                    <i class="fas fa-user-graduate icono-aplazado"></i> Estudiantes Aplazados (Repitentes)
                                </h5>
                                <small class="text-light">Reprobaron los 4 momentos y deben repetir el año escolar</small>
                            </div>
                            <div>
                                <span class="badge badge-contador-aplazados"><?= count($estudiantes_aplazados) ?> estudiante(s)</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Mensaje informativo simplificado -->
                            <div class="alert-aplazado-info">
                                <h6><i class="fas fa-exclamation-circle"></i> Información Importante</h6>
                                <p class="mb-2">Los siguientes estudiantes han sido registrados como <strong class="texto-negrita">REPITENTES</strong> debido a que reprobaron los 4 momentos de recuperación.</p>
                                <p class="mb-0"><strong class="texto-negrita">Consecuencia:</strong> No podrán inscribirse en un año superior hasta que repitan el año escolar actual.</p>
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table table-hover tabla-aplazados">
                                    <thead>
                                        <tr>
                                            <th class="texto-negrita">#</th>
                                            <th class="texto-negrita">Estudiante</th>
                                            <th class="texto-negrita">Cédula</th>
                                            <th class="texto-negrita">Nota Final</th>
                                            <th class="texto-negrita">Fecha de Aplazo</th>
                                            <th class="texto-negrita">Motivo</th>
                                            <th class="texto-negrita">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($estudiantes_aplazados as $index => $aplazado): ?>
                                        <tr class="estudiante-aplazado">
                                            <td><?= $index + 1 ?></td>
                                            <td>
                                                <strong><?= htmlspecialchars($aplazado['apellidos'] . ', ' . $aplazado['nombres']) ?></strong>
                                                <br><small class="badge-aplazado">ESTUDIANTE REPITENTE</small>
                                            </td>
                                            <td><?= htmlspecialchars($aplazado['cedula']) ?></td>
                                            <td>
                                                <span class="estado-aplazado font-weight-bold">
                                                    <?= $aplazado['nota_final'] ?>/20
                                                </span>
                                            </td>
                                            <td>
                                                <small><?= date('d/m/Y', strtotime($aplazado['fecha_aplazado'])) ?></small>
                                            </td>
                                            <td>
                                                <small class="text-muted"><?= htmlspecialchars($aplazado['motivo']) ?></small>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-aplazado-ver mb-1" 
                                                        onclick="verDetalleAplazado(<?= $aplazado['id_estudiante'] ?>, '<?= addslashes($aplazado['apellidos'] . ', ' . $aplazado['nombres']) ?>')">
                                                    <i class="fas fa-eye"></i> Ver Detalle
                                                </button>
                                                <a href="reporte_aplazado.php?id_estudiante=<?= $aplazado['id_estudiante'] ?>&materia=<?= $id_materia ?>" 
                                                   class="btn btn-sm btn-aplazado-constancia mb-1" target="_blank">
                                                    <i class="fas fa-file-alt"></i> Constancia
                                                </a>
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
            <?php endif; ?>

            <!-- Instrucciones en Modal -->
            <div class="modal fade" id="instruccionesModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-azul-suave">
                            <h5 class="modal-title text-azul texto-negrita">
                                <i class="fas fa-info-circle"></i> Instrucciones del Sistema de Recuperaciones
                            </h5>
                            <button type="button" class="close" data-dismiss="modal">
                                <span>&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-danger advertencia-momento4">
                                <h6 class="texto-negrita"><i class="fas fa-exclamation-triangle"></i> ¡ATENCIÓN! CONSECUENCIA DEL 4TO MOMENTO</h6>
                                <p class="mb-2 texto-negrita">Si un estudiante reprueba el <strong class="texto-negrita">4to y último momento</strong>:</p>
                                <ul class="mb-0">
                                    <li class="texto-negrita">Automáticamente se registra como <strong class="text-danger texto-negrita">REPITENTE</strong></li>
                                    <li class="texto-negrita">Se guarda en la tabla <strong class="texto-negrita">estudiantes_aplazados</strong></li>
                                    <li class="texto-negrita">NO podrá inscribirse en año superior</li>
                                    <li class="texto-negrita">DEBERÁ REPETIR el año escolar actual</li>
                                </ul>
                            </div>
                            
                            <div class="alert alert-info" style="background: linear-gradient(135deg, #e3f2fd, #bbdefb); border-left: 5px solid #2196f3;">
                                <h6 class="texto-negrita"><i class="fas fa-exclamation-circle"></i> IMPORTANTE</h6>
                                <p class="mb-0 texto-negrita">Cada estudiante tiene <strong class="texto-negrita">4 momentos (intentos)</strong> para aprobar la materia pendiente.</p>
                            </div>
                            
                            <div class="row mt-4">
                                <div class="col-md-6">
                                    <div class="card h-100" style="border: 2px solid #4caf50; background: linear-gradient(135deg, #f1f8e9, #e8f5e9);">
                                        <div class="card-body">
                                            <h6 class="text-success texto-negrita"><i class="fas fa-check-circle"></i> Para Aprobar</h6>
                                            <ul class="mb-0">
                                                <li class="texto-negrita">Nota mínima requerida: <strong class="texto-negrita">10 puntos</strong></li>
                                                <li class="texto-negrita">Si el estudiante aprueba en cualquier momento, se elimina de materias pendientes</li>
                                                <li class="texto-negrita">La nota final será la obtenida en el momento que aprueba</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card h-100" style="border: 2px solid #ff6b6b; background: linear-gradient(135deg, #fff5f5, #ffeaea);">
                                        <div class="card-body">
                                            <h6 class="text-danger texto-negrita"><i class="fas fa-times-circle"></i> Para Reprobados (4to momento)</h6>
                                            <ul class="mb-0">
                                                <li class="texto-negrita">Si reprueba los 4 momentos: <strong class="text-danger texto-negrita">APLAZADO</strong></li>
                                                <li class="texto-negrita">Se registra automáticamente en la tabla <strong class="texto-negrita">estudiantes_aplazados</strong></li>
                                                <li class="texto-negrita">El sistema bloqueará su inscripción en año superior</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-azul" data-dismiss="modal">Entendido</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contenido Principal -->
            <div class="row">
                <!-- Columna Izquierda: Lista de Estudiantes -->
                <div class="col-md-8">
                    <div class="card border-azul">
                        <!-- HEADER CON AZUL MÁS CLARO Y LETRAS BLANCAS -->
                        <div class="card-header card-header-estudiantes">
                            <h5 class="mb-0">
                                <i class="fas fa-user-graduate"></i> Estudiantes con Materia Pendiente
                                <span class="badge badge-light ml-2"><?= count($estudiantes) ?></span>
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($estudiantes)): ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                                    <h4 class="text-success">¡Excelente!</h4>
                                    <p class="text-muted">No hay estudiantes con esta materia pendiente.</p>
                                    <a href="seleccion_materia_pendiente.php" class="btn btn-azul mt-2">
                                        <i class="fas fa-arrow-left"></i> Volver a Materias
                                    </a>
                                </div>
                            <?php else: ?>
                                <!-- Advertencia para momento 4 -->
                                <?php 
                                $estudiantes_momento4 = array_filter($estudiantes, function($est) {
                                    return ($est['intento_actual'] + 1) == 4;
                                });
                                if (count($estudiantes_momento4) > 0): ?>
                                <div class="alert alert-warning advertencia-momento4">
                                    <h5 class="texto-negrita"><i class="fas fa-exclamation-triangle"></i> ¡ATENCIÓN! ESTUDIANTES EN ÚLTIMO MOMENTO</h5>
                                    <p class="mb-2 texto-negrita">Hay <strong class="texto-negrita"><?= count($estudiantes_momento4) ?> estudiante(s)</strong> en el <strong class="texto-negrita">4to y último momento</strong>.</p>
                                    <p class="mb-0 texto-negrita"><strong class="texto-negrita">CONSECUENCIA:</strong> Si reprueban este momento, serán registrados automáticamente como <strong class="text-danger texto-negrita">REPITENTES</strong> y deberán repetir el año escolar.</p>
                                </div>
                                <?php endif; ?>
                                
                                <div class="table-responsive">
                                    <table class="table table-hover table-estudiantes">
                                        <thead class="bg-light">
                                            <tr>
                                                <th width="5%" class="texto-negrita">#</th>
                                                <th class="texto-negrita">Estudiante</th>
                                                <th width="15%" class="texto-negrita">Cédula</th>
                                                <th width="15%" class="texto-negrita">Último Momento</th>
                                                <th width="15%" class="texto-negrita">Nota Actual</th>
                                                <th width="15%" class="texto-negrita">Próximo Momento</th>
                                                <th width="15%" class="texto-negrita">Acción</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($estudiantes as $index => $est): 
                                                $nota_actual = $est['nota_actual'];
                                                $intento_actual = $est['intento_actual'];
                                                $proximo_intento = $intento_actual + 1;
                                                $nota_redondeada = round($nota_actual, 0, PHP_ROUND_HALF_UP);
                                                
                                                // Determinar color de la nota
                                                $color_nota = 'text-danger';
                                                $clase_fila = '';
                                                $advertencia = '';
                                                
                                                if ($nota_redondeada >= 10) {
                                                    $color_nota = 'estado-aprobado';
                                                } elseif ($nota_redondeada > 0) {
                                                    $color_nota = 'estado-en-proceso';
                                                }
                                                
                                                if ($proximo_intento == 4) {
                                                    $clase_fila = 'estudiante-momento4';
                                                    $advertencia = ' ÚLTIMO MOMENTO';
                                                }
                                            ?>
                                                <tr class="<?= $clase_fila ?>">
                                                    <td><?= $index + 1 ?></td>
                                                    <td>
                                                        <strong><?= htmlspecialchars($est['apellidos'] . ', ' . $est['nombres']) ?></strong>
                                                        <?php if (!empty($advertencia)): ?>
                                                            <small class="text-danger d-block">
                                                                <i class="fas fa-exclamation-circle"></i> <?= $advertencia ?>
                                                            </small>
                                                        <?php endif; ?>
                                                        <?php if (isset($historial_agrupado[$est['id_estudiante']])): ?>
                                                            <small class="text-muted d-block">
                                                                <i class="fas fa-history"></i> 
                                                                <?= count($historial_agrupado[$est['id_estudiante']]['historial']) ?> intento(s)
                                                            </small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?= htmlspecialchars($est['cedula']) ?></td>
                                                    <td>
                                                        <?php if ($intento_actual > 0): ?>
                                                            <span class="badge badge-momento-<?= $intento_actual ?>"><?= $intento_actual ?>°</span>
                                                        <?php else: ?>
                                                            <span class="badge badge-secondary">Ninguno</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <span class="font-weight-bold <?= $color_nota ?>">
                                                            <?= $nota_actual > 0 ? $nota_redondeada . '/20' : '-' ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <?php if ($proximo_intento <= 4): ?>
                                                            <span class="badge badge-momento-<?= $proximo_intento ?>">
                                                                <?= $proximo_intento ?>° Momento
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="badge badge-danger">Aplazado</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <button type="button" class="btn btn-sm btn-azul" 
                                                                data-toggle="modal" 
                                                                data-target="#registrarModal<?= $est['id_estudiante'] ?>">
                                                            <i class="fas fa-edit"></i> Registrar Nota
                                                        </button>
                                                    </td>
                                                </tr>
                                                
                                                <!-- Modal para registrar nota CON LETRAS EN NEGRITA -->
                                                <div class="modal fade" id="registrarModal<?= $est['id_estudiante'] ?>" tabindex="-1">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <form method="POST" action="" id="formNota<?= $est['id_estudiante'] ?>">
                                                                <div class="modal-header modal-header-estudiante">
                                                                    <h5 class="modal-title texto-negrita">
                                                                        <i class="fas fa-graduation-cap"></i> Registrar Nota
                                                                    </h5>
                                                                    <button type="button" class="close" data-dismiss="modal">
                                                                        <span>&times;</span>
                                                                    </button>
                                                                </div>
                                                                <div class="modal-body modal-body-estudiante">
                                                                    <div class="alert alert-info" style="background: linear-gradient(135deg, #e3f2fd, #bbdefb); border-left: 5px solid #2196f3;">
                                                                        <h6 class="texto-negrita" style="color: #333;">Estudiante: <strong><?= htmlspecialchars($est['apellidos'] . ', ' . $est['nombres']) ?></strong></h6>
                                                                        <p class="mb-1 texto-negrita" style="color: #333;"><strong class="texto-negrita">Próximo momento:</strong> <span class="badge badge-momento-<?= $proximo_intento ?>"><?= $proximo_intento ?>°</span></p>
                                                                        <p class="mb-0 texto-negrita" style="color: #333;"><strong class="texto-negrita">Intentos realizados:</strong> <?= $intento_actual ?></p>
                                                                        
                                                                        <?php if ($proximo_intento == 4): ?>
                                                                            <div class="advertencia-modal mt-3">
                                                                                <h6 class="texto-negrita"><i class="fas fa-exclamation-triangle text-warning"></i> ¡ATENCIÓN! ÚLTIMO MOMENTO</h6>
                                                                                <p class="mb-1 texto-negrita">Este es el <strong class="texto-negrita">4to y último momento</strong>.</p>
                                                                                <p class="mb-0 texto-negrita">Si el estudiante no aprueba, será registrado como <strong class="text-danger texto-negrita">REPITENTE</strong>.</p>
                                                                            </div>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                    
                                                                    <input type="hidden" name="id_estudiante" value="<?= $est['id_estudiante'] ?>">
                                                                    <input type="hidden" name="intento" value="<?= $proximo_intento ?>">
                                                                    
                                                                    <div class="form-group text-center">
                                                                        <label for="calificacion<?= $est['id_estudiante'] ?>" class="texto-negrita" style="color: #333;">
                                                                            <i class="fas fa-star"></i> Calificación (0-20)
                                                                        </label>
                                                                        <input type="number" 
                                                                               class="form-control nota-input mx-auto" 
                                                                               id="calificacion<?= $est['id_estudiante'] ?>" 
                                                                               name="calificacion" 
                                                                               min="0" 
                                                                               max="20" 
                                                                               step="0.1" 
                                                                               required
                                                                               value="<?= $nota_redondeada > 0 ? $nota_redondeada : '' ?>">
                                                                    </div>
                                                                    
                                                                    <div class="form-group">
                                                                        <label for="observacion<?= $est['id_estudiante'] ?>" class="texto-negrita" style="color: #333;">
                                                                            <i class="fas fa-comment"></i> Observación (Opcional)
                                                                        </label>
                                                                        <textarea class="form-control" 
                                                                                  id="observacion<?= $est['id_estudiante'] ?>" 
                                                                                  name="observacion" 
                                                                                  rows="2" 
                                                                                  placeholder="Ej: Examen parcial, trabajo práctico, etc."></textarea>
                                                                    </div>
                                                                    
                                                                    <!-- Historial del estudiante -->
                                                                    <?php if (isset($historial_agrupado[$est['id_estudiante']])): ?>
                                                                        <div class="mt-3">
                                                                            <h6 class="texto-negrita" style="color: #333;"><i class="fas fa-history"></i> Historial de Intentos</h6>
                                                                            <?php foreach ($historial_agrupado[$est['id_estudiante']]['historial'] as $hist): ?>
                                                                                <div class="historial-item">
                                                                                    <small>
                                                                                        <strong class="texto-negrita">Momento <?= $hist['intento'] ?>:</strong> 
                                                                                        <span class="<?= $hist['calificacion'] >= 10 ? 'text-success' : 'text-danger' ?>">
                                                                                            <?= $hist['calificacion'] ?>/20
                                                                                        </span>
                                                                                        - 
                                                                                        <?= date('d/m/Y', strtotime($hist['fecha_registro'])) ?>
                                                                                        <?php if (!empty($hist['observaciones'])): ?>
                                                                                            <br><small class="text-muted"><?= htmlspecialchars($hist['observaciones']) ?></small>
                                                                                        <?php endif; ?>
                                                                                    </small>
                                                                                </div>
                                                                            <?php endforeach; ?>
                                                                        </div>
                                                                    <?php endif; ?>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                                                    <button type="button" class="btn btn-azul" onclick="confirmarEnvioNota(<?= $est['id_estudiante'] ?>, <?= $proximo_intento ?>, '<?= addslashes($est['apellidos'] . ', ' . $est['nombres']) ?>')">
                                                                        <i class="fas fa-save"></i> Guardar Nota
                                                                    </button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Columna Derecha: Resumen SIMPLIFICADO -->
                <div class="col-md-4">
                    <div class="card border-success" style="background: linear-gradient(135deg, #f1f8e9, #e8f5e9);">
                        <div class="card-header bg-light" style="border-bottom: 2px solid #c8e6c9;">
                            <h5 class="mb-0 text-success">
                                <i class="fas fa-clipboard-check"></i> Resumen de la Materia
                            </h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between" style="background: transparent; border-color: #e8f5e9;">
                                    <span class="texto-negrita">Estudiantes pendientes:</span>
                                    <strong class="text-warning"><?= count($estudiantes) ?></strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between" style="background: transparent; border-color: #e8f5e9;">
                                    <span class="texto-negrita">Estudiantes aplazados:</span>
                                    <strong class="text-danger"><?= count($estudiantes_aplazados) ?></strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between" style="background: transparent; border-color: #e8f5e9;">
                                    <span class="texto-negrita">Nota mínima para aprobar:</span>
                                    <strong class="text-danger">10 pts</strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between" style="background: transparent; border-color: #e8f5e9;">
                                    <span class="texto-negrita">Momentos disponibles:</span>
                                    <strong class="text-info">4 por estudiante</strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between" style="background: transparent; border-color: #e8f5e9;">
                                    <span class="texto-negrita">Est. en último momento:</span>
                                    <strong class="text-danger"><?= count($estudiantes_momento4) ?></strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between" style="background: transparent; border-color: #e8f5e9;">
                                    <span class="texto-negrita">Profesor asignado:</span>
                                    <strong><?= htmlspecialchars($nombre_profesor) ?></strong>
                                </li>
                            </ul>
                            
                            <!-- Enlaces adicionales -->
                            <div class="mt-3">
                                <a href="historial_aprobados.php?seccion=<?= $id_seccion ?>&materia=<?= $id_materia ?>" 
                                   class="btn btn-outline-primary btn-block mb-2">
                                    <i class="fas fa-history"></i> Historial de Aprobados
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="../../assets/js/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// NUEVA FUNCIÓN: Ver detalle de estudiante aplazado
function verDetalleAplazado(idEstudiante, nombreEstudiante) {
    Swal.fire({
        title: '📋 Detalle del Estudiante Aplazado',
        html: `<div style='text-align: left; padding: 15px;'>
            <div style='background: #fff5f5; padding: 15px; border-radius: 8px; border-left: 4px solid #ff6b6b; margin-bottom: 15px;'>
                <h6 style='color: #333; font-weight: bold; margin-bottom: 10px;'><i class='fas fa-user-graduate'></i> ${nombreEstudiante}</h6>
                <p style='margin: 5px 0; color: #666; font-weight: bold;'><strong>ID Estudiante:</strong> ${idEstudiante}</p>
            </div>
            
            <div style='background: #ffeaea; padding: 12px; border-radius: 8px; margin-bottom: 15px;'>
                <h6 style='color: #d32f2f; font-weight: bold;'><i class='fas fa-exclamation-circle'></i> Estado Actual</h6>
                <ul style='margin: 8px 0 0 15px; color: #d32f2f; font-weight: bold;'>
                    <li>📋 <strong>Registrado como REPITENTE</strong></li>
                    <li>📝 <strong>Tabla: estudiantes_aplazados</strong></li>
                    <li>⏳ <strong>Estado: pendiente de reinscripción</strong></li>
                    <li>❌ <strong>No puede inscribirse en año superior</strong></li>
                </ul>
            </div>
            
            <div style='background: #e3f2fd; padding: 12px; border-radius: 8px;'>
                <h6 style='color: #1565c0; font-weight: bold;'><i class='fas fa-info-circle'></i> Para Habilitar Inscripción</h6>
                <ol style='margin: 8px 0 0 15px; color: #1976d2; font-weight: bold;'>
                    <li>📚 El estudiante debe repetir el año escolar actual</li>
                    <li>✅ Debe aprobar todas las materias pendientes</li>
                    <li>🔓 Un administrador debe cambiar su estado a "reinscrito"</li>
                </ol>
            </div>
        </div>`,
        icon: 'info',
        confirmButtonColor: '#ff6b6b',
        confirmButtonText: 'Entendido',
        showCancelButton: true,
        cancelButtonText: 'Cerrar',
        width: 500,
        heightAuto: false
    });
}

// Función para confirmar el envío del formulario
function confirmarEnvioNota(idEstudiante, momento, nombreEstudiante) {
    // Obtener los valores del formulario
    const calificacionInput = document.getElementById('calificacion' + idEstudiante);
    const calificacion = parseFloat(calificacionInput.value);
    
    if (isNaN(calificacion)) {
        Swal.fire({
            title: '❌ Error',
            html: `<div style='text-align: center; padding: 12px;'>
                <div style='font-size: 2.5em; margin-bottom: 8px; color: #ff6b6b;'>⚠️</div>
                <h4 style='color: #ff6b6b; margin-bottom: 8px; font-size: 1.1em; font-weight: bold;'>CAMPO REQUERIDO</h4>
                <div style='background: #ffeaea; padding: 10px; border-radius: 6px; border: 2px solid #ff6b6b; margin: 8px 0;'>
                    <p style='margin: 0; color: #d32f2f; font-size: 0.9em; font-weight: bold;'>Debe ingresar una calificación válida</p>
                </div>
                <p style='color: #666; margin-top: 8px; font-size: 0.8em; font-weight: bold;'>
                    La calificación debe ser un número entre 0 y 20
                </p>
            </div>`,
            icon: 'error',
            confirmButtonColor: '#ff6b6b',
            confirmButtonText: 'Entendido',
            background: '#fffafa',
            allowOutsideClick: false,
            width: 450,
            heightAuto: false
        });
        return;
    }
    
    if (calificacion < 0 || calificacion > 20) {
        Swal.fire({
            title: '⚠️ Valor Inválido',
            html: `<div style='text-align: center; padding: 12px;'>
                <div style='font-size: 2.5em; margin-bottom: 8px; color: #ff9800;'>📊</div>
                <h4 style='color: #f57c00; margin-bottom: 8px; font-size: 1.1em; font-weight: bold;'>RANGO INCORRECTO</h4>
                <div style='background: #fff3e0; padding: 10px; border-radius: 6px; border: 2px solid #ff9800; margin: 8px 0;'>
                    <p style='margin: 0; color: #f57c00; font-weight: bold;'>La calificación debe estar entre <strong>0</strong> y <strong>20</strong></p>
                </div>
                <p style='color: #666; margin-top: 8px; font-size: 0.8em; font-weight: bold;'>
                    Por favor, corrija la calificación
                </p>
            </div>`,
            icon: 'warning',
            confirmButtonColor: '#ff9800',
            confirmButtonText: 'Corregir',
            background: '#fffbf0',
            allowOutsideClick: false,
            width: 450,
            heightAuto: false
        });
        return;
    }
    
    const notaRedondeada = Math.round(calificacion);
    const aprobado = (notaRedondeada >= 10);
    
    let mensaje = '';
    let icono = 'question';
    let titulo = '';
    let colorPrincipal = '';
    let fondo = '';
    let confirmButtonText = 'Sí, Registrar';
    
    // NUEVA LÓGICA: Mensaje específico para 4to momento no aprobado
    if (momento == 4 && !aprobado) {
        titulo = '⚠️ ¡ATENCIÓN!';
        colorPrincipal = '#ff6b6b';
        icono = 'warning';
        fondo = '#fffafa';
        confirmButtonText = 'Sí, Registrar';
        
        mensaje = `<div style='text-align: center; padding: 12px;'>
            <div style='font-size: 2.5em; margin-bottom: 8px; color: #ff6b6b;'>🚫</div>
            <h4 style='color: #d32f2f; margin-bottom: 8px; font-size: 1.1em; font-weight: bold;'>4° MOMENTO - ÚLTIMA OPORTUNIDAD</h4>
            <p style='font-size: 0.95em; margin-bottom: 8px; color: #333; font-weight: bold;'>
                <strong style='color: #1565c0;'>${nombreEstudiante}</strong>
            </p>
            <div style='background: #fff5f5; padding: 12px; border-radius: 6px; border: 2px solid #ff6b6b; margin: 8px 0;'>
                <p style='margin: 0; font-size: 0.95em; font-weight: bold;'><strong>Nota:</strong> <span style='font-size: 1.2em; color: #d32f2f; font-weight: bold;'>${notaRedondeada}/20</span></p>
                <p style='margin: 4px 0 0 0; color: #ff6b6b; font-size: 0.85em; font-weight: bold;'><i class='fas fa-times-circle'></i> <strong>REPROBADO</strong></p>
            </div>
            <div style='background: #fff3e0; padding: 12px; border-radius: 6px; border: 2px solid #ffa726; margin-top: 10px;'>
                <p style='margin: 0; color: #d84315; font-weight: bold; font-size: 0.9em;'>
                    <i class='fas fa-exclamation-triangle'></i> El estudiante será registrado como REPITENTE
                </p>
            </div>
        </div>`;
    } else if (momento == 4 && aprobado) {
        titulo = '🎉 ¡FELICIDADES!';
        colorPrincipal = '#2e7d32';
        icono = 'success';
        fondo = '#f8fff8';
        confirmButtonText = 'Sí, Aprobar';
        mensaje = `<div style='text-align: center; padding: 12px;'>
            <div style='font-size: 2.5em; margin-bottom: 8px;'>🏆</div>
            <h4 style='color: #2e7d32; margin-bottom: 8px; font-size: 1.1em; font-weight: bold;'>¡APROBÓ EN EL ÚLTIMO MOMENTO!</h4>
            <p style='font-size: 0.95em; margin-bottom: 8px; color: #333; font-weight: bold;'>
                <strong style='color: #1565c0;'>${nombreEstudiante}</strong>
            </p>
            <div style='background: #e8f5e9; padding: 12px; border-radius: 6px; border: 2px solid #4caf50; margin: 8px 0;'>
                <p style='margin: 0; font-size: 0.95em; font-weight: bold;'><strong>Nota:</strong> <span style='font-size: 1.2em; color: #2e7d32; font-weight: bold;'>${notaRedondeada}/20</span></p>
                <p style='margin: 4px 0 0 0; color: #388e3c; font-size: 0.85em; font-weight: bold;'><i class='fas fa-check-circle'></i> <strong>APROBADO</strong></p>
            </div>
            <p style='color: #388e3c; font-weight: bold; margin-top: 8px; font-size: 0.85em;'>
                ✅ Será eliminado de pendientes
            </p>
        </div>`;
    } else if (aprobado) {
        titulo = '✨ ¡MUY BIEN!';
        colorPrincipal = '#2e7d32';
        icono = 'success';
        fondo = '#f8fff8';
        confirmButtonText = 'Sí, Aprobar';
        mensaje = `<div style='text-align: center; padding: 12px;'>
            <div style='font-size: 2.5em; margin-bottom: 8px;'>🌟</div>
            <h4 style='color: #2e7d32; margin-bottom: 8px; font-size: 1.1em; font-weight: bold;'>¡ESTUDIANTE APROBADO!</h4>
            <p style='font-size: 0.95em; margin-bottom: 8px; color: #333; font-weight: bold;'>
                <strong style='color: #1565c0;'>${nombreEstudiante}</strong><br>
                Momento <strong style='color: #0288d1;'>${momento}°</strong>
            </p>
            <div style='background: #e8f5e9; padding: 12px; border-radius: 6px; border: 2px solid #4caf50; margin: 8px 0;'>
                <p style='margin: 0; font-size: 0.95em; font-weight: bold;'><strong>Nota:</strong> <span style='font-size: 1.2em; color: #2e7d32; font-weight: bold;'>${notaRedondeada}/20</span></p>
                <p style='margin: 4px 0 0 0; color: #388e3c; font-size: 0.85em; font-weight: bold;'><i class='fas fa-check-circle'></i> <strong>APROBADO</strong></p>
            </div>
            <p style='color: #388e3c; font-weight: bold; margin-top: 8px; font-size: 0.85em;'>
                ✅ Será eliminado de pendientes
            </p>
        </div>`;
    } else {
        titulo = '📝 ¿REGISTRAR NOTA?';
        colorPrincipal = '#f57c00';
        icono = 'info';
        fondo = '#fffbf0';
        confirmButtonText = 'Sí, Registrar';
        mensaje = `<div style='text-align: center; padding: 12px;'>
            <div style='font-size: 2.5em; margin-bottom: 8px; color: #ff9800;'>📋</div>
            <h4 style='color: #f57c00; margin-bottom: 8px; font-size: 1.1em; font-weight: bold;'>REGISTRAR NOTA</h4>
            <p style='font-size: 0.95em; margin-bottom: 8px; color: #333; font-weight: bold;'>
                <strong style='color: #1565c0;'>${nombreEstudiante}</strong><br>
                Momento <strong style='color: #ff9800;'>${momento}°</span></strong>
            </p>
            <div style='background: #fff3e0; padding: 12px; border-radius: 6px; border: 2px solid #ff9800; margin: 8px 0;'>
                <p style='margin: 0; font-size: 0.95em; font-weight: bold;'><strong>Nota:</strong> <span style='font-size: 1.2em; color: #f57c00; font-weight: bold;'>${notaRedondeada}/20</span></p>
                <p style='margin: 4px 0 0 0; color: #f57c00; font-size: 0.85em; font-weight: bold;'><i class='fas fa-clock'></i> <strong>CONTINÚA</strong></p>
            </div>
            <div style='background: #f5f5f5; padding: 10px; border-radius: 6px; margin-top: 8px; border-left: 4px solid #9e9e9e;'>
                <p style='margin: 0; color: #666; font-weight: bold; font-size: 0.85em;'>
                    ➡️ Próximo momento: ${momento + 1}°
                </p>
            </div>
        </div>`;
    }
    
    Swal.fire({
        title: titulo,
        html: mensaje,
        icon: icono,
        showCancelButton: true,
        confirmButtonColor: colorPrincipal,
        cancelButtonColor: '#757575',
        confirmButtonText: confirmButtonText,
        cancelButtonText: 'Cancelar',
        background: fondo,
        allowOutsideClick: false,
        width: 450,
        heightAuto: false
    }).then((result) => {
        if (result.isConfirmed) {
            // Mostrar mensaje de carga
            Swal.fire({
                title: '⏳ Guardando...',
                html: `<div style='text-align: center; padding: 12px;'>
                    <div class="spinner-border text-primary" style="width: 2rem; height: 2rem;" role="status">
                        <span class="sr-only">Cargando...</span>
                    </div>
                    <p style='margin-top: 10px; color: #666; font-size: 0.9em; font-weight: bold;'>
                        Guardando nota del estudiante...
                    </p>
                </div>`,
                allowOutsideClick: false,
                showConfirmButton: false,
                background: '#f8fdff',
                width: 320,
                heightAuto: false
            });
            
            // Enviar formulario
            document.getElementById('formNota' + idEstudiante).submit();
        }
    });
}

$(document).ready(function() {
    // Validar nota al salir del campo
    $('.nota-input').blur(function() {
        let nota = parseFloat($(this).val());
        if (nota > 20) {
            $(this).val(20);
            Swal.fire({
                title: '📊 Nota Ajustada',
                html: `<div style='text-align: center; padding: 12px;'>
                    <div style='font-size: 2.5em; margin-bottom: 8px; color: #2196f3;'>🔧</div>
                    <p style='color: #666; font-size: 0.9em; font-weight: bold;'>La nota fue ajustada al máximo permitido: <strong style='color: #2196f3;'>20/20</strong></p>
                </div>`,
                icon: 'info',
                confirmButtonColor: '#2196f3',
                confirmButtonText: 'OK',
                background: '#f8fdff',
                width: 350,
                heightAuto: false
            });
        } else if (nota < 0) {
            $(this).val(0);
        }
    });
    
    // Mostrar instrucciones si es la primera vez
    if (!localStorage.getItem('instrucciones_vistas')) {
        setTimeout(() => {
            $('#instruccionesModal').modal('show');
            localStorage.setItem('instrucciones_vistas', 'true');
        }, 1000);
    }
    
    // Limpiar parámetros de la URL para evitar reenvíos
    function cleanURL() {
        const cleanURL = window.location.pathname + '?seccion=<?= $id_seccion ?>&materia=<?= $id_materia ?>';
        window.history.replaceState({}, document.title, cleanURL);
    }
    
    // Verificar si hay parámetros en la URL
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('registro') || urlParams.has('error')) {
        // Si ya se mostró un mensaje, limpiar URL después de 3 segundos
        setTimeout(cleanURL, 3000);
    }
});
</script>

<?php include('../../admin/layout/parte2.php'); ?>