<?php  
try {  
    // Consulta SQL para seleccionar las columnas necesarias  
    $sql_grados = "SELECT id_grado, nivel, grado, estado FROM grados";  
    $query_grados = $pdo->prepare($sql_grados);  
    $query_grados->execute();  

    // Obtener todos los grados como un array asociativo  
    $grados = $query_grados->fetchAll(PDO::FETCH_ASSOC);  
} catch (PDOException $e) {  
    // Manejo de errores  
    echo "Error al obtener los grados: " . $e->getMessage();  
    $grados = []; // Inicializar $grados como un array vacío en caso de error  
}  

// Agregar esta sección si se espera que el usuario realice acciones de habilitar/inhabilitar en este archivo.  
if ($_SERVER['REQUEST_METHOD'] === 'POST') {  
    // Aquí puedes manejar las acciones de habilitar/inhabilitar  
    $grado_id = $_POST['grado_id'];  
    $action = $_POST['action'];  

    try {  
        // Modificar el estado del grado en la base de datos  
        if ($action === 'disable') {  
            $sql_update = "UPDATE grados SET estado = 0 WHERE id_grado = :id_grado";  
        } else {  
            $sql_update = "UPDATE grados SET estado = 1 WHERE id_grado = :id_grado";  
        }  

        $query_update = $pdo->prepare($sql_update);  
        $query_update->bindParam(':id_grado', $grado_id, PDO::PARAM_INT);  
        $query_update->execute();  

        // Puedes redirigir o mostrar un mensaje de éxito aquí  
    } catch (PDOException $e) {  
        echo "Error al cambiar el estado del grado: " . $e->getMessage();  
    }  
}  
?>