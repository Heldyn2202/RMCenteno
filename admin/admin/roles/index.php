<?php
include ('../../app/config.php');
include ('../../admin/layout/parte1.php');

include ('../../app/controllers/roles/listado_de_roles.php');
include ('../../app/controllers/roles/listado_de_permisos.php');
include ('../../app/controllers/roles/listado_de_roles_permisos.php');

function escapeOutput($value) {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <br>
    <div class="content">
        <div class="container">
            <div class="row">
                <h1>Listado de roles</h1>
            </div>
            <br>
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Roles registrados</h3>
                            <div class="card-tools">
                                <a href="create.php" class="btn btn-primary"><i class="bi bi-plus-square"></i> Crear nuevo rol</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <table id="example1" class="table table-striped table-bordered table-hover table-sm">
                                <thead>
                                <tr>
                                    <th><center>Nro</center></th>
                                    <th><center>Nombre del rol</center></th>
                                    <th><center>Acciones</center></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                $contador_rol = 0;
                                foreach ($roles as $role){
                                    $id_rol = $role['id_rol'];
                                    $contador_rol = $contador_rol +1; ?>
                                    <tr>
                                        <td style="text-align: center"><?=escapeOutput($contador_rol);?></td>
                                        <td><?=escapeOutput($role['nombre_rol']);?></td>
                                        <td style="text-align: center">
                                            <div class="btn-group" role="group" aria-label="Basic example">
                                                <button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#modal_asignacion<?=escapeOutput($id_rol);?>">
                                                    <i class="bi bi-check-circle"></i>
                                                </button>

                                                <div class="modal fade" id="modal_asignacion<?=escapeOutput($id_rol);?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                                    <div class="modal-dialog modal-lg">
                                                        <div class="modal-content">
                                                            <div class="modal-header" style="background-color: #dbcd59">
                                                                <h5 class="modal-title" id="exampleModalLabel">Asignación de roles</h5>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="row">
                                                                    <div class="col-md-3">
                                                                        <input type="text" name="rol_id" id="rol_id<?=escapeOutput($id_rol);?>" value="<?=escapeOutput($id_rol);?>" hidden>
                                                                        <label>Rol: <?=escapeOutput($role['nombre_rol']);?></label>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <select name="permiso_id" id="permiso_id<?=escapeOutput($id_rol);?>" class="form-control">
                                                                            <?php
                                                                            foreach ($permisos as $permiso){
                                                                                $id_permiso = $permiso['id_permiso']; ?>
                                                                                <option value="<?=escapeOutput($id_permiso);?>"><?=escapeOutput($permiso['nombre_url']);?></option>
                                                                                <?php
                                                                            }
                                                                            ?>
                                                                        </select>
                                                                    </div>
                                                                    <div class="col-md-3">
                                                                        <button type="submit" class="btn btn-primary mb-2" id="btn_reg<?=escapeOutput($id_rol);?>">Asignar</button>
                                                                    </div>
                                                                    <script>
                                                                        $('#btn_reg<?=escapeOutput($id_rol);?>').click(function () {
                                                                            var a = $('#rol_id<?=escapeOutput($id_rol);?>').val();
                                                                            var b = $('#permiso_id<?=escapeOutput($id_rol);?>').val();

                                                                            var url = "../../app/controllers/roles/create_roles_permisos.php";
                                                                            $.get(url,{rol_id:a,permiso_id:b},function (datos) {
                                                                                $('#respuesta<?=escapeOutput($id_rol);?>').html(datos);
                                                                                $('#tabla<?=escapeOutput($id_rol);?>').css('display','none');
                                                                                Swal.fire({
                                                                                    position: "top-end",
                                                                                    icon: "success",
                                                                                    title: "Se registro el permiso de la manera correcta en la base de datos",
                                                                                    showConfirmButton: false,
                                                                                    timer: 5000
                                                                                });
                                                                            });
                                                                        });
                                                                    </script>
                                                                </div>
                                                                <hr>
                                                                <div id="respuesta<?=escapeOutput($id_rol);?>"></div>
                                                                <div class="row" id="tabla<?=escapeOutput($id_rol);?>">
                                                                    <table class="table table-bordered table-sm table-striped table-hover">
                                                                        <tr>
                                                                            <th style="text-align: center;background-color: #dbcd59">Nro</th>
                                                                            <th style="text-align: center;background-color: #dbcd59">Rol</th>
                                                                            <th style="text-align: center;background-color: #dbcd59">Permiso</th>
                                                                            <th style="text-align: center;background-color: #dbcd59">Acción</th>
                                                                        </tr>
                                                                        <?php
                                                                        $contador = 0;
                                                                        foreach ($roles_permisos as $roles_permiso){
                                                                            if($id_rol == $roles_permiso['rol_id']){
                                                                                $id_rol_permiso = $roles_permiso['id_rol_permiso'];
                                                                                $contador = $contador + 1; ?>
                                                                                <tr>
                                                                                    <td><center><?=escapeOutput($contador);?></center></td>
                                                                                    <td><center><?=escapeOutput($roles_permiso['nombre_rol']);?></center></td>
                                                                                    <td><?=escapeOutput($roles_permiso['nombre_url']);?></td>
                                                                                    <td>
                                                                                        <form action="<?=escapeOutput(APP_URL);?>/app/controllers/roles/delete_rol_permiso.php" onclick="preguntar<?=escapeOutput($id_rol_permiso);?>(event)"
                                                                                              method="post" id="miFormulario<?=escapeOutput($id_rol_permiso);?>">
                                                                                            <input type="text" name="id_rol_permiso" value="<?=escapeOutput($id_rol_permiso);?>" hidden>
                                                                                            <button type="submit" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></button>
                                                                                        </form>
                                                                                        <script>
                                                                                            function preguntar<?=escapeOutput($id_rol_permiso);?>(event) {
                                                                                                event.preventDefault();
                                                                                                Swal.fire({
                                                                                                    title: 'Eliminar registro',
                                                                                                    text: '¿Desea eliminar este registro?',
                                                                                                    icon: 'question',
                                                                                                    showDenyButton: true,
                                                                                                    confirmButtonText: 'Eliminar',
                                                                                                    confirmButtonColor: '#a5161d',
                                                                                                    denyButtonColor: '#270a0a',
                                                                                                    denyButtonText: 'Cancelar',
                                                                                                }).then((result) => {
                                                                                                    if (result.isConfirmed) {
                                                                                                        var form = $('#miFormulario<?=escapeOutput($id_rol_permiso);?>');
                                                                                                        form.submit();
                                                                                                    }
                                                                                                });
                                                                                            }
                                                                                        </script>
                                                                                    </td>
                                                                                </tr>
                                                                                <?php
                                                                            }
                                                                        }
                                                                        ?>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <a href="show.php?id=<?=escapeOutput($id_rol);?>" type="button" class="btn btn-info btn-sm"><i class="bi bi-eye"></i></a>
                                                <a href="edit.php?id=<?=escapeOutput($id_rol);?>" type="button" class="btn btn-success btn-sm"><i class="bi bi-pencil"></i></a>
                                                <form action="<?=escapeOutput(APP_URL);?>/app/controllers/roles/delete.php" onclick="preguntar<?=escapeOutput($id_rol);?>(event)" method="post" id="miFormulario<?=escapeOutput($id_rol);?>">
                                                    <input type="text" name="id_rol" value="<?=escapeOutput($id_rol);?>" hidden>
                                                </form>
                                                <script>
                                                    function preguntar<?=escapeOutput($id_rol);?>(event) {
                                                        event.preventDefault();
                                                        Swal.fire({
                                                            title: 'Eliminar registro',
                                                            text: '¿Desea eliminar este registro?',
                                                            icon: 'question',
                                                            showDenyButton: true,
                                                            confirmButtonText: 'Eliminar',
                                                            confirmButtonColor: '#a5161d',
                                                            denyButtonColor: '#270a0a',
                                                            denyButtonText: 'Cancelar',
                                                        }).then((result) => {
                                                            if (result.isConfirmed) {
                                                                var form = $('#miFormulario<?=escapeOutput($id_rol);?>');
                                                                form.submit();
                                                            }
                                                        });
                                                    }
                                                </script>
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
        </div>
    </div>
</div>

<?php
include ('../../admin/layout/parte2.php');
include ('../../layout/mensajes.php');
?>

<script>
    $(function () {
        $("#example1").DataTable({
            "pageLength": 5,
            "language": {
                "emptyTable": "No hay información",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ Roles",
                "infoEmpty": "Mostrando 0 a 0 de 0 Roles",
                "infoFiltered": "(Filtrado de _MAX_ total Roles)",
                "infoPostFix": "",
                "thousands": ",",
                "lengthMenu": "Mostrar _MENU_ Roles",
                "loadingRecords": "Cargando...",
                "processing": "Procesando...",
                "search": "Buscador:",
                "zeroRecords": "Sin resultados encontrados",
                "paginate": {
                    "first": "Primero",
                    "last": "Ultimo",
                    "next": "Siguiente",
                    "previous": "Anterior"
                }
            },
            "responsive": true, "lengthChange": true, "autoWidth": false,
            buttons: [
                {
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel"></i> ',
                    titleAttr: 'Exportar a Excel',
                    className: 'btn btn-success',
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fas fa-file-pdf"></i> ',
                    titleAttr: 'Exportar a PDF',
                    className: 'btn btn-danger',
                },
                {
                    extend: 'print',
                    text: '<i class="fa fa-print"></i> ',
                    titleAttr: 'Imprimir',
                    className: 'btn btn-info',
                },
            ],
        }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
    });
</script>