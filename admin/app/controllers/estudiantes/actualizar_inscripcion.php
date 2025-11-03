<?php
session_start();
include('../../config.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_inscripcion = $_POST['id_inscripcion'];
    $id_estudiante = $_POST['id_estudiante'];
    $id_gestion = $_POST['id_gestion'];
    $id_grado = $_POST['grado'];
    $id_seccion = $_POST['id_seccion'];
    $talla_camisa = $_POST['talla_camisa'];
    $talla_pantalon = $_POST['talla_pantalon'];
    $talla_zapatos = $_POST['talla_zapatos'];
    
    // Obtener el turno y nivel del formulario
    $turno = $_POST['turno_id'];
    $nivel = $_POST['nivel_id'];
    
    $fechaHora = date('Y-m-d H:i:s');
    
    try {
        $pdo->beginTransaction();
        
        // Actualizar la inscripción
        $sql_update = "UPDATE inscripciones 
                      SET id_grado = :id_grado, 
                          id_seccion = :id_seccion, 
                          nivel = :nivel, 
                          turno = :turno, 
                          talla_camisa = :talla_camisa, 
                          talla_pantalon = :talla_pantalon, 
                          talla_zapatos = :talla_zapatos,
                          fyh_actualizacion = :fyh_actualizacion
                      WHERE id_inscripcion = :id_inscripcion";
        
        $sentencia_update = $pdo->prepare($sql_update);
        $sentencia_update->bindParam(':id_grado', $id_grado);
        $sentencia_update->bindParam(':id_seccion', $id_seccion);
        $sentencia_update->bindParam(':nivel', $nivel);
        $sentencia_update->bindParam(':turno', $turno);
        $sentencia_update->bindParam(':talla_camisa', $talla_camisa);
        $sentencia_update->bindParam(':talla_pantalon', $talla_pantalon);
        $sentencia_update->bindParam(':talla_zapatos', $talla_zapatos);
        $sentencia_update->bindParam(':fyh_actualizacion', $fechaHora);
        $sentencia_update->bindParam(':id_inscripcion', $id_inscripcion);
        
        if ($sentencia_update->execute()) {
            $pdo->commit();
            $_SESSION['message'] = "Inscripción actualizada correctamente";
            header('Location: ' . APP_URL . '/admin/estudiantes/Lista_de_estudiante.php');
            exit();
        } else {
            throw new Exception("Error al actualizar la inscripción");
        }
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Error al actualizar la inscripción: " . $e->getMessage();
        header('Location: ' . APP_URL . '/admin/estudiantes/actualizar_inscripcion.php?id=' . $id_estudiante);
        exit();
    }
} else {
    header('Location: ' . APP_URL . '/admin/estudiantes/Lista_de_estudiante.php');
    exit();
}
?>