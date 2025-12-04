<?php
/**
 * Script de diagnóstico para verificar conflictos de "Marcos Aurelio"
 * entre "Tercer Año B" y "Cuarto Año B"
 */

include('../../app/config.php');

header('Content-Type: text/html; charset=utf-8');

echo "<h2>Diagnóstico de conflicto: Marcos Aurelio</h2>";

try {
    // 1. Buscar a "Marcos Aurelio" en la base de datos
    $stmtProf = $pdo->prepare("SELECT id_profesor, nombres, apellidos, CONCAT(nombres, ' ', apellidos) AS nombre_completo FROM profesores WHERE CONCAT(nombres, ' ', apellidos) LIKE ? OR nombres LIKE ? OR apellidos LIKE ?");
    $stmtProf->execute(['%Marcos%', '%Marcos%', '%Aurelio%']);
    $profesores = $stmtProf->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>1. Profesores encontrados:</h3>";
    echo "<pre>";
    print_r($profesores);
    echo "</pre>";
    
    if (empty($profesores)) {
        echo "<p style='color: red;'>No se encontró a Marcos Aurelio en la base de datos.</p>";
        exit;
    }
    
    $id_profesor = $profesores[0]['id_profesor'];
    echo "<p><strong>ID Profesor seleccionado:</strong> {$id_profesor} ({$profesores[0]['nombre_completo']})</p>";
    
    // 2. Buscar todos los bloques donde está asignado "Marcos Aurelio"
    $stmtBloques = $pdo->prepare("SELECT hd.*, h.id_horario, h.id_grado, h.id_seccion, g.grado AS nombre_grado, s.nombre_seccion, h.id_gestion
                                  FROM horario_detalle hd
                                  INNER JOIN horarios h ON h.id_horario = hd.id_horario
                                  INNER JOIN grados g ON g.id_grado = h.id_grado
                                  INNER JOIN secciones s ON s.id_seccion = h.id_seccion
                                  WHERE hd.id_profesor = ?
                                  ORDER BY hd.dia_semana, hd.hora_inicio");
    $stmtBloques->execute([$id_profesor]);
    $bloques = $stmtBloques->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>2. Bloques donde está asignado:</h3>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID Detalle</th><th>Horario ID</th><th>Grado</th><th>Sección</th><th>Día</th><th>Hora Inicio</th><th>Hora Fin</th><th>Materia ID</th></tr>";
    foreach ($bloques as $bloque) {
        echo "<tr>";
        echo "<td>{$bloque['id_detalle']}</td>";
        echo "<td>{$bloque['id_horario']}</td>";
        echo "<td>{$bloque['nombre_grado']} (ID: {$bloque['id_grado']})</td>";
        echo "<td>{$bloque['nombre_seccion']} (ID: {$bloque['id_seccion']})</td>";
        echo "<td>{$bloque['dia_semana']}</td>";
        echo "<td>{$bloque['hora_inicio']}</td>";
        echo "<td>{$bloque['hora_fin']}</td>";
        echo "<td>{$bloque['id_materia']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // 3. Buscar específicamente en "Tercer Año B" y "Cuarto Año B"
    echo "<h3>3. Bloques en 'Tercer Año B' y 'Cuarto Año B':</h3>";
    
    $stmtEspecifico = $pdo->prepare("SELECT hd.*, h.id_horario, h.id_grado, h.id_seccion, g.grado AS nombre_grado, s.nombre_seccion, h.id_gestion
                                      FROM horario_detalle hd
                                      INNER JOIN horarios h ON h.id_horario = hd.id_horario
                                      INNER JOIN grados g ON g.id_grado = h.id_grado
                                      INNER JOIN secciones s ON s.id_seccion = h.id_seccion
                                      WHERE hd.id_profesor = ?
                                        AND (
                                          (g.grado LIKE '%TERCER%' AND s.nombre_seccion LIKE '%B%') OR
                                          (g.grado LIKE '%CUARTO%' AND s.nombre_seccion LIKE '%B%')
                                        )
                                      ORDER BY hd.dia_semana, hd.hora_inicio");
    $stmtEspecifico->execute([$id_profesor]);
    $bloquesEspecificos = $stmtEspecifico->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID Detalle</th><th>Horario ID</th><th>Grado</th><th>Sección</th><th>Día</th><th>Hora Inicio</th><th>Hora Fin</th><th>Gestión</th></tr>";
    foreach ($bloquesEspecificos as $bloque) {
        echo "<tr>";
        echo "<td>{$bloque['id_detalle']}</td>";
        echo "<td>{$bloque['id_horario']}</td>";
        echo "<td>{$bloque['nombre_grado']} (ID: {$bloque['id_grado']})</td>";
        echo "<td>{$bloque['nombre_seccion']} (ID: {$bloque['id_seccion']})</td>";
        echo "<td>{$bloque['dia_semana']}</td>";
        echo "<td>{$bloque['hora_inicio']}</td>";
        echo "<td>{$bloque['hora_fin']}</td>";
        echo "<td>{$bloque['id_gestion']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // 4. Simular la validación de conflicto para "Lunes 07:50-08:30" en "Cuarto Año B"
    echo "<h3>4. Simulación de validación de conflicto:</h3>";
    echo "<p>Simulando asignación en <strong>Cuarto Año B, Lunes 07:50-08:30</strong></p>";
    
    // Obtener el horario de "Cuarto Año B"
    $stmtHorarioCuarto = $pdo->prepare("SELECT h.id_horario, h.id_gestion, h.id_grado, h.id_seccion, g.grado AS nombre_grado, s.nombre_seccion
                                        FROM horarios h
                                        INNER JOIN grados g ON g.id_grado = h.id_grado
                                        INNER JOIN secciones s ON s.id_seccion = h.id_seccion
                                        WHERE g.grado LIKE '%CUARTO%' AND s.nombre_seccion LIKE '%B%'
                                        LIMIT 1");
    $stmtHorarioCuarto->execute();
    $horarioCuarto = $stmtHorarioCuarto->fetch(PDO::FETCH_ASSOC);
    
    if ($horarioCuarto) {
        echo "<p><strong>Horario Cuarto Año B encontrado:</strong></p>";
        echo "<pre>";
        print_r($horarioCuarto);
        echo "</pre>";
        
        // Simular la query de validación
        $dia_semana = 'Lunes';
        $hora_inicio = '07:50:00';
        $hora_fin = '08:30:00';
        $id_horario = $horarioCuarto['id_horario'];
        
        // Detectar PK
        $cols = $pdo->query("SHOW COLUMNS FROM horario_detalle")->fetchAll(PDO::FETCH_COLUMN);
        $pk = null;
        foreach (['id_detalle','id_horario_detalle','id'] as $c) { 
            if (in_array($c, $cols, true)) { 
                $pk = $c; 
                break; 
            } 
        }
        
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
        
        // Excluir horarios duplicados del mismo grado-sección
        $sql .= " AND NOT (h.id_gestion = ? AND h.id_grado = ? AND h.id_seccion = ? AND h.id_horario != ?)";
        $params[] = $horarioCuarto['id_gestion'];
        $params[] = $horarioCuarto['id_grado'];
        $params[] = $horarioCuarto['id_seccion'];
        $params[] = $id_horario;
        
        // Verificar solapamiento
        $sql .= " AND hd.hora_fin IS NOT NULL 
                  AND hd.hora_fin != '00:00:00'
                  AND hd.hora_inicio IS NOT NULL
                  AND hd.hora_inicio != '00:00:00'
                  AND (
                    (hd.hora_inicio <= ? AND hd.hora_fin > ?) OR
                    (hd.hora_inicio < ? AND hd.hora_fin >= ?) OR
                    (hd.hora_inicio >= ? AND hd.hora_fin <= ?)
                  )";
        
        $params[] = $hora_inicio; $params[] = $hora_inicio;
        $params[] = $hora_fin; $params[] = $hora_fin;
        $params[] = $hora_inicio; $params[] = $hora_fin;
        
        echo "<p><strong>SQL Query:</strong></p>";
        echo "<pre>" . htmlspecialchars($sql) . "</pre>";
        echo "<p><strong>Parámetros:</strong></p>";
        echo "<pre>";
        print_r($params);
        echo "</pre>";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $conflicto = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($conflicto) {
            echo "<p style='color: red;'><strong>CONFLICTO DETECTADO:</strong></p>";
            echo "<pre>";
            print_r($conflicto);
            echo "</pre>";
        } else {
            echo "<p style='color: green;'><strong>NO SE DETECTÓ CONFLICTO</strong></p>";
            echo "<p>Esto significa que la validación NO está funcionando correctamente.</p>";
        }
    } else {
        echo "<p style='color: red;'>No se encontró el horario de 'Cuarto Año B'</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>



