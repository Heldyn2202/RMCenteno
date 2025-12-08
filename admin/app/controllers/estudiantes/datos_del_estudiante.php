<?php  
// Asegúrate de validar y sanitizar este valor  
include ('../../app/config.php');  

// Asegúrate de que $id_estudiante esté definido y sea un número entero  
$id_estudiante = isset($_GET['id']) ? intval($_GET['id']) : 0;  

// Consulta para obtener el estudiante específico por ID, incluyendo el id_representante
$sql_estudiantes = "SELECT e.id_estudiante, e.tipo_cedula, e.cedula, e.nombres, e.apellidos, 
                           e.fecha_nacimiento, e.genero, e.correo_electronico, e.direccion, 
                           e.numeros_telefonicos, e.estatus, e.posicion_hijo, e.cedula_escolar, 
                           e.id_representante, e.tipo_discapacidad, r.nombres AS rep_nombres, 
                           r.apellidos AS rep_apellidos
                    FROM estudiantes e
                    LEFT JOIN representantes r ON e.id_representante = r.id_representante
                    WHERE e.id_estudiante = :id";  
                    
$query_estudiantes = $pdo->prepare($sql_estudiantes);  
$query_estudiantes->bindParam(':id', $id_estudiante, PDO::PARAM_INT);  
$query_estudiantes->execute();  
$estudiante = $query_estudiantes->fetch(PDO::FETCH_ASSOC);

if ($estudiante) {  
    // Asignar los valores a las variables  
    $tipo_cedula = $estudiante['tipo_cedula'];  
    $cedula = $estudiante['cedula'];  
    $nombres = $estudiante['nombres'];  
    $apellidos = $estudiante['apellidos'];  
    $fecha_nacimiento = $estudiante['fecha_nacimiento'];  
    $genero = $estudiante['genero'];  
    $correo_electronico = $estudiante['correo_electronico'];  
    $direccion = $estudiante['direccion'];  
    $numeros_telefonicos = $estudiante['numeros_telefonicos'];  
    $estatus = $estudiante['estatus'];  
    $posicion_hijo = $estudiante['posicion_hijo'];  
    $cedula_escolar = $estudiante['cedula_escolar'] ?? '';  
    $id_representante = $estudiante['id_representante'];
    $tipo_discapacidad = $estudiante['tipo_discapacidad']; 
    
    // Obtener nombre del representante
    if (!empty($estudiante['rep_nombres']) && !empty($estudiante['rep_apellidos'])) {
        $representante_nombre = $estudiante['rep_nombres'] . " " . $estudiante['rep_apellidos'];
    } else {
        $representante_nombre = "No asignado";
    }
  
} else {  
    // Manejo de error si no se encuentra el estudiante  
    $_SESSION['mensaje'] = "Error: No se encontró el estudiante con ID: " . htmlspecialchars($id_estudiante);  
    $_SESSION['icono'] = "error";  
    header('Location: ' . APP_URL . '/admin/estudiantes/Lista_de_estudiante.php');  
    exit();  
}  
?>