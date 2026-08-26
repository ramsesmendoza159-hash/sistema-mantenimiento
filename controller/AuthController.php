<?php
// controller/AuthController.php
// Controlador de autenticación

require_once __DIR__ . '/../model/UsuariosModel.php';
require_once __DIR__ . '/../helpers/ValidationHelper.php';
require_once __DIR__ . '/../helpers/SecurityHelper.php';

class AuthController {
    
    private $usuarioModel;
    
    public function __construct() {
        $this->usuarioModel = new UsuariosModel();
    }
    
    /**
     * Mostrar formulario de login
     * URL: /login o /auth/login
     */
    public function login() {
        // Si ya está logueado, redirigir al dashboard
        if (isset($_SESSION['usuario_id'])) {
            header('Location: /proyecto/dashboard');
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
        
        // Validar token CSRF
        if (!SecurityHelper::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'Token de seguridad inválido';
            header('Location: /proyecto/login');
            exit;
        }
        
        // Sanitizar y validar datos
        $email = ValidationHelper::sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        // Validar email
        if (!ValidationHelper::validateEmail($email)) {
            $_SESSION['error'] = 'El email no es válido';
            header('Location: /proyecto/login');
            exit;
        }
        
        // Validar contraseña no vacía
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
                $_SESSION['nombre'] = SecurityHelper::preventXSS($usuario['nombre']);
                $_SESSION['email'] = SecurityHelper::preventXSS($usuario['email']);
                $_SESSION['rol'] = SecurityHelper::preventXSS($usuario['rol']);
                $_SESSION['login_time'] = time();
                
                // Regenerar ID de sesión por seguridad
                session_regenerate_id(true);
                
                // Redirigir según rol
                $redirect = $this->getRedirectByRol($usuario['rol']);
                header('Location: ' . $redirect);
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
        // Destruir sesión
        $_SESSION = [];
        session_destroy();
        
        // Eliminar cookie de sesión
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
        
        header('Location: /proyecto/login');
        exit;
    }
    
    /**
     * Obtener URL de redirección según rol
     */
    private function getRedirectByRol($rol) {
        $redirects = [
            'admin' => '/proyecto/admin/dashboard',
            'supervisor' => '/proyecto/supervisor',
            'tecnico' => '/proyecto/tecnico',
            'usuario' => '/proyecto/dashboard'
        ];
        return $redirects[$rol] ?? '/proyecto/dashboard';
    }
}
?>