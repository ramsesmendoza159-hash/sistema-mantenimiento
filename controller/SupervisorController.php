<?php
// controller/SupervisorController.php
// Ubicación: C:\xampp\htdocs\proyecto\controller\SupervisorController.php

require_once __DIR__ . '/../helpers/Controller.php';

class SupervisorController extends Controller {
    
    private $db;

    public function __construct() {
        parent::__construct();
        
        // Verificar autenticación
        if (!$this->authHelper->isLoggedIn()) {
            header('Location: /proyecto/auth/login');
            exit;
        }
        
        // Verificar rol de supervisor
        if (!$this->authHelper->isSupervisor()) {
            $_SESSION['error'] = 'No tienes permisos para acceder a esta sección';
            header('Location: /proyecto/dashboard');
            exit;
        }
        
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Dashboard del supervisor
     * URL: /supervisor
     */
    public function index() {
        $this->view('supervisor/index');
    }

    /**
     * Obtener datos para el dashboard (AJAX)
     * URL: /supervisor/dashboardData
     */
    public function dashboardData() {
        try {
            $userId = $this->authHelper->getUserId();
            
            // Total de órdenes asignadas a técnicos bajo su supervisión
            $sql = "SELECT COUNT(*) as total 
                    FROM ordenes_mantenimiento om
                    JOIN tecnicos t ON om.tecnico_id = t.id
                    JOIN supervisores s ON t.supervisor_id = s.id
                    WHERE s.id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);
            $total = $stmt->fetchColumn() ?: 0;
            
            // Pendientes de revisión (cerradas sin supervisar)
            $sql = "SELECT COUNT(*) as total 
                    FROM ordenes_mantenimiento om
                    JOIN tecnicos t ON om.tecnico_id = t.id
                    JOIN supervisores s ON t.supervisor_id = s.id
                    WHERE s.id = ? AND om.status = 'CERRADA' 
                    AND NOT EXISTS (SELECT 1 FROM supervision sup WHERE sup.orden_id = om.id)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);
            $pendientes = $stmt->fetchColumn() ?: 0;
            
            // Aprobadas
            $sql = "SELECT COUNT(*) as total 
                    FROM supervision sup
                    JOIN ordenes_mantenimiento om ON sup.orden_id = om.id
                    WHERE sup.supervisor_id = ? AND sup.estado = 'APROBADA'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);
            $aprobadas = $stmt->fetchColumn() ?: 0;
            
            // Rechazadas
            $sql = "SELECT COUNT(*) as total 
                    FROM supervision sup
                    JOIN ordenes_mantenimiento om ON sup.orden_id = om.id
                    WHERE sup.supervisor_id = ? AND sup.estado = 'RECHAZADA'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);
            $rechazadas = $stmt->fetchColumn() ?: 0;
            
            // Órdenes pendientes de revisión (últimas 10)
            $sql = "SELECT om.id, om.titulo, om.prioridad, om.fecha_creacion, om.fecha_cierre,
                           t.nombre as tecnico
                    FROM ordenes_mantenimiento om
                    JOIN tecnicos t ON om.tecnico_id = t.id
                    JOIN supervisores s ON t.supervisor_id = s.id
                    WHERE s.id = ? AND om.status = 'CERRADA' 
                    AND NOT EXISTS (SELECT 1 FROM supervision sup WHERE sup.orden_id = om.id)
                    ORDER BY om.fecha_cierre DESC
                    LIMIT 10";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);
            $ordenes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $this->jsonResponse([
                'total_ordenes' => $total,
                'pendientes_revision' => $pendientes,
                'aprobadas' => $aprobadas,
                'rechazadas' => $rechazadas,
                'ordenes' => $ordenes
            ]);
            
        } catch (Exception $e) {
            error_log("Error en dashboardData: " . $e->getMessage());
            $this->jsonResponse([
                'total_ordenes' => 0,
                'pendientes_revision' => 0,
                'aprobadas' => 0,
                'rechazadas' => 0,
                'ordenes' => []
            ]);
        }
    }

    /**
     * Lista de órdenes del supervisor
     * URL: /supervisor/ordenes
     */
    public function ordenes() {
        $this->view('supervisor/ordenes');
    }

    /**
     * Lista de órdenes para AJAX
     * URL: /supervisor/ordenesList
     */
    public function ordenesList() {
        try {
            $userId = $this->authHelper->getUserId();
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            $offset = ($page - 1) * $limit;
            
            $sql = "SELECT om.*, t.nombre as tecnico,
                           (SELECT estado FROM supervision WHERE orden_id = om.id LIMIT 1) as supervision_estado
                    FROM ordenes_mantenimiento om
                    JOIN tecnicos t ON om.tecnico_id = t.id
                    JOIN supervisores s ON t.supervisor_id = s.id
                    WHERE s.id = ?";
            $params = [$userId];
            
            if (!empty($_GET['estado'])) {
                $sql .= " AND om.status = ?";
                $params[] = $_GET['estado'];
            }
            
            if (!empty($_GET['prioridad'])) {
                $sql .= " AND om.prioridad = ?";
                $params[] = $_GET['prioridad'];
            }
            
            if (!empty($_GET['tecnico'])) {
                $sql .= " AND om.tecnico_id = ?";
                $params[] = $_GET['tecnico'];
            }
            
            $sql .= " ORDER BY om.fecha_creacion DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $ordenes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Total
            $sqlCount = "SELECT COUNT(*) as total 
                         FROM ordenes_mantenimiento om
                         JOIN tecnicos t ON om.tecnico_id = t.id
                         JOIN supervisores s ON t.supervisor_id = s.id
                         WHERE s.id = ?";
            $stmtCount = $this->db->prepare($sqlCount);
            $stmtCount->execute([$userId]);
            $total = $stmtCount->fetchColumn() ?: 0;
            
            $this->jsonResponse([
                'ordenes' => $ordenes,
                'total' => $total,
                'paginas' => ceil($total / $limit)
            ]);
            
        } catch (Exception $e) {
            error_log("Error en ordenesList: " . $e->getMessage());
            $this->jsonResponse(['ordenes' => [], 'total' => 0, 'paginas' => 0]);
        }
    }

    /**
     * Revisar una orden
     * URL: /supervisor/revisar/{id}
     */
    public function revisar($id) {
        try {
            $sql = "SELECT om.*, t.nombre as tecnico 
                    FROM ordenes_mantenimiento om
                    LEFT JOIN tecnicos t ON om.tecnico_id = t.id
                    WHERE om.id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            $orden = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$orden) {
                $_SESSION['error'] = 'Orden no encontrada';
                $this->redirect('/proyecto/supervisor/ordenes');
            }
            
            $this->view('supervisor/revisar', ['orden' => $orden]);
            
        } catch (Exception $e) {
            error_log("Error en revisar: " . $e->getMessage());
            $_SESSION['error'] = 'Error al cargar la orden';
            $this->redirect('/proyecto/supervisor/ordenes');
        }
    }

    /**
     * Guardar revisión de una orden
     * URL: /supervisor/guardar_revision (POST)
     */
    public function guardar_revision() {
        $this->requirePost();
        
        $ordenId = $this->post('orden_id', 0);
        $calificacion = $this->post('calificacion', 0);
        $estado = $this->post('estado', '');
        $observaciones = $this->post('observaciones', '');
        $cumple = $this->post('cumple', 0) ? 1 : 0;
        $userId = $this->authHelper->getUserId();
        
        if (empty($ordenId) || empty($estado) || empty($calificacion)) {
            $_SESSION['error'] = 'Todos los campos son obligatorios';
            $this->redirect('/proyecto/supervisor/revisar/' . $ordenId);
            return;
        }
        
        try {
            // Verificar que la orden existe y está cerrada
            $sql = "SELECT id FROM ordenes_mantenimiento WHERE id = ? AND status = 'CERRADA'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$ordenId]);
            if (!$stmt->fetch()) {
                $_SESSION['error'] = 'La orden no está cerrada o no existe';
                $this->redirect('/proyecto/supervisor/ordenes');
                return;
            }
            
            // Verificar que no haya supervisión previa
            $sql = "SELECT id FROM supervision WHERE orden_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$ordenId]);
            if ($stmt->fetch()) {
                $_SESSION['error'] = 'Esta orden ya fue supervisada';
                $this->redirect('/proyecto/supervisor/ordenes');
                return;
            }
            
            // Guardar supervisión
            $sql = "INSERT INTO supervision (orden_id, supervisor_id, calificacion, estado, observaciones, cumple, fecha_creacion) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW())";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$ordenId, $userId, $calificacion, $estado, $observaciones, $cumple]);
            
            if ($result) {
                // Actualizar estado de la orden
                $sql = "UPDATE ordenes_mantenimiento SET status = ? WHERE id = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$estado === 'APROBADA' ? 'APROBADA' : 'RECHAZADA', $ordenId]);
                
                $_SESSION['success'] = 'Revisión guardada correctamente';
                $this->redirect('/proyecto/supervisor/ordenes');
            } else {
                $_SESSION['error'] = 'Error al guardar la revisión';
                $this->redirect('/proyecto/supervisor/revisar/' . $ordenId);
            }
            
        } catch (Exception $e) {
            error_log("Error en guardar_revision: " . $e->getMessage());
            $_SESSION['error'] = 'Error al guardar la revisión: ' . $e->getMessage();
            $this->redirect('/proyecto/supervisor/revisar/' . $ordenId);
        }
    }

    /**
     * Lista de supervisiones del supervisor
     * URL: /supervisor/supervisiones
     */
    public function supervisiones() {
        $this->view('supervisor/supervisiones');
    }

    /**
     * Lista de supervisiones para AJAX
     * URL: /supervisor/supervisionesList
     */
    public function supervisionesList() {
        try {
            $userId = $this->authHelper->getUserId();
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            $offset = ($page - 1) * $limit;
            
            $sql = "SELECT sup.*, om.titulo as orden_titulo, t.nombre as tecnico
                    FROM supervision sup
                    JOIN ordenes_mantenimiento om ON sup.orden_id = om.id
                    LEFT JOIN tecnicos t ON om.tecnico_id = t.id
                    WHERE sup.supervisor_id = ?";
            $params = [$userId];
            
            if (!empty($_GET['estado'])) {
                $sql .= " AND sup.estado = ?";
                $params[] = $_GET['estado'];
            }
            
            if (!empty($_GET['calificacion'])) {
                $sql .= " AND sup.calificacion = ?";
                $params[] = $_GET['calificacion'];
            }
            
            $sql .= " ORDER BY sup.fecha_creacion DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $supervisiones = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Total
            $sqlCount = "SELECT COUNT(*) as total FROM supervision WHERE supervisor_id = ?";
            $stmtCount = $this->db->prepare($sqlCount);
            $stmtCount->execute([$userId]);
            $total = $stmtCount->fetchColumn() ?: 0;
            
            $this->jsonResponse([
                'supervisiones' => $supervisiones,
                'total' => $total,
                'paginas' => ceil($total / $limit)
            ]);
            
        } catch (Exception $e) {
            error_log("Error en supervisionesList: " . $e->getMessage());
            $this->jsonResponse(['supervisiones' => [], 'total' => 0, 'paginas' => 0]);
        }
    }

    /**
     * Ver detalle de una orden
     * URL: /supervisor/ver_orden/{id}
     */
    public function ver_orden($id) {
        try {
            $sql = "SELECT om.*, t.nombre as tecnico,
                           (SELECT estado FROM supervision WHERE orden_id = om.id LIMIT 1) as supervision_estado
                    FROM ordenes_mantenimiento om
                    LEFT JOIN tecnicos t ON om.tecnico_id = t.id
                    WHERE om.id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            $orden = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$orden) {
                $_SESSION['error'] = 'Orden no encontrada';
                $this->redirect('/proyecto/supervisor/ordenes');
                return;
            }
            
            // Obtener datos de supervisión si existe
            $sql = "SELECT * FROM supervision WHERE orden_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            $supervision = $stmt->fetch(PDO::FETCH_ASSOC);
            $orden['supervision'] = $supervision;
            
            $this->view('supervisor/ver_orden', ['orden' => $orden]);
            
        } catch (Exception $e) {
            error_log("Error en ver_orden: " . $e->getMessage());
            $_SESSION['error'] = 'Error al cargar la orden';
            $this->redirect('/proyecto/supervisor/ordenes');
        }
    }

    /**
     * Ver detalle de una supervisión
     * URL: /supervisor/ver_supervision/{id}
     */
    public function ver_supervision($id) {
        try {
            $sql = "SELECT sup.*, om.titulo as orden_titulo, om.area, t.nombre as tecnico
                    FROM supervision sup
                    JOIN ordenes_mantenimiento om ON sup.orden_id = om.id
                    LEFT JOIN tecnicos t ON om.tecnico_id = t.id
                    WHERE sup.id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            $supervision = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$supervision) {
                $_SESSION['error'] = 'Supervisión no encontrada';
                $this->redirect('/proyecto/supervisor/supervisiones');
                return;
            }
            
            // Obtener datos de la orden
            $sql = "SELECT * FROM ordenes_mantenimiento WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$supervision['orden_id']]);
            $orden = $stmt->fetch(PDO::FETCH_ASSOC);
            $supervision['orden'] = $orden;
            
            $this->view('supervisor/ver_supervision', ['supervision' => $supervision]);
            
        } catch (Exception $e) {
            error_log("Error en ver_supervision: " . $e->getMessage());
            $_SESSION['error'] = 'Error al cargar la supervisión';
            $this->redirect('/proyecto/supervisor/supervisiones');
        }
    }

    /**
     * Obtener lista de técnicos para filtros (AJAX)
     * URL: /supervisor/tecnicosList
     */
    public function tecnicosList() {
        try {
            $userId = $this->authHelper->getUserId();
            
            $sql = "SELECT t.id, t.nombre 
                    FROM tecnicos t
                    JOIN supervisores s ON t.supervisor_id = s.id
                    WHERE s.id = ? AND t.estado = 'activo'
                    ORDER BY t.nombre ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);
            $tecnicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $this->jsonResponse($tecnicos);
            
        } catch (Exception $e) {
            error_log("Error en tecnicosList: " . $e->getMessage());
            $this->jsonResponse([]);
        }
    }
}