<?php
include('../../app/config.php');

$id_profesor = $_GET['id'];
$nuevo_estado = $_GET['estado'];

$sql = "UPDATE profesores SET estado = :estado WHERE id_profesor = :id_profesor";
$query = $pdo->prepare($sql);
$query->bindParam(':estado', $nuevo_estado);
$query->bindParam(':id_profesor', $id_profesor);

if ($query->execute()) {
    header("Location: listar_profesores.php?success=Estado actualizado correctamente");
} else {
    header("Location: listar_profesores.php?error=Error al actualizar el estado");
}