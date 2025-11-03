<?php
include ('../../app/config.php');
include ('../../admin/layout/parte1.php');
include ('../../app/controllers/roles/listado_de_roles.php');
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <br>
    <div class="content">
        <div class="container">
            <div class="row">
                <h1>Creación de un nuevo usuario</h1>
            </div>
            <br>
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-primary">
                        <div class="card-body">
                            <form action="<?=APP_URL;?>/app/controllers/usuarios/create.php" method="post">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="">Rol del usuario</label>
                                            <div class="form-inline">
                                                <select name="rol_id" id="rol_id" class="form-control" required>
                                                    <?php
                                                    foreach ($roles as $role){ ?>
                                                        <option value="<?=$role['id_rol'];?>"><?=$role['nombre_rol'];?></option>
                                                        <?php
                                                    }
                                                    ?>
                                                </select>
                                                <a href="<?=APP_URL;?>/admin/roles/create.php" style="margin-left: 5px" class="btn btn-primary"><i class="bi bi-file-plus"></i></a>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="">Correo electrónico</label>
                                            <input type="email" name="email" class="form-control" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="">Contraseña</label>
                                            <input type="password" name="password" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="">Repetir contraseña</label>
                                            <input type="password" name="password_repet" class="form-control" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- CAMPOS ADICIONALES PARA DOCENTES -->
                                <div id="campos-docente" style="display: none; background: #f8f9fa; padding: 20px; border-radius: 5px; margin: 15px 0;">
                                    <h5><i class="fas fa-chalkboard-teacher"></i> Información del Docente</h5>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="cedula">Cédula <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="cedula" name="cedula" placeholder="Cédula del docente">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="nombres">Nombres <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="nombres" name="nombres" placeholder="Nombres del docente">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="apellidos">Apellidos <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="apellidos" name="apellidos" placeholder="Apellidos del docente">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="telefono">Teléfono</label>
                                                <input type="text" class="form-control" id="telefono" name="telefono" placeholder="Teléfono del docente">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="especialidad">Especialidad</label>
                                                <input type="text" class="form-control" id="especialidad" name="especialidad" placeholder="Especialidad del docente">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <hr>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <button type="submit" class="btn btn-primary">Registrar</button>
                                            <a href="<?=APP_URL;?>/admin/usuarios" class="btn btn-secondary">Cancelar</a>
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
// Mostrar/ocultar campos de docente según el rol seleccionado
document.getElementById('rol_id').addEventListener('change', function() {
    var camposDocente = document.getElementById('campos-docente');
    if (this.value == '5') { // ID del rol docente
        camposDocente.style.display = 'block';
        // Hacer obligatorios los campos de docente
        document.getElementById('cedula').required = true;
        document.getElementById('nombres').required = true;
        document.getElementById('apellidos').required = true;
    } else {
        camposDocente.style.display = 'none';
        // Quitar requerido
        document.getElementById('cedula').required = false;
        document.getElementById('nombres').required = false;
        document.getElementById('apellidos').required = false;
    }
});

// Verificar al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    var rolSelect = document.getElementById('rol_id');
    if (rolSelect.value == '5') {
        document.getElementById('campos-docente').style.display = 'block';
        document.getElementById('cedula').required = true;
        document.getElementById('nombres').required = true;
        document.getElementById('apellidos').required = true;
    }
});
</script>

<?php
include ('../../admin/layout/parte2.php');
include ('../../layout/mensajes.php');
?>