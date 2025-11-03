<?php
include('../app/config.php');
include('../layout/sesion.php');
include('../layout/parte1.php');

// Verificar si el usuario es admin
if ($_SESSION['rol'] != 'admin') {
    header('Location: '.APP_URL.'/login.php');
    exit;
}

$pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);

// Obtener profesores
$sql_profesores = "SELECT id, nombres FROM usuarios WHERE rol = 'profesor'";
$stmt_profesores = $pdo->prepare($sql_profesores);
$stmt_profesores->execute();
$profesores = $stmt_profesores->fetchAll(PDO::FETCH_ASSOC);

// Obtener materias
$sql_materias = "SELECT id, nombre FROM materias";
$stmt_materias = $pdo->prepare($sql_materias);
$stmt_materias->execute();
$materias = $stmt_materias->fetchAll(PDO::FETCH_ASSOC);

// Obtener aulas
$sql_aulas = "SELECT id, nombre FROM aulas";
$stmt_aulas = $pdo->prepare($sql_aulas);
$stmt_aulas->execute();
$aulas = $stmt_aulas->fetchAll(PDO::FETCH_ASSOC);

// Grados y secciones
$grados = range(1, 5); // 1° a 5°
$secciones = ['A', 'B', 'C', 'D'];

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $dia_semana = $_POST['dia_semana'];
    $hora_inicio = $_POST['hora_inicio'];
    $hora_fin = $_POST['hora_fin'];
    $materia_id = $_POST['materia_id'];
    $profesor_id = $_POST['profesor_id'];
    $aula_id = $_POST['aula_id'];
    $grado = $_POST['grado'];
    $seccion = $_POST['seccion'];
    
    // Validar que la hora fin sea mayor que la hora inicio
    if (strtotime($hora_fin) <= strtotime($hora_inicio)) {
        $_SESSION['error'] = "La hora de fin debe ser mayor a la hora de inicio";
    } else {
        // Verificar conflicto de horarios
        $sql_conflicto = "SELECT id FROM horarios 
                          WHERE dia_semana = :dia_semana 
                          AND (
                              (hora_inicio < :hora_fin AND hora_fin > :hora_inicio)
                              AND (profesor_id = :profesor_id OR aula_id = :aula_id)
                          )";
        $stmt_conflicto = $pdo->prepare($sql_conflicto);
        $stmt_conflicto->bindParam(':dia_semana', $dia_semana);
        $stmt_conflicto->bindParam(':hora_inicio', $hora_inicio);
        $stmt_conflicto->bindParam(':hora_fin', $hora_fin);
        $stmt_conflicto->bindParam(':profesor_id', $profesor_id);
        $stmt_conflicto->bindParam(':aula_id', $aula_id);
        $stmt_conflicto->execute();
        
        if ($stmt_conflicto->fetch()) {
            $_SESSION['error'] = "Conflicto de horario: El profesor o el aula ya están ocupados en ese horario";
        } else {
            // Insertar nuevo horario
            $sql_insert = "INSERT INTO horarios (dia_semana, hora_inicio, hora_fin, materia_id, profesor_id, aula_id, grado, seccion) 
                           VALUES (:dia_semana, :hora_inicio, :hora_fin, :materia_id, :profesor_id, :aula_id, :grado, :seccion)";
            $stmt_insert = $pdo->prepare($sql_insert);
            $stmt_insert->bindParam(':dia_semana', $dia_semana);
            $stmt_insert->bindParam(':hora_inicio', $hora_inicio);
            $stmt_insert->bindParam(':hora_fin', $hora_fin);
            $stmt_insert->bindParam(':materia_id', $materia_id);
            $stmt_insert->bindParam(':profesor_id', $profesor_id);
            $stmt_insert->bindParam(':aula_id', $aula_id);
            $stmt_insert->bindParam(':grado', $grado);
            $stmt_insert->bindParam(':seccion', $seccion);
            
            if ($stmt_insert->execute()) {
                $_SESSION['success'] = "Horario creado exitosamente";
                header('Location: horarios.php');
                exit;
            } else {
                $_SESSION['error'] = "Error al crear el horario";
            }
        }
    }
}
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1 class="m-0">Crear Nuevo Horario</h1>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-8">
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Complete los datos del horario</h3>
                        </div>
                        <div class="card-body">
                            <?php if(isset($_SESSION['error'])): ?>
                                <div class="alert alert-danger">
                                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST" action="">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Día de la semana</label>
                                            <select class="form-control" name="dia_semana" required>
                                                <option value="1">Lunes</option>
                                                <option value="2">Martes</option>
                                                <option value="3">Miércoles</option>
                                                <option value="4">Jueves</option>
                                                <option value="5">Viernes</option>
                                                <option value="6">Sábado</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Hora de inicio</label>
                                            <input type="time" class="form-control" name="hora_inicio" required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Hora de fin</label>
                                            <input type="time" class="form-control" name="hora_fin" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Materia</label>
                                            <select class="form-control" name="materia_id" required>
                                                <option value="">Seleccione una materia</option>
                                                <?php foreach($materias as $materia): ?>
                                                    <option value="<?php echo $materia['id']; ?>"><?php echo $materia['nombre']; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Profesor</label>
                                            <select class="form-control" name="profesor_id" required>
                                                <option value="">Seleccione un profesor</option>
                                                <?php foreach($profesores as $profesor): ?>
                                                    <option value="<?php echo $profesor['id']; ?>"><?php echo $profesor['nombres']; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Aula</label>
                                            <select class="form-control" name="aula_id" required>
                                                <option value="">Seleccione un aula</option>
                                                <?php foreach($aulas as $aula): ?>
                                                    <option value="<?php echo $aula['id']; ?>"><?php echo $aula['nombre']; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Grado</label>
                                            <select class="form-control" name="grado" required>
                                                <option value="">Seleccione un grado</option>
                                                <?php foreach($grados as $grado): ?>
                                                    <option value="<?php echo $grado; ?>"><?php echo $grado; ?>°</option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Sección</label>
                                            <select class="form-control" name="seccion" required>
                                                <option value="">Seleccione una sección</option>
                                                <?php foreach($secciones as $seccion): ?>
                                                    <option value="<?php echo $seccion; ?>">"<?php echo $seccion; ?>"</option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Guardar Horario
                                    </button>
                                    <a href="horarios.php" class="btn btn-default">
                                        <i class="fas fa-times"></i> Cancelar
                                    </a>
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

<?php include('../layout/parte2.php'); ?>