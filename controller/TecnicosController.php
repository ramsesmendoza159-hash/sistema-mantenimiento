<?php
// controller/TecnicosController.php
// Ubicación: C:\xampp\htdocs\produmar\controller\TecnicosController.php

// Incluir el controlador base
require_once __DIR__ . '/../helpers/Controller.php';

class TecnicosController extends Controller {
    
    private $db;

    public function __construct() {
        parent::__construct();
        
        // Verificar autenticación
        if (!$this->authHelper->isLoggedIn()) {
            header('Location: /produmar/auth/login');
            exit;
        }
        
        // Verificar rol de administrador
        if (!$this->authHelper->isAdmin()) {
            $_SESSION['error'] = 'No tienes permisos para acceder a esta sección';
            header('Location: /produmar/dashboard');
            exit;
        }
        
        // Obtener conexión a la base de datos
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Lista de técnicos
     * URL: /tecnicos
     */
    public function index() {
        try {
            require_once __DIR__ . '/../model/TecnicosModel.php';
            $model = new TecnicosModel();
            $tecnicos = $model->obtenerTodos();
            $estadisticas = $model->obtenerEstadisticas();
            
        } catch (Exception $e) {
            error_log("Error en TecnicosController index: " . $e->getMessage());
            $tecnicos = [];
            $estadisticas = ['total' => 0, 'activos' => 0, 'inactivos' => 0];
            $_SESSION['error'] = 'Error al cargar los técnicos: ' . $e->getMessage();
        }
        
        $this->view('tecnicos/index', [
            'tecnicos' => $tecnicos,
            'estadisticas' => $estadisticas
        ]);
    }

    /**
     * Formulario para crear técnico
     * URL: /tecnicos/crear
     */
    public function crear() {
        $this->view('tecnicos/crear', [
            'tecnico' => null,
            'accion' => 'crear'
        ]);
    }

    /**
     * Guardar nuevo técnico
     * URL: /tecnicos/guardar (POST)
     */
    public function guardar() {
        $this->requirePost();
        
        $nombre = $this->post('nombre', '');
        $email = $this->post('email', '');
        $telefono = $this->post('telefono', '');
        $especialidad = $this->post('especialidad', '');
        $password = $this->post('password', '');
        $estado = $this->post('estado', 'activo');
        
        if (empty($nombre) || empty($email) || empty($password)) {
            $_SESSION['error'] = 'Nombre, email y contraseña son obligatorios';
            $this->redirect('/produmar/tecnicos/crear');
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'El email no es válido';
            $this->redirect('/produmar/tecnicos/crear');
        }
        
        if (strlen($password) < 6) {
            $_SESSION['error'] = 'La contraseña debe tener al menos 6 caracteres';
            $this->redirect('/produmar/tecnicos/crear');
        }
        
        try {
            require_once __DIR__ . '/../model/TecnicosModel.php';
            $model = new TecnicosModel();
            
            // Verificar si el email ya existe
            $existente = $model->obtenerPorEmail($email);
            if ($existente) {
                $_SESSION['error'] = 'El email ya está registrado';
                $this->redirect('/produmar/tecnicos/crear');
            }
            
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            
            $datos = [
                'nombre' => $nombre,
                'email' => $email,
                'telefono' => $telefono,
                'especialidad' => $especialidad,
                'password_hash' => $passwordHash,
                'estado' => $estado
            ];
            
            $resultado = $model->crear($datos);
            
            if ($resultado) {
                $_SESSION['success'] = 'Técnico creado correctamente';
                $this->redirect('/produmar/tecnicos');
            } else {
                $_SESSION['error'] = 'Error al crear el técnico';
                $this->redirect('/produmar/tecnicos/crear');
            }
            
        } catch (Exception $e) {
            error_log("Error en guardar tecnico: " . $e->getMessage());
            $_SESSION['error'] = 'Error al crear el técnico: ' . $e->getMessage();
            $this->redirect('/produmar/tecnicos/crear');
        }
    }

    /**
     * Formulario para editar técnico
     * URL: /tecnicos/editar/{id}
     */
    public function editar($id) {
        try {
            require_once __DIR__ . '/../model/TecnicosModel.php';
            $model = new TecnicosModel();
            $tecnico = $model->obtenerPorId($id);
            
            if (!$tecnico) {
                $_SESSION['error'] = 'Técnico no encontrado';
                $this->redirect('/produmar/tecnicos');
            }
            
            $this->view('tecnicos/editar', [
                'tecnico' => $tecnico,
                'accion' => 'editar'
            ]);
            
        } catch (Exception $e) {
            error_log("Error en editar tecnico: " . $e->getMessage());
            $_SESSION['error'] = 'Error al cargar el técnico';
            $this->redirect('/produmar/tecnicos');
        }
    }

    /**
     * Actualizar técnico
     * URL: /tecnicos/actualizar/{id} (POST)
     */
    public function actualizar($id) {
        $this->requirePost();
        
        $nombre = $this->post('nombre', '');
        $email = $this->post('email', '');
        $telefono = $this->post('telefono', '');
        $especialidad = $this->post('especialidad', '');
        $password = $this->post('password', '');
        $estado = $this->post('estado', 'activo');
        
        if (empty($nombre) || empty($email)) {
            $_SESSION['error'] = 'Nombre y email son obligatorios';
            $this->redirect('/produmar/tecnicos/editar/' . $id);
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'El email no es válido';
            $this->redirect('/produmar/tecnicos/editar/' . $id);
        }
        
        try {
            require_once __DIR__ . '/../model/TecnicosModel.php';
            $model = new TecnicosModel();
            
            // Verificar si el email ya existe (excepto el actual)
            $existente = $model->obtenerPorEmail($email);
            if ($existente && $existente['id'] != $id) {
                $_SESSION['error'] = 'El email ya está registrado por otro usuario';
                $this->redirect('/produmar/tecnicos/editar/' . $id);
            }
            
            $datos = [
                'nombre' => $nombre,
                'email' => $email,
                'telefono' => $telefono,
                'especialidad' => $especialidad,
                'estado' => $estado
            ];
            
            $resultado = $model->actualizar($id, $datos);
            
            // Si se proporcionó nueva contraseña, actualizarla
            if (!empty($password)) {
                if (strlen($password) < 6) {
                    $_SESSION['error'] = 'La contraseña debe tener al menos 6 caracteres';
                    $this->redirect('/produmar/tecnicos/editar/' . $id);
                }
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $model->actualizarPassword($id, $passwordHash);
            }
            
            if ($resultado) {
                $_SESSION['success'] = 'Técnico actualizado correctamente';
            } else {
                $_SESSION['error'] = 'Error al actualizar el técnico';
            }
            
        } catch (Exception $e) {
            error_log("Error en actualizar tecnico: " . $e->getMessage());
            $_SESSION['error'] = 'Error al actualizar el técnico: ' . $e->getMessage();
        }
        
        $this->redirect('/produmar/tecnicos');
    }

    /**
     * Eliminar técnico
     * URL: /tecnicos/eliminar/{id} (POST)
     */
    public function eliminar($id) {
        $this->requirePost();
        
        try {
            require_once __DIR__ . '/../model/TecnicosModel.php';
            $model = new TecnicosModel();
            $resultado = $model->eliminar($id);
            
            if ($resultado) {
                $_SESSION['success'] = 'Técnico eliminado correctamente';
            } else {
                $_SESSION['error'] = 'Error al eliminar el técnico';
            }
            
        } catch (Exception $e) {
            error_log("Error en eliminar tecnico: " . $e->getMessage());
            $_SESSION['error'] = 'Error al eliminar el técnico';
        }
        
        $this->redirect('/produmar/tecnicos');
    }

    /**
     * Cambiar estado del técnico
     * URL: /tecnicos/cambiar_estado/{id} (POST)
     */
    public function cambiar_estado($id) {
        $this->requirePost();
        
        $estado = $this->post('estado', 'activo');
        
        try {
            require_once __DIR__ . '/../model/TecnicosModel.php';
            $model = new TecnicosModel();
            $resultado = $model->cambiarEstado($id, $estado);
            
            if ($resultado) {
                $_SESSION['success'] = 'Estado del técnico actualizado correctamente';
            } else {
                $_SESSION['error'] = 'Error al cambiar el estado del técnico';
            }
            
        } catch (Exception $e) {
            error_log("Error en cambiar_estado tecnico: " . $e->getMessage());
            $_SESSION['error'] = 'Error al cambiar el estado del técnico';
        }
        
        $this->redirect('/produmar/tecnicos');
    }
}