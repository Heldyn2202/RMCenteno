<?php
include ('../../app/config.php');
include ('../../admin/layout/parte1.php');
include ('../../app/controllers/administrativos/listado_de_administrativos.php');
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
                            <h1 class="m-0">Lista de administrativos</h1>
                        </div><!-- /.col -->
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="#">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="<?=APP_URL;?>/admin/administrativos">Administrativos</a></li>
                                <li class="breadcrumb-item active">Lista de administrativos</li>
                            </ol>
                        </div><!-- /.col -->
                    </div><!-- /.row -->
                </div><!-- /.container-fluid -->
            </div>
            <br>
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <div class="card-tools">
                                <a href="create.php" class="btn btn-primary"><i class="bi bi-plus-square"></i> Registrar nuevo administrativo</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <table id="example1" class="table table-striped table-bordered table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th><center>N°</center></th>
                                        <th><center>Nombres del usuario</center></th>
                                        <th><center>Cédula</center></th>
                                        <th><center>Fecha de nacimiento</center></th>
                                        <th><center>Correo Electrónico</center></th>
                                        <th><center>Estado</center></th>
                                        <th><center>Acciones</center></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $contador_administrativos = 0;
                                    foreach ($administrativos as $administrativo) {
                                        $id_administrativo = $administrativo['id_administrativo'];
                                        $contador_administrativos++;
                                    ?>
                                        <tr>
                                            <td style="text-align: center"><?=$contador_administrativos;?></td>
                                            <td><?=$administrativo['nombres']." ".$administrativo['apellidos'];?></td>
                                            <td>
                                                <?php 
                                                // Formatear la cédula a XX-XXX-XXX
                                                $cedula_formateada = substr($administrativo['ci'], 0, 2) . '-' . substr($administrativo['ci'], 2, 3) . '-' . substr($administrativo['ci'], 5); 
                                                ?>
                                                <?=$cedula_formateada;?>
                                            </td>
                                            <td style="text-align: center">
                                                <?php
                                                // Convertir la fecha a formato dd/mm/yyyy
                                                $fecha_nacimiento = new DateTime($administrativo['fecha_nacimiento']);
                                                echo $fecha_nacimiento->format('d/m/Y');
                                                ?>
                                            </td>
                                            <td><?=$administrativo['email'];?></td>
                                            <td class="text-center">
                                                <?php if ($administrativo['estado'] == "1") { ?>  
                                                    <button class="btn btn-success btn-sm" style="border-radius: 20px">ACTIVO</button>  
                                                <?php } else { ?>  
                                                    <button class="btn btn-danger btn-sm" style="border-radius: 20px">INACTIVO</button>  
                                                <?php } ?>  
                                            </td>
                                            <td style="text-align: center">
                                                <div class="btn-group" role="group" aria-label="Basic example">
                                                    <a href="show.php?id=<?=$id_administrativo;?>" type="button" class="btn btn-info btn-sm"><i class="bi bi-eye"></i></a>
                                                    <a href="edit.php?id=<?=$id_administrativo;?>" type="button" class="btn btn-success btn-sm"><i class="bi bi-pencil"></i></a>
                                                                                                            <input type="hidden" name="id_administrativo" value="<?=$id_administrativo;?>">
                                                    <input type="hidden" name="action" value="<?=$administrativo['estado'] == "1" ? 'disable' : 'enable';?>"> <!-- Campo oculto para la acción -->
                                                    <button type="button" class="btn btn-<?= $administrativo['estado'] == "1" ? 'danger' : 'success'; ?> btn-sm" 
                                                            onclick="confirmAction(<?=$id_administrativo;?>, '<?= $administrativo['estado'] == "1" ? 'inhabilitar' : 'habilitar'; ?>')">
                                                        <?= $administrativo['estado'] == "1" ? '<i class="fa fa-user-times" aria-hidden="true"></i>' : '<i class="fa fa-user-plus" aria-hidden="true"></i>'; ?>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmAction(id, action) {
    const form = document.getElementById('form-' + id);
    Swal.fire({
        title: '¿Estás seguro?',
        text: "Deseas " + action + " este administrativo.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, continuar!',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit(); // Enviar el formulario si el usuario confirma
        }
    });
}
</script>

<?php
include ('../../admin/layout/parte2.php');
include ('../../layout/mensajes.php');
?>