<?php
include('../../app/config.php');
if (session_status() === PHP_SESSION_NONE) session_start();

$id_horario = isset($_GET['id_horario']) ? (int)$_GET['id_horario'] : 0;
if (!$id_horario) {
    $_SESSION['mensaje'] = 'ID de horario inválido';
    $_SESSION['icono'] = 'error';
    header('Location: ' . APP_URL . '/admin/notas/horarios_consolidados.php');
    exit;
}

// Obtener el grado y sección del horario antes de eliminarlo (para redirección)
$stmtInfo = $pdo->prepare("SELECT id_grado, id_seccion FROM horarios WHERE id_horario = :id");
$stmtInfo->bindParam(':id', $id_horario);
$stmtInfo->execute();
$horarioInfo = $stmtInfo->fetch(PDO::FETCH_ASSOC);

if (!$horarioInfo) {
    $_SESSION['mensaje'] = 'Horario no encontrado';
    $_SESSION['icono'] = 'error';
    header('Location: ' . APP_URL . '/admin/notas/horarios_consolidados.php');
    exit;
}

try {
    // Iniciar transacción
    $pdo->beginTransaction();
    
    // Primero eliminar todos los detalles del horario
    $stmtDetalle = $pdo->prepare("DELETE FROM horario_detalle WHERE id_horario = ?");
    $stmtDetalle->execute([$id_horario]);
    
    // Luego eliminar el horario principal
    $stmtHorario = $pdo->prepare("DELETE FROM horarios WHERE id_horario = ?");
    $stmtHorario->execute([$id_horario]);
    
    // Confirmar transacción
    $pdo->commit();
    
    $_SESSION['mensaje'] = 'Horario eliminado correctamente';
    $_SESSION['icono'] = 'success';
    
    // Redirigir a horarios_consolidados.php sin parámetros (mostrará la lista vacía)
    header('Location: ' . APP_URL . '/admin/notas/horarios_consolidados.php');
    
} catch (Exception $e) {
    // Revertir transacción en caso de error
    $pdo->rollBack();
    
    $_SESSION['mensaje'] = 'Error al eliminar el horario: ' . $e->getMessage();
    $_SESSION['icono'] = 'error';
    
    // Redirigir manteniendo los parámetros del horario
    $redirectUrl = APP_URL . '/admin/notas/horarios_consolidados.php?grado=' . urlencode($horarioInfo['id_grado']) . '&seccion=' . urlencode($horarioInfo['id_seccion']);
    header('Location: ' . $redirectUrl);
}

exit;

