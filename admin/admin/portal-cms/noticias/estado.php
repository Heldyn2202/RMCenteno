<?php
session_start();
require_once __DIR__ . '/../session-compat.php';

// Verificar sesión
if (!verificarSesionCMS()) {
    header('Location: ../../../login/login.php');
    exit;
}

// Verificar parámetros
if (!isset($_GET['id']) || !isset($_GET['estado'])) {
    header('Location: index.php');
    exit;
}

$id = (int)$_GET['id'];
$estado = (int)$_GET['estado'];

// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "sige");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Cambiar estado
$sql = "UPDATE tblposts SET Is_Active = ?, UpdationDate = NOW() WHERE id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("ii", $estado, $id);

if ($stmt->execute()) {
    $accion = $estado ? 'activada' : 'desactivada';
    $_SESSION['mensaje'] = "Noticia $accion correctamente";
    $_SESSION['icono'] = 'success';
    $_SESSION['titulo'] = '¡Éxito!';
} else {
    $_SESSION['mensaje'] = 'Error al cambiar el estado de la noticia';
    $_SESSION['icono'] = 'error';
    $_SESSION['titulo'] = 'Error';
}

$conexion->close();
header('Location: index.php');
exit;
?>