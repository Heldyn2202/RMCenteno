<?php
require_once('../../app/config.php');

$seccion_id = $_GET['seccion_id'] ?? null;
$id_profesor = $_GET['id_profesor'] ?? null;

if ($seccion_id) {
    try {
        // Obtener el grado de la sección
        $sql_grado = "SELECT id_grado FROM secciones WHERE id_seccion = :id_seccion";
        $query_grado = $pdo->prepare($sql_grado);
        $query_grado->bindParam(':id_seccion', $seccion_id);
        $query_grado->execute();
        $seccion_data = $query_grado->fetch(PDO::FETCH_ASSOC);
        
        if ($seccion_data) {
            $id_grado = $seccion_data['id_grado'];
            
            // Obtener todas las materias del grado
            $sql_materias = "SELECT 
                                m.id_materia, 
                                m.nombre_materia,
                                m.abreviatura
                            FROM materias m
                            WHERE m.id_grado = :id_grado
                            AND m.estado = 1
                            ORDER BY m.nombre_materia";
            
            $query_materias = $pdo->prepare($sql_materias);
            $query_materias->bindParam(':id_grado', $id_grado);
            $query_materias->execute();
            $materias = $query_materias->fetchAll(PDO::FETCH_ASSOC);
            
            echo '<option value="">Seleccionar Materia</option>';
            foreach ($materias as $materia) {
                echo '<option value="' . $materia['id_materia'] . '">' 
                     . htmlspecialchars($materia['nombre_materia']) 
                     . (!empty($materia['abreviatura']) ? ' (' . htmlspecialchars($materia['abreviatura']) . ')' : '')
                     . '</option>';
            }
            
            if (empty($materias)) {
                echo '<option value="">No hay materias para este grado</option>';
            }
        } else {
            echo '<option value="">Sección no encontrada</option>';
        }
    } catch (Exception $e) {
        echo '<option value="">Error al cargar materias</option>';
    }
} else {
    echo '<option value="">Seleccionar Materia</option>';
}
?>