<?php  
// Iniciar el almacenamiento en búfer  
ob_start();  

// Incluir la configuración y la biblioteca TCPDF  
include('../../app/config.php');  
require_once 'library/tcpdf.php';

$meses = [
    1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril', 5 => 'mayo', 6 => 'junio',
    7 => 'julio', 8 => 'agosto', 9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'
];
$mes_actual = $meses[date('n')]; 
// Obtener el id del estudiante desde la URL  
$id_estudiante = isset($_GET['id_estudiante']) ? $_GET['id_estudiante'] : null;  

// Obtener los datos del estudiante incluyendo el nombre del grado  
$sql = "SELECT e.nombres, e.apellidos, e.tipo_cedula, e.cedula, e.cedula_escolar, i.nivel_id, gr.grado, i.id_seccion, i.turno_id, g.desde, g.hasta   
        FROM inscripciones i  
        JOIN estudiantes e ON i.id_estudiante = e.id_estudiante  
        JOIN gestiones g ON i.id_gestion = g.id_gestion  
        JOIN grados gr ON i.grado = gr.id_grado  
        WHERE i.id_estudiante = :id_estudiante AND g.estado = 1  
        ORDER BY i.created_at DESC  
        LIMIT 1";  
$stmt = $pdo->prepare($sql);  
$stmt->bindParam(':id_estudiante', $id_estudiante);  
$stmt->execute();  
$inscripcion = $stmt->fetch(PDO::FETCH_ASSOC);  

if ($inscripcion) {  
    // Crear nuevo documento PDF  
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);  

    // Establecer información del documento  
    $pdf->SetCreator(PDF_CREATOR);  
    $pdf->SetAuthor('U.E. Agustín Zamora Quintana');  
    $pdf->SetTitle('Constancia de Inscripción');  
    $pdf->SetSubject('Constancia de Inscripción');  
    $pdf->SetKeywords('TCPDF, PDF, constancia, inscripción');  

    // Desactivar el encabezado y pie de página  
    $pdf->SetPrintHeader(false);  
    $pdf->SetPrintFooter(false);  

    // Establecer márgenes  
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);  
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);  
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);  

    // Añadir una página  
    $pdf->AddPage();  

    // Agregar el logo en la esquina superior izquierda (ajustado a la izquierda)  
    $logo = 'logos/logo.jpg'; // Ruta del logo  
    $pdf->Image($logo, 5, 10, 35, '', 'JPG', '', '', false, 300, '', false, false, 0, false, false, false);  

    // Agregar el escudo en la esquina superior derecha (ajustado a la derecha)  
    $escudo = 'logos/Escudo.jpg'; // Ruta del escudo  
    $pdf->Image($escudo, $pdf->getPageWidth() - 40, 10, 35, '', 'JPG', '', '', false, 300, '', false, false, 0, false, false, false);  

    // Establecer la fuente más pequeña  
    $pdf->SetFont('dejavusans', '', 10, '', true);  

    // Agregar espacio antes del encabezado  
    $pdf->Ln(10); // Añadir un espacio vertical de 10 unidades  

    // Agregar el encabezado centrado  
    $header = '<h2 style="text-align:center;">República Bolivariana de Venezuela</h2>  
               <h2 style="text-align:center;">Ministerio del Poder Popular para la Educación</h2>  
               <h2 style="text-align:center;">U.E.N Agustin Zamora Quintana</h2>  
               <h2 style="text-align:center;">Caracas-Venezuela</h2>';  

    // Imprimir el encabezado  
    $pdf->writeHTMLCell(0, 0, '', '', $header, 0, 1, 0, true, '', true);  

    // Codificar las fechas  
    $desde = date('Y', strtotime($inscripcion['desde']));  
    $hasta = date('Y', strtotime($inscripcion['hasta']));  

    // Agregar título  
    $pdf->Ln(10); // Añadir un espacio vertical  
    $pdf->SetFont('dejavusans', 'B', 14); // Cambiar a negrita para el título  
    $pdf->Cell(0, 10, 'Constancia de Inscripción', 0, 1, 'C'); // Título centrado  

    // Volver a la fuente normal  
    $pdf->SetFont('dejavusans', '', 12);  

    // Determinar qué cédula mostrar en el formato correcto  
    $cedula_info = '';  
    if (!empty($inscripcion['tipo_cedula']) && !empty($inscripcion['cedula'])) {  
        $cedula_info = "Titular de la Cédula: <strong>(" . $inscripcion['tipo_cedula'] . ") " . $inscripcion['cedula'] . "</strong>";  
    } elseif (!empty($inscripcion['cedula_escolar'])) {  
        $cedula_info = "Titular de la Cédula Escolar: <strong>" . $inscripcion['cedula_escolar'] . "</strong>";  
    }  

    // Contenido del PDF con interlineado de 1
    $html = '<div style="text-align: center; line-height: 1;">  
<p>Quien suscribe, <strong>LCDA YAGERVI DEL CASTILLO</strong> Titular de la Cédula de Identidad</p>  
<p>Nº 12.798.500, en su condición de Director(a) de la U.E.N AGUSTIN ZAMORA.</p>   
<p>QUINTANA, ubicada en Calle Circunvalación, Urb. San Martín I, Parroquia San Juan,</p>   
<p>Caracas. Adscrito a la Zona Educativa del Estado Distrito Capital. Certifica</p>   
<p>por medio de la presente que el (la) estudiante: <strong>' . $inscripcion['nombres'] . ' ' . $inscripcion['apellidos'] . '</strong></p>  
<p>' . $cedula_info . ' está inscrito(a) en este plantel educativo.</p>  
<p>para cursar el <strong>' . $inscripcion['grado'] . '</strong> de nivel <strong>' . $inscripcion['nivel_id'] . '</strong>. Periodo Académico: <strong>' . $desde . ' - ' . $hasta . '</strong></p>    
<p>Constancia que se expide a ' . $dia_actual . ' días del mes de ' . $mes_actual . ' de 2025.</p>  
</div>';

    // Imprimir el contenido  
    $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);  

    // Agregar espacio antes de la firma y la línea  
    $pdf->Ln(20); // Aumentar el espacio antes de la firma  

    // Dibujar una línea horizontal centrada de 60 unidades  
    $pdf->Line(($pdf->getPageWidth() - 60) / 2, $pdf->GetY(), ($pdf->getPageWidth() + 60) / 2, $pdf->GetY());  

    // Información para la firma centrada  
    $pdf->Ln(5); // Espacio entre la línea y el texto  
    $pdf->SetFont('dejavusans', 'B', 12); // Cambiar a negrita para el texto de la firma  
    $pdf->Cell(0, 10, 'Director(a):', 0, 1, 'C');  
    $pdf->SetFont('dejavusans', '', 12); // Volver a la fuente normal  
    $pdf->Cell(0, 10, 'U.E.N Agustín Zamora Quintana', 0, 1, 'C');  

    // Agregar la fecha actual  
    $fecha_actual = date('d/m/Y'); // Formato de fecha  
    $pdf->Ln(10); // Espacio antes de la fecha  
    $pdf->Cell(0, 10, 'Fecha: ' . $fecha_actual, 0, 1, 'C'); // Fecha centrada  

    // Generar el PDF  
    $pdf->Output('constancia_inscripcion.pdf', 'D'); // D para forzar la descarga  
} else {  
    echo "No se encontraron datos de inscripción para el estudiante.";  
}  

ob_end_flush(); // Finaliza el almacenamiento en búfer y envía la salida  
?>