<?php
/**
 * Script de diagnóstico para verificar por qué no se detecta un conflicto
 * Uso: diagnostico_conflicto_profesor.php?id_profesor=7&dia_semana=Lunes&hora_inicio=07:50:00&hora_fin=08:30:00&id_horario=7&id_bloque_excluir=32
 */

include('../../app/config.php');
header('Content-Type: text/html; charset=utf-8');

$id_profesor = isset($_GET['id_profesor']) ? (int)$_GET['id_profesor'] : 7;
$dia_semana = isset($_GET['dia_semana']) ? trim($_GET['dia_semana']) : 'Lunes';
$hora_inicio = isset($_GET['hora_inicio']) ? trim($_GET['hora_inicio']) : '07:50:00';
$hora_fin = isset($_GET['hora_fin']) ? trim($_GET['hora_fin']) : '08:30:00';
$id_horario = isset($_GET['id_horario']) ? (int)$_GET['id_horario'] : 7;
$id_bloque_excluir = isset($_GET['id_bloque_excluir']) ? (int)$_GET['id_bloque_excluir'] : 32;

echo "<h2>Diagnóstico de Conflicto de Profesor</h2>";
echo "<pre>";
echo "Parámetros recibidos:\n";
echo "  id_profesor: $id_profesor\n";
echo "  dia_semana: $dia_semana\n";
echo "  hora_inicio: $hora_inicio\n";
echo "  hora_fin: $hora_fin\n";
echo "  id_horario: $id_horario\n";
echo "  id_bloque_excluir: $id_bloque_excluir\n";
echo "\n";

// Normalizar horas
if (strlen($hora_inicio) == 5) {
    $hora_inicio .= ':00';
}
if (strlen($hora_fin) == 5) {
    $hora_fin .= ':00';
}

// Detectar PK
$cols = $pdo->query("SHOW COLUMNS FROM horario_detalle")->fetchAll(PDO::FETCH_COLUMN);
$pk = null;
foreach (['id_detalle','id_horario_detalle','id'] as $c) { 
    if (in_array($c, $cols, true)) { 
        $pk = $c; 
        break; 
    } 
}

echo "PK detectada: $pk\n\n";

// PASO 1: Verificar información del horario actual
echo "=== PASO 1: Información del horario actual ===\n";
$stmtHorarioActual = $pdo->prepare("SELECT id_gestion, id_grado, id_seccion, h.* FROM horarios h WHERE h.id_horario = ?");
$stmtHorarioActual->execute([$id_horario]);
$horarioActual = $stmtHorarioActual->fetch(PDO::FETCH_ASSOC);
if ($horarioActual) {
    echo "Horario actual:\n";
    echo "  id_horario: {$horarioActual['id_horario']}\n";
    echo "  id_gestion: {$horarioActual['id_gestion']}\n";
    echo "  id_grado: {$horarioActual['id_grado']}\n";
    echo "  id_seccion: {$horarioActual['id_seccion']}\n";
    
    // Obtener nombre del grado y sección
    $stmtGrado = $pdo->prepare("SELECT grado FROM grados WHERE id_grado = ?");
    $stmtGrado->execute([$horarioActual['id_grado']]);
    $grado = $stmtGrado->fetch(PDO::FETCH_ASSOC);
    echo "  grado: " . ($grado ? $grado['grado'] : 'N/A') . "\n";
    
    $stmtSeccion = $pdo->prepare("SELECT nombre_seccion FROM secciones WHERE id_seccion = ?");
    $stmtSeccion->execute([$horarioActual['id_seccion']]);
    $seccion = $stmtSeccion->fetch(PDO::FETCH_ASSOC);
    echo "  seccion: " . ($seccion ? $seccion['nombre_seccion'] : 'N/A') . "\n";
} else {
    echo "ERROR: No se encontró el horario con id_horario = $id_horario\n";
}
echo "\n";

// PASO 2: Buscar TODOS los bloques del profesor en el mismo día (sin exclusiones)
echo "=== PASO 2: Todos los bloques del profesor en $dia_semana ===\n";
$sqlTodos = "SELECT hd.$pk as id_detalle, 
                   h.id_horario, 
                   h.id_grado, 
                   h.id_seccion, 
                   g.grado AS nombre_grado, 
                   s.nombre_seccion, 
                   CONCAT(p.nombres, ' ', p.apellidos) AS nombre_profesor,
                   hd.dia_semana, 
                   hd.hora_inicio, 
                   hd.hora_fin,
                   hd.id_profesor
            FROM horario_detalle hd
            INNER JOIN horarios h ON h.id_horario = hd.id_horario
            INNER JOIN grados g ON g.id_grado = h.id_grado
            INNER JOIN secciones s ON s.id_seccion = h.id_seccion
            INNER JOIN profesores p ON p.id_profesor = hd.id_profesor
            WHERE hd.id_profesor = ? 
              AND hd.id_profesor IS NOT NULL
              AND hd.dia_semana = ?";
$stmtTodos = $pdo->prepare($sqlTodos);
$stmtTodos->execute([$id_profesor, $dia_semana]);
$todosLosBloques = $stmtTodos->fetchAll(PDO::FETCH_ASSOC);

echo "Total de bloques encontrados: " . count($todosLosBloques) . "\n";
foreach ($todosLosBloques as $bloque) {
    echo "  - Bloque ID: {$bloque['id_detalle']}, Horario: {$bloque['id_horario']}, {$bloque['nombre_grado']} - {$bloque['nombre_seccion']}, {$bloque['hora_inicio']} - {$bloque['hora_fin']}\n";
}
echo "\n";

// PASO 3: Verificar solapamiento con el horario que se está validando
echo "=== PASO 3: Bloques que se solapan con $hora_inicio - $hora_fin ===\n";
$bloquesSolapados = [];
foreach ($todosLosBloques as $bloque) {
    $hi_bloque = $bloque['hora_inicio'];
    $hf_bloque = $bloque['hora_fin'];
    
    // Verificar solapamiento: inicio1 < fin2 AND fin1 > inicio2
    if ($hi_bloque < $hora_fin && $hf_bloque > $hora_inicio) {
        $bloquesSolapados[] = $bloque;
        echo "  ✓ SOLAPAMIENTO: Bloque ID {$bloque['id_detalle']}, {$bloque['nombre_grado']} - {$bloque['nombre_seccion']}, {$bloque['hora_inicio']} - {$bloque['hora_fin']}\n";
    } else {
        echo "  ✗ NO solapa: Bloque ID {$bloque['id_detalle']}, {$bloque['hora_inicio']} - {$bloque['hora_fin']}\n";
    }
}
echo "Total de bloques que se solapan: " . count($bloquesSolapados) . "\n\n";

// PASO 4: Aplicar exclusiones
echo "=== PASO 4: Aplicar exclusiones ===\n";
$bloquesDespuesExclusion = [];
foreach ($bloquesSolapados as $bloque) {
    $excluir = false;
    $razon = [];
    
    // Excluir el bloque actual si se está editando
    if ($id_bloque_excluir > 0 && $bloque['id_detalle'] == $id_bloque_excluir) {
        $excluir = true;
        $razon[] = "Es el bloque que se está editando (id_detalle = $id_bloque_excluir)";
    }
    
    // Excluir otros horarios duplicados (mismo grado-sección-gestión pero diferente id_horario)
    if ($horarioActual && !$excluir) {
        if ($bloque['id_gestion'] == $horarioActual['id_gestion'] && 
            $bloque['id_grado'] == $horarioActual['id_grado'] && 
            $bloque['id_seccion'] == $horarioActual['id_seccion'] && 
            $bloque['id_horario'] != $id_horario) {
            $excluir = true;
            $razon[] = "Es un horario duplicado (mismo grado-sección-gestión pero diferente id_horario)";
        }
    }
    
    if ($excluir) {
        echo "  ✗ EXCLUIDO: Bloque ID {$bloque['id_detalle']}, {$bloque['nombre_grado']} - {$bloque['nombre_seccion']} - " . implode(", ", $razon) . "\n";
    } else {
        $bloquesDespuesExclusion[] = $bloque;
        echo "  ✓ CONFLICTO VÁLIDO: Bloque ID {$bloque['id_detalle']}, {$bloque['nombre_grado']} - {$bloque['nombre_seccion']}, {$bloque['hora_inicio']} - {$bloque['hora_fin']}\n";
    }
}
echo "Total de conflictos válidos después de exclusiones: " . count($bloquesDespuesExclusion) . "\n\n";

// PASO 5: Ejecutar la query exacta que usa verificar_conflicto_profesor.php
echo "=== PASO 5: Query exacta de verificar_conflicto_profesor.php ===\n";
$sql = "SELECT hd.$pk as id_detalle, 
                   h.id_horario, 
                   h.id_grado, 
                   h.id_seccion, 
                   g.grado AS nombre_grado, 
                   s.nombre_seccion, 
                   CONCAT(p.nombres, ' ', p.apellidos) AS nombre_profesor,
                   hd.dia_semana, 
                   hd.hora_inicio, 
                   hd.hora_fin,
                   hd.id_profesor as id_profesor_real
            FROM horario_detalle hd
            INNER JOIN horarios h ON h.id_horario = hd.id_horario
            INNER JOIN grados g ON g.id_grado = h.id_grado
            INNER JOIN secciones s ON s.id_seccion = h.id_seccion
            INNER JOIN profesores p ON p.id_profesor = hd.id_profesor
            WHERE hd.id_profesor = ? 
              AND hd.id_profesor IS NOT NULL
              AND hd.dia_semana = ?";
$params = [$id_profesor, $dia_semana];

if ($id_bloque_excluir > 0) {
    $sql .= " AND hd.$pk != ?";
    $params[] = $id_bloque_excluir;
}

if ($horarioActual) {
    $sql .= " AND NOT (h.id_gestion = ? AND h.id_grado = ? AND h.id_seccion = ? AND h.id_horario != ?)";
    $params[] = $horarioActual['id_gestion'];
    $params[] = $horarioActual['id_grado'];
    $params[] = $horarioActual['id_seccion'];
    $params[] = $id_horario;
}

$sql .= " AND hd.hora_fin IS NOT NULL 
          AND hd.hora_fin != '00:00:00'
          AND hd.hora_inicio IS NOT NULL
          AND hd.hora_inicio != '00:00:00'
          AND hd.hora_inicio < ? 
          AND hd.hora_fin > ?";
$params[] = $hora_fin;
$params[] = $hora_inicio;

echo "SQL:\n$sql\n\n";
echo "Parámetros:\n";
print_r($params);
echo "\n";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$conflicto = $stmt->fetch(PDO::FETCH_ASSOC);

if ($conflicto) {
    echo "✓ CONFLICTO DETECTADO:\n";
    print_r($conflicto);
} else {
    echo "✗ NO SE DETECTÓ CONFLICTO\n";
    echo "\nPosibles razones:\n";
    echo "1. El bloque está siendo excluido incorrectamente por la condición de duplicados\n";
    echo "2. El bloque tiene hora_inicio o hora_fin inválidos (NULL o '00:00:00')\n";
    echo "3. No hay solapamiento real de horarios\n";
    echo "4. El profesor no está asignado en ese bloque\n";
}

echo "</pre>";
?>

