<?php
/**
 * Script de diagnóstico para verificar qué profesor está realmente asignado
 * en un bloque específico que está causando conflicto
 */

include('../../app/config.php');
header('Content-Type: text/html; charset=utf-8');

// Parámetros del conflicto reportado
$id_detalle = isset($_GET['id_detalle']) ? (int)$_GET['id_detalle'] : 13; // Del conflicto de Eymy
$id_horario = isset($_GET['id_horario']) ? (int)$_GET['id_horario'] : 2; // TERCER AÑO - Sección B

?>
<!DOCTYPE html>
<html>
<head>
    <title>Verificación de Bloque en Conflicto</title>
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
    <h1>🔍 Verificación de Bloque en Conflicto</h1>
    
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
        
        echo "<h2>Bloque ID: $id_detalle (PK: $pk)</h2>";
        echo "<h2>Horario ID: $id_horario</h2>";
        
        // Obtener información completa del bloque
        $sql = "SELECT hd.*, 
                       h.id_grado, h.id_seccion, h.id_gestion,
                       g.grado AS nombre_grado,
                       s.nombre_seccion,
                       CONCAT(p.nombres, ' ', p.apellidos) AS nombre_profesor,
                       m.nombre_materia
                FROM horario_detalle hd
                INNER JOIN horarios h ON h.id_horario = hd.id_horario
                INNER JOIN grados g ON g.id_grado = h.id_grado
                INNER JOIN secciones s ON s.id_seccion = h.id_seccion
                LEFT JOIN profesores p ON p.id_profesor = hd.id_profesor
                LEFT JOIN materias m ON m.id_materia = hd.id_materia
                WHERE hd.$pk = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_detalle]);
        $bloque = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($bloque) {
            echo "<div class='success'>";
            echo "<h3>✅ Bloque encontrado</h3>";
            echo "<table>";
            echo "<tr><th>Campo</th><th>Valor</th></tr>";
            foreach ($bloque as $campo => $valor) {
                $clase = '';
                if ($campo == 'id_profesor' && ($valor == null || $valor == '')) {
                    $clase = 'warning';
                }
                echo "<tr class='$clase'>";
                echo "<td><strong>" . htmlspecialchars($campo) . "</strong></td>";
                echo "<td>" . htmlspecialchars($valor ?? 'NULL') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            echo "</div>";
            
            // Verificar si hay otros bloques en el mismo horario con diferentes profesores
            echo "<h3>🔍 Otros bloques en el mismo horario (id_horario: $id_horario)</h3>";
            $sqlOtros = "SELECT hd.$pk, hd.dia_semana, hd.hora_inicio, hd.hora_fin, 
                                hd.id_profesor,
                                CONCAT(p.nombres, ' ', p.apellidos) AS nombre_profesor,
                                m.nombre_materia
                         FROM horario_detalle hd
                         LEFT JOIN profesores p ON p.id_profesor = hd.id_profesor
                         LEFT JOIN materias m ON m.id_materia = hd.id_materia
                         WHERE hd.id_horario = ?
                         ORDER BY hd.dia_semana, hd.hora_inicio";
            
            $stmtOtros = $pdo->prepare($sqlOtros);
            $stmtOtros->execute([$id_horario]);
            $otrosBloques = $stmtOtros->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<table>";
            echo "<tr><th>ID</th><th>Día</th><th>Hora Inicio</th><th>Hora Fin</th><th>Profesor ID</th><th>Profesor</th><th>Materia</th></tr>";
            foreach ($otrosBloques as $otro) {
                $esElBloque = ($otro[$pk] == $id_detalle);
                $clase = $esElBloque ? 'success' : '';
                echo "<tr class='$clase'>";
                echo "<td>" . ($esElBloque ? '<strong>' : '') . htmlspecialchars($otro[$pk]) . ($esElBloque ? '</strong>' : '') . "</td>";
                echo "<td>" . htmlspecialchars($otro['dia_semana']) . "</td>";
                echo "<td>" . htmlspecialchars($otro['hora_inicio']) . "</td>";
                echo "<td>" . htmlspecialchars($otro['hora_fin']) . "</td>";
                echo "<td>" . htmlspecialchars($otro['id_profesor'] ?? 'NULL') . "</td>";
                echo "<td>" . htmlspecialchars($otro['nombre_profesor'] ?? 'Sin profesor') . "</td>";
                echo "<td>" . htmlspecialchars($otro['nombre_materia'] ?? 'N/A') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            
            // Verificar si hay bloques duplicados en el mismo día/hora
            echo "<h3>🔍 Bloques en el mismo día y hora (Lunes 08:30-09:10)</h3>";
            $sqlMismoTiempo = "SELECT hd.$pk, hd.id_horario, hd.id_profesor,
                                      CONCAT(p.nombres, ' ', p.apellidos) AS nombre_profesor,
                                      h.id_grado, h.id_seccion,
                                      g.grado AS nombre_grado,
                                      s.nombre_seccion
                               FROM horario_detalle hd
                               INNER JOIN horarios h ON h.id_horario = hd.id_horario
                               INNER JOIN grados g ON g.id_grado = h.id_grado
                               INNER JOIN secciones s ON s.id_seccion = h.id_seccion
                               LEFT JOIN profesores p ON p.id_profesor = hd.id_profesor
                               WHERE hd.dia_semana = 'Lunes'
                                 AND hd.hora_inicio = '08:30:00'
                                 AND hd.hora_fin = '09:10:00'
                               ORDER BY h.id_horario";
            
            $stmtMismoTiempo = $pdo->prepare($sqlMismoTiempo);
            $stmtMismoTiempo->execute();
            $mismoTiempo = $stmtMismoTiempo->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<table>";
            echo "<tr><th>ID Bloque</th><th>ID Horario</th><th>Grado</th><th>Sección</th><th>Profesor ID</th><th>Profesor</th></tr>";
            foreach ($mismoTiempo as $mt) {
                $esElBloque = ($mt[$pk] == $id_detalle);
                $clase = $esElBloque ? 'success' : '';
                echo "<tr class='$clase'>";
                echo "<td>" . ($esElBloque ? '<strong>' : '') . htmlspecialchars($mt[$pk]) . ($esElBloque ? '</strong>' : '') . "</td>";
                echo "<td>" . htmlspecialchars($mt['id_horario']) . "</td>";
                echo "<td>" . htmlspecialchars($mt['nombre_grado']) . "</td>";
                echo "<td>" . htmlspecialchars($mt['nombre_seccion']) . "</td>";
                echo "<td>" . htmlspecialchars($mt['id_profesor'] ?? 'NULL') . "</td>";
                echo "<td>" . htmlspecialchars($mt['nombre_profesor'] ?? 'Sin profesor') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            
        } else {
            echo "<div class='error'>";
            echo "<h3>❌ Bloque no encontrado</h3>";
            echo "<p>No se encontró un bloque con ID: $id_detalle</p>";
            echo "</div>";
        }
        
    } catch (Exception $e) {
        echo "<div class='error'>";
        echo "<h3>❌ Error</h3>";
        echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
        echo "</div>";
    }
    ?>
    
    <div style="margin-top: 30px; padding: 15px; background: #e7f3ff; border-left: 4px solid #007bff;">
        <h3>📝 Información</h3>
        <p>Este script muestra la información real del bloque que está causando el conflicto.</p>
        <p>Si el bloque muestra un profesor diferente al que se ve en la interfaz, puede haber un problema de sincronización de datos.</p>
        <p><strong>Parámetros:</strong> id_detalle=<?=$id_detalle?>, id_horario=<?=$id_horario?></p>
        <p>Puedes cambiar los parámetros en la URL: <code>?id_detalle=13&id_horario=2</code></p>
    </div>
</body>
</html>



