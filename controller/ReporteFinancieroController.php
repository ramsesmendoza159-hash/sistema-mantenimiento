<?php
// controller/ReporteFinancieroController.php
// Ubicación: C:\xampp\htdocs\produmar\controller\ReporteFinancieroController.php

// Incluir el controlador base
require_once __DIR__ . '/../helpers/Controller.php';

class ReporteFinancieroController extends Controller {
    
    private $db;
    private $ordenModel;

    public function __construct() {
        parent::__construct();
        
        // Verificar autenticación
        if (!$this->authHelper->isLoggedIn()) {
            header('Location: /produmar/auth/login');
            exit;
        }
        
        // Verificar permisos (admin o supervisor)
        if (!$this->authHelper->isAdmin() && !$this->authHelper->isSupervisor()) {
            $_SESSION['error'] = 'No tienes permisos para acceder a esta sección';
            header('Location: /produmar/dashboard');
            exit;
        }
        
        // Obtener conexión a la base de datos
        $this->db = Database::getInstance()->getConnection();
        
        // Cargar modelo de órdenes
        require_once __DIR__ . '/../model/OrdenTrabajo.php';
        $this->ordenModel = new OrdenTrabajo();
    }

    /**
     * Dashboard de reportes financieros
     * URL: /reportes/financieros
     */
    public function index() {
        $fechaInicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : date('Y-m-01');
        $fechaFin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : date('Y-m-t');
        
        try {
            // Estadísticas generales
            $stats = $this->ordenModel->obtenerEstadisticasFinancieras($fechaInicio, $fechaFin);
            
            // Costos por planta
            $costos_por_planta = $this->ordenModel->obtenerCostosPorPlanta($fechaInicio, $fechaFin);
            
            // Costos por técnico
            $costos_por_tecnico = $this->ordenModel->obtenerCostosPorTecnico($fechaInicio, $fechaFin);
            
            // Costos por mes
            $costos_por_mes = $this->ordenModel->obtenerCostosPorMes(date('Y'));
            
            // Top repuestos
            $top_repuestos = $this->ordenModel->obtenerTopRepuestos(10);
            
        } catch (Exception $e) {
            error_log("Error en reportes financieros: " . $e->getMessage());
            $stats = [
                'total_ordenes' => 0,
                'total_costos' => 0,
                'total_repuestos' => 0,
                'total_mano_obra' => 0,
                'promedio_costo' => 0,
                'promedio_horas' => 0,
                'total_horas' => 0
            ];
            $costos_por_planta = [];
            $costos_por_tecnico = [];
            $costos_por_mes = [];
            $top_repuestos = [];
            $_SESSION['error'] = 'Error al cargar los reportes financieros';
        }
        
        $this->view('reportes/financieros', [
            'stats' => $stats,
            'costos_por_planta' => $costos_por_planta,
            'costos_por_tecnico' => $costos_por_tecnico,
            'costos_por_mes' => $costos_por_mes,
            'top_repuestos' => $top_repuestos,
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin
        ]);
    }

    /**
     * Exportar reporte financiero a Excel (CSV)
     * URL: /reportes/financieros/exportar
     */
    public function exportar() {
        $fechaInicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : date('Y-m-01');
        $fechaFin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : date('Y-m-t');
        $tipo = isset($_GET['tipo']) ? $_GET['tipo'] : 'detallado';
        
        try {
            if ($tipo === 'detallado') {
                $sql = "SELECT 
                            o.num_om,
                            o.titulo,
                            o.nombre_planta,
                            o.nombre_area,
                            t.nombre as tecnico,
                            o.prioridad,
                            o.status,
                            DATE_FORMAT(o.fecha_creacion, '%d/%m/%Y') as fecha,
                            o.horas_trabajadas,
                            o.tarifa_tecnico,
                            o.costo_repuestos,
                            o.costo_mano_obra,
                            o.costo_total
                        FROM ordenes_mantenimiento o
                        LEFT JOIN tecnicos t ON o.tecnico_id = t.id
                        WHERE o.status IN ('CERRADA', 'APROBADA', 'EJECUTADA')
                        AND o.fecha_creacion BETWEEN ? AND ?
                        ORDER BY o.fecha_creacion DESC";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59']);
                $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $nombreArchivo = 'reporte_financiero_detallado_' . date('Y-m-d') . '.csv';
            } else {
                $sql = "SELECT 
                            o.nombre_planta,
                            COUNT(*) as total_ordenes,
                            SUM(o.costo_total) as total_costos,
                            SUM(o.costo_repuestos) as total_repuestos,
                            SUM(o.costo_mano_obra) as total_mano_obra,
                            AVG(o.costo_total) as promedio_costo
                        FROM ordenes_mantenimiento o
                        WHERE o.status IN ('CERRADA', 'APROBADA', 'EJECUTADA')
                        AND o.fecha_creacion BETWEEN ? AND ?
                        AND o.nombre_planta IS NOT NULL AND o.nombre_planta != ''
                        GROUP BY o.nombre_planta
                        ORDER BY total_costos DESC";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59']);
                $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $nombreArchivo = 'reporte_financiero_resumen_' . date('Y-m-d') . '.csv';
            }
            
            if (empty($datos)) {
                $_SESSION['error'] = 'No hay datos financieros para exportar en el período seleccionado';
                $this->redirect('/produmar/reportes/financieros');
            }
            
            // Generar CSV
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
            header('Pragma: no-cache');
            header('Expires: 0');
            
            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Encabezados
            $headers = array_keys($datos[0]);
            fputcsv($output, $headers);
            
            // Datos
            foreach ($datos as $fila) {
                fputcsv($output, $fila);
            }
            
            fclose($output);
            exit;
            
        } catch (Exception $e) {
            error_log("Error al exportar reporte financiero: " . $e->getMessage());
            $_SESSION['error'] = 'Error al exportar el reporte financiero';
            $this->redirect('/produmar/reportes/financieros');
        }
    }
}