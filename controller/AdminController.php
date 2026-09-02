<?php
// controller/AuthController.php
// Controlador de autenticación - VERSIÓN CORREGIDA

require_once __DIR__ . '/../model/UsuariosModel.php';
require_once __DIR__ . '/../helpers/ValidationHelper.php';
require_once __DIR__ . '/../helpers/SecurityHelper.php';
require_once __DIR__ . '/../helpers/AuthHelper.php';  // ✅ AGREGADO

class AuthController {
    
    private $usuarioModel;
    private $authHelper;  // ✅ AGREGADO
    
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->usuarioModel = new UsuariosModel();
        $this->authHelper = new AuthHelper();  // ✅ AGREGADO
    }
    
    /**
     * Mostrar formulario de login
     * URL: /login o /auth/login
     */
    public function login() {
        if (isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id'])) {
            $this->authHelper->redirectByRole();  // ✅ USAR HELPER
            exit;
        }
        
        $titulo = 'Iniciar Sesión';
        require_once __DIR__ . '/../views/auth/login.php';
    }
    
    /**
     * Procesar autenticación
     * URL: /auth/authenticate (POST)
     */
    public function authenticate() {
        // Verificar método POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /proyecto/login');
            exit;
        }
        
        // ✅ VERIFICAR CSRF TOKEN
        $token = $_POST['csrf_token'] ?? '';
        
        if (empty($token)) {
            $_SESSION['error'] = 'Token de seguridad no proporcionado';
            header('Location: /proyecto/login');
            exit;
        }
        
        if (!SecurityHelper::verifyCSRFToken($token)) {
            $_SESSION['error'] = 'Token de seguridad inválido. Por favor, recarga la página.';
            header('Location: /proyecto/login');
            exit;
        }
        
        // Obtener datos
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        // Validar email
        if (empty($email)) {
            $_SESSION['error'] = 'El email es obligatorio';
            header('Location: /proyecto/login');
            exit;
        }
        
        // Validar formato de email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'El email no es válido';
            header('Location: /proyecto/login');
            exit;
        }
        
        // Validar contraseña
        if (empty($password)) {
            $_SESSION['error'] = 'La contraseña es obligatoria';
            header('Location: /proyecto/login');
            exit;
        }
        
        // Intentar autenticar
        try {
            $usuario = $this->usuarioModel->autenticar($email, $password);
            
            if ($usuario) {
                // Iniciar sesión
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['nombre'] = htmlspecialchars($usuario['nombre'] ?? 'Usuario', ENT_QUOTES, 'UTF-8');
                $_SESSION['email'] = htmlspecialchars($usuario['email'] ?? '', ENT_QUOTES, 'UTF-8');
                $_SESSION['rol'] = $usuario['rol'] ?? 'usuario';
                $_SESSION['login_time'] = time();
                
                // Regenerar ID de sesión por seguridad
                session_regenerate_id(true);
                
                // ✅ USAR AUTHHELPER PARA REDIRIGIR
                $this->authHelper->redirectByRole();
                exit;
            } else {
                $_SESSION['error'] = 'Credenciales incorrectas';
                header('Location: /proyecto/login');
                exit;
            }
        } catch (Exception $e) {
            error_log("Error en authenticate: " . $e->getMessage());
            $_SESSION['error'] = 'Error al iniciar sesión';
            header('Location: /proyecto/login');
            exit;
        }
    }
    
    /**
     * Cerrar sesión
     * URL: /logout o /auth/logout
     */
    public function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $_SESSION = [];
        
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        
        session_destroy();
        
        header('Location: /proyecto/login');
        exit;
    }
}
?>