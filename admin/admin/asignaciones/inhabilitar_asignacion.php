<?php
header('Content-Type: application/json; charset=utf-8');
session_start();

try {
    require_once('../../app/config.php');

    // Aceptar id desde POST o GET; aceptar 'id_asignacion' o 'id'
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

    // contar notas afectadas (informativo)
    $sql_notas = "
        SELECT COUNT(*) AS cnt
        FROM notas_estudiantes ne
        JOIN inscripciones i ON i.id_estudiante = ne.id_estudiante
        WHERE ne.id_materia = :id_materia
          AND i.id_seccion = :id_seccion
          AND i.id_gestion = :id_gestion
    ";
    $q = $pdo->prepare($sql_notas);
    $q->execute([
        ':id_materia' => $asig['id_materia'],
        ':id_seccion' => $asig['id_seccion'],
        ':id_gestion' => $asig['id_gestion']
    ]);
    $row = $q->fetch(PDO::FETCH_ASSOC);
    $notas_count = intval($row['cnt'] ?? 0);

    // inhabilitar
    $upd = $pdo->prepare("UPDATE asignaciones_profesor SET estado = 0, fecha_actualizacion = NOW() WHERE id_asignacion = ?");
    $ok = $upd->execute([$id]);

    if ($ok) {
        // Insertar auditoría si existe la tabla (opcional)
        if (isset($pdo)) {
            $usuario = $_SESSION['nombres_sesion_usuario'] ?? $_SESSION['usuario'] ?? $_SESSION['login'] ?? null;
            $ip = $_SERVER['REMOTE_ADDR'] ?? null;
            $detalles = json_encode([
                'id_profesor' => $asig['id_profesor'],
                'id_materia' => $asig['id_materia'],
                'id_seccion' => $asig['id_seccion'],
                'id_gestion' => $asig['id_gestion'],
                'notas_afectadas' => $notas_count
            ], JSON_UNESCAPED_UNICODE);
            // intentar insertar en tabla asignaciones_auditoria si existe
            try {
                $stmt_log = $pdo->prepare("INSERT INTO asignaciones_auditoria (id_asignacion, accion, usuario, detalles, ip_origen) VALUES (?, 'inhabilitar', ?, ?, ?)");
                $stmt_log->execute([$id, $usuario, $detalles, $ip]);
            } catch (Throwable $e) {
                // no bloquear proceso si la tabla no existe
            }
        }

        echo json_encode([
            'status'=>'ok',
            'msg'=>'La asignación fue inhabilitada correctamente.',
            'id_asignacion'=>$id,
            'notas_afectadas'=>$notas_count
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['status'=>'error','msg'=>'No se pudo inhabilitar la asignación.']);
    }

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['status'=>'error','msg'=>'Error servidor','detalle'=>$e->getMessage()]);
}
?>