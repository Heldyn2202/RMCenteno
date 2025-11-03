<?php
// Incluir archivos de configuración y librería TCPDF
include('../../app/config.php');
require_once 'library/tcpdf.php';

// Manejo de errores mejorado
try {
    // Obtener el periodo académico activo
    $sql_gestiones = "SELECT * FROM gestiones WHERE estado = 1 ORDER BY desde DESC LIMIT 1";
    $query_gestiones = $pdo->prepare($sql_gestiones);
    $query_gestiones->execute();
    $gestion_activa = $query_gestiones->fetch(PDO::FETCH_ASSOC);

    if (!$gestion_activa) {
        throw new Exception("No se encontró un periodo académico activo.");
    }

    // Obtener el grado seleccionado
    $grado_seleccionado = isset($_GET['grado']) ? $_GET['grado'] : null;

    // Consulta para obtener la lista de estudiantes inscritos
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

    // Crear clase personalizada para el PDF con footer profesional
    class MatriculaPDF extends TCPDF {
        private $color_primario;
        private $grado_filtrado;
        private $gestion_activa;
        
        public function __construct($color_primario, $grado_filtrado = null, $gestion_activa = null) {
            parent::__construct('P', PDF_UNIT, 'A4', true, 'UTF-8', false);
            $this->color_primario = $color_primario;
            $this->grado_filtrado = $grado_filtrado;
            $this->gestion_activa = $gestion_activa;
        }
        
        // Page footer profesional
        public function Footer() {
            $this->SetY(-20);
            $this->SetFont('helvetica', '', 8);
            $this->SetTextColor(100); // Color gris profesional
            
            // Línea separadora superior del pie
            $this->SetLineWidth(0.2);
            $this->SetDrawColor(200, 200, 200);
            $this->Line(15, $this->GetY(), $this->getPageWidth()-15, $this->GetY());
            
            $this->Ln(3);
            
            // Información institucional en el pie
            $this->SetFont('helvetica', 'B', 8);
            $this->Cell(0, 4, 'UNIDAD EDUCATIVA "ROBERTO MARTÍNEZ CENTENO"', 0, 1, 'C');
            
            // Información de documento y paginación
            $this->SetFont('helvetica', 'B', 8);
            $this->Cell(0, 4, 'Documento generado el ' . date('d/m/Y') . ' a las ' . date('H:i:s'), 0, 1, 'C');
            
            // Línea informativa inferior
            $this->SetY(-9);
            $this->SetFont('helvetica', 'B', 8);
            
            $info_text = 'Página ' . $this->getAliasNumPage() . ' de ' . $this->getAliasNbPages();
            
            $this->Cell(195, 4, $info_text, 0, 1, 'C');
        }
    }

    // Paleta de colores institucionales
    $color_primario = array(0, 51, 102);     // Azul oscuro corporativo
    $color_secundario = array(128, 0, 0);    // Rojo vino para acentos
    $color_terciario = array(79, 129, 189);  // Azul medio

    // Crear PDF personalizado
    $pdf = new MatriculaPDF($color_primario, $grado_seleccionado, $gestion_activa);
    
    // Información del documento
    $pdf->SetCreator('U.E Roberto Martínez Centeno');
    $pdf->SetAuthor('Ministerio de Educación');
    $pdf->SetTitle('Reporte Oficial de Matrícula Escolar');
    $pdf->SetSubject('Reporte Institucional de Matrícula');
    $pdf->SetKeywords('TCPDF, PDF, matrícula, escolar, reporte oficial, educación');

    // Configuración de márgenes y página
    $pdf->SetMargins(15, 35, 15);
    $pdf->SetHeaderMargin(8);
    $pdf->SetFooterMargin(20);
    $pdf->SetAutoPageBreak(TRUE, 25);
    
    // Configurar fuente por defecto
    $pdf->SetFont('helvetica', '', 10);
    
    // Configurar impresión
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(true);

    // Función para agregar encabezado institucional
    function agregarEncabezado($pdf, $gestion_activa, $color_primario, $color_secundario) {
        // Logos institucionales
        $logo_left = '../../logo/logo.png';
        $logo_right = '../../logo/MPPEducacion.png';
        
        // Verificar existencia de logos
        $logo_left_exists = file_exists($logo_left);
        $logo_right_exists = file_exists($logo_right);
        
        // Configuración separada para cada logo
        $logo_y_position = 10;
        
        // CONFIGURACIÓN LOGO IZQUIERDO (Logo de la Institución)
        $logo_left_width = 13;    // Ancho específico para logo izquierdo
        $logo_left_height = 13;   // Alto específico para logo izquierdo
        $logo_left_x = 20;        // Posición X específica
        
        // CONFIGURACIÓN LOGO DERECHO (Logo del Ministerio)
        $logo_right_width = 25;   // Ancho específico para logo derecho
        $logo_right_height = 10;  // Alto específico para logo derecho
        $page_width = $pdf->getPageWidth();
        $logo_right_x = $page_width - 20 - $logo_right_width; // Posición X calculada
        
        // Agregar logo izquierdo con su configuración específica
        if($logo_left_exists) {
            $pdf->Image(
                $logo_left, 
                $logo_left_x, 
                $logo_y_position, 
                $logo_left_width,  // Ancho específico
                $logo_left_height, // Alto específico
                'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false
            );
        } else {
            // Placeholder si no existe el logo izquierdo
            $pdf->SetFont('helvetica', 'B', 8);
            $pdf->SetTextColor(150);
            $pdf->SetXY($logo_left_x, $logo_y_position);
            $pdf->Cell($logo_left_width, $logo_left_height, 'LOGO INSTITUCIÓN', 1, 0, 'C');
        }
        
        // Agregar logo derecho con su configuración específica
        if($logo_right_exists) {
            $pdf->Image(
                $logo_right, 
                $logo_right_x, 
                $logo_y_position, 
                $logo_right_width,  // Ancho específico
                $logo_right_height, // Alto específico
                'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false
            );
        } else {
            // Placeholder si no existe el logo derecho
            $pdf->SetFont('helvetica', 'B', 8);
            $pdf->SetTextColor(150);
            $pdf->SetXY($logo_right_x, $logo_y_position);
            $pdf->Cell($logo_right_width, $logo_right_height, 'MINISTERIO', 1, 0, 'C');
        }
        
        // Información institucional central
        $pdf->SetY($logo_y_position);
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->SetTextColor($color_primario[0], $color_primario[1], $color_primario[2]);
        $pdf->Cell(0, 4, 'REPÚBLICA BOLIVARIANA DE VENEZUELA', 0, 1, 'C');
        
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->Cell(0, 5, 'MINISTERIO DEL PODER POPULAR PARA LA EDUCACIÓN', 0, 1, 'C');
        
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(0, 6, 'U.E "ROBERTO MARTÍNEZ CENTENO"', 0, 1, 'C');
        
        // Línea separadora
        $pdf->SetLineWidth(0.3);
        $pdf->SetDrawColor($color_primario[0], $color_primario[1], $color_primario[2]);
        $pdf->Line(15, $pdf->GetY() + 1, $pdf->getPageWidth()-15, $pdf->GetY() + 1);
        
        // Información del periodo académico
        $pdf->SetY($pdf->GetY() + 4);
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->SetTextColor($color_secundario[0], $color_secundario[1], $color_secundario[2]);
        $pdf->Cell(0, 6, 'REPORTE OFICIAL DE MATRÍCULA ESCOLAR', 0, 1, 'C');
        
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor($color_primario[0], $color_primario[1], $color_primario[2]);
        $periodo_texto = 'Periodo Académico: ' . date('Y', strtotime($gestion_activa['desde'])) . ' - ' . date('Y', strtotime($gestion_activa['hasta']));
        $pdf->Cell(0, 5, $periodo_texto, 0, 1, 'C');
        
        $pdf->SetY($pdf->GetY() + 3);
    }

    // Función para crear sección con estilo institucional
    function crearSeccion($pdf, $titulo, $color_primario) {
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->SetFillColor($color_primario[0], $color_primario[1], $color_primario[2]);
        $pdf->SetTextColor(255);
        $pdf->Cell(0, 7, $titulo, 0, 1, 'C', true);
        $pdf->SetTextColor(0);
        $pdf->Ln(3);
    }

    // Agregar primera página
    $pdf->AddPage();
    agregarEncabezado($pdf, $gestion_activa, $color_primario, $color_secundario);

    // Información de filtro aplicado - CENTRADO
    if ($grado_seleccionado) {
        $pdf->SetFont('helvetica', 'I', 9);
        $pdf->SetTextColor($color_secundario[0], $color_secundario[1], $color_secundario[2]);
        $pdf->Cell(0, 6, 'Filtro aplicado: Grado ' . htmlspecialchars($grado_seleccionado), 0, 1, 'C');
        $pdf->Ln(2);
    }

    // Sección de distribución por grados y secciones - CENTRADO
    crearSeccion($pdf, 'DISTRIBUCIÓN DE ESTUDIANTES POR GRADO Y SECCIÓN', $color_primario);

    // Agrupar por grado y sección
    $grados_secciones = [];
    $total_general_masculino = 0;
    $total_general_femenino = 0;
    $total_grados = 0;
    $total_secciones = 0;

    foreach ($inscripciones as $inscripcion) {
        $grado = $inscripcion['grado'];
        $seccion = $inscripcion['nombre_seccion'];

        if (!isset($grados_secciones[$grado][$seccion])) {
            $grados_secciones[$grado][$seccion] = ['masculino' => 0, 'femenino' => 0];
        }

        if (strtolower($inscripcion['genero']) == 'masculino') {
            $grados_secciones[$grado][$seccion]['masculino']++;
            $total_general_masculino++;
        } elseif (strtolower($inscripcion['genero']) == 'femenino') {
            $grados_secciones[$grado][$seccion]['femenino']++;
            $total_general_femenino++;
        }
    }

    // Calcular total de grados y secciones
    $total_grados = count($grados_secciones);
    foreach ($grados_secciones as $secciones) {
        $total_secciones += count($secciones);
    }

    // Crear tabla de distribución - CENTRADA
    $html = '<style>
                .centered-table { margin: 0 auto; border-collapse: collapse; font-size: 9pt; }
                th { background-color: #003366; color: white; font-weight: bold; padding: 6px; text-align: center; border: 1px solid #ddd; }
                td { padding: 6px; border: 1px solid #ddd; text-align: center; }
            </style>';

    $html .= '<table class="centered-table">
                <tr>
                    <th width="25%">Grado</th>
                    <th width="25%">Sección</th>
                    <th width="25%">Total Masculino</th>
                    <th width="25%">Total Femenino</th>
                </tr>';

    // Imprimir grados y secciones
    foreach ($grados_secciones as $grado => $secciones) {
        $primer_grado = true;
        $num_secciones = count($secciones);
        
        foreach ($secciones as $seccion => $totales) {
            $html .= '<tr>';
            
            if ($primer_grado) {
                $html .= '<td rowspan="' . $num_secciones . '"><strong>' . htmlspecialchars($grado) . '</strong></td>';
                $primer_grado = false;
            }
            
            $html .= '<td>' . htmlspecialchars($seccion) . '</td>
                      <td>' . htmlspecialchars($totales['masculino']) . '</td>
                      <td>' . htmlspecialchars($totales['femenino']) . '</td>
                    </tr>';
        }
    }

    $html .= '</table>';

    // Escribir la tabla al PDF
    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Ln(5);

    // Resumen por grados (detallado)
    crearSeccion($pdf, 'DISTRIBUCIÓN DETALLADA POR GRADOS', $color_primario);

    $html_detalle_grados = '<style>
                                .grados-table { margin: 0 auto; border-collapse: collapse; font-size: 9pt; width: 90%; }
                                .grados-header { background-color: #003366; color: white; font-weight: bold; }
                                .grados-subheader { background-color: #4f81bd; color: white; font-weight: bold; }
                            </style>';

    $html_detalle_grados .= '<table class="grados-table">
                                <tr class="grados-header">
                                    <th width="30%" style="text-align: center;">Grado</th>
                                    <th width="30%" style="text-align: center;">Secciones</th>
                                    <th width="26%" style="text-align: center;">Total Masculino</th>
                                    <th width="25%" style="text-align: center;">Total Femenino</th>
                                </tr>';

    foreach ($grados_secciones as $grado => $secciones) {
        $total_grado_masculino = 0;
        $total_grado_femenino = 0;
        $num_secciones_grado = count($secciones);
        
        foreach ($secciones as $seccion => $totales) {
            $total_grado_masculino += $totales['masculino'];
            $total_grado_femenino += $totales['femenino'];
        }
        
        $html_detalle_grados .= '<tr>
                                    <td style="text-align: center;"><strong>' . htmlspecialchars($grado) . '</strong></td>
                                    <td style="text-align: center;">' . $num_secciones_grado . '</td>
                                    <td style="text-align: center;">' . $total_grado_masculino . '</td>
                                    <td style="text-align: center;">' . $total_grado_femenino . '</td>
                                </tr>';
    }

    $html_detalle_grados .= '</table>';
    $pdf->writeHTML($html_detalle_grados, true, false, true, false, '');

    // SEGUNDA PÁGINA - RESUMEN GENERAL
    $pdf->AddPage();
    agregarEncabezado($pdf, $gestion_activa, $color_primario, $color_secundario);

    

    $total_general = $total_general_masculino + $total_general_femenino;
    $porcentaje_masculino = $total_general > 0 ? ($total_general_masculino / $total_general) * 100 : 0;
    $porcentaje_femenino = $total_general > 0 ? ($total_general_femenino / $total_general) * 100 : 0;

    // Estadísticas generales
    $html_estadisticas = '<style>
                            .estadisticas-table { margin: 0 auto; border-collapse: collapse; font-size: 10pt; width: 80%; }
                            .estadisticas-header { background-color: #003366; color: white; font-weight: bold; }
                            .estadisticas-row { background-color: #f8f9fa; }
                            .estadisticas-total { background-color: #004080; color: white; font-weight: bold; }
                            .estadisticas-label { font-weight: bold; text-align: right; padding-right: 10px; }
                            .estadisticas-value { text-align: left; padding-left: 10px; }
                        </style>';

    $html_estadisticas .= '<table class="estadisticas-table">
                            <tr class="estadisticas-header">
                                <th width="125%" style="text-align: center; font-size: 12pt;">ESTADÍSTICAS INSTITUCIONALES</th>
                            </tr>
                            <tr class="estadisticas-row">
                                <td width="100%" class="estadisticas-label" style="text-align: center;">Total de Grados:</td>
                                <td width="25%" class="estadisticas-value" style="text-align: center;"><strong>' . $total_grados . '</strong></td>
                               
                            </tr>
                            <tr>
                                 <td width="100%" class="estadisticas-label" style="text-align: center;">Total de Secciones:</td>
                                <td width="25%" class="estadisticas-value" style="text-align: center;"><strong>' . $total_secciones . '</strong></td>
                            </tr>
                        </table>';

    $pdf->writeHTML($html_estadisticas, true, false, true, false, '');
    $pdf->Ln(8);

    // Sección de resumen general de matrícula - CENTRADO
    crearSeccion($pdf, 'RESUMEN GENERAL DE MATRÍCULA', $color_primario);

    // Distribución por género
    $html_resumen = '<style>
                        .resumen-table { margin: 0 auto; border-collapse: collapse; font-size: 10pt; width: 60%; }
                        .resumen-header { background-color: #4f81bd; color: white; font-weight: bold; }
                        .resumen-total { background-color: #004080; color: white; font-weight: bold; }
                    </style>';

    $html_resumen .= '<table class="resumen-table">
                        <tr class="resumen-header">
                            <th width="46%">Género</th>
                            <th width="60%">Cantidad</th>
                            <th width="60%">Porcentaje</th>
                        </tr>
                        <tr>
                            <td>Masculino</td>
                            <td>' . $total_general_masculino . '</td>
                            <td>' . number_format($porcentaje_masculino, 1) . '%</td>
                        </tr>
                        <tr>
                            <td>Femenino</td>
                            <td>' . $total_general_femenino . '</td>
                            <td>' . number_format($porcentaje_femenino, 1) . '%</td>
                        </tr>
                        <tr class="resumen-total">
                            <td><strong>TOTAL GENERAL</strong></td>
                            <td><strong>' . $total_general . '</strong></td>
                            <td><strong>100%</strong></td>
                        </tr>
                    </table>';

    $pdf->writeHTML($html_resumen, true, false, true, false, '');
    $pdf->Ln(10);

    // Cerrar y generar el PDF
    $pdf->Output('reporte_matricula_oficial_' . date('Y_m_d') . '.pdf', 'I');

} catch (Exception $e) {
    // Manejo de errores mejorado - CENTRADO
    header('Content-Type: text/html; charset=utf-8');
    echo '<div style="background-color: #f8d7da; color: #721c24; padding: 15px; border: 1px solid #f5c6cb; border-radius: 5px; margin: 20px; text-align: center;">';
    echo '<h4 style="margin-top: 0;">Error al generar el reporte</h4>';
    echo '<p><strong>Detalles:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<p style="font-size: 0.9em; color: #856404;">Por favor, contacte al administrador del sistema.</p>';
    echo '</div>';
}
?>