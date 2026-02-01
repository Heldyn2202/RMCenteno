<?php
include('../../app/config.php');
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['status'=>'error','tipo'=>'error','titulo'=>'Método no permitido','mensaje'=>'Use POST']); 
        exit;
    }

    $id_profesor = isset($_POST['id_profesor']) ? intval($_POST['id_profesor']) : 0;
    $id_secciones = $_POST['id_seccion'] ?? [];
    $id_gestiones = $_POST['id_gestion'] ?? [];
    $materias_por_fila = $_POST['id_materia'] ?? [];

    if (!$id_profesor || !is_array($id_secciones) || !is_array($id_gestiones)) {
        echo json_encode(['status'=>'error','tipo'=>'error','titulo'=>'Datos incompletos','mensaje'=>'Faltan parámetros']); 
        exit;
    }

    // Obtener la gestión activa para verificar
    $stmt_gestion_activa = $pdo->prepare("
        SELECT id_gestion 
        FROM gestiones 
        WHERE estado = 1 
        ORDER BY desde DESC 
        LIMIT 1
    ");
    $stmt_gestion_activa->execute();
    $gestion_activa = $stmt_gestion_activa->fetch(PDO::FETCH_ASSOC);
    
    if (!$gestion_activa) {
        echo json_encode([
            'status'=>'error',
            'tipo'=>'error',
            'titulo'=>'Gestión no activa',
            'mensaje'=>'No hay una gestión académica activa. Active una gestión primero.'
        ]); 
        exit;
    }

    $inserted = 0;
    $duplicates = 0;
    $invalid_section_gestion = 0; // Contador para secciones no válidas
    $inserted_list = [];
    $duplicates_list = [];
    $invalid_list = [];

    // preparar statements
    // 1) Verificar que la sección pertenece a la gestión enviada y está activa
    $stmt_check_section_gestion = $pdo->prepare("
        SELECT COUNT(*) 
        FROM secciones 
        WHERE id_seccion = :id_seccion 
          AND id_gestion = :id_gestion
          AND estado = 1
    ");

    // 2) check existencia para el mismo profesor
    $stmt_check = $pdo->prepare("
        SELECT COUNT(*) 
        FROM asignaciones_profesor
        WHERE id_profesor = :id_profesor 
          AND id_materia = :id_materia 
          AND id_seccion = :id_seccion 
          AND id_gestion = :id_gestion
          AND estado = 1
    ");

    // 3) check existencia EN OTRO profesor (conflicto)
    $stmt_conflict = $pdo->prepare("
        SELECT ap.id_profesor, CONCAT(p.nombres,' ',p.apellidos) AS nombre_profesor
        FROM asignaciones_profesor ap
        JOIN profesores p ON p.id_profesor = ap.id_profesor
        WHERE ap.id_materia = :id_materia
          AND ap.id_seccion = :id_seccion
          AND ap.id_gestion = :id_gestion
          AND ap.estado = 1
        LIMIT 1
    ");

    $stmt_insert = $pdo->prepare("
        INSERT INTO asignaciones_profesor (id_profesor, id_materia, id_seccion, id_gestion, estado, fecha_creacion)
        VALUES (:id_profesor, :id_materia, :id_seccion, :id_gestion, 1, NOW())
    ");

    // obtener nombres legibles (con grado) - CORREGIDO: gds.grado
    $stmt_detalle = $pdo->prepare("
        SELECT 
            m.nombre_materia,
            s.nombre_seccion,
            gds.grado AS nombre_grado,
            CONCAT(YEAR(g.desde), '-', YEAR(g.hasta)) AS periodo
        FROM materias m
        JOIN secciones s ON s.id_seccion = :id_seccion
        JOIN grados gds ON s.id_grado = gds.id_grado
        JOIN gestiones g ON g.id_gestion = :id_gestion
        WHERE m.id_materia = :id_materia
        LIMIT 1
    ");

    // Obtener nombre del profesor para mensajes
    $stmt_nombre_profesor = $pdo->prepare("SELECT CONCAT(nombres,' ',apellidos) AS nombre FROM profesores WHERE id_profesor = ?");
    $stmt_nombre_profesor->execute([$id_profesor]);
    $nombre_profesor = $stmt_nombre_profesor->fetchColumn();
    $nombre_profesor = $nombre_profesor ?: "Profesor ID $id_profesor";

    foreach ($id_secciones as $idx => $id_seccion_raw) {
        $id_seccion = intval($id_seccion_raw);
        $id_gestion = isset($id_gestiones[$idx]) ? intval($id_gestiones[$idx]) : 0;
        $materias = isset($materias_por_fila[$idx]) && is_array($materias_por_fila[$idx]) ? $materias_por_fila[$idx] : [];

        if (!$id_seccion || !$id_gestion || count($materias) === 0) continue;

        // PRIMERO: Verificar que la sección pertenece a la gestión y está activa
        $stmt_check_section_gestion->execute([
            ':id_seccion' => $id_seccion,
            ':id_gestion' => $id_gestion
        ]);
        $is_valid_section = (int)$stmt_check_section_gestion->fetchColumn();
        
        if (!$is_valid_section) {
            $invalid_section_gestion += count($materias);
            
            // Obtener información para mostrar en el mensaje
            $stmt_info = $pdo->prepare("
                SELECT s.nombre_seccion, g.grado
                FROM secciones s
                JOIN grados g ON s.id_grado = g.id_grado
                WHERE s.id_seccion = ?
                LIMIT 1
            ");
            $stmt_info->execute([$id_seccion]);
            $section_info = $stmt_info->fetch(PDO::FETCH_ASSOC);
            
            $section_name = $section_info 
                ? "Sección " . $section_info['nombre_seccion'] . " (" . $section_info['grado'] . ")" 
                : "Sección ID $id_seccion";
                
            foreach ($materias as $m) {
                $id_materia = intval($m);
                $stmt_materia = $pdo->prepare("SELECT nombre_materia FROM materias WHERE id_materia = ?");
                $stmt_materia->execute([$id_materia]);
                $materia_info = $stmt_materia->fetch(PDO::FETCH_ASSOC);
                $materia_name = $materia_info ? $materia_info['nombre_materia'] : "Materia ID $id_materia";
                
                $invalid_list[] = "$materia_name en $section_name - Esta sección no pertenece a la gestión activa o no está disponible";
            }
            continue; // Saltar al siguiente ciclo
        }

        foreach ($materias as $m) {
            $id_materia = intval($m);

            // verificar duplicado para el mismo profesor
            $stmt_check->execute([
                ':id_profesor' => $id_profesor,
                ':id_materia'  => $id_materia,
                ':id_seccion'  => $id_seccion,
                ':id_gestion'  => $id_gestion
            ]);
            $exists_same_prof = (int)$stmt_check->fetchColumn();

            // verificar conflicto con OTRO profesor
            $stmt_conflict->execute([
                ':id_materia' => $id_materia,
                ':id_seccion' => $id_seccion,
                ':id_gestion' => $id_gestion
            ]);
            $conflict_row = $stmt_conflict->fetch(PDO::FETCH_ASSOC);
            $conflict_profesor_nombre = $conflict_row ? $conflict_row['nombre_profesor'] : null;
            $conflict_profesor_id = $conflict_row ? intval($conflict_row['id_profesor']) : 0;

            // obtener detalle completo
            $stmt_detalle->execute([
                ':id_materia'  => $id_materia,
                ':id_seccion'  => $id_seccion,
                ':id_gestion'  => $id_gestion
            ]);
            $info = $stmt_detalle->fetch(PDO::FETCH_ASSOC);

            // texto completo con grado, sección, materia y periodo
            $texto = $info
                ? "{$info['nombre_materia']} – {$info['nombre_grado']} – Sección {$info['nombre_seccion']} ({$info['periodo']})"
                : "Materia ID $id_materia en Sección ID $id_seccion (Gestión $id_gestion)";

            if ($exists_same_prof === 0) {
                if ($conflict_row && $conflict_profesor_id !== $id_profesor) {
                    // ya está asignada a otro profesor
                    $duplicates++;
                    $duplicates_list[] = "{$texto} — ya está asignada al profesor <strong>{$conflict_profesor_nombre}</strong>";
                } else {
                    // No conflict, proceder a insertar
                    $stmt_insert->execute([
                        ':id_profesor' => $id_profesor,
                        ':id_materia'  => $id_materia,
                        ':id_seccion'  => $id_seccion,
                        ':id_gestion'  => $id_gestion
                    ]);
                    $inserted++;
                    $inserted_list[] = $texto;
                }
            } else {
                // ya existe la asignación para este mismo profesor
                $duplicates++;
                $duplicates_list[] = "{$texto} — ya asignada a este profesor";
            }
        }
    }

    // construir respuesta con las tres categorías
    if ($inserted > 0 && $duplicates === 0 && $invalid_section_gestion === 0) {
        $titulo = $inserted === 1 ? 'Asignación guardada' : 'Asignaciones guardadas';
        $mensaje = $inserted === 1 
            ? "Se registró 1 nueva asignación para el profesor <strong>$nombre_profesor</strong>." 
            : "Se registraron $inserted nuevas asignaciones para el profesor <strong>$nombre_profesor</strong>.";

        $lista = "<ul style='text-align:left; margin-top:10px;'>" .
                 implode('', array_map(fn($t)=>"<li>✅ $t</li>", $inserted_list)) .
                 "</ul>";

        $resp = [
            'status'=>'ok',
            'tipo'=>'success',
            'titulo'=>$titulo,
            'mensaje'=>"$mensaje<br><br><strong>Asignadas:</strong>$lista"
        ];

    } elseif ($inserted > 0 && ($duplicates > 0 || $invalid_section_gestion > 0)) {
        $titulo = 'Asignaciones parciales';
        $mensaje = ($inserted === 1 ? "✅ 1 nueva asignación guardada para el profesor <strong>$nombre_profesor</strong>.<br>" : "✅ $inserted nuevas asignaciones guardadas para el profesor <strong>$nombre_profesor</strong>.<br>");
        
        if ($duplicates > 0) {
            $mensaje .= "⚠️ $duplicates no procesadas por duplicados o conflictos.<br>";
        }
        if ($invalid_section_gestion > 0) {
            $mensaje .= "⚠️ $invalid_section_gestion no procesadas porque la sección no pertenece a la gestión activa.<br>";
        }

        $lista_asignadas = $inserted_list ? 
            "<ul style='text-align:left; margin-top:10px;'>" .
            implode('', array_map(fn($t)=>"<li>✅ $t</li>", $inserted_list)) .
            "</ul>" : "";

        $lista_existentes = $duplicates_list ? 
            "<ul style='text-align:left; margin-top:10px;'>" .
            implode('', array_map(fn($t)=>"<li>🔶 $t</li>", $duplicates_list)) .
            "</ul>" : "";

        $lista_invalidas = $invalid_list ?
            "<ul style='text-align:left; margin-top:10px;'>" .
            implode('', array_map(fn($t)=>"<li>❌ $t</li>", $invalid_list)) .
            "</ul>" : "";

        $resp = [
            'status'=>'ok',
            'tipo'=>'warning',
            'titulo'=>$titulo,
            'mensaje'=>"$mensaje<br>" .
                      ($lista_asignadas ? "<strong>Asignadas:</strong>$lista_asignadas<br>" : "") .
                      ($lista_existentes ? "<strong>Duplicados/Conflictos:</strong>$lista_existentes<br>" : "") .
                      ($lista_invalidas ? "<strong>Secciones inválidas:</strong>$lista_invalidas" : "")
        ];

    } elseif ($inserted === 0 && $duplicates > 0) {
        $lista = "<ul style='text-align:left; margin-top:10px;'>" .
                 implode('', array_map(fn($t)=>"<li>❌ $t</li>", $duplicates_list)) .
                 "</ul>";

        $resp = [
            'status'=>'ok',
            'tipo'=>'error',
            'titulo'=>'Asignación existente o conflicto',
            'mensaje'=>"No se pudo asignar ninguna materia al profesor <strong>$nombre_profesor</strong> porque ya existen asignaciones o hay conflictos con otros profesores.<br><br><strong>Detalles:</strong>$lista"
        ];
    } elseif ($invalid_section_gestion > 0) {
        $lista = "<ul style='text-align:left; margin-top:10px;'>" .
                 implode('', array_map(fn($t)=>"<li>❌ $t</li>", $invalid_list)) .
                 "</ul>";
        
        $resp = [
            'status'=>'error',
            'tipo'=>'error',
            'titulo'=>'Secciones no válidas',
            'mensaje'=>"No se procesaron asignaciones para el profesor <strong>$nombre_profesor</strong> porque las secciones seleccionadas no pertenecen a la gestión activa.<br><br><strong>Detalles:</strong>$lista"
        ];
    } else {
        $resp = [
            'status'=>'error',
            'tipo'=>'error',
            'titulo'=>'Sin cambios',
            'mensaje'=>'No se procesaron asignaciones para el profesor <strong>$nombre_profesor</strong>.'
        ];
    }

    echo json_encode($resp);
    exit;

} catch (Throwable $e) {
    error_log("Error en guardar_asignacion.php: " . $e->getMessage());
    echo json_encode([
        'status'=>'error',
        'tipo'=>'error',
        'titulo'=>'Error del servidor',
        'mensaje'=>'Error interno del servidor. Detalle: ' . $e->getMessage()
    ]);
    exit;
}