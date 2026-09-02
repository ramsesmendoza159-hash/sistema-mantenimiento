<?php
// controller/ReporteController.php
// Controlador de reportes - CORREGIDO (sin dependencias externas)

require_once __DIR__ . '/../model/OrdenTrabajo.php';
require_once __DIR__ . '/../model/Tecnico.php';
require_once __DIR__ . '/../helpers/ValidationHelper.php';
require_once __DIR__ . '/../helpers/SecurityHelper.php';

class ReporteController {
    
    private $ordenModel;
    private $tecnicoModel;
    private $db;
    
    public function __construct() {
        // Verificar autenticación
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: /proyecto/auth/login');
            exit;
        }
        
        // Verificar permisos (admin o supervisor)
        $rol = $_SESSION['rol'] ?? '';
        if ($rol !== 'admin' && $rol !== 'supervisor') {
            $_SESSION['error'] = 'No tienes permisos para acceder a esta sección';
            header('Location: /proyecto/dashboard');
            exit;
        }
        
        $this->ordenModel = new OrdenTrabajo();
        $this->tecnicoModel = new Tecnico();
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Dashboard de reportes
     * URL: /reportes
     */
    public function index() {
        $fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
        $fechaFin = $_GET['fecha_fin'] ?? date('Y-m-t');
        
        // Validar fechas
        if (!ValidationHelper::validateDate($fechaInicio) || !ValidationHelper::validateDate($fechaFin)) {
            $fechaInicio = date('Y-m-01');
            $fechaFin = date('Y-m-t');
        }
        
        try {
            // Obtener estadísticas directamente desde el modelo
            $stats = $this->ordenModel->obtenerEstadisticas();
            
            // Obtener datos para gráficos
            $ordenes_por_mes = $this->getOrdenesPorMes($fechaInicio, $fechaFin);
            $ordenes_por_estado = $this->getOrdenesPorEstado($fechaInicio, $fechaFin);
            $ordenes_por_prioridad = $this->getOrdenesPorPrioridad($fechaInicio, $fechaFin);
            $ordenes_por_tecnico = $this->getOrdenesPorTecnico($fechaInicio, $fechaFin);
            $ordenes_por_planta = $this->ordenModel->obtenerCostosPorPlanta($fechaInicio, $fechaFin);
            $costos_por_mes = $this->getCostosPorMes($fechaInicio, $fechaFin);
            
            // Calcular eficiencia
            $total = $stats['total'] ?? 0;
            $completadas = ($stats['cerradas'] ?? 0) + ($stats['aprobadas'] ?? 0);
            $eficiencia = $total > 0 ? round(($completadas / $total) * 100, 1) : 0;
            $stats['eficiencia'] = $eficiencia;
            
        } catch (Exception $e) {
            error_log("Error en reportes index: " . $e->getMessage());
            $stats = [
                'total' => 0, 'pendientes' => 0, 'en_proceso' => 0,
                'cerradas' => 0, 'canceladas' => 0, 'aprobadas' => 0,
                'total_costos' => 0, 'promedio_horas' => 0, 'eficiencia' => 0
            ];
            $ordenes_por_mes = [];
            $ordenes_por_estado = [];
            $ordenes_por_prioridad = [];
            $ordenes_por_tecnico = [];
            $ordenes_por_planta = [];
            $costos_por_mes = [];
            $_SESSION['error'] = 'Error al cargar los reportes';
        }
        
        $seccion = 'reportes';
        $titulo = 'Reportes';
        require_once __DIR__ . '/../views/reportes/index.php';
    }
    
    /**
     * Reporte detallado de órdenes
     * URL: /reportes/ordenes
     */
    public function ordenes() {
        $fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
        $fechaFin = $_GET['fecha_fin'] ?? date('Y-m-t');
        $estado = $_GET['estado'] ?? '';
        $prioridad = $_GET['prioridad'] ?? '';
        $tecnico_id = $_GET['tecnico_id'] ?? '';
        
        // Validar fechas
        if (!ValidationHelper::validateDate($fechaInicio) || !ValidationHelper::validateDate($fechaFin)) {
            $fechaInicio = date('Y-m-01');
            $fechaFin = date('Y-m-t');
        }
        
        try {
            // Construir filtros
            $filtros = [
                'fecha_desde' => $fechaInicio,
                'fecha_hasta' => $fechaFin,
                'status' => $estado,
                'prioridad' => $prioridad,
                'tecnico_id' => $tecnico_id
            ];
            
            // Obtener órdenes
            $ordenes = $this->ordenModel->obtenerTodos($filtros);
            
            // Calcular estadísticas
            $total = count($ordenes);
            $total_costos = array_sum(array_column($ordenes, 'costo_total'));
            $total_horas = array_sum(array_column($ordenes, 'horas_trabajadas'));
            $promedio_horas = $total > 0 ? round($total_horas / $total, 1) : 0;
            
            // Contar por estado
            $pendientes = 0;
            $en_proceso = 0;
            $completadas = 0;
            $canceladas = 0;
            foreach ($ordenes as $orden) {
                $status = $orden['status'] ?? '';
                if (in_array($status, ['CERRADA', 'APROBADA', 'EJECUTADA'])) {
                    $completadas++;
                } elseif ($status === 'PENDIENTE') {
                    $pendientes++;
                } elseif ($status === 'EN_PROCESO') {
                    $en_proceso++;
                } elseif (in_array($status, ['CANCELADA', 'RECHAZADA'])) {
                    $canceladas++;
                }
            }
            
            $estadisticas = [
                'total' => $total,
                'completadas' => $completadas,
                'pendientes' => $pendientes,
                'en_proceso' => $en_proceso,
                'canceladas' => $canceladas,
                'costo_total' => $total_costos,
                'promedio_horas' => $promedio_horas
            ];
            
            // Obtener técnicos para el filtro
            $tecnicos = $this->tecnicoModel->obtenerTodos();
            
        } catch (Exception $e) {
            error_log("Error en reporte ordenes: " . $e->getMessage());
            $ordenes = [];
            $estadisticas = [
                'total' => 0, 'completadas' => 0, 'pendientes' => 0,
                'en_proceso' => 0, 'canceladas' => 0,
                'costo_total' => 0, 'promedio_horas' => 0
            ];
            $tecnicos = [];
            $_SESSION['error'] = 'Error al cargar el reporte de órdenes';
        }
        
        $seccion = 'reportes';
        $titulo = 'Reporte de Órdenes';
        require_once __DIR__ . '/../views/reportes/ordenes.php';
    }
    
    /**
     * Reporte de técnicos
     * URL: /reportes/tecnicos
     */
    public function tecnicos() {
        $fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
        $fechaFin = $_GET['fecha_fin'] ?? date('Y-m-t');
        
        // Validar fechas
        if (!ValidationHelper::validateDate($fechaInicio) || !ValidationHelper::validateDate($fechaFin)) {
            $fechaInicio = date('Y-m-01');
            $fechaFin = date('Y-m-t');
        }
        
        try {
            // Obtener técnicos con estadísticas
            $sql = "SELECT 
                        t.id,
                        t.nombre,
                        t.especialidad,
                        t.email,
                        t.telefono,
                        COUNT(om.id) as total_ordenes,
                        SUM(CASE WHEN om.status IN ('CERRADA', 'APROBADA', 'EJECUTADA') THEN 1 ELSE 0 END) as completadas,
                        SUM(CASE WHEN om.status = 'PENDIENTE' THEN 1 ELSE 0 END) as pendientes,
                        SUM(om.costo_total) as costo_total,
                        ROUND(AVG(om.horas_trabajadas), 1) as promedio_horas,
                        ROUND(SUM(CASE WHEN om.status IN ('CERRADA', 'APROBADA', 'EJECUTADA') THEN 1 ELSE 0 END) * 100.0 / NULLIF(COUNT(om.id), 0), 1) as eficiencia
                    FROM tecnicos t
                    LEFT JOIN ordenes_mantenimiento om ON t.id = om.tecnico_id
                        AND om.fecha_creacion BETWEEN ? AND ?
                    WHERE t.estado = 'activo'
                    GROUP BY t.id
                    HAVING total_ordenes > 0
                    ORDER BY eficiencia DESC, total_ordenes DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $fechaInicio . ' 00:00:00',
                $fechaFin . ' 23:59:59'
            ]);
            $tecnicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $total_ordenes = array_sum(array_column($tecnicos, 'total_ordenes'));
            $total_costos = array_sum(array_column($tecnicos, 'costo_total'));
            
        } catch (Exception $e) {
            error_log("Error en reporte tecnicos: " . $e->getMessage());
            $tecnicos = [];
            $total_ordenes = 0;
            $total_costos = 0;
            $_SESSION['error'] = 'Error al cargar el reporte de técnicos';
        }
        
        $seccion = 'reportes';
        $titulo = 'Reporte de Técnicos';
        require_once __DIR__ . '/../views/reportes/tecnicos.php';
    }
    
    /**
     * Exportar reporte a CSV
     * URL: /reportes/exportar
     */
    public function exportar() {
        $fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
        $fechaFin = $_GET['fecha_fin'] ?? date('Y-m-t');
        $tipo = $_GET['tipo'] ?? 'ordenes';
        
        if (!ValidationHelper::validateDate($fechaInicio) || !ValidationHelper::validateDate($fechaFin)) {
            $fechaInicio = date('Y-m-01');
            $fechaFin = date('Y-m-t');
        }
        
        try {
            if ($tipo === 'ordenes') {
                $sql = "SELECT 
                            om.num_om, 
                            om.titulo, 
                            om.nombre_planta, 
                            om.nombre_area, 
                            om.nombre_equipo,
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
                $stmt->execute([
                    $fechaInicio . ' 00:00:00',
                    $fechaFin . ' 23:59:59'
                ]);
                $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $nombreArchivo = 'reporte_ordenes_' . date('Y-m-d') . '.csv';
            } else {
                $sql = "SELECT 
                            t.nombre as tecnico,
                            t.especialidad,
                            t.email,
                            COUNT(om.id) as total_ordenes,
                            SUM(CASE WHEN om.status IN ('CERRADA', 'APROBADA', 'EJECUTADA') THEN 1 ELSE 0 END) as completadas,
                            SUM(om.costo_total) as costo_total,
                            ROUND(AVG(om.horas_trabajadas), 1) as promedio_horas,
                            ROUND(SUM(CASE WHEN om.status IN ('CERRADA', 'APROBADA', 'EJECUTADA') THEN 1 ELSE 0 END) * 100.0 / NULLIF(COUNT(om.id), 0), 1) as eficiencia
                        FROM tecnicos t
                        LEFT JOIN ordenes_mantenimiento om ON t.id = om.tecnico_id
                            AND om.fecha_creacion BETWEEN ? AND ?
                        WHERE t.estado = 'activo'
                        GROUP BY t.id
                        HAVING total_ordenes > 0
                        ORDER BY eficiencia DESC";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    $fechaInicio . ' 00:00:00',
                    $fechaFin . ' 23:59:59'
                ]);
                $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $nombreArchivo = 'reporte_tecnicos_' . date('Y-m-d') . '.csv';
            }
            
            if (empty($datos)) {
                $_SESSION['error'] = 'No hay datos para exportar';
                header('Location: /proyecto/reportes');
                exit;
            }
            
            // Generar CSV
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
            header('Pragma: no-cache');
            header('Expires: 0');
            
            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
            
            if (!empty($datos)) {
                $headers = array_keys($datos[0]);
                fputcsv($output, $headers);
                
                foreach ($datos as $fila) {
                    fputcsv($output, $fila);
                }
            }
            
            fclose($output);
            exit;
            
        } catch (Exception $e) {
            error_log("Error al exportar: " . $e->getMessage());
            $_SESSION['error'] = 'Error al exportar el reporte';
            header('Location: /proyecto/reportes');
            exit;
        }
    }
    
    /**
     * Vista de impresión (PDF)
     * URL: /reportes/imprimir
     */
    public function imprimir() {
        $fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
        $fechaFin = $_GET['fecha_fin'] ?? date('Y-m-t');
        $tipo = $_GET['tipo'] ?? 'ordenes';
        
        if (!ValidationHelper::validateDate($fechaInicio) || !ValidationHelper::validateDate($fechaFin)) {
            $fechaInicio = date('Y-m-01');
            $fechaFin = date('Y-m-t');
        }
        
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
                $stmt->execute([
                    $fechaInicio . ' 00:00:00',
                    $fechaFin . ' 23:59:59'
                ]);
                $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $total_costos = array_sum(array_column($datos, 'costo_total'));
                $total_ordenes = count($datos);
            } else {
                $sql = "SELECT 
                            t.nombre,
                            t.especialidad,
                            COUNT(om.id) as total_ordenes,
                            SUM(CASE WHEN om.status IN ('CERRADA', 'APROBADA', 'EJECUTADA') THEN 1 ELSE 0 END) as completadas,
                            SUM(om.costo_total) as costo_total,
                            ROUND(AVG(om.horas_trabajadas), 1) as promedio_horas,
                            ROUND(SUM(CASE WHEN om.status IN ('CERRADA', 'APROBADA', 'EJECUTADA') THEN 1 ELSE 0 END) * 100.0 / NULLIF(COUNT(om.id), 0), 1) as eficiencia
                        FROM tecnicos t
                        LEFT JOIN ordenes_mantenimiento om ON t.id = om.tecnico_id
                            AND om.fecha_creacion BETWEEN ? AND ?
                        WHERE t.estado = 'activo'
                        GROUP BY t.id
                        HAVING total_ordenes > 0
                        ORDER BY eficiencia DESC";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    $fechaInicio . ' 00:00:00',
                    $fechaFin . ' 23:59:59'
                ]);
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
            header('Location: /proyecto/reportes');
            exit;
        }
        
        $seccion = 'reportes';
        $titulo = 'Imprimir Reporte';
        require_once __DIR__ . '/../views/reportes/imprimir.php';
    }
    
    // ==========================================
    // MÉTODOS PRIVADOS PARA ESTADÍSTICAS
    // ==========================================
    
    private function getOrdenesPorMes($fechaInicio, $fechaFin) {
        $sql = "SELECT 
                    DATE_FORMAT(fecha_creacion, '%Y-%m') as mes,
                    COUNT(*) as total,
                    SUM(CASE WHEN status IN ('CERRADA', 'APROBADA', 'EJECUTADA') THEN 1 ELSE 0 END) as completadas
                FROM ordenes_mantenimiento 
                WHERE fecha_creacion BETWEEN ? AND ?
                GROUP BY DATE_FORMAT(fecha_creacion, '%Y-%m')
                ORDER BY mes ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
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
    
    private function getOrdenesPorTecnico($fechaInicio, $fechaFin) {
        $sql = "SELECT 
                    COALESCE(t.nombre, 'Sin Asignar') as tecnico,
                    COUNT(om.id) as total,
                    SUM(CASE WHEN om.status IN ('CERRADA', 'APROBADA', 'EJECUTADA') THEN 1 ELSE 0 END) as completadas
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
?>