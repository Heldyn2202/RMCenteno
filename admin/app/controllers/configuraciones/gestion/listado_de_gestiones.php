<?php


$sql_gestiones = "SELECT * FROM gestiones ";
$query_gestiones = $pdo->prepare($sql_gestiones);
$query_gestiones->execute();
$gestiones = $query_gestiones->fetchAll(PDO::FETCH_ASSOC);