<?php
session_start();
ob_start();

// 1. Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

// 2. Definir permisos por rol (ID rol => [archivos permitidos])
$permisos = [
    1 => ['*'], // Admin: acceso total
    5 => [      // Docente (ID 5)
        'notas_estudiantes.php',
        'editar_notas.php',
        'gestionar_horarios.php'
    ]
];

// 3. Obtener archivo actual
$archivo_actual = basename($_SERVER['PHP_SELF']);

// 4. Verificar permisos
$rol_usuario = $_SESSION['rol_id'];
$tiene_acceso = in_array($archivo_actual, $permisos[$rol_usuario]) || 
                in_array('*', $permisos[$rol_usuario]);

if (!$tiene_acceso) {
    include('../../layout/mensajes.php');
    $_SESSION['mensaje'] = "Acceso denegado: No tienes permisos";
    $_SESSION['icono'] = "error";
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}
?>