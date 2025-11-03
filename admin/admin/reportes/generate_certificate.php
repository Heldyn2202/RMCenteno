<?php
// Incluir archivos de configuración y librerías
include('../../app/config.php');
require_once 'library/tcpdf.php';
require_once 'library/phpqrcode/qrlib.php';

// Manejo de errores mejorado
try {
    $id_estudiante = isset($_GET['id_estudiante']) ? $_GET['id_estudiante'] : null;

    if (!$id_estudiante) {
        throw new Exception("No se especificó el ID del estudiante.");
    }

    // Consulta ampliada para incluir más datos - ACTUALIZADA para obtener nombre_seccion
    $sql = "SELECT e.nombres, e.apellidos, e.fecha_nacimiento, e.tipo_cedula, e.cedula, e.cedula_escolar, 
                   i.nivel_id, gr.grado, i.id_seccion, i.nombre_seccion, i.turno_id, g.desde, g.hasta, 
                   r.nombres as rep_nombres, r.apellidos as rep_apellidos, r.cedula as rep_cedula
            FROM inscripciones i  
            JOIN estudiantes e ON i.id_estudiante = e.id_estudiante  
            JOIN gestiones g ON i.id_gestion = g.id_gestion  
            JOIN grados gr ON i.grado = gr.id_grado
            LEFT JOIN representantes r ON e.id_representante = r.id_representante
            WHERE i.id_estudiante = :id_estudiante AND g.estado = 1  
            ORDER BY i.created_at DESC  
            LIMIT 1";  
    
    $stmt = $pdo->prepare($sql);  
    $stmt->bindParam(':id_estudiante', $id_estudiante);  
    $stmt->execute();  
    $inscripcion = $stmt->fetch(PDO::FETCH_ASSOC);  

    if (!$inscripcion) {
        throw new Exception("No se encontraron datos de inscripción para el estudiante especificado.");
    }

    // Consulta para obtener el nombre y cédula del director(a)
    $sql_director = "
        SELECT p.nombres, p.apellidos, p.ci 
        FROM personas p 
        JOIN administrativos a ON p.id_persona = a.persona_id
        WHERE a.estado = 1 AND p.estado = 1
        LIMIT 1";
    
    $stmt_director = $pdo->prepare($sql_director);
    $stmt_director->execute();
    $director = $stmt_director->fetch(PDO::FETCH_ASSOC);

    if (!$director) {
        throw new Exception("No se pudo obtener el nombre o la cédula del director(a) actual.");
    }

    // Función para obtener la sección en formato correcto
    function obtenerSeccionFormateada($inscripcion) {
        if (!empty($inscripcion['nombre_seccion'])) {
            return $inscripcion['nombre_seccion'];
        }
        if (!empty($inscripcion['id_seccion'])) {
            $secciones_letras = [
                '1' => 'A','2' => 'B','3' => 'C','4' => 'D','5' => 'E','6' => 'F',
                '7' => 'G','8' => 'H','9' => 'I','10' => 'J','11' => 'K','12' => 'L'
            ];
            return isset($secciones_letras[$inscripcion['id_seccion']]) ?
                   $secciones_letras[$inscripcion['id_seccion']] :
                   $inscripcion['id_seccion'];
        }
        return 'A';
    }

    // Obtener sección
    $seccion_letras = obtenerSeccionFormateada($inscripcion);

    // 🔹 Formatear cédulas al formato V-XX.XXX.XXX
    function formatearCedula($cedula, $prefijo = 'V') {
        if (empty($cedula)) return '';
        $solo_numeros = preg_replace('/\D/', '', $cedula);
        $formateada = number_format($solo_numeros, 0, '', '.');
        return strtoupper($prefijo) . '- ' . $formateada;
    }

    // Cédula del director formateada
    $cedula_director = formatearCedula($director['ci']);

    // Crear clase personalizada para el PDF
    class ConstanciaPDF extends TCPDF {
        private $color_primario;
        public function __construct($color_primario) {
            parent::__construct('P', PDF_UNIT, 'A4', true, 'UTF-8', false);
            $this->color_primario = $color_primario;
        }
        public function Footer() {
            $this->SetY(-20);
            $this->SetFont('helvetica', '', 8);
            $this->SetTextColor(100);
            $this->SetLineWidth(0.2);
            $this->SetDrawColor(200, 200, 200);
            $this->Line(15, $this->GetY(), $this->getPageWidth()-15, $this->GetY());
            $this->Ln(3);
            $this->SetFont('helvetica', 'B', 8);
            $this->Cell(0, 4, 'UNIDAD EDUCATIVA "ROBERTO MARTÍNEZ CENTENO"', 0, 1, 'C');
            $this->SetFont('helvetica', 'B', 8);
            $this->Cell(0, 4, 'Documento generado el ' . date('d/m/Y') . ' a las ' . date('H:i:s'), 0, 1, 'C');
        }
    }

    // Colores institucionales
    $color_primario = [0, 51, 102];
    $color_secundario = [0, 51, 102];

    // Crear PDF
    $pdf = new ConstanciaPDF($color_primario);
    $pdf->SetCreator('U.E.N Roberto Martínez Centeno');
    $pdf->SetAuthor('Ministerio de Educación');
    $pdf->SetTitle('Constancia de Inscripción Oficial');
    $pdf->SetMargins(15, 35, 15);
    $pdf->SetAutoPageBreak(TRUE, 25);
    $pdf->SetFont('helvetica', '', 10);
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(true);

    // Encabezado institucional
    function agregarEncabezado($pdf, $color_primario, $color_secundario) {
        $logo_left = '../../logo/logo.png';
        $logo_right = '../../logo/MPPEducacion.png';
        $logo_y = 10;

        if (file_exists($logo_left)) {
            $pdf->Image($logo_left, 20, $logo_y, 22, 15);
        }
        if (file_exists($logo_right)) {
            $pdf->Image($logo_right, $pdf->getPageWidth()-42, $logo_y, 22, 16);
        }

        $pdf->SetY($logo_y);
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetTextColor($color_primario[0], $color_primario[1], $color_primario[2]);
        $pdf->Cell(0, 4, 'REPÚBLICA BOLIVARIANA DE VENEZUELA', 0, 1, 'C');
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(0, 5, 'MINISTERIO DEL PODER POPULAR PARA LA EDUCACIÓN', 0, 1, 'C');
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 6, 'U.E "ROBERTO MARTÍNEZ CENTENO"', 0, 1, 'C');
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->Cell(0, 4, 'CARICUAO - CARACAS - DISTRITO CAPITAL', 0, 1, 'C');
        $pdf->SetLineWidth(0.3);
        $pdf->SetDrawColor($color_primario[0], $color_primario[1], $color_primario[2]);
        $pdf->Line(15, $pdf->GetY() + 1, $pdf->getPageWidth()-15, $pdf->GetY() + 1);
        $pdf->SetY($pdf->GetY() + 8);
    }

    // QR
    $qrData = "CONSTANCIA DE INSCRIPCIÓN\n";
    $qrData .= "Estudiante: ".mb_strtoupper($inscripcion['nombres'].' '.$inscripcion['apellidos'])."\n";
    $qrData .= "Cédula: ".($inscripcion['tipo_cedula'] ? $inscripcion['tipo_cedula'].'-'.$inscripcion['cedula'] : $inscripcion['cedula_escolar'])."\n";
    $qrData .= "Grado: ".mb_strtoupper($inscripcion['grado'])."\n";
    $qrData .= "Sección: ".$seccion_letras."\n";
    $qrData .= "Turno: ".mb_strtoupper($inscripcion['turno_id'])."\n";
    $qrData .= "Fecha: ".date('d/m/Y');
    $qrTempFile = tempnam(sys_get_temp_dir(), 'qr');
    QRcode::png($qrData, $qrTempFile, QR_ECLEVEL_L, 5);

    // Fecha actual
    $meses = [1=>'enero',2=>'febrero',3=>'marzo',4=>'abril',5=>'mayo',6=>'junio',7=>'julio',8=>'agosto',9=>'septiembre',10=>'octubre',11=>'noviembre',12=>'diciembre'];
    $mes_actual = $meses[date('n')];
    $dia_actual = date('d');
    $ultimo_dia_mes = date('t');
    $dias_restantes = $ultimo_dia_mes - $dia_actual;

    $desde = date('Y', strtotime($inscripcion['desde']));
    $hasta = date('Y', strtotime($inscripcion['hasta']));

    // Preparar cédula estudiante
    $cedula_info = '';
    if (!empty($inscripcion['tipo_cedula']) && !empty($inscripcion['cedula'])) {
        $cedula_info = "portador(a) de la Cédula de Identidad <strong>" . formatearCedula($inscripcion['cedula'], $inscripcion['tipo_cedula']) . "</strong>";
    } elseif (!empty($inscripcion['cedula_escolar'])) {
        $cedula_info = "portador(a) de la Cédula Escolar <strong>" . formatearCedula($inscripcion['cedula_escolar']) . "</strong>";
    }

    // Crear PDF
    $pdf->AddPage();
    agregarEncabezado($pdf, $color_primario, $color_secundario);

    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->SetTextColor($color_secundario[0], $color_secundario[1], $color_secundario[2]);
    $pdf->Cell(0, 10, 'CONSTANCIA DE INSCRIPCIÓN', 0, 1, 'C');
    $pdf->SetLineWidth(0.5);
    $pdf->Line(($pdf->getPageWidth()-80)/2, $pdf->GetY(), ($pdf->getPageWidth()+80)/2, $pdf->GetY());
    $pdf->Ln(1);

    // Contenido
    $pdf->SetTextColor(0,0,0);
    $pdf->SetFont('helvetica','',11);

    $nombre_completo = '<strong>'.mb_strtoupper($inscripcion['nombres'].' '.$inscripcion['apellidos']).'</strong>';
    $fecha_nacimiento = 'nacido(a) el <strong>'.date('d/m/Y', strtotime($inscripcion['fecha_nacimiento'])).'</strong>';

    $frase_estudiante = "El(la) estudiante $nombre_completo";
    $frase_estudiante .= (!empty($cedula_info)) ? ", $cedula_info, $fecha_nacimiento" : ", $fecha_nacimiento";

    $html = '<div style="text-align:justify; line-height:1.6;">
        <p>Quien suscribe, <strong>' . mb_strtoupper($director['nombres'] . ' ' . $director['apellidos']) . '</strong>, titular de la Cédula de Identidad <strong>' . $cedula_director . '</strong>, en su carácter de Directora de la <strong>UNIDAD EDUCATIVA "ROBERTO MARTÍNEZ CENTENO"</strong>, ubicada en la parroquia Caricuao, Avenida Este 0, Caracas, Distrito Capital, adscrita a la Zona Educativa del Estado Distrito Capital, <strong>CERTIFICA</strong> que:</p>
        <p>'.$frase_estudiante.', se encuentra formalmente inscrito(a) en este plantel para cursar el <strong>'.mb_strtoupper($inscripcion['grado']).'</strong> de Educación <strong>'.mb_strtoupper($inscripcion['nivel_id']).'</strong>, sección <strong>'.$seccion_letras.'</strong>, turno <strong>'.mb_strtoupper($inscripcion['turno_id']).'</strong>, correspondiente al período académico <strong>'.$desde.'-'.$hasta.'</strong>.</p>';

    if (!empty($inscripcion['rep_nombres']) && !empty($inscripcion['rep_apellidos']) && !empty($inscripcion['rep_cedula'])) {
        $html .= '<p>Representante Legal: <strong>'.mb_strtoupper($inscripcion['rep_nombres'].' '.$inscripcion['rep_apellidos']).'</strong>, C.I. <strong>' . formatearCedula($inscripcion['rep_cedula']) . '</strong>.</p>';
    }

    $html .= '<p>Constancia que se expide a los '.$dias_restantes.' días del mes de '.$mes_actual.' de '.date('Y').', a solicitud de la parte interesada.</p></div>';

    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Ln(15);

    // Firma
    $pdf->SetY(180);
    $ancho_texto = 80;
    $centro = ($pdf->getPageWidth() - $ancho_texto) / 2;
    $pdf->SetX($centro);
    $pdf->Line($centro, $pdf->GetY(), $centro + $ancho_texto, $pdf->GetY());
    $pdf->Ln(8);
    $pdf->SetFont('helvetica','B',12);
    $pdf->SetTextColor($color_primario[0],$color_primario[1],$color_primario[2]);
    $pdf->SetX($centro);
    $pdf->Cell($ancho_texto,6,'LCDA. '.mb_strtoupper($director['nombres'].' '.$director['apellidos']),0,1,'C');
    $pdf->SetFont('helvetica','',11);
    $pdf->SetTextColor(0,0,0);
    $pdf->SetX($centro);
    $pdf->Cell($ancho_texto,6,'Directora',0,1,'C');
    $pdf->SetX($centro);
    $pdf->Cell($ancho_texto,6,'U.E "ROBERTO MARTÍNEZ CENTENO"',0,1,'C');

    // QR
    $qrSize = 30;
    $qrX = $pdf->getPageWidth() - $qrSize - 20;
    $qrY = 180;
    $pdf->SetDrawColor(100,100,100);
    $pdf->Rect($qrX,$qrY,$qrSize,$qrSize,'D',array('width'=>0.3));
    $pdf->Image($qrTempFile,$qrX+1,$qrY+1,$qrSize-2,$qrSize-2,'PNG');
    $pdf->SetFont('helvetica','',8);
    $pdf->SetTextColor($color_primario[0],$color_primario[1],$color_primario[2]);
    $pdf->SetXY($qrX,$qrY+$qrSize);
    $pdf->Cell($qrSize,4,'CÓDIGO DE VERIFICACIÓN',0,1,'C');
    $pdf->SetXY($qrX,$qrY+$qrSize+4);
    $pdf->SetFont('helvetica','',7);
    $pdf->SetTextColor(100,100,100);
    $pdf->Cell($qrSize,4,'Escanear para validar',0,1,'C');

    // Marca de agua
    $pdf->SetAlpha(0.08);
    $pdf->Image('../../logo/logo.png',25,50,170,170,'','','',false,300,'',false,false,0);
    $pdf->SetAlpha(1);

    unlink($qrTempFile);

    $nombre_archivo = 'constancia_inscripcion_' .
                     ($inscripcion['cedula'] ? $inscripcion['cedula'] : $inscripcion['cedula_escolar']) . '_' .
                     date('Y_m_d') . '.pdf';
    $pdf->Output($nombre_archivo, 'I');

} catch (Exception $e) {
    header('Content-Type: text/html; charset=utf-8');
    echo '<div style="background:#f8d7da;color:#721c24;padding:15px;border:1px solid #f5c6cb;border-radius:5px;margin:20px;text-align:center;">';
    echo '<h4>Error al generar el reporte</h4>';
    echo '<p><strong>Detalles:</strong> '.htmlspecialchars($e->getMessage()).'</p>';
    echo '<p style="font-size:0.9em;color:#856404;">Por favor, contacte al administrador del sistema.</p>';
    echo '</div>';
}
?>
