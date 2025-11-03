<?php
include ('../../app/config.php');

// Obtener datos del formulario
$id_plantilla = $_POST['id_plantilla'];
$fecha_vencimiento = $_POST['fecha_vencimiento'];
$id_estudiante = $_POST['id_estudiante'];
$color_texto = isset($_POST['color_texto']) ? $_POST['color_texto'] : 'black';
$color_encabezado = isset($_POST['color_encabezado']) ? $_POST['color_encabezado'] : 'white';

// Obtener información del estudiante seleccionado incluyendo el grado
$query_estudiante = $pdo->prepare("
    SELECT e.*, g.grado as nombre_grado, s.nombre_seccion 
    FROM estudiantes e
    JOIN inscripciones i ON e.id_estudiante = i.id_estudiante
    JOIN grados g ON i.grado = g.id_grado
    JOIN secciones s ON i.id_seccion = s.id_seccion
    WHERE e.id_estudiante = :id_estudiante
");
$query_estudiante->bindParam(':id_estudiante', $id_estudiante);
$query_estudiante->execute();
$estudiante = $query_estudiante->fetch(PDO::FETCH_ASSOC);

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

// Obtener nombres y apellidos
$nombres = $estudiante['nombres'];
$apellidos = $estudiante['apellidos'];

// Separar nombres y apellidos en dos líneas
$partes_nombre = explode(' ', $nombres);
$partes_apellido = explode(' ', $apellidos);

$nombre_linea1 = $partes_nombre[0];
$nombre_linea2 = count($partes_nombre) > 1 ? $partes_nombre[1] : '';

$apellido_linea1 = $partes_apellido[0];
$apellido_linea2 = count($partes_apellido) > 1 ? $partes_apellido[1] : '';

// Usar siempre la foto predeterminada si no hay foto específica
$foto_estudiante = '../../public/images/students/default.jpg';
$mostrar_boton_estudiante = false;

if (!empty($estudiante['foto'])) {
    // Verificar si existe la foto en la carpeta de estudiantes
    $ruta_foto_estudiante = '../../public/images/students/' . $estudiante['foto'];
    if (file_exists($ruta_foto_estudiante)) {
        $foto_estudiante = $ruta_foto_estudiante;
    } else {
        $mostrar_boton_estudiante = true;
    }
} else {
    $mostrar_boton_estudiante = true;
}

// Generar datos para el código QR (igual para ambos lados)
$qrData = "ESTUDIANTE: " . $estudiante['nombres'] . " " . $estudiante['apellidos'] . "\n";
$qrData .= "CÉDULA: " . $estudiante['tipo_cedula'] . "-" . $estudiante['cedula'] . "\n";
$qrData .= "INSTITUCIÓN: ROBERTO MARTINEZ CENTENO\n";
$qrData .= "NIVEL-GRADO-AÑO: " . $estudiante['nombre_grado'] . "\n";
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
        padding: 2px;
        font-size: 5.5px;
        line-height: 1.2;
    }
    .subheader {
        width: 18mm;
        height: 18mm;
        background-color: #003366; /* Azul marino */
        color: white;
        display: flex;
        justify-content: center;
        align-items: center;
        border-radius: 4px;
        font-weight: bold;
        font-size: 8px;
        text-align: center;
        text-transform: uppercase;
        margin: 0 auto; /* Centrar horizontalmente con márgenes automáticos */
        margin-top: 2px; /* Espacio superior */
        margin-bottom: 2px; /* Espacio inferior */
    }
    .content {
        padding: 2px;
    }
    .institution-info {
        text-align: center;
        margin: 2px 0;
        font-size: 5.5px;
        line-height: 1.2;
    }
    .photo-container {
        text-align: center;
        margin: 3px auto;
        height: 25mm;
        width: 25mm;
        display: flex;
        justify-content: center;
        align-items: center;
        border: 1px solid #ccc;
        border-radius: 2px;
    }
    .student-photo {
        max-height: 24mm;
        max-width: 24mm;
        object-fit: cover;
    }
    .student-info {
        margin: 3px 0;
        font-size: 6px;
        line-height: 1.2;
    }
    .info-label {
        font-weight: bold;
        display: inline-block;
        width: 20mm;
    }
    .student-name {
        font-weight: bold;
        font-size: 7px;
        text-align: center;
        margin: 2px 0;
        text-transform: uppercase;
        color: blue;
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
    .qr-mini-area {
        position: absolute;
        bottom: 5mm;
        right: 2mm;
        width: 15mm;
        height: 15mm;
    }
</style>

<div class="carnet-container">
    <div class="header">
        REPÚBLICA BOLIVARIANA DE VENEZUELA<br>
        MINISTERIO DEL PODER POPULAR PARA LA EDUCACIÓN<br>
        UNIDAD EDUCATIVA NACIONAL<br>
        "ROBERTO MARTINEZ CENTENO"
    </div>
    
    <div class="subheader">
        ESTUDIANTE
    </div>
    
    <div class="content">
        <div class="photo-container">';
        
if ($mostrar_boton_estudiante) {
    $html_front .= '<div class="student-button">SIN FOTO</div>';
} else {
    $html_front .= '<img class="student-photo" src="' . $foto_estudiante . '" />';
}

$html_front .= '
        </div>
        
        <div class="student-name">' . htmlspecialchars($estudiante['nombres'] . ' ' . $estudiante['apellidos']) . '</div>
        
        <div class="student-info">
            <span class="info-label">CÉDULA:</span> ' . htmlspecialchars($estudiante['tipo_cedula']) . '-' . htmlspecialchars($estudiante['cedula']) . '<br>
            <span class="info-label">NIVEL-GRADO-AÑO:</span> ' . htmlspecialchars($estudiante['nombre_grado']) . '
        </div>
        
        <div class="dates">
            ELAB.: ' . date('d-m-Y') . '<br>
            VENC.: ' . date('d-m-Y', strtotime($fecha_vencimiento)) . '
        </div>
    </div>
    
    <div class="footer">
        DIVISION DE ADMINISTRACION ESCOLAR
    </div>
</div>';

// Escribir el contenido HTML para la parte delantera
$pdf->writeHTML($html_front, true, false, true, false, '');

// Agregar el código QR en la parte delantera (esquina inferior derecha)
$pdf->write2DBarcode($qrData, 'QRCODE,L', 36.98, 65, 15, 15, $style, 'N');

// Agregar página para la parte TRASERA
$pdf->AddPage();

// Ruta a tu imagen de marca de agua/logo
$marca_agua = '../../public/images/logo.png'; // Ajusta esta ruta

// Verificar si el archivo existe, si no, usar una alternativa
if (!file_exists($marca_agua)) {
    $marca_agua = '../../public/images/default_logo.png'; // Imagen alternativa
}

// Agregar la marca de agua en el centro de la página
// Calculamos la posición centrada (85.6x53.98mm es el tamaño de página)
$ancho_imagen = 30; // Ancho deseado para la marca de agua en mm
$alto_imagen = 30;  // Alto deseado para la marca de agua en mm

// Calcular posición centrada
$x_centro = (53.98 - $ancho_imagen) / 2;
$y_centro = (85.6 - $alto_imagen) / 2;

// Agregar la imagen como marca de agua (con transparencia)
$pdf->SetAlpha(0.2); // Establecer transparencia (0 = totalmente transparente, 1 = totalmente opaco)
$pdf->Image($marca_agua, $x_centro, $y_centro, $ancho_imagen, $alto_imagen, '', '', '', false, 300, '', false, false, 0);
$pdf->SetAlpha(1); // Restaurar opacidad normal

// Crear el contenido HTML para la parte TRASERA del carnet con mejor centrado y márgenes
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
        width: 80%; /* Limitar el ancho para crear márgenes laterales */
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
    .qr-container {
        margin: 2mm 0;
        width: 10mm;
        height: 10mm;
    }
    .qr-title {
        font-size: 5.5px;
        margin-bottom: 1px;
        font-weight: bold;
        color: white;
        text-align: center;
    }
    .signature {
        text-align: center;
        font-size: 4px;
        margin-top: 2mm;
        width: 100%;
    }
    .signature-line {
        border-top: 1px solid #ccc;
        width: 80%;
        margin: 3px auto;
        padding-top: 2px;
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
        
        <div class="qr-container">
            <div class="qr-title"></div>
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