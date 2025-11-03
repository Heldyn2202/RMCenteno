<?php
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
// CARGAR LISTAS
// ===============================
$profesores = $pdo->query("SELECT id_profesor, CONCAT(nombres,' ',apellidos) AS nombre FROM profesores WHERE estado = 1 ORDER BY nombres")->fetchAll(PDO::FETCH_ASSOC);
$materias   = $pdo->query("SELECT id_materia, nombre_materia FROM materias WHERE estado = 1 ORDER BY nombre_materia")->fetchAll(PDO::FETCH_ASSOC);
$secciones  = $pdo->query("SELECT id_seccion, CONCAT(g.grado, ' - ', s.nombre_seccion) AS nombre FROM secciones s JOIN grados g ON s.id_grado = g.id_grado WHERE s.estado = 1 ORDER BY g.grado, s.nombre_seccion")->fetchAll(PDO::FETCH_ASSOC);
$gestiones  = $pdo->query("SELECT id_gestion, CONCAT('Periodo ', YEAR(desde), ' - ', YEAR(hasta)) AS nombre FROM gestiones WHERE estado = 1 ORDER BY desde DESC")->fetchAll(PDO::FETCH_ASSOC);
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

            <div class="row mb-3">
              <div class="col-md-3">
                <label><strong>Profesor</strong></label>
                <select name="id_profesor" class="form-control" required>
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
                <select name="id_materia" class="form-control" required>
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
                <select name="id_seccion" class="form-control" required>
                  <option value="">Seleccione</option>
                  <?php foreach ($secciones as $s): ?>
                    <option value="<?= $s['id_seccion'] ?>" <?= $s['id_seccion'] == $asignacion['id_seccion'] ? 'selected' : '' ?>>
                      <?= htmlspecialchars($s['nombre']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="col-md-3">
                <label><strong>Gestión</strong></label>
                <select name="id_gestion" class="form-control" required>
                  <option value="">Seleccione</option>
                  <?php foreach ($gestiones as $g): ?>
                    <option value="<?= $g['id_gestion'] ?>" <?= $g['id_gestion'] == $asignacion['id_gestion'] ? 'selected' : '' ?>>
                      <?= htmlspecialchars($g['nombre']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
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

<!-- SweetAlert2 y jQuery (jQuery normalmente está en tu layout; si no, inclúyelo) -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(function(){
  $('#formEditarAsignacion').on('submit', function(e){
    e.preventDefault();

    // Confirmación antes de guardar
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

      // deshabilitar botón
      $('#btnGuardar').attr('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

      // mostrar loading SweetAlert
      Swal.fire({
        title: 'Guardando...',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
      });

      // enviar via AJAX (jQuery)
      $.ajax({
        url: 'actualizar_asignacion.php',
        method: 'POST',
        data: $(this).serialize(),
        dataType: 'json'
      }).done(function(resp){
        // cerrar loading
        Swal.close();

        // mapear tipo a icono
        const icon = resp.tipo || 'info';

        // mostrar resultado
        Swal.fire({
          icon: icon,
          title: resp.titulo || '',
          html: resp.mensaje || '',
          confirmButtonColor: '#2563eb'
        }).then(() => {
          // si fue OK o warning (se guardó o parcialmente), vamos al listado
          if (resp.status === 'ok' && (resp.tipo === 'success' || resp.tipo === 'warning')) {
            window.location.href = 'listar_asignaciones.php';
          } else {
            // re-habilitar botón para intentar nuevamente
            $('#btnGuardar').attr('disabled', false).html('<i class="fas fa-save"></i> Guardar Cambios');
          }
        });
      }).fail(function(xhr, status, err){
        Swal.close();
        Swal.fire({
          icon: 'error',
          title: 'Error servidor',
          text: 'Ocurrió un error al comunicarse con el servidor.'
        });
        $('#btnGuardar').attr('disabled', false).html('<i class="fas fa-save"></i> Guardar Cambios');
      });
    });
  });
});
</script>

<?php include('../../admin/layout/parte2.php'); ?>
