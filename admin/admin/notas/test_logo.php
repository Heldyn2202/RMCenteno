<?php
// Script de prueba para verificar la ruta del logo
$base_dir = __DIR__;
echo "Directorio base: $base_dir<br>";

$rutas_a_probar = [
    '../../login/logos/logo.png',
    '../../logos/logo.png',
    '../../logo/logo.png',
    '../../public/img/logo.png'
];

foreach ($rutas_a_probar as $ruta) {
    $ruta_completa = $base_dir . '/' . $ruta;
    $existe = file_exists($ruta_completa);
    $realpath = realpath($ruta_completa);
    echo "Ruta: $ruta<br>";
    echo "Ruta completa: $ruta_completa<br>";
    echo "Existe: " . ($existe ? 'SÍ' : 'NO') . "<br>";
    if ($realpath) {
        echo "Realpath: $realpath<br>";
    }
    echo "<br>";
}
?>

