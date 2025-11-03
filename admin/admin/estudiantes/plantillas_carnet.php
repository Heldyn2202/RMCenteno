<?php
include ('../../app/config.php');
include ('../../admin/layout/parte1.php');

// Verificar si hay un mensaje en la sesión
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']); // Limpiar el mensaje después de mostrarlo
}
?>

<div class="content-wrapper">
    <br>
    <div class="content">
        <div class="content-header">  
                <div class="container-fluid">  
                    <div class="row mb-2">  
                        <div class="col-sm-6">  
                        <h1 class="m-0">Lista de planillas de carnet</h1>    
                        </div><!-- /.col -->
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="<?=APP_URL;?>/admin">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="<?=APP_URL;?>/admin/estudiantes">Estudiantes</a></li>
                                <li class="breadcrumb-item">Lista de planillas de carnet</li>
                            </ol>
                        </div><!-- /.col -->
                    </div><!-- /.row -->
                </div><!-- /.container-fluid -->
            </div>
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Plantillas de Carnets</h3>
                            <div class="card-tools">
                                <a href="crear_plantilla.php" class="btn btn-primary">
                                    <i class="bi bi-plus-square"></i> Nueva Plantilla
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <table id="tabla-plantillas" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Descripción</th>
                                        <th>Tamaño</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $query = $pdo->prepare("SELECT * FROM plantillas_carnet");
                                    $query->execute();
                                    $plantillas = $query->fetchAll(PDO::FETCH_ASSOC);
                                    
                                    foreach ($plantillas as $plantilla) {
                                        $tamano = $plantilla['ancho'].'mm x '.$plantilla['alto'].'mm';
                                        echo "<tr>
                                            <td>{$plantilla['nombre']}</td>
                                            <td>{$plantilla['descripcion']}</td>
                                            <td>{$tamano}</td>
                                            <td>".ucfirst($plantilla['estatus'])."</td>
                                            <td>
                                                <a href='editar_plantilla.php?id={$plantilla['id_plantilla']}' class='btn btn-sm btn-primary'><i class='bi bi-pencil'></i></a>
                                                <a href='../../app/controllers/plantillas/eliminar.php?id={$plantilla['id_plantilla']}' class='btn btn-sm btn-danger' onclick='return confirm(\"¿Está seguro de eliminar esta plantilla?\")'><i class='bi bi-trash'></i></a>
                                            </td>
                                        </tr>";
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

<?php include ('../../admin/layout/parte2.php'); ?>