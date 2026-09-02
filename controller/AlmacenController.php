<?php
// controller/AlmacenController.php
// Panel de Almacén - VERSIÓN COMPLETA

require_once __DIR__ . '/../model/InventarioModel.php';
require_once __DIR__ . '/../helpers/AuthHelper.php';

class AlmacenController {
    
    private $inventarioModel;
    private $authHelper;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $this->authHelper = new AuthHelper();
        
        // Verificar que el usuario sea almacén
        if (!$this->authHelper->isLoggedIn() || !$this->authHelper->isAlmacen()) {
            header('Location: /proyecto/auth/login');
            exit();
        }
        
        $this->inventarioModel = new InventarioModel();
    }

    /**
     * Dashboard del almacén
     * URL: /almacen
     */
    public function index() {
        // Estadísticas de inventario
        $estadisticas = $this->inventarioModel->obtenerEstadisticas();
        
        // Productos con stock bajo
        $stock_bajo = $this->inventarioModel->obtenerStockBajo();
        
        // Últimos movimientos
        $ultimos_movimientos = $this->inventarioModel->obtenerUltimosMovimientos(10);
        
        $titulo = "Panel de Almacén";
        $seccion = "almacen";
        require_once __DIR__ . '/../views/almacen/index.php';
    }

    /**
     * Obtener datos del dashboard (AJAX)
     * URL: /almacen/dashboardData
     */
    public function dashboardData() {
        try {
            $estadisticas = $this->inventarioModel->obtenerEstadisticas();
            $stock_bajo = $this->inventarioModel->obtenerStockBajo();
            
            $this->jsonResponse([
                'total_items' => $estadisticas['total'] ?? 0,
                'stock_bajo' => count($stock_bajo),
                'alertas' => $stock_bajo
            ]);
            
        } catch (Exception $e) {
            error_log("Error en dashboardData: " . $e->getMessage());
            $this->jsonResponse(['total_items' => 0, 'stock_bajo' => 0, 'alertas' => []]);
        }
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