<?php  
session_start();  
include('../../../app/config.php');  

if ($_SERVER['REQUEST_METHOD'] === 'POST') {  
    // Obtener los datos del formulario  
    $id_estudiante = $_POST['id_estudiante'];  
    $tipo_cedula = $_POST['tipo_cedula'];  
    $cedula = $_POST['cedula'];  
    $nombres = $_POST['nombres'];  
    $apellidos = $_POST['apellidos'];  
    $fecha_nacimiento = $_POST['fecha_nacimiento'];  
    $genero = $_POST['genero'];  
    $correo_electronico = $_POST['correo_electronico'];  
    $direccion = $_POST['direccion'];  
    $numeros_telefonicos = $_POST['numeros_telefonicos'];  
    $estatus = $_POST['estatus'];  
    $posicion_hijo = $_POST['posicion_hijo'];  
    $cedula_escolar = $_POST['cedula_escolar'];  

    // Validaciones  
    if (empty($id_estudiante) || !is_numeric($id_estudiante)) {  
        $_SESSION['message'] = 'Error: ID de estudiante no válido.';  
        header('Location: ' . APP_URL . '/admin/estudiantes/edit.php?id=' . $id_estudiante);  
        exit();  
    }  
    if (!filter_var($correo_electronico, FILTER_VALIDATE_EMAIL)) {  
        $_SESSION['message'] = 'Error: Correo electrónico no válido.';  
        header('Location: ' . APP_URL . '/admin/estudiantes/edit.php?id=' . $id_estudiante);  
        exit();  
    }  

    // Verificación de cédula duplicada solo si no está vacía  
    if (!empty($cedula)) {  
        $sql_check_cedula = "SELECT COUNT(*) FROM estudiantes WHERE cedula = :cedula AND id_estudiante != :id_estudiante";  
        $stmt_check_cedula = $pdo->prepare($sql_check_cedula);  
        $stmt_check_cedula->bindParam(':cedula', $cedula);  
        $stmt_check_cedula->bindParam(':id_estudiante', $id_estudiante);  
        $stmt_check_cedula->execute();  
        $existe_duplicado_cedula = $stmt_check_cedula->fetchColumn();  

        if ($existe_duplicado_cedula > 0) {  
            $_SESSION['message'] = 'Error: La cédula de identidad ya está registrada en otro estudiante.';  
            header('Location: ' . APP_URL . '/admin/estudiantes/edit.php?id=' . $id_estudiante);  
            exit();  
        }  
    }  

    // *** Nueva verificación de cédula escolar duplicada ***
    if (!empty($cedula_escolar)) {
    $sql_check_cedula_escolar = "SELECT COUNT(*) FROM estudiantes WHERE cedula_escolar = :cedula_escolar AND id_estudiante != :id_estudiante";  
    $stmt_check_cedula_escolar = $pdo->prepare($sql_check_cedula_escolar);  
    $stmt_check_cedula_escolar->bindParam(':cedula_escolar', $cedula_escolar);  
    $stmt_check_cedula_escolar->bindParam(':id_estudiante', $id_estudiante);  
    $stmt_check_cedula_escolar->execute();  
    $existe_duplicado_cedula_escolar = $stmt_check_cedula_escolar->fetchColumn();  

    if ($existe_duplicado_cedula_escolar > 0) {  
        $_SESSION['message'] = 'Error: La cédula escolar ya está registrada en otro estudiante.';  
        header('Location: ' . APP_URL . '/admin/estudiantes/edit.php?id=' . $id_estudiante);  
        exit();  
    } 
}  
    // *** Fin de la nueva verificación de cédula escolar ****  

    // Preparar la consulta de actualización  
    $sql = "UPDATE estudiantes SET   
                tipo_cedula = :tipo_cedula,  
                cedula = :cedula,  
                nombres = :nombres,  
                apellidos = :apellidos,  
                fecha_nacimiento = :fecha_nacimiento,  
                genero = :genero,  
                correo_electronico = :correo_electronico,  
                direccion = :direccion,  
                numeros_telefonicos = :numeros_telefonicos,  
                estatus = :estatus,  
                posicion_hijo = :posicion_hijo,  
                cedula_escolar = :cedula_escolar  
            WHERE id_estudiante = :id_estudiante";  

    try {  
        $stmt = $pdo->prepare($sql);  

        // Vincular los parámetros  
        $stmt->bindParam(':tipo_cedula', $tipo_cedula);  
        $stmt->bindParam(':cedula', $cedula);  
        $stmt->bindParam(':nombres', $nombres);  
        $stmt->bindParam(':apellidos', $apellidos);  
        $stmt->bindParam(':fecha_nacimiento', $fecha_nacimiento);  
        $stmt->bindParam(':genero', $genero);  
        $stmt->bindParam(':correo_electronico', $correo_electronico);  
        $stmt->bindParam(':direccion', $direccion);  
        $stmt->bindParam(':numeros_telefonicos', $numeros_telefonicos);  
        $stmt->bindParam(':estatus', $estatus);  
        $stmt->bindParam(':posicion_hijo', $posicion_hijo);  
        $stmt->bindParam(':cedula_escolar', $cedula_escolar);  
        $stmt->bindParam(':id_estudiante', $id_estudiante);  

        // Ejecutar la consulta  
        $stmt->execute();  
        $_SESSION['message'] = 'El estudiante se ha actualizado correctamente.';  
        header('Location: ' . APP_URL . '/admin/estudiantes/Lista_de_estudiante.php');  
        exit();  
    } catch (PDOException $e) {  
        error_log($e->getMessage());  
        $_SESSION['message'] = 'Error: No se pudo actualizar el estudiante.';  
        header('Location: ' . APP_URL . '/admin/estudiantes/Lista_de_estudiante.php');  
        exit();  
    }  
} else {  
    header('Location: ' . APP_URL . '/admin/estudiantes/Lista_de_estudiante.php');  
    exit();  
}  
?>