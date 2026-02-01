<?php
// portal-cms/login-temp.php - Login temporal para testing
session_start();

// Si ya está logueado, redirigir al dashboard
if (isset($_SESSION['admin_login'])) {
    header('Location: index.php');
    exit;
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario = $_POST['usuario'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Credenciales temporales (cambia esto después)
    if ($usuario === 'admin' && $password === 'admin123') {
        $_SESSION['admin_login'] = true;
        $_SESSION['usuario'] = 'Administrador';
        $_SESSION['role'] = 'administrador';
        $_SESSION['login_time'] = time();
        
        // Redirigir al dashboard
        header('Location: index.php');
        exit;
    } else {
        $error = "Credenciales incorrectas";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Temporal - CMS Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1a4b8c, #2d68c4);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 400px;
        }
        
        .login-header {
            background: linear-gradient(135deg, #1a4b8c, #2d68c4);
            color: white;
            padding: 20px;
            border-radius: 15px 15px 0 0;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <h4><i class="fas fa-lock"></i> Acceso Temporal CMS</h4>
            <small>Panel de Control del Portal Escolar</small>
        </div>
        
        <div class="card-body p-4">
            <?php if(isset($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="mb-3">
                    <label class="form-label">Usuario</label>
                    <input type="text" name="usuario" class="form-control" value="admin" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Contraseña</label>
                    <input type="password" name="password" class="form-control" value="admin123" required>
                </div>
                
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                    </button>
                </div>
            </form>
            
            <hr class="my-4">
            
            <div class="text-center">
                <small class="text-muted">
                    <strong>Credenciales de prueba:</strong><br>
                    Usuario: <code>admin</code><br>
                    Contraseña: <code>admin123</code>
                </small>
                
                <div class="mt-3">
                    <a href="../../../admin/login/login.php" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-external-link-alt"></i> Ir al login oficial
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>