<?php
include ('../../app/config.php');

// Obtener datos del estudiante
$id_estudiante = $_GET['id_estudiante'] ?? $_POST['id_estudiante'] ?? null;

if (!$id_estudiante) {
    die("Error: No se especificó el estudiante");
}

// Obtener fecha de vencimiento (si se envía por formulario)
$fecha_vencimiento = $_POST['fecha_vencimiento'] ?? date('Y-m-d', strtotime('+1 year'));

// Primero, obtener los datos básicos del estudiante
$query_estudiante_basic = $pdo->prepare("
    SELECT * FROM estudiantes 
    WHERE id_estudiante = :id_estudiante
");
$query_estudiante_basic->bindParam(':id_estudiante', $id_estudiante);
$query_estudiante_basic->execute();
$estudiante = $query_estudiante_basic->fetch(PDO::FETCH_ASSOC);

if (!$estudiante) {
    die("Error: Estudiante no encontrado");
}

// Luego, intentar obtener información del grado y sección si existen
$query_inscripcion = $pdo->prepare("
    SELECT 
        g.grado as nombre_grado, 
        s.nombre_seccion
    FROM inscripciones i
    LEFT JOIN grados g ON i.grado = g.id_grado
    LEFT JOIN secciones s ON i.id_seccion = s.id_seccion
    WHERE i.id_estudiante = :id_estudiante
    LIMIT 1
");
$query_inscripcion->bindParam(':id_estudiante', $id_estudiante);
$query_inscripcion->execute();
$inscripcion_data = $query_inscripcion->fetch(PDO::FETCH_ASSOC);

// Combinar los datos
if ($inscripcion_data) {
    $estudiante['nombre_grado'] = $inscripcion_data['nombre_grado'] ?? 'SIN GRADO ASIGNADO';
    $estudiante['nombre_seccion'] = $inscripcion_data['nombre_seccion'] ?? '';
} else {
    $estudiante['nombre_grado'] = 'SIN GRADO ASIGNADO';
    $estudiante['nombre_seccion'] = '';
}

// Si no hay grado asignado, usar un valor por defecto
if (empty($estudiante['nombre_grado'])) {
    $estudiante['nombre_grado'] = 'SIN GRADO ASIGNADO';
}

// Si no hay sección, usar valor por defecto
if (empty($estudiante['nombre_seccion'])) {
    $estudiante['nombre_seccion'] = '';
}

// Incluir la librería TCPDF
require_once('../../app/tcpdf/tcpdf.php');

// Crear nuevo documento PDF en formato vertical (85.6x53.98mm es tamaño estándar de tarjeta)
$pdf = new TCPDF('P', 'mm', array(53.98, 85.6), true, 'UTF-8', false);

// Configurar información del documento
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('ROBERTO MARTINEZ CENTENO');
$pdf->SetTitle('Carnet Estudiantil - ' . $estudiante['nombres'] . ' ' . $estudiante['apellidos']);
$pdf->SetSubject('Carnet de Identificación Estudiantil');

// Eliminar márgenes
$pdf->SetMargins(0, 0, 0);
$pdf->SetHeaderMargin(0);
$pdf->SetFooterMargin(0);
$pdf->SetAutoPageBreak(false, 0);

// Agregar página para la parte DELANTERA
$pdf->AddPage();

// CORREGIDO: Definir rutas absolutas para las imágenes
$base_dir = dirname(dirname(dirname(__FILE__))); // Obtiene el directorio base del proyecto

// Ruta para la foto del estudiante
$foto_estudiante_ruta = $base_dir . '/public/fotos_estudiantes/';
$foto_default_ruta = $base_dir . '/public/images/no-image-available.png';

// Verificar si existe la foto del estudiante
$mostrar_boton_estudiante = true;
$foto_estudiante = $foto_default_ruta; // Por defecto usa la imagen no disponible

if (!empty($estudiante['foto'])) {
    $ruta_foto_estudiante = $foto_estudiante_ruta . $estudiante['foto'];
    if (file_exists($ruta_foto_estudiante)) {
        $foto_estudiante = $ruta_foto_estudiante;
        $mostrar_boton_estudiante = false;
    }
}

// Ruta para la MARCA DE AGUA (fondo transparente)
$marca_agua_ruta = $base_dir . '/public/images/Texto del párrafo.png'; // Cambiado a nombre específico
$marca_agua_default = $base_dir . '/public/images/no-image-available.png'; // Alternativa

// Ruta para el LOGO (imagen normal)
$logo_ruta = $base_dir . '/public/images/logo.png'; // Cambiado a nombre específico
$logo_default = $base_dir . '/public/images/Texto del párrafo.png'; // Tu logo original como alternativa

// Verificar si existe la marca de agua
$marca_agua = '';
if (file_exists($marca_agua_ruta)) {
    $marca_agua = $marca_agua_ruta;
} elseif (file_exists($marca_agua_default)) {
    $marca_agua = $marca_agua_default;
}

// Verificar si existe el logo
$logo = '';
if (file_exists($logo_ruta)) {
    $logo = $logo_ruta;
} elseif (file_exists($logo_default)) {
    $logo = $logo_default;
}

// Generar datos para el código QR
$qrData = "ESTUDIANTE: " . $estudiante['nombres'] . " " . $estudiante['apellidos'] . "\n";
$qrData .= "CÉDULA: " . $estudiante['tipo_cedula'] . "-" . $estudiante['cedula'] . "\n";
$qrData .= "INSTITUCIÓN: ROBERTO MARTINEZ CENTENO\n";
$qrData .= "GRADO: " . $estudiante['nombre_grado'] . "\n";
if (!empty($estudiante['nombre_seccion'])) {
    $qrData .= "SECCIÓN: " . $estudiante['nombre_seccion'] . "\n";
}
$qrData .= "VENCE: " . date('d/m/Y', strtotime($fecha_vencimiento));

// Definir estilo para el código QR
$style = array(
    'border' => 0,
    'vpadding' => 'auto',
    'hpadding' => 'auto',
    'fgcolor' => array(0,0,0),
    'bgcolor' => false,
    'module_width' => 1,
    'module_height' => 1
);

// CORREGIDO: AGREGAR SOLO LA MARCA DE AGUA (si existe)
if (!empty($marca_agua) && file_exists($marca_agua)) {
    $ancho_imagen = 40;
    $alto_imagen = 40;

    // Calcular posición centrada
    $x_centro = (53.98 - $ancho_imagen) / 2;
    $y_centro = (85.6 - $alto_imagen) / 2;

    // Agregar la imagen como marca de agua (con transparencia)
    $pdf->SetAlpha(1); // Más transparente para marca de agua
    $pdf->Image($marca_agua, $x_centro, $y_centro, $ancho_imagen, $alto_imagen, '', '', '', false, 300, '', false, false, 0);
    $pdf->SetAlpha(1);
}

// Crear el contenido HTML para la parte DELANTERA del carnet
$html_front = '
<style>
    .carnet-container {
        width: 54mm;
        height: 85.6mm;
        position: relative;
        overflow: hidden;
        background: white;
        font-family: Arial, sans-serif;
    }
    .header {
        background-color: white;
        color: black;
        text-align: center;
        font-size: 4.8px;
        padding: 1px;
        margin-bottom: 1px;
    }
    .subheader {
        width: 18mm;
        height: 18mm;
        background-color: #003366;
        color: white;
        display: flex;
        justify-content: center;
        align-items: center;
        border-radius: 4px;
        font-weight: bold;
        font-size: 8px;
        text-align: center;
        text-transform: uppercase;
        margin: 0 auto;
        margin-top: 1px;
        margin-bottom: 2px;
    }
    .photo-container {
        text-align: center;
        margin: 2px auto;
        height: 20mm;
        width: 20mm;
        display: flex;
        justify-content: center;
        align-items: center;
        border-radius: 2px;
        overflow: hidden;
        position: relative;
    }
    .student-photo {
        height: 20mm;
        width: 20mm;
        object-fit: cover;
        border: 2px solid #003366;
        border-radius: 1px;
    }
    .student-info {
        margin: 2px 0;
        font-size: 6px;
        line-height: 1.2;
        text-align: center;
    }
    .info-label {
        font-weight: bold;
        display: inline-block;
        width: 15mm;
    }
    .student-name {
        font-weight: bold;
        font-size: 7px;
        text-align: center;
        margin: 2px 0;
        text-transform: uppercase;
        color: blue;
        padding: 0 2px;
    }
    .footer {
        position: absolute;
        bottom: 2px;
        width: 100%;
        text-align: center;
        font-size: 5px;
        padding-top: 1px;
    }
    .dates {
        font-size: 5.5px;
        text-align: center;
        margin: 2px 0;
    }
    .content {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
    .no-photo {
        font-size: 5px;
        color: #666;
        text-align: center;
        position: absolute;
        bottom: 2px;
        width: 100%;
    }
    .photo-frame {
        border: 2px solid #003366;
        border-radius: 3px;
        padding: 1px;
        background: white;
        display: inline-block;
    }
</style>

<div class="carnet-container">
    <div class="header">
        REPÚBLICA BOLIVARIANA DE VENEZUELA<br>
        MINISTERIO DEL PODER POPULAR PARA LA EDUCACIÓN<br>
        UNIDAD EDUCATIVA "ROBERTO MARTINEZ CENTENO"<br>
        CÓDIGO N° S1570D0104 / TELÉFONO: 212-4320310<br>
        CARICUAO - CARACAS - DISTRITO CAPITAL
    </div>
    
    <div class="subheader">
        ESTUDIANTE
    </div>
    
    <div class="content">
        <div class="photo-container">';
        
// Siempre mostrar el texto "SIN FOTO" en el contenedor
$html_front .= '<div class="no-photo">SIN FOTO</div>';

$html_front .= '</div>
        
        <div class="student-name">' . htmlspecialchars($estudiante['nombres'] . ' ' . $estudiante['apellidos']) . '</div>
        
        <div class="student-info">
            <span class="info-label">CÉDULA:</span> ' . htmlspecialchars($estudiante['tipo_cedula']) . '-' . htmlspecialchars($estudiante['cedula']) . '<br>
            <span class="info-label">GRADO:</span> ' . htmlspecialchars($estudiante['nombre_grado']) . '<br>';
            
if (!empty($estudiante['nombre_seccion'])) {
    $html_front .= '<span class="info-label">SECCIÓN:</span> ' . htmlspecialchars($estudiante['nombre_seccion']) . '<br>';
}

$html_front .= '
        </div>
        
        <div class="dates">
            ELAB.: ' . date('d-m-Y') . '<br>
            VENC.: ' . date('d-m-Y', strtotime($fecha_vencimiento)) . '
        </div>
    </div>
</div>';

// Escribir el contenido HTML para la parte delantera
$pdf->writeHTML($html_front, true, false, true, false, '');

// Si hay foto disponible, agregarla directamente con TCPDF encima del texto
if (!$mostrar_boton_estudiante) {
    // Agregar un marco alrededor de la foto
    $pdf->SetLineStyle(array('width' => 0.5, 'cap' => 'round', 'join' => 'round', 'dash' => 0, 'color' => array(0, 51, 102)));
    $pdf->Rect(16.5, 26, 21, 21); // Marco exterior
    
    // Agregar la foto del estudiante con su propio marco
    $pdf->Image($foto_estudiante, 17.5, 27, 19, 19, '', '', '', false, 300, '', false, false, 0, false, false, true);
}

// CORREGIDO: AGREGAR SOLO EL LOGO EN LA PARTE IZQUIERDA (si existe)
if (!empty($logo) && file_exists($logo)) {
    // Tamaño más pequeño para el logo
    $logo_width = 12;
    $logo_height = 12;
    
    // Posición en la esquina inferior izquierda
    $logo_x = 2; // 2mm desde el borde izquierdo
    $logo_y = 65; // Misma altura que el QR
    
    // Agregar el logo sin transparencia
    $pdf->Image($logo, $logo_x, $logo_y, $logo_width, $logo_height, '', '', '', false, 300, '', false, false, 0, false, false, true);
}

// Agregar el código QR en la parte delantera (esquina inferior derecha)
$pdf->write2DBarcode($qrData, 'QRCODE,L', 36.98, 65, 15, 15, $style, 'N');

// Agregar página para la parte TRASERA
$pdf->AddPage();

// Crear el contenido HTML para la parte TRASERA del carnet
$html_back = '
<style>
    .carnet-container {
        width: 54mm;
        height: 85.6mm;
        position: relative;
        overflow: hidden;
        background: white;
        font-family: Arial, sans-serif;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }
    .header {
        background-color: white;
        color: black;
        text-align: center;
        padding: 2px;
        font-size: 5.5px;
        line-height: 1.2;
        margin-bottom: 2mm;
    }
    .content {
        padding: 3mm;
        position: relative;
        z-index: 1;
        height: 100%;
        width: 80%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        align-items: center;
        text-align: center;
    }
    .instructions {
        font-size: 5px;
        text-align: center;
        margin: 2px 0;
        color: black;
        width: 100%;
    }
    .signature {
        text-align: center;
        font-size: 4px;
        margin-top: 2mm;
        width: 100%;
    }
    .contact-info {
        font-size: 4px;
        text-align: center;
        margin-top: 2mm;
        width: 100%;
    }
</style>

<div class="carnet-container">
    <div class="header">
        INFORMACIÓN IMPORTANTE
    </div>
    
    <div class="content">
        <div class="instructions">
            Este carnet identifica al estudiante de la Unidad Educativa<br>
            Nacional "ROBERTO MARTINEZ CENTENO" y debe ser portado visiblemente dentro de la institución.<br><br>
            En caso de pérdida, el titular deberá reportarlo inmediatamente a la secretaría de la institución.
        </div>
        
        <div class="signature">
            <div>_________________________</div>
            <div>Firma del Director</div>
        </div>
        
        <div class="contact-info">
            EN CASO DE EMERGENCIA LLAMAR AL 0212-555-5555<br>
            www.ROBERTOMARTINEZCENTENO.edu.ve | Ejemplo@gmail.com
        </div>
    </div>
</div>';

// Escribir el contenido HTML para la parte trasera
$pdf->writeHTML($html_back, true, false, true, false, '');

// Cerrar y generar el PDF
$pdf->Output('carnet_' . $estudiante['cedula'] . '.pdf', 'I');
?>