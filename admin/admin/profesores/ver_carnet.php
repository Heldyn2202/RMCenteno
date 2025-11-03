<?php
include('../../app/config.php');

$id_profesor = $_GET['id'];

$sql = "SELECT * FROM profesores WHERE id_profesor = :id_profesor";
$query = $pdo->prepare($sql);
$query->bindParam(':id_profesor', $id_profesor);
$query->execute();
$profesor = $query->fetch(PDO::FETCH_ASSOC);

if (!$profesor) {
    die("Profesor no encontrado");
}
?>

<div class="carnet-container" style="border: 2px solid #333; width: 350px; margin: 0 auto; padding: 15px; font-family: Arial, sans-serif;">
    <div class="header" style="background-color: #007bff; color: white; padding: 10px; text-align: center;">
        <h2 style="margin: 0;">INSTITUCIÓN EDUCATIVA</h2>
        <p style="margin: 0;">Carnet de Identificación</p>
    </div>
    
    <div class="photo" style="margin: 15px auto; width: 120px; height: 150px; background-color: #eee; border: 1px solid #ddd; text-align: center; line-height: 150px;">
        FOTO
    </div>
    
    <div class="info" style="margin-top: 15px;">
        <p><strong>Cédula:</strong> <?= htmlspecialchars($profesor['cedula']) ?></p>
        <p><strong>Nombre:</strong> <?= htmlspecialchars($profesor['nombres'] . ' ' . $profesor['apellidos']) ?></p>
        <p><strong>Especialidad:</strong> <?= htmlspecialchars($profesor['especialidad']) ?></p>
        <p><strong>Teléfono:</strong> <?= htmlspecialchars($profesor['telefono']) ?></p>
    </div>
    
    <div class="footer" style="margin-top: 20px; border-top: 1px solid #333; padding-top: 10px; text-align: center;">
        <small>Válido mientras estudie en la institución</small>
    </div>
</div>