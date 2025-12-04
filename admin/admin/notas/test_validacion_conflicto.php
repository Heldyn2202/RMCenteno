<?php
/**
 * Script de prueba para verificar la lógica de validación de conflictos
 * Ejecutar con: ?profesor=X&dia=Lunes&hi=07:50&hf=08:30
 */
include('../../app/config.php');

$profesor = isset($_GET['profesor']) ? (int)$_GET['profesor'] : 0;
$dia = isset($_GET['dia']) ? trim($_GET['dia']) : 'Lunes';
$hi = isset($_GET['hi']) ? trim($_GET['hi']) : '07:50';
$hf = isset($_GET['hf']) ? trim($_GET['hf']) : '08:30';

if (!$profesor) {
    die("Uso: ?profesor=X&dia=Lunes&hi=07:50&hf=08:30");
}

// Normalizar
$dia_normalizado = ucfirst(strtolower(trim($dia)));
$hora_inicio_normalizada = strlen($hi) == 5 ? $hi . ':00' : $hi;
$hora_fin_normalizada = strlen($hf) == 5 ? $hf . ':00' : $hf;

$mapa_horas_fin = [
    '07:50:00' => '08:30:00',
    '08:30:00' => '09:10:00',
    '09:10:00' => '09:50:00',
    '10:10:00' => '10:50:00',
    '10:50:00' => '11:30:00',
    '11:30:00' => '12:10:00'
];

$gestion = $pdo->query("SELECT id_gestion FROM gestiones WHERE estado = 1 ORDER BY desde DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);

echo "<h2>Test de Validación de Conflictos</h2>";
echo "<p><strong>Profesor ID:</strong> $profesor</p>";
echo "<p><strong>Día:</strong> $dia_normalizado</p>";
echo "<p><strong>Hora propuesta:</strong> $hora_inicio_normalizada - $hora_fin_normalizada</p>";
echo "<hr>";

// Obtener todos los bloques del profesor
$sqlBloques = "SELECT hd.id_detalle, hd.id_horario, hd.hora_inicio, hd.hora_fin, 
                      h.id_grado, h.id_seccion, g.grado, s.nombre_seccion, m.nombre_materia
               FROM horario_detalle hd
               INNER JOIN horarios h ON h.id_horario = hd.id_horario
               INNER JOIN grados g ON g.id_grado = h.id_grado
               INNER JOIN secciones s ON s.id_seccion = h.id_seccion
               LEFT JOIN materias m ON m.id_materia = hd.id_materia
               WHERE h.id_gestion = ? 
                 AND hd.id_profesor = ? 
                 AND hd.id_profesor IS NOT NULL
                 AND hd.dia_semana = ?
                 AND hd.hora_inicio IS NOT NULL
                 AND hd.hora_inicio != '00:00:00'
               ORDER BY hd.hora_inicio";
$stmtBloques = $pdo->prepare($sqlBloques);
$stmtBloques->execute([$gestion['id_gestion'], $profesor, $dia_normalizado]);
$bloques = $stmtBloques->fetchAll(PDO::FETCH_ASSOC);

echo "<h3>Bloques existentes del profesor en $dia_normalizado:</h3>";
if (empty($bloques)) {
    echo "<p>No hay bloques asignados.</p>";
} else {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Grado</th><th>Sección</th><th>Materia</th><th>Hora Inicio</th><th>Hora Fin</th><th>¿Conflicto?</th></tr>";
    
    $intervalosSeSolapan = function($inicio1, $fin1, $inicio2, $fin2) {
        if (strlen($inicio1) == 5) $inicio1 .= ':00';
        if (strlen($fin1) == 5) $fin1 .= ':00';
        if (strlen($inicio2) == 5) $inicio2 .= ':00';
        if (strlen($fin2) == 5) $fin2 .= ':00';
        
        list($h1, $m1, $s1) = explode(':', $inicio1);
        list($hf1, $mf1, $sf1) = explode(':', $fin1);
        list($h2, $m2, $s2) = explode(':', $inicio2);
        list($hf2, $mf2, $sf2) = explode(':', $fin2);
        
        $seg1_inicio = ($h1 * 3600) + ($m1 * 60) + $s1;
        $seg1_fin = ($hf1 * 3600) + ($mf1 * 60) + $sf1;
        $seg2_inicio = ($h2 * 3600) + ($m2 * 60) + $s2;
        $seg2_fin = ($hf2 * 3600) + ($mf2 * 60) + $sf2;
        
        return ($seg1_inicio < $seg2_fin && $seg1_fin > $seg2_inicio);
    };
    
    foreach ($bloques as $bloque) {
        $hora_inicio_existente = $bloque['hora_inicio'];
        $hora_fin_existente = $bloque['hora_fin'];
        
        // Inferir hora_fin si es inválida
        if (empty($hora_fin_existente) || $hora_fin_existente == '00:00:00' || $hora_fin_existente == '00:00') {
            $hora_inicio_key = substr($hora_inicio_existente, 0, 5) . ':00';
            $hora_fin_existente = $mapa_horas_fin[$hora_inicio_key] ?? $hora_fin_existente;
        }
        
        if (strlen($hora_fin_existente) == 5) $hora_fin_existente .= ':00';
        if (strlen($hora_inicio_existente) == 5) $hora_inicio_existente .= ':00';
        
        $conflicto = $intervalosSeSolapan(
            $hora_inicio_existente,
            $hora_fin_existente,
            $hora_inicio_normalizada,
            $hora_fin_normalizada
        );
        
        $color = $conflicto ? 'red' : 'green';
        echo "<tr style='background-color: $color;'>";
        echo "<td>{$bloque['grado']}</td>";
        echo "<td>{$bloque['nombre_seccion']}</td>";
        echo "<td>{$bloque['nombre_materia']}</td>";
        echo "<td>$hora_inicio_existente</td>";
        echo "<td>$hora_fin_existente</td>";
        echo "<td><strong>" . ($conflicto ? 'SÍ - CONFLICTO' : 'NO') . "</strong></td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "<hr>";
echo "<p><a href='crear_horarios.php'>Volver a crear horarios</a></p>";
?>

