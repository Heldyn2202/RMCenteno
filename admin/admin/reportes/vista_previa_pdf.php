<?php
include('../../app/config.php');
require_once('library/tcpdf.php');

// Obtener parámetro
$id_seccion = isset($_GET['id_seccion']) ? (int)$_GET['id_seccion'] : 0;

if (!$id_seccion) {
    $_SESSION['mensaje'] = "Sección no especificada";
    $_SESSION['mensaje_tipo'] = "error";
    header('Location: listados_estudiantes.php');
    exit();
}

try {
    // Obtener periodo activo
    $sql_gestion = "SELECT * FROM gestiones WHERE estado = 1 ORDER BY desde DESC LIMIT 1";
    $query_gestion = $pdo->prepare($sql_gestion);
    $query_gestion->execute();
    $gestion = $query_gestion->fetch(PDO::FETCH_ASSOC);

    if (!$gestion) {
        throw new Exception("No hay un período académico activo configurado");
    }

    // Obtener datos de la sección
    $sql_seccion = "SELECT s.id_seccion, s.nombre_seccion, s.turno, 
                   g.grado, g.trayecto, g.trimestre
                   FROM secciones s
                   JOIN grados g ON s.id_grado = g.id_grado
                   WHERE s.id_seccion = :id_seccion";
    $query_seccion = $pdo->prepare($sql_seccion);
    $query_seccion->bindParam(':id_seccion', $id_seccion, PDO::PARAM_INT);
    $query_seccion->execute();
    $seccion = $query_seccion->fetch(PDO::FETCH_ASSOC);

    if (!$seccion) {
        throw new Exception("La sección seleccionada no existe");
    }

    // Obtener estudiantes
    $sql_estudiantes = "SELECT e.nombres, e.apellidos, e.cedula, e.genero
                       FROM estudiantes e
                       JOIN inscripciones i ON e.id_estudiante = i.id_estudiante
                       WHERE i.id_seccion = :id_seccion AND i.estado = 1
                       ORDER BY e.apellidos, e.nombres";
    $query_estudiantes = $pdo->prepare($sql_estudiantes);
    $query_estudiantes->bindParam(':id_seccion', $id_seccion, PDO::PARAM_INT);
    $query_estudiantes->execute();
    $estudiantes = $query_estudiantes->fetchAll(PDO::FETCH_ASSOC);

    // Contar estudiantes por género
    $total_masculino = 0;
    $total_femenino = 0;
    
    foreach ($estudiantes as $estudiante) {
        if (strtolower($estudiante['genero']) == 'masculino') {
            $total_masculino++;
        } else {
            $total_femenino++;
        }
    }

    // Crear nuevo documento PDF
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // Configurar documento
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Sistema Académico');
    $pdf->SetTitle('Listado de Estudiantes - '.$seccion['grado'].' - '.$seccion['nombre_seccion']);
    $pdf->SetSubject('Listado de Estudiantes');
    $pdf->SetKeywords('TCPDF, PDF, listado, estudiantes');

    // Eliminar encabezado y pie de página predeterminados
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);

    // Configurar márgenes
    $pdf->SetMargins(15, 30, 15);
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

    // Añadir página
    $pdf->AddPage();

    // Agregar logos (ajusta las rutas según tu estructura)
    $pdf->Image('logos/logo.png', 15, 10, 20, 0, 'PNG', '', '', true, 300, '', false, false, 0, false, false, false);
    $pdf->Image('logos/ministerio.jpg', 150, 10, 40, 0, 'JPG', '', '', true, 300, '', false, false, 0, false, false, false);

    // Título del documento
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, 'U.E.N ROBERTO MARTINEZ CENTENO', 0, 1, 'C');
    $pdf->SetFont('helvetica', '', 14);
    $pdf->Cell(0, 10, 'Periodo Académico: ' . date('Y', strtotime($gestion['desde'])), 0, 1, 'C');
    $pdf->Ln(5);

    // Título de la sección
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 10, 'Listado de Estudiantes - '.$seccion['grado'].' - Sección '.$seccion['nombre_seccion'], 0, 1, 'C');
    $pdf->Ln(5);

    // Crear tabla de estudiantes
    if (!empty($estudiantes)) {
        // Cabecera de la tabla
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetFillColor(0, 64, 128);
        $pdf->SetTextColor(255);
        $pdf->Cell(10, 7, 'N°', 1, 0, 'C', 1);
        $pdf->Cell(30, 7, 'CÉDULA', 1, 0, 'C', 1);
        $pdf->Cell(120, 7, 'APELLIDOS Y NOMBRES', 1, 0, 'C', 1);
        $pdf->Cell(30, 7, 'GÉNERO', 1, 1, 'C', 1);

        // Contenido de la tabla
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetTextColor(0);
        $pdf->SetFillColor(255);
        
        foreach ($estudiantes as $index => $estudiante) {
            $pdf->Cell(10, 6, $index + 1, 1, 0, 'C');
            $pdf->Cell(30, 6, $estudiante['cedula'], 1, 0, 'C');
            $pdf->Cell(120, 6, $estudiante['apellidos'].' '.$estudiante['nombres'], 1, 0, 'L');
            $pdf->Cell(30, 6, ucfirst($estudiante['genero']), 1, 1, 'C');
        }

        // Totales
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(160, 7, 'TOTAL ESTUDIANTES', 1, 0, 'R');
        $pdf->Cell(30, 7, count($estudiantes), 1, 1, 'C');
        
        $pdf->Cell(160, 7, 'TOTAL MASCULINO', 1, 0, 'R');
        $pdf->Cell(30, 7, $total_masculino, 1, 1, 'C');
        
        $pdf->Cell(160, 7, 'TOTAL FEMENINO', 1, 0, 'R');
        $pdf->Cell(30, 7, $total_femenino, 1, 1, 'C');
    } else {
        $pdf->SetFont('helvetica', 'I', 12);
        $pdf->Cell(0, 10, 'No hay estudiantes inscritos en esta sección', 0, 1, 'C');
    }

    // Firma
    $pdf->Ln(15);
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, '_________________________________________', 0, 1, 'C');
    $pdf->Cell(0, 10, 'Dirección - Subdirección', 0, 1, 'C');

    // Salida del PDF
    $pdf->Output('Listado_Estudiantes_'.$seccion['grado'].'_'.$seccion['nombre_seccion'].'.pdf', 'I');

} catch (PDOException $e) {
    $_SESSION['mensaje'] = "Error de base de datos: " . $e->getMessage();
    $_SESSION['mensaje_tipo'] = "error";
    header('Location: listados_estudiantes.php');
    exit();
} catch (Exception $e) {
    $_SESSION['mensaje'] = $e->getMessage();
    $_SESSION['mensaje_tipo'] = "error";
    header('Location: listados_estudiantes.php');
    exit();
}