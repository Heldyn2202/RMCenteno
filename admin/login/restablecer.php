<?php
include('../app/config.php');
session_start();

// Verificar si se proporcionó token y email
if (!isset($_GET['token']) || !isset($_GET['email'])) {
    $_SESSION['mensaje'] = "Enlace inválido o expirado";
    $_SESSION['icono'] = "error";
    header('Location: index.php');
    exit;
}

$token = $_GET['token'];
$email = urldecode($_GET['email']);

// Verificar token y expiración
$sql = "SELECT * FROM usuarios WHERE email = :email AND token_recuperacion = :token AND expiracion_token > NOW()";
$query = $pdo->prepare($sql);
$query->bindParam(':email', $email);
$query->bindParam(':token', $token);
$query->execute();
$usuario = $query->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    $_SESSION['mensaje'] = "Enlace inválido o expirado";
    $_SESSION['icono'] = "error";
    header('Location: index.php');
    exit;
}

// Variable para controlar SweetAlert2 en caso de contraseñas no coincidan
$mostrar_sweetalert_mismatch = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nueva_password = $_POST['password'];
    $confirmar_password = $_POST['confirm_password'];
    
    if ($nueva_password !== $confirmar_password) {
        // En lugar de usar la sesión para el mensaje, activamos la variable para mostrar SweetAlert2
        $mostrar_sweetalert_mismatch = true;
        $_SESSION['icono'] = "error";
    } elseif (strlen($nueva_password) < 6) {
        $_SESSION['mensaje'] = "La contraseña debe tener al menos 6 caracteres";
        $_SESSION['icono'] = "error";
    } else {
        // Hashear nueva contraseña
        $password_hash = password_hash($nueva_password, PASSWORD_DEFAULT);
        
        // Actualizar contraseña y limpiar token
        $sql_update = "UPDATE usuarios SET password = :password, token_recuperacion = NULL, expiracion_token = NULL WHERE email = :email";
        $query_update = $pdo->prepare($sql_update);
        $query_update->bindParam(':password', $password_hash);
        $query_update->bindParam(':email', $email);
        
        if ($query_update->execute()) {
            // Usamos sesión para el mensaje de éxito y redirigimos
            $_SESSION['mensaje'] = "✅ Contraseña restablecida exitosamente. Ya puedes iniciar sesión.";
            $_SESSION['icono'] = "success";
            header('Location: login.php');
            exit;
        } else {
            $_SESSION['mensaje'] = "❌ Error al restablecer la contraseña";
            $_SESSION['icono'] = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= APP_URL ?>/public/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/public/dist/css/adminlte.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .login-page { background: #f4f6f9; }
        .card { border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
        .card-header { 
            background: linear-gradient(135deg, #3c8dbc 0%, #2d5f7e 100%); 
            color: white; 
            border-radius: 15px 15px 0 0;
        }
    </style>
</head>
<body class="hold-transition login-page">
    <div class="login-box" style="width: 400px; margin-top: 50px;">
        <div class="card card-outline card-primary">
            <div class="card-header text-center">
                <h1 class="h1"><b><?= APP_NAME ?></b></h1>
                <p class="login-box-msg">Restablecer Contraseña</p>
            </div>
            <div class="card-body">
                <?php if (isset($_SESSION['mensaje'])): ?>
                    <div class="alert alert-<?= $_SESSION['icono'] === 'success' ? 'success' : 'danger' ?>">
                        <?= $_SESSION['mensaje'] ?>
                    </div>
                    <?php unset($_SESSION['mensaje']); unset($_SESSION['icono']); ?>
                <?php endif; ?>
                
                <p>Hola <strong><?= htmlspecialchars($email) ?></strong>, crea tu nueva contraseña:</p>
                
                <form action="restablecer.php?token=<?= $token ?>&email=<?= urlencode($email) ?>" method="post" id="restablecerForm">
                    <div class="form-group">
                        <label for="password">Nueva Contraseña</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="password" name="password" placeholder="Nueva contraseña" required minlength="6">
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-lock"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirmar Contraseña</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirmar contraseña" required minlength="6">
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-lock"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-save mr-1"></i> Restablecer Contraseña
                            </button>
                        </div>
                    </div>
                </form>
                
                <div class="text-center mt-3">
                    <a href="login.php" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left mr-1"></i> Volver al Login
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Opción adicional: validación cliente para mejorar UX (no reemplaza la validación servidor)
        document.getElementById('restablecerForm').addEventListener('submit', function(e) {
            var p1 = document.getElementById('password').value;
            var p2 = document.getElementById('confirm_password').value;
            if (p1 !== p2) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Las contraseñas no coinciden',
                    text: 'Por favor, verifica que ambas contraseñas sean iguales.',
                    confirmButtonText: 'Aceptar'
                });
                return false;
            }
        });
    </script>

    <?php if ($mostrar_sweetalert_mismatch): ?>
    <script>
        // Si el servidor detectó que no coinciden, mostramos SweetAlert2 al recargar
        Swal.fire({
            icon: 'error',
            title: 'Las contraseñas no coinciden',
            text: 'Por favor, verifica que ambas contraseñas sean iguales.',
            confirmButtonText: 'Aceptar'
        });
    </script>
    <?php endif; ?>

</body>
</html>