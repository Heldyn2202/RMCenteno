<?php
require_once('../../app/config.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_lapso = $_POST['id_lapso'];
    $nombre_lapso = $_POST['nombre_lapso'];
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'];

    // Validar fechas
    if (strtotime($fecha_fin) < strtotime($fecha_inicio)) {
        $_SESSION['mensaje'] = "Error: La fecha de fin no puede ser anterior a la fecha de inicio";
        header('Location: lapsos.php');
        exit();
    }

    // Obtener el id_gestion para verificar superposición
    $sql_gestion = "SELECT id_gestion FROM lapsos WHERE id_lapso = :id_lapso";
    $query_gestion = $pdo->prepare($sql_gestion);
    $query_gestion->bindParam(':id_lapso', $id_lapso);
    $query_gestion->execute();
    $gestion = $query_gestion->fetch(PDO::FETCH_ASSOC);

    // Verificar superposición de lapsos (excluyendo el actual)
    $sql_check = "SELECT id_lapso FROM lapsos 
                  WHERE id_gestion = :id_gestion 
                  AND id_lapso != :id_lapso
                  AND (
                      (:fecha_inicio BETWEEN fecha_inicio AND fecha_fin)
                      OR (:fecha_fin BETWEEN fecha_inicio AND fecha_fin)
                      OR (fecha_inicio BETWEEN :fecha_inicio AND :fecha_fin)
                  )";
    $query_check = $pdo->prepare($sql_check);
    $query_check->bindParam(':id_gestion', $gestion['id_gestion']);
    $query_check->bindParam(':id_lapso', $id_lapso);
    $query_check->bindParam(':fecha_inicio', $fecha_inicio);
    $query_check->bindParam(':fecha_fin', $fecha_fin);
    $query_check->execute();

    if ($query_check->rowCount() > 0) {
        $_SESSION['mensaje'] = "Error: El lapso se superpone con otro existente";
        header('Location: lapsos.php');
        exit();
    }

    // Actualizar lapso
    $sql = "UPDATE lapsos 
            SET nombre_lapso = :nombre_lapso, 
                fecha_inicio = :fecha_inicio, 
                fecha_fin = :fecha_fin
            WHERE id_lapso = :id_lapso";
    $query = $pdo->prepare($sql);
    $query->bindParam(':nombre_lapso', $nombre_lapso);
    $query->bindParam(':fecha_inicio', $fecha_inicio);
    $query->bindParam(':fecha_fin', $fecha_fin);
    $query->bindParam(':id_lapso', $id_lapso);

    if ($query->execute()) {
        $_SESSION['mensaje'] = "Lapso académico actualizado correctamente";
    } else {
        $_SESSION['mensaje'] = "Error al actualizar el lapso académico";
    }
}

header('Location: lapsos.php');
?>