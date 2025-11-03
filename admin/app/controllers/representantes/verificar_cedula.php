<?php
include ('../../../app/config.php'); // Incluye tu archivo de configuración de base de datos

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cedula = $_POST['cedula'];

    // Prepara y ejecuta la consulta para verificar si la cédula existe
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM representantes WHERE cedula = :cedula");
    $stmt->execute(['cedula' => $cedula]);
    $existe = $stmt->fetchColumn() > 0;

    echo json_encode(['existe' => $existe]);
} else {
    echo json_encode(['existe' => $false]);
}
?> 