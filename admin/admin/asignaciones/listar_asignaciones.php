<?php
include('../../app/config.php');
include('../../admin/layout/parte1.php');

// ===============================
// FILTRO DE ESTADO
// ===============================
$filtro = isset($_GET['estado']) ? $_GET['estado'] : '1';
$where = ($filtro === 'todas') ? '' : 'WHERE ap.estado = ' . intval($filtro);

// ===============================
// CONSULTA PRINCIPAL
// ===============================
$sql = "
SELECT 
    ap.id_asignacion,
    CONCAT(p.nombres, ' ', p.apellidos) AS profesor,
    m.nombre_materia,
    CONCAT(gr.grado, ' - ', s.nombre_seccion) AS seccion,
    CONCAT('Periodo ', YEAR(g.desde), ' - ', YEAR(g.hasta)) AS gestion,
    ap.estado,
    DATE_FORMAT(ap.fecha_creacion, '%d/%m/%Y %H:%i') AS fecha
FROM asignaciones_profesor ap
INNER JOIN profesores p ON ap.id_profesor = p.id_profesor
INNER JOIN materias m ON ap.id_materia = m.id_materia
INNER JOIN secciones s ON ap.id_seccion = s.id_seccion
INNER JOIN grados gr ON s.id_grado = gr.id_grado
INNER JOIN gestiones g ON ap.id_gestion = g.id_gestion
$where
ORDER BY p.apellidos, gr.grado, s.nombre_seccion, m.nombre_materia
";
$query = $pdo->prepare($sql);
$query->execute();
$asignaciones = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="content-wrapper">

  <div class="content-header">
    <div class="container-fluid d-flex justify-content-between align-items-center">
      <h3 class="m-0" style="color:#1e293b; font-weight:600;">
        <i class="fas fa-list"></i> Listado de Asignaciones de Profesores
      </h3>
      <ol class="breadcrumb float-sm-right m-0" style="background:transparent;">
        <li class="breadcrumb-item"><a href="<?= APP_URL; ?>/admin" style="color:#2563eb;">Inicio</a></li>
        <li class="breadcrumb-item active" style="color:#475569;">Asignaciones</li>
      </ol>
    </div>
  </div>

  <div class="content">
    <div class="container-fluid">
      <div class="card shadow-sm" style="border-top: 4px solid #3b82f6;">
        <div class="card-header" style="background-color:#f8fafc;">
          <h3 class="card-title text-dark m-0" style="font-weight:600;">Asignaciones Registradas</h3>
        </div>

        <div class="card-body">

          <!-- FILTRO DE ESTADO -->
          <form method="get" class="mb-3">
            <label class="me-2 fw-semibold">Mostrar:</label>
            <select name="estado" onchange="this.form.submit()" class="form-select w-auto d-inline">
              <option value="1" <?= $filtro === '1' ? 'selected' : '' ?>>Activas</option>
              <option value="0" <?= $filtro === '0' ? 'selected' : '' ?>>Inactivas</option>
              <option value="todas" <?= $filtro === 'todas' ? 'selected' : '' ?>>Todas</option>
            </select>
          </form>

          <!-- TABLA PRINCIPAL -->
          <table id="tablaAsignaciones" class="table table-bordered table-striped table-hover">
            <thead>
              <tr>
                <th>#</th>
                <th>Profesor</th>
                <th>Grado / Sección</th>
                <th>Materia</th>
                <th>Gestión</th>
                <th>Fecha Asignación</th>
                <th>Estado</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php $contador = 1; ?>
              <?php foreach ($asignaciones as $asig): ?>
                <tr>
                  <td class="text-center"><?= $contador++ ?></td>
                  <td><?= htmlspecialchars($asig['profesor']) ?></td>
                  <td><?= htmlspecialchars($asig['seccion']) ?></td>
                  <td><?= htmlspecialchars($asig['nombre_materia']) ?></td>
                  <td class="text-center"><?= htmlspecialchars($asig['gestion']) ?></td>
                  <td class="text-center"><?= htmlspecialchars($asig['fecha']) ?></td>
                  <td class="text-center">
                    <?php if ($asig['estado'] == 1): ?>
                      <button class="btn btn-success btn-sm btn-status" style="border-radius: 20px;">ACTIVO</button>
                    <?php else: ?>
                      <button class="btn btn-danger btn-sm btn-status" style="border-radius: 20px;">INACTIVO</button>
                    <?php endif; ?>
                  </td>
                  <td class="text-center">
                    <a href="editar_asignacion.php?id=<?= $asig['id_asignacion'] ?>" class="btn-icon" title="Editar">
                      <i class="fas fa-edit text-warning"></i>
                    </a>
                    <?php if ($asig['estado'] == 1): ?>
                      <button onclick="inhabilitar(<?= $asig['id_asignacion'] ?>)" class="btn-icon" title="Inhabilitar">
                        <i class="fas fa-ban text-danger"></i>
                      </button>
                    <?php else: ?>
                      <button onclick="reactivar(<?= $asig['id_asignacion'] ?>)" class="btn-icon" title="Reactivar">
                        <i class="fas fa-check text-success"></i>
                      </button>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>

        </div>
      </div>
    </div>
  </div>
</div>

<?php include('../../admin/layout/parte2.php'); ?>

<!-- ======== ESTILOS PERSONALIZADOS ======== -->
<style>
  thead th {
    background-color: #a5b4fc !important; /* azul más oscuro */
    color: #1e293b !important;
    text-align: center;
    font-weight: 600;
    border-bottom: 2px solid #818cf8;
  }

  tbody td {
    color: #1f2937;
    vertical-align: middle;
    font-weight: normal;
  }

  /* Botones transparentes con efecto hover */
  .btn-icon {
    border: none !important;
    background: transparent !important;
    color: #374151;
    transition: all 0.25s ease;
    padding: 5px 8px;
    border-radius: 6px;
  }

  .btn-icon:hover {
    background-color: rgba(59, 130, 246, 0.1);
    transform: scale(1.15);
  }

  .btn-status {
    border-radius: 20px;
    font-weight: 500;
    font-size: 13px;
    padding: 4px 14px;
  }

  table.dataTable tbody tr:hover {
    background-color: #f9fafb !important;
    transition: 0.2s ease-in-out;
  }

  .dataTables_wrapper .dataTables_filter input {
    border-radius: 8px;
    border: 1px solid #d1d5db;
    padding: 4px 8px;
  }
</style>

<!-- ======== DATATABLE EN ESPAÑOL ======== -->
<script>
$(document).ready(function() {
  $('#tablaAsignaciones').DataTable({
    responsive: true,
    language: {
      "decimal": "",
      "emptyTable": "No hay datos disponibles en la tabla",
      "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
      "infoEmpty": "Mostrando 0 a 0 de 0 registros",
      "infoFiltered": "(filtrado de _MAX_ registros totales)",
      "lengthMenu": "Mostrar _MENU_ registros",
      "loadingRecords": "Cargando...",
      "processing": "Procesando...",
      "search": "Buscar:",
      "zeroRecords": "No se encontraron coincidencias",
      "paginate": {
          "first": "Primero",
          "last": "Último",
          "next": "Siguiente",
          "previous": "Anterior"
      },
    },
    order: [[1, 'asc']]
  });
});

function inhabilitar(id){
  Swal.fire({
    icon:'warning',
    title:'¿Inhabilitar asignación?',
    text:'El profesor dejará de estar asignado a esta clase.',
    showCancelButton:true,
    confirmButtonText:'Sí, inhabilitar',
    confirmButtonColor:'#d33',
    cancelButtonText:'Cancelar'
  }).then((r)=>{
    if(r.isConfirmed) window.location.href='inhabilitar_asignacion.php?id='+id;
  });
}

function reactivar(id){
  Swal.fire({
    icon:'question',
    title:'¿Reactivar asignación?',
    text:'El profesor volverá a impartir esta clase.',
    showCancelButton:true,
    confirmButtonText:'Sí, reactivar',
    confirmButtonColor:'#3085d6',
    cancelButtonText:'Cancelar'
  }).then((r)=>{
    if(r.isConfirmed) window.location.href='reactivar_asignacion.php?id='+id;
  });
}
</script>
