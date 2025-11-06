<?php
include('../../app/config.php');
include('../../admin/layout/parte1.php');

// validar id_profesor
$id_profesor = isset($_GET['id_profesor']) ? intval($_GET['id_profesor']) : 0;
if (!$id_profesor) {
    echo "<script>alert('ID de profesor no válido'); window.location.href='listar_asignaciones.php';</script>";
    exit;
}

// Datos profesor
$stmt = $pdo->prepare("SELECT CONCAT(nombres,' ',apellidos) AS nombre_profesor FROM profesores WHERE id_profesor = ?");
$stmt->execute([$id_profesor]);
$prof = $stmt->fetch(PDO::FETCH_ASSOC);

// Listas
$secciones = $pdo->query("
    SELECT s.id_seccion, CONCAT(g.grado, ' - ', s.nombre_seccion) AS nombre_seccion, g.grado AS grado_text
    FROM secciones s
    INNER JOIN grados g ON s.id_grado = g.id_grado
    WHERE s.estado = 1
    ORDER BY g.grado, s.nombre_seccion
")->fetchAll(PDO::FETCH_ASSOC);

$materias = $pdo->query("SELECT id_materia, nombre_materia FROM materias WHERE estado = 1 ORDER BY nombre_materia")->fetchAll(PDO::FETCH_ASSOC);

$gestiones = $pdo->query("
    SELECT id_gestion, CONCAT('Periodo ', YEAR(desde), ' - ', YEAR(hasta)) AS periodo
    FROM gestiones
    WHERE estado = 1
    ORDER BY desde DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Asignaciones actuales
$sql_asig = "
SELECT CONCAT(gr.grado, ' - ', s.nombre_seccion) AS grado_seccion,
       m.nombre_materia,
       CONCAT('Periodo ', YEAR(g.desde), ' - ', YEAR(g.hasta)) AS periodo
FROM asignaciones_profesor ap
JOIN secciones s ON ap.id_seccion = s.id_seccion
JOIN grados gr ON s.id_grado = gr.id_grado
JOIN materias m ON ap.id_materia = m.id_materia
JOIN gestiones g ON ap.id_gestion = g.id_gestion
WHERE ap.id_profesor = ?
ORDER BY g.desde DESC, gr.grado, s.nombre_seccion, m.nombre_materia
";
$stmt = $pdo->prepare($sql_asig);
$stmt->execute([$id_profesor]);
$asignaciones_actuales = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Agrupar asignaciones por periodo para el acordeón
$asignaciones_por_periodo = [];
foreach ($asignaciones_actuales as $a) {
    $periodo = $a['periodo'];
    if (!isset($asignaciones_por_periodo[$periodo])) $asignaciones_por_periodo[$periodo] = [];
    $asignaciones_por_periodo[$periodo][] = $a;
}
?>

<!-- Select2 CSS/JS y SweetAlert2 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
/* Flecha en el botón de periodo: gira cuando tiene la clase .open */
.period-toggle .arrow-icon {
  float: right;
  transition: transform .2s ease;
}
.period-toggle.open .arrow-icon {
  transform: rotate(180deg);
}
</style>

<div class="content-wrapper">
  <div class="content">
    <div class="container py-3">
      <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
          <h4 class="card-title mb-0">Asignar Secciones y Materias a Profesor</h4>
        </div>
        <div class="card-body">

          <div class="mb-3">
            <label><strong>Profesor:</strong></label>
            <input class="form-control" value="<?= htmlspecialchars($prof['nombre_profesor']) ?>" readonly>
          </div>

          <!-- Asignaciones actuales (acordeón por período) -->
          <div class="mb-4 p-3 rounded" style="background:#f8f9fa;">
            <h5 style="color:#222; margin-bottom:12px;">Asignaciones actuales del profesor</h5>

            <?php if (count($asignaciones_actuales) > 0): ?>
              <div class="list-group">
                <?php $idx = 0; foreach ($asignaciones_por_periodo as $periodo => $items): $idx++; ?>
                  <div class="mb-2">
                    <button type="button" class="btn btn-outline-secondary w-100 text-start period-toggle" data-index="<?= $idx ?>">
                      <strong><?= htmlspecialchars($periodo) ?></strong>
                      <span class="badge bg-secondary ms-2"><?= count($items) ?></span>
                      <i class="fas fa-chevron-down arrow-icon"></i>
                    </button>

                    <div class="period-content mt-2 d-none" id="period-content-<?= $idx ?>">
                      <ul class="list-group">
                        <?php foreach ($items as $it): ?>
                          <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                              <strong><?= htmlspecialchars($it['grado_seccion']) ?></strong>
                              <span class="badge bg-primary ms-2"><?= htmlspecialchars($it['nombre_materia']) ?></span>
                            </div>
                          </li>
                        <?php endforeach; ?>
                      </ul>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php else: ?>
              <p class="text-muted mb-0">Este profesor no tiene asignaciones activas.</p>
            <?php endif; ?>

          </div>

          <!-- Formulario dinámico -->
          <form id="formAsignaciones">
            <input type="hidden" name="id_profesor" value="<?= $id_profesor ?>">

            <div id="contenedorAsignaciones">
              <!-- Fila inicial (usa la estructura "limpia" con data-mat-id en checkboxes) -->
              <div class="asignacion-item row align-items-start border rounded p-3 mb-3">
                <div class="col-md-3">
                  <label><strong>Sección</strong></label>
                  <select name="id_seccion[]" class="form-control selectSeccion" required>
                    <option value="">Seleccione una sección</option>
                    <?php
                    // Mostrar optgroups agrupadas por grado; OPTION TEXT incluye grado para que se vea al seleccionar
                    $seccionesAgrupadas = [];
                    foreach ($secciones as $s) {
                        $grado = $s['grado_text'];
                        $partes = explode(' - ', $s['nombre_seccion']);
                        $nombreSeccion = isset($partes[1]) ? trim($partes[1]) : trim($partes[0]);
                        $seccionesAgrupadas[$grado][] = [
                            'id_seccion' => $s['id_seccion'],
                            'nombre_seccion' => $nombreSeccion
                        ];
                    }
                    foreach ($seccionesAgrupadas as $grado => $lista) {
                        echo "<optgroup label='" . htmlspecialchars($grado) . "'>";
                        foreach ($lista as $sec) {
                            // Mostrar grado en el texto de la opción para que Select2 lo refleje al seleccionar
                            $labelOption = htmlspecialchars($grado . ' - Sección ' . $sec['nombre_seccion']);
                            echo "<option value='{$sec['id_seccion']}'>{$labelOption}</option>";
                        }
                        echo "</optgroup>";
                    }
                    ?>
                  </select>
                </div>

                <div class="col-md-5">
                  <label><strong>Materias</strong></label>
                  <div class="materias-list" style="max-height:220px; overflow:auto; border:1px solid #e3e3e3; padding:8px; border-radius:6px;">
                    <?php foreach ($materias as $m): ?>
                      <div class="form-check">
                        <!-- data-mat-id para reindexar luego -->
                        <input class="form-check-input" type="checkbox" data-mat-id="<?= $m['id_materia'] ?>">
                        <label class="form-check-label"><?= htmlspecialchars($m['nombre_materia']) ?></label>
                      </div>
                    <?php endforeach; ?>
                  </div>
                </div>

                <div class="col-md-3">
                  <label><strong>Gestión</strong></label>
                  <select name="id_gestion[]" class="form-control" required>
                    <option value="">Seleccione</option>
                    <?php foreach ($gestiones as $g): ?>
                      <option value="<?= $g['id_gestion'] ?>"><?= htmlspecialchars($g['periodo']) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div class="col-md-1 text-center">
                  <label>&nbsp;</label>
                  <button type="button" class="btn btn-danger btn-sm mt-1 eliminarFila"><i class="fas fa-trash"></i></button>
                </div>
              </div>
            </div>

            <div class="d-flex justify-content-between">
              <button type="button" id="agregarFila" class="btn btn-success"><i class="fas fa-plus"></i> Agregar otra asignación</button>
              <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar todas las asignaciones</button>
            </div>
          </form>

          <!-- Template oculto (limpio) para nuevas filas -->
          <template id="tplAsignacion">
            <div class="asignacion-item row align-items-start border rounded p-3 mb-3">
              <div class="col-md-3">
                <label><strong>Sección</strong></label>
                <select name="id_seccion[]" class="form-control selectSeccion" required>
                  <option value="">Seleccione una sección</option>
                  <?php
                  // Reutilizamos las optgroups para el template; la OPTION TEXT incluye grado
                  foreach ($seccionesAgrupadas as $grado => $lista) {
                      echo "<optgroup label='" . htmlspecialchars($grado) . "'>";
                      foreach ($lista as $sec) {
                          $labelOption = htmlspecialchars($grado . ' - Sección ' . $sec['nombre_seccion']);
                          echo "<option value='{$sec['id_seccion']}'>{$labelOption}</option>";
                      }
                      echo "</optgroup>";
                  }
                  ?>
                </select>
              </div>

              <div class="col-md-5">
                <label><strong>Materias</strong></label>
                <div class="materias-list" style="max-height:220px; overflow:auto; border:1px solid #e3e3e3; padding:8px; border-radius:6px;">
                  <?php foreach ($materias as $m): ?>
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" data-mat-id="<?= $m['id_materia'] ?>">
                      <label class="form-check-label"><?= htmlspecialchars($m['nombre_materia']) ?></label>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>

              <div class="col-md-3">
                <label><strong>Gestión</strong></label>
                <select name="id_gestion[]" class="form-control" required>
                  <option value="">Seleccione</option>
                  <?php foreach ($gestiones as $g): ?>
                    <option value="<?= $g['id_gestion'] ?>"><?= htmlspecialchars($g['periodo']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="col-md-1 text-center">
                <label>&nbsp;</label>
                <button type="button" class="btn btn-danger btn-sm mt-1 eliminarFila"><i class="fas fa-trash"></i></button>
              </div>
            </div>
          </template>

        </div>
      </div>
    </div>
  </div>
</div>

<script>
$(function(){

  // Inicializar Select2 en todas las selects .selectSeccion existentes
  function initSelect2(context) {
    context = context || $('.selectSeccion');
    context.select2({
      placeholder: "Buscar o seleccionar una sección",
      width: '100%'
    });
  }

  // Reindexar filas: asignar nombres e ids a checkboxes y labels
  function reindexFilas() {
    $('#contenedorAsignaciones .asignacion-item').each(function(index){
      // checkboxes de materias dentro de esta fila
      $(this).find('.materias-list .form-check-input').each(function(){
        var matId = $(this).data('mat-id');
        var newName = 'id_materia[' + index + '][]';
        var newId = 'mat_' + index + '_' + matId;
        $(this).attr('name', newName);
        $(this).attr('id', newId);
        $(this).closest('.form-check').find('label').attr('for', newId);
      });
    });
  }

  // Inicialización inicial
  initSelect2();
  reindexFilas();

  // Agregar nueva fila desde template
  $('#agregarFila').on('click', function(){
    var tpl = $('#tplAsignacion').html();
    $('#contenedorAsignaciones').append(tpl);
    var newRow = $('#contenedorAsignaciones .asignacion-item').last();

    // Inicializar Select2 solo en el nuevo select
    initSelect2(newRow.find('.selectSeccion'));

    // Reindexar nombres/ids para checkboxes
    reindexFilas();
    // opcional: hacer scroll hacia la nueva fila
    $('html, body').animate({ scrollTop: newRow.offset().top - 100 }, 300);
  });

  // Eliminar fila
  $(document).on('click', '.eliminarFila', function(){
    if ($('#contenedorAsignaciones .asignacion-item').length > 1) {
      $(this).closest('.asignacion-item').remove();
      reindexFilas();
    } else {
      Swal.fire({ icon: 'info', title: 'Al menos una fila', text: 'Debe existir al menos una asignación.' });
    }
  });

  // Toggle acordeón de asignaciones actuales por período (con rotación de flecha)
  document.querySelectorAll('.period-toggle').forEach(function(btn){
    btn.addEventListener('click', function(){
      var idx = this.getAttribute('data-index');
      var content = document.getElementById('period-content-' + idx);
      if (content) {
        content.classList.toggle('d-none');
        this.classList.toggle('open'); // para girar la flecha
      }
    });
  });

  // Submit: validar y enviar por AJAX
  $('#formAsignaciones').on('submit', function(e){
    e.preventDefault();
    reindexFilas();

    var filas = $('#contenedorAsignaciones .asignacion-item');
    var valido = true;
    var mensaje = '';
    var combinado = new Set();

    filas.each(function(i){
      var seccion = $(this).find('select[name="id_seccion[]"]').val();
      var gestion = $(this).find('select[name="id_gestion[]"]').val();
      var materias = $(this).find('input[type="checkbox"]:checked').map(function(){ return $(this).val(); }).get();

      // Si las checkboxes no tienen value (no se les puso), usamos data-mat-id como value
      // Aseguramos que cada checkbox tenga atributo value = data-mat-id (si no lo tiene)
      $(this).find('input[type="checkbox"]').each(function(){
        if (!$(this).attr('value')) {
          $(this).attr('value', $(this).data('mat-id'));
        }
      });

      materias = $(this).find('input[type="checkbox"]:checked').map(function(){ return $(this).val(); }).get();

      if (!seccion || !gestion || materias.length === 0) {
        valido = false;
        mensaje = 'Todas las filas deben tener Sección, Gestión y al menos una Materia seleccionada.';
        return false;
      }

      materias.forEach(function(m){
        var key = seccion + '-' + gestion + '-' + m;
        if (combinado.has(key)) {
          valido = false;
          mensaje = 'Hay duplicados dentro del formulario (misma sección/gestión/materia).';
          return false;
        }
        combinado.add(key);
      });
      if (!valido) return false;
    });

    if (!valido) {
      Swal.fire({ icon: 'warning', title: 'Validación', text: mensaje });
      return;
    }

    // Enviar datos por AJAX
    $.ajax({
      url: 'guardar_asignacion.php',
      method: 'POST',
      data: $(this).serialize(),
      dataType: 'json',
      success: function(resp){
        if (!resp) {
          Swal.fire({ icon: 'error', title: 'Error', text: 'Respuesta inválida del servidor' });
          return;
        }
        // resp.tipo controla icono; el servidor ahora puede devolver mensajes indicando que
        // la materia ya está asignada a otro profesor (el texto lo incluye).
        Swal.fire({
          icon: resp.tipo,
          title: resp.titulo,
          html: resp.mensaje,
          confirmButtonColor: '#6c63ff'
        }).then(() => {
          if (resp.tipo === 'success' || resp.tipo === 'warning') {
            window.location.href = 'listar_asignaciones.php';
          }
        });
      },
      error: function(){
        Swal.fire({ icon: 'error', title: 'Error AJAX', text: 'Error al comunicarse con el servidor.' });
      }
    });

  });

});
</script>

<?php include('../../admin/layout/parte2.php'); ?>