<?php
include ('../../app/config.php');
include ('../../admin/layout/parte1.php');
include ('../../app/controllers/roles/listado_de_roles.php');
?>
<!-- Bootstrap CSS -->  
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">  
<!-- Bootstrap Datepicker CSS -->  
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css"/>  
<!-- jQuery -->  
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>  
<!-- Bootstrap JS -->  
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>  
<!-- Bootstrap Datepicker JS -->  
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>  
<!-- Lenguaje español para Bootstrap Datepicker -->  
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/locales/bootstrap-datepicker.es.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Content Wrapper. Contains page content -->

<div class="content-wrapper">  
    <br>  
    <div class="content">  
        <div class="container">  
            <div class="content-header">  
                <div class="container-fluid">  
                    <div class="row mb-2">  
                        <div class="col-sm-6">  
                            <h3 class="m-1">Registrar nuevo representante</h3>  
                        </div><!-- /.col -->  
                        <div class="col-sm-6">  
                            <ol class="breadcrumb float-sm-right">  
                                <li class="breadcrumb-item"><a href="<?=APP_URL;?>/admin/representantes">Representantes</a></li>  
                                <li class="breadcrumb-item active">Registrar nuevo representante</li>  
                            </ol>  
                        </div><!-- /.col -->  
                    </div><!-- /.row -->  
                </div><!-- /.container-fluid -->  
            </div>  

            <div class="col-md-12">  
                <div class="card card-outline card-primary">  
                    <div class="card-body">  
                        <form action="<?= APP_URL; ?>/app/controllers/representantes/create.php" method="post" onsubmit="return validarFormulario()">  
                            <div class="container">  
                                <div class="row">  
                                    <div class="col-md-3">  
                                        <div class="form-group">  
                                            <label for="tipo_cedula">Tipo de Cédula</label>  
                                            <select id="tipo_cedula" name="tipo_cedula" class="form-control" required>  
                                                <option value="" disabled selected>Seleccione tipo de cédula</option>  
                                                <option value="V">Venezolana</option>  
                                                <option value="E">Extranjera</option>  
                                            </select>  
                                        </div>  
                                    </div>  

                                    <div class="col-md-3">  
                                        <div class="form-group">  
                                            <label for="cedula" class="obligatorio">Cédula de identidad</label>  
                                            <input type="number" id="cedula" name="cedula" class="form-control" required maxlength="8" max="99999999" minlength="7" min="1234567" placeholder="Número (máx. 8 dígitos)" required onblur="verificarCedula()">  
                                            <small id="mensajeCedula" class="text-danger"></small> <!-- Área para mensajes de retroalimentación -->  
                                        </div>  
                                    </div>  
                                    <div class="col-md-3">  
                                        <div class="form-group">  
                                            <label for="nombres" class="obligatorio">Nombres</label>  
                                            <input type="text" id="nombres" name="nombres" class="form-control" required pattern="[A-Za-záéíóúÁÉÍÓÚñÑ ]+" title="Solo se permiten letras y espacios" autocomplete="off">  
                                        </div>  
                                    </div>  
                                    <div class="col-md-3">  
                                        <div class="form-group">  
                                            <label for="apellidos" class="obligatorio">Apellidos</label>  
                                            <input type="text" id="apellidos" name="apellidos" class="form-control" required pattern="[A-Za-záéíóúÁÉÍÓÚñÑ ]+" title="Solo se permiten letras y espacios" autocomplete="off">  
                                        </div>  
                                    </div>  
                                </div>  
                                <div class="row">  
    <div class="col-md-3">  
        <div class="form-group">  
            <label for="fecha_nacimiento">Fecha de Nacimiento</label>  
            <input type="text" id="fecha_nacimiento" name="fecha_nacimiento" placeholder="Ingrese Fecha " required autocomplete="off">  
            <input type="hidden" id="fecha_nacimiento_bd" name="fecha_nacimiento_bd">  
            <div id="mensajeFecha" style="color:red;"></div>  <!-- Aquí se mostrará el mensaje -->  
                                        </div>  
                                    </div>  
                                    <div class="col-md-3">  
                                        <div class="form-group">  
                                            <label for="estado_civil">Estado Civil</label>  
                                            <select id="estado_civil" name="estado_civil" class="form-control" required>  
                                                <option value="" disabled selected>Seleccione estado civil</option>  
                                                <option value="soltero">Soltero/a</option>  
                                                <option value="casado">Casado/a</option>              
                                                <option value="viudo">Viudo/a</option>  
                                            </select>  
                                        </div>  
                                    </div>  
                                    <div class="col-md-3">  
                                        <div class="form-group">  
                                            <label for="genero">Sexo</label>  
                                            <select id="genero" name="genero" class="form-control" required>  
                                                <option value="" disabled selected>Seleccione sexo</option>  
                                                <option value="masculino">Masculino</option>  
                                                <option value="femenino">Femenino</option>  
                                            </select>  
                                        </div>  
                                    </div>  
                                    <div class="col-md-3">  
                                        <div class="form-group">  
                                            <label for="correo_electronico">Correo Electrónico</label>  
                                            <input type="email" id="correo_electronico" name="correo_electronico" class="form-control" required autocomplete="off">  
                                            <div id="mensajeCorreo" class="text-danger"></div> <!-- Área para mensajes de retroalimentación -->  
                                        </div>  
                                    </div>  
                                </div>  

                                <div class="row">   
                                    <div class="col-md-3">  
                                        <div class="form-group">  
                                            <label for="tipo_sangre">Tipo de sangre</label>  
                                            <select id="tipo_sangre" name="tipo_sangre" class="form-control" required>  
                                                <option value="" disabled selected>Seleccione tipo de sangre</option>  
                                                <option value="A+">A+</option>  
                                                <option value="A-">A-</option>  
                                                <option value="AB+">AB+</option>  
                                                <option value="AB-">AB-</option>  
                                                <option value="B+">B+</option>  
                                                <option value="B-">B-</option>  
                                                <option value="O+">O+</option>  
                                                <option value="O-">O-</option>  
                                            </select>  
                                        </div>  
                                    </div>  
                                    <div class="col-md-3">  
                                        <div class="form-group">  
                                            <label for="direccion" class="obligatorio">Dirección</label>  
                                            <input type="text" id="direccion" name="direccion" class="form-control" required autocomplete="off">  
                                        </div>  
                                    </div>  
                                    <div class="col-md-3">  
                                        <div class="form-group">  
                                            <label for="numeros_telefonicos">Teléfono</label>  
                                            <div class="input-group">  
                                                <input type="tel" id="numeros_telefonicos" name="numeros_telefonicos" class="form-control" required pattern="[0-9]{11}" title="El teléfono debe tener exactamente 11 dígitos numéricos" autocomplete="off">  
                                            </div>  
                                            <div id="mensajeTelefono" class="text-danger"></div> <!-- Área para mensajes de retroalimentación -->  
                                        </div>  
                                    </div>  
                                    <div class="col-md-3">  
                                        <div class="form-group">  
                                        <label for="estatus">Estatus</label>  
                                            <select id="estatus" name="estatus" class="form-control" required>  
                                                <option value="" disabled selected>Seleccione estatus</option>  
                                                <option value="activo">Activo</option>  
                                                <option value="inactivo">Inactivo</option>  
                                            </select>  
                                        </div>  
                                    </div>  
                                </div>  

                               

<script>  
    function validarFormulario() {  
        // Aquí puedes agregar cualquier validación adicional que desees  
        var cedula = document.getElementById("cedula").value;  
        var correo = document.getElementById("correo_electronico").value;  

        // Validar cédula  
        if (!/^[0-9]{7,8}$/.test(cedula)) {  
            document.getElementById("mensajeCedula").textContent = "La cédula debe contener entre 7 y 8 números.";  
            return false;  
        } else {  
            document.getElementById("mensajeCedula").textContent = "";  
        }  

        // Validar correo electrónico  
        if (!/\S+@\S+\.\S+/.test(correo)) {  
            document.getElementById("mensajeCorreo").textContent = "Por favor ingrese un correo electrónico válido.";  
            return false;  
        } else {  
            document.getElementById("mensajeCorreo").textContent = "";  
        }  

        // Validaciones adicionales que desees agregar  

        return true; // Si todas las validaciones pasan, se envía el formulario  
    }  

    // Otras funciones pueden ser agregadas aquí  
</script>

<script>  
    $(document).ready(function() {  
        // Obtener la fecha actual  
        const hoy = new Date();  
        const añoActual = hoy.getFullYear();  
        const fechaMinima = new Date(añoActual - 18, hoy.getMonth(), hoy.getDate()); // Fecha mínima (hace 18 años)  

        // Configura el datepicker en español  
        $("#fecha_nacimiento").datepicker({  
            format: "dd/mm/yyyy",  
            language: "es",  
            autoclose: true,  
            endDate: fechaMinima, // No permitir fechas futuras  
            startDate: new Date(añoActual - 100, 0, 1), // Permitir fechas desde hace 100 años  
            beforeShow: function(input, inst) {  
                var calendar = inst.dpDiv;  
                setTimeout(function() {  
                    calendar.position({  
                        my: 'center top',  
                        at: 'center bottom',  
                        of: input  
                    });  
                }, 1);  
            }  
        });  

        // Llama a verificarFecha cuando se selecciona una fecha  
        $("#fecha_nacimiento").on('changeDate', function() {  
            verificarFecha();  
        });  
    });  

    function verificarFecha() {  
        const fechaInput = document.getElementById('fecha_nacimiento').value;  
        const mensaje = document.getElementById('mensajeFecha');  
        const regex = /^(0[1-9]|[12][0-9]|3[01])\/(0[1-9]|1[0-2])\/(\d{4})$/;  

        // Validar formato de fecha  
        if (!regex.test(fechaInput)) {  
            mensaje.textContent = 'Formato incorrecto. Use DD/MM/AAAA.';  
            document.getElementById('fecha_nacimiento').value = ''; // Limpiar el campo  
            return false;  
        }  

        const [dia, mes, año] = fechaInput.split('/').map(Number);  
        const fecha = new Date(año, mes - 1, dia);  

        // Validar si la fecha es válida  
        if (fecha.getDate() !== dia || fecha.getMonth() + 1 !== mes || fecha.getFullYear() !== año) {  
            mensaje.textContent = 'Fecha no válida.';  
            document.getElementById('fecha_nacimiento').value = ''; // Limpiar el campo  
            return false;  
        }  

        // Validar que la fecha sea al menos 18 años atrás  
        const fechaLimite = new Date();  
        fechaLimite.setFullYear(fechaLimite.getFullYear() - 18);  
        if (fecha > fechaLimite) {  
            mensaje.textContent = 'Debes tener al menos 18 años.';  
            document.getElementById('fecha_nacimiento').value = ''; // Limpiar el campo  
            return false;  
        }  

        // Guardar la fecha en formato YYYY-MM-DD en un campo oculto si es necesario  
        const fechaFormateada = `${año}-${mes < 10 ? '0' : ''}${mes}-${dia < 10 ? '0' : ''}${dia}`;  
        document.getElementById('fecha_nacimiento_bd').value = fechaFormateada; // Asegúrate de tener un campo oculto para este propósito  

        mensaje.textContent = ''; // Limpiar mensaje en caso de éxito  
        return true;  
    }   
</script>  
                <!-- Botones de acción -->  
                <div class="row">  
                    <div class="col-md-12">  
                        <div class="form-group">  
                            <center>  
                                <button type="submit" class="btn btn-primary">Registrar</button>  
                                <a href="<?= APP_URL; ?>/admin/representantes" class="btn btn-danger">Cancelar</a>  
                            </center>  
                            </div>  
                    </div>  
                </div>  
            </div>  
        </div>  
    </div>  
</div>  

                    

<script>  
async function verificarCedula() {  
    const mensajeCedula = document.getElementById('mensajeCedula');  
    const cedula = document.getElementById('cedula').value.trim();  

    // Verificar si el campo de cédula está vacío  
    if (cedula === '') {  
        mensajeCedula.textContent = 'La cédula no puede estar vacía.';  
        return;  
    }  

    if (cedula.length < 7 || cedula.length > 8) {  
        mensajeCedula.textContent = 'La cédula debe tener entre 7 y 8 dígitos.';  
        return;  
    }  

    try {  
        const response = await fetch(`../../app/controllers/representantes/check_cedula.php`, {  
            method: 'POST',  
            headers: {  
                'Content-Type': 'application/x-www-form-urlencoded',  
            },  
            body: `cedula=${encodeURIComponent(cedula)}`  
        });  

        const data = await response.text();  

        if (data.trim() === 'existe') {  
            await Swal.fire({  
                title: 'La cédula ya está registrada',  
                text: 'No se puede enviar el formulario.',  
                icon: 'error',  
                confirmButtonText: 'Aceptar'  
            });  
            document.getElementById('cedula').value = '';  
        } else {  
            mensajeCedula.textContent = '';  
        }  
    } catch (error) {  
        console.error('Error:', error);  
        await Swal.fire({  
            title: 'Error',  
            text: 'Ocurrió un error al verificar la cédula.',  
            icon: 'error',  
            confirmButtonText: 'Aceptar'  
        });  
    }  
}  

document.addEventListener('DOMContentLoaded', function() {  
    document.getElementById('cedula').addEventListener('blur', verificarCedula);  
});
</script>  

<?php  
include('../../admin/layout/parte2.php');  
include('../../layout/mensajes.php');  
?>