<?php  
include('../../app/config.php');  

$nivel = isset($_GET['nivel']) ? $_GET['nivel'] : null;  

if ($nivel === null) {
    echo json_encode(['error' => 'Falta el parámetro nivel.']);
    exit;
}

$sql = "SELECT * FROM grados WHERE nivel = :nivel AND estado = 1";  
$query = $pdo->prepare($sql);  
$query->bindParam(':nivel', $nivel);  
$query->execute();  
$grados = $query->fetchAll(PDO::FETCH_ASSOC);  

echo json_encode($grados);  
?>