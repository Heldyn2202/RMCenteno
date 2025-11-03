<?php
// Consulta para obtener todos los representantes, ordenados por estatus
$sql_representantes = "SELECT * FROM representantes ORDER BY 
    CASE 
        WHEN estatus = 'activo' THEN 1
        WHEN estatus = 'inactivo' THEN 2
        ELSE 3 
    END";
$query_representantes = $pdo->prepare($sql_representantes);
$query_representantes->execute();
$representantes = $query_representantes->fetchAll(PDO::FETCH_ASSOC);


?>