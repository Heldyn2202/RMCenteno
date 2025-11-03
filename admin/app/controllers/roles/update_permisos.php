<?php


include ('../../../app/config.php');

$id_permiso = $_POST['id_permiso'];
$nombre_url = $_POST['nombre_url'];
$url = $_POST['url'];

$sentencia = $pdo->prepare('UPDATE permisos
 set nombre_url=:nombre_url,
     url=:url,
     fyh_actualizacion=:fyh_actualizacion
where id_permiso=:id_permiso ');

$sentencia->bindParam(':nombre_url',$nombre_url);
$sentencia->bindParam(':url',$url);
$sentencia->bindParam('fyh_actualizacion',$fechaHora);
$sentencia->bindParam('id_permiso',$id_permiso);

if($sentencia->execute()){
    echo 'success';
    session_start();
    $_SESSION['mensaje'] = "Se actualizó el permiso de la manera correcta en la base de datos";
    $_SESSION['icono'] = "success";
    header('Location:'.APP_URL."/admin/roles/permisos.php");
//header('Location:' .$URL.'/');
}else{
    echo 'error al registrar a la base de datos';
    session_start();
    $_SESSION['mensaje'] = "Error no se pudo registrar en la base datos, comuniquese con el administrador";
    $_SESSION['icono'] = "error";
    ?><script>window.history.back();</script><?php
}