<?php
/**
 * Script para corregir bloques con hora_fin = '00:00:00' o NULL
 * Infiere la hora_fin correcta basándose en la hora_inicio y los horarios estándar
 */

include('../../app/config.php');
header('Content-Type: text/html; charset=utf-8');

echo "<h2>Corrección de Horas Inválidas en horario_detalle</h2>";
echo "<pre>";

// Mapeo de horarios estándar (hora_inicio => hora_fin)
$horarios_estandar = [
    '07:50:00' => '08:30:00',
    '08:30:00' => '09:10:00',
    '09:10:00' => '09:50:00',
    '10:10:00' => '10:50:00',
    '10:50:00' => '11:30:00',
    '11:30:00' => '12:10:00',
    '07:50' => '08:30:00',
    '08:30' => '09:10:00',
    '09:10' => '09:50:00',
    '10:10' => '10:50:00',
    '10:50' => '11:30:00',
    '11:30' => '12:10:00',
];

// Buscar bloques con hora_fin inválida
$sql = "SELECT id_detalle, id_horario, dia_semana, hora_inicio, hora_fin, id_materia, id_profesor
        FROM horario_detalle
        WHERE hora_fin IS NULL 
           OR hora_fin = '00:00:00'
           OR hora_fin = ''
        ORDER BY id_detalle";
$stmt = $pdo->query($sql);
$bloques_invalidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Total de bloques con hora_fin inválida: " . count($bloques_invalidos) . "\n\n";

if (count($bloques_invalidos) == 0) {
    echo "No hay bloques con hora_fin inválida. Todo está correcto.\n";
    echo "</pre>";
    exit;
}

$corregidos = 0;
$no_corregidos = 0;

foreach ($bloques_invalidos as $bloque) {
    $id_detalle = $bloque['id_detalle'];
    $hora_inicio = $bloque['hora_inicio'];
    $hora_fin_correcta = null;
    
    // Normalizar hora_inicio
    if (strlen($hora_inicio) == 5) {
        $hora_inicio_normalizada = $hora_inicio . ':00';
    } else {
        $hora_inicio_normalizada = $hora_inicio;
    }
    
    // Buscar hora_fin en el mapeo
    if (isset($horarios_estandar[$hora_inicio_normalizada])) {
        $hora_fin_correcta = $horarios_estandar[$hora_inicio_normalizada];
    } elseif (isset($horarios_estandar[$hora_inicio])) {
        $hora_fin_correcta = $horarios_estandar[$hora_inicio];
    }
    
    if ($hora_fin_correcta) {
        // Actualizar el bloque
        $stmtUpdate = $pdo->prepare("UPDATE horario_detalle SET hora_fin = ? WHERE id_detalle = ?");
        $stmtUpdate->execute([$hora_fin_correcta, $id_detalle]);
        
        echo "✓ Corregido: Bloque ID $id_detalle, hora_inicio: $hora_inicio => hora_fin: $hora_fin_correcta\n";
        $corregidos++;
    } else {
        echo "✗ No se pudo corregir: Bloque ID $id_detalle, hora_inicio: $hora_inicio (no está en el mapeo estándar)\n";
        $no_corregidos++;
    }
}

echo "\n";
echo "Resumen:\n";
echo "  - Corregidos: $corregidos\n";
echo "  - No corregidos: $no_corregidos\n";

if ($corregidos > 0) {
    echo "\n¡Bloques corregidos exitosamente!\n";
    echo "Ahora puedes probar nuevamente la validación de conflictos.\n";
}

echo "</pre>";
?>

