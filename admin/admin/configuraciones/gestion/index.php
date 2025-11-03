<?php
include ('../../../app/config.php');
include ('../../../admin/layout/parte1.php');

include ('../../../app/controllers/configuraciones/gestion/listado_de_gestiones.php');

// Ordenar los registros por estado (activos primero, inactivos después)  
$query = $pdo->prepare('SELECT * FROM gestiones ORDER BY estado DESC');  
$query->execute();  
$gestiones = $query->fetchAll(PDO::FETCH_ASSOC); 
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
                            <h1 class="m-0">Periodo académico</h1>
                        </div><!-- /.col -->
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="#">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="<?=APP_URL;?>/admin/configuraciones">Configuraciones</a></li>
                                <li class="breadcrumb-item">Periodo académico</li>
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
                                <a href="create.php" class="btn btn-primary"><i class="bi bi-plus-square"></i> Crear nuevo periodo académico</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <table id="example1" class="table table-striped table-bordered table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th><center>N°</center></th>
                                        <th><center>Desde</center></th>
                                        <th><center>Hasta</center></th>
                                        <th><center>Estado</center></th>
                                        <th><center>Acciones</center></th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                $contador_gestiones = 0;
                                foreach ($gestiones as $gestione){
                                    $id_gestion = $gestione['id_gestion'];
                                    $contador_gestiones++;

                                    // Formatea las fechas
                                    $fechaDesde = DateTime::createFromFormat('Y-m-d', $gestione['desde'])->format('d/m/Y');
                                    $fechaHasta = DateTime::createFromFormat('Y-m-d', $gestione['hasta'])->format('d/m/Y');
                                    ?>
                                    <tr>
                                        <td class="text-center"><?=$contador_gestiones;?></td>
                                        <td class="text-center"><?=$fechaDesde;?></td>
                                        <td class="text-center"><?=$fechaHasta;?></td>
                                        <td class="text-center">
                                            <?php
                                            if($gestione['estado'] == "1"){ ?>
                                                <button class="btn btn-success btn-sm" style="border-radius: 20px">ACTIVO</button>
                                            <?php
                                            } else { ?>
                                                <button class="btn btn-danger btn-sm" style="border-radius: 20px">INACTIVO</button>
                                            <?php
                                            }
                                            ?>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group" role="group" aria-label="Basic example">
                                                <a href="show.php?id=<?=$id_gestion;?>" type="button" class="btn btn-info btn-sm"><i class="bi bi-eye"></i></a>
                                                <a href="edit.php?id=<?=$id_gestion;?>" type="button" class="btn btn-success btn-sm"><i class="bi bi-pencil"></i></a>
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
                "info": "Mostrando _START_ a _END_ de _TOTAL_ Gestiones",
                "infoEmpty": "Mostrando 0 a 0 de 0 Periodo académico",
                "infoFiltered": "(Filtrado de _MAX_ total Gestiones)",
                "infoPostFix": "",
                "thousands": ",",
                "lengthMenu": "Mostrar _MENU_ Periodo académico",
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