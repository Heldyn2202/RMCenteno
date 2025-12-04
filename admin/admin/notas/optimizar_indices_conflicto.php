<?php
/**
 * Script para crear índices compuestos que optimicen las consultas de conflictos
 * Ejecutar una sola vez para mejorar el rendimiento
 */

include('../../app/config.php');
header('Content-Type: text/html; charset=utf-8');

echo "<h2>Optimización de Índices para Conflictos</h2>";
echo "<pre>";

try {
    // Verificar índices existentes
    $indices = $pdo->query("SHOW INDEXES FROM horario_detalle")->fetchAll(PDO::FETCH_ASSOC);
    $indicesExistentes = [];
    foreach ($indices as $indice) {
        $indicesExistentes[] = $indice['Key_name'];
    }
    
    echo "Índices existentes en horario_detalle:\n";
    foreach ($indicesExistentes as $idx) {
        echo "  - $idx\n";
    }
    echo "\n";
    
    // Crear índice compuesto para búsquedas por profesor y día (si no existe)
    if (!in_array('idx_profesor_dia', $indicesExistentes)) {
        echo "Creando índice compuesto idx_profesor_dia (id_profesor, dia_semana)...\n";
        $pdo->exec("CREATE INDEX idx_profesor_dia ON horario_detalle(id_profesor, dia_semana)");
        echo "✓ Índice creado exitosamente\n\n";
    } else {
        echo "El índice idx_profesor_dia ya existe.\n\n";
    }
    
    // Crear índice compuesto para horarios (id_gestion, id_grado, id_seccion) si no existe
    $indicesHorarios = $pdo->query("SHOW INDEXES FROM horarios")->fetchAll(PDO::FETCH_ASSOC);
    $indicesHorariosExistentes = [];
    foreach ($indicesHorarios as $indice) {
        $indicesHorariosExistentes[] = $indice['Key_name'];
    }
    
    if (!in_array('idx_gestion_grado_seccion', $indicesHorariosExistentes)) {
        echo "Creando índice compuesto idx_gestion_grado_seccion (id_gestion, id_grado, id_seccion)...\n";
        $pdo->exec("CREATE INDEX idx_gestion_grado_seccion ON horarios(id_gestion, id_grado, id_seccion)");
        echo "✓ Índice creado exitosamente\n\n";
    } else {
        echo "El índice idx_gestion_grado_seccion ya existe.\n\n";
    }
    
    echo "Optimización completada.\n";
    echo "Las consultas de conflictos deberían ser más rápidas ahora.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Es posible que los índices ya existan o haya un problema de permisos.\n";
}

echo "</pre>";
?>

