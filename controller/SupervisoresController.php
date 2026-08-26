<?php
// controller/SupervisoresController.php
// Gestión de supervisores - CORREGIDO

require_once __DIR__ . '/../model/Supervisor.php';
require_once __DIR__ . '/../helpers/ValidationHelper.php';
require_once __DIR__ . '/../helpers/SecurityHelper.php';

class SupervisoresController {
    
    private Supervisor $model;
    
    public function __construct() {
        // Verificar autenticación y rol admin
        if (!isset($_SESSION['usuario_id']) || ($_SESSION['rol'] ?? '') !== 'admin') {
            header('Location: /proyecto/auth/login');
            exit;
        }
        $this->model = new Supervisor();
    }
    
    /**
     * Lista de supervisores
     * URL: /supervisores
     */
    public function index() {
        $filtros = [
            'estado' => $_GET['estado'] ?? '',
            'buscar' => $_GET['buscar'] ?? '',
            'area' => $_GET['area'] ?? ''
        ];
        
        $supervisores = $this->model->obtenerTodos($filtros);
        $estadisticas = $this->model->obtenerEstadisticas();
        
        $seccion = 'supervisores';
        $titulo = 'Gestión de Supervisores';
        require_once __DIR__ . '/../views/supervisores/index.php';
    }
    
    /**
     * Formulario para crear supervisor
     * URL: /supervisores/crear
     */
    public function crear() {
        $seccion = 'supervisores';
        $titulo = 'Crear Supervisor';
        require_once __DIR__ . '/../views/supervisores/form.php';
    }
    
    /**
     * Guardar nuevo supervisor
     * URL: /supervisores/guardar (POST)
     */
    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /proyecto/supervisores');
            exit;
        }
        
        // Validar CSRF
        if (!SecurityHelper::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'Token de seguridad inválido';
            header('Location: /proyecto/supervisores/crear');
            exit;
        }
        
        // Validar datos
        $errores = [];
        
        $nombre = ValidationHelper::sanitize($_POST['nombre'] ?? '');
        if (empty($nombre)) {
            $errores[] = 'El nombre es obligatorio';
        } elseif (!ValidationHelper::validateLength($nombre, 3, 100)) {
            $errores[] = 'El nombre debe tener entre 3 y 100 caracteres';
        }
        
        $email = ValidationHelper::sanitize($_POST['email'] ?? '');
        if (empty($email)) {
            $errores[] = 'El email es obligatorio';
        } elseif (!ValidationHelper::validateEmail($email)) {
            $errores[] = 'El email no es válido';
        }
        
        // Verificar si el email ya existe
        if ($this->model->emailExiste($email)) {
            $errores[] = 'El email ya está registrado';
        }
        
        $password = $_POST['password'] ?? '';
        if (empty($password)) {
            $errores[] = 'La contraseña es obligatoria';
        } elseif (strlen($password) < 6) {
            $errores[] = 'La contraseña debe tener al menos 6 caracteres';
        }
        
        if (!empty($errores)) {
            $_SESSION['errores'] = $errores;
            header('Location: /proyecto/supervisores/crear');
            exit;
        }
        
        // Crear supervisor
        $datos = [
            'nombre' => $nombre,
            'email' => $email,
            'password' => $password,
            'area' => ValidationHelper::sanitize($_POST['area'] ?? ''),
            'telefono' => ValidationHelper::sanitize($_POST['telefono'] ?? ''),
            'estado' => $_POST['estado'] ?? 'activo'
        ];
        
        $result = $this->model->crear($datos);
        
        if ($result) {
            $_SESSION['mensaje'] = 'Supervisor creado correctamente';
            $_SESSION['mensaje_tipo'] = 'success';
            header('Location: /proyecto/supervisores');
        } else {
            $_SESSION['error'] = 'Error al crear el supervisor';
            header('Location: /proyecto/supervisores/crear');
        }
        exit;
    }
    
    /**
     * Formulario para editar supervisor
     * URL: /supervisores/editar/{id}
     */
    public function editar($id) {
        $supervisor = $this->model->obtenerPorId($id);
        
        if (!$supervisor) {
            $_SESSION['error'] = 'Supervisor no encontrado';
            header('Location: /proyecto/supervisores');
            exit;
        }
        
        $seccion = 'supervisores';
        $titulo = 'Editar Supervisor';
        require_once __DIR__ . '/../views/supervisores/form.php';
    }
    
    /**
     * Actualizar supervisor
     * URL: /supervisores/actualizar/{id} (POST)
     */
    public function actualizar($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /proyecto/supervisores');
            exit;
        }
        
        // Validar CSRF
        if (!SecurityHelper::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'Token de seguridad inválido';
            header('Location: /proyecto/supervisores/editar/' . $id);
            exit;
        }
        
        // Validar datos
        $errores = [];
        
        $nombre = ValidationHelper::sanitize($_POST['nombre'] ?? '');
        if (empty($nombre)) {
            $errores[] = 'El nombre es obligatorio';
        } elseif (!ValidationHelper::validateLength($nombre, 3, 100)) {
            $errores[] = 'El nombre debe tener entre 3 y 100 caracteres';
        }
        
        $email = ValidationHelper::sanitize($_POST['email'] ?? '');
        if (empty($email)) {
            $errores[] = 'El email es obligatorio';
        } elseif (!ValidationHelper::validateEmail($email)) {
            $errores[] = 'El email no es válido';
        }
        
        // Verificar si el email ya existe (excepto el actual)
        if ($this->model->emailExiste($email, $id)) {
            $errores[] = 'El email ya está registrado por otro usuario';
        }
        
        if (!empty($errores)) {
            $_SESSION['errores'] = $errores;
            header('Location: /proyecto/supervisores/editar/' . $id);
            exit;
        }
        
        // Actualizar supervisor
        $datos = [
            'nombre' => $nombre,
            'email' => $email,
            'area' => ValidationHelper::sanitize($_POST['area'] ?? ''),
            'telefono' => ValidationHelper::sanitize($_POST['telefono'] ?? ''),
            'estado' => $_POST['estado'] ?? 'activo'
        ];
        
        // Si se proporcionó nueva contraseña
        $password = $_POST['password'] ?? '';
        if (!empty($password)) {
            if (strlen($password) < 6) {
                $_SESSION['error'] = 'La contraseña debe tener al menos 6 caracteres';
                header('Location: /proyecto/supervisores/editar/' . $id);
                exit;
            }
            $datos['password'] = $password;
        }
        
        $result = $this->model->actualizar($id, $datos);
        
        if ($result) {
            $_SESSION['mensaje'] = 'Supervisor actualizado correctamente';
            $_SESSION['mensaje_tipo'] = 'success';
        } else {
            $_SESSION['error'] = 'Error al actualizar el supervisor';
        }
        
        header('Location: /proyecto/supervisores');
        exit;
    }
    
    /**
     * Eliminar supervisor
     * URL: /supervisores/eliminar/{id} (POST)
     */
    public function eliminar($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /proyecto/supervisores');
            exit;
        }
        
        // Validar CSRF
        if (!SecurityHelper::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'Token de seguridad inválido';
            header('Location: /proyecto/supervisores');
            exit;
        }
        
        $result = $this->model->eliminar($id);
        
        if ($result) {
            $_SESSION['mensaje'] = 'Supervisor eliminado correctamente';
            $_SESSION['mensaje_tipo'] = 'success';
        } else {
            $_SESSION['error'] = 'Error al eliminar el supervisor';
        }
        
        header('Location: /proyecto/supervisores');
        exit;
    }
    
    /**
     * Cambiar estado del supervisor
     * URL: /supervisores/cambiarEstado/{id} (POST)
     */
    public function cambiarEstado($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /proyecto/supervisores');
            exit;
        }
        
        // Validar CSRF
        if (!SecurityHelper::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'Token de seguridad inválido';
            header('Location: /proyecto/supervisores');
            exit;
        }
        
        $estado = $_POST['estado'] ?? 'activo';
        $result = $this->model->cambiarEstado($id, $estado);
        
        if ($result) {
            $_SESSION['mensaje'] = 'Estado actualizado correctamente';
            $_SESSION['mensaje_tipo'] = 'success';
        } else {
            $_SESSION['error'] = 'Error al cambiar el estado';
        }
        
        header('Location: /proyecto/supervisores');
        exit;
    }
}
?>