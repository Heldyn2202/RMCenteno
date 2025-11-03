<?php  
$id_representante = $_GET['id'];  
include ('../../app/config.php');  
include ('../../admin/layout/parte1.php');  
include ('../../app/controllers/representantes/datos_del_representante.php');  
?>  

<!-- Content Wrapper. Contains page content -->  
<div class="content-wrapper">   
    <br>  
    <div class="content">  
        <div class="container">  
            <div class="row">  
                <h1 class="text-center mb-4"><?=$nombres." ".$apellidos;?></h1>  
            </div>  
            <div class="row">  
                <div class="col-md-12">  
                    <div class="card card-outline card-info">  
                        <div class="card-header">  
                            <h3 class="card-title"><b>Datos del Representante</b></h3>  
                        </div>  
                        <div class="card-body">  
                            <div class="row">  
                                <div class="col-md-3">  
                                    <div class="form-group">  
                                        <label>Tipo de Cédula</label>  
                                        <p class="lead"><?=$tipo_cedula;?></p>                                   
                                    </div>  
                                </div>  
                                <div class="col-md-3">  
                                    <div class="form-group">  
                                        <label>Cédula de Identidad</label>  
                                        <?php 
                                            // Formatear la cédula a XX.XXX.XXX
                                            $cedula_formateada = substr($cedula, 0, 2) . '.' . substr($cedula, 2, 3) . '.' . substr($cedula, 5); 
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
                                        <label>Estado Civil</label>  
                                        <p class="lead"><?=$estado_civil;?></p>  
                                    </div>  
                                </div>  
                                <div class="col-md-3">  
                                    <div class="form-group">  
                                        <label>Género</label>  
                                        <p class="lead"><?=$genero;?></p>  
                                    </div>  
                                </div>  
                                <div class="col-md-3">  
                                    <div class="form-group">  
                                        <label>Correo Electrónico</label>  
                                        <p class="lead"><?=$correo_electronico;?></p>  
                                    </div>  
                                </div>  
                                <div class="col-md-3">  
                                    <div class="form-group">  
                                        <label>Tipo de Sangre</label>  
                                        <p class="lead"><?=$tipo_sangre;?></p>  
                                    </div>  
                                </div>  

                                <div class="col-md-3">  
                                    <div class="form-group">  
                                        <label>Dirección</label>  
                                        <p class="lead"><?=$direccion;?></p>  
                                    </div>  
                                </div>  

                                <div class="col-md-3">  
                                <div class="form-group">  
                                        <label>Números Telefónicos</label>  
                                        <?php 
                                            // Formatear el número de teléfono a 0412-198-8817
                                            $telefono_formateado = substr($numeros_telefonicos, 0, 4) . '-' . substr($numeros_telefonicos, 4, 3) . '-' . substr($numeros_telefonicos, 7,); 
                                        ?>
                                        <p class="lead"><?=$telefono_formateado;?></p>  
                                    </div>  
                                </div>  
                                <div class="col-md-3">  
                                    <div class="form-group">  
                                        <label>Estatus</label>  
                                        <p class="lead">  
                                        <?php if (strtolower($representante['estatus']) == "activo") { ?>  
                                            <button class="btn btn-success btn-sm" style="border-radius: 20px" title="Activo">  
                                                ACTIVO  
                                            </button>  
                                        <?php } else { ?>  
                                            <button class="btn btn-danger btn-sm" style="border-radius: 20px" title="Inactivo">  
                                                INACTIVO  
                                            </button>  
                                        <?php } ?>  
                                        </p>  
                                    </div>  
                                </div>  
                            </div>  
                            <hr>  
                            <div class="row">  
                                <div class="col-md-10 text-center">  
                                    <a href="<?=APP_URL;?>/admin/representantes" class="btn btn-secondary">Volver</a> 
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

