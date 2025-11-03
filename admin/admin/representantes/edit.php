
<?php  
include ('../../app/config.php');  
$id_representante = $_GET['id'];  

include ('../../admin/layout/parte1.php');  
include ('../../app/controllers/representantes/datos_del_representante.php');  
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

<div class="content-wrapper">  
    <div class="content">  
        <div class="container-fluid">  
            <div class="row">  
                <div class="col-md-12">  
                    <div class="card">  
                        <div class="card-header">  
                            <h3 class="card-title">Actualizar Representante</h3>  
                        </div>  
                        <div class="card-body">  
                            <form action="<?= APP_URL; ?>/app/controllers/representantes/update.php" method="post" onsubmit="return confirmUpdate();">  
                                <input type="hidden" name="id_representante" value="<?= htmlspecialchars($id_representante); ?>"> <!-- Campo oculto para el ID -->  

                                <div class="row">  
                                    <div class="col-md-3">  
                                        <div class="form-group">  
                                            <label for="tipo_cedula" class="obligatorio">Tipo de Cédula</label>  
                                            <select id="tipo_cedula" name="tipo_cedula" class="form-control" required>  
                                                <option value="" disabled selected>Seleccione tipo</option>  
                                                <option value="V" <?= ($tipo_cedula == 'V') ? 'selected' : ''; ?>>V</option>  
                                                <option value="E" <?= ($tipo_cedula == 'E') ? 'selected' : ''; ?>>E</option>  
                                            </select>  
                                        </div>  
                                    </div>

                                    <div class="col-md-3">  
                                        <div class="form-group">  
                                            <label for="cedula" class="obligatorio">Cédula de identidad</label>  
                                            <input value="<?= htmlspecialchars($cedula); ?>" type="number" id="cedula" name="cedula" class="form-control" required maxlength="8" max="88888888" minlength="7" min="1234567" placeholder="Número (máx. 8 dígitos)" required onblur="verificarCedula()">  
                                            <small id="mensajeCedula" class="text-danger"></small> <!-- Área para mensajes de retroalimentación -->    
                                        </div>  
                                    </div>  

                                    <div class="col-md-3">  
                                        <div class="form-group">  
                                            <label for="nombres" class="obligatorio">Nombres</label>  
                                            <input value="<?= htmlspecialchars($nombres); ?>" type="text" id="nombres" name="nombres" class="form-control" required pattern="[A-Za-záéíóúÁÉÍÓÚñÑ ]+" title="Solo se permiten letras y espacios">  
                                        </div>  
                                    </div>  

                                    <div class="col-md-3">  
                                        <div class="form-group">  
                                            <label for="apellidos" class="obligatorio">Apellidos</label>  
                                            <input value="<?= htmlspecialchars($apellidos); ?>" type="text" id="apellidos" name="apellidos" class="form-control" required pattern="[A-Za-záéíóúÁÉÍÓÚñÑ ]+" title="Solo se permiten letras y espacios">  
                                        </div>  
                                    </div>  
                                </div>
                                <div class="row">  
                                    <?php  
                                    $fecha_nacimiento_formateada = !empty($fecha_nacimiento) ? date('d/m/Y', strtotime($fecha_nacimiento)) : '';  
                                    ?>  
                                    <div class="col-md-3">  
                                        <div class="form-group">  
                                            <label for="fecha_nacimiento">Fecha de Nacimiento</label>  
                                            <input   
                                                value="<?= htmlspecialchars($fecha_nacimiento_formateada); ?>"   
                                                type="text"   
                                                id="fecha_nacimiento"   
                                                name="fecha_nacimiento"   
                                                placeholder="DD/MM/AAAA"   
                                                required   
                                                pattern="\d{2}/\d{2}/\d{4}"   
                                                title="Formato: DD/MM/AAAA (días y meses de dos dígitos, año de cuatro dígitos)"   
                                                class="form-control"  
                                            >  
                                            <div id="mensajeFecha" style="color:red;"></div>  
                                        </div>  
                                    </div>

                                    <div class="col-md-3">  
                                        <div class="form-group">  
                                            <label for="estado_civil" class="obligatorio">Estado Civil</label>  
                                            <select id="estado_civil" name="estado_civil" class="form-control" required>  
                                                <option value="" disabled selected>Seleccione</option>  
                                                <option value="Soltero" <?= ($estado_civil == 'Soltero') ? 'selected' : ''; ?>>Soltero</option>  
                                                <option value="Casado" <?= ($estado_civil == 'Casado') ? 'selected' : ''; ?>>Casado</option>  
                                                <option value="Divorciado" <?= ($estado_civil == 'Divorciado') ? 'selected' : ''; ?>>Divorciado</option>  
                                                <option value="Viudo" <?= ($estado_civil == 'Viudo') ? 'selected' : ''; ?>>Viudo</option>  
                                            </select>  
                                        </div>  
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="genero">Sexo</label>
                                            <select id="genero" name="genero" class="form-control" required>
                                                <option value="" disabled selected>Seleccione sexo</option>
                                                <option value="masculino" <?= ($genero == 'masculino') ? 'selected' : ''; ?>>Masculino</option>  
                                                <option value="femenino" <?= ($genero == 'femenino') ? 'selected' : ''; ?>>Femenino</option>  
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="correo_electronico">Correo Electrónico</label>
                                            <input value="<?= htmlspecialchars($correo_electronico); ?>" type="email" id="correo_electronico" name="correo_electronico" class="form-control" required>
                                        </div>  
                                    </div>

                                    <div class="col-md-3">  
                                        <div class="form-group">  
                                            <label for="tipo_sangre" class="obligatorio">Tipo de Sangre</label>  
                                            <select id="tipo_sangre" name="tipo_sangre" class="form-control" required>  
                                                <option value="" disabled selected>Seleccione</option>  
                                                <option value="A+" <?= ($tipo_sangre == 'A+') ? 'selected' : ''; ?>>A+</option>  
                                                <option value="A-" <?= ($tipo_sangre == 'A-') ? 'selected' : ''; ?>>A-</option>  
                                                <option value="B+" <?= ($tipo_sangre == 'B+') ? 'selected' : ''; ?>>B+</option>  
                                                <option value="B-" <?= ($tipo_sangre == 'B-') ? 'selected' : ''; ?>>B-</option>  
                                                <option value="O+" <?= ($tipo_sangre == 'O+') ? 'selected' : ''; ?>>O+</option>  
                                                <option value="O-" <?= ($tipo_sangre == 'O-') ? 'selected' : ''; ?>>O-</option>  
                                                <option value="AB+" <?= ($tipo_sangre == 'AB+') ? 'selected' : ''; ?>>AB+</option>  
                                                <option value="AB-" <?= ($tipo_sangre == 'AB-') ? 'selected' : ''; ?>>AB-</option>  
                                                </select>  
                                        </div>  
                                    </div>  

                                    <div class="col-md-3">  
                                        <div class="form-group">  
                                            <label for="direccion" class="obligatorio">Dirección</label>  
                                            <input value="<?= htmlspecialchars($direccion); ?>" type="text" id="direccion" name="direccion" class="form-control" required>  
                                        </div>  
                                    </div>  

                                    <div class="col-md-3">  
                                        <div class="form-group">  
                                            <label for="numeros_telefonicos" class="obligatorio">Teléfonos</label>  
                                            <input value="<?= htmlspecialchars($numeros_telefonicos); ?>" type="tel" id="numeros_telefonicos" name="numeros_telefonicos" class="form-control" required pattern="[0-9]{11}" title="El teléfono debe tener exactamente 11 dígitos numéricos">  
                                        </div>  
                                    </div>  

                                    <div class="col-md-3">  
                                        <div class="form-group">  
                                            <label for="estatus">Estatus</label>  
                                            <select id="estatus" name="estatus" class="form-control" required>  
                                                <option value="" disabled selected>Seleccione</option>  
                                                <option value="Activo" <?= ($estatus == 'Activo') ? 'selected' : ''; ?>>Activo</option>  
                                                <option value="Inactivo" <?= ($estatus == 'Inactivo') ? 'selected' : ''; ?>>Inactivo</option>  
                                            </select>  
                                        </div>  
                                    </div>  
                                </div>  

                                <!-- Botones de acción -->  
                                <div class="row">  
                                    <div class="col-md-12 text-center">  
                                        <button type="submit" class="btn btn-success">Actualizar</button>  
                                        <a href="<?= APP_URL; ?>/admin/representantes" class="btn btn-secondary">Cancelar</a>  
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
            // Se añade la opción para mostrar el calendario hacia abajo  
            orientation: "bottom", // Asegúrate de que el elemento se muestre hacia abajo  
            beforeShow: function(input, inst) {  
                setTimeout(function() {  
                    $(inst.dpDiv).css({  
                        top: $(input).offset().top + $(input).outerHeight(), // Forzar la posición hacia abajo  
                        left: $(input).offset().left  
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





function verificarCedula() {
    const cedula = document.getElementById('cedula').value;
    const mensajeCedula = document.getElementById('mensajeCedula');

    // Verificar si el campo de cédula está vacío
    if (cedula.trim() === '') {
        mensajeCedula.textContent = 'La cédula no puede estar vacía.';
        return;
    }

    if (cedula.length < 7 || cedula.length > 8) {
        mensajeCedula.textContent = 'La cédula debe tener entre 7 y 8 dígitos.';
        return;
    }

    fetch('<?= APP_URL; ?>/ruta/a/check_cedula.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'cedula=' + encodeURIComponent(cedula) + '&id_representante=' + <?= json_encode($id_representante); ?>,
    })
    .then(response => response.json())
    .then(data => {
        if (data.exists) {
            mensajeCedula.textContent = 'Esta cédula ya está registrada.';
        } else {
            mensajeCedula.textContent = ''; // Sin mensaje de error
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mensajeCedula.textContent = '';
    });
}

// Función para confirmar la acción de actualización
function confirmUpdate() {
    const message = '¿Estás seguro que deseas actualizar este registro? Esta acción no se puede deshacer.';
    return confirm(message);
}
</script>

<?php  
include ('../../admin/layout/parte2.php');  
include ('../../layout/mensajes.php');  
?>