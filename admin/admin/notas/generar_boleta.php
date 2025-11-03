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

    // === PDF CONFIG ===
    $pdf = new TCPDF('L', PDF_UNIT, 'A4', true, 'UTF-8', false);
    $pdf->SetCreator('Sistema Académico');
    $pdf->SetAuthor('U.E.Roberto Martinez Centeno');
    $pdf->SetTitle('Boletín de Rendimiento Escolar');
    $pdf->SetMargins(15, 18, 15);
    $pdf->AddPage();

    // === ENCABEZADO ===
    if (file_exists('logos/logo.png')) $pdf->Image('logos/logo.png', 15, 12, 20); 
    if (file_exists('logos/ministerio.png')) $pdf->Image('logos/ministerio.png', 245, 14, 35); 

    $anio_escolar = (date('n') >= 9) ? date('Y') . '-' . (date('Y') + 1) : (date('Y') - 1) . '-' . date('Y');

    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->SetY(12); 
    $pdf->Cell(0, 5, 'REPÚBLICA BOLIVARIANA DE VENEZUELA', 0, 1, 'C');
    $pdf->Cell(0, 5, 'MINISTERIO DEL PODER POPULAR PARA LA EDUCACIÓN', 0, 1, 'C');
    $pdf->Cell(0, 5, 'U.E.N. ROBERTO MARTINEZ CENTENO', 0, 1, 'C');
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 6, 'BOLETÍN DE RENDIMIENTO DEL AÑO ESCOLAR ' . $anio_escolar, 0, 1, 'C');
    
    // AJUSTE CLAVE: Se sube la línea de 30 a 27 para que no tape el título
    $pdf->Line(15, 27, 282, 27); 

    // === DATOS DEL ESTUDIANTE ===
    $pdf->SetY(35);
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetFillColor(220, 230, 240);
    $pdf->Cell(0, 8, 'DATOS DEL ESTUDIANTE', 0, 1, 'C', true);

    $html = '
    <style>
      .student-table {width:100%;border-collapse:collapse;font-size:10px;}
      .student-table th {background-color:#e6f2ff;padding:5px;text-align:left;border:1px solid #cce0ff;}
      .student-table td {padding:5px;border:1px solid #e6f2ff;}
    </style>
    <table class="student-table">
      <tr><th width="30%">Nombre Completo</th><td width="70%">' . htmlspecialchars($estudiante['nombres'] . ' ' . $estudiante['apellidos']) . '</td></tr>
      <tr><th>Cédula de Identidad</th><td>' . htmlspecialchars($estudiante['cedula']) . '</td></tr>
      <tr><th>Grado/Sección</th><td>' . htmlspecialchars($estudiante['nombre_grado'] . ' - ' . $estudiante['nombre_seccion']) . '</td></tr>
    </table>';
    $pdf->writeHTML($html, true, false, true, false, '');

    // === CALIFICACIONES ===
    $pdf->SetFont('helvetica', 'B', 13);
    $pdf->SetFillColor(220, 230, 240);
    $pdf->Cell(0, 8, 'CALIFICACIONES POR MOMENTO', 0, 1, 'C', true);

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

    // Ajustamos anchos para los encabezados y celdas de datos
    $ancho_col_materias = 22; 
    $ancho_total_lapsos_y_def = 100 - $ancho_col_materias; 

    $ancho_columna_definitiva = $mostrar_definitiva ? ($ancho_total_lapsos_y_def * 0.15) : 0; 
    $ancho_restante_lapsos = $mostrar_definitiva ? ($ancho_total_lapsos_y_def - $ancho_columna_definitiva) : $ancho_total_lapsos_y_def;
    
    $ancho_lapso_single_col = $num_columnas_datos > 0 ? ($ancho_restante_lapsos / $num_columnas_datos) : 0; 
    
    // Color de fondo azul claro para las filas de resumen
    $color_azul_claro = '#e6f2ff'; 

    $html = '<table style="width:100%;border-collapse:collapse;font-size:11px;margin-bottom:10px;">
              <tr>
                <th style="background-color:#145388;color:white;padding:3px;text-align:center;font-size:8px;width:' . $ancho_col_materias . '%;">MATERIAS</th>';
    foreach ($lapsos_visibles as $lapso) {
        $html .= '<th style="background-color:#145388;color:white;padding:3px;text-align:center;font-size:8px;width:' . $ancho_lapso_single_col . '%;">' . strtoupper(str_replace(' ', '<br>', $lapso['nombre_momento'])) . '</th>
                  <th style="background-color:#145388;color:white;padding:3px;text-align:center;font-size:8px;width:' . $ancho_lapso_single_col . '%;">INASISTENCIAS</th>';
    }
    if ($mostrar_definitiva) {
        $html .= '<th style="background-color:#145388;color:white;padding:3px;text-align:center;font-size:8px;width:' . $ancho_columna_definitiva . '%;">NOTAS DEFINITIVAS</th>';
    }
    $html .= '</tr>';

    foreach ($notas_por_materia as $materia => $notas_lapsos) {
        $html .= '<tr><td style="padding:5px;border:1px solid #e6f2ff;text-align:left;width:' . $ancho_col_materias . '%;">' . htmlspecialchars($materia) . '</td>';
        $suma_materia = 0; $cuenta_materia = 0;
        foreach ($lapsos_visibles as $lapso) {
            $id = $lapso['id_lapso'];
            $nota = isset($notas_lapsos[$id]) ? number_format($notas_lapsos[$id], 2) : '-';
            $suma_materia += ($nota !== '-') ? $notas_lapsos[$id] : 0;
            $cuenta_materia += ($nota !== '-') ? 1 : 0;
            $html .= '<td style="padding:5px;border:1px solid #e6f2ff;text-align:center;width:' . $ancho_lapso_single_col . '%;">' . $nota . '</td>
                      <td style="padding:5px;border:1px solid #e6f2ff;text-align:center;width:' . $ancho_lapso_single_col . '%;"></td>';
        }
        if ($mostrar_definitiva) {
            $nota_def = $cuenta_materia ? round($suma_materia / $cuenta_materia) : '-';
            $total_general += ($nota_def !== '-') ? $nota_def : 0;
            $total_materias += ($nota_def !== '-') ? 1 : 0;
            $html .= '<td style="padding:5px;border:1px solid #cce0ff;text-align:center;font-weight:bold;background-color:#e8f5e9;width:' . $ancho_columna_definitiva . '%;">' . $nota_def . '</td>';
        }
        $html .= '</tr>';
    }

    // === FILA DE SEPARACIÓN ===
    $html .= '<tr>';
    for ($i = 0; $i < $total_columnas; $i++) {
        $current_width = ($i == 0) ? $ancho_col_materias : (($i == $total_columnas - 1 && $mostrar_definitiva) ? $ancho_columna_definitiva : $ancho_lapso_single_col);
        $html .= '<td style="padding:1px;border-top:1px solid #cce0ff;border-bottom:1px solid #e6f2ff;background-color:#ffffff;width:' . $current_width . '%;"></td>';
    }
    $html .= '</tr>';

    // === PROMEDIO POR MOMENTO (Pintado de azul claro) ===
    $html .= '<tr><td style="font-size:12px;padding:5px;border:1px solid #cce0ff;text-align:left;font-weight:bold;width:' . $ancho_col_materias . '%;background-color:' . $color_azul_claro . ';">Promedio de Calificaciones</td>';
    foreach ($lapsos_visibles as $lapso) {
        $id = $lapso['id_lapso'];
        $prom = isset($promedios_lapsos[$id]) && $promedios_lapsos[$id]['count'] > 0 ? number_format($promedios_lapsos[$id]['suma'] / $promedios_lapsos[$id]['count'], 2) : '-';
        $html .= '<td style="font-size:10px;padding:5px;border:1px solid #cce0ff;text-align:center;font-weight:bold;width:' . $ancho_lapso_single_col . '%;background-color:' . $color_azul_claro . ';">' . $prom . '</td>
                  <td style="font-size:10px;padding:5px;border:1px solid #cce0ff;text-align:center;width:' . $ancho_lapso_single_col . '%;background-color:' . $color_azul_claro . ';"></td>';
    }
    if ($mostrar_definitiva) $html .= '<td style="font-size:10px;padding:5px;border:1px solid #cce0ff;width:' . $ancho_columna_definitiva . '%;background-color:' . $color_azul_claro . ';"></td>';
    $html .= '</tr>';

    // === MATERIA PENDIENTE (Pintada de azul claro y con líneas de cierre) ===
    $html .= '<tr><td style="font-size:12px;padding:5px;border:1px solid #cce0ff;text-align:left;font-weight:bold;width:' . $ancho_col_materias . '%;background-color:' . $color_azul_claro . ';">Materia Pendiente</td>';
    
    // Celdas vacías para cerrar la línea
    foreach ($lapsos_visibles as $lapso) {
        $html .= '<td style="font-size:10px;padding:5px;border:1px solid #cce0ff;width:' . $ancho_lapso_single_col . '%;background-color:' . $color_azul_claro . ';"></td>
                  <td style="font-size:10px;padding:5px;border:1px solid #cce0ff;width:' . $ancho_lapso_single_col . '%;background-color:' . $color_azul_claro . ';"></td>';
    }
    if ($mostrar_definitiva) {
        $html .= '<td style="font-size:10px;padding:5px;border:1px solid #cce0ff;width:' . $ancho_columna_definitiva . '%;background-color:' . $color_azul_claro . ';"></td>';
    }
    $html .= '</tr>';

    $html .= '</table>';
    $pdf->writeHTML($html, true, false, true, false, '');

    // === PROMEDIO FINAL (Vuelto a Cell() de TCPDF) ===
    if ($mostrar_definitiva && $total_materias > 0) {
        $promedio_final = $total_general / $total_materias;
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->SetFillColor(200, 220, 240);
        $pdf->Cell(0, 8, 'PROMEDIO FINAL DEL AÑO: ' . number_format($promedio_final, 2), 0, 1, 'C', true);
    }

    // === METADATOS DE GENERACIÓN (NUEVA LÓGICA) ===
    $fecha_emision = date('d/m/Y h:i A'); // Formato día/mes/año hora:min AM/PM
    $pdf->SetY($pdf->GetY() + 5); 
    $pdf->SetFont('helvetica', '', 9);
    
    $html_metadata = '
        <div style="width:100%; text-align:right; font-size:8px; margin-bottom: 5px; color:#555;">
            Documento generado por el Sistema Académico.<br>
            Fecha de emisión: ' . $fecha_emision . '
        </div>';
    $pdf->writeHTML($html_metadata, true, false, true, false, '');


    // === PIE CON QR Y ARTÍCULO 109 ===
    $qrContent = 'ID:' . $id_estudiante . '|' . $estudiante['cedula'] . '|' . 
                 $estudiante['nombres'] . ' ' . $estudiante['apellidos'] . '|' . 
                 $anio_escolar;
    $qrDir = 'temp_qr/';
    if (!file_exists($qrDir)) mkdir($qrDir, 0755, true);
    $qrFile = $qrDir . 'qr_' . $id_estudiante . '_' . time() . '.png';
    QRcode::png($qrContent, $qrFile, QR_ECLEVEL_H, 6, 2);

    $html = '
    <div style="margin-top:20px;font-size:9px;text-align:center;">
        <img src="' . $qrFile . '" width="70" height="70" /><br>
        <i>Código de verificación</i><br><br>
        <table style="width:100%;border-collapse:collapse;margin-top:10px;">
            <tr>
                <td style="width:40%;text-align:center;border-top:1px solid #145388;padding-top:10px;">Prof. Tutor/a</td>
                <td style="width:20%;text-align:center;padding-top:10px;"></td> 
                <td style="width:40%;text-align:center;border-top:1px solid #145388;padding-top:10px;">Director/a</td>
            </tr>
        </table><br>
        <small><i>Artículo 109 del Reglamento General de la Ley Orgánica de Educación: 
        El boletín de rendimiento escolar es un documento oficial que refleja el progreso del estudiante durante el año académico.</i></small>
    </div>';
    $pdf->writeHTML($html, true, false, true, false, '');

    unlink($qrFile);

    $filename = 'Boletin_' . str_replace(' ', '_', $estudiante['nombres']) . '_' . str_replace(' ', '_', $estudiante['apellidos']) . '.pdf';
    $pdf->Output($filename, 'D');

} catch (Exception $e) {
    echo '<div style="font-family:Arial,sans-serif;max-width:600px;margin:50px auto;padding:20px;border:1px solid #e74c3c;border-radius:5px;background-color:#fdecea;color:#c0392b;">
            <h3>¡Ocurrió un error!</h3>
            <p>' . htmlspecialchars($e->getMessage()) . '</p>
          </div>';
}
?>