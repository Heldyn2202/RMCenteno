<?php
/**
 * Script de diagnóstico para verificar por qué no se detecta el conflicto
 * de Heldyn Diaz entre Tercer Año Sección A y Cuarto Año Sección A
 */
include('../../app/config.php');

// Parámetros del caso específico
$profesor_id = isset($_GET['profesor']) ? (int)$_GET['profesor'] : 0;
$dia = isset($_GET['dia']) ? trim($_GET['dia']) : 'Lunes';
$hora_inicio = isset($_GET['hi']) ? trim($_GET['hi']) : '07:50';
$hora_fin = isset($_GET['hf']) ? trim($_GET['hf']) : '08:30';

if (!$profesor_id) {
    die("Por favor, proporciona el ID del profesor: ?profesor=X&dia=Lunes&hi=07:50&hf=08:30");
}

// Normalizar
$dia_normalizado = ucfirst(strtolower(trim($dia)));
$hora_fin_normalizada = strlen($hora_fin) == 5 ? $hora_fin . ':00' : $hora_fin;
$hora_inicio_normalizada = strlen($hora_inicio) == 5 ? $hora_inicio . ':00' : $hora_inicio;

// Obtener gestión activa
$gestion = $pdo->query("SELECT id_gestion FROM gestiones WHERE estado = 1 ORDER BY desde DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);

echo "<h2>Diagnóstico de Conflicto de Profesor</h2>";
echo "<p><strong>Profesor ID:</strong> $profesor_id</p>";
echo "<p><strong>Día:</strong> $dia_normalizado</p>";
echo "<p><strong>Hora:</strong> $hora_inicio_normalizada - $hora_fin_normalizada</p>";
echo "<p><strong>Gestión:</strong> {$gestion['id_gestion']}</p>";
echo "<hr>";

// 1. Verificar que el profesor existe
$stmtProf = $pdo->prepare("SELECT id_profesor, nombres, apellidos FROM profesores WHERE id_profesor = ?");
$stmtProf->execute([$profesor_id]);
$profesor = $stmtProf->fetch(PDO::FETCH_ASSOC);
if (!$profesor) {
    die("Profesor no encontrado");
}
echo "<h3>1. Profesor encontrado:</h3>";
echo "<p>{$profesor['nombres']} {$profesor['apellidos']}</p>";

// 2. Buscar TODOS los horarios de este profesor en este día y gestión
echo "<h3>2. Buscando horarios existentes del profesor:</h3>";
$sqlTodos = "SELECT hd.*, h.id_grado, h.id_seccion, g.grado, s.nombre_seccion, m.nombre_materia
             FROM horario_detalle hd
             INNER JOIN horarios h ON h.id_horario = hd.id_horario
             INNER JOIN grados g ON g.id_grado = h.id_grado
             INNER JOIN secciones s ON s.id_seccion = h.id_seccion
             LEFT JOIN materias m ON m.id_materia = hd.id_materia
             WHERE h.id_gestion = ?
               AND hd.id_profesor = ?
               AND hd.dia_semana = ?
             ORDER BY hd.hora_inicio";
$stmtTodos = $pdo->prepare($sqlTodos);
$stmtTodos->execute([$gestion['id_gestion'], $profesor_id, $dia_normalizado]);
$todos = $stmtTodos->fetchAll(PDO::FETCH_ASSOC);

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Grado</th><th>Sección</th><th>Materia</th><th>Hora Inicio</th><th>Hora Fin</th><th>¿Conflicto?</th></tr>";
foreach ($todos as $h) {
    $conflicto = false;
    if ($h['hora_fin'] && $h['hora_fin'] != '00:00:00') {
        $conflicto = ($h['hora_inicio'] < $hora_fin_normalizada && $h['hora_fin'] > $hora_inicio_normalizada);
    } else {
        // Inferir hora_fin
        $mapa = [
            '07:50:00' => '08:30:00',
            '08:30:00' => '09:10:00',
            '09:10:00' => '09:50:00',
            '10:10:00' => '10:50:00',
            '10:50:00' => '11:30:00',
            '11:30:00' => '12:10:00'
        ];
        $hf_inferida = $mapa[$h['hora_inicio']] ?? null;
        if ($hf_inferida) {
            $conflicto = ($h['hora_inicio'] < $hora_fin_normalizada && $hf_inferida > $hora_inicio_normalizada);
        }
    }
    $color = $conflicto ? 'red' : 'green';
    echo "<tr style='background-color: $color;'>";
    echo "<td>{$h['grado']}</td>";
    echo "<td>{$h['nombre_seccion']}</td>";
    echo "<td>{$h['nombre_materia']}</td>";
    echo "<td>{$h['hora_inicio']}</td>";
    echo "<td>{$h['hora_fin']}</td>";
    echo "<td>" . ($conflicto ? 'SÍ' : 'NO') . "</td>";
    echo "</tr>";
}
echo "</table>";

// 3. Ejecutar la misma consulta que usa api_check_profesor.php
echo "<h3>3. Consulta de validación (igual que api_check_profesor.php):</h3>";
$sqlCheck = "SELECT hd.id_detalle, hd.id_horario, hd.hora_inicio, hd.hora_fin, h.id_grado, h.id_seccion, g.grado, s.nombre_seccion, m.nombre_materia
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
               AND (
                 (hd.hora_fin IS NOT NULL AND hd.hora_fin != '00:00:00' AND hd.hora_inicio < ? AND hd.hora_fin > ?)
                 OR
                 (hd.hora_fin IS NULL OR hd.hora_fin = '00:00:00' AND hd.hora_inicio IN ('07:50:00','08:30:00','09:10:00','10:10:00','10:50:00','11:30:00') AND
                  CASE hd.hora_inicio
                    WHEN '07:50:00' THEN '08:30:00' < ? AND '08:30:00' > ?
                    WHEN '08:30:00' THEN '09:10:00' < ? AND '09:10:00' > ?
                    WHEN '09:10:00' THEN '09:50:00' < ? AND '09:50:00' > ?
                    WHEN '10:10:00' THEN '10:50:00' < ? AND '10:50:00' > ?
                    WHEN '10:50:00' THEN '11:30:00' < ? AND '11:30:00' > ?
                    WHEN '11:30:00' THEN '12:10:00' < ? AND '12:10:00' > ?
                    ELSE FALSE
                  END
                 )
               )
             LIMIT 1";

$stmtCheck = $pdo->prepare($sqlCheck);
$stmtCheck->execute([
    $gestion['id_gestion'],
    $profesor_id,
    $dia_normalizado,
    $hora_fin_normalizada, $hora_inicio_normalizada, // Para hora_fin válida
    $hora_fin_normalizada, $hora_inicio_normalizada, // Para 07:50
    $hora_fin_normalizada, $hora_inicio_normalizada, // Para 08:30
    $hora_fin_normalizada, $hora_inicio_normalizada, // Para 09:10
    $hora_fin_normalizada, $hora_inicio_normalizada, // Para 10:10
    $hora_fin_normalizada, $hora_inicio_normalizada, // Para 10:50
    $hora_fin_normalizada, $hora_inicio_normalizada  // Para 11:30
]);
$conflicto = $stmtCheck->fetch(PDO::FETCH_ASSOC);

if ($conflicto) {
    echo "<p style='color: red;'><strong>✓ CONFLICTO DETECTADO:</strong></p>";
    echo "<ul>";
    echo "<li><strong>Grado:</strong> {$conflicto['grado']}</li>";
    echo "<li><strong>Sección:</strong> {$conflicto['nombre_seccion']}</li>";
    echo "<li><strong>Materia:</strong> {$conflicto['nombre_materia']}</li>";
    echo "<li><strong>Hora:</strong> {$conflicto['hora_inicio']} - {$conflicto['hora_fin']}</li>";
    echo "</ul>";
} else {
    echo "<p style='color: orange;'><strong>✗ NO SE DETECTÓ CONFLICTO</strong></p>";
    echo "<p>Esto significa que la consulta no encontró ningún horario que solape con el horario propuesto.</p>";
}

echo "<hr>";
echo "<p><a href='crear_horarios.php'>Volver a crear horarios</a></p>";
?>

