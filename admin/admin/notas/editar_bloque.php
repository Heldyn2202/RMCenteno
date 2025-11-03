<?php
include('../../app/config.php');
include('../../admin/layout/parte1.php');

$grado   = isset($_GET['grado']) ? (int)$_GET['grado'] : 0;
$seccion = isset($_GET['seccion']) ? (int)$_GET['seccion'] : 0;
$id      = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Detectar PK
$cols = $pdo->query("SHOW COLUMNS FROM horario_detalle")->fetchAll(PDO::FETCH_COLUMN);
$pk = null;
foreach (['id_detalle','id_horario_detalle','id'] as $c) { if (in_array($c, $cols, true)) { $pk = $c; break; } }
if (!$pk) { die('No se pudo detectar la clave primaria.'); }

// Cargar bloque
$stmt = $pdo->prepare("SELECT * FROM horario_detalle WHERE $pk = ?");
$stmt->execute([$id]);
$bloque = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$bloque) {
    echo '<div class="content-wrapper"><div class="content"><div class="container"><div class="alert alert-danger">Bloque no encontrado.</div></div></div></div>';
    include('../../admin/layout/parte2.php');
    exit;
}

// Listas básicas
$materias = $pdo->query("SELECT id_materia, nombre_materia FROM materias WHERE estado = 1 ORDER BY nombre_materia")->fetchAll(PDO::FETCH_ASSOC);
$profes   = $pdo->query("SELECT id_profesor, CONCAT(nombres,' ',apellidos) AS nombre FROM profesores WHERE estado = 1 ORDER BY apellidos, nombres")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_materia  = (int)($_POST['id_materia'] ?? 0);
    // Si el usuario deja "Sin profesor" (valor ""), actualizar a NULL para respetar la FK
    $id_profesor = (isset($_POST['id_profesor']) && $_POST['id_profesor'] !== '') ? (int)$_POST['id_profesor'] : null;
    try {
        $up = $pdo->prepare("UPDATE horario_detalle SET id_materia = :mat, id_profesor = :prof WHERE $pk = :id");
        $up->bindValue(':mat', $id_materia, PDO::PARAM_INT);
        if ($id_profesor === null) {
            $up->bindValue(':prof', null, PDO::PARAM_NULL);
        } else {
            $up->bindValue(':prof', $id_profesor, PDO::PARAM_INT);
        }
        $up->bindValue(':id', $id, PDO::PARAM_INT);
        $up->execute();
        $_SESSION['mensaje'] = 'Bloque actualizado correctamente.';
        $_SESSION['icono']   = 'success';
        header('Location: horarios_consolidados.php?grado=' . $grado . '&seccion=' . $seccion);
        exit;
    } catch (Throwable $e) {
        echo '<div class="content-wrapper"><div class="content"><div class="container"><div class="alert alert-danger">Error al actualizar: ' . htmlspecialchars($e->getMessage()) . '</div></div></div></div>';
    }
}
?>

<div class="content-wrapper">
  <div class="content">
    <div class="container">
      <div class="row mb-3"><div class="col-sm-12"><h3>Editar bloque de horario</h3></div></div>
      <div class="card card-body">
        <form method="post">
          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Materia</label>
              <select name="id_materia" class="form-control" required>
                <?php foreach($materias as $m): ?>
                  <option value="<?=$m['id_materia']?>" <?= $bloque['id_materia']==$m['id_materia']?'selected':'' ?>><?=htmlspecialchars($m['nombre_materia'])?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group col-md-6">
              <label>Profesor</label>
              <select name="id_profesor" class="form-control">
                <option value="">Sin profesor</option>
                <?php foreach($profes as $p): ?>
                  <option value="<?=$p['id_profesor']?>" <?= (int)$bloque['id_profesor']===(int)$p['id_profesor']?'selected':'' ?>><?=htmlspecialchars($p['nombre'])?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="d-flex justify-content-between">
            <div>
              <?php $pkMostrar = htmlspecialchars($pk); ?>
              <small class="text-muted">ID bloque: <?=$id?> · PK: <?=$pkMostrar?></small>
            </div>
            <div>
              <a class="btn btn-outline-danger mr-2" href="eliminar_bloque.php?id=<?=$id?>&grado=<?=$grado?>&seccion=<?=$seccion?>" onclick="return confirm('¿Eliminar este bloque?');">Eliminar</a>
              <a class="btn btn-secondary" href="horarios_consolidados.php?grado=<?=$grado?>&seccion=<?=$seccion?>">Cancelar</a>
              <button class="btn btn-primary" type="submit">Guardar cambios</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php
include('../../admin/layout/parte2.php');
include('../../layout/mensajes.php');
?>


