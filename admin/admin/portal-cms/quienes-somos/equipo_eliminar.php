<?php
// admin/admin/portal-cms/quienes-somos/equipo_eliminar.php

session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sige";

$con = new mysqli($servername, $username, $password, $dbname);
if ($con->connect_error) {
    die("Error de conexión: " . $con->connect_error);
}

if (!isset($_SESSION['portal_admin_logged_in'])) {
    header('Location: ../index.php');
    exit;
}

$id = intval($_GET['id'] ?? 0);

if ($id > 0) {
    // Primero obtener datos para eliminar la imagen
    $query = "SELECT imagen, nombre FROM equipo_quienes_somos WHERE id = $id";
    $result = mysqli_query($con, $query);
    
    if ($result && $row = mysqli_fetch_assoc($result)) {
        $nombre = $row['nombre'];
        
        // Eliminar la imagen si existe
        if (!empty($row['imagen'])) {
            $imagen_path = '../../../../heldyn/centeno/uploads/quienes-somos/equipo/' . $row['imagen'];
            if (file_exists($imagen_path)) {
                unlink($imagen_path);
            }
        }
        
        // Eliminar de la base de datos
        $delete_query = "DELETE FROM equipo_quienes_somos WHERE id = $id";
        
        if (mysqli_query($con, $delete_query)) {
            $_SESSION['mensaje'] = "Miembro '$nombre' eliminado correctamente";
            $_SESSION['mensaje_tipo'] = 'success';
        } else {
            $_SESSION['mensaje'] = 'Error al eliminar: ' . mysqli_error($con);
            $_SESSION['mensaje_tipo'] = 'error';
        }
    } else {
        $_SESSION['mensaje'] = 'Miembro no encontrado';
        $_SESSION['mensaje_tipo'] = 'error';
    }
} else {
    $_SESSION['mensaje'] = 'ID no válido';
    $_SESSION['mensaje_tipo'] = 'error';
}

// Redirigir manteniendo la pestaña de equipo
header('Location: index.php#equipo');
exit;
?>