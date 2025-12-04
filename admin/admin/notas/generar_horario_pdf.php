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
        'nombre_institucion' => 'U.E.N ROBERTO MARTINEZ CENTENO',
        'direccion' => 'Parroquia Caricuao, Avenida Este 0, Caracas, Distrito Capital',
        'telefono' => '0212-433-1080',
        'correo' => 'admin@gmail.com',
        'logo' => null
    ];
}

// Obtener los datos del horario
$sql_horario = "SELECT h.*, g.grado, s.nombre_seccion, s.turno, 
                CONCAT(DATE_FORMAT(ges.desde, '%d/%m/%Y'), ' - ', DATE_FORMAT(ges.hasta, '%d/%m/%Y')) as gestion_periodo,
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
    $horario_organizado[$dia][$hora_inicio] = $detalle;
}

// Definir los bloques horarios (basados en los horarios del sistema)
$bloques_horarios = [
    '07:50:00' => '08:30:00',
    '08:30:00' => '09:10:00',
    '09:10:00' => '09:50:00',
    '10:10:00' => '10:50:00',
    '10:50:00' => '11:30:00',
    '11:30:00' => '12:10:00'
];

// Definir los días de la semana
$dias_semana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];

// Crear nuevo documento PDF en orientación horizontal
$pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);

// Configurar documento
$pdf->SetCreator('Sistema de Gestión Escolar');
$pdf->SetAuthor($institucion['nombre_institucion']);
$pdf->SetTitle('Horario Académico - ' . $horario['grado'] . ' ' . $horario['nombre_seccion']);
$pdf->SetSubject('Horario de Clases');

// Desactivar encabezado y pie de página automáticos
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Configurar márgenes
$pdf->SetMargins(15, 20, 15);
$pdf->SetHeaderMargin(0);
$pdf->SetFooterMargin(0);
$pdf->SetAutoPageBreak(true, 15);

// Añadir una página
$pdf->AddPage();

// ========== ENCABEZADO ==========
// Logo de la institución (intentar múltiples ubicaciones)
$logo_path = null;
$logo_existe = false;

// Intentar obtener logo de diferentes ubicaciones
$posibles_logos = [];

// 1. Desde la base de datos (campo logo)
if ($institucion['logo']) {
    $posibles_logos[] = '../../public/img/uploads/' . $institucion['logo'];
}

// 2. Rutas comunes donde puede estar el logo (incluyendo login/logos)
$posibles_logos[] = '../../logo/logo.png';
$posibles_logos[] = '../../logos/logo.png';
$posibles_logos[] = '../../login/logos/logo.png';
$posibles_logos[] = '../../public/img/logo.png';
$posibles_logos[] = '../../public/img/uploads/logo.png';
$posibles_logos[] = '../../logo/logo.jpg';
$posibles_logos[] = '../../logos/logo.jpg';
$posibles_logos[] = '../../login/logos/logo.jpg';
$posibles_logos[] = '../../logo/logo.jpeg';
$posibles_logos[] = '../../logos/logo.jpeg';
$posibles_logos[] = '../../login/logos/logo.jpeg';

// Función para verificar si un archivo es un logo válido (no un puntero Git LFS)
function es_logo_valido($ruta) {
    if (!file_exists($ruta) || !is_file($ruta) || !is_readable($ruta)) {
        return false;
    }
    
    $tamano = filesize($ruta);
    // Si el archivo es muy pequeño (menos de 500 bytes), verificar si es un puntero LFS
    if ($tamano < 500) {
        $contenido = @file_get_contents($ruta, false, null, 0, 200);
        if ($contenido && (stripos($contenido, 'git-lfs') !== false || stripos($contenido, 'version https://git-lfs') !== false)) {
            return false; // Es un puntero LFS, no el archivo real
        }
    }
    
    // Verificar que sea una imagen válida leyendo la firma del archivo
    $handle = @fopen($ruta, 'rb');
    if ($handle) {
        $header = @fread($handle, 8);
        @fclose($handle);
        // PNG: 89 50 4E 47 0D 0A 1A 0A
        // JPEG: FF D8 FF
        if ($header && (substr($header, 0, 4) === "\x89\x50\x4E\x47" || substr($header, 0, 3) === "\xFF\xD8\xFF")) {
            return true;
        }
    }
    
    // Si el archivo es grande (>500 bytes), asumir que es válido
    return $tamano > 500;
}

// Buscar el primer logo que exista y sea válido
$base_dir = __DIR__; // admin/admin/notas

foreach ($posibles_logos as $ruta_logo) {
    // Construir ruta completa desde el directorio actual
    $ruta_completa = $base_dir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $ruta_logo);
    $ruta_completa = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $ruta_completa);
    
    // Verificar con ruta directa
    if (es_logo_valido($ruta_completa)) {
        $logo_path = realpath($ruta_completa);
        $logo_existe = true;
        break;
    }
    
    // Intentar con realpath
    $ruta_real = realpath($ruta_completa);
    if ($ruta_real && es_logo_valido($ruta_real)) {
        $logo_path = $ruta_real;
        $logo_existe = true;
        break;
    }
}

// Si no se encontró, buscar en rutas absolutas desde la raíz
if (!$logo_existe) {
    $raiz_proyecto = dirname(dirname(dirname($base_dir))); // Subir hasta la raíz
    $rutas_absolutas = [
        $raiz_proyecto . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'login' . DIRECTORY_SEPARATOR . 'logos' . DIRECTORY_SEPARATOR . 'logo.png',
        $raiz_proyecto . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'login' . DIRECTORY_SEPARATOR . 'logos' . DIRECTORY_SEPARATOR . 'logo.jpg',
        $raiz_proyecto . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'logos' . DIRECTORY_SEPARATOR . 'logo.png',
        $raiz_proyecto . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'logo.png',
    ];
    
    foreach ($rutas_absolutas as $ruta_abs) {
        if (es_logo_valido($ruta_abs)) {
            $logo_path = realpath($ruta_abs);
            $logo_existe = true;
            break;
        }
    }
}

// Configurar posición inicial
$pdf->SetY(10);
$page_width = $pdf->getPageWidth();
$margin_right = 15; // Margen derecho
$margin_left = 15; // Margen izquierdo
$logo_width = 35; // Ancho del logo
$logo_height = 35; // Alto del logo
$logo_x = $page_width - $logo_width - $margin_right; // Posición X: esquina superior derecha

// Mostrar logo en la esquina superior derecha PRIMERO (antes del texto)
if ($logo_existe && $logo_path) {
    try {
        // Verificar una vez más que el logo es válido antes de cargarlo
        if (es_logo_valido($logo_path)) {
            // Intentar cargar la imagen en el PDF
            $pdf->Image($logo_path, $logo_x, 10, $logo_width, $logo_height, '', '', 'T', false, 300, '', false, false, 0, false, false, false);
        } else {
            // El logo no es válido (probablemente un puntero LFS)
            error_log("Logo no válido detectado. Ruta: $logo_path");
            error_log("Tamaño: " . (file_exists($logo_path) ? filesize($logo_path) . " bytes" : "archivo no existe"));
            $logo_existe = false;
        }
    } catch (Exception $e) {
        error_log("Error al cargar logo en PDF: " . $e->getMessage());
        error_log("Ruta intentada: " . $logo_path);
        $logo_existe = false;
    } catch (Error $e) {
        error_log("Error fatal al cargar logo: " . $e->getMessage());
        $logo_existe = false;
    }
} else {
    // Logo no encontrado - esto es normal si el usuario no ha subido el logo aún
    // No es necesario loguear esto como error, es una situación normal
}

// Información de la institución (centrada en la página)
$pdf->SetFont('helvetica', 'B', 14);
$pdf->SetY(12);
// Restaurar color de dibujo a negro (por si acaso)
$pdf->SetDrawColor(0, 0, 0);
// Centrar el texto usando Text en lugar de Cell para evitar cualquier línea
$texto_nombre = strtoupper($institucion['nombre_institucion']);
$ancho_texto = $pdf->GetStringWidth($texto_nombre);
$x_centrado = ($page_width - $ancho_texto) / 2;
$pdf->SetX($x_centrado);
$pdf->Text($x_centrado, $pdf->GetY() + 6, $texto_nombre);
$pdf->SetY($pdf->GetY() + 8);

// Dirección y contacto (centrados usando Text)
$pdf->SetFont('helvetica', '', 9);
$y_actual = $pdf->GetY();
$texto_direccion = $institucion['direccion'];
$ancho_direccion = $pdf->GetStringWidth($texto_direccion);
$x_direccion = ($page_width - $ancho_direccion) / 2;
$pdf->Text($x_direccion, $y_actual + 4, $texto_direccion);
$pdf->SetY($y_actual + 5);

if ($institucion['telefono']) {
    $y_actual = $pdf->GetY();
    $texto_telefono = 'Teléfono: ' . $institucion['telefono'];
    $ancho_telefono = $pdf->GetStringWidth($texto_telefono);
    $x_telefono = ($page_width - $ancho_telefono) / 2;
    $pdf->Text($x_telefono, $y_actual + 4, $texto_telefono);
    $pdf->SetY($y_actual + 5);
}

if ($institucion['correo']) {
    $y_actual = $pdf->GetY();
    $texto_correo = 'Email: ' . $institucion['correo'];
    $ancho_correo = $pdf->GetStringWidth($texto_correo);
    $x_correo = ($page_width - $ancho_correo) / 2;
    $pdf->Text($x_correo, $y_actual + 4, $texto_correo);
    $pdf->SetY($y_actual + 5);
}

// Ajustar posición Y para la línea separadora (después de toda la información)
$pdf->Ln(5); // Espacio antes de la línea separadora
$y_actual = $pdf->GetY();
$y_separador = max($y_actual, ($logo_existe ? 50 : 38));
$pdf->SetY($y_separador);

// Línea separadora (solo UNA línea, después de toda la información)
$pdf->SetLineWidth(0.5);
$pdf->SetDrawColor(0, 0, 0); // Asegurar color negro
$pdf->Line(15, $pdf->GetY(), $page_width - 15, $pdf->GetY());
$pdf->Ln(5);

// ========== TÍTULO PRINCIPAL ==========
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, 'HORARIO ACADÉMICO', 0, 1, 'C');
$pdf->Ln(3);

// ========== INFORMACIÓN DEL HORARIO ==========
$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell(0, 6, $horario['grado'] . ' - Sección ' . $horario['nombre_seccion'] . ' (' . $horario['turno'] . ')', 0, 1, 'C');

$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 5, 'Período Académico: ' . $horario['gestion_periodo'], 0, 1, 'C');
$pdf->Ln(5);

// ========== TABLA DEL HORARIO ==========
// Ancho de columnas
$ancho_hora = 35;
$ancho_dia = 48;
$alto_celda = 12;

// Colores
$color_header = [70, 130, 180]; // Azul acero
$color_borde = [200, 200, 200]; // Gris claro

// Cabecera de la tabla
$pdf->SetFillColor($color_header[0], $color_header[1], $color_header[2]);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('helvetica', 'B', 10);
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
$pdf->SetFont('helvetica', '', 9);

// Filas de horarios
foreach ($bloques_horarios as $hora_inicio => $hora_fin) {
    // Formatear hora (sin segundos)
    $hora_inicio_formato = substr($hora_inicio, 0, 5);
    $hora_fin_formato = substr($hora_fin, 0, 5);
    
    // Guardar posición inicial
    $x_inicial = 15;
    $y_fila = $pdf->GetY();
    
    // Calcular altura máxima necesaria para esta fila
    $altura_maxima = $alto_celda;
    $contenidos = [];
    
    foreach ($dias_semana as $dia) {
        if (isset($horario_organizado[$dia][$hora_inicio])) {
            $clase = $horario_organizado[$dia][$hora_inicio];
            $materia = $clase['nombre_materia'];
            $profesor = $clase['profesor'] ? '(' . $clase['profesor'] . ')' : '';
            $texto = $materia . "\n" . $profesor;
            $contenidos[$dia] = $texto;
            // Calcular altura necesaria con padding
            $altura_texto = $pdf->getStringHeight($ancho_dia - 6, $texto, false, true, '', 1);
            $altura_maxima = max($altura_maxima, $altura_texto + 6);
        } else {
            $contenidos[$dia] = '';
        }
    }
    
    // Asegurar altura mínima uniforme
    $altura_maxima = max($altura_maxima, $alto_celda);
    
    // Dibujar celda de hora
    $pdf->SetFillColor(245, 245, 245);
    $pdf->SetXY($x_inicial, $y_fila);
    $pdf->Cell($ancho_hora, $altura_maxima, $hora_inicio_formato . ' - ' . $hora_fin_formato, 1, 0, 'C', 1);
    
    // Dibujar celdas de días (todas con la misma altura y posición Y fija)
    $x_pos = $x_inicial + $ancho_hora;
    foreach ($dias_semana as $dia) {
        $pdf->SetFillColor(255, 255, 255);
        
        if (!empty($contenidos[$dia])) {
            // Dibujar borde de la celda primero
            $pdf->SetXY($x_pos, $y_fila);
            $pdf->Cell($ancho_dia, $altura_maxima, '', 1, 0, 'C', true);
            
            // Calcular posición Y para centrar el texto verticalmente
            $altura_texto = $pdf->getStringHeight($ancho_dia - 8, $contenidos[$dia], false, true, '', 1);
            $y_texto = $y_fila + (($altura_maxima - $altura_texto) / 2);
            
            // Escribir el texto completamente centrado (horizontal y vertical)
            $pdf->SetXY($x_pos + 4, $y_texto);
            // MultiCell con alineación centrada y ajuste de altura de línea
            $pdf->MultiCell($ancho_dia - 8, 5.5, $contenidos[$dia], 0, 'C', false);
            
            // Restaurar posición Y para mantener alineación
            $pdf->SetXY($x_pos, $y_fila);
        } else {
            // Celda vacía
            $pdf->SetXY($x_pos, $y_fila);
            $pdf->Cell($ancho_dia, $altura_maxima, '', 1, 0, 'C', true);
        }
        
        $x_pos += $ancho_dia;
    }
    
    // Mover a la siguiente fila (todas las celdas tienen la misma altura)
    $pdf->SetXY($x_inicial, $y_fila + $altura_maxima);
}

$pdf->Ln(5);

// ========== PIE DE PÁGINA ==========
$pdf->SetFont('helvetica', 'I', 8);
$pdf->SetTextColor(128, 128, 128);
$pdf->Cell(0, 5, 'Generado el ' . date('d/m/Y H:i:s'), 0, 0, 'R');

// ========== SALIDA DEL PDF ==========
$nombre_archivo = 'Horario_' . str_replace(' ', '_', $horario['grado']) . '_' . $horario['nombre_seccion'] . '.pdf';

// Si es preview, mostrar en el navegador; si no, descargar
if ($preview) {
    $pdf->Output($nombre_archivo, 'I'); // 'I' = Inline (previsualización)
} else {
    $pdf->Output($nombre_archivo, 'D'); // 'D' = Download (descarga)
}
?>
