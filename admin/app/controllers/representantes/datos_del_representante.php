<?php
$id_representante = $_GET['id']; // Asegúrate de validar y sanitizar este valor
include ('../../app/config.php');

// Consulta para obtener el representante específico por ID
$sql_representantes = "SELECT * FROM representantes WHERE id_representante = :id";
$query_representantes = $pdo->prepare($sql_representantes);
$query_representantes->bindParam(':id', $id_representante, PDO::PARAM_INT);
$query_representantes->execute();
$representantes = $query_representantes->fetchAll(PDO::FETCH_ASSOC);



foreach ($representantes as $representante) {
    $tipo_cedula = $representante['tipo_cedula'];
    $cedula = $representante['cedula'];
    $nombres = $representante['nombres'];
    $apellidos = $representante['apellidos'];
    $fecha_nacimiento = $representante['fecha_nacimiento'];
    $estado_civil = $representante['estado_civil'];
    $genero = $representante['genero'];
    $correo_electronico = $representante['correo_electronico']; 
    $tipo_sangre = $representante['tipo_sangre'];
    $direccion = $representante['direccion'];
    $numeros_telefonicos = $representante['numeros_telefonicos'];
    $estatus = $representante['estatus'];

    // Aquí puedes hacer algo con los datos de cada representante si es necesario
} 
?>

