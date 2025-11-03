<?php
// guardar.php
include('../../app/config.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $ancho = $_POST['ancho'];
    $alto = $_POST['alto'];
    $estatus = $_POST['estatus'];

    try {
        $query = $pdo->prepare("INSERT INTO plantillas_carnet (nombre, descripcion, ancho, alto, estatus) VALUES (:nombre, :descripcion, :ancho, :alto, :estatus)");
        $query->bindParam(':nombre', $nombre);
        $query->bindParam(':descripcion', $descripcion);
        $query->bindParam(':ancho', $ancho);
        $query->bindParam(':alto', $alto);
        $query->bindParam(':estatus', $estatus);
        $query->execute();

        $_SESSION['message'] = "Plantilla de carnet creada exitosamente.";
        header('Location: ../../admin/plantillas'); // Redirige al index de plantillas
        exit;
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error al crear la plantilla: " . $e->getMessage();
        header('Location: ../../admin/plantillas/crear_plantilla.php'); // Redirige al formulario de creación
        exit;
    }
} else {
    // Si no es una petición POST, redirige
    header('Location: ../../admin/plantillas/crear_plantilla.php');
    exit;
}
?>
