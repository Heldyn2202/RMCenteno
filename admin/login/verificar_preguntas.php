<?php
include('../app/config.php');
session_start();

// Verificar si hay una recuperación en proceso
if (!isset($_SESSION['recovery_user_id']) || $_SESSION['recovery_method'] !== 'security') {
    header('Location: login.php');
    exit;
}

// Procesar respuestas
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $respuesta1 = strtolower(trim($_POST['respuesta1']));
    $respuesta2 = strtolower(trim($_POST['respuesta2']));
    
    // Obtener respuestas correctas
    $sql = "SELECT * FROM preguntas_seguridad WHERE usuario_id = :usuario_id";
    $query = $pdo->prepare($sql);
    $query->bindParam(':usuario_id', $_SESSION['recovery_user_id']);
    $query->execute();
    $preguntas = $query->fetch(PDO::FETCH_ASSOC);
    
    if ($preguntas && 
        strtolower(trim($preguntas['respuesta1'])) === $respuesta1 && 
        strtolower(trim($preguntas['respuesta2'])) === $respuesta2) {
        
        // Generar nueva contraseña
        $nueva_password = bin2hex(random_bytes(4)); // Contraseña temporal
        $password_hash = password_hash($nueva_password, PASSWORD_DEFAULT);
        
        // Actualizar contraseña
        $sql_update = "UPDATE usuarios SET password = :password WHERE id_usuario = :id";
        $query_update = $pdo->prepare($sql_update);
        $query_update->bindParam(':password', $password_hash);
        $query_update->bindParam(':id', $_SESSION['recovery_user_id']);
        
        if ($query_update->execute()) {
            $_SESSION['mensaje'] = "Tus respuestas son correctas. Tu nueva contraseña es: <strong>$nueva_password</strong>. Te recomendamos cambiarla después de iniciar sesión.";
            $_SESSION['icono'] = "success";
        } else {
            $_SESSION['mensaje'] = "Error al restablecer la contraseña.";
            $_SESSION['icono'] = "error";
        }
        
        // Limpiar sesión
        unset($_SESSION['recovery_user_id']);
        unset($_SESSION['recovery_method']);
        unset($_SESSION['pregunta1']);
        unset($_SESSION['pregunta2']);
        
        header('Location: login.php');
        exit;
    } else {
        $error = "Las respuestas proporcionadas son incorrectas.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificar Preguntas de Seguridad - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= APP_URL ?>/public/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/public/dist/css/adminlte.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="hold-transition login-page" style="background: #f4f6f9;">
    <div class="login-box" style="width: 500px;">
        <div class="card card-outline card-primary">
            <div class="card-header text-center">
                <h1 class="h1"><b><?= APP_NAME ?></b></h1>
                <p class="login-box-msg">Verificación de Preguntas de Seguridad</p>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                
                <form action="verificar_preguntas.php" method="post">
                    <div class="form-group">
                        <label for="pregunta1"><?= $_SESSION['pregunta1'] ?? 'Pregunta 1' ?></label>
                        <input type="text" class="form-control" id="pregunta1" name="respuesta1" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="pregunta2"><?= $_SESSION['pregunta2'] ?? 'Pregunta 2' ?></label>
                        <input type="text" class="form-control" id="pregunta2" name="respuesta2" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-6">
                            <a href="login.php" class="btn btn-secondary btn-block">Cancelar</a>
                        </div>
                        <div class="col-6">
                            <button type="submit" class="btn btn-primary btn-block">Verificar Respuestas</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>