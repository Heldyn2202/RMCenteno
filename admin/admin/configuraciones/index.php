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
            <h1 class="m-0">Configuraciones</h1>
          </div><!-- /.col -->
            <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Dashboard</a></li>
              <li class="breadcrumb-item"><a href="<?=APP_URL;?>/admin/configuraciones">Configuraciones</a></li>
            </ol>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
            <br>
            <div class="row">

                <div class="col-md-4 col-sm-4 col-12">
                    <div class="info-box">
                        <span class="info-box-icon bg-info"><i class="bi bi-hospital"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text"><b>Datos de la Institución</b></span>
                            <a href="institucion" class="btn btn-info btn-sm">Configurar</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 col-sm-4 col-12">
                    <div class="info-box">
                        <span class="info-box-icon bg-info"><i class="bi bi-calendar-range"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text"><b>Periodo académico</b></span>
                            <a href="gestion" class="btn btn-info btn-sm">Configurar</a>

                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-4 col-12">
    <div class="info-box">
        <span class="info-box-icon bg-info"><i class="bi bi-award"></i></span> <!-- Icono para Grados -->
        <div class="info-box-content">
            <span class="info-box-text"><b>Grados</b></span>
            <a href="grados" class="btn btn-info btn-sm">Crear Grados</a>
        </div>
    </div>
</div>
<div class="col-md-4 col-sm-4 col-12">
    <div class="info-box">
        <span class="info-box-icon bg-info"><i class="bi bi-bar-chart-steps"></i></span> <!-- Icono para Secciones -->
        <div class="info-box-content">
            <span class="info-box-text"><b>Secciones</b></span>
            <a href="secciones" class="btn btn-info btn-sm">Crear Secciones</a>
        </div>
    </div>
</div>
<div class="col-md-4 col-sm-4 col-12">
    <div class="info-box">
        <span class="info-box-icon bg-info"><i class="bi bi-people"></i></span> <!-- Icono para Secciones -->
        <div class="info-box-content">
            <span class="info-box-text"><b>Lista de usuarios</b></span>
            <a href="<?=APP_URL;?>/admin/usuarios" class="btn btn-info btn-sm">Entrar</a>
        </div>
    </div>
</div>
<div class="col-md-4 col-sm-4 col-12">
    <div class="info-box">
        <span class="info-box-icon bg-info"><i class="bi bi-people"></i></span> <!-- Icono para Secciones -->
        <div class="info-box-content">
            <span class="info-box-text"><b>Lista de directivos</b></span>
            <a href="<?=APP_URL;?>/admin/administrativos" class="btn btn-info btn-sm">Entrar</a>
        </div>
    </div>
</div>
<div class="col-md-4 col-sm-4 col-12">
    <div class="info-box">
        <span class="info-box-icon bg-info"><i class="fas fa-database"></i></span> <!-- Icono para Secciones -->
        <div class="info-box-content">
            <span class="info-box-text"><b>Backup de la base de datos</b></span>
            <a href="<?=APP_URL;?>/admin/configuraciones/backup" class="btn btn-info btn-sm">Entrar</a>
        </div>
    </div>
</div>
<div class="col-md-4 col-sm-4 col-12">
                    <div class="info-box">
                        <span class="info-box-icon bg-info"><i class="bi bi-calendar-range"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text"><b>Lapsos académicos</b></span>
                            <a href="<?=APP_URL;?>/admin/configuraciones/lapsos/lapsos.php" class="btn btn-info btn-sm">Configurar</a>

                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-4 col-12">
                    <div class="info-box">
                        <span class="info-box-icon bg-info"><i class="bi bi-bar-chart-steps"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text"><b>Materias</b></span>
                            <a href="<?=APP_URL;?>/admin/configuraciones/materias/materias.php" class="btn btn-info btn-sm">Configurar</a>

                        </div>
                    </div>
                </div>
                            




            </div>
            <!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->

<?php

include ('../../admin/layout/parte2.php');
include ('../../layout/mensajes.php');

?>

