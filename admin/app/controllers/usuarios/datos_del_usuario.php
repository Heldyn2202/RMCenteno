<?php

$id_usuario = $_GET['id'];

$sql = "SELECT u.*, r.nombre_rol, r.id_rol as rol_id 
        FROM usuarios u 
        INNER JOIN roles r ON u.rol_id = r.id_rol 
        WHERE u.id_usuario = :id_usuario";
$query = $pdo->prepare($sql);
$query->bindParam(':id_usuario', $id_usuario);
$query->execute();

$usuarios = $query->fetchAll(PDO::FETCH_ASSOC);

foreach ($usuarios as $usuario){
    $id_usuario = $usuario['id_usuario'];
    $rol_id = $usuario['rol_id']; 
    $email = $usuario['email'];
    $nombre_rol = $usuario['nombre_rol'];
}
?>