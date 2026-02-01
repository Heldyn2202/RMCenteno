<?php
// admin/admin/portal-cms/quienes-somos/colaborador_orden.php

// ================= CONEXIÓN MANUAL =================
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sige";

$con = new mysqli($servername, $username, $password, $dbname);
if ($con->connect_error) {
    die("Error de conexión: " . $con->connect_error);
}
// ====================================================

session_start();

// Verificar login temporal
if (!isset($_SESSION['portal_admin_logged_in'])) {
    header('Location: ../index.php');
    exit;
}

// Verificar parámetros
if (!isset($_GET['id']) || !is_numeric($_GET['id']) || !isset($_GET['accion'])) {
    $_SESSION['mensaje_tipo'] = 'error';
    $_SESSION['mensaje'] = 'Parámetros no válidos';
    header('Location: index.php#colaboradores');
    exit;
}

$id = intval($_GET['id']);
$accion = $_GET['accion'];

// Obtener datos del colaborador
$query = "SELECT orden FROM colaboradores_quienes_somos WHERE id = $id";
$result = mysqli_query($con, $query);
$colaborador = mysqli_fetch_assoc($result);

if (!$colaborador) {
    $_SESSION['mensaje_tipo'] = 'error';
    $_SESSION['mensaje'] = 'Colaborador no encontrado';
    header('Location: index.php#colaboradores');
    exit;
}

$orden_actual = $colaborador['orden'];

if ($accion === 'subir') {
    // Buscar el anterior
    $query = "SELECT id, orden FROM colaboradores_quienes_somos 
              WHERE orden < $orden_actual 
              ORDER BY orden DESC LIMIT 1";
    $result = mysqli_query($con, $query);
    
    if ($row = mysqli_fetch_assoc($result)) {
        $id_anterior = $row['id'];
        $orden_anterior = $row['orden'];
        
        // Intercambiar órdenes
        mysqli_query($con, "UPDATE colaboradores_quienes_somos SET orden = $orden_actual WHERE id = $id_anterior");
        mysqli_query($con, "UPDATE colaboradores_quienes_somos SET orden = $orden_anterior WHERE id = $id");
        
        $_SESSION['mensaje_tipo'] = 'success';
        $_SESSION['mensaje'] = 'Orden actualizado (subido)';
    } else {
        $_SESSION['mensaje_tipo'] = 'info';
        $_SESSION['mensaje'] = 'Ya está en la primera posición';
    }
    
} elseif ($accion === 'bajar') {
    // Buscar el siguiente
    $query = "SELECT id, orden FROM colaboradores_quienes_somos 
              WHERE orden > $orden_actual 
              ORDER BY orden ASC LIMIT 1";
    $result = mysqli_query($con, $query);
    
    if ($row = mysqli_fetch_assoc($result)) {
        $id_siguiente = $row['id'];
        $orden_siguiente = $row['orden'];
        
        // Intercambiar órdenes
        mysqli_query($con, "UPDATE colaboradores_quienes_somos SET orden = $orden_actual WHERE id = $id_siguiente");
        mysqli_query($con, "UPDATE colaboradores_quienes_somos SET orden = $orden_siguiente WHERE id = $id");
        
        $_SESSION['mensaje_tipo'] = 'success';
        $_SESSION['mensaje'] = 'Orden actualizado (bajado)';
    } else {
        $_SESSION['mensaje_tipo'] = 'info';
        $_SESSION['mensaje'] = 'Ya está en la última posición';
    }
    
} else {
    $_SESSION['mensaje_tipo'] = 'error';
    $_SESSION['mensaje'] = 'Acción no válida';
}

// Cerrar conexión y redirigir
mysqli_close($con);
header('Location: index.php#colaboradores');
exit;
?>