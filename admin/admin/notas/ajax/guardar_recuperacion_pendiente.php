<?php
session_start();
require_once('../../../app/config.php');

header('Content-Type: application/json; charset=utf-8');

// Solo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
    exit;
}

// Datos del formulario
$id_seccion = $_POST['id_seccion'] ?? null;
$id_materia = $_POST['id_materia'] ?? null;
$id_gestion = $_POST['id_gestion'] ?? null;
$tipo = $_POST['tipo'] ?? 'pendiente';
$notas = $_POST['nota'] ?? [];
$intentos = $_POST['intento'] ?? [];
$observaciones = $_POST['observaciones'] ?? [];

// Validaciones básicas
if (!$id_seccion || !$id_materia) {
    echo json_encode(['status' => 'error', 'message' => 'Datos incompletos (sección o materia).']);
    exit;
}

if (empty($notas) || !is_array($notas)) {
    echo json_encode(['status' => 'warning', 'message' => 'No hay notas para guardar']);
    exit;
}

try {
    // Detectar si la tabla recuperaciones tiene la columna id_gestion
    $has_recuperaciones_id_gestion = false;
    try {
        $chk = $pdo->query("SHOW COLUMNS FROM recuperaciones LIKE 'id_gestion'");
        if ($chk && $chk->fetch(PDO::FETCH_ASSOC)) {
            $has_recuperaciones_id_gestion = true;
        }
    } catch (Exception $ex) {
        $has_recuperaciones_id_gestion = false;
    }

    // Detectar si la tabla materias_pendientes tiene la columna id_gestion
    $has_mp_id_gestion = false;
    try {
        $chk2 = $pdo->query("SHOW COLUMNS FROM materias_pendientes LIKE 'id_gestion'");
        if ($chk2 && $chk2->fetch(PDO::FETCH_ASSOC)) {
            $has_mp_id_gestion = true;
        }
    } catch (Exception $ex) {
        $has_mp_id_gestion = false;
    }

    $pdo->beginTransaction();

    $registros_guardados = 0;
    $estudiantes_aprobados = 0;
    $estudiantes_reprobados = 0;
    $estudiantes_aplazados = 0;
    $detalles_aplazados = [];
    $detalles_aprobados = [];

    // Obtener nombre de la gestión
    $gestion_nombre = '';
    if ($id_gestion) {
        try {
            $stmt_g = $pdo->prepare("SELECT CONCAT('Periodo ', DATE_FORMAT(desde, '%Y'), ' - ', DATE_FORMAT(hasta, '%Y')) AS nombre_gestion FROM gestiones WHERE id_gestion = :id_gestion");
            $stmt_g->execute([':id_gestion' => $id_gestion]);
            $gi = $stmt_g->fetch(PDO::FETCH_ASSOC);
            $gestion_nombre = $gi['nombre_gestion'] ?? '';
        } catch (Exception $e) {
            $gestion_nombre = '';
        }
    }

    // ========== IMPORTANTE: PREPARAR STATEMENTS ==========

    // 1. SQL para INSERT en recuperaciones (SIEMPRE guardar historial)
    $insert_fields = ['id_estudiante', 'id_materia', 'id_seccion'];
    $insert_placeholders = [':id_estudiante', ':id_materia', ':id_seccion'];
    if ($has_recuperaciones_id_gestion) {
        $insert_fields[] = 'id_gestion';
        $insert_placeholders[] = ':id_gestion';
    }
    $insert_fields = array_merge($insert_fields, ['tipo', 'intento', 'calificacion', 'observaciones']);
    $insert_placeholders = array_merge($insert_placeholders, [':tipo', ':intento', ':calificacion', ':observaciones']);

    $sql_insert = "INSERT INTO recuperaciones (" . implode(', ', $insert_fields) . ", fecha_registro) VALUES (" . implode(', ', $insert_placeholders) . ", NOW())";
    $stmt_insert = $pdo->prepare($sql_insert);

    // 2. SQL para ACTUALIZAR materias_pendientes (NO eliminar)
    if ($has_mp_id_gestion) {
        $sql_update_mp = "UPDATE materias_pendientes SET 
                         ultimo_intento = :ultimo_intento,
                         ultima_nota = :ultima_nota,
                         estado = :estado,
                         fecha_ultima_actualizacion = NOW()
                         WHERE id_estudiante = :id_estudiante 
                           AND id_materia = :id_materia 
                           AND id_seccion = :id_seccion
                           AND id_gestion = :id_gestion";
    } else {
        $sql_update_mp = "UPDATE materias_pendientes SET 
                         ultimo_intento = :ultimo_intento,
                         ultima_nota = :ultima_nota,
                         estado = :estado,
                         fecha_ultima_actualizacion = NOW()
                         WHERE id_estudiante = :id_estudiante 
                           AND id_materia = :id_materia 
                           AND id_seccion = :id_seccion";
    }
    $stmt_update_mp = $pdo->prepare($sql_update_mp);

    // 3. SQL para verificar si existe en materias_pendientes
    if ($has_mp_id_gestion) {
        $sql_check_mp = "SELECT id_pendiente FROM materias_pendientes 
                        WHERE id_estudiante = :id_estudiante 
                          AND id_materia = :id_materia 
                          AND id_seccion = :id_seccion
                          AND id_gestion = :id_gestion";
    } else {
        $sql_check_mp = "SELECT id_pendiente FROM materias_pendientes 
                        WHERE id_estudiante = :id_estudiante 
                          AND id_materia = :id_materia 
                          AND id_seccion = :id_seccion";
    }
    $stmt_check_mp = $pdo->prepare($sql_check_mp);

    // 4. SQL para INSERT en materias_pendientes si no existe
    if ($has_mp_id_gestion) {
        $sql_insert_mp = "INSERT INTO materias_pendientes 
                         (id_estudiante, id_materia, id_seccion, id_gestion, 
                          ultimo_intento, ultima_nota, estado, fecha_registro) 
                         VALUES (:id_estudiante, :id_materia, :id_seccion, :id_gestion,
                                 :ultimo_intento, :ultima_nota, :estado, NOW())";
    } else {
        $sql_insert_mp = "INSERT INTO materias_pendientes 
                         (id_estudiante, id_materia, id_seccion, 
                          ultimo_intento, ultima_nota, estado, fecha_registro) 
                         VALUES (:id_estudiante, :id_materia, :id_seccion,
                                 :ultimo_intento, :ultima_nota, :estado, NOW())";
    }
    $stmt_insert_mp = $pdo->prepare($sql_insert_mp);

    // 5. SQL para obtener nombre del estudiante
    $stmt_est = $pdo->prepare("SELECT nombres, apellidos FROM estudiantes WHERE id_estudiante = :id_estudiante");

    // ========== PROCESAR CADA ESTUDIANTE ==========
    foreach ($notas as $id_estudiante => $nota_raw) {
        // Validar nota
        if ($nota_raw === '' || $nota_raw === null) continue;
        $nota_float = (float) $nota_raw;
        if (!is_numeric($nota_float)) continue;

        $intento = isset($intentos[$id_estudiante]) ? (int)$intentos[$id_estudiante] : 1;
        if ($intento < 1) $intento = 1;
        if ($intento > 999) $intento = 999;

        $observacion = isset($observaciones[$id_estudiante]) ? trim($observaciones[$id_estudiante]) : '';

        // Determinar si aprobó
        $nota_redondeada = round($nota_float, 0, PHP_ROUND_HALF_UP);
        $aprobado = ($nota_redondeada >= 10);

        // ========== 1. SIEMPRE guardar en recuperaciones ==========
        $bind_insert = [
            ':id_estudiante' => $id_estudiante,
            ':id_materia' => $id_materia,
            ':id_seccion' => $id_seccion,
        ];
        if ($has_recuperaciones_id_gestion) {
            $bind_insert[':id_gestion'] = $id_gestion;
        }
        $bind_insert[':tipo'] = $tipo;
        $bind_insert[':intento'] = $intento;
        $bind_insert[':calificacion'] = $nota_float;
        $bind_insert[':observaciones'] = $observacion;

        $stmt_insert->execute($bind_insert);
        $registros_guardados++;

        // ========== 2. Determinar estado para materias_pendientes ==========
        if ($aprobado) {
            $estado_mp = 'aprobado';
            $estudiantes_aprobados++;
            
            // Guardar detalles de aprobados
            $stmt_est->execute([':id_estudiante' => $id_estudiante]);
            $est_info = $stmt_est->fetch(PDO::FETCH_ASSOC);
            if ($est_info) {
                $detalles_aprobados[] = trim($est_info['nombres'] . ' ' . $est_info['apellidos']) . " - Nota: {$nota_redondeada}/20 (M{$intento})";
            }
        } else {
            if ($intento == 4) {
                $estado_mp = 'aplazado';
                $estudiantes_aplazados++;
                
                // Guardar detalles de aplazados
                $stmt_est->execute([':id_estudiante' => $id_estudiante]);
                $est_info = $stmt_est->fetch(PDO::FETCH_ASSOC);
                if ($est_info) {
                    $detalles_aplazados[] = trim($est_info['nombres'] . ' ' . $est_info['apellidos']) . " - Nota: {$nota_redondeada}/20";
                }
            } else {
                $estado_mp = 'pendiente';
                $estudiantes_reprobados++;
            }
        }

        // ========== 3. Verificar si ya existe en materias_pendientes ==========
        $bind_check = [
            ':id_estudiante' => $id_estudiante,
            ':id_materia' => $id_materia,
            ':id_seccion' => $id_seccion
        ];
        if ($has_mp_id_gestion) {
            $bind_check[':id_gestion'] = $id_gestion;
        }
        
        $stmt_check_mp->execute($bind_check);
        $existe_mp = $stmt_check_mp->fetch(PDO::FETCH_ASSOC);

        // ========== 4. Actualizar o insertar en materias_pendientes ==========
        if ($existe_mp) {
            // ACTUALIZAR registro existente
            $bind_update = [
                ':id_estudiante' => $id_estudiante,
                ':id_materia' => $id_materia,
                ':id_seccion' => $id_seccion,
                ':ultimo_intento' => $intento,
                ':ultima_nota' => $nota_float,
                ':estado' => $estado_mp
            ];
            if ($has_mp_id_gestion) {
                $bind_update[':id_gestion'] = $id_gestion;
            }
            
            $stmt_update_mp->execute($bind_update);
        } else {
            // INSERTAR nuevo registro
            $bind_insert_mp = [
                ':id_estudiante' => $id_estudiante,
                ':id_materia' => $id_materia,
                ':id_seccion' => $id_seccion,
                ':ultimo_intento' => $intento,
                ':ultima_nota' => $nota_float,
                ':estado' => $estado_mp
            ];
            if ($has_mp_id_gestion) {
                $bind_insert_mp[':id_gestion'] = $id_gestion;
            }
            
            $stmt_insert_mp->execute($bind_insert_mp);
        }
    }

    $pdo->commit();

    // ========== PREPARAR RESPUESTA ==========
    $response = [
        'status' => 'success',
        'message' => 'Registros guardados correctamente',
        'reload' => true,
        'summary' => [
            'total_registros' => $registros_guardados,
            'aprobados' => $estudiantes_aprobados,
            'reprobados' => $estudiantes_reprobados,
            'aplazados' => $estudiantes_aplazados
        ],
        'gestion_activa' => $gestion_nombre
    ];
    
    if (!empty($detalles_aprobados)) {
        $response['detalles_aprobados'] = $detalles_aprobados;
    }
    
    if (!empty($detalles_aplazados)) {
        $response['detalles_aplazados'] = $detalles_aplazados;
    }

    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;

} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'Error al guardar en la base de datos: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
    exit;
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'Error inesperado: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
    exit;
}