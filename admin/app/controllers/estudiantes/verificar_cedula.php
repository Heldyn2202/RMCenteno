<?php  
include ('../../app/config.php'); // Incluye tu archivo de configuración de base de datos  

header('Content-Type: application/json');  

if ($_SERVER['REQUEST_METHOD'] === 'POST') {  
    // Obtener los datos enviados por AJAX  
    $data = json_decode(file_get_contents('php://input'), true);  
    $posicion_hijo = $data['posicion_hijo'];  
    $cedula_representante = $data['cedula_representante'];  

    // Prepara y ejecuta la consulta para verificar si la cédula escolar para la posición del hijo existe  
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM estudiantes WHERE posicion_hijo = :posicion_hijo AND cedula_escolar LIKE :cedula_representante");  
    $stmt->bindParam(':posicion_hijo', $posicion_hijo);  
    // Usar LIKE para buscar por el prefijo de cédula del representante  
    $stmt->bindValue(':cedula_representante', $cedula_representante . '%');  
    $stmt->execute();  
    
    $existe = $stmt->fetchColumn() > 0;  

    echo json_encode(['existe' => $existe]);  
} else {  
    echo json_encode(['existe' => false]);  
}  
?>