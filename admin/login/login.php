<?php

include('../app/config.php');
session_start();

// Prevenir caching de la página de login
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Verificar si la imagen de fondo existe
$fondo_path = __DIR__ . '/../img/fondo3.png';
$fondo_url = APP_URL . '/img/fondo3.png';
$fondo_existe = file_exists($fondo_path);

// Si no existe, intentar con fondo2.jpg como respaldo
if (!$fondo_existe) {
    $fondo_path = __DIR__ . '/../img/fondo2.png';
    $fondo_url = APP_URL . '/img/fondo2.png';
    $fondo_existe = file_exists($fondo_path);
}

// Si ninguna imagen existe, usar un color de fondo sólido
$estilo_fondo = $fondo_existe 
    ? "background-image: url('" . htmlspecialchars($fondo_url, ENT_QUOTES) . "');" 
    : "background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);";

// Procesar recuperación de contraseña si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['recovery_method'])) {
    $email = trim($_POST['email'] ?? '');
    $recovery_method = $_POST['recovery_method'];
    
    // Validar que los campos requeridos estén presentes
    if (empty($email) || empty($recovery_method)) {
        $_SESSION['mensaje'] = "Todos los campos son requeridos";
        $_SESSION['icono'] = "error";
        header('Location: login.php');
        exit;
    }

    // Validar método de recuperación
    if (!in_array($recovery_method, ['email', 'security'])) {
        $_SESSION['mensaje'] = "Método de recuperación inválido";
        $_SESSION['icono'] = "error";
        header('Location: login.php');
        exit;
    }
    
    // VALIDACIÓN: Verificar que el email no esté vacío
    if (empty($email)) {
        $_SESSION['mensaje'] = "Por favor ingresa tu correo electrónico";
        $_SESSION['icono'] = "error";
        header('Location: login.php');
        exit;
    }
    
    // Validar formato de email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['mensaje'] = "Por favor ingresa un correo electrónico válido";
        $_SESSION['icono'] = "error";
        header('Location: login.php');
        exit;
    }
    
    // Buscar usuario por email
    $sql = "SELECT * FROM usuarios WHERE email = :email AND estado = 1";
    $query = $pdo->prepare($sql);
    $query->bindParam(':email', $email);
    $query->execute();
    $usuario = $query->fetch(PDO::FETCH_ASSOC);
    
    if ($usuario) {
        if ($recovery_method === 'email') {
            // Generar token de recuperación
            $token = bin2hex(random_bytes(32));
            $expiracion = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Guardar token en la base de datos
            $sql_token = "UPDATE usuarios SET token_recuperacion = :token, expiracion_token = :expiracion WHERE id_usuario = :id";
            $query_token = $pdo->prepare($sql_token);
            $query_token->bindParam(':token', $token);
            $query_token->bindParam(':expiracion', $expiracion);
            $query_token->bindParam(':id', $usuario['id_usuario']);
            
            if ($query_token->execute()) {
                // Crear enlace de recuperación
                $enlace_recuperacion = APP_URL . "/login/restablecer.php?token=$token&email=" . urlencode($email);
                
                // ENVÍO REAL DE EMAIL
                $asunto = "Recuperación de Contraseña - " . APP_NAME;
                
                // Verificar si la función crearTemplateRecuperacion existe, si no, crear un template básico
                if (function_exists('crearTemplateRecuperacion')) {
                    $cuerpo = crearTemplateRecuperacion($enlace_recuperacion, $email);
                } else {
                    // Template básico si la función no existe
                    $cuerpo = "
                    <html>
                    <head>
                        <style>
                            body { font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px; }
                            .container { background: white; padding: 20px; border-radius: 5px; max-width: 600px; margin: 0 auto; }
                            .button { background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; }
                        </style>
                    </head>
                    <body>
                        <div class='container'>
                            <h2>Recuperación de Contraseña</h2>
                            <p>Hola,</p>
                            <p>Has solicitado restablecer tu contraseña. Haz clic en el siguiente enlace para crear una nueva contraseña:</p>
                            <p><a href='$enlace_recuperacion' class='button'>Restablecer Contraseña</a></p>
                            <p>Si no solicitaste este cambio, puedes ignorar este mensaje.</p>
                            <p>Este enlace expirará en 1 hora.</p>
                        </div>
                    </body>
                    </html>
                    ";
                }
                
                // Verificar si la función enviarEmail existe
                if (function_exists('enviarEmail')) {
                    $email_enviado = enviarEmail($email, $asunto, $cuerpo);
                } else {
                    // Simular envío si la función no existe (para desarrollo)
                    $email_enviado = true;
                    // En producción, deberías implementar la función enviarEmail
                }
                
                if ($email_enviado) {
                    $_SESSION['mensaje'] = "✅ <strong>¡Correo enviado exitosamente!</strong><br><br>"
                                         . "Se ha enviado un enlace de recuperación a:<br>"
                                         . "<span style='color: #138496; font-weight: bold; background: #f8f9fa; padding: 5px 10px; border-radius: 5px;'>" 
                                         . htmlspecialchars($email) 
                                         . "</span><br><br>"
                                         . "• Revisa tu bandeja de entrada<br>"
                                         . "• Si no lo encuentras, revisa la carpeta de spam<br>"
                                         . "• El enlace expira en 1 hora";
                    $_SESSION['icono'] = "success";
                    $_SESSION['titulo'] = "✅ Correo enviado";
                    $_SESSION['email_enviado'] = $email; // Guardar email para JS
                } else {
                    $_SESSION['mensaje'] = "❌ <strong>Error al enviar el correo</strong><br><br>"
                                         . "No se pudo enviar el email a:<br>"
                                         . "<span style='color: #dc3545; font-weight: bold;'>" 
                                         . htmlspecialchars($email) 
                                         . "</span><br><br>"
                                         . "Por favor contacta al administrador del sistema.";
                    $_SESSION['icono'] = "error";
                    $_SESSION['titulo'] = "❌ Error de envío";
                }
            } else {
                $_SESSION['mensaje'] = "Error al generar el enlace de recuperación.";
                $_SESSION['icono'] = "error";
            }
            
        } elseif ($recovery_method === 'security') {
            // Verificar si el usuario tiene preguntas de seguridad configuradas
            $sql_preguntas = "SELECT * FROM preguntas_seguridad WHERE usuario_id = :usuario_id";
            $query_preguntas = $pdo->prepare($sql_preguntas);
            $query_preguntas->bindParam(':usuario_id', $usuario['id_usuario']);
            $query_preguntas->execute();
            $preguntas = $query_preguntas->fetch(PDO::FETCH_ASSOC);
            
            if ($preguntas) {
                // Guardar en sesión para verificar después
                $_SESSION['recovery_user_id'] = $usuario['id_usuario'];
                $_SESSION['recovery_method'] = 'security';
                $_SESSION['pregunta1'] = $preguntas['pregunta1'];
                $_SESSION['pregunta2'] = $preguntas['pregunta2'];
                $_SESSION['recovery_email'] = $email;
                
                // Redirigir a verificación de preguntas
                header('Location: verificar_preguntas.php');
                exit;
            } else {
                $_SESSION['mensaje'] = "No tienes preguntas de seguridad configuradas. Usa el método de email.";
                $_SESSION['icono'] = "warning";
            }
        }
    } else {
        $_SESSION['mensaje'] = "No se encontró un usuario activo con ese email.";
        $_SESSION['icono'] = "error";
    }
    
    // Si la petición es AJAX (fetch), devolver solo mensaje para JS
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        // Respuesta JSON con estado y mensaje
        header('Content-Type: application/json');
        echo json_encode([
            'icon' => $_SESSION['icono'] ?? 'info',
            'title' => $_SESSION['titulo'] ?? '',
            'message' => $_SESSION['mensaje'] ?? '',
            'email' => $_SESSION['email_enviado'] ?? ''
        ]);
        // Limpiar mensajes para evitar mostrar en recarga
        unset($_SESSION['mensaje'], $_SESSION['icono'], $_SESSION['titulo'], $_SESSION['email_enviado']);
        exit;
    }

    header('Location: login.php');
    exit;
}

// ====================================================================
// NUEVO: VERIFICAR SI ES DOCENTE DESPUÉS DEL LOGIN EXITOSO
// ====================================================================
// Esta función se llamará desde controller_login.php después de validar credenciales
function verificarYRedirigirDocente($email, $rol_id) {
    global $pdo;
    
    // Si es docente (rol_id = 5), buscar información adicional
    if ($rol_id == 5) {
        $sql_profesor = "SELECT p.* FROM profesores p WHERE p.email = ? AND p.estado = 1";
        $query_profesor = $pdo->prepare($sql_profesor);
        $query_profesor->execute([$email]);
        $profesor = $query_profesor->fetch(PDO::FETCH_ASSOC);
        
        if ($profesor) {
            // Guardar datos específicos del docente en sesión
            $_SESSION['id_profesor'] = $profesor['id_profesor'];
            $_SESSION['nombre_profesor'] = $profesor['nombres'] . ' ' . $profesor['apellidos'];
            $_SESSION['es_docente'] = true; // Cambiado a es_docente
            $_SESSION['especialidad'] = $profesor['especialidad'];
            
            // Redirigir al módulo de notas
            header('Location: ' . APP_URL . '/admin/notas/carga_notas_seccion.php');
            exit();
        }
    }
    
    // Si no es docente, continuar con el flujo normal
    return false;
}

// Verificar si ya está logueado
if (isset($_SESSION['login_id'])) {
    // Si es profesor y está logueado, redirigir al módulo de notas
    if (isset($_SESSION['es_profesor']) && $_SESSION['es_profesor'] && $_SESSION['rol_sesion'] == 5) {
        header('Location: ' . APP_URL . '/admin/notas/carga_notas_seccion.php');
        exit();
    }
    // Si no es profesor, redirigir al home normal
    header("location:index.php?page=home");
    exit;
}
?>  
<!DOCTYPE html>  
<html lang="es">  
<head>  
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Login - <?=htmlspecialchars(APP_NAME, ENT_QUOTES)?></title>
    
    <!-- Google Font: Source Sans Pro -->  
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">  
    <!-- Font Awesome -->  
    <link rel="stylesheet" href="<?=htmlspecialchars(APP_URL, ENT_QUOTES)?>/public/plugins/fontawesome-free/css/all.min.css">  
    <!-- icheck bootstrap -->  
    <link rel="stylesheet" href="<?=htmlspecialchars(APP_URL, ENT_QUOTES)?>/public/plugins/icheck-bootstrap/icheck-bootstrap.min.css">  
    <!-- Theme style -->  
    <link rel="stylesheet" href="<?=htmlspecialchars(APP_URL, ENT_QUOTES)?>/public/dist/css/adminlte.min.css">  
    <!-- Sweetalert2 -->  
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>  
    
    <style>
        .page-loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.08);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            transition: opacity 0.5s ease-out, visibility 0.5s;
        }
        
        .page-loader.hidden {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
        }
        
        .loader-content {
            text-align: center;
            background: white;
            padding: 20px 25px; 
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            border: 1px solid #e0e0e0;
            width: 280px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        
        .loader-header {
            margin-bottom: 15px;
            width: 100%;
        }
        
        .loader-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 5px;
            letter-spacing: 0.5px;
        }
        
        .loader-subtitle {
            font-size: 0.9rem;
            color: #7f8c8d;
            margin-bottom: 12px;
        }
        
        .logo-container {
            width: 80px;
            height: 80px;
            margin: 0 auto 12px;
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .circular-loader {
            position: absolute;
            width: 100%;
            height: 100%;
            border: 5px solid rgba(0, 86, 179, 0.2);
            border-top: 5px solid #0056b3;
            border-radius: 50%;
            animation: spin 1.2s linear infinite;
            box-sizing: border-box;
        }
        
        .logo-container img {
            max-width: 65%;
            max-height: 65%;
            z-index: 1;
        }
        
        .loader-text {
            color: #2c3e50;
            font-weight: 500;
            margin-top: 12px;
            font-size: 0.9rem;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        :root {
            --primary-color: #17a2b8;
            --primary-dark: #138496;
            --primary-light: #1abc9c;
            --primary-gradient: linear-gradient(135deg, #17a2b8 0%, #1abc9c 100%);
            --text-dark: #2c3e50;
            --text-light: #7f8c8d;
            --card-shadow: 0 10px 30px rgba(23, 162, 184, 0.15);
            --card-hover-shadow: 0 15px 35px rgba(23, 162, 184, 0.25);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Montserrat', 'Segoe UI', Arial, sans-serif;
        }
        
        html {
            height: 100%;
            margin: 0;
            padding: 0;
        }
        
        body {
            height: 100vh;
            max-width: 100%;
            <?= $estilo_fondo ?>
            background-size: cover;
            background-repeat: no-repeat;
            display: flex;
            align-items: center;
            justify-content: flex-start;
            padding-left: 0;
            position: relative;
        }
        
        .login-container {
            margin-left: 200px;
            margin-top: 50px;
            width: 400px;
            animation: fadeIn 0.8s ease;
            position: relative;
            z-index: 1;
        }
        
        .login-box {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--card-shadow);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .login-box:hover {
            box-shadow: var(--card-hover-shadow);
        }
        
        .login-header {
            background: var(--primary-gradient);
            color: white;
            padding: 20px;
            text-align: center;
            font-weight: bold;
            font-size: 1.2em;
            position: relative;
        }
        
        .login-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            border-radius: 2px;
            background: rgba(255, 255, 255, 0.7);
        }
        
        .login-body {
            padding: 25px;
        }
        
        .login-logo {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .login-logo img {
            max-width: 150px;
            height: auto;
            transition: transform 0.3s ease;
        }
        
        .login-logo img:hover {
            transform: scale(1.05);
        }
        
        .login-box-msg {
            text-align: center;
            color: var(--text-dark);
            margin-bottom: 20px;
            font-weight: 500;
            position: relative;
            padding-bottom: 15px;
        }
        
        .login-box-msg::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 260px;
            height: 2px;
            background: var(--primary-color);
        }
        
        hr {
            margin: 15px 0;
            border: 0;
            height: 1px;
            background: #e1e5eb;
        }
        
        .input-group {
            position: relative;
            margin-bottom: 20px;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #e1e5eb;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(23, 162, 184, 0.2);
            background: white;
        }
        
        .row {
            display: flex;
            flex-wrap: wrap;
            margin-right: -5px;
            margin-left: -5px;
        }
        
        .col-7, .col-5 {
            position: relative;
            width: 100%;
            padding-right: 5px;
            padding-left: 5px;
        }
        
        .col-7 {
            flex: 0 0 58.333333%;
            max-width: 58.333333%;
        }
        
        .col-5 {
            flex: 0 0 41.666667%;
            max-width: 41.666667%;
        }
        
        .icheck-primary {
            display: flex;
            align-items: center;
            height: 100%;
        }
        
        .icheck-primary input[type="checkbox"] {
            margin-right: 8px;
            width: 18px;
            height: 18px;
            accent-color: var(--primary-color);
        }
        
        .icheck-primary label {
            color: var(--text-dark);
            font-size: 0.95rem;
            cursor: pointer;
            margin: 0;
        }
        
        .btn {
            display: inline-block;
            font-weight: 400;
            text-align: center;
            vertical-align: middle;
            cursor: pointer;
            border: 1px solid transparent;
            padding: 0.375rem 0.75rem;
            font-size: 1rem;
            line-height: 1.5;
            border-radius: 0.25rem;
            transition: all 0.15s ease;
        }
        
        .btn-block {
            display: block;
            width: 100%;
        }
        
        .btn-primary {
            color: white;
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .input-group-text {
            cursor: pointer;
            transition: background-color 0.3s ease;
            background-color: #e9ecef;
            border: 1px solid #ced4da;
            padding: 0.375rem 0.75rem;
        }
        
        .input-group-text:hover {
            background-color: #f8f9fa;
        }
        
        .btn-forgot {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s ease;
        }
        
        .btn-forgot:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }
        
        /* Animaciones */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(0.98); }
            100% { transform: scale(1); }
        }
        
        /* Modal styles */
        .modal-content {
            border-radius: 15px;
            overflow: hidden;
        }
        
        .modal-header {
            background: var(--primary-gradient);
            color: white;
        }
        
        .recovery-option {
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .recovery-option:hover {
            border-color: var(--primary-color);
            box-shadow: 0 0 10px rgba(23, 162, 184, 0.2);
            transform: translateY(-2px);
        }
        
        .recovery-option.active {
            border-color: var(--primary-color);
            background-color: rgba(23, 162, 184, 0.1);
        }
        
        .recovery-form {
            transition: all 0.3s ease;
        }
        
        .custom-swal-popup {
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }
        
        .swal2-popup .swal2-title {
            color: var(--primary-color);
            font-weight: bold;
        }
        
        .swal2-popup .swal2-html-container strong {
            color: var(--primary-dark);
        }
        
        /* Estilos específicos para móviles */
        @media (max-width: 768px) {
            body {
                <?= $estilo_fondo ?>
                background-size: cover;
                background-position: center;
                justify-content: center;
                padding: 20px;
                align-items: center;
                height: 100vh;
            }
            
            .login-container {
                margin-left: 0;
                margin-top: 0;
                width: 100%;
                max-width: 400px;
                padding: 15px;
            }
            
            .login-box {
                box-shadow: 0 5px 15px rgba(0,0,0,0.3);
                width: 100%;
            }
            
            .login-header {
                padding: 15px;
                font-size: 1.1em;
            }
            
            .login-body {
                padding: 20px;
            }
            
            .login-logo img {
                max-width: 120px;
            }
            
            .form-control {
                padding: 10px 12px;
            }
            
            .login-box-msg {
                font-size: 0.8em;
            }
            
            /* Ajustes para el loader en tabletas */
            .loader-content {
                width: 260px; 
                padding: 18px 22px; 
            }
        }
        
        /* Estilos para pantallas muy pequeñas */
        @media (max-width: 480px) {
            .login-container {
                margin-top: 0;
                padding: 10px;
            }
            
            .login-header {
                font-size: 1em;
                padding: 12px;
            }
            
            .login-body {
                padding: 15px;
            }
            
            .login-logo img {
                max-width: 100px;
            }
            
            .form-control {
                padding: 8px 10px;
                font-size: 0.9em;
            }
            
            .login-box-msg {
                font-size: 0.8em;
            }
            
            .icheck-primary label {
                font-size: 0.8em;
            }
            
            .col-7, .col-5 {
                flex: 0 0 100%;
                max-width: 100%;
                margin-bottom: 10px;
            }
            
            .loader-content {
                width: 250px;
                padding: 18px;
            }
            
            .logo-container {
                width: 70px;
                height: 70px;
            }
            
            .loader-text {
                font-size: 0.85rem;
            }
        }
    </style>
</head>  
<body>
<!-- Page Loader para carga inicial -->
<div class="page-loader" id="pageLoaderInit">
    <div class="loader-content">
        <div class="logo-container">
            <div class="circular-loader"></div>
            <img src="<?=htmlspecialchars(APP_URL, ENT_QUOTES)?>/img/avatar.png" alt="<?=htmlspecialchars(APP_NAME, ENT_QUOTES)?> Logo">
        </div>
        <div class="loader-text">Cargando... Espere un momento, por favor.</div>
    </div>
</div>

<!-- Page Loader para login (Accediendo...) -->
<div class="page-loader hidden" id="pageLoaderLogin">
    <div class="loader-content">
        <div class="logo-container">
            <div class="circular-loader"></div>
            <img src="<?=htmlspecialchars(APP_URL, ENT_QUOTES)?>/img/avatar.png" alt="<?=htmlspecialchars(APP_NAME, ENT_QUOTES)?> Logo">
        </div>
        <div class="loader-text">Accediendo... Espere un momento, por favor.</div>
    </div>
</div>

<div class="login-container">
    <div class="login-box">
        <div class="login-header">
            AUTENTICACIÓN DE USUARIO
        </div>
        <div class="login-body">
            <div class="login-logo">
                <img src="<?=htmlspecialchars(APP_URL, ENT_QUOTES)?>/img/logo.png" alt="<?=htmlspecialchars(APP_NAME, ENT_QUOTES)?>" style="height: 90px; width: auto;">
            </div>
            <hr class="my-4">
            <form action="controller_login.php" method="post" id="loginForm" autocomplete="on">  

                <div class="input-group">  
                    <input type="email" name="email" class="form-control" placeholder="Correo electrónico"  autocapitalize="off" inputmode="email">
                    <div class="input-group-append">  
                        <div class="input-group-text">  
                            <span class="fas fa-envelope"></span>  
                        </div>  
                    </div>  
                </div>  
                
                <div class="input-group">  
                    <input type="password" id="password" name="password" class="form-control" placeholder="Contraseña" required minlength="6" autocomplete="current-password">
                    <div class="input-group-append">  
                        <div class="input-group-text" id="togglePassword" title="Mostrar/Ocultar contraseña">  
                            <span class="fas fa-eye" id="eyeIcon"></span>  
                        </div>  
                    </div>  
                </div>  

                <div class="row">
                    <div class="col-7 d-flex align-items-center">
                        <div class="icheck-primary" title="Mantenerme autenticado indefinidamente o hasta cerrar la sesión manualmente">
                            <input type="checkbox" name="remember" id="remember">
                            <label for="remember">
                                Recordarme
                            </label>
                        </div>
                    </div>
                    <div class="col-5">
                        <button type="submit" class="btn btn-primary btn-block">
                            <span class="fas fa-sign-in-alt"></span>
                            Acceder
                        </button>
                    </div>
                </div>
                
                <div class="text-center mt-3">
                    <a href="#" class="btn-forgot" data-toggle="modal" data-target="#recoveryModal">
                        <i class="fas fa-key mr-1"></i> ¿Olvidaste tu contraseña?
                    </a>
                </div>
            </form>  

        </div>  
    </div>  
</div>  

<!-- Modal para recuperación de contraseña -->
<div class="modal fade" id="recoveryModal" tabindex="-1" role="dialog" aria-labelledby="recoveryModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="recoveryModalLabel">Recuperar Contraseña</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true" style="color: white;">&times;</span>
                </button>
            </div>
            <form action="login.php" method="post" id="recoveryForm">
                <div class="modal-body">
                    <p class="text-center">Selecciona un método para recuperar tu contraseña:</p>
                    
                    <div class="recovery-option" data-method="email">
                        <div class="text-center">
                            <i class="fas fa-envelope fa-2x mb-2 text-primary"></i>
                            <h5>Recuperar por correo electrónico</h5>
                            <p class="small">Te enviaremos un enlace para restablecer tu contraseña</p>
                        </div>
                    </div>
                    
                    <div class="recovery-option" data-method="security">
                        <div class="text-center">
                            <i class="fas fa-shield-alt fa-2x mb-2 text-primary"></i>
                            <h5>Recuperar por preguntas de seguridad</h5>
                            <p class="small">Responde tus preguntas de seguridad para restablecer tu contraseña</p>
                        </div>
                    </div>
                    
                    <div id="emailRecoveryForm" class="recovery-form d-none mt-3">
                        <hr>
                        <div class="form-group">
                            <label for="recoveryEmail">Correo electrónico registrado</label>
                            <input type="email" class="form-control" id="recoveryEmail" name="email" required>
                        </div>
                    </div>
                    
                    <div id="securityRecoveryForm" class="recovery-form d-none mt-3">
                        <hr>
                        <div class="form-group">
                            <label for="securityEmail">Correo electrónico</label>
                            <input type="email" class="form-control" id="securityEmail" name="email" required>
                        </div>
                    </div>
                    
                    <input type="hidden" name="recovery_method" id="recoveryMethod" value="">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="recoverySubmitBtn" disabled>Continuar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- jQuery -->
<script src="<?=htmlspecialchars(APP_URL, ENT_QUOTES)?>/public/plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="<?=htmlspecialchars(APP_URL, ENT_QUOTES)?>/public/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="<?=htmlspecialchars(APP_URL, ENT_QUOTES)?>/public/dist/js/adminlte.min.js"></script>

<script>  
// Control del page loader inicial
document.addEventListener('DOMContentLoaded', function() {
    const pageLoaderInit = document.getElementById('pageLoaderInit');
    const minimumDisplayTime = 1000; // 1 segundo mínimo
    
    // Ocultar el loader después del tiempo mínimo
    setTimeout(function() {
        pageLoaderInit.classList.add('hidden');
    }, minimumDisplayTime);

    // Toggle password visibility
    const togglePassword = document.getElementById('togglePassword');  
    const passwordInput = document.getElementById('password');  
    const eyeIcon = document.getElementById('eyeIcon');  

    if (togglePassword && passwordInput && eyeIcon) {
        togglePassword.addEventListener('click', () => {  
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';  
            passwordInput.setAttribute('type', type);  
            eyeIcon.classList.toggle('fa-eye');  
            eyeIcon.classList.toggle('fa-eye-slash');  
        });
    }
    
    // Form submission handler
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Procesando...';
            
            // Mostrar el loader de login
            document.getElementById('pageLoaderLogin').classList.remove('hidden');
        });
    }

    // Recuperación de contraseña
    const recoveryOptions = document.querySelectorAll('.recovery-option');
    const emailRecoveryForm = document.getElementById('emailRecoveryForm');
    const securityRecoveryForm = document.getElementById('securityRecoveryForm');
    const recoveryMethodInput = document.getElementById('recoveryMethod');
    const recoverySubmitBtn = document.getElementById('recoverySubmitBtn');
    const recoveryForm = document.getElementById('recoveryForm');
    let selectedMethod = '';

    // Función para validar email
    function isValidEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }

    // Seleccionar método de recuperación
    recoveryOptions.forEach(option => {
        option.addEventListener('click', function() {
            recoveryOptions.forEach(opt => opt.classList.remove('active'));
            this.classList.add('active');
            selectedMethod = this.getAttribute('data-method');
            recoveryMethodInput.value = selectedMethod;
            
            emailRecoveryForm.classList.add('d-none');
            securityRecoveryForm.classList.add('d-none');
            
            if (selectedMethod === 'email') {
                emailRecoveryForm.classList.remove('d-none');
                setTimeout(() => {
                    document.getElementById('recoveryEmail').focus();
                }, 100);
            } else if (selectedMethod === 'security') {
                securityRecoveryForm.classList.remove('d-none');
                setTimeout(() => {
                    document.getElementById('securityEmail').focus();
                }, 100);
            }
            
            recoverySubmitBtn.disabled = false;
        });
    });

    // Enviar formulario recuperación
    recoverySubmitBtn.addEventListener('click', function(e) {
        e.preventDefault();
        
        if (!selectedMethod) {
            Swal.fire('Error', 'Por favor selecciona un método de recuperación', 'error');
            return;
        }
        
        let email, isValid = false;
        let emailField;
        
        if (selectedMethod === 'email') {
            emailField = document.getElementById('recoveryEmail');
            email = emailField.value.trim();
            isValid = email && isValidEmail(email);
        } else if (selectedMethod === 'security') {
            emailField = document.getElementById('securityEmail');
            email = emailField.value.trim();
            isValid = email && isValidEmail(email);
        }
        
        if (!isValid) {
            Swal.fire('Error', 'Por favor ingresa un correo electrónico válido', 'error');
            if(emailField) emailField.focus();
            return;
        }
        
        recoverySubmitBtn.disabled = true;
        recoverySubmitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Procesando...';
        
        const formData = new FormData();
        formData.append('email', email);
        formData.append('recovery_method', selectedMethod);
        
        fetch('login.php', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Error en la respuesta del servidor');
            }
            return response.json();
        })
        .then(data => {
            if (data.icon === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: data.title || 'Correo enviado',
                    html: data.message || `Se ha enviado un enlace de recuperación a:<br><strong style="color:#138496;">${email}</strong><br><br>
                           - Revisa tu bandeja de entrada<br>
                           - Si no lo encuentras, revisa la carpeta de spam<br>
                           - El enlace expira en 1 hora`,
                    confirmButtonText: 'Aceptar',
                    allowOutsideClick: false,
                    customClass: {
                        popup: 'custom-swal-popup'
                    }
                }).then(() => {
                    $('#recoveryModal').modal('hide');
                    recoveryForm.reset();
                    recoverySubmitBtn.disabled = true;
                    recoverySubmitBtn.innerHTML = 'Continuar';
                    recoveryOptions.forEach(opt => opt.classList.remove('active'));
                    emailRecoveryForm.classList.add('d-none');
                    securityRecoveryForm.classList.add('d-none');
                    recoveryMethodInput.value = '';
                    selectedMethod = '';
                });
            } else {
                Swal.fire({
                    icon: data.icon || 'error',
                    title: data.title || 'Error',
                    html: data.message || 'Ocurrió un error al procesar la solicitud',
                    confirmButtonText: 'Aceptar',
                    allowOutsideClick: false,
                    customClass: {
                        popup: 'custom-swal-popup'
                    }
                });
                recoverySubmitBtn.disabled = false;
                recoverySubmitBtn.innerHTML = 'Continuar';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error de conexión',
                html: 'No se pudo conectar con el servidor. Por favor intenta nuevamente.',
                confirmButtonText: 'Aceptar',
                customClass: {
                    popup: 'custom-swal-popup'
                }
            });
            recoverySubmitBtn.disabled = false;
            recoverySubmitBtn.innerHTML = 'Continuar';
        });
    });

    // Resetear formulario al cerrar modal
    $('#recoveryModal').on('hidden.bs.modal', function () {
        recoveryOptions.forEach(opt => opt.classList.remove('active'));
        emailRecoveryForm.classList.add('d-none');
        securityRecoveryForm.classList.add('d-none');
        recoverySubmitBtn.disabled = true;
        recoverySubmitBtn.innerHTML = 'Continuar';
        recoveryMethodInput.value = '';
        recoveryForm.reset();
        selectedMethod = '';
    });

    // Mostrar mensajes de sesión (no AJAX)
    <?php if (isset($_SESSION['mensaje'])): ?>  
        Swal.fire({  
            icon: '<?= isset($_SESSION['icono']) ? addslashes($_SESSION['icono']) : 'info' ?>',  
            title: '<?= isset($_SESSION['titulo']) ? addslashes($_SESSION['titulo']) : (isset($_SESSION['icono']) && $_SESSION['icono'] === 'success' ? 'Éxito' : 'Error') ?>',  
            html: '<?= addslashes($_SESSION['mensaje']) ?>',
            confirmButtonText: 'Aceptar',
            allowOutsideClick: false,
            customClass: {
                popup: 'custom-swal-popup'
            }
        });  
        <?php   
            unset($_SESSION['mensaje']);
            unset($_SESSION['icono']);
            unset($_SESSION['titulo']);
        ?>  
    <?php endif; ?>  
});

// Control para navegación con caché
window.addEventListener('pageshow', function(event) {
    if (event.persisted) {
        document.getElementById('pageLoaderInit').classList.add('hidden');
        document.getElementById('pageLoaderLogin').classList.add('hidden');
    }
});

// Asegurarse de que los loaders estén ocultos si la página se carga desde caché
if (window.performance && window.performance.navigation.type === 2) {
    document.getElementById('pageLoaderInit').classList.add('hidden');
    document.getElementById('pageLoaderLogin').classList.add('hidden');
}
</script>  

</body>  
</html>