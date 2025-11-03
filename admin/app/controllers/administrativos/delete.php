<?php
include ('../../../app/config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar si los parámetros necesarios están presentes
    if (isset($_POST['id_persona']) && isset($_POST['action'])) {
        $id_persona = $_POST['id_persona']; // Este debe coincidir con el campo del formulario
        $action = $_POST['action'] === 'disable' ? 0 : 1; // 0 para inhabilitar, 1 para habilitar

        // Actualizar el estado del administrativo
        $sql_update = "UPDATE personas SET estado = :estado WHERE id_persona = :id_persona"; // Asegúrate de que el nombre de la tabla y columna sean correctos
        $query_update = $pdo->prepare($sql_update);
        $query_update->bindParam(':estado', $action);
        $query_update->bindParam(':id_persona', $id_persona);

        try {
            if ($query_update->execute()) {
                session_start();
                $_SESSION['mensaje'] = "Registro " . ($action ? "habilitado" : "inhabilitado") . " correctamente.";
                $_SESSION['icono'] = "success";
                header("Location: " . APP_URL . "/admin/administrativos");
                exit; // Asegúrate de salir después de redirigir
            } else {
                session_start();
                $_SESSION['mensaje'] = "Error al actualizar el estado del registro.";
                $_SESSION['icono'] = "error";
                header("Location: " . APP_URL . "/admin/administrativos");
                exit;
            }
        } catch (Exception $exception) {
            session_start();
            $_SESSION['mensaje'] = "Error: no se pudo actualizar el estado del registro, debido a un problema de base de datos. Detalle: " . $exception->getMessage();
            $_SESSION['icono'] = "error";
            header("Location: " . APP_URL . "/admin/administrativos");
            exit;
        }
    } else {
        echo "Faltan parámetros necesarios."; // Mensaje si faltan parámetros
    }
} else {
    echo "Método no permitido."; // Mensaje para métodos no permitidos
}
?>