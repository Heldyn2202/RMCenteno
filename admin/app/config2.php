<?php
// app/config.php
define('APP_URL', 'http://localhost/DanielVQ/Portal_Escolar/admin');

// Database configuration
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'sige';

// Create connection
$conexion = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conexion->connect_error) {
    die("Connection failed: " . $conexion->connect_error);
}
?>