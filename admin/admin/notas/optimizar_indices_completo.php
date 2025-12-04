<?php
/**
 * Script COMPLETO de optimización de índices para validación de conflictos
 * Ejecutar una sola vez para mejorar significativamente el rendimiento
 */

include('../../app/config.php');
header('Content-Type: text/html; charset=utf-8');

echo "<h2>Optimización Completa de Índices para Validación de Conflictos</h2>";
echo "<pre>";

try {
    // ============================================================
    // 1. ÍNDICES PARA horario_detalle
    // ============================================================
    echo "=== OPTIMIZACIÓN DE horario_detalle ===\n\n";
    
    $indices = $pdo->query("SHOW INDEXES FROM horario_detalle")->fetchAll(PDO::FETCH_ASSOC);
    $indicesExistentes = [];
    foreach ($indices as $indice) {
        $indicesExistentes[] = $indice['Key_name'];
    }
    
    // Índice compuesto para búsquedas por profesor y día (CRÍTICO para rendimiento)
    if (!in_array('idx_profesor_dia', $indicesExistentes)) {
        echo "1. Creando índice compuesto idx_profesor_dia (id_profesor, dia_semana)...\n";
        $pdo->exec("CREATE INDEX idx_profesor_dia ON horario_detalle(id_profesor, dia_semana)");
        echo "   ✓ Índice creado exitosamente\n\n";
    } else {
        echo "1. El índice idx_profesor_dia ya existe.\n\n";
    }
    
    // Índice compuesto para búsquedas por profesor, día y hora_inicio
    if (!in_array('idx_profesor_dia_hora', $indicesExistentes)) {
        echo "2. Creando índice compuesto idx_profesor_dia_hora (id_profesor, dia_semana, hora_inicio)...\n";
        $pdo->exec("CREATE INDEX idx_profesor_dia_hora ON horario_detalle(id_profesor, dia_semana, hora_inicio)");
        echo "   ✓ Índice creado exitosamente\n\n";
    } else {
        echo "2. El índice idx_profesor_dia_hora ya existe.\n\n";
    }
    
    // Índice para hora_inicio (útil para filtros de tiempo)
    if (!in_array('idx_hora_inicio', $indicesExistentes)) {
        echo "3. Creando índice idx_hora_inicio (hora_inicio)...\n";
        $pdo->exec("CREATE INDEX idx_hora_inicio ON horario_detalle(hora_inicio)");
        echo "   ✓ Índice creado exitosamente\n\n";
    } else {
        echo "3. El índice idx_hora_inicio ya existe.\n\n";
    }
    
    // ============================================================
    // 2. ÍNDICES PARA horarios
    // ============================================================
    echo "=== OPTIMIZACIÓN DE horarios ===\n\n";
    
    $indicesHorarios = $pdo->query("SHOW INDEXES FROM horarios")->fetchAll(PDO::FETCH_ASSOC);
    $indicesHorariosExistentes = [];
    foreach ($indicesHorarios as $indice) {
        $indicesHorariosExistentes[] = $indice['Key_name'];
    }
    
    // Índice compuesto para búsquedas por gestión, grado y sección
    if (!in_array('idx_gestion_grado_seccion', $indicesHorariosExistentes)) {
        echo "4. Creando índice compuesto idx_gestion_grado_seccion (id_gestion, id_grado, id_seccion)...\n";
        $pdo->exec("CREATE INDEX idx_gestion_grado_seccion ON horarios(id_gestion, id_grado, id_seccion)");
        echo "   ✓ Índice creado exitosamente\n\n";
    } else {
        echo "4. El índice idx_gestion_grado_seccion ya existe.\n\n";
    }
    
    // Índice compuesto para búsquedas por gestión (más usado)
    if (!in_array('idx_gestion', $indicesHorariosExistentes)) {
        echo "5. Verificando índice idx_gestion (id_gestion)...\n";
        // Verificar si existe como KEY simple
        $tieneGestion = false;
        foreach ($indicesHorarios as $idx) {
            if ($idx['Column_name'] == 'id_gestion' && $idx['Key_name'] != 'PRIMARY') {
                $tieneGestion = true;
                break;
            }
        }
        if (!$tieneGestion) {
            $pdo->exec("CREATE INDEX idx_gestion ON horarios(id_gestion)");
            echo "   ✓ Índice creado exitosamente\n\n";
        } else {
            echo "   ✓ Índice ya existe\n\n";
        }
    } else {
        echo "5. El índice idx_gestion ya existe.\n\n";
    }
    
    // ============================================================
    // 3. ANÁLISIS DE RENDIMIENTO
    // ============================================================
    echo "=== ANÁLISIS DE RENDIMIENTO ===\n\n";
    
    // Contar registros en horario_detalle
    $totalDetalles = $pdo->query("SELECT COUNT(*) as total FROM horario_detalle")->fetch(PDO::FETCH_ASSOC);
    echo "Total de registros en horario_detalle: " . $totalDetalles['total'] . "\n";
    
    // Contar registros en horarios
    $totalHorarios = $pdo->query("SELECT COUNT(*) as total FROM horarios")->fetch(PDO::FETCH_ASSOC);
    echo "Total de registros en horarios: " . $totalHorarios['total'] . "\n\n";
    
    // Verificar índices creados
    echo "Índices en horario_detalle:\n";
    $indicesFinales = $pdo->query("SHOW INDEXES FROM horario_detalle")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($indicesFinales as $idx) {
        if ($idx['Key_name'] != 'PRIMARY') {
            echo "  - {$idx['Key_name']}: " . implode(', ', array_unique(array_column(array_filter($indicesFinales, function($i) use ($idx) {
                return $i['Key_name'] == $idx['Key_name'];
            }), 'Column_name'))) . "\n";
        }
    }
    
    echo "\n✅ Optimización completada exitosamente!\n";
    echo "Las consultas de validación de conflictos deberían ser significativamente más rápidas ahora.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Es posible que algunos índices ya existan o haya un problema de permisos.\n";
}

echo "</pre>";
?>

