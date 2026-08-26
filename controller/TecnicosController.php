<?php
// controller/TecnicosController.php
// Gestión de técnicos - CORREGIDO

require_once __DIR__ . '/../model/Tecnico.php';
require_once __DIR__ . '/../helpers/ValidationHelper.php';
require_once __DIR__ . '/../helpers/SecurityHelper.php';

class TecnicosController {
    
    private Tecnico $model;
    
    public function __construct() {
        // Verificar autenticación y rol admin
        if (!isset($_SESSION['usuario_id']) || ($_SESSION['rol'] ?? '') !== 'admin') {
            header('Location: /proyecto/auth/login');
            exit;
        }
        $this->model = new Tecnico();
    }
    
    /**
     * Lista de técnicos
     * URL: /tecnicos
     */
    public function index() {
        $filtros = [
            'estado' => $_GET['estado'] ?? '',
            'buscar' => $_GET['buscar'] ?? '',
            'especialidad' => $_GET['especialidad'] ?? ''
        ];
        
        $tecnicos = $this->model->obtenerTodos($filtros);
        $estadisticas = $this->model->obtenerEstadisticas();
        
        $seccion = 'tecnicos';
        $titulo = 'Gestión de Técnicos';
        require_once __DIR__ . '/../views/tecnicos/index.php';
    }
    
    /**
     * Formulario para crear técnico
     * URL: /tecnicos/crear
     */
    public function crear() {
        $seccion = 'tecnicos';
        $titulo = 'Crear Técnico';
        require_once __DIR__ . '/../views/tecnicos/form.php';
    }
    
    /**
     * Guardar nuevo técnico
     * URL: /tecnicos/guardar (POST)
     */
    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /proyecto/tecnicos');
            exit;
        }
        
        // Validar CSRF
        if (!SecurityHelper::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'Token de seguridad inválido';
            header('Location: /proyecto/tecnicos/crear');
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
            header('Location: /proyecto/tecnicos/crear');
            exit;
        }
        
        // Crear técnico
        $datos = [
            'nombre' => $nombre,
            'email' => $email,
            'password' => $password,
            'especialidad' => ValidationHelper::sanitize($_POST['especialidad'] ?? ''),
            'tarifa' => (float)($_POST['tarifa'] ?? 0),
            'telefono' => ValidationHelper::sanitize($_POST['telefono'] ?? ''),
            'estado' => $_POST['estado'] ?? 'activo'
        ];
        
        $result = $this->model->crear($datos);
        
        if ($result) {
            $_SESSION['mensaje'] = 'Técnico creado correctamente';
            $_SESSION['mensaje_tipo'] = 'success';
            header('Location: /proyecto/tecnicos');
        } else {
            $_SESSION['error'] = 'Error al crear el técnico';
            header('Location: /proyecto/tecnicos/crear');
        }
        exit;
    }
    
    /**
     * Formulario para editar técnico
     * URL: /tecnicos/editar/{id}
     */
    public function editar($id) {
        $tecnico = $this->model->obtenerPorId($id);
        
        if (!$tecnico) {
            $_SESSION['error'] = 'Técnico no encontrado';
            header('Location: /proyecto/tecnicos');
            exit;
        }
        
        $seccion = 'tecnicos';
        $titulo = 'Editar Técnico';
        require_once __DIR__ . '/../views/tecnicos/form.php';
    }
    
    /**
     * Actualizar técnico
     * URL: /tecnicos/actualizar/{id} (POST)
     */
    public function actualizar($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /proyecto/tecnicos');
            exit;
        }
        
        // Validar CSRF
        if (!SecurityHelper::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'Token de seguridad inválido';
            header('Location: /proyecto/tecnicos/editar/' . $id);
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
            header('Location: /proyecto/tecnicos/editar/' . $id);
            exit;
        }
        
        // Actualizar técnico
        $datos = [
            'nombre' => $nombre,
            'email' => $email,
            'especialidad' => ValidationHelper::sanitize($_POST['especialidad'] ?? ''),
            'tarifa' => (float)($_POST['tarifa'] ?? 0),
            'telefono' => ValidationHelper::sanitize($_POST['telefono'] ?? ''),
            'estado' => $_POST['estado'] ?? 'activo'
        ];
        
        // Si se proporcionó nueva contraseña
        $password = $_POST['password'] ?? '';
        if (!empty($password)) {
            if (strlen($password) < 6) {
                $_SESSION['error'] = 'La contraseña debe tener al menos 6 caracteres';
                header('Location: /proyecto/tecnicos/editar/' . $id);
                exit;
            }
            $datos['password'] = $password;
        }
        
        $result = $this->model->actualizar($id, $datos);
        
        if ($result) {
            $_SESSION['mensaje'] = 'Técnico actualizado correctamente';
            $_SESSION['mensaje_tipo'] = 'success';
        } else {
            $_SESSION['error'] = 'Error al actualizar el técnico';
        }
        
        header('Location: /proyecto/tecnicos');
        exit;
    }
    
    /**
     * Eliminar técnico
     * URL: /tecnicos/eliminar/{id} (POST)
     */
    public function eliminar($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /proyecto/tecnicos');
            exit;
        }
        
        // Validar CSRF
        if (!SecurityHelper::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'Token de seguridad inválido';
            header('Location: /proyecto/tecnicos');
            exit;
        }
        
        $result = $this->model->eliminar($id);
        
        if ($result) {
            $_SESSION['mensaje'] = 'Técnico eliminado correctamente';
            $_SESSION['mensaje_tipo'] = 'success';
        } else {
            $_SESSION['error'] = 'Error al eliminar el técnico';
        }
        
        header('Location: /proyecto/tecnicos');
        exit;
    }
    
    /**
     * Cambiar estado del técnico
     * URL: /tecnicos/cambiarEstado/{id} (POST)
     */
    public function cambiarEstado($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /proyecto/tecnicos');
            exit;
        }
        
        // Validar CSRF
        if (!SecurityHelper::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'Token de seguridad inválido';
            header('Location: /proyecto/tecnicos');
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
        
        header('Location: /proyecto/tecnicos');
        exit;
    }
}
?>