<?php
require_once __DIR__ . '/../../../app/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $id_lapso = $_POST['id_lapso'];
        $nombre_lapso = $_POST['nombre_lapso'];
        $fecha_inicio = $_POST['fecha_inicio'];
        $fecha_fin = $_POST['fecha_fin'];

        // Conversión a objetos DateTime para cálculos precisos de PHP
        $datetime_inicio = new DateTime($fecha_inicio);
        $datetime_fin = new DateTime($fecha_fin);

        // --- 1. Validar Fechas y Duración ---

        // Validar: Fecha de fin no puede ser anterior a Inicio
        if ($datetime_fin < $datetime_inicio) {
            throw new Exception("La fecha de fin no puede ser anterior a la fecha de inicio.");
        }

        // VALIDACIÓN DE DURACIÓN (RESTRICCIÓN DE 90 A 100 DÍAS)
        $MIN_DIAS = 90;
        $MAX_DIAS = 100;
        
        // Calcular la diferencia en días. Se añade un segundo extra a la fecha de fin para 
        // asegurar que la diferencia incluya el día final (similar a Math.ceil en JS).
        $datetime_fin_inclusive = (clone $datetime_fin)->modify('+1 day');
        $interval = $datetime_inicio->diff($datetime_fin_inclusive);
        $diffDays = (int)$interval->days;

        if ($diffDays < $MIN_DIAS || $diffDays > $MAX_DIAS) {
            // Nota: Se resta 1 al diffDays ya que la modificación '+1 day' infla el conteo en 1 para el chequeo
            // La duración real para mostrar es la diferencia de días calendario.
            $diff_calendar = $datetime_inicio->diff($datetime_fin)->days;
            throw new Exception("Error de Duración: El lapso debe durar entre {$MIN_DIAS} y {$MAX_DIAS} días. La duración actual es de {$diff_calendar} días.");
        }
        
        // --- 2. Obtener Gestión y Validar Límites ---
        
        // Obtener el id_gestion y las fechas de la GESTIÓN activa a partir del lapso
        $sql_gestion = "SELECT l.id_gestion, g.desde AS gestion_inicio, g.hasta AS gestion_fin 
                        FROM lapsos l 
                        JOIN gestiones g ON l.id_gestion = g.id_gestion
                        WHERE l.id_lapso = :id";
        $query_gestion = $pdo->prepare($sql_gestion);
        $query_gestion->bindParam(':id', $id_lapso);
        $query_gestion->execute();
        $datos_gestion = $query_gestion->fetch(PDO::FETCH_ASSOC);

        if (!$datos_gestion) {
            throw new Exception("Lapso o su gestión asociada no encontrado.");
        }
        
        $gestion_inicio = new DateTime($datos_gestion['gestion_inicio']);
        $gestion_fin = new DateTime($datos_gestion['gestion_fin']);
        
        // Validar: Las fechas del lapso deben caer DENTRO de la gestión
        if ($datetime_inicio < $gestion_inicio || $datetime_fin > $gestion_fin) {
            $inicio_fmt = $gestion_inicio->format('d/m/Y');
            $fin_fmt = $gestion_fin->format('d/m/Y');
            throw new Exception("Error de Límites: Las fechas del lapso deben estar dentro del periodo activo ({$inicio_fmt} hasta {$fin_fmt}).");
        }
        
        // --- 3. Verificar Superposición ---
        
        // Verificar superposición (excluyendo el actual)
        $sql_check = "SELECT id_lapso FROM lapsos 
                      WHERE id_gestion = :gestion 
                      AND id_lapso != :id
                      AND (
                          (:inicio BETWEEN fecha_inicio AND fecha_fin)
                          OR (:fin BETWEEN fecha_inicio AND fecha_fin)
                          OR (fecha_inicio BETWEEN :inicio AND :fin)
                      )";
        $query_check = $pdo->prepare($sql_check);
        $query_check->bindParam(':gestion', $datos_gestion['id_gestion']);
        $query_check->bindParam(':id', $id_lapso);
        $query_check->bindParam(':inicio', $fecha_inicio);
        $query_check->bindParam(':fin', $fecha_fin);
        $query_check->execute();

        if ($query_check->rowCount() > 0) {
            throw new Exception("El lapso se superpone con otro existente en el mismo periodo académico.");
        }

        // --- 4. Ejecutar Actualización ---
        
        $sql = "UPDATE lapsos 
                SET nombre_lapso = :nombre, 
                    fecha_inicio = :inicio, 
                    fecha_fin = :fin
                WHERE id_lapso = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':nombre', $nombre_lapso);
        $stmt->bindParam(':inicio', $fecha_inicio);
        $stmt->bindParam(':fin', $fecha_fin);
        $stmt->bindParam(':id', $id_lapso);
        
        if ($stmt->execute()) {
            $_SESSION['mensaje'] = "Lapso actualizado correctamente";
        } else {
            throw new Exception("Error al actualizar el lapso en la base de datos.");
        }
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
} else {
    $_SESSION['error'] = "Acceso no permitido.";
}

header('Location: ' . APP_URL . '/admin/configuraciones/lapsos/lapsos.php');
exit();
?>