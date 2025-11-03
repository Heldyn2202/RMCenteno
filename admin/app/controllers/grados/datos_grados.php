<?php
// Asegúrate de que el ID del grado se pase correctamente
$id_grado = $_GET['id']; // Asegúrate de que este ID se pase desde el archivo show.php

// Consulta SQL para obtener los datos del grado específico
$sql_grados = "SELECT nivel, grado, estado, fyh_creacion FROM grados WHERE id_grado = :id_grado";
$query_grados = $pdo->prepare($sql_grados);
$query_grados->bindParam(':id_grado', $id_grado);
$query_grados->execute();

// Obtener el grado específico
$gradoData = $query_grados->fetch(PDO::FETCH_ASSOC); // Cambié el nombre de la variable a $gradoData

// Verificar si se encontró el grado
if ($gradoData) {
    $nivel = $gradoData['nivel'];
    $grado = $gradoData['grado']; // Este ahora es el grado en letras
    $estado = $gradoData['estado'];
    $fyh_creacion = $gradoData['fyh_creacion'];
} else {
    // Manejo de error si no se encuentra el grado
    echo "No se encontró el grado.";
    exit();
}
?>