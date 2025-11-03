<?php
include('../../app/config.php');

$id_seccion = $_GET['id_seccion'];

$sql = "SELECT capacidad, cupo_actual FROM secciones WHERE id_seccion = :id_seccion";
$query = $pdo->prepare($sql);
$query->execute(['id_seccion' => $id_seccion]);
$seccion = $query->fetch(PDO::FETCH_ASSOC);

if ($seccion) {
    // Calcular los cupos disponibles
    $cupos_disponibles = $seccion['capacidad'] - $seccion['cupo_actual'];
    echo json_encode(['cupos_disponibles' => $cupos_disponibles]);
} else {
    echo json_encode(['cupos_disponibles' => 0]); // Si no se encuentra la sección, devolver 0
}
?>