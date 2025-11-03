<?php  
include ('../app/config.php');  
include ('../admin/layout/parte1.php');  
include ('../app/controllers/roles/listado_de_roles.php');  
include ('../app/controllers/usuarios/listado_de_usuarios.php');  
include ('../app/controllers/niveles/listado_de_niveles.php');  
include ('../app/controllers/grados/listado_de_grados.php');  
include ('../app/controllers/administrativos/listado_de_administrativos.php');  
include ('../app/controllers/representantes/listado_de_representantes.php');  
include ('../app/controllers/estudiantes/listado_de_estudiantes.php');  
include ('../app/controllers/estudiantes/reporte_estudiantes.php');  
include ('../app/controllers/secciones/listado_de_secciones.php');  
include ('../app/controllers/estudiantes/lista_inscripcion.php');  

?>  

<!-- Content Wrapper. Contains page content -->  
<div class="content-wrapper">  
    <div class="container">  
       <!-- Reemplaza todo el div content-header con este código: -->
<div class="content-header" style="background: linear-gradient(135deg, #3c8dbc 0%, #2d5f7e 100%); padding: 15px 25px; border-radius: 0; margin-bottom: 20px; margin-left: -15px; margin-right: -15px;">  
    <div class="container-fluid" style="padding: 0;">  
        <div class="row align-items-center">  
            <div class="col-sm-8">  
                <h1 class="m-0" style="color: white; font-weight: bold; text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.3); margin: 0; line-height: 1.2; padding: 5px 0;">
                    Bienvenidos al Sistema Integral de Gestión de Inscripciones</h1> 
                
            </div><!-- /.col -->  
            <div class="col-sm-4">  
                <ol class="breadcrumb float-sm-right" style="background: rgba(255, 255, 255, 0.2); padding: 8px 15px; border-radius: 20px; margin: 0;">  
                    <li class="breadcrumb-item"><a href="#" style="color: white; text-decoration: none;">Inicio</a></li>  
                    <li class="breadcrumb-item active" style="color: white; font-weight: bold;">Panel de control</li>  
                </ol>  
            </div><!-- /.col -->  
        </div><!-- /.row -->  
        <hr style="border-color: rgba(255,255,255,0.3); margin: 10px 0 5px 0;">
    </div><!-- /.container-fluid -->  
</div>
        <!-- Fila para el botón de refrescar -->
        <div class="row mb-3">
            <div class="col-11"></div>
            <div class="col-1">
                <button class="btn btn-link btn-lg" onclick="refrescarPagina();" style="color: #3c8dbc; padding: 0;">
                    <i class="fas fa-sync"></i>
                </button>
            </div>
        </div>
        
        <hr>
        
        <!-- Vista para el administrador -->  
        <?php  
        if ($rol_sesion_usuario == "ADMINISTRADOR") { ?>  
            <div class="row">  
                <!-- Tarjeta 1: Periodo Escolar - Mismo estilo azul -->
                <div class="col-lg-3 col-6 mb-4">  
                    <div class="small-box" style="background-color:#fff; color:#1A1341; border: 2px solid; border-image: linear-gradient(135deg, #3c8dbc 0%, #2d5f7e 100%); border-image-slice: 1; box-shadow: 0 4px 15px rgba(60, 141, 188, 0.2);">  
                        <div class="inner">  
                            <?php  
                            // Obtener el periodo escolar activo  
                            function getPeriodoEscolarActivo($pdo) {  
                                $sql = "SELECT * FROM gestiones WHERE estado = '1' LIMIT 1";  
                                $stmt = $pdo->prepare($sql);  
                                $stmt->execute();  
                                return $stmt->fetch(PDO::FETCH_ASSOC);  
                            }  

                            $gestion_activa = getPeriodoEscolarActivo($pdo);  
                            if ($gestion_activa) {  
                                $año_inicio = date('Y', strtotime($gestion_activa['desde']));  
                                $año_fin = date('Y', strtotime($gestion_activa['hasta']));  
                                echo "<h3 style='color: #3c8dbc; font-weight: bold;'>{$año_inicio}-{$año_fin}</h3>";  
                            } else {  
                                echo "<h3 style='color: #3c8dbc; font-weight: bold;'>No hay periodo activo</h3>";  
                            }  
                            ?>  
                            <p style="color: #6c757d;">Periodo escolar</p>  
                        </div>  
                        <div class="icon">  
                            <i class="fas fa-calendar-alt" style="color: #3c8dbc; opacity: 0.3;"></i>  
                        </div>  
                        <a href="<?=APP_URL;?>/admin/configuraciones/gestion" style="color:#fff; display:block; background: linear-gradient(135deg, #3c8dbc 0%, #2d5f7e 100%);" class="small-box-footer">  
                            Más información <i class="fas fa-arrow-circle-right"></i>  
                        </a>  
                    </div>  
                </div>  

                <!-- Tarjeta 2: Estudiantes - Estilo azul -->
                <div class="col-lg-3 col-6 mb-4">   
                    <div class="small-box" style="background-color:#fff; color:#1A1341; border: 2px solid; border-image: linear-gradient(135deg, #3c8dbc 0%, #2d5f7e 100%); border-image-slice: 1; box-shadow: 0 4px 15px rgba(60, 141, 188, 0.2);">  
                        <div class="inner">  
                            <?php  
                            $estudiantes_activos = array_filter($estudiantes, function($estudiante) {
                                return $estudiante['estatus'] === 'activo'; 
                            });
                            $contador_estudiantes = count($estudiantes_activos);  
                            ?>  
                            <h3 style="color: #3c8dbc; font-weight: bold;"><?=$contador_estudiantes;?></h3>  
                            <p style="color: #6c757d;">Estudiantes</p>  
                        </div>  
                        <div class="icon">  
                            <i class="fas fa-user-graduate" style="color: #3c8dbc; opacity: 0.3;"></i>  
                        </div>  
                        <a href="<?=APP_URL;?>/admin/estudiantes" style="color:#fff; display:block; background: linear-gradient(135deg, #3c8dbc 0%, #2d5f7e 100%);" class="small-box-footer">  
                            Más información <i class="fas fa-arrow-circle-right"></i>  
                        </a>  
                    </div>  
                </div>

                <!-- Tarjeta 3: Representantes - Mismo estilo azul -->
                <div class="col-lg-3 col-6 mb-4">  
                    <div class="small-box" style="background-color:#fff; color:#1A1341; border: 2px solid; border-image: linear-gradient(135deg, #3c8dbc 0%, #2d5f7e 100%); border-image-slice: 1; box-shadow: 0 4px 15px rgba(60, 141, 188, 0.2);">  
                        <div class="inner">  
                            <?php  
                            $representantes_activos = array_filter($representantes, function($representante) {
                                return $representante['estatus'] === 'Activo'; 
                            });
                            $contador_representantes = count($representantes_activos);  
                            ?>  
                            <h3 style="color: #3c8dbc; font-weight: bold;"><?=$contador_representantes;?></h3>  
                            <p style="color: #6c757d;">Representantes</p>  
                        </div>  
                        <div class="icon">  
                            <i class="fas fa-users" style="color: #3c8dbc; opacity: 0.3;"></i>  
                        </div>  
                        <a href="<?=APP_URL;?>/admin/representantes" style="color:#fff; display:block; background: linear-gradient(135deg, #3c8dbc 0%, #2d5f7e 100%);" class="small-box-footer">  
                            Más información <i class="fas fa-arrow-circle-right"></i>  
                        </a>  
                    </div>  
                </div>

                <!-- Tarjeta 4: Administrativos - Mismo estilo azul -->
                <div class="col-lg-3 col-6 mb-4">  
                    <div class="small-box" style="background-color:#fff; color:#1A1341; border: 2px solid; border-image: linear-gradient(135deg, #3c8dbc 0%, #2d5f7e 100%); border-image-slice: 1; box-shadow: 0 4px 15px rgba(60, 141, 188, 0.2);">  
                        <div class="inner">  
                            <?php  
                            $contador_administrativos = count($administrativos);  
                            ?>  
                            <h3 style="color: #3c8dbc; font-weight: bold;"><?=$contador_administrativos;?></h3>  
                            <p style="color: #6c757d;">Administrativos</p>  
                        </div>  
                        <div class="icon">  
                            <i class="fas fa-user-cog" style="color: #3c8dbc; opacity: 0.3;"></i>
                        </div>  
                        <a href="<?=APP_URL;?>/admin/administrativos" style="color:#fff; display:block; background: linear-gradient(135deg, #3c8dbc 0%, #2d5f7e 100%);" class="small-box-footer">  
                            Más información <i class="fas fa-arrow-circle-right"></i>  
                        </a>  
                    </div>  
                </div>  

                <!-- Tarjeta 5: Inscripciones - Mismo estilo azul -->
                <div class="col-lg-3 col-6 mb-4">  
                    <div class="small-box" style="background-color:#fff; color:#1A1341; border: 2px solid; border-image: linear-gradient(135deg, #3c8dbc 0%, #2d5f7e 100%); border-image-slice: 1; box-shadow: 0 4px 15px rgba(60, 141, 188, 0.2);">  
                        <div class="inner">  
                            <?php  
                            $id_gestion_activa = $gestion_activa['id_gestion'];  
                            function getInscripcionesByGestion($pdo, $id_gestion) {  
                                $sql = "SELECT * FROM inscripciones WHERE id_gestion = :id_gestion";  
                                $stmt = $pdo->prepare($sql);  
                                $stmt->bindParam(':id_gestion', $id_gestion, PDO::PARAM_INT);  
                                $stmt->execute();  
                                return $stmt->fetchAll(PDO::FETCH_ASSOC);  
                            }  
                            $inscripciones = getInscripcionesByGestion($pdo, $id_gestion_activa);  
                            $contador_inscripciones = count($inscripciones);  
                            ?>  
                            <h3 style="color: #3c8dbc; font-weight: bold;"><?=$contador_inscripciones;?></h3>  
                            <p style="color: #6c757d;">Inscripciones</p>  
                        </div>  
                        <div class="icon">  
                            <i class="fas fa-clipboard-list" style="color: #3c8dbc; opacity: 0.3;"></i>  
                        </div>  
                        <a href="<?=APP_URL;?>/admin/estudiantes/Lista_de_inscripcion.php" style="color:#fff; display:block; background: linear-gradient(135deg, #3c8dbc 0%, #2d5f7e 100%);" class="small-box-footer">  
                            Más información <i class="fas fa-arrow-circle-right"></i>  
                        </a>  
                    </div>  
                </div>  

                <!-- Tarjeta 6: Grados - Mismo estilo azul -->
                <div class="col-lg-3 col-6 mb-4">   
                    <div class="small-box" style="background-color:#fff; color:#1A1341; border: 2px solid; border-image: linear-gradient(135deg, #3c8dbc 0%, #2d5f7e 100%); border-image-slice: 1; box-shadow: 0 4px 15px rgba(60, 141, 188, 0.2);">  
                        <div class="inner">  
                            <?php  
                            $contador_grados = count($grados);  
                            ?>  
                            <h3 style="color: #3c8dbc; font-weight: bold;"><?=$contador_grados;?></h3>  
                            <p style="color: #6c757d;">Grados</p>  
                        </div>  
                        <div class="icon">  
                            <i class="fas fa-graduation-cap" style="color: #3c8dbc; opacity: 0.3;"></i>  
                        </div>  
                        <a href="<?=APP_URL;?>/admin/configuraciones/grados" style="color:#fff; display:block; background: linear-gradient(135deg, #3c8dbc 0%, #2d5f7e 100%);" class="small-box-footer">  
                            Más información <i class="fas fa-arrow-circle-right"></i>  
                        </a>  
                    </div>  
                </div>  

                <!-- Tarjeta 7: Secciones - Mismo estilo azul -->
                <div class="col-lg-3 col-6 mb-4">   
                    <div class="small-box" style="background-color:#fff; color:#1A1341; border: 2px solid; border-image: linear-gradient(135deg, #3c8dbc 0%, #2d5f7e 100%); border-image-slice: 1; box-shadow: 0 4px 15px rgba(60, 141, 188, 0.2);">  
                        <div class="inner">  
                            <?php  
                            $contador_secciones = count($secciones);  
                            ?>  
                            <h3 style="color: #3c8dbc; font-weight: bold;"><?=$contador_secciones;?></h3>  
                            <p style="color: #6c757d;">Secciones</p>  
                        </div>  
                        <div class="icon">  
                            <i class="fas fa-chalkboard" style="color: #3c8dbc; opacity: 0.3;"></i>  
                        </div>  
                        <a href="<?=APP_URL;?>/admin/configuraciones/secciones" style="color:#fff; display:block; background: linear-gradient(135deg, #3c8dbc 0%, #2d5f7e 100%);" class="small-box-footer">  
                            Más información <i class="fas fa-arrow-circle-right"></i>  
                        </a>  
                    </div>  
                </div>  
            </div>  
            <hr>
                
            <!-- Gráfico de estudiantes registrados -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-primary">
                        <div class="card-header" style="background: linear-gradient(135deg, #3c8dbc 0%, #2d5f7e 100%);">
                            <h3 class="card-title" style="color: white; margin: 0;">
                                Estudiantes Registrados
                            </h3>
                        </div>
                        <div class="card-body">
                            <div>
                                <canvas id="myChart2"></canvas>
                            </div>
                        </div>
                    </div>
                    <?php
                    $enero = 0; $febrero = 0; $marzo = 0; $abril = 0; $mayo = 0; $junio = 0; $julio = 0;
                    $agosto = 0; $septiembre = 0; $octubre = 0; $noviembre = 0; $diciembre = 0;
                    foreach ($reportes_estudiantes as $reportes_estudiante){
                        $fecha = $reportes_estudiante['created_at'];
                        $fecha = strtotime($fecha);
                        $mes = date("m",$fecha);
                        if($mes == "01") $enero = $enero + 1;
                        if($mes == "02") $febrero = $febrero + 1;
                        if($mes == "03") $marzo = $marzo + 1;
                        if($mes == "04") $abril = $abril + 1;
                        if($mes == "05") $mayo = $mayo + 1;
                        if($mes == "06") $junio = $junio + 1;
                        if($mes == "07") $julio = $julio + 1;
                        if($mes == "08") $agosto = $agosto + 1;
                        if($mes == "09") $septiembre = $septiembre + 1;
                        if($mes == "10") $octubre = $octubre + 1;
                        if($mes == "11") $noviembre = $noviembre + 1;
                        if($mes == "12") $diciembre = $diciembre + 1;
                    }
                    $reporte_meses = $enero.",".$febrero.",".$marzo.",".$abril.",".$mayo.",".$junio.",".$julio.",".$agosto.",".$septiembre.",".$octubre.",".$noviembre.",".$diciembre;
                    ?>
                    <script>
                        var meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio','Julio',
                            'Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
                        var datos =[<?=$reporte_meses;?>];
                        const ctx2 = document.getElementById('myChart2');
                        new Chart(ctx2, {
                            type: 'bar',
                            data: {
                                labels: meses,
                                datasets: [{
                                    label: 'Registrados por meses',
                                    data: datos,
                                    borderWidth: 2,
                                    backgroundColor: 'rgba(60, 141, 188, 0.6)',
                                    borderColor: 'rgba(60, 141, 188, 1)'
                                }]
                            },
                            options: {
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        max: 100,
                                        min: 0
                                    }
                                }
                            }
                        });
                    </script>
                </div>
            </div>
            <hr>
        <?php } ?>
        <!-- /.row -->
    </div><!-- /.container-fluid -->
</div>
<!-- /.content -->

<?php
include ('../admin/layout/parte2.php');
include ('../layout/mensajes.php');
?>

<script>
    $(function () {
        $('.knob').knob({
            draw: function () {
                if (this.$.data('skin') == 'tron') {
                    var a   = this.angle(this.cv)  // Angle
                        ,
                        sa  = this.startAngle          // Previous start angle
                        ,
                        sat = this.startAngle         // Start angle
                        ,
                        ea                            // Previous end angle
                        ,
                        eat = sat + a                 // End angle
                        ,
                        r   = true

                    this.g.lineWidth = this.lineWidth

                    this.o.cursor
                    && (sat = eat - 0.3)
                    && (eat = eat + 0.3)

                    if (this.o.displayPrevious) {
                        ea = this.startAngle + this.angle(this.value)
                        this.o.cursor
                        && (sa = ea - 0.3)
                        && (ea = ea + 0.3)
                        this.g.beginPath()
                        this.g.strokeStyle = this.previousColor
                        this.g.arc(this.xy, this.xy, this.radius - this.lineWidth, sa, ea, false)
                        this.g.stroke()
                    }

                    this.g.beginPath()
                    this.g.strokeStyle = r ? this.o.fgColor : this.fgColor
                    this.g.arc(this.xy, this.xy, this.radius - this.lineWidth, sat, eat, false)
                    this.g.stroke()

                    this.g.lineWidth = 2
                    this.g.beginPath()
                    this.g.strokeStyle = this.o.fgColor
                    this.g.arc(this.xy, this.xy, this.radius - this.lineWidth + 1 + this.lineWidth * 2 / 3, 0, 2 * Math.PI, false)
                    this.g.stroke()

                    return false
                }
            }
        })
    });
</script>

<script>
        window.onload = () => {
            setTimeout(() => {
                document.getElementsByTagName('body')[0].style.backgroundColor = 'white';
            }, 3000);
        }
        function refrescarPagina() {
            location.reload();
        }
    </script>