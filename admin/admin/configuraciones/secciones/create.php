<?php
include ('../../../app/config.php');
include ('../../../admin/layout/parte1.php');

// Obtener los grados
$sql_grados = "SELECT * FROM grados";
$query_grados = $pdo->prepare($sql_grados);
$query_grados->execute();
$grados = $query_grados->fetchAll(PDO::FETCH_ASSOC);

// Obtener el periodo académico activo
$sql_gestiones = "SELECT * FROM gestiones WHERE estado = 1 ORDER BY desde DESC LIMIT 1";
$query_gestiones = $pdo->prepare($sql_gestiones);
$query_gestiones->execute();
$gestion_activa = $query_gestiones->fetch(PDO::FETCH_ASSOC);
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <br>
    <div class="content">
        <div class="container">
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">Crear nueva Sección</h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="<?=APP_URL;?>/admin">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="<?=APP_URL;?>/admin/configuraciones/secciones">Secciones</a></li>
                                <li class="breadcrumb-item active">Crear nueva Sección</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <br>
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-primary">
                        <div class="card-body">
                            <form action="<?=APP_URL;?>/app/controllers/secciones/create.php" method="post">
                                <div class="row">
                                    <!-- Turno -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="">Turno</label>
                                            <select name="turno" id="turno" class="form-control" required>
                                                <option value="">Seleccione un turno</option>
                                                <option value="Mañana">Mañana</option>
                                                <option value="Tarde">Tarde</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Cupos -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="">Cupos</label>
                                            <input type="number" name="capacidad" class="form-control" required min="1" placeholder="Ejemplo: 30">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <!-- Grado -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="">Grado</label>
                                            <select name="id_grado" id="id_grado" class="form-control" required>
                                                <option value="">Seleccione un grado</option>
                                                <?php foreach ($grados as $grado) { ?>
                                                    <option value="<?=$grado['id_grado'];?>"><?=$grado['grado'];?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Periodo Académico -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="">Periodo Académico</label>
                                            <?php if ($gestion_activa): ?>
                                                <input type="text" class="form-control" 
                                                       value="Desde: <?=$gestion_activa['desde'];?> Hasta: <?=$gestion_activa['hasta'];?>" readonly>
                                                <input type="hidden" name="id_gestion" value="<?=$gestion_activa['id_gestion'];?>">
                                            <?php else: ?>
                                                <input type="text" class="form-control" value="No hay periodo activo" readonly>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <!-- Sección -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="">Sección</label>
                                            <input type="text" name="nombre_seccion" id="nombre_seccion" 
                                                class="form-control" required placeholder="Ejemplo: A, B, C"
                                                pattern="[A-Z]{1}" title="Solo una letra mayúscula (A-Z)"
                                                oninput="this.value = this.value.toUpperCase();">
                                        </div>
                                    </div>
                                </div>

                                <hr>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group text-center">
                                            <button type="submit" class="btn btn-primary">Registrar</button>
                                            <a href="<?=APP_URL;?>/admin/configuraciones/secciones" class="btn btn-secondary">Cancelar</a>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div><!-- /.container -->
    </div><!-- /.content -->
</div><!-- /.content-wrapper -->

<?php
include ('../../../admin/layout/parte2.php');
include ('../../../layout/mensajes.php');
?>
