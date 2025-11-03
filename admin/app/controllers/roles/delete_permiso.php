<?php

include ('../../../app/config.php');

$id_permiso = $_POST['id_permiso'];

$sentencia = $pdo->prepare("DELETE FROM permisos where id_permiso=:id_permiso ");

$sentencia->bindParam('id_permiso',$id_permiso);

try{
    if($sentencia->execute()){
        session_start();
        $_SESSION['mensaje'] = "Se elimino el permiso de la manera correcta en la base de datos";
        $_SESSION['icono'] = "success";
        header('Location:'.APP_URL."/admin/roles/permisos.php");
    }else{
        session_start();
        $_SESSION['mensaje'] = "Error no se pudo eliminar en la base datos, comuniquese con el administrador";
        $_SESSION['icono'] = "error";
        header('Location:'.APP_URL."/admin/roles/permisos.php");
    }
}catch (Exception $exception){
    session_start();
    $_SESSION['mensaje'] = "Error no se pudo eliminar en la base datos, porque existe este registro en otras tablas";
    $_SESSION['icono'] = "error";
    header('Location:'.APP_URL."/admin/roles/permisos.php");
}
