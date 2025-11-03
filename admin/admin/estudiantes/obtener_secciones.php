<?php
include('../../app/config.php');

$turno = $_GET['turno'];
$grado = $_GET['grado'];

// Obtener el periodo académico activo
$sql_gestiones = "SELECT * FROM gestiones WHERE estado = 1 ORDER BY desde DESC LIMIT 1";  
$query_gestiones = $pdo->prepare($sql_gestiones);  
$query_gestiones->execute();  
$gestion_activa = $query_gestiones->fetch(PDO::FETCH_ASSOC);

if ($gestion_activa) {
    $id_gestion_activa = $gestion_activa['id_gestion'];

    // Asegúrate de que el grado y el turno estén definidos
    if (isset($turno) && isset($grado)) {
        // Consulta para obtener las secciones filtradas por turno, grado y periodo académico activo
        $sql = "SELECT id_seccion, nombre_seccion, capacidad, cupo_actual FROM secciones WHERE turno = :turno AND id_grado = :grado AND id_gestion = :id_gestion AND estado = 1"; // Solo secciones activas
        $query = $pdo->prepare($sql);
        $query->bindParam(':turno', $turno);
        $query->bindParam(':grado', $grado);
        $query->bindParam(':id_gestion', $id_gestion_activa);
        $query->execute();
        $secciones = $query->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($secciones);
    }
}
?>