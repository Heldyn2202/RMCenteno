<?php
session_start();

// Redirigir si no hay sesión activa
if (!isset($_SESSION['id_usuario'])) {
    header("Location: " . APP_URL . "/login/login.php");
    exit();
}

// Verificar tiempo de sesión (opcional - 8 horas)
if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time'] > 28800)) {
    session_destroy();
    header("Location: " . APP_URL . "/login/login.php?error=Sesión expirada");
    exit();
}

// Verificar que el usuario aún esté activo
include('../config.php');
$sql = "SELECT estado FROM usuarios WHERE id_usuario = :id_usuario";
$query = $pdo->prepare($sql);
$query->bindParam(':id_usuario', $_SESSION['id_usuario']);
$query->execute();
$usuario = $query->fetch(PDO::FETCH_ASSOC);

if (!$usuario || $usuario['estado'] == 0) {
    // Usuario inhabilitado
    session_destroy();
    header("Location: " . APP_URL . "/login/login.php?error=Su cuenta ha sido inhabilitada");
    exit();
}

// Si es docente, verificar que también esté activo en profesores
if (isset($_SESSION['es_docente']) && $_SESSION['es_docente'] && isset($_SESSION['id_profesor'])) {
    $sql_profesor = "SELECT estado FROM profesores WHERE id_profesor = :id_profesor";
    $query_profesor = $pdo->prepare($sql_profesor);
    $query_profesor->bindParam(':id_profesor', $_SESSION['id_profesor']);
    $query_profesor->execute();
    $profesor = $query_profesor->fetch(PDO::FETCH_ASSOC);
    
    if (!$profesor || $profesor['estado'] == 0) {
        // Profesor inhabilitado
        session_destroy();
        header("Location: " . APP_URL . "/login/login.php?error=Su perfil de profesor ha sido inhabilitado");
        exit();
    }
}
?>