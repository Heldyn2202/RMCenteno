<?php

// Validar y sanitizar el ID del rol
$id_rol = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
if (!$id_rol) {
    die("ID de rol no válido");
}

// Incluir archivos de configuración y controladores
if (!include('../../app/config.php')) {
    die('Error al cargar la configuración');
}

include ('../../admin/layout/parte1.php');
include ('../../app/controllers/roles/datos_del_rol.php');

// Generar token CSRF
session_start();
$csrf_token = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrf_token;

?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <br>
    <div class="content">
        <div class="container">
            <div class="row">
                <h1>Editar el rol: <?=htmlspecialchars($nombre_rol, ENT_QUOTES, 'UTF-8');?></h1>
            </div>
            <br>
            <div class="row">
                <div class="col-md-6">
                    <div class="card card-outline card-success">
                        <div class="card-header">
                            <h3 class="card-title">Datos registrados</h3>
                        </div>
                        <div class="card-body">
                            <form action="<?=APP_URL;?>/app/controllers/roles/update.php" method="post">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="">Nombre del rol</label>
                                            <input type="text" name="id_rol" value="<?=$id_rol;?>" hidden>
                                            <input type="text" class="form-control" name="nombre_rol" value="<?=htmlspecialchars($nombre_rol, ENT_QUOTES, 'UTF-8');?>" required maxlength="50">
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" name="csrf_token" value="<?=$csrf_token;?>">
                                <hr>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <button type="submit" class="btn btn-success">Actualizar</button>
                                            <a href="<?=APP_URL;?>/admin/roles" class="btn btn-secondary">Cancelar</a>
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

include ('../../admin/layout/parte2.php');
include ('../../layout/mensajes.php');

?>