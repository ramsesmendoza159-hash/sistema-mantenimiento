<?php
// controller/SupervisoresController.php
// Ubicación: C:\xampp\htdocs\proyecto\controller\SupervisoresController.php

require_once __DIR__ . '/../helpers/Controller.php';

class SupervisoresController extends Controller {
    
    private $db;

    public function __construct() {
        parent::__construct();
        
        // Verificar autenticación y rol admin
        if (!$this->authHelper->isLoggedIn()) {
            header('Location: /proyecto/auth/login');
            exit;
        }
        
        if (!$this->authHelper->isAdmin()) {
            $_SESSION['error'] = 'No tienes permisos para acceder a esta sección';
            header('Location: /proyecto/dashboard');
            exit;
        }
        
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Lista de supervisores
     * URL: /supervisores
     */
    public function index() {
        try {
            require_once __DIR__ . '/../model/SupervisoresModel.php';
            $model = new SupervisoresModel();  // 👈 AHORA USA SupervisoresModel
            
            // Obtener filtros de la URL
            $filtros = [
                'buscar' => $_GET['buscar'] ?? '',
                'estado' => $_GET['estado'] ?? '',
                'area' => $_GET['area'] ?? ''
            ];
            
            $supervisores = $model->obtenerTodos($filtros);
            $estadisticas = $model->obtenerEstadisticas();
            
        } catch (Exception $e) {
            error_log("Error en SupervisoresController index: " . $e->getMessage());
            $supervisores = [];
            $estadisticas = ['total' => 0, 'activos' => 0, 'inactivos' => 0];
            $_SESSION['error'] = 'Error al cargar los supervisores';
        }
        
        $this->view('supervisores/index', [
            'supervisores' => $supervisores,
            'estadisticas' => $estadisticas,
            'filtros' => $filtros ?? []
        ]);
    }

    /**
     * Formulario para crear supervisor
     * URL: /supervisores/crear
     */
    public function crear() {
        $this->view('supervisores/form', [
            'supervisor' => null,
            'accion' => 'crear'
        ]);
    }

    /**
     * Guardar nuevo supervisor
     * URL: /supervisores/guardar (POST)
     */
    public function guardar() {
        $this->requirePost();
        
        $nombre = $this->post('nombre', '');
        $email = $this->post('email', '');
        $password = $this->post('password', '');
        $area = $this->post('area', '');
        $estado = $this->post('estado', 'activo');
        
        if (empty($nombre) || empty($email) || empty($password)) {
            $_SESSION['error'] = 'Todos los campos obligatorios deben ser llenados';
            $this->redirect('/proyecto/supervisores/crear');
            return;
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'El email no es válido';
            $this->redirect('/proyecto/supervisores/crear');
            return;
        }
        
        if (strlen($password) < 6) {
            $_SESSION['error'] = 'La contraseña debe tener al menos 6 caracteres';
            $this->redirect('/proyecto/supervisores/crear');
            return;
        }
        
        try {
            require_once __DIR__ . '/../model/SupervisoresModel.php';
            $model = new SupervisoresModel();
            
            // Verificar si el email ya existe
            if ($model->emailExiste($email)) {
                $_SESSION['error'] = 'El email ya está registrado';
                $this->redirect('/proyecto/supervisores/crear');
                return;
            }
            
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            
            $datos = [
                'nombre' => $nombre,
                'email' => $email,
                'password_hash' => $passwordHash,
                'area' => $area,
                'estado' => $estado
            ];
            
            $resultado = $model->crear($datos);
            
            if ($resultado) {
                $_SESSION['success'] = 'Supervisor creado correctamente';
                $this->redirect('/proyecto/supervisores');
            } else {
                $_SESSION['error'] = 'Error al crear el supervisor';
                $this->redirect('/proyecto/supervisores/crear');
            }
            
        } catch (Exception $e) {
            error_log("Error en guardar supervisor: " . $e->getMessage());
            $_SESSION['error'] = 'Error al crear el supervisor: ' . $e->getMessage();
            $this->redirect('/proyecto/supervisores/crear');
        }
    }

    /**
     * Formulario para editar supervisor
     * URL: /supervisores/editar/{id}
     */
    public function editar($id) {
        try {
            require_once __DIR__ . '/../model/SupervisoresModel.php';
            $model = new SupervisoresModel();
            $supervisor = $model->obtenerPorId($id);
            
            if (!$supervisor) {
                $_SESSION['error'] = 'Supervisor no encontrado';
                $this->redirect('/proyecto/supervisores');
                return;
            }
            
            $this->view('supervisores/form', [
                'supervisor' => $supervisor,
                'accion' => 'editar'
            ]);
            
        } catch (Exception $e) {
            error_log("Error en editar supervisor: " . $e->getMessage());
            $_SESSION['error'] = 'Error al cargar el supervisor';
            $this->redirect('/proyecto/supervisores');
        }
    }

    /**
     * Actualizar supervisor
     * URL: /supervisores/actualizar/{id} (POST)
     */
    public function actualizar($id) {
        $this->requirePost();
        
        $nombre = $this->post('nombre', '');
        $email = $this->post('email', '');
        $password = $this->post('password', '');
        $area = $this->post('area', '');
        $estado = $this->post('estado', 'activo');
        
        if (empty($nombre) || empty($email)) {
            $_SESSION['error'] = 'Nombre y email son obligatorios';
            $this->redirect('/proyecto/supervisores/editar/' . $id);
            return;
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'El email no es válido';
            $this->redirect('/proyecto/supervisores/editar/' . $id);
            return;
        }
        
        try {
            require_once __DIR__ . '/../model/SupervisoresModel.php';
            $model = new SupervisoresModel();
            
            // Verificar si el email ya existe (excepto el actual)
            if ($model->emailExiste($email, $id)) {
                $_SESSION['error'] = 'El email ya está registrado por otro usuario';
                $this->redirect('/proyecto/supervisores/editar/' . $id);
                return;
            }
            
            $datos = [
                'nombre' => $nombre,
                'email' => $email,
                'area' => $area,
                'estado' => $estado
            ];
            
            $resultado = $model->actualizar($id, $datos);
            
            // Si se proporcionó nueva contraseña, actualizarla
            if (!empty($password)) {
                if (strlen($password) < 6) {
                    $_SESSION['error'] = 'La contraseña debe tener al menos 6 caracteres';
                    $this->redirect('/proyecto/supervisores/editar/' . $id);
                    return;
                }
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $model->actualizarPassword($id, $passwordHash);
            }
            
            if ($resultado) {
                $_SESSION['success'] = 'Supervisor actualizado correctamente';
            } else {
                $_SESSION['error'] = 'Error al actualizar el supervisor';
            }
            
        } catch (Exception $e) {
            error_log("Error en actualizar supervisor: " . $e->getMessage());
            $_SESSION['error'] = 'Error al actualizar el supervisor: ' . $e->getMessage();
        }
        
        $this->redirect('/proyecto/supervisores');
    }

    /**
     * Eliminar supervisor
     * URL: /supervisores/eliminar/{id} (POST)
     */
    public function eliminar($id) {
        $this->requirePost();
        
        try {
            require_once __DIR__ . '/../model/SupervisoresModel.php';
            $model = new SupervisoresModel();
            $resultado = $model->eliminar($id);
            
            if ($resultado) {
                $_SESSION['success'] = 'Supervisor eliminado correctamente';
            } else {
                $_SESSION['error'] = 'Error al eliminar el supervisor';
            }
            
        } catch (Exception $e) {
            error_log("Error en eliminar supervisor: " . $e->getMessage());
            $_SESSION['error'] = 'Error al eliminar el supervisor';
        }
        
        $this->redirect('/proyecto/supervisores');
    }
    
    /**
     * Cambiar estado de un supervisor
     * URL: /supervisores/cambiarEstado/{id} (POST)
     */
    public function cambiarEstado($id) {
        $this->requirePost();
        
        $estado = $this->post('estado', 'activo');
        
        try {
            require_once __DIR__ . '/../model/SupervisoresModel.php';
            $model = new SupervisoresModel();
            $resultado = $model->cambiarEstado($id, $estado);
            
            if ($resultado) {
                $_SESSION['success'] = 'Estado actualizado correctamente';
            } else {
                $_SESSION['error'] = 'Error al cambiar el estado';
            }
            
        } catch (Exception $e) {
            error_log("Error en cambiarEstado supervisor: " . $e->getMessage());
            $_SESSION['error'] = 'Error al cambiar el estado';
        }
        
        $this->redirect('/proyecto/supervisores');
    }
    
    /**
     * Cambiar contraseña de un supervisor
     * URL: /supervisores/cambiarPassword/{id} (POST)
     */
    public function cambiarPassword($id) {
        $this->requirePost();
        
        $password = $this->post('password', '');
        $confirmar = $this->post('confirmar_password', '');
        
        if ($password !== $confirmar) {
            $_SESSION['error'] = 'Las contraseñas no coinciden';
            $this->redirect('/proyecto/supervisores');
            return;
        }
        
        if (strlen($password) < 6) {
            $_SESSION['error'] = 'La contraseña debe tener al menos 6 caracteres';
            $this->redirect('/proyecto/supervisores');
            return;
        }
        
        try {
            require_once __DIR__ . '/../model/SupervisoresModel.php';
            $model = new SupervisoresModel();
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $resultado = $model->actualizarPassword($id, $passwordHash);
            
            if ($resultado) {
                $_SESSION['success'] = 'Contraseña actualizada correctamente';
            } else {
                $_SESSION['error'] = 'Error al cambiar la contraseña';
            }
            
        } catch (Exception $e) {
            error_log("Error en cambiarPassword supervisor: " . $e->getMessage());
            $_SESSION['error'] = 'Error al cambiar la contraseña';
        }
        
        $this->redirect('/proyecto/supervisores');
    }
}
?>