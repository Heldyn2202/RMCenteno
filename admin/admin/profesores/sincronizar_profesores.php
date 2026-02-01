<?php
// sincronizar_profesores.php
include('../../app/config.php');

// Verificar que sea solo accesible desde el navegador, no desde fuera
if (php_sapi_name() === 'cli') {
    exit('Este script solo puede ejecutarse desde el navegador.');
}

// Solo permitir acceso a administradores
session_start();
if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 1) {
    header('Location: ../../login/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sincronizar Profesores</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
<div class="container mt-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h3 class="card-title">
                <i class="fas fa-sync-alt"></i> Sincronización de Profesores
            </h3>
        </div>
        <div class="card-body">
            <?php
            echo "<h4 class='mb-4'>Sincronizando datos de profesores...</h4>";
            
            // 1. Mostrar estado actual
            echo "<div class='alert alert-info mb-4'>";
            echo "<h5><i class='fas fa-info-circle'></i> Estado actual de sincronización:</h5>";
            
            $sql_estado = "SELECT 
                COUNT(*) as total_usuarios,
                SUM(CASE WHEN rol_id = 5 THEN 1 ELSE 0 END) as total_docentes,
                (SELECT COUNT(*) FROM profesores) as total_profesores,
                (SELECT COUNT(*) FROM profesores WHERE id_usuario IS NOT NULL) as profesores_con_usuario,
                (SELECT COUNT(*) FROM profesores WHERE id_usuario IS NULL) as profesores_sin_usuario
                FROM usuarios";
            
            $query_estado = $pdo->prepare($sql_estado);
            $query_estado->execute();
            $estado = $query_estado->fetch(PDO::FETCH_ASSOC);
            
            echo "<p>• Total usuarios: {$estado['total_usuarios']}</p>";
            echo "<p>• Usuarios docentes (rol 5): {$estado['total_docentes']}</p>";
            echo "<p>• Registros en tabla profesores: {$estado['total_profesores']}</p>";
            echo "<p>• Profesores con usuario asignado: {$estado['profesores_con_usuario']}</p>";
            echo "<p>• Profesores sin usuario asignado: {$estado['profesores_sin_usuario']}</p>";
            echo "</div>";
            
            // 2. Sincronizar id_usuario en profesores
            echo "<h5><i class='fas fa-user-check'></i> 1. Asignando usuarios a profesores...</h5>";
            
            $sql = "SELECT u.id_usuario, u.email FROM usuarios u WHERE u.rol_id = 5";
            $query = $pdo->prepare($sql);
            $query->execute();
            $usuarios = $query->fetchAll(PDO::FETCH_ASSOC);
            
            $actualizados = 0;
            foreach ($usuarios as $usuario) {
                // Buscar profesor por email
                $sql_profesor = "SELECT id_profesor, id_usuario FROM profesores WHERE email = :email";
                $query_profesor = $pdo->prepare($sql_profesor);
                $query_profesor->bindParam(':email', $usuario['email']);
                $query_profesor->execute();
                $profesor = $query_profesor->fetch(PDO::FETCH_ASSOC);
                
                if ($profesor) {
                    // Si el profesor no tiene id_usuario o es diferente
                    if (empty($profesor['id_usuario']) || $profesor['id_usuario'] != $usuario['id_usuario']) {
                        // Actualizar id_usuario en profesor
                        $sql_update = "UPDATE profesores SET id_usuario = :id_usuario WHERE id_profesor = :id_profesor";
                        $query_update = $pdo->prepare($sql_update);
                        $query_update->bindParam(':id_usuario', $usuario['id_usuario']);
                        $query_update->bindParam(':id_profesor', $profesor['id_profesor']);
                        
                        if ($query_update->execute()) {
                            echo "<div class='alert alert-success'>";
                            echo "✅ <strong>Actualizado:</strong> Profesor ID {$profesor['id_profesor']} → Usuario ID {$usuario['id_usuario']} ({$usuario['email']})";
                            echo "</div>";
                            $actualizados++;
                        }
                    } else {
                        echo "<div class='alert alert-secondary'>";
                        echo "✓ <strong>Ya sincronizado:</strong> Profesor ID {$profesor['id_profesor']} ya tiene usuario ID {$usuario['id_usuario']}";
                        echo "</div>";
                    }
                } else {
                    echo "<div class='alert alert-warning'>";
                    echo "⚠️ <strong>No encontrado:</strong> No hay profesor registrado para {$usuario['email']}";
                    echo "</div>";
                }
            }
            
            // 3. Sincronizar estados
            echo "<hr><h5><i class='fas fa-exchange-alt'></i> 2. Sincronizando estados...</h5>";
            
            $sql_profesores = "SELECT p.id_profesor, p.email, p.estado as estado_profesor, u.estado as estado_usuario 
                            FROM profesores p 
                            LEFT JOIN usuarios u ON p.email = u.email AND u.rol_id = 5";
            $query_profesores = $pdo->prepare($sql_profesores);
            $query_profesores->execute();
            $profesores = $query_profesores->fetchAll(PDO::FETCH_ASSOC);
            
            $estados_actualizados = 0;
            foreach ($profesores as $prof) {
                if ($prof['estado_profesor'] != $prof['estado_usuario'] && !is_null($prof['estado_usuario'])) {
                    // Actualizar usuario para que coincida con profesor
                    $sql_update_usuario = "UPDATE usuarios SET estado = :estado WHERE email = :email AND rol_id = 5";
                    $query_update_usuario = $pdo->prepare($sql_update_usuario);
                    $query_update_usuario->bindParam(':estado', $prof['estado_profesor']);
                    $query_update_usuario->bindParam(':email', $prof['email']);
                    
                    if ($query_update_usuario->execute()) {
                        echo "<div class='alert alert-info'>";
                        echo "🔄 <strong>Estado actualizado:</strong> {$prof['email']} ahora tiene estado {$prof['estado_profesor']} (antes {$prof['estado_usuario']})";
                        echo "</div>";
                        $estados_actualizados++;
                    }
                }
            }
            
            // 4. Mostrar resumen final
            echo "<hr><div class='alert alert-success'>";
            echo "<h4><i class='fas fa-check-circle'></i> ✅ Sincronización completada</h4>";
            echo "<p><strong>Resumen:</strong></p>";
            echo "<p>• Profesores actualizados con usuario: {$actualizados}</p>";
            echo "<p>• Estados sincronizados: {$estados_actualizados}</p>";
            echo "<p>• Total inconsistencias corregidas: " . ($actualizados + $estados_actualizados) . "</p>";
            echo "</div>";
            
            // 5. Mostrar estado final
            echo "<h5><i class='fas fa-chart-bar'></i> 3. Estado final después de sincronización:</h5>";
            
            $sql_final = "SELECT 
                p.id_profesor,
                p.nombres,
                p.apellidos,
                p.email as email_profesor,
                p.estado as estado_profesor,
                u.email as email_usuario,
                u.estado as estado_usuario,
                CASE 
                    WHEN u.id_usuario IS NULL THEN '❌ Sin usuario'
                    WHEN p.estado != u.estado THEN '⚠️ Estados diferentes'
                    WHEN p.estado = 0 THEN '❌ INACTIVO'
                    WHEN p.estado = 1 THEN '✅ ACTIVO'
                    ELSE '🔧 Otro estado'
                END as estado_sincronizacion
                FROM profesores p
                LEFT JOIN usuarios u ON p.email = u.email AND u.rol_id = 5
                ORDER BY estado_sincronizacion, p.apellidos";
            
            $query_final = $pdo->prepare($sql_final);
            $query_final->execute();
            $resultados = $query_final->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<div class='table-responsive'>";
            echo "<table class='table table-bordered table-hover'>";
            echo "<thead class='table-dark'>";
            echo "<tr>
                    <th>ID</th>
                    <th>Profesor</th>
                    <th>Email Profesor</th>
                    <th>Estado Profesor</th>
                    <th>Email Usuario</th>
                    <th>Estado Usuario</th>
                    <th>Sincronización</th>
                  </tr>";
            echo "</thead>";
            echo "<tbody>";
            
            foreach ($resultados as $row) {
                $estado_profesor = $row['estado_profesor'] == 1 ? 
                    "<span class='badge bg-success'>ACTIVO</span>" : 
                    "<span class='badge bg-danger'>INACTIVO</span>";
                
                $estado_usuario = $row['estado_usuario'] == 1 ? 
                    "<span class='badge bg-success'>ACTIVO</span>" : 
                    ($row['estado_usuario'] == 0 ? 
                    "<span class='badge bg-danger'>INACTIVO</span>" : 
                    "<span class='badge bg-warning'>NO EXISTE</span>");
                
                $estado_sync = $row['estado_sincronizacion'];
                $color = 'secondary';
                if (strpos($estado_sync, '✅') !== false) $color = 'success';
                if (strpos($estado_sync, '❌') !== false) $color = 'danger';
                if (strpos($estado_sync, '⚠️') !== false) $color = 'warning';
                
                echo "<tr>";
                echo "<td>{$row['id_profesor']}</td>";
                echo "<td>{$row['nombres']} {$row['apellidos']}</td>";
                echo "<td>{$row['email_profesor']}</td>";
                echo "<td>{$estado_profesor}</td>";
                echo "<td>{$row['email_usuario']}</td>";
                echo "<td>{$estado_usuario}</td>";
                echo "<td><span class='badge bg-{$color}'>{$estado_sync}</span></td>";
                echo "</tr>";
            }
            
            echo "</tbody>";
            echo "</table>";
            echo "</div>";
            
            ?>
            
            <hr>
            <div class="text-center">
                <a href="listar_profesores.php" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Volver a Listado de Profesores
                </a>
                <a href="sincronizar_profesores.php" class="btn btn-success" onclick="return confirm('¿Ejecutar sincronización nuevamente?')">
                    <i class="fas fa-sync-alt"></i> Ejecutar de nuevo
                </a>
            </div>
        </div>
        <div class="card-footer text-muted">
            <small>Última sincronización: <?= date('d/m/Y H:i:s'); ?></small>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>