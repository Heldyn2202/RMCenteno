<?php
include ('../../../app/config.php');
include ('../../../admin/layout/parte1.php');

// Directorio de backups
$backup_dir = 'backups/';
$backups = glob($backup_dir . '*.sql');
?>

<div class="content-wrapper">
    <br>
    <div class="content">
        <div class="container">
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">Listado de Backups</h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="#">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="<?=APP_URL;?>/admin/configuraciones">Configuraciones</a></li>
                                <li class="breadcrumb-item">Backups</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <br>
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <div class="card-tools">
                                <a href="backup.php" class="btn btn-primary"><i class="bi bi-plus-square"></i> Generar Backup</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <table id="example1" class="table table-striped table-bordered table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th><center>N°</center></th>
                                        <th><center>Nombre del Archivo</center></th>
                                        <th><center>Tamaño</center></th>
                                        <th><center>Fecha de Creación</center></th>
                                        <th><center>Acciones</center></th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                $contador = 0;
                                foreach ($backups as $backup) {
                                    $contador++;
                                    $file_name = basename($backup);
                                    $file_size = round(filesize($backup) / 1024, 2) . ' KB'; // Tamaño en KB
                                    $file_date = date("d/m/Y H:i:s", filemtime($backup));
                                    ?>
                                    <tr>
                                        <td class="text-center"><?=$contador;?></td>
                                        <td class="text-center"><?=$file_name;?></td>
                                        <td class="text-center"><?=$file_size;?></td>
                                        <td class="text-center"><?=$file_date;?></td>
                                        <td class="text-center">
                                            <div class="btn-group" role="group" aria-label="Basic example">
                                                <a href="download_backup.php?file=<?=$file_name;?>" class="btn btn-success btn-sm"><i class="bi bi-download"></i> Descargar</a>
                                                <a href="delete_backup.php?file=<?=$file_name;?>" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i> Eliminar</a>
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
include ('../../../admin/layout/parte2.php');
?>
<script>
    $(function () {
        $("#example1").DataTable({
            "pageLength": 5,
            "language": {
                "emptyTable": "No hay información",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ Backups",
                "infoEmpty": "Mostrando 0 a 0 de 0 Backups",
                "infoFiltered": "(Filtrado de _MAX_ total Backups)",
                "infoPostFix": "",
                "thousands": ",",
                "lengthMenu": "Mostrar _MENU_ Backups",
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
            "responsive": true, 
            "lengthChange": true, 
            "autoWidth": false,
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

    $(document).ready(function() {
        $('a.btn-success').click(function(e) {
            e.preventDefault();
            var downloadUrl = $(this).attr('href');

            // Mostrar una barra de progreso
            var progressBar = $('<div class="progress"><div class="progress-bar" role="progressbar" style="width: 0%;"></div></div>');
            $(this).closest('td').append(progressBar);

            // Iniciar la descarga
            var xhr = new XMLHttpRequest();
            xhr.open('GET', downloadUrl, true);
            xhr.responseType = 'blob';

            xhr.onprogress = function(e) {
                if (e.lengthComputable) {
                    var percentComplete = (e.loaded / e.total) * 100;
                    progressBar.find('.progress-bar').css('width', percentComplete + '%');
                }
            };

            xhr.onload = function() {
                if (this.status === 200) {
                    var blob = new Blob([this.response], { type: 'application/octet-stream' });
                    var link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = downloadUrl.split('/').pop();
                    link.click();
                    progressBar.remove();
                    Swal.fire({
                        icon: 'success',
                        title: 'Descarga completada',
                        text: 'El archivo se ha descargado correctamente.',
                    });
                }
            };

            xhr.send();
        });
    });

    // Función para mostrar SweetAlert basado en los parámetros de la URL
function showSweetAlert() {
    const urlParams = new URLSearchParams(window.location.search);
    const success = urlParams.get('success');
    const error = urlParams.get('error');

    const alerts = {
        success: {
            '1': {
                icon: 'success',
                title: 'Éxito',
                text: 'El archivo de backup se eliminó correctamente.',
            }
        },
        error: {
            '1': {
                icon: 'error',
                title: 'Error',
                text: 'No se pudo eliminar el archivo de backup.',
            },
            '2': {
                icon: 'error',
                title: 'Error',
                text: 'El archivo no existe.',
            },
            '3': {
                icon: 'error',
                title: 'Error',
                text: 'No se proporcionó un archivo.',
            }
        }
    };

    if (success && alerts.success[success]) {
        Swal.fire(alerts.success[success]);
    } else if (error && alerts.error[error]) {
        Swal.fire(alerts.error[error]);
    }
}

// Llamar a la función cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', showSweetAlert);

    // Llamar a la función cuando la página se cargue
    window.onload = showSweetAlert;
</script>