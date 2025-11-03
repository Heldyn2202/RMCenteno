<?php
include('../../app/config.php');
include('../../admin/layout/parte1.php');

// ==========================
// CONSULTA DE PROFESORES
// ==========================
$sql_profesores = "SELECT * FROM profesores ORDER BY apellidos, nombres";
$query_profesores = $pdo->prepare($sql_profesores);
$query_profesores->execute();
$profesores = $query_profesores->fetchAll(PDO::FETCH_ASSOC);
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="content-wrapper">
  <!-- ====================================== -->
  <!-- ENCABEZADO -->
  <!-- ====================================== -->
  <div class="content-header">
    <div class="container-fluid">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <h3 class="m-0" style="color:#1e293b;">
          <i class="fas fa-chalkboard-teacher"></i> Listado de Profesores
        </h3>
        <ol class="breadcrumb float-sm-right" style="margin-bottom: 0;">
          <li class="breadcrumb-item"><a href="<?= APP_URL; ?>/admin" style="color:#2563eb;">Inicio</a></li>
          <li class="breadcrumb-item active" style="color:#555;">Profesores</li>
        </ol>
      </div>
    </div>
  </div>

  <!-- ====================================== -->
  <!-- CONTENIDO PRINCIPAL -->
  <!-- ====================================== -->
  <div class="content">
    <div class="container-fluid">
      <div class="card shadow-sm" style="border-top: 4px solid #2563eb;">
        <div class="card-header" style="background-color:#f8fafc;">
          <h3 class="card-title text-dark m-0">Profesores Registrados</h3>
        </div>

        <div class="card-body">
          <table id="tablaProfesores" class="table table-bordered table-hover table-striped">
            <thead style="background-color:#e0e7ff; color:#111827; text-align:center;">
              <tr>
                <th>Cédula</th>
                <th>Nombres</th>
                <th>Apellidos</th>
                <th>Especialidad</th>
                <th>Teléfono</th>
                <th>Usuario</th>
                <th>Estado</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($profesores as $profesor): ?>
              <tr>
                <td><?= htmlspecialchars($profesor['cedula']); ?></td>
                <td><?= htmlspecialchars($profesor['nombres']); ?></td>
                <td><?= htmlspecialchars($profesor['apellidos']); ?></td>
                <td><?= htmlspecialchars($profesor['especialidad']); ?></td>
                <td><?= htmlspecialchars($profesor['telefono']); ?></td>
                <td><?= htmlspecialchars($profesor['usuario']); ?></td>
                <td class="text-center">
                  <?php if ($profesor['estado'] == 1): ?>
                    <button class="btn btn-success btn-sm" style="border-radius:20px;">ACTIVO</button>
                  <?php else: ?>
                    <button class="btn btn-danger btn-sm" style="border-radius:20px;">INACTIVO</button>
                  <?php endif; ?>
                </td>
                <td class="text-center">
                  <a href="ver_profesor.php?id=<?= $profesor['id_profesor']; ?>" 
                     class="btn btn-sm btn-outline-info" 
                     title="Ver Detalles" style="border: none;">
                    <i class="fas fa-eye"></i>
                  </a>
                  <a href="editar_profesor.php?id=<?= $profesor['id_profesor']; ?>" 
                     class="btn btn-sm btn-outline-warning" 
                     title="Editar" style="border: none;">
                    <i class="fas fa-edit"></i>
                  </a>
                  <a href="../asignaciones/asignar_profesor.php?id_profesor=<?= $profesor['id_profesor']; ?>" 
                     class="btn btn-sm btn-outline-primary" 
                     title="Asignaciones" style="border: none;">
                    <i class="fas fa-tasks"></i>
                  </a>

                  <?php if ($profesor['estado'] == 1): ?>
                    <a href="cambiar_estado.php?id=<?= $profesor['id_profesor']; ?>&estado=0" 
                       class="btn btn-sm btn-outline-secondary" 
                       title="Inhabilitar" 
                       style="border: none;"
                       onclick="return confirm('¿Está seguro de inhabilitar este profesor?')">
                      <i class="fas fa-toggle-off"></i>
                    </a>
                  <?php else: ?>
                    <a href="cambiar_estado.php?id=<?= $profesor['id_profesor']; ?>&estado=1" 
                       class="btn btn-sm btn-outline-success" 
                       title="Habilitar" 
                       style="border: none;"
                       onclick="return confirm('¿Está seguro de habilitar este profesor?')">
                      <i class="fas fa-toggle-on"></i>
                    </a>
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

<script>
$(document).ready(function() {
  $('#tablaProfesores').DataTable({
    responsive: true,
    autoWidth: false,
    language: {
      "decimal": "",
      "emptyTable": "No hay profesores registrados",
      "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
      "infoEmpty": "Mostrando 0 a 0 de 0 registros",
      "infoFiltered": "(filtrado de _MAX_ registros totales)",
      "lengthMenu": "Mostrar _MENU_ registros",
      "loadingRecords": "Cargando...",
      "processing": "Procesando...",
      "search": "Buscar:",
      "zeroRecords": "No se encontraron resultados",
      "paginate": {
        "first": "Primero",
        "last": "Último",
        "next": "Siguiente",
        "previous": "Anterior"
      }
    },
    order: [[2, 'asc']]
  });

  // Efecto hover en filas
  $('#tablaProfesores tbody tr').hover(
    function() { $(this).css('background-color', '#f1f5f9'); },
    function() { $(this).css('background-color', ''); }
  );
});
</script>
