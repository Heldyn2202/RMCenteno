<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
    <title>Calendario - Portal Escolar</title>
    
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

        /* Calendar Timeline Styles */
        .timeline {
            position: relative;
            padding: 20px 0;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .timeline::before {
            content: '';
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            top: 0;
            height: 100%;
            width: 4px;
            background: linear-gradient(to bottom, var(--accent-color), var(--secondary-color));
            border-radius: 10px;
        }
        
        .timeline-item {
            position: relative;
            margin-bottom: 30px;
            width: 100%;
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.4s ease;
        }
        
        .timeline-item.visible {
            opacity: 1;
            transform: translateY(0);
        }
        
        .timeline-item:nth-child(odd) .timeline-content {
            margin-left: auto;
        }
        
        .timeline-item:nth-child(even) .timeline-content {
            margin-right: auto;
        }
        
        .timeline-icon {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            top: 15px;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
            z-index: 2;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .timeline-icon:hover {
            transform: translateX(-50%) scale(1.15);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.25);
        }
        
        .timeline-content {
            width: 44%;
            background: var(--white);
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
            border-left: 5px solid;
            position: relative;
            transition: all 0.3s ease;
            cursor: pointer;
            border: 1px solid var(--border-color);
        }
        
        .timeline-content:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            border-color: var(--accent-color);
        }
        
        .timeline-date {
            font-weight: 600;
            color: var(--text-light);
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            font-size: 0.95rem;
        }
        
        .timeline-date i {
            margin-right: 8px;
            font-size: 1rem;
        }
        
        .timeline-title {
            font-weight: 700;
            margin-bottom: 12px;
            color: var(--text-color);
            font-size: 1.2rem;
            line-height: 1.3;
        }
        
        .timeline-desc {
            color: var(--text-light);
            margin-bottom: 0;
            line-height: 1.6;
            font-size: 0.95rem;
        }
        
        .timeline-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 18px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-bottom: 12px;
            color: white;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .badge-inscripcion {
            background-color: #4caf50;
            border-left-color: #4caf50 !important;
        }
        
        .badge-inicio {
            background-color: #2196f3;
            border-left-color: #2196f3 !important;
        }
        
        .badge-fin {
            background-color: #f44336;
            border-left-color: #f44336 !important;
        }
        
        .badge-vacaciones {
            background-color: #ff9800;
            border-left-color: #ff9800 !important;
        }
        
        .badge-evaluacion {
            background-color: #9c27b0;
            border-left-color: #9c27b0 !important;
        }
        
        .badge-otro {
            background-color: #607d8b;
            border-left-color: #607d8b !important;
        }
        
        .filter-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 30px;
            justify-content: center;
        }
        
        .filter-button {
            padding: 8px 16px;
            border-radius: 22px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid;
            background-color: transparent;
            font-size: 0.9rem;
        }
        
        .filter-button.active, .filter-button:hover {
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .filter-inscripcion {
            color: #4caf50;
            border-color: #4caf50;
        }
        
        .filter-inscripcion.active, .filter-inscripcion:hover {
            background-color: #4caf50;
        }
        
        .filter-inicio {
            color: #2196f3;
            border-color: #2196f3;
        }
        
        .filter-inicio.active, .filter-inicio:hover {
            background-color: #2196f3;
        }
        
        .filter-fin {
            color: #f44336;
            border-color: #f44336;
        }
        
        .filter-fin.active, .filter-fin:hover {
            background-color: #f44336;
        }
        
        .filter-vacaciones {
            color: #ff9800;
            border-color: #ff9800;
        }
        
        .filter-vacaciones.active, .filter-vacaciones:hover {
            background-color: #ff9800;
        }
        
        .filter-evaluacion {
            color: #9c27b0;
            border-color: #9c27b0;
        }
        
        .filter-evaluacion.active, .filter-evaluacion:hover {
            background-color: #9c27b0;
        }
        
        .filter-otro {
            color: #607d8b;
            border-color: #607d8b;
        }
        
        .filter-otro.active, .filter-otro:hover {
            background-color: #607d8b;
        }
        
        .event-level {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            background-color: var(--light-bg);
            color: var(--text-light);
            font-size: 0.75rem;
            margin-top: 12px;
            border: 1px solid var(--border-color);
        }
        
        .modal-content {
            border-radius: 12px;
            overflow: hidden;
            border: none;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
        }
        
        .modal-header {
            border-bottom: 2px solid var(--light-bg);
            padding: 18px 22px;
        }
        
        .modal-title {
            font-weight: 700;
            font-size: 1.3rem;
        }
        
        .modal-body {
            padding: 22px;
        }
        
        .modal-footer {
            border-top: 2px solid var(--light-bg);
            padding: 15px 22px;
        }
        
        .event-detail-item {
            display: flex;
            margin-bottom: 18px;
            align-items: flex-start;
        }
        
        .event-detail-icon {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            flex-shrink: 0;
            color: white;
            font-size: 16px;
        }
        
        .event-detail-content {
            flex: 1;
        }
        
        .event-detail-label {
            font-weight: 600;
            color: var(--text-light);
            font-size: 0.85rem;
            margin-bottom: 4px;
        }
        
        .event-detail-value {
            font-size: 1rem;
            color: var(--text-color);
        }
        
        .periodo-selector {
            display: flex;
            justify-content: center;
            margin-bottom: 25px;
            gap: 12px;
        }
        
        .periodo-btn {
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 600;
            background-color: var(--white);
            border: 2px solid var(--accent-color);
            color: var(--accent-color);
            transition: all 0.3s ease;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
        }
        
        .periodo-btn.active, .periodo-btn:hover {
            background-color: var(--accent-color);
            color: var(--white);
            transform: translateY(-2px);
        }
        
        .no-events {
            text-align: center;
            padding: 35px;
            background-color: var(--white);
            border-radius: 10px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.06);
            margin: 25px 0;
            border: 1px solid var(--border-color);
        }
        
        .no-events i {
            font-size: 2.5rem;
            color: var(--border-color);
            margin-bottom: 15px;
        }
        
        .no-events h4 {
            color: var(--text-light);
            margin-bottom: 12px;
            font-size: 1.2rem;
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
            
            .timeline::before {
                left: 25px;
            }
            
            .timeline-content {
                width: calc(100% - 70px);
                margin-left: 70px !important;
                margin-right: 0 !important;
            }
            
            .timeline-icon {
                left: 25px;
                width: 40px;
                height: 40px;
                font-size: 16px;
            }
        }
        
        @media (max-width: 768px) {
            .filter-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .filter-button {
                width: 100%;
                text-align: center;
            }
            
            .periodo-selector {
                flex-direction: column;
                align-items: center;
            }
            
            .periodo-btn {
                width: 100%;
                text-align: center;
            }
            
            .timeline-content {
                padding: 16px;
            }
            
            .timeline-title {
                font-size: 1.1rem;
            }
        }
        
        @media (max-width: 576px) {
            .timeline-content {
                width: calc(100% - 60px);
                margin-left: 60px !important;
                padding: 14px;
            }
            
            .timeline-icon {
                width: 36px;
                height: 36px;
                font-size: 14px;
                left: 18px;
            }
            
            .timeline::before {
                left: 18px;
                width: 3px;
            }
            
            .modal-body {
                padding: 18px 12px;
            }
            
            .event-detail-item {
                flex-direction: column;
            }
            
            .event-detail-icon {
                margin-bottom: 8px;
            }
            
            .timeline-date, .timeline-desc {
                font-size: 0.9rem;
            }
        }
    </style>
</head>

<body>
    <?php
    // Simulamos la conexión a base de datos
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "sige";
    
    // Crear conexión
    $con = new mysqli($servername, $username, $password, $dbname);
    
    // Verificar conexión
    if ($con->connect_error) {
        die("Conexión fallida: " . $con->connect_error);
    }

    // Obtener eventos del calendario académico
    $calendarioQuery = "SELECT * FROM calendario_academico WHERE activo = 1 ORDER BY fecha_inicio";
    $calendarioResult = mysqli_query($con, $calendarioQuery);
    $eventos = [];
    
    if ($calendarioResult) {
        $eventos = mysqli_fetch_all($calendarioResult, MYSQLI_ASSOC);
    }

    // Obtener redes sociales desde la base de datos
    $socialQuery = "SELECT * FROM social_media WHERE status = 1 ORDER BY name";
    $socialResult = mysqli_query($con, $socialQuery);
    $social_media = [];
    if ($socialResult) {
        $social_media = mysqli_fetch_all($socialResult, MYSQLI_ASSOC);
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
                            <li><a href="academico.php">Académico</a></li>
                            <li class="current-menu-item"><a href="calendario.php">Calendario</a></li>
                            <li><a href="contacto.php">Contacto</a></li>
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
                    <li class="current-menu-item"><a href="calendario.php">Calendario</a></li>
                    <li><a href="contacto.php">Contacto</a></li>
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
            <h1 class="section-title text-blue">Calendario</h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="index.php">Inicio</a>
                </li>
                <li class="breadcrumb-item active">Calendario</li>
            </ol>
            
            <!-- Vista de línea de tiempo vertical mejorada -->
            <h2 class="section-title text-blue">Línea de Tiempo del Año Escolar</h2>
            
            <?php if (count($eventos) > 0): ?>
            <div class="timeline">
                <?php foreach ($eventos as $evento): 
                    $icon = '';
                    $badge_class = '';
                    $border_color = '';
                    
                    switch($evento['tipo_evento']) {
                        case 'inscripcion':
                            $icon = 'fa-pencil-alt';
                            $badge_class = 'badge-inscripcion';
                            $border_color = 'border-left-color: #4caf50 !important;';
                            break;
                        case 'inicio_clases':
                            $icon = 'fa-school';
                            $badge_class = 'badge-inicio';
                            $border_color = 'border-left-color: #2196f3 !important;';
                            break;
                        case 'fin_lapso':
                            $icon = 'fa-flag-checkered';
                            $badge_class = 'badge-fin';
                            $border_color = 'border-left-color: #f44336 !important;';
                            break;
                        case 'vacaciones':
                            $icon = 'fa-umbrella-beach';
                            $badge_class = 'badge-vacaciones';
                            $border_color = 'border-left-color: #ff9800 !important;';
                            break;
                        case 'evaluacion':
                            $icon = 'fa-clipboard-list';
                            $badge_class = 'badge-evaluacion';
                            $border_color = 'border-left-color: #9c27b0 !important;';
                            break;
                        default:
                            $icon = 'fa-calendar-day';
                            $badge_class = 'badge-otro';
                            $border_color = 'border-left-color: #607d8b !important;';
                    }
                    
                    $fecha_texto = date('d/m/Y', strtotime($evento['fecha_inicio']));
                    if ($evento['fecha_fin'] && $evento['fecha_fin'] != $evento['fecha_inicio']) {
                        $fecha_texto .= ' - ' . date('d/m/Y', strtotime($evento['fecha_fin']));
                    }
                    
                    $nivel_texto = '';
                    if ($evento['nivel_educativo'] != 'todos') {
                        switch($evento['nivel_educativo']) {
                            case 'inicial': $nivel_texto = 'Educación Inicial'; break;
                            case 'primaria': $nivel_texto = 'Educación Primaria'; break;
                            case 'secundaria': $nivel_texto = 'Educación Secundaria'; break;
                            case 'media': $nivel_texto = 'Educación Media'; break;
                            default: $nivel_texto = 'Todos los niveles'; break;
                        }
                    }
                ?>
                <div class="timeline-item" data-type="<?php echo $evento['tipo_evento']; ?>" data-periodo="<?php echo $evento['periodo_academico'] ?? '2023-2024'; ?>">
                    <div class="timeline-icon <?php echo $badge_class; ?>" data-bs-toggle="modal" data-bs-target="#eventModal" data-event-id="<?php echo $evento['id']; ?>">
                        <i class="fas <?php echo $icon; ?>"></i>
                    </div>
                    <div class="timeline-content" style="<?php echo $border_color; ?>" data-bs-toggle="modal" data-bs-target="#eventModal" data-event-id="<?php echo $evento['id']; ?>">
                        <span class="timeline-badge <?php echo $badge_class; ?>">
                            <?php 
                                switch($evento['tipo_evento']) {
                                    case 'inscripcion': echo 'Inscripción'; break;
                                    case 'inicio_clases': echo 'Inicio de Clases'; break;
                                    case 'fin_lapso': echo 'Final de Lapso'; break;
                                    case 'vacaciones': echo 'Vacaciones'; break;
                                    case 'evaluacion': echo 'Evaluaciones'; break;
                                    default: echo 'Evento'; break;
                                }
                            ?>
                        </span>
                        <div class="timeline-date">
                            <i class="far fa-calendar-alt"></i> <?php echo $fecha_texto; ?>
                        </div>
                        <h4 class="timeline-title"><?php echo htmlspecialchars($evento['evento']); ?></h4>
                        <p class="timeline-desc"><?php echo htmlspecialchars($evento['descripcion']); ?></p>
                        
                        <?php if (!empty($nivel_texto)): ?>
                        <div class="event-level">
                            <i class="fas fa-graduation-cap me-1"></i> <?php echo $nivel_texto; ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($evento['periodo_academico'])): ?>
                        <div class="event-level mt-1">
                            <i class="fas fa-clock me-1"></i> <?php echo $evento['periodo_academico']; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="no-events">
                <i class="far fa-calendar-times"></i>
                <h4>No hay eventos programados</h4>
                <p>Próximamente se publicará el calendario académico.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Modal para detalles del evento -->
    <div class="modal fade" id="eventModal" tabindex="-1" aria-labelledby="eventModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="eventModalLabel">Detalles del Evento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php foreach ($eventos as $evento): 
                        $icon = '';
                        $badge_class = '';
                        
                        switch($evento['tipo_evento']) {
                            case 'inscripcion':
                                $icon = 'fa-pencil-alt';
                                $badge_class = 'badge-inscripcion';
                                break;
                            case 'inicio_clases':
                                $icon = 'fa-school';
                                $badge_class = 'badge-inicio';
                                break;
                            case 'fin_lapso':
                                $icon = 'fa-flag-checkered';
                                $badge_class = 'badge-fin';
                                break;
                            case 'vacaciones':
                                $icon = 'fa-umbrella-beach';
                                $badge_class = 'badge-vacaciones';
                                break;
                            case 'evaluacion':
                                $icon = 'fa-clipboard-list';
                                $badge_class = 'badge-evaluacion';
                                break;
                            default:
                                $icon = 'fa-calendar-day';
                                $badge_class = 'badge-otro';
                        }
                        
                        $fecha_inicio = date('d/m/Y', strtotime($evento['fecha_inicio']));
                        $fecha_fin = $evento['fecha_fin'] ? date('d/m/Y', strtotime($evento['fecha_fin'])) : '';
                        
                        $nivel_texto = '';
                        if ($evento['nivel_educativo'] != 'todos') {
                            switch($evento['nivel_educativo']) {
                                case 'inicial': $nivel_texto = 'Educación Inicial'; break;
                                case 'primaria': $nivel_texto = 'Educación Primaria'; break;
                                case 'secundaria': $nivel_texto = 'Educación Secundaria'; break;
                                case 'media': $nivel_texto = 'Educación Media'; break;
                                default: $nivel_texto = 'Todos los niveles'; break;
                            }
                        } else {
                            $nivel_texto = 'Todos los niveles educativos';
                        }
                    ?>
                    <div class="event-details" data-event-id="<?php echo $evento['id']; ?>" style="display: none;">
                        <div class="event-detail-item">
                            <div class="event-detail-icon <?php echo $badge_class; ?>">
                                <i class="fas <?php echo $icon; ?>"></i>
                            </div>
                            <div class="event-detail-content">
                                <div class="event-detail-label">Tipo de Evento</div>
                                <div class="event-detail-value">
                                    <span class="timeline-badge <?php echo $badge_class; ?>">
                                        <?php 
                                            switch($evento['tipo_evento']) {
                                                case 'inscripcion': echo 'Inscripción'; break;
                                                case 'inicio_clases': echo 'Inicio de Clases'; break;
                                                case 'fin_lapso': echo 'Final de Lapso'; break;
                                                case 'vacaciones': echo 'Vacaciones'; break;
                                                case 'evaluacion': echo 'Evaluaciones'; break;
                                                default: echo 'Evento'; break;
                                            }
                                        ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="event-detail-item">
                            <div class="event-detail-icon" style="background-color: #6c757d;">
                                <i class="far fa-calendar-alt"></i>
                            </div>
                            <div class="event-detail-content">
                                <div class="event-detail-label">Fechas</div>
                                <div class="event-detail-value">
                                    <?php echo $fecha_inicio; ?>
                                    <?php if ($fecha_fin && $fecha_fin != $fecha_inicio): ?>
                                     - <?php echo $fecha_fin; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="event-detail-item">
                            <div class="event-detail-icon" style="background-color: #17a2b8;">
                                <i class="fas fa-graduation-cap"></i>
                            </div>
                            <div class="event-detail-content">
                                <div class="event-detail-label">Nivel Educativo</div>
                                <div class="event-detail-value"><?php echo $nivel_texto; ?></div>
                            </div>
                        </div>
                        
                        <?php if (!empty($evento['periodo_academico'])): ?>
                        <div class="event-detail-item">
                            <div class="event-detail-icon" style="background-color: #6f42c1;">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="event-detail-content">
                                <div class="event-detail-label">Período Académico</div>
                                <div class="event-detail-value"><?php echo $evento['periodo_academico']; ?></div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="event-detail-item">
                            <div class="event-detail-icon" style="background-color: #fd7e14;">
                                <i class="fas fa-info-circle"></i>
                            </div>
                            <div class="event-detail-content">
                                <div class="event-detail-label">Descripción</div>
                                <div class="event-detail-value"><?php echo htmlspecialchars($evento['descripcion']); ?></div>
                            </div>
                        </div>
                        
                        <?php if (!empty($evento['observaciones'])): ?>
                        <div class="event-detail-item">
                            <div class="event-detail-icon" style="background-color: #20c997;">
                                <i class="fas fa-sticky-note"></i>
                            </div>
                            <div class="event-detail-content">
                                <div class="event-detail-label">Observaciones</div>
                                <div class="event-detail-value"><?php echo htmlspecialchars($evento['observaciones']); ?></div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
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
        document.addEventListener('DOMContentLoaded', function() {
            // Navegación móvil
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

            // Filtrado de eventos en la línea de tiempo
            const filterButtons = document.querySelectorAll('.filter-button');
            const timelineItems = document.querySelectorAll('.timeline-item');
            const periodoButtons = document.querySelectorAll('.periodo-btn');
            
            // Función para aplicar filtros
            function applyFilters() {
                const activeFilter = document.querySelector('.filter-button.active')?.getAttribute('data-filter') || 'all';
                const activePeriodo = document.querySelector('.periodo-btn.active')?.getAttribute('data-periodo') || 'todos';
                
                timelineItems.forEach(item => {
                    const itemType = item.getAttribute('data-type');
                    const itemPeriodo = item.getAttribute('data-periodo');
                    
                    const typeMatch = activeFilter === 'all' || itemType === activeFilter;
                    const periodoMatch = activePeriodo === 'todos' || itemPeriodo === activePeriodo;
                    
                    if (typeMatch && periodoMatch) {
                        item.style.display = 'block';
                        // Añadir clase para animación
                        setTimeout(() => {
                            item.classList.add('visible');
                        }, 50);
                    } else {
                        item.style.display = 'none';
                        item.classList.remove('visible');
                    }
                });
            }
            
            // Event listeners para botones de filtro
            filterButtons.forEach(button => {
                button.addEventListener('click', function() {
                    filterButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');
                    applyFilters();
                });
            });
            
            // Event listeners para botones de período
            periodoButtons.forEach(button => {
                button.addEventListener('click', function() {
                    periodoButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');
                    applyFilters();
                });
            });
            
            // Configuración del modal de eventos
            const eventModal = document.getElementById('eventModal');
            eventModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const eventId = button.getAttribute('data-event-id');
                
                // Ocultar todos los detalles de eventos
                const allEventDetails = eventModal.querySelectorAll('.event-details');
                allEventDetails.forEach(detail => {
                    detail.style.display = 'none';
                });
                
                // Mostrar solo el evento seleccionado
                const selectedEvent = eventModal.querySelector(`.event-details[data-event-id="${eventId}"]`);
                if (selectedEvent) {
                    selectedEvent.style.display = 'block';
                    
                    // Actualizar título del modal
                    const modalTitle = eventModal.querySelector('.modal-title');
                    const eventTitle = button.closest('.timeline-content').querySelector('.timeline-title').textContent;
                    modalTitle.textContent = eventTitle;
                }
            });
            
            // Animación de aparición de elementos al hacer scroll
            function checkVisibility() {
                timelineItems.forEach(item => {
                    const position = item.getBoundingClientRect();
                    
                    // Si el elemento está en el viewport
                    if(position.top < window.innerHeight && position.bottom >= 0) {
                        setTimeout(() => {
                            item.classList.add('visible');
                        }, 100);
                    }
                });
            }
            
            // Verificar visibilidad al cargar y al hacer scroll
            window.addEventListener('scroll', checkVisibility);
            checkVisibility(); // Verificar al cargar la página
            
            // Aplicar filtros iniciales
            applyFilters();
        });
    </script>
</body>
</html>