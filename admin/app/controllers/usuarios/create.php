<?php
include ('../../../app/config.php');

$rol_id = $_POST['rol_id'];
$email = $_POST['email'];
$password = $_POST['password'];
$password_repet = $_POST['password_repet'];

// Datos adicionales para docentes
$cedula = $_POST['cedula'] ?? '';
$nombres = $_POST['nombres'] ?? '';
$apellidos = $_POST['apellidos'] ?? '';
$telefono = $_POST['telefono'] ?? '';
$especialidad = $_POST['especialidad'] ?? '';

if($password == $password_repet){
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $fechaHora = date("Y-m-d H:i:s");
    $estado_de_registro = 1;

    try {
        $pdo->beginTransaction();

        // 1. CREAR EN TABLA USUARIOS
        $sentencia = $pdo->prepare('INSERT INTO usuarios
        (rol_id, email, password, fyh_creacion, estado)
        VALUES (:rol_id, :email, :password, :fyh_creacion, :estado)');

        $sentencia->bindParam(':rol_id', $rol_id);
        $sentencia->bindParam(':email', $email);
        $sentencia->bindParam(':password', $password_hash);
        $sentencia->bindParam(':fyh_creacion', $fechaHora);
        $sentencia->bindParam(':estado', $estado_de_registro);

        if($sentencia->execute()) {
            
            // 2. SI ES DOCENTE, CREAR EN TABLA PROFESORES
            if($rol_id == 5) {
                // Validar campos obligatorios para docente
                if(empty($cedula) || empty($nombres) || empty($apellidos)) {
                    throw new Exception("Para usuarios docentes, los campos cédula, nombres y apellidos son obligatorios");
                }

                $sentencia_profesor = $pdo->prepare('INSERT INTO profesores
                (cedula, nombres, apellidos, email, telefono, especialidad, estado, usuario, password, fecha_creacion)
                VALUES (:cedula, :nombres, :apellidos, :email, :telefono, :especialidad, :estado, :usuario, :password, :fecha_creacion)');

                $sentencia_profesor->bindParam(':cedula', $cedula);
                $sentencia_profesor->bindParam(':nombres', $nombres);
                $sentencia_profesor->bindParam(':apellidos', $apellidos);
                $sentencia_profesor->bindParam(':email', $email);
                $sentencia_profesor->bindParam(':telefono', $telefono);
                $sentencia_profesor->bindParam(':especialidad', $especialidad);
                $sentencia_profesor->bindParam(':estado', $estado_de_registro);
                $sentencia_profesor->bindParam(':usuario', $email);
                $sentencia_profesor->bindParam(':password', $password_hash);
                $sentencia_profesor->bindParam(':fecha_creacion', $fechaHora);

                if(!$sentencia_profesor->execute()) {
                    throw new Exception("Error al crear el registro en la tabla de profesores");
                }
            }

            $pdo->commit();
            
            session_start();
            $mensaje = "Se registró el usuario correctamente ";
            if($rol_id == 5) {
                $mensaje .= " y se creó el perfil de docente";
            }
            $_SESSION['mensaje'] = $mensaje;
            $_SESSION['icono'] = "success";
            header('Location:'.APP_URL."/admin/usuarios");
            exit;

        } else {
            throw new Exception("Error al ejecutar la consulta de usuario");
        }

    } catch (Exception $exception) {
        $pdo->rollBack();
        session_start();
        $_SESSION['mensaje'] = "Error: " . $exception->getMessage();
        $_SESSION['icono'] = "error";
        ?><script>window.history.back();</script><?php
    }

} else {
    session_start();
    $_SESSION['mensaje'] = "Las contraseñas introducidas no son iguales";
    $_SESSION['icono'] = "error";
    ?><script>window.history.back();</script><?php
}
?>
