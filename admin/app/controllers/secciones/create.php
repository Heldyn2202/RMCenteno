<?php
include ('../../../app/config.php');

// Obtener el periodo académico activo
$sql_gestion = "SELECT * FROM gestiones WHERE estado = 1 ORDER BY desde DESC LIMIT 1";
$query_gestion = $pdo->prepare($sql_gestion);
$query_gestion->execute();
$gestion_activa = $query_gestion->fetch(PDO::FETCH_ASSOC);

// Verificar si se encontró un periodo académico activo
if (!$gestion_activa) {
    session_start();
    $_SESSION['mensaje'] = "Error: No hay periodo académico activo disponible.";
    $_SESSION['icono'] = "error";
    header('Location: ' . APP_URL . "/admin/configuraciones/secciones/create.php");
    exit();
}

// Obtener los datos del formulario
$turno = $_POST['turno'] ?? null;
$capacidad = $_POST['capacidad'] ?? null;
$id_grado = $_POST['id_grado'] ?? null;
$id_gestion = $gestion_activa['id_gestion']; // Usar el id_gestion del periodo académico activo
$nombre_seccion = strtoupper(trim($_POST['nombre_seccion'] ?? '')); // Convertir a mayúsculas y limpiar espacios
$estado = 1; // Se crea activa por defecto

// Validar que los campos requeridos no estén vacíos
if (empty($turno) || empty($capacidad) || empty($id_grado) || empty($nombre_seccion)) {
    session_start();
    $_SESSION['mensaje'] = "Error: Todos los campos son obligatorios.";
    $_SESSION['icono'] = "error";
    header('Location: ' . APP_URL . "/admin/configuraciones/secciones/create.php");
    exit(); 
}

// ✅ Verificar si ya existe una sección igual en el mismo grado y periodo activo
$sql_verificar = "SELECT COUNT(*) AS total FROM secciones 
                  WHERE id_grado = :id_grado 
                  AND id_gestion = :id_gestion 
                  AND UPPER(nombre_seccion) = :nombre_seccion";

$query_verificar = $pdo->prepare($sql_verificar);
$query_verificar->bindParam(':id_grado', $id_grado);
$query_verificar->bindParam(':id_gestion', $id_gestion);
$query_verificar->bindParam(':nombre_seccion', $nombre_seccion);
$query_verificar->execute();
$resultado = $query_verificar->fetch(PDO::FETCH_ASSOC);

if ($resultado && $resultado['total'] > 0) {
    session_start();
    $_SESSION['mensaje'] = "⚠️ Ya existe la sección '$nombre_seccion' para este grado en el periodo académico activo.";
    $_SESSION['icono'] = "warning";
    header('Location: ' . APP_URL . "/admin/configuraciones/secciones/create.php");
    exit();
}

// Preparar la sentencia SQL para insertar la nueva sección
$sentencia = $pdo->prepare('INSERT INTO secciones (turno, capacidad, id_grado, id_gestion, nombre_seccion, estado)
VALUES (:turno, :capacidad, :id_grado, :id_gestion, :nombre_seccion, :estado)');

// Vincular los parámetros
$sentencia->bindParam(':turno', $turno);
$sentencia->bindParam(':capacidad', $capacidad);
$sentencia->bindParam(':id_grado', $id_grado);
$sentencia->bindParam(':id_gestion', $id_gestion);
$sentencia->bindParam(':nombre_seccion', $nombre_seccion);
$sentencia->bindParam(':estado', $estado);

if ($sentencia->execute()) {
    session_start();
    $_SESSION['mensaje'] = "✅ Se registró la sección '$nombre_seccion' de manera correcta.";
    $_SESSION['icono'] = "success";
    header('Location: ' . APP_URL . "/admin/configuraciones/secciones");
    exit();
} else {
    session_start();
    $_SESSION['mensaje'] = "Error: no se pudo registrar en la base de datos, comuníquese con el administrador.";
    $_SESSION['icono'] = "error";
    header('Location: ' . APP_URL . "/admin/configuraciones/secciones/create.php");
    exit();
}
?>
