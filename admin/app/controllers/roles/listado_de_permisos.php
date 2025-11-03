<?php

$sql_permisos = "SELECT * FROM permisos where estado = '1' ORDER BY nombre_url asc ";
$query_permisos = $pdo->prepare($sql_permisos);
$query_permisos->execute();
$permisos = $query_permisos->fetchAll(PDO::FETCH_ASSOC);