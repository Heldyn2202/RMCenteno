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
                <td class="text-center">
                  <?php if ($profesor['estado'] == 1): ?>
                    <span class="badge bg-success">ACTIVO</span>
                  <?php else: ?>
                    <span class="badge bg-danger">INACTIVO</span>
                  <?php endif; ?>
                </td>
                <td class="text-center">
                  <a href="ver_profesor.php?id=<?= $profesor['id_profesor']; ?>" 
                     class="btn btn-sm btn-outline-info" 
                     title="Ver Detalles" style="border: none;">
                    <i class="fas fa-eye"></i>
                  </a>
                  <a href="../asignaciones/asignar_profesor.php?id_profesor=<?= $profesor['id_profesor']; ?>" 
                     class="btn btn-sm btn-outline-primary" 
                     title="Asignaciones" style="border: none;">
                    <i class="fas fa-tasks"></i>
                  </a>

                  <?php if ($profesor['estado'] == 1): ?>
                    <a href="#" 
                       class="btn btn-sm btn-outline-secondary btn-inhabilitar" 
                       title="Inhabilitar" 
                       style="border: none;"
                       data-id="<?= $profesor['id_profesor']; ?>"
                       data-nombre="<?= htmlspecialchars($profesor['nombres'] . ' ' . $profesor['apellidos']); ?>"
                       data-estado="0">
                      <i class="fas fa-toggle-off"></i>
                    </a>
                  <?php else: ?>
                    <a href="#" 
                       class="btn btn-sm btn-outline-success btn-habilitar" 
                       title="Habilitar" 
                       style="border: none;"
                       data-id="<?= $profesor['id_profesor']; ?>"
                       data-nombre="<?= htmlspecialchars($profesor['nombres'] . ' ' . $profesor['apellidos']); ?>"
                       data-estado="1">
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
    order: [[2, 'asc']] // Ordenar por apellidos (columna 2)
  });

  // Efecto hover en filas
  $('#tablaProfesores tbody tr').hover(
    function() { $(this).css('background-color', '#f1f5f9'); },
    function() { $(this).css('background-color', ''); }
  );

  // Función para mostrar SweetAlert de confirmación
  function mostrarConfirmacion(id, nombre, estado) {
    const esInhabilitar = estado == 0;
    const titulo = esInhabilitar ? '¿Inhabilitar profesor?' : '¿Habilitar profesor?';
    const texto = esInhabilitar 
      ? `¿Está seguro de inhabilitar al profesor ${nombre}?` 
      : `¿Está seguro de habilitar al profesor ${nombre}?`;
    const icono = esInhabilitar ? 'warning' : 'success';
    const textoConfirmar = esInhabilitar ? 'Sí, inhabilitar' : 'Sí, habilitar';
    const colorConfirmar = esInhabilitar ? '#d33' : '#3085d6';
    
    Swal.fire({
      title: titulo,
      text: texto,
      icon: icono,
      showCancelButton: true,
      confirmButtonColor: colorConfirmar,
      cancelButtonColor: '#6c757d',
      confirmButtonText: textoConfirmar,
      cancelButtonText: 'Cancelar',
      reverseButtons: true,
      showLoaderOnConfirm: true,
      preConfirm: () => {
        return new Promise((resolve) => {
          // Redirigir directamente (sin AJAX)
          window.location.href = `cambiar_estado.php?id=${id}&estado=${estado}`;
          resolve(true);
        });
      },
      allowOutsideClick: () => !Swal.isLoading()
    });
  }

  // Evento para botón de inhabilitar
  $(document).on('click', '.btn-inhabilitar', function(e) {
    e.preventDefault();
    const id = $(this).data('id');
    const nombre = $(this).data('nombre');
    const estado = $(this).data('estado');
    
    mostrarConfirmacion(id, nombre, estado);
  });

  // Evento para botón de habilitar
  $(document).on('click', '.btn-habilitar', function(e) {
    e.preventDefault();
    const id = $(this).data('id');
    const nombre = $(this).data('nombre');
    const estado = $(this).data('estado');
    
    mostrarConfirmacion(id, nombre, estado);
  });

  // Función para decodificar entidades HTML
  function decodeHtmlEntities(text) {
    const textArea = document.createElement('textarea');
    textArea.innerHTML = text;
    return textArea.value;
  }

  // Mostrar mensaje de éxito/error si viene en la URL
  const urlParams = new URLSearchParams(window.location.search);
  const successMsg = urlParams.get('success');
  const errorMsg = urlParams.get('error');
  
  if (successMsg) {
    // Decodificar el mensaje (puede venir codificado)
    const decodedMsg = decodeURIComponent(successMsg);
    
    Swal.fire({
      icon: 'success',
      title: '¡Éxito!',
      html: decodedMsg,
      confirmButtonColor: '#3085d6',
      showConfirmButton: true,
      allowOutsideClick: false,
      customClass: {
        popup: 'custom-swal-popup'
      }
    }).then(() => {
      // Limpiar URL
      history.replaceState({}, document.title, window.location.pathname);
    });
  }
  
  if (errorMsg) {
    const decodedError = decodeURIComponent(errorMsg);
    
    Swal.fire({
      icon: 'error',
      title: 'Error',
      html: decodedError,
      confirmButtonColor: '#d33',
      showConfirmButton: true,
      allowOutsideClick: false,
      customClass: {
        popup: 'custom-swal-popup'
      }
    }).then(() => {
      // Limpiar URL
      history.replaceState({}, document.title, window.location.pathname);
    });
  }
});
</script>

<style>
/* Estilos para SweetAlert personalizados */
.custom-swal-popup {
  border-radius: 12px !important;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif !important;
}

.swal2-title {
  font-size: 1.4rem !important;
  font-weight: 600 !important;
  color: #2c3e50 !important;
}

.swal2-html-container {
  font-size: 1rem !important;
  line-height: 1.5 !important;
  text-align: left !important;
}

.swal2-confirm {
  border-radius: 6px !important;
  padding: 8px 25px !important;
  font-weight: 500 !important;
}
</style>