<?php  
session_start();  // Inicia la sesión  

include ('../../../app/config.php');  // Conexión a la base de datos  

if ($_SERVER["REQUEST_METHOD"] == "POST") {  
    // Asegúrate de capturar los datos enviados  
    $id_representante = $_POST['id_representante'];  
    $tipo_cedula = $_POST['tipo_cedula'];  
    $cedula = $_POST['cedula'];  
    $nombres = $_POST['nombres'];  
    $apellidos = $_POST['apellidos'];  
    $fecha_nacimiento = $_POST['fecha_nacimiento'];  
    $estado_civil = $_POST['estado_civil'];  
    $genero = $_POST['genero'];  
    $correo_electronico = $_POST['correo_electronico'];  
    $tipo_sangre = $_POST['tipo_sangre'];  
    $direccion = $_POST['direccion'];  
    $numeros_telefonicos = $_POST['numeros_telefonicos'];  
    $estatus = $_POST['estatus'];  

    // Validar el formato de la fecha de nacimiento (DD/MM/YYYY)  
    $regex_fecha = '/^(0[1-9]|[12][0-9]|3[01])\/(0[1-9]|1[0-2])\/(\d{4})$/';  
    if (!preg_match($regex_fecha, $fecha_nacimiento)) {  
        $_SESSION['mensaje'] = "Fecha de nacimiento inválida.";  
        $_SESSION['icono'] = "error";  
        header("Location: " . APP_URL . "/admin/representantes/edit.php?id=" . $id_representante);  
        exit();  
    }  

    // Convertir fecha a formato Y-m-d  
    list($dia, $mes, $año) = explode('/', $fecha_nacimiento);  
    $fecha_nacimiento = sprintf('%04d-%02d-%02d', $año, $mes, $dia);  

    // Verificar si la cédula ya está registrada  
    $checkSql = "SELECT COUNT(*) FROM representantes WHERE cedula = ? AND id_representante != ?";  
    $checkStmt = $pdo->prepare($checkSql);  
    $checkStmt->execute([$cedula, $id_representante]);  
    $exists = $checkStmt->fetchColumn();  
    
    if ($exists > 0) {  
        // Si la cédula ya existe, almacena el mensaje en la sesión y redirige  
        $_SESSION['mensaje'] = "La cédula ya está registrada en el sistema.";  
        $_SESSION['icono'] = "error";  
        header("Location: " . APP_URL . "/admin/representantes/edit.php?id=" . $id_representante);  
        exit();  
    }

    // Aquí va la consulta SQL para actualizar el registro  
    $sql = "UPDATE representantes SET   
            tipo_cedula = ?,   
            cedula = ?,   
            nombres = ?,   
            apellidos = ?,   
            fecha_nacimiento = ?,   
            estado_civil = ?,   
            genero = ?,   
            correo_electronico = ?,   
            tipo_sangre = ?,   
            direccion = ?,   
            numeros_telefonicos = ?,   
            estatus = ?   
            WHERE id_representante = ?";  

    // Preparar y ejecutar la consulta  
    $stmt = $pdo->prepare($sql);  
    
    if ($stmt->execute([$tipo_cedula, $cedula, $nombres, $apellidos, $fecha_nacimiento, $estado_civil, $genero, $correo_electronico, $tipo_sangre, $direccion, $numeros_telefonicos, $estatus, $id_representante])) {  
        // Si se registra exitosamente  
        $_SESSION['mensaje'] = "Se actualizo al representante de manera correcta";  
        $_SESSION['icono'] = "success";  
        // Limpiar datos de sesión  
        unset($_SESSION['datos_representante']);  
        header("Location: " . APP_URL . "/admin/representantes");  
        exit();  
    } else {  
        // Si hubo un error al registrar  
        $_SESSION['mensaje'] = "Error: no se pudo registrar en la base de datos. Comuníquese con el administrador.";  
        $_SESSION['icono'] = "error";  
        header("Location: " . APP_URL . "/admin/representantes/edit.php");  
        exit();  
    }  
} else {  
    echo "Método no permitido.";  
    exit();  
}  