<?php  
include('../../app/config.php');  
require_once 'library/tcpdf.php';   

// Obtener el periodo académico activo  
$sql_gestiones = "SELECT * FROM gestiones WHERE estado = 1 ORDER BY desde DESC LIMIT 1";  
$query_gestiones = $pdo->prepare($sql_gestiones);  
$query_gestiones->execute();  
$gestion_activa = $query_gestiones->fetch(PDO::FETCH_ASSOC);  

// Obtener el grado seleccionado  
$grado_seleccionado = isset($_GET['grado']) ? $_GET['grado'] : null;  

// Consulta para obtener la lista de estudiantes inscritos filtrados por grado y periodo académico activo  
$sql_inscripciones = "SELECT e.cedula, e.nombres, e.apellidos, e.genero, i.turno_id, g.grado, i.nombre_seccion  
                      FROM inscripciones i  
                      INNER JOIN estudiantes e ON i.id_estudiante = e.id_estudiante  
                      INNER JOIN grados g ON i.grado = g.id_grado  
                      WHERE i.id_gestion = :id_gestion";  

if ($grado_seleccionado) {  
    $sql_inscripciones .= " AND g.grado = :grado";  
}  

$stmt = $pdo->prepare($sql_inscripciones);  
$stmt->bindParam(':id_gestion', $gestion_activa['id_gestion']);  

if ($grado_seleccionado) {  
    $stmt->bindParam(':grado', $grado_seleccionado);  
}  

$stmt->execute();  
$inscripciones = $stmt->fetchAll(PDO::FETCH_ASSOC);  

// Crear nuevo PDF  
$pdf = new TCPDF();  
$pdf->SetCreator(PDF_CREATOR);  
$pdf->SetAuthor('Tu Nombre');  
$pdf->SetTitle('Reporte de Matrícula Escolar');  

// Configurar el encabezado  
$pdf->SetHeaderData('', 0, 'Reporte de Matrícula Escolar', 'Periodo Académico: ' . date('Y', strtotime($gestion_activa['desde'])) . '-' . date('Y', strtotime($gestion_activa['hasta'])));  
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));  
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));  
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);  
$pdf->SetMargins(10, 25, 10); // Aumentar el margen superior a 25  
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);  
$pdf->AddPage();  

// Agregar el cintillo en la esquina superior derecha  
$pdf->Image('logos/ministerio.jpg', 100, 5, 100, 10, 'JPG', '', '', false, 300, '', false, false, 0, false, false, false); // Ajusta la posición y el tamaño según sea necesario  

// Establecer el contenido del PDF  
$html = '<h4 style="font-weight: bold;">Turno Mañana</h4>';  
$html .= '<h4 style="font-weight: bold;">Grado y Sección</h4>';  
$html .= '<table border="1" cellpadding="4">  
            <tr>  
                <th>Grado</th>  
                <th>Sección</th>  
                <th>Total Masculino</th>  
                <th>Total Femenino</th>  
            </tr>';  

// Agrupar por grado y sección  
$grados_secciones = [];  
foreach ($inscripciones as $inscripcion) {  
    $grado = $inscripcion['grado'];  
    $seccion = $inscripcion['nombre_seccion'];  

    // Inicializar contadores si no existen  
    if (!isset($grados_secciones[$grado][$seccion])) {  
        $grados_secciones[$grado][$seccion] = ['masculino' => 0, 'femenino' => 0];  
    }  

    // Contar género (ajustado a minúsculas)  
    if (strtolower($inscripcion['genero']) == 'masculino') {  
        $grados_secciones[$grado][$seccion]['masculino']++;  
    } elseif (strtolower($inscripcion['genero']) == 'femenino') {  
        $grados_secciones[$grado][$seccion]['femenino']++;  
    }  
}  

// Imprimir grados y secciones  
foreach ($grados_secciones as $grado => $secciones) {  
    foreach ($secciones as $seccion => $totales) {  
        $html .= '<tr>  
                    <td>' . htmlspecialchars($grado) . '</td>  
                    <td>' . htmlspecialchars($seccion) . '</td>  
                    <td>' . htmlspecialchars($totales['masculino']) . '</td>  
                    <td>' . htmlspecialchars($totales['femenino']) . '</td>  
                  </tr>';  
    }  
}  

$html .= '</table>';  

// Agregar sección de matrícula  
$html .= '<h3>Matrícula</h3>';  
$html .= '<table border="1" cellpadding="4">  
            <tr>  
                <th>Género</th>  
                <th>Total</th>  
            </tr>';  

// Inicializar contadores para la sección de matrícula  
$total_masculino = 0;  
$total_femenino = 0;  

// Sumar los totales de masculinos y femeninos  
foreach ($grados_secciones as $secciones) {  
    foreach ($secciones as $totales) {  
        $total_masculino += $totales['masculino'];  
        $total_femenino += $totales['femenino'];  
    }  
}  

$html .= '<tr>  
            <td>Masculino</td>  
            <td>' . $total_masculino . '</td>  
          </tr>  
          <tr>  
            <td>Femenino</td>  
            <td>' . $total_femenino . '</td>  
          </tr>  
          <tr>  
            <td>Total</td>  
            <td>' . ($total_masculino + $total_femenino) . '</td>  
          </tr>  
          </table>';  

// Escribir el contenido al PDF  
$pdf->writeHTML($html, true, false, true, false, '');  

// Agregar el texto centrado en la parte inferior  
$pdf->SetY(-50); // Mover el cursor a 50 mm del fondo para que la línea esté aún más arriba  
$pdf->Cell(0, 10,  'Dirección - Subdirección', 0, 1, 'C', 0, '', 0);  
$pdf->Cell(0, 3, '________________________________________________', 0, 1, 'C', 0, '', 0); // Línea para firmar, haciendo la celda más pequeña  

// Cerrar y generar el PDF  
$pdf->Output('reporte_matricula.pdf', 'D'); // 'D' para descargar el PDF  
?>