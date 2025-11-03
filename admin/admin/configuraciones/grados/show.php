<?php

$id_grado = $_GET['id'];
include ('../../../app/config.php');
include ('../../../admin/layout/parte1.php');

// Incluir el archivo que obtiene los datos del grado
include ('../../../app/controllers/grados/datos_grados.php');

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
                            <h1 class="m-0">Datos del Grado</h1>
                        </div><!-- /.col -->
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="<?=APP_URL;?>/admin">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="<?=APP_URL;?>/admin/configuraciones/grados">Grados</a></li>
                                <li class="breadcrumb-item">Datos del Grado</li>
                            </ol>
                        </div><!-- /.col -->
                    </div><!-- /.row -->
                </div><!-- /.container-fluid -->
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-primary">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="">Nivel Académico</label>
                                        <p><?=$nivel;?></p>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="">Grado</label>
                                        <p><?=$grado;?></p> <!-- Cambiado de $curso a $grado -->
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="">Estado</label>
                                        <p>
                                            <?php
                                            if ($estado == "1") echo "ACTIVO";
                                            else echo "INACTIVO";
                                            ?>
                                        </p>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="">Fecha y Hora de Registro</label>
                                        <p><?=$fyh_creacion;?></p>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <center><a href="<?=APP_URL;?>/admin/configuraciones/grados" class="btn btn-secondary">Volver</a></center>
                                    </div>
                                </div>
                            </div>
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
include ('../../../admin/layout/parte2.php');
include ('../../../layout/mensajes.php');
?>