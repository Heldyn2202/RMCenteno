<?php
// actualizar_materia.php

require_once '../../../app/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_materia = $_POST['id_materia'];
    $nombre_materia = $_POST['nombre_materia'];
    $id_grado = $_POST['id_grado'];

    $id_materia = filter_var($id_materia, FILTER_VALIDATE_INT);
    $nombre_materia = htmlspecialchars(strip_tags(trim($nombre_materia)));
    $id_grado = filter_var($id_grado, FILTER_VALIDATE_INT);

    $errores = [];

    if (empty($id_materia) || $id_materia === false) {
        $errores[] = "El ID de la materia no es válido.";
    }

    if (empty($nombre_materia)) {
        $errores[] = "El nombre de la materia es obligatorio.";
    }

    if (empty($id_grado) || $id_grado === false) {
        $errores[] = "Debe seleccionar un grado.";
    }

    if (empty($errores)) {
        try {
            $sql = "UPDATE materias SET nombre_materia = :nombre_materia, id_grado = :id_grado WHERE id_materia = :id_materia";
            $query = $pdo->prepare($sql);
            $query->bindParam(':id_materia', $id_materia);
            $query->bindParam(':nombre_materia', $nombre_materia);
            $query->bindParam(':id_grado', $id_grado);
            $query->execute();

            $_SESSION['mensaje'] = "Materia actualizada correctamente.";
            $_SESSION['mensaje_tipo'] = 'success';
            header('Location: ../materias/materias.php');
            exit();

        } catch (PDOException $e) {
            $_SESSION['mensaje'] = "Error al actualizar la materia: " . $e->getMessage();
            $_SESSION['mensaje_tipo'] = 'danger';
            header('Location: ../materias/materias.php');
            exit();
        }
    } else {
        $_SESSION['mensaje'] = "Error al actualizar la materia. Por favor, corrija los siguientes errores:<br>" . implode("<br>", $errores);
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