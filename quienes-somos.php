<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
    <title>Quiénes Somos - Portal Escolar</title>
    
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
        
        .intro-content {
            margin-bottom: 4rem;
        }
        
        .intro-content img {
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        /* Team Styles */
        .team-container {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 30px;
            margin-bottom: 3rem;
        }
        
        .team-item {
            flex: 0 0 auto;
            text-align: center;
            width: 100%;
            max-width: 350px;
        }
        
        .team-card {
            border: none;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            height: 100%;
            background-color: var(--white);
            border: 1px solid var(--border-color);
        }
        
        .team-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.15);
            border-color: var(--accent-color);
        }
        
        .team-card .card-body {
            padding: 1.5rem;
        }
        
        .team-card .card-footer {
            background-color: var(--light-bg);
            border-top: 1px solid var(--border-color);
            padding: 0.75rem 1.5rem;
        }
        
        .team-card .card-footer a {
            color: var(--accent-color);
            text-decoration: none;
        }
        
        /* Collaborators Styles */
        .collaborators-container {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 30px;
            margin-bottom: 3rem;
        }
        
        .collaborator-item {
            flex: 0 0 auto;
            text-align: center;
        }
        
        .customer-logo {
            transition: all 0.3s ease;
            border-radius: 8px;
            padding: 15px;
            background: var(--white);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
            border: 1px solid var(--border-color);
            max-height: 150px;
        }
        
        .customer-logo:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
            border-color: var(--accent-color);
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
            
            .collaborators-container {
                gap: 20px;
            }
            
            .team-container {
                gap: 20px;
            }
        }
        
        @media (max-width: 768px) {
            .collaborators-container {
                gap: 20px;
            }
            
            .team-container {
                gap: 20px;
            }
        }
    </style>
</head>

<body>
    <?php
    // Conexión a la base de datos
    $host = 'localhost';
    $dbname = 'sige'; // Cambiado a portal_escolar
    $username = 'root'; // Cambiar por tu usuario
    $password = ''; // Cambiar por tu contraseña
    
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Obtener información de la página "Quiénes Somos"
        $stmt = $pdo->query("SELECT * FROM about_us WHERE id = 1");
        $about_info = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Obtener miembros del equipo
        $stmt = $pdo->query("SELECT * FROM team_members WHERE status = 1 ORDER BY position_order");
        $team_members = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Obtener instituciones colaboradoras
        $stmt = $pdo->query("SELECT * FROM collaborators WHERE status = 1 ORDER BY name");
        $collaborators = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Obtener redes sociales
        $stmt = $pdo->query("SELECT * FROM social_media WHERE status = 1 ORDER BY name");
        $social_media = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch(PDOException $e) {
        echo "Error de conexión: " . $e->getMessage();
        die();
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

    <div id="content" class="site-content">
        <div class="content-area container">
            <!-- Page Heading/Breadcrumbs -->
            <h1 class="section-title text-blue">Quiénes Somos</h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="index.php">Inicio</a>
                </li>
                <li class="breadcrumb-item active">Quiénes Somos</li>
            </ol>
            
            <!-- Intro Content -->
            <div class="row intro-content">
                <div class="col-lg-6">
                    <img class="img-fluid rounded mb-4" src="admin/<?php echo $about_info['main_image'] ?? 'default-school.jpg'; ?>" alt="<?php echo $about_info['image_alt'] ?? 'Nuestra Escuela'; ?>" onerror="this.src='https://placehold.co/600x400/1a4b8c/white?text=Portal+Escolar'">
                </div>
                <div class="col-lg-6">
                    <h2 class="text-blue text-center"><?php echo $about_info['title'] ?? 'Bienvenidos a Nuestro Portal Escolar'; ?></h2>
                    <?php 
                    if (isset($about_info['content'])) {
                        echo $about_info['content'];
                    } else {
                        echo '<p>Somos una institución educativa comprometida con la excelencia académica y la formación integral de nuestros estudiantes. Nuestra misión es proporcionar un ambiente de aprendizaje enriquecedor que fomente el crecimiento intelectual, emocional y social.</p>
                        <p>Contamos con un equipo de educadores altamente calificados y dedicados, que utilizan métodos pedagógicos innovadores para inspirar el amor por el aprendizaje en cada estudiante.</p>
                        <p>Nuestros valores se centran en el respeto, la responsabilidad, la honestidad y la solidaridad, preparando a nuestros alumnos para los desafíos del futuro.</p>';
                    }
                    ?>
                </div>
            </div>
            <!-- /.row -->

            <!-- Team Members -->
            <h2 class="section-title text-blue">Nuestro Equipo Directivo</h2>

            <div class="team-container">
                <?php 
                if (!empty($team_members)) {
                    foreach ($team_members as $member): ?>
                    <div class="team-item">
                        <div class="card h-100 text-center team-card">
                            <div class="card-body">
                                <h4 class="card-title"><?php echo $member['name']; ?></h4>
                                <h6 class="card-subtitle mb-2 text-muted"><?php echo $member['position']; ?></h6>
                                <p class="card-text"><?php echo $member['description']; ?></p>
                            </div>
                            <div class="card-footer">
                                <a href="mailto:<?php echo $member['email']; ?>"><?php echo $member['email']; ?></a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; 
                } else {
                    // Datos de ejemplo para equipo escolar
                    $equipoEscolar = [
                        ['name' => 'María González', 'position' => 'Directora', 'description' => 'Licenciada en Educación con 20 años de experiencia en dirección escolar.', 'email' => 'directora@escuela.edu'],
                        ['name' => 'Carlos Rodríguez', 'position' => 'Subdirector', 'description' => 'Especialista en pedagogía y gestión educativa.', 'email' => 'subdirector@escuela.edu'],
                        ['name' => 'Ana Martínez', 'position' => 'Coordinadora Académica', 'description' => 'Magister en Ciencias de la Educación y planificación curricular.', 'email' => 'academica@escuela.edu']
                    ];
                    
                    foreach ($equipoEscolar as $miembro): ?>
                    <div class="team-item">
                        <div class="card h-100 text-center team-card">
                            <div class="card-body">
                                <h4 class="card-title"><?php echo $miembro['name']; ?></h4>
                                <h6 class="card-subtitle mb-2 text-muted"><?php echo $miembro['position']; ?></h6>
                                <p class="card-text"><?php echo $miembro['description']; ?></p>
                            </div>
                            <div class="card-footer">
                                <a href="mailto:<?php echo $miembro['email']; ?>"><?php echo $miembro['email']; ?></a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach;
                }
                ?>
            </div>
            <!-- /.row -->

            <!-- Our Customers -->
            <h2 class="section-title text-blue">Instituciones Colaboradoras</h2>
            <div class="collaborators-container">
                <?php 
                if (!empty($collaborators)) {
                    foreach ($collaborators as $collaborator): ?>
                    <div class="collaborator-item">
                        <img class="img-fluid customer-logo" src="admin/<?php echo $collaborator['logo']; ?>" alt="<?php echo $collaborator['name']; ?>" style="max-height: 150px;" onerror="this.src='https://placehold.co/150x100/1a4b8c/white?text=Institución'">
                    </div>
                    <?php endforeach; 
                } else {
                    // Logos de ejemplo para instituciones educativas
                    $instituciones = [
                        ['name' => 'Ministerio de Educación', 'logo' => 'https://placehold.co/150x100/1a4b8c/white?text=Ministerio+Educación'],
                        ['name' => 'Universidad Local', 'logo' => 'https://placehold.co/150x100/1a4b8c/white?text=Universidad'],
                        ['name' => 'Biblioteca Municipal', 'logo' => 'https://placehold.co/150x100/1a4b8c/white?text=Biblioteca']
                    ];
                    
                    foreach ($instituciones as $institucion): ?>
                    <div class="collaborator-item">
                        <img class="img-fluid customer-logo" src="<?php echo $institucion['logo']; ?>" alt="<?php echo $institucion['name']; ?>" style="max-height: 150px;">
                    </div>
                    <?php endforeach;
                }
                ?>
            </div>
            <!-- /.row -->
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
        });
    </script>
</body>
</html>