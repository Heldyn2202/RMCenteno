<?php


// Incluir el archivo de configuración que establece la conexión a la base de datos
include ('../../../app/config.php');

// Obtener el periodo académico activo
$sql_gestiones = "SELECT * FROM gestiones WHERE estado = 1 ORDER BY desde DESC LIMIT 1"; // Suponiendo que el estado 1 es activo
$query_gestiones = $pdo->prepare($sql_gestiones);
$query_gestiones->execute();
$gestion_activa = $query_gestiones->fetch(PDO::FETCH_ASSOC);

// Obtener los periodos académicos anteriores
$sql_periodos = "SELECT * FROM gestiones WHERE estado = 0 ORDER BY desde DESC"; // Suponiendo que el estado 0 es inactivo
$query_periodos = $pdo->prepare($sql_periodos);
$query_periodos->execute();
$periodos_anteriores = $query_periodos->fetchAll(PDO::FETCH_ASSOC);

// Determinar el periodo a mostrar
if (isset($_GET['periodo_id'])) {
    $periodo_id = $_GET['periodo_id'];
    // Obtener las secciones que pertenecen al periodo académico seleccionado
    $sql_secciones = "SELECT s.*, g.grado FROM secciones s
                      JOIN grados g ON s.id_grado = g.id_grado
                      WHERE s.id_gestion = :id_gestion";
    $query_secciones = $pdo->prepare($sql_secciones);
    $query_secciones->bindParam(':id_gestion', $periodo_id);
    $query_secciones->execute();
    $secciones = $query_secciones->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Si no se selecciona un periodo, mostrar las secciones del periodo activo
    if ($gestion_activa) {
        $id_gestion_activa = $gestion_activa['id_gestion'];
        $sql_secciones = "SELECT s.*, g.grado FROM secciones s
                          JOIN grados g ON s.id_grado = g.id_grado
                          WHERE s.id_gestion = :id_gestion";
        $query_secciones = $pdo->prepare($sql_secciones);
        $query_secciones->bindParam(':id_gestion', $id_gestion_activa);
        $query_secciones->execute();
        $secciones = $query_secciones->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $secciones = []; // No hay secciones si no hay periodo activo
    }
}

// Guardar el periodo académico activo para usar en la vista
if ($gestion_activa) {
    $anio_desde = date('Y', strtotime($gestion_activa['desde']));
    $anio_hasta = date('Y', strtotime($gestion_activa['hasta']));
    $periodo_academico = $anio_desde . '-' . $anio_hasta; // Formato YYYY-YYYY
} else {
    $periodo_academico = 'No hay periodo activo';
}
?>