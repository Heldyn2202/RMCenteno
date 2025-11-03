<?php
include ('../../../app/config.php');

$id_usuario = $_POST['id_usuario'];
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

$fechaHora = date("Y-m-d H:i:s");

try {
    $pdo->beginTransaction();

    // 1. ACTUALIZAR TABLA USUARIOS
    if($password == ""){
        $sentencia = $pdo->prepare("UPDATE usuarios 
        SET rol_id=:rol_id,
            email=:email,
            fyh_actualizacion=:fyh_actualizacion
        WHERE id_usuario=:id_usuario ");

        $sentencia->bindParam(':rol_id',$rol_id);
        $sentencia->bindParam(':email',$email);
        $sentencia->bindParam(':fyh_actualizacion',$fechaHora);
        $sentencia->bindParam(':id_usuario',$id_usuario);

        if(!$sentencia->execute()){
            throw new Exception("Error no se pudo actualizar , comuniquese con el administrador");
        }

    } else {
        if($password == $password_repet){
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            $sentencia = $pdo->prepare("UPDATE usuarios 
            SET rol_id=:rol_id,
                email=:email,
                password=:password,
                fyh_actualizacion=:fyh_actualizacion
            WHERE id_usuario=:id_usuario ");

            $sentencia->bindParam(':rol_id',$rol_id);
            $sentencia->bindParam(':email',$email);
            $sentencia->bindParam(':password',$password_hash);
            $sentencia->bindParam(':fyh_actualizacion',$fechaHora);
            $sentencia->bindParam(':id_usuario',$id_usuario);

            if(!$sentencia->execute()){
                throw new Exception("Error no se pudo actualizar , comuniquese con el administrador");
            }
        } else {
            throw new Exception("Las contraseñas introducidas no son iguales");
        }
    }

    // 2. GESTIONAR TABLA PROFESORES (SINCRONIZACIÓN)
    if($rol_id == 5) {
        // Validar campos obligatorios para docente
        if(empty($cedula) || empty($nombres) || empty($apellidos)) {
            throw new Exception("Para usuarios docentes, los campos cédula, nombres y apellidos son obligatorios");
        }

        // Verificar si ya existe en profesores
        $sql_check = "SELECT id_profesor FROM profesores WHERE email = :email";
        $query_check = $pdo->prepare($sql_check);
        $query_check->bindParam(':email', $email);
        $query_check->execute();
        $existe_profesor = $query_check->fetch(PDO::FETCH_ASSOC);

        if($existe_profesor) {
            // Actualizar profesor existente
            $sql_profesor = "UPDATE profesores SET 
                cedula = :cedula, 
                nombres = :nombres, 
                apellidos = :apellidos,
                telefono = :telefono, 
                especialidad = :especialidad,
                fecha_actualizacion = NOW()
                WHERE email = :email";
                
            $query_profesor = $pdo->prepare($sql_profesor);
            $query_profesor->bindParam(':cedula', $cedula);
            $query_profesor->bindParam(':nombres', $nombres);
            $query_profesor->bindParam(':apellidos', $apellidos);
            $query_profesor->bindParam(':telefono', $telefono);
            $query_profesor->bindParam(':especialidad', $especialidad);
            $query_profesor->bindParam(':email', $email);
            
            if(!$query_profesor->execute()) {
                throw new Exception("Error al actualizar el registro en la tabla de profesores");
            }
            
        } else {
            // Crear nuevo profesor
            $sql_profesor = "INSERT INTO profesores (
                cedula, nombres, apellidos, email, telefono, especialidad, 
                estado, usuario, fecha_creacion
            ) VALUES (
                :cedula, :nombres, :apellidos, :email, :telefono, :especialidad,
                1, :usuario, NOW()
            )";
            
            $query_profesor = $pdo->prepare($sql_profesor);
            $query_profesor->bindParam(':cedula', $cedula);
            $query_profesor->bindParam(':nombres', $nombres);
            $query_profesor->bindParam(':apellidos', $apellidos);
            $query_profesor->bindParam(':email', $email);
            $query_profesor->bindParam(':telefono', $telefono);
            $query_profesor->bindParam(':especialidad', $especialidad);
            $query_profesor->bindParam(':usuario', $email);
            
            if(!$query_profesor->execute()) {
                throw new Exception("Error al crear el registro en la tabla de profesores");
            }
        }
        
    } else {
        // Si ya no es docente, desactivar en profesores (opcional)
        // Puedes comentar esta parte si no quieres desactivar profesores
        $sql_desactivar = "UPDATE profesores SET estado = 0 WHERE email = :email";
        $query_desactivar = $pdo->prepare($sql_desactivar);
        $query_desactivar->bindParam(':email', $email);
        $query_desactivar->execute();
    }

    $pdo->commit();
    
    session_start();
    $mensaje = "✅ Se actualizó el usuario correctamente ";
    if($rol_id == 5) {
        $mensaje .= " y se sincronizó con el perfil de docente";
    }
    $_SESSION['mensaje'] = $mensaje;
    $_SESSION['icono'] = "success";
    header('Location:'.APP_URL."/admin/usuarios");
    exit;

} catch (Exception $exception) {
    $pdo->rollBack();
    session_start();
    $_SESSION['mensaje'] = "❌ Error: " . $exception->getMessage();
    $_SESSION['icono'] = "error";
    ?><script>window.history.back();</script><?php
}
?>

