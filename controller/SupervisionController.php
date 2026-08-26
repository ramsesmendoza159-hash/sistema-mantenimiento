<?php
// controller/SupervisionController.php
// Ubicación: C:\xampp\htdocs\proyecto\controller\SupervisionController.php

// Incluir el controlador base
require_once __DIR__ . '/../helpers/Controller.php';

class SupervisionController extends Controller {
    
    private $db;
    private $ordenModel;

    public function __construct() {
        parent::__construct();
        
        // Verificar autenticación
        if (!$this->authHelper->isLoggedIn()) {
            header('Location: /proyecto/auth/login');
            exit;
        }
        
        // Verificar permisos (admin o supervisor)
        if (!$this->authHelper->isAdmin() && !$this->authHelper->isSupervisor()) {
            $_SESSION['error'] = 'No tienes permisos para acceder a esta sección';
            header('Location: /proyecto/dashboard');
            exit;
        }
        
        // Obtener conexión a la base de datos
        $this->db = Database::getInstance()->getConnection();
        
        // Cargar modelo de órdenes
        require_once __DIR__ . '/../model/OrdenTrabajo.php';
        $this->ordenModel = new OrdenTrabajo();
    }

    /**
     * Dashboard de supervisión
     * URL: /supervision
     */
    public function index() {
        try {
            // Obtener todas las supervisiones
            $sql = "SELECT s.*, 
                           o.num_om,
                           o.titulo,
                           o.status as orden_estado,
                           u.nombre as supervisor_nombre,
                           t.nombre as tecnico_nombre
                    FROM supervision s
                    LEFT JOIN ordenes_mantenimiento o ON s.orden_id = o.id
                    LEFT JOIN usuarios u ON s.supervisor_id = u.id
                    LEFT JOIN tecnicos t ON o.tecnico_id = t.id
                    ORDER BY s.fecha_creacion DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $supervisiones = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Estadísticas
            $estadisticas = [
                'total' => count($supervisiones),
                'pendientes' => count(array_filter($supervisiones, function($s) { return $s['estado'] === 'pendiente'; })),
                'aprobadas' => count(array_filter($supervisiones, function($s) { return $s['estado'] === 'aprobada'; })),
                'rechazadas' => count(array_filter($supervisiones, function($s) { return $s['estado'] === 'rechazada'; }))
            ];
            
            // Técnicos para el filtro
            require_once __DIR__ . '/../model/TecnicosModel.php';
            $tecnicosModel = new TecnicosModel();
            $tecnicos = $tecnicosModel->obtenerTodos(['estado' => 'activo']);
            
        } catch (Exception $e) {
            error_log("Error en SupervisionController index: " . $e->getMessage());
            $supervisiones = [];
            $estadisticas = ['total' => 0, 'pendientes' => 0, 'aprobadas' => 0, 'rechazadas' => 0];
            $tecnicos = [];
            $_SESSION['error'] = 'Error al cargar las supervisiones';
        }
        
        $this->view('supervision/index', [
            'supervisiones' => $supervisiones,
            'estadisticas' => $estadisticas,
            'tecnicos' => $tecnicos
        ]);
    }

    /**
     * Ver detalle de una supervisión
     * URL: /supervision/ver/{id}
     */
    public function ver($id) {
        $id = (int)$id;
        if ($id <= 0) {
            $_SESSION['error'] = 'ID de supervisión inválido';
            $this->redirect('/proyecto/supervision');
        }
        
        try {
            $sql = "SELECT s.*, 
                           o.num_om,
                           o.titulo,
                           o.descripcion_mantenimiento,
                           o.status as orden_estado,
                           u.nombre as supervisor_nombre,
                           t.nombre as tecnico_nombre
                    FROM supervision s
                    LEFT JOIN ordenes_mantenimiento o ON s.orden_id = o.id
                    LEFT JOIN usuarios u ON s.supervisor_id = u.id
                    LEFT JOIN tecnicos t ON o.tecnico_id = t.id
                    WHERE s.id = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            $supervision = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$supervision) {
                $_SESSION['error'] = 'Supervisión no encontrada';
                $this->redirect('/proyecto/supervision');
            }
            
        } catch (Exception $e) {
            error_log("Error en SupervisionController ver: " . $e->getMessage());
            $_SESSION['error'] = 'Error al cargar la supervisión';
            $this->redirect('/proyecto/supervision');
        }
        
        $this->view('supervision/ver', ['supervision' => $supervision]);
    }

    /**
     * Formulario para editar supervisión
     * URL: /supervision/editar/{id}
     */
    public function editar($id) {
        $id = (int)$id;
        if ($id <= 0) {
            $_SESSION['error'] = 'ID de supervisión inválido';
            $this->redirect('/proyecto/supervision');
        }
        
        try {
            $sql = "SELECT s.*, 
                           o.num_om,
                           o.titulo
                    FROM supervision s
                    LEFT JOIN ordenes_mantenimiento o ON s.orden_id = o.id
                    WHERE s.id = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            $supervision = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$supervision) {
                $_SESSION['error'] = 'Supervisión no encontrada';
                $this->redirect('/proyecto/supervision');
            }
            
        } catch (Exception $e) {
            error_log("Error en SupervisionController editar: " . $e->getMessage());
            $_SESSION['error'] = 'Error al cargar la supervisión';
            $this->redirect('/proyecto/supervision');
        }
        
        $this->view('supervision/editar', ['supervision' => $supervision]);
    }

    /**
     * Actualizar supervisión
     * URL: /supervision/actualizar/{id} (POST)
     */
    public function actualizar($id) {
        $this->requirePost();
        
        $id = (int)$id;
        if ($id <= 0) {
            $_SESSION['error'] = 'ID de supervisión inválido';
            $this->redirect('/proyecto/supervision');
        }
        
        $calificacion = $this->post('calificacion', 0);
        $estado = $this->post('estado', 'pendiente');
        $observaciones = $this->post('observaciones', '');
        $cumple = $this->post('cumple', 0);
        
        try {
            $sql = "UPDATE supervision SET 
                        calificacion = ?,
                        estado = ?,
                        observaciones = ?,
                        cumple = ?,
                        fecha_actualizacion = NOW()
                    WHERE id = ?";
            
            $stmt = $this->db->prepare($sql);
            $resultado = $stmt->execute([$calificacion, $estado, $observaciones, $cumple, $id]);
            
            if ($resultado) {
                $_SESSION['success'] = 'Supervisión actualizada correctamente';
            } else {
                $_SESSION['error'] = 'Error al actualizar la supervisión';
            }
            
        } catch (Exception $e) {
            error_log("Error en SupervisionController actualizar: " . $e->getMessage());
            $_SESSION['error'] = 'Error al actualizar la supervisión';
        }
        
        $this->redirect('/proyecto/supervision/ver/' . $id);
    }

    /**
     * Aprobar orden
     * URL: /supervision/aprobar/{id} (POST)
     */
    public function aprobar($id) {
        $this->requirePost();
        
        $id = (int)$id;
        if ($id <= 0) {
            $_SESSION['error'] = 'ID de supervisión inválido';
            $this->redirect('/proyecto/supervision');
        }
        
        $observaciones = $this->post('observaciones_aprobacion', 'Aprobada por supervisor');
        
        try {
            // Actualizar supervisión
            $sql = "UPDATE supervision SET 
                        estado = 'aprobada',
                        observaciones = ?,
                        fecha_actualizacion = NOW()
                    WHERE id = ?";
            
            $stmt = $this->db->prepare($sql);
            $resultado = $stmt->execute([$observaciones, $id]);
            
            if ($resultado) {
                $_SESSION['success'] = 'Orden aprobada correctamente';
            } else {
                $_SESSION['error'] = 'Error al aprobar la orden';
            }
            
        } catch (Exception $e) {
            error_log("Error en SupervisionController aprobar: " . $e->getMessage());
            $_SESSION['error'] = 'Error al aprobar la orden';
        }
        
        $this->redirect('/proyecto/supervision');
    }

    /**
     * Rechazar orden
     * URL: /supervision/rechazar/{id} (POST)
     */
    public function rechazar($id) {
        $this->requirePost();
        
        $id = (int)$id;
        if ($id <= 0) {
            $_SESSION['error'] = 'ID de supervisión inválido';
            $this->redirect('/proyecto/supervision');
        }
        
        $motivo = $this->post('motivo_rechazo', 'Rechazada por supervisor');
        
        try {
            // Actualizar supervisión
            $sql = "UPDATE supervision SET 
                        estado = 'rechazada',
                        observaciones = ?,
                        fecha_actualizacion = NOW()
                    WHERE id = ?";
            
            $stmt = $this->db->prepare($sql);
            $resultado = $stmt->execute([$motivo, $id]);
            
            if ($resultado) {
                $_SESSION['success'] = 'Orden rechazada correctamente';
            } else {
                $_SESSION['error'] = 'Error al rechazar la orden';
            }
            
        } catch (Exception $e) {
            error_log("Error en SupervisionController rechazar: " . $e->getMessage());
            $_SESSION['error'] = 'Error al rechazar la orden';
        }
        
        $this->redirect('/proyecto/supervision');
    }

    /**
     * Obtener datos de una orden para AJAX
     * URL: /supervision/orden/{id}
     */
    public function orden($id) {
        $id = (int)$id;
        if ($id <= 0) {
            $this->jsonResponse(['error' => 'ID inválido'], 400);
        }
        
        try {
            $sql = "SELECT id, num_om, titulo FROM ordenes_mantenimiento WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            $orden = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($orden) {
                $this->jsonResponse($orden);
            } else {
                $this->jsonResponse(['error' => 'Orden no encontrada'], 404);
            }
            
        } catch (Exception $e) {
            error_log("Error en SupervisionController orden: " . $e->getMessage());
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Reporte de supervisiones
     * URL: /supervision/reporte
     */
    public function reporte() {
        try {
            $sql = "SELECT 
                        DATE_FORMAT(s.fecha_creacion, '%Y-%m') as mes,
                        COUNT(*) as total,
                        SUM(CASE WHEN s.estado = 'aprobada' THEN 1 ELSE 0 END) as aprobadas,
                        SUM(CASE WHEN s.estado = 'rechazada' THEN 1 ELSE 0 END) as rechazadas,
                        AVG(s.calificacion) as promedio_calificacion
                    FROM supervision s
                    GROUP BY DATE_FORMAT(s.fecha_creacion, '%Y-%m')
                    ORDER BY mes DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $reporte = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error en SupervisionController reporte: " . $e->getMessage());
            $reporte = [];
            $_SESSION['error'] = 'Error al generar el reporte';
        }
        
        $this->view('supervision/reporte', ['reporte' => $reporte]);
    }
}