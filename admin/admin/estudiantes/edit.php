<?php  
include ('../../app/config.php');  
$id_estudiante = $_GET['id'];  
include ('../../admin/layout/parte1.php');  
include ('../../app/controllers/estudiantes/datos_del_estudiante.php');  

// Obtener la información del estudiante  
$sql_estudiante = "SELECT e.cedula, e.nombres, e.apellidos, e.fecha_nacimiento, e.genero, e.correo_electronico,   
                           e.direccion, e.numeros_telefonicos, e.tipo_cedula, e.posicion_hijo, e.cedula_escolar, e.estatus,   
                           e.tipo_discapacidad, r.cedula AS cedula_representante   
FROM estudiantes e   
JOIN representantes r ON e.id_representante = r.id_representante WHERE e.id_estudiante = :id_estudiante";  
$stmt_estudiante = $pdo->prepare($sql_estudiante);  
$stmt_estudiante->bindParam(':id_estudiante', $id_estudiante, PDO::PARAM_INT);  
$stmt_estudiante->execute();  
$estudiante = $stmt_estudiante->fetch(PDO::FETCH_ASSOC);  

// Comprobar que el estudiante existe  
if (!$estudiante) {  
    echo "Estudiante no encontrado.";  
    exit;  
}  
?>  

<div class="content-wrapper">  
    <br>  
    <div class="content">  
        <div class="container">  
            <div class="content-header">  
                <div class="container-fluid">  
                    <div class="row mb-2">  
                        <div class="col-sm-6">  
                            <h3 class="m-1">Editar estudiante</h3>  
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
                        <form action="<?= APP_URL; ?>/app/controllers/estudiantes/update.php" method="post" onsubmit="return validarFormulario(event)">  
                            <input type="hidden" name="id_estudiante" value="<?= $id_estudiante ?>">  
                            <input type="hidden" name="id_representante" value="<?= $estudiante['cedula_representante'] ?>">  

                            <div class="row">  
                                <div class="col-md-3">  
                                    <div class="form-group">  
                                        <label for="tipo_cedula">Tipo de Cédula</label>  
                                        <select id="tipo_cedula" name="tipo_cedula" class="form-control" required>  
                                            <option value="" disabled>Seleccione tipo de cédula</option>  
                                            <option value="V" <?= ($estudiante['tipo_cedula'] === 'V') ? 'selected' : ''; ?>>Venezolana</option>  
                                            <option value="E" <?= ($estudiante['tipo_cedula'] === 'E') ? 'selected' : ''; ?>>Extranjera</option>  
                                        </select>  
                                    </div>  
                                </div>  

                                <div class="col-md-3" id="cedula_field" style="<?= $estudiante['cedula_escolar'] ? 'display: none;' : 'display: block;'; ?>">  
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
                                            value="<?= $estudiante['cedula'] ?>"   
                                            onchange="validarCedulaIdentidad()"  
                                        >  
                                        <small id="mensajeCedula" class="text-danger"></small>  
                                    </div>  
                                </div>  
                                <div class="col-md-3" id="posicion_hijo_field" style="<?= $estudiante['cedula_escolar'] ? 'display: block;' : 'display: none;'; ?>">  
    <div class="form-group">  
        <label for="posicion_hijo">Posición del hijo</label>  
        <input type="text" id="posicion_hijo" name="posicion_hijo" class="form-control" value="<?= htmlspecialchars($estudiante['posicion_hijo']) ?>" placeholder="Ejemplo: 1, 2, 3..." required />  
    
                                        <small class="text-danger"></small>  
                                    </div>  
                                </div>  

                                <div class="col-md-3" id="cedula_escolar_field">  
                                    <div class="form-group">  
                                        <label for="cedula_escolar" class="obligatorio">Cédula Escolar</label>  
                                        <input type="text" id="cedula_escolar" name="cedula_escolar" class="form-control" required value="<?= $estudiante['cedula_escolar'] ?>" readonly />  
                                    </div>  
                                </div>  
                                <div class="col-md-3">  
                                    <div class="form-group">  
                                        <label for="nombres" class="obligatorio">Nombres</label>  
                                        <input type="text" id="nombres" name="nombres" class="form-control" required pattern="[A-Za-záéíóúÁÉÍÓÚ ]+" title="Solo se permiten letras y espacios" value="<?= $estudiante['nombres'] ?>" />  
                                    </div>  
                                </div>  
                            </div>  

                            <div class="row">  
                                <div class="col-md-3">  
                                    <div class="form-group">  
                                        <label for="apellidos" class="obligatorio">Apellidos</label>  
                                        <input type="text" id="apellidos" name="apellidos" class="form-control" required pattern="[A-Za-záéíóúÁÉÍÓÚ ]+" title="Solo se permiten letras y espacios" value="<?= $estudiante['apellidos'] ?>" />  
                                    </div>  
                                </div>  

                                <div class="col-md-3">  
                                    <div class="form-group">  
                                        <label for="fecha_nacimiento">Fecha de Nacimiento</label>  
                                        <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" class="form-control" required max="<?= date('Y-m-d', strtotime('-3 years')) ?>" value="<?= $estudiante['fecha_nacimiento'] ?>" onchange="actualizarCedulaEscolar()" />  
                                    </div>  
                                </div>  

                                <div class="col-md-3">  
                                    <div class="form-group">  
                                        <label for="genero">Sexo</label>  
                                        <select id="genero" name="genero" class="form-control" required>  
                                            <option value="" disabled>Seleccione sexo</option>  
                                            <option value="masculino" <?= ($estudiante['genero'] === 'masculino') ? 'selected' : ''; ?>>Masculino</option>  
                                            <option value="femenino" <?= ($estudiante['genero'] === 'femenino') ? 'selected' : ''; ?>>Femenino</option>  
                                        </select>  
                                    </div>  
                                    </div>  
                                    <div class="col-md-3">  
                                    <div class="form-group">  
                                        <label for="tipo_discapacidad">¿Posee alguna discapacidad?</label>  
                                        <select id="tipo_discapacidad" name="tipo_discapacidad" class="form-control" required>  
                                            <option value="" disabled>Seleccione tipo de discapacidad</option>  
                                            <option value="visual" <?= ($estudiante['tipo_discapacidad'] === 'visual') ? 'selected' : ''; ?>>Visual</option>  
                                            <option value="auditiva" <?= ($estudiante['tipo_discapacidad'] === 'auditiva') ? 'selected' : ''; ?>>Auditiva</option>  
                                            <option value="motora" <?= ($estudiante['tipo_discapacidad'] === 'motora') ? 'selected' : ''; ?>>Motora</option>  
                                            <option value="intelectual" <?= ($estudiante['tipo_discapacidad'] === 'intelectual') ? 'selected' : ''; ?>>Intelectual</option>  
                                            <option value="psicosocial" <?= ($estudiante['tipo_discapacidad'] === 'psicosocial') ? 'selected' : ''; ?>>Psicosocial</option>  
                                            <option value="otra" <?= ($estudiante['tipo_discapacidad'] === 'otra') ? 'selected' : ''; ?>>Otra</option>  
                                            <option value="ninguna" <?= ($estudiante['tipo_discapacidad'] === 'ninguna') ? 'selected' : ''; ?>>Ninguna</option>  
                                        </select>  
                                    </div>  
                                </div>  
                            </div>  

                            <div class="row">  
                                <div class="col-md-3">  
                                    <div class="form-group">  
                                        <label for="correo_electronico">Correo Electrónico</label>  
                                        <input type="email" id="correo_electronico" name="correo_electronico" class="form-control" required value="<?= $estudiante['correo_electronico'] ?>" />  
                                    </div>  
                                </div>  
                                <div class="col-md-3">  
                                    <div class="form-group">  
                                        <label for="direccion" class="obligatorio">Dirección</label>  
                                        <input type="text" id="direccion" name="direccion" class="form-control" required value="<?= $estudiante['direccion'] ?>" />  
                                    </div>  
                                </div>  
                                <div class="col-md-3">  
                                    <div class="form-group">  
                                        <label for="numeros_telefonicos" class="obligatorio">Teléfono</label>  
                                        <input type="tel" id="numeros_telefonicos" name="numeros_telefonicos" class="form-control" required pattern="[0-9]{11}" title="El teléfono debe tener exactamente 11 dígitos numéricos" value="<?= $estudiante['numeros_telefonicos'] ?>" />  
                                    </div>  
                                </div> 
                                <div class="col-md-3">  
                                    <div class="form-group">  
                                        <label for="estatus">Estatus</label>  
                                        <select id="estatus" name="estatus" class="form-control" required>  
                                            <option value="" disabled>Seleccione estatus</option>  
                                            <option value="activo" <?= ($estudiante['estatus'] === 'activo') ? 'selected' : ''; ?>>Activo</option>  
                                            <option value="inactivo" <?= ($estudiante['estatus'] === 'inactivo') ? 'selected' : ''; ?>>Inactivo</option>  
                                        </select>  
                                    </div>  
                                </div>  
                            </div>
                            <div class="row">  
                                <div class="col-md-12">  
                                    <div class="form-group">  
                                        <center>  
                                            <button type="submit" class="btn btn-primary">Actualizar</button>  
                                            <a href="<?= APP_URL; ?>/admin/estudiantes/Lista_de_estudiante.php" class="btn btn-danger">Cancelar</a>  
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
<?php
include ('../../admin/layout/parte2.php');
include ('../../layout/mensajes.php');
?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>  
<script>  
    function validarFormulario(event) {
    event.preventDefault(); // Evitar el envío del formulario por defecto
    Swal.fire({
        title: '¿Está seguro?',
        text: "Los datos se actualizarán",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, actualizar!',
        cancelButtonText: 'No, cancelar' // Texto para el botón "No"
    }).then((result) => {
        if (result.isConfirmed) {
            // Si el usuario confirma, enviar el formulario
            document.querySelector('form').submit();
        }
    });
} 

    function actualizarCedulaEscolar() {  
        const tipoCedula = document.getElementById('tipo_cedula').value; // Obtener el tipo de cédula  
        const cedulaRepresentante = document.querySelector('input[name="id_representante"]').value; // Obtener la cédula del representante  
        const fechaNacimiento = document.getElementById('fecha_nacimiento').value; // Obtener la fecha de nacimiento  
        const posicionHijo = document.getElementById('posicion_hijo').value; // Obtener la posición del hijo  

        if (fechaNacimiento && posicionHijo) {  
            // Formatear la fecha de nacimiento  
            const fecha = new Date(fechaNacimiento);  
            const anio = fecha.getFullYear().toString().slice(-2); // Obtener los últimos dos dígitos del año  

            // Concatenar para formar la cédula escolar  
            const nuevaCedulaEscolar = `${tipoCedula}${posicionHijo}${anio}${cedulaRepresentante}`;  
            document.getElementById('cedula_escolar').value = nuevaCedulaEscolar; // Actualizar el campo de cédula escolar  
        }  
    }  

    function validarCedulaIdentidad() {  
        const cedula = document.getElementById('cedula').value;  
        const cedulaEscolar = document.getElementById('cedula_escolar').value;  

        // Simulamos la verificación con un arreglo de cédulas registradas  
        const cedulasRegistradas = ['12345678', '87654321', '14725836']; // Ejemplo de cédulas registradas  
        const cedulasEscolaresRegistradas = ['V12214756124', 'E12321475612']; // Ejemplo de cédulas escolares registradas  

        if (cedulasRegistradas.includes(cedula)) {  
            Swal.fire({  
                icon: 'error',  
                title: 'Error',  
                text: 'La cédula de identidad ya está registrada.',  
            });  
            document.getElementById('cedula').value = ''; // Limpiar el campo de cédula  
            return false;  
        }  

        if (cedulasEscolaresRegistradas.includes(cedulaEscolar)) {  
            Swal.fire({  
                icon: 'error',  
                title: 'Error',  
                text: 'La cédula escolar ya está registrada.',  
            });  
            document.getElementById('cedula_escolar').value = ''; // Limpiar el campo de cédula escolar  
            return false;  
        }  

        return true; // Si no hay errores, permitir el envío del formulario  
    }  

    // Agregar eventos para actualizar la cédula escolar al cambiar la fecha de nacimiento o la posición del hijo  
    document.getElementById('fecha_nacimiento').addEventListener('change', actualizarCedulaEscolar);  
    document.getElementById('posicion_hijo').addEventListener('change', actualizarCedulaEscolar);  
</script>  

<?php  
// Mostrar mensaje de error si está presente en la sesión  
if (isset($_SESSION['message'])) {  
    echo '<script>  
            Swal.fire({  
                icon: "error",  
                title: "Error",  
                text: "' . $_SESSION['message'] . '",  
                confirmButtonText: "Aceptar"  
            });  
          </script>';  
    unset($_SESSION['message']);  // Limpiar el mensaje después de mostrarlo  
}  

// Mostrar mensaje de éxito si se ha actualizado correctamente  
if (isset($_GET['msg']) && $_GET['msg'] == 'success') {  
    echo '<script>  
            Swal.fire({  
                icon: "success",  
                title: "Actualización exitosa",  
                text: "Los datos del estudiante se han actualizado correctamente.",  
                confirmButtonText: "Aceptar"  
            }).then((result) => {  
                if (result.isConfirmed) {  
                    window.location.href = "' . APP_URL . '/admin/estudiantes?id_representante=' . $estudiante['cedula_representante'] . '";  
                }  
            });  
          </script>';  
}  
?>