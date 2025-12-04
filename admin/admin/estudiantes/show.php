<?php  
$id_estudiante = $_GET['id'];  
include ('../../app/config.php');  
include ('../../admin/layout/parte1.php');  
include ('../../app/controllers/estudiantes/datos_del_estudiante.php');  
?>  

<div class="content-wrapper">
    <div class="content">
        <div class="container">
            <!-- Breadcrumb -->  
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0"><?=$nombres." ".$apellidos;?></h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="<?=APP_URL;?>/admin">Inicio</a></li>
                                <li class="breadcrumb-item"><a href="<?=APP_URL;?>/admin/estudiantes">Estudiantes</a></li>
                                <li class="breadcrumb-item"><a href="<?=APP_URL;?>/admin/estudiantes/lista_de_estudiante.php">Lista de estudiante</a></li>
                                <li class="breadcrumb-item">Ver datos</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sección 1: Información Personal -->
            <div class="col-lg-12">
                <div class="card card-outline card-info">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-user-graduate mr-2"></i>Información Personal</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="info-box bg-light">
                                    <div class="info-box-content">
                                        <span class="info-box-text text-muted">Tipo de Cédula</span>
                                        <span class="info-box-number"><?=$tipo_cedula;?></span>
                                    </div>
                                    <span class="info-box-icon bg-info"><i class="fas fa-id-card"></i></span>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="info-box bg-light">
                                    <div class="info-box-content">
                                        <span class="info-box-text text-muted">Cédula de Identidad</span>
                                        <?php if (!empty($cedula)) : ?>
                                            <?php   
                                                $cedula_formateada = substr($cedula, 0, 2) . '.' . substr($cedula, 2, 3) . '.' . substr($cedula, 5);   
                                            ?>  
                                            <span class="info-box-number"><?=$cedula_formateada;?></span>
                                        <?php else: ?>  
                                            <span class="info-box-number text-muted">No tiene</span>
                                        <?php endif; ?>
                                    </div>
                                    <span class="info-box-icon bg-info"><i class="fas fa-address-card"></i></span>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="info-box bg-light">
                                    <div class="info-box-content">
                                        <span class="info-box-text text-muted">Cédula Escolar</span>
                                        <?php if (!empty($cedula_escolar)) : ?>
                                            <?php   
                                                $cedula_escolar_formateada = substr($cedula_escolar, 0, 2) . '-' . 
                                                    substr($cedula_escolar, 2, 3) . '.' . 
                                                    substr($cedula_escolar, 5);
                                            ?>  
                                            <span class="info-box-number"><?=$cedula_escolar_formateada;?></span>
                                        <?php else: ?>  
                                            <span class="info-box-number text-muted">No tiene</span>
                                        <?php endif; ?>
                                    </div>
                                    <span class="info-box-icon bg-info"><i class="fas fa-graduation-cap"></i></span>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="control-label">Nombres</label>
                                    <div class="form-control-plaintext p-3 bg-light rounded">
                                        <i class="fas fa-user mr-2 text-primary"></i>
                                        <?=$nombres;?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="control-label">Apellidos</label>
                                    <div class="form-control-plaintext p-3 bg-light rounded">
                                        <i class="fas fa-user mr-2 text-primary"></i>
                                        <?=$apellidos;?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="control-label">Fecha de Nacimiento</label>
                                    <div class="form-control-plaintext p-3 bg-light rounded">
                                        <i class="fas fa-birthday-cake mr-2 text-primary"></i>
                                        <?php   
                                            $fecha_nacimiento_formateada = date("d/m/Y", strtotime($fecha_nacimiento));   
                                        ?>  
                                        <?=$fecha_nacimiento_formateada;?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="control-label">Género</label>
                                    <div class="form-control-plaintext p-3 bg-light rounded">
                                        <i class="fas fa-venus-mars mr-2 text-primary"></i>
                                        <?=ucfirst($genero);?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sección 2: Información de Contacto -->
            <div class="col-lg-12">
                <div class="card card-outline card-info">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-address-book mr-2"></i>Información de Contacto</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label">Correo Electrónico</label>
                                    <div class="form-control-plaintext p-3 bg-light rounded">
                                        <i class="fas fa-envelope mr-2 text-primary"></i>
                                        <?=$correo_electronico;?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label">Teléfono</label>
                                    <div class="form-control-plaintext p-3 bg-light rounded">
                                        <i class="fas fa-phone mr-2 text-primary"></i>
                                        <?php   
                                            $telefono_formateado = substr($numeros_telefonicos, 0, 4) . '' . 
                                                substr($numeros_telefonicos, 4, 3) . '-' . 
                                                substr($numeros_telefonicos, 7);   
                                        ?>  
                                        <?=$telefono_formateado;?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label">Dirección</label>
                                    <div class="form-control-plaintext p-3 bg-light rounded">
                                        <i class="fas fa-map-marker-alt mr-2 text-primary"></i>
                                        <?=$direccion;?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sección 3: Información Adicional -->
            <div class="col-lg-12">
                <div class="card card-outline card-info">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i>Información Adicional</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label">Tipo de Discapacidad</label>
                                    <div class="form-control-plaintext p-3 bg-light rounded">
                                        <i class="fas fa-wheelchair mr-2 text-primary"></i>
                                        <?=ucfirst($tipo_discapacidad);?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label">Estatus</label>
                                    <div class="form-control-plaintext p-3 bg-light rounded">
                                        <?php if (strtolower($estudiante['estatus']) == "activo") : ?>  
                                            <span class="badge badge-success p-2">
                                                <i class="fas fa-check-circle mr-1"></i> ACTIVO
                                            </span>  
                                        <?php else: ?>  
                                            <span class="badge badge-danger p-2">
                                                <i class="fas fa-times-circle mr-1"></i> INACTIVO
                                            </span>  
                                        <?php endif; ?>  
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label">Representante</label>
                                    <div class="form-control-plaintext p-3 bg-light rounded">
                                        <i class="fas fa-user-tie mr-2 text-primary"></i>
                                        <?php
                                            // Aquí puedes agregar el nombre del representante si lo tienes disponible
                                            echo "Representante Asociado";
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer border-top border-info">
                        <div class="d-flex w-100 justify-content-center align-items-center">
                            <a href="<?=APP_URL;?>/admin/estudiantes/Lista_de_estudiante.php" class="btn btn-secondary mr-2">
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

.bg-info {
    background-color: #36b9cc !important;
}

.bg-success {
    background-color: #1cc88a !important;
}

.bg-warning {
    background-color: #f6c23e !important;
}

.text-muted {
    color: #858796 !important;
}
</style>

<?php  
include ('../../admin/layout/parte2.php');  
include ('../../layout/mensajes.php');  
?>