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
            <h1 class="m-0">Reportes</h1>
          </div><!-- /.col -->
            <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="<?=APP_URL;?>/admin">Inicio</a></li>
              <li class="breadcrumb-item">Reportes</li>
            </ol>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
            <br>
            <div class="row">

                <div class="col-md-6 col-sm-6 col-12">
                    <div class="info-box">
                        <span class="info-box-icon bg-info"><i class="bi bi-download"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text"><b>Matricula escolar</b></span>
                            <a href="vista_reportes.php" class="btn btn-info btn-sm">Ver</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-sm-6 col-12">
                    <div class="info-box">
                        <span class="info-box-icon bg-info"><i class="bi bi-download"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text"><b> Constancias de incripción</b></span>
                            <a href="Lista_de_inscripcion.php" class="btn btn-info btn-sm">Ver</a>
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

