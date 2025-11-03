<?php
include ('../../../app/config.php');

// Obtener datos del formulario
$id_plantilla = $_POST['id_plantilla'];
$fecha_vencimiento = $_POST['fecha_vencimiento'];
$estudiantes_seleccionados = $_POST['estudiantes'];

// Consultar la plantilla seleccionada
$query_plantilla = $pdo->prepare("SELECT * FROM plantillas_carnet WHERE id_plantilla = :id_plantilla");
$query_plantilla->bindParam(':id_plantilla', $id_plantilla);
$query_plantilla->execute();
$plantilla = $query_plantilla->fetch(PDO::FETCH_ASSOC);

// Crear PDF con TCPDF
require_once(APP_PATH . '/libraries/tcpdf/tcpdf.php');

// Crear nuevo documento PDF
$pdf = new TCPDF('L', 'mm', array($plantilla['ancho'], $plantilla['alto']), true, 'UTF-8', false);

// Configurar márgenes
$pdf->SetMargins($plantilla['margen_izquierdo'], $plantilla['margen_superior'], $plantilla['margen_derecho']);
$pdf->SetAutoPageBreak(false);

// Para cada estudiante seleccionado
foreach ($estudiantes_seleccionados as $id_estudiante) {
    // Consultar datos del estudiante
    $query_estudiante = $pdo->prepare("SELECT * FROM estudiantes WHERE id_estudiante = :id_estudiante");
    $query_estudiante->bindParam(':id_estudiante', $id_estudiante);
    $query_estudiante->execute();
    $estudiante = $query_estudiante->fetch(PDO::FETCH_ASSOC);
    
    // Generar código de barras o QR
    $codigo = $estudiante['id_estudiante'] . '-' . date('Ymd');
    
    // Registrar el carnet en la base de datos
    $query_insert = $pdo->prepare("INSERT INTO carnets_estudiantiles 
        (id_estudiante, id_plantilla, codigo_barras, qr_code, fecha_vencimiento) 
        VALUES (:id_estudiante, :id_plantilla, :codigo, :qr_code, :fecha_vencimiento)");
    $query_insert->bindParam(':id_estudiante', $id_estudiante);
    $query_insert->bindParam(':id_plantilla', $id_plantilla);
    $query_insert->bindParam(':codigo', $codigo);
    $query_insert->bindParam(':qr_code', $codigo);
    $query_insert->bindParam(':fecha_vencimiento', $fecha_vencimiento);
    $query_insert->execute();
    
    // Agregar nueva página para cada carnet
    $pdf->AddPage();
    
    // Cargar la plantilla HTML (puede ser un archivo o una cadena)
    $html = file_get_contents(APP_PATH . '/admin/estudiantes/plantillas/' . $plantilla['archivo_plantilla']);
    
    // Reemplazar marcadores de posición con datos reales
    $html = str_replace('{{nombre}}', $estudiante['nombres'] . ' ' . $estudiante['apellidos'], $html);
    $html = str_replace('{{cedula}}', $estudiante['cedula'], $html);
    $html = str_replace('{{fecha_nacimiento}}', date('d/m/Y', strtotime($estudiante['fecha_nacimiento'])), $html);
    $html = str_replace('{{codigo}}', $codigo, $html);
    $html = str_replace('{{fecha_vencimiento}}', date('d/m/Y', strtotime($fecha_vencimiento)), $html);
    
    // Escribir el HTML en el PDF
    $pdf->writeHTML($html, true, false, true, false, '');
}

// Salida del PDF
$pdf->Output('carnets_estudiantiles.pdf', 'I');