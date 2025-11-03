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
                            <h1 class="m-0">Lista de administrativos</h1>
                        </div><!-- /.col -->
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="<?=APP_URL;?>/admin/">Dashboard</a></li>
                                <li class="breadcrumb-item">Administrativos</li>
                            </ol>
                        </div><!-- /.col -->
                    </div><!-- /.row -->
                </div><!-- /.container-fluid -->
            </div>
            <br>
            <div class="col-md-6 col-sm-6 col-12">
                    <div class="info-box">
                        <span class="info-box-icon bg-info"><i class="bi bi-bar-chart-steps"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text"><b>Lista de administrativos</b></span>
                            <a href="lista_administrativo.php" class="btn btn-info btn-sm">Ver</a>
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