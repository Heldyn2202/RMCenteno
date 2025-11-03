<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
    <title>Portal Escolar - Educación Primaria</title>
    
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
        
        .site-branding img {
            height: 60px;
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
        
        /* Content Area */
        .content-area {
            padding: 50px 0;
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
        
        /* Academic Card Styles */
        .academic-card {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            height: 100%;
            background-color: var(--white);
            border: 1px solid var(--border-color);
        }
        
        .academic-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.15);
            border-color: var(--accent-color);
        }
        
        .academic-card img {
            height: 220px;
            object-fit: cover;
            width: 100%;
            transition: transform 0.5s ease;
        }
        
        .academic-card:hover img {
            transform: scale(1.05);
        }
        
        .academic-card .card-body {
            padding: 1.5rem;
        }
        
        .academic-card .card-title {
            font-size: 1.25rem;
            height: 60px;
            overflow: hidden;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 0.8rem;
        }
        
        .academic-card .card-text {
            height: 130px;
            overflow: hidden;
            color: var(--text-light);
            margin-bottom: 1.2rem;
        }
        
        .academic-card .card-footer {
            background-color: var(--light-bg);
            border-top: 1px solid var(--border-color);
            padding: 0.75rem 1.5rem;
            color: var(--text-light);
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
        
        /* Alert Styles */
        .alert-no-content {
            border-radius: 4px;
            padding: 20px;
            margin: 30px 0;
            text-align: center;
            background-color: var(--white);
            border: 1px solid var(--border-color);
        }
        
        .alert-no-content i {
            font-size: 2.5rem;
            color: var(--accent-color);
            margin-bottom: 15px;
        }
        
        .alert-no-content h4 {
            color: var(--text-color);
            margin-bottom: 10px;
        }
        
        .alert-no-content p {
            color: var(--text-light);
            margin-bottom: 0;
        }
        
        /* Academic Specific Styles */
        .program-icon {
            font-size: 3rem;
            color: var(--accent-color);
            margin-bottom: 1rem;
        }
        
        .resource-list {
            list-style: none;
            padding: 0;
        }
        
        .resource-list li {
            padding: 0.75rem 0;
            border-bottom: 1px solid var(--border-color);
        }
        
        .resource-list li:last-child {
            border-bottom: none;
        }
        
        .resource-list a {
            display: flex;
            align-items: center;
            color: var(--text-color);
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .resource-list a:hover {
            color: var(--accent-color);
        }
        
        .resource-list i {
            margin-right: 0.75rem;
            color: var(--accent-color);
            width: 24px;
            text-align: center;
        }
        
        /* Program Detail Styles */
        .program-hero {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 60px 0;
            margin-bottom: 40px;
            text-align: center;
        }
        
        .program-hero h1 {
            color: white;
            font-size: 2.5rem;
            margin-bottom: 20px;
        }
        
        .program-hero p {
            font-size: 1.2rem;
            max-width: 800px;
            margin: 0 auto;
        }
        
        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin: 40px 0;
        }
        
        .feature-item {
            text-align: center;
            padding: 30px 20px;
            background: var(--white);
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            border: 1px solid var(--border-color);
        }
        
        .feature-icon {
            font-size: 3rem;
            color: var(--accent-color);
            margin-bottom: 1.5rem;
        }
        
        .curriculum-section {
            background-color: var(--light-bg);
            padding: 50px 0;
            margin: 50px 0;
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
            .academic-card .card-text {
                height: auto;
                min-height: 100px;
            }
            
            .feature-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <?php
    // Conexión a la base de datos
    $host = 'localhost';
    $dbname = 'sige';
    $username = 'root';
    $password = '';
    
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Obtener información específica de Educación Primaria
        $stmt = $pdo->prepare("SELECT * FROM academic_programs WHERE name = 'Educación Primaria'");
        $stmt->execute();
        $program_info = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Obtener redes sociales
        $stmt = $pdo->query("SELECT * FROM social_media WHERE status = 1 ORDER BY name");
        $social_media = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch(PDOException $e) {
        // En caso de error, inicializar variables vacías
        $program_info = [];
        $social_media = [];
    }
    ?>
    
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
                            <li class="current-menu-item"><a href="academico.php">Académico</a></li>
                            <li><a href="calendario.php">Calendario</a></li>
                            <li><a href="contacto.php">Contacto</a></li>
                        </ul>
                    </nav>
                    
                    <div class="hm-header-gadgets">
                        <nav class="hm-social-menu">
                            <?php if (!empty($social_media)): ?>
                                <?php foreach ($social_media as $social): ?>
                                <a href="<?php echo $social['url']; ?>" target="_blank">
                                    <?php if ($social['icon_type'] == 'fontawesome'): ?>
                                        <i class="<?php echo $social['icon']; ?>"></i>
                                    <?php else: ?>
                                        <img src="admin/<?php echo $social['icon']; ?>" width="16" height="16">
                                    <?php endif; ?>
                                </a>
                                <?php endforeach; ?>
                            <?php endif; ?>
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
                    <li class="current-menu-item"><a href="academico.php">Académico</a></li>
                    <li><a href="calendario.php">Calendario</a></li>
                    <li><a href="contacto.php">Contacto</a></li>
                </ul>
            </nav>
            
            <div class="mobile-gadgets">
                <nav class="mobile-social-menu">
                    <?php if (!empty($social_media)): ?>
                        <?php foreach ($social_media as $social): ?>
                        <a href="<?php echo $social['url']; ?>" target="_blank">
                            <?php if ($social['icon_type'] == 'fontawesome'): ?>
                                <i class="<?php echo $social['icon']; ?>"></i>
                            <?php else: ?>
                                <img src="admin/<?php echo $social['icon']; ?>" width="16" height="16">
                            <?php endif; ?>
                        </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </nav>
                <a href="admin/" class="mobile-cta-btn">Acceso Administrativo</a>
            </div>
        </div>
    </div>

    <!-- Contenido Principal -->
    <div id="content" class="site-content">
        <!-- Program Hero Section -->
        <section class="program-hero">
            <div class="container">
                <h1>Educación Primaria</h1>
                <p>Formando las bases para el desarrollo integral de nuestros estudiantes a través de una educación de calidad y valores.</p>
            </div>
        </section>

        <div class="content-area">
            <div class="container">
                <!-- Page Heading/Breadcrumbs -->
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="index.php">Inicio</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="academico.php">Académico</a>
                    </li>
                    <li class="breadcrumb-item active">Educación Primaria</li>
                </ol>

                <!-- Program Description -->
                <div class="row mb-5">
                    <div class="col-lg-12">
                        <div class="card academic-card">
                            <div class="card-body">
                                <h2 class="section-title text-blue">Nuestro Programa de Educación Primaria</h2>
                                <div class="mt-4">
                                    <p>Nuestro programa de Educación Primaria está diseñado para desarrollar las habilidades fundamentales en lectura, escritura, matemáticas y ciencias, mientras fomentamos valores como el respeto, la responsabilidad y la solidaridad.</p>
                                    
                                    <p>A través de un enfoque integral, nuestros estudiantes adquieren las herramientas necesarias para su desarrollo académico y personal, preparándolos para los desafíos de la educación secundaria y para la vida.</p>
                                    
                                    <div class="row mt-4">
                                        <div class="col-md-6">
                                            <h5 class="text-blue">Objetivos Principales</h5>
                                            <ul>
                                                <li>Desarrollar habilidades básicas de lectoescritura y matemáticas</li>
                                                <li>Fomentar el pensamiento crítico y la creatividad</li>
                                                <li>Promover valores éticos y sociales</li>
                                                <li>Estimular el trabajo en equipo y la colaboración</li>
                                            </ul>
                                        </div>
                                        <div class="col-md-6">
                                            <h5 class="text-blue">Metodología</h5>
                                            <ul>
                                                <li>Enfoque constructivista del aprendizaje</li>
                                                <li>Aprendizaje basado en proyectos</li>
                                                <li>Uso de tecnología educativa</li>
                                                <li>Evaluación formativa continua</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Program Features -->
                <h2 class="section-title text-blue">Características del Programa</h2>
                <div class="feature-grid">
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-book-reader"></i>
                        </div>
                        <h4>Enfoque Integral</h4>
                        <p>Desarrollamos habilidades académicas, sociales y emocionales para un crecimiento equilibrado de nuestros estudiantes.</p>
                    </div>
                    
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h4>Aprendizaje Colaborativo</h4>
                        <p>Fomentamos el trabajo en equipo y la comunicación efectiva a través de actividades grupales y proyectos conjuntos.</p>
                    </div>
                    
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-laptop-code"></i>
                        </div>
                        <h4>Tecnología Educativa</h4>
                        <p>Integramos herramientas tecnológicas modernas para enriquecer el proceso de aprendizaje y desarrollar competencias digitales.</p>
                    </div>
                </div>

                <!-- Curriculum Section -->
                <section class="curriculum-section">
                    <div class="container">
                        <h2 class="section-title text-blue">Plan de Estudios</h2>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card academic-card">
                                    <div class="card-body">
                                        <h4 class="text-blue">Áreas Fundamentales</h4>
                                        <ul class="resource-list">
                                            <li><i class="fas fa-book"></i> Lengua y Literatura</li>
                                            <li><i class="fas fa-calculator"></i> Matemáticas</li>
                                            <li><i class="fas fa-flask"></i> Ciencias Naturales</li>
                                            <li><i class="fas fa-globe-americas"></i> Estudios Sociales</li>
                                            <li><i class="fas fa-language"></i> Inglés como segunda lengua</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card academic-card">
                                    <div class="card-body">
                                        <h4 class="text-blue">Áreas Complementarias</h4>
                                        <ul class="resource-list">
                                            <li><i class="fas fa-running"></i> Educación Física</li>
                                            <li><i class="fas fa-palette"></i> Arte y Música</li>
                                            <li><i class="fas fa-laptop"></i> Informática Educativa</li>
                                            <li><i class="fas fa-heart"></i> Valores y Ética</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Activities -->
                <h2 class="section-title text-blue">Actividades Complementarias</h2>
                <div class="row">
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card academic-card">
                            <img src="https://placehold.co/400x250/1a4b8c/white?text=Actividades+Culturales" alt="Actividades Culturales" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAwIiBoZWlnaHQ9IjI1MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZGRkIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCwgc2Fucy1zZXJpZiIgZm9udC1zaXplPSIxNCIgZmlsbD0iIzk5OSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPkFjdGl2aWRhZGVzIEN1bHR1cmFsZXM8L3RleHQ+PC9zdmc+'">
                            <div class="card-body">
                                <h4 class="card-title">Actividades Culturales</h4>
                                <p class="card-text">Celebraciones, festivales y presentaciones artísticas que enriquecen nuestra identidad cultural y desarrollan la expresión creativa.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card academic-card">
                            <img src="https://placehold.co/400x250/1a4b8c/white?text=Deportes" alt="Deportes" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAwIiBoZWlnaHQ9IjI1MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZGRkIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCwgc2Fucy1zZXJpZiIgZm9udC1zaXplPSIxNCIgZmlsbD0iIzk5OSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPkRlcG9ydGVzPC90ZXh0Pjwvc3ZnPg=='">
                            <div class="card-body">
                                <h4 class="card-title">Deportes</h4>
                                <p class="card-text">Programas deportivos para desarrollar habilidades motoras, coordinación y valores como el trabajo en equipo y la disciplina.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card academic-card">
                            <img src="https://placehold.co/400x250/1a4b8c/white?text=Excursiones" alt="Excursiones" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAwIiBoZWlnaHQ9IjI1MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZGRkIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCwgc2Fucy1zZXJpZiIgZm9udC1zaXplPSIxNCIgZmlsbD0iIzk5OSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPkV4Y3Vyc2lvbmVzPC90ZXh0Pjwvc3ZnPg=='">
                            <div class="card-body">
                                <h4 class="card-title">Excursiones Educativas</h4>
                                <p class="card-text">Visitas a museos, parques naturales y centros culturales para aprendizaje experiencial fuera del aula.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact CTA -->
                <div class="row mt-5">
                    <div class="col-lg-12 text-center">
                        <div class="card academic-card">
                            <div class="card-body">
                                <h3 class="text-blue">¿Interesado en nuestro programa de Educación Primaria?</h3>
                                <p class="mt-3">Contáctanos para obtener más información sobre el proceso de admisión y nuestros programas educativos.</p>
                                <a href="contacto.php" class="btn btn-blue mt-3">Solicitar Información</a>
                            </div>
                        </div>
                    </div>
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
                            <?php if (!empty($social_media)): ?>
                                <?php foreach ($social_media as $social): ?>
                                <a href="<?php echo $social['url']; ?>" target="_blank">
                                    <?php if ($social['icon_type'] == 'fontawesome'): ?>
                                        <i class="<?php echo $social['icon']; ?>"></i>
                                    <?php else: ?>
                                        <img src="admin/<?php echo $social['icon']; ?>" width="16" height="16">
                                    <?php endif; ?>
                                </a>
                                <?php endforeach; ?>
                            <?php endif; ?>
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
        });
    </script>
</body>
</html>