<?php
session_start();
require_once('../../app/config.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_seccion = $_POST['id_seccion'];
    $id_materia = $_POST['id_materia'];
    $tipo = $_POST['tipo'] ?? 'REVISION';
    $notas = $_POST['notas'] ?? [];
    $observaciones = $_POST['observaciones'] ?? [];

    try {
        $pdo->beginTransaction();
        $procesadas = 0;
        $aprobadas = 0;
        $pendientes = 0;

        foreach($notas as $id_est => $nota){
            $nota = floatval($nota);
            $obs = trim($observaciones[$id_est] ?? '');

            // Obtener intentos previos
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM recuperaciones WHERE id_estudiante = :e AND id_materia = :m AND tipo = :t");
            $stmt->execute([':e'=>$id_est, ':m'=>$id_materia, ':t'=>$tipo]);
            $intentos = $stmt->fetchColumn();
            $nuevo_intento = $intentos + 1;

            // Registrar intento
            $pdo->prepare("INSERT INTO recuperaciones (id_estudiante,id_materia,tipo,intento,calificacion,observaciones) 
                           VALUES (:e,:m,:t,:i,:n,:o)")
                ->execute([':e'=>$id_est, ':m'=>$id_materia, ':t'=>$tipo, ':i'=>$nuevo_intento, ':n'=>$nota, ':o'=>$obs]);

            if($nota >= 10){
                // Aprueba => actualizamos nota definitiva
                $pdo->prepare("UPDATE notas_estudiantes 
                               SET calificacion = :n, observaciones = CONCAT(IFNULL(observaciones,''), ' | Aprobado por recuperación') 
                               WHERE id_estudiante = :e AND id_materia = :m AND id_lapso = 3")
                    ->execute([':n'=>$nota, ':e'=>$id_est, ':m'=>$id_materia]);
                $aprobadas++;
            } else {
                // Falla
                if($tipo == 'REVISION' && $nuevo_intento >= 2){
                    // Pasa a materia pendiente
                    $pendientes++;
                }
            }

            $procesadas++;
        }

        $pdo->commit();

        $_SESSION['success_message'] = "
        ✅ <strong>Recuperaciones guardadas correctamente.</strong><br>
        📊 Procesadas: <b>$procesadas</b><br>
        🟢 Aprobadas: <b>$aprobadas</b><br>
        🔴 Pendientes: <b>$pendientes</b>";

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error_message'] = "❌ Error al guardar recuperaciones: " . $e->getMessage();
    }

    header("Location: recuperacion_revision.php?seccion=$id_seccion&materia=$id_materia");
    exit;
} else {
    $_SESSION['error_message'] = "❌ Método no permitido.";
    header("Location: recuperacion_revision.php");
    exit;
}
?>
