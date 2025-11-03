<?php
// crear_plantilla.php
include('../../app/config.php');
include('../../admin/layout/parte1.php');
?>

<div class="content-wrapper">
    <br>
    <div class="content">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Crear Plantilla de Carnet</h3>
                        </div>
                        <div class="card-body">
                            <form action="../../app/controllers/plantillas/guardar.php" method="post">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="nombre">Nombre de la Plantilla <span class="text-danger">*</span></label>
                                            <input type="text" name="nombre" id="nombre" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="descripcion">Descripción <span class="text-danger">*</span></label>
                                            <input type="text" name="descripcion" id="descripcion" class="form-control" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                     <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="ancho">Ancho (mm) <span class="text-danger">*</span></label>
                                            <input type="number" name="ancho" id="ancho" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="alto">Alto (mm) <span class="text-danger">*</span></label>
                                            <input type="number" name="alto" id="alto" class="form-control" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="estatus">Estado <span class="text-danger">*</span></label>
                                            <select name="estatus" id="estatus" class="form-control" required>
                                                <option value="activo">Activo</option>
                                                <option value="inactivo">Inactivo</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bi bi-save"></i> Guardar Plantilla
                                            </button>
                                            <a href="index.php" class="btn btn-secondary">
                                                <i class="bi bi-x-circle"></i> Cancelar
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include('../../admin/layout/parte2.php');
?>
