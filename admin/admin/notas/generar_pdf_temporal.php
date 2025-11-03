<?php
session_start();
require_once('../../app/config.php');
require_once('library/tcpdf.php');

// 1. Verificar y obtener datos del horario
if (!isset($_SESSION['horario_data'])) {
    die('Error: No hay datos de horario para generar el PDF.');
}

$data = $_SESSION['horario_data'];

// 2. Crear documento PDF
$pdf = new TCPDF('L', PDF_UNIT, 'A4', true, 'UTF-8', false);

// 3. Configurar metadatos del documento
$pdf->SetCreator(APP_NAME);
$pdf->SetAuthor('Sistema Académico ' . APP_NAME);
$pdf->SetTitle('Horario Escolar ' . ($data['grado_nombre'] ?? '') . ' ' . ($data['seccion_nombre'] ?? ''));
$pdf->SetSubject('Horario Escolar Generado');

// 4. Configurar márgenes y propiedades
$pdf->SetMargins(15, 20, 15);
$pdf->SetHeaderMargin(10);
$pdf->SetFooterMargin(10);
$pdf->SetAutoPageBreak(TRUE, 15);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// 5. Agregar página
$pdf->AddPage();

// 6. Crear contenido HTML para el PDF
$html = '
<style>
    .header {
        text-align: center;
        margin-bottom: 10px;
    }
    .header h1 {
        font-size: 16px;
        font-weight: bold;
        margin: 5px 0;
    }
    .header h2 {
        font-size: 14px;
        margin: 5px 0;
        color: #555;
    }
    .info-container {
        margin-bottom: 15px;
        font-size: 12px;
    }
    .info-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 5px;
    }
    .info-label {
        font-weight: bold;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 10px;
    }
    th {
        background-color: #3498db;
        color: white;
        font-weight: bold;
        padding: 5px;
        text-align: center;
    }
    td {
        border: 1px solid #ddd;
        padding: 5px;
        text-align: center;
    }
    .time-cell {
        background-color: #f1f8fe;
        font-weight: bold;
    }
    .subject {
        font-weight: bold;
    }
    .teacher {
        font-style: italic;
        font-size: 9px;
    }
    .free-period {
        background-color: #f5f5f5;
        color: #999;
        font-style: italic;
    }
    .footer {
        margin-top: 10px;
        font-size: 8px;
        text-align: center;
        color: #777;
    }
</style>

<div class="header">
    <h1>INSTITUCIÓN EDUCATIVA "' . strtoupper(htmlspecialchars(APP_NAME)) . '"</h1>
    <h2>HORARIO ESCOLAR ' . htmlspecialchars($data['gestion_nombre'] ?? '') . '</h2>
</div>

<div class="info-container">
    <div class="info-row">
        <div><span class="info-label">Grado:</span> ' . htmlspecialchars($data['grado_nombre'] ?? '') . '</div>
        <div><span class="info-label">Sección:</span> ' . htmlspecialchars($data['seccion_nombre'] ?? '') . '</div>
        <div><span class="info-label">Turno:</span> ' . htmlspecialchars($data['turno'] ?? '') . '</div>
    </div>
    <div class="info-row">
        <div><span class="info-label">Aula:</span> ' . htmlspecialchars($data['aula'] ?? '') . '</div>
        <div><span class="info-label">Vigencia:</span> ' . 
            (isset($data['fecha_inicio']) ? date('d/m/Y', strtotime($data['fecha_inicio'])) : '') . ' - ' . 
            (isset($data['fecha_fin']) ? date('d/m/Y', strtotime($data['fecha_fin'])) : '') . '</div>
        <div><span class="info-label">Periodo:</span> ' . htmlspecialchars($data['gestion_rango'] ?? '') . '</div>
    </div>
</div>';

// 7. Crear tabla de horario
$html .= '<table>
    <thead>
        <tr>
            <th width="15%">Hora</th>
            <th>Lunes</th>
            <th>Martes</th>
            <th>Miércoles</th>
            <th>Jueves</th>
            <th>Viernes</th>
        </tr>
    </thead>
    <tbody>';

// Definir bloques horarios
$bloques_horarios = [
    ['07:50', '08:30'],
    ['08:30', '09:10'],
    ['09:10', '09:50'],
    ['10:10', '10:50'],
    ['10:50', '11:30'],
    ['11:30', '12:10']
];

foreach ($bloques_horarios as $bloque) {
    $html .= '<tr>
        <td class="time-cell">' . $bloque[0] . ' - ' . $bloque[1] . '</td>';
    
    foreach (['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'] as $dia) {
        $clase = isset($data['horario'][$dia][$bloque[0]]) ? '' : 'free-period';
        $materia = isset($data['horario'][$dia][$bloque[0]]['materia_nombre']) ? 
                   htmlspecialchars($data['horario'][$dia][$bloque[0]]['materia_nombre']) : '';
        $profesor = isset($data['horario'][$dia][$bloque[0]]['profesor_nombre']) ? 
                    htmlspecialchars($data['horario'][$dia][$bloque[0]]['profesor_nombre']) : '';
        
        $html .= '<td class="' . $clase . '">';
        if (!empty($materia)) {
            $html .= '<div class="subject">' . $materia . '</div>';
            $html .= '<div class="teacher">' . $profesor . '</div>';
        } else {
            $html .= 'Libre';
        }
        $html .= '</td>';
    }
    
    $html .= '</tr>';
}

$html .= '</tbody></table>';

// 8. Añadir pie de página
$html .= '
<div class="footer">
    Generado el ' . date('d/m/Y H:i:s') . ' | ' . htmlspecialchars(APP_NAME) . ' - Sistema de Gestión Académica
</div>';

// 9. Escribir el contenido HTML en el PDF
$pdf->writeHTML($html, true, false, true, false, '');

// 10. Generar nombre del archivo
$nombre_archivo = 'Horario_' . 
                 ($data['grado_nombre'] ?? '') . '_' . 
                 ($data['seccion_nombre'] ?? '') . '_' . 
                 date('Ymd_His') . '.pdf';

// 11. Salida del PDF (forzar descarga)
$pdf->Output($nombre_archivo, 'D');