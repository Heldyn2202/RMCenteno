<?php
/**
 * Script CLI para ejecutar la consolidación de horarios duplicados
 * Ejecuta directamente la consolidación sin interfaz web
 */

include('../../app/config.php');

echo "🔧 Iniciando consolidación de horarios duplicados...\n\n";

try {
    // Detectar PK de horario_detalle
    $cols = $pdo->query("SHOW COLUMNS FROM horario_detalle")->fetchAll(PDO::FETCH_COLUMN);
    $pk = null;
    foreach (['id_detalle','id_horario_detalle','id'] as $c) { 
        if (in_array($c, $cols, true)) { 
            $pk = $c; 
            break; 
        } 
    }
    
    if (!$pk) {
        throw new Exception('No se pudo detectar la clave primaria de horario_detalle');
    }
    
    // 1. Identificar duplicados
    $sqlDuplicados = "
        SELECT 
            h.id_gestion,
            h.id_grado,
            h.id_seccion,
            g.grado AS nombre_grado,
            s.nombre_seccion,
            COUNT(*) as total_horarios,
            GROUP_CONCAT(h.id_horario ORDER BY h.id_horario DESC SEPARATOR ', ') as ids_horarios
        FROM horarios h
        INNER JOIN grados g ON g.id_grado = h.id_grado
        INNER JOIN secciones s ON s.id_seccion = h.id_seccion
        GROUP BY h.id_gestion, h.id_grado, h.id_seccion
        HAVING COUNT(*) > 1
        ORDER BY total_horarios DESC
    ";
    
    $stmtDuplicados = $pdo->query($sqlDuplicados);
    $duplicados = $stmtDuplicados->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($duplicados)) {
        echo "✅ No hay horarios duplicados. La base de datos está limpia.\n";
        exit(0);
    }
    
    echo "📋 Encontrados " . count($duplicados) . " grupos de horarios duplicados\n\n";
    
    // 2. Para cada grupo, determinar cuál mantener
    $planConsolidacion = [];
    
    foreach ($duplicados as $dup) {
        $idsArray = array_map('trim', explode(',', $dup['ids_horarios']));
        $idsArray = array_map('intval', $idsArray);
        
        // Analizar cada horario del grupo
        $horariosInfo = [];
        foreach ($idsArray as $idHorario) {
            // Contar detalles
            $stmtCount = $pdo->prepare("SELECT COUNT(*) as total FROM horario_detalle WHERE id_horario = ?");
            $stmtCount->execute([$idHorario]);
            $countResult = $stmtCount->fetch(PDO::FETCH_ASSOC);
            $totalDetalles = $countResult['total'] ?? 0;
            
            // Obtener información del horario
            $stmtHorario = $pdo->prepare("SELECT * FROM horarios WHERE id_horario = ?");
            $stmtHorario->execute([$idHorario]);
            $horarioInfo = $stmtHorario->fetch(PDO::FETCH_ASSOC);
            
            $estado = isset($horarioInfo['estado']) ? $horarioInfo['estado'] : 'BORRADOR';
            $esPublicado = ($estado === 'PUBLICADO');
            
            $horariosInfo[] = [
                'id' => $idHorario,
                'detalles' => $totalDetalles,
                'estado' => $estado,
                'es_publicado' => $esPublicado,
                'info' => $horarioInfo
            ];
        }
        
        // Determinar cuál mantener
        usort($horariosInfo, function($a, $b) {
            // Primero por estado (PUBLICADO primero)
            if ($a['es_publicado'] && !$b['es_publicado']) return -1;
            if (!$a['es_publicado'] && $b['es_publicado']) return 1;
            
            // Luego por cantidad de detalles
            if ($a['detalles'] != $b['detalles']) {
                return $b['detalles'] - $a['detalles'];
            }
            
            // Finalmente por ID (más reciente)
            return $b['id'] - $a['id'];
        });
        
        $mantener = $horariosInfo[0];
        $eliminar = array_slice($horariosInfo, 1);
        
        $planConsolidacion[] = [
            'gestion' => $dup['id_gestion'],
            'grado' => $dup['nombre_grado'],
            'seccion' => $dup['nombre_seccion'],
            'id_grado' => $dup['id_grado'],
            'id_seccion' => $dup['id_seccion'],
            'mantener' => $mantener,
            'eliminar' => $eliminar
        ];
    }
    
    // 3. Mostrar plan
    echo "📝 Plan de Consolidación:\n";
    echo str_repeat("=", 80) . "\n";
    foreach ($planConsolidacion as $plan) {
        $totalDetallesEliminar = array_sum(array_column($plan['eliminar'], 'detalles'));
        echo "Grado: {$plan['grado']} - Sección: {$plan['seccion']}\n";
        echo "  Mantener: ID {$plan['mantener']['id']} ({$plan['mantener']['detalles']} detalles, {$plan['mantener']['estado']})\n";
        echo "  Eliminar: ";
        foreach ($plan['eliminar'] as $elim) {
            echo "ID {$elim['id']} ({$elim['detalles']} detalles) ";
        }
        echo "\n  Detalles a mover: {$totalDetallesEliminar}\n";
        echo str_repeat("-", 80) . "\n";
    }
    
    // 4. Ejecutar consolidación
    echo "\n⚙️ Ejecutando consolidación...\n\n";
    
    $pdo->beginTransaction();
    
    $totalMovidos = 0;
    $totalEliminados = 0;
    $conflictos = [];
    
    try {
        foreach ($planConsolidacion as $plan) {
            $idHorarioMantener = $plan['mantener']['id'];
            
            echo "📦 Consolidando: {$plan['grado']} - {$plan['seccion']}\n";
            echo "   Manteniendo horario ID {$idHorarioMantener}\n";
            
            foreach ($plan['eliminar'] as $elim) {
                $idHorarioEliminar = $elim['id'];
                
                echo "   Procesando horario ID {$idHorarioEliminar}:\n";
                
                // Obtener todos los detalles del horario a eliminar
                $stmtDetalles = $pdo->prepare("SELECT * FROM horario_detalle WHERE id_horario = ?");
                $stmtDetalles->execute([$idHorarioEliminar]);
                $detallesEliminar = $stmtDetalles->fetchAll(PDO::FETCH_ASSOC);
                
                foreach ($detallesEliminar as $detalle) {
                    // Verificar si ya existe un detalle en el horario que se mantiene con el mismo día/hora
                    $stmtExiste = $pdo->prepare("
                        SELECT $pk FROM horario_detalle 
                        WHERE id_horario = ? 
                          AND dia_semana = ? 
                          AND hora_inicio = ? 
                          AND hora_fin = ?
                    ");
                    $stmtExiste->execute([
                        $idHorarioMantener,
                        $detalle['dia_semana'],
                        $detalle['hora_inicio'],
                        $detalle['hora_fin']
                    ]);
                    $existe = $stmtExiste->fetch(PDO::FETCH_ASSOC);
                    
                    if ($existe) {
                        // Ya existe un detalle en ese día/hora
                        $conflictos[] = [
                            'grado' => $plan['grado'],
                            'seccion' => $plan['seccion'],
                            'horario_eliminar' => $idHorarioEliminar,
                            'horario_mantener' => $idHorarioMantener,
                            'detalle' => $detalle
                        ];
                        echo "      ⚠️  Conflicto: {$detalle['dia_semana']} {$detalle['hora_inicio']} ya existe. Se mantiene el existente.\n";
                    } else {
                        // Mover el detalle al horario que se mantiene
                        $stmtMover = $pdo->prepare("
                            UPDATE horario_detalle 
                            SET id_horario = ? 
                            WHERE $pk = ?
                        ");
                        $stmtMover->execute([$idHorarioMantener, $detalle[$pk]]);
                        $totalMovidos++;
                        echo "      ✓ Movido: {$detalle['dia_semana']} {$detalle['hora_inicio']}\n";
                    }
                }
                
                // Eliminar el horario
                $stmtEliminar = $pdo->prepare("DELETE FROM horarios WHERE id_horario = ?");
                $stmtEliminar->execute([$idHorarioEliminar]);
                $totalEliminados++;
                
                echo "      ✓ Horario ID {$idHorarioEliminar} eliminado\n";
            }
            echo "\n";
        }
        
        $pdo->commit();
        
        echo str_repeat("=", 80) . "\n";
        echo "✅ Consolidación Completada\n";
        echo "   Detalles movidos: {$totalMovidos}\n";
        echo "   Horarios eliminados: {$totalEliminados}\n";
        if (!empty($conflictos)) {
            echo "   Conflictos detectados: " . count($conflictos) . "\n";
            echo "\n⚠️  Conflictos:\n";
            foreach ($conflictos as $conflicto) {
                echo "   - {$conflicto['grado']} - {$conflicto['seccion']}: ";
                echo "{$conflicto['detalle']['dia_semana']} {$conflicto['detalle']['hora_inicio']} ya existe\n";
            }
        }
        echo "\n";
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
    
} catch (Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}

echo "✅ Proceso completado exitosamente.\n";



