<?php

$id_administrativo = $_GET['id'];
include ('../../app/config.php');
include ('../../admin/layout/parte1.php');

include ('../../app/controllers/administrativos/datos_administrativos.php');

?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <br>
<div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><?= $nombres . " " . $apellidos; ?></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?= APP_URL; ?>/admin/administrativos">Administrativos</a></li>
                        <li class="breadcrumb-item"><a href="<?= APP_URL; ?>/admin/administrativos/Lista_administrativo.php">Lista administrativos</a></li>
                        <li class="breadcrumb-item active">Datos administrativos</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <br>
    <div class="content">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-info">
                        <div class="card-header">
                            <h3 class="card-title"><b>Datos del Administrativo</b></h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Nombre del rol</label>
                                        <p class="lead"><?=$nombre_rol;?></p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Cédula de Identidad</label>
                                        <?php 
                                            // Formatear la cédula a XX.XXX.XXX
                                            $cedula_formateada = substr($ci, 0, 2) . '.' . substr($ci, 2, 3) . '.' . substr($ci, 5); 
                                        ?>
                                        <p class="lead"><?=$cedula_formateada;?></p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Nombres</label>
                                        <p class="lead"><?=$nombres;?></p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Apellidos</label>
                                        <p class="lead"><?=$apellidos;?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Fecha de Nacimiento</label>
                                        <?php 
                                            // Formatear la fecha de nacimiento a dd/mm/yyyy
                                            $fecha_nacimiento_formateada = date("d/m/Y", strtotime($fecha_nacimiento)); 
                                        ?>
                                        <p class="lead"><?=$fecha_nacimiento_formateada;?></p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Celular</label>
                                        <p class="lead"><?=$celular;?></p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Correo Electrónico</label>
                                        <p class="lead"><?=$email;?></p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Dirección</label>
                                        <p class="lead"><?=$direccion;?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Fecha y hora de creación</label>
                                        <p class="lead"><?=$fyh_creacion;?></p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Estado</label>
                                        <p class="lead">
                                            <?php
                                            if($estado == "1") echo "ACTIVO";
                                            else echo "INACTIVO";
                                            ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-12 text-center">
                                    <a href="<?=APP_URL;?>/admin/administrativos" class="btn btn-secondary">Volver</a>
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
include ('../../admin/layout/parte2.php');
include ('../../layout/mensajes.php');
?>