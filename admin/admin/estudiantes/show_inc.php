<?php
// Obtener el ID del estudiante desde la URL
$id_estudiante = isset($_GET['id']) ? $_GET['id'] : null;

if ($id_estudiante === null) {
    die("Error: ID del estudiante no proporcionado.");
}

// Incluir la configuración y otros archivos necesarios
include ('../../app/config.php');
include ('../../admin/layout/parte1.php');
include ('../../app/controllers/estudiantes/datos_del_estudiante.php');
include ('../../app/controllers/estudiantes/datos_inscripcion.php');

// Obtener el nombre del grado
if (isset($grado)) {
    $sql_grado = "SELECT grado FROM grados WHERE id_grado = :id_grado";
    $query_grado = $pdo->prepare($sql_grado);
    $query_grado->bindParam(':id_grado', $grado, PDO::PARAM_INT);
    $query_grado->execute();
    $grado_info = $query_grado->fetch(PDO::FETCH_ASSOC);
    $nombre_grado = $grado_info ? $grado_info['grado'] : 'N/A';
} else {
    $nombre_grado = 'N/A';
}

// Mapeo de turnos
$turno_map = [
    'M' => 'Mañana',
    'T' => 'Tarde'
];

// Obtener el turno correspondiente
$turno_mostrado = isset($turno) && array_key_exists($turno, $turno_map) ? $turno_map[$turno] : 'N/A';
?>

<div class="content-wrapper">
    <div class="content">
        <div class="container">
            <!-- Breadcrumb -->
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">Detalles de Inscripción</h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="<?=APP_URL;?>/admin">Inicio</a></li>
                                <li class="breadcrumb-item"><a href="<?=APP_URL;?>/admin/estudiantes">Estudiantes</a></li>
                                <li class="breadcrumb-item"><a href="<?=APP_URL;?>/admin/estudiantes/lista_de_inscripcion.php">Lista de Inscripciones</a></li>
                                <li class="breadcrumb-item active">Detalles de Inscripción</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Información del Estudiante -->
            <div class="col-lg-12">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-user-graduate mr-2"></i>Información del Estudiante</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="student-info text-center">
                                    <div class="student-avatar mb-3">
                                        <i class="fas fa-user-circle fa-4x text-primary"></i>
                                    </div>
                                    <h4 class="student-name"><?= htmlspecialchars($nombres . " " . $apellidos); ?></h4>
                                    <?php if (!empty($cedula_escolar)) : ?>
                                        <p class="student-id text-muted">
                                            <i class="fas fa-id-card mr-1"></i>
                                            <?= htmlspecialchars($cedula_escolar); ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="info-box bg-light">
                                            <div class="info-box-content">
                                                <span class="info-box-text text-muted">Cédula</span>
                                                <span class="info-box-number">
                                                    <?= !empty($cedula) ? htmlspecialchars($cedula) : 'No tiene'; ?>
                                                </span>
                                            </div>
                                            <span class="info-box-icon bg-info"><i class="fas fa-address-card"></i></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-box bg-light">
                                            <div class="info-box-content">
                                                <span class="info-box-text text-muted">Estatus</span>
                                                <span class="info-box-number">
                                                    <?php if (strtolower($estudiante['estatus']) == "activo") : ?>
                                                        <span class="badge badge-success">ACTIVO</span>
                                                    <?php else : ?>
                                                        <span class="badge badge-danger">INACTIVO</span>
                                                    <?php endif; ?>
                                                </span>
                                            </div>
                                            <span class="info-box-icon bg-success"><i class="fas fa-check-circle"></i></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sección 1: Información Académica -->
            <div class="col-lg-12">
                <div class="card card-outline card-info">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-graduation-cap mr-2"></i>Información Académica</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="control-label">ID Gestión</label>
                                    <div class="form-control-plaintext p-3 bg-light rounded">
                                        <i class="fas fa-hashtag mr-2 text-primary"></i>
                                        <?= htmlspecialchars($id_gestion); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="control-label">Nivel</label>
                                    <div class="form-control-plaintext p-3 bg-light rounded">
                                        <i class="fas fa-layer-group mr-2 text-primary"></i>
                                        <?= htmlspecialchars($nivel); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="control-label">Grado</label>
                                    <div class="form-control-plaintext p-3 bg-light rounded">
                                        <i class="fas fa-book mr-2 text-primary"></i>
                                        <?= htmlspecialchars($nombre_grado); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="control-label">Sección</label>
                                    <div class="form-control-plaintext p-3 bg-light rounded">
                                        <i class="fas fa-users mr-2 text-primary"></i>
                                        <?= htmlspecialchars($nombre_seccion); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label">Turno</label>
                                    <div class="form-control-plaintext p-3 bg-light rounded">
                                        <i class="fas fa-clock mr-2 text-primary"></i>
                                        <?= htmlspecialchars($turno_mostrado); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label class="control-label">Horario del Turno</label>
                                    <div class="form-control-plaintext p-3 bg-light rounded">
                                        <i class="fas fa-calendar-alt mr-2 text-primary"></i>
                                        <?php if ($turno_mostrado == 'Mañana') : ?>
                                            7:00 AM - 12:00 PM
                                        <?php else : ?>
                                            1:00 PM - 6:00 PM
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sección 2: Uniforme y Medidas -->
            <div class="col-lg-12">
                <div class="card card-outline card-info">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-tshirt mr-2"></i>Información de Uniforme</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="info-box bg-gradient-info">
                                    <div class="info-box-content">
                                        <span class="info-box-text text-white">Talla de Camisa</span>
                                        <span class="info-box-number text-white" style="font-size: 2rem;">
                                            <?= htmlspecialchars($talla_camisa); ?>
                                        </span>
                                    </div>
                                    <span class="info-box-icon"><i class="fas fa-tshirt"></i></span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-box bg-gradient-info">
                                    <div class="info-box-content">
                                        <span class="info-box-text text-white">Talla de Pantalón</span>
                                        <span class="info-box-number text-white" style="font-size: 2rem;">
                                            <?= htmlspecialchars($talla_pantalon); ?>
                                        </span>
                                    </div>
                                    <span class="info-box-icon"><i class="fas fa-tshirt"></i></span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-box bg-gradient-info">
                                    <div class="info-box-content">
                                        <span class="info-box-text text-white">Talla de Zapatos</span>
                                        <span class="info-box-number text-white" style="font-size: 2rem;">
                                            <?= htmlspecialchars($talla_zapatos); ?>
                                        </span>
                                    </div>
                                    <span class="info-box-icon"><i class="fas fa-shoe-prints"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sección 3: Resumen de Inscripción -->
            <div class="col-lg-12">
                <div class="card card-outline card-success">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-clipboard-check mr-2"></i>Resumen de Inscripción</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="alert alert-info">
                                    <h5><i class="fas fa-info-circle mr-2"></i>Resumen de la Inscripción</h5>
                                    <p class="mb-1"><strong>Estudiante:</strong> <?= htmlspecialchars($nombres . " " . $apellidos); ?></p>
                                    <p class="mb-1"><strong>Grado y Sección:</strong> <?= htmlspecialchars($nombre_grado . " - " . $nombre_seccion); ?></p>
                                    <p class="mb-1"><strong>Turno:</strong> <?= htmlspecialchars($turno_mostrado); ?></p>
                                    <p class="mb-0"><strong>Estado:</strong> 
                                        <?php if (strtolower($estudiante['estatus']) == "activo") : ?>
                                            <span class="badge badge-success">Inscripción Activa</span>
                                        <?php else : ?>
                                            <span class="badge badge-danger">Inscripción Inactiva</span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer border-top border-success">
                        <div class="d-flex w-100 justify-content-center align-items-center">
                            <a href="<?= APP_URL; ?>/admin/estudiantes/lista_de_inscripcion.php" class="btn btn-secondary mr-2">
                                <i class="fas fa-arrow-left mr-1"></i> Volver a la Lista
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    margin-bottom: 20px;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    border: 1px solid #e3e6f0;
}

.card-header {
    background-color: #f8f9fc;
    border-bottom: 1px solid #e3e6f0;
}

.card-title {
    color: #5a5c69;
    font-weight: 600;
}

.card-title i {
    color: #4e73df;
}

.form-group {
    margin-bottom: 1.5rem;
}

label.control-label {
    font-weight: 600;
    color: #5a5c69;
    margin-bottom: 0.5rem;
}

.form-control-plaintext {
    min-height: 50px;
    border: 1px solid #e3e6f0 !important;
    background-color: #f8f9fc !important;
}

.info-box {
    box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
    border-radius: 0.35rem;
    background-color: #fff;
    display: flex;
    margin-bottom: 1rem;
    min-height: 80px;
    padding: 0.5rem;
    position: relative;
}

.info-box .info-box-icon {
    border-radius: 0.35rem;
    align-items: center;
    display: flex;
    font-size: 1.875rem;
    justify-content: center;
    text-align: center;
    width: 70px;
    height: 70px;
}

.info-box .info-box-content {
    display: flex;
    flex-direction: column;
    justify-content: center;
    line-height: 1.8;
    flex: 1;
    padding: 0 10px;
}

.info-box .info-box-number {
    font-size: 1.5rem;
    font-weight: 700;
}

.info-box-text {
    text-transform: uppercase;
    font-size: 0.875rem;
}

.bg-gradient-info {
    background: linear-gradient(45deg, #36b9cc, #2c9faf) !important;
}

.bg-gradient-success {
    background: linear-gradient(45deg, #1cc88a, #17a673) !important;
}

.bg-gradient-warning {
    background: linear-gradient(45deg, #f6c23e, #f4b619) !important;
}

.student-info {
    padding: 20px;
    border-right: 2px solid #e3e6f0;
}

.student-name {
    color: #5a5c69;
    font-weight: 600;
    margin-bottom: 5px;
}

.student-id {
    font-size: 0.9rem;
}

.student-avatar {
    color: #4e73df;
}

.badge {
    font-size: 0.875rem;
    padding: 0.5rem 1rem;
}

.breadcrumb {
    background-color: transparent;
    padding: 0;
    margin-bottom: 0;
}

.breadcrumb-item.active {
    color: #6c757d;
}

.content-header h1 {
    color: #5a5c69;
    font-weight: 600;
}

.btn-primary {
    background-color: #4e73df;
    border-color: #4e73df;
}

.btn-primary:hover {
    background-color: #2e59d9;
    border-color: #2653d4;
}

.alert {
    border-radius: 0.35rem;
    border-left: 4px solid #4e73df;
}

@media print {
    .btn, .card-footer, .breadcrumb {
        display: none !important;
    }
    
    .card {
        box-shadow: none !important;
        border: 1px solid #ddd !important;
    }
}
</style>

<?php
include ('../../admin/layout/parte2.php');
include ('../../layout/mensajes.php');
?>