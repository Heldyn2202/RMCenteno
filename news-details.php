<?php
session_start();
include('includes/config.php');

// Genrating CSRF Token
if (empty($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32));
}

// Obtener redes sociales desde la base de datos
$socialQuery = "SELECT * FROM social_media WHERE status = 1 ORDER BY name";
$socialResult = mysqli_query($con, $socialQuery);
$social_media = [];
if ($socialResult) {
    $social_media = mysqli_fetch_all($socialResult, MYSQLI_ASSOC);
}

// Obtener información de la noticia para el breadcrumb
$currentPostTitle = "Noticias";
if(isset($_GET['nid'])) {
    $pid = intval($_GET['nid']);
    $postQuery = mysqli_query($con, "SELECT PostTitle FROM tblposts WHERE id = '$pid'");
    if(mysqli_num_rows($postQuery) > 0) {
        $postData = mysqli_fetch_array($postQuery);
        $currentPostTitle = htmlentities($postData['PostTitle']);
    }
}

// Variable para controlar la alerta
$showAlert = false;
$alertType = '';
$alertMessage = '';

if(isset($_POST['submit'])) {
    // Verifying CSRF Token
    if (!empty($_POST['csrftoken'])) {
        if (hash_equals($_SESSION['token'], $_POST['csrftoken'])) {
            $name = mysqli_real_escape_string($con, $_POST['name']);
            $email = mysqli_real_escape_string($con, $_POST['email']);
            $comment = mysqli_real_escape_string($con, $_POST['comment']);
            $postid = intval($_GET['nid']);
            $st1 = '0';
            
            $query = mysqli_query($con, "INSERT INTO tblcomments(postId, name, email, comment, status) 
                                         VALUES('$postid', '$name', '$email', '$comment', '$st1')");
            
            if($query) {
                $showAlert = true;
                $alertType = 'success';
                $alertMessage = 'Comentario enviado exitosamente. Se mostrará después de la revisión del administrador.';
                unset($_SESSION['token']);
            } else {
                $showAlert = true;
                $alertType = 'error';
                $alertMessage = 'Algo salió mal. Por favor, inténtelo de nuevo.'; 
            }
        } else {
            $showAlert = true;
            $alertType = 'error';
            $alertMessage = 'Token de seguridad inválido.';
        }
    } else {
        $showAlert = true;
        $alertType = 'error';
        $alertMessage = 'Token de seguridad no proporcionado.';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
    <title>Detalles de Noticia | Portal Escolar</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
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
        
        /* News Detail Styles */
        .news-detail-card {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.08);
            border: none;
            margin-bottom: 30px;
            background-color: var(--white);
            border: 1px solid var(--border-color);
        }
        
        .news-image-container {
            padding: 15px;
            margin: 20px;
        }
        
        .news-detail-img {
            width: 100%;
            max-height: 450px;
            object-fit: cover;
            border-radius: 6px;
        }
        
        .news-meta {
            color: var(--text-light);
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }
        
        .news-content {
            line-height: 1.8;
            font-size: 1.1rem;
            color: var(--text-color);
        }
        
        /* Comment Section Styles */
        .comment-section {
            background-color: var(--white);
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            margin-top: 2rem;
            border: 1px solid var(--border-color);
        }
        
        .comment-card {
            border: none;
            border-left: 4px solid var(--accent-color);
            border-radius: 4px;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            background-color: var(--white);
        }
        
        .comment-header {
            background-color: var(--white);
            padding: 0.75rem 1rem;
            border-top-left-radius: 4px;
        }
        
        .comment-body {
            padding: 1rem;
        }
        
        .user-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: var(--accent-color);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            margin-right: 15px;
        }
        
        /* Button Styles */
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
        
        /* Sidebar Styles */
        .sidebar-card {
            margin-bottom: 30px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.08);
            border: none;
            background-color: var(--white);
            border: 1px solid var(--border-color);
        }
        
        .sidebar-card .card-header {
            background-color: var(--accent-color);
            color: white;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
        }
        
        .sidebar-card .card-body {
            padding: 1.5rem;
        }
        
        .list-group-item {
            background-color: transparent;
            border: 1px solid var(--border-color);
            padding: 0.75rem 1.25rem;
        }
        
        .list-group-item:first-child {
            border-top-left-radius: 0;
            border-top-right-radius: 0;
        }
        
        .list-group-item:last-child {
            border-bottom-left-radius: 0;
            border-bottom-right-radius: 0;
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
            .hm-footer-bottom-content {
                flex-direction: column;
                gap: 10px;
                text-align: center;
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
                            <li class="current-menu-item"><a href="noticias.php">Noticias</a></li>
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
                    <li><a href="quienes-somos.php">Quiénes Somos</a></li>
                    <li class="current-menu-item"><a href="noticias.php">Noticias</a></li>
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

    <!-- Contenido Principal -->
    <div class="content-area">
        <div class="container">
            <!-- Page Heading/Breadcrumbs -->
            <h1 class="section-title text-blue">Detalles de Noticia</h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="index.php">Inicio</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="noticias.php">Noticias</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">
                    <?php echo $currentPostTitle; ?>
                </li>
            </ol>
            
            <div class="row">
                <!-- Contenido Principal -->
                <div class="col-lg-8">
                    <?php
                    if(isset($_GET['nid'])) {
                        $pid = intval($_GET['nid']);
                        $query = mysqli_query($con, "SELECT tblposts.PostTitle as posttitle, tblposts.PostImage, tblcategory.CategoryName as category, tblcategory.id as cid, tblsubcategory.Subcategory as subcategory, tblposts.PostDetails as postdetails, tblposts.PostingDate as postingdate, tblposts.PostUrl as url FROM tblposts LEFT JOIN tblcategory ON tblcategory.id = tblposts.CategoryId LEFT JOIN tblsubcategory ON tblsubcategory.SubCategoryId = tblposts.SubCategoryId WHERE tblposts.id = '$pid'");
                        
                        if(mysqli_num_rows($query) > 0) {
                            while ($row = mysqli_fetch_array($query)) {
                    ?>
                    <div class="news-detail-card card mb-4">
                        <div class="news-image-container">
                            <img class="news-detail-img card-img-top" src="admin/uploads/post/<?php echo htmlentities($row['PostImage']);?>" alt="<?php echo htmlentities($row['posttitle']);?>" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iODAwIiBoZWlnaHQ9IjQ1MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZGRkIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCwgc2Fucy1zZXJpZiIgZm9udC1zaXplPSIxNCIgZmlsbD0iIzk5OSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPkltYWdlbiBubyBkaXNwb25pYmxlPC90ZXh0Pjwvc3ZnPg=='">
                        </div>
                        <div class="card-body">
                            <h1 class="card-title text-blue"><?php echo htmlentities($row['posttitle']);?></h1>
                            <div class="news-meta">
                                <span><i class="fas fa-folder-open me-1"></i> <a href="category.php?catid=<?php echo htmlentities($row['cid'])?>" class="text-blue"><?php echo htmlentities($row['category']);?></a></span> | 
                                <span><i class="fas fa-tag me-1"></i> <?php echo htmlentities($row['subcategory']);?></span> | 
                                <span><i class="fas fa-calendar-alt me-1"></i> <?php echo htmlentities($row['postingdate']);?></span>
                            </div>
                            <div class="news-content">
                                <?php echo htmlspecialchars_decode($row['postdetails']); ?>
                            </div>
                        </div>
                    </div>
                    <?php 
                            }
                        } else {
                            echo "<div class='alert alert-warning'>No se encontró la publicación solicitada.</div>";
                        }
                    } else {
                        echo "<div class='alert alert-danger'>No se ha especificado una publicación.</div>";
                    }
                    ?>
                    
                    <!-- Sección de Comentarios -->
                    <div class="comment-section">
                        <h4 class="text-blue mb-4">Deja un comentario:</h4>
                        <form name="Comment" method="post">
                            <input type="hidden" name="csrftoken" value="<?php echo htmlentities($_SESSION['token']); ?>" />
                            <div class="mb-3">
                                <input type="text" name="name" class="form-control" placeholder="Ingresa tu nombre completo" required>
                            </div>
                            <div class="mb-3">
                                <input type="email" name="email" class="form-control" placeholder="Ingresa tu correo electrónico válido" required>
                            </div>
                            <div class="mb-3">
                                <textarea class="form-control" name="comment" rows="4" placeholder="Escribe tu comentario aquí..." required></textarea>
                            </div>
                            <button type="submit" class="btn btn-blue" name="submit">Enviar Comentario</button>
                        </form>
                        
                        <hr class="my-5">
                        
                        <h4 class="text-blue mb-4">Comentarios:</h4>
                        <?php 
                        if(isset($pid)) {
                           $sts = 1;
                            $comment_query = mysqli_query($con, "SELECT name, comment, postingDate FROM tblcomments WHERE postId = '$pid' AND status = '$sts'");
                            
                            if(mysqli_num_rows($comment_query) > 0) {
                                while ($comment_row = mysqli_fetch_array($comment_query)) {
                        ?>
                        <div class="comment-card">
                            <div class="comment-header d-flex align-items-center">
                                <div class="user-icon">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0"><?php echo htmlentities($comment_row['name']);?></h5>
                                    <small class="text-muted"><?php echo htmlentities($comment_row['postingDate']);?></small>
                                </div>
                            </div>
                            <div class="comment-body">
                                <p class="mb-0"><?php echo htmlentities($comment_row['comment']);?></p>
                            </div>
                        </div>
                        <?php 
                                }
                            } else {
                                echo "<p class='text-center text-muted'>No hay comentarios aún. Sé el primero en comentar.</p>";
                            }
                        }
                        ?>
                    </div>
                </div>
                
                <!-- Barra Lateral -->
                <div class="col-lg-4">
                    <div class="sidebar-card card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-search me-2"></i>Buscar</h5>
                        </div>
                        <div class="card-body">
                            <form action="search.php" method="get">
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control" placeholder="Buscar..." required>
                                    <button class="btn btn-blue" type="submit">Ir</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <div class="sidebar-card card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-folder me-2"></i>Categorías</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <?php
                                $cat_query = mysqli_query($con, "SELECT id, CategoryName FROM tblcategory");
                                while($cat_row = mysqli_fetch_array($cat_query)) {
                                ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <a href="category.php?catid=<?php echo htmlentities($cat_row['id']); ?>" class="text-blue"><?php echo htmlentities($cat_row['CategoryName']);?></a>
                                </li>
                                <?php } ?>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="sidebar-card card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-calendar me-2"></i>Publicaciones Recientes</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <?php
                                $recent_query = mysqli_query($con, "SELECT id, PostTitle, PostingDate FROM tblposts WHERE Is_Active = 1 ORDER BY id DESC LIMIT 5");
                                while($recent_row = mysqli_fetch_array($recent_query)) {
                                ?>
                                <li class="list-group-item">
                                    <a href="news-details.php?nid=<?php echo htmlentities($recent_row['id']); ?>" class="text-blue"><?php echo htmlentities($recent_row['PostTitle']);?></a>
                                    <small class="text-muted d-block"><?php echo htmlentities($recent_row['PostingDate']);?></small>
                                </li>
                                <?php } ?>
                            </ul>
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
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
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
    
    <?php if ($showAlert): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            <?php if ($alertType == 'success'): ?>
                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: '<?php echo $alertMessage; ?>',
                    confirmButtonColor: '#1a4b8c',
                    confirmButtonText: 'Aceptar'
                });
            <?php else: ?>
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: '<?php echo $alertMessage; ?>',
                    confirmButtonColor: '#1a4b8c',
                    confirmButtonText: 'Aceptar'
                });
            <?php endif; ?>
        });
    </script>
    <?php endif; ?>
</body>
</html>