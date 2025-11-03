<?php
session_start();

require_once('../../app/config.php');
require_once('verificar_docente.php');

// Verificar usuario (docente o admin)
$datos_docente = verificarDocente();
$id_profesor = $datos_docente['id_profesor'] ?? null;

// Nombre completo del usuario que realiza el cambio
$usuario_cambio = trim(($datos_docente['nombre_profesor'] ?? '') . ' ' . ($datos_docente['apellido_profesor'] ?? ''));
if ($usuario_cambio === '') {
    $usuario_cambio = $_SESSION['usuario'] ?? 'Desconocido';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_seccion = $_POST['id_seccion'] ?? null;
    $id_materia = $_POST['id_materia'] ?? null;
    $id_lapso = $_POST['id_lapso'] ?? null;
    $id_profesor_post = $_POST['id_profesor'] ?? null;
    $notas = $_POST['notas'] ?? [];
    $observaciones = $_POST['observaciones'] ?? [];

    if (!$id_seccion || !$id_materia || !$id_lapso) {
        $_SESSION['error'] = "❌ Datos incompletos para procesar las notas.";
        header("Location: carga_notas_seccion.php");
        exit();
    }

    try {
        $pdo->beginTransaction();

        // === Detectar si historial_notas tiene columna id_nota (para compatibilidad) ===
        $stmt_col = $pdo->prepare("
            SELECT COUNT(*) 
            FROM information_schema.columns 
            WHERE table_schema = DATABASE() 
              AND table_name = 'historial_notas' 
              AND column_name = 'id_nota'
        ");
        $stmt_col->execute();
        $has_id_nota = intval($stmt_col->fetchColumn()) > 0;

        // Preparar la consulta de historial según exista id_nota o no
        if ($has_id_nota) {
            $sql_hist = "INSERT INTO historial_notas (
                            id_nota, id_estudiante, id_materia, id_lapso,
                            calificacion_anterior, calificacion_nueva,
                            observaciones_anterior, observaciones_nueva,
                            tipo_cambio, usuario_cambio, fecha_cambio, estado
                          ) VALUES (
                            :id_nota, :id_estudiante, :id_materia, :id_lapso,
                            :calificacion_anterior, :calificacion_nueva,
                            :obs_anterior, :obs_nueva,
                            :tipo_cambio, :usuario_cambio, NOW(), 1
                          )";
        } else {
            $sql_hist = "INSERT INTO historial_notas (
                            id_estudiante, id_materia, id_lapso,
                            calificacion_anterior, calificacion_nueva,
                            observaciones_anterior, observaciones_nueva,
                            tipo_cambio, usuario_cambio, fecha_cambio, estado
                          ) VALUES (
                            :id_estudiante, :id_materia, :id_lapso,
                            :calificacion_anterior, :calificacion_nueva,
                            :obs_anterior, :obs_nueva,
                            :tipo_cambio, :usuario_cambio, NOW(), 1
                          )";
        }
        $stmt_hist = $pdo->prepare($sql_hist);

        // Preparar consultas de uso frecuente (opcional: mejora rendimiento)
        $sql_check = "SELECT id_nota, calificacion, observaciones 
                      FROM notas_estudiantes 
                      WHERE id_estudiante = :id_estudiante 
                        AND id_materia = :id_materia 
                        AND id_lapso = :id_lapso
                      LIMIT 1";
        $stmt_check = $pdo->prepare($sql_check);

        $sql_update = "UPDATE notas_estudiantes 
                       SET calificacion = :calificacion, observaciones = :observaciones
                       WHERE id_nota = :id_nota";
        $stmt_update = $pdo->prepare($sql_update);

        $sql_insert = "INSERT INTO notas_estudiantes (
                          id_estudiante, id_materia, id_lapso, calificacion, observaciones, fecha_registro
                       ) VALUES (
                          :id_estudiante, :id_materia, :id_lapso, :calificacion, :observaciones, NOW()
                       )";
        $stmt_insert = $pdo->prepare($sql_insert);

        $procesadas = 0;
        $actualizadas = 0;
        $nuevas = 0;
        $sin_cambios = 0;
        $errores_validacion = [];

        foreach ($notas as $id_estudiante => $calificacion_raw) {
            // Solo procesar si hay nota ingresada
            if ($calificacion_raw !== '') {
                $calificacion = floatval($calificacion_raw);
                $observacion_nueva = trim($observaciones[$id_estudiante] ?? '');

                // Validar rango
                if ($calificacion < 0 || $calificacion > 20) {
                    $errores_validacion[] = "Nota fuera de rango (0-20) para estudiante ID: $id_estudiante - Valor: $calificacion";
                    continue;
                }

                // Verificar si ya existe la nota
                $stmt_check->execute([
                    ':id_estudiante' => $id_estudiante,
                    ':id_materia' => $id_materia,
                    ':id_lapso' => $id_lapso
                ]);
                $nota_existente = $stmt_check->fetch(PDO::FETCH_ASSOC);

                if ($nota_existente) {
                    // NOTA EXISTENTE - Verificar si hay cambios reales
                    $observacion_anterior = $nota_existente['observaciones'] ?? '';
                    $nota_anterior = floatval($nota_existente['calificacion']);

                    $hay_cambio_nota = (abs($nota_anterior - $calificacion) > 0.001);
                    $hay_cambio_obs = ($observacion_anterior !== $observacion_nueva);

                    if ($hay_cambio_nota || $hay_cambio_obs) {
                        // 1) Insertar en historial (desde PHP) antes o después del UPDATE
                        if ($has_id_nota) {
                            $params_hist = [
                                ':id_nota' => $nota_existente['id_nota'],
                                ':id_estudiante' => $id_estudiante,
                                ':id_materia' => $id_materia,
                                ':id_lapso' => $id_lapso,
                                ':calificacion_anterior' => $nota_anterior,
                                ':calificacion_nueva' => $calificacion,
                                ':obs_anterior' => $observacion_anterior,
                                ':obs_nueva' => $observacion_nueva,
                                ':tipo_cambio' => 'ACTUALIZACION',
                                ':usuario_cambio' => $usuario_cambio
                            ];
                        } else {
                            $params_hist = [
                                ':id_estudiante' => $id_estudiante,
                                ':id_materia' => $id_materia,
                                ':id_lapso' => $id_lapso,
                                ':calificacion_anterior' => $nota_anterior,
                                ':calificacion_nueva' => $calificacion,
                                ':obs_anterior' => $observacion_anterior,
                                ':obs_nueva' => $observacion_nueva,
                                ':tipo_cambio' => 'ACTUALIZACION',
                                ':usuario_cambio' => $usuario_cambio
                            ];
                        }
                        $stmt_hist->execute($params_hist);

                        // 2) Actualizar nota principal
                        $stmt_update->execute([
                            ':calificacion' => $calificacion,
                            ':observaciones' => $observacion_nueva,
                            ':id_nota' => $nota_existente['id_nota']
                        ]);

                        $actualizadas++;
                        $procesadas++;
                    } else {
                        $sin_cambios++;
                    }
                } else {
                    // NUEVA NOTA: insertar nota y luego historial de CREACION
                    $stmt_insert->execute([
                        ':id_estudiante' => $id_estudiante,
                        ':id_materia' => $id_materia,
                        ':id_lapso' => $id_lapso,
                        ':calificacion' => $calificacion,
                        ':observaciones' => $observacion_nueva
                    ]);

                    $id_nota_nueva = $pdo->lastInsertId();

                    if ($has_id_nota) {
                        $params_hist = [
                            ':id_nota' => $id_nota_nueva,
                            ':id_estudiante' => $id_estudiante,
                            ':id_materia' => $id_materia,
                            ':id_lapso' => $id_lapso,
                            ':calificacion_anterior' => null,
                            ':calificacion_nueva' => $calificacion,
                            ':obs_anterior' => null,
                            ':obs_nueva' => $observacion_nueva,
                            ':tipo_cambio' => 'CREACION',
                            ':usuario_cambio' => $usuario_cambio
                        ];
                    } else {
                        $params_hist = [
                            ':id_estudiante' => $id_estudiante,
                            ':id_materia' => $id_materia,
                            ':id_lapso' => $id_lapso,
                            ':calificacion_anterior' => null,
                            ':calificacion_nueva' => $calificacion,
                            ':obs_anterior' => null,
                            ':obs_nueva' => $observacion_nueva,
                            ':tipo_cambio' => 'CREACION',
                            ':usuario_cambio' => $usuario_cambio
                        ];
                    }
                    $stmt_hist->execute($params_hist);

                    $nuevas++;
                    $procesadas++;
                }
            }
        }

        $pdo->commit();

        // Mensaje de éxito
        if ($procesadas > 0) {
            $mensaje = "✅ <strong>Notas guardadas exitosamente!</strong><br><br>";
            $mensaje .= "📊 <strong>Resumen de la operación:</strong><br>";
            $mensaje .= "• Total procesadas: <strong>{$procesadas}</strong><br>";
            if ($nuevas > 0) $mensaje .= "• Nuevas notas: <strong>{$nuevas}</strong><br>";
            if ($actualizadas > 0) $mensaje .= "• Notas actualizadas: <strong>{$actualizadas}</strong><br>";
            if ($sin_cambios > 0) $mensaje .= "• Sin cambios: <strong>{$sin_cambios}</strong><br>";
        } else {
            $mensaje = "ℹ️ <strong>No se realizaron cambios</strong><br>";
            $mensaje .= "Todas las notas ya estaban actualizadas o no se ingresaron nuevas notas.";
        }

        if (count($errores_validacion) > 0) {
            $mensaje .= "<br>⚠️ Se omitieron " . count($errores_validacion) . " notas por errores de validación.";
        }

        $_SESSION['success_message'] = $mensaje;

    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Error al guardar notas: " . $e->getMessage());
        $_SESSION['error_message'] = "❌ Error al guardar las notas.<br>Detalle: " . $e->getMessage();
    }
} else {
    $_SESSION['error_message'] = "❌ Método de solicitud no válido.";
}

// Redirigir
$redirect_url = "carga_notas_seccion.php?seccion=$id_seccion&materia=$id_materia&lapso=$id_lapso";
header('Location: ' . $redirect_url);
exit();
?>
