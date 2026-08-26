<?php
// controller/TecnicoController.php
// Ubicación: C:\xampp\htdocs\proyecto\controller\TecnicoController.php

require_once __DIR__ . '/../helpers/Controller.php';

class TecnicoController extends Controller {
    
    private $db;

    public function __construct() {
        parent::__construct();
        
        // Verificar autenticación
        if (!$this->authHelper->isLoggedIn()) {
            header('Location: /proyecto/auth/login');
            exit;
        }
        
        // Verificar rol de técnico
        if (!$this->authHelper->isTecnico()) {
            $_SESSION['error'] = 'No tienes permisos para acceder a esta sección';
            header('Location: /proyecto/dashboard');
            exit;
        }
        
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Dashboard del técnico
     * URL: /tecnico
     */
    public function index() {
        $this->view('tecnico/index');
    }

    /**
     * Obtener datos para el dashboard (AJAX)
     * URL: /tecnico/dashboardData
     */
    public function dashboardData() {
        try {
            $userId = $this->authHelper->getUserId();
            
            // Total de órdenes asignadas
            $sql = "SELECT COUNT(*) as total FROM ordenes_mantenimiento WHERE tecnico_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);
            $total = $stmt->fetchColumn() ?: 0;
            
            // Pendientes
            $sql = "SELECT COUNT(*) as total FROM ordenes_mantenimiento WHERE tecnico_id = ? AND status = 'PENDIENTE'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);
            $pendientes = $stmt->fetchColumn() ?: 0;
            
            // En Progreso
            $sql = "SELECT COUNT(*) as total FROM ordenes_mantenimiento WHERE tecnico_id = ? AND status = 'EN_PROCESO'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);
            $en_progreso = $stmt->fetchColumn() ?: 0;
            
            // Completadas
            $sql = "SELECT COUNT(*) as total FROM ordenes_mantenimiento WHERE tecnico_id = ? AND status IN ('CERRADA', 'APROBADA')";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);
            $completadas = $stmt->fetchColumn() ?: 0;
            
            // Últimas órdenes
            $sql = "SELECT om.id, om.titulo, om.prioridad, om.status as estado, om.fecha_creacion,
                           a.nombre_area as area
                    FROM ordenes_mantenimiento om
                    LEFT JOIN areas a ON om.id_area = a.id_area
                    WHERE om.tecnico_id = ?
                    ORDER BY om.fecha_creacion DESC
                    LIMIT 10";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);
            $ordenes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $this->jsonResponse([
                'total' => $total,
                'pendientes' => $pendientes,
                'en_progreso' => $en_progreso,
                'completadas' => $completadas,
                'ordenes' => $ordenes
            ]);
            
        } catch (Exception $e) {
            error_log("Error en dashboardData: " . $e->getMessage());
            $this->jsonResponse([
                'total' => 0,
                'pendientes' => 0,
                'en_progreso' => 0,
                'completadas' => 0,
                'ordenes' => []
            ]);
        }
    }

    /**
     * Mis órdenes
     * URL: /tecnico/mis_ordenes
     */
    public function mis_ordenes() {
        $this->view('tecnico/mis_ordenes');
    }

    /**
     * Lista de mis órdenes (AJAX)
     * URL: /tecnico/mis_ordenes_list
     */
    public function mis_ordenes_list() {
        try {
            $userId = $this->authHelper->getUserId();
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            $offset = ($page - 1) * $limit;
            
            $sql = "SELECT om.*, a.nombre_area as area
                    FROM ordenes_mantenimiento om
                    LEFT JOIN areas a ON om.id_area = a.id_area
                    WHERE om.tecnico_id = ?";
            $params = [$userId];
            
            if (!empty($_GET['estado'])) {
                $sql .= " AND om.status = ?";
                $params[] = $_GET['estado'];
            }
            
            if (!empty($_GET['prioridad'])) {
                $sql .= " AND om.prioridad = ?";
                $params[] = $_GET['prioridad'];
            }
            
            if (!empty($_GET['fecha'])) {
                $sql .= " AND DATE(om.fecha_creacion) = ?";
                $params[] = $_GET['fecha'];
            }
            
            $sql .= " ORDER BY om.fecha_creacion DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $ordenes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Total
            $sqlCount = "SELECT COUNT(*) as total FROM ordenes_mantenimiento WHERE tecnico_id = ?";
            $stmtCount = $this->db->prepare($sqlCount);
            $stmtCount->execute([$userId]);
            $total = $stmtCount->fetchColumn() ?: 0;
            
            $this->jsonResponse([
                'ordenes' => $ordenes,
                'total' => $total,
                'paginas' => ceil($total / $limit)
            ]);
            
        } catch (Exception $e) {
            error_log("Error en mis_ordenes_list: " . $e->getMessage());
            $this->jsonResponse(['ordenes' => [], 'total' => 0, 'paginas' => 0]);
        }
    }

    /**
     * Detalle de una orden
     * URL: /tecnico/detalle_orden/{id}
     */
    public function detalle_orden($id) {
        try {
            $userId = $this->authHelper->getUserId();
            
            $sql = "SELECT om.*, a.nombre_area as area, t.nombre as tecnico
                    FROM ordenes_mantenimiento om
                    LEFT JOIN areas a ON om.id_area = a.id_area
                    LEFT JOIN tecnicos t ON om.tecnico_id = t.id
                    WHERE om.id = ? AND om.tecnico_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id, $userId]);
            $orden = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$orden) {
                $_SESSION['error'] = 'Orden no encontrada o no te pertenece';
                $this->redirect('/proyecto/tecnico/mis_ordenes');
                return;
            }
            
            $this->view('tecnico/detalle_orden', ['orden' => $orden]);
            
        } catch (Exception $e) {
            error_log("Error en detalle_orden: " . $e->getMessage());
            $_SESSION['error'] = 'Error al cargar la orden';
            $this->redirect('/proyecto/tecnico/mis_ordenes');
        }
    }

    /**
     * Formulario para cerrar una orden
     * URL: /tecnico/cerrar_orden/{id}
     */
    public function cerrar_orden($id) {
        try {
            $userId = $this->authHelper->getUserId();
            
            $sql = "SELECT * FROM ordenes_mantenimiento WHERE id = ? AND tecnico_id = ? 
                    AND status IN ('PENDIENTE', 'EN_PROCESO')";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id, $userId]);
            $orden = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$orden) {
                $_SESSION['error'] = 'Orden no encontrada o no se puede cerrar';
                $this->redirect('/proyecto/tecnico/mis_ordenes');
                return;
            }
            
            $this->view('tecnico/cerrar_orden', ['orden' => $orden]);
            
        } catch (Exception $e) {
            error_log("Error en cerrar_orden: " . $e->getMessage());
            $_SESSION['error'] = 'Error al cargar la orden';
            $this->redirect('/proyecto/tecnico/mis_ordenes');
        }
    }

    /**
     * Procesar cierre de una orden
     * URL: /tecnico/cerrar/{id} (POST)
     */
    public function cerrar($id) {
        $this->requirePost();
        
        $userId = $this->authHelper->getUserId();
        $descripcion = $this->post('descripcion_cierre', '');
        $tiempo = $this->post('tiempo_invertido', 0);
        $repuestos = $this->post('repuestos_utilizados', '');
        $satisfactorio = $this->post('satisfactorio', 0) ? 1 : 0;
        
        if (empty($descripcion)) {
            $_SESSION['error'] = 'La descripción del trabajo es obligatoria';
            $this->redirect('/proyecto/tecnico/cerrar_orden/' . $id);
            return;
        }
        
        if ($tiempo <= 0) {
            $_SESSION['error'] = 'El tiempo invertido debe ser mayor a 0';
            $this->redirect('/proyecto/tecnico/cerrar_orden/' . $id);
            return;
        }
        
        try {
            // Verificar que la orden existe y pertenece al técnico
            $sql = "SELECT id FROM ordenes_mantenimiento WHERE id = ? AND tecnico_id = ? 
                    AND status IN ('PENDIENTE', 'EN_PROCESO')";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id, $userId]);
            if (!$stmt->fetch()) {
                $_SESSION['error'] = 'Orden no encontrada o no se puede cerrar';
                $this->redirect('/proyecto/tecnico/mis_ordenes');
                return;
            }
            
            // Actualizar la orden
            $sql = "UPDATE ordenes_mantenimiento SET 
                        descripcion_cierre = ?,
                        tiempo_invertido = ?,
                        repuestos_utilizados = ?,
                        satisfactorio = ?,
                        status = 'CERRADA',
                        fecha_cierre = NOW()
                    WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$descripcion, $tiempo, $repuestos, $satisfactorio, $id]);
            
            if ($result) {
                $_SESSION['success'] = 'Orden cerrada correctamente. Esperando supervisión.';
                $this->redirect('/proyecto/tecnico/mis_ordenes');
            } else {
                $_SESSION['error'] = 'Error al cerrar la orden';
                $this->redirect('/proyecto/tecnico/cerrar_orden/' . $id);
            }
            
        } catch (Exception $e) {
            error_log("Error en cerrar: " . $e->getMessage());
            $_SESSION['error'] = 'Error al cerrar la orden: ' . $e->getMessage();
            $this->redirect('/proyecto/tecnico/cerrar_orden/' . $id);
        }
    }
}