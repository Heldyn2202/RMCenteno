<?php  
  /*
include('../app/config.php');  
session_start();  
?>  
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Escolar - Acceso Rápido</title>
    <style>
        :root {
            --primary-color: #2c3e50;
            --accent-color: #0011ff;
            --light-color: #f8f9fa;
            --text-color: #333;
            --text-light: #6c757d;
        }
        
        body, html {
        height: 100%;
        margin: 0;
        padding: 0;
        font-family: 'Poppins', sans-serif;
        background: url(<?=APP_URL;?>/img/fondo3.jpg) no-repeat center center fixed;
        background-size: cover;
    }
    
    body {
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text-color);
    }
        
        .portal-container {
            width: 90%;
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .welcome-message {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .welcome-message h2 {
            color: var(--primary-color);
            margin-bottom: 5px;
            font-size: 1.5rem;
        }
        
        .welcome-message p {
            color: var(--text-light);
            font-size: 0.9rem;
        }
        
        .system-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        
        .system-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            height: 200px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            border-top: 4px solid var(--accent-color);
        }
        
        .system-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 17, 255, 0.2);
        }
        
        .system-card .icon-container {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background-color: rgba(0, 17, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }
        
        .system-card:hover .icon-container {
            background-color: var(--accent-color);
            transform: scale(1.1);
        }
        
        .system-card i {
            font-size: 1.5rem;
            color: var(--accent-color);
            transition: all 0.3s ease;
        }
        
        .system-card:hover i {
            color: white;
        }
        
        .system-card h3 {
            color: var(--primary-color);
            margin: 0 0 10px 0;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }
        
        .system-card p {
            color: var(--text-light);
            font-size: 0.8rem;
            line-height: 1.4;
            margin: 0;
            opacity: 0;
            max-height: 0;
            transition: all 0.4s ease;
            transform: translateY(10px);
        }
        
        .system-card:hover p {
            opacity: 1;
            max-height: 100px;
            transform: translateY(0);
        }
        
        .system-card .badge {
            position: absolute;
            bottom: 15px;
            left: 50%;
            transform: translateX(-50%);
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            background-color: rgba(0, 17, 255, 0.1);
            color: var(--accent-color);
            transition: all 0.3s ease;
        }
        
        .system-card:hover .badge {
            background-color: var(--accent-color);
            color: white;
        }
        
        Animaciones 
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .system-card {
            animation: fadeInUp 0.5s ease forwards;
            opacity: 0;
        }
        
        .system-card:nth-child(1) { animation-delay: 0.1s; }
        .system-card:nth-child(2) { animation-delay: 0.2s; }
        .system-card:nth-child(3) { animation-delay: 0.3s; }
        .system-card:nth-child(4) { animation-delay: 0.4s; }
        
         Estilos responsivos 
        @media (max-width: 768px) {
            .system-grid {
                grid-template-columns: 1fr;
            }
            
            .welcome-message h2 {
                font-size: 1.3rem;
            }
            
            .system-card {
                height: 180px;
            }
        }
    </style>
    <!-- Font Awesome para los iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>
<body class="hold-transition login-page">  
    <div class="portal-container">
        
        <div class="welcome-message">
            <h1>U.E.N ROBERTO MARTINEZ CENTENO</h1>
            <p>Selecciona el módulo que necesitas</p>
        </div>
        
        <div class="system-grid">
            <!-- Tarjeta 1: Historial Académico -->
            <div class="system-card" onclick="selectSystem('historial', '')">
                <div class="icon-container">
                    <i class="fas fa-file-invoice"></i>
                </div>
                <h3>Historial Académico</h3>
                <p>Accede a tus calificaciones, boletas y registros académicos completos en cualquier momento.</p>
                <br>
                <span class="badge">Documentos</span>
            </div>
            
            <!-- Tarjeta 2: Acceso al Sistema -->
            <div class="system-card" onclick="selectSystem('login', 'login.php')">
                <div class="icon-container">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <h3>Acceso Administrativo</h3>
                <p>Ingreso seguro para personal administrativo del instituto.</p>
                <br>
                <span class="badge">Acceso</span>
            </div>

            <!-- Tarjeta 3: Acceso al Sistema -->
            <div class="system-card" onclick="selectSystem('login', 'profesores/')">
                <div class="icon-container">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
                <h3>Acceso Docente</h3>
                <p>Ingreso seguro para profesores del instituto.</p>
                <br>
                <span class="badge">Acceso</span>
            </div>
            
            <!-- Tarjeta 4: Trámites Escolares -->
            <div class="system-card" onclick="selectSystem('constancias', 'constancias.php')">
                <div class="icon-container">
                    <i class="fas fa-file"></i>
                </div>
                <h3>Trámites Escolares</h3>
                <p>Solicita constancias, certificados y realiza otros trámites académicos de forma digital.</p>
                <br>
                <span class="badge">Trámites</span>
            </div>
        </div>
    </div>

    <script>
    // Función para redirigir al sistema seleccionado
    function selectSystem(system, url) {
        // Agregar efecto de clic
        const element = event.currentTarget;
        element.style.transform = 'scale(0.95) translateY(-5px)';
        setTimeout(() => {
            element.style.transform = 'scale(1) translateY(-5px)';
        }, 150);
        
        // Redirigir después del efecto
        setTimeout(() => {
            if(url) {
                window.location.href = url;
            } else {
                alert(`Módulo en desarrollo: ${system}`);
            }
        }, 300);
    }
    </script>
</body>
</html> */