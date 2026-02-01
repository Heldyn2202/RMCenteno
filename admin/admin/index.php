<?php  
include ('../app/config.php');  
include ('../admin/layout/parte1.php');  
include ('../app/controllers/roles/listado_de_roles.php');  
include ('../app/controllers/usuarios/listado_de_usuarios.php');  
include ('../app/controllers/niveles/listado_de_niveles.php');  
include ('../app/controllers/grados/listado_de_grados.php');  
include ('../app/controllers/administrativos/listado_de_administrativos.php');  
include ('../app/controllers/representantes/listado_de_representantes.php');  
include ('../app/controllers/estudiantes/listado_de_estudiantes.php');  
include ('../app/controllers/estudiantes/reporte_estudiantes.php');  
include ('../app/controllers/secciones/listado_de_secciones.php');  
include ('../app/controllers/estudiantes/lista_inscripcion.php');  

// Funciones auxiliares
function getPeriodoEscolarActivo($pdo) {  
    $sql = "SELECT * FROM gestiones WHERE estado = '1' LIMIT 1";  
    $stmt = $pdo->prepare($sql);  
    $stmt->execute();  
    return $stmt->fetch(PDO::FETCH_ASSOC);  
}  

function getInscripcionesByGestion($pdo, $id_gestion) {  
    $sql = "SELECT * FROM inscripciones WHERE id_gestion = :id_gestion";  
    $stmt = $pdo->prepare($sql);  
    $stmt->bindParam(':id_gestion', $id_gestion, PDO::PARAM_INT);  
    $stmt->execute();  
    return $stmt->fetchAll(PDO::FETCH_ASSOC);  
}

// Consultas para estudiantes registrados pero no inscritos
function getEstudiantesNoInscritos($pdo, $id_gestion_activa) {
    $sql = "SELECT e.* 
            FROM estudiantes e 
            LEFT JOIN inscripciones i ON e.id_estudiante = i.id_estudiante AND i.id_gestion = :id_gestion
            WHERE e.estatus = 'activo' AND i.id IS NULL";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_gestion', $id_gestion_activa, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Consulta para inscripciones por año/grado
function getInscripcionesPorGrado($pdo, $id_gestion_activa) {
    $sql = "SELECT g.grado, COUNT(i.id) as total
            FROM inscripciones i 
            JOIN grados g ON i.grado = g.id_grado 
            WHERE i.id_gestion = :id_gestion 
            GROUP BY g.grado 
            ORDER BY g.grado";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_gestion', $id_gestion_activa, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// FUNCIÓN AUXILIAR: Contar calificaciones en un lapso específico
function contarCalificacionesEnLapso($pdo, $id_profesor, $id_gestion, $id_lapso) {
    $sql = "SELECT COUNT(*) as total
            FROM notas_estudiantes ne
            JOIN asignaciones_profesor ap ON ne.id_materia = ap.id_materia
            WHERE ap.id_profesor = :id_profesor 
            AND ap.id_gestion = :id_gestion
            AND ne.id_lapso = :id_lapso";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_profesor', $id_profesor, PDO::PARAM_INT);
    $stmt->bindParam(':id_gestion', $id_gestion, PDO::PARAM_INT);
    $stmt->bindParam(':id_lapso', $id_lapso, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $result['total'] ?? 0;
}

// FUNCIÓN AUXILIAR: Verificar pendientes en un lapso específico
function verificarPendientesEnLapso($pdo, $id_profesor, $id_gestion, $id_lapso) {
    // Contar estudiantes sin calificación en este lapso
    $sql = "SELECT COUNT(*) as pendientes
            FROM asignaciones_profesor ap
            JOIN inscripciones i ON ap.id_seccion = i.id_seccion
            JOIN estudiantes e ON i.id_estudiante = e.id_estudiante
            LEFT JOIN notas_estudiantes ne ON e.id_estudiante = ne.id_estudiante 
                AND ap.id_materia = ne.id_materia 
                AND ne.id_lapso = :id_lapso
            WHERE ap.id_profesor = :id_profesor 
            AND ap.estado = 1
            AND ap.id_gestion = :id_gestion
            AND i.id_gestion = :id_gestion2
            AND i.estado = 'activo'
            AND e.estatus = 'activo'
            AND ne.id_nota IS NULL";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_profesor', $id_profesor, PDO::PARAM_INT);
    $stmt->bindParam(':id_gestion', $id_gestion, PDO::PARAM_INT);
    $stmt->bindParam(':id_gestion2', $id_gestion, PDO::PARAM_INT);
    $stmt->bindParam(':id_lapso', $id_lapso, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $result['pendientes'] ?? 0;
}

// FUNCIÓN PARA DETERMINAR EL LAPSO ACTUAL SEGÚN SISTEMA SECUENCIAL
function getLapsoActualSecuencial($pdo, $id_profesor, $id_gestion) {
    // Obtener todos los lapsos de esta gestión ordenados
    $sql_lapsos = "SELECT id_lapso, nombre_lapso, fecha_inicio, fecha_fin 
                   FROM lapsos 
                   WHERE id_gestion = :id_gestion 
                   ORDER BY id_lapso ASC";
    
    $stmt = $pdo->prepare($sql_lapsos);
    $stmt->bindParam(':id_gestion', $id_gestion, PDO::PARAM_INT);
    $stmt->execute();
    $lapsos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($lapsos)) {
        // Si no hay lapsos, usar el primero (6) por defecto para gestión 34
        return ['id_lapso' => 6, 'nombre_lapso' => 'Primer Lapso'];
    }
    
    // Para cada lapso en orden, verificar si ya están completas todas las calificaciones
    foreach ($lapsos as $lapso) {
        $id_lapso = $lapso['id_lapso'];
        
        // Verificar si hay calificaciones pendientes en este lapso
        $pendientes = verificarPendientesEnLapso($pdo, $id_profesor, $id_gestion, $id_lapso);
        
        // Si hay pendientes en este lapso, este es el lapso actual
        if ($pendientes > 0) {
            return [
                'id_lapso' => $id_lapso,
                'nombre_lapso' => $lapso['nombre_lapso']
            ];
        }
        
        // Si no hay pendientes, verificar si el lapso está completamente vacío
        $total_calificaciones = contarCalificacionesEnLapso($pdo, $id_profesor, $id_gestion, $id_lapso);
        
        // Si el lapso está completamente vacío, es el siguiente a trabajar
        if ($total_calificaciones == 0) {
            return [
                'id_lapso' => $id_lapso,
                'nombre_lapso' => $lapso['nombre_lapso']
            ];
        }
    }
    
    // Si todos los lapsos están completos, usar el último
    $ultimo_lapso = end($lapsos);
    return [
        'id_lapso' => $ultimo_lapso['id_lapso'],
        'nombre_lapso' => $ultimo_lapso['nombre_lapso']
    ];
}

// CONSULTAS ESPECÍFICAS PARA DOCENTES - CORREGIDAS
function getDatosDocente($pdo, $id_usuario_sesion) {
    if (!$id_usuario_sesion) {
        error_log("ERROR getDatosDocente: ID de usuario es 0");
        return ['nombre' => 'Docente', 'error' => 'ID usuario vacío'];
    }
    
    try {
        // Obtener email del usuario desde la tabla usuarios
        $sql_usuario = "SELECT email, id_usuario FROM usuarios WHERE id_usuario = :id_usuario AND estado = 1";
        $stmt = $pdo->prepare($sql_usuario);
        $stmt->bindParam(':id_usuario', $id_usuario_sesion, PDO::PARAM_INT);
        $stmt->execute();
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$usuario || empty($usuario['email'])) {
            error_log("ERROR getDatosDocente: No se encontró usuario con ID " . $id_usuario_sesion);
            return ['nombre' => 'Docente'];
        }
        
        // Buscar el profesor por email (PRIMER MÉTODO - por email)
        $sql_profesor = "SELECT * FROM profesores WHERE email = :email AND estado = 1 LIMIT 1";
        $stmt = $pdo->prepare($sql_profesor);
        $stmt->bindParam(':email', $usuario['email'], PDO::PARAM_STR);
        $stmt->execute();
        $profesor = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($profesor) {
            return [
                'nombre' => $profesor['nombres'] . ' ' . $profesor['apellidos'],
                'email' => $profesor['email'],
                'id_profesor' => $profesor['id_profesor'],
                'cedula' => $profesor['cedula'] ?? ''
            ];
        }
        
        // Si no encuentra por email, buscar por id_usuario (SEGUNDO MÉTODO)
        $sql_profesor2 = "SELECT * FROM profesores WHERE id_usuario = :id_usuario AND estado = 1 LIMIT 1";
        $stmt = $pdo->prepare($sql_profesor2);
        $stmt->bindParam(':id_usuario', $id_usuario_sesion, PDO::PARAM_INT);
        $stmt->execute();
        $profesor2 = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($profesor2) {
            return [
                'nombre' => $profesor2['nombres'] . ' ' . $profesor2['apellidos'],
                'email' => $profesor2['email'],
                'id_profesor' => $profesor2['id_profesor'],
                'cedula' => $profesor2['cedula'] ?? ''
            ];
        }
        
        return ['nombre' => 'Docente'];
        
    } catch (PDOException $e) {
        error_log("Error en getDatosDocente: " . $e->getMessage());
        return ['nombre' => 'Docente'];
    }
}

// Obtener cursos asignados al docente (materias) - VERSIÓN CORREGIDA
function getCursosDocente($pdo, $id_profesor) {
    if (!$id_profesor) {
        return ['total_cursos' => 0, 'detalle' => []];
    }
    
    try {
        // Primero obtener la gestión activa
        $sql_gestion = "SELECT id_gestion FROM gestiones WHERE estado = 1 LIMIT 1";
        $stmt = $pdo->prepare($sql_gestion);
        $stmt->execute();
        $gestion = $stmt->fetch(PDO::FETCH_ASSOC);
        $id_gestion_activa = $gestion ? $gestion['id_gestion'] : 0;
        
        // Contar materias asignadas al profesor en la gestión activa SOLO ACTIVAS
        $sql_count = "SELECT COUNT(DISTINCT a.id_materia) as total_cursos 
                     FROM asignaciones_profesor a 
                     WHERE a.id_profesor = :id_profesor 
                     AND a.estado = 1
                     AND a.id_gestion = :id_gestion";
        $stmt = $pdo->prepare($sql_count);
        $stmt->bindParam(':id_profesor', $id_profesor, PDO::PARAM_INT);
        $stmt->bindParam(':id_gestion', $id_gestion_activa, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $total = $result['total_cursos'] ?? 0;
        
        // Obtener detalle de cursos
        $sql_detalle = "SELECT DISTINCT a.id_materia, m.nombre_materia, a.id_seccion, 
                        s.nombre_seccion, g.grado
                       FROM asignaciones_profesor a
                       LEFT JOIN materias m ON a.id_materia = m.id_materia
                       LEFT JOIN secciones s ON a.id_seccion = s.id_seccion
                       LEFT JOIN grados gr ON s.id_grado = gr.id_grado
                       LEFT JOIN grados g ON s.id_grado = g.id_grado
                       WHERE a.id_profesor = :id_profesor 
                       AND a.estado = 1
                       AND a.id_gestion = :id_gestion
                       ORDER BY g.grado, s.nombre_seccion, m.nombre_materia";
        
        $stmt = $pdo->prepare($sql_detalle);
        $stmt->bindParam(':id_profesor', $id_profesor, PDO::PARAM_INT);
        $stmt->bindParam(':id_gestion', $id_gestion_activa, PDO::PARAM_INT);
        $stmt->execute();
        $detalle = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'total_cursos' => $total,
            'detalle' => $detalle
        ];
        
    } catch (PDOException $e) {
        error_log("Error en getCursosDocente: " . $e->getMessage());
        return ['total_cursos' => 0, 'detalle' => []];
    }
}

// Obtener total de estudiantes del docente - VERSIÓN CORREGIDA DEFINITIVA
function getEstudiantesDocente($pdo, $id_profesor) {
    if (!$id_profesor) {
        return ['total_estudiantes' => 0];
    }
    
    try {
        // Obtener gestión activa
        $sql_gestion = "SELECT id_gestion FROM gestiones WHERE estado = 1 LIMIT 1";
        $stmt = $pdo->prepare($sql_gestion);
        $stmt->execute();
        $gestion = $stmt->fetch(PDO::FETCH_ASSOC);
        $id_gestion_activa = $gestion ? $gestion['id_gestion'] : 0;
        
        if (!$id_gestion_activa) {
            return ['total_estudiantes' => 0];
        }
        
        // Obtener estudiantes únicos SOLO de asignaciones ACTIVAS del profesor
        $sql = "SELECT COUNT(DISTINCT i.id_estudiante) as total_estudiantes
                FROM asignaciones_profesor ap
                JOIN inscripciones i ON ap.id_seccion = i.id_seccion
                JOIN estudiantes e ON i.id_estudiante = e.id_estudiante
                WHERE ap.id_profesor = :id_profesor 
                AND ap.estado = 1
                AND ap.id_gestion = :id_gestion
                AND i.id_gestion = :id_gestion2
                AND i.estado = 'activo'
                AND e.estatus = 'activo'";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id_profesor', $id_profesor, PDO::PARAM_INT);
        $stmt->bindParam(':id_gestion', $id_gestion_activa, PDO::PARAM_INT);
        $stmt->bindParam(':id_gestion2', $id_gestion_activa, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $total = $result['total_estudiantes'] ?? 0;
        
        return ['total_estudiantes' => $total];
        
    } catch (PDOException $e) {
        error_log("Error en getEstudiantesDocente: " . $e->getMessage());
        return ['total_estudiantes' => 0];
    }
}

// Obtener detalle de estudiantes del docente - SOLO ASIGNACIONES ACTIVAS
function getDetalleEstudiantesDocente($pdo, $id_profesor) {
    if (!$id_profesor) {
        return ['detalle' => [], 'total' => 0];
    }
    
    try {
        // Obtener gestión activa
        $sql_gestion = "SELECT id_gestion FROM gestiones WHERE estado = 1 LIMIT 1";
        $stmt = $pdo->prepare($sql_gestion);
        $stmt->execute();
        $gestion = $stmt->fetch(PDO::FETCH_ASSOC);
        $id_gestion_activa = $gestion ? $gestion['id_gestion'] : 0;
        
        if (!$id_gestion_activa) {
            return ['detalle' => [], 'total' => 0];
        }
        
        // Obtener las secciones asignadas al profesor SOLO ACTIVAS
        $sql_secciones = "SELECT DISTINCT a.id_seccion, s.nombre_seccion, g.grado
                         FROM asignaciones_profesor a
                         LEFT JOIN secciones s ON a.id_seccion = s.id_seccion
                         LEFT JOIN grados g ON s.id_grado = g.id_grado
                         WHERE a.id_profesor = :id_profesor 
                         AND a.estado = 1
                         AND a.id_gestion = :id_gestion
                         ORDER BY g.grado, s.nombre_seccion";
        
        $stmt = $pdo->prepare($sql_secciones);
        $stmt->bindParam(':id_profesor', $id_profesor, PDO::PARAM_INT);
        $stmt->bindParam(':id_gestion', $id_gestion_activa, PDO::PARAM_INT);
        $stmt->execute();
        $secciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $detalle_estudiantes = [];
        $total_estudiantes = 0;
        
        foreach ($secciones as $seccion) {
            // Obtener estudiantes en esta sección SOLO ACTIVOS
            $sql_estudiantes = "SELECT i.id_estudiante, e.nombres, e.apellidos, e.cedula
                               FROM inscripciones i
                               LEFT JOIN estudiantes e ON i.id_estudiante = e.id_estudiante
                               WHERE i.id_seccion = :id_seccion 
                               AND i.id_gestion = :id_gestion
                               AND i.estado = 'activo'
                               AND e.estatus = 'activo'
                               ORDER BY e.apellidos, e.nombres";
            
            $stmt = $pdo->prepare($sql_estudiantes);
            $stmt->bindParam(':id_seccion', $seccion['id_seccion'], PDO::PARAM_INT);
            $stmt->bindParam(':id_gestion', $id_gestion_activa, PDO::PARAM_INT);
            $stmt->execute();
            $estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($estudiantes) > 0) {
                $detalle_estudiantes[] = [
                    'seccion_id' => $seccion['id_seccion'],
                    'seccion' => $seccion['nombre_seccion'],
                    'grado' => $seccion['grado'],
                    'total' => count($estudiantes),
                    'estudiantes' => $estudiantes
                ];
                $total_estudiantes += count($estudiantes);
            }
        }
        
        return [
            'detalle' => $detalle_estudiantes,
            'total' => $total_estudiantes
        ];
        
    } catch (PDOException $e) {
        error_log("Error en getDetalleEstudiantesDocente: " . $e->getMessage());
        return ['detalle' => [], 'total' => 0];
    }
}

// Obtener calificaciones pendientes del docente - VERSIÓN CON SISTEMA DE LAPSOS SECUENCIALES
function getCalificacionesPendientes($pdo, $id_profesor) {
    if (!$id_profesor) {
        return ['pendientes' => 0, 'detalle' => [], 'mensaje' => 'Profesor no identificado'];
    }
    
    try {
        // Obtener gestión activa
        $sql_gestion = "SELECT id_gestion FROM gestiones WHERE estado = 1 LIMIT 1";
        $stmt = $pdo->prepare($sql_gestion);
        $stmt->execute();
        $gestion = $stmt->fetch(PDO::FETCH_ASSOC);
        $id_gestion_activa = $gestion ? $gestion['id_gestion'] : 0;
        
        if (!$id_gestion_activa) {
            return ['pendientes' => 0, 'detalle' => [], 'mensaje' => 'No hay período activo'];
        }
        
        // Obtener el lapso actual según el sistema secuencial
        $lapso_info = getLapsoActualSecuencial($pdo, $id_profesor, $id_gestion_activa);
        $id_lapso_actual = $lapso_info['id_lapso'];
        $nombre_lapso = $lapso_info['nombre_lapso'];
        
        error_log("DEBUG: Lapso actual (secuencial): $id_lapso_actual - $nombre_lapso");
        
        // Obtener materias asignadas al profesor con detalle
        $sql_materias = "SELECT DISTINCT ap.id_materia, m.nombre_materia, 
                         ap.id_seccion, s.nombre_seccion, g.grado
                        FROM asignaciones_profesor ap
                        LEFT JOIN materias m ON ap.id_materia = m.id_materia
                        LEFT JOIN secciones s ON ap.id_seccion = s.id_seccion
                        LEFT JOIN grados g ON s.id_grado = g.id_grado
                        WHERE ap.id_profesor = :id_profesor 
                        AND ap.estado = 1
                        AND ap.id_gestion = :id_gestion
                        ORDER BY g.grado, s.nombre_seccion, m.nombre_materia";
        
        $stmt = $pdo->prepare($sql_materias);
        $stmt->bindParam(':id_profesor', $id_profesor, PDO::PARAM_INT);
        $stmt->bindParam(':id_gestion', $id_gestion_activa, PDO::PARAM_INT);
        $stmt->execute();
        $materias = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($materias)) {
            return ['pendientes' => 0, 'detalle' => [], 'mensaje' => 'No tiene materias asignadas'];
        }
        
        $total_pendientes = 0;
        $detalle_pendientes = [];
        
        foreach ($materias as $materia) {
            $id_materia = $materia['id_materia'];
            $nombre_materia = $materia['nombre_materia'];
            $id_seccion = $materia['id_seccion'];
            $nombre_seccion = $materia['nombre_seccion'];
            $grado = $materia['grado'];
            
            // Obtener estudiantes ACTIVOS en esta sección
            $sql_estudiantes = "SELECT i.id_estudiante, e.nombres, e.apellidos, e.cedula,
                               (SELECT COUNT(*) FROM notas_estudiantes ne 
                                WHERE ne.id_estudiante = i.id_estudiante 
                                AND ne.id_materia = :id_materia 
                                AND ne.id_lapso = :id_lapso) as tiene_calificacion
                               FROM inscripciones i
                               LEFT JOIN estudiantes e ON i.id_estudiante = e.id_estudiante
                               WHERE i.id_seccion = :id_seccion 
                               AND i.id_gestion = :id_gestion
                               AND i.estado = 'activo'
                               AND e.estatus = 'activo'
                               ORDER BY e.apellidos, e.nombres";
            
            $stmt = $pdo->prepare($sql_estudiantes);
            $stmt->bindParam(':id_seccion', $id_seccion, PDO::PARAM_INT);
            $stmt->bindParam(':id_gestion', $id_gestion_activa, PDO::PARAM_INT);
            $stmt->bindParam(':id_materia', $id_materia, PDO::PARAM_INT);
            $stmt->bindParam(':id_lapso', $id_lapso_actual, PDO::PARAM_INT);
            $stmt->execute();
            $estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $estudiantes_sin_calificacion = [];
            
            foreach ($estudiantes as $estudiante) {
                if ($estudiante['tiene_calificacion'] == 0) {
                    $estudiantes_sin_calificacion[] = [
                        'id_estudiante' => $estudiante['id_estudiante'],
                        'nombres' => $estudiante['nombres'],
                        'apellidos' => $estudiante['apellidos'],
                        'cedula' => $estudiante['cedula']
                    ];
                }
            }
            
            $pendientes_materia = count($estudiantes_sin_calificacion);
            
            if ($pendientes_materia > 0) {
                $total_pendientes += $pendientes_materia;
                $detalle_pendientes[] = [
                    'materia_id' => $id_materia,
                    'materia' => $nombre_materia,
                    'seccion_id' => $id_seccion,
                    'seccion' => $nombre_seccion,
                    'grado' => $grado,
                    'pendientes' => $pendientes_materia,
                    'estudiantes' => $estudiantes_sin_calificacion,
                    'lapso' => $id_lapso_actual,
                    'nombre_lapso' => $nombre_lapso
                ];
            }
        }
        
        error_log("DEBUG: Total pendientes en lapso $id_lapso_actual ($nombre_lapso): $total_pendientes");
        
        return [
            'pendientes' => $total_pendientes,
            'detalle' => $detalle_pendientes,
            'mensaje' => $total_pendientes > 0 ? 
                "Tiene $total_pendientes calificación(es) pendiente(s) en el $nombre_lapso" : 
                "Todas las calificaciones están registradas para el $nombre_lapso",
            'lapso_actual' => $id_lapso_actual,
            'nombre_lapso' => $nombre_lapso
        ];
        
    } catch (PDOException $e) {
        error_log("Error en getCalificacionesPendientes: " . $e->getMessage());
        return ['pendientes' => 0, 'detalle' => [], 'mensaje' => 'Error al calcular pendientes'];
    }
}

// Obtener horarios del docente
function getHorariosDocente($pdo, $id_profesor) {
    if (!$id_profesor) {
        return ['horarios' => [], 'total' => 0];
    }
    
    try {
        // Obtener gestión activa
        $sql_gestion = "SELECT id_gestion FROM gestiones WHERE estado = 1 LIMIT 1";
        $stmt = $pdo->prepare($sql_gestion);
        $stmt->execute();
        $gestion = $stmt->fetch(PDO::FETCH_ASSOC);
        $id_gestion_activa = $gestion ? $gestion['id_gestion'] : 0;
        
        if (!$id_gestion_activa) {
            return ['horarios' => [], 'total' => 0];
        }
        
        // Obtener horarios del profesor para la gestión activa
        $sql_horarios = "SELECT h.*, 
                        g.grado, 
                        s.nombre_seccion,
                        h.aula,
                        DATE_FORMAT(h.fecha_inicio, '%d/%m/%Y') as fecha_inicio_formatted,
                        DATE_FORMAT(h.fecha_fin, '%d/%m/%Y') as fecha_fin_formatted
                       FROM horarios h
                       LEFT JOIN grados g ON h.id_grado = g.id_grado
                       LEFT JOIN secciones s ON h.id_seccion = s.id_seccion
                       LEFT JOIN asignaciones_profesor ap ON h.id_grado = ap.id_grado 
                         AND h.id_seccion = ap.id_seccion
                       WHERE ap.id_profesor = :id_profesor 
                       AND h.id_gestion = :id_gestion
                       AND h.estado = 1
                       AND ap.estado = 1
                       ORDER BY h.fecha_inicio, g.grado, s.nombre_seccion";
        
        $stmt = $pdo->prepare($sql_horarios);
        $stmt->bindParam(':id_profesor', $id_profesor, PDO::PARAM_INT);
        $stmt->bindParam(':id_gestion', $id_gestion_activa, PDO::PARAM_INT);
        $stmt->execute();
        $horarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Agrupar horarios por día de la semana
        $horarios_agrupados = [];
        $dias_semana = [
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miércoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sábado',
            0 => 'Domingo'
        ];
        
        foreach ($horarios as $horario) {
            // Extraer día de la semana de fecha_inicio
            $fecha = new DateTime($horario['fecha_inicio']);
            $dia_semana = $fecha->format('N'); // 1 (lunes) a 7 (domingo)
            $dia_nombre = $dias_semana[$dia_semana] ?? 'Sin día';
            
            if (!isset($horarios_agrupados[$dia_nombre])) {
                $horarios_agrupados[$dia_nombre] = [];
            }
            
            $horarios_agrupados[$dia_nombre][] = $horario;
        }
        
        return [
            'horarios' => $horarios_agrupados,
            'total' => count($horarios),
            'detalle' => $horarios
        ];
        
    } catch (PDOException $e) {
        error_log("Error en getHorariosDocente: " . $e->getMessage());
        return ['horarios' => [], 'total' => 0];
    }
}

// Consultas seguras (solo para tablas que existen)
$gestion_activa = getPeriodoEscolarActivo($pdo);
$id_gestion_activa = $gestion_activa ? $gestion_activa['id_gestion'] : null;

// Obtener estudiantes no inscritos
$estudiantes_no_inscritos = [];
if ($id_gestion_activa) {
    $estudiantes_no_inscritos = getEstudiantesNoInscritos($pdo, $id_gestion_activa);
}
$contador_no_inscritos = count($estudiantes_no_inscritos);

// Obtener inscripciones por grado del año actual
$inscripciones_por_grado = [];
if ($id_gestion_activa) {
    $inscripciones_por_grado = getInscripcionesPorGrado($pdo, $id_gestion_activa);
}

// Obtener datos específicos para docentes
if ($rol_sesion_usuario == "DOCENTE") {
    // CORRECCIÓN PRINCIPAL: Obtener correctamente el ID del usuario de la sesión
    $id_usuario_sesion = 0;
    
    // Intentar diferentes formas de obtener el ID
    if (isset($_SESSION['id_usuario']) && $_SESSION['id_usuario'] > 0) {
        $id_usuario_sesion = $_SESSION['id_usuario'];
    } elseif (isset($_SESSION['id']) && $_SESSION['id'] > 0) {
        $id_usuario_sesion = $_SESSION['id'];
    } elseif (isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0) {
        $id_usuario_sesion = $_SESSION['user_id'];
    } elseif (isset($_SESSION['usuario_id']) && $_SESSION['usuario_id'] > 0) {
        $id_usuario_sesion = $_SESSION['usuario_id'];
    }
    
    // Si aún es 0, intentar obtener del email en sesión
    if ($id_usuario_sesion == 0 && isset($_SESSION['email'])) {
        $sql_email = "SELECT id_usuario FROM usuarios WHERE email = :email AND estado = 1 LIMIT 1";
        $stmt = $pdo->prepare($sql_email);
        $stmt->bindParam(':email', $_SESSION['email'], PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            $id_usuario_sesion = $user['id_usuario'];
        }
    }
    
    // Obtener datos del docente
    $datos_docente = getDatosDocente($pdo, $id_usuario_sesion);
    
    // Obtener el ID del profesor
    $id_profesor = $datos_docente['id_profesor'] ?? 0;
    
    // Si no se encontró profesor por los métodos anteriores, usar valor por defecto
    if (!$id_profesor && $id_usuario_sesion > 0) {
        // Para pruebas, usar el profesor ID 3 (Heldyn David)
        $id_profesor = 3;
        $datos_docente = [
            'nombre' => 'Heldyn David Diaz Daboin',
            'email' => 'heldyndiaz19@gmail.com',
            'id_profesor' => 3,
            'cedula' => '27985583'
        ];
    }
    
    if ($id_profesor) {
        // Obtener estadísticas
        $cursos_docente = getCursosDocente($pdo, $id_profesor);
        $estudiantes_docente = getEstudiantesDocente($pdo, $id_profesor);
        $calificaciones_pendientes = getCalificacionesPendientes($pdo, $id_profesor);
        $detalle_estudiantes = getDetalleEstudiantesDocente($pdo, $id_profesor);
        $horarios_docente = getHorariosDocente($pdo, $id_profesor);
        
        // Asegurar consistencia entre métodos
        if (isset($detalle_estudiantes['total']) && $detalle_estudiantes['total'] != $estudiantes_docente['total_estudiantes']) {
            $estudiantes_docente['total_estudiantes'] = $detalle_estudiantes['total'];
        }
    } else {
        $cursos_docente = ['total_cursos' => 0, 'detalle' => []];
        $estudiantes_docente = ['total_estudiantes' => 0];
        $calificaciones_pendientes = ['pendientes' => 0, 'detalle' => [], 'mensaje' => 'Profesor no encontrado'];
        $detalle_estudiantes = ['detalle' => [], 'total' => 0];
        $horarios_docente = ['horarios' => [], 'total' => 0];
    }
}

?>  

<!-- Content Wrapper. Contains page content -->  
<div class="content-wrapper">  
    <div class="container">  
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-dark">Panel de Control</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="./index.php?page=home" class="text-info">Inicio</a></li>
                            <li class="breadcrumb-item active">Panel de Control</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        
        <hr>
        
        <!-- VISTA PARA DOCENTES -->
        <?php if ($rol_sesion_usuario == "DOCENTE") { ?>
            <!-- TARJETA DE BIENVENIDA CON NUEVO ESTILO -->
            <div class="col-lg-12 mb-4">  
                <div class="card card-dashboard" style="border-radius: 10px; overflow: hidden; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); border: none; height: 100%;">  
                    <div class="card-body" style="padding: 1.5rem; position: relative;">  
                        <div class="image-background">
                            <img src="<?=APP_URL;?>" alt="Profesor" style="width: 80px; height: 80px; opacity: 0.1;">
                        </div>
                        <div class="row">
                            <div class="col-md-8">
                                <h3 style="color: #3c8dbc; font-weight: 700; margin-bottom: 0.5rem;">Bienvenido, Prof. <?php echo htmlspecialchars($datos_docente['nombre'] ?? 'Docente'); ?></h3>
                                <p class="text-muted mb-2">Es un placer tenerle de vuelta. Aquí tiene un resumen de sus actividades.</p>
                                <?php if (isset($datos_docente['email'])): ?>
                                    <small class="opacity-75"><i class="fas fa-envelope mr-1"></i><?php echo htmlspecialchars($datos_docente['email']); ?></small>
                                <?php endif; ?>
                                <?php if (isset($datos_docente['cedula']) && !empty($datos_docente['cedula'])): ?>
                                    <small class="opacity-75 d-block"><i class="fas fa-id-card mr-1"></i><?php echo htmlspecialchars($datos_docente['cedula']); ?></small>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-4 text-right">
                                <div class="icon-dashboard" style="background: linear-gradient(135deg, rgba(60, 141, 188, 0.1) 0%, rgba(45, 95, 126, 0.1) 100%); width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-left: auto;">
                                    <i class="fas fa-chalkboard-teacher" style="color: #3c8dbc; font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>  
                </div>  
            </div>

            <!-- ESTADÍSTICAS DEL DOCENTE CON NUEVO ESTILO -->
            <div class="row mt-2">
                <!-- Tarjeta 1: Distribución de Estudiantes (PRIMERA POSICIÓN) -->
                <div class="col-lg-3 col-md-6 col-6 mb-4">  
                    <div class="card card-dashboard" style="border-radius: 10px; overflow: hidden; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); border: none; height: 100%;">  
                        <div class="card-body" style="padding: 1.5rem; position: relative;">  
                            <div class="image-background">
                                <img src="<?=APP_URL;?>" alt="Estudiantes" style="width: 80px; height: 80px; opacity: 0.1;">
                            </div>
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h3 style="color: #3c8dbc; font-weight: 700; margin-bottom: 0.5rem;"><?php echo $detalle_estudiantes['total'] ?? 0; ?></h3>
                                    <p class="text-muted mb-2">Distribución de Estudiantes</p>
                                    <?php if (($detalle_estudiantes['total'] ?? 0) == 0): ?>
                                        <span class="badge badge-info"><i class="fas fa-info-circle mr-1"></i>Sin estudiantes</span>
                                    <?php else: ?>
                                        <span class="badge badge-primary">
                                            <i class="fas fa-users mr-1"></i>
                                            <?php echo count($detalle_estudiantes['detalle'] ?? []); ?> sección(es)
                                        </span>
                                    <?php endif; ?>
                                </div>  
                                <div class="icon-dashboard" style="background: linear-gradient(135deg, rgba(60, 141, 188, 0.1) 0%, rgba(45, 95, 126, 0.1) 100%); width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-user-graduate" style="color: #3c8dbc; font-size: 1.5rem;"></i>  
                                </div>  
                            </div>
                        </div>  
                        <a href="#" class="card-footer custom-btn text-white text-center" style="text-decoration: none; display: block; padding: 0.75rem; background-color: #3c8dbc;">  
                            Ver Detalles <i class="fas fa-arrow-circle-right ml-1"></i>  
                        </a>  
                    </div>  
                </div>

                <!-- Tarjeta 2: Cursos Asignados -->
                <div class="col-lg-3 col-md-6 col-6 mb-4">  
                    <div class="card card-dashboard" style="border-radius: 10px; overflow: hidden; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); border: none; height: 100%;">  
                        <div class="card-body" style="padding: 1.5rem; position: relative;">  
                            <div class="image-background">
                                <img src="<?=APP_URL;?>" alt="Libro" style="width: 80px; height: 80px; opacity: 0.1;">
                            </div>
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h3 style="color: #3c8dbc; font-weight: 700; margin-bottom: 0.5rem;"><?php echo $cursos_docente['total_cursos'] ?? 0; ?></h3>
                                    <p class="text-muted mb-2">Mis Cursos Asignados</p>
                                    <?php if (($cursos_docente['total_cursos'] ?? 0) == 0): ?>
                                        <span class="badge badge-warning"><i class="fas fa-exclamation-circle mr-1"></i>Sin asignaciones</span>
                                    <?php else: ?>
                                        <span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i>Activas</span>
                                    <?php endif; ?>
                                </div>  
                                <div class="icon-dashboard" style="background: linear-gradient(135deg, rgba(60, 141, 188, 0.1) 0%, rgba(45, 95, 126, 0.1) 100%); width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-book" style="color: #3c8dbc; font-size: 1.5rem;"></i>  
                                </div>  
                            </div>
                        </div>  
                        <a href="#" class="card-footer custom-btn text-white text-center" style="text-decoration: none; display: block; padding: 0.75rem; background-color: #3c8dbc;">  
                            Ver Cursos <i class="fas fa-arrow-circle-right ml-1"></i>  
                        </a>  
                    </div>  
                </div>

                <!-- Tarjeta 3: Calificaciones Pendientes -->
                <div class="col-lg-3 col-md-6 col-6 mb-4">  
                    <div class="card card-dashboard" style="border-radius: 10px; overflow: hidden; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); border: none; height: 100%;">  
                        <div class="card-body" style="padding: 1.5rem; position: relative;">  
                            <div class="image-background">
                                <img src="<?=APP_URL;?>" alt="Tareas" style="width: 80px; height: 80px; opacity: 0.1;">
                            </div>
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h3 style="color: #3c8dbc; font-weight: 700; margin-bottom: 0.5rem;"><?php echo $calificaciones_pendientes['pendientes'] ?? 0; ?></h3>
                                    <p class="text-muted mb-2">Calificaciones Pendientes</p>
                                    <small class="d-block text-muted mb-1">
                                        <i class="fas fa-calendar-alt mr-1"></i> Lapso actual: <?php echo $calificaciones_pendientes['nombre_lapso'] ?? 'N/A'; ?>
                                    </small>
                                    <?php if (($calificaciones_pendientes['pendientes'] ?? 0) > 0): ?>
                                        <span class="badge badge-danger"><i class="fas fa-clock mr-1"></i>Requiere atención</span>
                                    <?php else: ?>
                                        <span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i>Al día</span>
                                    <?php endif; ?>
                                </div>  
                                <div class="icon-dashboard" style="background: linear-gradient(135deg, rgba(60, 141, 188, 0.1) 0%, rgba(45, 95, 126, 0.1) 100%); width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-tasks" style="color: #3c8dbc; font-size: 1.5rem;"></i>  
                                </div>  
                            </div>
                        </div>  
                        <a href="#" class="card-footer custom-btn text-white text-center" style="text-decoration: none; display: block; padding: 0.75rem; background-color: #3c8dbc;">  
                            Gestionar <i class="fas fa-arrow-circle-right ml-1"></i>  
                        </a>  
                    </div>  
                </div>

                <!-- Tarjeta 4: Información del Periodo -->
                <div class="col-lg-3 col-md-6 col-6 mb-4">  
                    <div class="card card-dashboard" style="border-radius: 10px; overflow: hidden; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); border: none; height: 100%;">  
                        <div class="card-body" style="padding: 1.5rem; position: relative;">  
                            <div class="image-background">
                                <img src="<?=APP_URL;?>" alt="Calendario" style="width: 80px; height: 80px; opacity: 0.1;">
                            </div>
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <?php if ($gestion_activa): ?>
                                        <?php  
                                        $año_inicio = date('Y', strtotime($gestion_activa['desde']));  
                                        $año_fin = date('Y', strtotime($gestion_activa['hasta']));  
                                        echo "<h3 style='color: #3c8dbc; font-weight: 700; margin-bottom: 0.5rem;'>{$año_inicio}-{$año_fin}</h3>";  
                                        ?>
                                    <?php else: ?>
                                        <h3 style="color: #3c8dbc; font-weight: 700; margin-bottom: 0.5rem;">No activo</h3>
                                    <?php endif; ?>
                                    <p class="text-muted mb-2">Información del Periodo</p>
                                    <?php if ($gestion_activa): ?>
                                        <span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i>Activo</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger"><i class="fas fa-exclamation-circle mr-1"></i>Inactivo</span>
                                    <?php endif; ?>
                                    <?php if (isset($calificaciones_pendientes['nombre_lapso'])): ?>
                                        <small class="d-block text-muted mt-1">
                                            <i class="fas fa-clock mr-1"></i> Lapso: <?php echo $calificaciones_pendientes['nombre_lapso']; ?>
                                        </small>
                                    <?php endif; ?>
                                </div>  
                                <div class="icon-dashboard" style="background: linear-gradient(135deg, rgba(60, 141, 188, 0.1) 0%, rgba(45, 95, 126, 0.1) 100%); width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-calendar-alt" style="color: #3c8dbc; font-size: 1.5rem;"></i>  
                                </div>  
                            </div>
                        </div>  
                        <a href="#" class="card-footer custom-btn text-white text-center" style="text-decoration: none; display: block; padding: 0.75rem; background-color: #3c8dbc;">  
                            Ver Detalles <i class="fas fa-arrow-circle-right ml-1"></i>  
                        </a>  
                    </div>  
                </div>
            </div>

            <!-- DETALLE DE CURSOS ASIGNADOS (DESPLEGABLE) -->
            <?php if (isset($cursos_docente['detalle']) && count($cursos_docente['detalle']) > 0): ?>
            <div class="card mt-3 card-dashboard">
                <div class="card-header bg-primary text-white" style="cursor: pointer;" onclick="toggleCursos()">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-book-open mr-2"></i>Mis Cursos Asignados
                        <span class="badge badge-light float-right"><?php echo $cursos_docente['total_cursos']; ?> materias</span>
                        <i class="fas fa-chevron-down float-right mr-2" id="cursosIcon"></i>
                    </h5>
                </div>
                <div class="card-body p-0" id="cursosDetalle" style="display: none;">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Materia</th>
                                <th>Sección</th>
                                <th>Grado</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cursos_docente['detalle'] as $curso): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($curso['nombre_materia'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($curso['nombre_seccion'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($curso['grado'] ?? 'N/A'); ?></td>
                                <td><span class="badge badge-success">Activa</span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <!-- DETALLE DE CALIFICACIONES PENDIENTES (DESPLEGABLE) -->
            <?php if (isset($calificaciones_pendientes['detalle']) && count($calificaciones_pendientes['detalle']) > 0): ?>
            <div class="card mt-3 border-warning card-dashboard">
                <div class="card-header bg-warning text-dark" style="cursor: pointer;" onclick="toggleCalificaciones()">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-exclamation-triangle mr-2"></i>Calificaciones Pendientes
                        <span class="badge badge-danger float-right"><?php echo $calificaciones_pendientes['pendientes']; ?> pendientes</span>
                        <i class="fas fa-chevron-down float-right mr-2" id="calificacionesIcon"></i>
                    </h5>
                    <small class="d-block mt-1">
                        <i class="fas fa-calendar-alt mr-1"></i> Lapso actual: <?php echo $calificaciones_pendientes['nombre_lapso'] ?? 'N/A'; ?>
                    </small>
                </div>
                <div class="card-body p-0" id="calificacionesDetalle" style="display: none;">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Materia</th>
                                <th>Sección</th>
                                <th>Pendientes</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($calificaciones_pendientes['detalle'] as $detalle): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($detalle['materia']); ?></td>
                                <td><?php echo htmlspecialchars($detalle['seccion']); ?> (<?php echo htmlspecialchars($detalle['grado']); ?>)</td>
                                <td>
                                    <span class="badge badge-danger"><?php echo $detalle['pendientes']; ?> estudiantes</span>
                                    <small class="d-block text-muted">Lapso: <?php echo $detalle['nombre_lapso'] ?? $detalle['lapso']; ?></small>
                                </td>
                                <td>
                                    <a href="?page=calificaciones&materia=<?php echo $detalle['materia_id']; ?>&seccion=<?php echo $detalle['seccion_id']; ?>&lapso=<?php echo $detalle['lapso']; ?>" 
                                       class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-edit mr-1"></i> Calificar
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <!-- DETALLE DE ESTUDIANTES POR SECCIÓN (MANTENIDO ASÍ) -->
            <?php if (isset($detalle_estudiantes['detalle']) && count($detalle_estudiantes['detalle']) > 0): ?>
            <div class="card mt-3 card-dashboard">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user-graduate mr-2"></i>Distribución de Estudiantes por Sección
                        <span class="badge badge-light float-right"><?php echo $detalle_estudiantes['total']; ?> estudiantes</span>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach ($detalle_estudiantes['detalle'] as $seccion): ?>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="border rounded p-3 text-center">
                                <h3 class="mb-1 text-primary"><?php echo $seccion['total']; ?></h3>
                                <h6 class="mb-1"><?php echo htmlspecialchars($seccion['grado']); ?> - <?php echo htmlspecialchars($seccion['seccion']); ?></h6>
                                <small class="text-muted">estudiantes</small>
                                <div class="mt-2">
                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                            onclick="verEstudiantesSeccion(<?php echo $seccion['seccion_id']; ?>)">
                                        <i class="fas fa-eye mr-1"></i> Ver
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- HORARIOS DEL DOCENTE (MANTENIDO ASÍ) -->
            <?php if (isset($horarios_docente['horarios']) && count($horarios_docente['horarios']) > 0): ?>
            <div class="card mt-3 card-dashboard">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-calendar-alt mr-2"></i>Mis Horarios de Clase
                        <span class="badge badge-light float-right"><?php echo $horarios_docente['total']; ?> horario(s)</span>
                    </h5>
                    <small class="d-block mt-1">
                        <i class="fas fa-info-circle mr-1"></i> Estos son los días y horarios en que debe impartir clases
                    </small>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach ($horarios_docente['horarios'] as $dia => $horarios_dia): ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card border-primary">
                                <div class="card-header bg-light text-primary">
                                    <h6 class="mb-0">
                                        <i class="fas fa-calendar-day mr-1"></i><?php echo $dia; ?>
                                        <span class="badge badge-primary float-right"><?php echo count($horarios_dia); ?></span>
                                    </h6>
                                </div>
                                <div class="card-body p-0">
                                    <div class="list-group list-group-flush">
                                        <?php foreach ($horarios_dia as $horario): ?>
                                        <div class="list-group-item">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h6 class="mb-1"><?php echo htmlspecialchars($horario['grado'] ?? 'N/A'); ?> - <?php echo htmlspecialchars($horario['nombre_seccion'] ?? 'N/A'); ?></h6>
                                                <small class="text-muted"><?php echo $horario['aula'] ?? 'N/A'; ?></small>
                                            </div>
                                            <p class="mb-1 small">
                                                <i class="fas fa-clock mr-1"></i>
                                                <?php echo $horario['fecha_inicio_formatted']; ?> 
                                                <?php if ($horario['fecha_fin_formatted']): ?>
                                                - <?php echo $horario['fecha_fin_formatted']; ?>
                                                <?php endif; ?>
                                            </p>
                                            <small class="text-muted">
                                                <i class="fas fa-check-circle text-success mr-1"></i>
                                                <?php echo $horario['estado'] == 1 ? 'Activo' : 'Inactivo'; ?>
                                            </small>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- MENSAJES DE ALERTA (MANTENIDO) -->
            <div class="mt-3">
                <?php if (($cursos_docente['total_cursos'] ?? 0) == 0): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        <strong>Atención:</strong> No tiene cursos asignados en este período.
                        <small class="d-block mt-1">Contacte al administrador para asignaciones.</small>
                    </div>
                <?php elseif (($estudiantes_docente['total_estudiantes'] ?? 0) > 0 && ($calificaciones_pendientes['pendientes'] ?? 0) == 0): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle mr-1"></i>
                        <strong>Al día:</strong> Todas las calificaciones están registradas para el <?php echo $calificaciones_pendientes['nombre_lapso'] ?? 'lapso actual'; ?>.
                        <?php if (isset($calificaciones_pendientes['lapso_actual']) && $calificaciones_pendientes['lapso_actual'] < 8): ?>
                        <small class="d-block mt-1">
                            <i class="fas fa-arrow-right"></i> Podrá avanzar al siguiente lapso cuando esté habilitado.
                        </small>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <script>
            function verEstudiantesSeccion(idSeccion) {
                // Redirigir a página de estudiantes de la sección
                window.location.href = '?page=estudiantes_seccion&id=' + idSeccion;
            }
            
            function toggleCursos() {
                var detalle = document.getElementById('cursosDetalle');
                var icon = document.getElementById('cursosIcon');
                if (detalle.style.display === 'none') {
                    detalle.style.display = 'block';
                    icon.className = 'fas fa-chevron-up float-right mr-2';
                } else {
                    detalle.style.display = 'none';
                    icon.className = 'fas fa-chevron-down float-right mr-2';
                }
            }
            
            function toggleCalificaciones() {
                var detalle = document.getElementById('calificacionesDetalle');
                var icon = document.getElementById('calificacionesIcon');
                if (detalle.style.display === 'none') {
                    detalle.style.display = 'block';
                    icon.className = 'fas fa-chevron-up float-right mr-2';
                } else {
                    detalle.style.display = 'none';
                    icon.className = 'fas fa-chevron-down float-right mr-2';
                }
            }
            </script>
           
            
            <script>
            function verEstudiantesSeccion(idSeccion) {
                // Redirigir a página de estudiantes de la sección
                window.location.href = '?page=estudiantes_seccion&id=' + idSeccion;
            }
            </script>
            
            <hr>
        <?php } ?>
        <!-- Vista para el administrador -->  
        <?php if ($rol_sesion_usuario == "ADMINISTRADOR") { ?>  
            <div class="row"> 
                 
               <!-- Tarjeta 1: Periodo Escolar CON MARGEN -->
        <div class="col-lg-3 col-6 mb-4 margen-dashboard">  
            <div class="card card-dashboard" style="border-radius: 10px; overflow: hidden; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); border: none; height: 100%;">  
                <div class="card-body" style="padding: 1.5rem; position: relative;">  
                    <div class="image-background">
                        <img src="<?=APP_URL;?>" alt="Calendario" style="width: 80px; height: 80px; opacity: 0.1;">
                    </div>
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <?php  
                            if ($gestion_activa) {  
                                $año_inicio = date('Y', strtotime($gestion_activa['desde']));  
                                $año_fin = date('Y', strtotime($gestion_activa['hasta']));  
                                echo "<h3 style='color: #3c8dbc; font-weight: 700; margin-bottom: 0.5rem;'>{$año_inicio}-{$año_fin}</h3>";  
                            } else {  
                                echo "<h3 style='color: #6c757d; font-weight: 700; margin-bottom: 0.5rem;'>No activo</h3>";  
                            }  
                            ?>  
                            <p class="text-muted mb-2">Periodo escolar</p>
                            <?php if ($gestion_activa): ?>
                                <span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i>Activo</span>
                            <?php else: ?>
                                <span class="badge badge-danger"><i class="fas fa-exclamation-circle mr-1"></i>Inactivo</span>
                            <?php endif; ?>
                        </div>  
                        <div class="icon-dashboard" style="background: linear-gradient(135deg, rgba(60, 141, 188, 0.1) 0%, rgba(45, 95, 126, 0.1) 100%); width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-calendar-alt" style="color: #3c8dbc; font-size: 1.5rem;"></i>  
                        </div>  
                    </div>
                </div>  
                <a href="<?=APP_URL;?>/admin/configuraciones/gestion" class="card-footer custom-btn text-white text-center" style="text-decoration: none; display: block; padding: 0.75rem;">  
                    Gestionar <i class="fas fa-arrow-circle-right ml-1"></i>  
                </a>  
            </div>  
        </div>  

                <!-- Tarjeta 2: Estudiantes Activos -->
                <div class="col-lg-3 col-6 mb-4">   
                    <div class="card card-dashboard" style="border-radius: 10px; overflow: hidden; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); border: none; height: 100%;">  
                        <div class="card-body" style="padding: 1.5rem; position: relative;">  
                            <div class="image-background">
                                <img src="<?=APP_URL;?>" alt="Estudiantes" style="width: 80px; height: 80px; opacity: 0.1;">
                            </div>
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <?php  
                                    $estudiantes_activos = array_filter($estudiantes, function($estudiante) {
                                        return $estudiante['estatus'] === 'activo'; 
                                    });
                                    $contador_estudiantes = count($estudiantes_activos);  
                                    ?>  
                                    <h3 style="color: #3c8dbc; font-weight: 700; margin-bottom: 0.5rem;"><?=$contador_estudiantes;?></h3>  
                                    <p class="text-muted mb-2">Estudiantes activos</p>
                                    <span class="badge badge-info">Total en sistema</span>
                                </div>  
                                <div class="icon-dashboard" style="background: linear-gradient(135deg, rgba(60, 141, 188, 0.1) 0%, rgba(45, 95, 126, 0.1) 100%); width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-user-graduate" style="color: #3c8dbc; font-size: 1.5rem;"></i>  
                                </div>  
                            </div>
                        </div>  
                        <a href="<?=APP_URL;?>/admin/estudiantes" class="card-footer custom-btn text-white text-center" style="text-decoration: none; display: block; padding: 0.75rem;">  
                            Ver todos <i class="fas fa-arrow-circle-right ml-1"></i>  
                        </a>  
                    </div>  
                </div>

                <!-- Tarjeta 3: Representantes -->
                <div class="col-lg-3 col-6 mb-4">  
                    <div class="card card-dashboard" style="border-radius: 10px; overflow: hidden; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); border: none; height: 100%;">  
                        <div class="card-body" style="padding: 1.5rem; position: relative;">  
                            <div class="image-background">
                                <img src="<?=APP_URL;?>" alt="Representantes" style="width: 80px; height: 80px; opacity: 0.1;">
                            </div>
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <?php  
                                    $representantes_activos = array_filter($representantes, function($representante) {
                                        return $representante['estatus'] === 'Activo'; 
                                    });
                                    $contador_representantes = count($representantes_activos);  
                                    ?>  
                                    <h3 style="color: #3c8dbc; font-weight: 700; margin-bottom: 0.5rem;"><?=$contador_representantes;?></h3>  
                                    <p class="text-muted mb-2">Representantes</p>
                                    <div class="progress" style="height: 6px; width: 100px; background-color: #e9ecef;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: 85%;" aria-valuenow="85" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>  
                                <div class="icon-dashboard" style="background: linear-gradient(135deg, rgba(60, 141, 188, 0.1) 0%, rgba(45, 95, 126, 0.1) 100%); width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-users" style="color: #3c8dbc; font-size: 1.5rem;"></i>  
                                </div>  
                            </div>
                        </div>  
                        <a href="<?=APP_URL;?>/admin/representantes" class="card-footer custom-btn text-white text-center" style="text-decoration: none; display: block; padding: 0.75rem;">  
                            Ver todos <i class="fas fa-arrow-circle-right ml-1"></i>  
                        </a>  
                    </div>  
                </div>

                <!-- Tarjeta 4: Administrativos -->
                <div class="col-lg-3 col-6 mb-4">  
                    <div class="card card-dashboard" style="border-radius: 10px; overflow: hidden; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); border: none; height: 100%;">  
                        <div class="card-body" style="padding: 1.5rem; position: relative;">  
                            <div class="image-background">
                                <img src="<?=APP_URL;?>" alt="Administrativos" style="width: 80px; height: 80px; opacity: 0.1;">
                            </div>
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <?php  
                                    $contador_usuarios = count($usuarios);  
                                    ?>  
                                    <h3 style="color: #3c8dbc; font-weight: 700; margin-bottom: 0.5rem;"><?=$contador_usuarios;?></h3>  
                                    <p class="text-muted mb-2">Usuarios</p>
                                    <small class="text-muted"><?=count($roles)?> roles activos</small>
                                </div>  
                                <div class="icon-dashboard" style="background: linear-gradient(135deg, rgba(60, 141, 188, 0.1) 0%, rgba(45, 95, 126, 0.1) 100%); width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-user-cog" style="color: #3c8dbc; font-size: 1.5rem;"></i>
                                </div>  
                            </div>
                        </div>  
                        <a href="<?=APP_URL;?>/admin/usuarios" class="card-footer custom-btn text-white text-center" style="text-decoration: none; display: block; padding: 0.75rem;">  
                            Ver todos <i class="fas fa-arrow-circle-right ml-1"></i>  
                        </a>  
                    </div>  
                </div>  

                 <!-- Tarjeta 5: Inscripciones Activas CON MARGEN -->
        <div class="col-lg-3 col-6 mb-4 margen-dashboard">  
            <div class="card card-dashboard" style="border-radius: 10px; overflow: hidden; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); border: none; height: 100%;">  
                <div class="card-body" style="padding: 1.5rem; position: relative;">  
                    <div class="image-background">
                        <img src="<?=APP_URL;?>" alt="Inscripciones" style="width: 80px; height: 80px; opacity: 0.1;">
                    </div>
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <?php  
                            $inscripciones = $id_gestion_activa ? getInscripcionesByGestion($pdo, $id_gestion_activa) : [];  
                            $contador_inscripciones = count($inscripciones);  
                            ?>  
                            <h3 style="color: #3c8dbc; font-weight: 700; margin-bottom: 0.5rem;"><?=$contador_inscripciones;?></h3>  
                            <p class="text-muted mb-2">Inscripciones activas</p>
                            <span class="badge badge-success">Periodo actual</span>
                        </div>  
                        <div class="icon-dashboard" style="background: linear-gradient(135deg, rgba(60, 141, 188, 0.1) 0%, rgba(45, 95, 126, 0.1) 100%); width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-clipboard-list" style="color: #3c8dbc; font-size: 1.5rem;"></i>  
                        </div>  
                    </div>
                </div>  
                <a href="<?=APP_URL;?>/admin/estudiantes/Lista_de_inscripcion.php" class="card-footer custom-btn text-white text-center" style="text-decoration: none; display: block; padding: 0.75rem;">  
                    Gestionar <i class="fas fa-arrow-circle-right ml-1"></i>  
                </a>  
            </div>  
        </div>

                <!-- Tarjeta 6: Estudiantes No Inscritos -->
                <div class="col-lg-3 col-6 mb-4">  
                    <div class="card card-dashboard" style="border-radius: 10px; overflow: hidden; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); border: none; height: 100%;">  
                        <div class="card-body" style="padding: 1.5rem; position: relative;">  
                            <div class="image-background">
                                <img src="<?=APP_URL;?>" alt="Atención" style="width: 80px; height: 80px; opacity: 0.1;">
                            </div>
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h3 style="color: #3c8dbc; font-weight: 700; margin-bottom: 0.5rem;"><?=$contador_no_inscritos;?></h3>  
                                    <p class="text-muted mb-2">Estudiantes sin inscripción</p>
                                    <span class="badge badge-warning">Requieren atención</span>
                                </div>  
                                <div class="icon-dashboard" style="background: linear-gradient(135deg, rgba(60, 141, 188, 0.1) 0%, rgba(45, 95, 126, 0.1) 100%); width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-exclamation-triangle" style="color: #3c8dbc; font-size: 1.5rem;"></i>  
                                </div>  
                            </div>
                        </div>  
                        <a href="<?=APP_URL;?>/admin/estudiantes" class="card-footer custom-btn text-white text-center" style="text-decoration: none; display: block; padding: 0.75rem;">  
                            Inscribir <i class="fas fa-arrow-circle-right ml-1"></i>  
                        </a>  
                    </div>  
                </div>

                <!-- Tarjeta 7: Grados -->
                <div class="col-lg-3 col-6 mb-4">   
                    <div class="card card-dashboard" style="border-radius: 10px; overflow: hidden; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); border: none; height: 100%;">  
                        <div class="card-body" style="padding: 1.5rem; position: relative;">  
                            <div class="image-background">
                                <img src="<?=APP_URL;?>" alt="Grados" style="width: 80px; height: 80px; opacity: 0.1;">
                            </div>
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <?php  
                                    $contador_grados = count($grados);  
                                    ?>  
                                    <h3 style="color: #3c8dbc; font-weight: 700; margin-bottom: 0.5rem;"><?=$contador_grados;?></h3>  
                                    <p class="text-muted mb-2">Años/Grados activos</p>
                                    <small class="text-muted">Configurados en sistema</small>
                                </div>  
                                <div class="icon-dashboard" style="background: linear-gradient(135deg, rgba(60, 141, 188, 0.1) 0%, rgba(45, 95, 126, 0.1) 100%); width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-graduation-cap" style="color: #3c8dbc; font-size: 1.5rem;"></i>  
                                </div>  
                            </div>
                        </div>  
                        <a href="<?=APP_URL;?>/admin/configuraciones/grados" class="card-footer custom-btn text-white text-center" style="text-decoration: none; display: block; padding: 0.75rem;">  
                            Configurar <i class="fas fa-arrow-circle-right ml-1"></i>  
                        </a>  
                    </div>  
                </div>  

                <!-- Tarjeta 8: Secciones -->
                <div class="col-lg-3 col-6 mb-4">   
                    <div class="card card-dashboard" style="border-radius: 10px; overflow: hidden; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); border: none; height: 100%;">  
                        <div class="card-body" style="padding: 1.5rem; position: relative;">  
                            <div class="image-background">
                                <img src="<?=APP_URL;?>" alt="Secciones" style="width: 80px; height: 80px; opacity: 0.1;">
                            </div>
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <?php  
                                    $contador_secciones = count($secciones);  
                                    ?>  
                                    <h3 style="color: #3c8dbc; font-weight: 700; margin-bottom: 0.5rem;"><?=$contador_secciones;?></h3>  
                                    <p class="text-muted mb-2">Secciones activas</p>
                                    <small class="text-muted">Disponibles para inscripción</small>
                                </div>  
                                <div class="icon-dashboard" style="background: linear-gradient(135deg, rgba(60, 141, 188, 0.1) 0%, rgba(45, 95, 126, 0.1) 100%); width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-chalkboard" style="color: #3c8dbc; font-size: 1.5rem;"></i>  
                                </div>  
                            </div>
                        </div>  
                        <a href="<?=APP_URL;?>/admin/configuraciones/secciones" class="card-footer custom-btn text-white text-center" style="text-decoration: none; display: block; padding: 0.75rem;">  
                            Configurar <i class="fas fa-arrow-circle-right ml-1"></i>  
                        </a>  
                    </div>  
                </div>  
            </div>  
            <hr>
                
            <!-- Nueva Sección: Alertas del Sistema -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card" style="border-radius: 10px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); border: none;">
                        <div class="card-header bg-white" style="border-bottom: 1px solid #eaeaea; border-radius: 10px 10px 0 0 !important;">
                            <h5 class="card-title mb-0" style="color: #3c8dbc; font-weight: 600;">
                                <i class="fas fa-bell mr-2"></i>Alertas del Sistema
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush">
                                <a href="<?=APP_URL;?>/admin/estudiantes" class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">Inscripciones pendientes</h6>
                                        <small class="text-muted">Hoy</small>
                                    </div>
                                    <p class="mb-1"><?=$contador_no_inscritos?> estudiantes registrados pero no inscritos</p>
                                    <small class="text-primary">Inscribir ahora</small>
                                </a>
                                <a href="<?=APP_URL;?>/admin/estudiantes" class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">Documentación incompleta</h6>
                                        <small class="text-muted">Revisar</small>
                                    </div>
                                    <p class="mb-1">Verificar documentos de estudiantes recién registrados</p>
                                    <small class="text-warning">Revisar documentos</small>
                                </a>
                                <a href="<?=APP_URL;?>/admin/representantes" class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">Representantes por verificar</h6>
                                        <small class="text-muted">Pendiente</small>
                                    </div>
                                    <p class="mb-1">Validar información de representantes registrados</p>
                                    <small class="text-info">Verificar datos</small>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <hr>
            
            <!-- Sección de Gráficos Mejorada -->
            <div class="row">
                <!-- Gráfico de estudiantes registrados -->
                <div class="col-md-8">
                    <div class="card card-outline card-primary">
                        <div class="card-header" style="background: linear-gradient(135deg, #3c8dbc 0%, #2d5f7e 100%);">
                            <h3 class="card-title" style="color: white; margin: 0;">
                                <i class="fas fa-chart-bar mr-2"></i>Estudiantes Registrados por Mes
                            </h3>
                        </div>
                        <div class="card-body">
                            <div>
                                <canvas id="myChart2"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Nuevo Gráfico: Inscripciones por Año/Grado -->
                <div class="col-md-4">
                    <div class="card card-outline card-primary">
                        <div class="card-header" style="background: linear-gradient(135deg, #3c8dbc 0%, #2d5f7e 100%);">
                            <h3 class="card-title" style="color: white; margin: 0;">
                                <i class="fas fa-chart-pie mr-2"></i>Inscripciones por Año
                            </h3>
                        </div>
                        <div class="card-body">
                            <div>
                                <canvas id="inscripcionesGradoChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>
        <!-- /.row -->
    </div><!-- /.container-fluid -->
</div>
<!-- /.content -->

<?php
// Datos para el gráfico de estudiantes por mes
$enero = 0; $febrero = 0; $marzo = 0; $abril = 0; $mayo = 0; $junio = 0; $julio = 0;
$agosto = 0; $septiembre = 0; $octubre = 0; $noviembre = 0; $diciembre = 0;
foreach ($reportes_estudiantes as $reportes_estudiante){
    $fecha = $reportes_estudiante['created_at'];
    $fecha = strtotime($fecha);
    $mes = date("m",$fecha);
    if($mes == "01") $enero = $enero + 1;
    if($mes == "02") $febrero = $febrero + 1;
    if($mes == "03") $marzo = $marzo + 1;
    if($mes == "04") $abril = $abril + 1;
    if($mes == "05") $mayo = $mayo + 1;
    if($mes == "06") $junio = $junio + 1;
    if($mes == "07") $julio = $julio + 1;
    if($mes == "08") $agosto = $agosto + 1;
    if($mes == "09") $septiembre = $septiembre + 1;
    if($mes == "10") $octubre = $octubre + 1;
    if($mes == "11") $noviembre = $noviembre + 1;
    if($mes == "12") $diciembre = $diciembre + 1;
}
$reporte_meses = $enero.",".$febrero.",".$marzo.",".$abril.",".$mayo.",".$junio.",".$julio.",".$agosto.",".$septiembre.",".$octubre.",".$noviembre.",".$diciembre;
?>

<script>
    // Gráfico de estudiantes por mes
    var meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio','Julio',
        'Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
    var datos =[<?=$reporte_meses;?>];
    const ctx2 = document.getElementById('myChart2');
    new Chart(ctx2, {
        type: 'bar',
        data: {
            labels: meses,
            datasets: [{
                label: 'Estudiantes registrados',
                data: datos,
                borderWidth: 2,
                backgroundColor: 'rgba(60, 141, 188, 0.7)',
                borderColor: 'rgba(60, 141, 188, 1)',
                borderRadius: 5,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        drawBorder: false
                    },
                    ticks: {
                        precision: 0
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.7)',
                    titleFont: {
                        size: 14
                    },
                    bodyFont: {
                        size: 13
                    },
                    padding: 10,
                    cornerRadius: 5
                }
            }
        }
    });
    
    // Gráfico de inscripciones por año/grado - 
    const inscripcionesGradoCtx = document.getElementById('inscripcionesGradoChart');
    <?php if(count($inscripciones_por_grado) > 0): ?>
    new Chart(inscripcionesGradoCtx, {
        type: 'doughnut',
        data: {
           labels: [
<?php 
foreach($inscripciones_por_grado as $grado) {
    echo "'" . $grado['grado'] . "',";
}
?>
],
            datasets: [{
                data: [
                    <?php 
                    foreach($inscripciones_por_grado as $grado) {
                        echo $grado['total'] . ",";
                    }
                    ?>
                ],
                backgroundColor: [
                    'rgba(60, 141, 188, 0.8)',
                    'rgba(60, 188, 141, 0.8)',
                    'rgba(188, 141, 60, 0.8)',
                    'rgba(141, 60, 188, 0.8)',
                    'rgba(188, 60, 141, 0.8)',
                    'rgba(60, 188, 188, 0.8)',
                    'rgba(141, 188, 60, 0.8)',
                    'rgba(188, 60, 188, 0.8)'
                ],
                borderWidth: 1,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true,
                    }
                }
            },
            cutout: '60%'
        }
    });
    <?php else: ?>
    // Mostrar mensaje si no hay datos
    inscripcionesGradoCtx.getContext('2d').font = '16px Arial';
    inscripcionesGradoCtx.getContext('2d').fillStyle = '#6c757d';
    inscripcionesGradoCtx.getContext('2d').textAlign = 'center';
    inscripcionesGradoCtx.getContext('2d').fillText('No hay datos disponibles', inscripcionesGradoCtx.width/2, inscripcionesGradoCtx.height/2);
    <?php endif; ?>
</script>

<?php
include ('../admin/layout/parte2.php');
include ('../layout/mensajes.php');
?>

<style>
    /* Estilos adicionales para mejorar la apariencia */
    .card-dashboard {
        transition: all 0.3s ease;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        border: none;
        height: 100%;
    }
    
    .icon-dashboard {
        transition: all 0.3s ease;
    }
    
    .card-dashboard:hover .icon-dashboard {
        transform: scale(1.1);
    }
    
    .list-group-item {
        border: none;
        border-bottom: 1px solid #eaeaea;
        transition: all 0.2s ease;
    }
    
    .list-group-item:hover {
        background-color: #f8f9fa;
    }
    
    /* Nuevos estilos para botones azul más oscuro */
    .card-footer.custom-btn {
        background-color: #9ec5fe !important;
        color: #052c65 !important;
        border: none;
        transition: all 0.3s ease;
        font-weight: 600;
    }
    
    .card-footer.custom-btn:hover {
        background-color: #6ea8fe !important;
        color: #031633 !important;
    }
    
    /* Imágenes de fondo transparentes */
    .image-background {
        position: absolute;
        bottom: 10px;
        right: 10px;
        opacity: 0.08;
        z-index: 0;
    }
    
    .card-body {
        position: relative;
        z-index: 1;
    }
    
    /* Colores Bootstrap para badges */
    .badge-success {
        background-color: #28a745;
    }
    
    .badge-danger {
        background-color: #dc3545;
    }
    
    .badge-warning {
        background-color: #ffc107;
        color: #212529;
    }
    
    .badge-info {
        background-color: #17a2b8;
    }
    
    /* ESTILOS PARA LA VISTA DE DOCENTES */
    .docente-welcome {
        background: linear-gradient(135deg, #3c8dbc 0%, #2d5f7e 100%);
        color: white;
        padding: 2rem;
        border-radius: 10px;
        margin-bottom: 2rem;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }
    
    .docente-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .stat-card {
        background: white;
        padding: 1.5rem;
        border-radius: 10px;
        text-align: center;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        border: 1px solid #eaeaea;
        transition: all 0.3s ease;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }
    
    .stat-card.info {
        border-top: 4px solid #3c8dbc;
    }
    
    .stat-card i {
        font-size: 2.5rem;
        color: #3c8dbc;
        margin-bottom: 1rem;
    }
    
    .stat-number {
        font-size: 2rem;
        font-weight: 700;
        color: #3c8dbc;
        margin-bottom: 0.5rem;
    }
    
    .stat-label {
        color: #6c757d;
        font-weight: 500;
    }
    
    .docente-dashboard {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .activities-card {
        background: white;
        padding: 1.5rem;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        border: 1px solid #eaeaea;
    }
    
    .activities-card h5 {
        color: #3c8dbc;
        font-weight: 600;
        margin-bottom: 1rem;
    }
    
    @media (max-width: 768px) {
        .docente-dashboard {
            grid-template-columns: 1fr;
        }
        
        .docente-stats {
            grid-template-columns: 1fr;
        }
    }
</style>

<script>
    $(function () {
        $('.knob').knob({
            draw: function () {
                if (this.$.data('skin') == 'tron') {
                    var a   = this.angle(this.cv)  
                        ,
                        sa  = this.startAngle          
                        ,
                        sat = this.startAngle         
                        ,
                        ea                           
                        eat = sat + a                
                        ,
                        r   = true

                    this.g.lineWidth = this.lineWidth

                    this.o.cursor
                    && (sat = eat - 0.3)
                    && (eat = eat + 0.3)

                    if (this.o.displayPrevious) {
                        ea = this.startAngle + this.angle(this.value)
                        this.o.cursor
                        && (sa = ea - 0.3)
                        && (ea = ea + 0.3)
                        this.g.beginPath()
                        this.g.strokeStyle = this.previousColor
                        this.g.arc(this.xy, this.xy, this.radius - this.lineWidth, sa, ea, false)
                        this.g.stroke()
                    }

                    this.g.beginPath()
                    this.g.strokeStyle = r ? this.o.fgColor : this.fgColor
                    this.g.arc(this.xy, this.xy, this.radius - this.lineWidth, sat, eat, false)
                    this.g.stroke()

                    this.g.lineWidth = 2
                    this.g.beginPath()
                    this.g.strokeStyle = this.o.fgColor
                    this.g.arc(this.xy, this.xy, this.radius - this.lineWidth + 1 + this.lineWidth * 2 / 3, 0, 2 * Math.PI, false)
                    this.g.stroke()

                    return false
                }
            }
        })
    });
</script>

<script>
        window.onload = () => {
            setTimeout(() => {
                document.getElementsByTagName('body')[0].style.backgroundColor = 'white';
            }, 3000);
        }
        function refrescarPagina() {
            location.reload();
        }
    </script>