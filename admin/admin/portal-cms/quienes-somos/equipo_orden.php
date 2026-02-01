<?php
// admin/admin/portal-cms/quienes-somos/equipo_orden.php

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
$accion = $_GET['accion'] ?? '';

if ($id > 0 && in_array($accion, ['subir', 'bajar'])) {
    // Obtener orden actual y nombre
    $query = "SELECT orden, nombre FROM equipo_quienes_somos WHERE id = $id";
    $result = mysqli_query($con, $query);
    
    if ($result && $row = mysqli_fetch_assoc($result)) {
        $orden_actual = $row['orden'];
        $nombre = $row['nombre'];
        $nuevo_orden = ($accion == 'subir') ? $orden_actual - 1 : $orden_actual + 1;
        
        // Encontrar quién tiene ese orden
        $query_buscar = "SELECT id FROM equipo_quienes_somos WHERE orden = $nuevo_orden";
        $result_buscar = mysqli_query($con, $query_buscar);
        
        if ($result_buscar && $otro_row = mysqli_fetch_assoc($result_buscar)) {
            // Intercambiar órdenes
            $otro_id = $otro_row['id'];
            
            // Actualizar el otro a orden actual
            $update_otro = "UPDATE equipo_quienes_somos SET orden = $orden_actual WHERE id = $otro_id";
            mysqli_query($con, $update_otro);
            
            // Actualizar este a nuevo orden
            $update_este = "UPDATE equipo_quienes_somos SET orden = $nuevo_orden WHERE id = $id";
            
            if (mysqli_query($con, $update_este)) {
                $direccion = ($accion == 'subir') ? 'subió' : 'bajó';
                $_SESSION['mensaje'] = "Miembro '$nombre' $direccion de posición";
                $_SESSION['mensaje_tipo'] = 'success';
            } else {
                $_SESSION['mensaje'] = 'Error al cambiar orden: ' . mysqli_error($con);
                $_SESSION['mensaje_tipo'] = 'error';
            }
        } else {
            $_SESSION['mensaje'] = 'No se puede mover más en esa dirección';
            $_SESSION['mensaje_tipo'] = 'warning';
        }
    } else {
        $_SESSION['mensaje'] = 'Miembro no encontrado';
        $_SESSION['mensaje_tipo'] = 'error';
    }
} else {
    $_SESSION['mensaje'] = 'Parámetros inválidos';
    $_SESSION['mensaje_tipo'] = 'error';
}

// Redirigir manteniendo la pestaña de equipo
header('Location: index.php#equipo');
exit;
?>