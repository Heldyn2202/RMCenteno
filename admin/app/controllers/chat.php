<?php
// app/controllers/chat.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config.php';

// Verificar autenticación
if (!isset($_SESSION['sesion_email'])) {
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('HTTP/1.1 401 Unauthorized');
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'No autenticado']);
        exit();
    } else {
        header('Location: ' . APP_URL . '/login');
        exit();
    }
}

// CONEXIÓN
try {
    $dsn = "mysql:dbname=" . BD . ";host=" . SERVIDOR . ";charset=utf8mb4";
    $pdo = new PDO($dsn, USUARIO, PASSWORD, array(
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ));
} catch (PDOException $e) {
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('HTTP/1.1 500 Internal Server Error');
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos: ' . $e->getMessage()]);
        exit();
    } else {
        die('Error de conexión a la base de datos: ' . $e->getMessage());
    }
}

// Obtener el ID del usuario actual
$email_sesion = $_SESSION['sesion_email'];
$user_query = $pdo->prepare("SELECT id_usuario FROM usuarios WHERE email = :email AND estado = '1'");
$user_query->bindParam(':email', $email_sesion, PDO::PARAM_STR);
$user_query->execute();
$user_data = $user_query->fetch(PDO::FETCH_ASSOC);

if (!$user_data) {
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('HTTP/1.1 401 Unauthorized');
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Usuario no válido']);
        exit();
    } else {
        header('Location: ' . APP_URL . '/login');
        exit();
    }
}

$current_user_id = $user_data['id_usuario'];

// Determinar la acción solicitada
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'get_unread_count':
        getUnreadCount($pdo, $current_user_id);
        break;
    case 'get_contacts':
        getContacts($pdo, $current_user_id);
        break;
    case 'get_messages':
        getMessages($pdo, $current_user_id);
        break;
    case 'check_new_messages':
        checkNewMessages($pdo, $current_user_id);
        break;
    case 'send_message':
        sendMessage($pdo, $current_user_id);
        break;
    case 'mark_as_read':
        markAsRead($pdo, $current_user_id);
        break;
    case 'delete_message':
        deleteMessage($pdo, $current_user_id);
        break;
    case 'edit_message':
        editMessage($pdo, $current_user_id);
        break;
    case 'add_reaction':
        addReaction($pdo, $current_user_id);
        break;
    case 'remove_reaction':
        removeReaction($pdo, $current_user_id);
        break;
    case 'get_reactions':
        getReactions($pdo, $current_user_id);
        break;
    default:
        showChatInterface($pdo, $current_user_id);
        break;
}

// ----- FUNCIONES DE LA API -----

function getUnreadCount($pdo, $current_user_id) {
    header('Content-Type: application/json');
    try {
        $query = $pdo->prepare("
            SELECT
                id_remitente,
                COUNT(*) as unread_count
            FROM chat_mensajes
            WHERE id_destinatario = :current_user_id
              AND leido = '0'
              AND estado = '1'
            GROUP BY id_remitente
        ");
        
        $query->bindParam(':current_user_id', $current_user_id, PDO::PARAM_INT);
        $query->execute();
        $results = $query->fetchAll(PDO::FETCH_ASSOC);

        $total_unread = array_sum(array_column($results, 'unread_count'));
        $unread_by_contact = [];
        foreach ($results as $row) {
            $unread_by_contact[$row['id_remitente']] = (int)$row['unread_count'];
        }
        
        echo json_encode([
            'success' => true,
            'total_unread' => $total_unread,
            'unread_by_contact' => $unread_by_contact
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'total_unread' => 0, 'unread_by_contact' => [], 'message' => $e->getMessage()]);
    }
    exit();
}

function getContacts($pdo, $current_user_id) {
    header('Content-Type: application/json');
    try {
        // Obtener contactos con información del último mensaje y mensajes no leídos
        $query = $pdo->prepare("
            SELECT 
                u.id_usuario, 
                u.email, 
                p.nombres, 
                p.apellidos,
                p.foto_perfil,
                (SELECT COUNT(*) FROM chat_mensajes 
                 WHERE id_remitente = u.id_usuario 
                 AND id_destinatario = :current_user_id 
                 AND leido = '0' AND estado = '1') as mensajes_no_leidos,
                (SELECT mensaje FROM chat_mensajes 
                 WHERE ((id_remitente = u.id_usuario AND id_destinatario = :current_user_id) 
                 OR (id_remitente = :current_user_id AND id_destinatario = u.id_usuario))
                 AND estado = '1' 
                 ORDER BY fecha_envio DESC LIMIT 1) as ultimo_mensaje,
                (SELECT fecha_envio FROM chat_mensajes 
                 WHERE ((id_remitente = u.id_usuario AND id_destinatario = :current_user_id) 
                 OR (id_remitente = :current_user_id AND id_destinatario = u.id_usuario))
                 AND estado = '1' 
                 ORDER BY fecha_envio DESC LIMIT 1) as ultima_fecha
            FROM usuarios u 
            LEFT JOIN personas p ON p.usuario_id = u.id_usuario 
            WHERE u.id_usuario != :current_user_id 
              AND u.estado = '1'
            ORDER BY ultima_fecha DESC, p.nombres, p.apellidos
        ");
        
        $query->bindParam(':current_user_id', $current_user_id, PDO::PARAM_INT);
        $query->execute();
        $contacts = $query->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'contacts' => $contacts
        ]);
        
    } catch (PDOException $e) {
        header('HTTP/1.1 500 Internal Server Error');
        echo json_encode([
            'success' => false,
            'message' => 'Error al obtener contactos: ' . $e->getMessage()
        ]);
    }
    exit();
}

function getMessages($pdo, $current_user_id) {
    header('Content-Type: application/json');

    if (!isset($_GET['contact_id'])) {
        echo json_encode(['success' => false, 'message' => 'ID de contacto no proporcionado']);
        exit();
    }
    
    $contact_id = (int)$_GET['contact_id'];
    
    try {
        // Marcar mensajes como leídos
        $update_query = $pdo->prepare("
            UPDATE chat_mensajes 
            SET leido = '1' 
            WHERE id_destinatario = :current_user_id 
              AND id_remitente = :contact_id 
              AND leido = '0'
              AND estado = '1'
        ");
        $update_query->bindParam(':current_user_id', $current_user_id, PDO::PARAM_INT);
        $update_query->bindParam(':contact_id', $contact_id, PDO::PARAM_INT);
        $update_query->execute();

        // Obtener mensajes con reacciones
        $select_query = $pdo->prepare("
            SELECT 
                m.id_mensaje,
                m.id_remitente,
                m.id_destinatario,
                m.mensaje,
                m.archivo,
                m.fecha_envio,
                m.leido,
                m.editado,
                m.fecha_edicion,
                m.reacciones,
                ur.email as remitente_email,
                pr.nombres as remitente_nombres,
                pr.apellidos as remitente_apellidos,
                pr.foto_perfil as remitente_foto,
                ud.email as destinatario_email,
                pd.nombres as destinatario_nombres,
                pd.apellidos as destinatario_apellidos,
                pd.foto_perfil as destinatario_foto
            FROM chat_mensajes m
            LEFT JOIN usuarios ur ON ur.id_usuario = m.id_remitente
            LEFT JOIN personas pr ON pr.usuario_id = ur.id_usuario
            LEFT JOIN usuarios ud ON ud.id_usuario = m.id_destinatario
            LEFT JOIN personas pd ON pd.usuario_id = ud.id_usuario
            WHERE ((m.id_remitente = :current_user_id AND m.id_destinatario = :contact_id)
                OR (m.id_remitente = :contact_id AND m.id_destinatario = :current_user_id))
                AND m.estado = '1'
            ORDER BY m.fecha_envio ASC
        ");
        
        $select_query->bindParam(':current_user_id', $current_user_id, PDO::PARAM_INT);
        $select_query->bindParam(':contact_id', $contact_id, PDO::PARAM_INT);
        $select_query->execute();
        $messages = $select_query->fetchAll(PDO::FETCH_ASSOC);

        // Procesar reacciones
        foreach ($messages as &$message) {
            if ($message['reacciones']) {
                $message['reacciones'] = json_decode($message['reacciones'], true);
            } else {
                $message['reacciones'] = [];
            }
        }

        echo json_encode([
            'success' => true,
            'messages' => $messages
        ]);
        
    } catch (PDOException $e) {
        header('HTTP/1.1 500 Internal Server Error');
        echo json_encode([
            'success' => false,
            'message' => 'Error al obtener mensajes: ' . $e->getMessage()
        ]);
    }
    exit();
}

function checkNewMessages($pdo, $current_user_id) {
    header('Content-Type: application/json');
    
    if (!isset($_GET['contact_id']) || !isset($_GET['last_id'])) {
        echo json_encode(['success' => false, 'message' => 'Parámetros incompletos']);
        exit();
    }
    
    $contact_id = (int)$_GET['contact_id'];
    $last_id = (int)$_GET['last_id'];
    
    try {
        $query = $pdo->prepare("
            SELECT 
                m.id_mensaje,
                m.id_remitente,
                m.id_destinatario,
                m.mensaje,
                m.archivo,
                m.fecha_envio,
                m.leido,
                m.editado,
                m.fecha_edicion,
                m.reacciones,
                ur.email as remitente_email,
                pr.nombres as remitente_nombres,
                pr.apellidos as remitente_apellidos,
                pr.foto_perfil as remitente_foto
            FROM chat_mensajes m
            LEFT JOIN usuarios ur ON ur.id_usuario = m.id_remitente
            LEFT JOIN personas pr ON pr.usuario_id = ur.id_usuario
            WHERE ((m.id_remitente = :current_user_id AND m.id_destinatario = :contact_id)
                OR (m.id_remitente = :contact_id AND m.id_destinatario = :current_user_id))
                AND m.id_mensaje > :last_id
                AND m.estado = '1'
            ORDER BY m.fecha_envio ASC
        ");
        
        $query->bindParam(':current_user_id', $current_user_id, PDO::PARAM_INT);
        $query->bindParam(':contact_id', $contact_id, PDO::PARAM_INT);
        $query->bindParam(':last_id', $last_id, PDO::PARAM_INT);
        $query->execute();
        $new_messages = $query->fetchAll(PDO::FETCH_ASSOC);

        // Procesar reacciones
        foreach ($new_messages as &$message) {
            if ($message['reacciones']) {
                $message['reacciones'] = json_decode($message['reacciones'], true);
            } else {
                $message['reacciones'] = [];
            }
        }
        
        $last_message_id = $last_id;
        if (!empty($new_messages)) {
            $last_message_id = max(array_column($new_messages, 'id_mensaje'));
        }
        
        echo json_encode([
            'success' => true,
            'new_messages' => $new_messages,
            'last_message_id' => $last_message_id
        ]);
        
    } catch (PDOException $e) {
        header('HTTP/1.1 500 Internal Server Error');
        echo json_encode([
            'success' => false,
            'new_messages' => [],
            'last_message_id' => $last_id,
            'message' => 'Error al verificar nuevos mensajes: ' . $e->getMessage()
        ]);
    }
    exit();
}

function sendMessage($pdo, $current_user_id) {
    header('Content-Type: application/json');

    if (!isset($_POST['destinatario_id'])) {
        echo json_encode(['success' => false, 'message' => 'Destinatario no proporcionado']);
        exit();
    }

    $destinatario_id = (int)$_POST['destinatario_id'];
    $mensaje = isset($_POST['mensaje']) ? trim($_POST['mensaje']) : '';
    $tieneArchivo = (isset($_FILES['file']) && isset($_FILES['file']['tmp_name']) && $_FILES['file']['error'] === UPLOAD_ERR_OK);

    if ($mensaje === '' && !$tieneArchivo) {
        echo json_encode(['success' => false, 'message' => 'Debes enviar un texto o adjuntar un archivo.']);
        exit();
    }

    try {
        $check_user = $pdo->prepare("SELECT id_usuario FROM usuarios WHERE id_usuario = :destinatario_id AND estado = '1'");
        $check_user->bindParam(':destinatario_id', $destinatario_id, PDO::PARAM_INT);
        $check_user->execute();
        if ($check_user->rowCount() === 0) {
            echo json_encode(['success' => false, 'message' => 'El usuario destinatario no existe o está inactivo']);
            exit();
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error validando destinatario: ' . $e->getMessage()]);
        exit();
    }

    $fileUrl = null;
    if ($tieneArchivo) {
        $uploadBase = realpath(__DIR__ . '/../') ?: (__DIR__ . '/../');
        $uploadDir = $uploadBase . '/uploads/';

        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0775, true);
        }
        
        $maxBytes = 16 * 1024 * 1024; 
        
        $allowedExt = ['jpg', 'jpeg', 'jfif', 'png', 'gif', 'webp', 'tiff', 'svg', 'ai', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'mp4', 'mov', 'webm', 'ogg', 'avi', 'mkv', 'txt'];
        
        $originalName = $_FILES['file']['name'];
        $tmpPath = $_FILES['file']['tmp_name'];
        $size = (int)$_FILES['file']['size'];

        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExt)) {
            echo json_encode(['success' => false, 'message' => 'Tipo de archivo no permitido.']);
            exit();
        }

        if ($size <= 0 || $size > $maxBytes) {
            echo json_encode(['success' => false, 'message' => 'El archivo excede el tamaño máximo permitido (16MB).']);
            exit();
        }

        $safeBase = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', pathinfo($originalName, PATHINFO_FILENAME));
        $uniqueName = $safeBase . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $destPath = $uploadDir . $uniqueName;

        if (!move_uploaded_file($tmpPath, $destPath)) {
            echo json_encode(['success' => false, 'message' => 'Error al mover el archivo al directorio de destino.']);
            exit();
        }

        $fileUrl = 'app/uploads/' . $uniqueName;
    }

    try {
        $query = $pdo->prepare("
            INSERT INTO chat_mensajes (id_remitente, id_destinatario, mensaje, archivo, fecha_envio, leido, estado, reacciones)
            VALUES (:remitente_id, :destinatario_id, :mensaje, :archivo, NOW(), '0', '1', '[]')
        ");
        
        $query->bindParam(':remitente_id', $current_user_id, PDO::PARAM_INT);
        $query->bindParam(':destinatario_id', $destinatario_id, PDO::PARAM_INT);
        $query->bindParam(':mensaje', $mensaje, PDO::PARAM_STR);
        $query->bindValue(':archivo', $fileUrl, PDO::PARAM_STR);

        if ($query->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Mensaje enviado correctamente',
                'message_id' => $pdo->lastInsertId(),
                'archivo' => $fileUrl
            ]);
        } else {
            $errorInfo = $query->errorInfo();
            echo json_encode([
                'success' => false,
                'message' => 'Error al ejecutar la inserción',
                'error_info' => $errorInfo
            ]);
        }
    } catch (PDOException $e) {
        error_log("Error en chat.php (sendMessage): " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error al enviar mensaje: ' . $e->getMessage()]);
    }
    exit();
}

function markAsRead($pdo, $current_user_id) {
    header('Content-Type: application/json');
    if (!isset($_POST['contact_id'])) {
        echo json_encode(['success' => false, 'message' => 'ID de contacto no proporcionado']);
        exit();
    }
    
    $contact_id = (int)$_POST['contact_id'];
    
    try {
        $query = $pdo->prepare("
            UPDATE chat_mensajes 
            SET leido = '1' 
            WHERE id_destinatario = :current_user_id 
              AND id_remitente = :contact_id 
              AND leido = '0'
              AND estado = '1'
        ");
        
        $query->bindParam(':current_user_id', $current_user_id, PDO::PARAM_INT);
        $query->bindParam(':contact_id', $contact_id, PDO::PARAM_INT);
        $query->execute();
        
        echo json_encode(['success' => true, 'message' => 'Mensajes marcados como leídos']);
    } catch (PDOException $e) {
        header('HTTP/1.1 500 Internal Server Error');
        echo json_encode(['success' => false, 'message' => 'Error al marcar mensajes como leídos: ' . $e->getMessage()]);
    }
    exit();
}

function deleteMessage($pdo, $current_user_id) {
    header('Content-Type: application/json');
    
    if (!isset($_POST['message_id'])) {
        echo json_encode(['success' => false, 'message' => 'ID de mensaje no proporcionado']);
        exit();
    }
    
    $message_id = (int)$_POST['message_id'];
    $delete_for_all = isset($_POST['delete_for_all']) && $_POST['delete_for_all'] === 'true';
    
    try {
        if ($delete_for_all) {
            // Eliminar para todos (solo el remitente puede hacer esto)
            $query = $pdo->prepare("
                UPDATE chat_mensajes 
                SET estado = '0' 
                WHERE id_mensaje = :message_id 
                AND id_remitente = :current_user_id
            ");
        } else {
            // Eliminar solo para el usuario actual (eliminación suave)
            $query = $pdo->prepare("
                UPDATE chat_mensajes 
                SET estado = '0' 
                WHERE id_mensaje = :message_id 
                AND (id_remitente = :current_user_id OR id_destinatario = :current_user_id)
            ");
        }
        
        $query->bindParam(':message_id', $message_id, PDO::PARAM_INT);
        $query->bindParam(':current_user_id', $current_user_id, PDO::PARAM_INT);
        $query->execute();
        
        if ($query->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Mensaje eliminado correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se pudo eliminar el mensaje']);
        }
    } catch (PDOException $e) {
        header('HTTP/1.1 500 Internal Server Error');
        echo json_encode(['success' => false, 'message' => 'Error al eliminar mensaje: ' . $e->getMessage()]);
    }
    exit();
}

function editMessage($pdo, $current_user_id) {
    header('Content-Type: application/json');
    
    if (!isset($_POST['message_id']) || !isset($_POST['new_message'])) {
        echo json_encode(['success' => false, 'message' => 'Parámetros incompletos']);
        exit();
    }
    
    $message_id = (int)$_POST['message_id'];
    $new_message = trim($_POST['new_message']);
    
    if (empty($new_message)) {
        echo json_encode(['success' => false, 'message' => 'El mensaje no puede estar vacío']);
        exit();
    }
    
    try {
        // Verificar que el mensaje pertenece al usuario
        $check_query = $pdo->prepare("
            SELECT id_mensaje FROM chat_mensajes 
            WHERE id_mensaje = :message_id 
            AND id_remitente = :current_user_id
            AND estado = '1'
        ");
        $check_query->bindParam(':message_id', $message_id, PDO::PARAM_INT);
        $check_query->bindParam(':current_user_id', $current_user_id, PDO::PARAM_INT);
        $check_query->execute();
        
        if ($check_query->rowCount() === 0) {
            echo json_encode(['success' => false, 'message' => 'No tienes permisos para editar este mensaje']);
            exit();
        }

        // Actualizar mensaje de forma discreta
        $query = $pdo->prepare("
            UPDATE chat_mensajes 
            SET mensaje = :new_message, editado = '1', fecha_edicion = NOW()
            WHERE id_mensaje = :message_id 
            AND id_remitente = :current_user_id
            AND estado = '1'
        ");
        
        $query->bindParam(':new_message', $new_message, PDO::PARAM_STR);
        $query->bindParam(':message_id', $message_id, PDO::PARAM_INT);
        $query->bindParam(':current_user_id', $current_user_id, PDO::PARAM_INT);
        $query->execute();
        
        if ($query->rowCount() > 0) {
            echo json_encode([
                'success' => true, 
                'message' => 'Mensaje editado correctamente',
                'editado' => '1',
                'fecha_edicion' => date('Y-m-d H:i:s')
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se pudo editar el mensaje']);
        }
    } catch (PDOException $e) {
        header('HTTP/1.1 500 Internal Server Error');
        echo json_encode(['success' => false, 'message' => 'Error al editar mensaje: ' . $e->getMessage()]);
    }
    exit();
}

// NUEVAS FUNCIONES PARA REACCIONES

function addReaction($pdo, $current_user_id) {
    header('Content-Type: application/json');
    
    if (!isset($_POST['message_id']) || !isset($_POST['reaction_type'])) {
        echo json_encode(['success' => false, 'message' => 'Parámetros incompletos']);
        exit();
    }
    
    $message_id = (int)$_POST['message_id'];
    $reaction_type = $_POST['reaction_type'];
    $allowed_reactions = ['like', 'love', 'haha', 'wow', 'sad', 'angry'];
    
    if (!in_array($reaction_type, $allowed_reactions)) {
        echo json_encode(['success' => false, 'message' => 'Tipo de reacción no válido']);
        exit();
    }
    
    try {
        // Primero obtener las reacciones actuales
        $get_query = $pdo->prepare("SELECT reacciones FROM chat_mensajes WHERE id_mensaje = :message_id AND estado = '1'");
        $get_query->bindParam(':message_id', $message_id, PDO::PARAM_INT);
        $get_query->execute();
        
        if ($get_query->rowCount() === 0) {
            echo json_encode(['success' => false, 'message' => 'Mensaje no encontrado']);
            exit();
        }
        
        $message_data = $get_query->fetch(PDO::FETCH_ASSOC);
        $reacciones = $message_data['reacciones'] ? json_decode($message_data['reacciones'], true) : [];
        
        // Remover reacción anterior del usuario si existe
        $reacciones = array_filter($reacciones, function($reaccion) use ($current_user_id) {
            return $reaccion['user_id'] != $current_user_id;
        });
        
        // Agregar nueva reacción
        $reacciones[] = [
            'user_id' => $current_user_id,
            'reaction' => $reaction_type,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        // Actualizar en la base de datos
        $update_query = $pdo->prepare("UPDATE chat_mensajes SET reacciones = :reacciones WHERE id_mensaje = :message_id");
        $update_query->bindParam(':reacciones', json_encode($reacciones), PDO::PARAM_STR);
        $update_query->bindParam(':message_id', $message_id, PDO::PARAM_INT);
        $update_query->execute();
        
        echo json_encode([
            'success' => true,
            'message' => 'Reacción agregada',
            'reacciones' => $reacciones
        ]);
        
    } catch (PDOException $e) {
        header('HTTP/1.1 500 Internal Server Error');
        echo json_encode(['success' => false, 'message' => 'Error al agregar reacción: ' . $e->getMessage()]);
    }
    exit();
}

function removeReaction($pdo, $current_user_id) {
    header('Content-Type: application/json');
    
    if (!isset($_POST['message_id'])) {
        echo json_encode(['success' => false, 'message' => 'ID de mensaje no proporcionado']);
        exit();
    }
    
    $message_id = (int)$_POST['message_id'];
    
    try {
        // Obtener las reacciones actuales
        $get_query = $pdo->prepare("SELECT reacciones FROM chat_mensajes WHERE id_mensaje = :message_id AND estado = '1'");
        $get_query->bindParam(':message_id', $message_id, PDO::PARAM_INT);
        $get_query->execute();
        
        if ($get_query->rowCount() === 0) {
            echo json_encode(['success' => false, 'message' => 'Mensaje no encontrado']);
            exit();
        }
        
        $message_data = $get_query->fetch(PDO::FETCH_ASSOC);
        $reacciones = $message_data['reacciones'] ? json_decode($message_data['reacciones'], true) : [];
        
        // Filtrar reacción del usuario actual
        $nuevas_reacciones = array_filter($reacciones, function($reaccion) use ($current_user_id) {
            return $reaccion['user_id'] != $current_user_id;
        });
        
        // Actualizar en la base de datos
        $update_query = $pdo->prepare("UPDATE chat_mensajes SET reacciones = :reacciones WHERE id_mensaje = :message_id");
        $update_query->bindParam(':reacciones', json_encode(array_values($nuevas_reacciones)), PDO::PARAM_STR);
        $update_query->bindParam(':message_id', $message_id, PDO::PARAM_INT);
        $update_query->execute();
        
        echo json_encode([
            'success' => true,
            'message' => 'Reacción eliminada',
            'reacciones' => array_values($nuevas_reacciones)
        ]);
        
    } catch (PDOException $e) {
        header('HTTP/1.1 500 Internal Server Error');
        echo json_encode(['success' => false, 'message' => 'Error al eliminar reacción: ' . $e->getMessage()]);
    }
    exit();
}

function getReactions($pdo, $current_user_id) {
    header('Content-Type: application/json');
    
    if (!isset($_GET['message_id'])) {
        echo json_encode(['success' => false, 'message' => 'ID de mensaje no proporcionado']);
        exit();
    }
    
    $message_id = (int)$_GET['message_id'];
    
    try {
        $query = $pdo->prepare("
            SELECT m.reacciones, 
                   u.id_usuario, 
                   p.nombres, 
                   p.apellidos,
                   p.foto_perfil
            FROM chat_mensajes m
            LEFT JOIN usuarios u ON u.id_usuario = m.id_remitente
            LEFT JOIN personas p ON p.usuario_id = u.id_usuario
            WHERE m.id_mensaje = :message_id
        ");
        
        $query->bindParam(':message_id', $message_id, PDO::PARAM_INT);
        $query->execute();
        
        if ($query->rowCount() === 0) {
            echo json_encode(['success' => false, 'message' => 'Mensaje no encontrado']);
            exit();
        }
        
        $message_data = $query->fetch(PDO::FETCH_ASSOC);
        $reacciones = $message_data['reacciones'] ? json_decode($message_data['reacciones'], true) : [];
        
        echo json_encode([
            'success' => true,
            'reacciones' => $reacciones
        ]);
        
    } catch (PDOException $e) {
        header('HTTP/1.1 500 Internal Server Error');
        echo json_encode(['success' => false, 'message' => 'Error al obtener reacciones: ' . $e->getMessage()]);
    }
    exit();
}

function showChatInterface($pdo, $current_user_id) {
    $chat_view_path = __DIR__ . '/chat_interface.php';
    
    if (file_exists($chat_view_path)) {
        $email_sesion = $_SESSION['sesion_email'];
        $user_query = $pdo->prepare("SELECT u.id_usuario, u.email, p.nombres, p.apellidos, p.foto_perfil
                                     FROM usuarios u 
                                     LEFT JOIN personas p ON p.usuario_id = u.id_usuario 
                                     WHERE u.email = :email AND u.estado = '1'");
        $user_query->bindParam(':email', $email_sesion, PDO::PARAM_STR);
        $user_query->execute();
        $user_data = $user_query->fetch(PDO::FETCH_ASSOC);
        
        $nombres_sesion_usuario = $user_data['nombres'] ?? 'Usuario';
        $apellidos_sesion_usuario = $user_data['apellidos'] ?? 'Sistema';
        $foto_perfil_usuario = $user_data['foto_perfil'] ?? 'default-avatar.jpg';
        
        include_once $chat_view_path;
    } else {
        echo '<!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Chat - ' . APP_NAME . '</title>
            <link rel="stylesheet" href="' . APP_URL . '/public/dist/css/adminlte.min.css">
        </head>
        <body>
            <div class="container mt-5">
                <div class="alert alert-warning">
                    <h4><i class="fas fa-exclamation-triangle"></i> Vista de chat no encontrada</h4>
                    <p>El archivo chat_interface.php no existe en la ruta: ' . $chat_view_path . '</p>
                    <p>Contacta al administrador del sistema.</p>
                    <a href="' . APP_URL . '/admin" class="btn btn-primary">Volver al inicio</a>
                </div>
            </div>
        </body>
        </html>';
    }
}