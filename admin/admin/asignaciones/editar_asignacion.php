<?php
session_start();
include('../../app/config.php');
include('../../admin/layout/parte1.php');

// ===============================
// VALIDAR ID
// ===============================
$id_asignacion = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id_asignacion <= 0) {
    echo "<script>
        Swal.fire({
          icon: 'error',
          title: 'ID inválido',
          text: 'No se pudo identificar la asignación.',
        }).then(() => window.location.href='listar_asignaciones.php');
    </script>";
    exit;
}

// ===============================
// CONSULTAR ASIGNACIÓN ACTUAL
// ===============================
$sql = "
SELECT 
    ap.id_asignacion,
    ap.id_profesor,
    ap.id_materia,
    ap.id_seccion,
    ap.id_gestion
FROM asignaciones_profesor ap
WHERE ap.id_asignacion = ?
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id_asignacion]);
$asignacion = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$asignacion) {
    echo "<script>
        Swal.fire({
          icon: 'error',
          title: 'No encontrada',
          text: 'La asignación no existe o fue eliminada.',
        }).then(() => window.location.href='listar_asignaciones.php');
    </script>";
    exit;
}

// ===============================
// GESTIÓN ACTIVA (mostrar y usar automáticamente)
// ===============================
$sql_g = "SELECT id_gestion, CONCAT('Periodo ', YEAR(desde), ' - ', YEAR(hasta)) AS nombre FROM gestiones WHERE estado = 1 LIMIT 1";
$stmt_g = $pdo->prepare($sql_g);
$stmt_g->execute();
$gestion_activa = $stmt_g->fetch(PDO::FETCH_ASSOC);
$gestion_activa_id = $gestion_activa['id_gestion'] ?? $asignacion['id_gestion'];
$gestion_activa_nombre = $gestion_activa['nombre'] ?? ('Periodo ID ' . ($asignacion['id_gestion'] ?? 'N/A'));

// ===============================
// CARGAR LISTAS
// ===============================
$profesores = $pdo->query("SELECT id_profesor, CONCAT(nombres,' ',apellidos) AS nombre FROM profesores WHERE estado = 1 ORDER BY nombres")->fetchAll(PDO::FETCH_ASSOC);
$materias   = $pdo->query("SELECT id_materia, nombre_materia FROM materias WHERE estado = 1 ORDER BY nombre_materia")->fetchAll(PDO::FETCH_ASSOC);
$secciones  = $pdo->query("SELECT id_seccion, CONCAT(g.grado, ' - ', s.nombre_seccion) AS nombre FROM secciones s JOIN grados g ON s.id_grado = g.id_grado WHERE s.estado = 1 ORDER BY g.grado, s.nombre_seccion")->fetchAll(PDO::FETCH_ASSOC);

// ===============================
// CONSULTAR ASIGNACIONES EXISTENTES EN LA GESTIÓN ACTIVA (EXCEPTO LA ACTUAL) PARA VALIDACIÓN CLIENTE
// ===============================
$asig_conf = [];
if (!empty($gestion_activa_id)) {
    $sql_conf = "
        SELECT ap.id_asignacion, ap.id_profesor, ap.id_materia, ap.id_seccion, CONCAT(p.nombres,' ',p.apellidos) AS profesor
        FROM asignaciones_profesor ap
        JOIN profesores p ON p.id_profesor = ap.id_profesor
        WHERE ap.id_gestion = :id_gestion
          AND ap.estado = 1
          AND ap.id_asignacion != :id_asignacion
    ";
    $stmt_conf = $pdo->prepare($sql_conf);
    $stmt_conf->execute([
        ':id_gestion' => $gestion_activa_id,
        ':id_asignacion' => $id_asignacion
    ]);
    $rows = $stmt_conf->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $r) {
        $key = $r['id_seccion'] . '_' . $r['id_materia'];
        if (!isset($asig_conf[$key])) {
            $asig_conf[$key] = [
                'id_asignacion' => $r['id_asignacion'],
                'id_profesor' => $r['id_profesor'],
                'profesor' => $r['profesor']
            ];
        }
    }
}
?>

<div class="content-wrapper">
  <div class="content">
    <div class="container py-3">
      <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
          <h4 class="mb-0"><i class="fas fa-edit"></i> Editar Asignación</h4>
        </div>

        <div class="card-body">
          <form id="formEditarAsignacion" method="POST">
            <input type="hidden" name="id_asignacion" value="<?= $asignacion['id_asignacion'] ?>">
            <!-- Enviamos la gestión activa por hidden (no seleccionable) -->
            <input type="hidden" name="id_gestion" value="<?= htmlspecialchars($gestion_activa_id) ?>">

            <div class="row mb-3">
              <div class="col-md-3">
                <label><strong>Profesor</strong></label>
                <select name="id_profesor" id="selectProfesor" class="form-control" required>
                  <option value="">Seleccione</option>
                  <?php foreach ($profesores as $p): ?>
                    <option value="<?= $p['id_profesor'] ?>" <?= $p['id_profesor'] == $asignacion['id_profesor'] ? 'selected' : '' ?>>
                      <?= htmlspecialchars($p['nombre']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="col-md-3">
                <label><strong>Materia</strong></label>
                <select name="id_materia" id="selectMateria" class="form-control" required>
                  <option value="">Seleccione</option>
                  <?php foreach ($materias as $m): ?>
                    <option value="<?= $m['id_materia'] ?>" <?= $m['id_materia'] == $asignacion['id_materia'] ? 'selected' : '' ?>>
                      <?= htmlspecialchars($m['nombre_materia']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="col-md-3">
                <label><strong>Sección</strong></label>
                <select name="id_seccion" id="selectSeccion" class="form-control" required>
                  <option value="">Seleccione</option>
                  <?php foreach ($secciones as $s): ?>
                    <option value="<?= $s['id_seccion'] ?>" <?= $s['id_seccion'] == $asignacion['id_seccion'] ? 'selected' : '' ?>>
                      <?= htmlspecialchars($s['nombre']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
                <small id="infoSecciones" class="text-muted"></small>
              </div>

              <div class="col-md-3">
                <label><strong>Gestión (activa)</strong></label>
                <input type="text" class="form-control" readonly 
                       value="<?= htmlspecialchars($gestion_activa_nombre) ?>">
                <small class="text-muted">La gestión no es editable desde aquí; se usará la gestión activa.</small>
              </div>
            </div>

            <div class="d-flex justify-content-between mt-4">
              <button id="btnGuardar" type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar Cambios</button>
              <a href="listar_asignaciones.php" class="btn btn-secondary">Cancelar</a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  // cliente: asignaciones existentes para validación rápida
  const existingAssignments = <?= json_encode($asig_conf, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
  const gestionActivaNombre = <?= json_encode($gestion_activa_nombre) ?>;
  const gestionActivaId = <?= json_encode($gestion_activa_id) ?>;

  // Construcción robusta de la URL al endpoint según tu estructura de carpetas:
  // Intentamos APP_URL si está definida; si no, usamos window.location.origin con la ruta que usaste.
  const urlGetSeccionesProfesor = (function(){
    try {
      // si APP_URL está expuesta en JS por tu config (no siempre ocurre)
      if (typeof APP_URL !== 'undefined' && APP_URL) {
        return APP_URL.replace(/\/$/, '') + '/admin/admin/notas/ajax/get_secciones_profesor.php';
      }
    } catch(e){}
    // fallback: usa origin + ruta conocida (ajusta si tu proyecto no está en root)
    return window.location.origin + '/heldyn/centeno/admin/admin/notas/ajax/get_secciones_profesor.php';
  })();
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(function(){

  // Al cargar la página: si hay profesor seleccionado, solicitar sus secciones
  const profesorInicial = $('#selectProfesor').val();
  if (profesorInicial) {
    cargarSeccionesProfesor(profesorInicial, <?= intval($asignacion['id_seccion']) ?>);
  }

  // Cuando se cambia el profesor: cargar sólo las secciones que da ese profesor en la gestión activa
  $('#selectProfesor').on('change', function(){
    const idProfesor = $(this).val();
    $('#infoSecciones').text('');
    if (!idProfesor) {
      // si quieres restaurar todas las secciones al quitar profesor, descomenta:
      // location.reload();
      return;
    }
    cargarSeccionesProfesor(idProfesor, null);
  });

  function cargarSeccionesProfesor(idProfesor, selectedSeccion = null) {
  $('#infoSecciones').text('Cargando secciones del profesor…');

  // guarda opciones originales por si queremos restaurarlas
  const $sel = $('#selectSeccion');
  const originalOptions = $sel.data('original-options') || ($sel.data('original-options', $sel.html()) && $sel.data('original-options'));

  $.ajax({
    url: urlGetSeccionesProfesor,
    method: 'GET',
    data: { id_profesor: idProfesor, id_gestion: gestionActivaId },
    dataType: 'json',
    timeout: 10000
  }).done(function(resp) {
    // resp debe ser un array
    if (!Array.isArray(resp)) {
      $('#infoSecciones').text('Respuesta inválida: no es un array JSON. Revisa consola.');
      console.error('get_secciones_profesor -> respuesta no es array:', resp);
      // restaurar opciones originales para no dejar el select vacío
      if (originalOptions) $sel.html(originalOptions);
      return;
    }

    $sel.empty().append($('<option>').val('').text('Seleccione'));
    if (resp.length === 0) {
      $('#infoSecciones').text('No hay secciones asignadas a este profesor en la gestión activa.');
      return;
    }

    resp.forEach(function(s) {
      $sel.append($('<option>').val(s.id_seccion).text(s.nombre));
    });

    if (selectedSeccion) {
      $sel.val(selectedSeccion);
    } else {
      $sel.val('');
    }

    $('#infoSecciones').text('Mostrando las secciones que el profesor imparte en la gestión activa.');
  }).fail(function(jqXHR, textStatus, errorThrown) {
    // Mensaje visible y log detallado en consola para depuración
    $('#infoSecciones').text('Error cargando secciones del profesor. Revisa consola (Network -> Response).');

    console.error('AJAX error get_secciones_profesor:', {
      url: urlGetSeccionesProfesor,
      status: jqXHR.status,
      statusText: jqXHR.statusText,
      textStatus: textStatus,
      errorThrown: errorThrown,
      responseText: jqXHR.responseText
    });

    // Si la respuesta contiene JSON parseable con error, intentar parsearlo y mostrar
    try {
      const json = JSON.parse(jqXHR.responseText);
      console.error('JSON parseado desde responseText:', json);
    } catch (e) {
      // no JSON
    }

    // fallback: restaurar opciones originales (si tenemos)
    if (originalOptions) $sel.html(originalOptions);
  });
}

  $('#formEditarAsignacion').on('submit', function(e){
    e.preventDefault();

    const idProfesorSel = $('select[name="id_profesor"]').val();
    const idMateriaSel = $('select[name="id_materia"]').val();
    const idSeccionSel = $('select[name="id_seccion"]').val();

    if (!idProfesorSel || !idMateriaSel || !idSeccionSel) {
      Swal.fire({ icon: 'warning', title: 'Faltan datos', text: 'Complete Profesor, Materia y Sección.' });
      return;
    }

    const key = idSeccionSel + '_' + idMateriaSel;
    const conflict = existingAssignments[key] || null;

    if (conflict && String(conflict.id_profesor) !== String(idProfesorSel)) {
      Swal.fire({
        icon: 'error',
        title: 'Asignación ya registrada',
        html: `La materia seleccionada ya está asignada al profesor <strong>${conflict.profesor}</strong> en esta sección para la gestión activa (${gestionActivaNombre}).<br><br>No se puede asignar la misma materia a dos profesores en la misma sección/gestión.`,
        confirmButtonText: 'Entendido'
      });
      return;
    }

    // confirmar y enviar al servidor (actualizar_asignacion.php hace la validación final)
    Swal.fire({
      title: '¿Guardar los cambios?',
      text: "Se actualizará la información de la asignación.",
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#2563eb',
      cancelButtonColor: '#6b7280',
      confirmButtonText: 'Sí, guardar',
      cancelButtonText: 'Cancelar'
    }).then((result) => {
      if (!result.isConfirmed) return;
      $('#btnGuardar').attr('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');
      Swal.fire({ title: 'Guardando...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

      $.ajax({
        url: 'actualizar_asignacion.php',
        method: 'POST',
        data: $(this).serialize(),
        dataType: 'json'
      }).done(function(resp){
        Swal.close();
        const icon = resp.tipo || 'info';
        Swal.fire({ icon: icon, title: resp.titulo || '', html: resp.mensaje || '', confirmButtonColor: '#2563eb' })
          .then(() => {
            if (resp.status === 'ok' && (resp.tipo === 'success' || resp.tipo === 'warning')) {
              window.location.href = 'listar_asignaciones.php';
            } else {
              $('#btnGuardar').attr('disabled', false).html('<i class="fas fa-save"></i> Guardar Cambios');
            }
        });
      }).fail(function(xhr, status, err){
        Swal.close();
        console.error('Error actualizar_asignacion:', xhr.responseText, status, err);
        Swal.fire({ icon: 'error', title: 'Error servidor', text: 'Ocurrió un error al comunicarse con el servidor.' });
        $('#btnGuardar').attr('disabled', false).html('<i class="fas fa-save"></i> Guardar Cambios');
      });
    });
  });
});
</script>

<?php include('../../admin/layout/parte2.php'); ?>