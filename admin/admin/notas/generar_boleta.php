<?php
include('../../app/config.php');
require_once('library/tcpdf.php');

// === LIBRERÍA QR ===
$qrLibPath = 'library/phpqrcode/qrlib.php';
if (!file_exists($qrLibPath)) {
    if (!is_dir('library/phpqrcode')) mkdir('library/phpqrcode', 0755, true);
    $qrLibContent = file_get_contents('https://raw.githubusercontent.com/t0k4rt/phpqrcode/master/qrlib.php');
    file_put_contents($qrLibPath, $qrLibContent);
}
require_once($qrLibPath);

// Crear clase personalizada para el PDF con footer profesional
class BoletinPDF extends TCPDF {
    private $color_primario;
    
    public function __construct($color_primario, $orientation='L', $unit='mm', $format='A4') {
        parent::__construct($orientation, $unit, $format, true, 'UTF-8', false);
        $this->color_primario = $color_primario;
    }
    
    // Page footer profesional
    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', '', 8);
        $this->SetTextColor(100); // Color gris profesional
        
        // Línea separadora superior del pie
        $this->SetLineWidth(0.2);
        $this->SetDrawColor(200, 200, 200);
        $this->Line(15, $this->GetY(), $this->getPageWidth()-15, $this->GetY());
        
        $this->Ln(2);
        
        // Información institucional en el pie
        $this->SetFont('helvetica', 'B', 8);
        $this->Cell(0, 3, 'UNIDAD EDUCATIVA "ROBERTO MARTÍNEZ CENTENO"', 0, 1, 'C');
        
        // Información de documento y paginación
        $this->SetFont('helvetica', 'B', 8);
        $this->Cell(0, 3, 'Documento generado el ' . date('d/m/Y') . ' a las ' . date('H:i:s'), 0, 1, 'C');
    }
}

try {
    if (!isset($_GET['id_estudiante']) || !is_numeric($_GET['id_estudiante'])) {
        throw new Exception("ID de estudiante no válido");
    }
    $id_estudiante = $_GET['id_estudiante'];

    $lapsos_seleccionados = isset($_GET['lapsos']) && !empty($_GET['lapsos']) ? explode(',', $_GET['lapsos']) : [];
    $incluir_final = isset($_GET['final']) && $_GET['final'] == '1';

    if (empty($lapsos_seleccionados) && !$incluir_final) {
        throw new Exception("Debe seleccionar al menos un momento o el promedio final");
    }

    // === DATOS DEL ESTUDIANTE ===
    $sql_estudiante = "SELECT e.nombres, e.apellidos, e.cedula, g.grado AS nombre_grado, i.nombre_seccion
                         FROM estudiantes e
                         JOIN inscripciones i ON e.id_estudiante = i.id_estudiante
                         JOIN grados g ON i.grado = g.id_grado
                         WHERE e.id_estudiante = ? LIMIT 1";
    $stmt = $pdo->prepare($sql_estudiante);
    $stmt->execute([$id_estudiante]);
    $estudiante = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$estudiante) throw new Exception("Estudiante no encontrado");

    // === LAPSOS ===
    $sql_lapsos = "SELECT * FROM lapsos ORDER BY fecha_inicio";
    $stmt = $pdo->prepare($sql_lapsos);
    $stmt->execute();
    $lapsos_info = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // === NOTAS ===
    $sql_notas = "SELECT m.nombre_materia, ne.calificacion, l.nombre_lapso, l.id_lapso
                  FROM notas_estudiantes ne
                  JOIN materias m ON ne.id_materia = m.id_materia
                  JOIN lapsos l ON ne.id_lapso = l.id_lapso
                  WHERE ne.id_estudiante = ?";
    $params = [$id_estudiante];
    if (!empty($lapsos_seleccionados)) {
        $placeholders = implode(',', array_fill(0, count($lapsos_seleccionados), '?'));
        $sql_notas .= " AND ne.id_lapso IN ($placeholders)";
        $params = array_merge($params, $lapsos_seleccionados);
    }
    $stmt = $pdo->prepare($sql_notas);
    $stmt->execute($params);
    $notas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // === ORGANIZAR NOTAS ===
    $notas_por_materia = [];
    $promedios_lapsos = [];
    $total_general = 0;
    $total_materias = 0;

    foreach ($notas as $nota) {
        $materia = $nota['nombre_materia'];
        $id_lapso = $nota['id_lapso'];
        $calificacion = $nota['calificacion'];

        if (!isset($notas_por_materia[$materia])) $notas_por_materia[$materia] = [];
        $notas_por_materia[$materia][$id_lapso] = $calificacion;

        if (!isset($promedios_lapsos[$id_lapso])) $promedios_lapsos[$id_lapso] = ['suma' => 0, 'count' => 0];
        $promedios_lapsos[$id_lapso]['suma'] += $calificacion;
        $promedios_lapsos[$id_lapso]['count']++;
    }

    // Paleta de colores institucionales
    $color_primario = array(0, 51, 102);     // Azul oscuro corporativo
    $color_secundario = array(0, 51, 102);    // Azul oscuro corporativo
    $color_terciario = array(79, 129, 189);  // Azul medio

    // === PDF CONFIG ===
    $pdf = new BoletinPDF($color_primario, 'L');
    
    // Información del documento
    $pdf->SetCreator('U.E.N Roberto Martínez Centeno');
    $pdf->SetAuthor('Ministerio de Educación');
    $pdf->SetTitle('Boletín de Rendimiento Escolar');
    $pdf->SetSubject('Boletín de Rendimiento Estudiantil');
    $pdf->SetKeywords('TCPDF, PDF, boletín, calificaciones, educación');

    // Configuración de márgenes y página (márgenes más pequeños para una sola página)
    $pdf->SetMargins(10, 25, 10);
    $pdf->SetHeaderMargin(5);
    $pdf->SetFooterMargin(12);
    $pdf->SetAutoPageBreak(TRUE, 15);
    
    // Configurar fuente por defecto
    $pdf->SetFont('helvetica', '', 9);
    
    // Configurar impresión
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(true);

    // Función para agregar encabezado institucional compacto
    function agregarEncabezado($pdf, $color_primario, $color_secundario) {
        // Logos institucionales
        $logo_left = '../../logo/logo.png';
        $logo_right = '../../logo/MPPEducacion.png';
        
        // Verificar existencia de logos
        $logo_left_exists = file_exists($logo_left);
        $logo_right_exists = file_exists($logo_right);
        
        // Configuración más compacta para logos
        $logo_y_position = 8;
        
        // CONFIGURACIÓN LOGO IZQUIERDO
        $logo_left_width = 18;
        $logo_left_height = 12;
        $logo_left_x = 15;
        
        // CONFIGURACIÓN LOGO DERECHO
        $logo_right_width = 30;
        $logo_right_height = 13;
        $page_width = $pdf->getPageWidth();
        $logo_right_x = $page_width - 15 - $logo_right_width;
        
        // Agregar logo izquierdo
        if($logo_left_exists) {
            $pdf->Image(
                $logo_left, 
                $logo_left_x, 
                $logo_y_position, 
                $logo_left_width,
                $logo_left_height,
                '', '', 'T', false, 300, '', false, false, 0, false, false, false
            );
        }
        
        // Agregar logo derecho
        if($logo_right_exists) {
            $pdf->Image(
                $logo_right, 
                $logo_right_x, 
                $logo_y_position, 
                $logo_right_width,
                $logo_right_height,
                '', '', 'T', false, 300, '', false, false, 0, false, false, false
            );
        }
        
        // Información institucional central más compacta
        $pdf->SetY($logo_y_position);
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->SetTextColor($color_primario[0], $color_primario[1], $color_primario[2]);
        $pdf->Cell(0, 3, 'REPÚBLICA BOLIVARIANA DE VENEZUELA', 0, 1, 'C');
        
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->Cell(0, 4, 'MINISTERIO DEL PODER POPULAR PARA LA EDUCACIÓN', 0, 1, 'C');
        
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(0, 4, 'U.E "ROBERTO MARTINEZ CENTENO"', 0, 1, 'C');
        
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell(0, 3, 'CARICUAO - CARACAS - DISTRITO CAPITAL', 0, 1, 'C');
        
        // Línea separadora
        $pdf->SetLineWidth(0.2);
        $pdf->SetDrawColor($color_primario[0], $color_primario[1], $color_primario[2]);
        $pdf->Line(15, $pdf->GetY() + 1, $pdf->getPageWidth()-15, $pdf->GetY() + 1);
        
        $pdf->SetY($pdf->GetY() + 5);
    }

    $pdf->AddPage();
    agregarEncabezado($pdf, $color_primario, $color_secundario);

    // Título del documento más compacto
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->SetTextColor($color_secundario[0], $color_secundario[1], $color_secundario[2]);
    
    $anio_escolar = (date('n') >= 9) ? date('Y') . '-' . (date('Y') + 1) : (date('Y') - 1) . '-' . date('Y');
    $pdf->Cell(0, 6, 'BOLETÍN DE RENDIMIENTO ' . $anio_escolar, 0, 1, 'C');
    
    // Línea decorativa bajo el título
    $pdf->SetLineWidth(0.3);
    $pdf->SetDrawColor($color_secundario[0], $color_secundario[1], $color_secundario[2]);
    $pdf->Line(($pdf->getPageWidth()-80)/2, $pdf->GetY(), ($pdf->getPageWidth()+80)/2, $pdf->GetY());
    $pdf->Ln(5);

    // === GENERAR QR TEMPRANO ===
    $qrContent = 'ID:' . $id_estudiante . '|' . $estudiante['cedula'] . '|' . 
                 $estudiante['nombres'] . ' ' . $estudiante['apellidos'] . '|' . 
                 $anio_escolar;
    $qrDir = 'temp_qr/';
    if (!file_exists($qrDir)) mkdir($qrDir, 0755, true);
    $qrFile = $qrDir . 'qr_' . $id_estudiante . '_' . time() . '.png';
    QRcode::png($qrContent, $qrFile, QR_ECLEVEL_H, 4, 2);

    // === SECCIÓN COMPACTA CON DATOS DEL ESTUDIANTE Y QR ===
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->SetFillColor(220, 230, 240);
    $pdf->Cell(0, 6, 'DATOS DEL ESTUDIANTE', 0, 1, 'C', true);

    // Crear tabla compacta con datos del estudiante y QR
    $html_estudiante = '
    <style>
      .student-table {width:100%;border-collapse:collapse;font-size:9px;margin-bottom:3px;}
      .student-table th {background-color:#e6f2ff;padding:3px;text-align:left;border:1px solid #cce0ff;font-size:9px;}
      .student-table td {padding:3px;border:1px solid #e6f2ff;font-size:9px;}
      .qr-section {text-align:center;vertical-align:middle;}
    </style>
    <table class="student-table">
      <tr>
        <td width="75%">
          <table style="width:100%;border-collapse:collapse;font-size:9px;">
            <tr><th width="30%">Nombre Completo</th><td width="70%">' . htmlspecialchars($estudiante['nombres'] . ' ' . $estudiante['apellidos']) . '</td></tr>
            <tr><th>Cédula de Identidad</th><td>' . htmlspecialchars($estudiante['cedula']) . '</td></tr>
            <tr><th>Grado/Sección</th><td>' . htmlspecialchars($estudiante['nombre_grado'] . ' - ' . $estudiante['nombre_seccion']) . '</td></tr>
          </table>
        </td>
        <td width="25%" class="qr-section">
          <img src="' . $qrFile . '" width="50" height="50" /><br>
          <small><i>Código de verificación</i></small>
        </td>
      </tr>
    </table>';
    $pdf->writeHTML($html_estudiante, true, false, true, false, '');
    $pdf->Ln(2);

    // === CALIFICACIONES - TABLA MÁS COMPACTA ===
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->SetFillColor(220, 230, 240);
    $pdf->Cell(0, 6, 'CALIFICACIONES POR MOMENTO', 0, 1, 'C', true);

    $lapsos_visibles = [];
    $nombres_momentos = ['Primer Momento', 'Segundo Momento', 'Tercer Momento'];
    $index = 0;
    foreach ($lapsos_info as $lapso) {
        if (in_array($lapso['id_lapso'], $lapsos_seleccionados)) {
            $lapso['nombre_momento'] = $nombres_momentos[$index++] ?? $lapso['nombre_lapso'];
            $lapsos_visibles[] = $lapso;
        }
    }

    $mostrar_definitiva = count($lapsos_visibles) === 3;
    $num_columnas_datos = count($lapsos_visibles) * 2;
    $total_columnas = 1 + $num_columnas_datos + ($mostrar_definitiva ? 1 : 0);

    // Ajustar anchos para máxima compacidad
    $ancho_col_materias = 25; 
    $ancho_total_lapsos_y_def = 100 - $ancho_col_materias; 

    $ancho_columna_definitiva = $mostrar_definitiva ? ($ancho_total_lapsos_y_def * 0.15) : 0; 
    $ancho_restante_lapsos = $mostrar_definitiva ? ($ancho_total_lapsos_y_def - $ancho_columna_definitiva) : $ancho_total_lapsos_y_def;
    
    $ancho_lapso_single_col = $num_columnas_datos > 0 ? ($ancho_restante_lapsos / $num_columnas_datos) : 0; 

    // Color de fondo azul claro para las filas de resumen
    $color_azul_claro = '#e6f2ff'; 

    $html_calificaciones = '<table style="width:100%;border-collapse:collapse;font-size:8px;margin-bottom:5px;">
              <tr>
                <th style="background-color:#145388;color:white;padding:2px;text-align:center;font-size:7px;width:' . $ancho_col_materias . '%;">MATERIAS</th>';
    foreach ($lapsos_visibles as $lapso) {
        $html_calificaciones .= '<th style="background-color:#145388;color:white;padding:2px;text-align:center;font-size:7px;width:' . $ancho_lapso_single_col . '%;">' . strtoupper(str_replace(' ', '<br>', $lapso['nombre_momento'])) . '</th>
                  <th style="background-color:#145388;color:white;padding:2px;text-align:center;font-size:7px;width:' . $ancho_lapso_single_col . '%;">INASIST.</th>';
    }
    if ($mostrar_definitiva) {
        $html_calificaciones .= '<th style="background-color:#145388;color:white;padding:2px;text-align:center;font-size:7px;width:' . $ancho_columna_definitiva . '%;">DEFINITIVA</th>';
    }
    $html_calificaciones .= '</tr>';

    foreach ($notas_por_materia as $materia => $notas_lapsos) {
        $html_calificaciones .= '<tr><td style="padding:3px;border:1px solid #e6f2ff;text-align:left;width:' . $ancho_col_materias . '%;font-size:8px;">' . htmlspecialchars($materia) . '</td>';
        $suma_materia = 0; $cuenta_materia = 0;
        foreach ($lapsos_visibles as $lapso) {
            $id = $lapso['id_lapso'];
            $nota = isset($notas_lapsos[$id]) ? number_format($notas_lapsos[$id], 2) : '-';
            $suma_materia += ($nota !== '-') ? $notas_lapsos[$id] : 0;
            $cuenta_materia += ($nota !== '-') ? 1 : 0;
            $html_calificaciones .= '<td style="padding:3px;border:1px solid #e6f2ff;text-align:center;width:' . $ancho_lapso_single_col . '%;font-size:8px;">' . $nota . '</td>
                      <td style="padding:3px;border:1px solid #e6f2ff;text-align:center;width:' . $ancho_lapso_single_col . '%;font-size:8px;"></td>';
        }
        if ($mostrar_definitiva) {
            $nota_def = $cuenta_materia ? round($suma_materia / $cuenta_materia) : '-';
            $total_general += ($nota_def !== '-') ? $nota_def : 0;
            $total_materias += ($nota_def !== '-') ? 1 : 0;
            $html_calificaciones .= '<td style="padding:3px;border:1px solid #cce0ff;text-align:center;font-weight:bold;background-color:#e8f5e9;width:' . $ancho_columna_definitiva . '%;font-size:8px;">' . $nota_def . '</td>';
        }
        $html_calificaciones .= '</tr>';
    }

    // === PROMEDIO POR MOMENTO ===
    $html_calificaciones .= '<tr><td style="font-size:9px;padding:3px;border:1px solid #cce0ff;text-align:left;font-weight:bold;width:' . $ancho_col_materias . '%;background-color:' . $color_azul_claro . ';">Promedio</td>';
    foreach ($lapsos_visibles as $lapso) {
        $id = $lapso['id_lapso'];
        $prom = isset($promedios_lapsos[$id]) && $promedios_lapsos[$id]['count'] > 0 ? number_format($promedios_lapsos[$id]['suma'] / $promedios_lapsos[$id]['count'], 2) : '-';
        $html_calificaciones .= '<td style="font-size:8px;padding:3px;border:1px solid #cce0ff;text-align:center;font-weight:bold;width:' . $ancho_lapso_single_col . '%;background-color:' . $color_azul_claro . ';">' . $prom . '</td>
                  <td style="font-size:8px;padding:3px;border:1px solid #cce0ff;text-align:center;width:' . $ancho_lapso_single_col . '%;background-color:' . $color_azul_claro . ';"></td>';
    }
    if ($mostrar_definitiva) $html_calificaciones .= '<td style="font-size:8px;padding:3px;border:1px solid #cce0ff;width:' . $ancho_columna_definitiva . '%;background-color:' . $color_azul_claro . ';"></td>';
    $html_calificaciones .= '</tr>';

    // === MATERIA PENDIENTE ===
    $html_calificaciones .= '<tr><td style="font-size:9px;padding:3px;border:1px solid #cce0ff;text-align:left;font-weight:bold;width:' . $ancho_col_materias . '%;background-color:' . $color_azul_claro . ';">Materia Pendiente</td>';
    foreach ($lapsos_visibles as $lapso) {
        $html_calificaciones .= '<td style="font-size:8px;padding:3px;border:1px solid #cce0ff;width:' . $ancho_lapso_single_col . '%;background-color:' . $color_azul_claro . ';"></td>
                  <td style="font-size:8px;padding:3px;border:1px solid #cce0ff;width:' . $ancho_lapso_single_col . '%;background-color:' . $color_azul_claro . ';"></td>';
    }
    if ($mostrar_definitiva) {
        $html_calificaciones .= '<td style="font-size:8px;padding:3px;border:1px solid #cce0ff;width:' . $ancho_columna_definitiva . '%;background-color:' . $color_azul_claro . ';"></td>';
    }
    $html_calificaciones .= '</tr>';

    $html_calificaciones .= '</table>';
    $pdf->writeHTML($html_calificaciones, true, false, true, false, '');

    // === PROMEDIO FINAL COMPACTO ===
    if ($mostrar_definitiva && $total_materias > 0) {
        $promedio_final = $total_general / $total_materias;
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetFillColor(200, 220, 240);
        $pdf->Cell(0, 6, 'PROMEDIO FINAL: ' . number_format($promedio_final, 2), 0, 1, 'C', true);
        $pdf->Ln(2);
    }

    // === FIRMAS COMPACTAS ===
// Agregar 3 cm de espacio antes de las firmas
$pdf->Ln(30);

$html_firmas = '
<div style="font-size:8px;text-align:center;">
    <table style="width:100%;border-collapse:collapse;margin-top:5px;">
        <tr>
            <td style="width:40%;text-align:center;border-top:1px solid #145388;padding-top:5px;font-size:8px;">Prof. Tutor/a</td>
            <td style="width:20%;text-align:center;padding-top:5px;"></td> 
            <td style="width:40%;text-align:center;border-top:1px solid #145388;padding-top:5px;font-size:8px;">Director/a</td>
        </tr>
    </table>
    <small style="font-size:7px;"><i>Artículo 109 del Reglamento General de la Ley Orgánica de Educación: 
    El boletín de rendimiento escolar es un documento oficial que refleja el progreso del estudiante durante el año académico.</i></small>
</div>';
$pdf->writeHTML($html_firmas, true, false, true, false, '');

    // Limpiar archivo QR temporal
    unlink($qrFile);

    $filename = 'Boletin_' . str_replace(' ', '_', $estudiante['nombres']) . '_' . str_replace(' ', '_', $estudiante['apellidos']) . '.pdf';
    $pdf->Output($filename, 'I');

} catch (Exception $e) {
    echo '<div style="font-family:Arial,sans-serif;max-width:600px;margin:50px auto;padding:20px;border:1px solid #e74c3c;border-radius:5px;background-color:#fdecea;color:#c0392b;">
            <h3>¡Ocurrió un error!</h3>
            <p>' . htmlspecialchars($e->getMessage()) . '</p>
          </div>';
}
?>