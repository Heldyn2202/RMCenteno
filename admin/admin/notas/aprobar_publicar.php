<?php
include('../../app/config.php');
if (session_status() === PHP_SESSION_NONE) session_start();

$id = isset($_GET['id_horario']) ? (int)$_GET['id_horario'] : 0;
if (!$id) { die('ID inválido'); }

$usuarioId = $_SESSION['usuario_id'] ?? null;

// Obtener el grado y sección del horario antes de actualizarlo
$stmtInfo = $pdo->prepare("SELECT id_grado, id_seccion FROM horarios WHERE id_horario = :id");
$stmtInfo->bindParam(':id', $id);
$stmtInfo->execute();
$horarioInfo = $stmtInfo->fetch(PDO::FETCH_ASSOC);

if (!$horarioInfo) {
    $_SESSION['mensaje'] = 'Horario no encontrado';
    $_SESSION['icono'] = 'error';
    header('Location: ' . APP_URL . '/admin/notas/horarios_consolidados.php');
    exit;
}

$stmt = $pdo->prepare("UPDATE horarios SET estado='PUBLICADO', aprobado_por = :u, aprobado_en = NOW() WHERE id_horario = :id");
$stmt->bindParam(':u', $usuarioId);
$stmt->bindParam(':id', $id);

if ($stmt->execute()) {
    $_SESSION['mensaje'] = 'Horario aprobado y publicado correctamente';
    $_SESSION['icono'] = 'success';
    
    // Redirigir a horarios_consolidados.php con los parámetros del grado y sección
    $redirectUrl = APP_URL . '/admin/notas/horarios_consolidados.php?grado=' . urlencode($horarioInfo['id_grado']) . '&seccion=' . urlencode($horarioInfo['id_seccion']);
    header('Location: ' . $redirectUrl);
} else {
    $_SESSION['mensaje'] = 'No se pudo publicar el horario';
    $_SESSION['icono'] = 'error';
    header('Location: ' . APP_URL . '/admin/notas/horarios_consolidados.php');
}
exit;




