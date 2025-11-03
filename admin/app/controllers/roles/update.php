<?php

include ('../../../app/config.php');

$id_rol = $_POST['id_rol'];
$nombre_rol = $_POST['nombre_rol'];
$nombre_rol = mb_strtoupper($nombre_rol,'UTF-8');

if($nombre_rol == ""){
    session_start();
    $_SESSION['mensaje'] = "Llene el campo nombre del rol";
    $_SESSION['icono'] = "error";
    header('Location:'.APP_URL."/admin/roles/edit.php?id=".$id_rol);
}else{
    $sentencia = $pdo->prepare("UPDATE roles 
    SET nombre_rol=:nombre_rol,
        fyh_actualizacion=:fyh_actualizacion
  WHERE id_rol=:id_rol ");

    $sentencia->bindParam('nombre_rol',$nombre_rol);
    $sentencia->bindParam('fyh_actualizacion',$fechaHora);
    $sentencia->bindParam('id_rol',$id_rol);

    try{
        if($sentencia->execute()){
            session_start();
            $_SESSION['mensaje'] = "Se actualizó el rol de la manera correcta en la base de datos";
            $_SESSION['icono'] = "success";
            header('Location:'.APP_URL."/admin/roles");
        }else{
            session_start();
            $_SESSION['mensaje'] = "Error no se pudo actualizar en la base datos, comuniquese con el administrador";
            $_SESSION['icono'] = "error";
            header('Location:'.APP_URL."/admin/roles/edit.php?id=".$id_rol);
        }
    }catch (Exception $exception){
        session_start();
        $_SESSION['mensaje'] = "Esta rol ya existe en la base de datos";
        $_SESSION['icono'] = "error";
        header('Location:'.APP_URL."/admin/roles/edit.php?id=".$id_rol);
    }



}


