<?php
require_once('../../app/config.php');

$grado_id = $_GET['grado_id'];

$sql = "SELECT s.* FROM secciones s
        JOIN grados_secciones gs ON s.id_seccion = gs.id_seccion
        WHERE gs.id_grado = :grado_id AND s.estado = 1";
$query = $pdo->prepare($sql);
$query->bindParam(':grado_id', $grado_id);
$query->execute();
$secciones = $query->fetchAll(PDO::FETCH_ASSOC);

$html = '<option value="">Todas las Secciones</option>';
foreach ($secciones as $seccion) {
    $html .= '<option value="' . $seccion['id_seccion'] . '">' . $seccion['nombre_seccion'] . '</option>';
}

echo $html;
?>