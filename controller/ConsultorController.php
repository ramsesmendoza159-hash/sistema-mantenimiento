<?php
// controller/ConsultorController.php
// Panel de Consultor - VERSIÓN COMPLETA

require_once __DIR__ . '/../model/OrdenTrabajo.php';
require_once __DIR__ . '/../model/Tecnico.php';
require_once __DIR__ . '/../model/Supervisor.php';
require_once __DIR__ . '/../helpers/AuthHelper.php';

class ConsultorController {
    
    private $ordenModel;
    private $authHelper;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $this->authHelper = new AuthHelper();
        
        // Verificar que el usuario sea consultor
        if (!$this->authHelper->isLoggedIn() || !$this->authHelper->isConsultor()) {
            header('Location: /proyecto/auth/login');
            exit();
        }
        
        $this->ordenModel = new OrdenTrabajo();
    }

    /**
     * Dashboard del consultor
     * URL: /consultor
     */
    public function index() {
        // Estadísticas generales
        $estadisticas = $this->ordenModel->obtenerEstadisticas();
        
        // Últimas órdenes
        $ordenes_recientes = $this->ordenModel->obtenerTodos([], 10, 0);
        
        $titulo = "Panel de Consultor";
        $seccion = "consultor";
        require_once __DIR__ . '/../views/consultor/index.php';
    }

    /**
     * Lista de órdenes (solo lectura)
     * URL: /consultor/ordenes
     */
    public function ordenes() {
        $ordenes = $this->ordenModel->obtenerTodos();
        
        $titulo = "Órdenes de Trabajo";
        $seccion = "consultor";
        require_once __DIR__ . '/../views/consultor/ordenes.php';
    }

    /**
     * Ver detalle de orden (solo lectura)
     * URL: /consultor/ver_orden/{id}
     */
    public function ver_orden($id) {
        $orden = $this->ordenModel->obtenerPorId($id);
        
        if (!$orden) {
            $_SESSION['error'] = 'Orden no encontrada';
            header('Location: /proyecto/consultor/ordenes');
            exit();
        }
        
        $titulo = "Detalle de Orden";
        $seccion = "consultor";
        require_once __DIR__ . '/../views/consultor/ver_orden.php';
    }
}
?>