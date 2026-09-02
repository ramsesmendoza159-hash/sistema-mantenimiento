<?php
// controller/OperadorController.php
// Panel de Operador - VERSIÓN COMPLETA

require_once __DIR__ . '/../model/OrdenTrabajo.php';
require_once __DIR__ . '/../model/Tecnico.php';
require_once __DIR__ . '/../model/Supervisor.php';
require_once __DIR__ . '/../model/PlantasModel.php';
require_once __DIR__ . '/../model/AreasModel.php';
require_once __DIR__ . '/../model/EquiposModel.php';
require_once __DIR__ . '/../model/ComponentesModel.php';
require_once __DIR__ . '/../helpers/AuthHelper.php';
require_once __DIR__ . '/../helpers/SecurityHelper.php';

class OperadorController {
    
    private $ordenModel;
    private $authHelper;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $this->authHelper = new AuthHelper();
        
        // Verificar que el usuario sea operador
        if (!$this->authHelper->isLoggedIn() || !$this->authHelper->isOperador()) {
            header('Location: /proyecto/auth/login');
            exit();
        }
        
        $this->ordenModel = new OrdenTrabajo();
    }

    /**
     * Dashboard del operador
     * URL: /operador
     */
    public function index() {
        $usuarioId = $this->authHelper->getUserId();
        
        // Órdenes creadas por el operador
        $filtros = ['creado_por' => $usuarioId];
        $ordenes = $this->ordenModel->obtenerTodos($filtros);
        $estadisticas = $this->calcularEstadisticas($ordenes);
        
        $titulo = "Panel de Operador";
        $seccion = "operador";
        require_once __DIR__ . '/../views/operador/index.php';
    }

    /**
     * Lista de órdenes del operador
     * URL: /operador/ordenes
     */
    public function ordenes() {
        $usuarioId = $this->authHelper->getUserId();
        $ordenes = $this->ordenModel->obtenerTodos(['creado_por' => $usuarioId]);
        
        $titulo = "Mis Órdenes";
        $seccion = "operador";
        require_once __DIR__ . '/../views/operador/ordenes.php';
    }

    /**
     * Crear orden (Operador)
     * URL: /ordenes/crear
     */
    public function crear() {
        if (!$this->authHelper->puedeCrearOrden()) {
            $_SESSION['error'] = 'No tienes permisos para crear órdenes';
            header('Location: /proyecto/dashboard');
            exit;
        }
        
        $tecnicos = (new Tecnico())->obtenerTodos(['estado' => 'activo']);
        $supervisores = (new Supervisor())->obtenerTodos(['estado' => 'activo']);
        $plantas = (new PlantasModel())->obtenerTodos();
        $areas = (new AreasModel())->obtenerTodos();
        $equipos = (new EquiposModel())->obtenerTodos();
        $componentes = (new ComponentesModel())->obtenerTodos();
        
        $titulo = 'Crear Orden';
        $seccion = 'operador';
        require_once __DIR__ . '/../views/ordenes/crear.php';
    }

    /**
     * Calcular estadísticas del operador
     */
    private function calcularEstadisticas($ordenes) {
        $total = count($ordenes);
        $pendientes = 0;
        $en_proceso = 0;
        $completadas = 0;
        
        foreach ($ordenes as $orden) {
            $status = $orden['status'] ?? 'PENDIENTE';
            if ($status === 'PENDIENTE') {
                $pendientes++;
            } elseif ($status === 'EN_PROCESO') {
                $en_proceso++;
            } elseif ($status === 'CERRADA' || $status === 'APROBADA') {
                $completadas++;
            }
        }
        
        return [
            'total' => $total,
            'pendientes' => $pendientes,
            'en_proceso' => $en_proceso,
            'completadas' => $completadas
        ];
    }

    /**
     * Responder con JSON
     */
    private function jsonResponse($data, $status = 200) {
        header('Content-Type: application/json');
        http_response_code($status);
        echo json_encode($data);
        exit();
    }
}
?>