<?php
// ============================================================================
// CONFIGURACIÓN GLOBAL - SISTEMA U.E.N ROBERTO MARTÍNEZ CENTENO
// ============================================================================

// Cargar PHPMailer automáticamente
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ============================================================================
// DETECCIÓN DE ENTORNO (Local vs Producción)
// ============================================================================
$isProduction = getenv('RENDER') || getenv('RAILWAY_ENVIRONMENT') ? true : false;

// ============================================================================
// CONFIGURACIÓN DE BASE DE DATOS
// ============================================================================
if ($isProduction) {
    // ⚙️ CONFIGURACIÓN PARA PRODUCCIÓN (Railway)
    define('SERVIDOR', 'mysql.railway.internal');
    define('USUARIO', 'root');
    define('PASSWORD', 'UjfWqSGWFeeRJtwJdpeHtJrrKPgWOWaw');
    define('BD', 'railway');
    define('PORT', 3306);
} else {
    // ⚙️ CONFIGURACIÓN LOCAL (XAMPP)
    define('SERVIDOR', 'localhost');
    define('USUARIO', 'root');
    define('PASSWORD', '');
    define('BD', 'sige');
    define('PORT', 3306);
}

// ============================================================================
// CONFIGURACIÓN DE LA APP
// ============================================================================
if (!defined('APP_NAME')) {
    define('APP_NAME', 'U.E.N ROBERTO MARTÍNEZ CENTENO');
}

if (!defined('APP_URL')) {
    // Detectar dominio automáticamente
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    define('APP_URL', $protocol . '://' . $host . '/heldyn/centeno/admin');
}

if (!defined('KEY_API_MAPS')) {
    define('KEY_API_MAPS', '');
}

// ============================================================================
// CONFIGURACIÓN DE CORREO (PHPMailer)
// ============================================================================
if (!defined('SMTP_HOST')) define('SMTP_HOST', 'smtp.gmail.com');
if (!defined('SMTP_USER')) define('SMTP_USER', 'heldyndiaz19@gmail.com');
if (!defined('SMTP_PASS')) define('SMTP_PASS', 'udtw erfq pyfn ydgh'); // contraseña de aplicación Gmail
if (!defined('SMTP_SECURE')) define('SMTP_SECURE', 'ssl');
if (!defined('SMTP_PORT')) define('SMTP_PORT', 465);

// ============================================================================
// CONEXIÓN PDO
// ============================================================================
$dsn = "mysql:host=" . SERVIDOR . ";port=" . PORT . ";dbname=" . BD . ";charset=utf8mb4";

try {
    $pdo = new PDO($dsn, USUARIO, PASSWORD, [
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    error_log("❌ Error de conexión a la base de datos: " . $e->getMessage());
    die("Error: No se pudo conectar a la base de datos. Contacte al administrador.");
}

// ============================================================================
// CONFIGURACIÓN DE FECHAS Y VARIABLES GLOBALES
// ============================================================================
date_default_timezone_set("America/Caracas");

$fechaHora = date('Y-m-d H:i:s');
$fecha_actual = date('Y-m-d');
$dia_actual = date('d');
$mes_actual = date('m');
$ano_actual = date('Y');
$ano_siguiente = $ano_actual + 1;
$estado_de_registro = '1';

// ============================================================================
// FUNCIÓN PARA ENVIAR CORREOS CON PHPMailer
// ============================================================================
if (!function_exists('enviarEmail')) {
    function enviarEmail($destinatario, $asunto, $cuerpo) {
        $mail = new PHPMailer(true);

        try {
            // Configuración del servidor SMTP
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = SMTP_USER;
            $mail->Password   = SMTP_PASS;
            $mail->SMTPSecure = SMTP_SECURE;
            $mail->Port       = SMTP_PORT;

            // Remitente y destinatario
            $mail->setFrom(SMTP_USER, APP_NAME);
            $mail->addAddress($destinatario);

            // Contenido del correo
            $mail->isHTML(true);
            $mail->Subject = $asunto;
            $mail->Body    = $cuerpo;
            $mail->AltBody = strip_tags($cuerpo);

            // Enviar
            $mail->send();
            error_log("✅ Email enviado exitosamente a: $destinatario");
            return true;
        } catch (Exception $e) {
            error_log("❌ Error al enviar email: {$mail->ErrorInfo}");

            // Fallback al mail() nativo
            $headers = "From: " . APP_NAME . " <no-reply@" . $_SERVER['HTTP_HOST'] . ">\r\n";
            $headers .= "Reply-To: no-reply@" . $_SERVER['HTTP_HOST'] . "\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

            return mail($destinatario, $asunto, $cuerpo, $headers);
        }
    }
}

// ============================================================================
// FUNCIÓN PARA CREAR TEMPLATE DE RECUPERACIÓN
// ============================================================================
if (!function_exists('crearTemplateRecuperacion')) {
    function crearTemplateRecuperacion($enlace_recuperacion, $email) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Recuperación de Contraseña</title>
            <style>
                body {font-family: Arial, sans-serif; line-height: 1.6; color: #333; background: #f4f4f4; margin: 0; padding: 0;}
                .container {max-width: 600px; margin: 20px auto; background: #fff; border-radius: 10px; overflow: hidden; box-shadow: 0 0 20px rgba(0,0,0,0.1);}
                .header {background: linear-gradient(135deg, #3c8dbc 0%, #2d5f7e 100%); color: white; padding: 30px; text-align: center;}
                .button {display: inline-block; background: linear-gradient(135deg, #3c8dbc 0%, #2d5f7e 100%); color: white; padding: 14px 28px; text-decoration: none; border-radius: 5px;}
                .footer {background: #f8f9fa; padding: 20px; text-align: center; color: #666; font-size: 12px; border-top: 1px solid #eee;}
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>" . APP_NAME . "</h2>
                    <h3>Recuperación de Contraseña</h3>
                </div>
                <div class='content' style='padding:30px;'>
                    <p>Hola,</p>
                    <p>Has solicitado restablecer tu contraseña para el correo: <strong>$email</strong>.</p>
                    <p style='text-align: center;'>
                        <a href='$enlace_recuperacion' class='button'>Restablecer Contraseña</a>
                    </p>
                    <p>Si el botón no funciona, copia este enlace:</p>
                    <p style='word-break: break-all;'>$enlace_recuperacion</p>
                    <p><strong>⚠️ Este enlace expirará en 1 hora.</strong></p>
                    <p>Atentamente,<br>El equipo de " . APP_NAME . "</p>
                </div>
                <div class='footer'>
                    <p>© " . date('Y') . " " . APP_NAME . ". Todos los derechos reservados.</p>
                </div>
            </div>
        </body>
        </html>";
    }
}
?>
