<?php
// controller/TecnicosController.php
// Gestión de técnicos - VERSIÓN CORREGIDA

require_once __DIR__ . '/../model/Tecnico.php';
require_once __DIR__ . '/../helpers/ValidationHelper.php';
require_once __DIR__ . '/../helpers/SecurityHelper.php';

class TecnicosController {
    
    private $model;
    
    public function __construct() {
        // Iniciar sesión si no está iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Verificar autenticación
        if (!isset($_SESSION['usuario_id']) || empty($_SESSION['usuario_id'])) {
            $_SESSION['error'] = 'Debes iniciar sesión para acceder a esta página';
            header('Location: /proyecto/auth/login');
            exit;
        }
        
        // Verificar rol admin
        if (($_SESSION['rol'] ?? '') !== 'admin') {
            $_SESSION['error'] = 'No tienes permisos para acceder a esta sección';
            header('Location: /proyecto/dashboard');
            exit;
        }
        
        $this->model = new Tecnico();
    }
    
    /**
     * Lista de técnicos
     * URL: /tecnicos
     */
    public function index() {
        try {
            $filtros = [
                'estado' => $_GET['estado'] ?? '',
                'buscar' => $_GET['buscar'] ?? '',
                'especialidad' => $_GET['especialidad'] ?? ''
            ];
            
            $tecnicos = $this->model->obtenerTodos($filtros);
            $estadisticas = $this->model->obtenerEstadisticas();
            $especialidades = $this->model->obtenerEspecialidades();
            
            // Calcular tarifa promedio
            $tarifa_promedio = 0;
            if (!empty($tecnicos)) {
                $total_tarifas = array_sum(array_column($tecnicos, 'tarifa'));
                $tarifa_promedio = $total_tarifas / count($tecnicos);
            }
            
            // Contar órdenes asignadas
            $db = Database::getInstance()->getConnection();
            $sql = "SELECT COUNT(DISTINCT id) as total FROM ordenes_mantenimiento WHERE tecnico_id IS NOT NULL";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $total_ordenes = (int)($result['total'] ?? 0);
            
            $seccion = 'tecnicos';
            $titulo = 'Gestión de Técnicos';
            
            require_once __DIR__ . '/../views/tecnicos/index.php';
            
        } catch (Exception $e) {
            error_log("Error en TecnicosController::index: " . $e->getMessage());
            $_SESSION['error'] = 'Error al cargar los técnicos';
            header('Location: /proyecto/dashboard');
            exit;
        }
    }
    
    /**
     * Formulario para crear técnico
     * URL: /tecnicos/crear
     */
    public function crear() {
        try {
            $seccion = 'tecnicos';
            $titulo = 'Crear Técnico';
            
            // ✅ USAR crear.php (NO form.php)
            require_once __DIR__ . '/../views/tecnicos/crear.php';
            
        } catch (Exception $e) {
            error_log("Error en TecnicosController::crear: " . $e->getMessage());
            $_SESSION['error'] = 'Error al cargar el formulario';
            header('Location: /proyecto/tecnicos');
            exit;
        }
    }
    
    /**
     * Guardar nuevo técnico
     * URL: /tecnicos/guardar (POST)
     */
    public function guardar() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                header('Location: /proyecto/tecnicos');
                exit;
            }
            
            // Validar CSRF
            if (method_exists('SecurityHelper', 'verifyCSRFToken')) {
                $token = $_POST['csrf_token'] ?? '';
                if (!SecurityHelper::verifyCSRFToken($token)) {
                    $_SESSION['error'] = 'Token de seguridad inválido. Por favor, recarga la página.';
                    header('Location: /proyecto/tecnicos/crear');
                    exit;
                }
            }
            
            // Guardar datos del formulario para mantenerlos si hay error
            $_SESSION['old'] = [
                'nombre' => $_POST['nombre'] ?? '',
                'email' => $_POST['email'] ?? '',
                'telefono' => $_POST['telefono'] ?? '',
                'especialidad' => $_POST['especialidad'] ?? '',
                'tarifa' => $_POST['tarifa'] ?? 0,
                'estado' => $_POST['estado'] ?? 'activo'
            ];
            
            // Validar datos
            $errores = [];
            
            // Validar nombre
            $nombre = trim($_POST['nombre'] ?? '');
            if (empty($nombre)) {
                $errores['nombre'] = 'El nombre es obligatorio';
            } elseif (strlen($nombre) < 3 || strlen($nombre) > 100) {
                $errores['nombre'] = 'El nombre debe tener entre 3 y 100 caracteres';
            }
            
            // Validar email
            $email = trim($_POST['email'] ?? '');
            if (empty($email)) {
                $errores['email'] = 'El email es obligatorio';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errores['email'] = 'El email no es válido';
            }
            
            // Verificar si el email ya existe
            if (method_exists($this->model, 'emailExiste') && $this->model->emailExiste($email)) {
                $errores['email'] = 'El email ya está registrado';
            }
            
            // Validar contraseña
            $password = $_POST['password'] ?? '';
            $confirmar_password = $_POST['confirmar_password'] ?? '';
            
            if (empty($password)) {
                $errores['password'] = 'La contraseña es obligatoria';
            } elseif (strlen($password) < 6) {
                $errores['password'] = 'La contraseña debe tener al menos 6 caracteres';
            } elseif ($password !== $confirmar_password) {
                $errores['confirmar_password'] = 'Las contraseñas no coinciden';
            }
            
            // Validar especialidad
            $especialidad = trim($_POST['especialidad'] ?? '');
            if (empty($especialidad)) {
                $errores['especialidad'] = 'La especialidad es obligatoria';
            }
            
            // Validar tarifa
            $tarifa = (float)($_POST['tarifa'] ?? 0);
            if ($tarifa < 0) {
                $errores['tarifa'] = 'La tarifa no puede ser negativa';
            }
            
            // Validar teléfono (opcional)
            $telefono = trim($_POST['telefono'] ?? '');
            if (!empty($telefono) && !preg_match('/^[0-9\s\-+()]{6,15}$/', $telefono)) {
                $errores['telefono'] = 'El teléfono no es válido';
            }
            
            // Si hay errores, volver al formulario
            if (!empty($errores)) {
                $_SESSION['errores'] = $errores;
                header('Location: /proyecto/tecnicos/crear');
                exit;
            }
            
            // Crear técnico
            $datos = [
                'nombre' => $nombre,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'especialidad' => $especialidad,
                'tarifa' => $tarifa,
                'telefono' => $telefono,
                'estado' => $_POST['estado'] ?? 'activo'
            ];
            
            $result = $this->model->crear($datos);
            
            if ($result) {
                unset($_SESSION['old']);
                $_SESSION['mensaje'] = 'Técnico creado correctamente';
                $_SESSION['mensaje_tipo'] = 'success';
                header('Location: /proyecto/tecnicos');
            } else {
                $_SESSION['error'] = 'Error al crear el técnico. Intenta nuevamente.';
                header('Location: /proyecto/tecnicos/crear');
            }
            exit;
            
        } catch (Exception $e) {
            error_log("Error en TecnicosController::guardar: " . $e->getMessage());
            $_SESSION['error'] = 'Error al guardar el técnico: ' . $e->getMessage();
            header('Location: /proyecto/tecnicos/crear');
            exit;
        }
    }
    
    /**
     * Formulario para editar técnico
     * URL: /tecnicos/editar/{id}
     */
    public function editar($id) {
        try {
            $id = (int)$id;
            
            if ($id <= 0) {
                $_SESSION['error'] = 'ID de técnico inválido';
                header('Location: /proyecto/tecnicos');
                exit;
            }
            
            $tecnico = $this->model->obtenerPorId($id);
            
            if (!$tecnico) {
                $_SESSION['error'] = 'Técnico no encontrado';
                header('Location: /proyecto/tecnicos');
                exit;
            }
            
            $seccion = 'tecnicos';
            $titulo = 'Editar Técnico';
            
            // ✅ USAR editar.php (NO form.php)
            require_once __DIR__ . '/../views/tecnicos/editar.php';
            
        } catch (Exception $e) {
            error_log("Error en TecnicosController::editar: " . $e->getMessage());
            $_SESSION['error'] = 'Error al cargar el técnico';
            header('Location: /proyecto/tecnicos');
            exit;
        }
    }
    
    /**
     * Actualizar técnico
     * URL: /tecnicos/actualizar/{id} (POST)
     */
    public function actualizar($id) {
        try {
            $id = (int)$id;
            
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                header('Location: /proyecto/tecnicos');
                exit;
            }
            
            if ($id <= 0) {
                $_SESSION['error'] = 'ID de técnico inválido';
                header('Location: /proyecto/tecnicos');
                exit;
            }
            
            // Validar CSRF
            if (method_exists('SecurityHelper', 'verifyCSRFToken')) {
                $token = $_POST['csrf_token'] ?? '';
                if (!SecurityHelper::verifyCSRFToken($token)) {
                    $_SESSION['error'] = 'Token de seguridad inválido. Por favor, recarga la página.';
                    header('Location: /proyecto/tecnicos/editar/' . $id);
                    exit;
                }
            }
            
            // Guardar datos del formulario para mantenerlos si hay error
            $_SESSION['old'] = [
                'nombre' => $_POST['nombre'] ?? '',
                'email' => $_POST['email'] ?? '',
                'telefono' => $_POST['telefono'] ?? '',
                'especialidad' => $_POST['especialidad'] ?? '',
                'tarifa' => $_POST['tarifa'] ?? 0,
                'estado' => $_POST['estado'] ?? 'activo'
            ];
            
            // Validar datos
            $errores = [];
            
            // Validar nombre
            $nombre = trim($_POST['nombre'] ?? '');
            if (empty($nombre)) {
                $errores['nombre'] = 'El nombre es obligatorio';
            } elseif (strlen($nombre) < 3 || strlen($nombre) > 100) {
                $errores['nombre'] = 'El nombre debe tener entre 3 y 100 caracteres';
            }
            
            // Validar email
            $email = trim($_POST['email'] ?? '');
            if (empty($email)) {
                $errores['email'] = 'El email es obligatorio';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errores['email'] = 'El email no es válido';
            }
            
            // Verificar si el email ya existe (excepto el actual)
            if (method_exists($this->model, 'emailExiste') && $this->model->emailExiste($email, $id)) {
                $errores['email'] = 'El email ya está registrado por otro usuario';
            }
            
            // Validar contraseña (opcional en edición)
            $password = $_POST['password'] ?? '';
            $confirmar_password = $_POST['confirmar_password'] ?? '';
            
            if (!empty($password) || !empty($confirmar_password)) {
                if (strlen($password) < 6) {
                    $errores['password'] = 'La contraseña debe tener al menos 6 caracteres';
                } elseif ($password !== $confirmar_password) {
                    $errores['confirmar_password'] = 'Las contraseñas no coinciden';
                }
            }
            
            // Validar especialidad
            $especialidad = trim($_POST['especialidad'] ?? '');
            if (empty($especialidad)) {
                $errores['especialidad'] = 'La especialidad es obligatoria';
            }
            
            // Validar tarifa
            $tarifa = (float)($_POST['tarifa'] ?? 0);
            if ($tarifa < 0) {
                $errores['tarifa'] = 'La tarifa no puede ser negativa';
            }
            
            // Validar teléfono (opcional)
            $telefono = trim($_POST['telefono'] ?? '');
            if (!empty($telefono) && !preg_match('/^[0-9\s\-+()]{6,15}$/', $telefono)) {
                $errores['telefono'] = 'El teléfono no es válido';
            }
            
            // Si hay errores, volver al formulario
            if (!empty($errores)) {
                $_SESSION['errores'] = $errores;
                header('Location: /proyecto/tecnicos/editar/' . $id);
                exit;
            }
            
            // Actualizar técnico
            $datos = [
                'nombre' => $nombre,
                'email' => $email,
                'especialidad' => $especialidad,
                'tarifa' => $tarifa,
                'telefono' => $telefono,
                'estado' => $_POST['estado'] ?? 'activo'
            ];
            
            // Si se proporcionó nueva contraseña
            if (!empty($password)) {
                $datos['password'] = password_hash($password, PASSWORD_DEFAULT);
            }
            
            $result = $this->model->actualizar($id, $datos);
            
            if ($result) {
                unset($_SESSION['old']);
                $_SESSION['mensaje'] = 'Técnico actualizado correctamente';
                $_SESSION['mensaje_tipo'] = 'success';
            } else {
                $_SESSION['error'] = 'Error al actualizar el técnico';
            }
            
            header('Location: /proyecto/tecnicos');
            exit;
            
        } catch (Exception $e) {
            error_log("Error en TecnicosController::actualizar: " . $e->getMessage());
            $_SESSION['error'] = 'Error al actualizar el técnico: ' . $e->getMessage();
            header('Location: /proyecto/tecnicos/editar/' . $id);
            exit;
        }
    }
    
    /**
     * Eliminar técnico
     * URL: /tecnicos/eliminar/{id} (POST)
     */
    public function eliminar($id) {
        try {
            $id = (int)$id;
            
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                header('Location: /proyecto/tecnicos');
                exit;
            }
            
            if ($id <= 0) {
                $_SESSION['error'] = 'ID de técnico inválido';
                header('Location: /proyecto/tecnicos');
                exit;
            }
            
            // Validar CSRF
            if (method_exists('SecurityHelper', 'verifyCSRFToken')) {
                $token = $_POST['csrf_token'] ?? '';
                if (!SecurityHelper::verifyCSRFToken($token)) {
                    $_SESSION['error'] = 'Token de seguridad inválido';
                    header('Location: /proyecto/tecnicos');
                    exit;
                }
            }
            
            // Verificar si tiene órdenes asignadas
            $db = Database::getInstance()->getConnection();
            $sql = "SELECT COUNT(*) as total FROM ordenes_mantenimiento WHERE tecnico_id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (($result['total'] ?? 0) > 0) {
                $_SESSION['error'] = 'No se puede eliminar el técnico porque tiene órdenes asignadas';
                header('Location: /proyecto/tecnicos');
                exit;
            }
            
            if (!method_exists($this->model, 'eliminar')) {
                $_SESSION['error'] = 'Método de eliminación no disponible';
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
            
        } catch (Exception $e) {
            error_log("Error en TecnicosController::eliminar: " . $e->getMessage());
            $_SESSION['error'] = 'Error al eliminar el técnico';
            header('Location: /proyecto/tecnicos');
            exit;
        }
    }
    
    /**
     * Cambiar estado del técnico
     * URL: /tecnicos/cambiarEstado/{id} (POST)
     */
    public function cambiarEstado($id) {
        try {
            $id = (int)$id;
            
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                header('Location: /proyecto/tecnicos');
                exit;
            }
            
            if ($id <= 0) {
                $_SESSION['error'] = 'ID de técnico inválido';
                header('Location: /proyecto/tecnicos');
                exit;
            }
            
            // Validar CSRF
            if (method_exists('SecurityHelper', 'verifyCSRFToken')) {
                $token = $_POST['csrf_token'] ?? '';
                if (!SecurityHelper::verifyCSRFToken($token)) {
                    $_SESSION['error'] = 'Token de seguridad inválido';
                    header('Location: /proyecto/tecnicos');
                    exit;
                }
            }
            
            $estado = $_POST['estado'] ?? 'activo';
            
            if (!in_array($estado, ['activo', 'inactivo'])) {
                $_SESSION['error'] = 'Estado inválido';
                header('Location: /proyecto/tecnicos');
                exit;
            }
            
            if (!method_exists($this->model, 'cambiarEstado')) {
                $_SESSION['error'] = 'Método de cambio de estado no disponible';
                header('Location: /proyecto/tecnicos');
                exit;
            }
            
            $result = $this->model->cambiarEstado($id, $estado);
            
            if ($result) {
                $_SESSION['mensaje'] = 'Estado actualizado correctamente';
                $_SESSION['mensaje_tipo'] = 'success';
            } else {
                $_SESSION['error'] = 'Error al cambiar el estado';
            }
            
            header('Location: /proyecto/tecnicos');
            exit;
            
        } catch (Exception $e) {
            error_log("Error en TecnicosController::cambiarEstado: " . $e->getMessage());
            $_SESSION['error'] = 'Error al cambiar el estado';
            header('Location: /proyecto/tecnicos');
            exit;
        }
    }
    
    /**
     * Obtener datos de técnicos para API/AJAX
     * URL: /tecnicos/api/datos
     */
    public function apiDatos() {
        try {
            header('Content-Type: application/json');
            
            $filtros = [
                'estado' => $_GET['estado'] ?? '',
                'buscar' => $_GET['buscar'] ?? '',
                'especialidad' => $_GET['especialidad'] ?? ''
            ];
            
            $tecnicos = $this->model->obtenerTodos($filtros);
            
            echo json_encode([
                'success' => true,
                'data' => $tecnicos,
                'total' => count($tecnicos)
            ]);
            
        } catch (Exception $e) {
            error_log("Error en TecnicosController::apiDatos: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
        exit;
    }
}
?>