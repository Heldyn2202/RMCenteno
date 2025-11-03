<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

/**
 * Configura y envía un correo electrónico usando PHPMailer
 * 
 * @param string $destinatario Email del destinatario
 * @param string $asunto Asunto del correo
 * @param string $cuerpo Cuerpo del mensaje (HTML)
 * @param string $altCuerpo Cuerpo alternativo en texto plano
 * @return bool True si se envió correctamente, False en caso contrario
 */
function enviarEmail($destinatario, $asunto, $cuerpo, $altCuerpo = '') {
    $mail = new PHPMailer(true);
    
    try {
        // Configuración del servidor SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';  // Servidor SMTP de Gmail
        $mail->SMTPAuth = true;
        $mail->Username = 'tu_email@gmail.com';  // Tu dirección de Gmail
        $mail->Password = 'tu_contraseña_de_aplicacion';  // Contraseña de aplicación de Gmail
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;
        
        // Configuración de caracteres
        $mail->CharSet = 'UTF-8';
        
        // Remitente
        $mail->setFrom('tu_email@gmail.com', APP_NAME);
        $mail->addReplyTo('no-reply@' . $_SERVER['HTTP_HOST'], APP_NAME);
        
        // Destinatario
        $mail->addAddress($destinatario);
        
        // Contenido
        $mail->isHTML(true);
        $mail->Subject = $asunto;
        $mail->Body = $cuerpo;
        $mail->AltBody = !empty($altCuerpo) ? $altCuerpo : strip_tags($cuerpo);
        
        // Enviar correo
        return $mail->send();
    } catch (Exception $e) {
        error_log("Error al enviar correo: " . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Crea el template HTML para el correo de recuperación de contraseña
 * 
 * @param string $enlace_recuperacion Enlace para restablecer la contraseña
 * @param string $email Email del usuario
 * @return string HTML del correo
 */
function crearTemplateRecuperacion($enlace_recuperacion, $email) {
    $app_name = APP_NAME;
    $anio_actual = date('Y');
    
    return "
    <!DOCTYPE html>
    <html lang='es'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Recuperación de Contraseña</title>
        <style>
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                line-height: 1.6;
                color: #333;
                background-color: #f9f9f9;
                margin: 0;
                padding: 0;
            }
            .container {
                max-width: 600px;
                margin: 0 auto;
                background-color: #ffffff;
                border-radius: 10px;
                overflow: hidden;
                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            }
            .header {
                background: linear-gradient(135deg, #3c8dbc 0%, #2d5f7e 100%);
                padding: 20px;
                text-align: center;
            }
            .header h1 {
                color: white;
                margin: 0;
                font-size: 24px;
            }
            .content {
                padding: 30px;
            }
            .button {
                display: inline-block;
                padding: 12px 30px;
                background: linear-gradient(135deg, #3c8dbc 0%, #2d5f7e 100%);
                color: white;
                text-decoration: none;
                border-radius: 5px;
                font-weight: bold;
                margin: 20px 0;
            }
            .footer {
                background-color: #f1f1f1;
                padding: 20px;
                text-align: center;
                font-size: 12px;
                color: #666;
            }
            .logo {
                max-width: 180px;
                margin-bottom: 15px;
            }
            .warning {
                background-color: #fff3cd;
                border-left: 4px solid #ffc107;
                padding: 10px 15px;
                margin: 15px 0;
                border-radius: 4px;
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Recuperación de Contraseña</h1>
            </div>
            <div class='content'>
                <p>Hola,</p>
                <p>Hemos recibido una solicitud para restablecer la contraseña de tu cuenta en <strong>$app_name</strong>.</p>
                <p>Para continuar con el proceso, haz clic en el siguiente botón:</p>
                
                <p style='text-align: center;'>
                    <a href='$enlace_recuperacion' class='button'>Restablecer Contraseña</a>
                </p>
                
                <p>Si el botón no funciona, copia y pega el siguiente enlace en tu navegador:</p>
                <p style='word-break: break-all; color: #3c8dbc;'>$enlace_recuperacion</p>
                
                <div class='warning'>
                    <p><strong>Importante:</strong> Este enlace expirará en 1 hora por motivos de seguridad.</p>
                    <p>Si no solicitaste este restablecimiento, ignora este mensaje y tu contraseña permanecerá sin cambios.</p>
                </div>
                
                <p>Atentamente,<br>El equipo de <strong>$app_name</strong></p>
            </div>
            <div class='footer'>
                <p>&copy; $anio_actual $app_name. Todos los derechos reservados.</p>
                <p>Este es un mensaje automático, por favor no respondas a este correo.</p>
            </div>
        </div>
    </body>
    </html>
    ";
}
?>  


<?php
// ================================
// CONFIGURACIÓN GENERAL DEL SISTEMA
// ================================
date_default_timezone_set('America/Caracas');

// URL base del proyecto (ajusta si cambias carpeta)
define('APP_URL', 'http://localhost/heldyn/centeno/admin');

// Ruta local hacia las imágenes
define('APP_PATH', __DIR__ . '/../');
define('APP_LOGO', APP_PATH . 'logo/Banner.jpeg');

// ================================
// CONEXIÓN A BASE DE DATOS
// ================================
define('DB_HOST', 'localhost');
define('DB_NAME', 'siges');  // <-- cambia esto por el nombre real de tu BD
define('DB_USER', 'root');
define('DB_PASS', '');

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME,
        DB_USER,
        DB_PASS,
        [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("❌ Error de conexión a la base de datos: " . $e->getMessage());
}

// ================================
// CONFIGURACIÓN DE CORREO
// ================================
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require __DIR__ . '/vendor/autoload.php';

// ================================
// FUNCIÓN: ENVIAR CORREO DE RECUPERACIÓN
// ================================
function enviarCorreoRecuperacion($correoDestino, $nombreUsuario, $token) {
    $mail = new PHPMailer(true);

    try {
        // --- Configuración SMTP ---
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'heldyndiaz19@gmail.com'; // ⚠️ tu correo Gmail o institucional
        $mail->Password   = ''; // ⚠️ App Password de Gmail
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // --- Remitente y destinatario ---
        $mail->setFrom('tu_correo@gmail.com', 'U.E.N. Roberto Martínez Centeno');
        $mail->addAddress($correoDestino, $nombreUsuario);

        // --- Embebe el banner local dentro del correo ---
        $rutaBanner = APP_LOGO;
        if (file_exists($rutaBanner)) {
            $mail->addEmbeddedImage($rutaBanner, 'banner_cabecera', 'Banner.jpeg');
        }

        // --- Asunto ---
        $mail->isHTML(true);
        $mail->Subject = 'Recuperación de Contraseña - U.E.N. Roberto Martínez Centeno';

        // --- Enlace de recuperación ---
        $enlace = APP_URL . '/recuperar.php?token=' . urlencode($token);

        // --- Cuerpo HTML del correo ---
        $mail->Body = "
        <div style='font-family: Arial, sans-serif; background: linear-gradient(135deg, #2E7DAB, #1E5D88); padding: 20px; border-radius: 10px; color: white; text-align: center;'>
            <img src='cid:banner_cabecera' alt='Banner Institucional' style='max-width: 100%; border-radius: 8px; margin-bottom: 10px;'>
            <h2 style='margin-top: 10px;'>U.E.N ROBERTO MARTÍNEZ CENTENO</h2>
            <h3>Recuperación de Contraseña</h3>
        </div>
        <div style='padding: 20px; background-color: #f4f4f4; border-radius: 10px; color: #333; margin-top: 10px;'>
            <p>Estimado/a <b>$nombreUsuario</b>,</p>
            <p>Recibimos una solicitud para restablecer tu contraseña. Para continuar, haz clic en el siguiente botón:</p>
            <p style='text-align: center;'>
                <a href='$enlace' style='display: inline-block; background: #1E5D88; color: white; padding: 12px 20px; border-radius: 6px; text-decoration: none; font-weight: bold;'>Restablecer Contraseña</a>
            </p>
            <p style='margin-top: 15px;'>Si no solicitaste este cambio, puedes ignorar este mensaje.</p>
        </div>
        <p style='text-align:center; color:gray; font-size:12px; margin-top:10px;'>© 2025 U.E.N Roberto Martínez Centeno</p>
        ";

        // --- Enviar correo ---
        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log("Error enviando correo: {$mail->ErrorInfo}");
        return false;
    }
}
?>
