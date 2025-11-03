<?php
$seccion_id = $_GET['id'];
include ('../../../app/config.php');
include ('../../../admin/layout/parte1.php');
include ('../../../app/controllers/secciones/datos_secciones.php');

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
                            <h1 class="m-0">Editar Sección</h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="<?=APP_URL;?>/admin">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="<?=APP_URL;?>/admin/secciones">Secciones</a></li>
                                <li class="breadcrumb-item">Editar Sección</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <br>
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-success">
                        <div class="card-body">
                            <form id="formEditar" action="<?=APP_URL;?>/app/controllers/secciones/update.php" method="post">
                                <input type="hidden" name="id_seccion" value="<?=$seccion_id;?>">

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="">Turno</label>
                                            <select name="turno" class="form-control" required>
                                                <option value="Mañana" <?= $turno == "Mañana" ? 'selected' : ''; ?>>Mañana</option>
                                                <option value="Tarde" <?= $turno == "Tarde" ? 'selected' : ''; ?>>Tarde</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="">Cupos</label>
                                            <input value="<?=$capacidad;?>" type="number" name="capacidad" class="form-control" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="">Grado</label>
                                            <select name="id_grado" id="id_grado" class="form-control" required>
                                                <option value="">Seleccione un grado</option>
                                                <?php
                                                $sql_grados = "SELECT * FROM grados";
                                                $query_grados = $pdo->prepare($sql_grados);
                                                $query_grados->execute();
                                                $grados = $query_grados->fetchAll(PDO::FETCH_ASSOC);
                                                foreach ($grados as $grado) {
                                                    $selected = ($id_grado == $grado['id_grado']) ? 'selected' : '';
                                                    echo "<option value='{$grado['id_grado']}' $selected>{$grado['grado']}</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="">Periodo Académico</label>
                                            <?php if ($gestion_activa): ?>
                                                <input type="text" class="form-control" value="Desde: <?=$gestion_activa['desde'];?> Hasta: <?=$gestion_activa['hasta'];?>" readonly>
                                                <input type="hidden" name="id_gestion" id="id_gestion" value="<?=$gestion_activa['id_gestion'];?>">
                                            <?php else: ?>
                                                <input type="text" class="form-control" value="No hay periodo activo" readonly>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="">Nombre Sección</label>
                                            <input 
                                                type="text" 
                                                name="nombre_seccion" 
                                                id="nombre_seccion" 
                                                class="form-control" 
                                                value="<?=$nombre_seccion;?>" 
                                                required 
                                                placeholder="Escriba el nombre de la sección (A, B, C...)" 
                                                pattern="[A-Z]{1}" 
                                                title="Solo una letra mayúscula (A-Z)" 
                                                oninput="this.value = this.value.toUpperCase();">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="">Estado</label>
                                            <select name="estado" class="form-control" required>
                                                <option value="1" <?= $estado == "1" ? 'selected' : ''; ?>>ACTIVO</option>
                                                <option value="0" <?= $estado == "0" ? 'selected' : ''; ?>>INACTIVO</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group text-center">
                                            <button type="submit" class="btn btn-success">Actualizar</button>
                                            <a href="<?=APP_URL;?>/admin/configuraciones/secciones" class="btn btn-secondary">Cancelar</a>
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

<script>
// Validar en el frontend antes de enviar
document.getElementById('formEditar').addEventListener('submit', function(e) {
    const idGrado = document.getElementById('id_grado').value;
    const nombreSeccion = document.getElementById('nombre_seccion').value.toUpperCase();
    const idGestion = document.getElementById('id_gestion').value;
    const idSeccion = <?=$seccion_id;?>; // Para evitar validar contra sí misma

    e.preventDefault(); // Detenemos el envío hasta validar

    fetch('<?=APP_URL;?>/app/controllers/secciones/verificar_duplicado.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `id_grado=${idGrado}&nombre_seccion=${nombreSeccion}&id_gestion=${idGestion}&id_seccion=${idSeccion}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.existe) {
            alert(`⚠️ Ya existe la sección ${nombreSeccion} en este grado para el periodo activo.`);
        } else {
            this.submit(); // Si no hay duplicado, ahora sí enviamos el formulario
        }
    })
    .catch(error => {
        console.error('Error en la validación:', error);
        this.submit(); // En caso de error, deja enviar (para no bloquear el proceso)
    });
});
</script>

<?php
include('../../../admin/layout/parte2.php');
include ('../../../layout/mensajes.php');
?>
