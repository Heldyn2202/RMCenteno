<?php  
include('../../app/config.php'); // Incluye tu archivo de configuración de base de datos  

if ($_SERVER['REQUEST_METHOD'] === 'POST') {  
    try {  
        // Recibe los datos del formulario  
        $id_gestion = $_POST['id_gestion'];  
        $nivel_id = $_POST['nivel_id'];  
        $grado = $_POST['grado'];  
        $nombre_seccion = $_POST['nombre_seccion'];  
        $turno_id = $_POST['turno_id'];  
        $talla_camisa = $_POST['talla_camisa'];  
        $talla_pantalon = $_POST['talla_pantalon'];  
        $talla_zapatos = $_POST['talla_zapatos'];  
        $estudiante_id = $_POST['estudiante_id']; // ID del estudiante que se está inscribiendo  

        // Obtener el nombre del estudiante  
        $stmt = $pdo->prepare("SELECT CONCAT(nombres, ' ', apellidos) AS nombre_estudiante FROM estudiantes WHERE id = :estudiante_id");  
        $stmt->execute(['estudiante_id' => $estudiante_id]);  
        $nombre_estudiante = $stmt->fetchColumn();  

        // Verifica si la inscripción ya existe  
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM inscripciones WHERE estudiante_id = :estudiante_id AND id_gestion = :id_gestion");  
        $stmt->execute(['estudiante_id' => $estudiante_id, 'id_gestion' => $id_gestion]);  

        if ($stmt->fetchColumn() > 0) {  
            // Si la inscripción ya existe, se redirige con mensaje de error  
            $_SESSION['mensaje'] = "El estudiante ya está inscrito en este periodo académico.";  
            $_SESSION['icono'] = "error";  
            header('Location: ' . APP_URL . "/admin/estudiantes/create.php");  
            exit();  
        }  

        // Verificar la capacidad de la sección
        $stmt = $pdo->prepare("SELECT capacidad, cupo_actual FROM secciones WHERE nombre_seccion = :nombre_seccion");
        $stmt->execute(['nombre_seccion' => $nombre_seccion]);
        $seccion = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($seccion) {
            // Comprobar si hay cupos disponibles
            if ($seccion['cupo_actual'] >= $seccion['capacidad']) {
                // Redirigir con mensaje de error si no hay cupos disponibles
                $_SESSION['mensaje'] = "No hay cupos disponibles en la sección seleccionada.";
                $_SESSION['icono'] = "error";
                header('Location: ' . APP_URL . "/admin/estudiantes/create.php");
                exit();
            }
        } else {
            // Redirigir con mensaje de error si la sección no existe
            $_SESSION['mensaje'] = "La sección seleccionada no existe.";
            $_SESSION['icono'] = "error";
            header('Location: ' . APP_URL . "/admin/estudiantes/create.php");
            exit();
        }

        // Inicia la transacción  
        $pdo->beginTransaction();  

        // Inserta la nueva inscripción en la base de datos  
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
            // Incrementar el cupo actual de la sección
            $stmt = $pdo->prepare("UPDATE secciones SET cupo_actual = cupo_actual + 1 WHERE nombre_seccion = :nombre_seccion");
            $stmt->execute(['nombre_seccion' => $nombre_seccion]);

            $pdo->commit();  
            $_SESSION['mensaje'] = "Inscripción registrada correctamente.";  
            $_SESSION['icono'] = "success";  
            header('Location: ' . APP_URL . "/admin/estudiantes/Lista_de_estudiante.php");  
            exit();  
        } else {  
            // Si hubo un error al registrar  
            $pdo->rollBack();  
            $_SESSION['mensaje'] = "Error: no se pudo registrar la inscripción en la base de datos. Comuníquese con el administrador.";  
            $_SESSION['icono'] = "error";  
            header('Location: ' . APP_URL . "/admin/estudiantes/create.php");  
            exit();  
        }  
    } catch (PDOException $e) {  
        // Manejar el error de conexión a la base de datos  
        $pdo->rollBack(); // Asegúrate de revertir cualquier cambio en caso de error  
        $_SESSION['mensaje'] = "Error de base de datos: " . $e->getMessage();  
        $_SESSION['icono'] = "error";  
        header('Location: ' . APP_URL . "/admin/estudiantes/create.php");  
        exit();  
    }  
} else {  
    // Si no es una solicitud POST, redirige al formulario de inscripción  
    header('Location: ' . APP_URL . "/admin/estudiantes/create.php");  
    exit();  
}  
?>