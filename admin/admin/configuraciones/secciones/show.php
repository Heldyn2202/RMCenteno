<?php
$id_seccion = $_GET['id']; // Asegúrate de que estás pasando el ID correctamente
include ('../../../app/config.php');
include ('../../../admin/layout/parte1.php');

// Incluir el archivo que obtiene los datos de la sección
include ('../../../app/controllers/secciones/datos_secciones.php'); // Asegúrate de que este archivo obtenga los datos correctamente

// Obtener el periodo académico activo
$sql_gestiones = "SELECT * FROM gestiones WHERE estado = 1 ORDER BY desde DESC LIMIT 1"; // Suponiendo que el estado 1 es activo
$query_gestiones = $pdo->prepare($sql_gestiones);
$query_gestiones->execute();
$gestion_activa = $query_gestiones->fetch(PDO::FETCH_ASSOC); // Obtener el periodo activo

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
                            <h1 class="m-0">Datos de la Sección</h1>
                        </div><!-- /.col -->
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="<?=APP_URL;?>/admin">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="<?=APP_URL;?>/admin/configuraciones/secciones">Secciones</a></li>
                                <li class="breadcrumb-item">Datos de la Sección</li>
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
                                        <label for="">Turno</label>
                                        <p><?=$turno;?></p>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="">Capacidad</label>
                                        <p><?=$capacidad;?></p>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="">Grado</label>
                                        <p><?=$grado;?></p> <!-- Asegúrate de que $grado esté definido correctamente -->
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="">Periodo Académico</label>
                                        <?php if ($gestion_activa): ?>
                                            <p>Desde: <?=$gestion_activa['desde'];?> Hasta: <?=$gestion_activa['hasta'];?></p>
                                        <?php else: ?>
                                            <p>No hay periodo activo</p>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="">Nombre de la Sección</label>
                                        <p><?=$nombre_seccion;?></p>
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
                                        <p><?=$fyh_creacion;?></p> <!-- Asegúrate de que $fyh_creacion esté definido correctamente -->
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <center><a href="<?=APP_URL;?>/admin/configuraciones/secciones" class="btn btn-secondary">Volver</a></center>
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