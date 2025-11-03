<?php  
include('../../config.php');  

// Obtener la cédula desde el formulario  
$cedula = $_POST['cedula'];  

// Verificar si la cédula existe en la tabla de representantes  
$query = "SELECT COUNT(*) as count FROM representantes WHERE cedula = ?";  
$stmt = $conn->prepare($query);  
$stmt->bind_param('s', $cedula);  
$stmt->execute();  
$result = $stmt->get_result();  
$row = $result->fetch_assoc();  

// Devolver true si la cédula existe, false si no  
echo $row['count'] > 0 ? 'true' : 'false';  
?>