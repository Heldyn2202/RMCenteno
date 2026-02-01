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
                            <form action="<?=APP_URL;?>/app/controllers/usuarios/create.php" method="post" id="formUsuario">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="">Rol del usuario <span class="text-danger">*</span></label>
                                            <div class="form-inline">
                                                <select name="rol_id" id="rol_id" class="form-control" required>
                                                    <option value="">Seleccionar rol</option>
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
                                            <label for="">Correo electrónico <span class="text-danger">*</span> <span id="email-status" class="ml-2"></span></label>
                                            <input type="email" name="email" id="email" class="form-control" required>
                                            <div class="invalid-feedback" id="email-error"></div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="">Contraseña <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <input type="password" name="password" id="password" class="form-control" required>
                                                <div class="input-group-append">
                                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="">Repetir contraseña <span class="text-danger">*</span> <span id="password-match-status" class="ml-2"></span></label>
                                            <div class="input-group">
                                                <input type="password" name="password_repet" id="password_repet" class="form-control" required>
                                                <div class="input-group-append">
                                                    <button class="btn btn-outline-secondary" type="button" id="togglePasswordRepet">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="invalid-feedback" id="password-error"></div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- CAMPOS ADICIONALES PARA ROLES CON DATOS PERSONALES -->
                                <div id="campos-personales" style="display: none; background: #f8f9fa; padding: 20px; border-radius: 5px; margin: 15px 0;">
                                    <h5><i class="fas fa-user-circle"></i> Información Personal <span class="text-danger">* Campos obligatorios</span></h5>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="cedula">Cédula <span class="text-danger">*</span> <span id="cedula-status" class="ml-2"></span></label>
                                                <input type="text" class="form-control" id="cedula" name="cedula" placeholder="Ej: 12345678" maxlength="8">
                                                <div class="invalid-feedback" id="cedula-error"></div>
                                                <small class="form-text text-muted">8 dígitos sin puntos ni guiones</small>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="nombres">Nombres <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="nombres" name="nombres" placeholder="Nombres completos">
                                                <div class="invalid-feedback" id="nombres-error"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="apellidos">Apellidos <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="apellidos" name="apellidos" placeholder="Apellidos completos">
                                                <div class="invalid-feedback" id="apellidos-error"></div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Campos para TODOS los roles con datos personales -->
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="telefono_comun">Teléfono</label>
                                                <input type="text" class="form-control" id="telefono_comun" name="telefono" placeholder="Ej: 04121234567" maxlength="11">
                                                <small class="form-text text-muted">Máximo 11 dígitos</small>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="direccion">Dirección</label>
                                                <input type="text" class="form-control" id="direccion" name="direccion" placeholder="Dirección completa">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Campos específicos para DOCENTES -->
                                    <div id="campos-docente-extra" style="display: none;">
                                        <hr>
                                        <h6><i class="fas fa-chalkboard-teacher"></i> Información Específica de Docente</h6>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="especialidad">Especialidad</label>
                                                    <input type="text" class="form-control" id="especialidad" name="especialidad" placeholder="Ej: Matemáticas, Ciencias, etc.">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Campos específicos para ADMINISTRATIVOS -->
                                    <div id="campos-admin-extra" style="display: none;">
                                        <hr>
                                        <h6><i class="fas fa-briefcase"></i> Información Administrativa</h6>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="departamento">Departamento/Cargo</label>
                                                    <input type="text" class="form-control" id="departamento" name="departamento" placeholder="Ej: Secretaría, Contabilidad, etc.">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Campo de cédula para roles SIN datos personales (como administrador) -->
                                <div id="cedula-simple" style="display: none; background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 15px 0;">
                                    <h6><i class="fas fa-id-card"></i> Identificación</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="cedula_admin">Cédula (Opcional)</label>
                                                <input type="text" class="form-control" id="cedula_admin" name="cedula_simple" placeholder="Cédula para administrador" maxlength="8">
                                                <small class="form-text text-muted">Opcional para administradores</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <hr>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <button type="submit" class="btn btn-primary" id="submit-btn">Registrar</button>
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
let emailValido = false;
let cedulaValida = false;
let emailVerificado = false;
let cedulaVerificada = false;
let passwordValida = false;

// Roles que requieren datos personales COMPLETOS
const ROLES_CON_PERSONALES = ['2', '3', '4', '5']; // Administrativo, Estudiante, Representante, Docente
// Roles que NO requieren datos personales (solo cédula opcional)
const ROLES_SIN_PERSONALES = ['1']; // Administrador

// Mostrar/ocultar campos según el rol seleccionado
document.getElementById('rol_id').addEventListener('change', function() {
    const rolId = this.value;
    const camposPersonales = document.getElementById('campos-personales');
    const camposDocenteExtra = document.getElementById('campos-docente-extra');
    const camposAdminExtra = document.getElementById('campos-admin-extra');
    const cedulaSimple = document.getElementById('cedula-simple');
    
    // Resetear estados de validación
    cedulaValida = false;
    cedulaVerificada = false;
    
    // Resetear campos
    document.getElementById('cedula').value = '';
    document.getElementById('nombres').value = '';
    document.getElementById('apellidos').value = '';
    document.getElementById('telefono_comun').value = '';
    document.getElementById('direccion').value = '';
    document.getElementById('especialidad').value = '';
    document.getElementById('departamento').value = '';
    document.getElementById('cedula_admin').value = '';
    
    // Ocultar todos los campos primero
    camposPersonales.style.display = 'none';
    camposDocenteExtra.style.display = 'none';
    camposAdminExtra.style.display = 'none';
    cedulaSimple.style.display = 'none';
    
    // Quitar requerido de todos los campos
    document.getElementById('cedula').required = false;
    document.getElementById('nombres').required = false;
    document.getElementById('apellidos').required = false;
    
    // Si el rol requiere datos personales COMPLETOS
    if (ROLES_CON_PERSONALES.includes(rolId)) {
        camposPersonales.style.display = 'block';
        
        // Hacer obligatorios los campos básicos
        document.getElementById('cedula').required = true;
        document.getElementById('nombres').required = true;
        document.getElementById('apellidos').required = true;
        
        // Mostrar campos específicos por rol
        if (rolId === '5') { // DOCENTE
            camposDocenteExtra.style.display = 'block';
            camposAdminExtra.style.display = 'none';
        } 
        else if (rolId === '2') { // ADMINISTRATIVO
            camposDocenteExtra.style.display = 'none';
            camposAdminExtra.style.display = 'block';
        }
        else { // Otros roles (estudiante, representante)
            camposDocenteExtra.style.display = 'none';
            camposAdminExtra.style.display = 'none';
        }
    } 
    // Si el rol NO requiere datos personales (solo cédula opcional)
    else if (ROLES_SIN_PERSONALES.includes(rolId)) {
        cedulaSimple.style.display = 'block';
        cedulaValida = true; // Para que pase la validación (es opcional)
        cedulaVerificada = true;
    }
    // Si no es ninguno de los anteriores
    else {
        // No mostrar campos adicionales
        cedulaValida = true;
        cedulaVerificada = true;
    }
});

// Función para mostrar/ocultar contraseña
function setupPasswordToggle(inputId, buttonId) {
    const toggleButton = document.getElementById(buttonId);
    const passwordInput = document.getElementById(inputId);
    
    if (toggleButton && passwordInput) {
        toggleButton.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
        });
    }
}

// Configurar ojitos para contraseñas
setupPasswordToggle('password', 'togglePassword');
setupPasswordToggle('password_repet', 'togglePasswordRepet');

// Validación en tiempo real de contraseñas
document.getElementById('password_repet').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const passwordRepet = this.value;
    const statusElement = document.getElementById('password-match-status');
    const errorElement = document.getElementById('password-error');
    
    if (passwordRepet.length > 0) {
        // Validar carácter por carácter
        let coincide = true;
        for (let i = 0; i < passwordRepet.length; i++) {
            if (i >= password.length || passwordRepet[i] !== password[i]) {
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
                passwordValida = true;
            } else {
                statusElement.innerHTML = '<i class="fas fa-exclamation-circle text-warning"></i>';
                errorElement.style.display = 'none';
                this.classList.remove('is-invalid');
                this.classList.remove('is-valid');
                passwordValida = false;
            }
        } else {
            statusElement.innerHTML = '<i class="fas fa-times-circle text-danger"></i>';
            errorElement.textContent = 'La contraseña no coincide';
            errorElement.style.display = 'block';
            this.classList.remove('is-valid');
            this.classList.add('is-invalid');
            passwordValida = false;
        }
    } else {
        statusElement.innerHTML = '';
        errorElement.style.display = 'none';
        this.classList.remove('is-invalid', 'is-valid');
        passwordValida = false;
    }
});

// Validar cuando se escribe en el primer campo de contraseña
document.getElementById('password').addEventListener('input', function() {
    const passwordRepet = document.getElementById('password_repet').value;
    if (passwordRepet.length > 0) {
        document.getElementById('password_repet').dispatchEvent(new Event('input'));
    }
});

// Validación de cédula (formato)
function validarFormatoCedula(cedula) {
    const cedulaLimpiada = cedula.replace(/\D/g, '');
    return cedulaLimpiada.length === 8 && /^\d+$/.test(cedulaLimpiada);
}

// Validación de cédula principal (para roles con datos personales)
document.getElementById('cedula').addEventListener('input', function() {
    let cedula = this.value.replace(/\D/g, '');
    this.value = cedula.substring(0, 8);
    cedulaVerificada = false;
    
    // Validar formato en tiempo real
    if (cedula.length === 8) {
        this.classList.add('is-valid');
        this.classList.remove('is-invalid');
    } else if (cedula.length > 0) {
        this.classList.add('is-invalid');
        this.classList.remove('is-valid');
    } else {
        this.classList.remove('is-invalid', 'is-valid');
    }
});

// Validación de cédula duplicada - para roles que la requieren
document.getElementById('cedula').addEventListener('blur', function() {
    const rolId = document.getElementById('rol_id').value;
    const cedulaValue = this.value.trim();
    
    if (ROLES_CON_PERSONALES.includes(rolId) && cedulaValue !== '') {
        if (!validarFormatoCedula(cedulaValue)) {
            Swal.fire({
                icon: 'error',
                title: 'Formato inválido',
                text: 'La cédula debe tener 8 dígitos numéricos',
                confirmButtonColor: '#3085d6',
                timer: 3000
            });
            this.classList.add('is-invalid');
            document.getElementById('cedula-status').innerHTML = '<i class="fas fa-times-circle text-danger"></i>';
            cedulaValida = false;
            cedulaVerificada = true;
            return;
        }
        validarCedulaDuplicada(cedulaValue, 'cedula');
    }
});

// Validación de cédula simple (para administradores)
document.getElementById('cedula_admin').addEventListener('blur', function() {
    const cedulaValue = this.value.trim();
    if (cedulaValue !== '' && !validarFormatoCedula(cedulaValue)) {
        Swal.fire({
            icon: 'error',
            title: 'Formato inválido',
            text: 'La cédula debe tener 8 dígitos numéricos',
            confirmButtonColor: '#3085d6',
            timer: 3000
        });
        this.value = '';
    }
});

// Función para validar cédula duplicada
function validarCedulaDuplicada(cedula, campoId) {
    if (cedula.length !== 8) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'La cédula debe tener exactamente 8 dígitos',
            confirmButtonColor: '#3085d6',
            timer: 3000
        });
        document.getElementById(campoId).classList.add('is-invalid');
        document.getElementById('cedula-status').innerHTML = '<i class="fas fa-times-circle text-danger"></i>';
        cedulaValida = false;
        cedulaVerificada = true;
        return;
    }
    
    const cedulaInput = document.getElementById(campoId);
    cedulaInput.disabled = true;
    document.getElementById('cedula-status').innerHTML = '<i class="fas fa-spinner fa-spin text-primary"></i>';
    
    // URL para validar cédula
    const url = '<?=APP_URL;?>/app/controllers/usuarios/validar_cedula.php?cedula=' + cedula;
    
    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error('Error en la respuesta del servidor: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            cedulaInput.disabled = false;
            
            if (data.existe) {
                Swal.fire({
                    icon: 'error',
                    title: 'Cédula duplicada',
                    text: 'La cédula ya está registrada en el sistema',
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
            Swal.fire({
                icon: 'error',
                title: 'Error de conexión',
                text: 'No se pudo validar la cédula. Intente nuevamente.',
                confirmButtonColor: '#3085d6'
            });
        });
}

// Validación de nombres (solo letras y espacios)
function setupValidacionTexto(inputId) {
    const input = document.getElementById(inputId);
    if (input) {
        input.addEventListener('input', function() {
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
    }
}

// Configurar validación para nombres y apellidos
setupValidacionTexto('nombres');
setupValidacionTexto('apellidos');

// Validación de teléfono
document.getElementById('telefono_comun').addEventListener('input', function() {
    let telefono = this.value.replace(/\D/g, '');
    this.value = telefono.substring(0, 11);
});

// Validación de email duplicado
document.getElementById('email').addEventListener('blur', function() {
    const email = this.value.trim();
    if (email !== '') {
        validarEmailDuplicado(email);
    }
});

// Función para validar email duplicado
function validarEmailDuplicado(email) {
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
    
    const emailInput = document.getElementById('email');
    emailInput.disabled = true;
    document.getElementById('email-status').innerHTML = '<i class="fas fa-spinner fa-spin text-primary"></i>';
    
    const url = '<?=APP_URL;?>/app/controllers/usuarios/validar_email.php?email=' + encodeURIComponent(email);
    
    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error('Error en la respuesta del servidor: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            emailInput.disabled = false;
            
            if (data.existe) {
                Swal.fire({
                    icon: 'error',
                    title: 'Email duplicado',
                    text: 'El correo electrónico ya está registrado en el sistema',
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
            Swal.fire({
                icon: 'error',
                title: 'Error de conexión',
                text: 'No se pudo validar el email. Intente nuevamente.',
                confirmButtonColor: '#3085d6'
            });
        });
}

// Validación del formulario
document.getElementById('formUsuario').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const rolId = document.getElementById('rol_id').value;
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;
    const passwordRepet = document.getElementById('password_repet').value;
    
    // Validación 1: Rol obligatorio
    if (rolId === '') {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Debe seleccionar un rol',
            confirmButtonColor: '#3085d6'
        });
        return;
    }
    
    // Validación 2: Email verificado
    if (!emailVerificado) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Debe validar el correo electrónico',
            confirmButtonColor: '#3085d6'
        });
        return;
    }
    
    if (!emailValido) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'El correo electrónico no es válido o ya está registrado',
            confirmButtonColor: '#3085d6'
        });
        return;
    }
    
    // Validación 3: Contraseñas coinciden
    if (!passwordValida || password !== passwordRepet) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Las contraseñas no coinciden o no son válidas',
            confirmButtonColor: '#3085d6'
        });
        return;
    }
    
    // Validación 4: Para roles con datos personales
    if (ROLES_CON_PERSONALES.includes(rolId)) {
        const cedula = document.getElementById('cedula').value.trim();
        const nombres = document.getElementById('nombres').value.trim();
        const apellidos = document.getElementById('apellidos').value.trim();
        
        // Cédula obligatoria y validada
        if (!cedulaVerificada) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Debe validar la cédula',
                confirmButtonColor: '#3085d6'
            });
            return;
        }
        
        if (!cedulaValida) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'La cédula no es válida o ya está registrada',
                confirmButtonColor: '#3085d6'
            });
            return;
        }
        
        if (cedula.length !== 8 || !/^\d+$/.test(cedula)) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'La cédula debe tener 8 dígitos numéricos',
                confirmButtonColor: '#3085d6'
            });
            return;
        }
        
        // Nombres y apellidos obligatorios
        if (nombres === '' || apellidos === '') {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Los nombres y apellidos son obligatorios para este rol',
                confirmButtonColor: '#3085d6'
            });
            return;
        }
    }
    
    // Para administradores con cédula opcional
    if (ROLES_SIN_PERSONALES.includes(rolId)) {
        const cedulaAdmin = document.getElementById('cedula_admin').value.trim();
        if (cedulaAdmin !== '' && (cedulaAdmin.length !== 8 || !/^\d+$/.test(cedulaAdmin))) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'La cédula debe tener 8 dígitos numéricos',
                confirmButtonColor: '#3085d6'
            });
            return;
        }
    }
    
    // Confirmación final
    Swal.fire({
        title: '¿Confirmar creación?',
        text: '¿Está seguro de crear este usuario?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, crear usuario',
        cancelButtonText: 'Cancelar',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            return new Promise((resolve) => {
                setTimeout(() => {
                    resolve();
                }, 500);
            });
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Mostrar loader en el botón
            const submitBtn = document.getElementById('submit-btn');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
            submitBtn.disabled = true;
            
            // Enviar formulario
            setTimeout(() => {
                this.submit();
            }, 1000);
        }
    });
});

// Inicialización al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    const rolSelect = document.getElementById('rol_id');
    
    // Si ya hay un rol seleccionado (en caso de error al enviar)
    if (rolSelect.value) {
        rolSelect.dispatchEvent(new Event('change'));
        
        // Si es un rol con datos personales y hay cédula, validarla
        if (ROLES_CON_PERSONALES.includes(rolSelect.value)) {
            const cedulaInput = document.getElementById('cedula');
            if (cedulaInput.value.trim() !== '') {
                validarCedulaDuplicada(cedulaInput.value.trim(), 'cedula');
            }
        }
    }
    
    // Validar email si ya tiene valor
    const emailInput = document.getElementById('email');
    if (emailInput.value.trim() !== '') {
        validarEmailDuplicado(emailInput.value.trim());
    }
    
    // Validar contraseñas si ya tienen valor
    const passwordRepetInput = document.getElementById('password_repet');
    if (passwordRepetInput.value.length > 0) {
        passwordRepetInput.dispatchEvent(new Event('input'));
    }
});
</script>

<?php
include ('../../admin/layout/parte2.php');
include ('../../layout/mensajes.php');
?>