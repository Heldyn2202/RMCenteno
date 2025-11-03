<?php
require_once __DIR__ . '/../../../app/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $nombre_lapso = $_POST['nombre_lapso'];
        $fecha_inicio = $_POST['fecha_inicio'];
        $fecha_fin = $_POST['fecha_fin'];
        $id_gestion = $_POST['id_gestion'];

        // Validar fechas
        if (strtotime($fecha_fin) < strtotime($fecha_inicio)) {
            throw new Exception("La fecha de fin no puede ser anterior a la fecha de inicio");
        }

        // Verificar superposición de lapsos
        $sql_check = "SELECT id_lapso FROM lapsos 
                      WHERE id_gestion = :id_gestion 
                      AND (
                          (:fecha_inicio BETWEEN fecha_inicio AND fecha_fin)
                          OR (:fecha_fin BETWEEN fecha_inicio AND fecha_fin)
                          OR (fecha_inicio BETWEEN :fecha_inicio AND :fecha_fin)
                      )";
        $query_check = $pdo->prepare($sql_check);
        $query_check->bindParam(':id_gestion', $id_gestion);
        $query_check->bindParam(':fecha_inicio', $fecha_inicio);
        $query_check->bindParam(':fecha_fin', $fecha_fin);
        $query_check->execute();

        if ($query_check->rowCount() > 0) {
            throw new Exception("El lapso se superpone con otro existente");
        }

        // Insertar nuevo lapso
        $sql = "INSERT INTO lapsos (nombre_lapso, fecha_inicio, fecha_fin, id_gestion) 
                VALUES (:nombre, :inicio, :fin, :gestion)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':nombre', $nombre_lapso);
        $stmt->bindParam(':inicio', $fecha_inicio);
        $stmt->bindParam(':fin', $fecha_fin);
        $stmt->bindParam(':gestion', $id_gestion);
        
        if ($stmt->execute()) {
            $_SESSION['mensaje'] = "Lapso creado correctamente";
        } else {
            throw new Exception("Error al crear el lapso");
        }
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
}

header('Location: ' . APP_URL . '/admin/configuraciones/lapsos/lapsos.php');
exit();
?>