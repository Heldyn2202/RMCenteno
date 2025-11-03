<?php

$id_administrativo = $_GET['id'];

// Sanitize $id_administrativo (CRITICAL for security!)
$id_administrativo = filter_var($id_administrativo, FILTER_SANITIZE_NUMBER_INT);
if ($id_administrativo === false || $id_administrativo === null) {
    echo "Invalid administrative ID.";
    exit;
}

include('../../app/config.php');
include('../../admin/layout/parte1.php');

include('../../app/controllers/administrativos/datos_administrativos.php');
include('../../app/controllers/roles/listado_de_roles.php');

// Initialize variables (important to avoid warnings)
$nombres = isset($nombres) ? $nombres : "";
$apellidos = isset($apellidos) ? $apellidos : "";
$ci = isset($ci) ? $ci : "";
$fecha_nacimiento = isset($fecha_nacimiento) ? $fecha_nacimiento : "";
$celular = isset($celular) ? $celular : "";
$email = isset($email) ? $email : "";
$direccion = isset($direccion) ? $direccion : "";
$nombre_rol = isset($nombre_rol) ? $nombre_rol : "";

?>

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
                        <li class="breadcrumb-item"><a href="<?= APP_URL; ?>/admin/administrativos/">Lista administrativos</a></li>
                        <li class="breadcrumb-item active">Editar personal administrativo</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">  <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-success">
                        <div class="card-header">
                            <h3 class="card-title">Editar los datos</h3>
                        </div>
                        <div class="card-body">
                            <form action="<?= APP_URL; ?>/app/controllers/administrativos/update.php" id="miFormulario" method="post">
                                <input type="hidden" name="id_administrativo" value="<?= $id_administrativo; ?>">
                                <input type="hidden" name="id_usuario" value="<?= $id_usuario; ?>">
                                <input type="hidden" name="id_persona" value="<?= $id_persona; ?>">

                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="rol_id">Nombre del rol</label>  <a href="<?= APP_URL; ?>/admin/roles/create.php" class="btn btn-primary btn-sm float-right" title="Agregar Rol"><i class="bi bi-file-plus"></i></a> <select name="rol_id" id="rol_id" class="form-control"> <?php foreach ($roles as $role) : ?>
                                                    <option value="<?= $role['id_rol']; ?>" <?= ($nombre_rol == $role['nombre_rol']) ? 'selected' : ''; ?>>
                                                        <?= $role['nombre_rol']; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="nombres">Nombres</label> <input type="text" name="nombres" id="nombres" value="<?= $nombres; ?>" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="apellidos">Apellidos</label> <input type="text" name="apellidos" id="apellidos" value="<?= $apellidos; ?>" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="ci">Cédula de Identidad</label> <input type="number" name="ci" id="ci" value="<?= $ci; ?>" class="form-control" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="fecha_nacimiento">Fecha de Nacimiento</label> <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" value="<?= $fecha_nacimiento; ?>" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="celular">Número Telefónico</label> <input type="number" name="celular" id="celular" value="<?= $celular; ?>" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="email">Correo Electrónico</label> <input type="email" name="email" id="email" value="<?= $email; ?>" class="form-control" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="direccion">Dirección</label> <input type="address" name="direccion" id="direccion" value="<?= $direccion; ?>" class="form-control" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group text-center">  
                                            <button type="submit"  onclick="mostrarSweetAlert(event)" class="btn btn-success">Actualizar</button>
                                            <a href="<?= APP_URL; ?>/admin/administrativos" class="btn btn-secondary">Cancelar</a>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    function mostrarSweetAlert(event) {
    event.preventDefault(); // Previene el envío del formulario por defecto

    Swal.fire({
        title: '¿Estás seguro de actualizar los datos?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí',
        cancelButtonText: 'No'
    }).then((result) => {
        if (result.isConfirmed) {
            // Si el usuario hace clic en "sí", envía el formulario
            document.getElementById('miFormulario').submit();
        } else {
            // Si el usuario hace clic en "no", no hagas nada
        }
    });
}
</script>
<?php

include('../../admin/layout/parte2.php');
include('../../layout/mensajes.php');

?>
