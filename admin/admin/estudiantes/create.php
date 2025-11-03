<?php  
include('../../app/config.php');  
include('../../admin/layout/parte1.php');  
include('../../app/controllers/roles/listado_de_roles.php');  

// Obtener el id del representante desde la URL  
$id_representante = isset($_GET['id_representante']) ? $_GET['id_representante'] : null;  

// Obtener la información del representante  
$sql_representante = "SELECT cedula, nombres, apellidos FROM representantes WHERE id_representante = :id_representante";  
$stmt_representante = $pdo->prepare($sql_representante);  
$stmt_representante->bindParam(':id_representante', $id_representante);  
$stmt_representante->execute();  
$representante = $stmt_representante->fetch(PDO::FETCH_ASSOC);   
?>  

<!-- Content Wrapper. Contains page content -->  
<div class="content-wrapper">  
    <br>  
    <div class="content">  
        <div class="container">  
            <div class="content">  
                <div class="container">  
                    <div class="content-header">  
                        <div class="container-fluid">  
                            <div class="row mb-2">  
                                <div class="col-sm-6">  
                                    <h3 class="m-1">Registrar nuevo estudiante</h3>  
                                </div><!-- /.col -->  
                                <div class="col-sm-6">  
                                    <ol class="breadcrumb float-sm-right">  
                            <li class="breadcrumb-item"><a href="<?= APP_URL; ?>/admin/">Inicio</a></li> 
                                <li class="breadcrumb-item"><a href="<?= APP_URL; ?>/admin/estudiantes">Estudiantes</a></li>
                                <li class="breadcrumb-item"><a href="<?= APP_URL; ?>/admin/estudiantes/Lista_de_estudiante.php">Lista de estudiante</a></li>   
                                <li class="breadcrumb-item active">Editar estudiante</li>  
                                    </ol>  
                                </div><!-- /.col -->  
                            </div><!-- /.row -->  
                        </div><!-- /.container-fluid -->  
                    </div>  
                    <div class="col-md-12">  
                        <div class="card card-outline card-primary">  
                            <div class="card-header">  
                                <h3 class="card-title"><b>Datos del estudiante</b></h3>  
                            </div>  
                            <div class="card-body">  
                                <form action="<?=APP_URL;?>/app/controllers/estudiantes/create.php" method="post" onsubmit="return validarFormulario()">  
                                    <input type="hidden" name="id_representante" value="<?= $id_representante ?>">  
                                    <div class="row">  
                                        <div class="col-md-3">  
                                            <div class="form-group">  
                                                <label for="posee_cedula">¿Posee cédula de identidad?</label>  
                                                <div class="form-check">  
                                                    <input class="form-check-input" type="radio" name="posee_cedula" id="posee_cedula_si" value="si" onchange="toggleCedulaField(true); togglePosicionField(false)">  
                                                    <label class="form-check-label" for="posee_cedula_si">  
                                                        Sí  
                                                    </label>  
                                                </div>  
                                                <div class="form-check">  
                                                    <input class="form-check-input" type="radio" name="posee_cedula" id="posee_cedula_no" value="no" onchange="toggleCedulaField(false); togglePosicionField(true)">  
                                                    <label class="form-check-label" for="posee_cedula_no">  
                                                        No  
                                                    </label>  
                                                </div>  
                                            </div>  
                                        </div>  
                                        <div class="col-md-3" id="cedula_field" style="display: none;">  
    <div class="form-group">  
        <label for="cedula" class="obligatorio">Cédula de identidad</label>  
        <input   
            type="number"   
            id="cedula"   
            name="cedula"   
            class="form-control"   
            maxlength="8"   
            min="1000000"   
            max="99999999"   
            placeholder="Número (máx. 8 dígitos)"   
        >  
        <small id="mensajeCedula" class="text-danger"></small>  <!-- Mensaje de error para la cédula -->  
    </div>  
</div>
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
                                                <label for="nombres" class="obligatorio">Nombres</label>  
                                                <input type="text" id="nombres" name="nombres" class="form-control" required pattern="[A-Za-záéíóúÁÉÍÓÚ ]+" title="Solo se permiten letras y espacios">  
                                            </div>  
                                        </div>  
                                    </div>  
                                    <div class="row">  
                                        <div class="col-md-3">  
                                            <div class="form-group">  
                                                <label for="apellidos" class="obligatorio">Apellidos</label>  
                                                <input type="text" id="apellidos" name="apellidos" class="form-control" required pattern="[A-Za-záéíóúÁÉÍÓÚ ]+" title="Solo se permiten letras y espacios">  
                                            </div>  
                                        </div>  
                                        <div class="col-md-3">  
                                            <div class="form-group">  
                                                <label for="fecha_nacimiento">Fecha de Nacimiento</label>  
                                                <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" class="form-control" required max="<?= date('Y-m-d', strtotime('-3 years')) ?>">  
                                            </div>  
                                        </div>  
                                        
                                        <div class="col-md-3" id="posicion_hijo_field" style="display: none;">  
    <div class="form-group">  
        <label for="posicion_hijo">Posición del hijo</label>  
        <input type="text" id="posicion_hijo" name="posicion_hijo" class="form-control" placeholder="Ejemplo: 1, 2, 3..." onchange="verificarCedulaEscolar()" />  
    
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
                                    </div>  
                                    <div class="row">  
                                        <div class="col-md-6">  
                                            <div class="form-group">  
                                                <label for="correo_electronico">Correo Electrónico</label>  
                                                <input type="email" id="correo_electronico" name="correo_electronico" class="form-control" required>  
                                            </div>  
                                        </div>  
                                        <div class="col-md-6">  
                                            <div class="form-group">  
                                                <label for="direccion" class="obligatorio">Dirección</label>  
                                                <input type="text" id="direccion" name="direccion" class="form-control" required>  
                                            </div>  
                                        </div>  
                                    </div>  
                                    <div class="row">  
                                        <div class="col-md-6">  
                                            <div class="form-group">  
                                                <label for="numeros_telefonicos" class="obligatorio">Teléfono</label>  
                                                <input type="tel" id="numeros_telefonicos" name="numeros_telefonicos" class="form-control" required pattern="[0-9]{11}" title="El teléfono debe tener exactamente 11 dígitos numéricos">  
                                            </div>  
                                        </div>
     <div class="col-md-3">  
    <div class="form-group">  
        <label for="tipo_discapacidad">¿Posee alguna de Discapacidad?</label>  
        <select id="tipo_discapacidad" name="tipo_discapacidad" class="form-control" required>  
            <option value="" disabled selected>Seleccione tipo de discapacidad</option>  
            <option value="visual">Visual</option>  
            <option value="auditiva">Auditiva</option>  
            <option value="motora">Motora</option>  
            <option value="intelectual">Intelectual</option>  
            <option value="psicosocial">Psicosocial</option>  
            <option value="otra">Otra</option>  
            <option value="ninguna">Ninguna </option> 
        </select>  
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
                                        <div class="col-md-9">  
                                            <div class="form-group">  
                                                <label for="cedula_escolar">Cédula Escolar</label>  
                                                <input type="text" id="cedula_escolar" name="cedula_escolar" class="form-control" readonly>  
                                            </div>  
                                        </div>  
                                    </div>  
                                    <div class="row">  
                                        <div class="col-md-12">  
                                            <div class="form-group">  
                                                <center>  
                                                    <button type="submit" class="btn btn-primary">Registrar</button>  
                                                    <a href="<?=APP_URL;?>/admin/estudiantes?id_representante=<?= $id_representante ?>" class="btn btn-danger">Cancelar</a>  
                                                </center>  
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

    async function verificarCedulaEscolar() {
    const posicionHijo = document.getElementById('posicion_hijo').value.trim();
    const tipoCedula = document.getElementById('tipo_cedula').value;
    const cedulaRepresentante = '<?= $representante['cedula'] ?>'; // Cédula del representante

    if (posicionHijo === '') {
        return; // No hacer nada si la posición está vacía
    }

    // Generar la cédula escolar basada en la posición del hijo
    let cedulaEscolar = (tipoCedula === 'V' ? 'V' : 'E') + posicionHijo + 'XX' + cedulaRepresentante; // 'XX' se reemplazará con el año de nacimiento

    try {
        const response = await fetch(`../../app/controllers/estudiantes/verificar_cedula_escolar.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `cedula_escolar=${encodeURIComponent(cedulaEscolar)}`
        });

        const data = await response.text();

        if (data.trim() === 'existe') {
            await Swal.fire({
                title: 'Cédula Escolar ya registrada',
                text: 'La cédula escolar para esta posición de hijo ya está registrada.',
                icon: 'error',
                confirmButtonText: 'Aceptar'
            });
            document.getElementById('posicion_hijo').value = ''; // Limpiar el campo si ya está registrada
            return false; // Evitar el envío del formulario
        }
    } catch (error) {
        console.error('Error:', error);
        await Swal.fire({
            title: 'Error',
            text: 'Ocurrió un error al verificar la cédula escolar.',
            icon: 'error',
            confirmButtonText: 'Aceptar'
        });
    }
}
    function toggleCedulaField(show) {  
        const cedulaField = document.getElementById('cedula_field');  
        cedulaField.style.display = show ? 'block' : 'none';  
    }  

    function togglePosicionField(show) {  
        const posicionField = document.getElementById('posicion_hijo_field');  
        posicionField.style.display = show ? 'block' : 'none';  
    }  

    function generarCedulaEscolar() {  
        const tipo_cedula = document.getElementById('tipo_cedula').value;  
        const posicion_hijo = document.getElementById('posicion_hijo').value;  
        const fecha_nacimiento = document.getElementById('fecha_nacimiento').value;   
        const cedula_representante = '<?= $representante['cedula'] ?>';  

        if (tipo_cedula && posicion_hijo && fecha_nacimiento) {  
            let cedula_escolar = '';  
            if (tipo_cedula === 'V') {  
                cedula_escolar = 'V' + (posicion_hijo || '0') + fecha_nacimiento.slice(2, 4) + cedula_representante;  
            } else {  
                cedula_escolar = 'E' + (posicion_hijo || '0') + fecha_nacimiento.slice(2, 4) + cedula_representante;  
            }  
            document.getElementById('cedula_escolar').value = cedula_escolar;  
        } else {  
            document.getElementById('cedula_escolar').value = ''; // Limpiar si no hay datos suficientes  
        }  
    }  

    function validarFormulario() {  
        const posee_cedula = document.querySelector('input[name="posee_cedula"]:checked');  

        if (posee_cedula === null) {  
            alert("Por favor, seleccione si posee cédula de identidad.");  
            return false;  
        }  

        if (posee_cedula.value === 'si') {  
            const cedula = document.getElementById('cedula');  
            if (cedula.value.trim() === "") {  
                alert("Por favor, ingrese la cédula de identidad.");  
                cedula.focus();  
                return false;  
            } else if (cedula.value.length !== 8) {  
                alert("La cédula de identidad debe tener 8 dígitos.");  
                cedula.focus();  
                return false;  
            }  
        }  

        return true;  
    }  

    // Agregar eventos para generar la cédula escolar al cambiar la posición del hijo o la fecha de nacimiento  
    document.getElementById('posicion_hijo').addEventListener('change', function() {
        generarCedulaEscolar();
        verificarCedulaEscolar(); // Verificar cédula escolar al cambiar la posición
    });  
    document.getElementById('fecha_nacimiento').addEventListener('change', generarCedulaEscolar);  

    // Agregar evento para verificar cédula cuando el campo pierde el foco  
    document.addEventListener('DOMContentLoaded', function() {  
        document.getElementById('cedula').addEventListener('blur', verificarCedula);  
    });  
</script>

<?php  
include('../../admin/layout/parte2.php');  
include('../../layout/mensajes.php');  
?>