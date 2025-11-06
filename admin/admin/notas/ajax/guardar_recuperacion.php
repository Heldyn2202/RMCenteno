<?php
require_once('../../../app/config.php');
header('Content-Type: application/json');

try {
    // Verificar método POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido.');
    }

    // Recoger datos
    $id_seccion = $_POST['id_seccion'] ?? null;
    $id_materia = $_POST['id_materia'] ?? null;
    $id_lapso   = $_POST['id_lapso'] ?? null;
    $tipo       = $_POST['tipo'] ?? null;
    $notas      = $_POST['nota'] ?? [];
    $intentos   = $_POST['intento'] ?? [];
    $observaciones = $_POST['observaciones'] ?? []; // ahora con key 'observaciones'

    if (!$id_seccion || !$id_materia || !$tipo) {
        throw new Exception('Faltan datos obligatorios.');
    }

    // Obtener gestion activa para mensajes (si aplica)
    $stmt_g = $pdo->prepare("SELECT CONCAT('Periodo ', DATE_FORMAT(desde, '%Y'), ' - ', DATE_FORMAT(hasta, '%Y')) AS nombre_gestion FROM gestiones WHERE estado = 1 LIMIT 1");
    $stmt_g->execute();
    $gestion_activa_row = $stmt_g->fetch(PDO::FETCH_ASSOC);
    $gestion_activa_nombre = $gestion_activa_row['nombre_gestion'] ?? null;

    $pdo->beginTransaction();
    $fecha_actual = date('Y-m-d H:i:s');
    $procesados = 0;

    // Estadísticas para el resumen
    $aprobados = [];
    $reprobados = [];
    $movidos_a_pendiente = [];
    $aplazados = []; // repite curso (pendiente aplazado)
    $guardados_ids = [];

    // Preparar consultas
    $sql_check = "
        SELECT COUNT(*) 
        FROM recuperaciones 
        WHERE id_estudiante = :id_estudiante 
          AND id_materia = :id_materia 
          AND id_seccion = :id_seccion 
          AND tipo = :tipo 
          AND intento = :intento
    ";
    $stmt_check = $pdo->prepare($sql_check);

    // Insert en recuperaciones: asegurarse que la columna se llama 'observaciones'
    $sql_insert = "
        INSERT INTO recuperaciones (id_estudiante, id_materia, id_seccion, tipo, intento, calificacion, fecha_registro, observaciones)
        VALUES (:id_estudiante, :id_materia, :id_seccion, :tipo, :intento, :nota, :fecha, :observaciones)
    ";
    $stmt_insert = $pdo->prepare($sql_insert);

    $sql_upsert_nota = "
        INSERT INTO notas_estudiantes (id_estudiante, id_materia, id_lapso, calificacion, observaciones)
        VALUES (:id_estudiante, :id_materia, :id_lapso, :nota, :observaciones)
        ON DUPLICATE KEY UPDATE calificacion = VALUES(calificacion), observaciones = VALUES(observaciones)
    ";
    $stmt_upsert_nota = $pdo->prepare($sql_upsert_nota);

    $sql_insert_pend = "
        INSERT INTO materias_pendientes (id_estudiante, id_materia, id_seccion, fecha_registro, estado)
        VALUES (:id_estudiante, :id_materia, :id_seccion, :fecha, 'pendiente')
        ON DUPLICATE KEY UPDATE estado = 'pendiente', fecha_registro = :fecha
    ";
    $stmt_insert_pend = $pdo->prepare($sql_insert_pend);

    $sql_update_pend_aplazado = "
        UPDATE materias_pendientes SET estado = 'aplazado' 
        WHERE id_estudiante = :id_estudiante AND id_materia = :id_materia
    ";
    $stmt_update_pend_aplazado = $pdo->prepare($sql_update_pend_aplazado);

    // Para obtener nombre del estudiante (para mensajes)
    $stmt_nombre = $pdo->prepare("SELECT CONCAT(nombres, ' ', apellidos) AS nombre FROM estudiantes WHERE id_estudiante = :id_estudiante LIMIT 1");

    foreach ($notas as $id_estudiante => $nota_raw) {
        // Normalizar
        $nota = trim($nota_raw);
        if ($nota === '' || !is_numeric($nota)) continue;
        $nota = floatval($nota);

        $intento = isset($intentos[$id_estudiante]) ? intval($intentos[$id_estudiante]) : 0;
        if ($intento === 0) continue;

        // Observación asociada
        $obs_text = isset($observaciones[$id_estudiante]) ? trim($observaciones[$id_estudiante]) : '';

        // Evitar duplicados
        $stmt_check->execute([
            ':id_estudiante' => $id_estudiante,
            ':id_materia' => $id_materia,
            ':id_seccion' => $id_seccion,
            ':tipo' => $tipo,
            ':intento' => $intento
        ]);
        if ($stmt_check->fetchColumn() > 0) continue;

        // Insertar intento (con observaciones)
        $stmt_insert->execute([
            ':id_estudiante' => $id_estudiante,
            ':id_materia' => $id_materia,
            ':id_seccion' => $id_seccion,
            ':tipo' => $tipo,
            ':intento' => $intento,
            ':nota' => $nota,
            ':fecha' => $fecha_actual,
            ':observaciones' => $obs_text
        ]);

        // Lógica de aprobación y transición
        if ($tipo === 'revision') {
            if ($nota >= 10) {
                // Aprueba Revisión: actualizar nota definitiva del lapso (usamos id_lapso recibido)
                $stmt_upsert_nota->execute([
                    ':id_estudiante' => $id_estudiante,
                    ':id_materia' => $id_materia,
                    ':id_lapso' => $id_lapso,
                    ':nota' => $nota,
                    ':observaciones' => ($obs_text !== '' ? $obs_text : 'Aprobado en revisión')
                ]);
                $aprobados[] = $id_estudiante;
            } else {
                // Reprobó en este intento
                $reprobados[] = $id_estudiante;
                // Si fue el 2º intento y reprobó -> se mueve a materias pendientes
                if ($intento >= 2) {
                    $stmt_insert_pend->execute([
                        ':id_estudiante' => $id_estudiante,
                        ':id_materia' => $id_materia,
                        ':id_seccion' => $id_seccion,
                        ':fecha' => $fecha_actual
                    ]);
                    $movidos_a_pendiente[] = $id_estudiante;
                }
            }
        } // fin revision

        if ($tipo === 'pendiente') {
            if ($nota >= 10) {
                // Aprueba Materia Pendiente: actualizar nota y eliminar de pendientes
                $stmt_upsert_nota->execute([
                    ':id_estudiante' => $id_estudiante,
                    ':id_materia' => $id_materia,
                    ':id_lapso' => $id_lapso,
                    ':nota' => $nota,
                    ':observaciones' => ($obs_text !== '' ? $obs_text : 'Aprobado en materia pendiente')
                ]);
                // eliminar de pendientes
                $pdo->prepare("DELETE FROM materias_pendientes WHERE id_estudiante = ? AND id_materia = ?")
                    ->execute([$id_estudiante, $id_materia]);
                $aprobados[] = $id_estudiante;
            } else {
                // Reprobó un intento de materia pendiente
                $reprobados[] = $id_estudiante;
                if ($intento >= 4) {
                    // 4º intento reprobado -> aplazado / repite año
                    $stmt_update_pend_aplazado->execute([
                        ':id_estudiante' => $id_estudiante,
                        ':id_materia' => $id_materia
                    ]);
                    $aplazados[] = $id_estudiante;
                }
            }
        } // fin pendiente

        $procesados++;
        $guardados_ids[] = $id_estudiante;
    }

    $pdo->commit();

    // Preparar mensajes contados y listados con nombres
    $get_names = function($ids) use ($stmt_nombre) {
        $names = [];
        foreach ($ids as $id) {
            $stmt_nombre->execute([':id_estudiante' => $id]);
            $r = $stmt_nombre->fetch(PDO::FETCH_ASSOC);
            $names[] = $r['nombre'] ?? ('Estudiante #' . $id);
        }
        return $names;
    };

    $aprobados_nombres = $get_names($aprobados);
    $reprobados_nombres = $get_names(array_diff($reprobados, $movidos_a_pendiente, $aplazados)); // reprobados que no pasaron a otra categoría
    $movidos_nombres = $get_names($movidos_a_pendiente);
    $aplazados_nombres = $get_names($aplazados);

    // Construir mensaje principal
    $summary = [
        'aprobados' => count($aprobados),
        'reprobados' => count($reprobados),
        'movido_a_pendiente_count' => count($movidos_a_pendiente),
        'aplazados_count' => count($aplazados)
    ];

    $message_parts = [];
    $message_parts[] = "✅ Se guardaron $procesados registros correctamente.";
    $message_parts[] = (count($aprobados) > 0) ? (count($aprobados) . " aprobados") : "";
    $message_parts[] = (count($reprobados) > 0) ? (count($reprobados) . " reprobados") : "";
    if (count($movidos_a_pendiente) > 0) $message_parts[] = (count($movidos_a_pendiente) . " movidos a Materias Pendientes");
    if (count($aplazados) > 0) $message_parts[] = (count($aplazados) . " aplazados (repite año)");

    $message_combined = implode(' · ', array_filter($message_parts));

    // Mensajes adicionales por categoría
    $extra_msgs = [];
    if (!empty($movidos_nombres)) {
        foreach ($movidos_nombres as $nm) {
            $extra_msgs[] = "Estudiante {$nm}: Reprobó revisión y fue movido a Materia Pendiente.";
        }
    }
    if (!empty($aplazados_nombres)) {
        foreach ($aplazados_nombres as $nm) {
            $extra_msgs[] = "Estudiante {$nm}: Reprobó los 4 intentos. Repite el año escolar ({$gestion_activa_nombre}).";
        }
    }

    echo json_encode([
        'status' => 'success',
        'message' => $message_combined . (count($extra_msgs) ? '<br><br>' . implode('<br>', $extra_msgs) : ''),
        'summary' => $summary,
        'movidos' => $movidos_nombres,
        'aplazados' => $aplazados_nombres,
        'aprobados' => $aprobados_nombres,
        'reprobados' => $reprobados_nombres,
        'gestion_activa' => $gestion_activa_nombre,
        'reload' => true
    ]);
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    echo json_encode([
        'status' => 'error',
        'message' => '❌ Error: ' . $e->getMessage()
    ]);
}
?>