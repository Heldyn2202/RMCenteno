<?php
// guardar_materia.php

require_once '../../../app/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_materia = $_POST['nombre_materia'];
    $id_grado = $_POST['id_grado'];

    $nombre_materia = htmlspecialchars(strip_tags(trim($nombre_materia)));
    $id_grado = filter_var($id_grado, FILTER_VALIDATE_INT);

    $errores = [];

    if (empty($nombre_materia)) {
        $errores[] = "El nombre de la materia es obligatorio.";
    }

    if (empty($id_grado) || $id_grado === false) {
        $errores[] = "Debe seleccionar un grado.";
    }

    if (empty($errores)) {
        try {
            $sql = "INSERT INTO materias (nombre_materia, id_grado, estado) VALUES (:nombre_materia, :id_grado, :estado)";
            $query = $pdo->prepare($sql);
            $query->bindParam(':nombre_materia', $nombre_materia);
            $query->bindParam(':id_grado', $id_grado);
            $query->bindValue(':estado', 1); // Establecer estado como activo
            $query->execute();

            $_SESSION['mensaje'] = "Materia guardada correctamente.";
            $_SESSION['mensaje_tipo'] = 'success';
            header('Location: ../materias/materias.php');
            exit();

        } catch (PDOException $e) {
            $_SESSION['mensaje'] = "Error al guardar la materia: " . $e->getMessage();
            $_SESSION['mensaje_tipo'] = 'danger';
            header('Location: ../materias/materias.php');
            exit();
        }
    } else {
        $_SESSION['mensaje'] = "Error al guardar la materia. Por favor, corrija los siguientes errores:<br>" . implode("<br>", $errores);
        $_SESSION['mensaje_tipo'] = 'warning';
        header('Location: ../materias/materias.php');
        exit();
    }
} else {
    $_SESSION['mensaje'] = "Acceso no permitido.";
    $_SESSION['mensaje_tipo'] = 'danger';
    header('Location: ../materias/materias.php');
    exit();
}
?>