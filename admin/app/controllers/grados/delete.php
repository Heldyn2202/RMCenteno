<?php  
include ('../../../app/config.php');  

if ($_SERVER['REQUEST_METHOD'] === 'POST') {  
    var_dump($_POST); // Para depuración, útil para verificar datos  

    // Verificar si los parámetros necesarios están presentes  
    if (isset($_POST['grado_id']) && isset($_POST['action'])) {  
        $id_grado = $_POST['grado_id']; // Este debe coincidir con el campo del formulario  
        $action = $_POST['action'] === 'disable' ? 0 : 1; // 0 para inhabilitar, 1 para habilitar  

        // Actualizar el estado del grado  
        $sql_update = "UPDATE grados SET estado = :estado WHERE id_grado = :id_grado";  
        $query_update = $pdo->prepare($sql_update);  
        $query_update->bindParam(':estado', $action);  
        $query_update->bindParam(':id_grado', $id_grado);  

        try {  
            if ($query_update->execute()) {  
                session_start();  
                $_SESSION['mensaje'] = "Grado " . ($action ? "habilitado" : "inhabilitado") . " correctamente.";  
                $_SESSION['icono'] = "success";  
                header("Location: " . APP_URL . "/admin/configuraciones/grados");  
                exit; // Asegúrate de salir después de redirigir  
            } else {  
                session_start();  
                $_SESSION['mensaje'] = "Error al actualizar el estado del grado.";  
                $_SESSION['icono'] = "error";  
                header("Location: " . APP_URL . "/admin/configuraciones/grados");  
                exit;  
            }  
        } catch (Exception $exception) {  
            session_start();  
            $_SESSION['mensaje'] = "Error no se pudo actualizar el estado del grado, debido a un problema de base de datos.";  
            $_SESSION['icono'] = "error";  
            header("Location: " . APP_URL . "/admin/configuraciones/grados");  
            exit;  
        }  
    } else {  
        echo "Faltan parámetros necesarios."; // Mensaje si faltan parámetros  
    }  
} else {   
    echo "Método no permitido."; // Mensaje para métodos no permitidos  
}  
?>



