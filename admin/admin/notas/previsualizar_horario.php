<?php
include('../../app/config.php');
// Obtener periodo académico activo
$gestion_activa = $pdo->query("SELECT * FROM gestiones WHERE estado = 1 ORDER BY desde DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);

// Obtener datos para formulario
$grados = $pdo->query("SELECT * FROM grados ORDER BY trayecto, trimestre")->fetchAll(PDO::FETCH_ASSOC);
$secciones = $pdo->query("SELECT * FROM secciones ORDER BY nombre_seccion")->fetchAll(PDO::FETCH_ASSOC);
$materias = $pdo->query("SELECT * FROM materias ORDER BY nombre_materia")->fetchAll(PDO::FETCH_ASSOC);
$profesores = $pdo->query("SELECT * FROM profesores ORDER BY apellidos, nombres")->fetchAll(PDO::FETCH_ASSOC);

// 2. Procesar el formulario si se envió
// ------------------------------------
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['grado'])) {
    // Insertar horario principal
    $stmt = $pdo->prepare("INSERT INTO horarios 
                          (id_gestion, id_grado, id_seccion, aula, fecha_inicio, fecha_fin) 
                          VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $gestion_activa['id_gestion'],
        $_POST['grado'],
        $_POST['seccion'],
        $_POST['aula'] ?? null,
        $_POST['fecha_inicio'] ?? null,
        $_POST['fecha_fin'] ?? null
    ]);
    $id_horario = $pdo->lastInsertId();

    // Insertar detalles del horario (si existen)
    if (!empty($_POST['horario'])) {
        $stmt_detalle = $pdo->prepare("INSERT INTO horario_detalle 
                                      (id_horario, dia_semana, hora_inicio, hora_fin, id_materia, id_profesor) 
                                      VALUES (?, ?, ?, ?, ?, ?)");
        
        foreach ($_POST['horario'] as $dia => $bloques) {
            foreach ($bloques as $hora_inicio => $bloque) {
                if (!empty($bloque['materia'])) {
                    $stmt_detalle->execute([
                        $id_horario,
                        $dia,
                        $bloque['hora_inicio'],
                        $bloque['hora_fin'],
                        $bloque['materia'],
                        $bloque['profesor'] ?? null
                    ]);
                }
            }
        }
    }
    // For preview only - don't save to database
    if (isset($_POST['preview_only'])) {
        header("Location: previsualizar_horario.php");
        exit();
    }

}

// Get data from session for preview
$horario_data = $_SESSION['horario_data'] ?? [];
$grado_id = $horario_data['grado'] ?? null;
$seccion_id = $horario_data['seccion'] ?? null;
$aula = $horario_data['aula'] ?? '';
$fecha_inicio = $horario_data['fecha_inicio'] ?? '';
$fecha_fin = $horario_data['fecha_fin'] ?? '';
$horario_data_raw = $horario_data['horario'] ?? []; // Get the raw horario data
$gestion_id = $horario_data['gestion_activa'] ?? null;

// Get grade and section info
$grado = $seccion = null;
if ($grado_id) {
    $query = $pdo->prepare("SELECT * FROM grados WHERE id_grado = ?");
    $query->execute([$grado_id]);
    $grado = $query->fetch(PDO::FETCH_ASSOC);
}

if ($seccion_id) {
    $query = $pdo->prepare("SELECT * FROM secciones WHERE id_seccion = ?");
    $query->execute([$seccion_id]);
    $seccion = $query->fetch(PDO::FETCH_ASSOC);
}

// Get active academic period
$gestion_nombre = "PERÍODO NO CONFIGURADO";
$gestion_rango = "";
$gestion = null;

if ($gestion_id) {
    $query = $pdo->prepare("SELECT * FROM gestiones WHERE id_gestion = ?");
    $query->execute([$gestion_id]);
    $gestion = $query->fetch(PDO::FETCH_ASSOC);
}

if ($gestion) {
    $gestion_nombre = strtoupper($gestion['nombre_gestion'] ?? 'GESTIÓN SIN NOMBRE');
    $gestion_rango = " (Desde: " . date('d/m/Y', strtotime($gestion['desde'])) .
                        " - Hasta: " . date('d/m/Y', strtotime($gestion['hasta'])) . ")";
}

// Organize schedule data correctly
$horario_organizado = [];
$materias_ids = [];
$profesores_ids = [];

foreach ($horario_data_raw as $dia => $bloques) {
    if (is_array($bloques)) {
        foreach ($bloques as $hora_inicio => $bloque_data) {
            if (is_array($bloque_data)) {
                $horario_organizado[$dia][$hora_inicio] = [
                    'materia' => $bloque_data['materia'] ?? null,
                    'profesor' => $bloque_data['profesor'] ?? null,
                    'hora_inicio' => $bloque_data['hora_inicio'] ?? '',
                    'hora_fin' => $bloque_data['hora_fin'] ?? ''
                ];

                if (!empty($bloque_data['materia'])) {
                    $materias_ids[] = $bloque_data['materia'];
                }
                if (!empty($bloque_data['profesor'])) {
                    $profesores_ids[] = $bloque_data['profesor'];
                }
            }
        }
    }
}

// Remove duplicates
$materias_ids = array_unique($materias_ids);
$profesores_ids = array_unique($profesores_ids);

// Get subjects and teachers names
$materias = [];
if (!empty($materias_ids)) {
    $placeholders = implode(',', array_fill(0, count($materias_ids), '?'));
    $query = $pdo->prepare("SELECT id_materia, nombre_materia FROM materias WHERE id_materia IN ($placeholders)");
    $query->execute($materias_ids);
    $materias_temp = $query->fetchAll(PDO::FETCH_KEY_PAIR);
    if (is_array($materias_temp)) {
        $materias = $materias_temp;
    }
}

$profesores = [];
if (!empty($profesores_ids)) {
    $placeholders = implode(',', array_fill(0, count($profesores_ids), '?'));
    $query = $pdo->prepare("SELECT id_profesor, CONCAT(nombres, ' ', apellidos) AS nombre_completo FROM profesores WHERE id_profesor IN ($placeholders)");
    $query->execute($profesores_ids);
    $profesores_temp = $query->fetchAll(PDO::FETCH_KEY_PAIR);
    if (is_array($profesores_temp)) {
        $profesores = $profesores_temp;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Horario Escolar - <?= $grado ? $grado['grado'] : '' ?> <?= $seccion ? $seccion['nombre_seccion'] : '' ?></title>
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2980b9;
            --accent-color: #e74c3c;
            --light-color: #ecf0f1;
            --dark-color: #2c3e50;
            --success-color: #2ecc71;
            --warning-color: #f39c12;
            --border-color: #bdc3c7;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 25px;
            color: #333;
            background-color: #f9f9f9;
        }
        
        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--border-color);
        }
        
        .logo-container {
            width: 120px;
        }
        
        .logo {
            max-width: 100%;
            height: auto;
            max-height: 80px;
        }
        
        .header-info {
            text-align: center;
            flex-grow: 1;
            padding: 0 20px;
        }
        
        .header-info h1 {
            margin: 0;
            font-size: 22px;
            color: var(--dark-color);
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .header-info h2 {
            margin: 8px 0;
            font-size: 18px;
            color: var(--primary-color);
            font-weight: 600;
        }
        
        .header-info p {
            margin: 5px 0;
            font-size: 14px;
            color: #555;
        }
        
        .header-meta {
            text-align: right;
            width: 120px;
            font-size: 12px;
            color: #777;
        }
        
        .period-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            background-color: #e8f4fc;
            padding: 12px 15px;
            border-radius: 5px;
            font-size: 14px;
            flex-wrap: wrap;
        }
        
        .period-info div {
            margin: 5px 10px;
        }
        
        .period-info span {
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .schedule-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
            margin-bottom: 30px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            background-color: var(--primary-color);
            color: white;
            padding: 12px 8px;
            text-align: center;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 13px;
            letter-spacing: 0.5px;
        }
        
        td {
            border: 1px solid var(--border-color);
            padding: 12px 8px;
            text-align: center;
            vertical-align: middle;
        }
        
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .time-cell {
            background-color: #f1f8fe;
            font-weight: 500;
            color: var(--dark-color);
            width: 120px;
        }
        
        .subject {
            font-weight: 600;
            margin-bottom: 4px;
            color: var(--dark-color);
            font-size: 14px;
        }
        
        .teacher {
            font-size: 12px;
            color: #666;
            font-style: italic;
        }
        
        .free-period {
            background-color: #f5f5f5;
            color: #999;
            font-style: italic;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #777;
            padding-top: 15px;
            border-top: 1px solid var(--border-color);
        }
        
        .actions {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 25px;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .btn i {
            margin-right: 8px;
        }
        
        .btn-print {
            background-color: var(--primary-color);
            color: white;
        }
        
        .btn-print:hover {
            background-color: var(--secondary-color);
        }
        
        .btn-pdf {
            background-color: var(--accent-color);
            color: white;
        }
        
        .btn-pdf:hover {
            background-color: #c0392b;
        }
        
        @media print {
            body {
                padding: 0;
                background-color: white;
            }
            
            .actions {
                display: none;
            }
            
            .schedule-container {
                box-shadow: none;
                border: 1px solid #ddd;
            }
            
            .period-info {
                background-color: transparent;
                border: 1px solid #eee;
            }
        }
        
        @media (max-width: 768px) {
            .header-container {
                flex-direction: column;
                text-align: center;
            }
            
            .logo-container, .header-meta {
                width: 100%;
                text-align: center;
                margin-bottom: 10px;
            }
            
            .period-info {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }
            
            table {
                font-size: 14px;
            }
            
            td, th {
                padding: 8px 4px;
            }
        }
    </style>
    <!-- Font Awesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <div class="header-container">
        <div class="logo-container">
            <img src="logos/logo.png" alt="Logo" class="logo">
        </div>
        <div class="header-info">
            <h3>UNIDAD EDUCATIVA NACIONAL "<?= strtoupper(APP_NAME) ?>"</h3>
            <h2>HORARIO ESCOLAR <?= $gestion_nombre ?></h2>
            <?php if ($gestion_rango): ?>
            <p><?= $gestion_rango ?></p>
            <?php endif; ?>
            <p>
                <strong>GRADO:</strong> <?= $grado ? strtoupper($grado['grado']) : 'NO ESPECIFICADO' ?> | 
                <strong>SECCIÓN:</strong> <?= $seccion ? strtoupper($seccion['nombre_seccion']) : 'NO ESPECIFICADA' ?> | 
                <strong>TURNO:</strong> <?= $seccion ? strtoupper($seccion['turno']) : 'NO ESPECIFICADO' ?>
            </p>
            <p>
                <strong>AULA:</strong> <?= strtoupper($aula) ?> | 
                <strong>VIGENCIA:</strong> <?= date('d/m/Y', strtotime($fecha_inicio)) ?> - <?= date('d/m/Y', strtotime($fecha_fin)) ?>
            </p>
        </div>
        
        <div class="header-meta">
            Código: <?= $grado ? $grado['id_grado'] : '' ?>-<?= $seccion ? $seccion['id_seccion'] : '' ?><br>
            Generado: <?= date('d/m/Y H:i') ?>
        </div>
    </div>
    
    <div class="period-info">
        <div><strong>Periodo:</strong> <?= $gestion_nombre ?></div>
        <div><strong>Director:</strong> Lic. Juan Pérez González</div>
        <div><strong>Coordinador:</strong> Mg. María López Sánchez</div>
    </div>

    <div class="schedule-container">
        <table>
            <thead>
                <tr>
                    <th>Hora</th>
                    <th>Lunes</th>
                    <th>Martes</th>
                    <th>Miércoles</th>
                    <th>Jueves</th>
                    <th>Viernes</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $bloques_horarios = [
                    ['07:50','08:30'],
                    ['08:30','09:10'],
                    ['09:10','09:50'],
                    ['10:10','10:50'],
                    ['10:50','11:30'],
                    ['11:30','12:10']
                ];

                $dias_semana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];

                foreach ($bloques_horarios as $bloque):
                    list($hora_inicio, $hora_fin) = $bloque;
                ?>
                <tr>
                    <td class="time-cell"><?= $hora_inicio ?>-<?= $hora_fin ?></td>
                    <?php foreach ($dias_semana as $dia):
                        $clase = isset($horario_organizado[$dia][$hora_inicio]) ? '' : 'free-period';
                        $materia_id = $horario_organizado[$dia][$hora_inicio]['materia'] ?? null;
                        $profesor_id = $horario_organizado[$dia][$hora_inicio]['profesor'] ?? null;
                    ?>
                    <td class="<?= $clase ?>">
                        <?php if (isset($horario_organizado[$dia][$hora_inicio])): ?>
                            <div class="subject"><?= $materias[$materia_id] ?? 'Materia no encontrada' ?></div>
                            <div class="teacher"><?= $profesores[$profesor_id] ?? 'Profesor no encontrado' ?></div>
                        <?php else: ?>
                            Libre
                        <?php endif; ?>
                    </td>
                    <?php endforeach; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="actions">
        <button class="btn btn-print" onclick="window.print()">
            <i class="fas fa-print"></i> Imprimir Horario
        </button>
        <button class="btn btn-pdf" onclick="downloadPDF()">
            <i class="fas fa-file-pdf"></i> Descargar PDF
        </button>
    </div>

    <div class="footer">
        <p>Generado el <?= date('d/m/Y H:i:s') ?> | <?= APP_NAME ?> - Sistema de Gestión Académica</p>
    </div>

    <script>
    function downloadPDF() {
        // Create a form dynamically
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'generar_pdf_temporal.php';

        // Add all schedule data as hidden inputs
        const data = <?= json_encode($_SESSION['horario_data'] ?? []) ?>;

        for (const key in data) {
            if (key === 'horario') {
                // Handle nested horario data
                const horario = data[key];
                for (const dia in horario) {
                    if (horario.hasOwnProperty(dia)) {
                        for (const hora in horario[dia]) {
                            if (horario[dia].hasOwnProperty(hora)) {
                                for (const field in horario[dia][hora]) {
                                    if (horario[dia][hora].hasOwnProperty(field)) {
                                        const input = document.createElement('input');
                                        input.type = 'hidden';
                                        input.name = `horario[${dia}][${hora}][${field}]`;
                                        input.value = horario[dia][hora][field] ?? '';
                                        form.appendChild(input);
                                    }
                                }
                            }
                        }
                    }
                }
            } else {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = data[key] ?? '';
                form.appendChild(input);
            }
        }

        // Add to document and submit
        document.body.appendChild(form);
        form.submit();
    }
    </script>
</body>
</html>