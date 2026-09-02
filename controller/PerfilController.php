<?php
// controller/PerfilController.php
// Controlador de perfil de usuario - VERSIÓN CORREGIDA

require_once __DIR__ . '/../model/UsuariosModel.php';
require_once __DIR__ . '/../helpers/SecurityHelper.php';
require_once __DIR__ . '/../helpers/HashHelper.php';

class PerfilController {
    
    private $usuarioModel;
    
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Verificar que el usuario esté logueado
        if (!isset($_SESSION['usuario_id']) || empty($_SESSION['usuario_id'])) {
            header('Location: /proyecto/login');
            exit;
        }
        
        $this->usuarioModel = new UsuariosModel();
    }
    
    /**
     * ✅ Mostrar perfil del usuario
     */
    public function index() {
        $usuarioId = $_SESSION['usuario_id'];
        
        // Obtener datos del usuario
        $usuario = $this->usuarioModel->obtenerPorId($usuarioId);
        
        if (!$usuario) {
            $_SESSION['error'] = 'No se pudo cargar la información del usuario.';
            header('Location: /proyecto/dashboard');
            exit;
        }
        
        // ✅ Pasar datos a la vista
        $titulo = 'Mi Perfil';
        $seccion = 'perfil';  // ✅ AGREGADO - Para el sidebar
        require_once __DIR__ . '/../views/perfil/index.php';
    }
    
    /**
     * ✅ Mostrar formulario de edición de perfil
     */
    public function editar() {
        $usuarioId = $_SESSION['usuario_id'];
        $usuario = $this->usuarioModel->obtenerPorId($usuarioId);
        
        if (!$usuario) {
            $_SESSION['error'] = 'No se pudo cargar la información del usuario.';
            header('Location: /proyecto/perfil');
            exit;
        }
        
        // ✅ Pasar datos a la vista
        $titulo = 'Editar Perfil';
        $seccion = 'perfil';  // ✅ AGREGADO - Para el sidebar
        require_once __DIR__ . '/../views/perfil/editar.php';
    }
    
    /**
     * ✅ Actualizar datos del perfil
     */
    public function actualizar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /proyecto/perfil');
            exit;
        }
        
        // Verificar CSRF
        if (method_exists('SecurityHelper', 'verifyCSRFToken')) {
            $token = $_POST['csrf_token'] ?? '';
            if (!SecurityHelper::verifyCSRFToken($token)) {
                $_SESSION['error'] = 'Token de seguridad inválido';
                header('Location: /proyecto/perfil');
                exit;
            }
        }
        
        $usuarioId = $_SESSION['usuario_id'];
        
        // Validar datos
        $nombre = trim($_POST['nombre'] ?? '');
        $apellido = trim($_POST['apellido'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $email = trim($_POST['email'] ?? '');
        
        $errores = [];
        
        if (empty($nombre)) {
            $errores[] = 'El nombre es obligatorio';
        }
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errores[] = 'El email no es válido';
        }
        
        // Verificar que el email no esté en uso por otro usuario
        if (!empty($email) && $this->usuarioModel->emailExiste($email, $usuarioId)) {
            $errores[] = 'El email ya está en uso por otro usuario';
        }
        
        if (!empty($errores)) {
            $_SESSION['errores'] = $errores;
            $_SESSION['old'] = [
                'nombre' => $nombre,
                'apellido' => $apellido,
                'telefono' => $telefono,
                'email' => $email
            ];
            header('Location: /proyecto/perfil/editar');
            exit;
        }
        
        // Actualizar datos
        $datos = [
            'nombre' => $nombre,
            'apellido' => $apellido,
            'telefono' => $telefono,
            'email' => $email,
            'rol' => $_SESSION['rol'] ?? 'usuario',
            'estado' => 'activo'
        ];
        
        $resultado = $this->usuarioModel->actualizar($usuarioId, $datos);
        
        if ($resultado) {
            // Actualizar sesión
            $_SESSION['nombre'] = $nombre;
            $_SESSION['email'] = $email;
            
            $_SESSION['mensaje'] = 'Perfil actualizado correctamente';
            $_SESSION['mensaje_tipo'] = 'success';
        } else {
            $_SESSION['error'] = 'Error al actualizar el perfil';
        }
        
        header('Location: /proyecto/perfil');
        exit;
    }
    
    /**
     * ✅ Cambiar contraseña
     */
    public function cambiarPassword() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /proyecto/perfil');
            exit;
        }
        
        // Verificar CSRF
        if (method_exists('SecurityHelper', 'verifyCSRFToken')) {
            $token = $_POST['csrf_token'] ?? '';
            if (!SecurityHelper::verifyCSRFToken($token)) {
                $_SESSION['error'] = 'Token de seguridad inválido';
                header('Location: /proyecto/perfil');
                exit;
            }
        }
        
        $usuarioId = $_SESSION['usuario_id'];
        
        // Obtener datos del formulario
        $passwordActual = $_POST['password_actual'] ?? '';
        $passwordNueva = $_POST['password_nueva'] ?? '';
        $passwordConfirmar = $_POST['password_confirmar'] ?? '';
        
        // Validaciones
        $errores = [];
        
        if (empty($passwordActual)) {
            $errores[] = 'Debes ingresar tu contraseña actual';
        }
        
        if (strlen($passwordNueva) < 6) {
            $errores[] = 'La nueva contraseña debe tener al menos 6 caracteres';
        }
        
        if ($passwordNueva !== $passwordConfirmar) {
            $errores[] = 'Las contraseñas no coinciden';
        }
        
        if (!empty($errores)) {
            $_SESSION['errores'] = $errores;
            header('Location: /proyecto/perfil');
            exit;
        }
        
        // Verificar contraseña actual
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT password_hash FROM usuarios WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute([':id' => $usuarioId]);
        $hash = $stmt->fetchColumn();
        
        if (!HashHelper::verify($passwordActual, $hash)) {
            $_SESSION['error'] = 'La contraseña actual es incorrecta';
            header('Location: /proyecto/perfil');
            exit;
        }
        
        // Cambiar contraseña
        $resultado = $this->usuarioModel->cambiarPassword($usuarioId, $passwordNueva);
        
        if ($resultado) {
            $_SESSION['mensaje'] = 'Contraseña actualizada correctamente';
            $_SESSION['mensaje_tipo'] = 'success';
        } else {
            $_SESSION['error'] = 'Error al actualizar la contraseña';
        }
        
        header('Location: /proyecto/perfil');
        exit;
    }
}
?>