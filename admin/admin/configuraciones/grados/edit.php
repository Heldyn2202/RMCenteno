<?php
$grado_id = $_GET['id'];
include ('../../../app/config.php');
include ('../../../admin/layout/parte1.php');
include ('../../../app/controllers/grados/datos_grados.php'); // Asegúrate de que este archivo obtenga los datos correctamente

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
                            <h1 class="m-0">Editar Grado</h1>
                        </div><!-- /.col -->
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="<?=APP_URL;?>/admin">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="<?=APP_URL;?>/admin/configuraciones/grados">Grados</a></li>
                                <li class="breadcrumb-item">Editar Grado</li>
                            </ol>
                        </div><!-- /.col -->
                    </div><!-- /.row -->
                </div><!-- /.container-fluid -->
            </div>
            <br>
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-success">
                        <div class="card-body">
                            <form action="<?=APP_URL;?>/app/controllers/grados/update.php" method="post">
                                <input type="hidden" name="id_grado" value="<?=$grado_id;?>"> <!-- Asegúrate de que este campo esté presente -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="">Nivel Académico</label>
                                            <input value="<?=$nivel;?>" type="text" name="nivel" class="form-control" required readonly> <!-- Campo no editable -->
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="">Grado</label>
                                            <input type="text" name="grado" class="form-control" required value="<?=$grado;?>"> <!-- Campo de texto para el grado -->
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="">Estado</label>
                                            <select name="estado" class="form-control" required>
                                                <option value="1" <?= $estado == "1" ? 'selected' : ''; ?>>ACTIVO</option>
                                                <option value="0" <?= $estado == "0" ? 'selected' : ''; ?>>INACTIVO</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <center>
                                                <button type="submit" class="btn btn-success">Actualizar</button>
                                                <a href="<?=APP_URL;?>/admin/configuraciones/grados" class="btn btn-secondary">Cancelar</a>
                                            </center>
                                        </div>
                                    </div>
                                </div>
                            </form>
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
include('../../../admin/layout/parte2.php');
include ('../../../layout/mensajes.php');
?>