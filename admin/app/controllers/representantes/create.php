<?php  
include('../../../app/config.php'); // Incluir archivo de configuración de base de datos  

session_start(); // Iniciar sesión para manejar mensajes y datos anteriores  

if ($_SERVER['REQUEST_METHOD'] === 'POST') {  
    // Recibir los datos del formulario  
    $tipo_cedula = isset($_POST['tipo_cedula']) ? $_POST['tipo_cedula'] : null;  
    $cedula = isset($_POST['cedula']) ? $_POST['cedula'] : null;  
    $nombres = isset($_POST['nombres']) ? $_POST['nombres'] : null;  
    $apellidos = isset($_POST['apellidos']) ? $_POST['apellidos'] : null;  
    $fecha_nacimiento = isset($_POST['fecha_nacimiento']) ? $_POST['fecha_nacimiento'] : null;  
    $estado_civil = isset($_POST['estado_civil']) ? $_POST['estado_civil'] : null;  
    $genero = isset($_POST['genero']) ? $_POST['genero'] : null;  
    $correo_electronico = isset($_POST['correo_electronico']) ? $_POST['correo_electronico'] : null;  
    $tipo_sangre = isset($_POST['tipo_sangre']) ? $_POST['tipo_sangre'] : null;  
    $direccion = isset($_POST['direccion']) ? $_POST['direccion'] : null;  
    $numeros_telefonicos = isset($_POST['numeros_telefonicos']) ? $_POST['numeros_telefonicos'] : null;  
    $estatus = isset($_POST['estatus']) ? $_POST['estatus'] : null;  

    // Almacenar los datos en la sesión para poder llenarlos después en caso de error  
    $_SESSION['datos_representante'] = [  
        'tipo_cedula' => $tipo_cedula,  
        'cedula' => $cedula,  
        'nombres' => $nombres,  
        'apellidos' => $apellidos,  
        'fecha_nacimiento' => $fecha_nacimiento,  
        'estado_civil' => $estado_civil,  
        'genero' => $genero,  
        'correo_electronico' => $correo_electronico,  
        'tipo_sangre' => $tipo_sangre,  
        'direccion' => $direccion,  
        'numeros_telefonicos' => $numeros_telefonicos,  
        'estatus' => $estatus,  
    ];  

    // Verificar si la cédula ya está registrada  
    $stmt = $pdo->prepare("SELECT * FROM representantes WHERE cedula = :cedula");  
    $stmt->execute(['cedula' => $cedula]);  
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);  

    if (count($result) > 0) {  
        // La cédula ya está registrada  
        echo "La cédula ya está registrada. No se puede enviar el formulario.";  
    } else {  
        // Convertir la fecha de nacimiento al formato correcto para la DB (YYYY-MM-DD)  
        $fecha_nacimiento_array = explode('/', $fecha_nacimiento);  
        if (count($fecha_nacimiento_array) === 3) {  
            $fecha_nacimiento_db = "{$fecha_nacimiento_array[2]}-{$fecha_nacimiento_array[1]}-{$fecha_nacimiento_array[0]}"; // YYYY-MM-DD  
        } else {  
            // Manejar el caso de fecha no válida  
            $_SESSION['mensaje'] = "Fecha de nacimiento no válida.";  
            $_SESSION['icono'] = "error";  
            header('Location: ' . APP_URL . "/admin/representantes/create.php");  
            exit();  
        }  

        // Iniciar la transacción  
        $pdo->beginTransaction();  

        // Intentar insertar el nuevo representante en la base de datos  
        $stmt = $pdo->prepare("INSERT INTO representantes (tipo_cedula, cedula, nombres, apellidos, fecha_nacimiento, estado_civil, genero, correo_electronico, tipo_sangre, direccion, numeros_telefonicos, estatus)  
        VALUES (:tipo_cedula, :cedula, :nombres, :apellidos, :fecha_nacimiento, :estado_civil, :genero, :correo_electronico, :tipo_sangre, :direccion, :numeros_telefonicos, :estatus)");  

        if ($stmt->execute([  
            'tipo_cedula' => $tipo_cedula,  
            'cedula' => $cedula,  
            'nombres' => $nombres,  
            'apellidos' => $apellidos,  
            'fecha_nacimiento' => $fecha_nacimiento_db, // Usar YYYY-MM-DD en DB  
            'estado_civil' => $estado_civil,  
            'genero' => $genero,  
            'correo_electronico' => $correo_electronico,  
            'tipo_sangre' => $tipo_sangre,  
            'direccion' => $direccion,  
            'numeros_telefonicos' => $numeros_telefonicos,  
            'estatus' => $estatus,  
        ])) {  
            // Si se registra exitosamente  
            $pdo->commit();  
            $_SESSION['mensaje'] = "Se registró al representante de manera correcta";  
            $_SESSION['icono'] = "success";  
            // Limpiar datos de sesión  
            unset($_SESSION['datos_representante']);  
            header('Location: ' . APP_URL . "/admin/representantes");  
            exit();   
        } else {  
            // Si hubo un error al registrar  
            $pdo->rollBack();  
            $_SESSION['mensaje'] = "Error: no se pudo registrar en la base de datos. Comuníquese con el administrador.";  
            $_SESSION['icono'] = "error";  
            header('Location: ' . APP_URL . "/admin/representantes/create.php");  
            exit();  
        }  
    }  
} else {  
    echo "Método no permitido.";  
    exit();  
}  

// Parte para mostrar los datos del representante, asumiendo que ya has recuperado los datos de la base de datos  
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['cedula'])) {  
    $cedula = $_GET['cedula'];  
    
    // Concatenar la consulta para recuperar los datos de un representante  
    $stmt = $pdo->prepare("SELECT * FROM representantes WHERE cedula = :cedula");  
    $stmt->execute(['cedula' => $cedula]);  

    $representante = $stmt->fetch();  
    
    if ($representante) {  
        // Extraer la fecha de nacimiento y convertirla al formato DD/MM/YY  
        $fecha_nacimiento = $representante['fecha_nacimiento'];  
        $fecha_nacimiento_formateada = date("d/m/Y", strtotime($fecha_nacimiento)); // Convertir a DD/MM/YY  
        
        // Mostrar los datos del representante  
        echo "Nombre: " . htmlspecialchars($representante['nombres']) . " " . htmlspecialchars($representante['apellidos']) . "<br>";  
        echo "Cédula: " . htmlspecialchars($representante['cedula']) . "<br>";  
        echo "Fecha de Nacimiento: " . $fecha_nacimiento_formateada . "<br>";  
        echo "Estado Civil: " . htmlspecialchars($representante['estado_civil']) . "<br>";  
        echo "Género: " . htmlspecialchars($representante['genero']) . "<br>";  
        echo "Correo Electrónico: " . htmlspecialchars($representante['correo_electronico']) . "<br>";  
        echo "Tipo de Sangre: " . htmlspecialchars($representante['tipo_sangre']) . "<br>";  
        echo "Dirección: " . htmlspecialchars($representante['direccion']) . "<br>";  
        echo "Teléfonos: " . htmlspecialchars($representante['numeros_telefonicos']) . "<br>";  
        echo "Estatus: " . htmlspecialchars($representante['estatus']) . "<br>";  
    } else {  
        echo "No se encontró el representante.";  
    }  
}  
?>