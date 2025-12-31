<?php
require_once __DIR__ . '/../../../app/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $id_gestion = $_POST['id_gestion'];
        $nombre_lapso = $_POST['nombre_lapso'];
        $fecha_inicio = $_POST['fecha_inicio'];
        $fecha_fin = $_POST['fecha_fin'];

        // Conversión a objetos DateTime
        $datetime_inicio = new DateTime($fecha_inicio);
        $datetime_fin = new DateTime($fecha_fin);
        
        // --- 1. Obtener Límites de la Gestión Activa ---
        
        $sql_gestion_limites = "SELECT desde, hasta FROM gestiones WHERE id_gestion = :id_gestion AND estado = 1";
        $query_gestion_limites = $pdo->prepare($sql_gestion_limites);
        $query_gestion_limites->bindParam(':id_gestion', $id_gestion);
        $query_gestion_limites->execute();
        $gestion_activa = $query_gestion_limites->fetch(PDO::FETCH_ASSOC);

        if (!$gestion_activa) {
            throw new Exception("Error: El periodo académico seleccionado no está activo o no existe.");
        }
        
        $gestion_inicio = new DateTime($gestion_activa['desde']);
        $gestion_fin = new DateTime($gestion_activa['hasta']);

        // VALIDACIÓN: El lapso debe caer dentro de la gestión
        if ($datetime_inicio < $gestion_inicio || $datetime_fin > $gestion_fin) {
            $inicio_fmt = $gestion_inicio->format('d/m/Y');
            $fin_fmt = $gestion_fin->format('d/m/Y');
            throw new Exception("Error de Límites: Las fechas del lapso deben estar dentro del periodo activo ({$inicio_fmt} hasta {$fin_fmt}).");
        }
        
        // --- 2. VALIDACIÓN: Fecha de fin anterior a la de inicio ---
        if ($datetime_fin < $datetime_inicio) {
            throw new Exception("Error: La fecha de fin no puede ser anterior a la de inicio.");
        }

        // --- 3. VALIDACIÓN DE DURACIÓN (90 a 100 días) ---
        $MIN_DIAS = 90;
        $MAX_DIAS = 100;
        
        // Calcular la diferencia en días incluyendo el día de fin. 
        // Se clona y modifica la fecha de fin para un cálculo inclusivo preciso.
        $datetime_fin_inclusive = (clone $datetime_fin)->modify('+1 day');
        $interval = $datetime_inicio->diff($datetime_fin_inclusive);
        $diffDays = (int)$interval->days;

        // Para mostrar el mensaje de error de forma más clara, calculamos la diferencia real sin el +1 día
        $diff_calendar = $datetime_inicio->diff($datetime_fin)->days; 

        if ($diffDays < $MIN_DIAS + 1 || $diffDays > $MAX_DIAS + 1) { // La comparación es +1 día debido al truco de cálculo
            throw new Exception("Error de Duración: El lapso debe durar entre {$MIN_DIAS} y {$MAX_DIAS} días. La duración fue de {$diff_calendar} días.");
        }
        
        // --- 4. VALIDACIÓN DE SUPERPOSICIÓN DE FECHAS ---
        $sql_check_superposicion = "SELECT id_lapso FROM lapsos 
                                    WHERE id_gestion = :gestion 
                                    AND estado = 1
                                    AND (
                                        (:inicio BETWEEN fecha_inicio AND fecha_fin)
                                        OR (:fin BETWEEN fecha_inicio AND fecha_fin)
                                        OR (fecha_inicio BETWEEN :inicio AND :fin)
                                    )";
        $query_check = $pdo->prepare($sql_check_superposicion);
        $query_check->bindParam(':gestion', $id_gestion);
        $query_check->bindParam(':inicio', $fecha_inicio);
        $query_check->bindParam(':fin', $fecha_fin);
        $query_check->execute();

        if ($query_check->rowCount() > 0) {
            throw new Exception("Error: El nuevo lapso se superpone con otro existente en el periodo académico activo.");
        }
        
        // --- INSERCIÓN EN BASE DE DATOS ---
        $pdo->beginTransaction();
        
        $sql = "INSERT INTO lapsos (id_gestion, nombre_lapso, fecha_inicio, fecha_fin, fyh_creacion, estado) 
                VALUES (:id_gestion, :nombre_lapso, :fecha_inicio, :fecha_fin, :fyh_creacion, :estado)";
        
        $query = $pdo->prepare($sql);
        $query->bindParam(':id_gestion', $id_gestion);
        $query->bindParam(':nombre_lapso', $nombre_lapso);
        $query->bindParam(':fecha_inicio', $fecha_inicio);
        $query->bindParam(':fecha_fin', $fecha_fin);
        
        $fyh_creacion = date('Y-m-d H:i:s');
        $estado = 1;
        $query->bindParam(':fyh_creacion', $fyh_creacion);
        $query->bindParam(':estado', $estado);
        
        if ($query->execute()) {
            $pdo->commit();
            // Éxito: Se activa el SweetAlert verde
            $_SESSION['mensaje'] = "Lapso académico creado exitosamente.";
        } else {
            $pdo->rollBack();
            throw new Exception("Error al guardar el lapso en la base de datos.");
        }
        
    } catch (PDOException $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
             $pdo->rollBack();
        }
        // Error de BD: Se activa el SweetAlert rojo
        $_SESSION['error'] = "Error de base de datos: " . $e->getMessage();
    } catch (Exception $e) {
        // Error de Validación/Lógica: Se activa el SweetAlert rojo
        $_SESSION['error'] = $e->getMessage();
    }

    header('Location: ' . APP_URL . '/admin/configuraciones/lapsos/lapsos.php');
    exit();
} else {
    // Manejar acceso directo no autorizado
    $_SESSION['error'] = "Acceso no permitido.";
    header('Location: ' . APP_URL . '/admin/configuraciones/lapsos/lapsos.php');
    exit();
}
?>