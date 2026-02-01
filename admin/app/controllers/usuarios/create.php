<?php
include ('../../../app/config.php');

// Validaciones básicas
$rol_id = $_POST['rol_id'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$password_repet = $_POST['password_repet'] ?? '';

// Datos personales (para roles que los necesitan)
$cedula = $_POST['cedula'] ?? '';
$nombres = $_POST['nombres'] ?? '';
$apellidos = $_POST['apellidos'] ?? '';
$telefono = $_POST['telefono'] ?? '';
$especialidad = $_POST['especialidad'] ?? '';
$direccion = $_POST['direccion'] ?? '';
$departamento = $_POST['departamento'] ?? '';

// Para administradores (si usas cédula simple)
$cedula_simple = $_POST['cedula_simple'] ?? '';

// 1. VALIDACIONES PREVIAS
if(empty($rol_id)) {
    session_start();
    $_SESSION['mensaje'] = "El rol es obligatorio";
    $_SESSION['icono'] = "error";
    ?><script>window.history.back();</script><?php
    exit();
}

if($password != $password_repet) {
    session_start();
    $_SESSION['mensaje'] = "Las contraseñas no coinciden";
    $_SESSION['icono'] = "error";
    ?><script>window.history.back();</script><?php
    exit();
}

// Validar campos obligatorios según rol
$roles_con_persona = [2, 3, 4, 5]; // IDs que necesitan datos personales
if(in_array($rol_id, $roles_con_persona)) {
    if(empty($cedula) || empty($nombres) || empty($apellidos)) {
        session_start();
        $_SESSION['mensaje'] = "Para este rol, cédula, nombres y apellidos son obligatorios";
        $_SESSION['icono'] = "error";
        ?><script>window.history.back();</script><?php
        exit();
    }
}

// Para administradores (rol 1), la cédula no es obligatoria
if($rol_id == 1 && !empty($cedula_simple)) {
    // Validar que tenga formato correcto si se ingresó
    if(!preg_match('/^\d{8}$/', $cedula_simple)) {
        session_start();
        $_SESSION['mensaje'] = "La cédula debe tener 8 dígitos";
        $_SESSION['icono'] = "error";
        ?><script>window.history.back();</script><?php
        exit();
    }
}

$password_hash = password_hash($password, PASSWORD_DEFAULT);
$fechaHora = date("Y-m-d H:i:s");
$estado_de_registro = 1;

try {
    $pdo->beginTransaction();

    // 1. CREAR USUARIO (SIEMPRE)
    $sentencia_usuario = $pdo->prepare('INSERT INTO usuarios
    (rol_id, email, password, fyh_creacion, estado)
    VALUES (:rol_id, :email, :password, :fyh_creacion, :estado)');

    $sentencia_usuario->bindParam(':rol_id', $rol_id);
    $sentencia_usuario->bindParam(':email', $email);
    $sentencia_usuario->bindParam(':password', $password_hash);
    $sentencia_usuario->bindParam(':fyh_creacion', $fechaHora);
    $sentencia_usuario->bindParam(':estado', $estado_de_registro);

    if(!$sentencia_usuario->execute()) {
        throw new Exception("Error al crear el usuario: " . implode(", ", $sentencia_usuario->errorInfo()));
    }

    // Obtener ID del usuario recién creado
    $usuario_id = $pdo->lastInsertId();

    // 2. CREAR PERSONA (si el rol lo requiere - opcional, dependiendo de tu sistema)
    // COMENTADO: Ya que no estás usando la tabla personas
    /*
    $persona_id = null;
    if(in_array($rol_id, $roles_con_persona)) {
        $sentencia_persona = $pdo->prepare('INSERT INTO personas
        (usuario_id, nombres, apellidos, ci, celular, direccion, fyh_creacion, estado)
        VALUES (:usuario_id, :nombres, :apellidos, :ci, :celular, :direccion, :fyh_creacion, :estado)');

        $sentencia_persona->bindParam(':usuario_id', $usuario_id);
        $sentencia_persona->bindParam(':nombres', $nombres);
        $sentencia_persona->bindParam(':apellidos', $apellidos);
        $sentencia_persona->bindParam(':ci', $cedula);
        $sentencia_persona->bindParam(':celular', $telefono);
        $sentencia_persona->bindParam(':direccion', $direccion);
        $sentencia_persona->bindParam(':fyh_creacion', $fechaHora);
        $sentencia_persona->bindParam(':estado', $estado_de_registro);

        if(!$sentencia_persona->execute()) {
            throw new Exception("Error al crear la persona: " . implode(", ", $sentencia_persona->errorInfo()));
        }

        $persona_id = $pdo->lastInsertId();
    }
    */

    // 3. CREAR REGISTRO ESPECÍFICO SEGÚN ROL
    switch($rol_id) {
        case 5: // DOCENTE
            $sentencia_profesor = $pdo->prepare('INSERT INTO profesores
            (id_usuario, cedula, nombres, apellidos, email, telefono, especialidad, estado, fecha_creacion, password)
            VALUES (:id_usuario, :cedula, :nombres, :apellidos, :email, :telefono, :especialidad, :estado, :fecha_creacion, :password)');

            $sentencia_profesor->bindParam(':id_usuario', $usuario_id);
            $sentencia_profesor->bindParam(':cedula', $cedula);
            $sentencia_profesor->bindParam(':nombres', $nombres);
            $sentencia_profesor->bindParam(':apellidos', $apellidos);
            $sentencia_profesor->bindParam(':email', $email);
            $sentencia_profesor->bindParam(':telefono', $telefono);
            $sentencia_profesor->bindParam(':especialidad', $especialidad);
            $sentencia_profesor->bindParam(':estado', $estado_de_registro);
            $sentencia_profesor->bindParam(':fecha_creacion', $fechaHora);
            $sentencia_profesor->bindParam(':password', $password_hash); // Guardar password encriptado

            if(!$sentencia_profesor->execute()) {
                throw new Exception("Error al crear el perfil de docente: " . implode(", ", $sentencia_profesor->errorInfo()));
            }
            break;

        case 2: // ADMINISTRATIVO
            // Si tienes tabla administrativos con estructura similar
            $sentencia_admin = $pdo->prepare('INSERT INTO administrativos
            (id_usuario, cedula, nombres, apellidos, email, telefono, departamento, estado, fecha_creacion, password)
            VALUES (:id_usuario, :cedula, :nombres, :apellidos, :email, :telefono, :departamento, :estado, :fecha_creacion, :password)');

            $sentencia_admin->bindParam(':id_usuario', $usuario_id);
            $sentencia_admin->bindParam(':cedula', $cedula);
            $sentencia_admin->bindParam(':nombres', $nombres);
            $sentencia_admin->bindParam(':apellidos', $apellidos);
            $sentencia_admin->bindParam(':email', $email);
            $sentencia_admin->bindParam(':telefono', $telefono);
            $sentencia_admin->bindParam(':departamento', $departamento);
            $sentencia_admin->bindParam(':estado', $estado_de_registro);
            $sentencia_admin->bindParam(':fecha_creacion', $fechaHora);
            $sentencia_admin->bindParam(':password', $password_hash);

            if(!$sentencia_admin->execute()) {
                throw new Exception("Error al crear el perfil administrativo: " . implode(", ", $sentencia_admin->errorInfo()));
            }
            break;

        case 3: // ESTUDIANTE
            // Si tienes tabla estudiantes con estructura similar
            $sentencia_estudiante = $pdo->prepare('INSERT INTO estudiantes
            (id_usuario, cedula, nombres, apellidos, email, telefono, estado, fecha_creacion, password)
            VALUES (:id_usuario, :cedula, :nombres, :apellidos, :email, :telefono, :estado, :fecha_creacion, :password)');
            
            $sentencia_estudiante->bindParam(':id_usuario', $usuario_id);
            $sentencia_estudiante->bindParam(':cedula', $cedula);
            $sentencia_estudiante->bindParam(':nombres', $nombres);
            $sentencia_estudiante->bindParam(':apellidos', $apellidos);
            $sentencia_estudiante->bindParam(':email', $email);
            $sentencia_estudiante->bindParam(':telefono', $telefono);
            $sentencia_estudiante->bindParam(':estado', $estado_de_registro);
            $sentencia_estudiante->bindParam(':fecha_creacion', $fechaHora);
            $sentencia_estudiante->bindParam(':password', $password_hash);
            
            if(!$sentencia_estudiante->execute()) {
                throw new Exception("Error al crear el perfil de estudiante: " . implode(", ", $sentencia_estudiante->errorInfo()));
            }
            break;
            
        case 4: // REPRESENTANTE
            // Si tienes tabla representantes con estructura similar
            $sentencia_representante = $pdo->prepare('INSERT INTO representantes
            (id_usuario, cedula, nombres, apellidos, email, telefono, estado, fecha_creacion, password)
            VALUES (:id_usuario, :cedula, :nombres, :apellidos, :email, :telefono, :estado, :fecha_creacion, :password)');
            
            $sentencia_representante->bindParam(':id_usuario', $usuario_id);
            $sentencia_representante->bindParam(':cedula', $cedula);
            $sentencia_representante->bindParam(':nombres', $nombres);
            $sentencia_representante->bindParam(':apellidos', $apellidos);
            $sentencia_representante->bindParam(':email', $email);
            $sentencia_representante->bindParam(':telefono', $telefono);
            $sentencia_representante->bindParam(':estado', $estado_de_registro);
            $sentencia_representante->bindParam(':fecha_creacion', $fechaHora);
            $sentencia_representante->bindParam(':password', $password_hash);
            
            if(!$sentencia_representante->execute()) {
                throw new Exception("Error al crear el perfil de representante: " . implode(", ", $sentencia_representante->errorInfo()));
            }
            break;
            
        case 1: // ADMINISTRADOR
            // Para administrador, solo crear usuario (sin datos personales obligatorios)
            // Si quieres guardar la cédula simple en administradores
            if(!empty($cedula_simple)) {
                $sentencia_admin_cedula = $pdo->prepare('UPDATE usuarios SET ci = :ci WHERE id_usuario = :id_usuario');
                $sentencia_admin_cedula->bindParam(':ci', $cedula_simple);
                $sentencia_admin_cedula->bindParam(':id_usuario', $usuario_id);
                $sentencia_admin_cedula->execute();
            }
            break;
    }

    $pdo->commit();

    session_start();
    $_SESSION['mensaje'] = "Usuario creado exitosamente";
    $_SESSION['icono'] = "success";
    header('Location:'.APP_URL."/admin/usuarios");
    exit;

} catch (Exception $exception) {
    $pdo->rollBack();
    session_start();
    $_SESSION['mensaje'] = "Error: " . $exception->getMessage();
    $_SESSION['icono'] = "error";
    error_log("Error al crear usuario: " . $exception->getMessage());
    ?><script>window.history.back();</script><?php
}