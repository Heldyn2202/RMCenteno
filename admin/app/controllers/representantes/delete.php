<?php  
session_start(); // Asegúrate de iniciar la sesión

include ('../../../app/config.php');

$id_representante = $_POST['id_representante'];  
$accion = $_POST['action'];   // 'inhabilitar' o 'habilitar'

// Primero, obtenemos el estatus actual del representante  
$consulta = $pdo->prepare("SELECT estatus FROM representantes WHERE id_representante = :id_representante");  
$consulta->bindParam('id_representante', $id_representante);  
$consulta->execute();  
$resultado = $consulta->fetch(PDO::FETCH_ASSOC);  

if ($resultado) {  
    $estatus_actual = strtolower($resultado['estatus']);  

    // Determinamos la acción a realizar  
    if ($estatus_actual === 'activo' && $accion === 'Inhabilitar') {  
        $nuevo_estatus = 'inactivo';  
        $_SESSION['mensaje'] = 'El representante ha sido inhabilitado correctamente.'; // Mensaje para inhabilitar
        $_SESSION['icono'] = 'success';  
    } elseif ($estatus_actual === 'inactivo' && $accion === 'Habilitar') {  
        $nuevo_estatus = 'activo';  
        $_SESSION['mensaje'] = 'El representante ha sido habilitado correctamente.'; // Mensaje para habilitar
        $_SESSION['icono'] = 'success';  
    } else {  
        $_SESSION['mensaje'] = 'Acción no válida.';  
        $_SESSION['icono'] = 'error';  
        header('Location: ' . APP_URL . "/admin/representantes");  
        exit;  
    }  

    // Actualizamos el estatus  
    $sentencia = $pdo->prepare("UPDATE representantes SET estatus = :nuevo_estatus WHERE id_representante = :id_representante");  
    $sentencia->bindParam('nuevo_estatus', $nuevo_estatus);  
    $sentencia->bindParam('id_representante', $id_representante);  

    try {  
        if ($sentencia->execute()) {  
            // Si se actualiza exitosamente  
            header("Location: " . APP_URL . "/admin/representantes");  
            exit();  
        } else {  
            // Si hubo un error al actualizar  
            $_SESSION['mensaje'] = "Error: no se pudo actualizar en la base de datos. Comuníquese con el administrador.";  
            $_SESSION['icono'] = 'error';  
            header("Location: " . APP_URL . "/admin/representantes");  
            exit();  
        }  
    } catch (Exception $exception) {  
        $_SESSION['mensaje'] = "Error: no se pudo actualizar en la base de datos. Comuníquese con el administrador.";  
        $_SESSION['icono'] = 'error';  
        header("Location: " . APP_URL . "/admin/representantes");  
        exit();  
    }  
} else {  
    $_SESSION['mensaje'] = "Error: representante no encontrado.";  
    $_SESSION['icono'] = 'error';  
    header("Location: " . APP_URL . "/admin/representantes");  
    exit();  
}  
?>