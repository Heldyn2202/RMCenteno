<?php  
include('../../app/config.php');  

// Obtener el periodo académico activo
$sql_gestiones = "SELECT id_gestion FROM gestiones WHERE estado = 1 ORDER BY desde DESC LIMIT 1";  
$query_gestiones = $pdo->prepare($sql_gestiones);  
$query_gestiones->execute();  
$gestion_activa = $query_gestiones->fetch(PDO::FETCH_ASSOC);  

if ($gestion_activa) {
    $id_gestion = $gestion_activa['id_gestion']; // Obtener el id_gestion del periodo activo

    $sql_secciones = "SELECT id_seccion, nombre_seccion, turno, capacidad, id_grado, estado, fyh_creacion, cupo_actual 
                      FROM secciones 
                      WHERE id_gestion = :id_gestion";  
    $query_secciones = $pdo->prepare($sql_secciones);  
    $query_secciones->bindParam(':id_gestion', $id_gestion, PDO::PARAM_INT);  
    $query_secciones->execute();  
    $secciones = $query_secciones->fetchAll(PDO::FETCH_ASSOC);  

    // Devolver como JSON
    echo json_encode($secciones);
} else {
    // Si no hay gestión activa, devolver un array vacío
    echo json_encode([]);
}