<?php  
include('../../app/config.php');  

$nivel = isset($_GET['nivel']) ? $_GET['nivel'] : null;  

if ($nivel === null) {
    echo json_encode(['error' => 'Falta el parámetro nivel.']);
    exit;
}

// También puede recibir ID del estudiante para validaciones
$id_estudiante = isset($_GET['id_estudiante']) ? $_GET['id_estudiante'] : null;

$sql = "SELECT * FROM grados WHERE nivel = :nivel AND estado = 1 ORDER BY grado";  
$query = $pdo->prepare($sql);  
$query->bindParam(':nivel', $nivel);  
$query->execute();  
$grados = $query->fetchAll(PDO::FETCH_ASSOC);  

// Si se proporciona ID del estudiante, obtener información de restricciones
if ($id_estudiante) {
    // Obtener si es repitente
    $sql_repitente = "SELECT COUNT(*) as total FROM inscripciones 
                      WHERE id_estudiante = :id_estudiante 
                      AND estado_inscripcion = 'repitente'";
    $query_repitente = $pdo->prepare($sql_repitente);
    $query_repitente->bindParam(':id_estudiante', $id_estudiante);
    $query_repitente->execute();
    $repitente_info = $query_repitente->fetch(PDO::FETCH_ASSOC);
    $es_repitente = ($repitente_info['total'] > 0);
    
    // Obtener si tiene aplazos
    $sql_aplazos = "SELECT COUNT(*) as total FROM estudiantes_aplazados 
                    WHERE id_estudiante = :id_estudiante 
                    AND estado = 'pendiente'";
    $query_aplazos = $pdo->prepare($sql_aplazos);
    $query_aplazos->bindParam(':id_estudiante', $id_estudiante);
    $query_aplazos->execute();
    $aplazos_info = $query_aplazos->fetch(PDO::FETCH_ASSOC);
    $tiene_aplazos = ($aplazos_info['total'] > 0);
    
    // Obtener último grado
    $sql_ultimo_grado = "SELECT g.* FROM inscripciones i 
                         JOIN secciones s ON i.id_seccion = s.id_seccion
                         JOIN grados g ON s.id_grado = g.id_grado
                         WHERE i.id_estudiante = :id_estudiante 
                         ORDER BY i.id_inscripcion DESC LIMIT 1";
    $query_ultimo_grado = $pdo->prepare($sql_ultimo_grado);
    $query_ultimo_grado->bindParam(':id_estudiante', $id_estudiante);
    $query_ultimo_grado->execute();
    $ultimo_grado = $query_ultimo_grado->fetch(PDO::FETCH_ASSOC);
    
    // Función para obtener número de grado
    function obtenerNumeroGradoDesdeAjax($nombreGrado) {
        if (preg_match('/(\d+)/', $nombreGrado, $matches)) {
            return intval($matches[1]);
        }
        return 0;
    }
    
    $ultimo_grado_numero = $ultimo_grado ? obtenerNumeroGradoDesdeAjax($ultimo_grado['grado']) : 0;
    
    // Agregar información de validación a cada grado
    foreach ($grados as &$grado) {
        $grado_numero = obtenerNumeroGradoDesdeAjax($grado['grado']);
        $grado['permitido'] = true;
        $grado['mensaje'] = '';
        
        if ($ultimo_grado_numero > 0) {
            if ($es_repitente || $tiene_aplazos) {
                if ($grado_numero > $ultimo_grado_numero) {
                    $grado['permitido'] = false;
                    $grado['mensaje'] = 'No permitido para ' . ($es_repitente ? 'repitentes' : 'estudiantes con aplazos');
                }
            } else {
                if ($grado_numero > ($ultimo_grado_numero + 1)) {
                    $grado['permitido'] = false;
                    $grado['mensaje'] = 'No puede saltar años académicos';
                }
            }
        }
    }
}

header('Content-Type: application/json');
echo json_encode($grados);  
?>