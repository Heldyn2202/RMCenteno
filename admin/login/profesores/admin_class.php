<?php
session_start();
ini_set('display_errors', 1);

class Action {
    private $db;

    public function __construct() {
        ob_start();
        include 'db_connect.php';
        $this->db = $conn;
    }
    
    function __destruct() {
        $this->db->close();
        ob_end_flush();
    }

    function login(){
        extract($_POST);
        
        // Validar email y contraseña
        if(empty($email) || empty($password)){
            return 2; // Campos vacíos
        }
        
        // Consulta preparada para evitar SQL injection
        $stmt = $this->db->prepare("SELECT * FROM profesores WHERE email = ? AND estado = 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($result->num_rows > 0){
            $profesor = $result->fetch_assoc();
            
            // Verificar contraseña (md5 en este caso)
            if(md5($password) == $profesor['password']){
                // Establecer variables de sesión
                $_SESSION['login_id'] = $profesor['id_profesor'];
                $_SESSION['login_email'] = $profesor['email'];
                $_SESSION['login_nombres'] = $profesor['nombres'];
                $_SESSION['login_apellidos'] = $profesor['apellidos'];
                $_SESSION['login_especialidad'] = $profesor['especialidad'];
                
                // Recordar usuario si marcó la opción
                if(isset($remember) && $remember == 'on'){
                    setcookie('profesor_email', $email, time() + (86400 * 30), "/");
                    setcookie('profesor_remember', '1', time() + (86400 * 30), "/");
                } else {
                    if(isset($_COOKIE['profesor_email'])){
                        setcookie('profesor_email', '', time() - 3600, "/");
                        setcookie('profesor_remember', '', time() - 3600, "/");
                    }
                }
                
                return 1; // Login exitoso
            }
        }
        
        return 2; // Credenciales incorrectas
    }

    function logout(){
        // Destruir todas las variables de sesión
        $_SESSION = array();
        
        // Borrar la cookie de sesión
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Destruir la sesión
        session_destroy();
        
        // Redirigir al login
        header("location: login.php");
        exit();
    }
}