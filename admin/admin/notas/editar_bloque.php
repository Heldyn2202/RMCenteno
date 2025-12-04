<?php
include('../../app/config.php');
include('../../admin/layout/parte1.php');

$grado   = isset($_GET['grado']) ? (int)$_GET['grado'] : 0;
$seccion = isset($_GET['seccion']) ? (int)$_GET['seccion'] : 0;
$id      = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Detectar PK
$cols = $pdo->query("SHOW COLUMNS FROM horario_detalle")->fetchAll(PDO::FETCH_COLUMN);
$pk = null;
foreach (['id_detalle','id_horario_detalle','id'] as $c) { if (in_array($c, $cols, true)) { $pk = $c; break; } }
if (!$pk) { die('No se pudo detectar la clave primaria.'); }

// Cargar bloque con información del horario para obtener sección
$stmt = $pdo->prepare("SELECT hd.*, h.id_seccion, h.id_grado 
                       FROM horario_detalle hd 
                       JOIN horarios h ON h.id_horario = hd.id_horario 
                       WHERE hd.$pk = ?");
$stmt->execute([$id]);
$bloque = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$bloque) {
    echo '<div class="content-wrapper"><div class="content"><div class="container"><div class="alert alert-danger">Bloque no encontrado.</div></div></div></div>';
    include('../../admin/layout/parte2.php');
    exit;
}

// Obtener sección y grado del bloque (usar los del bloque si están disponibles, sino los de GET)
$id_seccion_bloque = $bloque['id_seccion'] ?? $seccion;
$id_grado_bloque = $bloque['id_grado'] ?? $grado;

// Listas básicas
// Filtrar profesores: solo los que tienen asignaciones en esta sección
$profes = [];
if ($id_seccion_bloque > 0) {
    $stmtProf = $pdo->prepare("SELECT DISTINCT p.id_profesor, CONCAT(p.nombres,' ',p.apellidos) AS nombre
                                FROM profesores p
                                INNER JOIN asignaciones_profesor ap ON p.id_profesor = ap.id_profesor
                                WHERE ap.id_seccion = ? AND ap.estado = 1 AND p.estado = 1
                                ORDER BY p.apellidos, p.nombres");
    $stmtProf->execute([$id_seccion_bloque]);
    $profes = $stmtProf->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Fallback: todos los profesores activos si no hay sección
    $profes = $pdo->query("SELECT id_profesor, CONCAT(nombres,' ',apellidos) AS nombre FROM profesores WHERE estado = 1 ORDER BY apellidos, nombres")->fetchAll(PDO::FETCH_ASSOC);
}

// Filtrar materias: solo las asignadas al profesor seleccionado en esta sección
$materias = [];
$id_profesor_actual = $bloque['id_profesor'] ?? null;
if ($id_profesor_actual && $id_profesor_actual > 0 && $id_seccion_bloque > 0) {
    // Obtener solo las materias que el profesor tiene asignadas en esta sección
    $stmtMat = $pdo->prepare("SELECT DISTINCT m.id_materia, m.nombre_materia
                               FROM materias m
                               INNER JOIN asignaciones_profesor ap ON m.id_materia = ap.id_materia
                               WHERE ap.id_profesor = ? AND ap.id_seccion = ? AND ap.estado = 1 AND m.estado = 1
                               ORDER BY m.nombre_materia");
    $stmtMat->execute([$id_profesor_actual, $id_seccion_bloque]);
    $materias = $stmtMat->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Si no hay profesor seleccionado, mostrar todas las materias (para permitir selección inicial)
    $materias = $pdo->query("SELECT id_materia, nombre_materia FROM materias WHERE estado = 1 ORDER BY nombre_materia")->fetchAll(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_materia  = (int)($_POST['id_materia'] ?? 0);
    // Si el usuario deja "Sin profesor" (valor ""), actualizar a NULL para respetar la FK
    $id_profesor = (isset($_POST['id_profesor']) && $_POST['id_profesor'] !== '') ? (int)$_POST['id_profesor'] : null;
    try {
        $up = $pdo->prepare("UPDATE horario_detalle SET id_materia = :mat, id_profesor = :prof WHERE $pk = :id");
        $up->bindValue(':mat', $id_materia, PDO::PARAM_INT);
        if ($id_profesor === null) {
            $up->bindValue(':prof', null, PDO::PARAM_NULL);
        } else {
            $up->bindValue(':prof', $id_profesor, PDO::PARAM_INT);
        }
        $up->bindValue(':id', $id, PDO::PARAM_INT);
        $up->execute();
        $_SESSION['mensaje'] = 'Bloque actualizado correctamente.';
        $_SESSION['icono']   = 'success';
        // Redirigir manteniendo el parámetro de edición si existe
        $editando = isset($_GET['editando']) ? '&editando=1&id_bloque=' . $id : '';
        header('Location: horarios_consolidados.php?grado=' . $grado . '&seccion=' . $seccion . $editando);
        exit;
    } catch (Throwable $e) {
        echo '<div class="content-wrapper"><div class="content"><div class="container"><div class="alert alert-danger">Error al actualizar: ' . htmlspecialchars($e->getMessage()) . '</div></div></div></div>';
    }
}
?>

<div class="content-wrapper">
  <div class="content">
    <div class="container">
      <div class="row mb-3"><div class="col-sm-12"><h3>Editar bloque de horario</h3></div></div>
      <div class="card card-body">
        <form method="post">
          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Materia</label>
              <select name="id_materia" id="select_materia" class="form-control" required>
                <?php if(empty($materias)): ?>
                  <option value="">-- Seleccione primero un Profesor --</option>
                <?php else: ?>
                  <?php foreach($materias as $m): ?>
                    <option value="<?=$m['id_materia']?>" <?= $bloque['id_materia']==$m['id_materia']?'selected':'' ?>><?=htmlspecialchars($m['nombre_materia'])?></option>
                  <?php endforeach; ?>
                <?php endif; ?>
              </select>
            </div>
            <div class="form-group col-md-6">
              <label>Profesor</label>
              <select name="id_profesor" id="select_profesor" class="form-control" data-seccion="<?=$id_seccion_bloque?>">
                <option value="">Sin profesor</option>
                <?php foreach($profes as $p): ?>
                  <option value="<?=$p['id_profesor']?>" <?= (int)$bloque['id_profesor']===(int)$p['id_profesor']?'selected':'' ?>><?=htmlspecialchars($p['nombre'])?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="d-flex justify-content-between">
            <div>
              <?php $pkMostrar = htmlspecialchars($pk); ?>
              <small class="text-muted">ID bloque: <?=$id?> · PK: <?=$pkMostrar?></small>
            </div>
            <div>
              <a class="btn btn-outline-danger mr-2" href="eliminar_bloque.php?id=<?=$id?>&grado=<?=$grado?>&seccion=<?=$seccion?>" onclick="return confirm('¿Eliminar este bloque?');">Eliminar</a>
              <a class="btn btn-secondary" href="horarios_consolidados.php?grado=<?=$grado?>&seccion=<?=$seccion?>">Cancelar</a>
              <button class="btn btn-primary" type="submit">Guardar cambios</button>
            </div>
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

<script>
$(document).ready(function() {
    const $selectProfesor = $('#select_profesor');
    const $selectMateria = $('#select_materia');
    const idSeccion = $selectProfesor.data('seccion');
    const materiaActual = $selectMateria.val();
    
    // Cuando cambia el profesor, cargar sus materias asignadas
    $selectProfesor.on('change', function() {
        const idProfesor = $(this).val();
        
        if (!idProfesor || idProfesor === '') {
            // Si no hay profesor, limpiar materias
            $selectMateria.html('<option value="">-- Seleccione primero un Profesor --</option>').prop('disabled', true);
            return;
        }
        
        if (!idSeccion || idSeccion <= 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Advertencia',
                text: 'No se pudo determinar la sección. Las materias no se filtrarán correctamente.'
            });
            return;
        }
        
        // Mostrar indicador de carga
        $selectMateria.html('<option value="">Cargando materias...</option>').prop('disabled', true);
        
        // Obtener materias del profesor en esta sección
        $.ajax({
            url: 'ajax/obtener_materias.php',
            method: 'GET',
            data: {
                id_profesor: idProfesor,
                id_seccion: idSeccion
            },
            dataType: 'json',
            success: function(response) {
                if (response.success && response.data && response.data.length > 0) {
                    let options = '<option value="">Seleccionar materia</option>';
                    response.data.forEach(function(materia) {
                        const selected = materia.id == materiaActual ? 'selected' : '';
                        options += `<option value="${materia.id}" ${selected}>${materia.nombre}</option>`;
                    });
                    $selectMateria.html(options).prop('disabled', false);
                    
                    // Si la materia actual está en la lista, mantenerla seleccionada
                    if (materiaActual && response.data.some(m => m.id == materiaActual)) {
                        $selectMateria.val(materiaActual);
                    }
                } else {
                    $selectMateria.html('<option value="">-- Este profesor no tiene materias asignadas en esta sección --</option>').prop('disabled', true);
                    Swal.fire({
                        icon: 'warning',
                        title: 'Sin materias',
                        text: 'Este profesor no tiene materias asignadas en esta sección. Puede agregar asignaciones desde el botón "+".'
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Error al cargar materias:', error);
                $selectMateria.html('<option value="">-- Error al cargar materias --</option>').prop('disabled', true);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudieron cargar las materias del profesor.'
                });
            }
        });
    });
    
    // Si hay un profesor seleccionado al cargar, cargar sus materias
    if ($selectProfesor.val() && $selectProfesor.val() !== '') {
        $selectProfesor.trigger('change');
    }
});
</script>

