<?php
include ('../../../app/config.php');

$id_nivel = $_POST['id_nivel'];
$gestion_id = $_POST['gestion_id'];
$nivel = $_POST['nivel'];

$sentencia = $pdo->prepare('UPDATE niveles
 SET gestion_id=:gestion_id,
     nivel=:nivel,
     fyh_actualizacion=:fyh_actualizacion
WHERE id_nivel=:id_nivel ');

$sentencia->bindParam(':gestion_id',$gestion_id);
$sentencia->bindParam(':nivel',$nivel);
$sentencia->bindParam('fyh_actualizacion',$fechaHora);
$sentencia->bindParam('id_nivel',$id_nivel);

if($sentencia->execute()){
    echo 'success';
    session_start();
    $_SESSION['mensaje'] = "Se actualizó el nivel de la manera correcta en la base de datos";
    $_SESSION['icono'] = "success";
    header('Location:'.APP_URL."/admin/niveles");
//header('Location:' .$URL.'/');
}else{
    echo 'error al registrar a la base de datos';
    session_start();
    $_SESSION['mensaje'] = "Error no se pudo actualizar en la base datos, comuniquese con el administrador";
    $_SESSION['icono'] = "error";
    ?><script>window.history.back();</script><?php
}