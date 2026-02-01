<?php
// admin/admin/portal-cms/quienes-somos/equipo_estado.php

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
    // Obtener estado actual
    $query = "SELECT activo, nombre FROM equipo_quienes_somos WHERE id = $id";
    $result = mysqli_query($con, $query);
    
    if ($result && $row = mysqli_fetch_assoc($result)) {
        $nuevo_estado = $row['activo'] ? 0 : 1;
        $nombre = $row['nombre'];
        
        // Actualizar estado
        $update_query = "UPDATE equipo_quienes_somos SET activo = $nuevo_estado WHERE id = $id";
        
        if (mysqli_query($con, $update_query)) {
            $estado_texto = $nuevo_estado ? 'activado' : 'inactivado';
            $_SESSION['mensaje'] = "Miembro '$nombre' $estado_texto correctamente";
            $_SESSION['mensaje_tipo'] = 'success';
        } else {
            $_SESSION['mensaje'] = 'Error al cambiar el estado: ' . mysqli_error($con);
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