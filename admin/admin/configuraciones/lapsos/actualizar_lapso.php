<?php
require_once __DIR__ . '/../../../app/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $id_lapso = $_POST['id_lapso'];
        $nombre_lapso = $_POST['nombre_lapso'];
        $fecha_inicio = $_POST['fecha_inicio'];
        $fecha_fin = $_POST['fecha_fin'];

        // Validar fechas
        if (strtotime($fecha_fin) < strtotime($fecha_inicio)) {
            throw new Exception("La fecha de fin no puede ser anterior a la fecha de inicio");
        }

        // Obtener el id_gestion para verificar superposición
        $sql_gestion = "SELECT id_gestion FROM lapsos WHERE id_lapso = :id";
        $query_gestion = $pdo->prepare($sql_gestion);
        $query_gestion->bindParam(':id', $id_lapso);
        $query_gestion->execute();
        $gestion = $query_gestion->fetch(PDO::FETCH_ASSOC);

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
        $query_check->bindParam(':gestion', $gestion['id_gestion']);
        $query_check->bindParam(':id', $id_lapso);
        $query_check->bindParam(':inicio', $fecha_inicio);
        $query_check->bindParam(':fin', $fecha_fin);
        $query_check->execute();

        if ($query_check->rowCount() > 0) {
            throw new Exception("El lapso se superpone con otro existente");
        }

        // Actualizar lapso
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
            throw new Exception("Error al actualizar el lapso");
        }
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
}

header('Location: ' . APP_URL . '/admin/configuraciones/lapsos/lapsos.php');
exit();
?>