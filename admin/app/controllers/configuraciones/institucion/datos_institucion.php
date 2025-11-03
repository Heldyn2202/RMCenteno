<?php


$sql_instituciones = "SELECT * FROM configuracion_instituciones WHERE id_config_institucion = '$id_config_institucion' and estado = '1' ";
$query_instituciones = $pdo->prepare($sql_instituciones);
$query_instituciones->execute();
$instituciones = $query_instituciones->fetchAll(PDO::FETCH_ASSOC);

foreach ($instituciones as $institucione){
    $nombre_institucion = $institucione['nombre_institucion'];
    $direccion = $institucione['direccion'];
    $telefono = $institucione['telefono'];
    $celular = $institucione['celular'];
    $correo = $institucione['correo'];
    $logo = $institucione['logo'];
}