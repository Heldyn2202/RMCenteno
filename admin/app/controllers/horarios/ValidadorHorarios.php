<?php
/**
 * Clase ValidadorHorarios
 * Implementa todas las validaciones para la creación y modificación de horarios escolares
 */
class ValidadorHorarios {
    private $pdo;
    private $errores;
    private $gestion_activa;
    
    // Configuración de horarios permitidos (7:00 AM - 7:00 PM)
    const HORA_MINIMA = '07:00:00';
    const HORA_MAXIMA = '19:00:00';
    
    // Carga horaria mínima y máxima por defecto (horas semanales)
    const CARGA_MINIMA_DEFAULT = 1;
    const CARGA_MAXIMA_DEFAULT = 8;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->errores = [];
        
        // Obtener gestión activa
        $stmt = $this->pdo->query("SELECT * FROM gestiones WHERE estado = 1 ORDER BY desde DESC LIMIT 1");
        $this->gestion_activa = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Validar horario completo antes de guardar
     */
    public function validarHorarioCompleto($id_gestion, $id_grado, $id_seccion, $horario_data, $aula = null, $id_horario_excluir = null) {
        $this->errores = [];
        
        // 1. VALIDACIONES DE ESTRUCTURA ACADÉMICA
        $this->validarExistenciaMaterias($horario_data, $id_grado);
        $this->validarCoherenciaSeccionGrado($id_seccion, $id_grado, $id_gestion);
        
        // 2. VALIDACIONES DE ASIGNACIÓN DE RECURSOS
        $this->validarConflictosDocente($horario_data, $id_gestion, $id_horario_excluir);
        $this->validarConflictosAula($horario_data, $id_gestion, $aula, $id_horario_excluir);
        $this->validarDisponibilidadDocente($horario_data);
        $this->validarCapacidadAula($id_seccion, $aula);
        
        // 3. VALIDACIONES DE FLUJO Y TIEMPO
        $this->validarRangoHorario($horario_data);
        $this->validarIntervalosClase($horario_data);
        $this->validarCargaHorariaMaterias($horario_data, $id_grado);
        $this->validarHorarioCompletoPorMaterias($horario_data, $id_grado);
        
        return empty($this->errores);
    }
    
    /**
     * 1. Validar que las materias existan y pertenezcan al grado
     */
    private function validarExistenciaMaterias($horario_data, $id_grado) {
        if (empty($horario_data)) return;
        
        // Obtener materias válidas para el grado
        $stmt = $this->pdo->prepare("
            SELECT DISTINCT m.id_materia, m.nombre_materia 
            FROM materias m 
            LEFT JOIN grados_materias gm ON m.id_materia = gm.id_materia 
            WHERE m.estado = 1 
            AND (m.id_grado = :id_grado OR gm.id_grado = :id_grado)
        ");
        $stmt->execute([':id_grado' => $id_grado]);
        $materias_validas = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        foreach ($horario_data as $dia => $bloques) {
            foreach ($bloques as $hora_inicio => $bloque) {
                $id_materia = $bloque['materia'] ?? null;
                if ($id_materia && !in_array($id_materia, $materias_validas)) {
                    // Verificar si la materia existe
                    $stmt_mat = $this->pdo->prepare("SELECT nombre_materia FROM materias WHERE id_materia = ? AND estado = 1");
                    $stmt_mat->execute([$id_materia]);
                    $materia = $stmt_mat->fetch(PDO::FETCH_ASSOC);
                    
                    if ($materia) {
                        $this->agregarError("La materia '{$materia['nombre_materia']}' no corresponde al grado seleccionado ($dia, $hora_inicio)");
                    } else {
                        $this->agregarError("La materia seleccionada no existe en el sistema ($dia, $hora_inicio)");
                    }
                }
            }
        }
    }
    
    /**
     * 2. Validar coherencia Sección/Grado/Gestión
     */
    private function validarCoherenciaSeccionGrado($id_seccion, $id_grado, $id_gestion) {
        $stmt = $this->pdo->prepare("
            SELECT s.id_seccion, s.id_grado, s.id_gestion, g.grado 
            FROM secciones s
            JOIN grados g ON s.id_grado = g.id_grado
            WHERE s.id_seccion = :id_seccion
        ");
        $stmt->execute([':id_seccion' => $id_seccion]);
        $seccion = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$seccion) {
            $this->agregarError("La sección seleccionada no existe");
            return;
        }
        
        if ($seccion['id_grado'] != $id_grado) {
            $this->agregarError("La sección no corresponde al grado seleccionado. La sección pertenece a '{$seccion['grado']}'");
        }
        
        if ($seccion['id_gestion'] != $id_gestion) {
            $this->agregarError("La sección no corresponde al período académico activo");
        }
    }
    
    /**
     * 3. Validar conflictos de docente (no puede estar en dos materias al mismo tiempo)
     */
    private function validarConflictosDocente($horario_data, $id_gestion, $id_horario_excluir = null) {
        if (empty($horario_data)) return;
        
        // Agrupar por docente, día y hora
        $bloques_por_docente = [];
        
        foreach ($horario_data as $dia => $bloques) {
            foreach ($bloques as $hora_inicio => $bloque) {
                $id_profesor = !empty($bloque['profesor']) && $bloque['profesor'] != '' ? (int)$bloque['profesor'] : null;
                $hora_fin = $bloque['hora_fin'] ?? '';
                $id_materia = !empty($bloque['materia']) && $bloque['materia'] != '' ? (int)$bloque['materia'] : null;
                
                // Solo validar si hay materia asignada y profesor asignado (no validar si profesor está vacío)
                if ($id_materia && $id_materia > 0 && $id_profesor && $id_profesor > 0) {
                    $key = "$id_profesor|$dia|$hora_inicio|$hora_fin";
                    
                    // Verificar conflictos dentro del mismo horario
                    if (isset($bloques_por_docente[$key])) {
                        $bloques_por_docente[$key]['materias'][] = $id_materia;
                    } else {
                        $bloques_por_docente[$key] = [
                            'profesor' => $id_profesor,
                            'dia' => $dia,
                            'hora_inicio' => $hora_inicio,
                            'hora_fin' => $hora_fin,
                            'materias' => [$id_materia]
                        ];
                    }
                    
                    // Verificar conflictos con horarios existentes
                    $stmt = $this->pdo->prepare("
                        SELECT hd.id_horario, hd.dia_semana, hd.hora_inicio, hd.hora_fin,
                               m.nombre_materia, CONCAT(p.nombres, ' ', p.apellidos) as profesor_nombre
                        FROM horario_detalle hd
                        INNER JOIN horarios h ON h.id_horario = hd.id_horario
                        INNER JOIN materias m ON m.id_materia = hd.id_materia
                        INNER JOIN profesores p ON p.id_profesor = hd.id_profesor
                        WHERE h.id_gestion = :id_gestion
                        AND hd.id_profesor = :id_profesor
                        AND hd.dia_semana = :dia
                        AND (
                            (hd.hora_inicio < :hora_fin AND hd.hora_fin > :hora_inicio)
                        )
                        " . ($id_horario_excluir ? "AND h.id_horario != :excluir" : "") . "
                    ");
                    
                    $params = [
                        ':id_gestion' => $id_gestion,
                        ':id_profesor' => $id_profesor,
                        ':dia' => $dia,
                        ':hora_fin' => $hora_fin . ':00',
                        ':hora_inicio' => $hora_inicio . ':00'
                    ];
                    
                    if ($id_horario_excluir) {
                        $params[':excluir'] = $id_horario_excluir;
                    }
                    
                    $stmt->execute($params);
                    $conflicto = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($conflicto) {
                        $this->agregarError(
                            "Conflicto de docente: El profesor ya está asignado a '{$conflicto['nombre_materia']}' " .
                            "el $dia de {$conflicto['hora_inicio']} a {$conflicto['hora_fin']} " .
                            "(intentando asignar: $hora_inicio - $hora_fin)"
                        );
                    }
                }
            }
        }
        
        // Verificar múltiples materias en el mismo bloque (dentro del horario actual)
        foreach ($bloques_por_docente as $key => $bloque) {
            if (count($bloque['materias']) > 1) {
                $this->agregarError(
                    "Conflicto interno: El mismo docente está asignado a múltiples materias " .
                    "el {$bloque['dia']} de {$bloque['hora_inicio']} a {$bloque['hora_fin']}"
                );
            }
        }
    }
    
    /**
     * 4. Validar conflictos de aula
     */
    private function validarConflictosAula($horario_data, $id_gestion, $aula, $id_horario_excluir = null) {
        if (empty($aula) || empty($horario_data)) return;
        
        foreach ($horario_data as $dia => $bloques) {
            foreach ($bloques as $hora_inicio => $bloque) {
                $hora_fin = $bloque['hora_fin'] ?? '';
                $id_materia = $bloque['materia'] ?? null;
                
                if ($id_materia) {
                    // Verificar si el aula está ocupada en ese horario
                    $stmt = $this->pdo->prepare("
                        SELECT h.id_horario, h.aula, hd.dia_semana, hd.hora_inicio, hd.hora_fin,
                               m.nombre_materia, s.nombre_seccion
                        FROM horarios h
                        INNER JOIN horario_detalle hd ON h.id_horario = hd.id_horario
                        INNER JOIN materias m ON m.id_materia = hd.id_materia
                        INNER JOIN secciones s ON s.id_seccion = h.id_seccion
                        WHERE h.id_gestion = :id_gestion
                        AND h.aula = :aula
                        AND hd.dia_semana = :dia
                        AND (
                            (hd.hora_inicio < :hora_fin AND hd.hora_fin > :hora_inicio)
                        )
                        " . ($id_horario_excluir ? "AND h.id_horario != :excluir" : "") . "
                    ");
                    
                    $params = [
                        ':id_gestion' => $id_gestion,
                        ':aula' => $aula,
                        ':dia' => $dia,
                        ':hora_fin' => $hora_fin . ':00',
                        ':hora_inicio' => $hora_inicio . ':00'
                    ];
                    
                    if ($id_horario_excluir) {
                        $params[':excluir'] = $id_horario_excluir;
                    }
                    
                    $stmt->execute($params);
                    $conflicto = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($conflicto) {
                        $this->agregarError(
                            "Conflicto de aula: El aula '$aula' ya está ocupada " .
                            "el $dia de {$conflicto['hora_inicio']} a {$conflicto['hora_fin']} " .
                            "por la materia '{$conflicto['nombre_materia']}' (Sección {$conflicto['nombre_seccion']}) " .
                            "(intentando asignar: $hora_inicio - $hora_fin)"
                        );
                    }
                }
            }
        }
    }
    
    /**
     * 5. Validar disponibilidad del docente (verificar restricciones adicionales si existen)
     */
    private function validarDisponibilidadDocente($horario_data) {
        // Por ahora validamos que el profesor esté activo
        // En el futuro se puede agregar una tabla de disponibilidad_horaria_profesores
        if (empty($horario_data)) return;
        
        $profesores_ids = [];
        foreach ($horario_data as $bloques) {
            foreach ($bloques as $bloque) {
                // Solo validar si el profesor tiene un valor válido (no vacío, no 0)
                $id_profesor = !empty($bloque['profesor']) && $bloque['profesor'] != '' ? (int)$bloque['profesor'] : null;
                if ($id_profesor && $id_profesor > 0 && !in_array($id_profesor, $profesores_ids)) {
                    $profesores_ids[] = $id_profesor;
                }
            }
        }
        
        if (!empty($profesores_ids)) {
            $placeholders = implode(',', array_fill(0, count($profesores_ids), '?'));
            $stmt = $this->pdo->prepare("
                SELECT id_profesor, nombres, apellidos, estado 
                FROM profesores 
                WHERE id_profesor IN ($placeholders)
            ");
            $stmt->execute($profesores_ids);
            
            while ($profesor = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if ($profesor['estado'] != 1) {
                    $this->agregarError(
                        "El profesor '{$profesor['nombres']} {$profesor['apellidos']}' está inactivo"
                    );
                }
            }
        }
    }
    
    /**
     * 6. Validar capacidad del aula
     */
    private function validarCapacidadAula($id_seccion, $aula) {
        if (empty($aula)) return;
        
        // Obtener capacidad de la sección
        $stmt = $this->pdo->prepare("
            SELECT capacidad, cupo_actual, nombre_seccion 
            FROM secciones 
            WHERE id_seccion = :id_seccion
        ");
        $stmt->execute([':id_seccion' => $id_seccion]);
        $seccion = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$seccion) {
            $this->agregarError("La sección seleccionada no existe");
            return;
        }
        
        if ($seccion['cupo_actual'] > $seccion['capacidad']) {
            $this->agregarError(
                "La sección '{$seccion['nombre_seccion']}' excede su capacidad: " .
                "{$seccion['cupo_actual']} estudiantes / {$seccion['capacidad']} capacidad máxima"
            );
        }
    }
    
    /**
     * 7. Validar rango horario (7:00 AM - 7:00 PM)
     */
    private function validarRangoHorario($horario_data) {
        if (empty($horario_data)) return;
        
        foreach ($horario_data as $dia => $bloques) {
            foreach ($bloques as $hora_inicio => $bloque) {
                $hora_fin = $bloque['hora_fin'] ?? '';
                $id_materia = $bloque['materia'] ?? null;
                
                if ($id_materia) {
                    $hora_ini_time = strtotime($hora_inicio . ':00');
                    $hora_fin_time = strtotime($hora_fin . ':00');
                    $min_time = strtotime(self::HORA_MINIMA);
                    $max_time = strtotime(self::HORA_MAXIMA);
                    
                    if ($hora_ini_time < $min_time || $hora_fin_time > $max_time) {
                        $this->agregarError(
                            "Horario fuera de rango permitido: $hora_inicio - $hora_fin " .
                            "(rango permitido: " . self::HORA_MINIMA . " - " . self::HORA_MAXIMA . ") " .
                            "($dia)"
                        );
                    }
                }
            }
        }
    }
    
    /**
     * 8. Validar intervalos de clase (sin solapamientos, respetar tiempos de descanso)
     */
    private function validarIntervalosClase($horario_data) {
        if (empty($horario_data)) return;
        
        foreach ($horario_data as $dia => $bloques) {
            $horarios_dia = [];
            
            // Recopilar todos los bloques del día
            foreach ($bloques as $hora_inicio => $bloque) {
                $hora_fin = $bloque['hora_fin'] ?? '';
                $id_materia = $bloque['materia'] ?? null;
                
                if ($id_materia && $hora_fin) {
                    $horarios_dia[] = [
                        'inicio' => strtotime($hora_inicio . ':00'),
                        'fin' => strtotime($hora_fin . ':00'),
                        'hora_inicio' => $hora_inicio,
                        'hora_fin' => $hora_fin
                    ];
                }
            }
            
            // Ordenar por hora de inicio
            usort($horarios_dia, function($a, $b) {
                return $a['inicio'] - $b['inicio'];
            });
            
            // Verificar solapamientos y tiempos de descanso (mínimo 10 minutos entre clases)
            for ($i = 0; $i < count($horarios_dia) - 1; $i++) {
                $actual = $horarios_dia[$i];
                $siguiente = $horarios_dia[$i + 1];
                
                // Verificar solapamiento
                if ($actual['fin'] > $siguiente['inicio']) {
                    $this->agregarError(
                        "Solapamiento de horarios el $dia: " .
                        "{$actual['hora_inicio']} - {$actual['hora_fin']} se solapa con " .
                        "{$siguiente['hora_inicio']} - {$siguiente['hora_fin']}"
                    );
                }
                
                // Verificar tiempo de descanso (opcional: al menos 10 minutos)
                $tiempo_entre = $siguiente['inicio'] - $actual['fin'];
                if ($tiempo_entre > 0 && $tiempo_entre < 600) { // 600 segundos = 10 minutos
                    // Esto es solo una advertencia, no un error crítico
                    // Se puede hacer más flexible según políticas del liceo
                }
            }
        }
    }
    
    /**
     * 9. Validar carga horaria semanal por materia (mínimo y máximo de horas)
     */
    private function validarCargaHorariaMaterias($horario_data, $id_grado) {
        if (empty($horario_data)) return;
        
        // Contar horas por materia
        $horas_por_materia = [];
        
        foreach ($horario_data as $dia => $bloques) {
            foreach ($bloques as $hora_inicio => $bloque) {
                $hora_fin = $bloque['hora_fin'] ?? '';
                $id_materia = $bloque['materia'] ?? null;
                
                if ($id_materia && $hora_fin) {
                    $duracion = (strtotime($hora_fin . ':00') - strtotime($hora_inicio . ':00')) / 3600;
                    
                    if (!isset($horas_por_materia[$id_materia])) {
                        $horas_por_materia[$id_materia] = 0;
                    }
                    $horas_por_materia[$id_materia] += $duracion;
                }
            }
        }
        
        // Validar carga horaria
        foreach ($horas_por_materia as $id_materia => $horas_totales) {
            $stmt = $this->pdo->prepare("SELECT nombre_materia FROM materias WHERE id_materia = ?");
            $stmt->execute([$id_materia]);
            $materia = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($horas_totales < self::CARGA_MINIMA_DEFAULT) {
                $this->agregarError(
                    "La materia '{$materia['nombre_materia']}' tiene menos del mínimo de horas semanales: " .
                    "$horas_totales horas (mínimo: " . self::CARGA_MINIMA_DEFAULT . " horas)"
                );
            }
            
            if ($horas_totales > self::CARGA_MAXIMA_DEFAULT) {
                $this->agregarError(
                    "La materia '{$materia['nombre_materia']}' excede el máximo de horas semanales: " .
                    "$horas_totales horas (máximo: " . self::CARGA_MAXIMA_DEFAULT . " horas)"
                );
            }
        }
    }
    
    /**
     * 10. Validar que todas las materias obligatorias del grado estén asignadas
     */
    private function validarHorarioCompletoPorMaterias($horario_data, $id_grado) {
        // Obtener materias obligatorias del grado
        $stmt = $this->pdo->prepare("
            SELECT DISTINCT m.id_materia, m.nombre_materia 
            FROM materias m
            LEFT JOIN grados_materias gm ON m.id_materia = gm.id_materia
            WHERE m.estado = 1 
            AND (m.id_grado = :id_grado OR gm.id_grado = :id_grado)
        ");
        $stmt->execute([':id_grado' => $id_grado]);
        $materias_obligatorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Obtener materias asignadas en el horario
        $materias_asignadas = [];
        foreach ($horario_data as $bloques) {
            foreach ($bloques as $bloque) {
                $id_materia = $bloque['materia'] ?? null;
                if ($id_materia && !in_array($id_materia, $materias_asignadas)) {
                    $materias_asignadas[] = $id_materia;
                }
            }
        }
        
        // Verificar que todas las materias obligatorias estén asignadas
        foreach ($materias_obligatorias as $materia) {
            if (!in_array($materia['id_materia'], $materias_asignadas)) {
                $this->agregarError(
                    "Materia obligatoria no asignada: '{$materia['nombre_materia']}' " .
                    "(debe estar al menos una vez en el horario semanal)"
                );
            }
        }
    }
    
    /**
     * Obtener errores de validación
     */
    public function getErrores() {
        return $this->errores;
    }
    
    /**
     * Verificar si hay errores
     */
    public function tieneErrores() {
        return !empty($this->errores);
    }
    
    /**
     * Agregar error
     */
    private function agregarError($mensaje) {
        $this->errores[] = $mensaje;
    }
}

