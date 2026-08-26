<?php
// controller/AdminController.php
// Ubicación: C:\xampp\htdocs\proyecto\controller\AdminController.php

// Incluir el controlador base
require_once __DIR__ . '/../helpers/Controller.php';

class AdminController extends Controller {
    
    private $db;

    public function __construct() {
        parent::__construct();
        
        // Verificar autenticación
        if (!$this->authHelper->isLoggedIn()) {
            header('Location: /proyecto/auth/login');
            exit;
        }
        
        // Verificar rol de administrador
        if (!$this->authHelper->isAdmin()) {
            $_SESSION['error'] = 'No tienes permisos para acceder a esta sección';
            header('Location: /proyecto/dashboard');
            exit;
        }
        
        // Obtener conexión a la base de datos
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Dashboard del administrador
     * URL: /admin/dashboard
     */
    public function dashboard() {
        try {
            // Estadísticas de órdenes
            $stmt = $this->db->query("SELECT COUNT(*) FROM ordenes_mantenimiento");
            $total_ordenes = $stmt->fetchColumn();
            
            $stmt = $this->db->query("SELECT COUNT(*) FROM ordenes_mantenimiento WHERE status = 'PENDIENTE'");
            $pendientes = $stmt->fetchColumn();
            
            $stmt = $this->db->query("SELECT COUNT(*) FROM ordenes_mantenimiento WHERE status = 'EN_PROCESO'");
            $en_proceso = $stmt->fetchColumn();
            
            $stmt = $this->db->query("SELECT COUNT(*) FROM ordenes_mantenimiento WHERE status IN ('CERRADA', 'EJECUTADA', 'APROBADA')");
            $cerradas = $stmt->fetchColumn();
            
            $stmt = $this->db->query("SELECT COUNT(*) FROM ordenes_mantenimiento WHERE status = 'CANCELADA'");
            $canceladas = $stmt->fetchColumn();
            
            // Estadísticas de técnicos
            $stmt = $this->db->query("SELECT COUNT(*) FROM tecnicos WHERE estado = 'activo'");
            $total_tecnicos = $stmt->fetchColumn();
            
            // Estadísticas de supervisores
            $stmt = $this->db->query("SELECT COUNT(*) FROM supervisores WHERE estado = 'activo'");
            $total_supervisores = $stmt->fetchColumn();
            
            // Calcular eficiencia
            $eficiencia = $total_ordenes > 0 ? round(($cerradas / $total_ordenes) * 100, 1) : 0;
            
            // Últimas órdenes
            $stmt = $this->db->prepare("SELECT * FROM ordenes_mantenimiento ORDER BY fecha_creacion DESC LIMIT 10");
            $stmt->execute();
            $ordenes_recientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error en dashboard admin: " . $e->getMessage());
            $total_ordenes = 0;
            $pendientes = 0;
            $en_proceso = 0;
            $cerradas = 0;
            $canceladas = 0;
            $total_tecnicos = 0;
            $total_supervisores = 0;
            $eficiencia = 0;
            $ordenes_recientes = [];
        }
        
        // Cargar la vista
        $this->view('admin/dashboard', [
            'total_ordenes' => $total_ordenes,
            'pendientes' => $pendientes,
            'en_proceso' => $en_proceso,
            'cerradas' => $cerradas,
            'canceladas' => $canceladas,
            'total_tecnicos' => $total_tecnicos,
            'total_supervisores' => $total_supervisores,
            'eficiencia' => $eficiencia,
            'ordenes_recientes' => $ordenes_recientes
        ]);
    }

    /**
     * Gestión de órdenes
     * URL: /admin/gestion_ordenes
     */
    public function gestion_ordenes() {
        try {
            require_once __DIR__ . '/../model/OrdenTrabajo.php';
            $ordenModel = new OrdenTrabajo();
            
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $perPage = 15;
            $offset = ($page - 1) * $perPage;
            $filtros = [];

            if (isset($_GET['estado']) && !empty($_GET['estado'])) {
                $filtros['status'] = $_GET['estado'];
            }
            if (isset($_GET['buscar']) && !empty($_GET['buscar'])) {
                $filtros['buscar'] = $_GET['buscar'];
            }

            $ordenes = $ordenModel->obtenerTodos($filtros);
            
            if (!is_array($ordenes)) {
                $ordenes = [];
            }
            
            $total = count($ordenes);
            $totalPages = ceil($total / $perPage);
            $ordenes = array_slice($ordenes, $offset, $perPage);
            
            // Obtener técnicos para el filtro
            $stmt = $this->db->query("SELECT id, nombre FROM tecnicos WHERE estado = 'activo' ORDER BY nombre");
            $tecnicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error en gestion_ordenes: " . $e->getMessage());
            $ordenes = [];
            $tecnicos = [];
            $totalPages = 0;
            $page = 1;
            $total = 0;
            $_SESSION['error'] = 'Error al cargar las órdenes: ' . $e->getMessage();
        }

        $this->view('admin/gestion_ordenes', [
            'ordenes' => $ordenes,
            'tecnicos' => $tecnicos,
            'totalPages' => $totalPages,
            'page' => $page,
            'total' => $total ?? 0,
            'rol' => $this->authHelper->getRole()  // ✅ AGREGADO
        ]);
    }

    /**
     * Gestión de técnicos
     * URL: /admin/gestion_tecnicos
     */
    public function gestion_tecnicos() {
        try {
            require_once __DIR__ . '/../model/TecnicosModel.php';
            $tecnicosModel = new TecnicosModel();
            $tecnicos = $tecnicosModel->obtenerTodos();
            
            // Verificar si el método existe antes de llamarlo
            if (method_exists($tecnicosModel, 'obtenerEstadisticas')) {
                $estadisticas = $tecnicosModel->obtenerEstadisticas();
            } else {
                $estadisticas = ['total' => count($tecnicos), 'activos' => 0, 'inactivos' => 0];
            }
            
        } catch (Exception $e) {
            error_log("Error en gestion_tecnicos: " . $e->getMessage());
            $tecnicos = [];
            $estadisticas = ['total' => 0, 'activos' => 0, 'inactivos' => 0];
            $_SESSION['error'] = 'Error al cargar los técnicos: ' . $e->getMessage();
        }

        $this->view('admin/gestion_tecnicos', [
            'tecnicos' => $tecnicos,
            'estadisticas' => $estadisticas
        ]);
    }

    /**
     * Gestión de supervisores
     * URL: /admin/gestion_supervisores
     */
    public function gestion_supervisores() {
        try {
            require_once __DIR__ . '/../model/SupervisoresModel.php';
            $supervisoresModel = new SupervisoresModel();
            $supervisores = $supervisoresModel->obtenerTodos();
            
            // Verificar si el método existe antes de llamarlo
            if (method_exists($supervisoresModel, 'obtenerEstadisticas')) {
                $estadisticas = $supervisoresModel->obtenerEstadisticas();
            } else {
                $estadisticas = ['total' => count($supervisores), 'activos' => 0, 'inactivos' => 0];
            }
            
        } catch (Exception $e) {
            error_log("Error en gestion_supervisores: " . $e->getMessage());
            $supervisores = [];
            $estadisticas = ['total' => 0, 'activos' => 0, 'inactivos' => 0];
            $_SESSION['error'] = 'Error al cargar los supervisores: ' . $e->getMessage();
        }

        $this->view('admin/gestion_supervisores', [
            'supervisores' => $supervisores,
            'estadisticas' => $estadisticas
        ]);
    }

    /**
     * Gestión de inventario
     * URL: /admin/gestion_inventario
     */
    public function gestion_inventario() {
        try {
            require_once __DIR__ . '/../model/RepuestosModel.php';
            $repuestosModel = new RepuestosModel();
            $repuestos = $repuestosModel->obtenerTodos();
            
            // Verificar si el método existe antes de llamarlo
            if (method_exists($repuestosModel, 'obtenerEstadisticas')) {
                $estadisticas = $repuestosModel->obtenerEstadisticas();
            } else {
                $estadisticas = ['total' => count($repuestos), 'total_stock' => 0, 'precio_promedio' => 0, 'valor_total' => 0];
            }
            
        } catch (Exception $e) {
            error_log("Error en gestion_inventario: " . $e->getMessage());
            $repuestos = [];
            $estadisticas = ['total' => 0, 'total_stock' => 0, 'precio_promedio' => 0, 'valor_total' => 0];
            $_SESSION['error'] = 'Error al cargar el inventario: ' . $e->getMessage();
        }

        $this->view('admin/gestion_inventario', [
            'repuestos' => $repuestos,
            'estadisticas' => $estadisticas
        ]);
    }

    /**
     * Cambiar estado de una orden
     * URL: /admin/cambiar_estado_orden
     */
    public function cambiar_estado_orden() {
        $this->requirePost();
        
        $ordenId = $this->post('orden_id');
        $nuevoEstado = $this->post('estado');
        
        if (empty($ordenId) || empty($nuevoEstado)) {
            $_SESSION['error'] = 'Datos incompletos';
            $this->redirect('/proyecto/admin/gestion_ordenes');
            return;
        }
        
        try {
            $stmt = $this->db->prepare("UPDATE ordenes_mantenimiento SET status = ? WHERE id = ?");
            $result = $stmt->execute([$nuevoEstado, $ordenId]);
            
            if ($result) {
                $_SESSION['success'] = 'Estado actualizado correctamente';
            } else {
                $_SESSION['error'] = 'Error al actualizar el estado';
            }
        } catch (PDOException $e) {
            error_log("Error en cambiar_estado_orden: " . $e->getMessage());
            $_SESSION['error'] = 'Error al actualizar el estado';
        }
        
        $this->redirect('/proyecto/admin/gestion_ordenes');
    }

    /**
     * Eliminar una orden
     * URL: /admin/eliminar_orden
     */
    public function eliminar_orden() {
        $this->requirePost();
        
        $ordenId = $this->post('orden_id');
        
        if (empty($ordenId)) {
            $_SESSION['error'] = 'ID de orden no válido';
            $this->redirect('/proyecto/admin/gestion_ordenes');
            return;
        }
        
        try {
            $stmt = $this->db->prepare("DELETE FROM ordenes_mantenimiento WHERE id = ?");
            $result = $stmt->execute([$ordenId]);
            
            if ($result) {
                $_SESSION['success'] = 'Orden eliminada correctamente';
            } else {
                $_SESSION['error'] = 'Error al eliminar la orden';
            }
        } catch (PDOException $e) {
            error_log("Error en eliminar_orden: " . $e->getMessage());
            $_SESSION['error'] = 'Error al eliminar la orden';
        }
        
        $this->redirect('/proyecto/admin/gestion_ordenes');
    }
}