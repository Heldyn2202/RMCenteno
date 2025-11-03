<?php
// actualizar.php
include('../../app/config.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_plantilla = $_POST['id_plantilla'];
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
     $ancho = $_POST['ancho'];
    $alto = $_POST['alto'];
    $estatus = $_POST['estatus'];

    try {
        $query = $pdo->prepare("UPDATE plantillas_carnet SET nombre = :nombre, descripcion = :descripcion, ancho = :ancho, alto = :alto, estatus = :estatus WHERE id_plantilla = :id_plantilla");
        $query->bindParam(':id_plantilla', $id_plantilla);
        $query->bindParam(':nombre', $nombre);
        $query->bindParam(':descripcion', $descripcion);
        $query->bindParam(':ancho', $ancho);
        $query->bindParam(':alto', $alto);
        $query->bindParam(':estatus', $estatus);
        $query->execute();

        $_SESSION['message'] = "Plantilla de carnet actualizada exitosamente.";
        header('Location: ../../admin/plantillas'); // Redirige al index de plantillas
        exit;
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error al actualizar la plantilla: " . $e->getMessage();
        header('Location: ../../admin/plantillas/editar_plantilla.php?id=" . $id_plantilla); // Redirige al formulario de edición
        exit;
    }
} else {
    // Si no es una petición POST, redirige
    header('Location: ../../admin/plantillas/editar_plantilla.php?id=" . $id_plantilla);
    exit;
}
?>
