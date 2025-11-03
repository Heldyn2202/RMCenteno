<?php
include ('../../../app/config.php');
include ('../../../admin/layout/parte1.php');
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
                            <h1 class="m-0">Periodo académico</h1>
                        </div><!-- /.col -->
                        <div class="col-sm-8">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="#">Dashboard</a></li>
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
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Ingrese los datos</h3>
                        </div>
                        <div class="card-body">
                            <form id="periodoForm" action="<?=APP_URL;?>/app/controllers/configuraciones/gestion/create.php" method="post">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="">Desde</label>
                                            <input type="date" name="desde" id="desde" class="form-control" required>
                                            <small id="desdeFormatted" class="form-text text-muted"></small> <!-- Para mostrar la fecha formateada -->
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="">Hasta</label>
                                            <input type="date" name="hasta" id="hasta" class="form-control" required>
                                            <small id="hastaFormatted" class="form-text text-muted"></small> <!-- Para mostrar la fecha formateada -->
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="">Estatus</label>
                                            <select name="estado" id="" class="form-control">
                                                <option value="ACTIVO">ACTIVO</option>
                                                <option value="INACTIVO">INACTIVO</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <button type="submit" class="btn btn-primary">Registrar</button>
                                            <a href="<?=APP_URL;?>/admin/configuraciones/gestion" class="btn btn-secondary">Cancelar</a>
                                        </div>
                                    </div>
                                </div>
                            </form>
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

    document.getElementById('periodoForm').addEventListener('submit', function(event) {
        const desde = new Date(document.getElementById('desde').value);
        const hasta = new Date(document.getElementById('hasta').value);
        const diffTime = hasta - desde; // Diferencia en milisegundos
        const diffDays = diffTime / (1000 * 60 * 60 * 24); // Convertir a días

        // Valida que "Desde" no sea mayor que "Hasta"
        if (desde > hasta) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: "La fecha 'Desde' no puede ser mayor que la fecha 'Hasta'."
            });
            event.preventDefault(); // Evitar el envío del formulario
            return;
        }

        // Valida que la duración sea al menos 285 días
        if (diffDays < 285) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: "La duración del periodo académico debe ser de al menos 285 días."
            });
            event.preventDefault(); // Evitar el envío del formulario
            return;
        }

        // Validar que la duración no exceda un año (365 días)
        if (diffDays > 365) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: "La duración del periodo académico no puede exceder un año (365 días)."
            });
            event.preventDefault(); // Evitar el envío del formulario
            return;
        }
    });
</script>

<?php
include ('../../../admin/layout/parte2.php');
include ('../../../layout/mensajes.php');
?>