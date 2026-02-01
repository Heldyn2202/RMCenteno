<?php
// portal-cms/secciones/academico.php
// Obtener información académica
$result = mysqli_query($con, "SELECT * FROM contenido_paginas WHERE seccion = 'academico'");
$contenidos = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Obtener documentos
$docs_result = mysqli_query($con, "SELECT * FROM documentos WHERE seccion = 'academico' ORDER BY fecha_subida DESC");
$documentos = $docs_result ? mysqli_fetch_all($docs_result, MYSQLI_ASSOC) : [];
?>

<h4><i class="fas fa-graduation-cap text-primary me-2"></i> Área Académica</h4>
<p class="text-muted mb-4">Gestiona información académica, horarios, documentos y recursos.</p>

<!-- Tabs para las diferentes partes académicas -->
<ul class="nav nav-tabs mb-4" id="academicoTabs">
    <li class="nav-item">
        <a class="nav-link active" data-bs-toggle="tab" href="#infoAcademica">
            <i class="fas fa-info-circle"></i> Información
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#horarios">
            <i class="fas fa-clock"></i> Horarios
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#documentos">
            <i class="fas fa-file-pdf"></i> Documentos (<?php echo count($documentos); ?>)
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#cursos">
            <i class="fas fa-book"></i> Cursos
        </a>
    </li>
</ul>

<div class="tab-content">
    <!-- TAB 1: Información Académica -->
    <div class="tab-pane fade show active" id="infoAcademica">
        <form action="academico/guardar_info.php" method="POST">
            <div class="mb-3">
                <label class="form-label">Título de la Sección</label>
                <input type="text" name="titulo_seccion" class="form-control" value="Área Académica">
            </div>
            
            <div class="mb-3">
                <label class="form-label">Descripción General</label>
                <textarea name="descripcion_general" class="form-control summernote" rows="6">
                    <?php
                    // Buscar contenido existente
                    foreach($contenidos as $cont) {
                        if($cont['tipo'] == 'descripcion_general') {
                            echo htmlspecialchars($cont['contenido']);
                            break;
                        }
                    }
                    ?>
                </textarea>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Metodología Educativa</label>
                <textarea name="metodologia" class="form-control summernote" rows="6">
                    <?php
                    foreach($contenidos as $cont) {
                        if($cont['tipo'] == 'metodologia') {
                            echo htmlspecialchars($cont['contenido']);
                            break;
                        }
                    }
                    ?>
                </textarea>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Recursos Académicos</label>
                <textarea name="recursos" class="form-control summernote" rows="6">
                    <?php
                    foreach($contenidos as $cont) {
                        if($cont['tipo'] == 'recursos') {
                            echo htmlspecialchars($cont['contenido']);
                            break;
                        }
                    }
                    ?>
                </textarea>
            </div>
            
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <button type="submit" class="btn btn-save">
                    <i class="fas fa-save me-1"></i> Guardar Información Académica
                </button>
            </div>
        </form>
    </div>
    
    <!-- TAB 2: Horarios -->
    <div class="tab-pane fade" id="horarios">
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            Sube imágenes o PDFs con los horarios de clases.
        </div>
        
        <form action="academico/subir_horario.php" method="POST" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Título del Horario</label>
                    <input type="text" name="titulo" class="form-control" placeholder="Ej: Horario Primaria 2024">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nivel/Grado</label>
                    <select name="nivel" class="form-select">
                        <option value="">Seleccionar</option>
                        <option value="inicial">Inicial</option>
                        <option value="primaria">Primaria</option>
                        <option value="secundaria">Secundaria</option>
                        <option value="bachillerato">Bachillerato</option>
                    </select>
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Archivo (PDF o imagen)</label>
                <input type="file" name="archivo" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
            </div>
            
            <div class="d-grid">
                <button type="submit" class="btn btn-save">Subir Horario</button>
            </div>
        </form>
        
        <!-- Horarios existentes -->
        <div class="mt-4">
            <h6><i class="fas fa-list me-2"></i> Horarios Subidos</h6>
            <div class="list-group">
                <!-- Aquí mostrarías los horarios existentes -->
                <div class="list-group-item">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong>Horario Secundaria 2024</strong>
                            <div class="text-muted small">Nivel: Secundaria</div>
                        </div>
                        <div>
                            <a href="#" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-download"></i>
                            </a>
                            <a href="#" class="btn btn-sm btn-outline-danger">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- TAB 3: Documentos -->
    <div class="tab-pane fade" id="documentos">
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="fas fa-upload"></i> Subir Documento</h5>
                    </div>
                    <div class="card-body">
                        <form action="academico/subir_documento.php" method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label">Nombre del Documento *</label>
                                <input type="text" name="nombre" class="form-control" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Descripción</label>
                                <textarea name="descripcion" class="form-control" rows="2"></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Categoría</label>
                                <select name="categoria" class="form-select">
                                    <option value="programas">Programas de Estudio</option>
                                    <option value="reglamentos">Reglamentos</option>
                                    <option value="formatos">Formatos</option>
                                    <option value="guias">Guías</option>
                                    <option value="otros">Otros</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Archivo (PDF, DOC, DOCX) *</label>
                                <input type="file" name="archivo" class="form-control" accept=".pdf,.doc,.docx" required>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-save">Subir Documento</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="fas fa-folder"></i> Documentos Existentes</h5>
                    </div>
                    <div class="card-body">
                        <?php if(empty($documentos)): ?>
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-folder-open fa-3x mb-3"></i>
                                <p>No hay documentos subidos aún</p>
                            </div>
                        <?php else: ?>
                            <div class="list-group">
                                <?php foreach($documentos as $doc): ?>
                                <div class="list-group-item list-group-item-action">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong><?php echo htmlspecialchars($doc['nombre']); ?></strong>
                                            <div class="text-muted small">
                                                <?php 
                                                    echo date('d/m/Y', strtotime($doc['fecha_subida']));
                                                    echo ' • ' . strtoupper($doc['categoria']);
                                                ?>
                                            </div>
                                        </div>
                                        <div class="btn-group">
                                            <a href="../uploads/documentos/<?php echo $doc['archivo']; ?>" 
                                               target="_blank" class="btn btn-sm btn-outline-success">
                                                <i class="fas fa-download"></i>
                                            </a>
                                            <a href="academico/eliminar_documento.php?id=<?php echo $doc['id']; ?>" 
                                               class="btn btn-sm btn-outline-danger"
                                               onclick="return confirm('¿Eliminar este documento?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- TAB 4: Cursos -->
    <div class="tab-pane fade" id="cursos">
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            Esta sección está en desarrollo. Pronto podrás gestionar cursos y materias.
        </div>
        
        <div class="text-center py-5">
            <i class="fas fa-book fa-4x text-muted mb-3"></i>
            <h5 class="text-muted">Gestión de Cursos</h5>
            <p class="text-muted">Disponible próximamente</p>
        </div>
    </div>
</div>