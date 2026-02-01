<?php
// portal-cms/secciones/inicio.php
// Obtener datos del carrusel actual
$result = mysqli_query($con, "SELECT * FROM carrusel ORDER BY orden ASC");
$carrusel_items = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<h4><i class="fas fa-images text-primary me-2"></i> Carrusel Principal</h4>
<p class="text-muted mb-4">Administra las imágenes del carrusel en la página de inicio.</p>

<div class="row mb-4">
    <div class="col-md-6">
        <!-- Formulario para agregar nueva imagen -->
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="fas fa-plus-circle"></i> Agregar Nueva Imagen</h5>
            </div>
            <div class="card-body">
                <form action="inicio/guardar_imagen.php" method="POST" enctype="multipart/form-data" id="formCarrusel">
                    <div class="mb-3">
                        <label class="form-label">Título</label>
                        <input type="text" name="titulo" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea name="descripcion" class="form-control" rows="2"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Imagen (1920x600px recomendado)</label>
                        <input type="file" name="imagen" class="form-control" accept="image/*" required
                               onchange="previewImage(this, 'previewNueva')">
                        <div class="preview-box mt-2" id="previewNueva">
                            <small class="text-muted">Vista previa aparecerá aquí</small>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Enlace (opcional)</label>
                        <input type="text" name="enlace" class="form-control" placeholder="https://...">
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-save">
                            <i class="fas fa-save me-1"></i> Guardar Imagen
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <!-- Imágenes existentes -->
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="fas fa-list"></i> Imágenes Actuales (<?php echo count($carrusel_items); ?>)</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Imagen</th>
                                <th>Título</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($carrusel_items as $item): ?>
                            <tr>
                                <td>
                                    <img src="../uploads/carrusel/<?php echo $item['imagen']; ?>" 
                                         class="img-thumbnail" style="width: 60px; height: 40px; object-fit: cover;">
                                </td>
                                <td><?php echo substr($item['titulo'], 0, 30); ?>...</td>
                                <td>
                                    <a href="inicio/editar.php?id=<?php echo $item['id']; ?>" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="inicio/eliminar.php?id=<?php echo $item['id']; ?>" 
                                       class="btn btn-sm btn-outline-danger"
                                       onclick="return confirm('¿Eliminar esta imagen?')">
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