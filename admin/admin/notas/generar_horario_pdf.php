<?php
require_once('../../app/config.php');

// Verificar si TCPDF está disponible
$tcpdf_path = __DIR__ . '/library/tcpdf.php';
if (!file_exists($tcpdf_path)) {
    die("Error: TCPDF library no encontrada. Por favor, instale TCPDF en: " . $tcpdf_path);
}
require_once($tcpdf_path);

// Obtener el ID del horario
$id_horario = isset($_GET['id_horario']) ? (int)$_GET['id_horario'] : 0;
$preview = isset($_GET['preview']) ? (bool)$_GET['preview'] : true;

if (!$id_horario) {
    die("ID de horario no proporcionado");
}

// Obtener datos de la institución
$sql_institucion = "SELECT nombre_institucion, direccion, telefono, correo, logo 
                    FROM configuracion_instituciones 
                    WHERE estado = '1' 
                    ORDER BY id_config_institucion DESC 
                    LIMIT 1";
$query_institucion = $pdo->query($sql_institucion);
$institucion = $query_institucion->fetch(PDO::FETCH_ASSOC);

if (!$institucion) {
    $institucion = [
        'nombre_institucion' => 'U.E. ROBERTO MARTINEZ CENTENO', // MODIFICADO: U.E. en lugar de U.E.N
        'direccion' => 'Parroquia Caricuao, Avenida Este 0, Caracas, Distrito Capital',
        'telefono' => '0212-433-1080',
        'correo' => 'admin@gmail.com',
        'logo' => null
    ];
}

// Obtener los datos del horario
$sql_horario = "SELECT h.*, g.grado, s.nombre_seccion, s.turno, 
                YEAR(ges.desde) as anio_inicio,
                YEAR(ges.hasta) as anio_fin,
                ges.desde, ges.hasta 
                FROM horarios h
                JOIN grados g ON h.id_grado = g.id_grado
                JOIN secciones s ON h.id_seccion = s.id_seccion
                JOIN gestiones ges ON h.id_gestion = ges.id_gestion
                WHERE h.id_horario = :id_horario";
$query_horario = $pdo->prepare($sql_horario);
$query_horario->bindParam(':id_horario', $id_horario);
$query_horario->execute();
$horario = $query_horario->fetch(PDO::FETCH_ASSOC);

if (!$horario) {
    die("Horario no encontrado");
}

// Formatear período académico: solo años (2025 - 2026)
if (isset($horario['anio_inicio']) && isset($horario['anio_fin'])) {
    $periodo_academico = $horario['anio_inicio'] . ' - ' . $horario['anio_fin'];
} elseif (isset($horario['desde']) && isset($horario['hasta'])) {
    $anio_inicio = date('Y', strtotime($horario['desde']));
    $anio_fin = date('Y', strtotime($horario['hasta']));
    $periodo_academico = $anio_inicio . ' - ' . $anio_fin;
} else {
    $periodo_academico = 'N/A';
}

// Obtener los detalles del horario
$sql_detalles = "SELECT hd.*, m.nombre_materia, 
                 CONCAT(p.nombres, ' ', p.apellidos) as profesor
                 FROM horario_detalle hd
                 JOIN materias m ON hd.id_materia = m.id_materia
                 LEFT JOIN profesores p ON hd.id_profesor = p.id_profesor
                 WHERE hd.id_horario = :id_horario
                 ORDER BY FIELD(hd.dia_semana, 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'),
                 hd.hora_inicio";
$query_detalles = $pdo->prepare($sql_detalles);
$query_detalles->bindParam(':id_horario', $id_horario);
$query_detalles->execute();
$detalles = $query_detalles->fetchAll(PDO::FETCH_ASSOC);

// Organizar los detalles por día y hora
$horario_organizado = [];
foreach ($detalles as $detalle) {
    $dia = $detalle['dia_semana'];
    $hora_inicio = $detalle['hora_inicio'];
    if (is_string($hora_inicio)) {
        if (strlen($hora_inicio) == 5) {
            $hora_inicio .= ':00';
        }
    } else {
        $hora_inicio = $hora_inicio->format('H:i:s');
    }
    $hora_inicio_normalizada = substr($hora_inicio, 0, 8);
    $horario_organizado[$dia][$hora_inicio_normalizada] = $detalle;
}

// Definir los períodos de clase
$horarios_clase = [
    '07:00:00' => '07:40:00',   // Período 1
    '07:40:00' => '08:20:00',   // Período 2
    '08:20:00' => '09:00:00',   // Período 3
    '09:00:00' => '09:40:00',   // Período 4
    '09:50:00' => '10:30:00',   // Período 5
    '10:30:00' => '11:10:00',   // Período 6
    '11:10:00' => '11:50:00',   // Período 7
    '11:50:00' => '12:30:00',   // Período 8
    '01:00:00' => '01:40:00',   // Período 9
    '01:40:00' => '02:20:00',   // Período 10
    '02:30:00' => '03:10:00',   // Período 11
    '03:10:00' => '03:50:00',   // Período 12
    '03:50:00' => '04:30:00',   // Período 13
    '04:20:00' => '05:10:00'    // Período 14
];

// Bloques especiales (RECESO y ALMUERZO)
$bloques_especiales = [
    '09:40:00' => ['09:50:00', 'RECESO'],
    '12:30:00' => ['13:00:00', 'ALMUERZO'],
    '02:20:00' => ['02:30:00', 'RECESO']
];

// Combinar todos los bloques y ordenarlos
$todos_bloques = [];
foreach ($horarios_clase as $inicio => $fin) {
    $todos_bloques[] = ['tipo' => 'clase', 'inicio' => $inicio, 'fin' => $fin];
}
foreach ($bloques_especiales as $inicio => $datos) {
    $todos_bloques[] = ['tipo' => 'especial', 'inicio' => $inicio, 'fin' => $datos[0], 'nombre' => $datos[1]];
}

// Ordenar por hora de inicio
usort($todos_bloques, function($a, $b) {
    list($h_a, $m_a) = explode(':', $a['inicio']);
    list($h_b, $m_b) = explode(':', $b['inicio']);
    
    $h_a_int = (int)$h_a;
    $h_b_int = (int)$h_b;
    
    if ($h_a_int >= 1 && $h_a_int < 7) {
        $h_a_int += 12;
    }
    if ($h_b_int >= 1 && $h_b_int < 7) {
        $h_b_int += 12;
    }
    
    $min_a = $h_a_int * 60 + (int)$m_a;
    $min_b = $h_b_int * 60 + (int)$m_b;
    
    return $min_a - $min_b;
});

// Definir los días de la semana
$dias_semana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];

// Crear nuevo documento PDF en orientación horizontal
$pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);

// Configurar documento
$pdf->SetCreator('Sistema de Gestión Escolar');
$pdf->SetAuthor('Sistema de Gestión Escolar');
$pdf->SetTitle('Horario - ' . $horario['grado'] . ' ' . $horario['nombre_seccion']);
$pdf->SetSubject('Horario de Clases');

// Desactivar encabezado y pie de página automáticos
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Configurar márgenes mínimos (10mm como solicitado)
$pdf->SetMargins(10, 8, 10); // MODIFICADO: Margen superior reducido de 10 a 8 para subir contenido
$pdf->SetHeaderMargin(0);
$pdf->SetFooterMargin(0);
$pdf->SetAutoPageBreak(false, 0);

// Añadir una página
$pdf->AddPage();

// Obtener dimensiones de la página (Landscape A4: 297mm x 210mm)
$page_width = 297;
$page_height = 210;

// ========== ENCABEZADO CON LOGO ==========
$pdf->SetY(8); // MODIFICADO: Subido de 10 a 8
$margin_right = 10;
$logo_width = 28; // MODIFICADO: Un poco más pequeño para equilibrar
$logo_height = 28; // MODIFICADO: Un poco más pequeño para equilibrar
$logo_x = $page_width - $logo_width - $margin_right;

// Función para verificar si un archivo es un logo válido
function es_logo_valido($ruta) {
    if (!file_exists($ruta) || !is_file($ruta) || !is_readable($ruta)) {
        return false;
    }
    
    $tamano = filesize($ruta);
    if ($tamano < 500) {
        $contenido = @file_get_contents($ruta, false, null, 0, 200);
        if ($contenido && (stripos($contenido, 'git-lfs') !== false || stripos($contenido, 'version https://git-lfs') !== false)) {
            return false;
        }
    }
    
    $handle = @fopen($ruta, 'rb');
    if ($handle) {
        $header = @fread($handle, 8);
        @fclose($handle);
        if ($header && (substr($header, 0, 4) === "\x89\x50\x4E\x47" || substr($header, 0, 3) === "\xFF\xD8\xFF")) {
            return true;
        }
    }
    
    return $tamano > 500;
}

// Intentar obtener logo de diferentes ubicaciones
$logo_path = null;
$logo_existe = false;
$posibles_logos = [];

if ($institucion['logo']) {
    $posibles_logos[] = '../../public/img/uploads/' . $institucion['logo'];
}

$posibles_logos[] = '../../logo/logo.png';
$posibles_logos[] = '../../logos/logo.png';
$posibles_logos[] = '../../login/logos/logo.png';
$posibles_logos[] = '../../public/img/logo.png';
$posibles_logos[] = '../../public/img/uploads/logo.png';
$posibles_logos[] = '../../logo/logo.jpg';
$posibles_logos[] = '../../logos/logo.jpg';
$posibles_logos[] = '../../login/logos/logo.jpg';

$base_dir = __DIR__;

foreach ($posibles_logos as $ruta_logo) {
    $ruta_completa = $base_dir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $ruta_logo);
    $ruta_completa = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $ruta_completa);
    
    if (es_logo_valido($ruta_completa)) {
        $logo_path = realpath($ruta_completa);
        $logo_existe = true;
        break;
    }
}

// Mostrar logo en la esquina superior derecha
if ($logo_existe && $logo_path) {
    try {
        if (es_logo_valido($logo_path)) {
            $pdf->Image($logo_path, $logo_x, 8, $logo_width, $logo_height, '', '', 'T', false, 300, '', false, false, 0, false, false, false);
        } else {
            $logo_existe = false;
        }
    } catch (Exception $e) {
        $logo_existe = false;
    } catch (Error $e) {
        $logo_existe = false;
    }
}

// ========== TÍTULO PRINCIPAL ==========
$pdf->SetFont('helvetica', 'B', 16);
$pdf->SetY(12); // MODIFICADO: Subido de 15 a 12
$pdf->Cell(0, 6, 'HORARIO', 0, 1, 'C');
$pdf->Ln(1); // MODIFICADO: Reducido de 2 a 1

// ========== INFORMACIÓN DE LA INSTITUCIÓN ==========
if ($institucion['nombre_institucion']) {
    $pdf->SetFont('helvetica', 'B', 12);
    // MODIFICADO: Reemplazar "U.E.N" por "U.E." en el nombre de la institución
    $nombre_corregido = str_replace('U.E.N', 'U.E.', $institucion['nombre_institucion']);
    $texto_institucion = strtoupper($nombre_corregido);
    $pdf->Cell(0, 5, $texto_institucion, 0, 1, 'C');
}

// ========== INFORMACIÓN DEL HORARIO ==========
$pdf->SetFont('helvetica', 'B', 11); // MODIFICADO: Reducido de 12 a 11 para hacer espacio
$turno_formateado = str_replace('T', '', $horario['turno']);
// MODIFICADO: Si el turno está vacío, no mostrar paréntesis vacíos
if (empty($turno_formateado) || trim($turno_formateado) === '') {
    $texto_grado_seccion = $horario['grado'] . ' - Sección ' . $horario['nombre_seccion'];
} else {
    $texto_grado_seccion = $horario['grado'] . ' - Sección ' . $horario['nombre_seccion'] . ' (' . $turno_formateado . ')';
}
$pdf->Cell(0, 4, $texto_grado_seccion, 0, 1, 'C'); // MODIFICADO: Reducido de 5 a 4

$pdf->SetFont('helvetica', '', 10); // MODIFICADO: Reducido de 11 a 10
$texto_periodo = 'Período Académico: ' . $periodo_academico;
$pdf->Cell(0, 3, $texto_periodo, 0, 1, 'C'); // MODIFICADO: Reducido de 4 a 3
$pdf->Ln(2); // MODIFICADO: Reducido de 3 a 2

// ========== TABLA DEL HORARIO ==========
// Calcular ancho disponible
$ancho_disponible = 297 - 20; // 277mm total disponible
$ancho_hora = 32; // MODIFICADO: Aumentado de 30 a 32 para un poco más de espacio en columna hora
$ancho_dia = ($ancho_disponible - $ancho_hora) / 5; // 49mm por día (ligeramente reducido)
$suma_anchos = $ancho_hora + ($ancho_dia * 5);
if (abs($suma_anchos - $ancho_disponible) > 0.1) {
    $ancho_hora = $ancho_disponible - ($ancho_dia * 5);
}
$alto_celda = 8;
$padding_celda = 2;

// **MODIFICADO**: Altura de fila aumentada para hacer los cuadros más grandes
// Ahora tenemos más espacio porque hemos subido el contenido y reducido márgenes
// Altura disponible: 210mm - (encabezado ~35mm) - (pie ~15mm) = 160mm
// Número de filas: 17
// Altura por fila: 160mm / 17 = ~9.4mm
$altura_fija_fila = 9.0; // MODIFICADO: Aumentado de 7.5 a 9.0 para cuadros más grandes

// Colores
$color_header = [70, 130, 180];
$color_borde = [200, 200, 200];

// Cabecera de la tabla
$pdf->SetFillColor($color_header[0], $color_header[1], $color_header[2]);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('helvetica', 'B', 10); // MODIFICADO: Reducido de 11 a 10
$pdf->SetLineWidth(0.3);

// Columna de hora
$pdf->Cell($ancho_hora, $alto_celda, 'HORA', 1, 0, 'C', 1);

// Columnas de días
foreach ($dias_semana as $dia) {
    $pdf->Cell($ancho_dia, $alto_celda, strtoupper($dia), 1, 0, 'C', 1);
}
$pdf->Ln();

// Restaurar color de texto
$pdf->SetTextColor(0, 0, 0);
$font_size_base = 9; // MODIFICADO: Mantenido en 9 para legibilidad
$font_size_reducido = 7; // MODIFICADO: Mantenido en 7 para texto largo

// Guardar Y inicial antes de la tabla
$y_tabla_inicio = $pdf->GetY();

// Filas de horarios
foreach ($todos_bloques as $bloque_info) {
    $hora_inicio = $bloque_info['inicio'];
    $hora_fin = $bloque_info['fin'];
    $es_especial = ($bloque_info['tipo'] === 'especial');
    $nombre_especial = $es_especial ? $bloque_info['nombre'] : '';
    
    // Formatear hora (sin segundos)
    $hora_inicio_formato = substr($hora_inicio, 0, 5);
    $hora_fin_formato = substr($hora_fin, 0, 5);
    
    // Para ALMUERZO, mostrar 01:00 en lugar de 13:00
    if ($es_especial && $nombre_especial === 'ALMUERZO' && $hora_fin === '13:00:00') {
        $hora_fin_formato = '01:00';
    }
    
    // Guardar posición inicial
    $x_inicial = 10;
    $y_fila = $pdf->GetY();
    
    // Verificar si todavía hay espacio en la página
    if ($y_fila > ($page_height - 25)) {
        break;
    }
    
    $contenidos = [];
    $tamanos_fuente = [];
    
    if ($es_especial) {
        foreach ($dias_semana as $dia) {
            $contenidos[$dia] = $nombre_especial;
            $tamanos_fuente[$dia] = $font_size_base;
        }
    } else {
        foreach ($dias_semana as $dia) {
            if (isset($horario_organizado[$dia][$hora_inicio])) {
                $clase = $horario_organizado[$dia][$hora_inicio];
                $materia = $clase['nombre_materia'];
                $profesor = $clase['profesor'] ? '(' . $clase['profesor'] . ')' : '';
                $texto = $materia . "\n" . $profesor;
                $contenidos[$dia] = $texto;
                
                $ancho_celda_contenido = $ancho_dia - ($padding_celda * 2);
                $ancho_estimado = strlen($texto) * ($font_size_base * 0.4);
                $tamano_fuente_celda = ($ancho_estimado > $ancho_celda_contenido * 0.8) ? $font_size_reducido : $font_size_base;
                $tamanos_fuente[$dia] = $tamano_fuente_celda;
            } else {
                $contenidos[$dia] = '';
                $tamanos_fuente[$dia] = $font_size_base;
            }
        }
    }
    
    // Dibujar celda de hora
    if ($es_especial) {
        $pdf->SetFillColor(255, 243, 205);
    } else {
        $pdf->SetFillColor(245, 245, 245);
    }
    $pdf->SetXY($x_inicial, $y_fila);
    $pdf->SetFont('helvetica', '', $font_size_base);
    
    $altura_texto_hora = $pdf->getStringHeight($ancho_hora - 4, $hora_inicio_formato . ' - ' . $hora_fin_formato, false, false);
    $y_hora_texto = $y_fila + (($altura_fija_fila - $altura_texto_hora) / 2);
    
    $pdf->Cell($ancho_hora, $altura_fija_fila, '', 1, 0, 'C', 1);
    $pdf->SetXY($x_inicial + 2, $y_hora_texto);
    $pdf->Cell($ancho_hora - 4, $altura_texto_hora, $hora_inicio_formato . ' - ' . $hora_fin_formato, 0, 0, 'C', 0);
    $pdf->SetXY($x_inicial, $y_fila);
    
    // Dibujar celdas de días
    $x_pos = $x_inicial + $ancho_hora;
    foreach ($dias_semana as $dia) {
        if ($es_especial) {
            $pdf->SetFillColor(255, 243, 205);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFont('helvetica', 'B', $font_size_base);
        } else {
            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetTextColor(0, 0, 0);
        }
        
        if (!empty($contenidos[$dia])) {
            $tamano_fuente_actual = $tamanos_fuente[$dia];
            if (!$es_especial) {
                $pdf->SetFont('helvetica', '', $tamano_fuente_actual);
            }
            
            $ancho_celda_contenido = $ancho_dia - ($padding_celda * 2);
            $line_height = $tamano_fuente_actual * 1.2;
            
            $altura_texto_total = $pdf->getStringHeight($ancho_celda_contenido, $contenidos[$dia], false, true, '', 1);
            $y_texto_inicial = $y_fila + (($altura_fija_fila - $altura_texto_total) / 2);
            
            $pdf->SetXY($x_pos, $y_fila);
            $pdf->Cell($ancho_dia, $altura_fija_fila, '', 1, 0, 'C', true);
            
            $pdf->SetXY($x_pos + $padding_celda, $y_texto_inicial);
            $pdf->MultiCell($ancho_celda_contenido, $line_height, $contenidos[$dia], 0, 'C', false);
            
            $pdf->SetXY($x_pos, $y_fila);
        } else {
            $pdf->SetXY($x_pos, $y_fila);
            $pdf->Cell($ancho_dia, $altura_fija_fila, '', 1, 0, 'C', true);
        }
        
        $x_pos += $ancho_dia;
    }
    
    // Mover a la siguiente fila
    $pdf->SetXY($x_inicial, $y_fila + $altura_fija_fila);
}

// ========== PIE DE PÁGINA CORREGIDO ==========
$y_actual = $pdf->GetY();

// Asegurarnos de que haya espacio para el pie de página
if ($y_actual < ($page_height - 15)) {
    $pdf->SetY($page_height - 15);
}

// CORRECCIÓN: Verificar y limpiar la dirección para evitar repeticiones
$direccion_institucion = trim($institucion['direccion']);

$texto_remover = "adscrito a la Zona Educativa del Estado Distrito Capital";
$direccion_institucion = preg_replace("/,\s*" . preg_quote($texto_remover, '/') . "$/i", "", $direccion_institucion);
$direccion_institucion = preg_replace("/\s*" . preg_quote($texto_remover, '/') . "$/i", "", $direccion_institucion);

$direccion_completa = $direccion_institucion . ', adscrito a la Zona Educativa del Estado Distrito Capital';

$pdf->SetFont('helvetica', 'I', 9);
$pdf->SetTextColor(128, 128, 128);
$pdf->Cell(0, 5, $direccion_completa, 0, 1, 'C');

// Fecha de generación
$pdf->SetFont('helvetica', 'I', 8);
$pdf->Cell(0, 5, 'Generado el ' . date('d/m/Y H:i:s'), 0, 0, 'R');

// ========== SALIDA DEL PDF ==========
$nombre_archivo = 'Horario_' . str_replace(' ', '_', $horario['grado']) . '_' . $horario['nombre_seccion'] . '.pdf';

if ($preview) {
    $pdf->Output($nombre_archivo, 'I');
} else {
    $pdf->Output($nombre_archivo, 'D');
}
?>