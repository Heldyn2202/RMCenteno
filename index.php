<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
    <title>U.E. Roberto Martinez Centeno - Institución Educativa</title>
    
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
        
        /* Slider Styles */
        .hm-slider {
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            height: 500px;
        }
        
        .hm-slides-container {
            position: relative;
            height: 100%;
            width: 100%;
        }
        
        .hm-slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            transition: opacity 0.5s ease-in-out;
        }
        
        .hm-slide.active {
            opacity: 1;
        }
        
        .hm-slide-image {
            width: 100%;
            height: 100%;
        }
        
        .hm-slide-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .hm-fp-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to bottom, transparent 0%, rgba(0,0,0,0.7) 100%);
        }
        
        .hm-slide-content {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            padding: 40px;
            color: var(--white);
            z-index: 2;
        }
        
        .hm-slider-title {
            font-size: 32px;
            margin-bottom: 10px;
            color: var(--white);
            font-weight: 600;
        }
        
        /* Controles del carrusel */
        .hm-slider-controls {
            position: absolute;
            bottom: 20px;
            left: 0;
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 10;
        }
        
        .hm-slider-dots {
            display: flex;
            gap: 8px;
        }
        
        .hm-slider-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.5);
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        
        .hm-slider-dot.active {
            background-color: var(--white);
        }
        
        .hm-slider-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 100%;
            display: flex;
            justify-content: space-between;
            padding: 0 20px;
            z-index: 10;
        }
        
        .hm-slider-prev, .hm-slider-next {
            background-color: rgba(255, 255, 255, 0.2);
            color: var(--white);
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        
        .hm-slider-prev:hover, .hm-slider-next:hover {
            background-color: rgba(255, 255, 255, 0.4);
        }
        
        /* Ejes de Gestión */
        .ejes-section {
            padding: 60px 0;
            background-color: var(--light-bg);
        }
        
        .ejes-title {
            text-align: center;
            margin-bottom: 40px;
            color: var(--primary-color);
            font-weight: 600;
        }
        
        .ejes-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 20px;
        }
        
        .eje-card {
            background: var(--white);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            text-align: center;
            padding: 20px;
        }
        
        .eje-card:hover {
            transform: translateY(-5px);
        }
        
        .eje-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 15px;
            background-color: var(--light-bg);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .eje-icon i {
            font-size: 32px;
            color: var(--primary-color);
        }
        
        .eje-title {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-color);
            line-height: 1.4;
        }
        
        /* Noticias Section */
        .noticias-section {
            padding: 60px 0;
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 40px;
            color: var(--primary-color);
            font-weight: 600;
        }
        
        .noticias-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
        }
        
        .noticia-card {
            background: var(--white);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease;
        }
        
        .noticia-card:hover {
            transform: translateY(-5px);
        }
        
        .noticia-image {
            height: 200px;
            overflow: hidden;
        }
        
        .noticia-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .noticia-card:hover .noticia-image img {
            transform: scale(1.05);
        }
        
        .noticia-content {
            padding: 20px;
        }
        
        .noticia-title {
            font-size: 18px;
            margin-bottom: 10px;
            line-height: 1.4;
        }
        
        .noticia-title a {
            color: var(--text-color);
        }
        
        .noticia-title a:hover {
            color: var(--accent-color);
        }
        
        .noticia-meta {
            font-size: 13px;
            color: var(--text-light);
            margin-bottom: 10px;
        }
        
        .noticia-excerpt {
            font-size: 14px;
            color: var(--text-light);
            margin-bottom: 15px;
        }
        
        .read-more {
            color: var(--primary-color);
            font-weight: 500;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .read-more:hover {
            color: var(--secondary-color);
        }
        
        /* Entes Section */
        .entes-section {
            padding: 60px 0;
            background-color: var(--light-bg);
        }
        
        .entes-carousel {
            display: flex;
            overflow-x: auto;
            gap: 20px;
            padding: 20px 0;
            scrollbar-width: none;
        }
        
        .entes-carousel::-webkit-scrollbar {
            display: none;
        }
        
        .ente-logo {
            flex: 0 0 auto;
            width: 150px;
            height: 80px;
            background: var(--white);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }
        
        .ente-logo:hover {
            transform: translateY(-3px);
        }
        
        .ente-logo img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
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
        
        /* ===========================================
           FOOTER COMPACTO (Mismo estilo que Quiénes Somos)
           =========================================== */
        
        .site-footer {
            background-color: #1a1a1a; /* Negro más oscuro */
            color: var(--white);
            padding: 20px 0 10px;
        }
        
        .footer-main {
            padding: 0;
        }
        
        /* Contenedor principal más compacto */
        .footer-main .container {
            padding: 0 10px;
        }
        
        /* Descripción - texto justificado */
        .footer-main p {
            font-size: 0.9rem !important;
            line-height: 1.5 !important;
            color: rgba(255, 255, 255, 0.9) !important;
            margin-bottom: 10px !important;
        }
        
        /* Información de contacto COMPACTA */
        .contact-info {
            font-size: 0.8rem !important;
            color: rgba(255, 255, 255, 0.9);
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .contact-info span {
            display: inline-flex;
            align-items: center;
            white-space: nowrap;
        }
        
        .contact-info i {
            font-size: 0.8rem;
            color: #4a8cff; /* Azul para los iconos */
        }
        
        .contact-info a {
            color: white !important;
            text-decoration: none !important;
            transition: color 0.3s ease;
        }
        
        .contact-info a:hover {
            color: #4a8cff !important;
            text-decoration: underline !important;
        }
        
        /* REDES SOCIALES centradas */
        .footer-social {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin: 10px 0;
        }
        
        .footer-social .social-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: rgba(255,255,255,0.1);
            color: var(--white);
            font-size: 0.85rem;
            transition: all 0.3s ease;
        }
        
        .footer-social .social-icon:hover {
            background-color: #4a8cff; /* Azul al hover */
            color: white;
            transform: translateY(-2px);
        }
        
        /* Derechos y créditos en una línea */
        .footer-bottom-content {
            font-size: 0.75rem;
            color: rgba(255, 255, 255, 0.8);
            padding-top: 8px;
            border-top: 1px solid rgba(255,255,255,0.1);
            margin-top: 8px;
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .ejes-grid {
                grid-template-columns: repeat(3, 1fr);
            }
            
            .noticias-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
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
            
            /* Footer responsive */
            .site-footer {
                padding: 15px 0 8px;
            }
        }
        
        @media (max-width: 768px) {
            .ejes-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .noticias-grid {
                grid-template-columns: 1fr;
            }
            
            .hm-slider {
                height: 400px;
            }
            
            .hm-slider-title {
                font-size: 24px;
            }
            
            .hm-slider-nav {
                display: none;
            }
            
            /* Footer responsive para tablet */
            .footer-main p {
                font-size: 0.85rem !important;
                line-height: 1.4 !important;
                margin-bottom: 8px !important;
            }
            
            .contact-info {
                font-size: 0.75rem !important;
                flex-direction: column;
                gap: 5px;
                align-items: center;
            }
            
            .contact-info span {
                margin-right: 0 !important;
                margin-bottom: 4px;
            }
            
            .footer-social {
                gap: 6px;
                margin: 8px 0;
            }
            
            .footer-social .social-icon {
                width: 28px;
                height: 28px;
                font-size: 0.8rem;
            }
            
            .footer-bottom-content {
                font-size: 0.7rem;
                padding-top: 6px;
                margin-top: 6px;
            }
        }
        
        @media (max-width: 576px) {
            .ejes-grid {
                grid-template-columns: 1fr;
            }
            
            .hm-slider {
                height: 300px;
            }
            
            .hm-slide-content {
                padding: 20px;
            }
            
            /* Footer responsive para móviles */
            .footer-bottom-content {
                display: flex;
                flex-direction: column;
                gap: 3px;
            }
            
            .footer-bottom-content span:nth-child(2) {
                display: none; /* Oculta el separador "|" en móviles */
            }
        }
    </style>
</head>

<body>
    <?php
    // CONEXIÓN A BASE DE DATOS Y CONSULTAS
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

    // Obtener elementos del carrusel que estén activos y dentro del rango de fechas
    $currentDate = date('Y-m-d');
    $carruselQuery = "SELECT * FROM carrusel 
                     WHERE activo = 1 
                     AND fecha_inicio <= '$currentDate' 
                     AND fecha_fin >= '$currentDate' 
                     ORDER BY fecha_creacion DESC";
    $carruselResult = mysqli_query($con, $carruselQuery);
    $carruselItems = [];
    $carruselCount = 0;

    if ($carruselResult) {
        $carruselItems = mysqli_fetch_all($carruselResult, MYSQLI_ASSOC);
        $carruselCount = count($carruselItems);
    }

    // Configuración de paginación para posts
    if (isset($_GET['pageno'])) {
        $pageno = $_GET['pageno'];
    } else {
        $pageno = 1;
    }
    $no_of_records_per_page = 6;
    $offset = ($pageno-1) * $no_of_records_per_page;

    $total_pages_sql = "SELECT COUNT(*) FROM tblposts WHERE Is_Active=1";
    $result = mysqli_query($con,$total_pages_sql);
    $total_rows = mysqli_fetch_array($result)[0];
    $total_pages = ceil($total_rows / $no_of_records_per_page);

    // Obtener posts para la página actual
    $query = mysqli_query($con,"SELECT tblposts.id as pid,tblposts.PostTitle as posttitle,tblposts.PostImage,tblcategory.CategoryName as category,tblcategory.id as cid,tblsubcategory.Subcategory as subcategory,tblposts.PostDetails as postdetails,tblposts.PostingDate as postingdate,tblposts.PostUrl as url FROM tblposts LEFT JOIN tblcategory ON tblcategory.id=tblposts.CategoryId LEFT JOIN tblsubcategory ON tblsubcategory.SubCategoryId=tblposts.SubCategoryId WHERE tblposts.Is_Active=1 ORDER BY tblposts.id DESC LIMIT $offset, $no_of_records_per_page");
    $posts = [];
    if ($query) {
        $posts = mysqli_fetch_all($query, MYSQLI_ASSOC);
    }

    // Obtener categorías
    $categoriesQuery = "SELECT * FROM tblcategory WHERE Is_Active = 1 ORDER BY CategoryName";
    $categoriesResult = mysqli_query($con, $categoriesQuery);
    $categories = [];
    if ($categoriesResult) {
        $categories = mysqli_fetch_all($categoriesResult, MYSQLI_ASSOC);
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
                        <h4 class="site-title"><a href="index.php">U.E. Roberto Martinez Centeno</a></h2>
                    </div>
                    
                    <nav class="main-navigation">
                        <ul>
                            <li class="current-menu-item"><a href="index.php">Inicio</a></li>
                            <li><a href="quienes-somos.php">Quiénes Somos</a></li>
                            <li><a href="noticias.php">Noticias</a></li>
                            <li><a href="academico.php">Académico</a></li>
                            <li><a href="calendario.php">Calendario</a></li>
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
                        <a href="admin/login/login.php" class="hm-cta-btn">Login</a>
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
                    <li class="current-menu-item"><a href="index.php">Inicio</a></li>
                    <li><a href="quienes-somos.php">Quiénes Somos</a></li>
                    <li><a href="noticias.php">Noticias</a></li>
                    <li><a href="academico.php">Académico</a></li>
                    <li><a href="calendario.php">Calendario</a></li>
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

   <!-- Slider Principal -->
<div class="hm-slider">
    <div class="hm-slides-container">
        <?php if ($carruselCount > 0): ?>
            <?php foreach ($carruselItems as $index => $item): 
                // ¡¡¡RUTA CORREGIDA!!!
                $ruta_imagen = '/heldyn/centeno/uploads/carrusel/' . $item['imagen_path'];
            ?>
            <div class="hm-slide <?php echo $index === 0 ? 'active' : ''; ?>">
                <div class="hm-slide-image">
                    <img src="<?php echo $ruta_imagen; ?>" 
                         alt="<?php echo htmlentities($item['titulo']); ?>" 
                         onerror="this.onerror=null; this.src='https://placehold.co/1200x500/1a4b8c/white?text=Portal+Escolar'; console.error('Error cargando imagen:', '<?php echo addslashes($ruta_imagen); ?>')">
                </div>
                <div class="hm-fp-overlay"></div>
                <div class="hm-slide-content">
                    <div class="hm-slider-details-container">
                        <h2 class="hm-slider-title"><?php echo htmlentities($item['titulo']); ?></h2>
                        <?php if (!empty($item['descripcion'])): ?>
                            <p><?php echo htmlentities($item['descripcion']); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <!-- Slides por defecto si no hay carrusel -->
            <div class="hm-slide active">
                <div class="hm-slide-image">
                    <img src="https://placehold.co/1200x500/1a4b8c/white?text=Portal+Escolar" alt="Portal Escolar">
                </div>
                <div class="hm-fp-overlay"></div>
                <div class="hm-slide-content">
                    <div class="hm-slider-details-container">
                        <h2 class="hm-slider-title">Bienvenido al Portal Escolar</h2>
                        <p>Innovación educativa para el desarrollo integral de nuestros estudiantes</p>
                    </div>
                </div>
            </div>
            
            <div class="hm-slide">
                <div class="hm-slide-image">
                    <img src="https://placehold.co/1200x500/2d68c4/white?text=Educación+de+Calidad" alt="Educación de Calidad">
                </div>
                <div class="hm-fp-overlay"></div>
                <div class="hm-slide-content">
                    <div class="hm-slider-details-container">
                        <h2 class="hm-slider-title">Educación de Calidad para Todos</h2>
                        <p>Recursos y herramientas para el aprendizaje integral</p>
                    </div>
                </div>
            </div>
            
            <div class="hm-slide">
                <div class="hm-slide-image">
                    <img src="https://placehold.co/1200x500/1a2238/white?text=Comunidad+Educativa" alt="Comunidad Educativa">
                </div>
                <div class="hm-fp-overlay"></div>
                <div class="hm-slide-content">
                    <div class="hm-slider-details-container">
                        <h2 class="hm-slider-title">Comunidad Educativa Activa</h2>
                        <p>Padres, estudiantes y docentes trabajando juntos</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Controles del carrusel -->
    <?php if ($carruselCount > 1): ?>
    <div class="hm-slider-nav">
        <button class="hm-slider-prev">
            <i class="fas fa-chevron-left"></i>
        </button>
        <button class="hm-slider-next">
            <i class="fas fa-chevron-right"></i>
        </button>
    </div>
    
    <div class="hm-slider-controls">
        <div class="hm-slider-dots">
            <?php for ($i = 0; $i < $carruselCount; $i++): ?>
            <div class="hm-slider-dot <?php echo $i === 0 ? 'active' : ''; ?>" data-index="<?php echo $i; ?>"></div>
            <?php endfor; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Script de depuración para el carrusel -->
<script>
console.log('🔍 DEPURACIÓN CARRUSEL PRINCIPAL');
<?php if ($carruselCount > 0): ?>
    <?php foreach ($carruselItems as $index => $item): ?>
        const img<?php echo $index; ?> = new Image();
        img<?php echo $index; ?>.onload = function() {
            console.log('✅ Slide <?php echo $index + 1; ?> cargado: <?php echo addslashes($item['titulo']); ?>');
        };
        img<?php echo $index; ?>.onerror = function() {
            console.error('❌ Error slide <?php echo $index + 1; ?>: /heldyn/centeno/uploads/carrusel/<?php echo addslashes($item['imagen_path']); ?>');
        };
        img<?php echo $index; ?>.src = '/heldyn/centeno/uploads/carrusel/<?php echo addslashes($item['imagen_path']); ?>';
    <?php endforeach; ?>
<?php endif; ?>
</script>
    <!-- Ejes de Gestión -->
    <section class="ejes-section">
        <div class="container">
            <h2 class="ejes-title">Nuestros Pilares Educativos</h2>
            <div class="ejes-grid">
                <div class="eje-card">
                    <div class="eje-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <h3 class="eje-title">Excelencia Académica</h3>
                </div>
                
                <div class="eje-card">
                    <div class="eje-icon">
                        <i class="fas fa-university"></i>
                    </div>
                    <h3 class="eje-title">Formación Integral</h3>
                </div>
                
                <div class="eje-card">
                    <div class="eje-icon">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <h3 class="eje-title">Valores y Convivencia</h3>
                </div>
                
                <div class="eje-card">
                    <div class="eje-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 class="eje-title">Comunidad Educativa</h3>
                </div>
                
                <div class="eje-card">
                    <div class="eje-icon">
                        <i class="fas fa-laptop-code"></i>
                    </div>
                    <h3 class="eje-title">Tecnología Educativa</h3>
                </div>
            </div>
        </div>
    </section>

    <!-- Noticias -->
    <section class="noticias-section">
        <div class="container">
            <h2 class="section-title">Noticias y Avisos</h2>
            <div class="noticias-grid">
                <?php if (count($posts) > 0): ?>
                    <?php foreach ($posts as $post): ?>
                    <article class="noticia-card">
                        <div class="noticia-image">
                            <a href="news-details.php?nid=<?php echo htmlentities($post['pid'])?>">
                                <img src="admin/uploads/post/<?php echo htmlentities($post['PostImage']);?>" alt="<?php echo htmlentities($post['posttitle']);?>" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZGRkIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCwgc2Fucy1zZXJpZiIgZm9udC1zaXplPSIxNCIgZmlsbD0iIzk5OSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPkltYWdlbiBubyBkaXNwb25pYmxlPC90ZXh0Pjwvc3ZnPg=='">
                            </a>
                        </div>
                        <div class="noticia-content">
                            <h3 class="noticia-title">
                                <a href="news-details.php?nid=<?php echo htmlentities($post['pid'])?>">
                                    <?php echo htmlentities($post['posttitle']);?>
                                </a>
                            </h3>
                            <div class="noticia-meta">
                                <span class="fecha"><?php echo htmlentities($post['postingdate']);?></span>
                                <span class="categoria"> | <?php echo htmlentities($post['category']);?></span>
                            </div>
                            <div class="noticia-excerpt">
                                <p><?php echo substr(strip_tags(htmlspecialchars_decode($post['postdetails'])), 0, 150); ?>...</p>
                            </div>
                            <a href="news-details.php?nid=<?php echo htmlentities($post['pid'])?>" class="read-more">
                                Leer más <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Noticias de ejemplo si no hay en la BD -->
                    <article class="noticia-card">
                        <div class="noticia-image">
                            <img src="https://placehold.co/400x200/1a4b8c/white?text=Inscripciones" alt="Inscripciones Abiertas">
                        </div>
                        <div class="noticia-content">
                            <h3 class="noticia-title">
                                <a href="#">Inscripciones abiertas para el nuevo año escolar</a>
                            </h3>
                            <div class="noticia-meta">
                                <span class="fecha">15/10/2024</span>
                                <span class="categoria"> | Admisiones</span>
                            </div>
                            <div class="noticia-excerpt">
                                <p>Ya están abiertas las inscripciones para el próximo año escolar. Conoce los requisitos y fechas importantes.</p>
                            </div>
                            <a href="#" class="read-more">
                                Leer más <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </article>
                    
                    <article class="noticia-card">
                        <div class="noticia-image">
                            <img src="https://placehold.co/400x200/2d68c4/white?text=Talleres" alt="Talleres para Padres">
                        </div>
                        <div class="noticia-content">
                            <h3 class="noticia-title">
                                <a href="#">Talleres para padres sobre educación emocional</a>
                            </h3>
                            <div class="noticia-meta">
                                <span class="fecha">12/10/2024</span>
                                <span class="categoria"> | Formación</span>
                            </div>
                            <div class="noticia-excerpt">
                                <p>Invitamos a todos los padres de familia a participar en nuestros talleres sobre educación emocional en casa.</p>
                            </div>
                            <a href="#" class="read-more">
                                Leer más <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </article>
                    
                    <article class="noticia-card">
                        <div class="noticia-image">
                            <img src="https://placehold.co/400x200/1a2238/white?text=Logros" alt="Logros Estudiantiles">
                        </div>
                        <div class="noticia-content">
                            <h3 class="noticia-title">
                                <a href="#">Reconocimiento a estudiantes destacados</a>
                            </h3>
                            <div class="noticia-meta">
                                <span class="fecha">10/10/2024</span>
                                <span class="categoria"> | Reconocimientos</span>
                            </div>
                            <div class="noticia-excerpt">
                                <p>Felicitamos a nuestros estudiantes que obtuvieron reconocimientos en competencias académicas regionales.</p>
                            </div>
                            <a href="#" class="read-more">
                                Leer más <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </article>
                <?php endif; ?>
            </div>
            
            <!-- Botón para ver más noticias -->
            <div class="text-center mt-4">
                <a href="noticias.php" class="btn btn-primary">Ver todas las noticias</a>
            </div>
        </div>
    </section>

    <!-- Sección de Departamentos -->
    <section class="entes-section">
        <div class="container">
            <h2 class="section-title">Departamentos y Áreas</h2>
            <div class="entes-carousel">
                <!-- Departamentos de ejemplo -->
                <div class="ente-logo">
                    <a href="#">
                        <img src="https://placehold.co/120x60/1a4b8c/white?text=Matemáticas" alt="Matemáticas">
                    </a>
                </div>
                <div class="ente-logo">
                    <a href="#">
                        <img src="https://placehold.co/120x60/2d68c4/white?text=Ciencias" alt="Ciencias">
                    </a>
                </div>
                <div class="ente-logo">
                    <a href="#">
                        <img src="https://placehold.co/120x60/1a2238/white?text=Lenguaje" alt="Lenguaje">
                    </a>
                </div>
                <div class="ente-logo">
                    <a href="#">
                        <img src="https://placehold.co/120x60/1a4b8c/white?text=Sociales" alt="Sociales">
                    </a>
                </div>
                <div class="ente-logo">
                    <a href="#">
                        <img src="https://placehold.co/120x60/2d68c4/white?text=Arte" alt="Arte">
                    </a>
                </div>
                <div class="ente-logo">
                    <a href="#">
                        <img src="https://placehold.co/120x60/1a2238/white?text=Deportes" alt="Deportes">
                    </a>
                </div>
                <div class="ente-logo">
                    <a href="#">
                        <img src="https://placehold.co/120x60/1a2238/white?text=Quimica" alt="Química">
                    </a>
                </div>
            </div>
        </div>
    </section>

    <?php
// Obtener configuración del footer desde la base de datos
$footerQuery = "SELECT * FROM footer_config WHERE id = 1";
$footerResult = mysqli_query($con, $footerQuery);
$footer_config = mysqli_fetch_assoc($footerResult);

if (!$footer_config) {
    // Valores por defecto
    $footer_config = [
        'titulo_izquierda' => 'Portal Escolar',
        'direccion' => 'Caricuso, Urbanización García',
        'email' => 'RobertoVC@gmail.com',
        'telefono' => '021223392',
        'titulo_derecha' => 'Portal Escolar',
        'descripcion_derecha' => 'El Portal Escolar es la plataforma oficial de comunicación e información educativa, dedicada a promover la innovación y el desarrollo integral en el ámbito educativo. Nuestro compromiso es brindar recursos, herramientas y contenidos de calidad para fortalecer el proceso de enseñanza-aprendizaje de nuestra comunidad educativa.',
        'derechos_autor' => '© [año] Portal Escolar | Institución Educativa',
        'creditos' => 'Desarrollado para la comunidad educativa'
    ];
}

// Reemplazar [año] por el año actual
$derechos_actual = str_replace('[año]', date('Y'), $footer_config['derechos_autor']);
?>

<!-- Footer Compacto y Elegante (Mismo estilo que Quiénes Somos) -->
<footer class="site-footer">
    <div class="footer-main">
        <div class="container">
            <!-- Primera fila: Descripción -->
            <div class="row mb-3">
                <div class="col-12">
                    <p style="font-size: 0.9rem; line-height: 1.5; text-align: justify; color: rgba(255,255,255,0.9); margin: 0 5px 8px 5px;">
                        <?php echo nl2br(htmlspecialchars($footer_config['descripcion_derecha'])); ?>
                    </p>
                </div>
            </div>
            
            <!-- Línea divisoria -->
            <div style="border-top: 1px solid rgba(255,255,255,0.15); margin: 10px auto; max-width: 500px;"></div>
            
            <!-- Redes Sociales Centradas -->
            <div class="row mb-3">
                <div class="col-12 text-center">
                    <div class="footer-social">
                        <?php foreach ($social_media as $social): ?>
                        <a href="<?php echo $social['url']; ?>" target="_blank" class="social-icon">
                            <?php if ($social['icon_type'] == 'fontawesome'): ?>
                                <i class="<?php echo $social['icon']; ?>"></i>
                            <?php else: ?>
                                <img src="admin/<?php echo $social['icon']; ?>" width="12" height="12">
                            <?php endif; ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <!-- Información de contacto COMPACTA -->
            <div class="row mb-3">
                <div class="col-12">
                    <div class="contact-info text-center" style="font-size: 0.8rem;">
                        <?php if (!empty($footer_config['direccion'])): ?>
                            <span style="margin-right: 15px;">
                                <i class="fas fa-map-marker-alt" style="margin-right: 5px;"></i>
                                <?php echo htmlspecialchars($footer_config['direccion']); ?>
                            </span>
                        <?php endif; ?>
                        
                        <?php if (!empty($footer_config['email'])): ?>
                            <span style="margin-right: 15px;">
                                <i class="fas fa-envelope" style="margin-right: 5px;"></i>
                                <a href="mailto:<?php echo htmlspecialchars($footer_config['email']); ?>" style="color: white; text-decoration: none;">
                                    <?php echo htmlspecialchars($footer_config['email']); ?>
                                </a>
                            </span>
                        <?php endif; ?>
                        
                        <?php if (!empty($footer_config['telefono'])): ?>
                            <span>
                                <i class="fas fa-phone" style="margin-right: 5px;"></i>
                                <a href="tel:<?php echo htmlspecialchars($footer_config['telefono']); ?>" style="color: white; text-decoration: none;">
                                    <?php echo htmlspecialchars($footer_config['telefono']); ?>
                                </a>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Derechos y créditos EN LA MISMA LÍNEA -->
            <div class="row">
                <div class="col-12">
                    <div class="footer-bottom-content text-center" style="font-size: 0.75rem; color: rgba(255,255,255,0.8); padding-top: 8px; border-top: 1px solid rgba(255,255,255,0.1); margin-top: 8px;">
                        <span><?php echo htmlspecialchars($derechos_actual); ?></span>
                        <span style="margin: 0 10px;">|</span>
                        <span><?php echo htmlspecialchars($footer_config['creditos']); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Carrusel personalizado
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar carrusel
            const slider = document.querySelector('.hm-slider');
            if (!slider) return;
            
            const slides = slider.querySelectorAll('.hm-slide');
            const dots = slider.querySelectorAll('.hm-slider-dot');
            const prevBtn = slider.querySelector('.hm-slider-prev');
            const nextBtn = slider.querySelector('.hm-slider-next');
            
            let currentSlide = 0;
            let slideInterval;
            
            // Función para mostrar un slide específico
            function showSlide(index) {
                // Ocultar todos los slides
                slides.forEach(slide => slide.classList.remove('active'));
                if (dots.length > 0) {
                    dots.forEach(dot => dot.classList.remove('active'));
                }
                
                // Mostrar el slide actual
                slides[index].classList.add('active');
                if (dots[index]) {
                    dots[index].classList.add('active');
                }
                
                currentSlide = index;
            }
            
            // Función para mostrar el siguiente slide
            function nextSlide() {
                let nextIndex = (currentSlide + 1) % slides.length;
                showSlide(nextIndex);
            }
            
            // Función para mostrar el slide anterior
            function prevSlide() {
                let prevIndex = (currentSlide - 1 + slides.length) % slides.length;
                showSlide(prevIndex);
            }
            
            // Iniciar autoplay
            function startAutoPlay() {
                if (slides.length > 1) {
                    slideInterval = setInterval(nextSlide, 5000);
                }
            }
            
            // Detener autoplay
            function stopAutoPlay() {
                clearInterval(slideInterval);
            }
            
            // Event listeners para controles
            if (nextBtn) {
                nextBtn.addEventListener('click', function() {
                    stopAutoPlay();
                    nextSlide();
                    startAutoPlay();
                });
            }
            
            if (prevBtn) {
                prevBtn.addEventListener('click', function() {
                    stopAutoPlay();
                    prevSlide();
                    startAutoPlay();
                });
            }
            
            // Event listeners para dots
            dots.forEach((dot, index) => {
                dot.addEventListener('click', function() {
                    stopAutoPlay();
                    showSlide(index);
                    startAutoPlay();
                });
            });
            
            // Iniciar autoplay si hay más de un slide
            if (slides.length > 1) {
                startAutoPlay();
                
                // Pausar autoplay al pasar el mouse
                slider.addEventListener('mouseenter', stopAutoPlay);
                slider.addEventListener('mouseleave', startAutoPlay);
            }
            
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
        });
    </script>
</body>
</html>