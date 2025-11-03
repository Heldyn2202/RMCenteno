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

    $inserted = 0;
    $duplicates = 0;
    $inserted_list = [];
    $duplicates_list = [];

    // preparar statements
    $stmt_check = $pdo->prepare("
        SELECT COUNT(*) 
        FROM asignaciones_profesor
        WHERE id_profesor = :id_profesor 
          AND id_materia = :id_materia 
          AND id_seccion = :id_seccion 
          AND id_gestion = :id_gestion
    ");

    $stmt_insert = $pdo->prepare("
        INSERT INTO asignaciones_profesor (id_profesor, id_materia, id_seccion, id_gestion, estado, fecha_creacion)
        VALUES (:id_profesor, :id_materia, :id_seccion, :id_gestion, 1, NOW())
    ");

    // obtener nombres legibles (ahora también con grado)
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

    foreach ($id_secciones as $idx => $id_seccion_raw) {
        $id_seccion = intval($id_seccion_raw);
        $id_gestion = isset($id_gestiones[$idx]) ? intval($id_gestiones[$idx]) : 0;
        $materias = isset($materias_por_fila[$idx]) && is_array($materias_por_fila[$idx]) ? $materias_por_fila[$idx] : [];

        if (!$id_seccion || !$id_gestion || count($materias) === 0) continue;

        foreach ($materias as $m) {
            $id_materia = intval($m);

            // verificar duplicado
            $stmt_check->execute([
                ':id_profesor' => $id_profesor,
                ':id_materia'  => $id_materia,
                ':id_seccion'  => $id_seccion,
                ':id_gestion'  => $id_gestion
            ]);
            $exists = (int)$stmt_check->fetchColumn();

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

            if ($exists === 0) {
                $stmt_insert->execute([
                    ':id_profesor' => $id_profesor,
                    ':id_materia'  => $id_materia,
                    ':id_seccion'  => $id_seccion,
                    ':id_gestion'  => $id_gestion
                ]);
                $inserted++;
                $inserted_list[] = $texto;
            } else {
                $duplicates++;
                $duplicates_list[] = $texto;
            }
        }
    }

    // construir respuesta
    if ($inserted > 0 && $duplicates === 0) {
        $titulo = $inserted === 1 ? 'Asignación guardada' : 'Asignaciones guardadas';
        $mensaje = $inserted === 1 
            ? "Se registró 1 nueva asignación." 
            : "Se registraron $inserted nuevas asignaciones.";

        $lista = "<ul style='text-align:left; margin-top:10px;'>" .
                 implode('', array_map(fn($t)=>"<li>✅ $t</li>", $inserted_list)) .
                 "</ul>";

        $resp = [
            'status'=>'ok',
            'tipo'=>'success',
            'titulo'=>$titulo,
            'mensaje'=>"$mensaje<br><br><strong>Asignadas:</strong>$lista"
        ];

    } elseif ($inserted > 0 && $duplicates > 0) {
        $titulo = 'Asignaciones parciales';
        $mensaje = ($inserted === 1 ? "✅ 1 nueva asignación guardada.<br>" : "✅ $inserted nuevas asignaciones guardadas.<br>")
                 . "⚠️ $duplicates ya existían y no se duplicaron.";

        $lista_asignadas = "<ul style='text-align:left; margin-top:10px;'>" .
                           implode('', array_map(fn($t)=>"<li>✅ $t</li>", $inserted_list)) .
                           "</ul>";

        $lista_existentes = "<ul style='text-align:left; margin-top:10px;'>" .
                            implode('', array_map(fn($t)=>"<li>🔶 $t</li>", $duplicates_list)) .
                            "</ul>";

        $resp = [
            'status'=>'ok',
            'tipo'=>'warning',
            'titulo'=>$titulo,
            'mensaje'=>"$mensaje<br><br><strong>Asignadas:</strong>$lista_asignadas<br><strong>Ya asignadas:</strong>$lista_existentes"
        ];

    } elseif ($inserted === 0 && $duplicates > 0) {
        $lista = "<ul style='text-align:left; margin-top:10px;'>" .
                 implode('', array_map(fn($t)=>"<li>❌ $t</li>", $duplicates_list)) .
                 "</ul>";

        $resp = [
            'status'=>'ok',
            'tipo'=>'error',
            'titulo'=>'Asignación existente',
            'mensaje'=>"La asignación seleccionada ya está registrada.<br><br><strong>Detalles:</strong>$lista"
        ];
    } else {
        $resp = [
            'status'=>'error',
            'tipo'=>'error',
            'titulo'=>'Sin cambios',
            'mensaje'=>'No se procesaron asignaciones.'
        ];
    }

    echo json_encode($resp);
    exit;

} catch (Throwable $e) {
    echo json_encode([
        'status'=>'error',
        'tipo'=>'error',
        'titulo'=>'Error del servidor',
        'mensaje'=>'Error: '.$e->getMessage()
    ]);
    exit;
}
?>
