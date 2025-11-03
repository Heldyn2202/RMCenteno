<?php
require_once('../../app/config.php');
require_once('library/tcpdf.php');

// Obtener el ID del horario
$id_horario = isset($_GET['id_horario']) ? $_GET['id_horario'] : null;

if (!$id_horario) {
    die("ID de horario no proporcionado");
}

// Obtener los datos del horario
$sql_horario = "SELECT h.*, g.grado, g.trayecto, g.trimestre, s.nombre_seccion, s.turno, 
                ges.nombre as gestion, ges.desde, ges.hasta 
                FROM horarios h
                JOIN grados g ON h.id_grado = g.id_grado
                JOIN secciones s ON h.id_seccion = s.id_seccion
                JOIN gestiones ges ON h.id_gestion = ges.id_gestion
                WHERE h.id_horario = :id_horario";
$query_horario = $pdo->prepare($sql_horario);
$query_horario->bindParam(':id_horario', $id_horario);
$query_horario->execute();
$horario = $query_horario->fetch(PDO::FETCH_ASSOC);

// Obtener los detalles del horario
$sql_detalles = "SELECT hd.*, m.nombre_materia, m.codigo, 
                 CONCAT(p.nombre, ' ', p.apellido) as profesor
                 FROM horario_detalle hd
                 JOIN materias m ON hd.id_materia = m.id_materia
                 JOIN profesores p ON hd.id_profesor = p.id_profesor
                 WHERE hd.id_horario = :id_horario
                 ORDER BY FIELD(hd.dia_semana, 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'),
                 hd.hora_inicio";
$query_detalles = $pdo->prepare($sql_detalles);
$query_detalles->bindParam(':id_horario', $id_horario);
$query_detalles->execute();
$detalles = $query_detalles->fetchAll(PDO::FETCH_ASSOC);

// Organizar los detalles por día y hora
$horario_organizado = [];
foreach ($detalles as $detalle) {
    $horario_organizado[$detalle['dia_semana']][$detalle['hora_inicio']] = $detalle;
}

// Crear nuevo documento PDF
$pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);

// Configurar documento
$pdf->SetCreator('Sistema de Horarios');
$pdf->SetAuthor('Universidad Politécnica Territorial de Caracas "Mariscal Sucre"');
$pdf->SetTitle('Horario Académico');
$pdf->SetSubject('Horario de Clases');

// Eliminar márgenes
$pdf->SetMargins(10, 10, 10);
$pdf->SetHeaderMargin(5);
$pdf->SetFooterMargin(5);

// Añadir una página
$pdf->AddPage();

// Logo de la universidad
$pdf->Image('../../public/img/logo_universidad.png', 10, 10, 25, 25, 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);

// Título
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, 'HORARIO ACADÉMICO', 0, 1, 'C');
$pdf->Ln(5);

// Información del horario
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 6, 'UNIVERSIDAD POLITÉCNICA TERRITORIAL DE CARACAS "MARISCAL SUCRE"', 0, 1, 'C');
$pdf->Cell(0, 6, 'COORDINACIÓN DE INFORMÁTICA - DEPARTAMENTO: INFORMÁTICA', 0, 1, 'C');
$pdf->Ln(5);

// Datos del período
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(40, 6, 'SEDE:', 0, 0);
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(50, 6, 'ANTIMANO', 0, 0);
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(40, 6, 'PERÍODO:', 0, 0);
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 6, $horario['gestion'], 0, 1);

$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(40, 6, 'TRAYECTO:', 0, 0);
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(50, 6, $horario['trayecto'], 0, 0);
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(40, 6, 'TRIMESTRE:', 0, 0);
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 6, $horario['trimestre'], 0, 1);

$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(40, 6, 'TURNO:', 0, 0);
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(50, 6, $horario['turno'], 0, 0);
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(40, 6, 'AULA:', 0, 0);
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 6, 'Aula 45', 0, 1);

$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(40, 6, 'INICIO:', 0, 0);
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(50, 6, date('d/m/Y', strtotime($horario['desde'])), 0, 0);
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(40, 6, 'FIN:', 0, 0);
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 6, date('d/m/Y', strtotime($horario['hasta'])), 0, 1);
$pdf->Ln(10);

// Definir los días de la semana
$dias_semana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];

// Definir los bloques horarios (basados en el ejemplo)
$bloques_horarios = [
    ['07:50:00', '11:55:00'],
    ['05:00:00', '05:35:00'],
    ['05:35:00', '06:10:00'],
    ['06:10:00', '06:45:00'],
    ['06:45:00', '07:20:00'],
    ['07:20:00', '07:55:00'],
    ['07:55:00', '08:30:00']
];

// Crear la tabla del horario
$pdf->SetFont('helvetica', '', 8);

// Cabecera de la tabla
$pdf->SetFillColor(200, 220, 255);
$pdf->Cell(30, 10, 'HORARIO', 1, 0, 'C', 1);
foreach ($dias_semana as $dia) {
    $pdf->Cell(40, 10, strtoupper($dia), 1, 0, 'C', 1);
}
$pdf->Ln();

// Contenido de la tabla
foreach ($bloques_horarios as $bloque) {
    $hora_inicio = $bloque[0];
    $hora_fin = $bloque[1];
    
    // Mostrar el rango de horas
    $pdf->Cell(30, 10, substr($hora_inicio, 0, 5).' - '.substr($hora_fin, 0, 5), 1, 0, 'C');
    
    // Mostrar las materias para cada día
    foreach ($dias_semana as $dia) {
        if (isset($horario_organizado[$dia][$hora_inicio])) {
            $clase = $horario_organizado[$dia][$hora_inicio];
            $texto = $clase['nombre_materia']."\n(".$clase['codigo'].")\n".$clase['profesor'];
            $pdf->MultiCell(40, 10, $texto, 1, 'C', false);
        } else {
            $pdf->Cell(40, 10, '', 1, 0, 'C');
        }
    }
    
    $pdf->Ln();
}

// Pie de página
$pdf->Ln(10);
$pdf->SetFont('helvetica', 'I', 8);
$pdf->Cell(0, 10, 'Generado el '.date('d/m/Y H:i:s'), 0, 0, 'R');

// Salida del PDF
$pdf->Output('horario_'.$horario['grado'].'_'.$horario['nombre_seccion'].'.pdf', 'D');
?>