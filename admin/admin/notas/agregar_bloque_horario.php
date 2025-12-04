<?php
include('../../app/config.php');
if (session_status() === PHP_SESSION_NONE) session_start();

header('Content-Type: application/json');

try {
    $id_horario = isset($_POST['id_horario']) ? (int)$_POST['id_horario'] : 0;
    $dia_semana = isset($_POST['dia_semana']) ? trim($_POST['dia_semana']) : '';
    $hora_inicio = isset($_POST['hora_inicio']) ? trim($_POST['hora_inicio']) : '';
    $hora_fin = isset($_POST['hora_fin']) ? trim($_POST['hora_fin']) : '';
    $id_materia = isset($_POST['id_materia']) ? (int)$_POST['id_materia'] : 0;
    $id_profesor = isset($_POST['id_profesor']) && $_POST['id_profesor'] !== '' ? (int)$_POST['id_profesor'] : null;
    
    if ($id_horario <= 0 || empty($dia_semana) || empty($hora_inicio) || empty($hora_fin) || $id_materia <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Datos incompletos'
        ]);
        exit;
    }
    
    // Normalizar horas al formato TIME completo (HH:MM:SS)
    if (strlen($hora_inicio) == 5) {
        $hora_inicio .= ':00';
    }
    if (strlen($hora_fin) == 5) {
        $hora_fin .= ':00';
    }
    
    // Verificar si ya existe un bloque en ese día y hora en el mismo horario
    $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM horario_detalle 
                                WHERE id_horario = ? AND dia_semana = ? AND hora_inicio = ?");
    $stmtCheck->execute([$id_horario, $dia_semana, $hora_inicio]);
    $existe = (int)$stmtCheck->fetchColumn();
    
    if ($existe > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Ya existe un bloque en este día y hora'
        ]);
        exit;
    }
    
    // Validar conflicto de profesor: verificar si el profesor ya está asignado en otro horario (otro grado/sección) a la misma hora
    if ($id_profesor !== null && $id_profesor > 0) {
        $stmtConflicto = $pdo->prepare("SELECT hd.id_detalle, h.id_horario, h.id_grado, h.id_seccion, g.grado AS nombre_grado, s.nombre_seccion, hd.dia_semana, hd.hora_inicio, hd.hora_fin
                                        FROM horario_detalle hd
                                        INNER JOIN horarios h ON h.id_horario = hd.id_horario
                                        INNER JOIN grados g ON g.id_grado = h.id_grado
                                        INNER JOIN secciones s ON s.id_seccion = h.id_seccion
                                        WHERE hd.id_profesor = ? 
                                          AND hd.dia_semana = ?
                                          AND hd.id_horario != ?
                                          AND (
                                            (hd.hora_inicio <= ? AND hd.hora_fin > ?) OR
                                            (hd.hora_inicio < ? AND hd.hora_fin >= ?) OR
                                            (hd.hora_inicio >= ? AND hd.hora_fin <= ?)
                                          )");
        $stmtConflicto->execute([
            $id_profesor,
            $dia_semana,
            $id_horario,
            $hora_inicio, $hora_inicio,  // Para solapamiento inicio
            $hora_fin, $hora_fin,        // Para solapamiento fin
            $hora_inicio, $hora_fin      // Para contenido dentro
        ]);
        $conflicto = $stmtConflicto->fetch(PDO::FETCH_ASSOC);
        
        if ($conflicto) {
            // Obtener nombre del profesor
            $stmtProf = $pdo->prepare("SELECT CONCAT(nombres, ' ', apellidos) AS nombre_completo FROM profesores WHERE id_profesor = ?");
            $stmtProf->execute([$id_profesor]);
            $profesor = $stmtProf->fetch(PDO::FETCH_ASSOC);
            $nombreProfesor = $profesor ? $profesor['nombre_completo'] : 'El profesor';
            
            echo json_encode([
                'success' => false,
                'message' => 'El profesor ' . $nombreProfesor . ' ya está asignado en ' . $conflicto['nombre_grado'] . ' - Sección ' . $conflicto['nombre_seccion'] . ' el mismo día y hora. No puede tener dos clases simultáneas.'
            ]);
            exit;
        }
    }
    
    // Insertar nuevo bloque
    $stmt = $pdo->prepare("INSERT INTO horario_detalle 
                          (id_horario, dia_semana, hora_inicio, hora_fin, id_materia, id_profesor) 
                          VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $id_horario,
        $dia_semana,
        $hora_inicio,
        $hora_fin,
        $id_materia,
        $id_profesor
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Bloque agregado correctamente'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>

