<?php  
include ('../../../app/config.php');  
include ('../../../admin/layout/parte1.php');  
include ('../../../app/controllers/grados/listado_de_grados.php');  
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
                            <h1 class="m-0">Lista de Grados</h1>  
                        </div><!-- /.col -->  
                        <div class="col-sm-6">  
                            <ol class="breadcrumb float-sm-right">  
                                <li class="breadcrumb-item"><a href="<?=APP_URL;?>/admin">Dashboard</a></li>  
                                <li class="breadcrumb-item"><a href="<?=APP_URL;?>/admin/configuraciones/grados/index.php">Grados</a></li>  
                                <li class="breadcrumb-item">Lista de Grados</li>  
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
                                <a href="create.php" class="btn btn-primary"><i class="bi bi-plus-square"></i> Crear nuevo Grado</a>  
                            </div>  
                        </div>  
                        <div class="card-body">  
                            <table id="example1" class="table table-striped table-bordered table-hover table-sm">  
                                <thead>  
                                    <tr>  
                                        <th><center>N°</center></th>  
                                        <th><center>Nivel Académico</center></th>  
                                        <th><center>Grado</center></th>  
                                        <th><center>Estado</center></th>  
                                        <th><center>Acciones</center></th>  
                                    </tr>  
                                </thead>  
                                <tbody>  
                                <?php  
                                $contador_grados = 0;  
                                foreach ($grados as $grado) {  
                                    $grado_id = $grado['id_grado']; // Asegúrate de que este sea el nombre correcto de la columna  
                                    $contador_grados++; ?>  
                                    <tr>  
                                        <td style="text-align: center"><?=$contador_grados;?></td>  
                                        <td style="text-align: center;"><?=$grado['nivel'];?></td>  
                                        <td style="text-align: center;"><?=$grado['grado'];?></td> <!-- Cambia 'curso' por 'grado' -->  
                                        <td class="text-center">  
                                            <?php if ($grado['estado'] == "1") { ?>  
                                                <button class="btn btn-success btn-sm" style="border-radius: 20px">ACTIVO</button>  
                                            <?php } else { ?>  
                                                <button class="btn btn-danger btn-sm" style="border-radius: 20px">INACTIVO</button>  
                                            <?php } ?>  
                                        </td>  
                                        <td style="text-align: center">  
                                            <div class="btn-group" role="group" aria-label="Basic example">  
                                                <a href="show.php?id=<?=$grado_id;?>" type="button" class="btn btn- btn-sm"><i class="bi bi-eye"></i></a>  
                                                <a href="edit.php?id=<?=$grado_id;?>" type="button" class="btn btn- btn-sm"><i class="bi bi-pencil"></i></a>  
                                                <form action="<?=APP_URL;?>/app/controllers/grados/delete.php" method="post" style="display:inline;" id="form-<?=$grado_id;?>">  
                                                    <input type="hidden" name="grado_id" value="<?=$grado_id;?>">  
                                                    <input type="hidden" name="action" value="<?=$grado['estado'] ? 'disable' : 'enable';?>"> <!-- Campo oculto para la acción -->  
                                                    <button type="button" class="btn btn-<?= $grado['estado'] ? 'danger' : 'success'; ?> btn-sm"   
                                                            onclick="confirmAction(<?=$grado_id;?>, '<?= $grado['estado'] ? 'inhabilitar' : 'habilitar'; ?>')">  
                                                        <?= $grado['estado'] ? 'Inhabilitar' : 'Habilitar'; ?>  
                                                    </button>  
                                                </form>  
                                            </div>  
                                        </td>  
                                    </tr>  
                                <?php } ?>  
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
        text: "Deseas " + action + " este grado.",  
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
include ('../../../admin/layout/parte2.php');  
include ('../../../layout/mensajes.php');  
?>

<script>
    $(function () {
        $("#example1").DataTable({
            "pageLength": 10,
            "language": {
                "emptyTable": "No hay información",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ Grados ",
                "infoEmpty": "Mostrando 0 a 0 de 0 Grados ",
                "infoFiltered": "(Filtrado de _MAX_ total Grados )",
                "infoPostFix": "",
                "thousands": ",",
                "lengthMenu": "Mostrar _MENU_ Grados ",
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