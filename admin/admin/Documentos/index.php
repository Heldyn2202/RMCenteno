<?php
include ('../../app/config.php');
include ('../../admin/layout/parte1.php');
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
            <h1 class="m-0">Documentos</h1>
          </div><!-- /.col -->
            <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="<?=APP_URL;?>/admin">Dashboard</a></li>
              <li class="breadcrumb-item">Documentos</li>
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
                            <a class="btn btn-primary" data-toggle="modal" data-target="#agregar"><i class="fa fa-plus"></i> Añadir nuevo documento</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <table id="example1" class="table table-striped table-bordered table-hover table-sm">
                            <thead>
                  <th class="text-center">N°</th>
                  <th class="text-center">Documento</th>
                  <th class="text-center">Descripción</th>
                  <th class="text-center">Acciones</th>
                </thead>
                <tbody>
                <?php
                        require_once("includes/db.php");
                        $result = mysqli_query($conexion, "SELECT * FROM documento");
                        while ($fila = mysqli_fetch_assoc($result)) :
                        ?>
					<tr>
          <td class="text-center"><?php echo $fila['id']; ?></td>
          <td class="text-center"><?php echo $fila['archivo']; ?></td>
                                <td class="text-center"><?php echo $fila['descripcion']; ?></td>
                               
            <td class="text-center">
                                    <a href="includes/download.php?id=<?php echo $fila['id']; ?>" class="btn btn-primary btn-sm">
                                        <i class="fas fa-download"></i></a>
                                    <button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editar<?php echo $fila['id']; ?>">
                                        <i class="fa fa-edit "></i>
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#delete<?php echo $fila['id']; ?>">
                                        <i class="fa fa-trash "></i>
                                    </button>
                                </td>
                            </tr>
                            <?php include "includes/editar.php"; ?>
                            <?php include "includes/eliminar.php"; ?>
                            
                        <?php endwhile; ?>
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

<?php

include ('../../admin/layout/parte2.php');
include ('../../layout/mensajes.php');

?>
<?php include "agregar.php"; ?>
<script>
    $(function () {
        $("#example1").DataTable({
            "pageLength": 5,
            "language": {
                "emptyTable": "No hay información",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ Documentos",
                "infoEmpty": "Mostrando 0 a 0 de 0 Documentos",
                "infoFiltered": "(Filtrado de _MAX_ total Documentos)",
                "infoPostFix": "",
                "thousands": ",",
                "lengthMenu": "Mostrar _MENU_ Documentos",
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
        }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
    });
</script>