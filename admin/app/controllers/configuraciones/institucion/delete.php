<?php

include ('../../../../app/config.php');

$id_gestion = $_POST['id_gestion'];

$sentencia = $pdo->prepare("DELETE FROM gestiones where id_gestion=:id_gestion ");

$sentencia->bindParam('id_gestion',$id_gestion);

try{
    if($sentencia->execute()){
        session_start();
        $_SESSION['mensaje'] = "Se elimino los datos de la institución de la manera correcta en la base de datos";
        $_SESSION['icono'] = "success";
        header('Location:'.APP_URL."/admin/configuraciones/institucion");
    }else{
        session_start();
        $_SESSION['mensaje'] = "Error no se pudo eliminar en la base datos, comuniquese con el administrador";
        $_SESSION['icono'] = "error";
        ?><script>window.history.back();</script><?php
    }

}catch (Exception $exception){
    session_start();
    $_SESSION['mensaje'] = "Error no se pudo eliminar en la base datos, porque existe este registro en otras tablas";
    $_SESSION['icono'] = "error";
    ?><script>window.history.back();</script><?php
}


