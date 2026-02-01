<?php
session_start();
require_once('../../app/config.php');
include('../../admin/layout/parte1.php');
include('../../layout/mensajes.php');

// Obtener gestión activa
$sql_gestion = "SELECT id_gestion, CONCAT('Periodo ', DATE_FORMAT(desde, '%Y'), ' - ', DATE_FORMAT(hasta, '%Y')) AS nombre_gestion FROM gestiones WHERE estado = 1 LIMIT 1";
$stmt_g = $pdo->query($sql_gestion);
$gestion_info = $stmt_g->fetch(PDO::FETCH_ASSOC);
$gestion_nombre = $gestion_info['nombre_gestion'] ?? 'Gestión no definida';
$id_gestion_activa = $gestion_info['id_gestion'] ?? null;

// Consultar TODOS los estudiantes repitentes - CORREGIDO
$sql_repitentes = "
    SELECT 
        ea.id_aplazado,
        ea.id_estudiante,
        ea.id_materia,
        ea.id_seccion,
        ea.id_gestion,
        ea.nota_final,
        ea.intentos_completados,
        ea.motivo,
        ea.fecha_aplazado,
        ea.estado,
        e.nombres,
        e.apellidos,
        e.cedula,
        e.fecha_nacimiento,
        e.numeros_telefonicos as telefono,
        e.correo_electronico as correo,
        e.direccion,
        m.nombre_materia,
        s.nombre_seccion,
        g.grado,
        g.nivel
    FROM estudiantes_aplazados ea
    INNER JOIN estudiantes e ON ea.id_estudiante = e.id_estudiante
    INNER JOIN materias m ON ea.id_materia = m.id_materia
    INNER JOIN secciones s ON ea.id_seccion = s.id_seccion
    INNER JOIN grados g ON s.id_grado = g.id_grado
    WHERE ea.estado = 'pendiente'
    ORDER BY g.nivel, g.grado, s.nombre_seccion, e.apellidos, e.nombres
";

$stmt_rep = $pdo->query($sql_repitentes);
$repitentes = $stmt_rep->fetchAll(PDO::FETCH_ASSOC);

// Agrupar por grado y sección para mejor visualización
$grados_agrupados = [];
foreach ($repitentes as $rep) {
    $grado_key = $rep['grado'] . ' - ' . $rep['nivel'];
    $seccion_key = $rep['nombre_seccion'];
    
    if (!isset($grados_agrupados[$grado_key])) {
        $grados_agrupados[$grado_key] = [
            'grado' => $rep['grado'],
            'nivel' => $rep['nivel'],
            'secciones' => [],
            'total_repitentes' => 0
        ];
    }
    
    if (!isset($grados_agrupados[$grado_key]['secciones'][$seccion_key])) {
        $grados_agrupados[$grado_key]['secciones'][$seccion_key] = [
            'nombre_seccion' => $seccion_key,
            'repitentes' => [],
            'total' => 0
        ];
    }
    
    $grados_agrupados[$grado_key]['secciones'][$seccion_key]['repitentes'][] = $rep;
    $grados_agrupados[$grado_key]['secciones'][$seccion_key]['total']++;
    $grados_agrupados[$grado_key]['total_repitentes']++;
}

// Estadísticas generales
$total_repitentes = count($repitentes);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estudiantes Repitentes</title>
    <style>
    /* ESTILOS LIMPIOS Y PROFESIONALES */
    :root {
        --azul-profesional: #2c3e50;
        --gris-claro: #f8f9fa;
        --gris-medio: #e9ecef;
        --gris-oscuro: #6c757d;
        --blanco: #ffffff;
        --borde-suave: #dee2e6;
        --texto-principal: #343a40;
        --texto-secundario: #6c757d;
    }

    .card-repitentes {
        border: 1px solid var(--borde-suave);
        border-radius: 8px;
        background: var(--blanco);
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        margin-bottom: 20px;
        transition: all 0.3s ease;
    }

    .card-repitentes:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }

    .card-header-repitentes {
        background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        color: var(--azul-profesional) !important;
        border-bottom: 2px solid var(--borde-suave);
        padding: 16px 20px;
        font-weight: 600;
    }

    .badge-repitente {
        background: #e74c3c;
        color: white;
        font-weight: 500;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 0.8em;
    }

    .badge-contador {
        background: var(--azul-profesional);
        color: white;
        font-weight: 600;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.9em;
    }

    .estudiante-item-card {
        background: var(--blanco);
        border: 1px solid var(--borde-suave);
        border-radius: 8px;
        margin-bottom: 12px;
        padding: 16px;
        transition: all 0.3s ease;
    }

    .estudiante-item-card:hover {
        border-color: #adb5bd;
        background: #f8f9fa;
    }

    .nota-final {
        font-size: 1.1em;
        font-weight: 600;
        color: #e74c3c;
        background: #fdf2f2;
        padding: 6px 12px;
        border-radius: 6px;
        display: inline-block;
    }

    .icono-info {
        color: var(--azul-profesional);
        font-size: 1em;
        margin-right: 8px;
    }

    .btn-ver-detalles {
        background: var(--azul-profesional);
        border: 1px solid var(--azul-profesional);
        color: white;
        font-weight: 500;
        padding: 6px 14px;
        border-radius: 6px;
        font-size: 0.9em;
        transition: all 0.3s ease;
    }

    .btn-ver-detalles:hover {
        background: #1a252f;
        border-color: #1a252f;
        transform: translateY(-1px);
        color: white;
    }

    .btn-volver {
        background: transparent;
        border: 1px solid var(--borde-suave);
        color: var(--azul-profesional);
        font-weight: 500;
        padding: 8px 16px;
        border-radius: 6px;
        transition: all 0.3s ease;
    }

    .btn-volver:hover {
        background: var(--gris-claro);
        border-color: #adb5bd;
        color: var(--azul-profesional);
    }

    .alert-informativo {
        background: linear-gradient(135deg, #e3f2fd, #bbdefb);
        border: 1px solid #bbdefb;
        border-left: 4px solid var(--azul-profesional);
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 20px;
    }

    .alert-informativo h6 {
        color: var(--azul-profesional) !important; /* AZUL CORREGIDO */
        font-weight: 600;
    }

    .titulo-azul {
        color: var(--azul-profesional) !important;
        font-weight: 600;
    }

    .subtitulo-gestion {
        color: var(--gris-oscuro);
        font-size: 0.9em;
        margin-top: 5px;
    }

    .tabla-estudiantes {
        background: var(--blanco);
    }

    .tabla-estudiantes thead th {
        background: #f8f9fa;
        color: var(--azul-profesional) !important;
        font-weight: 600;
        border-bottom: 2px solid var(--borde-suave);
        padding: 14px 12px;
        font-size: 0.9em;
    }

    .tabla-estudiantes tbody tr {
        transition: all 0.3s ease;
    }

    .tabla-estudiantes tbody tr:hover {
        background: #f8f9fa;
    }

    .tabla-estudiantes tbody td {
        padding: 12px;
        vertical-align: middle;
        font-size: 0.9em;
    }

    .estado-pendiente {
        background: #fff3cd;
        color: #856404;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 0.8em;
        font-weight: 500;
    }

    .empty-state {
        background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        border: 2px dashed #dee2e6;
        border-radius: 12px;
        padding: 50px 20px;
        text-align: center;
    }

    .empty-state i {
        font-size: 4em;
        color: #adb5bd;
        margin-bottom: 15px;
        opacity: 0.7;
    }

    .filtro-box {
        background: var(--blanco);
        border: 1px solid var(--borde-suave);
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .filtro-box label {
        font-weight: 600;
        color: var(--azul-profesional);
        font-size: 0.9em;
    }

    .form-control-sm {
        font-size: 0.9em;
        border: 1px solid var(--borde-suave);
    }

    .form-control-sm:focus {
        border-color: #adb5bd;
        box-shadow: 0 0 0 0.2rem rgba(44, 62, 80, 0.1);
    }

    .acordeon-grado .card {
        border: 1px solid var(--borde-suave);
        border-radius: 8px;
        margin-bottom: 12px;
        overflow: hidden;
    }

    .acordeon-grado .card-header {
        background: #f8f9fa;
        border-bottom: 1px solid var(--borde-suave);
        padding: 16px;
        cursor: pointer;
        transition: background 0.3s ease;
    }

    .acordeon-grado .card-header:hover {
        background: #e9ecef;
    }

    .acordeon-grado .card-header h5 {
        color: var(--azul-profesional);
        font-weight: 600;
        font-size: 1em;
    }

    .header-principal {
        border-bottom: 1px solid var(--borde-suave);
        padding-bottom: 20px;
        margin-bottom: 30px;
    }

    .header-principal h1 {
        color: var(--azul-profesional);
        font-weight: 700;
        font-size: 1.8em;
    }

    .breadcrumb {
        background: transparent;
        padding: 0;
        margin-bottom: 0;
    }

    .breadcrumb-item a {
        color: var(--azul-profesional);
        text-decoration: none;
        font-weight: 500;
    }

    .breadcrumb-item.active {
        color: var(--gris-oscuro);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .estudiante-item-card {
            padding: 12px;
        }
    }
    </style>
</head>
<body>
<div class="content-wrapper">
    <div class="content">
        <div class="container-fluid">
            <!-- ENCABEZADO LIMPIO -->
            <div class="header-principal">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1>
                            <i class="fas fa-users mr-2"></i> Estudiantes Repitentes - <?= htmlspecialchars($gestion_nombre) ?>
                        </h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item">
                                    <a href="index.php">
                                        <i class="fas fa-home"></i> Inicio
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="seleccion_materia_pendiente.php">Materias Pendientes</a>
                                </li>
                                <li class="breadcrumb-item active">Repitentes</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="col-md-4 text-right">
                        <a href="seleccion_materia_pendiente.php" class="btn btn-volver">
                            <i class="fas fa-arrow-left mr-1"></i> Volver
                        </a>
                    </div>
                </div>
            </div>

            <!-- INFORMACIÓN IMPORTANTE - TÍTULO EN AZUL -->
            <div class="alert-informativo mb-4">
                <h6 class="titulo-azul">
                    <i class="fas fa-info-circle mr-2"></i> Lista de Estudiantes Repitentes
                </h6>
                <p class="mb-0">Estudiantes que han reprobado los 4 momentos de recuperación. Estos estudiantes deberán repetir el año escolar en la misma materia.</p>
            </div>

            <!-- FILTROS SIMPLES -->
            <div class="filtro-box mb-4">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="filtro_grado"><i class="fas fa-filter mr-1"></i> Filtrar por Grado</label>
                            <select class="form-control form-control-sm" id="filtro_grado">
                                <option value="">Todos los grados</option>
                                <?php foreach ($grados_agrupados as $grado_key => $grado_info): ?>
                                    <option value="<?= htmlspecialchars($grado_key) ?>">
                                        <?= htmlspecialchars($grado_key) ?> (<?= $grado_info['total_repitentes'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="filtro_busqueda"><i class="fas fa-search mr-1"></i> Buscar Estudiante</label>
                            <input type="text" class="form-control form-control-sm" id="filtro_busqueda" 
                                   placeholder="Nombre, cédula o materia...">
                        </div>
                    </div>
                </div>
            </div>

            <!-- CONTENIDO PRINCIPAL -->
            <div class="row">
                <div class="col-md-12">
                    <?php if (empty($repitentes)): ?>
                        <div class="empty-state">
                            <i class="fas fa-check-circle"></i>
                            <h4 class="mb-3" style="color: #28a745;">No hay estudiantes repitentes</h4>
                            <p class="text-muted mb-4">No se encontraron estudiantes registrados como repitentes en el sistema.</p>
                            <a href="seleccion_materia_pendiente.php" class="btn btn-ver-detalles">
                                <i class="fas fa-book mr-1"></i> Ver Materias Pendientes
                            </a>
                        </div>
                    <?php else: ?>
                        <!-- VISTA EN TARJETAS (Móvil/Tablet) -->
                        <div class="d-block d-lg-none">
                            <?php foreach ($grados_agrupados as $grado_key => $grado_info): ?>
                                <div class="card card-repitentes mb-3 grado-item" data-grado="<?= htmlspecialchars($grado_key) ?>">
                                    <div class="card-header-repitentes">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-0 titulo-azul"><?= htmlspecialchars($grado_key) ?></h6>
                                                <small class="text-muted"><?= $grado_info['total_repitentes'] ?> estudiante(s)</small>
                                            </div>
                                            <span class="badge-contador"><?= $grado_info['total_repitentes'] ?></span>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <?php foreach ($grado_info['secciones'] as $seccion_key => $seccion_info): ?>
                                            <div class="mb-3">
                                                <h6 class="mb-2 titulo-azul" style="font-weight: 600;">
                                                    Sección <?= htmlspecialchars($seccion_key) ?>
                                                </h6>
                                                
                                                <?php foreach ($seccion_info['repitentes'] as $rep): ?>
                                                    <div class="estudiante-item-card mb-2 estudiante-item" 
                                                         data-nombre="<?= htmlspecialchars(strtolower($rep['apellidos'] . ' ' . $rep['nombres'])) ?>"
                                                         data-materia="<?= htmlspecialchars(strtolower($rep['nombre_materia'])) ?>"
                                                         data-grado-item="<?= htmlspecialchars($grado_key) ?>">
                                                        <div class="row">
                                                            <div class="col-8">
                                                                <h6 class="mb-1" style="font-weight: 600; font-size: 0.95em;">
                                                                    <?= htmlspecialchars($rep['apellidos'] . ', ' . $rep['nombres']) ?>
                                                                </h6>
                                                                <small class="text-muted d-block mb-1">
                                                                    <i class="fas fa-id-card icono-info"></i> <?= htmlspecialchars($rep['cedula']) ?>
                                                                </small>
                                                                <small class="text-muted d-block">
                                                                    <i class="fas fa-book icono-info"></i> <?= htmlspecialchars($rep['nombre_materia']) ?>
                                                                </small>
                                                            </div>
                                                            <div class="col-4 text-right">
                                                                <div class="nota-final mb-2"><?= $rep['nota_final'] ?>/20</div>
                                                                <span class="estado-pendiente">Pendiente</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- VISTA EN TABLA (Desktop) -->
                        <div class="d-none d-lg-block">
                            <div class="card">
                                <div class="card-header-repitentes">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h5 class="mb-0 titulo-azul">Lista de Estudiantes Repitentes</h5>
                                            <div class="subtitulo-gestion">
                                                <?= $total_repitentes ?> estudiantes registrados | Gestión: <?= htmlspecialchars($gestion_nombre) ?>
                                            </div>
                                        </div>
                                        <span class="badge-contador">Total: <?= $total_repitentes ?></span>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover tabla-estudiantes w-100" id="tablaRepitentes">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Estudiante</th>
                                                    <th>Cédula</th>
                                                    <th>Grado - Sección</th>
                                                    <th>Materia</th>
                                                    <th>Nota Final</th>
                                                    <th>Intentos</th>
                                                    <th>Estado</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($repitentes as $index => $rep): ?>
                                                    <tr class="estudiante-item" 
                                                        data-grado="<?= htmlspecialchars($rep['grado'] . ' - ' . $rep['nivel']) ?>"
                                                        data-nombre="<?= htmlspecialchars(strtolower($rep['apellidos'] . ' ' . $rep['nombres'])) ?>"
                                                        data-materia="<?= htmlspecialchars(strtolower($rep['nombre_materia'])) ?>"
                                                        data-grado-item="<?= htmlspecialchars($rep['grado'] . ' - ' . $rep['nivel']) ?>">
                                                        <td><?= $index + 1 ?></td>
                                                        <td>
                                                            <strong><?= htmlspecialchars($rep['apellidos'] . ', ' . $rep['nombres']) ?></strong>
                                                        </td>
                                                        <td><?= htmlspecialchars($rep['cedula']) ?></td>
                                                        <td>
                                                            <?= htmlspecialchars($rep['grado'] . ' - ' . $rep['nivel']) ?>
                                                            <br><small><?= htmlspecialchars($rep['nombre_seccion']) ?></small>
                                                        </td>
                                                        <td><?= htmlspecialchars($rep['nombre_materia']) ?></td>
                                                        <td>
                                                            <span class="nota-final"><?= $rep['nota_final'] ?>/20</span>
                                                        </td>
                                                        <td><?= $rep['intentos_completados'] ?> de 4</td>
                                                        <td>
                                                            <span class="estado-pendiente">Pendiente</span>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Incluir jQuery desde una ruta correcta -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    console.log('jQuery cargado correctamente');
    console.log('SweetAlert2 cargado correctamente');
    
    // Filtrar por grado (vista móvil y escritorio)
    $('#filtro_grado').change(function() {
        console.log('Filtro grado cambiado:', $(this).val());
        const filtroGrado = $(this).val().toLowerCase();
        
        if (filtroGrado === '' || filtroGrado === 'todos los grados') {
            // Mostrar todos
            $('.grado-item').show();
            $('.estudiante-item').show();
            $('#tablaRepitentes tbody tr').show();
        } else {
            // Filtrar grados en vista móvil
            $('.grado-item').each(function() {
                const grado = $(this).data('grado').toLowerCase();
                console.log('Comparando grado:', grado, 'con filtro:', filtroGrado);
                if (grado.includes(filtroGrado)) {
                    $(this).show();
                    $(this).find('.estudiante-item').show();
                } else {
                    $(this).hide();
                }
            });
            
            // Filtrar filas en la tabla
            $('#tablaRepitentes tbody tr').each(function() {
                const gradoItem = $(this).data('grado-item').toLowerCase();
                console.log('Comparando fila grado:', gradoItem, 'con filtro:', filtroGrado);
                if (gradoItem.includes(filtroGrado)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }
    });
    
    // Filtrar por búsqueda general
    $('#filtro_busqueda').on('keyup', function() {
        const busqueda = $(this).val().toLowerCase();
        console.log('Búsqueda:', busqueda);
        
        if (busqueda === '') {
            // Si no hay búsqueda, mostrar todos
            $('.estudiante-item').show();
            $('.grado-item').show();
            $('#tablaRepitentes tbody tr').show();
            return;
        }
        
        // Filtrar en vista móvil
        $('.estudiante-item').each(function() {
            const nombre = $(this).data('nombre');
            const materia = $(this).data('materia');
            const cedula = $(this).find('small:contains("Cédula")').text().toLowerCase().replace('cédula: ', '');
            
            if (nombre.includes(busqueda) || materia.includes(busqueda) || cedula.includes(busqueda)) {
                $(this).show();
                $(this).closest('.grado-item').show();
            } else {
                $(this).hide();
            }
        });
        
        // Filtrar en vista escritorio
        $('#tablaRepitentes tbody tr').each(function() {
            const nombre = $(this).find('td:nth-child(2)').text().toLowerCase();
            const materia = $(this).find('td:nth-child(5)').text().toLowerCase();
            const cedula = $(this).find('td:nth-child(3)').text().toLowerCase();
            const gradoSeccion = $(this).find('td:nth-child(4)').text().toLowerCase();
            
            if (nombre.includes(busqueda) || materia.includes(busqueda) || cedula.includes(busqueda) || gradoSeccion.includes(busqueda)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
    
    // Mostrar información al hacer clic en un estudiante (versión simple)
    $(document).on('click', '.estudiante-item-card', function(e) {
        if (!$(e.target).closest('a, button').length) {
            const estudiante = $(this).find('h6').text();
            const materia = $(this).find('small:contains("Materia")').text().replace('Materia: ', '');
            const nota = $(this).find('.nota-final').text();
            const cedula = $(this).find('small:contains("Cédula")').text().replace('Cédula: ', '');
            
            Swal.fire({
                title: 'Información del Estudiante',
                html: `
                    <div class="text-left" style="font-size: 0.95em;">
                        <p><strong>Estudiante:</strong> ${estudiante}</p>
                        <p><strong>Cédula:</strong> ${cedula}</p>
                        <p><strong>Materia:</strong> ${materia}</p>
                        <p><strong>Nota Final:</strong> ${nota}</p>
                        <p><strong>Estado:</strong> <span class="badge bg-warning text-dark">Pendiente de reinscripción</span></p>
                        <hr>
                        <p class="text-muted"><small>Este estudiante reprobó los 4 momentos de recuperación y debe repetir el año escolar.</small></p>
                    </div>
                `,
                icon: 'info',
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#2c3e50',
                width: 500
            });
        }
    });
    
    // Mostrar información al hacer clic en fila de la tabla
    $(document).on('click', '#tablaRepitentes tbody tr', function(e) {
        if (!$(e.target).closest('a, button, span').length) {
            const estudiante = $(this).find('td:nth-child(2)').text();
            const cedula = $(this).find('td:nth-child(3)').text();
            const materia = $(this).find('td:nth-child(5)').text();
            const nota = $(this).find('.nota-final').text();
            
            Swal.fire({
                title: 'Información del Estudiante',
                html: `
                    <div class="text-left" style="font-size: 0.95em;">
                        <p><strong>Estudiante:</strong> ${estudiante}</p>
                        <p><strong>Cédula:</strong> ${cedula}</p>
                        <p><strong>Materia:</strong> ${materia}</p>
                        <p><strong>Nota Final:</strong> ${nota}</p>
                        <p><strong>Estado:</strong> <span class="badge bg-warning text-dark">Pendiente de reinscripción</span></p>
                        <hr>
                        <p class="text-muted"><small>Este estudiante reprobó los 4 momentos de recuperación y debe repetir el año escolar.</small></p>
                    </div>
                `,
                icon: 'info',
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#2c3e50',
                width: 500
            });
        }
    });
});
</script>

</body>
</html>
<?php include('../../admin/layout/parte2.php'); ?>