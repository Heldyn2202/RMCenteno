<?php

session_start();
require_once('../../app/config.php');
require_once('../../app/tcpdf/tcpdf.php');

// Evitar que se envíe salida antes del PDF
ob_clean();

class PDF extends TCPDF {
    public function Header() {
        $image_file = '../../assets/img/logo.png';
        if (file_exists($image_file)) {
            $this->Image($image_file, 10, 10, 25, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        }
        
        $this->SetFont('helvetica', 'B', 14);
        $this->Cell(0, 15, 'REPORTE DE ESTUDIANTES CON MATERIAS PENDIENTES', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(5);
        
        $this->SetFont('helvetica', 'I', 9);
        $this->Cell(0, 0, 'Generado: ' . date('d/m/Y H:i:s'), 0, false, 'R', 0, '', 0, false, 'M', 'M');
        $this->Ln(8);
        
        $this->Line(10, 30, 280, 30);
        $this->Ln(5);
    }
    
    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Página ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

// Verificar si hay filtro por año/grado
$grado_filter = isset($_GET['grado']) ? intval($_GET['grado']) : 0;


$sql = "
    SELECT DISTINCT
        e.id_estudiante,
        e.cedula,
        e.nombres,
        e.apellidos,
        e.genero as sexo,
        s.nombre_seccion,
        s.id_seccion,  -- AÑADIDO: Este campo faltaba
        s.id_grado as grado,
        m.id_materia,
        m.nombre_materia,
        m.abreviatura,
        mp.fecha_registro as fecha_pendiente,
        g.grado as nombre_grado  -- Cambiado de 'nombre' a 'grado'
    FROM materias_pendientes mp
    INNER JOIN estudiantes e ON e.id_estudiante = mp.id_estudiante
    INNER JOIN materias m ON m.id_materia = mp.id_materia
    INNER JOIN secciones s ON s.id_seccion = mp.id_seccion
    INNER JOIN grados g ON g.id_grado = s.id_grado
    WHERE mp.estado = 'pendiente'
    " . ($grado_filter > 0 ? " AND s.id_grado = :grado" : "") . "
    ORDER BY 
        s.id_grado ASC,
        s.nombre_seccion ASC,
        e.apellidos ASC,
        e.nombres ASC
";

$stmt = $pdo->prepare($sql);
if ($grado_filter > 0) {
    $stmt->bindParam(':grado', $grado_filter, PDO::PARAM_INT);
}
$stmt->execute();
$estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Agrupar por grado
$estudiantes_por_grado = [];
foreach ($estudiantes as $est) {
    $grado = $est['grado'];
    if (!isset($estudiantes_por_grado[$grado])) {
        $estudiantes_por_grado[$grado] = [];
    }
    $estudiantes_por_grado[$grado][] = $est;
}

// Crear PDF
$pdf = new PDF('L', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetCreator('Sistema Escolar');
$pdf->SetAuthor('Admin');
$pdf->SetTitle('Reporte Materias Pendientes');
$pdf->SetMargins(10, 35, 10);
$pdf->SetHeaderMargin(10);
$pdf->SetFooterMargin(10);
$pdf->SetAutoPageBreak(TRUE, 15);
$pdf->AddPage();

// Función para obtener notas por momento
function obtenerNotasPorMomento($pdo, $id_estudiante, $id_materia) {
    $sql_notas = "
        SELECT 
            l.nombre_lapso,
            l.id_lapso,
            n.calificacion,
            n.fecha_registro as fecha_nota
        FROM notas_estudiantes n
        INNER JOIN lapsos l ON l.id_lapso = n.id_lapso
        WHERE n.id_estudiante = :id_estudiante 
          AND n.id_materia = :id_materia
        ORDER BY l.id_lapso ASC
    ";
    
    $stmt = $pdo->prepare($sql_notas);
    $stmt->execute([
        ':id_estudiante' => $id_estudiante,
        ':id_materia' => $id_materia
    ]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Función para calcular nota definitiva
function calcularNotaDefinitiva($notas) {
    if (empty($notas)) return 0;
    
    $suma = 0;
    $contador = 0;
    foreach ($notas as $nota) {
        $suma += floatval($nota['calificacion']);
        $contador++;
    }
    
    return $contador > 0 ? round($suma / $contador, 2) : 0;
}

// Función para obtener profesor de la materia
function obtenerProfesorMateria($pdo, $id_materia, $id_seccion) {
    // Verificar si existe tabla asignaciones_materias
    $sql_check = "SHOW TABLES LIKE 'asignaciones_materias'";
    $stmt_check = $pdo->query($sql_check);
    
    if ($stmt_check->rowCount() > 0) {
        $sql_profesor = "
            SELECT CONCAT(p.nombres, ' ', p.apellidos) as profesor
            FROM asignaciones_materias a
            INNER JOIN profesores p ON p.id_profesor = a.id_profesor
            WHERE a.id_materia = :id_materia 
              AND a.id_seccion = :id_seccion
            LIMIT 1
        ";
        
        $stmt = $pdo->prepare($sql_profesor);
        $stmt->execute([
            ':id_materia' => $id_materia,
            ':id_seccion' => $id_seccion
        ]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ? $result['profesor'] : 'Sin asignar';
    } else {
        // Si no existe, buscar en otra tabla o devolver por defecto
        $sql_check2 = "SHOW TABLES LIKE 'asignaciones'";
        $stmt_check2 = $pdo->query($sql_check2);
        
        if ($stmt_check2->rowCount() > 0) {
            $sql_profesor2 = "
                SELECT CONCAT(p.nombres, ' ', p.apellidos) as profesor
                FROM asignaciones a
                INNER JOIN profesores p ON p.id_profesor = a.id_profesor
                WHERE a.id_materia = :id_materia 
                  AND a.id_seccion = :id_seccion
                LIMIT 1
            ";
            
            $stmt = $pdo->prepare($sql_profesor2);
            $stmt->execute([
                ':id_materia' => $id_materia,
                ':id_seccion' => $id_seccion
            ]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result ? $result['profesor'] : 'Sin asignar';
        }
        
        return 'Sin asignar';
    }
}

// Función para obtener nombre del grado
function obtenerNombreGrado($pdo, $id_grado) {
    $sql_grado = "SELECT grado FROM grados WHERE id_grado = :id_grado LIMIT 1";
    $stmt = $pdo->prepare($sql_grado);
    $stmt->execute([':id_grado' => $id_grado]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $result ? $result['grado'] : "Grado $id_grado";
}

$numero = 1;

// Recorrer por grados
foreach ($estudiantes_por_grado as $grado_id => $estudiantes_del_grado) {
    // Obtener nombre del grado
    $nombre_grado = obtenerNombreGrado($pdo, $grado_id);
    
    // Título del grado
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetFillColor(200, 220, 255);
    $pdf->Cell(0, 8, "GRADO: $nombre_grado", 0, 1, 'L', true);
    $pdf->Ln(3);
    
    // Recorrer estudiantes de este grado
    foreach ($estudiantes_del_grado as $est) {
        // Verificar si necesitamos nueva página
        if ($pdf->GetY() > 180) {
            $pdf->AddPage();
            
            // Volver a poner el título del grado en nueva página
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->SetFillColor(200, 220, 255);
            $pdf->Cell(0, 8, "GRADO: $nombre_grado (cont.)", 0, 1, 'L', true);
            $pdf->Ln(3);
        }
        
        // Cabecera de estudiante - Primera fila: Datos básicos
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetFillColor(240, 240, 240);
        
        // Encabezados
        $pdf->Cell(8, 8, '#', 1, 0, 'C', true);
        $pdf->Cell(22, 8, 'CÉDULA', 1, 0, 'C', true);
        $pdf->Cell(45, 8, 'APELLIDOS Y NOMBRES', 1, 0, 'C', true);
        $pdf->Cell(12, 8, 'SEXO', 1, 0, 'C', true);
        $pdf->Cell(15, 8, 'GRADO', 1, 0, 'C', true);
        $pdf->Cell(20, 8, 'SECCIÓN', 1, 0, 'C', true);
        $pdf->Cell(50, 8, 'MATERIA PENDIENTE', 1, 1, 'C', true);
        
        // Datos del estudiante
        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->Cell(8, 8, $numero, 1, 0, 'C');
        $pdf->Cell(22, 8, $est['cedula'], 1, 0, 'C');
        $pdf->Cell(45, 8, substr($est['apellidos'] . ', ' . $est['nombres'], 0, 30), 1, 0, 'L');
        $pdf->Cell(12, 8, substr($est['sexo'], 0, 1), 1, 0, 'C');
        $pdf->Cell(15, 8, $grado_id, 1, 0, 'C');
        $pdf->Cell(20, 8, $est['nombre_seccion'], 1, 0, 'C');
        $pdf->Cell(50, 8, substr($est['nombre_materia'], 0, 30), 1, 1, 'L');
        
        // Segunda fila: Título de notas
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetFillColor(220, 240, 220);
        $pdf->Cell(0, 8, 'NOTAS', 1, 1, 'C', true);
        
        // Obtener notas del estudiante para esta materia
        $notas = obtenerNotasPorMomento($pdo, $est['id_estudiante'], $est['id_materia']);
        $nota_definitiva = calcularNotaDefinitiva($notas);
        
        // Obtener profesor usando id_seccion que ahora está disponible
        $profesor = isset($est['id_seccion']) ? 
            obtenerProfesorMateria($pdo, $est['id_materia'], $est['id_seccion']) : 
            'Sin asignar';
        
        // Tercera fila: Cabecera de momentos
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->SetFillColor(245, 245, 245);
        
        // I Momento
        $pdf->Cell(20, 8, 'I MOMENTO', 1, 0, 'C', true);
        $pdf->Cell(18, 8, 'FECHA', 1, 0, 'C', true);
        $pdf->Cell(12, 8, 'NOTA', 1, 0, 'C', true);
        
        // II Momento
        $pdf->Cell(20, 8, 'II MOMENTO', 1, 0, 'C', true);
        $pdf->Cell(18, 8, 'FECHA', 1, 0, 'C', true);
        $pdf->Cell(12, 8, 'NOTA', 1, 0, 'C', true);
        
        // III Momento
        $pdf->Cell(20, 8, 'III MOMENTO', 1, 0, 'C', true);
        $pdf->Cell(18, 8, 'FECHA', 1, 0, 'C', true);
        $pdf->Cell(12, 8, 'NOTA', 1, 0, 'C', true);
        
        // Nota definitiva y Profesor
        $pdf->Cell(25, 8, 'NOTA DEFINITIVA', 1, 0, 'C', true);
        $pdf->Cell(40, 8, 'PROFESOR', 1, 1, 'C', true);
        
        // Datos de las notas
        $pdf->SetFont('helvetica', '', 8);
        
        // Inicializar array de notas por momento
        $notas_por_momento = [];
        foreach ($notas as $nota) {
            $notas_por_momento[$nota['id_lapso']] = $nota;
        }
        
        // I Momento (asumiendo id_lapso = 1)
        $nota_i = isset($notas_por_momento[1]) ? $notas_por_momento[1] : null;
        $pdf->Cell(20, 8, '', 1, 0, 'C');
        $pdf->Cell(18, 8, $nota_i ? date('d/m/Y', strtotime($nota_i['fecha_nota'])) : '-', 1, 0, 'C');
        $pdf->Cell(12, 8, $nota_i ? number_format($nota_i['calificacion'], 1) : '-', 1, 0, 'C');
        
        // II Momento (asumiendo id_lapso = 2)
        $nota_ii = isset($notas_por_momento[2]) ? $notas_por_momento[2] : null;
        $pdf->Cell(20, 8, '', 1, 0, 'C');
        $pdf->Cell(18, 8, $nota_ii ? date('d/m/Y', strtotime($nota_ii['fecha_nota'])) : '-', 1, 0, 'C');
        $pdf->Cell(12, 8, $nota_ii ? number_format($nota_ii['calificacion'], 1) : '-', 1, 0, 'C');
        
        // III Momento (asumiendo id_lapso = 3)
        $nota_iii = isset($notas_por_momento[3]) ? $notas_por_momento[3] : null;
        $pdf->Cell(20, 8, '', 1, 0, 'C');
        $pdf->Cell(18, 8, $nota_iii ? date('d/m/Y', strtotime($nota_iii['fecha_nota'])) : '-', 1, 0, 'C');
        $pdf->Cell(12, 8, $nota_iii ? number_format($nota_iii['calificacion'], 1) : '-', 1, 0, 'C');
        
        // Nota definitiva y Profesor
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell(25, 8, number_format($nota_definitiva, 1), 1, 0, 'C', true);
        $pdf->SetFont('helvetica', '', 8);
        $pdf->Cell(40, 8, substr($profesor, 0, 25), 1, 1, 'L');
        
        // Fecha de registro como materia pendiente
        $pdf->SetFont('helvetica', 'I', 7);
        $pdf->Cell(0, 6, 'Registrado como pendiente: ' . date('d/m/Y', strtotime($est['fecha_pendiente'])), 0, 1, 'R');
        
        $pdf->Ln(3); // Espacio entre estudiantes
        $numero++;
    }
    
    // Salto de página después de cada grado
    $pdf->AddPage();
}

// Si no hay datos
if (empty($estudiantes)) {
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(0, 10, 'No hay estudiantes con materias pendientes para el filtro seleccionado.', 0, 1, 'C');
}

// Limpiar buffer de salida y enviar PDF
ob_end_clean();
$pdf->Output('reporte_materias_pendientes_' . date('Ymd_His') . '.pdf', 'I');
exit;
?>