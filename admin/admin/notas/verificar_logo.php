<?php
// Script para verificar la ruta del logo
$base_dir = __DIR__; // admin/admin/notas
echo "Directorio base: $base_dir<br><br>";

// Rutas a probar
$rutas = [
    '../../login/logos/logo.png',
    '../../logos/logo.png',
    '../../logo/logo.png',
    '../../public/img/logo.png'
];

echo "<h3>Buscando logo...</h3>";

foreach ($rutas as $ruta) {
    $ruta_completa = $base_dir . '\\' . str_replace('/', '\\', $ruta);
    $existe = file_exists($ruta_completa);
    $tamano = $existe ? filesize($ruta_completa) : 0;
    $legible = $existe ? is_readable($ruta_completa) : false;
    
    echo "<strong>Ruta:</strong> $ruta<br>";
    echo "<strong>Ruta completa:</strong> $ruta_completa<br>";
    echo "<strong>Existe:</strong> " . ($existe ? 'SÍ' : 'NO') . "<br>";
    if ($existe) {
        echo "<strong>Tamaño:</strong> $tamano bytes<br>";
        echo "<strong>Legible:</strong> " . ($legible ? 'SÍ' : 'NO') . "<br>";
        
        // Verificar si es un archivo LFS
        if ($tamano < 200) {
            $contenido = file_get_contents($ruta_completa);
            if (strpos($contenido, 'git-lfs') !== false) {
                echo "<strong>⚠️ ADVERTENCIA:</strong> Este archivo es un puntero de Git LFS. El archivo real no está disponible.<br>";
            }
        }
    }
    echo "<br>";
}

// También intentar desde la raíz
$raiz = dirname(dirname(dirname($base_dir)));
$ruta_absoluta = $raiz . '\\admin\\login\\logos\\logo.png';
echo "<h3>Ruta absoluta desde raíz:</h3>";
echo "<strong>Ruta:</strong> $ruta_absoluta<br>";
echo "<strong>Existe:</strong> " . (file_exists($ruta_absoluta) ? 'SÍ' : 'NO') . "<br>";
if (file_exists($ruta_absoluta)) {
    echo "<strong>Tamaño:</strong> " . filesize($ruta_absoluta) . " bytes<br>";
    echo "<strong>Legible:</strong> " . (is_readable($ruta_absoluta) ? 'SÍ' : 'NO') . "<br>";
}
?>

