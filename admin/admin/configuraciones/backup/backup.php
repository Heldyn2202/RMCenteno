<?php
include ('../../../app/config.php');

// Directorio donde se guardarán los backups
$backup_dir = 'backups/';
if (!is_dir($backup_dir)) {
    mkdir($backup_dir, 0777, true);
}

// Configuración del nombre del archivo de backup
$backup_file = $backup_dir . 'SIGE-' . date("Y-m-d-H-i-s") . '.sql';

// Obtener todas las tablas de la base de datos
$tables = array();
$query = $pdo->query('SHOW TABLES');
while ($row = $query->fetch(PDO::FETCH_NUM)) {
    $tables[] = $row[0];
}

// Iniciar el contenido del archivo SQL
$output = '';

// Recorrer cada tabla y obtener su estructura y datos
foreach ($tables as $table) {
    // Obtener la estructura de la tabla
    $query = $pdo->query("SHOW CREATE TABLE $table");
    $row = $query->fetch(PDO::FETCH_NUM);
    $output .= "\n\n" . $row[1] . ";\n\n";

    // Obtener los datos de la tabla
    $query = $pdo->query("SELECT * FROM $table");
    while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
        $output .= "INSERT INTO $table VALUES(";
        $values = array();
        foreach ($row as $value) {
            $values[] = "'" . addslashes($value) . "'";
        }
        $output .= implode(',', $values) . ");\n";
    }
}

// Guardar el contenido en un archivo SQL
file_put_contents($backup_file, $output);

// Redirigir al usuario a la página de listado de backups
header('Location: index.php');
exit;
?>