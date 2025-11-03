<?php
ob_start();
date_default_timezone_set("America/Guayaquil");

$action = $_GET['action'];
include 'admin_class.php';
$crud = new Action();

if($action == 'login'){
    $login = $crud->login();
    if($login == 1){
        echo json_encode(['success' => true, 'message' => 'Login exitoso']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Usuario o contraseña incorrectos']);
    }
}
if($action == 'logout'){
    $logout = $crud->logout();
    if($logout)
        echo json_encode(['success' => true, 'message' => 'Sesión cerrada']);
}

ob_end_flush();
?>