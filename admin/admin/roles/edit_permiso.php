<?php
// Validar y sanitizar el ID del permiso
$id_permiso = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
if (!$id_permiso) {
    die("ID de permiso no válido");
}

// Incluir archivos de configuración y layout
require '../../app/config.php';
require '../../admin/layout/parte1.php';

// Obtener datos del permiso
require '../../app/controllers/roles/datos_permiso.php';
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <br>
    <div class="content">
        <div class="container">
            <div class="row">
                <h1>Modificación de un nuevo permiso</h1>
            </div>
            <br>
            <div class="row">
                <div class="col-md-6">
                    <div class="card card-outline card-success">
                        <div class="card-header">
                            <h3 class="card-title">Ingrese los datos</h3>
                        </div>
                        <div class="card-body">
                            <form action="<?= APP_URL; ?>/app/controllers/roles/update_permisos.php" method="post">
                                <input type="hidden" name="id_permiso" value="<?= $id_permiso; ?>">
                                <div class="form-group">
                                    <label for="nombre_url">Nombre de la URL</label>
                                    <input type="text" id="nombre_url" name="nombre_url" value="<?= htmlspecialchars($nombre_url); ?>" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label for="url">URL</label>
                                    <input type="text" id="url" name="url" value="<?= htmlspecialchars($url); ?>" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <button type="submit" class="btn btn-success">Actualizar</button>
                                    <a href="<?= APP_URL; ?>/admin/roles/permisos.php" class="btn btn-secondary">Cancelar</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Incluir el resto del layout
require '../../admin/layout/parte2.php';
require '../../layout/mensajes.php';
?>