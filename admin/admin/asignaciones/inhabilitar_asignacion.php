<?php
include('../../app/config.php');
$id = intval($_GET['id_asignacion'] ?? 0);

if ($id > 0) {
    $stmt = $pdo->prepare("UPDATE asignaciones_profesor SET estado = 0 WHERE id_asignacion = ?");
    $stmt->execute([$id]);
    echo json_encode(['status'=>'ok','msg'=>'La asignación fue inhabilitada correctamente.']);
} else {
    echo json_encode(['status'=>'error','msg'=>'ID no válido.']);
}
