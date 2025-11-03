<?php
include ('../../../app/config.php');

// Verificar si el archivo fue proporcionado en la solicitud GET
if (isset($_GET['file'])) {
    // Obtener el nombre del archivo y asegurarse de que no contenga caracteres maliciosos
    $file = 'backups/' . basename($_GET['file']);

    // Verificar si el archivo existe en el servidor
    if (file_exists($file)) {
        // Intentar eliminar el archivo
        if (unlink($file)) {
            // Redirigir con un mensaje de éxito
            header('Location: index.php?success=1');
            exit(); // Asegurarse de que el script se detenga después de la redirección
        } else {
            // Redirigir con un mensaje de error si no se pudo eliminar el archivo
            header('Location: index.php?error=1');
            exit();
        }
    } else {
        // Redirigir con un mensaje de error si el archivo no existe
        header('Location: index.php?error=2');
        exit();
    }
} else {
    // Redirigir con un mensaje de error si no se proporcionó un archivo
    header('Location: index.php?error=3');
    exit();
}
?>