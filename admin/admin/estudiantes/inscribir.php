<?php  
$id_estudiante = $_GET['id']; // Asegúrate de que este ID esté disponible
include('../../app/config.php');  
include('../../admin/layout/parte1.php');  
include ('../../app/controllers/estudiantes/datos_del_estudiante.php');
include('../../app/controllers/estudiantes/listado_de_estudiantes.php');   

// Obtener el periodo académico activo  
$sql_gestiones = "SELECT * FROM gestiones WHERE estado = 1 ORDER BY desde DESC LIMIT 1";  
$query_gestiones = $pdo->prepare($sql_gestiones);  
$query_gestiones->execute();  
$gestion_activa = $query_gestiones->fetch(PDO::FETCH_ASSOC);  

// Obtener los grados registrados  
$sql_grados = "SELECT * FROM grados WHERE estado = 1";  
$query_grados = $pdo->prepare($sql_grados);  
$query_grados->execute();  
$grados = $query_grados->fetchAll(PDO::FETCH_ASSOC);  

// Obtener información del estudiante para validaciones
$sql_estudiante = "SELECT * FROM estudiantes WHERE id_estudiante = :id_estudiante";
$query_estudiante = $pdo->prepare($sql_estudiante);
$query_estudiante->bindParam(':id_estudiante', $id_estudiante);
$query_estudiante->execute();
$estudiante_info = $query_estudiante->fetch(PDO::FETCH_ASSOC);

// Obtener si el estudiante tiene aplazos pendientes
$sql_aplazos = "SELECT COUNT(*) as total FROM estudiantes_aplazados 
                WHERE id_estudiante = :id_estudiante 
                AND estado = 'pendiente'";
$query_aplazos = $pdo->prepare($sql_aplazos);
$query_aplazos->bindParam(':id_estudiante', $id_estudiante);
$query_aplazos->execute();
$aplazos_info = $query_aplazos->fetch(PDO::FETCH_ASSOC);
$tiene_aplazos = ($aplazos_info['total'] > 0);

// Obtener el ÚLTIMO grado donde estuvo inscrito el estudiante
$sql_ultima_inscripcion = "SELECT i.*, g.grado as nombre_grado, g.id_grado 
                          FROM inscripciones i 
                          JOIN secciones s ON i.id_seccion = s.id_seccion
                          JOIN grados g ON s.id_grado = g.id_grado
                          WHERE i.id_estudiante = :id_estudiante 
                          ORDER BY i.id DESC LIMIT 1";
$query_ultima_inscripcion = $pdo->prepare($sql_ultima_inscripcion);
$query_ultima_inscripcion->bindParam(':id_estudiante', $id_estudiante);
$query_ultima_inscripcion->execute();
$ultima_inscripcion = $query_ultima_inscripcion->fetch(PDO::FETCH_ASSOC);

// Obtener información de los aplazos si existen
$aplazos_detalle = [];
if ($tiene_aplazos) {
    $sql_aplazos_detalle = "SELECT motivo FROM estudiantes_aplazados 
                           WHERE id_estudiante = :id_estudiante 
                           AND estado = 'pendiente' 
                           ORDER BY fecha_aplazado DESC LIMIT 1";
    $query_aplazos_detalle = $pdo->prepare($sql_aplazos_detalle);
    $query_aplazos_detalle->bindParam(':id_estudiante', $id_estudiante);
$query_aplazos_detalle->execute();
    $aplazos_detalle = $query_aplazos_detalle->fetchAll(PDO::FETCH_ASSOC);
}

// Función para obtener el número del grado
function obtenerNumeroGrado($nombreGrado) {
    if (preg_match('/(\d+)/', $nombreGrado, $matches)) {
        return intval($matches[1]);
    }
    return 0;
}

// Determinar el último grado del estudiante
$ultimo_grado_numero = 0;
$ultimo_grado_nombre = 'Ninguno';
$ultimo_grado_id = 0;

if ($ultima_inscripcion) {
    $ultimo_grado_nombre = $ultima_inscripcion['nombre_grado'];
    $ultimo_grado_numero = obtenerNumeroGrado($ultima_inscripcion['nombre_grado']);
    $ultimo_grado_id = $ultima_inscripcion['id_grado']; // ID del grado, no el número
}

// Verificar si el estudiante ya está inscrito en el periodo activo
$ya_inscrito = false;
if ($gestion_activa) {
    $sql_verificacion = "SELECT COUNT(*) as total FROM inscripciones 
                         WHERE id_estudiante = :id_estudiante 
                         AND id_gestion = :id_gestion";  
    $stmt_verificacion = $pdo->prepare($sql_verificacion);  
    $stmt_verificacion->bindParam(':id_estudiante', $id_estudiante);  
    $stmt_verificacion->bindParam(':id_gestion', $gestion_activa['id_gestion']);  
    $stmt_verificacion->execute();  
    $verificacion = $stmt_verificacion->fetch(PDO::FETCH_ASSOC);
    $ya_inscrito = ($verificacion['total'] > 0);
}
?> 

<div class="content-wrapper">  
    <br>  
    <div class="content">  
        <div class="container">  
            <div class="content-header">  
                <div class="container-fluid">  
                    <div class="row mb-2">  
                        <div class="col-sm-12">  
                            <h3 class="m-0">Formulario de Inscripción para el estudiante: <?= htmlspecialchars($nombres); ?> <?= htmlspecialchars($apellidos); ?></h3>
                            
                            <!-- ALERTA ROJA MUY VISIBLE PARA APLAZADOS -->
                            <?php if ($tiene_aplazos): ?>
                                <div class="alert alert-danger mt-2" style="border-left: 5px solid #dc3545; background-color: #fde8e8;">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-ban fa-2x mr-3" style="color: #dc3545;"></i>
                                        <div>
                                            <h5 class="alert-heading mb-1" style="color: #000;"><strong>¡ESTUDIANTE REPITENTE - APLAZOS PENDIENTES!</strong></h5>
                                            <p class="mb-0" style="color: #000;">
                                                <strong>Restricción:</strong> Este estudiante tiene aplazos pendientes y por política académica 
                                                <span class="font-weight-bold" style="color: #dc3545;">SOLO PUEDE REPETIR EL MISMO GRADO</span>.
                                            </p>
                                            <p class="mb-0 mt-1" style="color: #000;">
                                                <i class="fas fa-graduation-cap mr-1" style="color: #f39c12;"></i> 
                                                <strong>Último grado cursado:</strong> <?= htmlspecialchars($ultimo_grado_nombre); ?>
                                            </p>
                                            <p class="mb-0" style="color: #000;">
                                                <i class="fas fa-lock mr-1" style="color: #e74c3c;"></i> 
                                                <strong>Grado permitido:</strong> 
                                                <span class="badge badge-danger p-2"><?= htmlspecialchars($ultimo_grado_nombre); ?></span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($ultima_inscripcion && !$tiene_aplazos): ?>
                                <div class="alert alert-info mt-2">
                                    <i class="fas fa-info-circle"></i> 
                                    Último grado inscrito: <strong><?= htmlspecialchars($ultimo_grado_nombre); ?></strong>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($ya_inscrito): ?>
                                <div class="alert alert-warning mt-2">
                                    <i class="fas fa-exclamation-triangle"></i> 
                                    <strong>ESTUDIANTE YA INSCRITO</strong><br>
                                    Este estudiante ya está inscrito en el periodo académico actual.
                                </div>
                            <?php endif; ?>
                        </div>  
                    </div>  
                </div>  
            </div>  
            <hr>  
            <form action="<?= APP_URL; ?>/admin/estudiantes/Lista_de_inscripcion.php" method="POST" id="formInscripcion">  
                <div class="row">  
                    <div class="col-md-3">  
                        <div class="form-group">  
                            <label for="id_gestion" class="obligatorio">Periodo académico</label>  
                            <?php if ($gestion_activa): ?>  
                                <input type="text" id="id_gestion" name="id_gestion" class="form-control" value="Desde: <?= htmlspecialchars($gestion_activa['desde']); ?> Hasta: <?= htmlspecialchars($gestion_activa['hasta']); ?>" readonly>  
                                <input type="hidden" name="id_gestion_hidden" value="<?= htmlspecialchars($gestion_activa['id_gestion']); ?>">  
                            <?php else: ?>  
                                <input type="text" id="id_gestion" name="id_gestion" class="form-control" value="No hay periodo activo" readonly>  
                            <?php endif; ?>  
                        </div>  
                    </div>  

                    <div class="col-md-3">  
                        <div class="form-group">  
                            <label for="nivel_id" class="obligatorio">Nivel</label>  
                            <select id="nivel_id" name="nivel_id" class="form-control" required onchange="filtrarGrados()" <?= $ya_inscrito ? 'disabled' : '' ?>>  
                                <option value="">Seleccione un nivel</option>  
                                <option value="Primaria">Primaria</option>  
                                <option value="Secundaria">Secundaria</option>  
                            </select>  
                        </div>  
                    </div>

                    <div class="col-md-3">  
                        <div class="form-group">  
                            <label for="grado" class="obligatorio">Grado</label>  
                            <select id="grado" name="grado" class="form-control" required onchange="validarGradoSeleccionado(this)" <?= $ya_inscrito ? 'disabled' : '' ?>>  
                                <option value="">Seleccione un grado</option>  
                                <?php foreach ($grados as $grado_item): ?>  
                                    <?php 
                                    $grado_numero = obtenerNumeroGrado($grado_item['grado']);
                                    $disabled = false;
                                    $mensaje = '';
                                    $restriccion = '';
                                    
                                    // CORRECCIÓN CRÍTICA: Si tiene aplazos, solo puede repetir el mismo grado
                                    if ($tiene_aplazos && $ultimo_grado_id > 0) {
                                        if ($grado_item['id_grado'] != $ultimo_grado_id) {
                                            $disabled = true;
                                            $restriccion = 'data-restriccion="true"';
                                            $mensaje = ' (NO DISPONIBLE - ESTUDIANTE REPITENTE)';
                                        } else {
                                            $mensaje = ' (GRADO PERMITIDO PARA REPITENTE)';
                                        }
                                    }
                                    
                                    // Si ya está inscrito, deshabilitar todo
                                    if ($ya_inscrito) {
                                        $disabled = true;
                                        $mensaje = ' (YA INSCRITO)';
                                    }
                                    ?>
                                    <option value="<?= htmlspecialchars($grado_item['id_grado']); ?>" 
                                            <?= $disabled ? 'disabled style="color:#ccc; background-color:#f8f9fa;"' : '' ?>
                                            <?= $restriccion ?>
                                            data-numero="<?= $grado_numero ?>"
                                            data-nombre="<?= htmlspecialchars($grado_item['grado']) ?>"
                                            data-id="<?= htmlspecialchars($grado_item['id_grado']) ?>">
                                        <?= htmlspecialchars($grado_item['grado']); ?><?= $mensaje ?>
                                    </option>  
                                <?php endforeach; ?>  
                            </select>  
                        </div>  
                    </div>  

                    <div class="col-md-3">  
                        <div class="form-group">  
                            <label for="turno_id" class="obligatorio">Turno</label>  
                            <select id="turno_id" name="turno_id" class="form-control" required onchange="filtrarSecciones()" <?= $ya_inscrito ? 'disabled' : '' ?>>  
                                <option value="">Seleccione un turno</option>  
                                <option value="M">Mañana</option>  
                                <option value="T">Tarde</option>  
                            </select>  
                        </div>  
                    </div>  
                </div>  
                <div class="row">
                    <div class="col-md-3">  
                        <div class="form-group">  
                            <label for="nombre_seccion" class="obligatorio">Sección</label>  
                            <select id="nombre_seccion" name="id_seccion" class="form-control" required onchange="mostrarCupos()" <?= $ya_inscrito ? 'disabled' : '' ?>>  
                                <option value="">Seleccione una sección</option>  
                            </select>  
                        </div>  
                    </div>

                    <div class="col-md-3">  
                        <div class="form-group">  
                            <label for="cupos_disponibles">Cupos disponibles</label>  
                            <input type="text" id="cupos_disponibles" class="form-control" readonly>  
                        </div>  
                    </div>

                    <div class="col-md-3">  
                        <div class="form-group">  
                            <label for="talla_camisa" class="obligatorio">Talla de camisa</label>  
                            <input type="text" id="talla_camisa" name="talla_camisa" class="form-control" required pattern="[A-Za-z0-9áéíóúÁÉÍÓÚ ]+" title="Solo se permiten letras, números y espacios" <?= $ya_inscrito ? 'disabled' : '' ?>>  
                        </div>  
                    </div>  

                    <div class="col-md-3">  
                        <div class="form-group">  
                            <label for="talla_pantalon" class="obligatorio">Talla de pantalón</label>  
                            <input type="text" id="talla_pantalon" name="talla_pantalon" class="form-control" required pattern="[A-Za-z0-9áéíóúÁÉÍÓÚ ]+" title="Solo se permiten letras, números y espacios" <?= $ya_inscrito ? 'disabled' : '' ?>>  
                        </div>  
                    </div>  
                </div>
                <div class="row">
                    <div class="col-md-3">  
                        <div class="form-group">  
                            <label for="talla_zapatos" class="obligatorio">Talla de zapatos</label>  
                            <input type="text" id="talla_zapatos" name="talla_zapatos" class="form-control" required pattern="\d+" title="Solo se permiten números" <?= $ya_inscrito ? 'disabled' : '' ?>>  
                        </div>  
                    </div>  
                    
                    <div class="col-md-9">
                        <div class="alert alert-light border mt-2" style="border-left: 4px solid #17a2b8;">
                            <h6 class="text-info"><i class="fas fa-gavel mr-2"></i><strong>POLÍTICA ACADÉMICA - INSCRIPCIÓN DE ESTUDIANTES</strong></h6>
                            <small class="text-muted">
                                1. <strong>Estudiantes con aplazos (Repitentes):</strong> Solo pueden inscribirse en el MISMO grado que cursaron el periodo anterior.<br>
                                2. <strong>Estudiantes regulares:</strong> Pueden avanzar máximo un año académico.<br>
                                3. <strong>Prohibido:</strong> Saltos de años académicos sin autorización especial.<br>
                                4. <strong>Documentación:</strong> Presentar libreta de notas y documento de identidad.
                            </small>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="id_estudiante" value="<?php echo htmlspecialchars($id_estudiante); ?>">  
                <input type="hidden" id="tiene_aplazos" value="<?php echo $tiene_aplazos ? 'true' : 'false'; ?>">  
                <input type="hidden" id="ultimo_grado_numero" value="<?php echo $ultimo_grado_numero; ?>">  
                <input type="hidden" id="ultimo_grado_nombre" value="<?php echo htmlspecialchars($ultimo_grado_nombre); ?>">  
                <input type="hidden" id="ultimo_grado_id" value="<?php echo $ultimo_grado_id; ?>">  
                <input type="hidden" id="ya_inscrito" value="<?php echo $ya_inscrito ? 'true' : 'false'; ?>">  

                <hr>  
                <div class="row">  
                    <div class="col-md-12 text-center">  
                        <?php if ($ya_inscrito): ?>
                            <button type="button" class="btn btn-secondary btn-lg px-4" disabled>
                                <i class="fas fa-check-circle mr-2"></i> ESTUDIANTE YA INSCRITO
                            </button>
                        <?php else: ?>
                            <button type="submit" class="btn btn-success btn-lg px-4" id="btnInscribir">
                                <i class="fas fa-user-graduate mr-2"></i> CONFIRMAR INSCRIPCIÓN
                            </button>  
                        <?php endif; ?>
                        <a href="<?= APP_URL; ?>/admin/estudiantes/Lista_de_estudiante.php" class="btn btn-outline-secondary btn-lg px-4">
                            <i class="fas fa-arrow-left mr-2"></i> VOLVER A ESTUDIANTES
                        </a>  
                    </div>  
                </div>  
            </form>  
        </div>  
    </div>  
</div>  

<?php  
include('../../admin/layout/parte2.php');  
include('../../layout/mensajes.php');  
?>  

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>  
// Variables globales
var tieneAplazos = document.getElementById('tiene_aplazos').value === 'true';
var ultimoGradoNumero = parseInt(document.getElementById('ultimo_grado_numero').value) || 0;
var ultimoGradoNombre = document.getElementById('ultimo_grado_nombre').value;
var ultimoGradoId = parseInt(document.getElementById('ultimo_grado_id').value) || 0;
var yaInscrito = document.getElementById('ya_inscrito').value === 'true';
var gradoSeleccionado = null;

function obtenerNumeroGrado(gradoNombre) {
    var match = gradoNombre.match(/(\d+)/);
    return match ? parseInt(match[1]) : 0;
}

// FUNCIÓN NUEVA: Validar cuando selecciona un grado
function validarGradoSeleccionado(gradoElement) {
    if (!gradoElement.value) return true;
    
    var gradoOption = gradoElement.options[gradoElement.selectedIndex];
    var gradoId = parseInt(gradoOption.getAttribute('data-id'));
    var gradoNumero = parseInt(gradoOption.getAttribute('data-numero'));
    var gradoNombre = gradoOption.getAttribute('data-nombre');
    var tieneRestriccion = gradoOption.hasAttribute('data-restriccion');
    
    // Guardar el grado seleccionado
    gradoSeleccionado = {
        id: gradoId,
        nombre: gradoNombre,
        numero: gradoNumero
    };
    
    // Si tiene restricción (estudiante repitente intentando elegir otro grado)
    if (tieneRestriccion) {
        Swal.fire({
            title: '¡NO PERMITIDO!',
            html: `<div style="text-align: left;">
                    <div style="background-color: #fde8e8; border-left: 4px solid #e74c3c; padding: 15px; margin-bottom: 15px; border-radius: 4px;">
                        <strong style="color: #000; font-size: 1.2rem;">¡ESTUDIANTE REPITENTE - APLAZOS PENDIENTES!</strong>
                    </div>
                    <p style="color: #000;"><strong>Restricción:</strong> Este estudiante tiene aplazos pendientes y por política académica</p>
                    <p style="color: #000; margin-bottom: 10px;">
                        <i class="fas fa-graduation-cap" style="color: #f39c12;"></i> 
                        <strong>Último grado cursado:</strong> 
                        <span style="background-color: #f39c12; color: white; padding: 5px 10px; border-radius: 4px;">${ultimoGradoNombre}</span>
                    </p>
                    <p style="color: #000; margin-bottom: 15px;">
                        <i class="fas fa-lock" style="color: #e74c3c;"></i> 
                        <strong>Grado permitido:</strong> 
                        <span style="background-color: #e74c3c; color: white; padding: 5px 10px; border-radius: 4px;">${ultimoGradoNombre}</span>
                    </p>
                    <hr>
                    <div style="background-color: #fff3cd; padding: 15px; border-radius: 4px; margin-top: 15px;">
                        <p style="color: #856404; margin: 0;">
                            <i class="fas fa-exclamation-triangle" style="color: #f39c12;"></i>
                            <strong> POR POLÍTICA ACADÉMICA:</strong> Un estudiante repitente solo puede inscribirse en el mismo grado que repite.
                        </p>
                    </div>
                    <p style="margin-top: 15px; color: #000;"><strong>Grado seleccionado:</strong> 
                        <span style="background-color: #95a5a6; color: white; padding: 5px 10px; border-radius: 4px;">${gradoNombre}</span>
                    </p>
                   </div>`,
            icon: 'error',
            confirmButtonText: 'ENTENDIDO',
            confirmButtonColor: '#d33'
        }).then(() => {
            // Resetear al grado permitido si existe, o a vacío
            var gradoSelect = document.getElementById('grado');
            var encontrado = false;
            
            // Buscar el grado permitido
            for (var i = 0; i < gradoSelect.options.length; i++) {
                var opt = gradoSelect.options[i];
                if (parseInt(opt.getAttribute('data-id')) === ultimoGradoId && !opt.disabled) {
                    gradoSelect.selectedIndex = i;
                    encontrado = true;
                    break;
                }
            }
            
            if (!encontrado) {
                gradoSelect.selectedIndex = 0;
            }
            
            // Limpiar secciones si es necesario
            filtrarSecciones();
        });
        return false;
    }
    
    // Si todo está bien, filtrar secciones
    filtrarSecciones();
    return true;
}

function filtrarSecciones() {  
    var turnoId = document.getElementById('turno_id').value;  
    var gradoId = document.getElementById('grado').value; 
    
    // Si ya está inscrito, no hacer nada
    if (yaInscrito) {
        Swal.fire({
            title: 'Acción no permitida',
            text: 'Este estudiante ya está inscrito en el periodo actual.',
            icon: 'error',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#d33'
        });
        return;
    }
    
    var secciones = document.getElementById('nombre_seccion');  
    var cuposDisponibles = document.getElementById('cupos_disponibles');

    secciones.innerHTML = '<option value="">Seleccione una sección</option>';  
    cuposDisponibles.value = '';

    var xhr = new XMLHttpRequest();  
    xhr.open('GET', '<?= APP_URL; ?>/admin/estudiantes/obtener_secciones.php?turno=' + turnoId + '&grado=' + gradoId, true);  
    xhr.onreadystatechange = function() {  
        if (xhr.readyState === 4 && xhr.status === 200) {  
            try {
                var seccionesFiltradas = JSON.parse(xhr.responseText);  
                if (Array.isArray(seccionesFiltradas)) {
                    if (seccionesFiltradas.length > 0) {
                        seccionesFiltradas.forEach(function(seccion) {  
                            var option = document.createElement('option');  
                            option.value = seccion.id_seccion;
                            var cupos = seccion.capacidad - seccion.cupo_actual;
                            option.text = seccion.nombre_seccion + ' (Cupos: ' + cupos + '/' + seccion.capacidad + ')';  
                            secciones.add(option);  
                        });
                    } else {
                        var option = document.createElement('option');  
                        option.value = "";
                        option.text = "No hay secciones disponibles";
                        option.disabled = true;
                        secciones.add(option);
                    }
                }
            } catch (e) {
                console.error('Error al procesar secciones:', e);
            }
        }  
    };
    xhr.send();  

    secciones.onchange = function() {
        var selectedOption = secciones.options[secciones.selectedIndex];
        if (selectedOption.value && selectedOption.value !== "") {
            var xhrCupos = new XMLHttpRequest();
            xhrCupos.open('GET', '<?= APP_URL; ?>/admin/estudiantes/obtener_cupos.php?id_seccion=' + selectedOption.value, true);
            xhrCupos.onreadystatechange = function() {
                if (xhrCupos.readyState === 4 && xhrCupos.status === 200) {
                    try {
                        var cupos = JSON.parse(xhrCupos.responseText);
                        cuposDisponibles.value = cupos.cupos_disponibles;
                        
                        if (cupos.cupos_disponibles <= 0) {
                            Swal.fire({
                                title: 'Sin cupos disponibles',
                                text: 'Esta sección no tiene cupos disponibles. Por favor seleccione otra sección.',
                                icon: 'warning',
                                confirmButtonText: 'Entendido'
                            });
                        }
                    } catch (e) {
                        console.error('Error al procesar cupos:', e);
                        cuposDisponibles.value = 'Error';
                    }
                }
            };
            xhrCupos.send();
        } else {
            cuposDisponibles.value = '';
        }
    };
}

function filtrarGrados() {  
    var nivelId = document.getElementById('nivel_id').value;  
    var grados = document.getElementById('grado');  

    // Si ya está inscrito, no hacer nada
    if (yaInscrito) {
        return;
    }
    
    grados.innerHTML = '<option value="">Seleccione un grado</option>';  

    var xhr = new XMLHttpRequest();  
    xhr.open('GET', '<?= APP_URL; ?>/admin/estudiantes/obtener_grados.php?nivel=' + nivelId, true);  
    xhr.onreadystatechange = function() {  
        if (xhr.readyState === 4 && xhr.status === 200) {  
            try {
                var gradosFiltrados = JSON.parse(xhr.responseText);  
                if (Array.isArray(gradosFiltrados)) {
                    gradosFiltrados.forEach(function(grado) {  
                        var option = document.createElement('option');  
                        option.value = grado.id_grado;
                        option.text = grado.grado;  
                        option.setAttribute('data-numero', obtenerNumeroGrado(grado.grado));
                        option.setAttribute('data-nombre', grado.grado);
                        option.setAttribute('data-id', grado.id_grado);
                        
                        // Validar si este grado está permitido para estudiantes con aplazos
                        if (tieneAplazos && ultimoGradoId > 0) {
                            if (grado.id_grado != ultimoGradoId) {
                                option.disabled = true;
                                option.setAttribute('data-restriccion', 'true');
                                option.text += ' (NO DISPONIBLE - ESTUDIANTE REPITENTE)';
                            } else {
                                option.text += ' (GRADO PERMITIDO PARA REPITENTE)';
                            }
                        }
                        
                        grados.add(option);  
                    });
                }
            } catch (e) {
                console.error('Error al procesar grados:', e);
            }
        }  
    };  
    xhr.send();  
}

// Validación al enviar el formulario
document.getElementById('formInscripcion').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Si ya está inscrito, bloquear envío
    if (yaInscrito) {
        Swal.fire({
            title: 'Estudiante ya inscrito',
            text: 'Este estudiante ya está inscrito en el periodo académico actual.',
            icon: 'info',
            confirmButtonText: 'Entendido'
        });
        return false;
    }
    
    var gradoSelect = document.getElementById('grado');
    var gradoOption = gradoSelect.options[gradoSelect.selectedIndex];
    var gradoId = parseInt(gradoOption.getAttribute('data-id'));
    var gradoNombre = gradoOption.getAttribute('data-nombre');
    
    // VALIDACIÓN FINAL CRÍTICA - Por ID del grado, no por número
    if (tieneAplazos && ultimoGradoId > 0) {
        if (gradoId != ultimoGradoId) {
            Swal.fire({
                title: '¡VALIDACIÓN FALLIDA!',
                html: `<div style="text-align: left;">
                        <div style="background-color: #fde8e8; border-left: 4px solid #e74c3c; padding: 15px; margin-bottom: 15px; border-radius: 4px;">
                            <strong style="color: #000; font-size: 1.2rem;">¡ESTUDIANTE REPITENTE - APLAZOS PENDIENTES!</strong><br>
                            <small style="color: #000;">ESTUDIANTE: <?= htmlspecialchars($nombres . " " . $apellidos) ?></small>
                        </div>
                        
                        <p style="color: #000;"><strong>Restricción:</strong> Este estudiante tiene aplazos pendientes y por política académica</p>
                        
                        <p style="color: #000;">
                            <i class="fas fa-graduation-cap" style="color: #f39c12;"></i> 
                            <strong>Último grado cursado:</strong> 
                            <span style="background-color: #f39c12; color: white; padding: 5px 10px; border-radius: 4px;">${ultimoGradoNombre}</span>
                        </p>
                        
                        <p style="color: #000;">
                            <i class="fas fa-lock" style="color: #e74c3c;"></i> 
                            <strong>Grado permitido:</strong> 
                            <span style="background-color: #e74c3c; color: white; padding: 5px 10px; border-radius: 4px;">${ultimoGradoNombre}</span>
                        </p>
                        
                        <hr>
                        
                        <p style="color: #000;"><strong>Grado solicitado:</strong> 
                            <span style="background-color: #95a5a6; color: white; padding: 5px 10px; border-radius: 4px;">${gradoNombre}</span>
                        </p>
                        
                        <div style="background-color: #fff3cd; padding: 15px; border-radius: 4px; margin-top: 15px;">
                            <strong style="color: #856404;">POLÍTICA ACADÉMICA INCUMPLIDA</strong>
                            <p style="margin-top: 10px; margin-bottom: 0; color: #856404;">
                                <i class="fas fa-exclamation-triangle" style="color: #f39c12;"></i>
                                Los estudiantes con aplazos pendientes <strong>NO PUEDEN AVANZAR DE GRADO</strong> y deben repetir el mismo año académico.
                            </p>
                        </div>
                       </div>`,
                icon: 'error',
                confirmButtonText: 'CORREGIR INSCRIPCIÓN',
                confirmButtonColor: '#d33'
            });
            return false;
        }
    }
    
    // Mostrar confirmación antes de enviar
    Swal.fire({
        title: '¿CONFIRMAR INSCRIPCIÓN?',
        html: `<div style="text-align: left;">
                <p><strong>ESTUDIANTE: <?= htmlspecialchars($nombres . " " . $apellidos) ?></strong></p>
                <p><strong>Grado seleccionado:</strong> 
                    <span style="background-color: ${tieneAplazos ? '#f39c12' : '#3498db'}; color: white; padding: 5px 10px; border-radius: 4px;">${gradoNombre}</span>
                </p>
                ${tieneAplazos ? 
                `<div style="background-color: #fff3cd; padding: 10px; border-radius: 4px; margin-top: 10px;">
                    <p style="color: #856404; margin: 0;">
                        <i class="fas fa-exclamation-triangle" style="color: #f39c12;"></i>
                        <strong> ATENCIÓN:</strong> Este estudiante será inscrito como REPITENTE
                    </p>
                </div>` 
                : ''}
               </div>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'SÍ, INSCRIBIR',
        cancelButtonText: 'CANCELAR',
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d'
    }).then((result) => {
        if (result.isConfirmed) {
            // Si confirma, enviar el formulario
            this.submit();
        }
    });
    
    return false;
});

// Mostrar alerta inicial si el estudiante tiene aplazos
window.onload = function() {
    if (tieneAplazos) {
        setTimeout(function() {
            Swal.fire({
                title: '¡ATENCIÓN! ESTUDIANTE REPITENTE',
                html: `<div style="text-align: left;">
                        <div style="background-color: #fde8e8; border-left: 4px solid #e74c3c; padding: 15px; margin-bottom: 15px; border-radius: 4px;">
                            <strong style="color: #000; font-size: 1.2rem;">INSCRIPCIÓN RESTRINGIDA</strong>
                        </div>
                        
                        <p style="color: #000;"><strong>Estudiante:</strong> <?= htmlspecialchars($nombres . " " . $apellidos) ?></p>
                        
                        <p style="color: #000;"><strong>Motivo:</strong> Tiene aplazos pendientes del periodo anterior.</p>
                        
                        <hr>
                        
                        <p style="color: #000;">
                            <i class="fas fa-lock" style="color: #e74c3c;"></i>
                            <strong> Grado permitido para inscripción:</strong> 
                            <span style="background-color: #27ae60; color: white; padding: 5px 10px; border-radius: 4px;">${ultimoGradoNombre}</span>
                        </p>
                        
                        <div style="background-color: #e8f4fd; padding: 10px; border-radius: 4px; margin-top: 15px;">
                            <p style="color: #2980b9; margin: 0;">
                                <i class="fas fa-gavel" style="color: #3498db;"></i>
                                <strong> POLÍTICA ACADÉMICA:</strong> Los estudiantes con aplazos solo pueden inscribirse en el mismo grado.
                            </p>
                        </div>
                       </div>`,
                icon: 'warning',
                confirmButtonText: 'ENTENDIDO',
                confirmButtonColor: '#17a2b8'
            });
        }, 800);
    }
};
</script>