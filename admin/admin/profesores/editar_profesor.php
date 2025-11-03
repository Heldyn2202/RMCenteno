<?php
include('../../app/config.php');
include('../../admin/layout/parte1.php');

// Verificar si se recibió el ID del profesor
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: listar_profesores.php");
    exit();
}

$id_profesor = $_GET['id'];

// Obtener los datos del profesor
$sql_profesor = "SELECT * FROM profesores WHERE id_profesor = :id_profesor";
$query_profesor = $pdo->prepare($sql_profesor);
$query_profesor->bindParam(':id_profesor', $id_profesor);
$query_profesor->execute();

$profesor = $query_profesor->fetch(PDO::FETCH_ASSOC);

if (!$profesor) {
    $_SESSION['error_message'] = "Profesor no encontrado";
    header("Location: listar_profesores.php");
    exit();
}

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cedula = $_POST['cedula'];
    $nombres = $_POST['nombres'];
    $apellidos = $_POST['apellidos'];
    $email = $_POST['email'];
    $telefono = $_POST['telefono'];
    $especialidad = $_POST['especialidad'];
    $usuario = $_POST['usuario'];
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];
    $estado = $_POST['estado'];

    // Validar los datoss
    if (empty($nombres) || empty($apellidos) || empty($cedula) || empty($usuario)) {
        $_SESSION['error_message'] = "Los campos cédula, nombres, apellidos y usuario son obligatorios";
    } elseif (!empty($password) && $password !== $password_confirm) {
        $_SESSION['error_message'] = "Las contraseñas no coinciden";
    } else {
        // Verificar si la cédula o usuario ya existen (excluyendo al profesor actual)
        $sql_verificar = "SELECT * FROM profesores WHERE (cedula = :cedula OR usuario = :usuario) AND id_profesor != :id_profesor";
        $query_verificar = $pdo->prepare($sql_verificar);
        $query_verificar->bindParam(':cedula', $cedula);
        $query_verificar->bindParam(':usuario', $usuario);
        $query_verificar->bindParam(':id_profesor', $id_profesor);
        $query_verificar->execute();
        
        if ($query_verificar->fetch()) {
            $_SESSION['error_message'] = "Ya existe otro profesor con esta cédula o nombre de usuario";
        } else {
            // Preparar la consulta SQL para actualizar
            if (!empty($password)) {
                // Si se proporcionó una nueva contraseña, actualizarla
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $sql_update = "UPDATE profesores SET 
                              cedula = :cedula, 
                              nombres = :nombres, 
                              apellidos = :apellidos, 
                              email = :email, 
                              telefono = :telefono, 
                              especialidad = :especialidad, 
                              usuario = :usuario,
                              password = :password,
                              estado = :estado 
                              WHERE id_profesor = :id_profesor";
            } else {
                // Si no se proporcionó contraseña, no actualizarla
                $sql_update = "UPDATE profesores SET 
                              cedula = :cedula, 
                              nombres = :nombres, 
                              apellidos = :apellidos, 
                              email = :email, 
                              telefono = :telefono, 
                              especialidad = :especialidad, 
                              usuario = :usuario,
                              estado = :estado 
                              WHERE id_profesor = :id_profesor";
            }
            
            $query_update = $pdo->prepare($sql_update);
            $query_update->bindParam(':cedula', $cedula);
            $query_update->bindParam(':nombres', $nombres);
            $query_update->bindParam(':apellidos', $apellidos);
            $query_update->bindParam(':email', $email);
            $query_update->bindParam(':telefono', $telefono);
            $query_update->bindParam(':especialidad', $especialidad);
            $query_update->bindParam(':usuario', $usuario);
            $query_update->bindParam(':estado', $estado);
            $query_update->bindParam(':id_profesor', $id_profesor);
            
            if (!empty($password)) {
                $query_update->bindParam(':password', $password_hash);
            }
            
            if ($query_update->execute()) {
                $_SESSION['success_message'] = "Profesor actualizado correctamente";
                header("Location: listar_profesores.php");
                exit();
            } else {
                $_SESSION['error_message'] = "Error al actualizar el profesor";
            }
        }
    }
}
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <br>
    <div class="content">
        <div class="container-fluid">
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h3 class="m-0">Editar Profesor: <?= htmlspecialchars($profesor['nombres'] . ' ' . $profesor['apellidos']); ?></h3>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="<?= APP_URL; ?>/admin">Inicio</a></li>
                                <li class="breadcrumb-item"><a href="<?= APP_URL; ?>/admin/profesores">Profesores</a></li>
                                <li class="breadcrumb-item active">Editar Profesor</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <br>
            
            <!-- Formulario principal -->
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Datos del Profesor</h3>
                </div>
                <form method="POST" action="">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="cedula">Cédula de Identidad</label>
                                    <input type="text" class="form-control" id="cedula" name="cedula" 
                                           value="<?= htmlspecialchars($profesor['cedula']); ?>"
                                           placeholder="Ej: V-12345678" required
                                           pattern="[VEve]-?\d{5,8}" 
                                           title="Formato válido: V-12345678 o E-12345678">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="nombres">Nombres</label>
                                    <input type="text" class="form-control" id="nombres" name="nombres" 
                                           value="<?= htmlspecialchars($profesor['nombres']); ?>"
                                           placeholder="Nombres del profesor" required
                                           pattern="[A-Za-záéíóúÁÉÍÓÚñÑ ]+" 
                                           title="Solo letras y espacios">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="apellidos">Apellidos</label>
                                    <input type="text" class="form-control" id="apellidos" name="apellidos" 
                                           value="<?= htmlspecialchars($profesor['apellidos']); ?>"
                                           placeholder="Apellidos del profesor" required
                                           pattern="[A-Za-záéíóúÁÉÍÓÚñÑ ]+" 
                                           title="Solo letras y espacios">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="email">Correo Electrónico</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?= htmlspecialchars($profesor['email']); ?>"
                                           placeholder="correo@ejemplo.com">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="telefono">Teléfono</label>
                                    <input type="tel" class="form-control" id="telefono" name="telefono" 
                                           value="<?= htmlspecialchars($profesor['telefono']); ?>"
                                           placeholder="Ej: 04121234567"
                                           pattern="[0-9]{11}" 
                                           title="11 dígitos (ej: 04121234567)">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="especialidad">Especialidad</label>
                                    <select id="especialidad" name="especialidad" class="form-control">
                                        <option value="">Seleccione una especialidad</option>
                                        <option value="Informática" <?= ($profesor['especialidad'] == 'Informática') ? 'selected' : ''; ?>>Informática</option>
                                        <option value="Matemáticas" <?= ($profesor['especialidad'] == 'Matemáticas') ? 'selected' : ''; ?>>Matemáticas</option>
                                        <option value="Idiomas" <?= ($profesor['especialidad'] == 'Idiomas') ? 'selected' : ''; ?>>Idiomas</option>
                                        <option value="Ciencias" <?= ($profesor['especialidad'] == 'Ciencias') ? 'selected' : ''; ?>>Ciencias</option>
                                        <option value="Sociales" <?= ($profesor['especialidad'] == 'Sociales') ? 'selected' : ''; ?>>Sociales</option>
                                        <option value="Tecnología" <?= ($profesor['especialidad'] == 'Tecnología') ? 'selected' : ''; ?>>Tecnología</option>
                                        <option value="Otra" <?= ($profesor['especialidad'] == 'Otra') ? 'selected' : ''; ?>>Otra</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="usuario">Nombre de Usuario</label>
                                    <input type="text" class="form-control" id="usuario" name="usuario" 
                                           value="<?= htmlspecialchars($profesor['usuario']); ?>"
                                           placeholder="Nombre de usuario para acceso" required
                                           pattern="[A-Za-z0-9_]+" 
                                           title="Solo letras, números y guiones bajos">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="password">Nueva Contraseña (dejar en blanco para no cambiar)</label>
                                    <input type="password" class="form-control" id="password" name="password" 
                                           placeholder="Nueva contraseña"
                                           minlength="6">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="password_confirm">Confirmar Nueva Contraseña</label>
                                    <input type="password" class="form-control" id="password_confirm" name="password_confirm" 
                                           placeholder="Repita la nueva contraseña"
                                           minlength="6">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="estado">Estado</label>
                                    <select id="estado" name="estado" class="form-control" required>
                                        <option value="1" <?= ($profesor['estado'] == 1) ? 'selected' : ''; ?>>Activo</option>
                                        <option value="0" <?= ($profesor['estado'] == 0) ? 'selected' : ''; ?>>Inactivo</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                        <a href="<?= APP_URL; ?>/admin/profesores/listar_profesores.php" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
include('../../admin/layout/parte2.php');
include('../../layout/mensajes.php');
?>

<!-- Validación adicional con JavaScript -->
<script>
$(document).ready(function() {
    // Validar formato de cédula
    $('#cedula').on('blur', function() {
        var cedula = $(this).val();
        if (cedula !== '') {
            if (!/^[VEve]-?\d{5,8}$/.test(cedula)) {
                alert('Formato de cédula no válido. Ejemplo: V-12345678 o E-12345678');
                $(this).focus();
            }
        }
    });

    // Validar que nombres y apellidos solo contengan letras
    $('#nombres, #apellidos').on('blur', function() {
        var value = $(this).val();
        if (value !== '' && !/^[A-Za-záéíóúÁÉÍÓÚñÑ ]+$/.test(value)) {
            alert('Este campo solo puede contener letras y espacios');
            $(this).focus();
        }
    });

    // Validar formato de teléfono
    $('#telefono').on('blur', function() {
        var telefono = $(this).val();
        if (telefono !== '' && !/^\d{11}$/.test(telefono)) {
            alert('El teléfono debe tener 11 dígitos (ej: 04121234567)');
            $(this).focus();
        }
    });
    
    // Validar formato de usuario
    $('#usuario').on('blur', function() {
        var usuario = $(this).val();
        if (usuario !== '' && !/^[A-Za-z0-9_]+$/.test(usuario)) {
            alert('El usuario solo puede contener letras, números y guiones bajos');
            $(this).focus();
        }
    });
    
    // Validar coincidencia de contraseñas solo si se ingresó una nueva contraseña
    $('#password_confirm').on('blur', function() {
        var password = $('#password').val();
        if (password !== '' && password !== $(this).val()) {
            alert('Las contraseñas no coinciden');
            $(this).focus();
        }
    });
});
</script>