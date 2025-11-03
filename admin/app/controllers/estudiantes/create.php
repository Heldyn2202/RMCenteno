<?php  
include('../../../app/config.php'); // Incluye tu archivo de configuración de base de datos  

session_start(); // Iniciar sesión para manejar mensajes y datos anteriores  

if ($_SERVER['REQUEST_METHOD'] === 'POST') {  
    // Recibe los datos del formulario y los guarda en la sesión  
    $tipo_cedula = $_POST['tipo_cedula'];  
    $cedula = $_POST['cedula'];  
    $nombres = $_POST['nombres'];  
    $apellidos = $_POST['apellidos'];  
    $fecha_nacimiento = $_POST['fecha_nacimiento'];  
    $genero = $_POST['genero'];  
    $correo_electronico = $_POST['correo_electronico'];  
    $direccion = $_POST['direccion'];  
    $numeros_telefonicos = $_POST['numeros_telefonicos'];  
    $estatus = $_POST['estatus'];  
    $id_representante = isset($_POST['id_representante']) ? $_POST['id_representante'] : null; // Obtener el ID del representante  
    $posicion_hijo = isset($_POST['posicion_hijo']) ? $_POST['posicion_hijo'] : null; // Obtener la posición del hijo, puede ser NULL
    $tipo_discapacidad = isset($_POST['tipo_discapacidad']) ? $_POST['tipo_discapacidad'] : null; // Obtener el tipo de discapacidad

    // Almacena los datos en la sesión para volver a mostrarlos  
    $_SESSION['datos_estudiante'] = [  
        'tipo_cedula' => $tipo_cedula,  
        'cedula' => $cedula,  
        'nombres' => $nombres,  
        'apellidos' => $apellidos,  
        'fecha_nacimiento' => $fecha_nacimiento,   
        'genero' => $genero,  
        'correo_electronico' => $correo_electronico,  
        'direccion' => $direccion,  
        'numeros_telefonicos' => $numeros_telefonicos,  
        'estatus' => $estatus,  
        'id_representante' => $id_representante, // Guardar el ID del representante  
        'posicion_hijo' => $posicion_hijo, // Guardar la posición del hijo
        'tipo_discapacidad' => $tipo_discapacidad // Guardar el tipo de discapacidad
    ];  

    // Almacena los datos en la sesión para volver a mostrarlos  
    $_SESSION['datos_estudiante'] = [  
        'tipo_cedula' => $tipo_cedula,  
        'cedula' => $cedula,  
        'nombres' => $nombres,  
        'apellidos' => $apellidos,  
        'fecha_nacimiento' => $fecha_nacimiento,   
        'genero' => $genero,  
        'correo_electronico' => $correo_electronico,  
        'direccion' => $direccion,  
        'numeros_telefonicos' => $numeros_telefonicos,  
        'estatus' => $estatus,  
        'id_representante' => $id_representante, // Guardar el ID del representante  
        'posicion_hijo' => $posicion_hijo, // Guardar la posición del hijo
        'tipo_discapacidad' => $tipo_discapacidad // Guardar el tipo de discapacidad
    ];  

    // Verifica si se ha ingresado la cédula  
    if (!empty($cedula)) {  
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM estudiantes WHERE cedula = :cedula");  
        $stmt->execute(['cedula' => $cedula]);  
        if ($stmt->fetchColumn() > 0) {  
            // Si la cédula ya existe, se redirige con mensaje de error  
            $_SESSION['mensaje'] = "La cédula ya está registrada.";  
            $_SESSION['icono'] = "error";  
            header('Location: ' . APP_URL . "/admin/estudiantes/create.php");  
            exit();  
        }  
    } 

    // Obtener la cédula del representante
    $stmt_representante = $pdo->prepare("SELECT cedula FROM representantes WHERE id_representante = :id_representante");
    $stmt_representante->execute(['id_representante' => $id_representante]);
    $representante = $stmt_representante->fetch(PDO::FETCH_ASSOC);

    // Verifica si se encontró el representante
    if ($representante) {
        $cedula_representante = $representante['cedula']; // Asigna la cédula del representante
    } else {
        // Manejo de error si no se encuentra el representante
        $_SESSION['mensaje'] = "Error: No se encontró el representante con ID: " . $id_representante;
        $_SESSION['icono'] = "error";
        header('Location: ' . APP_URL . "/admin/estudiantes/create.php");
        exit();
    }

    // Generar la cédula escolar solo si no posee cédula de identidad
    $posee_cedula = isset($_POST['posee_cedula']) ? $_POST['posee_cedula'] : 'no';
    $cedula_escolar = null; // Inicializa la cédula escolar como NULL

    if ($posee_cedula === 'no') {
        // Generar la cédula escolar solo si no posee cédula de identidad
        $cedula_escolar = ($tipo_cedula === 'V' ? 'V' : 'E') . ($posicion_hijo ?? '0') . substr($fecha_nacimiento, 2, 2) . $cedula_representante;

        // Verificar si la cédula escolar ya existe
        $stmt_check_cedula_escolar = $pdo->prepare("SELECT COUNT(*) FROM estudiantes WHERE cedula_escolar = :cedula_escolar");
        $stmt_check_cedula_escolar->execute(['cedula_escolar' => $cedula_escolar]);
        if ($stmt_check_cedula_escolar->fetchColumn() > 0) {
            // Si la cédula escolar ya existe, se redirige con mensaje de error
            $_SESSION['mensaje'] = "La cédula escolar ya está registrada.";
            $_SESSION['icono'] = "error";
            header('Location: ' . APP_URL . "/admin/estudiantes/create.php");
            exit();
        }
    }

    // Inicia la transacción  
    $pdo->beginTransaction();  

    // Inserta el nuevo estudiante en la base de datos  
    $stmt = $pdo->prepare("INSERT INTO estudiantes (tipo_cedula, cedula, nombres, apellidos, fecha_nacimiento, genero, correo_electronico, direccion, numeros_telefonicos, estatus, id_representante, cedula_escolar, posicion_hijo, tipo_discapacidad)  
    VALUES (:tipo_cedula, :cedula, :nombres, :apellidos, :fecha_nacimiento, :genero, :correo_electronico, :direccion, :numeros_telefonicos, :estatus, :id_representante, :cedula_escolar, :posicion_hijo, :tipo_discapacidad)");  

    if ($stmt->execute([  
        'tipo_cedula' => $tipo_cedula,  
        'cedula' => $cedula,  
        'nombres' => $nombres,  
        'apellidos' => $apellidos,  
        'fecha_nacimiento' => $fecha_nacimiento,  
        'genero' => $genero,  
        'correo_electronico' => $correo_electronico,  
        'direccion' => $direccion,  
        'numeros_telefonicos' => $numeros_telefonicos,  
        'estatus' => $estatus,  
        'id_representante' => $id_representante, // Incluir el ID del representante  
        'cedula_escolar' => $cedula_escolar, // Incluir la cédula escolar generada o NULL  
        'posicion_hijo' => $posicion_hijo, // Incluir la posición del hijo, puede ser NULL  
        'tipo_discapacidad' => $tipo_discapacidad // Incluir el tipo de discapacidad
    ])) {  
        // Si se registra exitosamente  
        $pdo->commit();  
        $_SESSION['mensaje'] = "Se registró al estudiante de manera correcta";  
        $_SESSION['icono'] = "success";  
        header('Location: ' . APP_URL . "/admin/estudiantes/Lista_de_estudiante.php");  
        exit();  
    } else {  
        // Si hubo un error al registrar  
        $pdo->rollBack();  
        $_SESSION['mensaje'] = "Error: no se pudo registrar en la base de datos. Comuníquese con el administrador.";  
        $_SESSION['icono'] = "error";  
        header('Location: ' . APP_URL . "/admin/estudiantes/create.php");  
        exit();  
    }  
} else {   
    echo "Método no permitido.";  
}  
?>