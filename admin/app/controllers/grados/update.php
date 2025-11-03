<?php
include ('../../../app/config.php');

// Verificar si los datos del formulario están definidos
if (isset($_POST['id_grado'], $_POST['nivel'], $_POST['grado'], $_POST['estado'])) {
    // Obtener los datos del formulario
    $grado_id = $_POST['id_grado'];
    $nivel = $_POST['nivel'];
    $grado = $_POST['grado']; // Cambié 'curso' a 'grado'
    $estado = $_POST['estado'];

    // Preparar la sentencia SQL para actualizar los datos
    $sentencia = $pdo->prepare('UPDATE grados
    SET nivel = :nivel,
        grado = :grado,
        estado = :estado
    WHERE id_grado = :grado_id');

    // Vincular los parámetros
    $sentencia->bindParam(':nivel', $nivel);
    $sentencia->bindParam(':grado', $grado);
    $sentencia->bindParam(':estado', $estado);
    $sentencia->bindParam(':grado_id', $grado_id);

    // Ejecutar la sentencia
    if ($sentencia->execute()) {
        session_start();
        $_SESSION['mensaje'] = "Se actualizó el grado correctamente.";
        $_SESSION['icono'] = "success";
        header('Location: ' . APP_URL . "/admin/configuraciones/grados"); // Redirigir a la lista de grados
        exit();
    } else {
        session_start();
        $_SESSION['mensaje'] = "Error: no se pudo actualizar , comuníquese con el administrador.";
        $_SESSION['icono'] = "error";
        header('Location: ' . APP_URL . "/admin/configuraciones/grados/edit.php?id=" . $grado_id); // Redirigir de vuelta al formulario de edición
        exit();
    }
} else {
    // Manejo de error si no se enviaron los datos correctamente
    session_start();
    $_SESSION['mensaje'] = "Error: datos del formulario no enviados correctamente.";
    $_SESSION['icono'] = "error";
    header('Location: ' . APP_URL . "/admin/configuraciones/grados/edit.php?id=" . $grado_id); // Redirigir de vuelta al formulario de edición
    exit();
}
?>