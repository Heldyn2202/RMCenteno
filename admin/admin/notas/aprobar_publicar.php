<?php
include('../../app/config.php');
if (session_status() === PHP_SESSION_NONE) session_start();

$id = isset($_GET['id_horario']) ? (int)$_GET['id_horario'] : 0;
if (!$id) { die('ID inválido'); }

$usuarioId = $_SESSION['usuario_id'] ?? null;

$stmt = $pdo->prepare("UPDATE horarios SET estado='PUBLICADO', aprobado_por = :u, aprobado_en = NOW() WHERE id_horario = :id");
$stmt->bindParam(':u', $usuarioId);
$stmt->bindParam(':id', $id);

if ($stmt->execute()) {
    $_SESSION['mensaje'] = 'Horario aprobado y publicado correctamente';
    $_SESSION['icono'] = 'success';
} else {
    $_SESSION['mensaje'] = 'No se pudo publicar el horario';
    $_SESSION['icono'] = 'error';
}

header('Location: ' . APP_URL . '/admin/notas/horarios_consolidados.php');
exit;




