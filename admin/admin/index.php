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

// FUNCIÓN PARA OBTENER HORARIO SEMANAL DEL PROFESOR
function getHorarioProfesor($pdo, $id_profesor, $id_gestion) {
    try {
        // Obtener las materias y secciones asignadas al profesor
        $sql_asignaciones = "SELECT DISTINCT ap.id_materia, ap.id_seccion, 
                            m.nombre_materia, s.nombre_seccion, g.grado
                            FROM asignaciones_profesor ap
                            JOIN materias m ON ap.id_materia = m.id_materia
                            JOIN secciones s ON ap.id_seccion = s.id_seccion
                            JOIN grados g ON s.id_grado = g.id_grado
                            WHERE ap.id_profesor = :id_profesor 
                            AND ap.estado = 1
                            AND ap.id_gestion = :id_gestion";
        
        $stmt = $pdo->prepare($sql_asignaciones);
        $stmt->bindParam(':id_profesor', $id_profesor, PDO::PARAM_INT);
        $stmt->bindParam(':id_gestion', $id_gestion, PDO::PARAM_INT);
        $stmt->execute();
        $asignaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($asignaciones)) {
            return [];
        }
        
        $horario_completo = [];
        
        // Para cada asignación, buscar el horario en la tabla horarios
        foreach ($asignaciones as $asignacion) {
            $id_seccion = $asignacion['id_seccion'];
            $id_grado = $asignacion['id_grado'] ?? null;
            
            // Buscar horarios para esta sección en la gestión actual
            $sql_horario = "SELECT h.*, g.grado, s.nombre_seccion,
                           DATE_FORMAT(h.fecha_inicio, '%H:%i') as hora_inicio_24,
                           DATE_FORMAT(h.fecha_inicio, '%h:%i %p') as hora_inicio_12,
                           DATE_FORMAT(h.fecha_fin, '%H:%i') as hora_fin_24,
                           DATE_FORMAT(h.fecha_fin, '%h:%i %p') as hora_fin_12,
                           DAYOFWEEK(h.fecha_inicio) as dia_numero,
                           DAYNAME(h.fecha_inicio) as dia_nombre
                           FROM horarios h
                           JOIN grados g ON h.id_grado = g.id_grado
                           JOIN secciones s ON h.id_seccion = s.id_seccion
                           WHERE h.id_seccion = :id_seccion 
                           AND h.id_gestion = :id_gestion
                           AND h.estado = 1
                           ORDER BY FIELD(DAYOFWEEK(h.fecha_inicio), 2,3,4,5,6,7,1), h.fecha_inicio";
            
            $stmt = $pdo->prepare($sql_horario);
            $stmt->bindParam(':id_seccion', $id_seccion, PDO::PARAM_INT);
            $stmt->bindParam(':id_gestion', $id_gestion, PDO::PARAM_INT);
            $stmt->execute();
            $horarios_seccion = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($horarios_seccion)) {
                foreach ($horarios_seccion as $horario) {
                    // Mapear número de día a nombre en español
                    $dias_ingles_espanol = [
                        'Monday' => 'Lunes',
                        'Tuesday' => 'Martes',
                        'Wednesday' => 'Miércoles',
                        'Thursday' => 'Jueves',
                        'Friday' => 'Viernes',
                        'Saturday' => 'Sábado',
                        'Sunday' => 'Domingo'
                    ];
                    
                    $dia_nombre_ingles = $horario['dia_nombre'];
                    $dia_nombre = $dias_ingles_espanol[$dia_nombre_ingles] ?? $dia_nombre_ingles;
                    $dia_numero = $horario['dia_numero'];
                    
                    // Ajustar para que Lunes sea 1, Martes 2, etc.
                    $dia_ajustado = ($dia_numero == 1) ? 7 : $dia_numero - 1;
                    
                    $horario_completo[] = [
                        'seccion' => $asignacion['nombre_seccion'],
                        'grado' => $asignacion['grado'],
                        'materia' => $asignacion['nombre_materia'],
                        'dia_nombre' => $dia_nombre,
                        'dia_numero' => $dia_ajustado,
                        'hora_inicio_24' => $horario['hora_inicio_24'],
                        'hora_inicio_12' => $horario['hora_inicio_12'],
                        'hora_fin_24' => $horario['hora_fin_24'],
                        'hora_fin_12' => $horario['hora_fin_12'],
                        'aula' => $horario['aula'] ?? 'Sin aula',
                        'fecha_inicio' => $horario['fecha_inicio'],
                        'fecha_fin' => $horario['fecha_fin']
                    ];
                }
            }
        }
        
        return $horario_completo;
        
    } catch (PDOException $e) {
        error_log("Error en getHorarioProfesor: " . $e->getMessage());
        return [];
    }
}

// FUNCIÓN PARA OBTENER HORARIO POR DÍA
function getHorarioPorDia($horario_completo) {
    $dias_ordenados = [
        1 => 'Lunes',
        2 => 'Martes',
        3 => 'Miércoles',
        4 => 'Jueves',
        5 => 'Viernes',
        6 => 'Sábado',
        7 => 'Domingo'
    ];
    
    $horario_por_dia = [];
    
    // Inicializar todos los días
    foreach ($dias_ordenados as $num => $nombre) {
        $horario_por_dia[$num] = [
            'nombre' => $nombre,
            'clases' => []
        ];
    }
    
    // Agrupar clases por día
    foreach ($horario_completo as $horario) {
        $dia_num = $horario['dia_numero'];
        
        if (isset($horario_por_dia[$dia_num])) {
            $horario_por_dia[$dia_num]['clases'][] = [
                'seccion' => $horario['seccion'],
                'grado' => $horario['grado'],
                'materia' => $horario['materia'],
                'hora_inicio_24' => $horario['hora_inicio_24'],
                'hora_inicio_12' => $horario['hora_inicio_12'],
                'hora_fin_24' => $horario['hora_fin_24'],
                'hora_fin_12' => $horario['hora_fin_12'],
                'aula' => $horario['aula']
            ];
        }
    }
    
    // Ordenar clases por hora dentro de cada día
    foreach ($horario_por_dia as &$dia) {
        usort($dia['clases'], function($a, $b) {
            return strcmp($a['hora_inicio_24'], $b['hora_inicio_24']);
        });
    }
    
    return $horario_por_dia;
}

// CONSULTAS ESPECÍFICAS PARA DOCENTES
function getDatosDocente($pdo, $id_usuario_sesion) {
    if (!$id_usuario_sesion) {
        return ['nombre' => 'Docente', 'error' => 'ID usuario vacío'];
    }
    
    try {
        $sql_usuario = "SELECT email, id_usuario FROM usuarios WHERE id_usuario = :id_usuario AND estado = 1";
        $stmt = $pdo->prepare($sql_usuario);
        $stmt->bindParam(':id_usuario', $id_usuario_sesion, PDO::PARAM_INT);
        $stmt->execute();
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$usuario || empty($usuario['email'])) {
            return ['nombre' => 'Docente'];
        }
        
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

// Obtener cursos asignados al docente
function getCursosDocente($pdo, $id_profesor) {
    if (!$id_profesor) {
        return ['total_cursos' => 0, 'detalle' => []];
    }
    
    try {
        $sql_gestion = "SELECT id_gestion FROM gestiones WHERE estado = 1 LIMIT 1";
        $stmt = $pdo->prepare($sql_gestion);
        $stmt->execute();
        $gestion = $stmt->fetch(PDO::FETCH_ASSOC);
        $id_gestion_activa = $gestion ? $gestion['id_gestion'] : 0;
        
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
        
        $sql_detalle = "SELECT DISTINCT a.id_materia, m.nombre_materia, a.id_seccion, 
                        s.nombre_seccion, g.grado
                       FROM asignaciones_profesor a
                       LEFT JOIN materias m ON a.id_materia = m.id_materia
                       LEFT JOIN secciones s ON a.id_seccion = s.id_seccion
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

// Obtener total de estudiantes del docente
function getEstudiantesDocente($pdo, $id_profesor) {
    if (!$id_profesor) {
        return ['total_estudiantes' => 0];
    }
    
    try {
        $sql_gestion = "SELECT id_gestion FROM gestiones WHERE estado = 1 LIMIT 1";
        $stmt = $pdo->prepare($sql_gestion);
        $stmt->execute();
        $gestion = $stmt->fetch(PDO::FETCH_ASSOC);
        $id_gestion_activa = $gestion ? $gestion['id_gestion'] : 0;
        
        if (!$id_gestion_activa) {
            return ['total_estudiantes' => 0];
        }
        
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
        
        return ['total_estudiantes' => $result['total_estudiantes'] ?? 0];
        
    } catch (PDOException $e) {
        error_log("Error en getEstudiantesDocente: " . $e->getMessage());
        return ['total_estudiantes' => 0];
    }
}

// Obtener calificaciones pendientes del docente
function getCalificacionesPendientes($pdo, $id_profesor) {
    if (!$id_profesor) {
        return ['pendientes' => 0, 'detalle' => [], 'mensaje' => 'Profesor no identificado'];
    }
    
    try {
        $sql_gestion = "SELECT id_gestion FROM gestiones WHERE estado = 1 LIMIT 1";
        $stmt = $pdo->prepare($sql_gestion);
        $stmt->execute();
        $gestion = $stmt->fetch(PDO::FETCH_ASSOC);
        $id_gestion_activa = $gestion ? $gestion['id_gestion'] : 0;
        
        if (!$id_gestion_activa) {
            return ['pendientes' => 0, 'detalle' => [], 'mensaje' => 'No hay período activo'];
        }
        
        $lapso_info = getLapsoActualSecuencial($pdo, $id_profesor, $id_gestion_activa);
        $id_lapso_actual = $lapso_info['id_lapso'];
        $nombre_lapso = $lapso_info['nombre_lapso'];
        
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

// Consultas seguras (solo para tablas que existen)
$gestion_activa = getPeriodoEscolarActivo($pdo);
$id_gestion_activa = $gestion_activa ? $gestion_activa['id_gestion'] : null;

// Obtener datos específicos para docentes
if ($rol_sesion_usuario == "DOCENTE") {
    // Obtener ID del usuario de la sesión
    $id_usuario_sesion = $_SESSION['id_usuario'] ?? $_SESSION['id'] ?? $_SESSION['user_id'] ?? $_SESSION['usuario_id'] ?? 0;
    
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
    
    // Si no se encontró profesor, buscar por nombre (Heldyn/Saned)
    if (!$id_profesor) {
        $sql_check = "SELECT id_profesor FROM profesores WHERE nombres LIKE '%Heldyn%' OR nombres LIKE '%Saned%' LIMIT 1";
        $stmt = $pdo->prepare($sql_check);
        $stmt->execute();
        $prof_check = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($prof_check) {
            $id_profesor = $prof_check['id_profesor'];
            $sql_prof = "SELECT * FROM profesores WHERE id_profesor = :id_profesor";
            $stmt = $pdo->prepare($sql_prof);
            $stmt->bindParam(':id_profesor', $id_profesor, PDO::PARAM_INT);
            $stmt->execute();
            $prof_data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $datos_docente = [
                'nombre' => $prof_data['nombres'] . ' ' . $prof_data['apellidos'],
                'email' => $prof_data['email'],
                'id_profesor' => $prof_data['id_profesor'],
                'cedula' => $prof_data['cedula'] ?? ''
            ];
        } else {
            $id_profesor = 3;
            $datos_docente = [
                'nombre' => 'Heldyn David Diaz Daboin',
                'id_profesor' => 3
            ];
        }
    }
    
    if ($id_profesor) {
        // Obtener estadísticas
        $cursos_docente = getCursosDocente($pdo, $id_profesor);
        $estudiantes_docente = getEstudiantesDocente($pdo, $id_profesor);
        $calificaciones_pendientes = getCalificacionesPendientes($pdo, $id_profesor);
        
        // Obtener horario del profesor
        $horario_profesor = getHorarioProfesor($pdo, $id_profesor, $id_gestion_activa);
        $horario_por_dia = getHorarioPorDia($horario_profesor);
    } else {
        $cursos_docente = ['total_cursos' => 0, 'detalle' => []];
        $estudiantes_docente = ['total_estudiantes' => 0];
        $calificaciones_pendientes = ['pendientes' => 0, 'detalle' => [], 'mensaje' => 'Profesor no encontrado'];
        $horario_profesor = [];
        $horario_por_dia = [];
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
            <!-- Bienvenida con fondo azul -->
            <div class="card mb-4">
                <div class="card-body" style="background: linear-gradient(135deg, #0066cc 0%, #004d99 100%); color: white; border-radius: 8px;">
                    <div class="row">
                        <div class="col-md-8">
                            <h3 class="mb-2">Bienvenido, Prof. <?php echo htmlspecialchars($datos_docente['nombre'] ?? 'Docente'); ?></h3>
                            <p class="mb-0" style="opacity: 0.9;">Es un placer tenerle de vuelta. Aquí tiene un resumen de sus actividades.</p>
                        </div>
                        <div class="col-md-4 text-right">
                            <i class="fas fa-chalkboard-teacher fa-3x" style="opacity: 0.8;"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Estadísticas principales - TODAS CON FRANJA AZUL -->
            <div class="row mb-4">
                <!-- Cursos Asignados - CON FRANJA AZUL -->
                <div class="col-md-4 mb-3">
                    <div class="card h-100">
                        <div class="card-header" style="background: linear-gradient(135deg, #0066cc 0%, #004d99 100%); color: white; padding: 12px;">
                            <h5 class="card-title mb-0" style="font-size: 1rem;">
                                <i class="fas fa-book mr-1"></i>Cursos Asignados
                            </h5>
                        </div>
                        <div class="card-body p-3 text-center">
                            <h2 style="font-size: 2.5rem; font-weight: bold; margin-bottom: 10px;"><?php echo $cursos_docente['total_cursos'] ?? 0; ?></h2>
                            <div>
                                <?php if (($cursos_docente['total_cursos'] ?? 0) == 0): ?>
                                    <span class="badge badge-warning">Sin asignaciones</span>
                                <?php else: ?>
                                    <span class="badge badge-success">Activas</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Estudiantes - CON FRANJA AZUL -->
                <div class="col-md-4 mb-3">
                    <div class="card h-100">
                        <div class="card-header" style="background: linear-gradient(135deg, #0066cc 0%, #004d99 100%); color: white; padding: 12px;">
                            <h5 class="card-title mb-0" style="font-size: 1rem;">
                                <i class="fas fa-user-graduate mr-1"></i>Estudiantes
                            </h5>
                        </div>
                        <div class="card-body p-3 text-center">
                            <h2 style="font-size: 2.5rem; font-weight: bold; margin-bottom: 10px;"><?php echo $estudiantes_docente['total_estudiantes'] ?? 0; ?></h2>
                            <div>
                                <?php if (($estudiantes_docente['total_estudiantes'] ?? 0) == 0): ?>
                                    <span class="badge badge-info">Sin estudiantes</span>
                                <?php else: ?>
                                    <span style="color: #0066cc;">
                                        <i class="fas fa-users mr-1"></i>
                                        <?php echo $estudiantes_docente['total_estudiantes']; ?> estudiantes
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Calificaciones Pendientes - CON FRANJA AZUL -->
                <div class="col-md-4 mb-3">
                    <div class="card h-100">
                        <div class="card-header" style="background: linear-gradient(135deg, #0066cc 0%, #004d99 100%); color: white; padding: 12px;">
                            <h5 class="card-title mb-0" style="font-size: 1rem;">
                                <i class="fas fa-tasks mr-1"></i>Calificaciones Pendientes
                            </h5>
                        </div>
                        <div class="card-body p-3 text-center">
                            <h2 style="font-size: 2.5rem; font-weight: bold; margin-bottom: 10px;"><?php echo $calificaciones_pendientes['pendientes'] ?? 0; ?></h2>
                            <div>
                                <?php if (($calificaciones_pendientes['pendientes'] ?? 0) > 0): ?>
                                    <span class="badge badge-danger">Requiere atención</span>
                                <?php else: ?>
                                    <span class="badge badge-success">Al día</span>
                                <?php endif; ?>
                            </div>
                            <?php if (isset($calificaciones_pendientes['nombre_lapso'])): ?>
                                <small class="text-muted d-block mt-2">
                                    Lapso: <?php echo $calificaciones_pendientes['nombre_lapso']; ?>
                                </small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Horario Semanal del Profesor -->
            <?php if (!empty($horario_por_dia)): ?>
            <div class="card mb-4">
                <div class="card-header" style="background: linear-gradient(135deg, #0066cc 0%, #004d99 100%); color: white; padding: 12px;">
                    <h5 class="card-title mb-0" style="font-size: 1.1rem;">
                        <i class="fas fa-calendar-alt mr-2"></i>Horario Semanal
                    </h5>
                </div>
                <div class="card-body p-3">
                    <div class="row">
                        <?php 
                        $dias_con_clases = 0;
                        foreach ($horario_por_dia as $dia_num => $dia_data): 
                            if (!empty($dia_data['clases'])): 
                                $dias_con_clases++;
                        ?>
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="card border h-100">
                                <div class="card-header bg-light py-2">
                                    <h6 class="card-title mb-0" style="font-size: 0.95rem;">
                                        <i class="fas fa-calendar-day mr-1"></i><?php echo $dia_data['nombre']; ?>
                                    </h6>
                                </div>
                                <div class="card-body p-2" style="font-size: 0.85rem;">
                                    <?php foreach ($dia_data['clases'] as $clase): ?>
                                    <div class="border-bottom pb-2 mb-2">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <strong class="text-primary"><?php echo $clase['materia']; ?></strong>
                                            <small class="text-muted text-nowrap ml-2">
                                                <?php echo $clase['hora_inicio_12']; ?> - <?php echo $clase['hora_fin_12']; ?>
                                            </small>
                                        </div>
                                        <div class="mt-1">
                                            <small><i class="fas fa-users mr-1"></i> 
                                                <?php echo $clase['grado']; ?> - <?php echo $clase['seccion']; ?>
                                            </small>
                                        </div>
                                        <div>
                                            <small><i class="fas fa-door-open mr-1"></i> Aula: <?php echo $clase['aula']; ?></small>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <?php 
                            endif;
                        endforeach; 
                        
                        // Mostrar mensaje si no hay clases programadas
                        if ($dias_con_clases == 0): 
                        ?>
                        <div class="col-12">
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-info-circle mr-2"></i>No hay clases programadas para esta semana.
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="card mb-4">
                <div class="card-header" style="background: linear-gradient(135deg, #6c757d 0%, #545b62 100%); color: white; padding: 12px;">
                    <h5 class="card-title mb-0" style="font-size: 1.1rem;">
                        <i class="fas fa-calendar-alt mr-2"></i>Horario Semanal
                    </h5>
                </div>
                <div class="card-body p-3">
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle mr-2"></i>No hay horario asignado para este período.
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Información del Período -->
            <div class="card mb-4">
                <div class="card-header" style="background: linear-gradient(135deg, #0066cc 0%, #004d99 100%); color: white; padding: 12px;">
                    <h5 class="card-title mb-0" style="font-size: 1.1rem;">
                        <i class="fas fa-info-circle mr-2"></i>Información del Período
                    </h5>
                </div>
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 style="font-size: 0.95rem; margin-bottom: 15px;"><i class="fas fa-calendar mr-2"></i>Período Escolar</h6>
                            <?php if ($gestion_activa): ?>
                                <div style="font-size: 0.9rem;">
                                    <p class="mb-2">
                                        <strong>Periodo:</strong> 
                                        <?php  
                                        $año_inicio = date('Y', strtotime($gestion_activa['desde']));  
                                        $año_fin = date('Y', strtotime($gestion_activa['hasta']));  
                                        echo "{$año_inicio}-{$año_fin}";  
                                        ?>
                                    </p>
                                    <p class="mb-2">
                                        <strong>Estado:</strong> 
                                        <span class="badge badge-success">ACTIVO</span>
                                    </p>
                                    <p class="mb-1">
                                        <strong>Inicio:</strong> <?php echo date('d/m/Y', strtotime($gestion_activa['desde'])); ?>
                                    </p>
                                    <p class="mb-0">
                                        <strong>Fin:</strong> <?php echo date('d/m/Y', strtotime($gestion_activa['hasta'])); ?>
                                    </p>
                                </div>
                            <?php else: ?>
                                <p class="mb-0 text-danger" style="font-size: 0.9rem;">
                                    <i class="fas fa-exclamation-triangle mr-1"></i> No hay período activo configurado.
                                </p>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <h6 style="font-size: 0.95rem; margin-bottom: 15px;"><i class="fas fa-chart-line mr-2"></i>Sistema de Calificaciones</h6>
                            <?php if (isset($calificaciones_pendientes['nombre_lapso'])): ?>
                                <div style="font-size: 0.9rem;">
                                    <p class="mb-2">
                                        <strong>Lapso actual:</strong> 
                                        <span class="badge badge-info"><?php echo $calificaciones_pendientes['nombre_lapso']; ?></span>
                                    </p>
                                    <p class="mb-3 text-muted">
                                        <small><?php echo $calificaciones_pendientes['mensaje'] ?? ''; ?></small>
                                    </p>
                                    <?php 
                                    $total_estudiantes = $estudiantes_docente['total_estudiantes'] ?? 1;
                                    $calificadas = $total_estudiantes - ($calificaciones_pendientes['pendientes'] ?? 0);
                                    $porcentaje = $total_estudiantes > 0 ? ($calificadas / $total_estudiantes) * 100 : 0;
                                    ?>
                                    <div class="progress" style="height: 10px; margin-bottom: 5px;">
                                        <div class="progress-bar bg-success" role="progressbar" 
                                             style="width: <?php echo $porcentaje; ?>%" 
                                             aria-valuenow="<?php echo $porcentaje; ?>" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100">
                                        </div>
                                    </div>
                                    <small class="text-muted d-block">
                                        <?php echo $calificadas; ?> de <?php echo $total_estudiantes; ?> calificaciones registradas
                                    </small>
                                </div>
                            <?php else: ?>
                                <p class="mb-0 text-muted" style="font-size: 0.9rem;">
                                    <i class="fas fa-info-circle mr-1"></i> Información de lapsos no disponible
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detalle de Calificaciones Pendientes -->
            <?php if (isset($calificaciones_pendientes['detalle']) && count($calificaciones_pendientes['detalle']) > 0): ?>
            <div class="card mb-4" style="border: 1px solid #ffc107;">
                <div class="card-header" style="background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%); color: #212529; padding: 12px;">
                    <h5 class="card-title mb-0" style="font-size: 1.1rem;">
                        <i class="fas fa-exclamation-triangle mr-2"></i>Calificaciones Pendientes
                        <span class="badge badge-danger float-right" style="font-size: 0.8rem;"><?php echo $calificaciones_pendientes['pendientes']; ?> pendientes</span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 30%; padding: 10px;">Materia</th>
                                    <th style="width: 25%; padding: 10px;">Sección</th>
                                    <th style="width: 20%; padding: 10px;">Pendientes</th>
                                    <th style="width: 25%; padding: 10px;">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($calificaciones_pendientes['detalle'] as $detalle): ?>
                                <tr>
                                    <td style="padding: 10px; vertical-align: middle;"><?php echo htmlspecialchars($detalle['materia']); ?></td>
                                    <td style="padding: 10px; vertical-align: middle;"><?php echo htmlspecialchars($detalle['seccion']); ?> (<?php echo htmlspecialchars($detalle['grado']); ?>)</td>
                                    <td style="padding: 10px; vertical-align: middle;">
                                        <span class="badge badge-danger"><?php echo $detalle['pendientes']; ?> estudiantes</span>
                                    </td>
                                    <td style="padding: 10px; vertical-align: middle;">
                                        <a href="?page=calificaciones&materia=<?php echo $detalle['materia_id']; ?>&seccion=<?php echo $detalle['seccion_id']; ?>&lapso=<?php echo $detalle['lapso']; ?>" 
                                           class="btn btn-sm btn-outline-danger" style="padding: 3px 10px; font-size: 0.85rem;">
                                            <i class="fas fa-edit mr-1"></i> Calificar
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Detalle de Cursos Asignados -->
            <?php if (isset($cursos_docente['detalle']) && count($cursos_docente['detalle']) > 0): ?>
            <div class="card mb-4">
                <div class="card-header" style="background: linear-gradient(135deg, #0066cc 0%, #004d99 100%); color: white; padding: 12px;">
                    <h5 class="card-title mb-0" style="font-size: 1.1rem;">
                        <i class="fas fa-book-open mr-2"></i>Cursos Asignados
                        <span class="badge badge-light float-right" style="font-size: 0.8rem;"><?php echo $cursos_docente['total_cursos']; ?> materias</span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm mb-0">
                            <thead>
                                <tr>
                                    <th style="padding: 10px;">Materia</th>
                                    <th style="padding: 10px;">Sección</th>
                                    <th style="padding: 10px;">Grado</th>
                                    <th style="padding: 10px;">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cursos_docente['detalle'] as $curso): ?>
                                <tr>
                                    <td style="padding: 10px; vertical-align: middle;"><?php echo htmlspecialchars($curso['nombre_materia'] ?? 'N/A'); ?></td>
                                    <td style="padding: 10px; vertical-align: middle;"><?php echo htmlspecialchars($curso['nombre_seccion'] ?? 'N/A'); ?></td>
                                    <td style="padding: 10px; vertical-align: middle;"><?php echo htmlspecialchars($curso['grado'] ?? 'N/A'); ?></td>
                                    <td style="padding: 10px; vertical-align: middle;"><span class="badge badge-success">Activa</span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <style>
            .card {
                border: none;
                border-radius: 8px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            
            .card-header {
                border-radius: 8px 8px 0 0 !important;
            }
            
            .badge {
                font-size: 0.75em;
                padding: 0.4em 0.8em;
            }
            
            .table th {
                background-color: #f8f9fa;
                border-bottom: 2px solid #dee2e6;
            }
            
            .table td {
                border-top: 1px solid #dee2e6;
            }
            
            .btn-outline-danger {
                border-color: #dc3545;
                color: #dc3545;
            }
            
            .btn-outline-danger:hover {
                background-color: #dc3545;
                color: white;
            }
            
            h2 {
                font-size: 2.5rem;
                font-weight: bold;
            }
            </style>
            
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
                                    $contador_administrativos = count($administrativos);  
                                    ?>  
                                    <h3 style="color: #3c8dbc; font-weight: 700; margin-bottom: 0.5rem;"><?=$contador_administrativos;?></h3>  
                                    <p class="text-muted mb-2">Administrativos</p>
                                    <small class="text-muted"><?=count($roles)?> roles activos</small>
                                </div>  
                                <div class="icon-dashboard" style="background: linear-gradient(135deg, rgba(60, 141, 188, 0.1) 0%, rgba(45, 95, 126, 0.1) 100%); width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-user-cog" style="color: #3c8dbc; font-size: 1.5rem;"></i>
                                </div>  
                            </div>
                        </div>  
                        <a href="<?=APP_URL;?>/admin/administrativos" class="card-footer custom-btn text-white text-center" style="text-decoration: none; display: block; padding: 0.75rem;">  
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