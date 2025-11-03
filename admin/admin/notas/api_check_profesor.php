<?php
include('../../app/config.php');
header('Content-Type: application/json; charset=utf-8');

$profesor = isset($_GET['profesor']) ? (int)$_GET['profesor'] : 0;
$dia      = isset($_GET['dia']) ? trim($_GET['dia']) : '';
$hi       = isset($_GET['hi']) ? trim($_GET['hi']) : '';
$hf       = isset($_GET['hf']) ? trim($_GET['hf']) : '';

if ($profesor <= 0 || $dia === '' || $hi === '' || $hf === '') {
    echo json_encode(['ok'=>false,'msg'=>'Parámetros incompletos','ocupado'=>false]);
    exit;
}

$gestion = $pdo->query("SELECT id_gestion FROM gestiones WHERE estado = 1 ORDER BY desde DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
if (!$gestion) { echo json_encode(['ok'=>false,'msg'=>'No hay gestión activa','ocupado'=>false]); exit; }

try {
    $sql = "SELECT hd.*, m.nombre_materia, s.nombre_seccion, g.grado, h.aula, h.id_grado, h.id_seccion,
                   CONCAT(p.nombres,' ',p.apellidos) AS profesor_nombre
            FROM horario_detalle hd
            INNER JOIN horarios h ON h.id_horario = hd.id_horario
            INNER JOIN materias m ON m.id_materia = hd.id_materia
            INNER JOIN secciones s ON s.id_seccion = h.id_seccion
            INNER JOIN grados g ON g.id_grado = h.id_grado
            LEFT JOIN profesores p ON p.id_profesor = hd.id_profesor
            WHERE h.id_gestion = :gestion AND hd.id_profesor = :p AND TRIM(LOWER(hd.dia_semana)) = TRIM(LOWER(:d))
            AND (
                ( (CASE WHEN TIME_TO_SEC(hd.hora_inicio) IS NULL THEN 0 ELSE TIME_TO_SEC(hd.hora_inicio) END) < TIME_TO_SEC(:hf)
                  AND (
                      CASE WHEN TIME_TO_SEC(hd.hora_fin)=0 THEN (
                          CASE LEFT(hd.hora_inicio,5)
                              WHEN '07:50' THEN TIME_TO_SEC('08:30:00')
                              WHEN '08:30' THEN TIME_TO_SEC('09:10:00')
                              WHEN '09:10' THEN TIME_TO_SEC('09:50:00')
                              WHEN '10:10' THEN TIME_TO_SEC('10:50:00')
                              WHEN '10:50' THEN TIME_TO_SEC('11:30:00')
                              WHEN '11:30' THEN TIME_TO_SEC('12:10:00')
                              ELSE 86400
                          END
                      ) ELSE TIME_TO_SEC(hd.hora_fin) END) > TIME_TO_SEC(:hi)
                )
            )";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':gestion' => $gestion['id_gestion'],
        ':p' => $profesor,
        ':d' => $dia,
        ':hf' => $hf . ':00',
        ':hi' => $hi . ':00'
    ]);
    $conflictos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['ok'=>true,'ocupado'=>!empty($conflictos),'conflictos'=>$conflictos]);
} catch (Throwable $e) {
    echo json_encode(['ok'=>false,'msg'=>$e->getMessage(),'ocupado'=>false]);
}
?>


