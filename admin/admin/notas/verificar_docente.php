<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once('../../app/config.php');


function verificarDocente() {
    if (!isset($_SESSION['es_docente']) || !$_SESSION['es_docente'] || $_SESSION['rol_id'] != 5) {
        $_SESSION['mensaje'] = "❌ Acceso restringido. Esta función es solo para docentes.";
        $_SESSION['icono'] = "error";
        header('Location: ' . APP_URL . '/admin');
        exit();
    }
    
    return [
        'id_profesor' => $_SESSION['id_profesor'],
        'nombre_profesor' => $_SESSION['nombre_profesor'],
        'especialidad' => $_SESSION['especialidad'] ?? 'Docente'
    ];
}

// Función SIMPLIFICADA para obtener secciones
function obtenerSeccionesAsignadas($id_profesor) {
    global $pdo;
    
    try {
        // Obtener gestión activa
        $sql_gestion = "SELECT * FROM gestiones WHERE estado = 1 LIMIT 1";
        $query_gestion = $pdo->prepare($sql_gestion);
        $query_gestion->execute();
        $gestion_activa = $query_gestion->fetch(PDO::FETCH_ASSOC);
        
        if (!$gestion_activa) {
            return [];
        }
        
        // Consulta SIMPLIFICADA - mostrar todas las secciones activas
        $sql_secciones = "SELECT 
                            s.id_seccion, 
                            s.nombre_seccion,
                            g.grado as nombre_grado,
                            g.id_grado
                        FROM secciones s
                        INNER JOIN grados g ON s.id_grado = g.id_grado
                        WHERE s.id_gestion = :id_gestion 
                        AND s.estado = 1
                        ORDER BY g.grado, s.nombre_seccion";
        
        $query_secciones = $pdo->prepare($sql_secciones);
        $query_secciones->bindParam(':id_gestion', $gestion_activa['id_gestion']);
        $query_secciones->execute();
        
        return $query_secciones->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (Exception $e) {
        error_log("Error en obtenerSeccionesAsignadas: " . $e->getMessage());
        return [];
    }
}

// Función SIMPLIFICADA para obtener materias
function obtenerMateriasAsignadas($id_profesor, $id_seccion) {
    global $pdo;
    
    try {
        // Primero obtener el grado de la sección
        $sql_grado = "SELECT id_grado FROM secciones WHERE id_seccion = :id_seccion";
        $query_grado = $pdo->prepare($sql_grado);
        $query_grado->bindParam(':id_seccion', $id_seccion);
        $query_grado->execute();
        $seccion_data = $query_grado->fetch(PDO::FETCH_ASSOC);
        
        if (!$seccion_data) {
            return [];
        }
        
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
        
        return $query_materias->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (Exception $e) {
        error_log("Error en obtenerMateriasAsignadas: " . $e->getMessage());
        return [];
    }
}
?>