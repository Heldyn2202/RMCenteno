<?php 
session_start();

// Verificar si ya está logueado
if(isset($_SESSION['login_id'])) {
    header("location:index.php?page=home");
    exit();
}

// Incluir configuración de la base de datos
include 'db_connect.php';

// Configuración del sistema
$system_name = "SIGED";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Login - <?php echo htmlspecialchars($system_name, ENT_QUOTES) ?></title>

     <!-- Font Awesome -->  
    <link rel="stylesheet" href="assets/plugins/fontawesome-free/css/all.min.css">  
    <!-- icheck bootstrap -->  
    <link rel="stylesheet" href="assets/plugins/icheck-bootstrap/icheck-bootstrap.min.css">  
    <!-- Theme style -->  
    <link rel="stylesheet" href="assets/dist/css/adminlte.min.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        .login-page {
            background-image: url('img/fondo2.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            min-height: 100vh;
        }
        .login-card {
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            overflow: hidden;
        }
        .login-logo img {
            transition: transform 0.3s ease;
        }
        .login-logo img:hover {
            transform: scale(1.05);
        }
        .btn-login {
            transition: all 0.3s ease;
            letter-spacing: 0.5px;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .input-group-text {
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .input-group-text:hover {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body class="login-page">
<div class="container">
    <div class="row justify-content-center align-items-center min-vh-100">
        <div class="col-md-6 col-lg-4">
            <div class="login-card card">
                <div class="card-body">
                    <div class="text-center mb-4">
                        <figure class="login-logo">
                            <img src="img/avatar.png" alt="<?php echo htmlspecialchars($system_name, ENT_QUOTES) ?>" class="img-fluid" style="max-width: 100px;">
                        </figure>
                        <h3 class="mt-3"><b><?php echo htmlspecialchars($system_name, ENT_QUOTES) ?></b></h3>
                    </div>
                    
                    <hr class="my-4">

                    <form id="login-form" autocomplete="on">
                        <div class="input-group mb-3">
                            <input type="email" name="email" class="form-control" placeholder="Correo electrónico" required autocomplete="off" autocapitalize="off" inputmode="email">
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-envelope"></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="input-group mb-3">
                            <input type="password" id="password" name="password" class="form-control" placeholder="Contraseña" required minlength="6" autocomplete="current-password">
                            <div class="input-group-append">
                                <div class="input-group-text" id="togglePassword" title="Mostrar/Ocultar contraseña">
                                    <span class="fas fa-eye" id="eyeIcon"></span>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-7 d-flex align-items-center">
                                <div class="icheck-primary">
                                    <input type="checkbox" name="remember" id="remember">
                                    <label for="remember" title="Mantenerme autenticado">
                                        Recordarme
                                    </label>
                                </div>
                            </div>
                            <div class="col-5">
                                <button type="submit" class="btn btn-primary btn-block btn-login">
                                    <span class="fas fa-sign-in-alt"></span>
                                    Acceder
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function() {
    // Toggle password visibility
    $('#togglePassword').click(function() {
        const passwordInput = $('#password');
        const eyeIcon = $('#eyeIcon');
        
        if (passwordInput.attr('type') === 'password') {
            passwordInput.attr('type', 'text');
            eyeIcon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            passwordInput.attr('type', 'password');
            eyeIcon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });
    
    // Form submission handler
    $('#login-form').submit(function(e) {
        e.preventDefault();
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        // Mostrar estado de carga
        submitBtn.prop('disabled', true);
        submitBtn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Procesando...');
        
        // Limpiar mensajes de error previos
        $('.alert-danger').remove();
        
        // Enviar datos via AJAX
        $.ajax({
            url: 'ajax.php?action=login',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Redirección exitosa
                    window.location.href = 'index.php?page=home';
                } else {
                    // Mostrar error
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message,
                        confirmButtonText: 'Aceptar'
                    });
                    
                    // Restaurar botón
                    submitBtn.prop('disabled', false);
                    submitBtn.html(originalText);
                    
                    // Efecto de shake en el formulario
                    $('.login-card').addClass('animate__animated animate__shakeX');
                    setTimeout(function() {
                        $('.login-card').removeClass('animate__animated animate__shakeX');
                    }, 1000);
                }
            },
            error: function(xhr, status, error) {
                // Mostrar error de conexión
                Swal.fire({
                    icon: 'error',
                    title: 'Error de conexión',
                    text: 'Por favor, intente nuevamente.',
                    confirmButtonText: 'Aceptar'
                });
                
                // Restaurar botón
                submitBtn.prop('disabled', false);
                submitBtn.html(originalText);
                
                console.error(error);
            }
        });
    });
});
</script>

</body>
</html>