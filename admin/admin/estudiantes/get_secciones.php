<?php
include('../../app/config.php');

if (isset($_GET['grado_id'])) {
    $grado_id = $_GET['grado_id'];

    // Consulta para obtener las secciones del grado seleccionado
    $sql = "SELECT id_seccion, nombre_seccion FROM secciones WHERE id_grado = :grado_id AND estado = 1"; // Asegúrate de que el estado sea activo
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':grado_id', $grado_id);
    $stmt->execute();

    $secciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($secciones); // Devuelve las secciones en formato JSON
}
?>