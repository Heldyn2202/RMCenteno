<?php

// Validar y sanitizar el ID del rol
$id_rol = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
if (!$id_rol) {
    header('Location: ' . APP_URL . '/admin/roles');
    exit();
}

// Incluir archivos de configuración y controladores
require_once '../../app/config.php';
require_once '../../admin/layout/parte1.php';
require_once '../../app/controllers/roles/datos_del_rol.php';

?>

<!-- Content Wrapper. Contains page content -->
<main class="content-wrapper">
    <section class="content">
        <div class="container">
            <header>
                <h1>Rol: <?=htmlspecialchars($nombre_rol, ENT_QUOTES, 'UTF-8');?></h1>
            </header>
            <div class="row">
                <div class="col-md-6">
                    <article class="card card-outline card-info">
                        <header class="card-header">
                            <h2 class="card-title">Datos registrados</h2>
                        </header>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="nombre-rol">Nombre del rol</label>
                                        <p id="nombre-rol"><?=htmlspecialchars($nombre_rol, ENT_QUOTES, 'UTF-8');?></p>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <a href="<?=APP_URL;?>/admin/roles" class="btn btn-secondary">Volver</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </article>
                </div>
            </div>
        </div>
    </section>
</main>

<?php

// Incluir archivos de cierre
require_once '../../admin/layout/parte2.php';
require_once '../../layout/mensajes.php';

?>