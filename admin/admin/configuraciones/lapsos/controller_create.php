<?php
require_once('../../app/config.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre_lapso = $_POST['nombre_lapso'];
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'];
    $id_gestion = $_POST['id_gestion'];

    // Validar fechas
    if (strtotime($fecha_fin) < strtotime($fecha_inicio)) {
        $_SESSION['mensaje'] = "Error: La fecha de fin no puede ser anterior a la fecha de inicio";
        header('Location: lapsos.php');
        exit();
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
        $_SESSION['mensaje'] = "Error: El lapso se superpone con otro existente";
        header('Location: lapsos.php');
        exit();
    }

    // Insertar nuevo lapso
    $sql = "INSERT INTO lapsos (nombre_lapso, fecha_inicio, fecha_fin, id_gestion) 
            VALUES (:nombre_lapso, :fecha_inicio, :fecha_fin, :id_gestion)";
    $query = $pdo->prepare($sql);
    $query->bindParam(':nombre_lapso', $nombre_lapso);
    $query->bindParam(':fecha_inicio', $fecha_inicio);
    $query->bindParam(':fecha_fin', $fecha_fin);
    $query->bindParam(':id_gestion', $id_gestion);

    if ($query->execute()) {
        $_SESSION['mensaje'] = "Lapso académico creado correctamente";
    } else {
        $_SESSION['mensaje'] = "Error al crear el lapso académico";
    }
}

header('Location: lapsos.php');
?>