<?php
// portal-cms/secciones/quienes-somos.php
// Obtener información principal
$info_result = mysqli_query($con, "SELECT * FROM quienes_somos LIMIT 1");
$info = $info_result ? mysqli_fetch_assoc($info_result) : null;

// Obtener equipo
$equipo_result = mysqli_query($con, "SELECT * FROM equipo_quienes_somos ORDER BY orden ASC");
$equipo = $equipo_result ? mysqli_fetch_all($equipo_result, MYSQLI_ASSOC) : [];
?>

<h4><i class="fas fa-university text-primary me-2"></i> Quiénes Somos</h4>
<p class="text-muted mb-4">Información institucional y equipo directivo.</p>

<!-- Tabs para las dos partes -->
<ul class="nav nav-tabs mb-4" id="quienesTabs">
    <li class="nav-item">
        <a class="nav-link active" data-bs-toggle="tab" href="#info">
            <i class="fas fa-info-circle"></i> Información
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#equipo">
            <i class="fas fa-users"></i> Equipo (<?php echo count($equipo); ?>)
        </a>
    </li>
</ul>

<div class="tab-content">
    <!-- TAB 1: Información -->
    <div class="tab-pane fade show active" id="info">
        <form action="quienes-somos/guardar_info.php" method="POST" id="formInfo">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Título Principal</label>
                    <input type="text" name="titulo" class="form-control" 
                           value="<?php echo $info ? htmlspecialchars($info['titulo']) : ''; ?>" required>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Subtítulo</label>
                    <input type="text" name="subtitulo" class="form-control"
                           value="<?php echo $info ? htmlspecialchars($info['subtitulo']) : ''; ?>">
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Contenido Principal</label>
                <textarea name="contenido" class="form-control summernote" rows="8"><?php 
                    echo $info ? htmlspecialchars($info['contenido']) : ''; 
                ?></textarea>
            </div>
            
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Misión</label>
                    <textarea name="mision" class="form-control summernote" rows="4"><?php 
                        echo $info ? htmlspecialchars($info['mision']) : ''; 
                    ?></textarea>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label class="form-label">Visión</label>
                    <textarea name="vision" class="form-control summernote" rows="4"><?php 
                        echo $info ? htmlspecialchars($info['vision']) : ''; 
                    ?></textarea>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label class="form-label">Valores</label>
                    <textarea name="valores" class="form-control summernote" rows="4"><?php 
                        echo $info ? htmlspecialchars($info['valores']) : ''; 
                    ?></textarea>
                </div>
            </div>
            
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <button type="submit" class="btn btn-save">
                    <i class="fas fa-save me-1"></i> Guardar Información
                </button>
            </div>
        </form>
    </div>
    
    <!-- TAB 2: Equipo -->
    <div class="tab-pane fade" id="equipo">
        <div class="row mb-4">
            <div class="col-md-6">
                <!-- Formulario para nuevo miembro -->
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="fas fa-user-plus"></i> Agregar Miembro</h5>
                    </div>
                    <div class="card-body">
                        <form action="quienes-somos/equipo_guardar.php" method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label">Nombre Completo</label>
                                <input type="text" name="nombre" class="form-control" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Cargo</label>
                                <input type="text" name="cargo" class="form-control" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Biografía</label>
                                <textarea name="biografia" class="form-control" rows="3"></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Foto (300x300px)</label>
                                <input type="file" name="foto" class="form-control" accept="image/*">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Orden de aparición</label>
                                <input type="number" name="orden" class="form-control" value="1" min="1">
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-save">Guardar Miembro</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <!-- Lista de miembros -->
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="fas fa-users"></i> Miembros del Equipo</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Foto</th>
                                        <th>Nombre</th>
                                        <th>Cargo</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($equipo as $miembro): ?>
                                    <tr>
                                        <td>
                                            <?php if(!empty($miembro['foto'])): ?>
                                            <img src="../uploads/equipo/<?php echo $miembro['foto']; ?>" 
                                                 class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
                                            <?php else: ?>
                                            <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center" 
                                                 style="width: 40px; height: 40px;">
                                                <i class="fas fa-user text-white"></i>
                                            </div>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($miembro['nombre']); ?></td>
                                        <td><?php echo htmlspecialchars($miembro['cargo']); ?></td>
                                        <td>
                                            <a href="quienes-somos/equipo_editar.php?id=<?php echo $miembro['id']; ?>" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="quienes-somos/equipo_eliminar.php?id=<?php echo $miembro['id']; ?>" 
                                               class="btn btn-sm btn-outline-danger"
                                               onclick="return confirm('¿Eliminar este miembro?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
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
    </div>
</div>