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
                            <h1 class="m-0">Notas y Horarios Escolares</h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="<?=APP_URL;?>/admin">Inicio</a></li>
                                <li class="breadcrumb-item">Notas y Horarios</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <br>
            <div class="row">
                <div class="col-md-6 col-sm-6 col-12">
                    <div class="info-box">
                        <span class="info-box-icon bg-info"><i class="bi bi-journal-text"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text"><b>Gestionar Notas</b></span>
                            <a href="notas_estudiantes.php" class="btn btn-info btn-sm">Ver</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-sm-6 col-12">
                    <div class="info-box">
                        <span class="info-box-icon bg-info"><i class="bi bi-calendar-plus"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text"><b>Crear Horarios</b></span>
                            <a href="crear_horarios.php" class="btn btn-info btn-sm">Ver</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include ('../../admin/layout/parte2.php');
include ('../../layout/mensajes.php');
?>