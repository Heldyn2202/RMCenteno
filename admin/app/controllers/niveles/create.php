<?php
include ('../../../app/config.php');

$gestion_id = $_POST['gestion_id'];
$nivel = $_POST['nivel'];

$sentencia = $pdo->prepare('INSERT INTO niveles
(gestion_id,nivel,fyh_creacion,estado)
VALUES ( :gestion_id,:nivel,:fyh_creacion,:estado)');

$sentencia->bindParam(':gestion_id',$gestion_id);
$sentencia->bindParam(':nivel',$nivel);
$sentencia->bindParam('fyh_creacion',$fechaHora);
$sentencia->bindParam('estado',$estado);

if($sentencia->execute()){
    echo 'success';
    session_start();
    $_SESSION['mensaje'] = "Se registro el nivel de la manera correcta en la base de datos";
    $_SESSION['icono'] = "success";
    header('Location:'.APP_URL."/admin/niveles");
//header('Location:' .$URL.'/');
}else{
    echo 'error al registrar a la base de datos';
    session_start();
    $_SESSION['mensaje'] = "Error no se pudo registrar en la base datos, comuniquese con el administrador";
    $_SESSION['icono'] = "error";
    ?><script>window.history.back();</script><?php
}