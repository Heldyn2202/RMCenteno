<?php
include('../../app/config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cedula_escolar = $_POST['cedula_escolar'];

    // Verificar si la cédula escolar ya existe
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM estudiantes WHERE cedula_escolar = :cedula_escolar");
    $stmt->execute(['cedula_escolar' => $cedula_escolar]);
    
    if ($stmt->fetchColumn() > 0) {
        echo 'existe'; // La cédula escolar ya está registrada
    } else {
        echo 'no_existe'; // La cédula escolar no está registrada
    }
} else {
    echo 'Método no permitido.';
}
?>