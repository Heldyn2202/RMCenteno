<?php
include ('../../../app/config.php');

// Verificar si se proporcionó un archivo
if (isset($_GET['file'])) {
    $file = 'backups/' . basename($_GET['file']);

    // Verificar si el archivo existe
    if (file_exists($file)) {
        // Forzar la descarga del archivo
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        ob_clean();
        flush();
        readfile($file);
        exit;
    } else {
        echo "El archivo no existe.";
    }
} else {
    echo "No se proporcionó un archivo.";
}
?>