<?php
include('../../app/config.php');

// Parámetros para volver a la pantalla
$grado   = isset($_GET['grado']) ? (int)$_GET['grado'] : 0;
$seccion = isset($_GET['seccion']) ? (int)$_GET['seccion'] : 0;
$id      = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: horarios_consolidados.php?grado=' . $grado . '&seccion=' . $seccion);
    exit;
}

try {
    // Detectar nombre de la PK en horario_detalle
    $cols = $pdo->query("SHOW COLUMNS FROM horario_detalle")->fetchAll(PDO::FETCH_COLUMN);
    $pk = null;
    foreach (['id_detalle','id_horario_detalle','id'] as $c) {
        if (in_array($c, $cols, true)) { $pk = $c; break; }
    }
    if (!$pk) { throw new Exception('No se pudo determinar la clave primaria de horario_detalle'); }

    // Eliminar bloque
    $stmt = $pdo->prepare("DELETE FROM horario_detalle WHERE $pk = ? LIMIT 1");
    $stmt->execute([$id]);

    $_SESSION['mensaje'] = 'Bloque eliminado correctamente.';
    $_SESSION['icono']   = 'success';
} catch (Throwable $e) {
    $_SESSION['mensaje'] = 'Error al eliminar: ' . $e->getMessage();
    $_SESSION['icono']   = 'error';
}

header('Location: horarios_consolidados.php?grado=' . $grado . '&seccion=' . $seccion);
exit;
?>


