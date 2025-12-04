<?php
/**
 * Script de análisis para detectar horarios duplicados
 * Identifica múltiples horarios para la misma combinación de gestión-grado-sección
 * NO MODIFICA la base de datos, solo analiza
 */

include('../../app/config.php');
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Análisis de Horarios Duplicados</title>
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
        .duplicado {
            background: #fff3cd !important;
        }
        .duplicado-multiple {
            background: #f8d7da !important;
        }
        .resumen {
            background: #e7f3ff;
            padding: 15px;
            border-left: 4px solid #007bff;
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
        .detalle-horario {
            margin-left: 30px;
            font-size: 0.9em;
            color: #666;
        }
        .accion {
            color: #28a745;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Análisis de Horarios Duplicados</h1>
        
        <?php
        try {
            // 1. Identificar horarios duplicados (misma gestión, grado, sección)
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
                ORDER BY total_horarios DESC, g.grado, s.nombre_seccion
            ";
            
            $stmtDuplicados = $pdo->query($sqlDuplicados);
            $duplicados = $stmtDuplicados->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($duplicados)) {
                echo '<div class="resumen">';
                echo '<h2>✅ No se encontraron horarios duplicados</h2>';
                echo '<p>La base de datos está limpia. No hay múltiples horarios para la misma combinación de gestión-grado-sección.</p>';
                echo '</div>';
            } else {
                // Resumen
                $totalDuplicados = count($duplicados);
                $totalHorariosDuplicados = array_sum(array_column($duplicados, 'total_horarios'));
                $totalHorariosEliminar = $totalHorariosDuplicados - $totalDuplicados; // Los que sobran
                
                echo '<div class="danger">';
                echo '<h2>⚠️ Se encontraron ' . $totalDuplicados . ' grupos de horarios duplicados</h2>';
                echo '<p><strong>Total de horarios duplicados:</strong> ' . $totalHorariosDuplicados . '</p>';
                echo '<p><strong>Horarios a eliminar (después de consolidar):</strong> ' . $totalHorariosEliminar . '</p>';
                echo '</div>';
                
                // Detalle de cada grupo de duplicados
                echo '<h2>Detalle de Duplicados</h2>';
                echo '<table>';
                echo '<thead>';
                echo '<tr>';
                echo '<th>Gestión</th>';
                echo '<th>Grado</th>';
                echo '<th>Sección</th>';
                echo '<th>Total Horarios</th>';
                echo '<th>IDs de Horarios</th>';
                echo '<th>Acción Propuesta</th>';
                echo '</tr>';
                echo '</thead>';
                echo '<tbody>';
                
                foreach ($duplicados as $dup) {
                    $idsArray = explode(', ', $dup['ids_horarios']);
                    $clase = $dup['total_horarios'] > 2 ? 'duplicado-multiple' : 'duplicado';
                    
                    echo '<tr class="' . $clase . '">';
                    echo '<td>' . htmlspecialchars($dup['id_gestion']) . '</td>';
                    echo '<td><strong>' . htmlspecialchars($dup['nombre_grado']) . '</strong></td>';
                    echo '<td><strong>' . htmlspecialchars($dup['nombre_seccion']) . '</strong></td>';
                    echo '<td><strong>' . $dup['total_horarios'] . '</strong></td>';
                    echo '<td>' . htmlspecialchars($dup['ids_horarios']) . '</td>';
                    echo '<td class="accion">Mantener 1, eliminar ' . ($dup['total_horarios'] - 1) . '</td>';
                    echo '</tr>';
                    
                    // Detalle de cada horario en el grupo
                    echo '<tr class="' . $clase . '">';
                    echo '<td colspan="6" class="detalle-horario">';
                    echo '<strong>Detalle de cada horario:</strong><br>';
                    
                    foreach ($idsArray as $idHorario) {
                        $idHorario = trim($idHorario);
                        
                        // Obtener información del horario
                        $stmtHorario = $pdo->prepare("
                            SELECT 
                                h.*,
                                COUNT(hd.id_detalle) as total_detalles,
                                MAX(hd.dia_semana) as ultimo_dia,
                                MAX(hd.hora_inicio) as ultima_hora
                            FROM horarios h
                            LEFT JOIN horario_detalle hd ON hd.id_horario = h.id_horario
                            WHERE h.id_horario = ?
                            GROUP BY h.id_horario
                        ");
                        $stmtHorario->execute([$idHorario]);
                        $horarioInfo = $stmtHorario->fetch(PDO::FETCH_ASSOC);
                        
                        if ($horarioInfo) {
                            // Detectar PK de horario_detalle
                            $cols = $pdo->query("SHOW COLUMNS FROM horario_detalle")->fetchAll(PDO::FETCH_COLUMN);
                            $pk = null;
                            foreach (['id_detalle','id_horario_detalle','id'] as $c) { 
                                if (in_array($c, $cols, true)) { 
                                    $pk = $c; 
                                    break; 
                                } 
                            }
                            
                            // Contar detalles correctamente
                            $stmtCount = $pdo->prepare("SELECT COUNT(*) as total FROM horario_detalle WHERE id_horario = ?");
                            $stmtCount->execute([$idHorario]);
                            $countResult = $stmtCount->fetch(PDO::FETCH_ASSOC);
                            $totalDetalles = $countResult['total'] ?? 0;
                            
                            $estado = isset($horarioInfo['estado']) ? $horarioInfo['estado'] : 'N/A';
                            $aula = $horarioInfo['aula'] ?? 'N/A';
                            
                            echo '• <strong>ID Horario ' . $idHorario . ':</strong> ';
                            echo 'Detalles: ' . $totalDetalles . ', ';
                            echo 'Estado: ' . htmlspecialchars($estado) . ', ';
                            echo 'Aula: ' . htmlspecialchars($aula) . ', ';
                            echo 'Fecha: ' . htmlspecialchars($horarioInfo['fecha_inicio'] ?? 'N/A') . ' - ' . htmlspecialchars($horarioInfo['fecha_fin'] ?? 'N/A');
                            
                            // Sugerir cuál mantener (el que tenga más detalles o el más reciente)
                            if ($idHorario == $idsArray[0]) {
                                echo ' <span style="color: green;">[Sugerido: MANTENER - Más reciente]</span>';
                            }
                            echo '<br>';
                        }
                    }
                    echo '</td>';
                    echo '</tr>';
                }
                
                echo '</tbody>';
                echo '</table>';
                
                // Análisis de impacto
                echo '<h2>📊 Análisis de Impacto</h2>';
                echo '<div class="warning">';
                echo '<h3>Detalles que serían afectados:</h3>';
                
                $sqlImpacto = "
                    SELECT 
                        hd.id_horario,
                        COUNT(*) as total_detalles,
                        GROUP_CONCAT(DISTINCT hd.id_profesor) as profesores,
                        GROUP_CONCAT(DISTINCT hd.id_materia) as materias
                    FROM horario_detalle hd
                    INNER JOIN horarios h ON h.id_horario = hd.id_horario
                    WHERE h.id_horario IN (
                        SELECT GROUP_CONCAT(h2.id_horario) 
                        FROM (
                            SELECT h3.id_horario
                            FROM horarios h3
                            INNER JOIN (
                                SELECT id_gestion, id_grado, id_seccion
                                FROM horarios
                                GROUP BY id_gestion, id_grado, id_seccion
                                HAVING COUNT(*) > 1
                            ) dup ON dup.id_gestion = h3.id_gestion 
                                AND dup.id_grado = h3.id_grado 
                                AND dup.id_seccion = h3.id_seccion
                            ORDER BY h3.id_horario DESC
                            LIMIT 1000
                        ) h2
                    )
                    GROUP BY hd.id_horario
                    ORDER BY hd.id_horario
                ";
                
                // Versión más simple
                $idsTodosDuplicados = [];
                foreach ($duplicados as $dup) {
                    $idsArray = explode(', ', $dup['ids_horarios']);
                    foreach ($idsArray as $id) {
                        $idsTodosDuplicados[] = trim($id);
                    }
                }
                
                if (!empty($idsTodosDuplicados)) {
                    $placeholders = implode(',', array_fill(0, count($idsTodosDuplicados), '?'));
                    $sqlImpactoSimple = "
                        SELECT 
                            hd.id_horario,
                            COUNT(*) as total_detalles,
                            GROUP_CONCAT(DISTINCT CONCAT(p.nombres, ' ', p.apellidos) SEPARATOR ', ') as nombres_profesores,
                            GROUP_CONCAT(DISTINCT m.nombre_materia SEPARATOR ', ') as nombres_materias
                        FROM horario_detalle hd
                        LEFT JOIN profesores p ON p.id_profesor = hd.id_profesor
                        LEFT JOIN materias m ON m.id_materia = hd.id_materia
                        WHERE hd.id_horario IN ($placeholders)
                        GROUP BY hd.id_horario
                        ORDER BY hd.id_horario
                    ";
                    
                    $stmtImpacto = $pdo->prepare($sqlImpactoSimple);
                    $stmtImpacto->execute($idsTodosDuplicados);
                    $impactos = $stmtImpacto->fetchAll(PDO::FETCH_ASSOC);
                    
                    if (!empty($impactos)) {
                        echo '<ul>';
                        foreach ($impactos as $impacto) {
                            echo '<li><strong>Horario ID ' . $impacto['id_horario'] . ':</strong> ';
                            echo $impacto['total_detalles'] . ' detalle(s)';
                            if ($impacto['nombres_profesores']) {
                                echo ' - Profesores: ' . htmlspecialchars($impacto['nombres_profesores']);
                            }
                            if ($impacto['nombres_materias']) {
                                echo ' - Materias: ' . htmlspecialchars($impacto['nombres_materias']);
                            }
                            echo '</li>';
                        }
                        echo '</ul>';
                    } else {
                        echo '<p>Los horarios duplicados no tienen detalles asociados.</p>';
                    }
                }
                echo '</div>';
                
                // Propuesta de solución
                echo '<h2>💡 Propuesta de Solución</h2>';
                echo '<div class="resumen">';
                echo '<h3>Plan de Consolidación:</h3>';
                echo '<ol>';
                echo '<li><strong>Para cada grupo de duplicados:</strong>';
                echo '<ul>';
                echo '<li>Identificar el horario a MANTENER (preferencia: el más reciente con más detalles, o simplemente el más reciente por ID)</li>';
                echo '<li>Mover todos los detalles de los horarios duplicados al horario que se mantiene</li>';
                echo '<li>Eliminar los horarios duplicados (y sus detalles ya movidos)</li>';
                echo '</ul>';
                echo '</li>';
                echo '<li><strong>Validaciones:</strong>';
                echo '<ul>';
                echo '<li>Verificar que no haya conflictos al consolidar (mismos días/horas)</li>';
                echo '<li>Preservar el estado más relevante (PUBLICADO > BORRADOR)</li>';
                echo '<li>Preservar la información más completa (aula, fechas)</li>';
                echo '</ul>';
                echo '</li>';
                echo '<li><strong>Resultado esperado:</strong> Un solo horario por cada combinación gestión-grado-sección</li>';
                echo '</ol>';
                echo '</div>';
            }
            
            // 2. También verificar si hay secciones duplicadas (mismo nombre, turno, grado)
            echo '<h2>🔍 Análisis Adicional: Secciones Duplicadas</h2>';
            $sqlSeccionesDup = "
                SELECT 
                    s.id_grado,
                    g.grado AS nombre_grado,
                    s.nombre_seccion,
                    s.turno,
                    COUNT(*) as total_secciones,
                    GROUP_CONCAT(s.id_seccion ORDER BY s.id_seccion SEPARATOR ', ') as ids_secciones
                FROM secciones s
                INNER JOIN grados g ON g.id_grado = s.id_grado
                WHERE s.estado = 1
                GROUP BY s.id_grado, s.nombre_seccion, s.turno
                HAVING COUNT(*) > 1
                ORDER BY total_secciones DESC
            ";
            
            $stmtSeccionesDup = $pdo->query($sqlSeccionesDup);
            $seccionesDup = $stmtSeccionesDup->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($seccionesDup)) {
                echo '<div class="resumen">';
                echo '<p>✅ No se encontraron secciones duplicadas.</p>';
                echo '</div>';
            } else {
                echo '<div class="warning">';
                echo '<p><strong>⚠️ Se encontraron ' . count($seccionesDup) . ' grupos de secciones duplicadas</strong></p>';
                echo '<p>Nota: Las secciones duplicadas pueden causar confusión pero no necesariamente conflictos en los horarios.</p>';
                echo '</div>';
                
                echo '<table>';
                echo '<thead>';
                echo '<tr>';
                echo '<th>Grado</th>';
                echo '<th>Sección</th>';
                echo '<th>Turno</th>';
                echo '<th>Total Secciones</th>';
                echo '<th>IDs</th>';
                echo '</tr>';
                echo '</thead>';
                echo '<tbody>';
                foreach ($seccionesDup as $secDup) {
                    echo '<tr class="duplicado">';
                    echo '<td>' . htmlspecialchars($secDup['nombre_grado']) . '</td>';
                    echo '<td>' . htmlspecialchars($secDup['nombre_seccion']) . '</td>';
                    echo '<td>' . htmlspecialchars($secDup['turno']) . '</td>';
                    echo '<td>' . $secDup['total_secciones'] . '</td>';
                    echo '<td>' . htmlspecialchars($secDup['ids_secciones']) . '</td>';
                    echo '</tr>';
                }
                echo '</tbody>';
                echo '</table>';
            }
            
        } catch (Exception $e) {
            echo '<div class="danger">';
            echo '<h2>❌ Error</h2>';
            echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
            echo '</div>';
        }
        ?>
        
        <div style="margin-top: 40px; padding: 20px; background: #f8f9fa; border-radius: 4px;">
            <h3>📝 Notas</h3>
            <ul>
                <li>Este script <strong>NO modifica</strong> la base de datos, solo analiza.</li>
                <li>Para aplicar la solución, se necesitará crear un script de consolidación.</li>
                <li>Se recomienda hacer un <strong>backup completo</strong> antes de ejecutar cualquier cambio.</li>
                <li>La consolidación debe realizarse con precaución para no perder datos.</li>
            </ul>
        </div>
    </div>
</body>
</html>



