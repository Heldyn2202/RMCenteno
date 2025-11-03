<?php

 
include ('../../../app/config.php');

$id_rol_permiso = $_POST['id_rol_permiso'];

$sentencia = $pdo->prepare("DELETE FROM roles_permisos where id_rol_permiso=:id_rol_permiso ");

$sentencia->bindParam('id_rol_permiso',$id_rol_permiso);

if($sentencia->execute()){
    session_start();
    $_SESSION['mensaje'] = "Se elimino el permiso de la manera correcta en la base de datos";
    $_SESSION['icono'] = "success";
    header('Location:'.APP_URL."/admin/roles");
}else{
    session_start();
    $_SESSION['mensaje'] = "Error no se pudo eliminar en la base datos, comuniquese con el administrador";
    $_SESSION['icono'] = "error";
    header('Location:'.APP_URL."/admin/roles");
}
