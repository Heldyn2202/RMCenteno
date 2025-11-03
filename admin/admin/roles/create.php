<?php
// Incluir archivos de configuración y layout
include ('../../app/config.php');
include ('../../admin/layout/parte1.php');

// Verificar si el usuario tiene permisos para acceder a esta página
// Aquí podrías agregar una verificación de sesión o roles

?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <br>
    <div class="content">
        <div class="container">
            <div class="row">
                <h1>Creación de un nuevo rol</h1>
            </div>
            <br>
            <div class="row">
                <div class="col-md-6">
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Ingrese los datos</h3>
                        </div>
                        <div class="card-body">
                            <form action="<?= htmlspecialchars(APP_URL); ?>/app/controllers/roles/create.php" method="post">
                                <!-- Token CSRF para protección contra ataques CSRF -->
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>">
                                
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="nombre_rol">Nombre del rol</label>
                                            <input type="text" name="nombre_rol" id="nombre_rol" class="form-control" required>
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <button type="submit" class="btn btn-primary">Registrar</button>
                                            <a href="<?= htmlspecialchars(APP_URL); ?>/admin/roles" class="btn btn-secondary">Cancelar</a>
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
// Incluir el resto del layout
include ('../../admin/layout/parte2.php');
include ('../../layout/mensajes.php');
?>