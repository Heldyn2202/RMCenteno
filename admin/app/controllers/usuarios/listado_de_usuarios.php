<?php

 
$sql_usuarios = "SELECT * FROM usuarios as usu INNER JOIN roles as rol 
                  ON rol.id_rol = usu.rol_id  where usu.estado = '1' ";
$query_usuarios = $pdo->prepare($sql_usuarios);
$query_usuarios->execute();
$usuarios = $query_usuarios->fetchAll(PDO::FETCH_ASSOC);