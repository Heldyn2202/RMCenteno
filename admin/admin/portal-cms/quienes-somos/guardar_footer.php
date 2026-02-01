<?php
// admin/admin/portal-cms/quienes-somos/guardar_footer.php

// ================= CONEXIÓN MANUAL =================
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sige";

$con = new mysqli($servername, $username, $password, $dbname);
if ($con->connect_error) {
    die("Error de conexión: " . $con->connect_error);
}
// ====================================================

session_start();

// Verificar login temporal
if (!isset($_SESSION['portal_admin_logged_in'])) {
    header('Location: ../index.php');
    exit;
}

// Verificar si es POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['mensaje_tipo'] = 'error';
    $_SESSION['mensaje'] = 'Método no permitido';
    header('Location: index.php');
    exit;
}

// Obtener datos del formulario
$titulo_izquierda = mysqli_real_escape_string($con, $_POST['titulo_izquierda'] ?? '');
$direccion = mysqli_real_escape_string($con, $_POST['direccion'] ?? '');
$email = mysqli_real_escape_string($con, $_POST['email'] ?? '');
$telefono = mysqli_real_escape_string($con, $_POST['telefono'] ?? '');
$titulo_derecha = mysqli_real_escape_string($con, $_POST['titulo_derecha'] ?? '');
$descripcion_derecha = mysqli_real_escape_string($con, $_POST['descripcion_derecha'] ?? '');
$derechos_autor = mysqli_real_escape_string($con, $_POST['derechos_autor'] ?? '');
$creditos = mysqli_real_escape_string($con, $_POST['creditos'] ?? '');

// Validar título izquierdo
if (empty($titulo_izquierda)) {
    $_SESSION['mensaje_tipo'] = 'error';
    $_SESSION['mensaje'] = 'El título izquierdo es obligatorio';
    header('Location: index.php');
    exit;
}

// Verificar si existe la tabla footer_config
$checkTable = mysqli_query($con, "SHOW TABLES LIKE 'footer_config'");
if (mysqli_num_rows($checkTable) == 0) {
    // Crear tabla si no existe
    $createTable = "CREATE TABLE IF NOT EXISTS footer_config (
        id INT PRIMARY KEY AUTO_INCREMENT,
        titulo_izquierda VARCHAR(200) DEFAULT 'Portal Escolar',
        direccion TEXT,
        email VARCHAR(100),
        telefono VARCHAR(50),
        titulo_derecha VARCHAR(200) DEFAULT 'Portal Escolar',
        descripcion_derecha TEXT,
        derechos_autor VARCHAR(200) DEFAULT '© [año] Portal Escolar | Institución Educativa',
        creditos VARCHAR(200) DEFAULT 'Desarrollado para la comunidad educativa',
        fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    mysqli_query($con, $createTable);
}

// Verificar si existe el registro
$checkRecord = mysqli_query($con, "SELECT id FROM footer_config WHERE id = 1");
if (mysqli_num_rows($checkRecord) == 0) {
    // Insertar nuevo registro
    $query = "INSERT INTO footer_config (
                titulo_izquierda,
                direccion,
                email,
                telefono,
                titulo_derecha,
                descripcion_derecha,
                derechos_autor,
                creditos
              ) VALUES (
                '$titulo_izquierda',
                '$direccion',
                '$email',
                '$telefono',
                '$titulo_derecha',
                '$descripcion_derecha',
                '$derechos_autor',
                '$creditos'
              )";
} else {
    // Actualizar registro existente
    $query = "UPDATE footer_config SET 
              titulo_izquierda = '$titulo_izquierda',
              direccion = '$direccion',
              email = '$email',
              telefono = '$telefono',
              titulo_derecha = '$titulo_derecha',
              descripcion_derecha = '$descripcion_derecha',
              derechos_autor = '$derechos_autor',
              creditos = '$creditos',
              fecha_actualizacion = NOW()
              WHERE id = 1";
}

if (mysqli_query($con, $query)) {
    $_SESSION['mensaje_tipo'] = 'success';
    $_SESSION['mensaje'] = 'Footer actualizado exitosamente';
} else {
    $_SESSION['mensaje_tipo'] = 'error';
    $_SESSION['mensaje'] = 'Error al guardar: ' . mysqli_error($con);
}

// Cerrar conexión y redirigir
mysqli_close($con);
header('Location: index.php#footer');
exit;
?>