<?php
include('../../app/config.php');

if (isset($_GET['grado']) && isset($_GET['turno'])) {
    $grado = $_GET['grado'];
    $turno = $_GET['turno'];

    $sql = "SELECT id_seccion, nombre_seccion FROM secciones WHERE grado = :grado AND turno_id = :turno";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':grado', $grado);
    $stmt->bindParam(':turno', $turno);
    $stmt->execute();

    $secciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($secciones);
} else {
    echo json_encode([]);
}
?>