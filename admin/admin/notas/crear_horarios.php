<?php
// Incluir configuraciones y procesar todo ANTES de cualquier output
include('../../app/config.php');

// 1. Obtener datos necesarios de la base de datos

// Obtener periodo académico activo
$gestion_activa = $pdo->query("SELECT * FROM gestiones WHERE estado = 1 ORDER BY desde DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);

// Obtener datos para formulario
$grados = $pdo->query("SELECT * FROM grados ORDER BY trayecto, trimestre")->fetchAll(PDO::FETCH_ASSOC);
$secciones = $pdo->query("SELECT * FROM secciones ORDER BY nombre_seccion")->fetchAll(PDO::FETCH_ASSOC);
$materias = $pdo->query("SELECT * FROM materias ORDER BY nombre_materia")->fetchAll(PDO::FETCH_ASSOC);
$profesores = $pdo->query("SELECT * FROM profesores ORDER BY apellidos, nombres")->fetchAll(PDO::FETCH_ASSOC);

// 2. Procesar el formulario si se envió
// ------------------------------------
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['grado'])) {
    // Insertar horario principal
    $stmt = $pdo->prepare("INSERT INTO horarios 
                          (id_gestion, id_grado, id_seccion, aula, fecha_inicio, fecha_fin) 
                          VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $gestion_activa['id_gestion'],
        $_POST['grado'],
        $_POST['seccion'],
        $_POST['aula'] ?? null,
        $_POST['fecha_inicio'] ?? null,
        $_POST['fecha_fin'] ?? null
    ]);
    $id_horario = $pdo->lastInsertId();

    // Insertar detalles del horario (si existen)
    if (!empty($_POST['horario'])) {
        $stmt_detalle = $pdo->prepare("INSERT INTO horario_detalle 
                                      (id_horario, dia_semana, hora_inicio, hora_fin, id_materia, id_profesor) 
                                      VALUES (?, ?, ?, ?, ?, ?)");
        
        foreach ($_POST['horario'] as $dia => $bloques) {
            foreach ($bloques as $hora_inicio => $bloque) {
                if (!empty($bloque['materia'])) {
                    $stmt_detalle->execute([
                        $id_horario,
                        $dia,
                        $bloque['hora_inicio'],
                        $bloque['hora_fin'],
                        $bloque['materia'],
                        $bloque['profesor'] ?? null
                    ]);
                }
            }
        }
    }

    // Redirigir a PDF
    header("Location: generar_horario_pdf.php?id_horario=$id_horario");
    exit();
}

// 3. Incluir cabecera HTML (después de todo el procesamiento)
// ----------------------------------------------------------
include('../../admin/layout/parte1.php');
?>

<!-- Contenido HTML -->
<div class="content-wrapper">
    <div class="content">
        <div class="container-fluid">
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h3 class="m-0">Creación de Horarios Escolares</h3>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="#">Inicio</a></li>
                                <li class="breadcrumb-item"><a href="<?= APP_URL ?>/admin/reportes">Reportes</a></li>
                                <li class="breadcrumb-item active">Horarios Escolares</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Formulario Principal -->
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Configuración del Horario</h3>
                </div>
                <form method="POST">
                    <div class="card-body">
                        <!-- Sección de Configuración Básica -->
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Período Académico</label>
                                    <input type="text" class="form-control" readonly 
                                           value="<?= $gestion_activa ? 'Desde: '.date('d/m/Y', strtotime($gestion_activa['desde'])).' Hasta: '.date('d/m/Y', strtotime($gestion_activa['hasta'])) : 'No hay período activo' ?>">
                                    <?php if($gestion_activa): ?>
                                    <input type="hidden" name="id_gestion" value="<?= $gestion_activa['id_gestion'] ?>">
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="grado">Grado</label>
                                    <select id="grado" name="grado" class="form-control" required>
                                        <option value="">Seleccionar Grado</option>
                                        <?php foreach($grados as $g): ?>
                                        <option value="<?= $g['id_grado'] ?>"><?= htmlspecialchars($g['grado']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="seccion">Sección/Turno</label>
                                    <select id="seccion" name="seccion" class="form-control" required>
                                        <option value="">Seleccionar Sección</option>
                                        <?php foreach($secciones as $s): ?>
                                        <option value="<?= $s['id_seccion'] ?>">
                                            <?= htmlspecialchars($s['nombre_seccion']) ?> (<?= htmlspecialchars($s['turno']) ?>)
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Información Adicional -->
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Aula</label>
                                    <input type="text" name="aula" class="form-control" placeholder="Ej: Aula 101">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Fecha Inicio</label>
                                    <input type="date" name="fecha_inicio" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Fecha Fin</label>
                                    <input type="date" name="fecha_fin" class="form-control">
                                </div>
                            </div>
                        </div>

                        <!-- Horario Semanal -->
                        <div class="table-responsive mt-4">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Hora</th>
                                        <th>Lunes</th>
                                        <th>Martes</th>
                                        <th>Miércoles</th>
                                        <th>Jueves</th>
                                        <th>Viernes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $horarios = [
                                        ['07:50', '08:30'], ['08:30', '09:10'], ['09:10', '09:50'],
                                        ['10:10', '10:50'], ['10:50', '11:30'], ['11:30', '12:10']
                                    ];
                                    
                                    foreach($horarios as $bloque): 
                                        list($inicio, $fin) = $bloque;
                                    ?>
                                    <tr>
                                        <td><?= "$inicio - $fin" ?></td>
                                        <?php foreach(['Lunes','Martes','Miércoles','Jueves','Viernes'] as $dia): ?>
                                        <td>
                                            <select name="horario[<?= $dia ?>][<?= $inicio ?>][materia]" class="form-control mb-2 materia-select">
                                                <option value="">Materia</option>
                                                <?php foreach($materias as $m): ?>
                                                <option value="<?= $m['id_materia'] ?>"><?= htmlspecialchars($m['nombre_materia']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <select name="horario[<?= $dia ?>][<?= $inicio ?>][profesor]" class="form-control profesor-select">
                                                <option value="">Profesor</option>
                                                <?php foreach($profesores as $p): ?>
                                                <option value="<?= $p['id_profesor'] ?>">
                                                    <?= htmlspecialchars($p['nombres'].' '.$p['apellidos']) ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <input type="hidden" name="horario[<?= $dia ?>][<?= $inicio ?>][hora_inicio]" value="<?= $inicio ?>">
                                            <input type="hidden" name="horario[<?= $dia ?>][<?= $inicio ?>][hora_fin]" value="<?= $fin ?>">
                                        </td>
                                        <?php endforeach; ?>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="button" id="previsualizar" class="btn btn-success">
                            <i class="fas fa-eye"></i> Previsualizar
                        </button>
                        <button type="button" id="rellenarPrueba" class="btn btn-info">
                            <i class="fas fa-magic"></i> Datos de Prueba
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Previsualización -->
<div class="modal fade" id="previewModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Previsualización del Horario</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <iframe id="previewFrame" style="width:100%;height:500px;border:none;"></iframe>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript -->
<script>
$(function() {
    // Rellenar datos de prueba
    $('#rellenarPrueba').click(function() {
        // Seleccionar primer grado y sección
        $('#grado').val($('#grado option:eq(1)').val());
        $('#seccion').val($('#seccion option:eq(1)').val());
        
        // Establecer fechas
        const hoy = new Date().toISOString().split('T')[0];
        const mesSiguiente = new Date();
        mesSiguiente.setMonth(mesSiguiente.getMonth() + 1);
        const fin = mesSiguiente.toISOString().split('T')[0];
        
        $('[name="aula"]').val('Aula 101');
        $('[name="fecha_inicio"]').val(hoy);
        $('[name="fecha_fin"]').val(fin);
        
        // Rellenar algunos bloques
        const materias = $('.materia-select option:not(:first)');
        const profesores = $('.profesor-select option:not(:first)');
        
        // Llenar primeros bloques de cada día
        ['Lunes','Martes','Miércoles','Jueves','Viernes'].forEach((dia, i) => {
            const bloque = $(`select[name^="horario[${dia}]"]`).first();
            if(bloque.length) {
                bloque.val(materias.eq(i).val());
                bloque.next('.profesor-select').val(profesores.eq(i).val());
            }
        });
        
        toastr.success('Datos de prueba cargados');
    });
    
    // Previsualización
    $('#previsualizar').click(function() {
        if($('#grado').val() === '' || $('#seccion').val() === '') {
            toastr.error('Seleccione un grado y sección primero');
            return;
        }
        
        $('#previewModal').modal('show');
        $('#previewFrame').attr('src', 'previsualizar_horario.php?' + $('form').serialize());
    });
    
    // Descargar PDF
    $('#descargarPdf').click(function() {
        window.open('generar_horario_pdf.php?' + $('form').serialize(), '_blank');
    });
});
</script>

<?php
// Incluir pie de página
include('../../admin/layout/parte2.php');
include('../../layout/mensajes.php');
?>