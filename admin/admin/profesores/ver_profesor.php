<?php
include('../../app/config.php');
include('../../admin/layout/parte1.php');

// Get professor ID from URL
$id_profesor = $_GET['id'];

// Query to get professor details
$sql_profesor = "SELECT * FROM profesores WHERE id_profesor = :id_profesor";
$query_profesor = $pdo->prepare($sql_profesor);
$query_profesor->bindParam(':id_profesor', $id_profesor);
$query_profesor->execute();
$profesor = $query_profesor->fetch(PDO::FETCH_ASSOC);

if (!$profesor) {
    echo "<script>window.location.href = 'index.php';</script>";
    exit();
}
?>

<div class="content-wrapper">
    <br>
    <div class="content">
        <div class="container-fluid">
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h3 class="m-0">Información del Profesor</h3>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="<?= APP_URL; ?>/admin">Inicio</a></li>
                                <li class="breadcrumb-item"><a href="<?= APP_URL; ?>/admin/profesores">Profesores</a></li>
                                <li class="breadcrumb-item active">Ver Profesor</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-8 offset-md-2">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Carnet del Profesor</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" onclick="window.print()">
                                    <i class="fas fa-print"></i> Imprimir
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 text-center">
                                    <!-- Placeholder for professor photo -->
                                    <div class="professor-photo" style="width: 200px; height: 250px; background-color: #f8f9fa; border: 1px solid #dee2e6; margin: 0 auto; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-user-tie" style="font-size: 80px; color: #6c757d;"></i>
                                    </div>
                                    <p class="text-muted mt-2">Foto del Profesor</p>
                                </div>
                                <div class="col-md-8">
                                    <div class="professor-info">
                                        <h2 style="color: #007bff;"><?= htmlspecialchars($profesor['nombres'] . ' ' . $profesor['apellidos']) ?></h2>
                                        <hr>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p><strong>Cédula:</strong> <?= htmlspecialchars($profesor['cedula']) ?></p>
                                                <p><strong>Especialidad:</strong> <?= htmlspecialchars($profesor['especialidad']) ?></p>
                                            </div>
                                            <div class="col-md-6">
                                                <p><strong>Teléfono:</strong> <?= htmlspecialchars($profesor['telefono']) ?></p>
                                                <p><strong>Estado:</strong> 
                                                    <?php if ($profesor['estado'] == 1): ?>
                                                        <span class="badge badge-success">Activo</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-danger">Inactivo</span>
                                                    <?php endif; ?>
                                                </p>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="institution-info text-center" style="margin-top: 30px;">
                                            <h4 style="color: #28a745;"><?= APP_NAME ?></h4>
                                            <p class="text-muted">Carnet de Identificación Docente</p>
                                            <small class="text-muted">Válido mientras pertenezca a la institución</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="listar_profesores.php" class="btn btn-default">Volver</a>
                            <a href="editar_profesor.php?id=<?= $profesor['id_profesor'] ?>" class="btn btn-warning float-right">
                                <i class="fas fa-edit"></i> Editar Información
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        body * {
            visibility: hidden;
        }
        .card, .card * {
            visibility: visible;
        }
        .card {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            border: none;
        }
        .card-header, .card-footer {
            display: none;
        }
    }
</style>

<?php
include('../../admin/layout/parte2.php');
?>