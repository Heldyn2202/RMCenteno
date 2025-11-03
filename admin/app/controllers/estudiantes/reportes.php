<?php  
include('../../app/config.php');  
include('../../admin/layout/parte1.php');  

// Recuperar los datos de la nueva inscripción  
if (isset($_GET['nuevaInscripcion'])) {  
    $nuevaInscripcion = unserialize(urldecode($_GET['nuevaInscripcion']));  

    // Generar el reporte de matrícula  
    $reporte = "Matrícula Escolar\n\n";  
    $reporte .= "Nombre: " . $nuevaInscripcion['estudiante_nombre'] . "\n";  
    $reporte .= "Período Académico: " . $nuevaInscripcion['periodo_academico'] . "\n";  
    $reporte .= "Nivel: " . ($nuevaInscripcion['nivel_id'] == 'inicial' ? 'Inicial' : 'Primaria') . "\n";  
    $reporte .= "Grado: " . getGradoDescripcion($nuevaInscripcion['grado_id']) . "\n";  
    $reporte .= "Sección: " . getSeccionDescripcion($nuevaInscripcion['seccion_id']) . "\n";  
    $reporte .= "Turno: " . ($nuevaInscripcion['turno_id'] == 'manana' ? 'Mañana' : 'Tarde') . "\n";  
    $reporte .= "Talla de Camisa: " . $nuevaInscripcion['talla_camisa'] . "\n";  
    $reporte .= "Talla de Pantalón: " . $nuevaInscripcion['talla_pantalon'] . "\n";  
    $reporte .= "Talla de Zapatos: " . $nuevaInscripcion['talla_zapatos'] . "\n";  

    // Guardar el reporte en un archivo  
    $nombre_archivo = "reporte_matricula_" . $nuevaInscripcion['estudiante_nombre'] . ".txt";  
    file_put_contents($nombre_archivo, $reporte);  

    // Guardar el reporte en la base de datos  
    guardarReporteMatricula($nuevaInscripcion);  

    // Redirigir al usuario a la página de reportes  
    header('Location: ' . APP_URL . "/reportes.php");  
    exit();  
}  

// Conexión a la base de datos  
include('../../../app/config.php');  

function guardarReporteMatricula($data) {  
    global $pdo;  

    $sql_insert_reporte = "INSERT INTO reportes (id_estudiante, periodo_academico, nivel_id, grado_id, seccion_id, turno_id, talla_camisa, talla_pantalon, talla_zapatos, nombre_archivo)  
                           VALUES (:id_estudiante, :periodo_academico, :nivel_id, :grado_id, :seccion_id, :turno_id, :talla_camisa, :talla_pantalon, :talla_zapatos, :nombre_archivo)";  
    $query_insert_reporte = $pdo->prepare($sql_insert_reporte);  
    $query_insert_reporte->bindParam(':id_estudiante', $data['id_estudiante'], PDO::PARAM_INT);  
    $query_insert_reporte->bindParam(':periodo_academico', $data['periodo_academico'], PDO::PARAM_STR);  
    $query_insert_reporte->bindParam(':nivel_id', $data['nivel_id'], PDO::PARAM_STR);  
    $query_insert_reporte->bindParam(':grado_id', $data['grado_id'], PDO::PARAM_STR);  
    $query_insert_reporte->bindParam(':seccion_id', $data['seccion_id'], PDO::PARAM_STR);  
    $query_insert_reporte->bindParam(':turno_id', $data['turno_id'], PDO::PARAM_STR);  
    $query_insert_reporte->bindParam(':talla_camisa', $data['talla_camisa'], PDO::PARAM_STR);  
    $query_insert_reporte->bindParam(':talla_pantalon', $data['talla_pantalon'], PDO::PARAM_STR);  
    $query_insert_reporte->bindParam(':talla_zapatos', $data['talla_zapatos'], PDO::PARAM_STR);  
    $query_insert_reporte->bindParam(':nombre_archivo', $data['nombre_archivo'], PDO::PARAM_STR);  
    $query_insert_reporte->execute();  
}