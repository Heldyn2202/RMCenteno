<?php
session_start();
include('includes/config.php');

// Procesar el formulario de contacto si se envió
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['contacto'])) {
    $nombre = mysqli_real_escape_string($con, $_POST['nombre']);
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $telefono = mysqli_real_escape_string($con, $_POST['telefono']);
    $asunto = mysqli_real_escape_string($con, $_POST['asunto']);
    $comentarios = mysqli_real_escape_string($con, $_POST['comentarios']);
    
    $sql = "INSERT INTO contactos (nombre, email, telefono, asunto, comentarios, fecha_creacion) 
            VALUES ('$nombre', '$email', '$telefono', '$asunto', '$comentarios', NOW())";
    
    if (mysqli_query($con, $sql)) {
        $mensaje_exito = "¡Gracias por contactarnos! Nos comunicaremos con usted a la brevedad.";
    } else {
        $mensaje_error = "Error al enviar el mensaje. Por favor, intente nuevamente.";
    }
}

// Obtener redes sociales desde la base de datos
$socialQuery = "SELECT * FROM social_media WHERE status = 1 ORDER BY name";
$socialResult = mysqli_query($con, $socialQuery);
$social_media = [];
if ($socialResult) {
    $social_media = mysqli_fetch_all($socialResult, MYSQLI_ASSOC);
} else {
    $social_media = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
    <title>Contacto | Portal Escolar</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #1a4b8c;
            --secondary-color: #2d68c4;
            --accent-color: #1a4b8c;
            --light-bg: #f8f9fa;
            --dark-bg: #1a2238;
            --text-color: #2c3e50;
            --text-light: #546e7a;
            --border-color: #e0e0e0;
            --white: #fff;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Figtree', sans-serif;
            color: var(--text-color);
            background-color: var(--white);
            line-height: 1.6;
        }
        
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Figtree', sans-serif;
            font-weight: 600;
            color: var(--text-color);
        }

        a {
            color: var(--text-color);
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        a:hover {
            color: var(--accent-color);
        }

        /* Header Styles - TEMA BLANCO */
        .site-header {
            background-color: var(--white);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .header-main {
            background-color: var(--white);
            padding: 10px 0;
            border-bottom: 1px solid var(--border-color);
        }
        
        .hm-header-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px 0;
        }
        
        .site-branding {
            flex: 1;
        }
        
        .site-title {
            font-size: 28px;
            font-weight: 700;
            margin: 0;
        }
        
        .site-title a {
            color: var(--primary-color);
        }
        
        .site-title a:hover {
            color: var(--secondary-color);
        }
        
        .main-navigation {
            flex: 3;
            display: flex;
            justify-content: center;
        }
        
        .main-navigation ul {
            display: flex;
            list-style: none;
            margin: 0;
            padding: 0;
        }
        
        .main-navigation li {
            margin: 0 15px;
        }
        
        .main-navigation a {
            color: var(--text-color);
            font-weight: 500;
            padding: 5px 0;
            position: relative;
            font-size: 15px;
        }
        
        .main-navigation a:hover:after,
        .main-navigation .current-menu-item a:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 2px;
            background-color: var(--primary-color);
        }
        
        .hm-header-gadgets {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 15px;
        }
        
        .hm-social-menu {
            display: flex;
            gap: 10px;
        }
        
        .hm-social-menu a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: var(--light-bg);
            color: var(--text-color);
            border: 1px solid var(--border-color);
        }
        
        .hm-social-menu a:hover {
            background-color: var(--primary-color);
            color: var(--white);
            border-color: var(--primary-color);
        }
        
        .hm-cta-btn {
            background-color: var(--primary-color);
            color: var(--white);
            padding: 8px 20px;
            border-radius: 4px;
            font-weight: 500;
        }
        
        .hm-cta-btn:hover {
            background-color: var(--secondary-color);
            color: var(--white);
        }
        
        /* ===========================================
           BARRA DE NAVEGACIÓN RESPONSIVE
           =========================================== */
        
        /* Contenedor de navegación móvil */
        .mobile-nav-container {
            display: none;
        }
        
        /* Botón hamburguesa */
        .hamburger-menu {
            display: none;
            flex-direction: column;
            justify-content: space-between;
            width: 30px;
            height: 21px;
            background: transparent;
            border: none;
            cursor: pointer;
            padding: 0;
            z-index: 1001;
        }
        
        .hamburger-menu span {
            display: block;
            height: 3px;
            width: 100%;
            background-color: var(--text-color);
            border-radius: 3px;
            transition: all 0.3s ease;
        }
        
        /* Menú móvil activo */
        .hamburger-menu.active span:nth-child(1) {
            transform: translateY(9px) rotate(45deg);
        }
        
        .hamburger-menu.active span:nth-child(2) {
            opacity: 0;
        }
        
        .hamburger-menu.active span:nth-child(3) {
            transform: translateY(-9px) rotate(-45deg);
        }
        
        /* Overlay del menú móvil */
        .mobile-menu-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 998;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        
        .mobile-menu-overlay.active {
            opacity: 1;
            visibility: visible;
        }
        
        /* Menú móvil */
        .mobile-menu {
            position: fixed;
            top: 0;
            right: -100%;
            width: 280px;
            height: 100%;
            background-color: var(--white);
            z-index: 999;
            transition: right 0.3s ease;
            overflow-y: auto;
            box-shadow: -2px 0 10px rgba(0, 0, 0, 0.1);
        }
        
        .mobile-menu.active {
            right: 0;
        }
        
        /* Encabezado del menú móvil */
        .mobile-menu-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            border-bottom: 1px solid var(--border-color);
            background-color: var(--white);
        }
        
        .mobile-menu-logo {
            font-size: 20px;
            font-weight: 700;
            color: var(--text-color);
        }
        
        .mobile-menu-close {
            background: none;
            border: none;
            font-size: 24px;
            color: var(--text-color);
            cursor: pointer;
        }
        
        /* Navegación móvil */
        .mobile-nav {
            padding: 20px;
        }
        
        .mobile-nav ul {
            list-style: none;
        }
        
        .mobile-nav li {
            margin-bottom: 10px;
        }
        
        .mobile-nav a {
            display: block;
            padding: 12px 0;
            font-weight: 500;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-color);
        }
        
        .mobile-nav a:hover,
        .mobile-nav .current-menu-item a {
            color: var(--accent-color);
        }
        
        .mobile-nav .current-menu-item a {
            font-weight: 600;
        }
        
        /* Gadgets móviles */
        .mobile-gadgets {
            padding: 20px;
            border-top: 1px solid var(--border-color);
        }
        
        .mobile-social-menu {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            justify-content: center;
        }
        
        .mobile-social-menu a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--light-bg);
            color: var(--text-color);
        }
        
        .mobile-social-menu a:hover {
            background-color: var(--accent-color);
            color: var(--white);
        }
        
        .mobile-cta-btn {
            display: block;
            text-align: center;
            background-color: var(--accent-color);
            color: var(--white);
            padding: 12px 20px;
            border-radius: 4px;
            font-weight: 500;
            margin-top: 10px;
        }
        
        .mobile-cta-btn:hover {
            background-color: var(--secondary-color);
            color: var(--white);
        }
        
        /* Breadcrumb */
        .breadcrumb {
            background-color: var(--light-bg);
            padding: 0.75rem 1rem;
            border-radius: 0.375rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            border: 1px solid var(--border-color);
            margin-bottom: 2rem;
        }
        
        .breadcrumb-item a {
            color: var(--accent-color);
            text-decoration: none;
        }
        
        .breadcrumb-item.active {
            color: var(--text-light);
        }
        
        /* Section Styles */
        .section-title {
            position: relative;
            margin-bottom: 2.5rem;
            text-align: center;
        }
        
        .section-title::after {
            content: '';
            display: block;
            width: 80px;
            height: 4px;
            background: var(--accent-color);
            margin: 15px auto;
            border-radius: 2px;
        }
        
        .text-blue {
            color: var(--accent-color);
        }
        
        /* Content Area */
        .content-area {
            padding: 50px 0;
        }
        
        /* Footer */
        .site-footer {
            background-color: var(--primary-color);
            color: var(--white);
        }
        
        .footer-main {
            padding: 60px 0 30px;
        }
        
        .footer-logo img {
            height: 80px;
            margin-bottom: 20px;
        }
        
        .footer-info {
            font-size: 14px;
            line-height: 1.6;
        }
        
        .footer-info a {
            color: var(--white);
        }
        
        .footer-info a:hover {
            color: var(--light-bg);
        }
        
        .footer-social {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .footer-social a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: rgba(255,255,255,0.1);
            color: var(--white);
        }
        
        .footer-social a:hover {
            background-color: var(--white);
            color: var(--primary-color);
        }
        
        .footer-bottom {
            background-color: var(--dark-bg);
            padding: 20px 0;
            font-size: 14px;
        }
        
        .footer-bottom a {
            color: var(--white);
        }
        
        .footer-bottom a:hover {
            color: var(--light-bg);
        }
        
        /* Botones */
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
            padding: 10px 25px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        /* Container */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }

        /* Contact Card Styles */
        .contact-card {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            height: 100%;
            background-color: var(--white);
            border: 1px solid var(--border-color);
        }
        
        .contact-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.15);
            border-color: var(--accent-color);
        }
        
        .contact-card .card-body {
            padding: 1.5rem;
        }
        
        .service-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 32px;
            color: var(--white);
            background: linear-gradient(135deg, var(--accent-color) 0%, var(--secondary-color) 100%);
            box-shadow: 0 5px 15px rgba(26, 75, 140, 0.3);
        }
        
        .btn-blue {
            background-color: var(--accent-color);
            color: var(--white);
            border: none;
            font-weight: 500;
            padding: 0.6rem 1.5rem;
            border-radius: 6px;
            transition: all 0.3s ease;
        }
        
        .btn-blue:hover {
            background-color: var(--secondary-color);
            color: var(--white);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(26, 75, 140, 0.3);
        }
        
        /* Modal Styles */
        .modal-content {
            border-radius: 12px;
            border: none;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }
        
        .modal-header {
            background-color: var(--accent-color);
            color: var(--white);
            border-bottom: 1px solid var(--secondary-color);
            border-radius: 12px 12px 0 0;
        }
        
        .btn-close {
            filter: invert(1);
        }
        
        .form-label {
            font-weight: 500;
            color: var(--text-color);
            margin-bottom: 0.5rem;
        }
        
        .required-field::after {
            content: " *";
            color: #dc3545;
        }
        
        .form-control:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 0.25rem rgba(26, 75, 140, 0.25);
        }
        
        .form-select:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 0.25rem rgba(26, 75, 140, 0.25);
        }

        /* Alert Styles */
        .alert {
            border-radius: 8px;
            border: none;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        /* Map Styles */
        .ratio-16x9 {
            border-radius: 8px;
            overflow: hidden;
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            /* Navegación responsive - ocultar navegación principal en móvil */
            .main-navigation {
                display: none;
            }
            
            .hm-header-gadgets .hm-social-menu,
            .hm-header-gadgets .hm-cta-btn {
                display: none;
            }
            
            /* Mostrar botón hamburguesa en móvil */
            .hamburger-menu {
                display: flex;
            }
            
            /* Mostrar contenedor de navegación móvil */
            .mobile-nav-container {
                display: block;
            }
        }
        
        @media (max-width: 768px) {
            .service-icon {
                width: 60px;
                height: 60px;
                font-size: 24px;
            }
        }
    </style>
</head>

<body>
    <!-- Header -->
    <header class="site-header">
        <div class="header-main">
            <div class="container">
                <div class="hm-header-inner">
                    <div class="site-branding">
                        <h2 class="site-title"><a href="index.php">Portal Escolar</a></h2>
                    </div>
                    
                    <nav class="main-navigation">
                        <ul>
                            <li><a href="index.php">Inicio</a></li>
                            <li><a href="quienes-somos.php">Quiénes Somos</a></li>
                            <li><a href="noticias.php">Noticias</a></li>
                            <li><a href="academico.php">Académico</a></li>
                            <li><a href="calendario.php">Calendario</a></li>
                            <li class="current-menu-item"><a href="contacto.php">Contacto</a></li>
                        </ul>
                    </nav>
                    
                    <div class="hm-header-gadgets">
                        <nav class="hm-social-menu">
                            <?php foreach ($social_media as $social): ?>
                            <a href="<?php echo $social['url']; ?>" target="_blank">
                                <?php if ($social['icon_type'] == 'fontawesome'): ?>
                                    <i class="<?php echo $social['icon']; ?>"></i>
                                <?php else: ?>
                                    <img src="admin/<?php echo $social['icon']; ?>" width="16" height="16">
                                <?php endif; ?>
                            </a>
                            <?php endforeach; ?>
                        </nav>
                        <a href="admin/" class="hm-cta-btn">Login</a>
                    </div>
                    
                    <!-- Botón hamburguesa para móviles -->
                    <button class="hamburger-menu">
                        <span></span>
                        <span></span>
                        <span></span>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Navegación móvil -->
    <div class="mobile-nav-container">
        <!-- Overlay del menú móvil -->
        <div class="mobile-menu-overlay"></div>
        
        <!-- Menú móvil -->
        <div class="mobile-menu">
            <div class="mobile-menu-header">
                <div class="mobile-menu-logo">Portal Escolar</div>
                <button class="mobile-menu-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <nav class="mobile-nav">
                <ul>
                    <li><a href="index.php">Inicio</a></li>
                    <li><a href="quienes-somos.php">Quiénes Somos</a></li>
                    <li><a href="noticias.php">Noticias</a></li>
                    <li><a href="academico.php">Académico</a></li>
                    <li><a href="calendario.php">Calendario</a></li>
                    <li class="current-menu-item"><a href="contacto.php">Contacto</a></li>
                </ul>
            </nav>
            
            <div class="mobile-gadgets">
                <nav class="mobile-social-menu">
                    <?php foreach ($social_media as $social): ?>
                    <a href="<?php echo $social['url']; ?>" target="_blank">
                        <?php if ($social['icon_type'] == 'fontawesome'): ?>
                            <i class="<?php echo $social['icon']; ?>"></i>
                        <?php else: ?>
                            <img src="admin/<?php echo $social['icon']; ?>" width="16" height="16">
                        <?php endif; ?>
                    </a>
                    <?php endforeach; ?>
                </nav>
                <a href="admin/" class="mobile-cta-btn">Acceso Administrativo</a>
            </div>
        </div>
    </div>

    <div id="content" class="site-content">
        <div class="content-area container">
            <!-- Page Heading/Breadcrumbs -->
            <h1 class="section-title text-blue">Contacto</h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="index.php">Inicio</a>
                </li>
                <li class="breadcrumb-item active">Contacto</li>
            </ol>
            
            <!-- Información de contacto -->
            <div class="row mb-5">
                <div class="col-md-6 mb-4">
                    <div class="card h-100 contact-card">
                        <div class="card-body text-center">
                            <h3 class="card-title text-blue">Horario de Atención</h3>
                            <div class="service-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <p class="card-text">
                                <strong>Lunes a Viernes:</strong> 7:30 am - 3:30 pm<br>
                                <strong>Sábados:</strong> 8:00 am - 12:00 pm<br>
                                <strong>Domingos:</strong> Cerrado
                            </p>
                            <p class="text-muted">Horario sujeto a cambios en días feriados y período vacacional</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="card h-100 contact-card">
                        <div class="card-body text-center">
                            <h3 class="card-title text-blue">Contacto Rápido</h3>
                            <div class="service-icon">
                                <i class="fas fa-phone-alt"></i>
                            </div>
                            <p class="card-text">
                                <strong>Teléfono:</strong> (0212) 555-0123<br>
                                <strong>Email:</strong> info@portalescolar.edu.ve<br>
                                <strong>Dirección:</strong> Av. Principal, Urbanización Los Olivos, Caracas
                            </p>
                            <button type="button" class="btn btn-blue mt-3" data-bs-toggle="modal" data-bs-target="#contactModal">
                                <i class="fas fa-envelope me-2"></i>Envíanos un mensaje
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Mapa de ubicación -->
            <div class="row mb-5">
                <div class="col-12">
                    <div class="card contact-card">
                        <div class="card-body">
                            <h3 class="card-title text-blue text-center mb-4">Nuestra Ubicación</h3>
                            <div class="ratio ratio-16x9">
                                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3923.381037503997!2d-66.90373482423964!3d10.48038908971967!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x8c2a58f7c03c35c9%3A0x5c22b3d7e8f3e3b4!2sCaracas%2C%20Venezuela!5e0!3m2!1ses!2sve!4v1642107028447!5m2!1ses!2sve" 
                                        width="100%" 
                                        height="100%" 
                                        style="border-radius: 8px;" 
                                        allowfullscreen="" 
                                        loading="lazy">
                                </iframe>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal de Contacto -->
    <div class="modal fade" id="contactModal" tabindex="-1" aria-labelledby="contactModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="contactModalLabel">Contáctanos</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php if (isset($mensaje_exito)): ?>
                    <div class="alert alert-success" role="alert">
                        <?php echo $mensaje_exito; ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (isset($mensaje_error)): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo $mensaje_error; ?>
                    </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nombre" class="form-label required-field">Nombre Completo</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label required-field">Correo electrónico</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="telefono" class="form-label">Teléfono</label>
                                <input type="tel" class="form-control" id="telefono" name="telefono">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="asunto" class="form-label required-field">Asunto</label>
                                <select class="form-select" id="asunto" name="asunto" required>
                                    <option value="" selected disabled>—Por favor, elige una opción—</option>
                                    <option value="Información general">Información general</option>
                                    <option value="Admisión y matrícula">Admisión y matrícula</option>
                                    <option value="Consulta académica">Consulta académica</option>
                                    <option value="Actividades extracurriculares">Actividades extracurriculares</option>
                                    <option value="Problemas técnicos">Problemas técnicos</option>
                                    <option value="Otro">Otro</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="comentarios" class="form-label required-field">Mensaje</label>
                            <textarea class="form-control" id="comentarios" name="comentarios" rows="4" required></textarea>
                        </div>
                        <div class="d-grid">
                            <button type="submit" name="contacto" class="btn btn-blue btn-lg">Enviar Mensaje</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <footer class="site-footer">
        <div class="footer-main">
            <div class="container">
                <div class="row">
                    <div class="col-md-6">
                        <div class="footer-logo">
                            <h3 style="color: white; margin-bottom: 20px;">Portal Escolar</h3>
                        </div>
                        <div class="footer-info">
                            <p><strong>Dirección:</strong> Calle Principal #123, Colonia Centro. Código Postal 12345</p>
                            <p><strong>Contacto:</strong> <a href="mailto:info@colegioejemplo.edu">info@colegioejemplo.edu</a></p>
                            <p><strong>Teléfono:</strong> <a href="tel:+1234567890">+123 456 7890</a></p>
                        </div>
                        <div class="footer-social">
                            <?php foreach ($social_media as $social): ?>
                            <a href="<?php echo $social['url']; ?>" target="_blank">
                                <?php if ($social['icon_type'] == 'fontawesome'): ?>
                                    <i class="<?php echo $social['icon']; ?>"></i>
                                <?php else: ?>
                                    <img src="admin/<?php echo $social['icon']; ?>" width="16" height="16">
                                <?php endif; ?>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="footer-info">
                            <h3 style="color: white; margin-bottom: 20px;">Portal Escolar</h3>
                            <p>El Portal Escolar es la plataforma oficial de comunicación e información educativa, dedicada a promover la innovación y el desarrollo integral en el ámbito educativo.</p>
                            <p>Nuestro compromiso es brindar recursos, herramientas y contenidos de calidad para fortalecer el proceso de enseñanza-aprendizaje de nuestra comunidad educativa.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="container">
                <div class="row">
                    <div class="col-md-6">
                        <div class="footer-copyright">
                            &copy; <?php echo date('Y'); ?> Portal Escolar | Institución Educativa
                        </div>
                    </div>
                    <div class="col-md-6 text-end">
                        <div class="footer-credits">
                            Desarrollado para la comunidad educativa
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Navegación móvil
        document.addEventListener('DOMContentLoaded', function() {
            const hamburgerMenu = document.querySelector('.hamburger-menu');
            const mobileMenu = document.querySelector('.mobile-menu');
            const mobileMenuOverlay = document.querySelector('.mobile-menu-overlay');
            const mobileMenuClose = document.querySelector('.mobile-menu-close');
            
            if (hamburgerMenu && mobileMenu && mobileMenuOverlay && mobileMenuClose) {
                // Abrir menú móvil
                hamburgerMenu.addEventListener('click', function() {
                    this.classList.toggle('active');
                    mobileMenu.classList.toggle('active');
                    mobileMenuOverlay.classList.toggle('active');
                    document.body.style.overflow = 'hidden';
                });
                
                // Cerrar menú móvil
                function closeMobileMenu() {
                    hamburgerMenu.classList.remove('active');
                    mobileMenu.classList.remove('active');
                    mobileMenuOverlay.classList.remove('active');
                    document.body.style.overflow = '';
                }
                
                mobileMenuClose.addEventListener('click', closeMobileMenu);
                mobileMenuOverlay.addEventListener('click', closeMobileMenu);
                
                // Cerrar menú al hacer clic en un enlace
                const mobileNavLinks = document.querySelectorAll('.mobile-nav a');
                mobileNavLinks.forEach(link => {
                    link.addEventListener('click', closeMobileMenu);
                });
            }

            // Mostrar automáticamente el modal si hay un error en el formulario
            <?php if (isset($mensaje_error) || isset($mensaje_exito)): ?>
                var contactModal = new bootstrap.Modal(document.getElementById('contactModal'));
                contactModal.show();
            <?php endif; ?>
        });
    </script>
</body>
</html>