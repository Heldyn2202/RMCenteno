<?php
include('../app/config.php');


?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Horarios Escolares - U.E.N Roberto Martínez Centeno</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .header {
            background-color: #2c3e50;
            color: white;
            padding: 20px 0;
            margin-bottom: 30px;
        }
        .card-horario {
            margin-bottom: 30px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            border: none;
        }
        .card-header {
            background-color: #3498db;
            color: white;
            font-weight: bold;
        }
        .table th {
            background-color: #2980b9;
            color: white;
        }
        .btn-volver {
            background-color: #2c3e50;
            color: white;
            margin-bottom: 20px;
        }
        .btn-volver:hover {
            background-color: #1a252f;
            color: white;
        }
        .no-horarios {
            text-align: center;
            padding: 20px;
            font-style: italic;
            color: #7f8c8d;
        }
    </style>
</head>
<body>
    <div class="header text-center">
        <h1><i class="fas fa-calendar-alt"></i> Horarios Escolares</h1>
        <p class="mb-0">U.E.N Roberto Martínez Centeno</p>
    </div>

    <div class="container">
        <a href="<?=APP_URL?>" class="btn btn-volver">
            <i class="fas fa-arrow-left"></i> Volver al Portal
        </a>

        <?php if(empty($horarios_organizados)): ?>
            <div class="alert alert-info">
                No hay horarios registrados actualmente.
            </div>
        <?php else: ?>
            <?php foreach($horarios_organizados as $grupo): ?>
                <div class="card card-horario">
                    <div class="card-header">
                        <i class="fas fa-users"></i> Horario de <?=$grupo['grado']?>° "<?=$grupo['seccion']?>"
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Día</th>
                                        <th>Hora Inicio</th>
                                        <th>Hora Fin</th>
                                        <th>Materia</th>
                                        <th>Profesor</th>
                                        <th>Aula</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($grupo['horarios'] as $horario): ?>
                                    <tr>
                                        <td><?=$dias_semana[$horario['dia_semana']]?></td>
                                        <td><?=date('h:i A', strtotime($horario['hora_inicio']))?></td>
                                        <td><?=date('h:i A', strtotime($horario['hora_fin']))?></td>
                                        <td><?=$horario['materia_nombre']?></td>
                                        <td><?=$horario['profesor_nombre']?></td>
                                        <td><?=$horario['aula_nombre']?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>