<?php
// Iniciar sesión al principio del script para evitar errores de headers
session_start();

// Incluir configuración y bibliotecas
require_once('../app/config2.php');
require_once('library/tcpdf.php');

// Verificar conexión a la base de datos
if (!isset($conexion)) {
    die("Error: No se pudo establecer conexión con la base de datos");
}

// Inicializar variables importantes
$tipos_constancia = [];
$datos_estudiante = null;
$mensajes = [];

// Función para redireccionar y mostrar mensajes
function redirectWithMessage($location, $message, $isError = false) {
    if ($isError) {
        $_SESSION['mensaje_error'] = $message;
    } else {
        $_SESSION['mensaje'] = $message;
    }
    header("Location: " . $location);
    exit();
}

// Obtener lista de tipos de constancia con manejo de errores
try {
    $consulta_tipos = $conexion->query("SELECT * FROM tipos_constancia");
    if ($consulta_tipos) {
        while ($fila = $consulta_tipos->fetch_assoc()) {
            $tipos_constancia[$fila['id_tipo_constancia']] = $fila['nombre_tipo_constancia'];
        }
        $consulta_tipos->free();
    } else {
        throw new Exception("Error al consultar tipos de constancia: " . $conexion->error);
    }
} catch (Exception $e) {
    redirectWithMessage("constancias.php", $e->getMessage(), true);
}

// Procesar búsqueda por cédula
if (isset($_POST['buscar_cedula'])) {
    try {
        if (empty($_POST['cedula_busqueda'])) {
            throw new Exception("Debe ingresar una cédula para buscar");
        }

        $cedula_busqueda = $conexion->real_escape_string($_POST['cedula_busqueda']);
        
        // Consulta corregida según estructura de tablas proporcionada
        $sql = "SELECT 
                    e.id_estudiante,
                    CONCAT(e.tipo_cedula, '-', e.cedula) AS cedula_estudiante,
                    CONCAT(e.nombres, ' ', e.apellidos) AS nombre_estudiante,
                    e.fecha_nacimiento,
                    e.direccion,
                    e.numeros_telefonicos AS telefono,
                    CONCAT(g.nivel, ' ', g.grado) AS nombre_grado,
                    s.nombre_seccion,
                    CONCAT(r.nombres, ' ', r.apellidos) AS nombre_representante,
                    CONCAT(r.tipo_cedula, '-', r.cedula) AS cedula_representante,
                    r.afinidad AS parentesco
                FROM estudiantes e
                LEFT JOIN grados g ON e.turno_id = g.id_grado
                LEFT JOIN secciones s ON g.id_grado = s.id_grado
                LEFT JOIN representantes r ON e.id_representante = r.id_representante
                WHERE e.cedula = ? OR r.cedula = ?";
        
        // Usar consultas preparadas para mayor seguridad
        $stmt = $conexion->prepare($sql);
        if (!$stmt) {
            throw new Exception("Error al preparar la consulta: " . $conexion->error);
        }
        
        $stmt->bind_param("ss", $cedula_busqueda, $cedula_busqueda);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {
            $fila = $resultado->fetch_assoc();
            $datos_estudiante = [
                'id_estudiante' => $fila['id_estudiante'],
                'cedula_estudiante' => $fila['cedula_estudiante'],
                'nombre_estudiante' => $fila['nombre_estudiante'],
                'grado_seccion' => ($fila['nombre_grado'] ?? 'N/A') . ' - ' . ($fila['nombre_seccion'] ?? 'N/A'),
                'fecha_nacimiento' => $fila['fecha_nacimiento'] ?? '',
                'direccion' => $fila['direccion'] ?? '',
                'telefono' => $fila['telefono'] ?? '',
                'nombre_representante' => $fila['nombre_representante'] ?? '',
                'cedula_representante' => $fila['cedula_representante'] ?? '',
                'parentesco' => $fila['parentesco'] ?? ''
            ];
        } else {
            throw new Exception("No se encontró ningún estudiante asociado a esa cédula.");
        }
        
        $stmt->close();
    } catch (Exception $e) {
        redirectWithMessage("constancias.php", $e->getMessage(), true);
    }
}

// Procesar solicitud de constancia
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['solicitar_constancia'])) {
    try {
        // Validar campos requeridos
        $required_fields = [
            'id_estudiante' => 'ID del estudiante',
            'cedula_estudiante' => 'Cédula del estudiante',
            'nombre_estudiante' => 'Nombre del estudiante',
            'grado_estudiante' => 'Grado y sección',
            'tipo_constancia' => 'Tipo de constancia',
            'nombre_representante' => 'Nombre del representante',
            'cedula_representante' => 'Cédula del representante',
            'parentesco' => 'Parentesco'
        ];
        
        $missing_fields = [];
        foreach ($required_fields as $field => $name) {
            if (empty($_POST[$field])) {
                $missing_fields[] = $name;
            }
        }
        
        if (!empty($missing_fields)) {
            throw new Exception("Los siguientes campos son requeridos: " . implode(", ", $missing_fields));
        }
        
        // Validar tipo de constancia
        if (!isset($tipos_constancia[$_POST['tipo_constancia']])) {
            throw new Exception("Tipo de constancia no válido");
        }

        // Sanitizar datos
        $id_estudiante = (int)$_POST['id_estudiante'];
        $cedula_estudiante = $conexion->real_escape_string($_POST['cedula_estudiante']);
        $nombre_estudiante = $conexion->real_escape_string($_POST['nombre_estudiante']);
        $grado_estudiante = $conexion->real_escape_string($_POST['grado_estudiante']);
        $id_tipo_constancia = (int)$_POST['tipo_constancia'];
        $nombre_representante = $conexion->real_escape_string($_POST['nombre_representante']);
        $cedula_representante = $conexion->real_escape_string($_POST['cedula_representante']);
        $parentesco = $conexion->real_escape_string($_POST['parentesco']);
        $observaciones = $conexion->real_escape_string($_POST['observaciones'] ?? '');
        $fecha_solicitud = date("Y-m-d H:i:s");
        $estado = 'Pendiente';

        // Insertar solicitud con transacción para mayor seguridad
        $conexion->begin_transaction();
        
        try {
            $sql_solicitud = "INSERT INTO solicitudes_constancias (
                                id_estudiante, cedula_estudiante, nombre_estudiante, grado_seccion, id_tipo_constancia,
                                nombre_representante, cedula_representante, parentesco, observaciones,
                                fecha_solicitud, estado
                              ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conexion->prepare($sql_solicitud);
            if (!$stmt) {
                throw new Exception("Error al preparar la consulta: " . $conexion->error);
            }
            
            $stmt->bind_param(
                "isssissssss",
                $id_estudiante,
                $cedula_estudiante,
                $nombre_estudiante,
                $grado_estudiante,
                $id_tipo_constancia,
                $nombre_representante,
                $cedula_representante,
                $parentesco,
                $observaciones,
                $fecha_solicitud,
                $estado
            );
            
            if (!$stmt->execute()) {
                throw new Exception("Error al guardar la solicitud: " . $stmt->error);
            }
            
            $id_solicitud = $conexion->insert_id;
            $conexion->commit();
            
            // Generar PDF
            generarConstanciaPDF(
                $nombre_estudiante,
                $cedula_estudiante,
                $grado_estudiante,
                $nombre_representante,
                $cedula_representante,
                $parentesco,
                $tipos_constancia[$id_tipo_constancia],
                $observaciones
            );
            
        } catch (Exception $e) {
            $conexion->rollback();
            throw $e;
        }
        
    } catch (Exception $e) {
        redirectWithMessage("constancias.php", $e->getMessage(), true);
    }
}

// Función para generar el PDF
function generarConstanciaPDF($nombre_estudiante, $cedula_estudiante, $grado_estudiante, 
                            $nombre_representante, $cedula_representante, $parentesco, 
                            $tipo_constancia, $observaciones) {
    try {
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // Configuración del documento
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Institución Educativa');
        $pdf->SetTitle('Constancia Escolar');
        $pdf->SetSubject('Constancia Escolar');
        $pdf->SetKeywords('constancia, escolar, estudiante');

        // Desactivar el encabezado y pie de página  
    $pdf->SetPrintHeader(false);  
    $pdf->SetPrintFooter(false);  

    // Establecer márgenes  
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);  
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);  
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER); 

        // Fuente
        $pdf->SetFont('helvetica', '', 12);

        // Añadir página
        $pdf->AddPage();

        // Agregar el logo en la esquina superior izquierda (ajustado a la izquierda)  
    $logo = 'logos/logo.png'; // Ruta del logo  
    $pdf->Image($logo, 5, 10, 35, '', 'PNG', '', '', false, 300, '', false, false, 0, false, false, false);  

    // Agregar el escudo en la esquina superior derecha (ajustado a la derecha)  
    $escudo = 'logos/Escudo.jpg'; // Ruta del escudo  
    $pdf->Image($escudo, $pdf->getPageWidth() - 40, 10, 35, '', 'JPG', '', '', false, 300, '', false, false, 0, false, false, false);  


        // Contenido del PDF
        $html = '
        <div style="text-align:center; margin-top: 10px;">
            <h1 style="font-size: 18px; font-weight: bold;">U.E.N ROBERTO MARTINEZ CENTENO</h1>
            <p style="font-size: 14px;">Teléfono: (0212) 123-4567</p>
        </div>

        <div style="text-align:center; margin: 10px 0;">
            <h2 style="font-size: 16px; font-weight: bold; text-decoration: underline;">CONSTANCIA ESCOLAR</h2>
        </div>
        <p style="text-align: justify;">Quien suscribe, Director(a) de la Institución Educativa U.E.N ROBERTO MARTINEZ CENTENO, hace constar que el(la) estudiante:</p>
        <table border="0" cellpadding="5">
            <tr>
                <td width="30%"><strong>Nombre Completo:</strong></td>
                <td width="70%">' . htmlspecialchars($nombre_estudiante) . '</td>
            </tr>
            <tr>
                <td><strong>Cédula de Identidad:</strong></td>
                <td>' . htmlspecialchars($cedula_estudiante) . '</td>
            </tr>
            <tr>
                <td><strong>Grado y Sección:</strong></td>
                <td>' . htmlspecialchars($grado_estudiante) . '</td>
            </tr>
        </table>

        <p style="text-align: justify; margin-top: 10px;">
            Está regularmente inscrito(a) en esta institución para el año escolar en curso, según consta en nuestros registros.
        </p>

        <p style="text-align: justify;">
            La presente constancia se expide a solicitud del(la) representante legal:
        </p>

        <table border="0" cellpadding="5">
            <tr>
                <td width="30%"><strong>Representante:</strong></td>
                <td width="70%">' . htmlspecialchars($nombre_representante) . '</td>
            </tr>
            <tr>
                <td><strong>Cédula:</strong></td>
                <td>' . htmlspecialchars($cedula_representante) . '</td>
            </tr>
            <tr>
                <td><strong>Parentesco:</strong></td>
                <td>' . htmlspecialchars($parentesco) . '</td>
            </tr>
            <tr>
                <td><strong>Tipo de Constancia:</strong></td>
                <td>' . htmlspecialchars($tipo_constancia) . '</td>
            </tr>
        </table>';

        $html .= '
        <p style="text-align: justify; margin-top: 10px;">
            Constancia que se expide en Caracas, a los ' . date('d') . ' días del mes de ' . obtenerMesEnEspanol(date('m')) . ' del año ' . date('Y') . '.
        </p>

        <div style="margin-top: 40px; text-align: center;">
            <p style="border-top: 1px solid #000; width: 50px; margin: 0 auto; padding-top: 5px;">
                <strong>Firma y Sello</strong>
            </p>
            <p style="margin-top: 2px;">
                <strong>Director(a) de la Institución</strong>
            </p>
        </div>';

        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Output('constancia_' . preg_replace('/[^a-zA-Z0-9]/', '_', $cedula_estudiante) . '.pdf', 'I');
        exit();
        
    } catch (Exception $e) {
        redirectWithMessage("constancias.php", "Error al generar el PDF: " . $e->getMessage(), true);
    }
}

// Función auxiliar para obtener el mes en español
function obtenerMesEnEspanol($mes) {
    $meses = [
        '01' => 'enero', '02' => 'febrero', '03' => 'marzo', '04' => 'abril',
        '05' => 'mayo', '06' => 'junio', '07' => 'julio', '08' => 'agosto',
        '09' => 'septiembre', '10' => 'octubre', '11' => 'noviembre', '12' => 'diciembre'
    ];
    return $meses[$mes];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Constancias Escolares</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2980b9;
            --accent-color: #e74c3c;
            --light-color: #ecf0f1;
            --dark-color: #2c3e50;
            --success-color: #2ecc71;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background-color: #f5f5f5;
            color: #333;
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            margin-top: 30px;
            margin-bottom: 30px;
        }
        
        h1, h2, h3 {
            color: var(--dark-color);
            margin-bottom: 20px;
        }
        
        h1 {
            font-size: 28px;
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 2px solid var(--light-color);
            margin-bottom: 30px;
        }
        
        h2 {
            font-size: 22px;
            margin-top: 30px;
            color: var(--primary-color);
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .search-section {
            background-color: var(--light-color);
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -10px;
        }
        
        .form-row .form-group {
            flex: 1;
            min-width: 250px;
            padding: 0 10px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark-color);
        }
        
        .required-field::after {
            content: " *";
            color: var(--danger-color);
        }
        
        input[type="text"],
        input[type="number"],
        input[type="date"],
        select,
        textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        input[type="text"]:focus,
        input[type="number"]:focus,
        input[type="date"]:focus,
        select:focus,
        textarea:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }
        
        textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        .btn {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .btn i {
            margin-right: 8px;
        }
        
        .btn-block {
            display: block;
            width: 100%;
        }
        
        .signature-section {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin-top: 30px;
            border: 1px dashed #ccc;
        }
        
        .signature-preview {
            background-color: white;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
            margin-top: 15px;
            color: var(--primary-color);
            font-weight: 500;
            border: 1px solid #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .signature-preview i {
            font-size: 24px;
            margin-right: 10px;
        }
        
        .student-info-card {
            background-color: #f8f9fa;
            border-left: 4px solid var(--primary-color);
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }
        
        .student-info-card h3 {
            color: var(--primary-color);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .student-info-card p {
            margin-bottom: 10px;
            padding-left: 10px;
            border-left: 2px solid #eee;
        }
        
        .student-info-card strong {
            color: var(--dark-color);
            min-width: 150px;
            display: inline-block;
        }
        
        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
            }
            
            .form-row .form-group {
                padding: 0;
                margin-bottom: 15px;
            }
            
            .container {
                padding: 15px;
                margin-top: 15px;
                margin-bottom: 15px;
            }
            
            .student-info-card p {
                display: flex;
                flex-direction: column;
                gap: 5px;
            }
            
            .student-info-card strong {
                min-width: auto;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="fas fa-file-certificate"></i> Constancias Escolares</h1>

        <?php if (isset($_SESSION['mensaje'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> 
                <span><?php echo $_SESSION['mensaje']; unset($_SESSION['mensaje']); ?></span>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['mensaje_error'])): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> 
                <span><?php echo $_SESSION['mensaje_error']; unset($_SESSION['mensaje_error']); ?></span>
            </div>
        <?php endif; ?>

        <div class="search-section">
            <h2><i class="fas fa-search"></i> Buscar Estudiante</h2>
            <form method="POST">
                <div class="form-group">
                    <label for="cedula_busqueda" class="required-field">Cédula del Estudiante o Representante</label>
                    <input type="text" id="cedula_busqueda" name="cedula_busqueda" required 
                           placeholder="Ej: V-12345678" pattern="[VEve][-]?\d{6,8}" 
                           title="Formato válido: V12345678, V-12345678, E1234567 o E-1234567">
                </div>
                <button type="submit" class="btn" name="buscar_cedula">
                    <i class="fas fa-search"></i> Buscar Estudiante
                </button>
            </form>
        </div>

        <?php if ($datos_estudiante): ?>
            <div class="student-info-card">
                <h3><i class="fas fa-user-graduate"></i> Información del Estudiante</h3>
                <p><strong>Nombre:</strong> <?= htmlspecialchars($datos_estudiante['nombre_estudiante']) ?></p>
                <p><strong>Cédula:</strong> <?= htmlspecialchars($datos_estudiante['cedula_estudiante']) ?></p>
                <p><strong>Grado/Sección:</strong> <?= htmlspecialchars($datos_estudiante['grado_seccion']) ?></p>
                <?php if (!empty($datos_estudiante['fecha_nacimiento'])): ?>
                    <p><strong>Fecha de Nacimiento:</strong> <?= date('d/m/Y', strtotime($datos_estudiante['fecha_nacimiento'])) ?></p>
                <?php endif; ?>
                <?php if (!empty($datos_estudiante['direccion'])): ?>
                    <p><strong>Dirección:</strong> <?= htmlspecialchars($datos_estudiante['direccion']) ?></p>
                <?php endif; ?>
                <?php if (!empty($datos_estudiante['telefono'])): ?>
                    <p><strong>Teléfono:</strong> <?= htmlspecialchars($datos_estudiante['telefono']) ?></p>
                <?php endif; ?>
            </div>

            <form action="constancias.php" method="POST">
                <input type="hidden" name="id_estudiante" value="<?= $datos_estudiante['id_estudiante'] ?? '' ?>">
                <input type="hidden" name="cedula_estudiante" value="<?= htmlspecialchars($datos_estudiante['cedula_estudiante']) ?>">
                <input type="hidden" name="nombre_estudiante" value="<?= htmlspecialchars($datos_estudiante['nombre_estudiante']) ?>">
                <input type="hidden" name="grado_estudiante" value="<?= htmlspecialchars($datos_estudiante['grado_seccion']) ?>">

                <h2><i class="fas fa-file-alt"></i> Datos de la Constancia</h2>
                
                <div class="form-group">
                    <label for="tipo_constancia" class="required-field">Tipo de Constancia</label>
                    <select id="tipo_constancia" name="tipo_constancia" required>
                        <option value="">Seleccione un tipo...</option>
                        <?php foreach ($tipos_constancia as $id => $tipo): ?>
                            <option value="<?= htmlspecialchars($id) ?>" <?= (isset($_POST['tipo_constancia']) && $_POST['tipo_constancia'] == $id) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($tipo) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <h2><i class="fas fa-user-tie"></i> Datos del Representante</h2>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="nombre_representante" class="required-field">Nombre Completo</label>
                        <input type="text" id="nombre_representante" name="nombre_representante" 
                               value="<?= htmlspecialchars($datos_estudiante['nombre_representante']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="cedula_representante" class="required-field">Cédula</label>
                        <input type="text" id="cedula_representante" name="cedula_representante" 
                               value="<?= htmlspecialchars($datos_estudiante['cedula_representante']) ?>" required
                               pattern="[VEve][-]?\d{6,8}" title="Formato válido: V12345678, V-12345678, E1234567 o E-1234567">
                    </div>
                </div>

                <div class="form-group">
    <label for="parentesco" class="required-field">Parentesco</label>
    <select id="parentesco" name="parentesco" required>
        <option value="">Seleccione un parentesco...</option>
        <option value="Madre" <?= (isset($datos_estudiante['parentesco']) && $datos_estudiante['parentesco'] == 'Madre') ? 'selected' : '' ?>>Madre</option>
        <option value="Padre" <?= (isset($datos_estudiante['parentesco']) && $datos_estudiante['parentesco'] == 'Padre') ? 'selected' : '' ?>>Padre</option>
        <option value="Representante Legal" <?= (isset($datos_estudiante['parentesco']) && $datos_estudiante['parentesco'] == 'Representante Legal') ? 'selected' : '' ?>>Representante Legal</option>
        <option value="Tutor" <?= (isset($datos_estudiante['parentesco']) && $datos_estudiante['parentesco'] == 'Tutor') ? 'selected' : '' ?>>Tutor</option>
        <option value="Abuelo/a" <?= (isset($datos_estudiante['parentesco']) && $datos_estudiante['parentesco'] == 'Abuelo/a') ? 'selected' : '' ?>>Abuelo/a</option>
        <option value="Tío/a" <?= (isset($datos_estudiante['parentesco']) && $datos_estudiante['parentesco'] == 'Tío/a') ? 'selected' : '' ?>>Tío/a</option>
        <option value="Hermano/a" <?= (isset($datos_estudiante['parentesco']) && $datos_estudiante['parentesco'] == 'Hermano/a') ? 'selected' : '' ?>>Hermano/a</option>
        <option value="Otro" <?= (isset($datos_estudiante['parentesco']) && $datos_estudiante['parentesco'] == 'Otro') ? 'selected' : '' ?>>Otro</option>
    </select>
</div>

                <div class="form-group" style="margin-top: 30px;">
                    <button type="submit" class="btn btn-block" name="solicitar_constancia">
                        <i class="fas fa-file-download"></i> Generar Constancia PDF
                    </button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>