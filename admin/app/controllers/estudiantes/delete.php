<?php

include ('../../../app/config.php');

$id_estudiante = $_POST['id_estudiante'];
$accion = $_POST['action']; // 'inhabilitar' o 'habilitar'

// Verificar el estado actual del estudiante
$consulta = $pdo->prepare("SELECT estatus FROM estudiantes WHERE id_estudiante = :id_estudiante");
$consulta->bindParam(':id_estudiante', $id_estudiante);
$consulta->execute();
$resultado = $consulta->fetch(PDO::FETCH_ASSOC);

if ($resultado) {
    $estatus_actual = $resultado['estatus'];

    if ($accion === 'inhabilitar' && $estatus_actual === 'activo') {
        // Inhabilitar estudiante
        $sentencia = $pdo->prepare("UPDATE estudiantes SET estatus = 'inactivo' WHERE id_estudiante = :id_estudiante");
    } elseif ($accion === 'habilitar' && $estatus_actual === 'inactivo') {
        // Habilitar estudiante
        $sentencia = $pdo->prepare("UPDATE estudiantes SET estatus = 'activo' WHERE id_estudiante = :id_estudiante");
    } else {
        // Acción no válida o el estatus ya está en el estado deseado
        session_start();
        $_SESSION['mensaje'] = "El estudiante ya está en el estado deseado.";
        $_SESSION['icono'] = "info"; // Cambiar a 'info' para indicar que no se realizó ninguna acción
        header('Location:' . APP_URL . "/admin/estudiantes/Lista_de_estudiante.php");
        exit;
    }

    $sentencia->bindParam(':id_estudiante', $id_estudiante);

    try {
        if ($sentencia->execute()) {
            session_start();
            if ($accion === 'inhabilitar') {
                $_SESSION['mensaje'] = "Se inhabilitó al estudiante correctamente.";
            } else {
                $_SESSION['mensaje'] = "Se habilitó al estudiante correctamente.";
            }
            $_SESSION['icono'] = "success";
            header('Location:' . APP_URL . "/admin/estudiantes/Lista_de_estudiante.php");
        } else {
            session_start();
            $_SESSION['mensaje'] = "Error, no se pudo realizar la acción, comuníquese con el administrador";
            $_SESSION['icono'] = "error";
            header('Location:' . APP_URL . "/admin/estudiantes/Lista_de_estudiante.php");
        }
    } catch (Exception $exception) {
        session_start();
        $_SESSION['mensaje'] = "Error, no se pudo realizar la acción en la base de datos, porque este registro está en otras tablas";
        $_SESSION['icono'] = "error";
        header('Location:' . APP_URL . "/admin/estudiantes/Lista_de_estudiante.php");
    }
} else {
    session_start();
    $_SESSION['mensaje'] = "Estudiante no encontrado.";
    $_SESSION['icono'] = "error";
    header('Location:' . APP_URL . "/admin/estudiantes/Lista_de_estudiante.php");
}