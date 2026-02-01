<?php
// portal-cms/secciones/noticias.php
// Obtener noticias
$result = mysqli_query($con, "SELECT * FROM tblposts ORDER BY fecha_publicacion DESC LIMIT 20");
$noticias = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<h4><i class="fas fa-newspaper text-primary me-2"></i> Noticias</h4>
<p class="text-muted mb-4">Gestiona las noticias del portal escolar.</p>

<div class="row">
    <div class="col-md-4 mb-4">
        <!-- Formulario rápido -->
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="fas fa-plus-circle"></i> Nueva Noticia Rápida</h5>
            </div>
            <div class="card-body">
                <form action="noticias/guardar_rapido.php" method="POST" id="formNoticiaRapida">
                    <div class="mb-3">
                        <label class="form-label">Título *</label>
                        <input type="text" name="titulo" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Contenido breve</label>
                        <textarea name="contenido_breve" class="form-control" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Imagen destacada</label>
                        <input type="file" name="imagen_destacada" class="form-control" accept="image/*">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Categoría</label>
                        <select name="categoria" class="form-select">
                            <option value="general">General</option>
                            <option value="academico">Académico</option>
                            <option value="eventos">Eventos</option>
                            <option value="deportes">Deportes</option>
                            <option value="anuncios">Anuncios</option>
                        </select>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-save">
                            <i class="fas fa-paper-plane me-1"></i> Publicar
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Estadísticas -->
        <div class="card mt-3">
            <div class="card-body">
                <h6><i class="fas fa-chart-bar me-2"></i> Estadísticas</h6>
                <ul class="list-unstyled mt-3">
                    <li class="mb-2">
                        <span class="text-muted">Total noticias:</span>
                        <strong class="float-end"><?php echo count($noticias); ?></strong>
                    </li>
                    <li class="mb-2">
                        <span class="text-muted">Publicadas hoy:</span>
                        <strong class="float-end">0</strong>
                    </li>
                    <li>
                        <span class="text-muted">Borradores:</span>
                        <strong class="float-end">0</strong>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <!-- Lista de noticias -->
        <div class="card">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-list"></i> Últimas Noticias</h5>
                <a href="noticias/editor-completo.php" class="btn btn-sm btn-primary">
                    <i class="fas fa-edit me-1"></i> Editor Completo
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Título</th>
                                <th>Categoría</th>
                                <th>Fecha</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($noticias as $noticia): 
                                $fecha = date('d/m/Y', strtotime($noticia['fecha_publicacion']));
                            ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars(substr($noticia['titulo'], 0, 40)); ?>...</strong>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?php echo $noticia['categoria']; ?></span>
                                </td>
                                <td><?php echo $fecha; ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $noticia['estado'] == 'publicado' ? 'success' : 'warning'; ?>">
                                        <?php echo ucfirst($noticia['estado']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="noticias/editar.php?id=<?php echo $noticia['id']; ?>" 
                                           class="btn btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="../noticias.php?slug=<?php echo $noticia['slug']; ?>" 
                                           target="_blank" class="btn btn-outline-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="noticias/eliminar.php?id=<?php echo $noticia['id']; ?>" 
                                           class="btn btn-outline-danger"
                                           onclick="return confirm('¿Eliminar esta noticia?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
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