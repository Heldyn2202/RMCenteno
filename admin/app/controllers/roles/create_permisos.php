<?php


include ('../../../app/config.php');

$nombre_url = $_POST['nombre_url'];
$url = $_POST['url'];

$sentencia = $pdo->prepare('INSERT INTO permisos
(nombre_url,url,fyh_creacion, estado)
VALUES ( :nombre_url,:url,:fyh_creacion,:estado)');

$sentencia->bindParam(':nombre_url',$nombre_url);
$sentencia->bindParam(':url',$url);
$sentencia->bindParam('fyh_creacion',$fechaHora);
$sentencia->bindParam('estado',$estado_de_registro);

if($sentencia->execute()){
    echo 'success';
    session_start();
    $_SESSION['mensaje'] = "Se registro el permiso de la manera correcta en la base de datos";
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