<?php
require_once('../../app/config.php');

// Verificar si TCPDF está disponible
$tcpdf_path = __DIR__ . '/library/tcpdf.php';
if (!file_exists($tcpdf_path)) {
    die("Error: TCPDF library no encontrada. Por favor, instale TCPDF en: " . $tcpdf_path);
}
require_once($tcpdf_path);

// Obtener filtro por grado
$grado_filtro = isset($_GET['grado']) ? $_GET['grado'] : '0';

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
        'nombre_institucion' => 'U.E. ROBERTO MARTINEZ CENTENO',
        'direccion' => 'Parroquia Caricuao, Avenida Este 0, Caracas, Distrito Capital',
        'telefono' => '0212-433-1080',
        'correo' => 'admin@gmail.com',
        'logo' => null
    ];
}

// Obtener período escolar activo
$sql_gestion = "SELECT desde, hasta FROM gestiones WHERE estado = '1' LIMIT 1";
$query_gestion = $pdo->query($sql_gestion);
$gestion = $query_gestion->fetch(PDO::FETCH_ASSOC);

// Formatear el período escolar
if ($gestion) {
    $ano_desde = date('Y', strtotime($gestion['desde']));
    $ano_hasta = date('Y', strtotime($gestion['hasta']));
    $periodo_escolar = "$ano_desde-$ano_hasta";
} else {
    $ano_actual = date('Y');
    $periodo_escolar = "$ano_actual-" . ($ano_actual + 1);
}

// ===== CONSULTA: Obtener estudiantes únicos con recuperaciones tipo PENDIENTE =====
$sql = "
    SELECT DISTINCT
        r.id_estudiante,
        e.cedula,
        CONCAT(e.apellidos, ', ', e.nombres) as nombre_completo,
        e.genero,
        g.grado,
        s.nombre_seccion
    FROM recuperaciones r
    INNER JOIN estudiantes e ON e.id_estudiante = r.id_estudiante
    INNER JOIN secciones s ON s.id_seccion = r.id_seccion
    INNER JOIN grados g ON g.id_grado = s.id_grado
    WHERE r.tipo = 'PENDIENTE'
";

if ($grado_filtro != '0') {
    $sql .= " AND g.grado = :grado_filtro";
}

$sql .= " ORDER BY e.apellidos, e.nombres, g.grado, s.nombre_seccion";

$stmt = $pdo->prepare($sql);
if ($grado_filtro != '0') {
    $stmt->bindParam(':grado_filtro', $grado_filtro);
}
$stmt->execute();
$estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($estudiantes)) {
    die("No hay estudiantes con materias en recuperación para el filtro seleccionado.");
}

// Función para redondear y formatear notas
function formatearNota($nota) {
    if (!is_numeric($nota)) return $nota;
    
    $nota_num = floatval($nota);
    $decimal = $nota_num - floor($nota_num);
    
    // Redondear
    if ($decimal >= 0.5) {
        $nota_num = ceil($nota_num);
    } else {
        $nota_num = floor($nota_num);
    }
    
    // Formatear con cero a la izquierda
    return ($nota_num >= 0 && $nota_num <= 9) ? "0" . $nota_num : $nota_num;
}

// Crear nuevo documento PDF
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

// Configurar documento
$pdf->SetCreator('Sistema de Gestión Escolar');
$pdf->SetAuthor('Sistema de Gestión Escolar');
$pdf->SetTitle('Reporte de Recuperaciones ' . $periodo_escolar);
$pdf->SetSubject('Reporte PDF');

// Configurar márgenes más pequeños para más espacio
$pdf->SetMargins(10, 15, 10); // Reducido de 15,25,15
$pdf->SetHeaderMargin(0);
$pdf->SetFooterMargin(0); // Sin margen de pie
$pdf->SetAutoPageBreak(TRUE, 10); // Reducido de 15
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false); // SIN PIE DE PÁGINA

// Alturas predefinidas para optimizar espacio
$altura_encabezado_estudiante = 10;
$altura_info_estudiante = 8;
$altura_titulo_materia = 8;
$altura_fila_tabla = 6; // Reducido de 8
$altura_resumen_materia = 8; // Reducido de 10

// Contadores
$contador_global = 0;
$total_estudiantes = count($estudiantes);
$y_inicio_pagina = 0;
$espacio_disponible = 0;

// Para cada estudiante
for ($i = 0; $i < $total_estudiantes; $i++) {
    $estudiante = $estudiantes[$i];
    $contador_global++;
    
    // ========== OBTENER MATERIAS DEL ESTUDIANTE ==========
    $sql_materias = "
        SELECT DISTINCT
            r.id_materia,
            m.nombre_materia,
            r.id_seccion
        FROM recuperaciones r
        INNER JOIN materias m ON m.id_materia = r.id_materia
        WHERE r.id_estudiante = :id_estudiante
        AND r.tipo = 'PENDIENTE'
    ";
    
    $stmt_materias = $pdo->prepare($sql_materias);
    $stmt_materias->bindParam(':id_estudiante', $estudiante['id_estudiante']);
    $stmt_materias->execute();
    $materias = $stmt_materias->fetchAll(PDO::FETCH_ASSOC);
    
    // Calcular espacio aproximado que ocupará este estudiante
    $espacio_necesario = $altura_encabezado_estudiante + (2 * $altura_info_estudiante) + 5;
    
    foreach ($materias as $materia) {
        $espacio_necesario += $altura_titulo_materia + $altura_info_estudiante + 
                             (4 * $altura_fila_tabla) + $altura_resumen_materia + 10;
    }
    
    // Si es el primer estudiante o no hay espacio suficiente, nueva página
    if ($contador_global == 1 || $pdf->GetY() + $espacio_necesario > 275) {
        $pdf->AddPage();
        $y_inicio_pagina = $pdf->GetY();
        
        // ========== ENCABEZADO DE PÁGINA ==========
        // Logo
        $logo_existe = false;
        $logo_path = null;
        $posibles_logos = [
            '../../logo/logo.png', '../../logos/logo.png', '../../login/logos/logo.png',
            '../../public/img/logo.png', '../../public/img/uploads/logo.png'
        ];

        $base_dir = __DIR__;
        foreach ($posibles_logos as $ruta_logo) {
            $ruta_completa = $base_dir . '/' . str_replace('../', '', $ruta_logo);
            if (file_exists($ruta_completa)) {
                $logo_path = $ruta_completa;
                $logo_existe = true;
                break;
            }
        }

        // Posicionar logo más pequeño
        $logo_x = 10;
        $logo_y = 10;
        $logo_width = 15;
        $logo_height = 15;

        if ($logo_existe && $logo_path) {
            $pdf->Image($logo_path, $logo_x, $logo_y, $logo_width, $logo_height);
        }

        // Título del reporte
        $pdf->SetFont('helvetica', 'B', 14); // Reducido de 16
        $pdf->SetXY($logo_x + $logo_width + 5, $logo_y - 3);
        $pdf->Cell(0, 8, 'REPORTE DE RECUPERACIONES', 0, 1, 'C');

        // Información de la institución
        $pdf->SetFont('helvetica', 'B', 10); // Reducido de 12
        $nombre_corregido = str_replace('U.E.N', 'U.E.', $institucion['nombre_institucion']);
        $pdf->SetX($logo_x + $logo_width + 5);
        $pdf->Cell(0, 5, strtoupper($nombre_corregido), 0, 1, 'C');
        
        // Período escolar
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetX($logo_x + $logo_width + 5);
        $pdf->Cell(0, 5, 'PERÍODO: ' . $periodo_escolar, 0, 1, 'C');

        // Línea separadora
        $pdf->SetDrawColor(200, 200, 200);
        $pdf->Line(10, $pdf->GetY() + 2, 200, $pdf->GetY() + 2);
        $pdf->Ln(5);
    }
    
    // ========== INFORMACIÓN DEL ESTUDIANTE ==========
    
    // Encabezado del estudiante
    $pdf->SetFont('helvetica', 'B', 10); // Reducido de 12
    $pdf->SetFillColor(240, 245, 250);
    $pdf->Cell(0, $altura_encabezado_estudiante, "ESTUDIANTE #" . str_pad($contador_global, 2, '0', STR_PAD_LEFT), 0, 1, 'L', true);
    $pdf->Ln(2);
    
    // Información básica compacta
    $pdf->SetFont('helvetica', '', 8); // Reducido de 10
    
    // Fila 1 compacta
    $pdf->Cell(20, $altura_info_estudiante, 'CÉDULA:', 0, 0, 'L');
    $pdf->Cell(30, $altura_info_estudiante, $estudiante['cedula'] ?: 'N/A', 0, 0, 'L');
    $pdf->Cell(15, $altura_info_estudiante, 'NOMBRE:', 0, 0, 'L');
    $pdf->Cell(0, $altura_info_estudiante, $estudiante['nombre_completo'], 0, 1, 'L');
    
    // Fila 2 compacta
    $pdf->Cell(20, $altura_info_estudiante, 'GÉNERO:', 0, 0, 'L');
    $pdf->Cell(10, $altura_info_estudiante, ($estudiante['genero'] == 'masculino' ? 'M' : 'F'), 0, 0, 'L');
    $pdf->Cell(15, $altura_info_estudiante, 'GRADO:', 0, 0, 'L');
    $pdf->Cell(20, $altura_info_estudiante, $estudiante['grado'], 0, 0, 'L');
    $pdf->Cell(20, $altura_info_estudiante, 'SECCIÓN:', 0, 0, 'L');
    $pdf->Cell(0, $altura_info_estudiante, $estudiante['nombre_seccion'], 0, 1, 'L');
    
    $pdf->Ln(2);
    
    // ========== MATERIAS EN RECUPERACIÓN ==========
    
    if (count($materias) > 0) {
        $pdf->SetFont('helvetica', 'B', 9); // Reducido de 11
        $pdf->Cell(0, 6, 'MATERIAS EN RECUPERACIÓN:', 0, 1, 'L');
        $pdf->Ln(1);
        
        // Para cada materia del estudiante
        foreach ($materias as $index_materia => $materia) {
            // Obtener intentos para esta materia
            $sql_intentos = "
                SELECT 
                    r.intento,
                    r.calificacion,
                    r.fecha_registro,
                    r.observaciones
                FROM recuperaciones r
                WHERE r.id_estudiante = :id_estudiante
                AND r.id_materia = :id_materia
                AND r.id_seccion = :id_seccion
                AND r.tipo = 'PENDIENTE'
                ORDER BY r.intento ASC
            ";
            
            $stmt_intentos = $pdo->prepare($sql_intentos);
            $stmt_intentos->bindParam(':id_estudiante', $estudiante['id_estudiante']);
            $stmt_intentos->bindParam(':id_materia', $materia['id_materia']);
            $stmt_intentos->bindParam(':id_seccion', $materia['id_seccion']);
            $stmt_intentos->execute();
            $intentos = $stmt_intentos->fetchAll(PDO::FETCH_ASSOC);
            
            // Organizar por momento
            $notas_momentos = [];
            foreach ($intentos as $intento) {
                $momento = $intento['intento'];
                if ($momento >= 1 && $momento <= 4) {
                    if (!isset($notas_momentos[$momento]) || 
                        floatval($intento['calificacion']) > floatval($notas_momentos[$momento]['calificacion'])) {
                        $notas_momentos[$momento] = $intento;
                    }
                }
            }
            
            // Completar momentos
            for ($momento = 1; $momento <= 4; $momento++) {
                if (!isset($notas_momentos[$momento])) {
                    $notas_momentos[$momento] = [
                        'calificacion' => '-',
                        'fecha_registro' => '-',
                        'observaciones' => ''
                    ];
                }
            }
            
            ksort($notas_momentos);
            
            // Obtener profesor
            $sql_profesor = "
                SELECT CONCAT(p.nombres, ' ', p.apellidos) as nombre_profesor
                FROM asignaciones_profesor ap
                INNER JOIN profesores p ON p.id_profesor = ap.id_profesor
                WHERE ap.id_materia = :id_materia
                AND ap.id_seccion = :id_seccion
                AND ap.estado = '1'
                LIMIT 1
            ";
            
            $stmt_profesor = $pdo->prepare($sql_profesor);
            $stmt_profesor->bindParam(':id_materia', $materia['id_materia']);
            $stmt_profesor->bindParam(':id_seccion', $materia['id_seccion']);
            $stmt_profesor->execute();
            $profesor = $stmt_profesor->fetch(PDO::FETCH_ASSOC);
            $nombre_profesor = $profesor ? $profesor['nombre_profesor'] : 'Sin asignar';
            
            // Calcular nota definitiva
            $nota_definitiva = '-';
            $aprobo_en_momento = null;
            
            foreach ($notas_momentos as $momento => $nota) {
                if (is_numeric($nota['calificacion']) && floatval($nota['calificacion']) >= 10) {
                    $aprobo_en_momento = $momento;
                    $nota_definitiva = formatearNota($nota['calificacion']);
                    break;
                }
            }
            
            if ($aprobo_en_momento === null) {
                $suma_notas = 0;
                $contador_notas = 0;
                
                foreach ($notas_momentos as $nota) {
                    if (is_numeric($nota['calificacion'])) {
                        $suma_notas += floatval($nota['calificacion']);
                        $contador_notas++;
                    }
                }
                
                if ($contador_notas > 0) {
                    $nota_definitiva = formatearNota($suma_notas / $contador_notas);
                }
            }
            
            // Estado final
            $estado_final = 'PENDIENTE';
            if ($aprobo_en_momento !== null) {
                $estado_final = 'APROBADO';
            } elseif ($nota_definitiva != '-' && is_numeric($nota_definitiva) && floatval($nota_definitiva) < 10) {
                $estado_final = 'REPROBADO';
            }
            
            // ========== DETALLE COMPACTO DE LA MATERIA ==========
            
            // Encabezado de materia
            $pdf->SetFont('helvetica', 'B', 8); // Reducido
            $pdf->SetFillColor(220, 235, 245);
            $pdf->Cell(0, $altura_titulo_materia, " " . $materia['nombre_materia'] . " [$estado_final]", 1, 1, 'L', true);
            
            // Profesor
            $pdf->SetFont('helvetica', '', 7); // Reducido
            $pdf->Cell(20, $altura_fila_tabla, 'Profesor:', 0, 0, 'L');
            $pdf->Cell(0, $altura_fila_tabla, $nombre_profesor, 0, 1, 'L');
            
            // Cabecera tabla compacta
            $pdf->SetFont('helvetica', 'B', 7);
            $pdf->SetFillColor(60, 120, 180);
            $pdf->SetTextColor(255, 255, 255);
            
            $pdf->Cell(15, $altura_fila_tabla, 'MOMENTO', 1, 0, 'C', true);
            $pdf->Cell(15, $altura_fila_tabla, 'NOTA', 1, 0, 'C', true);
            $pdf->Cell(20, $altura_fila_tabla, 'FECHA', 1, 0, 'C', true);
            $pdf->Cell(0, $altura_fila_tabla, 'OBSERVACIÓN', 1, 1, 'C', true);
            
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFillColor(255, 255, 255);
            
            // Datos de los 4 momentos
            $pdf->SetFont('helvetica', '', 7);
            
            for ($momento = 1; $momento <= 4; $momento++) {
                $nota = $notas_momentos[$momento];
                $calificacion_formateada = formatearNota($nota['calificacion']);
                
                // Color según nota
                if ($nota['calificacion'] == '-') {
                    $pdf->SetFillColor(250, 250, 250);
                } elseif (is_numeric($nota['calificacion'])) {
                    if (floatval($nota['calificacion']) >= 10) {
                        $pdf->SetFillColor(230, 255, 230);
                    } else {
                        $pdf->SetFillColor(255, 245, 230);
                    }
                }
                
                $pdf->Cell(15, $altura_fila_tabla, "M" . str_pad($momento, 2, '0', STR_PAD_LEFT), 1, 0, 'C', true);
                $pdf->Cell(15, $altura_fila_tabla, $calificacion_formateada, 1, 0, 'C', true);
                
                $fecha = $nota['fecha_registro'];
                if ($fecha != '-' && $fecha != '' && $fecha != null && $fecha != '0000-00-00 00:00:00') {
                    $fecha = date('d/m/y', strtotime($fecha)); // Formato corto
                }
                $pdf->Cell(20, $altura_fila_tabla, $fecha, 1, 0, 'C', true);
                
                $observacion = $nota['observaciones'] ?: '';
                if (empty($observacion)) {
                    if ($nota['calificacion'] == '-') {
                        $observacion = 'Sin registro';
                    } elseif (is_numeric($nota['calificacion'])) {
                        $observacion = floatval($nota['calificacion']) >= 10 ? 'Aprobado' : 'Reprobado';
                    }
                }
                
                // Truncar observación si es muy larga
                if (strlen($observacion) > 40) {
                    $observacion = substr($observacion, 0, 37) . '...';
                }
                
                $pdf->Cell(0, $altura_fila_tabla, $observacion, 1, 1, 'L', true);
                $pdf->SetFillColor(255, 255, 255);
            }
            
            // Resumen compacto
            $pdf->SetFont('helvetica', 'B', 8);
            
            // Color según estado
            if ($estado_final == 'APROBADO') {
                $color_final = [230, 255, 230];
                $estatus = 'APROBADO';
            } elseif ($estado_final == 'REPROBADO') {
                $color_final = [255, 245, 230];
                $estatus = 'REPROBADO';
            } else {
                $color_final = [240, 240, 240];
                $estatus = 'EN REVISIÓN';
            }
            
            $pdf->SetFillColor($color_final[0], $color_final[1], $color_final[2]);
            $pdf->Cell(30, $altura_resumen_materia, 'NOTA DEFINITIVA:', 1, 0, 'R', true);
            $pdf->SetFont('helvetica', 'B', 9);
            $pdf->Cell(15, $altura_resumen_materia, $nota_definitiva, 1, 0, 'C', true);
            
            $pdf->SetFont('helvetica', 'B', 8);
            $pdf->SetFillColor(240, 240, 240);
            $pdf->Cell(20, $altura_resumen_materia, 'ESTATUS:', 1, 0, 'R', true);
            
            $pdf->SetFillColor($color_final[0], $color_final[1], $color_final[2]);
            $pdf->Cell(0, $altura_resumen_materia, $estatus, 1, 1, 'C', true);
            
            // Espacio entre materias
            if ($index_materia < count($materias) - 1) {
                $pdf->Ln(3);
                $pdf->SetDrawColor(220, 220, 220);
                $pdf->SetLineWidth(0.2);
                $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
                $pdf->Ln(3);
            } else {
                $pdf->Ln(5);
            }
        }
    }
    
    // Línea separadora entre estudiantes (excepto el último)
    if ($i < $total_estudiantes - 1) {
        $pdf->SetDrawColor(180, 200, 220);
        $pdf->SetLineWidth(0.3);
        $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
        $pdf->Ln(8);
    }
    
    // Si estamos cerca del final de la página y aún quedan estudiantes, nueva página
    if ($pdf->GetY() > 250 && $i < $total_estudiantes - 1) {
        // No hacemos nada, el próximo ciclo creará nueva página automáticamente
    }
}

// Salida del PDF SIN PIE DE PÁGINA
$nombre_archivo = 'Reporte_Recuperaciones_' . $periodo_escolar . '_' . date('Ymd_His') . '.pdf';
$pdf->Output($nombre_archivo, 'I');
?>