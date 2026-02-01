<?php
$id_usuario = $_GET['id'];

include ('../../app/config.php');
include ('../../admin/layout/parte1.php');
include ('../../app/controllers/usuarios/datos_del_usuario.php');
include ('../../app/controllers/roles/listado_de_roles.php');

// Obtener datos del docente si existe
$datos_docente = null;
if ($rol_id == 5) {
    $sql_docente = "SELECT * FROM profesores WHERE email = ?";
    $query_docente = $pdo->prepare($sql_docente);
    $query_docente->execute([$email]);
    $datos_docente = $query_docente->fetch(PDO::FETCH_ASSOC);
}

// Guardar email original para validaciones
$email_original = $email;
$cedula_original = $datos_docente['cedula'] ?? '';
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <br>
    <div class="content">
        <div class="container">
            <div class="row">
                <h1>Modificar usuario: <?=$email;?></h1>
            </div>
            <br>
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-success">
                        <div class="card-body">
                            <form action="<?=APP_URL;?>/app/controllers/usuarios/update.php" method="post" id="formUsuario">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="">Rol del usuario</label>
                                            <input type="text" name="id_usuario" value="<?=$id_usuario;?>" hidden>
                                            <div class="form-inline">
                                                <select name="rol_id" id="rol_id" class="form-control" required>
                                                    <option value="">Seleccionar rol</option>
                                                    <?php
                                                    foreach ($roles as $role){
                                                        $nombre_rol_tabla = $role['nombre_rol'];?>
                                                        <option value="<?=$role['id_rol'];?>" <?php if($nombre_rol==$nombre_rol_tabla){ ?> selected="selected" <?php } ?> >
                                                            <?=$role['nombre_rol'];?>
                                                        </option>
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
                                            <label for="">Correo electrónico <span id="email-status" class="ml-2"></span></label>
                                            <input type="email" name="email" id="email" value="<?=$email;?>" class="form-control" required>
                                            <input type="hidden" id="email_original" value="<?=$email_original;?>">
                                            <div class="invalid-feedback" id="email-error"></div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="">Contraseña <small class="text-muted">(Dejar vacío para mantener la actual)</small></label>
                                            <input type="password" name="password" id="password" class="form-control" placeholder="Nueva contraseña">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="">Repetir contraseña <span id="password-match-status" class="ml-2"></span></label>
                                            <input type="password" name="password_repet" id="password_repet" class="form-control" placeholder="Repetir nueva contraseña">
                                            <div class="invalid-feedback" id="password-error"></div>
                                            <small class="form-text text-muted">La validación se activa al escribir</small>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- CAMPOS ADICIONALES PARA DOCENTES -->
                                <div id="campos-docente" style="<?= ($rol_id == 5) ? 'display: block;' : 'display: none;' ?> background: #f8f9fa; padding: 20px; border-radius: 5px; margin: 15px 0;">
                                    <h5><i class="fas fa-chalkboard-teacher"></i> Información del Docente</h5>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="cedula">Cédula <span class="text-danger">*</span> <span id="cedula-status" class="ml-2"></span></label>
                                                <input type="text" class="form-control" id="cedula" name="cedula" 
                                                       value="<?= $datos_docente['cedula'] ?? '' ?>" 
                                                       placeholder="Cédula del docente" 
                                                       <?= ($rol_id == 5) ? 'required' : '' ?>
                                                       maxlength="8">
                                                <input type="hidden" id="cedula_original" value="<?= $cedula_original ?>">
                                                <div class="invalid-feedback" id="cedula-error"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="nombres">Nombres <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="nombres" name="nombres" 
                                                       value="<?= $datos_docente['nombres'] ?? '' ?>" 
                                                       placeholder="Nombres del docente"
                                                       <?= ($rol_id == 5) ? 'required' : '' ?>>
                                                <div class="invalid-feedback" id="nombres-error"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="apellidos">Apellidos <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="apellidos" name="apellidos" 
                                                       value="<?= $datos_docente['apellidos'] ?? '' ?>" 
                                                       placeholder="Apellidos del docente"
                                                       <?= ($rol_id == 5) ? 'required' : '' ?>>
                                                <div class="invalid-feedback" id="apellidos-error"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="telefono">Teléfono</label>
                                                <input type="text" class="form-control" id="telefono" name="telefono" 
                                                       value="<?= $datos_docente['telefono'] ?? '' ?>" 
                                                       placeholder="Teléfono del docente"
                                                       maxlength="11">
                                                <div class="invalid-feedback" id="telefono-error"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="especialidad">Especialidad</label>
                                                <input type="text" class="form-control" id="especialidad" name="especialidad" 
                                                       value="<?= $datos_docente['especialidad'] ?? '' ?>" 
                                                       placeholder="Especialidad del docente">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <hr>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <button type="submit" class="btn btn-success" id="submit-btn">Actualizar</button>
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

<!-- Incluir SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Variables para controlar validaciones
let emailValido = true; // Inicialmente válido porque es el email existente
let cedulaValida = true; // Inicialmente válido si existe
let emailVerificado = true; // Inicialmente verificado porque es el email original
let cedulaVerificada = true; // Inicialmente verificado porque es la cédula original

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
        cedulaValida = true; // No es docente, no necesita validar cédula
        cedulaVerificada = true; // No necesita verificación
    }
});

// Validación en tiempo real de contraseñas - MEJORADA
document.getElementById('password_repet').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const passwordRepet = this.value;
    const statusElement = document.getElementById('password-match-status');
    const errorElement = document.getElementById('password-error');
    
    // Validar solo si hay algo escrito en ambos campos
    if (passwordRepet.length > 0 && password.length > 0) {
        // Verificar si se excedió la longitud de la contraseña original
        if (passwordRepet.length > password.length) {
            statusElement.innerHTML = '<i class="fas fa-times-circle text-danger"></i>';
            errorElement.textContent = 'La contraseña excede la longitud';
            errorElement.style.display = 'block';
            this.classList.remove('is-valid');
            this.classList.add('is-invalid');
            return;
        }
        
        // Validar coincidencia carácter por carácter
        let coincide = true;
        for (let i = 0; i < passwordRepet.length; i++) {
            if (passwordRepet[i] !== password[i]) {
                coincide = false;
                break;
            }
        }
        
        if (coincide) {
            if (passwordRepet.length === password.length) {
                statusElement.innerHTML = '<i class="fas fa-check-circle text-success"></i>';
                errorElement.style.display = 'none';
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            } else {
                statusElement.innerHTML = '<i class="fas fa-exclamation-circle text-warning"></i>';
                errorElement.style.display = 'none';
                this.classList.remove('is-invalid');
                this.classList.remove('is-valid');
            }
        } else {
            statusElement.innerHTML = '<i class="fas fa-times-circle text-danger"></i>';
            errorElement.textContent = 'Las contraseñas no coinciden';
            errorElement.style.display = 'block';
            this.classList.remove('is-valid');
            this.classList.add('is-invalid');
        }
    } else {
        statusElement.innerHTML = '';
        errorElement.style.display = 'none';
        this.classList.remove('is-invalid', 'is-valid');
    }
});

// También validar cuando se escribe en el primer campo de contraseña
document.getElementById('password').addEventListener('input', function() {
    const passwordRepet = document.getElementById('password_repet').value;
    if (passwordRepet.length > 0) {
        document.getElementById('password_repet').dispatchEvent(new Event('input'));
    }
});

// Validación de cédula (máximo 8 dígitos)
document.getElementById('cedula').addEventListener('input', function() {
    let cedula = this.value.replace(/\D/g, ''); // Solo números
    this.value = cedula.substring(0, 8); // Máximo 8 dígitos
    
    const cedulaOriginal = document.getElementById('cedula_original').value.trim();
    if (cedula !== cedulaOriginal) {
        cedulaVerificada = false; // Resetear verificación cuando se modifica
    }
});

// Validación de cédula duplicada (al perder foco)
document.getElementById('cedula').addEventListener('blur', function() {
    if (document.getElementById('rol_id').value === '5' && this.value.trim() !== '') {
        const cedulaActual = this.value.trim();
        const cedulaOriginal = document.getElementById('cedula_original').value.trim();
        
        // Solo validar si la cédula cambió
        if (cedulaActual !== cedulaOriginal) {
            validarCedula(cedulaActual);
        }
    }
});

// Función para validar cédula duplicada
function validarCedula(cedula) {
    if (cedula.length !== 8) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'La cédula debe tener exactamente 8 dígitos',
            confirmButtonColor: '#3085d6',
            timer: 3000
        });
        document.getElementById('cedula').classList.add('is-invalid');
        document.getElementById('cedula-status').innerHTML = '<i class="fas fa-times-circle text-danger"></i>';
        cedulaValida = false;
        cedulaVerificada = true; // Se verificó pero es inválida
        return;
    }
    
    // Mostrar loading
    const cedulaInput = document.getElementById('cedula');
    cedulaInput.disabled = true;
    document.getElementById('cedula-status').innerHTML = '<i class="fas fa-spinner fa-spin text-primary"></i>';
    
    fetch('<?=APP_URL;?>/app/controllers/usuarios/validar_cedula.php?cedula=' + cedula + '&id_usuario=<?=$id_usuario;?>')
        .then(response => response.json())
        .then(data => {
            cedulaInput.disabled = false;
            
            if (data.existe) {
                Swal.fire({
                    icon: 'error',
                    title: 'Cédula duplicada',
                    text: 'La cédula ya está registrada con otro usuario',
                    confirmButtonColor: '#3085d6',
                    timer: 3000
                });
                cedulaInput.classList.remove('is-valid');
                cedulaInput.classList.add('is-invalid');
                cedulaValida = false;
                document.getElementById('cedula-status').innerHTML = '<i class="fas fa-times-circle text-danger"></i>';
            } else {
                cedulaInput.classList.remove('is-invalid');
                cedulaInput.classList.add('is-valid');
                cedulaValida = true;
                document.getElementById('cedula-status').innerHTML = '<i class="fas fa-check-circle text-success"></i>';
                Swal.fire({
                    icon: 'success',
                    title: 'Cédula válida',
                    text: 'La cédula está disponible',
                    confirmButtonColor: '#3085d6',
                    timer: 2000,
                    showConfirmButton: false
                });
            }
            cedulaVerificada = true;
        })
        .catch(error => {
            console.error('Error:', error);
            cedulaInput.disabled = false;
            cedulaValida = false;
            cedulaVerificada = true;
            document.getElementById('cedula-status').innerHTML = '<i class="fas fa-times-circle text-danger"></i>';
        });
}

// Validación de nombres (solo letras y espacios)
document.getElementById('nombres').addEventListener('input', function() {
    const regex = /^[A-Za-záéíóúÁÉÍÓÚñÑ\s]*$/;
    if (this.value.trim() !== '' && !regex.test(this.value)) {
        this.value = this.value.replace(/[^A-Za-záéíóúÁÉÍÓÚñÑ\s]/g, '');
        Swal.fire({
            icon: 'warning',
            title: 'Carácter no permitido',
            text: 'Solo se permiten letras y espacios',
            confirmButtonColor: '#3085d6',
            timer: 2000
        });
    }
});

// Validación de apellidos (solo letras y espacios)
document.getElementById('apellidos').addEventListener('input', function() {
    const regex = /^[A-Za-záéíóúÁÉÍÓÚñÑ\s]*$/;
    if (this.value.trim() !== '' && !regex.test(this.value)) {
        this.value = this.value.replace(/[^A-Za-záéíóúÁÉÍÓÚñÑ\s]/g, '');
        Swal.fire({
            icon: 'warning',
            title: 'Carácter no permitido',
            text: 'Solo se permiten letras y espacios',
            confirmButtonColor: '#3085d6',
            timer: 2000
        });
    }
});

// Validación de teléfono (solo números, máximo 11 dígitos)
document.getElementById('telefono').addEventListener('input', function() {
    let telefono = this.value.replace(/\D/g, ''); // Solo números
    this.value = telefono.substring(0, 11); // Máximo 11 dígitos
});

// Validación de email duplicado (al perder foco, solo si cambió)
document.getElementById('email').addEventListener('blur', function() {
    const email = this.value.trim();
    const emailOriginal = document.getElementById('email_original').value.trim();
    
    if (email !== '' && email !== emailOriginal) {
        validarEmail(email);
        emailVerificado = false; // Resetear porque es un email nuevo
    }
});

// Función para validar email duplicado
function validarEmail(email) {
    // Validar formato de email
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        Swal.fire({
            icon: 'error',
            title: 'Formato inválido',
            text: 'Por favor ingrese un correo electrónico válido',
            confirmButtonColor: '#3085d6',
            timer: 3000
        });
        document.getElementById('email').classList.add('is-invalid');
        document.getElementById('email-status').innerHTML = '<i class="fas fa-times-circle text-danger"></i>';
        emailValido = false;
        emailVerificado = true;
        return;
    }
    
    // Mostrar loading
    const emailInput = document.getElementById('email');
    emailInput.disabled = true;
    document.getElementById('email-status').innerHTML = '<i class="fas fa-spinner fa-spin text-primary"></i>';
    
    fetch('<?=APP_URL;?>/app/controllers/usuarios/validar_email.php?email=' + encodeURIComponent(email) + '&id_usuario=<?=$id_usuario;?>')
        .then(response => response.json())
        .then(data => {
            emailInput.disabled = false;
            
            if (data.existe) {
                Swal.fire({
                    icon: 'error',
                    title: 'Email duplicado',
                    text: 'El correo electrónico ya está registrado',
                    confirmButtonColor: '#3085d6',
                    timer: 3000
                });
                emailInput.classList.remove('is-valid');
                emailInput.classList.add('is-invalid');
                emailValido = false;
                document.getElementById('email-status').innerHTML = '<i class="fas fa-times-circle text-danger"></i>';
            } else {
                emailInput.classList.remove('is-invalid');
                emailInput.classList.add('is-valid');
                emailValido = true;
                document.getElementById('email-status').innerHTML = '<i class="fas fa-check-circle text-success"></i>';
                Swal.fire({
                    icon: 'success',
                    title: 'Email disponible',
                    text: 'El correo electrónico está disponible',
                    confirmButtonColor: '#3085d6',
                    timer: 2000,
                    showConfirmButton: false
                });
            }
            emailVerificado = true;
        })
        .catch(error => {
            console.error('Error:', error);
            emailInput.disabled = false;
            emailValido = false;
            emailVerificado = true;
            document.getElementById('email-status').innerHTML = '<i class="fas fa-times-circle text-danger"></i>';
        });
}

// Validación del formulario antes de enviar
document.getElementById('formUsuario').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const rolId = document.getElementById('rol_id').value;
    const email = document.getElementById('email').value.trim();
    const emailOriginal = document.getElementById('email_original').value.trim();
    const password = document.getElementById('password').value;
    const passwordRepet = document.getElementById('password_repet').value;
    
    // Validaciones básicas
    if (rolId === '') {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Debe seleccionar un rol',
            confirmButtonColor: '#3085d6'
        });
        return;
    }
    
    // Si el email cambió, debe estar verificado
    if (email !== emailOriginal && !emailVerificado) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Debe validar el correo electrónico (escriba y presione fuera del campo)',
            confirmButtonColor: '#3085d6'
        });
        return;
    }
    
    if (email !== emailOriginal && !emailValido) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'El correo electrónico no es válido o ya está registrado',
            confirmButtonColor: '#3085d6'
        });
        return;
    }
    
    // Validar contraseñas si se ingresaron
    if (password !== '' && passwordRepet !== '' && password !== passwordRepet) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Las contraseñas no coinciden',
            confirmButtonColor: '#3085d6'
        });
        return;
    }
    
    // Validaciones específicas para docentes
    if (rolId === '5') {
        const cedula = document.getElementById('cedula').value.trim();
        const cedulaOriginal = document.getElementById('cedula_original').value.trim();
        const nombres = document.getElementById('nombres').value.trim();
        const apellidos = document.getElementById('apellidos').value.trim();
        
        // Si la cédula cambió, debe estar verificada
        if (cedula !== cedulaOriginal && !cedulaVerificada) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Debe validar la cédula (escriba y presione fuera del campo)',
                confirmButtonColor: '#3085d6'
            });
            return;
        }
        
        if (cedula.length !== 8) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'La cédula debe tener 8 dígitos',
                confirmButtonColor: '#3085d6'
            });
            return;
        }
        
        if (cedula !== cedulaOriginal && !cedulaValida) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'La cédula ya está registrada',
                confirmButtonColor: '#3085d6'
            });
            return;
        }
        
        if (nombres === '' || apellidos === '') {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Los nombres y apellidos son obligatorios para docentes',
                confirmButtonColor: '#3085d6'
            });
            return;
        }
    }
    
    // Si todas las validaciones pasan, mostrar confirmación y enviar
    Swal.fire({
        title: '¿Está seguro?',
        text: '¿Desea actualizar este usuario?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, actualizar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            this.submit();
        }
    });
});

// Inicializar estado del email
document.addEventListener('DOMContentLoaded', function() {
    const email = document.getElementById('email').value.trim();
    const emailOriginal = document.getElementById('email_original').value.trim();
    if (email === emailOriginal) {
        document.getElementById('email').classList.add('is-valid');
        document.getElementById('email-status').innerHTML = '<i class="fas fa-check-circle text-success"></i>';
    }
    
    // Inicializar estado de la cédula si es docente
    if (document.getElementById('rol_id').value == '5') {
        const cedula = document.getElementById('cedula').value.trim();
        const cedulaOriginal = document.getElementById('cedula_original').value.trim();
        if (cedula === cedulaOriginal && cedula !== '') {
            document.getElementById('cedula').classList.add('is-valid');
            document.getElementById('cedula-status').innerHTML = '<i class="fas fa-check-circle text-success"></i>';
        }
    }
});
</script>

<?php
include ('../../admin/layout/parte2.php');
include ('../../layout/mensajes.php');
?>