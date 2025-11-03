<?php  
include ('../../../app/config.php');  

if ($_SERVER['REQUEST_METHOD'] === 'POST') {  
    var_dump($_POST); // Para depuración, útil para verificar datos  

    // Verificar si los parámetros necesarios están presentes  
    if (isset($_POST['id_seccion']) && isset($_POST['action'])) {  
        $id_seccion = $_POST['id_seccion']; // Este debe coincidir con el campo del formulario  
        $action = $_POST['action'] === 'disable' ? 0 : 1; // 0 para inhabilitar, 1 para habilitar  

        // Actualizar el estado de la sección  
        $sql_update = "UPDATE secciones SET estado = :estado WHERE id_seccion = :id_seccion";  
        $query_update = $pdo->prepare($sql_update);  
        $query_update->bindParam(':estado', $action);  
        $query_update->bindParam(':id_seccion', $id_seccion);  

        try {  
            if ($query_update->execute()) {  
                session_start();  
                $_SESSION['mensaje'] = "Sección " . ($action ? "habilitada" : "inhabilitada") . " correctamente.";  
                $_SESSION['icono'] = "success";  
                header("Location: " . APP_URL . "/admin/configuraciones/secciones");  
                exit; // Asegúrate de salir después de redirigir  
            } else {  
                session_start();  
                $_SESSION['mensaje'] = "Error al actualizar el estado de la sección.";  
                $_SESSION['icono'] = "error";  
                header("Location: " . APP_URL . "/admin/configuraciones/secciones");  
                exit;  
            }  
        } catch (Exception $exception) {  
            session_start();  
            $_SESSION['mensaje'] = "Error no se pudo actualizar el estado de la sección, debido a un problema de base de datos.";  
            $_SESSION['icono'] = "error";  
            header("Location: " . APP_URL . "/admin/configuraciones/secciones");  
            exit;  
        }  
    } else {  
        echo "Faltan parámetros necesarios."; // Mensaje si faltan parámetros  
    }  
} else {   
    echo "Método no permitido."; // Mensaje para métodos no permitidos  
}  
?>