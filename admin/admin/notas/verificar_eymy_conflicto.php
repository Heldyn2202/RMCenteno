<?php
/**
 * Script para verificar específicamente el conflicto de Eymy Perez
 * en Lunes 08:30-09:10
 */

include('../../app/config.php');
header('Content-Type: text/html; charset=utf-8');

$id_profesor = 6; // Eymy Perez
$dia_semana = 'Lunes';
$hora_inicio = '08:30:00';
$hora_fin = '09:10:00';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Verificación Conflicto Eymy Perez</title>
    <style>
        body { font-family: Arial; margin: 20px; }
        table { border-collapse: collapse; width: 100%; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #007bff; color: white; }
        .error { background: #f8d7da; }
        .success { background: #d4edda; }
        .warning { background: #fff3cd; }
    </style>
</head>
<body>
    <h1>🔍 Verificación Conflicto: Eymy Perez</h1>
    <p><strong>Buscando:</strong> Profesor ID <?=$id_profesor?>, <?=$dia_semana?>, <?=$hora_inicio?> - <?=$hora_fin?></p>
    
    <?php
    try {
        // Detectar PK
        $cols = $pdo->query("SHOW COLUMNS FROM horario_detalle")->fetchAll(PDO::FETCH_COLUMN);
        $pk = null;
        foreach (['id_detalle','id_horario_detalle','id'] as $c) { 
            if (in_array($c, $cols, true)) { 
                $pk = $c; 
                break; 
            } 
        }
        
        // Buscar TODOS los bloques de Eymy Perez en Lunes
        $sql = "SELECT hd.$pk, hd.id_horario, hd.dia_semana, hd.hora_inicio, hd.hora_fin, hd.id_profesor,
                       CONCAT(p.nombres, ' ', p.apellidos) AS nombre_profesor,
                       h.id_grado, h.id_seccion,
                       g.grado AS nombre_grado,
                       s.nombre_seccion,
                       m.nombre_materia
                FROM horario_detalle hd
                INNER JOIN horarios h ON h.id_horario = hd.id_horario
                INNER JOIN grados g ON g.id_grado = h.id_grado
                INNER JOIN secciones s ON s.id_seccion = h.id_seccion
                LEFT JOIN profesores p ON p.id_profesor = hd.id_profesor
                LEFT JOIN materias m ON m.id_materia = hd.id_materia
                WHERE hd.id_profesor = ?
                  AND hd.dia_semana = ?
                ORDER BY hd.hora_inicio";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_profesor, $dia_semana]);
        $bloques = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h2>📋 Todos los bloques de Eymy Perez en Lunes:</h2>";
        echo "<table>";
        echo "<tr><th>ID Bloque</th><th>ID Horario</th><th>Grado</th><th>Sección</th><th>Hora Inicio</th><th>Hora Fin</th><th>Materia</th><th>¿Solapa con 08:30-09:10?</th></tr>";
        
        foreach ($bloques as $bloque) {
            $solapa = false;
            $horaInicioBloque = $bloque['hora_inicio'];
            $horaFinBloque = $bloque['hora_fin'];
            
            // Verificar solapamiento
            if ($horaFinBloque && $horaFinBloque != '00:00:00' && $horaInicioBloque && $horaInicioBloque != '00:00:00') {
                if (($horaInicioBloque <= $hora_inicio && $horaFinBloque > $hora_inicio) ||
                    ($horaInicioBloque < $hora_fin && $horaFinBloque >= $hora_fin) ||
                    ($horaInicioBloque >= $hora_inicio && $horaFinBloque <= $hora_fin)) {
                    $solapa = true;
                }
            }
            
            $clase = $solapa ? 'error' : '';
            echo "<tr class='$clase'>";
            echo "<td>" . htmlspecialchars($bloque[$pk]) . "</td>";
            echo "<td>" . htmlspecialchars($bloque['id_horario']) . "</td>";
            echo "<td>" . htmlspecialchars($bloque['nombre_grado']) . "</td>";
            echo "<td>" . htmlspecialchars($bloque['nombre_seccion']) . "</td>";
            echo "<td>" . htmlspecialchars($horaInicioBloque) . "</td>";
            echo "<td>" . htmlspecialchars($horaFinBloque ?: 'NULL/00:00:00') . "</td>";
            echo "<td>" . htmlspecialchars($bloque['nombre_materia'] ?? 'N/A') . "</td>";
            echo "<td>" . ($solapa ? '<strong style="color:red;">SÍ</strong>' : 'No') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Ahora buscar específicamente en el rango 08:30-09:10
        echo "<h2>🔍 Bloques en el rango 08:30-09:10 (con solapamiento):</h2>";
        $sqlSolapamiento = "SELECT hd.$pk, hd.id_horario, hd.dia_semana, hd.hora_inicio, hd.hora_fin, hd.id_profesor,
                                  CONCAT(p.nombres, ' ', p.apellidos) AS nombre_profesor,
                                  h.id_grado, h.id_seccion,
                                  g.grado AS nombre_grado,
                                  s.nombre_seccion,
                                  m.nombre_materia
                           FROM horario_detalle hd
                           INNER JOIN horarios h ON h.id_horario = hd.id_horario
                           INNER JOIN grados g ON g.id_grado = h.id_grado
                           INNER JOIN secciones s ON s.id_seccion = h.id_seccion
                           LEFT JOIN profesores p ON p.id_profesor = hd.id_profesor
                           LEFT JOIN materias m ON m.id_materia = hd.id_materia
                           WHERE hd.id_profesor = ?
                             AND hd.dia_semana = ?
                             AND hd.hora_fin IS NOT NULL
                             AND hd.hora_fin != '00:00:00'
                             AND hd.hora_inicio IS NOT NULL
                             AND hd.hora_inicio != '00:00:00'
                             AND (
                               (hd.hora_inicio <= ? AND hd.hora_fin > ?) OR
                               (hd.hora_inicio < ? AND hd.hora_fin >= ?) OR
                               (hd.hora_inicio >= ? AND hd.hora_fin <= ?)
                             )";
        
        $stmtSolapamiento = $pdo->prepare($sqlSolapamiento);
        $stmtSolapamiento->execute([
            $id_profesor, $dia_semana,
            $hora_inicio, $hora_inicio,
            $hora_fin, $hora_fin,
            $hora_inicio, $hora_fin
        ]);
        $solapamientos = $stmtSolapamiento->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($solapamientos)) {
            echo "<div class='success'><p>✅ No se encontraron bloques que solapen con 08:30-09:10 (excluyendo datos inválidos)</p></div>";
        } else {
            echo "<div class='error'><p>❌ Se encontraron " . count($solapamientos) . " bloque(s) que solapan:</p></div>";
            echo "<table>";
            echo "<tr><th>ID Bloque</th><th>ID Horario</th><th>Grado</th><th>Sección</th><th>Hora Inicio</th><th>Hora Fin</th><th>Materia</th></tr>";
            foreach ($solapamientos as $sol) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($sol[$pk]) . "</td>";
                echo "<td>" . htmlspecialchars($sol['id_horario']) . "</td>";
                echo "<td>" . htmlspecialchars($sol['nombre_grado']) . "</td>";
                echo "<td>" . htmlspecialchars($sol['nombre_seccion']) . "</td>";
                echo "<td>" . htmlspecialchars($sol['hora_inicio']) . "</td>";
                echo "<td>" . htmlspecialchars($sol['hora_fin']) . "</td>";
                echo "<td>" . htmlspecialchars($sol['nombre_materia'] ?? 'N/A') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        
    } catch (Exception $e) {
        echo "<div class='error'>";
        echo "<h3>❌ Error</h3>";
        echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
        echo "</div>";
    }
    ?>
</body>
</html>



