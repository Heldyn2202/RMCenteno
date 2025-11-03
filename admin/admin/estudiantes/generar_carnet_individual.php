<?php
include ('../../app/config.php');

$id_estudiante = $_GET['id'];

// Consultar datos del estudiante
$query_estudiante = $pdo->prepare("SELECT * FROM estudiantes WHERE id_estudiante = :id_estudiante");
$query_estudiante->bindParam(':id_estudiante', $id_estudiante);
$query_estudiante->execute();
$estudiante = $query_estudiante->fetch(PDO::FETCH_ASSOC);

// Consultar la plantilla predeterminada
$query_plantilla = $pdo->prepare("SELECT * FROM plantillas_carnet WHERE estatus = 'activo' ORDER BY id_plantilla LIMIT 1");
$query_plantilla->execute();
$plantilla = $query_plantilla->fetch(PDO::FETCH_ASSOC);

require_once(APP_PATH . '/libraries/tcpdf/tcpdf.php');

// Crear nuevo documento PDF
$pdf = new TCPDF('L', 'mm', array($plantilla['ancho'], $plantilla['alto']), true, 'UTF-8', false);

// Configurar márgenes
$pdf->SetMargins($plantilla['margen_izquierdo'], $plantilla['margen_superior'], $plantilla['margen_derecho']);
$pdf->SetAutoPageBreak(false);
$pdf->AddPage();

// Cargar la plantilla HTML
$html = file_get_contents(APP_PATH . '/admin/estudiantes/plantillas/' . $plantilla['archivo_plantilla']);

// Reemplazar marcadores de posición
$html = str_replace('{{nombre}}', $estudiante['nombres'] . ' ' . $estudiante['apellidos'], $html);
$html = str_replace('{{cedula}}', $estudiante['cedula'], $html);
$html = str_replace('{{fecha_nacimiento}}', date('d/m/Y', strtotime($estudiante['fecha_nacimiento'])), $html);

// Generar código único
$codigo = $estudiante['id_estudiante'] . '-' . date('Ymd');
$html = str_replace('{{codigo}}', $codigo, $html);

// Fecha de vencimiento (1 año desde hoy)
$fecha_vencimiento = date('d/m/Y', strtotime('+1 year'));
$html = str_replace('{{fecha_vencimiento}}', $fecha_vencimiento, $html);

// Escribir el HTML en el PDF
$pdf->writeHTML($html, true, false, true, false, '');

// Salida del PDF
$pdf->Output('carnet_' . $estudiante['cedula'] . '.pdf', 'I');