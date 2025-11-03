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

    // validar duplicado
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
