<?php
include('../../app/config.php');
header('Content-Type: application/json; charset=utf-8');

try {
    // recoger y validar
    $id_asignacion = isset($_POST['id_asignacion']) ? intval($_POST['id_asignacion']) : 0;
    $id_profesor   = isset($_POST['id_profesor']) ? intval($_POST['id_profesor']) : 0;
    $id_materia    = isset($_POST['id_materia']) ? intval($_POST['id_materia']) : 0;
    $id_seccion    = isset($_POST['id_seccion']) ? intval($_POST['id_seccion']) : 0;
    $id_gestion    = isset($_POST['id_gestion']) ? intval($_POST['id_gestion']) : 0;

    if (!$id_asignacion || !$id_profesor || !$id_materia || !$id_seccion || !$id_gestion) {
        echo json_encode([
            'status' => 'error',
            'tipo'   => 'error',
            'titulo' => 'Datos incompletos',
            'mensaje'=> 'Verifica los campos e intenta de nuevo.'
        ]);
        exit;
    }

    // 1) comprobar conflicto: misma materia+seccion+gestion pero asignada a otro profesor (no permitimos)
    $sql_conf = "
        SELECT ap.id_profesor, CONCAT(p.nombres,' ',p.apellidos) AS nombre_profesor
        FROM asignaciones_profesor ap
        JOIN profesores p ON p.id_profesor = ap.id_profesor
        WHERE ap.id_materia = ? AND ap.id_seccion = ? AND ap.id_gestion = ? AND ap.estado = 1 AND ap.id_asignacion != ?
        LIMIT 1
    ";
    $stmt_conf = $pdo->prepare($sql_conf);
    $stmt_conf->execute([$id_materia, $id_seccion, $id_gestion, $id_asignacion]);
    $conf = $stmt_conf->fetch(PDO::FETCH_ASSOC);

    if ($conf && intval($conf['id_profesor']) !== $id_profesor) {
        // obtener nombre de la gestión para el mensaje (legible)
        $stmt_g = $pdo->prepare("SELECT CONCAT('Periodo ', YEAR(desde), ' - ', YEAR(hasta)) AS nombre FROM gestiones WHERE id_gestion = ? LIMIT 1");
        $stmt_g->execute([$id_gestion]);
        $g_row = $stmt_g->fetch(PDO::FETCH_ASSOC);
        $gestion_nombre = $g_row['nombre'] ?? ('ID ' . $id_gestion);

        echo json_encode([
            'status' => 'ok',
            'tipo'   => 'error',
            'titulo' => 'Asignación ya registrada',
            'mensaje'=> "La materia seleccionada ya está asignada al profesor <strong>{$conf['nombre_profesor']}</strong> en esta sección para la gestión activa ({$gestion_nombre}).<br><br>No se puede asignar la misma materia a dos profesores en la misma sección/gestión."
        ]);
        exit;
    }

    // 2) comprobar duplicado exacto para el mismo profesor (ya tenías esto)
    $sql_check = "SELECT COUNT(*) FROM asignaciones_profesor 
                  WHERE id_profesor=? AND id_materia=? AND id_seccion=? AND id_gestion=? 
                  AND id_asignacion != ?";
    $stmt = $pdo->prepare($sql_check);
    $stmt->execute([$id_profesor, $id_materia, $id_seccion, $id_gestion, $id_asignacion]);
    $existe = (int)$stmt->fetchColumn();

    if ($existe > 0) {
        echo json_encode([
            'status' => 'ok',
            'tipo'   => 'warning',
            'titulo' => 'Asignación existente',
            'mensaje'=> 'Ya existe una asignación idéntica para este profesor en esa sección y gestión.'
        ]);
        exit;
    }

    // actualizar (usando fecha_actualizacion que dijiste que tienes)
    $sql = "UPDATE asignaciones_profesor 
            SET id_profesor = ?, id_materia = ?, id_seccion = ?, id_gestion = ?, fecha_actualizacion = NOW()
            WHERE id_asignacion = ?";
    $stmt = $pdo->prepare($sql);
    $ok = $stmt->execute([$id_profesor, $id_materia, $id_seccion, $id_gestion, $id_asignacion]);

    if ($ok) {
        echo json_encode([
            'status' => 'ok',
            'tipo'   => 'success',
            'titulo' => 'Actualización exitosa',
            'mensaje'=> 'La asignación fue modificada correctamente.'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'tipo'   => 'error',
            'titulo' => 'Error',
            'mensaje'=> 'No se pudo actualizar la asignación. Inténtalo nuevamente.'
        ]);
    }

} catch (Throwable $e) {
    echo json_encode([
        'status' => 'error',
        'tipo'   => 'error',
        'titulo' => 'Error servidor',
        'mensaje'=> 'Detalle: ' . $e->getMessage()
    ]);
}
?>