<?php
ob_start();
include('../../app/config.php');
include('../../app/auth.php');

// Especificar los roles que pueden acceder (en mayúsculas como están en tu BD)
authMiddleware(['ADMINISTRADOR', 'DIRECTOR', 'SUBDIRECTOR', 'PERSONAL ADMINISTRATIVO']);

include('../../admin/layout/parte1.php');

// Obtener roles disponibles de la base de datos
$sql_roles = "SELECT nombre FROM tb_roles WHERE estado = '1' ORDER BY nombre";
$query_roles = $pdo->prepare($sql_roles);
$query_roles->execute();
$roles = $query_roles->fetchAll(PDO::FETCH_COLUMN);

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $pdo->beginTransaction();
        
        // Datos personales
        $cedula = trim($_POST['cedula']);
        $nombres = trim($_POST['nombres']);
        $apellidos = trim($_POST['apellidos']);
        $email = trim($_POST['email']);
        $telefono = trim($_POST['telefono']);
        $especialidad = trim($_POST['especialidad']);
        
        // Datos de acceso
        $usuario = trim($_POST['usuario']);
        $password = $_POST['password'];
        $password_confirm = $_POST['password_confirm'];
        $rol = trim($_POST['rol']); // Rol seleccionado del formulario
        
        // Validaciones
        if (empty($nombres) || empty($apellidos) || empty($cedula) || empty($usuario) || empty($password) || empty($rol)) {
            throw new Exception("Los campos cédula, nombres, apellidos, usuario, contraseña y rol son obligatorios");
        }
        
        if ($password !== $password_confirm) {
            throw new Exception("Las contraseñas no coinciden");
        }
        
        if (strlen($password) < 8) {
            throw new Exception("La contraseña debe tener al menos 8 caracteres");
        }
        
        // Verificar si el profesor ya existe
        $sql_verificar = "SELECT 1 FROM tb_profesores WHERE cedula = :cedula OR usuario = :usuario";
        $query_verificar = $pdo->prepare($sql_verificar);
        $query_verificar->execute([':cedula' => $cedula, ':usuario' => $usuario]);
        
        if ($query_verificar->fetch()) {
            throw new Exception("Ya existe un profesor con esta cédula o nombre de usuario");
        }
        
        // Encriptar la contraseña
        $password_hash = password_hash($password, PASSWORD_BCRYPT);
        
        // Insertar el nuevo profesor
        $sql_insert = "INSERT INTO tb_profesores (
            cedula, nombres, apellidos, email, telefono, especialidad, 
            usuario, password, estado, rol, fyh_creacion
        ) VALUES (
            :cedula, :nombres, :apellidos, :email, :telefono, :especialidad,
            :usuario, :password, '1', :rol, NOW()
        )";
        
        $query_insert = $pdo->prepare($sql_insert);
        $query_insert->execute([
            ':cedula' => $cedula,
            ':nombres' => $nombres,
            ':apellidos' => $apellidos,
            ':email' => $email,
            ':telefono' => $telefono,
            ':especialidad' => $especialidad,
            ':usuario' => $usuario,
            ':password' => $password_hash,
            ':rol' => $rol
        ]);
        
        $pdo->commit();
        $_SESSION['success_message'] = "Profesor registrado correctamente";
        header("Location: listar_profesores.php");
        exit();
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error_message'] = $e->getMessage();
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
                            <h3 class="m-0">Registro de Nuevo Profesor</h3>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="<?= APP_URL; ?>/admin">Inicio</a></li>
                                <li class="breadcrumb-item"><a href="<?= APP_URL; ?>/admin/profesores">Profesores</a></li>
                                <li class="breadcrumb-item active"><a href="<?= APP_URL; ?>/admin/profesores/listar_profesores.php">Listado de Profesores</a></li>
                                <li class="breadcrumb-item active">Nuevo Profesor</li>
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
                                           placeholder="Ej: V-12345678" required
                                           pattern="[VEve]-?\d{5,8}" 
                                           title="Formato válido: V-12345678 o E-12345678">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="nombres">Nombres</label>
                                    <input type="text" class="form-control" id="nombres" name="nombres" 
                                           placeholder="Nombres del profesor" required
                                           pattern="[A-Za-záéíóúÁÉÍÓÚñÑ ]+" 
                                           title="Solo letras y espacios">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="apellidos">Apellidos</label>
                                    <input type="text" class="form-control" id="apellidos" name="apellidos" 
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
                                           placeholder="correo@ejemplo.com" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="telefono">Teléfono</label>
                                    <input type="tel" class="form-control" id="telefono" name="telefono" 
                                           placeholder="Ej: 04121234567"
                                           pattern="[0-9]{11}" 
                                           title="11 dígitos (ej: 04121234567)">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="especialidad">Especialidad</label>
                                    <select id="especialidad" name="especialidad" class="form-control" required>
                                        <option value="">Seleccione una especialidad</option>
                                        <option value="Informática">Informática</option>
                                        <option value="Matemáticas">Matemáticas</option>
                                        <option value="Idiomas">Idiomas</option>
                                        <option value="Ciencias">Ciencias</option>
                                        <option value="Sociales">Sociales</option>
                                        <option value="Tecnología">Tecnología</option>
                                        <option value="Otra">Otra</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="usuario">Nombre de Usuario</label>
                                    <input type="text" class="form-control" id="usuario" name="usuario" 
                                           placeholder="Nombre de usuario para acceso" required
                                           pattern="[A-Za-z0-9_]+" 
                                           title="Solo letras, números y guiones bajos">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="rol">Rol</label>
                                    <select id="rol" name="rol" class="form-control" required>
                                        <option value="">Seleccione un rol</option>
                                        <?php foreach ($roles as $rol_option): ?>
                                            <option value="<?= htmlspecialchars($rol_option) ?>">
                                                <?= htmlspecialchars($rol_option) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="password">Contraseña</label>
                                    <input type="password" class="form-control" id="password" name="password" 
                                           placeholder="Contraseña" required
                                           minlength="8" 
                                           pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$"
                                           title="Debe contener al menos 8 caracteres, una mayúscula, una minúscula y un número">
                                    <small class="text-muted">Mínimo 8 caracteres, incluyendo mayúsculas, minúsculas y números</small>
                                    <div id="password-strength" class="small ml-2"></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="password_confirm">Confirmar Contraseña</label>
                                    <input type="password" class="form-control" id="password_confirm" name="password_confirm" 
                                           placeholder="Repita la contraseña" required
                                           minlength="8">
                                    <div id="password-match" class="small ml-2"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Guardar Profesor</button>
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
    
    // Validar fortaleza de contraseña
    $('#password').on('keyup', function() {
        var password = $(this).val();
        var strength = 0;
        
        if (password.length >= 8) strength++;
        if (password.match(/[a-z]+/)) strength++;
        if (password.match(/[A-Z]+/)) strength++;
        if (password.match(/[0-9]+/)) strength++;
        if (password.match(/[^a-zA-Z0-9]+/)) strength++;
        
        var strengthText = ['Muy débil', 'Débil', 'Moderada', 'Fuerte', 'Muy fuerte'][strength];
        var strengthClass = ['danger', 'warning', 'info', 'primary', 'success'][strength];
        
        $('#password-strength').html('Seguridad: <span class="text-' + strengthClass + '">' + strengthText + '</span>');
    });

    // Validar coincidencia de contraseñas en tiempo real
    $('#password, #password_confirm').on('keyup', function() {
        if ($('#password').val() !== $('#password_confirm').val()) {
            $('#password-match').html('<span class="text-danger">Las contraseñas no coinciden</span>');
        } else {
            $('#password-match').html('<span class="text-success">Las contraseñas coinciden</span>');
        }
    });
    
    // Mostrar/ocultar contraseña
    $('#togglePassword').click(function() {
        var passwordInput = $('#password');
        var passwordConfirmInput = $('#password_confirm');
        var type = passwordInput.attr('type') === 'password' ? 'text' : 'password';
        passwordInput.attr('type', type);
        passwordConfirmInput.attr('type', type);
        $(this).find('i').toggleClass('fa-eye fa-eye-slash');
    });
});
</script>

<?php
// Limpiar buffer de salida al final del archivo
ob_end_flush();
?>