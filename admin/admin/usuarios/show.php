<?php
$id_usuario = $_GET['id'];

include ('../../app/config.php');
include ('../../admin/layout/parte1.php');
include ('../../app/controllers/usuarios/datos_del_usuario.php');

// Verificar que las variables estén definidas antes de usarlas
$estado_display = isset($estado) ? $estado : '0';
$fyh_creacion_display = isset($fyh_creacion) && !empty($fyh_creacion) ? $fyh_creacion : 'No registrada';
$estado_text = ($estado_display == '1') ? 'ACTIVO' : 'INACTIVO';
$estado_color = ($estado_display == '1') ? 'success' : 'danger';
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <br>
    <div class="content">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="page-header">
                        <h1>
                            <i class="fas fa-user"></i> Detalles del Usuario
                            <small class="text-muted"><?=htmlspecialchars($email ?? 'Usuario');?></small>
                        </h1>
                    </div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?=APP_URL;?>/admin/dashboard">Inicio</a></li>
                            <li class="breadcrumb-item"><a href="<?=APP_URL;?>/admin/usuarios">Usuarios</a></li>
                            <li class="breadcrumb-item active">Detalles</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <br>
            
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h3 class="card-title">
                                    <i class="fas fa-id-card"></i> Información del Usuario
                                </h3>
                                <span class="badge bg-<?=$estado_color;?>">
                                    <?=$estado_text;?>
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- Información Básica -->
                                <div class="col-md-12">
                                    <div class="info-box bg-light">
                                        <span class="info-box-icon bg-info">
                                            <i class="fas fa-user-tag"></i>
                                        </span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">ROL</span>
                                            <span class="info-box-number"><?=htmlspecialchars($nombre_rol ?? 'No asignado');?></span>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Datos principales en cards -->
                                <div class="col-md-6">
                                    <div class="card card-primary card-outline">
                                        <div class="card-header">
                                            <h4 class="card-title">
                                                <i class="fas fa-envelope"></i> Información de Acceso
                                            </h4>
                                        </div>
                                        <div class="card-body">
                                            <dl class="row">
                                                <dt class="col-sm-4">Email:</dt>
                                                <dd class="col-sm-8">
                                                    <a href="mailto:<?=$email ?? '';?>"><?=$email ?? 'No registrado';?></a>
                                                </dd>
                                                
                                                <dt class="col-sm-4">Estado:</dt>
                                                <dd class="col-sm-8">
                                                    <span class="badge bg-<?=$estado_color;?>">
                                                        <?=$estado_text;?>
                                                    </span>
                                                </dd>
                                                
                                                <dt class="col-sm-4">Creado:</dt>
                                                <dd class="col-sm-8">
                                                    <?php if($fyh_creacion_display != 'No registrada'): ?>
                                                    <i class="far fa-calendar-alt"></i>
                                                    <?=date('d/m/Y', strtotime($fyh_creacion_display));?>
                                                    <small class="text-muted">
                                                        <?=date('H:i:s', strtotime($fyh_creacion_display));?>
                                                    </small>
                                                    <?php else: ?>
                                                    <span class="text-muted"><?=$fyh_creacion_display;?></span>
                                                    <?php endif; ?>
                                                </dd>
                                                
                                                <?php if(isset($fyh_actualizacion) && !empty($fyh_actualizacion) && $fyh_actualizacion != '0000-00-00 00:00:00'): ?>
                                                <dt class="col-sm-4">Actualizado:</dt>
                                                <dd class="col-sm-8">
                                                    <i class="far fa-calendar-check"></i>
                                                    <?=date('d/m/Y', strtotime($fyh_actualizacion));?>
                                                    <small class="text-muted">
                                                        <?=date('H:i:s', strtotime($fyh_actualizacion));?>
                                                    </small>
                                                </dd>
                                                <?php endif; ?>
                                            </dl>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Información específica del rol -->
                                <div class="col-md-6">
                                    <?php 
                                    $card_color = 'secondary';
                                    $card_icon = 'user';
                                    
                                    if(isset($rol_id)) {
                                        switch($rol_id) {
                                            case 1: 
                                                $card_color = 'danger'; 
                                                $card_icon = 'user-shield'; 
                                                break;
                                            case 2: 
                                                $card_color = 'info'; 
                                                $card_icon = 'briefcase'; 
                                                break;
                                            case 3: 
                                                $card_color = 'warning'; 
                                                $card_icon = 'graduation-cap'; 
                                                break;
                                            case 4: 
                                                $card_color = 'primary'; 
                                                $card_icon = 'user-friends'; 
                                                break;
                                            case 5: 
                                                $card_color = 'success'; 
                                                $card_icon = 'chalkboard-teacher'; 
                                                break;
                                        }
                                    }
                                    ?>
                                    
                                    <div class="card card-<?=$card_color;?> card-outline">
                                        <div class="card-header">
                                            <h4 class="card-title">
                                                <i class="fas fa-<?=$card_icon;?>"></i> 
                                                Información de <?=isset($tipo_persona) && !empty($tipo_persona) ? $tipo_persona : 'Perfil';?>
                                            </h4>
                                        </div>
                                        <div class="card-body">
                                            <?php if(isset($datos_personales) && $datos_personales): ?>
                                                <dl class="row">
                                                    <?php if(isset($datos_personales['cedula']) && !empty($datos_personales['cedula'])): ?>
                                                    <dt class="col-sm-4">Cédula:</dt>
                                                    <dd class="col-sm-8"><?=htmlspecialchars($datos_personales['cedula']);?></dd>
                                                    <?php endif; ?>
                                                    
                                                    <?php if(isset($datos_personales['nombres']) && !empty($datos_personales['nombres'])): ?>
                                                    <dt class="col-sm-4">Nombres:</dt>
                                                    <dd class="col-sm-8"><?=htmlspecialchars($datos_personales['nombres']);?></dd>
                                                    <?php endif; ?>
                                                    
                                                    <?php if(isset($datos_personales['apellidos']) && !empty($datos_personales['apellidos'])): ?>
                                                    <dt class="col-sm-4">Apellidos:</dt>
                                                    <dd class="col-sm-8"><?=htmlspecialchars($datos_personales['apellidos']);?></dd>
                                                    <?php endif; ?>
                                                    
                                                    <?php if(isset($datos_personales['telefono']) && !empty($datos_personales['telefono'])): ?>
                                                    <dt class="col-sm-4">Teléfono:</dt>
                                                    <dd class="col-sm-8">
                                                        <a href="tel:<?=$datos_personales['telefono'];?>">
                                                            <?=htmlspecialchars($datos_personales['telefono']);?>
                                                        </a>
                                                    </dd>
                                                    <?php endif; ?>
                                                    
                                                    <?php if(isset($datos_personales['especialidad']) && !empty($datos_personales['especialidad'])): ?>
                                                    <dt class="col-sm-4">Especialidad:</dt>
                                                    <dd class="col-sm-8">
                                                        <span class="badge bg-info">
                                                            <?=htmlspecialchars($datos_personales['especialidad']);?>
                                                        </span>
                                                    </dd>
                                                    <?php endif; ?>
                                                    
                                                    <?php if(isset($datos_personales['departamento']) && !empty($datos_personales['departamento'])): ?>
                                                    <dt class="col-sm-4">Departamento:</dt>
                                                    <dd class="col-sm-8"><?=htmlspecialchars($datos_personales['departamento']);?></dd>
                                                    <?php endif; ?>
                                                    
                                                    <?php if(isset($datos_personales['direccion']) && !empty($datos_personales['direccion'])): ?>
                                                    <dt class="col-sm-4">Dirección:</dt>
                                                    <dd class="col-sm-8"><?=htmlspecialchars($datos_personales['direccion']);?></dd>
                                                    <?php endif; ?>
                                                </dl>
                                            <?php else: ?>
                                                <div class="alert alert-info">
                                                    <i class="fas fa-info-circle"></i>
                                                    Este usuario no tiene información adicional registrada.
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Sección de estadísticas o información adicional -->
                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <div class="card card-default">
                                        <div class="card-header">
                                            <h4 class="card-title">
                                                <i class="fas fa-chart-line"></i> Resumen
                                            </h4>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-4 col-sm-6">
                                                    <div class="small-box bg-gradient-info">
                                                        <div class="inner">
                                                            <h4>Cuenta</h4>
                                                            <p>Usuario <?=$estado_text;?></p>
                                                        </div>
                                                        <div class="icon">
                                                            <i class="fas fa-user-check"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="col-md-4 col-sm-6">
                                                    <div class="small-box bg-gradient-success">
                                                        <div class="inner">
                                                            <h4>Rol</h4>
                                                            <p><?=htmlspecialchars($nombre_rol ?? 'Sin rol');?></p>
                                                        </div>
                                                        <div class="icon">
                                                            <i class="fas fa-user-tag"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="col-md-4 col-sm-6">
                                                    <div class="small-box bg-gradient-warning">
                                                        <div class="inner">
                                                            <h4>Registro</h4>
                                                            <p>
                                                                <?php if($fyh_creacion_display != 'No registrada'): ?>
                                                                <?=date('d/m/Y', strtotime($fyh_creacion_display));?>
                                                                <?php else: ?>
                                                                <?=$fyh_creacion_display;?>
                                                                <?php endif; ?>
                                                            </p>
                                                        </div>
                                                        <div class="icon">
                                                            <i class="fas fa-calendar-alt"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Botones de acción SIMPLIFICADOS -->
                            <div class="row mt-4">
                                <div class="col-md-12">
                                    <div class="card">
                                        <div class="card-footer text-right">
                                            <div class="btn-group" role="group">
                                                <a href="<?=APP_URL;?>/admin/usuarios" class="btn btn-secondary">
                                                    <i class="fas fa-arrow-left"></i> Volver a la lista
                                                </a>
                                                <a href="<?=APP_URL;?>/admin/usuarios/edit.php?id=<?=$id_usuario;?>" class="btn btn-primary">
                                                    <i class="fas fa-edit"></i> Editar Usuario
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include ('../../admin/layout/parte2.php');
include ('../../layout/mensajes.php');
?>