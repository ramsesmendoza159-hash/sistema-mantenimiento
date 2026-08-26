<?php
// controller/ReporteFinancieroController.php
// Controlador para reportes financieros

require_once __DIR__ . '/../model/OrdenTrabajo.php';
require_once __DIR__ . '/../helpers/ValidationHelper.php';

class ReporteFinancieroController {
    
    private $ordenModel;
    
    public function __construct() {
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: /proyecto/auth/login');
            exit;
        }
        $this->ordenModel = new OrdenTrabajo();
    }
    
    public function index() {
        $fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
        $fechaFin = $_GET['fecha_fin'] ?? date('Y-m-t');
        
        // Validar fechas
        if (!ValidationHelper::validateDate($fechaInicio) || !ValidationHelper::validateDate($fechaFin)) {
            $_SESSION['error'] = 'Fechas inválidas';
            $fechaInicio = date('Y-m-01');
            $fechaFin = date('Y-m-t');
        }
        
        try {
            // Obtener estadísticas
            $stats = $this->ordenModel->obtenerEstadisticasFinancieras($fechaInicio, $fechaFin);
            $costos_por_planta = $this->ordenModel->obtenerCostosPorPlanta($fechaInicio, $fechaFin);
            $costos_por_tecnico = $this->ordenModel->obtenerCostosPorTecnico($fechaInicio, $fechaFin);
            $costos_por_mes = $this->ordenModel->obtenerCostosPorMes();
            $top_repuestos = $this->ordenModel->obtenerTopRepuestos(10);
            
        } catch (Exception $e) {
            error_log("Error en reporte financiero: " . $e->getMessage());
            $stats = [
                'total_ordenes' => 0, 'total_costos' => 0,
                'total_repuestos' => 0, 'total_mano_obra' => 0,
                'promedio_costo' => 0, 'promedio_horas' => 0, 'total_horas' => 0
            ];
            $costos_por_planta = [];
            $costos_por_tecnico = [];
            $costos_por_mes = [];
            $top_repuestos = [];
            $_SESSION['error'] = 'Error al cargar los datos financieros';
        }
        
        $seccion = 'reportes';
        $titulo = 'Reportes Financieros';
        require_once __DIR__ . '/../views/reportes/financieros.php';
    }
    
    public function exportar() {
        $fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
        $fechaFin = $_GET['fecha_fin'] ?? date('Y-m-t');
        $tipo = $_GET['tipo'] ?? 'detallado';
        
        // Validar fechas
        if (!ValidationHelper::validateDate($fechaInicio) || !ValidationHelper::validateDate($fechaFin)) {
            $_SESSION['error'] = 'Fechas inválidas';
            header('Location: /proyecto/reportes/financieros');
            exit;
        }
        
        // Obtener estadísticas
        $stats = $this->ordenModel->obtenerEstadisticasFinancieras($fechaInicio, $fechaFin);
        
        // Obtener órdenes filtradas por fecha usando el método obtenerTodos con filtros
        $filtros = [
            'fecha_desde' => $fechaInicio,
            'fecha_hasta' => $fechaFin
        ];
        $ordenes = $this->ordenModel->obtenerTodos($filtros);
        
        // Filtrar solo las órdenes con estado final
        $ordenes_filtradas = array_filter($ordenes, function($orden) {
            $estados_finales = ['CERRADA', 'APROBADA', 'EJECUTADA'];
            return in_array($orden['status'], $estados_finales);
        });
        
        // Configurar cabeceras para descarga
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="reporte_financiero_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // Agregar BOM para UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        if ($tipo === 'detallado') {
            // Cabeceras del reporte detallado
            fputcsv($output, [
                'N° OM', 'Título', 'Planta', 'Área', 'Técnico', 
                'Horas', 'Costo Repuestos', 'Costo Mano Obra', 'Costo Total', 'Estado'
            ]);
            
            foreach ($ordenes_filtradas as $orden) {
                fputcsv($output, [
                    $orden['num_om'] ?? '',
                    $orden['titulo'] ?? '',
                    $orden['nombre_planta'] ?? '',
                    $orden['nombre_area'] ?? '',
                    $orden['tecnico_nombre'] ?? '',
                    $orden['horas_trabajadas'] ?? 0,
                    $orden['costo_repuestos'] ?? 0,
                    $orden['costo_mano_obra'] ?? 0,
                    $orden['costo_total'] ?? 0,
                    $orden['status'] ?? ''
                ]);
            }
        } else {
            // Resumen
            fputcsv($output, ['REPORTE FINANCIERO RESUMEN']);
            fputcsv($output, ['Período:', $fechaInicio . ' al ' . $fechaFin]);
            fputcsv($output, []);
            fputcsv($output, ['Métrica', 'Valor']);
            fputcsv($output, ['Total Órdenes', $stats['total_ordenes']]);
            fputcsv($output, ['Costo Total', 'S/ ' . number_format($stats['total_costos'], 2)]);
            fputcsv($output, ['Costo Repuestos', 'S/ ' . number_format($stats['total_repuestos'], 2)]);
            fputcsv($output, ['Costo Mano de Obra', 'S/ ' . number_format($stats['total_mano_obra'], 2)]);
            fputcsv($output, ['Promedio por Orden', 'S/ ' . number_format($stats['promedio_costo'], 2)]);
            fputcsv($output, ['Total Horas', $stats['total_horas']]);
            fputcsv($output, ['Promedio Horas', $stats['promedio_horas']]);
        }
        
        fclose($output);
        exit;
    }
}
?>