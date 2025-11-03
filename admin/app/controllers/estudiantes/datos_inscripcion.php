<?php  
// Obtener el ID del estudiante desde la URL  
$id_estudiante = isset($_GET['id']) ? $_GET['id'] : null; // Validar que el ID esté presente

if ($id_estudiante === null) {
    die("Error: ID del estudiante no proporcionado.");
}

// Obtener los datos de la inscripción del estudiante  
$query = "SELECT i.*, g.desde, g.hasta, s.nombre_seccion  
FROM inscripciones i  
JOIN gestiones g ON i.id_gestion = g.id_gestion  
JOIN secciones s ON i.id_seccion = s.id_seccion  
WHERE i.id_estudiante = :id_estudiante  
ORDER BY i.id DESC  
LIMIT 1";  

try {  
    $stmt = $pdo->prepare($query);  
    $stmt->bindParam(':id_estudiante', $id_estudiante, PDO::PARAM_INT);  
    $stmt->execute();  
    $inscripcion = $stmt->fetch(PDO::FETCH_ASSOC);  

    if ($inscripcion) {  
        $id_gestion = $inscripcion['id_gestion'];  
        $nivel = $inscripcion['nivel_id'];  
        $grado = $inscripcion['grado'];  
        $nombre_seccion = $inscripcion['nombre_seccion'];  
        $turno = isset($inscripcion['turno_id']) ? $inscripcion['turno_id'] : 'N/A';  
        $talla_camisa = $inscripcion['talla_camisa'];  
        $talla_pantalon = $inscripcion['talla_pantalon'];  
        $talla_zapatos = $inscripcion['talla_zapatos'];  
    } else {  
        // Manejar el caso en el que no se encuentre una inscripción  
        $id_gestion = '';  
        $nivel = '';  
        $grado = '';  
        $nombre_seccion = '';  
        $turno = 'N/A';  
        $talla_camisa = '';  
        $talla_pantalon = '';  
        $talla_zapatos = '';  
    }  
} catch (PDOException $e) {  
    // Manejar el error de conexión a la base de datos  
    echo "Error de conexión a la base de datos: " . $e->getMessage();  
    exit;  
}  
?>
