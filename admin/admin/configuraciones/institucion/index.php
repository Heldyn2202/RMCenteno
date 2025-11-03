<?php
include ('../../../app/config.php');
include ('../../../admin/layout/parte1.php');

include ('../../../app/controllers/configuraciones/institucion/listado_de_instituciones.php');

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
            <h1 class="m-0">Datos de la Institución</h1>
          </div><!-- /.col -->
            <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Dashboard</a></li>
              <li class="breadcrumb-item"><a href="<?=APP_URL;?>/admin/configuraciones">Configuraciones</a></li>
              <li class="breadcrumb-item">Datos de la institución</li>
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
                                
                        </div>
                        <div class="card-body">
                            <table id="example1" class="table table-striped table-bordered table-hover table-sm">
                                <thead>
                                <tr>
                                    <th><center>N°</center></th>
                                    <th><center>Institución</center></th>
                                    <th><center>Logo</center></th>
                                    <th><center>Dirección</center></th>
                                    <th><center>Teléfono</center></th>
                                    <th><center>Celular</center></th>
                                    <th><center>Correo Electrónico</center></th>
                                    <th><center>Acciones</center></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                $contador_institucion = 0;
                                foreach ($instituciones as $institucione){
                                    $id_config_institucion = $institucione['id_config_institucion'];
                                    $contador_institucion = $contador_institucion +1; ?>
                                    <tr>
                                        <td class="text-center"><?=$contador_institucion;?></td>
                                        <td class="text-center"><?=$institucione['nombre_institucion'];?></td>
                                        <td class="text-center">
                                            <img src="<?=APP_URL."/public/images/configuracion/".$institucione['logo'];?>" width="100px" alt="">
                                        </td>
                                        <td class="text-center"><?=$institucione['direccion'];?></td>
                                        <td class="text-center"><?=$institucione['telefono'];?></td>
                                        <td class="text-center"><?=$institucione['celular'];?></td>
                                        <td class="text-center"><?=$institucione['correo'];?></td>
                                        <td class="text-center">
                                            <div class="btn-group" role="group" aria-label="Basic example">
                                                <a href="show.php?id=<?=$id_config_institucion;?>" type="button" class="btn btn-info btn-sm"><i class="bi bi-eye"></i></a>
                                                <a href="edit.php?id=<?=$id_config_institucion;?>" type="button" class="btn btn-success btn-sm"><i class="bi bi-pencil"></i></a>
                                                <form action="<?=APP_URL;?>/app/controllers/configuraciones/institucion/delete.php" onclick="preguntar<?=$id_config_institucion;?>(event)" method="post" id="miFormulario<?=$id_config_institucion;?>">
                                                    <input type="text" name="id_config_institucion" value="<?=$id_config_institucion;?>" hidden>
                                                    
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

<?php

include ('../../../admin/layout/parte2.php');
include ('../../../layout/mensajes.php');

?>

<script>
    $(function () {
        $("#example1").DataTable({
            "pageLength": 5,
            "language": {
                "emptyTable": "No hay información",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ Instituciones",
                "infoEmpty": "Mostrando 0 a 0 de 0 Instituciones",
                "infoFiltered": "(Filtrado de _MAX_ total Instituciones)",
                "infoPostFix": "",
                "thousands": ",",
                "lengthMenu": "Mostrar _MENU_ Instituciones",
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