<?php
require_once('../../app/config.php');

$id_lapso = $_GET['id'] ?? null;

if ($id_lapso) {
    $sql = "UPDATE lapsos SET estado = 1 WHERE id_lapso = :id_lapso";
    $query = $pdo->prepare($sql);
    $query->bindParam(':id_lapso', $id_lapso);
    
    if ($query->execute()) {
        $_SESSION['mensaje'] = "Lapso académico activado correctamente";
    } else {
        $_SESSION['mensaje'] = "Error al activar el lapso académico";
    }
}

header('Location: lapsos.php');
?>