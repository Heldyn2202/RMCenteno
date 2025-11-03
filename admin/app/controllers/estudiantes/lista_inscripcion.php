<?php  
 

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
    // Obtener las inscripciones que pertenecen al periodo académico seleccionado
    $sql_inscripciones = "SELECT * FROM inscripciones WHERE id_gestion = :id_gestion";
    $query_inscripciones = $pdo->prepare($sql_inscripciones);
    $query_inscripciones->bindParam(':id_gestion', $periodo_id);
    $query_inscripciones->execute();
    $inscripciones = $query_inscripciones->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Si no se selecciona un periodo, mostrar las inscripciones del periodo activo
    if ($gestion_activa) {
        $id_gestion_activa = $gestion_activa['id_gestion'];
        $sql_inscripciones = "SELECT * FROM inscripciones WHERE id_gestion = :id_gestion";
        $query_inscripciones = $pdo->prepare($sql_inscripciones);
        $query_inscripciones->bindParam(':id_gestion', $id_gestion_activa);
        $query_inscripciones->execute();
        $inscripciones = $query_inscripciones->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $inscripciones = []; // No hay inscripciones si no hay periodo activo
    }
}

// Declarar la variable $inscripciones como un array vacío
$inscripciones = []; // Inicializa la variable

// Validar si se recibió una solicitud POST  
if ($_SERVER["REQUEST_METHOD"] == "POST") {  
    // Obtener los datos del formulario  
    $id_gestion = $_POST['id_gestion'];  
    $nivel_id = $_POST['nivel_id'];  
    $grado = $_POST['grado'];  
    $nombre_seccion = $_POST['nombre_seccion'];  
    $turno_id = $_POST['turno_id'];  
    $talla_camisa = $_POST['talla_camisa'];  
    $talla_pantalon = $_POST['talla_pantalon'];  
    $talla_zapatos = $_POST['talla_zapatos'];  
    $estudiante_id = $_POST['estudiante_id'];  

    // Inicia la transacción  
    $pdo->beginTransaction();  

    // Insertar la nueva inscripción en la base de datos  
    $stmt = $pdo->prepare("INSERT INTO inscripciones (id_gestion, nivel_id, grado, nombre_seccion, turno_id, talla_camisa, talla_pantalon, talla_zapatos, estudiante_id)  
    VALUES (:id_gestion, :nivel_id, :grado, :nombre_seccion, :turno_id, :talla_camisa, :talla_pantalon, :talla_zapatos, :estudiante_id)");  

    if ($stmt->execute([  
        'id_gestion' => $id_gestion,  
        'nivel_id' => $nivel_id,  
        'grado' => $grado,  
        'nombre_seccion' => $nombre_seccion,  
        'turno_id' => $turno_id,  
        'talla_camisa' => $talla_camisa,  
        'talla_pantalon' => $talla_pantalon,  
        'talla_zapatos' => $talla_zapatos,  
        'estudiante_id' => $estudiante_id  
    ])) {  
        // Si se registra exitosamente  
        $pdo->commit();  
        $_SESSION['mensaje'] = "Inscripción registrada correctamente.";  
        $_SESSION['icono'] = "success";  
    } else {  
        // Si hay un error, se revierte la transacción  
        $pdo->rollBack();  
        $_SESSION['mensaje'] = "Error al registrar la inscripción.";  
        $_SESSION['icono'] = "error";  
    }
}

// Obtener todas las inscripciones para mostrarlas en la tabla
$sql_inscripciones = "SELECT * FROM inscripciones"; // Ajusta la consulta según tus necesidades
$query_inscripciones = $pdo->prepare($sql_inscripciones);
$query_inscripciones->execute();
$inscripciones = $query_inscripciones->fetchAll(PDO::FETCH_ASSOC); // Obtener todas las inscripciones

// Aquí puedes incluir el código HTML para mostrar las inscripciones en una tabla
?>