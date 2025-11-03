<?php
include ('../../../app/config.php');
include ('../../../admin/layout/parte1.php');
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
                            <h1 class="m-0">Crear nuevo Grado</h1>
                        </div><!-- /.col -->
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="<?=APP_URL;?>/admin">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="<?=APP_URL;?>/admin/configuraciones/grados">Grados</a></li>
                                <li class="breadcrumb-item">Crear nuevo Grado</li>
                            </ol>
                        </div><!-- /.col -->
                    </div><!-- /.row -->
                </div><!-- /.container-fluid -->
            </div>
            <br>
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-primary">
                        <div class="card-body">
                            <form action="<?=APP_URL;?>/app/controllers/grados/create.php" method="post">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="">Nivel</label>
                                            <select name="nivel" id="nivel" class="form-control" required>
                                                <option value="">Seleccione un nivel</option>
                                                <option value="Primaria">PRIMARIA</option>
                                                <option value="Secundaria">SECUNDARIA</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="">Grado o Año</label>
                                            <input type="text" name="grado" id="grado" class="form-control" required placeholder="Escriba el grado o año" style="text-transform: uppercase;">
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group text-center">
                                            <button type="submit" class="btn btn-primary">Registrar</button>
                                            <a href="<?=APP_URL;?>/admin/configuraciones/grados" class="btn btn-secondary">Cancelar</a>
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

<script>
// Convierte automáticamente a mayúsculas mientras se escribe
document.getElementById('grado').addEventListener('input', function() {
    this.value = this.value.toUpperCase();
});
</script>

<?php
include ('../../../admin/layout/parte2.php');
include ('../../../layout/mensajes.php');
?>
