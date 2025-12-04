<?php
/**
 * Endpoint AJAX para verificar conflictos de profesores en tiempo real
 * Usa la misma lógica robusta que api_check_profesor.php
 * GET/POST: id_profesor, dia_semana, hora_inicio, hora_fin, id_horario (opcional), id_bloque_excluir (opcional)
 */

$configPath = dirname(__DIR__) . '/../../app/config.php';
if (file_exists($configPath)) {
    include($configPath);
} else {
    include(__DIR__ . '/../../../app/config.php');
}

header('Content-Type: application/json; charset=utf-8');

try {
    $id_profesor = isset($_REQUEST['id_profesor']) ? (int)$_REQUEST['id_profesor'] : 0;
    $dia_semana = isset($_REQUEST['dia_semana']) ? trim($_REQUEST['dia_semana']) : '';
    $hora_inicio = isset($_REQUEST['hora_inicio']) ? trim($_REQUEST['hora_inicio']) : '';
    $hora_fin = isset($_REQUEST['hora_fin']) ? trim($_REQUEST['hora_fin']) : '';
    $id_horario = isset($_REQUEST['id_horario']) ? (int)$_REQUEST['id_horario'] : 0;
    $id_bloque_excluir = isset($_REQUEST['id_bloque_excluir']) ? (int)$_REQUEST['id_bloque_excluir'] : 0;
    
    if ($id_profesor <= 0 || empty($dia_semana) || empty($hora_inicio) || empty($hora_fin)) {
        echo json_encode([
            'success' => false,
            'message' => 'Datos incompletos',
            'conflicto' => false
        ]);
        exit;
    }
    
    // Obtener gestión activa
    $gestion = $pdo->query("SELECT id_gestion FROM gestiones WHERE estado = 1 ORDER BY desde DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    if (!$gestion) {
        echo json_encode([
            'success' => false,
            'message' => 'No hay gestión activa',
            'conflicto' => false
        ]);
        exit;
    }
    
    // ============================================================
    // NORMALIZACIÓN DE PARÁMETROS
    // ============================================================
    
    // Normalizar día: capitalizar primera letra (Lunes, Martes, etc.)
    $dia_normalizado = ucfirst(strtolower(trim($dia_semana)));
    
    // Normalizar horas: asegurar formato HH:MM:SS
    $hora_inicio_normalizada = strlen($hora_inicio) == 5 ? $hora_inicio . ':00' : $hora_inicio;
    $hora_fin_normalizada = strlen($hora_fin) == 5 ? $hora_fin . ':00' : $hora_fin;
    
    // Validar formato de horas
    if (!preg_match('/^\d{2}:\d{2}:\d{2}$/', $hora_inicio_normalizada) || 
        !preg_match('/^\d{2}:\d{2}:\d{2}$/', $hora_fin_normalizada)) {
        echo json_encode([
            'success' => false,
            'message' => 'Formato de hora inválido',
            'conflicto' => false
        ]);
        exit;
    }
    
    // Mapeo de hora_inicio a hora_fin estándar (para bloques con hora_fin inválida)
    $mapa_horas_fin = [
        '07:50:00' => '08:30:00',
        '08:30:00' => '09:10:00',
        '09:10:00' => '09:50:00',
        '10:10:00' => '10:50:00',
        '10:50:00' => '11:30:00',
        '11:30:00' => '12:10:00'
    ];
    
    // ============================================================
    // OBTENER TODOS LOS BLOQUES DEL PROFESOR EN ESE DÍA
    // ============================================================
    // IMPORTANTE: Buscar en TODOS los horarios de la gestión activa,
    // sin importar grado o sección. Un profesor no puede estar en dos
    // lugares al mismo tiempo, sin importar el grado o sección.
    
    $sqlBloques = "SELECT hd.id_detalle, hd.id_horario, hd.hora_inicio, hd.hora_fin, 
                          h.id_grado, h.id_seccion
                   FROM horario_detalle hd
                   INNER JOIN horarios h ON h.id_horario = hd.id_horario
                   WHERE h.id_gestion = :gestion 
                     AND hd.id_profesor = :p 
                     AND hd.id_profesor IS NOT NULL
                     AND hd.dia_semana = :d
                     AND hd.hora_inicio IS NOT NULL
                     AND hd.hora_inicio != '00:00:00'";
    
    // Excluir SOLO el bloque actual si se está editando (para evitar auto-conflicto)
    // Detectar PK de horario_detalle
    $cols = $pdo->query("SHOW COLUMNS FROM horario_detalle")->fetchAll(PDO::FETCH_COLUMN);
    $pk = null;
    foreach (['id_detalle','id_horario_detalle','id'] as $c) { 
        if (in_array($c, $cols, true)) { 
            $pk = $c; 
            break; 
        } 
    }
    
    if ($pk && $id_bloque_excluir > 0) {
        $sqlBloques .= " AND hd.$pk != :id_bloque_excluir";
    }
    
    $stmtBloques = $pdo->prepare($sqlBloques);
    $paramsBloques = [
        ':gestion' => $gestion['id_gestion'],
        ':p' => $id_profesor,
        ':d' => $dia_normalizado
    ];
    if ($pk && $id_bloque_excluir > 0) {
        $paramsBloques[':id_bloque_excluir'] = $id_bloque_excluir;
    }
    $stmtBloques->execute($paramsBloques);
    $bloques = $stmtBloques->fetchAll(PDO::FETCH_ASSOC);
    
    // ============================================================
    // FUNCIÓN PARA VERIFICAR SOLAPAMIENTO
    // ============================================================
    // Dos intervalos [a1, b1] y [a2, b2] se solapan si: a1 < b2 AND b1 > a2
    $intervalosSeSolapan = function($inicio1, $fin1, $inicio2, $fin2) {
        // Normalizar formatos (asegurar HH:MM:SS)
        if (strlen($inicio1) == 5) $inicio1 .= ':00';
        if (strlen($fin1) == 5) $fin1 .= ':00';
        if (strlen($inicio2) == 5) $inicio2 .= ':00';
        if (strlen($fin2) == 5) $fin2 .= ':00';
        
        // Convertir a segundos desde medianoche para comparación precisa
        list($h1, $m1, $s1) = explode(':', $inicio1);
        list($hf1, $mf1, $sf1) = explode(':', $fin1);
        list($h2, $m2, $s2) = explode(':', $inicio2);
        list($hf2, $mf2, $sf2) = explode(':', $fin2);
        
        $seg1_inicio = ($h1 * 3600) + ($m1 * 60) + $s1;
        $seg1_fin = ($hf1 * 3600) + ($mf1 * 60) + $sf1;
        $seg2_inicio = ($h2 * 3600) + ($m2 * 60) + $s2;
        $seg2_fin = ($hf2 * 3600) + ($mf2 * 60) + $sf2;
        
        // Verificar solapamiento: inicio1 < fin2 AND fin1 > inicio2
        return ($seg1_inicio < $seg2_fin && $seg1_fin > $seg2_inicio);
    };
    
    // ============================================================
    // BUSCAR CONFLICTOS
    // ============================================================
    $conflictoCheck = null;
    foreach ($bloques as $bloque) {
        $hora_inicio_existente = $bloque['hora_inicio'];
        $hora_fin_existente = $bloque['hora_fin'];
        
        // Si hora_fin es inválida, inferirla
        if (empty($hora_fin_existente) || $hora_fin_existente == '00:00:00' || $hora_fin_existente == '00:00') {
            $hora_inicio_key = substr($hora_inicio_existente, 0, 5) . ':00';
            $hora_fin_existente = $mapa_horas_fin[$hora_inicio_key] ?? $hora_fin_existente;
        }
        
        // Normalizar formato (asegurar HH:MM:SS)
        if (strlen($hora_fin_existente) == 5) {
            $hora_fin_existente .= ':00';
        }
        if (strlen($hora_inicio_existente) == 5) {
            $hora_inicio_existente .= ':00';
        }
        
        // Verificar solapamiento usando la función auxiliar
        if ($intervalosSeSolapan(
            $hora_inicio_existente, 
            $hora_fin_existente,
            $hora_inicio_normalizada,
            $hora_fin_normalizada
        )) {
            $conflictoCheck = $bloque;
            break; // Encontrar el primer conflicto es suficiente
        }
    }
    
    // Si no hay conflicto, retornar inmediatamente
    if (!$conflictoCheck) {
        echo json_encode([
            'success' => true,
            'conflicto' => false,
            'message' => 'No hay conflictos'
        ]);
        exit;
    }
    
    // ============================================================
    // OBTENER DETALLES COMPLETOS DEL CONFLICTO
    // ============================================================
    $sql = "SELECT hd.id_detalle, hd.id_horario, hd.dia_semana, hd.hora_inicio, hd.hora_fin, 
                   hd.id_materia, hd.id_profesor,
                   m.nombre_materia, 
                   s.nombre_seccion, 
                   g.grado, 
                   h.aula, 
                   h.id_grado, 
                   h.id_seccion,
                   CONCAT(p.nombres,' ',p.apellidos) AS nombre_profesor
            FROM horario_detalle hd
            INNER JOIN horarios h ON h.id_horario = hd.id_horario
            INNER JOIN materias m ON m.id_materia = hd.id_materia
            INNER JOIN secciones s ON s.id_seccion = h.id_seccion
            INNER JOIN grados g ON g.id_grado = h.id_grado
            LEFT JOIN profesores p ON p.id_profesor = hd.id_profesor
            WHERE hd.id_detalle = :id_detalle
            LIMIT 1";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id_detalle' => $conflictoCheck['id_detalle']]);
    $conflicto = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Normalizar hora_fin para mostrarla correctamente
    if ($conflicto && (empty($conflicto['hora_fin']) || $conflicto['hora_fin'] == '00:00:00' || $conflicto['hora_fin'] == '00:00')) {
        $hora_inicio_key = substr($conflicto['hora_inicio'], 0, 5) . ':00';
        $conflicto['hora_fin'] = $mapa_horas_fin[$hora_inicio_key] ?? $conflicto['hora_fin'];
    }
    
    // Retornar conflicto con información completa
    echo json_encode([
        'success' => true,
        'conflicto' => true,
        'message' => 'El profesor ' . $conflicto['nombre_profesor'] . ' ya está asignado en ' . $conflicto['grado'] . ' - Sección ' . $conflicto['nombre_seccion'] . ' el mismo día y hora. No puede tener dos clases simultáneas.',
        'datos' => $conflicto
    ]);
    
} catch (Exception $e) {
    error_log("Error en verificar_conflicto_profesor.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    echo json_encode([
        'success' => false,
        'message' => 'Error al validar conflicto: ' . $e->getMessage(),
        'conflicto' => false
    ]);
}
?>
