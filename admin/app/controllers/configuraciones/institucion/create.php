<?php
/**
 * Created by PhpStorm.
 * User: HILARIWEB
 * Date: 8/1/2024
 * Time: 10:15
 */

include ('../../../../app/config.php');

$nombre_institucion = $_POST['nombre_institucion'];
$direccion = $_POST['direccion'];
$telefono = $_POST['telefono'];
$celular = $_POST['celular'];
$correo = $_POST['correo'];

if($_FILES['file']['name'] != null){
    //echo "existe una imagen";
    $nombre_del_archivo =  date('Y-m-d-H-i-s').$_FILES['file']['name'];
    $location = "../../../../public/images/configuracion/".$nombre_del_archivo;
    move_uploaded_file($_FILES['file']['tmp_name'],$location);
    $logo = $nombre_del_archivo;
}else{
    //echo "no existe imagen";
    $logo = "";
}


$sentencia = $pdo->prepare('INSERT INTO configuracion_instituciones
         (nombre_institucion,logo,direccion,telefono,celular,correo, fyh_creacion, estado)
VALUES ( :nombre_institucion,:logo,:direccion,:telefono,:celular,:correo,:fyh_creacion,:estado)');

$sentencia->bindParam(':nombre_institucion',$nombre_institucion);
$sentencia->bindParam(':logo',$logo);
$sentencia->bindParam(':direccion',$direccion);
$sentencia->bindParam(':telefono',$telefono);
$sentencia->bindParam(':celular',$celular);
$sentencia->bindParam(':correo',$correo);
$sentencia->bindParam('fyh_creacion',$fechaHora);
$sentencia->bindParam('estado',$estado_de_registro);

if($sentencia->execute()){
    echo 'success';
    session_start();
    $_SESSION['mensaje'] = "Se registro los datos de configuración de la manera correcta en la base de datos";
    $_SESSION['icono'] = "success";
    header('Location:'.APP_URL."/admin/configuraciones/institucion");
//header('Location:' .$URL.'/');
}else{
    //echo 'error al registrar a la base de datos';
    session_start();
    $_SESSION['mensaje'] = "Error no se pudo registrar en la base datos, comuniquese con el administrador";
    $_SESSION['icono'] = "error";
    ?><script>window.history.back();</script><?php
}
