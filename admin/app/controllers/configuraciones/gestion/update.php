<?php  
include ('../../../../app/config.php');  

$id_gestion = $_POST['id_gestion'];  
$desde = $_POST['desde'];  
$hasta = $_POST['hasta'];  
$estado = $_POST['estado'];  

// Convertir el estado a un valor numérico  
$estadoNumerico = ($estado == "ACTIVO") ? 1 : 0;  

// Actualizar el registro seleccionado  
$sentencia = $pdo->prepare('UPDATE gestiones  
    SET desde = :desde,  
        hasta = :hasta,  
        fyh_actualizacion = :fyh_actualizacion,  
        estado = :estado  
    WHERE id_gestion = :id_gestion');  

$fechaHora = date('Y-m-d H:i:s');  
$sentencia->bindParam(':desde', $desde);  
$sentencia->bindParam(':hasta', $hasta);  
$sentencia->bindParam(':fyh_actualizacion', $fechaHora);  
$sentencia->bindParam(':estado', $estadoNumerico);  
$sentencia->bindParam(':id_gestion', $id_gestion);  

if ($sentencia->execute()) {  
    if ($estadoNumerico == 1) { // Si el nuevo registro es activo  
        // Inactivar todos los demás registros activos  
        $updateQuery = $pdo->prepare("UPDATE gestiones SET estado = 0 WHERE estado = 1 AND id_gestion != :id_gestion");  
        $updateQuery->bindParam(':id_gestion', $id_gestion);  
        $updateQuery->execute();  
    }  

    echo 'success';  
    session_start();  
    $_SESSION['mensaje'] = "Se actualizó el periodo académico de manera correcta.";  
    $_SESSION['icono'] = "success";  
    header('Location:' . APP_URL . "/admin/configuraciones/gestion");  
} else {  
    echo 'error al actualizar la base de datos';  
    session_start();  
    $_SESSION['mensaje'] = "Error: no se pudo actualizar el periodo académico , comuníquese con el administrador";  
    $_SESSION['icono'] = "error";  
    ?><script>window.history.back();</script><?php  
}  
?>