<?php  
$id_gestion = $_GET['id'];  
include ('../../../app/config.php');  
include ('../../../admin/layout/parte1.php');  
include ('../../../app/controllers/configuraciones/gestion/datos_gestion.php');  
?>  

<!-- Content Wrapper. Contains page content -->  
<div class="content-wrapper">  
    <br>  
    <div class="content">  
        <div class="container">  
            <div class="content-header">  
                <div class="container-fluid">  
                    <div class="row mb-2">  
                        <div class="col-sm-4">  
                            <h1 class="m-0">Editar Periodo académico</h1>  
                        </div><!-- /.col -->  
                        <div class="col-sm-8">  
                            <ol class="breadcrumb float-sm-right">  
                                <li class="breadcrumb-item"><a href="#">Inicio</a></li>  
                                <li class="breadcrumb-item"><a href="<?=APP_URL;?>/admin/configuraciones">Configuraciones</a></li>  
                                <li class="breadcrumb-item"><a href="<?=APP_URL;?>/admin/configuraciones/gestion">Periodo académico</a></li>  
                            </ol>  
                        </div><!-- /.col -->  
                    </div><!-- /.row -->  
                </div><!-- /.container-fluid -->  
            </div>  
            <br>  
            <div class="row">  
                <div class="col-md-12">  
                    <div class="card card-outline card-success">  
                        <div class="card-body">  
                            <form id="periodoForm" action="<?=APP_URL;?>/app/controllers/configuraciones/gestion/update.php" method="post">  
                                <div class="row">  
                                    <div class="col-md-12">  
                                        <div class="form-group">  
                                            <input type="text" name="id_gestion" value="<?=$id_gestion;?>" hidden>  
                                            <label for="">Desde</label>  
                                            <input type="date" value="<?=$desde;?>" name="desde" id="desde" class="form-control" required>  
                                            <small id="desdeFormatted" class="form-text text-muted"></small>  
                                        </div>  
                                    </div>  
                                </div>  
                                <div class="row">  
                                    <div class="col-md-12">  
                                        <div class="form-group">  
                                            <label for="">Hasta</label>  
                                            <input type="date" value="<?=$hasta;?>" name="hasta" id="hasta" class="form-control" required>  
                                            <small id="hastaFormatted" class="form-text text-muted"></small>  
                                        </div>  
                                    </div>  
                                </div>  
                                <div class="row">  
                                    <div class="col-md-12">  
                                        <div class="form-group">  
                                            <label for="">Estatus</label>  
                                            <select name="estado" id="" class="form-control">  
                                                <option value="ACTIVO" <?= $estado == "1" ? 'selected' : ''; ?>>ACTIVO</option>  
                                                <option value="INACTIVO" <?= $estado == "0" ? 'selected' : ''; ?>>INACTIVO</option>  
                                            </select>  
                                        </div>  
                                    </div>  
                                </div>  
                                <hr>  
                                <div class="row">  
                                    <div class="col-md-12">  
                                        <div class="form-group">  
                                            <center>  
                                                <button type="button" class="btn btn-success" id="btnActualizar">Actualizar</button>  
                                                <a href="<?=APP_URL;?>/admin/configuraciones/gestion" class="btn btn-secondary">Cancelar</a>  
                                            </center>  
                                        </div>  
                                    </div>  
                                </div>  
                            </form>  
                        </div>  
                    </div>  
                </div>  
            </div>  
        </div><!-- /.container-fluid -->  
    </div>  
    <!-- /.content -->  
</div>  
<!-- /.content-wrapper -->  

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>  
    // Función para formatear la fecha
    function formatDate(date) {
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0'); // Los meses son 0-indexados
        const year = date.getFullYear();
        return `${day}/${month}/${year}`;
    }

    // Actualizar la fecha formateada al seleccionar una fecha
    document.getElementById('desde').addEventListener('change', function() {
        const selectedDate = new Date(this.value);
        document.getElementById('desdeFormatted').innerText = formatDate(selectedDate);
    });

    document.getElementById('hasta').addEventListener('change', function() {
        const selectedDate = new Date(this.value);
        document.getElementById('hastaFormatted').innerText = formatDate(selectedDate);
    });

    document.getElementById('btnActualizar').addEventListener('click', function() {
        const desde = new Date(document.getElementById('desde').value);
        const hasta = new Date(document.getElementById('hasta').value);
        const diffTime = hasta - desde; // Diferencia en milisegundos
        const diffDays = diffTime / (1000 * 60 * 60 * 24); // Convertir a días

        // Validar que "Desde" no sea mayor que "Hasta"
        if (desde > hasta) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: "La fecha 'Desde' no puede ser mayor que la fecha 'Hasta'."
            });
            return;
        }

        // Validar que la duración sea al menos 285 días
        if (diffDays < 285) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: "La duración del periodo académico debe ser de al menos 285 días."
            });
            return;
        }

        // Validar que la duración no exceda un año (365 días)
        if (diffDays > 365) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: "La duración del periodo académico no puede exceder un año (365 días)."
            });
            return;
        }

        Swal.fire({
            title: '¿Estás seguro?',
            text: "¿Deseas actualizar este periodo académico?",            
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, actualizar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Si el usuario confirma, se envía el formulario
                document.getElementById('periodoForm').submit();
            }
        });
    });
</script>  

<?php  
include ('../../../admin/layout/parte2.php');  
include ('../../../layout/mensajes.php');  
?>