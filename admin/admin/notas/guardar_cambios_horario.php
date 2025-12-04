<?php
/**
 * Guardar cambios en el horario consolidado
 * Usa la misma lógica robusta de validación de conflictos que api_check_profesor.php
 */
include('../../app/config.php');
if (session_status() === PHP_SESSION_NONE) session_start();

header('Content-Type: application/json');

try {
    $id_horario = isset($_POST['id_horario']) ? (int)$_POST['id_horario'] : 0;
    $cambios = isset($_POST['cambios']) ? json_decode($_POST['cambios'], true) : [];
    
    if ($id_horario <= 0 || empty($cambios)) {
        echo json_encode([
            'success' => false,
            'message' => 'Datos inválidos'
        ]);
        exit;
    }
    
    // Detectar PK
    $cols = $pdo->query("SHOW COLUMNS FROM horario_detalle")->fetchAll(PDO::FETCH_COLUMN);
    $pk = null;
    foreach (['id_detalle','id_horario_detalle','id'] as $c) { 
        if (in_array($c, $cols, true)) { 
            $pk = $c; 
            break; 
        } 
    }
    
    if (!$pk) {
        echo json_encode([
            'success' => false,
            'message' => 'No se pudo detectar la clave primaria'
        ]);
        exit;
    }
    
    // Obtener gestión activa
    $gestion = $pdo->query("SELECT id_gestion FROM gestiones WHERE estado = 1 ORDER BY desde DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    if (!$gestion) {
        echo json_encode([
            'success' => false,
            'message' => 'No hay gestión activa'
        ]);
        exit;
    }
    
    // Mapeo de hora_inicio a hora_fin estándar (para bloques con hora_fin inválida)
    $mapa_horas_fin = [
        '07:50:00' => '08:30:00',
        '08:30:00' => '09:10:00',
        '09:10:00' => '09:50:00',
        '10:10:00' => '10:50:00',
        '10:50:00' => '11:30:00',
        '11:30:00' => '12:10:00'
    ];
    
    // Función auxiliar para verificar solapamiento de dos intervalos de tiempo
    $intervalosSeSolapan = function($inicio1, $fin1, $inicio2, $fin2) {
        // Normalizar formatos (asegurar HH:MM:SS)
        if (strlen($inicio1) == 5) $inicio1 .= ':00';
        if (strlen($fin1) == 5) $fin1 .= ':00';
        if (strlen($inicio2) == 5) $inicio2 .= ':00';
        if (strlen($fin2) == 5) $fin2 .= ':00';
        
        // Convertir a segundos desde medianoche para comparación precisa
        list($h1, $m1, $s1) = explode(':', $inicio1);
        list($hf1, $mf1, $sf1) = explode(':', $fin1);
        list($h2, $m2, $s2) = explode(':', $inicio2);
        list($hf2, $mf2, $sf2) = explode(':', $fin2);
        
        $seg1_inicio = ($h1 * 3600) + ($m1 * 60) + $s1;
        $seg1_fin = ($hf1 * 3600) + ($mf1 * 60) + $sf1;
        $seg2_inicio = ($h2 * 3600) + ($m2 * 60) + $s2;
        $seg2_fin = ($hf2 * 3600) + ($mf2 * 60) + $sf2;
        
        // Verificar solapamiento: inicio1 < fin2 AND fin1 > inicio2
        return ($seg1_inicio < $seg2_fin && $seg1_fin > $seg2_inicio);
    };
    
    // Función para validar conflictos de profesor
    $validarConflictoProfesor = function($id_profesor, $dia_semana, $hora_inicio, $hora_fin, $id_bloque_excluir = 0) use ($pdo, $gestion, $mapa_horas_fin, $intervalosSeSolapan, $pk) {
        if ($id_profesor <= 0 || empty($dia_semana) || empty($hora_inicio) || empty($hora_fin)) {
            return null;
        }
        
        // Normalizar
        $dia_normalizado = ucfirst(strtolower(trim($dia_semana)));
        $hora_inicio_normalizada = strlen($hora_inicio) == 5 ? $hora_inicio . ':00' : $hora_inicio;
        $hora_fin_normalizada = strlen($hora_fin) == 5 ? $hora_fin . ':00' : $hora_fin;
        
        // Obtener todos los bloques del profesor en ese día
        $sqlBloques = "SELECT hd.$pk as id_detalle, hd.id_horario, hd.hora_inicio, hd.hora_fin, 
                              h.id_grado, h.id_seccion
                       FROM horario_detalle hd
                       INNER JOIN horarios h ON h.id_horario = hd.id_horario
                       WHERE h.id_gestion = :gestion 
                         AND hd.id_profesor = :p 
                         AND hd.id_profesor IS NOT NULL
                         AND hd.dia_semana = :d
                         AND hd.hora_inicio IS NOT NULL
                         AND hd.hora_inicio != '00:00:00'";
        
        if ($id_bloque_excluir > 0) {
            $sqlBloques .= " AND hd.$pk != :id_bloque_excluir";
        }
        
        $stmtBloques = $pdo->prepare($sqlBloques);
        $paramsBloques = [
            ':gestion' => $gestion['id_gestion'],
            ':p' => $id_profesor,
            ':d' => $dia_normalizado
        ];
        if ($id_bloque_excluir > 0) {
            $paramsBloques[':id_bloque_excluir'] = $id_bloque_excluir;
        }
        $stmtBloques->execute($paramsBloques);
        $bloques = $stmtBloques->fetchAll(PDO::FETCH_ASSOC);
        
        // Buscar conflicto
        foreach ($bloques as $bloque) {
            $hora_inicio_existente = $bloque['hora_inicio'];
            $hora_fin_existente = $bloque['hora_fin'];
            
            // Si hora_fin es inválida, inferirla
            if (empty($hora_fin_existente) || $hora_fin_existente == '00:00:00' || $hora_fin_existente == '00:00') {
                $hora_inicio_key = substr($hora_inicio_existente, 0, 5) . ':00';
                $hora_fin_existente = $mapa_horas_fin[$hora_inicio_key] ?? $hora_fin_existente;
            }
            
            // Normalizar formato
            if (strlen($hora_fin_existente) == 5) $hora_fin_existente .= ':00';
            if (strlen($hora_inicio_existente) == 5) $hora_inicio_existente .= ':00';
            
            // Verificar solapamiento
            if ($intervalosSeSolapan(
                $hora_inicio_existente,
                $hora_fin_existente,
                $hora_inicio_normalizada,
                $hora_fin_normalizada
            )) {
                // Obtener detalles del conflicto
                $sql = "SELECT hd.id_detalle, hd.id_horario, hd.dia_semana, hd.hora_inicio, hd.hora_fin,
                               hd.id_materia, hd.id_profesor,
                               m.nombre_materia,
                               s.nombre_seccion,
                               g.grado,
                               h.id_grado,
                               h.id_seccion,
                               CONCAT(p.nombres,' ',p.apellidos) AS nombre_profesor
                        FROM horario_detalle hd
                        INNER JOIN horarios h ON h.id_horario = hd.id_horario
                        INNER JOIN materias m ON m.id_materia = hd.id_materia
                        INNER JOIN secciones s ON s.id_seccion = h.id_seccion
                        INNER JOIN grados g ON g.id_grado = h.id_grado
                        LEFT JOIN profesores p ON p.id_profesor = hd.id_profesor
                        WHERE hd.id_detalle = :id_detalle
                        LIMIT 1";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':id_detalle' => $bloque['id_detalle']]);
                return $stmt->fetch(PDO::FETCH_ASSOC);
            }
        }
        
        return null;
    };
    
    $pdo->beginTransaction();
    
    // Obtener información del horario actual para referencia
    $stmtHorario = $pdo->prepare("SELECT id_gestion, id_grado, id_seccion FROM horarios WHERE id_horario = ?");
    $stmtHorario->execute([$id_horario]);
    $horarioActual = $stmtHorario->fetch(PDO::FETCH_ASSOC);
    
    foreach ($cambios as $cambio) {
        if ($cambio['tipo'] === 'eliminar') {
            // Eliminar bloque
            $id_bloque = (int)$cambio['id_bloque'];
            $del = $pdo->prepare("DELETE FROM horario_detalle WHERE $pk = ?");
            $del->execute([$id_bloque]);
            
        } elseif ($cambio['tipo'] === 'actualizar') {
            // Obtener datos actuales del bloque antes de actualizar
            $id_bloque = (int)$cambio['id_bloque'];
            $stmtBloque = $pdo->prepare("SELECT dia_semana, hora_inicio, hora_fin FROM horario_detalle WHERE $pk = ?");
            $stmtBloque->execute([$id_bloque]);
            $bloqueActual = $stmtBloque->fetch(PDO::FETCH_ASSOC);
            
            if (!$bloqueActual) {
                continue; // Bloque no existe, saltar
            }
            
            $id_materia = (int)$cambio['id_materia'];
            $id_profesor = isset($cambio['id_profesor']) && $cambio['id_profesor'] !== '' ? (int)$cambio['id_profesor'] : null;
            $dia_semana = $bloqueActual['dia_semana'];
            $hora_inicio = $bloqueActual['hora_inicio'];
            $hora_fin = $bloqueActual['hora_fin'];
            
            // Normalizar horas
            if (strlen($hora_inicio) == 5) $hora_inicio .= ':00';
            if (strlen($hora_fin) == 5) $hora_fin .= ':00';
            
            // Validar conflicto de profesor
            if ($id_profesor !== null && $id_profesor > 0) {
                $conflicto = $validarConflictoProfesor($id_profesor, $dia_semana, $hora_inicio, $hora_fin, $id_bloque);
                
                if ($conflicto) {
                    // Normalizar hora_fin para mostrar
                    if (empty($conflicto['hora_fin']) || $conflicto['hora_fin'] == '00:00:00' || $conflicto['hora_fin'] == '00:00') {
                        $hora_inicio_key = substr($conflicto['hora_inicio'], 0, 5) . ':00';
                        $conflicto['hora_fin'] = $mapa_horas_fin[$hora_inicio_key] ?? $conflicto['hora_fin'];
                    }
                    
                    $nombreProfesor = $conflicto['nombre_profesor'] ?? 'El profesor';
                    $horaInicioDisplay = substr($conflicto['hora_inicio'], 0, 5);
                    $horaFinDisplay = substr($conflicto['hora_fin'], 0, 5);
                    
                    $pdo->rollBack();
                    echo json_encode([
                        'success' => false,
                        'message' => 'El profesor ' . $nombreProfesor . ' ya está asignado en ' . $conflicto['grado'] . ' - Sección ' . $conflicto['nombre_seccion'] . ' (' . $conflicto['nombre_materia'] . ') el ' . $conflicto['dia_semana'] . ' de ' . $horaInicioDisplay . ' a ' . $horaFinDisplay . '. No puede tener dos clases simultáneas.'
                    ]);
                    exit;
                }
            }
            
            // Actualizar bloque
            $up = $pdo->prepare("UPDATE horario_detalle SET id_materia = :mat, id_profesor = :prof WHERE $pk = :id");
            $up->bindValue(':mat', $id_materia, PDO::PARAM_INT);
            if ($id_profesor === null) {
                $up->bindValue(':prof', null, PDO::PARAM_NULL);
            } else {
                $up->bindValue(':prof', $id_profesor, PDO::PARAM_INT);
            }
            $up->bindValue(':id', $id_bloque, PDO::PARAM_INT);
            $up->execute();
            
        } elseif ($cambio['tipo'] === 'agregar') {
            // Agregar nuevo bloque
            $dia_semana = $cambio['dia_semana'];
            $hora_inicio = $cambio['hora_inicio'];
            $hora_fin = $cambio['hora_fin'];
            $id_materia = (int)$cambio['id_materia'];
            $id_profesor = isset($cambio['id_profesor']) && $cambio['id_profesor'] !== '' ? (int)$cambio['id_profesor'] : null;
            
            // Normalizar horas
            if (strlen($hora_inicio) == 5) {
                $hora_inicio .= ':00';
            }
            if (strlen($hora_fin) == 5) {
                $hora_fin .= ':00';
            }
            
            // Validar conflicto de profesor
            if ($id_profesor !== null && $id_profesor > 0) {
                $conflicto = $validarConflictoProfesor($id_profesor, $dia_semana, $hora_inicio, $hora_fin);
                
                if ($conflicto) {
                    // Normalizar hora_fin para mostrar
                    if (empty($conflicto['hora_fin']) || $conflicto['hora_fin'] == '00:00:00' || $conflicto['hora_fin'] == '00:00') {
                        $hora_inicio_key = substr($conflicto['hora_inicio'], 0, 5) . ':00';
                        $conflicto['hora_fin'] = $mapa_horas_fin[$hora_inicio_key] ?? $conflicto['hora_fin'];
                    }
                    
                    $nombreProfesor = $conflicto['nombre_profesor'] ?? 'El profesor';
                    $horaInicioDisplay = substr($conflicto['hora_inicio'], 0, 5);
                    $horaFinDisplay = substr($conflicto['hora_fin'], 0, 5);
                    
                    $pdo->rollBack();
                    echo json_encode([
                        'success' => false,
                        'message' => 'El profesor ' . $nombreProfesor . ' ya está asignado en ' . $conflicto['grado'] . ' - Sección ' . $conflicto['nombre_seccion'] . ' (' . $conflicto['nombre_materia'] . ') el ' . $conflicto['dia_semana'] . ' de ' . $horaInicioDisplay . ' a ' . $horaFinDisplay . '. No puede tener dos clases simultáneas.'
                    ]);
                    exit;
                }
            }
            
            // Insertar nuevo bloque
            $ins = $pdo->prepare("INSERT INTO horario_detalle 
                                  (id_horario, dia_semana, hora_inicio, hora_fin, id_materia, id_profesor)
                                  VALUES (?, ?, ?, ?, ?, ?)");
            $ins->execute([
                $id_horario,
                $dia_semana,
                $hora_inicio,
                $hora_fin,
                $id_materia,
                $id_profesor
            ]);
        }
    }
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Cambios guardados correctamente'
    ]);
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    error_log("Error en guardar_cambios_horario.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    echo json_encode([
        'success' => false,
        'message' => 'Error al guardar cambios: ' . $e->getMessage()
    ]);
}
?>
