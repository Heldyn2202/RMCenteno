<?php
// quienes-somos.php - PORTAL ESCOLAR

// ================= CONEXIÓN A BD =================
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sige";

$con = new mysqli($servername, $username, $password, $dbname);
if ($con->connect_error) {
    die("Error de conexión: " . $con->connect_error);
}
// =================================================

session_start();

// Obtener información principal de "Quiénes Somos"
$query = "SELECT * FROM quienes_somos WHERE id = 1";
$result = mysqli_query($con, $query);
$quienes_somos = mysqli_fetch_assoc($result);

if (!$quienes_somos) {
    // Si no existe, crear un array vacío para evitar errores
    $quienes_somos = [
        'titulo' => 'Quiénes Somos',
        'contenido' => '',
        'imagen_principal' => '',
        'imagen_principal_alt' => '',
        'mision' => '',
        'vision' => '',
        'valores' => ''
    ];
}

// Obtener equipo directivo activo
$query = "SELECT * FROM equipo_quienes_somos WHERE activo = 1 ORDER BY orden, nombre LIMIT 4";
$result = mysqli_query($con, $query);
$equipo = [];
if ($result) {
    $equipo = mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Obtener colaboradores activos
$query = "SELECT * FROM colaboradores_quienes_somos WHERE activo = 1 ORDER BY orden, nombre";
$result = mysqli_query($con, $query);
$colaboradores = [];
if ($result) {
    $colaboradores = mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Obtener redes sociales para el header/footer
$socialQuery = "SELECT * FROM social_media WHERE status = 1 ORDER BY name";
$socialResult = mysqli_query($con, $socialQuery);
$social_media = [];
if ($socialResult) {
    $social_media = mysqli_fetch_all($socialResult, MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
    <title>Quiénes Somos - <?php echo htmlspecialchars($quienes_somos['titulo']); ?></title>
    
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
            --footer-bg: #222222;
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

        /* Header Styles */
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
        
        /* Estilos específicos para Quiénes Somos */
        .hero-section {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: var(--white);
            padding: 100px 0 80px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="none"><path d="M0,0 L100,0 L100,100 Z" fill="rgba(255,255,255,0.1)"/></svg>');
            background-size: cover;
        }
        
        .hero-section h1 {
            color: var(--white);
            font-size: 3.2rem;
            margin-bottom: 15px;
            font-weight: 700;
            text-shadow: 0 2px 10px rgba(0,0,0,0.2);
            position: relative;
            z-index: 1;
        }
        
        .hero-section .subtitulo {
            color: rgba(255,255,255,0.9);
            font-size: 1.3rem;
            max-width: 700px;
            margin: 0 auto;
            font-weight: 300;
            position: relative;
            z-index: 1;
        }
        
        .content-section {
            padding: 80px 0;
        }
        
        .content-image {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            transition: transform 0.5s ease;
        }
        
        .content-image:hover {
            transform: translateY(-10px);
        }
        
        .content-image img {
            width: 100%;
            height: 450px;
            object-fit: cover;
        }
        
        /* Contenido de Quiénes Somos profesional - VUELTA A LA VERSIÓN ANTERIOR */
        .content-text {
            background-color: var(--white);
            padding: 50px;
            border-radius: 12px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.08);
            font-size: 1.15rem;
            line-height: 1.8;
            text-align: justify;
            border-left: 5px solid var(--primary-color);
            position: relative;
            /* ELIMINADO: height, display, flex, justify-content, overflow-y */
        }
        
        .content-text::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, rgba(26, 75, 140, 0.05), transparent);
            border-radius: 0 12px 0 0;
        }
        
        .content-text h2 {
            color: var(--primary-color);
            margin-bottom: 30px;
            text-align: center;
            font-size: 2rem;
            position: relative;
            padding-bottom: 15px;
        }
        
        .content-text h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            border-radius: 2px;
        }
        
        .content-text p {
            margin-bottom: 25px;
            line-height: 1.9;
            color: #444;
            text-align: justify;
        }
        
        .content-text p:last-child {
            margin-bottom: 0;
        }
        
        .content-text .highlight {
            background: linear-gradient(120deg, rgba(26, 75, 140, 0.1), transparent);
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid var(--primary-color);
            margin: 25px 0;
            font-style: italic;
        }
        
        .mision-vision-section {
            background-color: var(--light-bg);
            padding: 80px 0;
            position: relative;
            overflow: hidden;
        }
        
        .mision-vision-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="none" opacity="0.03"><path d="M0,50 Q25,40 50,50 T100,50 L100,100 L0,100 Z" fill="%231a4b8c"/></svg>');
            background-size: cover;
        }
        
        .mission-card, .vision-card {
            background: var(--white);
            padding: 45px 40px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            height: 100%;
            text-align: center;
            transition: transform 0.4s ease, box-shadow 0.4s ease;
            position: relative;
            z-index: 1;
            border-top: 4px solid var(--primary-color); /* MISMO COLOR AZUL */
        }
        
        .mission-card:hover, .vision-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.12);
        }
        
        .mission-card i {
            color: var(--primary-color);
            font-size: 3.5rem;
            margin-bottom: 25px;
            background: linear-gradient(135deg, rgba(26, 75, 140, 0.1), transparent);
            width: 90px;
            height: 90px;
            line-height: 90px;
            border-radius: 50%;
            display: inline-block;
        }
        
        .vision-card i {
            color: var(--primary-color); /* Mismo color que misión */
            font-size: 3.5rem;
            margin-bottom: 25px;
            background: linear-gradient(135deg, rgba(26, 75, 140, 0.1), transparent);
            width: 90px;
            height: 90px;
            line-height: 90px;
            border-radius: 50%;
            display: inline-block;
        }
        
        .mission-card h3, .vision-card h3 {
            font-size: 1.8rem;
            margin-bottom: 25px;
            color: var(--text-color);
            position: relative;
            padding-bottom: 15px;
        }
        
        .mission-card h3::after, .vision-card h3::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 50px;
            height: 3px;
            background-color: var(--primary-color);
            border-radius: 2px;
        }
        
        .mission-card p, .vision-card p {
            font-size: 1.1rem;
            line-height: 1.8;
            color: #555;
            text-align: justify;
            margin-bottom: 0;
        }
        
        .values-section {
            padding: 80px 0;
            background: linear-gradient(135deg, #f8fafc, #f1f5f9);
        }
        
        .values-section h2 {
            font-size: 2.2rem;
            margin-bottom: 50px;
            color: var(--primary-color);
            position: relative;
            padding-bottom: 15px;
        }
        
        .values-section h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            border-radius: 2px;
        }
        
        .value-item {
            text-align: center;
            padding: 30px 20px;
            background: var(--white);
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            transition: all 0.4s ease;
            height: 100%;
            border-bottom: 4px solid transparent;
        }
        
        .value-item:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 35px rgba(0,0,0,0.1);
            border-bottom-color: var(--primary-color);
        }
        
        .value-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            color: var(--white);
            font-size: 2.5rem;
            box-shadow: 0 10px 20px rgba(26, 75, 140, 0.2);
        }
        
        .value-item h4 {
            font-size: 1.3rem;
            color: var(--text-color);
            margin-bottom: 15px;
            font-weight: 600;
        }
        
        .value-item p {
            color: var(--text-light);
            font-size: 0.95rem;
            line-height: 1.6;
        }
        
        .team-section {
            background-color: var(--light-bg);
            padding: 80px 0;
        }
        
        .team-section h2 {
            font-size: 2.2rem;
            margin-bottom: 50px;
            color: var(--primary-color);
        }
        
        .team-member {
            background: var(--white);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0,0,0,0.08);
            transition: all 0.4s ease;
            text-align: center;
            margin-bottom: 30px;
            position: relative;
            border-top: 5px solid transparent;
        }
        
        .team-member:hover {
            transform: translateY(-15px);
            box-shadow: 0 25px 50px rgba(0,0,0,0.15);
            border-top-color: var(--primary-color);
        }
        
        .team-member-img {
            height: 220px;
            width: 220px;
            overflow: hidden;
            border-radius: 50%;
            margin: 35px auto 25px;
            border: 8px solid var(--white);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            position: relative;
        }
        
        .team-member-img::before {
            content: '';
            position: absolute;
            top: -8px;
            left: -8px;
            right: -8px;
            bottom: -8px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            z-index: -1;
        }
        
        .team-member-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .team-member-info {
            padding: 0 30px 30px;
        }
        
        .team-member-name {
            font-size: 1.4rem;
            font-weight: 700;
            margin-bottom: 8px;
            color: var(--text-color);
        }
        
        .team-member-position {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 20px;
            font-size: 1rem;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .team-member-description {
            color: var(--text-light);
            font-size: 0.95rem;
            line-height: 1.7;
            margin-bottom: 20px;
        }
        
        .team-member-contact {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
            display: flex;
            justify-content: center;
            gap: 20px;
        }
        
        .team-member-contact a {
            color: var(--primary-color) !important; /* COLOR AZUL */
            font-size: 0.95rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 15px;
            border-radius: 25px;
            background: rgba(26, 75, 140, 0.1);
            transition: all 0.3s ease;
        }
        
        .team-member-contact a:hover {
            color: var(--white) !important;
            background: var(--primary-color);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(26, 75, 140, 0.3);
        }
        
        .collaborators-section {
            padding: 80px 0;
        }
        
        .collaborators-section h2 {
            font-size: 2.2rem;
            margin-bottom: 50px;
            color: var(--primary-color);
        }
        
        .collaborator-logo {
            background: var(--white);
            border-radius: 12px;
            padding: 35px;
            height: 160px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 25px rgba(0,0,0,0.06);
            margin-bottom: 30px;
            transition: all 0.4s ease;
            border: 1px solid rgba(0,0,0,0.05);
        }
        
        .collaborator-logo:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            border-color: var(--primary-color);
        }
        
        .collaborator-logo img {
            max-width: 100%;
            max-height: 100px;
            object-fit: contain;
        }
        
        /* Navegación móvil y responsive... (mantener igual) */
        
        .mobile-nav-container {
            display: none;
        }
        
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
        
        .hamburger-menu.active span:nth-child(1) {
            transform: translateY(9px) rotate(45deg);
        }
        
        .hamburger-menu.active span:nth-child(2) {
            opacity: 0;
        }
        
        .hamburger-menu.active span:nth-child(3) {
            transform: translateY(-9px) rotate(-45deg);
        }
        
        /* SOLO LOS ICONOS AZULES en el footer */
        .contact-info i {
            color: var(--primary-color) !important; /* SOLO ICONOS AZUL */
            font-size: 0.9rem;
        }
        
        /* El texto (email y teléfono) se queda BLANCO */
        .contact-info a {
            color: white !important; /* TEXTO BLANCO */
            text-decoration: none !important;
            transition: color 0.3s ease;
        }
        
        .contact-info a:hover {
            color: #e0f0ff !important; /* Azul claro al hover */
            text-decoration: underline !important;
        }
        
        /* Footer compacto */
        .site-footer {
            background-color: var(--footer-bg);
            color: var(--white);
        }
        
        .footer-main {
            padding: 15px 0 8px;
        }
        
        /* Container */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }
        
        /* Efectos de aparición al hacer scroll */
        .fade-in {
            opacity: 0;
            transform: translateY(40px);
            transition: opacity 0.8s ease, transform 0.8s ease;
        }
        
        .fade-in.visible {
            opacity: 1;
            transform: translateY(0);
        }
        
        /* Responsive */
        @media (max-width: 1200px) {
            .hero-section h1 {
                font-size: 2.8rem;
            }
            
            .content-text {
                padding: 40px;
            }
        }
        
        @media (max-width: 992px) {
            .main-navigation {
                display: none;
            }
            
            .hm-header-gadgets .hm-social-menu,
            .hm-header-gadgets .hm-cta-btn {
                display: none;
            }
            
            .hamburger-menu {
                display: flex;
            }
            
            .mobile-nav-container {
                display: block;
            }
            
            .hero-section {
                padding: 80px 0 60px;
            }
            
            .hero-section h1 {
                font-size: 2.5rem;
            }
            
            .hero-section .subtitulo {
                font-size: 1.1rem;
            }
            
            .content-section,
            .mision-vision-section,
            .values-section,
            .team-section,
            .collaborators-section {
                padding: 60px 0;
            }
            
            .content-text {
                padding: 35px;
                font-size: 1.05rem;
            }
            
            .mission-card, .vision-card {
                padding: 35px 30px;
            }
        }
        
        @media (max-width: 768px) {
            .hero-section {
                padding: 60px 0 40px;
            }
            
            .hero-section h1 {
                font-size: 2.2rem;
            }
            
            .hero-section .subtitulo {
                font-size: 1rem;
            }
            
            .content-image img {
                height: 350px;
            }
            
            .content-text {
                padding: 30px;
                font-size: 1rem;
            }
            
            .content-text h2 {
                font-size: 1.8rem;
            }
            
            .mission-card h3, .vision-card h3 {
                font-size: 1.6rem;
            }
            
            .values-section h2,
            .team-section h2,
            .collaborators-section h2 {
                font-size: 1.8rem;
            }
            
            .value-item {
                padding: 25px 15px;
            }
            
            .team-member-img {
                height: 180px;
                width: 180px;
            }
            
            .team-member-contact {
                flex-direction: column;
                gap: 10px;
            }
        }
        
        @media (max-width: 576px) {
            .hero-section {
                padding: 50px 0 30px;
            }
            
            .hero-section h1 {
                font-size: 1.9rem;
            }
            
            .hero-section .subtitulo {
                font-size: 0.95rem;
                padding: 0 15px;
            }
            
            .content-section,
            .mision-vision-section,
            .values-section,
            .team-section,
            .collaborators-section {
                padding: 50px 0;
            }
            
            .content-image img {
                height: 280px;
            }
            
            .content-text {
                padding: 25px;
                font-size: 0.95rem;
            }
            
            .content-text h2 {
                font-size: 1.6rem;
            }
            
            .mission-card, .vision-card {
                padding: 30px 25px;
            }
            
            .mission-card h3, .vision-card h3 {
                font-size: 1.4rem;
            }
            
            .team-member-img {
                height: 160px;
                width: 160px;
                margin: 25px auto 20px;
            }
            
            .team-member-info {
                padding: 0 20px 25px;
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
                            <li class="current-menu-item"><a href="quienes-somos.php">Quiénes Somos</a></li>
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
        <div class="mobile-menu-overlay"></div>
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
                    <li class="current-menu-item"><a href="quienes-somos.php">Quiénes Somos</a></li>
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

    <!-- Hero Section - PROFESIONAL Y BRUTAL -->
    <section class="hero-section">
        <div class="container">
            <h1 class="fade-in"><?php echo htmlspecialchars($quienes_somos['titulo']); ?></h1>
            <p class="subtitulo fade-in">Descubre la esencia de nuestra institución y nuestro compromiso con la excelencia educativa</p>
        </div>
    </section>

    <!-- Contenido Principal - VUELTA A LA VERSIÓN ANTERIOR -->
    <section class="content-section">
        <div class="container">
            <div class="row align-items-center">
                <?php if (!empty($quienes_somos['imagen_principal'])): 
                    $ruta_imagen = '/sige/centeno/uploads/quienes-somos/' . $quienes_somos['imagen_principal'];
                ?>
                <div class="col-lg-6 mb-5 mb-lg-0 fade-in">
                    <div class="content-image">
                        <img src="<?php echo $ruta_imagen; ?>" 
                             alt="<?php echo htmlspecialchars($quienes_somos['imagen_principal_alt'] ?? $quienes_somos['titulo']); ?>"
                             onerror="this.src='https://placehold.co/600x450/1a4b8c/white?text=Institución+Educativa'">
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="<?php echo !empty($quienes_somos['imagen_principal']) ? 'col-lg-6' : 'col-12'; ?> fade-in">
                    <div class="content-text">
                        <?php if (!empty($quienes_somos['contenido'])): ?>
                            <?php 
                            // Mostrar el contenido formateado con párrafos
                            $contenido = html_entity_decode($quienes_somos['contenido']);
                            // Si tiene saltos de línea dobles, convertirlos en párrafos
                            if (strpos($contenido, "\n\n") !== false) {
                                $parrafos = explode("\n\n", $contenido);
                                foreach ($parrafos as $index => $parrafo) {
                                    $parrafo_limpio = trim($parrafo);
                                    if (!empty($parrafo_limpio)) {
                                        if ($index === 0) {
                                            echo '<p class="lead" style="font-weight: 500; color: #2c3e50;">' . nl2br($parrafo_limpio) . '</p>';
                                        } else {
                                            echo '<p>' . nl2br($parrafo_limpio) . '</p>';
                                        }
                                    }
                                }
                            } else {
                                echo '<p class="lead" style="font-weight: 500; color: #2c3e50;">' . nl2br($contenido) . '</p>';
                            }
                            ?>
                        <?php else: ?>
                            <!-- Texto por defecto con la estructura que quieres -->
                            <p class="lead" style="font-weight: 500; color: #2c3e50;">Somos una institución educativa comprometida con la excelencia académica y la formación integral de nuestros estudiantes. Nuestra misión es proporcionar un ambiente de aprendizaje enriquecedor que fomente el crecimiento intelectual, emocional y social.</p>
                            
                            <p>Contamos con un equipo de educadores altamente calificados y dedicados, que utilizan métodos pedagógicos innovadores para inspirar el amor por el aprendizaje en cada estudiante. Nuestros valores se centran en el respeto, la responsabilidad, la honestidad y la solidaridad, preparando a nuestros alumnos para los desafíos del futuro.</p>
                            
                            <div class="highlight">
                                <p>Con más de 20 años de experiencia en educación, hemos formado a generaciones de estudiantes que hoy son profesionales exitosos y ciudadanos comprometidos con el desarrollo de nuestra sociedad.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Misión y Visión -->
    <?php if (!empty($quienes_somos['mision']) || !empty($quienes_somos['vision'])): ?>
    <section class="mision-vision-section">
        <div class="container">
            <div class="row">
                <?php if (!empty($quienes_somos['mision'])): ?>
                <div class="col-md-6 mb-5 mb-md-0 fade-in">
                    <div class="mission-card">
                        <i class="fas fa-bullseye"></i>
                        <h3>Nuestra Misión</h3>
                        <p><?php echo nl2br(htmlspecialchars($quienes_somos['mision'])); ?></p>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($quienes_somos['vision'])): ?>
                <div class="col-md-6 fade-in">
                    <div class="vision-card">
                        <i class="fas fa-eye"></i>
                        <h3>Nuestra Visión</h3>
                        <p><?php echo nl2br(htmlspecialchars($quienes_somos['vision'])); ?></p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Valores (solo 4) -->
    <?php if (!empty($quienes_somos['valores'])): 
        $valores = explode(',', $quienes_somos['valores']);
        $valores_limpios = array_filter(array_map('trim', $valores));
        // Tomar solo los primeros 4 valores
        $valores_limitados = array_slice($valores_limpios, 0, 4);
        
        // Descripciones para TODOS los valores incluyendo Innovación y Compromiso
        $descripciones_valores = [
            'Respeto' => 'Valoramos la dignidad de cada persona, promoviendo un ambiente de convivencia armónica.',
            'Responsabilidad' => 'Cumplimos con nuestros compromisos y fomentamos la autonomía en nuestros estudiantes.',
            'Solidaridad' => 'Trabajamos en equipo y apoyamos a quienes más lo necesitan en nuestra comunidad.',
            'Innovación' => 'Implementamos metodologías educativas actualizadas para una enseñanza de calidad.',
            'Honestidad' => 'Actuamos con transparencia y veracidad en todas nuestras acciones.',
            'Compromiso' => 'Dedicación total hacia la formación integral de nuestros estudiantes.',
            'Excelencia' => 'Buscamos la máxima calidad en todos los procesos educativos.',
            'Integridad' => 'Coherencia entre pensamiento, palabra y acción en nuestro quehacer educativo.'
        ];
    ?>
    <section class="values-section">
        <div class="container">
            <h2 class="text-center mb-5 fade-in">Nuestros Valores Fundamentales</h2>
            <div class="row">
                <?php 
                $iconos = ['fas fa-handshake', 'fas fa-tasks', 'fas fa-users', 'fas fa-lightbulb', 
                          'fas fa-star', 'fas fa-heart', 'fas fa-award', 'fas fa-shield-alt'];
                $i = 0;
                foreach ($valores_limitados as $valor): 
                    // Buscar descripción para este valor
                    $descripcion = isset($descripciones_valores[$valor]) ? $descripciones_valores[$valor] : 
                                  'Valor fundamental que guía nuestra acción educativa diaria.';
                ?>
                <div class="col-md-3 col-sm-6 mb-4 fade-in">
                    <div class="value-item">
                        <div class="value-icon">
                            <i class="<?php echo $iconos[$i] ?? 'fas fa-star'; ?>"></i>
                        </div>
                        <h4><?php echo htmlspecialchars($valor); ?></h4>
                        <p><?php echo htmlspecialchars($descripcion); ?></p>
                    </div>
                </div>
                <?php 
                    $i++;
                endforeach; 
                ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Equipo Directivo (solo 4) -->
    <?php if (!empty($equipo)): ?>
    <section class="team-section">
        <div class="container">
            <h2 class="text-center mb-5 fade-in">Nuestro Equipo Directivo</h2>
            <div class="row justify-content-center">
                <?php foreach ($equipo as $miembro): ?>
                <div class="col-lg-3 col-md-6 mb-4 fade-in">
                    <div class="team-member">
                        <div class="team-member-img">
                            <?php if (!empty($miembro['imagen'])): 
                                $ruta_foto = '/sige/centeno/uploads/quienes-somos/equipo/' . $miembro['imagen'];
                            ?>
                                <img src="<?php echo $ruta_foto; ?>" 
                                     alt="<?php echo htmlspecialchars($miembro['nombre']); ?>"
                                     onerror="this.src='https://placehold.co/400x400/1a4b8c/white?text=<?php echo urlencode($miembro['nombre']); ?>'">
                            <?php else: ?>
                                <img src="https://placehold.co/400x400/1a4b8c/white?text=<?php echo urlencode($miembro['nombre']); ?>" 
                                     alt="<?php echo htmlspecialchars($miembro['nombre']); ?>">
                            <?php endif; ?>
                        </div>
                        <div class="team-member-info">
                            <h3 class="team-member-name"><?php echo htmlspecialchars($miembro['nombre']); ?></h3>
                            <div class="team-member-position"><?php echo htmlspecialchars($miembro['cargo']); ?></div>
                            <?php if (!empty($miembro['descripcion'])): ?>
                                <p class="team-member-description"><?php echo htmlspecialchars($miembro['descripcion']); ?></p>
                            <?php endif; ?>
                            
                            <div class="team-member-contact">
                                <?php if (!empty($miembro['email'])): ?>
                                    <a href="mailto:<?php echo htmlspecialchars($miembro['email']); ?>" title="Enviar correo">
                                        <i class="fas fa-envelope"></i> Email
                                    </a>
                                <?php endif; ?>
                                <?php if (!empty($miembro['telefono'])): ?>
                                    <a href="tel:<?php echo htmlspecialchars($miembro['telefono']); ?>" title="Llamar">
                                        <i class="fas fa-phone"></i> Teléfono
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Colaboradores -->
    <?php if (!empty($colaboradores)): ?>
    <section class="collaborators-section">
        <div class="container">
            <h2 class="text-center mb-5 fade-in">Instituciones Colaboradoras</h2>
            <div class="row">
                <?php foreach ($colaboradores as $colaborador): ?>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4 fade-in">
                    <div class="collaborator-logo">
                        <?php if (!empty($colaborador['logo'])): 
                            $ruta_logo = '/sige/centeno/uploads/quienes-somos/colaboradores/' . $colaborador['logo'];
                        ?>
                            <?php if (!empty($colaborador['url'])): ?>
                                <a href="<?php echo htmlspecialchars($colaborador['url']); ?>" target="_blank">
                                    <img src="<?php echo $ruta_logo; ?>" 
                                         alt="<?php echo htmlspecialchars($colaborador['nombre']); ?>"
                                         onerror="this.src='https://placehold.co/150x100/1a4b8c/white?text=Logo'">
                                </a>
                            <?php else: ?>
                                <img src="<?php echo $ruta_logo; ?>" 
                                     alt="<?php echo htmlspecialchars($colaborador['nombre']); ?>"
                                     onerror="this.src='https://placehold.co/150x100/1a4b8c/white?text=Logo'">
                            <?php endif; ?>
                        <?php else: ?>
                            <div style="text-align: center; width: 100%;">
                                <?php if (!empty($colaborador['url'])): ?>
                                    <a href="<?php echo htmlspecialchars($colaborador['url']); ?>" target="_blank" style="color: var(--primary-color);">
                                        <i class="fas fa-building fa-3x mb-3"></i>
                                        <div class="collaborator-name"><?php echo htmlspecialchars($colaborador['nombre']); ?></div>
                                    </a>
                                <?php else: ?>
                                    <i class="fas fa-building fa-3x mb-3" style="color: var(--primary-color);"></i>
                                    <div class="collaborator-name"><?php echo htmlspecialchars($colaborador['nombre']); ?></div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="collaborator-info">
                        <?php if (!empty($colaborador['descripcion'])): ?>
                            <p class="collaborator-description"><?php echo htmlspecialchars(substr($colaborador['descripcion'], 0, 100)); ?>...</p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <?php
    // Obtener configuración del footer
    $footerQuery = "SELECT * FROM footer_config WHERE id = 1";
    $footerResult = mysqli_query($con, $footerQuery);
    $footer_config = mysqli_fetch_assoc($footerResult);

    if (!$footer_config) {
        $footer_config = [
            'direccion' => 'Caricuao, Urbanización García Carballo',
            'email' => 'RobertoMC@gmail.com',
            'telefono' => '02125368526',
            'derechos_autor' => '© 2026 Portal Escolar | Institución Educativa',
            'creditos' => 'Desarrollado para la comunidad educativa'
        ];
    }

    $derechos_actual = str_replace('[año]', date('Y'), $footer_config['derechos_autor']);
    ?>

    <!-- Footer Compacto -->
    <footer class="site-footer">
        <div class="footer-main">
            <div class="container">
                <div class="row mb-2">
                    <div class="col-12 text-center">
                        <h5 style="color: white; font-size: 1.1rem; margin-bottom: 8px; font-weight: 600;">PORTAL ESCOLAR</h5>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-12">
                        <p style="font-size: 0.9rem; line-height: 1.5; text-align: center; color: rgba(255,255,255,0.9); margin: 0 5px 8px 5px;">
                            Plataforma oficial de comunicación e información educativa, comprometida con la innovación y el desarrollo integral de nuestra comunidad educativa.
                        </p>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="contact-info text-center" style="font-size: 0.85rem;">
                            <span style="margin-right: 15px;">
                                <i class="fas fa-map-marker-alt" style="margin-right: 5px;"></i>
                                <?php echo htmlspecialchars($footer_config['direccion']); ?>
                            </span>
                            
                            <span style="margin-right: 15px;">
                                <i class="fas fa-envelope" style="margin-right: 5px;"></i>
                                <a href="mailto:<?php echo htmlspecialchars($footer_config['email']); ?>">
                                    <?php echo htmlspecialchars($footer_config['email']); ?>
                                </a>
                            </span>
                            
                            <span>
                                <i class="fas fa-phone" style="margin-right: 5px;"></i>
                                <a href="tel:<?php echo htmlspecialchars($footer_config['telefono']); ?>">
                                    <?php echo htmlspecialchars($footer_config['telefono']); ?>
                                </a>
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-12 text-center">
                        <div class="footer-social">
                            <?php foreach ($social_media as $social): ?>
                            <a href="<?php echo $social['url']; ?>" target="_blank" class="social-icon">
                                <?php if ($social['icon_type'] == 'fontawesome'): ?>
                                    <i class="<?php echo $social['icon']; ?>"></i>
                                <?php else: ?>
                                    <img src="admin/<?php echo $social['icon']; ?>" width="14" height="14">
                                <?php endif; ?>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-12">
                        <div class="footer-bottom-content text-center" style="font-size: 0.8rem; color: rgba(255,255,255,0.8);">
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
        // Navegación móvil
        document.addEventListener('DOMContentLoaded', function() {
            const hamburgerMenu = document.querySelector('.hamburger-menu');
            const mobileMenu = document.querySelector('.mobile-menu');
            const mobileMenuOverlay = document.querySelector('.mobile-menu-overlay');
            const mobileMenuClose = document.querySelector('.mobile-menu-close');
            
            if (hamburgerMenu && mobileMenu && mobileMenuOverlay && mobileMenuClose) {
                hamburgerMenu.addEventListener('click', function() {
                    this.classList.toggle('active');
                    mobileMenu.classList.toggle('active');
                    mobileMenuOverlay.classList.toggle('active');
                    document.body.style.overflow = 'hidden';
                });
                
                function closeMobileMenu() {
                    hamburgerMenu.classList.remove('active');
                    mobileMenu.classList.remove('active');
                    mobileMenuOverlay.classList.remove('active');
                    document.body.style.overflow = '';
                }
                
                mobileMenuClose.addEventListener('click', closeMobileMenu);
                mobileMenuOverlay.addEventListener('click', closeMobileMenu);
                
                const mobileNavLinks = document.querySelectorAll('.mobile-nav a');
                mobileNavLinks.forEach(link => {
                    link.addEventListener('click', closeMobileMenu);
                });
            }

            // Efecto de aparición al hacer scroll
            const fadeElements = document.querySelectorAll('.fade-in');
            
            function checkScroll() {
                fadeElements.forEach(element => {
                    const elementTop = element.getBoundingClientRect().top;
                    const elementVisible = 100;
                    
                    if (elementTop < window.innerHeight - elementVisible) {
                        element.classList.add('visible');
                    }
                });
            }
            
            // Verificar al cargar y al hacer scroll
            window.addEventListener('load', checkScroll);
            window.addEventListener('scroll', checkScroll);
            
            // Aplicar efecto inicial
            setTimeout(() => {
                checkScroll();
            }, 100);
        });
    </script>
</body>
</html>
<?php
// Cerrar conexión
mysqli_close($con);
?>