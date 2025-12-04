<?php
include ('../../app/config.php');
include ('../../admin/layout/parte1.php');
?>

<style>
    :root {
        --primary: #2c3e50;
        --secondary: #3498db;
        --accent: #e74c3c;
        --light: #ecf0f1;
        --dark: #2c3e50;
        --success: #2ecc71;
        --warning: #f39c12;
        --info: #3498db;
        --danger: #e74c3c;
        --card-shadow: 0 8px 15px rgba(0,0,0,0.1);
        --hover-shadow: 0 12px 25px rgba(0,0,0,0.15);
        --transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    .row2 {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        justify-content: flex-start;
        margin-top: 20px;
    }

    .col-lg-3 {
        flex: 0 0 calc(25% - 20px);
        min-width: 220px;
        max-width: calc(25% - 20px);
    }

    .settings-card {
        background: white;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: var(--card-shadow);
        transition: var(--transition);
        cursor: pointer;
        height: 100px;
        display: flex;
        align-items: center;
        position: relative;
        opacity: 0;
        transform: translateY(20px);
        animation: cardAppear 0.6s forwards;
        margin-bottom: 0;
        text-decoration: none;
        color: inherit;
    }

    .settings-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(45deg, var(--secondary), transparent);
        opacity: 0;
        transition: var(--transition);
        z-index: 0;
    }

    .settings-card:hover {
        transform: translateY(-5px) scale(1.02);
        box-shadow: var(--hover-shadow);
        text-decoration: none;
        color: inherit;
    }

    .settings-card:hover::before {
        opacity: 0.05;
    }

    .settings-card:hover .card-icon {
        transform: scale(1.08) rotate(5deg);
        box-shadow: 0 6px 15px rgba(0,0,0,0.12);
    }

    .card-icon {
        width: 60px;
        height: 60px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 15px;
        color: white;
        font-size: 1.4rem;
        transition: var(--transition);
        position: relative;
        z-index: 1;
        box-shadow: 0 4px 10px rgba(0,0,0,0.08);
    }

    .bg-info {
        background: linear-gradient(45deg, var(--info), #2980b9);
    }

    .bg-success {
        background: linear-gradient(45deg, var(--success), #27ae60);
    }

    .bg-warning {
        background: linear-gradient(45deg, var(--warning), #e67e22);
    }

    .bg-danger {
        background: linear-gradient(45deg, var(--danger), #c0392b);
    }

    .bg-primary {
        background: linear-gradient(45deg, var(--primary), #34495e);
    }

    .bg-secondary {
        background: linear-gradient(45deg, var(--secondary), #2980b9);
    }

    .card-content {
        flex: 1;
        padding-right: 15px;
        position: relative;
        z-index: 1;
    }

    .card-title {
        font-weight: 600;
        color: var(--dark);
        font-size: 1rem;
        transition: var(--transition);
        margin-bottom: 5px;
    }

    .card-description {
        font-size: 0.8rem;
        color: #6c757d;
        transition: var(--transition);
    }

    .settings-card:hover .card-title {
        color: var(--secondary);
    }

    .settings-card:hover .card-description {
        color: #495057;
    }

    /* Animaciones */
    @keyframes cardAppear {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .settings-card:nth-child(1) { animation-delay: 0.1s; }
    .settings-card:nth-child(2) { animation-delay: 0.2s; }
    .settings-card:nth-child(3) { animation-delay: 0.3s; }
    .settings-card:nth-child(4) { animation-delay: 0.4s; }
    .settings-card:nth-child(5) { animation-delay: 0.5s; }
    .settings-card:nth-child(6) { animation-delay: 0.6s; }

    /* Responsive */
    @media (max-width: 1200px) {
        .col-lg-3 {
            flex: 0 0 calc(33.333% - 20px);
            max-width: calc(33.333% - 20px);
        }
    }
    
    @media (max-width: 992px) {
        .col-lg-3 {
            flex: 0 0 calc(50% - 20px);
            max-width: calc(50% - 20px);
        }
    }

    @media (max-width: 768px) {
        .content-header .breadcrumb {
            margin-top: 15px;
            justify-content: center !important;
        }
        
        .col-lg-3 {
            flex: 0 0 100%;
            max-width: 100%;
        }
        
        .settings-card {
            height: 90px;
        }
        
        .card-icon {
            width: 55px;
            height: 55px;
            font-size: 1.3rem;
        }
    }

    @media (max-width: 576px) {
        .settings-card {
            height: 85px;
        }
        
        .card-icon {
            width: 50px;
            height: 50px;
            margin: 0 12px;
            font-size: 1.1rem;
        }
        
        .card-title {
            font-size: 0.9rem;
        }
        
        .card-description {
            font-size: 0.75rem;
        }
    }
</style>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <div class="content">
        <div class="container-fluid">
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">Estudiantes</h1>
                        </div><!-- /.col -->
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="<?=APP_URL;?>/admin">Inicio</a></li>
                                <li class="breadcrumb-item active">Estudiantes</li>
                            </ol>
                        </div><!-- /.col -->
                    </div><!-- /.row -->
                </div><!-- /.container-fluid -->
            </div>

            <div class="container-fluid">
                <div class="row2">
                    <!-- Tarjeta 1: Lista de Estudiantes Registrados -->
                    <div class="col-lg-3 col-md-6">
                        <a href="lista_de_estudiante.php" class="settings-card animated-card">
                            <div class="card-icon bg-info">
                                <i class="bi bi-people-fill"></i>
                            </div>
                            <div class="card-content">
                                <span class="card-title">Estudiantes Registrados</span>
                            </div>
                        </a>
                    </div>
                    
                    <!-- Tarjeta 2: Lista de Estudiantes Inscritos -->
                    <div class="col-lg-3 col-md-6">
                        <a href="Lista_de_inscripcion.php" class="settings-card animated-card">
                            <div class="card-icon bg-info">
                                <i class="bi bi-card-checklist"></i>
                            </div>
                            <div class="card-content">
                                <span class="card-title">Estudiantes Inscritos</span>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Hacer que las tarjetas sean clickeables
    document.querySelectorAll('.animated-card').forEach(card => {
        card.addEventListener('click', function(e) {
            // Animación al hacer clic
            this.style.transform = 'scale(0.95)';
            setTimeout(() => {
                this.style.transform = '';
            }, 200);
        });
        
        // Efecto hover con teclado
        card.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                this.click();
            }
        });
    });
    
    // Ajustar automáticamente el layout según las tarjetas visibles
    function adjustLayout() {
        const columns = document.querySelectorAll('.col-lg-3');
        columns.forEach(column => {
            const visibleCards = column.querySelectorAll('.settings-card:not([style*="display: none"])');
            if (visibleCards.length === 0) {
                column.style.display = 'none';
            } else {
                column.style.display = 'block';
            }
        });
    }
    
    // Ejecutar al cargar y si hay cambios
    adjustLayout();
    
    // Observar cambios en el DOM para reajustar si es necesario
    const observer = new MutationObserver(adjustLayout);
    observer.observe(document.body, { childList: true, subtree: true });
});
</script>

<?php
include ('../../admin/layout/parte2.php');
include ('../../layout/mensajes.php');
?>