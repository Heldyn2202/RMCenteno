<?php  
// PHPMailer
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

if (!defined('SERVIDOR')) {  
    define('SERVIDOR', 'localhost');  
}  

if (!defined('USUARIO')) {  
    define('USUARIO', 'root');  
}  

if (!defined('PASSWORD')) {  
    define('PASSWORD', '');  
}  

if (!defined('BD')) {  
    define('BD', 'sige');  
}  

if (!defined('APP_NAME')) {  
    define('APP_NAME', 'U.E. ROBERTO MARTINEZ CENTENO');  
}  

if (!defined('APP_URL')) {  
    define('APP_URL', 'http://localhost/sige/centeno/admin');  
}  

if (!defined('KEY_API_MAPS')) {  
    define('KEY_API_MAPS', '');  
}  

// Configuración de PHPMailer
if (!defined('SMTP_HOST')) {
    define('SMTP_HOST', 'smtp.gmail.com');
}
if (!defined('SMTP_USER')) {
    define('SMTP_USER', 'heldyndiaz19@gmail.com'); // Reemplaza con tu email
}
if (!defined('SMTP_PASS')) {
    define('SMTP_PASS', ' udtw erfq pyfn ydgh'); //  contraseña de aplicación
}
if (!defined('SMTP_SECURE')) {
    define('SMTP_SECURE', 'ssl');
}
if (!defined('SMTP_PORT')) {
    define('SMTP_PORT', 465);
}

$dsn = "mysql:dbname=" . BD . ";host=" . SERVIDOR . ";charset=utf8mb4"; 

try {  
    $pdo = new PDO($dsn, USUARIO, PASSWORD, array(
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ));  
} catch (PDOException $e) {  
    die("Error: No se pudo conectar a la base de datos. Contacte al administrador.");
}  

date_default_timezone_set("America/Caracas");  
$fechaHora = date('Y-m-d');  

$fecha_actual = date('Y-m-d');  
$dia_actual = date('d');  
$mes_actual = date('m');  
$ano_actual = date('Y');  
$ano_siguiente = date("Y") + 1;  

$estado_de_registro = '1';

// ============================================================================
// FUNCIÓN PARA ENVIAR CORREOS ELECTRÓNICOS CON PHPMailer
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
            
            // Configuración de remitente y destinatario
            $mail->setFrom(SMTP_USER, APP_NAME);
            $mail->addAddress($destinatario);
            
            // Contenido del correo
            $mail->isHTML(true);
            $mail->Subject = $asunto;
            $mail->Body    = $cuerpo;
            $mail->AltBody = strip_tags($cuerpo); // Versión alternativa sin HTML
            
            // Enviar el correo
            $mail->send();
            error_log("Email enviado exitosamente a: $destinatario");
            return true;
        } catch (Exception $e) {
            error_log("Error al enviar email: {$mail->ErrorInfo}");
            
            // Fallback al método mail() nativo si PHPMailer falla
            $headers = "From: " . APP_NAME . " <sistema@" . $_SERVER['HTTP_HOST'] . ">\r\n";
            $headers .= "Reply-To: no-reply@" . $_SERVER['HTTP_HOST'] . "\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            $headers .= "X-Mailer: PHP/" . phpversion();
            $headers .= "X-Priority: 1 (Highest)\r\n";
            $headers .= "X-MSMail-Priority: High\r\n";
            $headers .= "Importance: High\r\n";

            if (mail($destinatario, $asunto, $cuerpo, $headers)) {
                error_log("Email enviado usando fallback a: $destinatario");
                return true;
            } else {
                error_log("Error al enviar email con fallback a: $destinatario");
                return false;
            }
        }
    }
}

// Función para crear el template de email de recuperación
if (!function_exists('crearTemplateRecuperacion')) {
    function crearTemplateRecuperacion($enlace_recuperacion, $email) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Recuperación de Contraseña</title>
            <style>
                body { 
                    font-family: Arial, sans-serif; 
                    line-height: 1.6; 
                    color: #333; 
                    margin: 0; 
                    padding: 0; 
                    background-color: #f4f4f4;
                }
                .container { 
                    max-width: 600px; 
                    margin: 20px auto; 
                    background: white; 
                    border-radius: 10px; 
                    overflow: hidden;
                    box-shadow: 0 0 20px rgba(0,0,0,0.1);
                }
                .header { 
                    background: linear-gradient(135deg, #3c8dbc 0%, #2d5f7e 100%); 
                    color: white; 
                    padding: 30px; 
                    text-align: center; 
                }
                .content { 
                    padding: 30px; 
                    background: #ffffff;
                }
                .button { 
                    display: inline-block; 
                    background: linear-gradient(135deg, #3c8dbc 0%, #2d5f7e 100%); 
                    color: white; 
                    padding: 14px 28px; 
                    text-decoration: none; 
                    border-radius: 5px; 
                    margin: 20px 0; 
                    font-weight: bold;
                    border: none;
                    cursor: pointer;
                }
                .footer { 
                    background: #f8f9fa; 
                    padding: 20px; 
                    text-align: center; 
                    color: #666; 
                    font-size: 12px;
                    border-top: 1px solid #eee;
                }
                .code-box {
                    background: #f8f9fa;
                    border: 1px solid #ddd;
                    border-radius: 5px;
                    padding: 15px;
                    margin: 15px 0;
                    word-break: break-all;
                    font-family: monospace;
                    color: #3c8dbc;
                }
                .warning {
                    background: #fff3cd;
                    border: 1px solid #ffeaa7;
                    border-radius: 5px;
                    padding: 15px;
                    margin: 15px 0;
                    color: #856404;
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>" . APP_NAME . "</h2>
                    <h3>Recuperación de Contraseña</h3>
                </div>
                <div class='content'>
                    <p>Hola,</p>
                    <p>Has solicitado restablecer tu contraseña para el email: <strong>$email</strong></p>
                    
                    <p style='text-align: center;'>
                        <a href='$enlace_recuperacion' class='button'>Restablecer Contraseña</a>
                    </p>
                    
                    <p>Si el botón no funciona, copia y pega este enlace en tu navegador:</p>
                    <div class='code-box'>
                        $enlace_recuperacion
                    </div>
                    
                    <div class='warning'>
                        <p><strong>⚠️ Este enlace expirará en 1 hora.</strong></p>
                        <p>Por seguridad, no compartas este enlace con nadie.</p>
                    </div>
                    
                    <p>Si no solicitaste este cambio, por favor ignora este mensaje y tu contraseña permanecerá igual.</p>
                    
                    <p>Atentamente,<br>El equipo de " . APP_NAME . "</p>
                </div>
                <div class='footer'>
                    <p>© " . date('Y') . " " . APP_NAME . ". Todos los derechos reservados.</p>
                    <p>Este es un mensaje automático, por favor no respondas a este correo.</p>
                </div>
            </div>
        </body>
        </html>";
    }
}
?>