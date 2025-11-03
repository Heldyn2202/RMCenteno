<?php
include ('../../../app/config.php');

// Verificar si las claves existen en $_POST
if (isset($_POST['id_seccion']) && isset($_POST['turno']) && isset($_POST['capacidad']) && isset($_POST['id_grado']) && isset($_POST['nombre_seccion']) && isset($_POST['estado'])) {
    $id_seccion = $_POST['id_seccion'];
    $turno = $_POST['turno'];
    $capacidad = $_POST['capacidad'];
    $id_grado = $_POST['id_grado'];
    $nombre_seccion = strtoupper(trim($_POST['nombre_seccion'])); // Convertir a mayúsculas
    $estado = $_POST['estado'];

    // Obtener el periodo académico activo
    $sql_gestion = "SELECT * FROM gestiones WHERE estado = 1 ORDER BY desde DESC LIMIT 1";
    $query_gestion = $pdo->prepare($sql_gestion);
    $query_gestion->execute();
    $gestion_activa = $query_gestion->fetch(PDO::FETCH_ASSOC);

    if (!$gestion_activa) {
        session_start();
        $_SESSION['mensaje'] = "Error: No hay un periodo académico activo.";
        $_SESSION['icono'] = "error";
        header('Location: ' . APP_URL . "/admin/configuraciones/secciones/edit.php?id=" . $id_seccion);
        exit();
    }

    $id_gestion = $gestion_activa['id_gestion'];

    // Validar duplicados (misma sección, mismo grado, mismo periodo, excluyendo la que se está editando)
    $sql_validacion = "SELECT COUNT(*) AS total 
                       FROM secciones 
                       WHERE id_grado = :id_grado 
                         AND nombre_seccion = :nombre_seccion 
                         AND id_gestion = :id_gestion 
                         AND id_seccion != :id_seccion";
    $query_validacion = $pdo->prepare($sql_validacion);
    $query_validacion->bindParam(':id_grado', $id_grado);
    $query_validacion->bindParam(':nombre_seccion', $nombre_seccion);
    $query_validacion->bindParam(':id_gestion', $id_gestion);
    $query_validacion->bindParam(':id_seccion', $id_seccion);
    $query_validacion->execute();
    $resultado = $query_validacion->fetch(PDO::FETCH_ASSOC);

    if ($resultado['total'] > 0) {
        session_start();
        $_SESSION['mensaje'] = "Error: Ya existe una sección '$nombre_seccion' para este grado en el periodo académico activo.";
        $_SESSION['icono'] = "error";
        header('Location: ' . APP_URL . "/admin/configuraciones/secciones/edit.php?id=" . $id_seccion);
        exit();
    }

    // Preparar la sentencia SQL para actualizar la sección
    $sentencia = $pdo->prepare('UPDATE secciones
    SET turno = :turno,
        capacidad = :capacidad,
        id_grado = :id_grado,
        nombre_seccion = :nombre_seccion,
        estado = :estado
    WHERE id_seccion = :id_seccion');

    // Vincular los parámetros
    $sentencia->bindParam(':id_seccion', $id_seccion);
    $sentencia->bindParam(':turno', $turno);
    $sentencia->bindParam(':capacidad', $capacidad);
    $sentencia->bindParam(':id_grado', $id_grado);
    $sentencia->bindParam(':nombre_seccion', $nombre_seccion);
    $sentencia->bindParam(':estado', $estado);

    // Ejecutar la sentencia
    if ($sentencia->execute()) {
        session_start();
        $_SESSION['mensaje'] = "Se actualizó la sección de manera correcta.";
        $_SESSION['icono'] = "success";
        header('Location: ' . APP_URL . "/admin/configuraciones/secciones");
        exit();
    } else {
        session_start();
        $_SESSION['mensaje'] = "Error: no se pudo actualizar, comuníquese con el administrador.";
        $_SESSION['icono'] = "error";
        header('Location: ' . APP_URL . "/admin/configuraciones/secciones/edit.php?id=" . $id_seccion);
        exit();
    }
} else {
    // Manejo de error si las claves no están definidas
    session_start();
    $_SESSION['mensaje'] = "Error: datos incompletos, por favor verifique.";
    $_SESSION['icono'] = "error";
    header('Location: ' . APP_URL . "/admin/configuraciones/secciones");
    exit();
}
?>
