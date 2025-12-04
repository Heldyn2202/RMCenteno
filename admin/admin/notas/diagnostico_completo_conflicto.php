<?php
/**
 * Script de diagnóstico COMPLETO para verificar conflictos de profesores
 * Analiza meticulosamente la lógica de validación
 */

include('../../app/config.php');

header('Content-Type: text/html; charset=utf-8');

echo "<h1>Diagnóstico Completo: Validación de Conflictos</h1>";
echo "<style>
    table { border-collapse: collapse; width: 100%; margin: 20px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #4CAF50; color: white; }
    .error { color: red; font-weight: bold; }
    .success { color: green; font-weight: bold; }
    .warning { color: orange; font-weight: bold; }
    pre { background: #f4f4f4; padding: 10px; border-left: 3px solid #4CAF50; }
</style>";

try {
    // ============================================================
    // PASO 1: Identificar al profesor "Marcos Aurelio"
    // ============================================================
    echo "<h2>PASO 1: Identificar Profesor</h2>";
    
    $stmtProf = $pdo->prepare("SELECT id_profesor, nombres, apellidos, CONCAT(nombres, ' ', apellidos) AS nombre_completo 
                               FROM profesores 
                               WHERE CONCAT(nombres, ' ', apellidos) LIKE ? 
                                  OR nombres LIKE ? 
                                  OR apellidos LIKE ?");
    $stmtProf->execute(['%Marcos%', '%Marcos%', '%Aurelio%']);
    $profesores = $stmtProf->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($profesores)) {
        echo "<p class='error'>No se encontró a Marcos Aurelio</p>";
        exit;
    }
    
    $id_profesor = $profesores[0]['id_profesor'];
    echo "<p class='success'>Profesor encontrado: <strong>{$profesores[0]['nombre_completo']}</strong> (ID: {$id_profesor})</p>";
    
    // ============================================================
    // PASO 2: Buscar TODOS los bloques donde está asignado
    // ============================================================
    echo "<h2>PASO 2: Bloques Asignados al Profesor</h2>";
    
    $stmtBloques = $pdo->prepare("SELECT hd.id_detalle, hd.dia_semana, hd.hora_inicio, hd.hora_fin, hd.id_materia, hd.id_profesor,
                                         h.id_horario, h.id_gestion, h.id_grado, h.id_seccion,
                                         g.grado AS nombre_grado, s.nombre_seccion
                                  FROM horario_detalle hd
                                  INNER JOIN horarios h ON h.id_horario = hd.id_horario
                                  INNER JOIN grados g ON g.id_grado = h.id_grado
                                  INNER JOIN secciones s ON s.id_seccion = h.id_seccion
                                  WHERE hd.id_profesor = ?
                                  ORDER BY hd.dia_semana, hd.hora_inicio");
    $stmtBloques->execute([$id_profesor]);
    $bloques = $stmtBloques->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table>";
    echo "<tr><th>ID Detalle</th><th>Horario ID</th><th>Grado</th><th>Sección</th><th>Día</th><th>Hora Inicio</th><th>Hora Fin</th><th>Gestión</th></tr>";
    foreach ($bloques as $bloque) {
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
    
    // ============================================================
    // PASO 3: Buscar específicamente en "Tercer Año B" y "Cuarto Año B"
    // ============================================================
    echo "<h2>PASO 3: Bloques en Tercer Año B y Cuarto Año B</h2>";
    
    $stmtEspecifico = $pdo->prepare("SELECT hd.id_detalle, hd.dia_semana, hd.hora_inicio, hd.hora_fin,
                                            h.id_horario, h.id_gestion, h.id_grado, h.id_seccion,
                                            g.grado AS nombre_grado, s.nombre_seccion
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
    
    if (empty($bloquesEspecificos)) {
        echo "<p class='warning'>No se encontraron bloques en Tercer Año B o Cuarto Año B</p>";
    } else {
        echo "<table>";
        echo "<tr><th>ID Detalle</th><th>Horario ID</th><th>Grado</th><th>Sección</th><th>Día</th><th>Hora Inicio</th><th>Hora Fin</th><th>Gestión</th><th>Grado ID</th><th>Sección ID</th></tr>";
        foreach ($bloquesEspecificos as $bloque) {
            echo "<tr>";
            echo "<td>{$bloque['id_detalle']}</td>";
            echo "<td>{$bloque['id_horario']}</td>";
            echo "<td>{$bloque['nombre_grado']}</td>";
            echo "<td>{$bloque['nombre_seccion']}</td>";
            echo "<td>{$bloque['dia_semana']}</td>";
            echo "<td>{$bloque['hora_inicio']}</td>";
            echo "<td>{$bloque['hora_fin']}</td>";
            echo "<td>{$bloque['id_gestion']}</td>";
            echo "<td>{$bloque['id_grado']}</td>";
            echo "<td>{$bloque['id_seccion']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // ============================================================
    // PASO 4: Obtener información del horario "Cuarto Año B"
    // ============================================================
    echo "<h2>PASO 4: Información del Horario 'Cuarto Año B'</h2>";
    
    $stmtHorarioCuarto = $pdo->prepare("SELECT h.id_horario, h.id_gestion, h.id_grado, h.id_seccion, 
                                               g.grado AS nombre_grado, s.nombre_seccion
                                        FROM horarios h
                                        INNER JOIN grados g ON g.id_grado = h.id_grado
                                        INNER JOIN secciones s ON s.id_seccion = h.id_seccion
                                        WHERE g.grado LIKE '%CUARTO%' AND s.nombre_seccion LIKE '%B%'
                                        LIMIT 1");
    $stmtHorarioCuarto->execute();
    $horarioCuarto = $stmtHorarioCuarto->fetch(PDO::FETCH_ASSOC);
    
    if (!$horarioCuarto) {
        echo "<p class='error'>No se encontró el horario de 'Cuarto Año B'</p>";
        exit;
    }
    
    echo "<pre>";
    print_r($horarioCuarto);
    echo "</pre>";
    
    // ============================================================
    // PASO 5: Obtener información del horario "Tercer Año B"
    // ============================================================
    echo "<h2>PASO 5: Información del Horario 'Tercer Año B'</h2>";
    
    $stmtHorarioTercer = $pdo->prepare("SELECT h.id_horario, h.id_gestion, h.id_grado, h.id_seccion, 
                                               g.grado AS nombre_grado, s.nombre_seccion
                                        FROM horarios h
                                        INNER JOIN grados g ON g.id_grado = h.id_grado
                                        INNER JOIN secciones s ON s.id_seccion = h.id_seccion
                                        WHERE g.grado LIKE '%TERCER%' AND s.nombre_seccion LIKE '%B%'
                                        LIMIT 1");
    $stmtHorarioTercer->execute();
    $horarioTercer = $stmtHorarioTercer->fetch(PDO::FETCH_ASSOC);
    
    if (!$horarioTercer) {
        echo "<p class='warning'>No se encontró el horario de 'Tercer Año B'</p>";
    } else {
        echo "<pre>";
        print_r($horarioTercer);
        echo "</pre>";
    }
    
    // ============================================================
    // PASO 6: Simular la validación EXACTA que hace verificar_conflicto_profesor.php
    // ============================================================
    echo "<h2>PASO 6: Simulación de Validación (Lunes 07:50-08:30 en Cuarto Año B)</h2>";
    
    $dia_semana = 'Lunes';
    $hora_inicio = '07:50:00';
    $hora_fin = '08:30:00';
    $id_horario = $horarioCuarto['id_horario'];
    $id_bloque_excluir = 32; // Según la consola del navegador
    
    echo "<p><strong>Parámetros de validación:</strong></p>";
    echo "<ul>";
    echo "<li>ID Profesor: {$id_profesor}</li>";
    echo "<li>Día: {$dia_semana}</li>";
    echo "<li>Hora Inicio: {$hora_inicio}</li>";
    echo "<li>Hora Fin: {$hora_fin}</li>";
    echo "<li>ID Horario (Cuarto Año B): {$id_horario}</li>";
    echo "<li>ID Bloque a Excluir: {$id_bloque_excluir}</li>";
    echo "</ul>";
    
    // Detectar PK
    $cols = $pdo->query("SHOW COLUMNS FROM horario_detalle")->fetchAll(PDO::FETCH_COLUMN);
    $pk = null;
    foreach (['id_detalle','id_horario_detalle','id'] as $c) { 
        if (in_array($c, $cols, true)) { 
            $pk = $c; 
            break; 
        } 
    }
    
    echo "<p><strong>PK detectada:</strong> {$pk}</p>";
    
    // Construir la query EXACTA que usa verificar_conflicto_profesor.php
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
                   hd.id_profesor as id_profesor_real,
                   h.id_gestion
            FROM horario_detalle hd
            INNER JOIN horarios h ON h.id_horario = hd.id_horario
            INNER JOIN grados g ON g.id_grado = h.id_grado
            INNER JOIN secciones s ON s.id_seccion = h.id_seccion
            INNER JOIN profesores p ON p.id_profesor = hd.id_profesor
            WHERE hd.id_profesor = ? 
              AND hd.id_profesor IS NOT NULL
              AND hd.dia_semana = ?";
    
    $params = [$id_profesor, $dia_semana];
    
    // Excluir bloque actual
    if ($id_bloque_excluir > 0) {
        $sql .= " AND hd.$pk != ?";
        $params[] = $id_bloque_excluir;
    }
    
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
    
    echo "<p><strong>SQL Query Final:</strong></p>";
    echo "<pre>" . htmlspecialchars($sql) . "</pre>";
    
    echo "<p><strong>Parámetros:</strong></p>";
    echo "<pre>";
    print_r($params);
    echo "</pre>";
    
    // Ejecutar query
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $conflictos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($conflictos)) {
        echo "<p class='error'><strong>NO SE DETECTÓ CONFLICTO</strong></p>";
        echo "<p>Esto significa que la query NO está encontrando el conflicto que debería existir.</p>";
        
        // ============================================================
        // PASO 7: Análisis detallado - ¿Por qué no se detecta?
        // ============================================================
        echo "<h2>PASO 7: Análisis - ¿Por qué no se detecta el conflicto?</h2>";
        
        // Buscar bloques en "Tercer Año B" en "Lunes 07:50-08:30"
        $stmtAnalisis = $pdo->prepare("SELECT hd.*, h.id_horario, h.id_gestion, h.id_grado, h.id_seccion,
                                              g.grado AS nombre_grado, s.nombre_seccion
                                       FROM horario_detalle hd
                                       INNER JOIN horarios h ON h.id_horario = hd.id_horario
                                       INNER JOIN grados g ON g.id_grado = h.id_grado
                                       INNER JOIN secciones s ON s.id_seccion = h.id_seccion
                                       WHERE hd.id_profesor = ?
                                         AND hd.dia_semana = ?
                                         AND g.grado LIKE '%TERCER%'
                                         AND s.nombre_seccion LIKE '%B%'");
        $stmtAnalisis->execute([$id_profesor, $dia_semana]);
        $bloquesTercer = $stmtAnalisis->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<p><strong>Bloques encontrados en 'Tercer Año B' el día '{$dia_semana}':</strong></p>";
        if (empty($bloquesTercer)) {
            echo "<p class='warning'>No hay bloques en Tercer Año B el día {$dia_semana}</p>";
        } else {
            echo "<table>";
            echo "<tr><th>ID Detalle</th><th>Horario ID</th><th>Grado</th><th>Sección</th><th>Hora Inicio</th><th>Hora Fin</th><th>Gestión</th><th>Grado ID</th><th>Sección ID</th></tr>";
            foreach ($bloquesTercer as $bloque) {
                $solapa = false;
                if ($bloque['hora_inicio'] && $bloque['hora_fin']) {
                    // Verificar solapamiento manual
                    $hi = strtotime($bloque['hora_inicio']);
                    $hf = strtotime($bloque['hora_fin']);
                    $hi_check = strtotime($hora_inicio);
                    $hf_check = strtotime($hora_fin);
                    
                    $solapa = ($hi <= $hi_check && $hf > $hi_check) || 
                              ($hi < $hf_check && $hf >= $hf_check) ||
                              ($hi >= $hi_check && $hf <= $hf_check);
                }
                
                $color = $solapa ? 'background-color: #ffcccc;' : '';
                echo "<tr style='{$color}'>";
                echo "<td>{$bloque['id_detalle']}</td>";
                echo "<td>{$bloque['id_horario']}</td>";
                echo "<td>{$bloque['nombre_grado']}</td>";
                echo "<td>{$bloque['nombre_seccion']}</td>";
                echo "<td>{$bloque['hora_inicio']}</td>";
                echo "<td>{$bloque['hora_fin']}</td>";
                echo "<td>{$bloque['id_gestion']}</td>";
                echo "<td>{$bloque['id_grado']}</td>";
                echo "<td>{$bloque['id_seccion']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        
        // Verificar si la condición de exclusión está eliminando el conflicto
        echo "<p><strong>Análisis de la condición de exclusión:</strong></p>";
        echo "<ul>";
        echo "<li>Gestión Cuarto Año B: {$horarioCuarto['id_gestion']}</li>";
        echo "<li>Grado Cuarto Año B: {$horarioCuarto['id_grado']}</li>";
        echo "<li>Sección Cuarto Año B: {$horarioCuarto['id_seccion']}</li>";
        if ($horarioTercer) {
            echo "<li>Gestión Tercer Año B: {$horarioTercer['id_gestion']}</li>";
            echo "<li>Grado Tercer Año B: {$horarioTercer['id_grado']}</li>";
            echo "<li>Sección Tercer Año B: {$horarioTercer['id_seccion']}</li>";
            
            $mismaGestion = ($horarioCuarto['id_gestion'] == $horarioTercer['id_gestion']);
            $mismoGrado = ($horarioCuarto['id_grado'] == $horarioTercer['id_grado']);
            $mismaSeccion = ($horarioCuarto['id_seccion'] == $horarioTercer['id_seccion']);
            
            echo "<li><strong>¿Misma gestión?</strong> " . ($mismaGestion ? 'SÍ' : 'NO') . "</li>";
            echo "<li><strong>¿Mismo grado?</strong> " . ($mismoGrado ? 'SÍ' : 'NO') . "</li>";
            echo "<li><strong>¿Misma sección?</strong> " . ($mismaSeccion ? 'SÍ' : 'NO') . "</li>";
            
            if ($mismaGestion && $mismoGrado && $mismaSeccion) {
                echo "<li class='error'><strong>PROBLEMA DETECTADO:</strong> La condición de exclusión está eliminando el conflicto porque Tercer Año B y Cuarto Año B tienen el mismo grado-sección-gestión (lo cual es incorrecto).</li>";
            } else {
                echo "<li class='success'>La condición de exclusión NO debería eliminar el conflicto porque tienen diferentes grados/secciones.</li>";
            }
        }
        echo "</ul>";
        
    } else {
        echo "<p class='success'><strong>CONFLICTO DETECTADO:</strong></p>";
        echo "<table>";
        echo "<tr><th>ID Detalle</th><th>Horario ID</th><th>Grado</th><th>Sección</th><th>Día</th><th>Hora Inicio</th><th>Hora Fin</th><th>Gestión</th></tr>";
        foreach ($conflictos as $conflicto) {
            echo "<tr>";
            echo "<td>{$conflicto['id_detalle']}</td>";
            echo "<td>{$conflicto['id_horario']}</td>";
            echo "<td>{$conflicto['nombre_grado']}</td>";
            echo "<td>{$conflicto['nombre_seccion']}</td>";
            echo "<td>{$conflicto['dia_semana']}</td>";
            echo "<td>{$conflicto['hora_inicio']}</td>";
            echo "<td>{$conflicto['hora_fin']}</td>";
            echo "<td>{$conflicto['id_gestion']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>Error: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>



