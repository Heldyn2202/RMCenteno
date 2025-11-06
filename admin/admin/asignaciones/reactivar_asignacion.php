<?php
header('Content-Type: application/json; charset=utf-8');
session_start();

try {
    require_once('../../app/config.php');

    // Aceptar id desde POST o GET
    $id = null;
    $idPost = filter_input(INPUT_POST, 'id_asignacion', FILTER_VALIDATE_INT);
    $idAltPost = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $idGet = filter_input(INPUT_GET, 'id_asignacion', FILTER_VALIDATE_INT);
    $idAltGet = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    if ($idPost !== null && $idPost !== false) $id = intval($idPost);
    elseif ($idAltPost !== null && $idAltPost !== false) $id = intval($idAltPost);
    elseif ($idGet !== null && $idGet !== false) $id = intval($idGet);
    elseif ($idAltGet !== null && $idAltGet !== false) $id = intval($idAltGet);
    else $id = 0;

    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['status'=>'error','msg'=>'ID no válido. Asegúrese de enviar id_asignacion o id (POST o GET).']);
        exit;
    }

    // comprobar existencia
    $stmt = $pdo->prepare("SELECT id_asignacion, id_profesor, id_materia, id_seccion, id_gestion FROM asignaciones_profesor WHERE id_asignacion = ? LIMIT 1");
    $stmt->execute([$id]);
    $asig = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$asig) {
        http_response_code(404);
        echo json_encode(['status'=>'error','msg'=>'Asignación no encontrada.']);
        exit;
    }

    // comprobar conflicto: otra asignación activa con la misma materia+seccion+gestion
    $stmt_conf = $pdo->prepare("
        SELECT ap.id_asignacion, CONCAT(p.nombres,' ',p.apellidos) AS profesor
        FROM asignaciones_profesor ap
        JOIN profesores p ON p.id_profesor = ap.id_profesor
        WHERE ap.id_materia = :id_materia
          AND ap.id_seccion = :id_seccion
          AND ap.id_gestion = :id_gestion
          AND ap.estado = 1
          AND ap.id_asignacion != :id_asignacion
        LIMIT 1
    ");
    $stmt_conf->execute([
        ':id_materia' => $asig['id_materia'],
        ':id_seccion' => $asig['id_seccion'],
        ':id_gestion' => $asig['id_gestion'],
        ':id_asignacion' => $id
    ]);
    $conf = $stmt_conf->fetch(PDO::FETCH_ASSOC);
    if ($conf && intval($conf['id_asignacion']) > 0) {
        http_response_code(409);
        echo json_encode(['status'=>'error','msg'=>"No se puede reactivar: la materia ya está asignada al profesor {$conf['profesor']} en la misma sección/gestión."]);
        exit;
    }

    // reactivar
    $upd = $pdo->prepare("UPDATE asignaciones_profesor SET estado = 1, fecha_actualizacion = NOW() WHERE id_asignacion = ?");
    $ok = $upd->execute([$id]);

    if ($ok) {
        // Insertar auditoría si es posible
        if (isset($pdo)) {
            $usuario = $_SESSION['nombres_sesion_usuario'] ?? $_SESSION['usuario'] ?? $_SESSION['login'] ?? null;
            $ip = $_SERVER['REMOTE_ADDR'] ?? null;
            $detalles = json_encode([
                'id_profesor' => $asig['id_profesor'],
                'id_materia' => $asig['id_materia'],
                'id_seccion' => $asig['id_seccion'],
                'id_gestion' => $asig['id_gestion']
            ], JSON_UNESCAPED_UNICODE);
            try {
                $stmt_log = $pdo->prepare("INSERT INTO asignaciones_auditoria (id_asignacion, accion, usuario, detalles, ip_origen) VALUES (?, 'reactivar', ?, ?, ?)");
                $stmt_log->execute([$id, $usuario, $detalles, $ip]);
            } catch (Throwable $e) {
                // ignorar si la tabla no existe
            }
        }

        echo json_encode(['status'=>'ok','msg'=>'La asignación fue reactivada correctamente.','id_asignacion'=>$id]);
    } else {
        http_response_code(500);
        echo json_encode(['status'=>'error','msg'=>'No se pudo reactivar la asignación.']);
    }

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['status'=>'error','msg'=>'Error servidor','detalle'=>$e->getMessage()]);
}
?>