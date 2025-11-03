<?php
include ('../../app/config.php');
include ('../../admin/layout/parte1.php');
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <br>
    <div class="content">
        <div class="container">
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">Estudiantes</h1>
                        </div><!-- /.col -->
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="<?=APP_URL;?>/admin">Inicio</a></li>
                                <li class="breadcrumb-item">Estudiantes</li>
                            </ol>
                        </div><!-- /.col -->
                    </div><!-- /.row -->
                </div><!-- /.container-fluid -->
            </div>
            <br>
            
            <!-- Primera fila con 2 botones -->
            <div class="row">
                <div class="col-md-6 col-sm-6 col-12">
                    <div class="info-box">
                        <span class="info-box-icon bg-info"><i class="bi bi-people-fill" style="font-size: 2rem;"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text"><b>Lista de Estudiantes Registrados</b></span>
                            <a href="lista_de_estudiante.php" class="btn btn-info btn-sm">Ver</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-sm-6 col-12">
                    <div class="info-box">
                        <span class="info-box-icon bg-info"><i class="bi bi-card-checklist" style="font-size: 2rem;"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text"><b>Lista de Estudiantes Inscritos</b></span>
                            <a href="Lista_de_inscripcion.php" class="btn btn-info btn-sm">Ver</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Segunda fila con 2 botones -->
            <div class="row">
                <div class="col-md-6 col-sm-6 col-12">
                    <div class="info-box">
                        <span class="info-box-icon bg-info"><i class="bi bi-person-badge" style="font-size: 2rem;"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text"><b>Carnet estudiantil</b></span>
                            <a href="generar_carnets.php" class="btn btn-info btn-sm">Ver</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-sm-6 col-12">
                    <div class="info-box">
                        <span class="info-box-icon bg-info"><i class="bi bi-palette" style="font-size: 2rem;"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text"><b>Diseño carnets estudiantil</b></span>
                            <a href="plantillas_carnet.php" class="btn btn-info btn-sm">Ver</a>
                        </div>
                    </div>
                </div>
            </div>
            
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->

<?php
include ('../../admin/layout/parte2.php');
include ('../../layout/mensajes.php');
?>