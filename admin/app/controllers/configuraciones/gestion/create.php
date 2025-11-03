<?php  
include ('../../../../app/config.php');  

$desde = $_POST['desde'];  
$hasta = $_POST['hasta'];  
$estado = $_POST['estado'];  
if($estado=="ACTIVO"){  
    $estado = 1;  
}else{  
    $estado = 0;  
}  
 
// Realizar el insert  
$sentencia = $pdo->prepare('INSERT INTO gestiones (desde, hasta, fyh_creacion, estado) VALUES ( :desde, :hasta, :fyh_creacion, :estado)');  
$sentencia->bindParam(':desde', $desde);  
$sentencia->bindParam(':hasta', $hasta);  
$sentencia->bindParam(':fyh_creacion', $fechaHora);  
$sentencia->bindParam(':estado', $estado);  

if ($sentencia->execute()) {  
    // Obtener el último ID creado  
    $id_gestion = $pdo->lastInsertId();  

    // Actualiza los estados de las gestiones con el mismo estado que el nuevo registro  
    $result = $pdo->prepare('UPDATE gestiones SET estado = 0 WHERE estado = ? AND id_gestion != ?');  
    $result->execute([$estado, $id_gestion]);  

    // Mostrar mensaje de éxito  
    echo 'success';  
    session_start();  
    $_SESSION['mensaje'] = "Se registro el período académico de manera correcta en la base de datos";  
    $_SESSION['icono'] = "success";  

     

    // Redirigir a la página de gestiones con los registros ordenados  
    header('Location:'.APP_URL."/admin/configuraciones/gestion?gestiones=".urlencode(serialize($gestiones)));  
} else {  
    // Mostrar mensaje de error  
    echo 'error al registrar a la base de datos';  
    session_start();  
    $_SESSION['mensaje'] = "Error, no se pudo registrar en la base de datos, comuníquese con el administrador";  
    $_SESSION['icono'] = "error";  
    ?>  
    <script>window.history.back();</script>  
    <?php  
}  
?>