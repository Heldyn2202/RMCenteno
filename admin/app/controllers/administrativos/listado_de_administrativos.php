<?php


$sql_administrativos = "SELECT * FROM usuarios as usu INNER JOIN roles as rol ON rol.id_rol = usu.rol_id 
INNER JOIN personas as per ON per.usuario_id = usu.id_usuario 
INNER JOIN administrativos as adm ON adm.persona_id = per.id_persona where adm.estado = '1' ";
$query_administrativos = $pdo->prepare($sql_administrativos);
$query_administrativos->execute();
$administrativos = $query_administrativos->fetchAll(PDO::FETCH_ASSOC);

