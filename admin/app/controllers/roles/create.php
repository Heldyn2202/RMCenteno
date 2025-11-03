<?php


include ('../../../app/config.php');

$nombre_rol = $_POST['nombre_rol'];
$nombre_rol = mb_strtoupper($nombre_rol,'UTF-8');

if($nombre_rol == ""){
    session_start();
    $_SESSION['mensaje'] = "Llene el campo nombre del rol";
    $_SESSION['icono'] = "error";
    header('Location:'.APP_URL."/admin/roles/create.php");
}else{
    $sentencia = $pdo->prepare("INSERT INTO roles 
       ( nombre_rol, fyh_creacion, estado) 
VALUES (:nombre_rol,:fyh_creacion,:estado) ");

    $sentencia->bindParam('nombre_rol',$nombre_rol);
    $sentencia->bindParam('fyh_creacion',$fechaHora);
    $sentencia->bindParam('estado',$estado_de_registro);

    try{
        if($sentencia->execute()){
            session_start();
            $_SESSION['mensaje'] = "Se registro el rol de la manera correcta en la base de datos";
            $_SESSION['icono'] = "success";
            header('Location:'.APP_URL."/admin/roles");
        }else{
            session_start();
            $_SESSION['mensaje'] = "Error no se pudo registrar en la base datos, comuniquese con el administrador";
            $_SESSION['icono'] = "error";
            header('Location:'.APP_URL."/admin/roles/create.php");
        }
    }catch (Exception $exception){
        session_start();
        $_SESSION['mensaje'] = "Esta rol ya existe en la base de datos";
        $_SESSION['icono'] = "error";
        header('Location:'.APP_URL."/admin/roles/create.php");
    }



}


