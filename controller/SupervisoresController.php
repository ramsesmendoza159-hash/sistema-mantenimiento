<?php
// controller/SupervisoresController.php

require_once __DIR__ . '/../helpers/Controller.php';

class SupervisoresController extends Controller {
    
    private $db;

    public function __construct() {
        parent::__construct();
        
        // Verificar autenticación y rol admin
        if (!$this->authHelper->isLoggedIn()) {
            header('Location: /produmar/auth/login');
            exit;
        }
        
        if (!$this->authHelper->isAdmin()) {
            $_SESSION['error'] = 'No tienes permisos para acceder a esta sección';
            header('Location: /produmar/dashboard');
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
            $model = new SupervisoresModel();
            $supervisores = $model->obtenerTodos();
            $estadisticas = $model->obtenerEstadisticas();
            
        } catch (Exception $e) {
            error_log("Error en SupervisoresController index: " . $e->getMessage());
            $supervisores = [];
            $estadisticas = ['total' => 0, 'activos' => 0, 'inactivos' => 0];
            $_SESSION['error'] = 'Error al cargar los supervisores';
        }
        
        $this->view('supervisores/index', [
            'supervisores' => $supervisores,
            'estadisticas' => $estadisticas
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
            $this->redirect('/produmar/supervisores/crear');
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'El email no es válido';
            $this->redirect('/produmar/supervisores/crear');
        }
        
        if (strlen($password) < 6) {
            $_SESSION['error'] = 'La contraseña debe tener al menos 6 caracteres';
            $this->redirect('/produmar/supervisores/crear');
        }
        
        try {
            require_once __DIR__ . '/../model/SupervisoresModel.php';
            $model = new SupervisoresModel();
            
            // Verificar si el email ya existe
            $existente = $model->obtenerPorEmail($email);
            if ($existente) {
                $_SESSION['error'] = 'El email ya está registrado';
                $this->redirect('/produmar/supervisores/crear');
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
                $this->redirect('/produmar/supervisores');
            } else {
                $_SESSION['error'] = 'Error al crear el supervisor';
                $this->redirect('/produmar/supervisores/crear');
            }
            
        } catch (Exception $e) {
            error_log("Error en guardar supervisor: " . $e->getMessage());
            $_SESSION['error'] = 'Error al crear el supervisor: ' . $e->getMessage();
            $this->redirect('/produmar/supervisores/crear');
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
                $this->redirect('/produmar/supervisores');
            }
            
            $this->view('supervisores/form', [
                'supervisor' => $supervisor,
                'accion' => 'editar'
            ]);
            
        } catch (Exception $e) {
            error_log("Error en editar supervisor: " . $e->getMessage());
            $_SESSION['error'] = 'Error al cargar el supervisor';
            $this->redirect('/produmar/supervisores');
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
            $this->redirect('/produmar/supervisores/editar/' . $id);
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'El email no es válido';
            $this->redirect('/produmar/supervisores/editar/' . $id);
        }
        
        try {
            require_once __DIR__ . '/../model/SupervisoresModel.php';
            $model = new SupervisoresModel();
            
            // Verificar si el email ya existe (excepto el actual)
            $existente = $model->obtenerPorEmail($email);
            if ($existente && $existente['id'] != $id) {
                $_SESSION['error'] = 'El email ya está registrado por otro usuario';
                $this->redirect('/produmar/supervisores/editar/' . $id);
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
                    $this->redirect('/produmar/supervisores/editar/' . $id);
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
        
        $this->redirect('/produmar/supervisores');
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
        
        $this->redirect('/produmar/supervisores');
    }
}