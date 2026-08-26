<?php
// controller/OrdenController.php
// Controlador de órdenes de trabajo - CORREGIDO

require_once __DIR__ . '/../model/OrdenTrabajo.php';
require_once __DIR__ . '/../model/Tecnico.php';
require_once __DIR__ . '/../model/Supervisor.php';
require_once __DIR__ . '/../model/Planta.php';
require_once __DIR__ . '/../model/Area.php';
require_once __DIR__ . '/../model/Equipo.php';
require_once __DIR__ . '/../model/Componente.php';
require_once __DIR__ . '/../helpers/ValidationHelper.php';
require_once __DIR__ . '/../helpers/SecurityHelper.php';

class OrdenController {
    
    private OrdenTrabajo $ordenModel;
    
    public function __construct() {
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: /proyecto/auth/login');
            exit;
        }
        $this->ordenModel = new OrdenTrabajo();
    }
    
    /**
     * Lista de órdenes
     */
    public function index() {
        $filtros = [
            'status' => $_GET['status'] ?? '',
            'buscar' => $_GET['buscar'] ?? '',
            'tecnico_id' => $_GET['tecnico_id'] ?? '',
            'prioridad' => $_GET['prioridad'] ?? '',
            'fecha_desde' => $_GET['fecha_desde'] ?? '',
            'fecha_hasta' => $_GET['fecha_hasta'] ?? ''
        ];
        
        // Paginación
        $pagina = (int)($_GET['pagina'] ?? 1);
        $porPagina = 20;
        $offset = ($pagina - 1) * $porPagina;
        
        $ordenes = $this->ordenModel->obtenerTodos($filtros, $porPagina, $offset);
        $estadisticas = $this->ordenModel->obtenerEstadisticas();
        
        // Cargar datos para filtros
        $tecnicoModel = new Tecnico();
        $tecnicos = $tecnicoModel->obtenerTodos();
        
        $seccion = 'ordenes';
        $titulo = 'Gestión de Órdenes';
        require_once __DIR__ . '/../views/ordenes/index.php';
    }
    
    /**
     * Ver detalle de orden
     */
    public function ver($id) {
        $orden = $this->ordenModel->obtenerPorId($id);
        
        if (!$orden) {
            $_SESSION['error'] = 'Orden no encontrada';
            header('Location: /proyecto/ordenes');
            exit;
        }
        
        $seccion = 'ordenes';
        $titulo = 'Detalle de Orden - ' . ($orden['num_om'] ?? '');
        require_once __DIR__ . '/../views/ordenes/ver.php';
    }
    
    /**
     * Formulario para crear orden
     */
    public function crear() {
        // Cargar datos para selects
        $tecnicoModel = new Tecnico();
        $tecnicos = $tecnicoModel->obtenerTodos();
        
        $supervisorModel = new Supervisor();
        $supervisores = $supervisorModel->obtenerTodos();
        
        $plantaModel = new Planta();
        $plantas = $plantaModel->obtenerTodos();
        
        $areaModel = new Area();
        $areas = $areaModel->obtenerTodos();
        
        $equipoModel = new Equipo();
        $equipos = $equipoModel->obtenerTodos();
        
        $componenteModel = new Componente();
        $componentes = $componenteModel->obtenerTodos();
        
        $seccion = 'ordenes';
        $titulo = 'Crear Orden de Trabajo';
        require_once __DIR__ . '/../views/ordenes/crear.php';
    }
    
    /**
     * Guardar nueva orden
     */
    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /proyecto/ordenes/crear');
            exit;
        }
        
        // Validar CSRF
        if (!SecurityHelper::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'Token de seguridad inválido';
            header('Location: /proyecto/ordenes/crear');
            exit;
        }
        
        // Generar número de OM si no viene
        $num_om = $_POST['num_om'] ?? $this->generarNumeroOM();
        
        $datos = [
            'num_om' => $num_om,
            'titulo' => ValidationHelper::sanitize($_POST['titulo'] ?? ''),
            'descripcion_mantenimiento' => ValidationHelper::sanitize($_POST['descripcion_mantenimiento'] ?? ''),
            'id_planta' => $_POST['id_planta'] ?? null,
            'id_area' => $_POST['id_area'] ?? null,
            'id_equipo' => $_POST['id_equipo'] ?? null,
            'id_componente' => $_POST['id_componente'] ?? null,
            'tecnico_id' => $_POST['tecnico_id'] ?? null,
            'id_supervisor' => $_POST['id_supervisor'] ?? null,
            'prioridad' => $_POST['prioridad'] ?? 'Media',
            'tipo_mantenimiento' => $_POST['tipo_mantenimiento'] ?? 'CORRECTIVO',
            'tipo_actividad' => $_POST['tipo_actividad'] ?? '',
            'fecha_inicio' => $_POST['fecha_inicio'] ?? date('Y-m-d'),
            'fecha_estimada' => $_POST['fecha_estimada'] ?? null,
            'horas_duracion' => $_POST['horas_duracion'] ?? 0,
            'horas_trabajadas' => $_POST['horas_trabajadas'] ?? 0,
            'tarifa_tecnico' => $_POST['tarifa_tecnico'] ?? 0,
            'costo_repuestos' => $_POST['costo_repuestos'] ?? 0,
            'solicitante' => ValidationHelper::sanitize($_POST['solicitante'] ?? ''),
            'supervisor_solicitante' => ValidationHelper::sanitize($_POST['supervisor_solicitante'] ?? ''),
            'creado_por' => $_SESSION['usuario_id'] ?? 1
        ];
        
        $result = $this->ordenModel->crear($datos);
        
        if ($result) {
            $_SESSION['mensaje'] = 'Orden creada correctamente';
            $_SESSION['mensaje_tipo'] = 'success';
            header('Location: /proyecto/ordenes/ver/' . $result);
        } else {
            $errores = $_SESSION['errores'] ?? [];
            $errorMsg = !empty($errores) ? implode(', ', $errores) : ($_SESSION['error'] ?? 'Error desconocido');
            $_SESSION['error'] = 'Error al crear la orden: ' . $errorMsg;
            header('Location: /proyecto/ordenes/crear');
        }
        exit;
    }
    
    /**
     * Formulario para editar orden
     */
    public function editar($id) {
        $orden = $this->ordenModel->obtenerPorId($id);
        
        if (!$orden) {
            $_SESSION['error'] = 'Orden no encontrada';
            header('Location: /proyecto/ordenes');
            exit;
        }
        
        // Cargar datos para selects
        $tecnicoModel = new Tecnico();
        $tecnicos = $tecnicoModel->obtenerTodos();
        
        $supervisorModel = new Supervisor();
        $supervisores = $supervisorModel->obtenerTodos();
        
        $plantaModel = new Planta();
        $plantas = $plantaModel->obtenerTodos();
        
        $areaModel = new Area();
        $areas = $areaModel->obtenerTodos();
        
        $equipoModel = new Equipo();
        $equipos = $equipoModel->obtenerTodos();
        
        $componenteModel = new Componente();
        $componentes = $componenteModel->obtenerTodos();
        
        $seccion = 'ordenes';
        $titulo = 'Editar Orden de Trabajo';
        require_once __DIR__ . '/../views/ordenes/editar.php';
    }
    
    /**
     * Actualizar orden
     */
    public function actualizar($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /proyecto/ordenes/editar/' . $id);
            exit;
        }
        
        // Validar CSRF
        if (!SecurityHelper::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'Token de seguridad inválido';
            header('Location: /proyecto/ordenes/editar/' . $id);
            exit;
        }
        
        $datos = [
            'titulo' => ValidationHelper::sanitize($_POST['titulo'] ?? ''),
            'descripcion_mantenimiento' => ValidationHelper::sanitize($_POST['descripcion_mantenimiento'] ?? ''),
            'descripcion_realizada' => ValidationHelper::sanitize($_POST['descripcion_realizada'] ?? ''),
            'id_planta' => $_POST['id_planta'] ?? null,
            'id_area' => $_POST['id_area'] ?? null,
            'id_equipo' => $_POST['id_equipo'] ?? null,
            'id_componente' => $_POST['id_componente'] ?? null,
            'tecnico_id' => $_POST['tecnico_id'] ?? null,
            'id_supervisor' => $_POST['id_supervisor'] ?? null,
            'prioridad' => $_POST['prioridad'] ?? 'Media',
            'tipo_mantenimiento' => $_POST['tipo_mantenimiento'] ?? 'CORRECTIVO',
            'tipo_actividad' => $_POST['tipo_actividad'] ?? '',
            'fecha_inicio' => $_POST['fecha_inicio'] ?? date('Y-m-d'),
            'fecha_estimada' => $_POST['fecha_estimada'] ?? null,
            'horas_duracion' => $_POST['horas_duracion'] ?? 0,
            'horas_trabajadas' => $_POST['horas_trabajadas'] ?? 0,
            'tarifa_tecnico' => $_POST['tarifa_tecnico'] ?? 0,
            'costo_repuestos' => $_POST['costo_repuestos'] ?? 0,
            'solicitante' => ValidationHelper::sanitize($_POST['solicitante'] ?? ''),
            'supervisor_solicitante' => ValidationHelper::sanitize($_POST['supervisor_solicitante'] ?? ''),
            'observaciones_tecnico' => ValidationHelper::sanitize($_POST['observaciones_tecnico'] ?? ''),
            'observaciones_cierre' => ValidationHelper::sanitize($_POST['observaciones_cierre'] ?? ''),
            'status' => $_POST['status'] ?? 'PENDIENTE',
            'actualizado_por' => $_SESSION['usuario_id'] ?? 1
        ];
        
        $result = $this->ordenModel->actualizar($id, $datos);
        
        if ($result) {
            $_SESSION['mensaje'] = 'Orden actualizada correctamente';
            $_SESSION['mensaje_tipo'] = 'success';
            header('Location: /proyecto/ordenes/ver/' . $id);
        } else {
            $errores = $_SESSION['errores'] ?? [];
            $errorMsg = !empty($errores) ? implode(', ', $errores) : ($_SESSION['error'] ?? 'Error desconocido');
            $_SESSION['error'] = 'Error al actualizar la orden: ' . $errorMsg;
            header('Location: /proyecto/ordenes/editar/' . $id);
        }
        exit;
    }
    
    /**
     * Formulario para cerrar orden
     */
    public function cerrar($id) {
        $orden = $this->ordenModel->obtenerPorId($id);
        
        if (!$orden) {
            $_SESSION['error'] = 'Orden no encontrada';
            header('Location: /proyecto/ordenes');
            exit;
        }
        
        // Verificar que la orden esté en estado EJECUTADA o EN_PROCESO
        if (!in_array($orden['status'], ['EJECUTADA', 'EN_PROCESO'])) {
            $_SESSION['error'] = 'La orden debe estar en estado EJECUTADA o EN_PROCESO para cerrarla';
            header('Location: /proyecto/ordenes/ver/' . $id);
            exit;
        }
        
        $seccion = 'ordenes';
        $titulo = 'Cerrar Orden de Trabajo';
        require_once __DIR__ . '/../views/ordenes/cerrar.php';
    }
    
    /**
     * Procesar cierre de orden
     */
    public function procesarCierre($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /proyecto/ordenes/cerrar/' . $id);
            exit;
        }
        
        // Validar CSRF
        if (!SecurityHelper::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'Token de seguridad inválido';
            header('Location: /proyecto/ordenes/cerrar/' . $id);
            exit;
        }
        
        $datos = [
            'descripcion_realizada' => ValidationHelper::sanitize($_POST['descripcion_realizada'] ?? ''),
            'pasos_ejecutados' => ValidationHelper::sanitize($_POST['pasos_ejecutados'] ?? ''),
            'horas_trabajadas' => (float)($_POST['horas_trabajadas'] ?? 0),
            'tarifa_tecnico' => (float)($_POST['tarifa_tecnico'] ?? 0),
            'costo_repuestos' => (float)($_POST['costo_repuestos'] ?? 0),
            'observaciones_tecnico' => ValidationHelper::sanitize($_POST['observaciones_tecnico'] ?? ''),
            'observaciones_cierre' => ValidationHelper::sanitize($_POST['observaciones_cierre'] ?? ''),
            'foto_evidencia' => $_POST['foto_evidencia'] ?? '',
            'firma_tecnico' => $_POST['firma_tecnico'] ?? '',
            'actualizado_por' => $_SESSION['usuario_id'] ?? 1
        ];
        
        $result = $this->ordenModel->cerrar($id, $datos);
        
        if ($result) {
            $_SESSION['mensaje'] = 'Orden cerrada correctamente';
            $_SESSION['mensaje_tipo'] = 'success';
            header('Location: /proyecto/ordenes/ver/' . $id);
        } else {
            $_SESSION['error'] = 'Error al cerrar la orden: ' . ($_SESSION['error'] ?? '');
            header('Location: /proyecto/ordenes/cerrar/' . $id);
        }
        exit;
    }
    
    /**
     * Eliminar orden
     */
    public function eliminar($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /proyecto/ordenes');
            exit;
        }
        
        // Validar CSRF
        if (!SecurityHelper::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'Token de seguridad inválido';
            header('Location: /proyecto/ordenes');
            exit;
        }
        
        $result = $this->ordenModel->eliminar($id);
        
        if ($result) {
            $_SESSION['mensaje'] = 'Orden eliminada correctamente';
            $_SESSION['mensaje_tipo'] = 'success';
        } else {
            $_SESSION['error'] = 'Error al eliminar la orden';
        }
        
        header('Location: /proyecto/ordenes');
        exit;
    }
    
    /**
     * Estadísticas de órdenes
     */
    public function estadisticas() {
        $fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
        $fechaFin = $_GET['fecha_fin'] ?? date('Y-m-t');
        
        $estadisticas = $this->ordenModel->obtenerEstadisticas();
        
        // Obtener prioridades
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT 
                    SUM(CASE WHEN prioridad = 'Alta' THEN 1 ELSE 0 END) as alta,
                    SUM(CASE WHEN prioridad = 'Media' THEN 1 ELSE 0 END) as media,
                    SUM(CASE WHEN prioridad = 'Baja' THEN 1 ELSE 0 END) as baja
                FROM ordenes_mantenimiento";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $prioridades = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Evolución mensual
        $sql = "SELECT 
                    DATE_FORMAT(fecha_creacion, '%Y-%m') as mes,
                    COUNT(*) as total
                FROM ordenes_mantenimiento
                WHERE YEAR(fecha_creacion) = YEAR(CURDATE())
                GROUP BY DATE_FORMAT(fecha_creacion, '%Y-%m')
                ORDER BY mes ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $evolucion_mensual = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Rendimiento por técnico
        $sql = "SELECT 
                    t.nombre,
                    COUNT(o.id) as total,
                    SUM(CASE WHEN o.status IN ('CERRADA', 'APROBADA', 'EJECUTADA') THEN 1 ELSE 0 END) as completadas
                FROM ordenes_mantenimiento o
                LEFT JOIN tecnicos t ON o.tecnico_id = t.id
                WHERE o.tecnico_id IS NOT NULL
                GROUP BY o.tecnico_id";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $rendimiento_tecnicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $seccion = 'ordenes';
        $titulo = 'Estadísticas de Órdenes';
        require_once __DIR__ . '/../views/ordenes/estadisticas.php';
    }
    
    /**
     * Generar número de OM automático
     */
    private function generarNumeroOM() {
        $anio = date('Y');
        $mes = date('m');
        $sql = "SELECT COUNT(*) as total FROM ordenes_mantenimiento WHERE YEAR(fecha_creacion) = ? AND MONTH(fecha_creacion) = ?";
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute([$anio, $mes]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $numero = ($result['total'] ?? 0) + 1;
        return "OT-" . $anio . "-" . $mes . "-" . str_pad($numero, 4, '0', STR_PAD_LEFT);
    }
    
    /**
     * Cambiar estado de la orden (API para AJAX)
     */
    public function cambiarEstado() {
        // Solo acepta POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            exit;
        }
        
        // Verificar autenticación
        if (!isset($_SESSION['usuario_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'No autenticado']);
            exit;
        }
        
        // Validar CSRF
        if (!SecurityHelper::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            echo json_encode(['error' => 'Token inválido']);
            exit;
        }
        
        $id = (int)($_POST['id'] ?? 0);
        $estado = ValidationHelper::sanitize($_POST['estado'] ?? '');
        $observaciones = ValidationHelper::sanitize($_POST['observaciones'] ?? '');
        
        if ($id <= 0 || empty($estado)) {
            http_response_code(400);
            echo json_encode(['error' => 'Datos incompletos']);
            exit;
        }
        
        $result = $this->ordenModel->cambiarEstado($id, $estado, $observaciones);
        
        if ($result) {
            echo json_encode(['success' => true, 'mensaje' => 'Estado actualizado correctamente']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Error al actualizar el estado']);
        }
        exit;
    }
}
?>