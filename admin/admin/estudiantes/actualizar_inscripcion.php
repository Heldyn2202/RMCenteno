<?php
$id_estudiante = $_GET['id']; // Asegúrate de que este ID esté disponible

include('../../app/config.php');
include('../../admin/layout/parte1.php');
include ('../../app/controllers/estudiantes/datos_del_estudiante.php');

// Obtener datos de la inscripción actual
$sql_inscripcion = "SELECT i.*, g.desde, g.hasta, gr.grado, s.nombre_seccion, s.cupo_maximo, s.estudiantes_inscritos
                    FROM inscripciones i
                    JOIN gestiones g ON i.id_gestion = g.id_gestion
                    JOIN grados gr ON i.id_grado = gr.id_grado
                    JOIN secciones s ON i.id_seccion = s.id_seccion
                    WHERE i.id_estudiante = :id_estudiante AND i.estado = 1
                    ORDER BY i.id_inscripcion DESC LIMIT 1";
$query_inscripcion = $pdo->prepare($sql_inscripcion);
$query_inscripcion->bindParam(':id_estudiante', $id_estudiante);
$query_inscripcion->execute();
$inscripcion_actual = $query_inscripcion->fetch(PDO::FETCH_ASSOC);

// Obtener el periodo académico activo
$sql_gestiones = "SELECT * FROM gestiones WHERE estado = 1 ORDER BY desde DESC LIMIT 1";
$query_gestiones = $pdo->prepare($sql_gestiones);
$query_gestiones->execute();
$gestion_activa = $query_gestiones->fetch(PDO::FETCH_ASSOC);

// Obtener los grados registrados
$sql_grados = "SELECT * FROM grados";
$query_grados = $pdo->prepare($sql_grados);
$query_grados->execute();
$grados = $query_grados->fetchAll(PDO::FETCH_ASSOC);

// Obtener todas las secciones para el select
$sql_secciones = "SELECT * FROM secciones WHERE estado = 1";
$query_secciones = $pdo->prepare($sql_secciones);
$query_secciones->execute();
$secciones = $query_secciones->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="content-wrapper">
    <br>
    <div class="content">
        <div class="container">
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-12">
                            <h3 class="m-0">Actualizar Inscripción para el estudiante: <?= htmlspecialchars($nombres); ?> <?= htmlspecialchars($apellidos); ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            <hr>
            <form action="<?= APP_URL; ?>/app/controllers/inscripciones/actualizar_inscripcion.php" method="POST">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="id_gestion" class="obligatorio">Periodo académico</label>
                            <?php if ($gestion_activa): ?>
                                <input type="text" id="id_gestion" name="id_gestion" class="form-control" value="Desde: <?= htmlspecialchars($gestion_activa['desde']); ?> Hasta: <?= htmlspecialchars($gestion_activa['hasta']); ?>" readonly>
                                <input type="hidden" name="id_gestion" value="<?= $gestion_activa['id_gestion']; ?>">
                            <?php else: ?>
                                <input type="text" id="id_gestion" name="id_gestion" class="form-control" value="No hay periodo activo" readonly>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="nivel_id" class="obligatorio">Nivel</label>
                            <select id="nivel_id" name="nivel_id" class="form-control" required onchange="filtrarGrados()">
                                <option value="">Seleccione un nivel</option>
                                <option value="Inicial" <?= ($inscripcion_actual && $inscripcion_actual['nivel'] == 'Inicial') ? 'selected' : ''; ?>>Inicial</option>
                                <option value="Primaria" <?= ($inscripcion_actual && $inscripcion_actual['nivel'] == 'Primaria') ? 'selected' : ''; ?>>Primaria</option>
                                <option value="Secundaria" <?= ($inscripcion_actual && $inscripcion_actual['nivel'] == 'Secundaria') ? 'selected' : ''; ?>>Secundaria</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="grado" class="obligatorio">Grado</label>
                            <select id="grado" name="grado" class="form-control" required onchange="filtrarSecciones()">
                                <option value="">Seleccione un grado</option>
                                <?php foreach ($grados as $grado): ?>
                                    <option value="<?= htmlspecialchars($grado['id_grado']); ?>" 
                                        <?= ($inscripcion_actual && $inscripcion_actual['id_grado'] == $grado['id_grado']) ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($grado['grado']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="turno_id" class="obligatorio">Turno</label>
                            <select id="turno_id" name="turno_id" class="form-control" required onchange="filtrarSecciones()">
                                <option value="">Seleccione un turno</option>
                                <option value="M" <?= ($inscripcion_actual && $inscripcion_actual['turno'] == 'M') ? 'selected' : ''; ?>>Mañana</option>
                                <option value="T" <?= ($inscripcion_actual && $inscripcion_actual['turno'] == 'T') ? 'selected' : ''; ?>>Tarde</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="nombre_seccion" class="obligatorio">Sección</label>
                            <select id="nombre_seccion" name="id_seccion" class="form-control" required onchange="mostrarCupos()">
                                <option value="">Seleccione una sección</option>
                                <?php foreach ($secciones as $seccion): ?>
                                    <option value="<?= htmlspecialchars($seccion['id_seccion']); ?>" 
                                        <?= ($inscripcion_actual && $inscripcion_actual['id_seccion'] == $seccion['id_seccion']) ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($seccion['nombre_seccion']); ?>
                                    </option>
                                <?php endforeach; ?>
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
                            <input type="text" id="talla_camisa" name="talla_camisa" class="form-control" required pattern="[A-Za-z0-9áéíóúÁÉÍÓÚ ]+" title="Solo se permiten letras, números y espacios" value="<?= $inscripcion_actual ? htmlspecialchars($inscripcion_actual['talla_camisa']) : ''; ?>">
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="talla_pantalon" class="obligatorio">Talla de pantalón</label>
                            <input type="text" id="talla_pantalon" name="talla_pantalon" class="form-control" required pattern="[A-Za-z0-9áéíóúÁÉÍÓÚ ]+" title="Solo se permiten letras, números y espacios" value="<?= $inscripcion_actual ? htmlspecialchars($inscripcion_actual['talla_pantalon']) : ''; ?>">
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="talla_zapatos" class="obligatorio">Talla de zapatos</label>
                        <input type="text" id="talla_zapatos" name="talla_zapatos" class="form-control" required pattern="\d+" title="Solo se permiten números" value="<?= $inscripcion_actual ? htmlspecialchars($inscripcion_actual['talla_zapatos']) : ''; ?>">
                    </div>
                </div>

                <input type="hidden" name="id_estudiante" value="<?php echo htmlspecialchars($id_estudiante); ?>">
                <input type="hidden" name="id_inscripcion" value="<?= $inscripcion_actual ? $inscripcion_actual['id_inscripcion'] : ''; ?>">

                <hr>
                <div class="row">
                    <div class="col-md-12 text-center">
                        <button type="submit" class="btn btn-success">Actualizar Inscripción</button>
                        <a href="<?= APP_URL; ?>/admin/estudiantes/Lista_de_estudiante.php" class="btn btn-secondary">Cancelar</a>
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

<script>
    // Cargar las secciones al cargar la página si ya hay una inscripción
    window.onload = function() {
        <?php if ($inscripcion_actual): ?>
            // Mostrar los cupos disponibles de la sección actual
            mostrarCuposActual();
        <?php endif; ?>
    };

    function mostrarCuposActual() {
        var seccionId = "<?= $inscripcion_actual ? $inscripcion_actual['id_seccion'] : ''; ?>";
        var cuposDisponibles = document.getElementById('cupos_disponibles');

        if (seccionId) {
            // Realizar una solicitud AJAX para obtener los cupos disponibles
            var xhr = new XMLHttpRequest();
            xhr.open('GET', '<?= APP_URL; ?>/admin/estudiantes/obtener_cupos.php?id_seccion=' + seccionId, true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    var cupos = JSON.parse(xhr.responseText);
                    cuposDisponibles.value = cupos.cupos_disponibles;
                }
            };
            xhr.send();
        } else {
            cuposDisponibles.value = '';
        }
    }

    function filtrarSecciones() {
        var turnoId = document.getElementById('turno_id').value;
        var gradoId = document.getElementById('grado').value; // Obtener el grado seleccionado
        var secciones = document.getElementById('nombre_seccion');
        var cuposDisponibles = document.getElementById('cupos_disponibles'); // Elemento para mostrar los cupos

        // Limpiar las opciones existentes
        secciones.innerHTML = '<option value="">Seleccione una sección</option>';
        cuposDisponibles.value = ''; // Limpiar el campo de cupos disponibles

        // Enviar una solicitud AJAX para obtener las secciones filtradas por turno y grado
        var xhr = new XMLHttpRequest();
        xhr.open('GET', '<?= APP_URL; ?>/admin/estudiantes/obtener_secciones.php?turno=' + turnoId + '&grado=' + gradoId, true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                var seccionesFiltradas = JSON.parse(xhr.responseText);
                seccionesFiltradas.forEach(function(seccion) {
                    var option = document.createElement('option');
                    option.value = seccion.id_seccion;
                    option.text = seccion.nombre_seccion;
                    secciones.add(option);
                });
            }
        };
        xhr.send();

        // Agregar un evento para mostrar los cupos disponibles al seleccionar una sección
        secciones.onchange = function() {
            var selectedOption = secciones.options[secciones.selectedIndex];
            if (selectedOption.value) {
                // Realizar una solicitud AJAX para obtener los cupos disponibles
                var xhrCupos = new XMLHttpRequest();
                xhrCupos.open('GET', '<?= APP_URL; ?>/admin/estudiantes/obtener_cupos.php?id_seccion=' + selectedOption.value, true);
                xhrCupos.onreadystatechange = function() {
                    if (xhrCupos.readyState === 4 && xhrCupos.status === 200) {
                        var cupos = JSON.parse(xhrCupos.responseText);
                        cuposDisponibles.value = cupos.cupos_disponibles; // Asignar el valor de cupos disponibles
                    }
                };
                xhrCupos.send();
            } else {
                cuposDisponibles.value = ''; // Limpiar si no hay sección seleccionada
            }
        };
    }

    function filtrarGrados() {
        var nivelId = document.getElementById('nivel_id').value;
        var grados = document.getElementById('grado');

        // Limpiar las opciones existentes
        grados.innerHTML = '<option value="">Seleccione un grado</option>';

        // Enviar una solicitud AJAX para obtener los grados filtrados por nivel
        var xhr = new XMLHttpRequest();
        xhr.open('GET', '<?= APP_URL; ?>/admin/estudiantes/obtener_grados.php?nivel=' + nivelId, true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                var gradosFiltrados = JSON.parse(xhr.responseText);
                gradosFiltrados.forEach(function(grado) {
                    var option = document.createElement('option');
                    option.value = grado.id_grado;
                    option.text = grado.grado;
                    grados.add(option);
                });
            }
        };
        xhr.send();
    }

    function mostrarCupos() {
        var seccionId = document.getElementById('nombre_seccion').value;
        var cuposDisponibles = document.getElementById('cupos_disponibles');

        if (seccionId) {
            // Realizar una solicitud AJAX para obtener los cupos disponibles
            var xhr = new XMLHttpRequest();
            xhr.open('GET', '<?= APP_URL; ?>/admin/estudiantes/obtener_cupos.php?id_seccion=' + seccionId, true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    var cupos = JSON.parse(xhr.responseText);
                    cuposDisponibles.value = cupos.cupos_disponibles;
                }
            };
            xhr.send();
        } else {
            cuposDisponibles.value = '';
        }
    }
</script>