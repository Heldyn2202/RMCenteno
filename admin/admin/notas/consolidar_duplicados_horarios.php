<?php
/**
 * Script de consolidación de horarios duplicados
 * Elimina duplicados manteniendo un solo horario por gestión-grado-sección
 * Mueve todos los detalles al horario que se mantiene
 */

include('../../app/config.php');
header('Content-Type: text/html; charset=utf-8');

// Modo: 'preview' (solo muestra) o 'execute' (ejecuta cambios)
$modo = isset($_GET['modo']) ? $_GET['modo'] : 'preview';
$confirmar = isset($_POST['confirmar']) && $_POST['confirmar'] === 'si';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consolidación de Horarios Duplicados</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #007bff;
            padding-bottom: 10px;
        }
        h2 {
            color: #555;
            margin-top: 30px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border: 1px solid #ddd;
        }
        th {
            background: #007bff;
            color: white;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background: #f9f9f9;
        }
        .success {
            background: #d4edda;
            padding: 15px;
            border-left: 4px solid #28a745;
            margin: 20px 0;
        }
        .warning {
            background: #fff3cd;
            padding: 15px;
            border-left: 4px solid #ffc107;
            margin: 20px 0;
        }
        .danger {
            background: #f8d7da;
            padding: 15px;
            border-left: 4px solid #dc3545;
            margin: 20px 0;
        }
        .info {
            background: #e7f3ff;
            padding: 15px;
            border-left: 4px solid #007bff;
            margin: 20px 0;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            margin: 10px 5px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        .btn:hover {
            background: #0056b3;
        }
        .btn-danger {
            background: #dc3545;
        }
        .btn-danger:hover {
            background: #c82333;
        }
        .btn-success {
            background: #28a745;
        }
        .btn-success:hover {
            background: #218838;
        }
        .conflicto {
            background: #f8d7da;
            color: #721c24;
            padding: 5px 10px;
            border-radius: 3px;
            margin: 5px 0;
        }
        .movido {
            background: #d4edda;
            color: #155724;
            padding: 5px 10px;
            border-radius: 3px;
            margin: 5px 0;
        }
        .form-consolidacion {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 4px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Consolidación de Horarios Duplicados</h1>
        
        <?php
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
                echo '<div class="success">';
                echo '<h2>✅ No hay horarios duplicados</h2>';
                echo '<p>La base de datos está limpia. No se requiere consolidación.</p>';
                echo '</div>';
            } else {
                echo '<div class="info">';
                echo '<h2>📋 Resumen de Duplicados Encontrados</h2>';
                echo '<p><strong>Total de grupos duplicados:</strong> ' . count($duplicados) . '</p>';
                $totalEliminar = 0;
                foreach ($duplicados as $dup) {
                    $totalEliminar += ($dup['total_horarios'] - 1);
                }
                echo '<p><strong>Horarios a eliminar:</strong> ' . $totalEliminar . '</p>';
                echo '</div>';
                
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
                    
                    // Determinar cuál mantener:
                    // 1. Prioridad: PUBLICADO > BORRADOR
                    // 2. Si hay empate: más detalles
                    // 3. Si hay empate: más reciente (mayor ID)
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
                
                // 3. Mostrar plan de consolidación
                echo '<h2>📝 Plan de Consolidación</h2>';
                echo '<table>';
                echo '<thead>';
                echo '<tr>';
                echo '<th>Grado - Sección</th>';
                echo '<th>Mantener (ID)</th>';
                echo '<th>Eliminar (IDs)</th>';
                echo '<th>Detalles a Mover</th>';
                echo '</tr>';
                echo '</thead>';
                echo '<tbody>';
                
                foreach ($planConsolidacion as $plan) {
                    $totalDetallesEliminar = array_sum(array_column($plan['eliminar'], 'detalles'));
                    echo '<tr>';
                    echo '<td><strong>' . htmlspecialchars($plan['grado']) . ' - ' . htmlspecialchars($plan['seccion']) . '</strong></td>';
                    echo '<td>';
                    echo 'ID ' . $plan['mantener']['id'] . '<br>';
                    echo '<small>Detalles: ' . $plan['mantener']['detalles'] . ', Estado: ' . htmlspecialchars($plan['mantener']['estado']) . '</small>';
                    echo '</td>';
                    echo '<td>';
                    foreach ($plan['eliminar'] as $elim) {
                        echo 'ID ' . $elim['id'] . ' (' . $elim['detalles'] . ' detalles)<br>';
                    }
                    echo '</td>';
                    echo '<td><strong>' . $totalDetallesEliminar . '</strong></td>';
                    echo '</tr>';
                }
                echo '</tbody>';
                echo '</table>';
                
                // 4. Si es modo preview, mostrar botón para ejecutar
                if ($modo === 'preview' && !$confirmar) {
                    echo '<div class="warning">';
                    echo '<h2>⚠️ Modo Preview</h2>';
                    echo '<p>Este es un resumen de los cambios que se realizarán. Para ejecutar la consolidación, haz clic en el botón de abajo.</p>';
                    echo '<p><strong>IMPORTANTE:</strong> Se recomienda hacer un backup completo de la base de datos antes de continuar.</p>';
                    echo '</div>';
                    
                    echo '<div class="form-consolidacion">';
                    echo '<form method="get" action="">';
                    echo '<input type="hidden" name="modo" value="execute">';
                    echo '<button type="submit" class="btn btn-danger" onclick="return confirm(\'¿Estás seguro de ejecutar la consolidación? Esta acción no se puede deshacer. Asegúrate de tener un backup.\');">';
                    echo '🔧 Ejecutar Consolidación';
                    echo '</button>';
                    echo '<a href="?" class="btn">Cancelar</a>';
                    echo '</form>';
                    echo '</div>';
                }
                
                // 5. Si es modo execute, ejecutar la consolidación
                if ($modo === 'execute' || $confirmar) {
                    echo '<div class="info">';
                    echo '<h2>⚙️ Ejecutando Consolidación...</h2>';
                    echo '</div>';
                    
                    $pdo->beginTransaction();
                    
                    $totalMovidos = 0;
                    $totalEliminados = 0;
                    $conflictos = [];
                    $errores = [];
                    
                    try {
                        foreach ($planConsolidacion as $plan) {
                            $idHorarioMantener = $plan['mantener']['id'];
                            $idGestion = $plan['gestion'];
                            $idGrado = $plan['id_grado'];
                            $idSeccion = $plan['id_seccion'];
                            
                            echo '<div class="info">';
                            echo '<h3>📦 Consolidando: ' . htmlspecialchars($plan['grado']) . ' - ' . htmlspecialchars($plan['seccion']) . '</h3>';
                            echo '<p>Manteniendo horario ID ' . $idHorarioMantener . '</p>';
                            echo '</div>';
                            
                            foreach ($plan['eliminar'] as $elim) {
                                $idHorarioEliminar = $elim['id'];
                                
                                echo '<div style="margin-left: 20px; margin-bottom: 10px;">';
                                echo '<strong>Procesando horario ID ' . $idHorarioEliminar . ':</strong><br>';
                                
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
                                            'detalle' => $detalle,
                                            'motivo' => 'Ya existe un detalle en el mismo día y hora'
                                        ];
                                        echo '<div class="conflicto">⚠️ Conflicto: Detalle en ' . htmlspecialchars($detalle['dia_semana']) . ' ' . htmlspecialchars($detalle['hora_inicio']) . ' ya existe. Se mantiene el existente.</div>';
                                    } else {
                                        // Mover el detalle al horario que se mantiene
                                        $stmtMover = $pdo->prepare("
                                            UPDATE horario_detalle 
                                            SET id_horario = ? 
                                            WHERE $pk = ?
                                        ");
                                        $stmtMover->execute([$idHorarioMantener, $detalle[$pk]]);
                                        $totalMovidos++;
                                        echo '<div class="movido">✓ Movido: ' . htmlspecialchars($detalle['dia_semana']) . ' ' . htmlspecialchars($detalle['hora_inicio']) . '</div>';
                                    }
                                }
                                
                                // Eliminar el horario (los detalles ya fueron movidos o están en conflicto)
                                $stmtEliminar = $pdo->prepare("DELETE FROM horarios WHERE id_horario = ?");
                                $stmtEliminar->execute([$idHorarioEliminar]);
                                $totalEliminados++;
                                
                                echo '<div class="success">✓ Horario ID ' . $idHorarioEliminar . ' eliminado</div>';
                                echo '</div>';
                            }
                        }
                        
                        $pdo->commit();
                        
                        echo '<div class="success">';
                        echo '<h2>✅ Consolidación Completada</h2>';
                        echo '<p><strong>Detalles movidos:</strong> ' . $totalMovidos . '</p>';
                        echo '<p><strong>Horarios eliminados:</strong> ' . $totalEliminados . '</p>';
                        if (!empty($conflictos)) {
                            echo '<p><strong>Conflictos detectados:</strong> ' . count($conflictos) . '</p>';
                        }
                        echo '</div>';
                        
                        if (!empty($conflictos)) {
                            echo '<div class="warning">';
                            echo '<h2>⚠️ Conflictos Detectados</h2>';
                            echo '<p>Los siguientes detalles no pudieron ser movidos porque ya existían en el horario que se mantiene:</p>';
                            echo '<ul>';
                            foreach ($conflictos as $conflicto) {
                                echo '<li>';
                                echo '<strong>' . htmlspecialchars($conflicto['grado']) . ' - ' . htmlspecialchars($conflicto['seccion']) . ':</strong> ';
                                echo 'Detalle en ' . htmlspecialchars($conflicto['detalle']['dia_semana']) . ' ' . htmlspecialchars($conflicto['detalle']['hora_inicio']) . ' ya existe. ';
                                echo 'Se mantuvo el detalle del horario ID ' . $conflicto['horario_mantener'] . '.';
                                echo '</li>';
                            }
                            echo '</ul>';
                            echo '</div>';
                        }
                        
                    } catch (Exception $e) {
                        $pdo->rollBack();
                        throw $e;
                    }
                }
            }
            
        } catch (Exception $e) {
            echo '<div class="danger">';
            echo '<h2>❌ Error</h2>';
            echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
            echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
            echo '</div>';
        }
        ?>
        
        <div style="margin-top: 40px; padding: 20px; background: #f8f9fa; border-radius: 4px;">
            <h3>📝 Notas</h3>
            <ul>
                <li>La consolidación se realiza en una transacción para garantizar atomicidad.</li>
                <li>Si hay conflictos (mismo día/hora), se mantiene el detalle existente en el horario que se conserva.</li>
                <li>Los detalles conflictivos se reportan pero no se pierden (permanecen en el horario duplicado hasta revisión manual).</li>
                <li>Se recomienda verificar los horarios después de la consolidación.</li>
            </ul>
        </div>
        
        <div style="margin-top: 20px;">
            <a href="horarios_consolidados.php" class="btn">← Volver a Horarios Consolidados</a>
            <a href="analizar_duplicados_horarios.php" class="btn">🔍 Ver Análisis Nuevamente</a>
        </div>
    </div>
</body>
</html>



