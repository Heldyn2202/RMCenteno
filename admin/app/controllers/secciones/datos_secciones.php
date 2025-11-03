<?php
$id_seccion = $_GET['id']; // Asegúrate de que estás pasando el ID correctamente

// Consulta para obtener los datos de la sección
$sql_seccion = "SELECT s.*, g.id_grado, g.grado FROM secciones s
                JOIN grados g ON s.id_grado = g.id_grado
                WHERE s.id_seccion = :id_seccion";

$query_seccion = $pdo->prepare($sql_seccion);
$query_seccion->bindParam(':id_seccion', $id_seccion);
$query_seccion->execute();
$seccion = $query_seccion->fetch(PDO::FETCH_ASSOC); // Cambié el nombre de la variable a $seccion

// Verifica si se encontró la sección
if ($seccion) {
    // Asignar los valores a las variables
    $turno = $seccion['turno'];
    $capacidad = $seccion['capacidad'];
    $id_grado = $seccion['id_grado']; // Obtener el ID del grado
    $grado = $seccion['grado']; // Asegúrate de que este campo exista en la consulta
    $id_gestion = $seccion['id_gestion']; // Este es el ID de gestión
    $nombre_seccion = $seccion['nombre_seccion'];
    $estado = $seccion['estado'];
    $fyh_creacion = $seccion['fyh_creacion']; // Asegúrate de que este campo exista en la tabla
} else {
    // Manejo de error si no se encuentra la sección
    echo "Sección no encontrada.";
    exit();
}
?>