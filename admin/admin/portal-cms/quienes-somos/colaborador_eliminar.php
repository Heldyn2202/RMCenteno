<?php
// admin/admin/portal-cms/quienes-somos/colaborador_eliminar.php

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

// Verificar que se pasó un ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['mensaje_tipo'] = 'error';
    $_SESSION['mensaje'] = 'ID no válido';
    header('Location: index.php#colaboradores');
    exit;
}

$id = intval($_GET['id']);

// Obtener datos del colaborador para eliminar el logo
$query = "SELECT logo FROM colaboradores_quienes_somos WHERE id = $id";
$result = mysqli_query($con, $query);
$colaborador = mysqli_fetch_assoc($result);

if ($colaborador) {
    // Eliminar logo si existe
    if (!empty($colaborador['logo'])) {
        $ruta_logo = '../../../../uploads/quienes-somos/colaboradores/' . $colaborador['logo'];
        if (file_exists($ruta_logo)) {
            unlink($ruta_logo);
        }
    }
    
    // Eliminar de la base de datos
    $query = "DELETE FROM colaboradores_quienes_somos WHERE id = $id";
    if (mysqli_query($con, $query)) {
        $_SESSION['mensaje_tipo'] = 'success';
        $_SESSION['mensaje'] = 'Colaborador eliminado exitosamente';
    } else {
        $_SESSION['mensaje_tipo'] = 'error';
        $_SESSION['mensaje'] = 'Error al eliminar: ' . mysqli_error($con);
    }
} else {
    $_SESSION['mensaje_tipo'] = 'error';
    $_SESSION['mensaje'] = 'Colaborador no encontrado';
}

// Cerrar conexión y redirigir
mysqli_close($con);
header('Location: index.php#colaboradores');
exit;
?>