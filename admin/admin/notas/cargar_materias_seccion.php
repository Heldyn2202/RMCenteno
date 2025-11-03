<?php
require_once('../../app/config.php');

$seccion_id = $_GET['seccion_id'] ?? null;

if ($seccion_id) {
    // Obtener materias de la sección basado en el grado
    $sql = "SELECT m.id_materia, m.nombre_materia 
            FROM secciones s
            JOIN materias m ON s.id_grado = m.id_grado
            WHERE s.id_seccion = :seccion_id 
            AND m.estado = 1
            ORDER BY m.nombre_materia";
    
    $query = $pdo->prepare($sql);
    $query->bindParam(':seccion_id', $seccion_id);
    $query->execute();
    $materias = $query->fetchAll(PDO::FETCH_ASSOC);
    
    echo '<option value="">Seleccionar Materia</option>';
    foreach ($materias as $materia) {
        echo '<option value="' . $materia['id_materia'] . '">' . htmlspecialchars($materia['nombre_materia']) . '</option>';
    }
} else {
    echo '<option value="">Seleccionar Materia</option>';
}
?>