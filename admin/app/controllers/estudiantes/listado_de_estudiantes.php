<?php  



$id_representante = isset($_POST['id_representante']) ? $_POST['id_representante'] : null;  

try {  
    if ($id_representante) {  
        // Obtener los estudiantes asociados al representante  
        $query = "SELECT * FROM estudiantes WHERE id_representante = :id_representante";  
        $stmt = $pdo->prepare($query);  
        $stmt->bindParam(':id_representante', $id_representante);  
        $stmt->execute();  
        $estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);  
    } else {  
        // Obtener todos los estudiantes  
        $query = "SELECT * FROM estudiantes";  
        $stmt = $pdo->prepare($query);  
        $stmt->execute();  
        $estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);  
    }  

    // Obtener el nombre del representante  
    $nombre_representante = isset($_POST['nombre_representante']) ? $_POST['nombre_representante'] : '';  
} catch (PDOException $e) {  
    // Manejar el error de conexión a la base de datos  
    echo "Error: " . $e->getMessage();  
    exit;  
}  

// Devolver los datos   
return array(  
    'estudiantes' => $estudiantes,  
    'nombre_representante' => $nombre_representante  
);