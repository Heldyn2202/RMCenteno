<?php
$id_config_institucion = $_GET['id'];
include ('../../../app/config.php');
include ('../../../admin/layout/parte1.php');

include ('../../../app/controllers/configuraciones/institucion/datos_institucion.php');

?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <br>
    <div class="content">
        <div class="container">
        <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-5">
            <h1 class="m-0">Editar institución</h1>
          </div><!-- /.col -->
            <div class="col-sm-7">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Dashboard</a></li>
              <li class="breadcrumb-item"><a href="<?=APP_URL;?>/admin/configuraciones">Configuraciones</a></li>
              <li class="breadcrumb-item"><a href="<?=APP_URL;?>/admin/configuraciones/gestion">Datos de la institución</a></li>
              <li class="breadcrumb-item">Editar institución</li>
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
                            <form action="<?=APP_URL;?>/app/controllers/configuraciones/institucion/update.php" method="post" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <input type="text" name="id_config_institucion" value="<?=$id_config_institucion;?>" hidden>
                                                    <input type="text" name="logo" value="<?=$logo;?>" hidden>
                                                    <label for="">Nombre de la institución <b>*</b></label>
                                                    <input type="text" name="nombre_institucion" value="<?=$nombre_institucion;?>" class="form-control" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="">Correo de la institución</label>
                                                    <input type="email" name="correo" value="<?=$correo;?>" class="form-control">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="">Teléfono</label>
                                                    <input type="text" name="telefono" value="<?=$telefono;?>" class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="">Celular <b>*</b></label>
                                                    <input type="text" name="celular" value="<?=$celular;?>" class="form-control">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="">Dirección <b>*</b></label>
                                                    <input type="text" name="direccion" value="<?=$direccion;?>" class="form-control" required>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="">Logo de la institución</label>
                                                    <input type="file" name="file" id="file" class="form-control">
                                                    <br>
                                                    <center>
                                                        <output id="list">
                                                            <img src="<?=APP_URL."/public/images/configuracion/".$logo;?>" width="200px" alt="">
                                                        </output>
                                                    </center>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <center>
                                            <button type="submit" class="btn btn-success">Actualizar</button>
                                            <a href="<?=APP_URL;?>/admin/configuraciones/institucion" class="btn btn-secondary">Cancelar</a>
                                            </center>
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

<?php

include ('../../../admin/layout/parte2.php');
include ('../../../layout/mensajes.php');

?>

<script>
    function archivo(evt) {
        var files = evt.target.files; // FileList object
        // Obtenemos la imagen del campo "file".
        for (var i = 0, f; f = files[i]; i++) {
            //Solo admitimos imágenes.
            if (!f.type.match('image.*')) {
                continue;
            }
            var reader = new FileReader();
            reader.onload = (function (theFile) {
                return function (e) {
                    // Insertamos la imagen
                    document.getElementById("list").innerHTML = ['<img class="thumb thumbnail" src="',e.target.result, '" width="300px" title="', escape(theFile.name), '"/>'].join('');
                };
            })(f);
            reader.readAsDataURL(f);
        }
    }
    document.getElementById('file').addEventListener('change', archivo, false);
</script>
