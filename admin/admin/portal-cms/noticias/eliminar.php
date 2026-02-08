<?php
session_start();
require_once __DIR__ . '/../session-compat.php';

// Verificar sesión
if (!verificarSesionCMS()) {
    header('Location: ../../../login/login.php');
    exit;
}

// Verificar ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id = (int)$_GET['id'];

// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "sige");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Obtener información de la noticia para eliminar la imagen
$sql = "SELECT PostImage FROM tblposts WHERE id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();
$noticia = $resultado->fetch_assoc();

// Eliminar la imagen si existe
if ($noticia && $noticia['PostImage']) {
    $ruta_imagen = '../../../admin/uploads/post/' . $noticia['PostImage'];
    if (file_exists($ruta_imagen)) {
        unlink($ruta_imagen);
    }
}

// Eliminar la noticia de la base de datos
$sql = "DELETE FROM tblposts WHERE id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    $_SESSION['mensaje'] = 'Noticia eliminada correctamente';
    $_SESSION['icono'] = 'success';
    $_SESSION['titulo'] = '¡Éxito!';
} else {
    $_SESSION['mensaje'] = 'Error al eliminar la noticia';
    $_SESSION['icono'] = 'error';
    $_SESSION['titulo'] = 'Error';
}

$conexion->close();
header('Location: index.php');
exit;
?>