<?php  
include('../../config.php'); // Asegúrate de tener la conexión a la base de datos incluida  

if ($_SERVER['REQUEST_METHOD'] === 'POST') {  
    $cedula = $_POST['cedula'];  

    // Sanitizar la entrada  
    $cedula = trim(strip_tags($cedula)); // Remueve espacios en blanco y etiquetas HTML  

    // Verificar si la cédula ya está registrada en representantes  
    $query_buscar_representantes = $pdo->prepare("SELECT * FROM representantes WHERE cedula = :cedula");  
    $query_buscar_representantes->bindParam(':cedula', $cedula, PDO::PARAM_STR); // Enlaza la variable  
    $query_buscar_representantes->execute();  
    $existe_representante = $query_buscar_representantes->fetchAll(PDO::FETCH_ASSOC);  

    if (count($existe_representante) > 0) {  
        // La cédula ya está registrada en la tabla de representantes  
        echo 'existe';  
        exit; // Salir del script  
    }  

    // Verificar si la cédula ya está registrada en estudiantes  
    $query_buscar_estudiantes = $pdo->prepare("SELECT * FROM estudiantes WHERE cedula = :cedula");  
    $query_buscar_estudiantes->bindParam(':cedula', $cedula, PDO::PARAM_STR); // Enlaza la variable  
    $query_buscar_estudiantes->execute();  
    $existe_estudiante = $query_buscar_estudiantes->fetchAll(PDO::FETCH_ASSOC);  

    if (count($existe_estudiante) > 0) {  
        // La cédula ya está registrada en la tabla de estudiantes  
        echo 'existe';  
    } else {  
        // La cédula no está registrada en ninguna tabla  
        echo 'no_existe';  
    }   
}  
?>