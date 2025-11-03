<?php  
// Asegúrate de validar y sanitizar este valor  
include ('../../app/config.php');  

// Asegúrate de que $id_estudiante esté definido y sea un número entero  
$id_estudiante = isset($_GET['id']) ? intval($_GET['id']) : 0;  

// Consulta para obtener el estudiante específico por ID, incluyendo el id_representante
$sql_estudiantes = "SELECT id_estudiante, tipo_cedula, cedula, nombres, apellidos, fecha_nacimiento, genero, correo_electronico, direccion, numeros_telefonicos, estatus, posicion_hijo, cedula_escolar, id_representante, tipo_discapacidad FROM estudiantes WHERE id_estudiante = :id";  
$query_estudiantes = $pdo->prepare($sql_estudiantes);  
$query_estudiantes->bindParam(':id', $id_estudiante, PDO::PARAM_INT);  
$query_estudiantes->execute();  
$estudiante = $query_estudiantes->fetch(PDO::FETCH_ASSOC); // Cambiado a fetch para obtener un solo registro  
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
    $id_representante = $estudiante['id_representante']; // Ahora puedes acceder al id_representante
    $tipo_discapacidad = $estudiante['tipo_discapacidad']; 
  
} else {  
    // Manejo de error si no se encuentra el estudiante  
    $_SESSION['mensaje'] = "Error: No se encontró el estudiante con ID: " . htmlspecialchars($id_estudiante);  
    $_SESSION['icono'] = "error";  
    header('Location: ' . APP_URL . '/admin/estudiantes/Lista_de_estudiante.php');  
    exit();  
}  
?>  


