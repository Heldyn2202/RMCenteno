<?php

$sql_roles = "SELECT * FROM roles where estado = '1' ";
$query_roles = $pdo->prepare($sql_roles);
$query_roles->execute();
$roles = $query_roles->fetchAll(PDO::FETCH_ASSOC);