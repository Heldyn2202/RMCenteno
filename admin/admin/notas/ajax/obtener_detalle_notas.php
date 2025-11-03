<?php
include('../../../app/config.php');

$id_estudiante = $_POST['id_estudiante'] ?? null;
$lapsos = $_POST['lapsos'] ?? [];

if (!$id_estudiante || empty($lapsos)) {
    exit('<div class="text-danger text-center">Datos incompletos.</div>');
}

$lapsos_str = implode(',', array_map('intval', $lapsos));

// 🔹 Consulta: trae materias, profesor y notas por cada lapso seleccionado
$sql = "
SELECT 
    m.id_materia,
    m.nombre_materia,
    CONCAT(p.nombres, ' ', p.apellidos) AS profesor,
    GROUP_CONCAT(CONCAT(ne.id_lapso, ':', IFNULL(ne.calificacion, '')) ORDER BY ne.id_lapso) AS notas
FROM asignaciones_profesor ap
INNER JOIN materias m ON m.id_materia = ap.id_materia
INNER JOIN profesores p ON p.id_profesor = ap.id_profesor
LEFT JOIN notas_estudiantes ne 
    ON ne.id_materia = m.id_materia 
    AND ne.id_estudiante = :id_estudiante
    AND ne.id_lapso IN ($lapsos_str)
WHERE ap.estado = 1
  AND ap.id_seccion IN (
    SELECT id_seccion FROM inscripciones WHERE id_estudiante = :id_estudiante
  )
GROUP BY m.id_materia
ORDER BY m.nombre_materia;
";

$query = $pdo->prepare($sql);
$query->bindParam(':id_estudiante', $id_estudiante);
$query->execute();
$materias = $query->fetchAll(PDO::FETCH_ASSOC);

if (empty($materias)) {
    echo '<div class="text-center text-muted p-2">No hay materias asignadas.</div>';
    exit;
}

// 🔹 Obtener los nombres de los lapsos seleccionados
$lapsos_nombres = [];
foreach ($lapsos as $l) {
    $lapsos_nombres[$l] = $pdo->query("SELECT nombre_lapso FROM lapsos WHERE id_lapso = $l")->fetchColumn();
}

// 🔹 Construcción de tabla
echo '<table class="table table-sm table-bordered mt-2">';
echo '<thead class="bg-light"><tr><th>Materia</th><th>Profesor</th>';
foreach ($lapsos_nombres as $nombre) {
    echo "<th class='text-center'>$nombre</th>";
}
echo '</tr></thead><tbody>';

foreach ($materias as $m) {
    echo "<tr>";
    echo "<td><strong>{$m['nombre_materia']}</strong></td>";
    echo "<td>{$m['profesor']}</td>";

    // Procesar notas por lapso
    $notas = [];
    if ($m['notas']) {
        foreach (explode(',', $m['notas']) as $n) {
            [$lapso_id, $calif] = explode(':', $n);
            $notas[$lapso_id] = $calif;
        }
    }

    foreach ($lapsos as $l) {
        if (isset($notas[$l]) && $notas[$l] !== '') {
            echo "<td class='text-center text-success'>
                    <i class='fas fa-check-circle'></i> 
                    <span class='badge badge-light ml-1'>{$notas[$l]}</span>
                  </td>";
        } else {
            echo "<td class='text-center text-muted'>—</td>";
        }
    }

    echo "</tr>";
}
echo '</tbody></table>';
?>
