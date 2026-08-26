<?php
// controller/ReporteController.php
// Ubicación: C:\xampp\htdocs\proyecto\controller\ReporteController.php

// Incluir el controlador base
require_once __DIR__ . '/../helpers/Controller.php';

class ReporteController extends Controller {
    
    private $db;

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
    }

    /**
     * Dashboard de reportes
     * URL: /reportes
     */
    public function index() {
        $fechaInicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : date('Y-m-01');
        $fechaFin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : date('Y-m-t');
        
        try {
            $stats = $this->getEstadisticas($fechaInicio, $fechaFin);
            $ordenes_por_mes = $this->getOrdenesPorMes($fechaInicio, $fechaFin);
            $ordenes_por_estado = $this->getOrdenesPorEstado($fechaInicio, $fechaFin);
            $ordenes_por_prioridad = $this->getOrdenesPorPrioridad($fechaInicio, $fechaFin);
            $ordenes_por_tecnico = $this->getOrdenesPorTecnico($fechaInicio, $fechaFin);
            $ordenes_por_planta = $this->getOrdenesPorPlanta($fechaInicio, $fechaFin);
            $costos_por_mes = $this->getCostosPorMes($fechaInicio, $fechaFin);
            
        } catch (Exception $e) {
            error_log("Error en reportes: " . $e->getMessage());
            $stats = ['total' => 0, 'pendientes' => 0, 'en_proceso' => 0, 'cerradas' => 0, 'canceladas' => 0, 'total_costos' => 0, 'promedio_horas' => 0, 'eficiencia' => 0];
            $ordenes_por_mes = [];
            $ordenes_por_estado = [];
            $ordenes_por_prioridad = [];
            $ordenes_por_tecnico = [];
            $ordenes_por_planta = [];
            $costos_por_mes = [];
            $_SESSION['error'] = 'Error al cargar los reportes: ' . $e->getMessage();
        }
        
        $this->view('reportes/index', [
            'stats' => $stats,
            'ordenes_por_mes' => $ordenes_por_mes,
            'ordenes_por_estado' => $ordenes_por_estado,
            'ordenes_por_prioridad' => $ordenes_por_prioridad,
            'ordenes_por_tecnico' => $ordenes_por_tecnico,
            'ordenes_por_planta' => $ordenes_por_planta,
            'costos_por_mes' => $costos_por_mes,
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin
        ]);
    }

    /**
     * Reporte detallado de órdenes
     * URL: /reportes/ordenes
     */
    public function ordenes() {
        $fechaInicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : date('Y-m-01');
        $fechaFin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : date('Y-m-t');
        $estado = isset($_GET['estado']) ? $_GET['estado'] : '';
        $prioridad = isset($_GET['prioridad']) ? $_GET['prioridad'] : '';
        
        try {
            $sql = "SELECT om.*, 
                           t.nombre as tecnico_nombre,
                           s.nombre as supervisor_nombre
                    FROM ordenes_mantenimiento om
                    LEFT JOIN tecnicos t ON om.tecnico_id = t.id
                    LEFT JOIN supervisores s ON om.id_supervisor = s.id
                    WHERE om.fecha_creacion BETWEEN ? AND ?";
            $params = [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'];
            
            if (!empty($estado)) {
                $sql .= " AND om.status = ?";
                $params[] = $estado;
            }
            
            if (!empty($prioridad)) {
                $sql .= " AND om.prioridad = ?";
                $params[] = $prioridad;
            }
            
            $sql .= " ORDER BY om.fecha_creacion DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $ordenes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Obtener totales
            $total = count($ordenes);
            $total_costos = array_sum(array_column($ordenes, 'costo_total'));
            $promedio_horas = $total > 0 ? round(array_sum(array_column($ordenes, 'horas_trabajadas')) / $total, 1) : 0;
            
            // Obtener estados para el filtro
            $stmtEstados = $this->db->query("SELECT DISTINCT status FROM ordenes_mantenimiento ORDER BY status");
            $estados = $stmtEstados->fetchAll(PDO::FETCH_COLUMN);
            
            // Obtener prioridades para el filtro
            $stmtPrioridades = $this->db->query("SELECT DISTINCT prioridad FROM ordenes_mantenimiento ORDER BY prioridad");
            $prioridades = $stmtPrioridades->fetchAll(PDO::FETCH_COLUMN);
            
        } catch (Exception $e) {
            error_log("Error en reporte ordenes: " . $e->getMessage());
            $ordenes = [];
            $total = 0;
            $total_costos = 0;
            $promedio_horas = 0;
            $estados = [];
            $prioridades = [];
            $_SESSION['error'] = 'Error al cargar el reporte: ' . $e->getMessage();
        }
        
        $this->view('reportes/ordenes', [
            'ordenes' => $ordenes,
            'total' => $total,
            'total_costos' => $total_costos,
            'promedio_horas' => $promedio_horas,
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,
            'estado' => $estado,
            'prioridad' => $prioridad,
            'estados' => $estados,
            'prioridades' => $prioridades
        ]);
    }

    /**
     * Reporte de técnicos
     * URL: /reportes/tecnicos
     */
    public function tecnicos() {
        $fechaInicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : date('Y-m-01');
        $fechaFin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : date('Y-m-t');
        
        try {
            $sql = "SELECT 
                        t.id,
                        t.nombre,
                        t.especialidad,
                        t.email,
                        t.telefono,
                        COUNT(om.id) as total_ordenes,
                        SUM(CASE WHEN om.status = 'CERRADA' OR om.status = 'APROBADA' THEN 1 ELSE 0 END) as completadas,
                        SUM(CASE WHEN om.status = 'PENDIENTE' THEN 1 ELSE 0 END) as pendientes,
                        SUM(CASE WHEN om.status = 'EN_PROCESO' THEN 1 ELSE 0 END) as en_proceso,
                        SUM(om.costo_total) as costo_total,
                        AVG(om.horas_trabajadas) as promedio_horas,
                        ROUND(SUM(CASE WHEN om.status = 'CERRADA' OR om.status = 'APROBADA' THEN 1 ELSE 0 END) * 100.0 / NULLIF(COUNT(om.id), 0), 1) as eficiencia
                    FROM tecnicos t
                    LEFT JOIN ordenes_mantenimiento om ON t.id = om.tecnico_id
                        AND om.fecha_creacion BETWEEN ? AND ?
                    WHERE t.estado = 'activo'
                    GROUP BY t.id
                    HAVING total_ordenes > 0
                    ORDER BY eficiencia DESC, total_ordenes DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59']);
            $tecnicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $total_ordenes = array_sum(array_column($tecnicos, 'total_ordenes'));
            $total_costos = array_sum(array_column($tecnicos, 'costo_total'));
            
            // Estadísticas adicionales
            $stmtStats = $this->db->prepare("SELECT 
                                                COUNT(DISTINCT tecnico_id) as total_tecnicos_activos,
                                                COUNT(*) as total_ordenes_periodo
                                            FROM ordenes_mantenimiento 
                                            WHERE fecha_creacion BETWEEN ? AND ?
                                            AND tecnico_id IS NOT NULL");
            $stmtStats->execute([$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59']);
            $stats = $stmtStats->fetch(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error en reporte tecnicos: " . $e->getMessage());
            $tecnicos = [];
            $total_ordenes = 0;
            $total_costos = 0;
            $stats = ['total_tecnicos_activos' => 0, 'total_ordenes_periodo' => 0];
            $_SESSION['error'] = 'Error al cargar el reporte de técnicos: ' . $e->getMessage();
        }
        
        $this->view('reportes/tecnicos', [
            'tecnicos' => $tecnicos,
            'total_ordenes' => $total_ordenes,
            'total_costos' => $total_costos,
            'stats' => $stats,
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin
        ]);
    }

    /**
     * Exportar reporte a CSV
     * URL: /reportes/exportar
     */
    public function exportar() {
        $fechaInicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : date('Y-m-01');
        $fechaFin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : date('Y-m-t');
        $tipo = isset($_GET['tipo']) ? $_GET['tipo'] : 'ordenes';
        
        try {
            if ($tipo === 'ordenes') {
                $sql = "SELECT 
                            om.num_om, 
                            om.titulo, 
                            om.nombre_planta, 
                            om.nombre_area, 
                            om.nombre_equipo,
                            om.nombre_componente, 
                            om.tipo_actividad, 
                            om.tipo_mantenimiento, 
                            om.prioridad, 
                            om.status, 
                            DATE_FORMAT(om.fecha_creacion, '%d/%m/%Y %H:%i') as fecha_creacion,
                            DATE_FORMAT(om.fecha_finalizacion, '%d/%m/%Y %H:%i') as fecha_finalizacion,
                            om.horas_trabajadas, 
                            om.costo_total, 
                            om.costo_repuestos, 
                            om.costo_mano_obra,
                            t.nombre as tecnico
                        FROM ordenes_mantenimiento om
                        LEFT JOIN tecnicos t ON om.tecnico_id = t.id
                        WHERE om.fecha_creacion BETWEEN ? AND ?
                        ORDER BY om.fecha_creacion DESC";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59']);
                $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $nombreArchivo = 'reporte_ordenes_' . date('Y-m-d') . '.csv';
            } else {
                $sql = "SELECT 
                            t.nombre as tecnico,
                            t.especialidad,
                            t.email,
                            COUNT(om.id) as total_ordenes,
                            SUM(CASE WHEN om.status = 'CERRADA' OR om.status = 'APROBADA' THEN 1 ELSE 0 END) as completadas,
                            SUM(CASE WHEN om.status = 'PENDIENTE' THEN 1 ELSE 0 END) as pendientes,
                            SUM(om.costo_total) as costo_total,
                            ROUND(AVG(om.horas_trabajadas), 1) as promedio_horas,
                            ROUND(SUM(CASE WHEN om.status = 'CERRADA' OR om.status = 'APROBADA' THEN 1 ELSE 0 END) * 100.0 / NULLIF(COUNT(om.id), 0), 1) as eficiencia
                        FROM tecnicos t
                        LEFT JOIN ordenes_mantenimiento om ON t.id = om.tecnico_id
                            AND om.fecha_creacion BETWEEN ? AND ?
                        WHERE t.estado = 'activo'
                        GROUP BY t.id
                        HAVING total_ordenes > 0
                        ORDER BY eficiencia DESC";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59']);
                $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $nombreArchivo = 'reporte_tecnicos_' . date('Y-m-d') . '.csv';
            }
            
            if (empty($datos)) {
                $_SESSION['error'] = 'No hay datos para exportar en el período seleccionado';
                $this->redirect('/proyecto/reportes');
            }
            
            // Generar CSV
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
            header('Pragma: no-cache');
            header('Expires: 0');
            
            $output = fopen('php://output', 'w');
            
            // Agregar BOM para UTF-8
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Escribir encabezados
            $headers = array_keys($datos[0]);
            fputcsv($output, $headers);
            
            // Escribir datos
            foreach ($datos as $fila) {
                fputcsv($output, $fila);
            }
            
            fclose($output);
            exit;
            
        } catch (Exception $e) {
            error_log("Error al exportar: " . $e->getMessage());
            $_SESSION['error'] = 'Error al exportar el reporte: ' . $e->getMessage();
            $this->redirect('/proyecto/reportes');
        }
    }

    /**
     * Exportar a PDF (vista previa para impresión)
     * URL: /reportes/imprimir
     */
    public function imprimir() {
        $fechaInicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : date('Y-m-01');
        $fechaFin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : date('Y-m-t');
        $tipo = isset($_GET['tipo']) ? $_GET['tipo'] : 'ordenes';
        
        try {
            if ($tipo === 'ordenes') {
                $sql = "SELECT 
                            om.*,
                            t.nombre as tecnico_nombre
                        FROM ordenes_mantenimiento om
                        LEFT JOIN tecnicos t ON om.tecnico_id = t.id
                        WHERE om.fecha_creacion BETWEEN ? AND ?
                        ORDER BY om.fecha_creacion DESC";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59']);
                $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $total_costos = array_sum(array_column($datos, 'costo_total'));
                $total_ordenes = count($datos);
            } else {
                $sql = "SELECT 
                            t.nombre,
                            t.especialidad,
                            COUNT(om.id) as total_ordenes,
                            SUM(CASE WHEN om.status = 'CERRADA' OR om.status = 'APROBADA' THEN 1 ELSE 0 END) as completadas,
                            SUM(om.costo_total) as costo_total,
                            ROUND(AVG(om.horas_trabajadas), 1) as promedio_horas,
                            ROUND(SUM(CASE WHEN om.status = 'CERRADA' OR om.status = 'APROBADA' THEN 1 ELSE 0 END) * 100.0 / NULLIF(COUNT(om.id), 0), 1) as eficiencia
                        FROM tecnicos t
                        LEFT JOIN ordenes_mantenimiento om ON t.id = om.tecnico_id
                            AND om.fecha_creacion BETWEEN ? AND ?
                        WHERE t.estado = 'activo'
                        GROUP BY t.id
                        HAVING total_ordenes > 0
                        ORDER BY eficiencia DESC";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59']);
                $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $total_costos = array_sum(array_column($datos, 'costo_total'));
                $total_ordenes = array_sum(array_column($datos, 'total_ordenes'));
            }
            
        } catch (Exception $e) {
            error_log("Error en imprimir: " . $e->getMessage());
            $datos = [];
            $total_costos = 0;
            $total_ordenes = 0;
            $_SESSION['error'] = 'Error al generar la vista de impresión';
            $this->redirect('/proyecto/reportes');
        }
        
        $this->view('reportes/imprimir', [
            'datos' => $datos,
            'tipo' => $tipo,
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,
            'total_costos' => $total_costos,
            'total_ordenes' => $total_ordenes
        ]);
    }

    // ==========================================
    // MÉTODOS PRIVADOS PARA ESTADÍSTICAS
    // ==========================================

    /**
     * Obtener estadísticas generales
     */
    private function getEstadisticas($fechaInicio, $fechaFin) {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'PENDIENTE' THEN 1 ELSE 0 END) as pendientes,
                    SUM(CASE WHEN status = 'EN_PROCESO' THEN 1 ELSE 0 END) as en_proceso,
                    SUM(CASE WHEN status = 'CERRADA' THEN 1 ELSE 0 END) as cerradas,
                    SUM(CASE WHEN status = 'CANCELADA' THEN 1 ELSE 0 END) as canceladas,
                    SUM(costo_total) as total_costos,
                    AVG(horas_trabajadas) as promedio_horas,
                    ROUND(SUM(CASE WHEN status = 'CERRADA' OR status = 'APROBADA' THEN 1 ELSE 0 END) * 100.0 / NULLIF(COUNT(*), 0), 1) as eficiencia
                FROM ordenes_mantenimiento 
                WHERE fecha_creacion BETWEEN ? AND ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59']);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return [
            'total' => $result['total'] ?? 0,
            'pendientes' => $result['pendientes'] ?? 0,
            'en_proceso' => $result['en_proceso'] ?? 0,
            'cerradas' => $result['cerradas'] ?? 0,
            'canceladas' => $result['canceladas'] ?? 0,
            'total_costos' => $result['total_costos'] ?? 0,
            'promedio_horas' => round($result['promedio_horas'] ?? 0, 1),
            'eficiencia' => $result['eficiencia'] ?? 0
        ];
    }

    /**
     * Obtener órdenes agrupadas por mes
     */
    private function getOrdenesPorMes($fechaInicio, $fechaFin) {
        $sql = "SELECT 
                    DATE_FORMAT(fecha_creacion, '%Y-%m') as mes,
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'CERRADA' OR status = 'APROBADA' THEN 1 ELSE 0 END) as completadas
                FROM ordenes_mantenimiento 
                WHERE fecha_creacion BETWEEN ? AND ?
                GROUP BY DATE_FORMAT(fecha_creacion, '%Y-%m')
                ORDER BY mes ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener órdenes agrupadas por estado
     */
    private function getOrdenesPorEstado($fechaInicio, $fechaFin) {
        $sql = "SELECT 
                    status,
                    COUNT(*) as total
                FROM ordenes_mantenimiento 
                WHERE fecha_creacion BETWEEN ? AND ?
                GROUP BY status
                ORDER BY total DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener órdenes agrupadas por prioridad
     */
    private function getOrdenesPorPrioridad($fechaInicio, $fechaFin) {
        $sql = "SELECT 
                    prioridad,
                    COUNT(*) as total
                FROM ordenes_mantenimiento 
                WHERE fecha_creacion BETWEEN ? AND ?
                GROUP BY prioridad
                ORDER BY 
                    CASE prioridad
                        WHEN 'Urgente' THEN 1
                        WHEN 'Alta' THEN 2
                        WHEN 'Media' THEN 3
                        WHEN 'Baja' THEN 4
                        ELSE 5
                    END";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener órdenes agrupadas por técnico
     */
    private function getOrdenesPorTecnico($fechaInicio, $fechaFin) {
        $sql = "SELECT 
                    COALESCE(t.nombre, 'Sin Asignar') as tecnico,
                    COUNT(om.id) as total,
                    SUM(CASE WHEN om.status = 'CERRADA' OR om.status = 'APROBADA' THEN 1 ELSE 0 END) as completadas
                FROM ordenes_mantenimiento om
                LEFT JOIN tecnicos t ON om.tecnico_id = t.id
                WHERE om.fecha_creacion BETWEEN ? AND ?
                GROUP BY om.tecnico_id
                ORDER BY total DESC
                LIMIT 10";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener órdenes agrupadas por planta
     */
    private function getOrdenesPorPlanta($fechaInicio, $fechaFin) {
        $sql = "SELECT 
                    nombre_planta,
                    COUNT(*) as total
                FROM ordenes_mantenimiento 
                WHERE fecha_creacion BETWEEN ? AND ?
                  AND nombre_planta IS NOT NULL
                  AND nombre_planta != ''
                GROUP BY nombre_planta
                ORDER BY total DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener costos agrupados por mes
     */
    private function getCostosPorMes($fechaInicio, $fechaFin) {
        $sql = "SELECT 
                    DATE_FORMAT(fecha_creacion, '%Y-%m') as mes,
                    SUM(costo_total) as total
                FROM ordenes_mantenimiento 
                WHERE fecha_creacion BETWEEN ? AND ?
                GROUP BY DATE_FORMAT(fecha_creacion, '%Y-%m')
                ORDER BY mes ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}