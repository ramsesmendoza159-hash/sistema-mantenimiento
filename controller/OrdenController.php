<?php
// controller/OrdenController.php
// Ubicación: C:\xampp\htdocs\proyecto\controller\OrdenController.php

// Incluir el controlador base
require_once __DIR__ . '/../helpers/Controller.php';

class OrdenController extends Controller {
    
    private $db;
    private $ordenModel;
    private $tecnicosModel;
    private $supervisoresModel;
    private $plantasModel;
    private $areasModel;
    private $equiposModel;
    private $componentesModel;
    private $repuestosModel;

    public function __construct() {
        parent::__construct();
        
        // Verificar autenticación
        if (!$this->authHelper->isLoggedIn()) {
            header('Location: /proyecto/auth/login');
            exit;
        }
        
        // Obtener conexión a la base de datos
        $this->db = Database::getInstance()->getConnection();
        
        // Cargar modelos
        require_once __DIR__ . '/../model/OrdenTrabajo.php';
        require_once __DIR__ . '/../model/TecnicosModel.php';
        require_once __DIR__ . '/../model/SupervisoresModel.php';
        require_once __DIR__ . '/../model/PlantasModel.php';
        require_once __DIR__ . '/../model/AreasModel.php';
        require_once __DIR__ . '/../model/EquiposModel.php';
        require_once __DIR__ . '/../model/ComponentesModel.php';
        require_once __DIR__ . '/../model/RepuestosModel.php';
        
        $this->ordenModel = new OrdenTrabajo();
        $this->tecnicosModel = new TecnicosModel();
        $this->supervisoresModel = new SupervisoresModel();
        $this->plantasModel = new PlantasModel();
        $this->areasModel = new AreasModel();
        $this->equiposModel = new EquiposModel();
        $this->componentesModel = new ComponentesModel();
        $this->repuestosModel = new RepuestosModel();
    }

    /**
     * Listado de órdenes de trabajo
     * URL: /ordenes
     */
    public function index() {
        // Verificar permisos
        $rol = $this->authHelper->getRole();
        
        try {
            $filtros = [];
            
            // Si es técnico, solo ver sus órdenes
            if ($rol === 'tecnico') {
                $filtros['tecnico_id'] = $this->authHelper->getUserId();
            }
            
            // Filtros desde GET
            if (isset($_GET['estado']) && !empty($_GET['estado'])) {
                $filtros['status'] = $_GET['estado'];
            }
            
            if (isset($_GET['prioridad']) && !empty($_GET['prioridad'])) {
                $filtros['prioridad'] = $_GET['prioridad'];
            }
            
            if (isset($_GET['buscar']) && !empty($_GET['buscar'])) {
                $filtros['buscar'] = $_GET['buscar'];
            }
            
            if (isset($_GET['fecha_desde']) && !empty($_GET['fecha_desde'])) {
                $filtros['fecha_desde'] = $_GET['fecha_desde'];
            }
            
            if (isset($_GET['fecha_hasta']) && !empty($_GET['fecha_hasta'])) {
                $filtros['fecha_hasta'] = $_GET['fecha_hasta'];
            }
            
            // Paginación
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
            if ($page < 1) {
                $page = 1;
            }
            $offset = ($page - 1) * $perPage;
            
            // Obtener órdenes
            $ordenes = $this->ordenModel->obtenerTodos($filtros);
            
            if (!is_array($ordenes)) {
                $ordenes = [];
            }
            
            $total = count($ordenes);
            $totalPages = ceil($total / $perPage);
            if ($totalPages < 1) {
                $totalPages = 1;
            }
            if ($page > $totalPages) {
                $page = $totalPages;
            }
            $ordenes = array_slice($ordenes, $offset, $perPage);
            
            // Obtener técnicos para filtros (si es admin)
            $tecnicos = [];
            if ($this->authHelper->isAdmin()) {
                $tecnicos = $this->tecnicosModel->obtenerTodos(['estado' => 'activo']);
            }
            
            // Obtener estados para filtros
            $estados = $this->getEstados();
            $prioridades = ['Baja', 'Media', 'Alta', 'Urgente'];
            
        } catch (Exception $e) {
            error_log("Error en OrdenController index: " . $e->getMessage());
            $ordenes = [];
            $tecnicos = [];
            $totalPages = 1;
            $page = 1;
            $total = 0;
            $estados = [];
            $prioridades = [];
            $_SESSION['error'] = 'Error al cargar las órdenes: ' . $e->getMessage();
        }
        
        $this->view('ordenes/index', [
            'ordenes' => $ordenes,
            'tecnicos' => $tecnicos,
            'totalPages' => $totalPages,
            'page' => $page,
            'total' => $total,
            'estados' => $estados,
            'prioridades' => $prioridades,
            'rol' => $rol
        ]);
    }

    /**
     * Formulario para crear orden de trabajo
     * URL: /ordenes/crear
     */
    public function crear() {
        // Verificar permisos (admin o supervisor)
        if (!$this->authHelper->isAdmin() && !$this->authHelper->isSupervisor()) {
            $_SESSION['error'] = 'No tienes permisos para crear órdenes';
            $this->redirect('/proyecto/ordenes');
        }
        
        try {
            $plantas = $this->plantasModel->obtenerTodos();
            $areas = $this->areasModel->obtenerTodos();
            $equipos = $this->equiposModel->obtenerTodos();
            $componentes = $this->componentesModel->obtenerTodos();
            $tecnicos = $this->tecnicosModel->obtenerTodos(['estado' => 'activo']);
            $supervisores = $this->supervisoresModel->obtenerTodos(['estado' => 'activo']);
            $repuestos = $this->repuestosModel->obtenerTodos(['estado' => 'activo']);
            
            // Generar número de orden
            $num_om = $this->generarNumeroOM();
            
        } catch (Exception $e) {
            error_log("Error en OrdenController crear: " . $e->getMessage());
            $plantas = [];
            $areas = [];
            $equipos = [];
            $componentes = [];
            $tecnicos = [];
            $supervisores = [];
            $repuestos = [];
            $num_om = 'OT-2026-001';
            $_SESSION['error'] = 'Error al cargar el formulario: ' . $e->getMessage();
        }
        
        $this->view('ordenes/crear', [
            'plantas' => $plantas,
            'areas' => $areas,
            'equipos' => $equipos,
            'componentes' => $componentes,
            'tecnicos' => $tecnicos,
            'supervisores' => $supervisores,
            'repuestos' => $repuestos,
            'num_om' => $num_om
        ]);
    }

    /**
     * Guardar nueva orden de trabajo
     * URL: /ordenes/guardar (POST)
     */
    public function guardar() {
        $this->requirePost();
        
        // Verificar permisos
        if (!$this->authHelper->isAdmin() && !$this->authHelper->isSupervisor()) {
            $_SESSION['error'] = 'No tienes permisos para crear órdenes';
            $this->redirect('/proyecto/ordenes');
        }
        
        // Obtener datos del formulario
        $datos = [
            'num_om' => $this->post('num_om', ''),
            'titulo' => $this->post('titulo', ''),
            'descripcion_mantenimiento' => $this->post('descripcion', ''),
            'prioridad' => $this->post('prioridad', 'Media'),
            'tipo_actividad' => $this->post('tipo_actividad', ''),
            'tipo_mantenimiento' => $this->post('tipo_mantenimiento', ''),
            'id_planta' => $this->post('id_planta', null),
            'id_area' => $this->post('id_area', null),
            'id_equipo' => $this->post('id_equipo', null),
            'id_componente' => $this->post('id_componente', null),
            'tecnico_id' => $this->post('tecnico_id', null),
            'id_supervisor' => $this->post('id_supervisor', null),
            'solicitante' => $this->post('solicitante', ''),
            'fecha_emision' => $this->post('fecha_emision', date('Y-m-d')),
            'fecha_inicio' => $this->post('fecha_inicio', date('Y-m-d')),
            'fecha_estimada' => $this->post('fecha_estimada', null),
            'horas_duracion' => $this->post('horas_duracion', 0),
            'creado_por' => $_SESSION['usuario_id'] ?? 1
        ];
        
        // Validar datos obligatorios
        if (empty($datos['num_om']) || empty($datos['titulo']) || empty($datos['descripcion_mantenimiento'])) {
            $_SESSION['error'] = 'Número de orden, título y descripción son obligatorios';
            $this->redirect('/proyecto/ordenes/crear');
        }
        
        try {
            // Verificar que el número de orden no exista
            $existe = $this->ordenModel->obtenerPorNumOM($datos['num_om']);
            if ($existe) {
                $_SESSION['error'] = 'El número de orden ya existe. Por favor, genera uno nuevo.';
                $this->redirect('/proyecto/ordenes/crear');
            }
            
            $id = $this->ordenModel->crear($datos);
            
            if ($id) {
                // Guardar repuestos si se seleccionaron
                $repuestos_ids = $this->post('repuestos_ids', []);
                $repuestos_cantidades = $this->post('repuestos_cantidades', []);
                $repuestos_costos = $this->post('repuestos_costos', []);
                
                if (!empty($repuestos_ids)) {
                    $this->guardarRepuestosOrden($id, $repuestos_ids, $repuestos_cantidades, $repuestos_costos);
                }
                
                $_SESSION['success'] = 'Orden de trabajo creada exitosamente. N° ' . $datos['num_om'];
                $this->redirect('/proyecto/ordenes/ver/' . $id);
            } else {
                $_SESSION['error'] = 'Error al crear la orden de trabajo';
                $this->redirect('/proyecto/ordenes/crear');
            }
            
        } catch (Exception $e) {
            error_log("Error en guardar orden: " . $e->getMessage());
            $_SESSION['error'] = 'Error al crear la orden: ' . $e->getMessage();
            $this->redirect('/proyecto/ordenes/crear');
        }
    }

    /**
     * Formulario para editar orden de trabajo - CORREGIDO
     * URL: /ordenes/editar/{id}
     */
    public function editar($id) {
        $id = (int)$id;
        if ($id <= 0) {
            $_SESSION['error'] = 'ID de orden inválido';
            $this->redirect('/proyecto/ordenes');
            return;
        }
        
        // Verificar permisos
        if (!$this->authHelper->isAdmin() && !$this->authHelper->isSupervisor()) {
            $_SESSION['error'] = 'No tienes permisos para editar órdenes';
            $this->redirect('/proyecto/ordenes');
            return;
        }
        
        try {
            $orden = $this->ordenModel->obtenerPorId($id);
            
            if (!$orden) {
                $_SESSION['error'] = 'Orden no encontrada';
                $this->redirect('/proyecto/ordenes');
                return;
            }
            
            // Verificar que la orden no esté cerrada o cancelada
            if ($orden['status'] === 'CERRADA' || $orden['status'] === 'CANCELADA' || $orden['status'] === 'APROBADA') {
                $_SESSION['error'] = 'No se puede editar una orden cerrada, cancelada o aprobada';
                $this->redirect('/proyecto/ordenes/ver/' . $id);
                return;
            }
            
            $plantas = $this->plantasModel->obtenerTodos();
            $areas = $this->areasModel->obtenerTodos();
            $equipos = $this->equiposModel->obtenerTodos();
            $componentes = $this->componentesModel->obtenerTodos();
            $tecnicos = $this->tecnicosModel->obtenerTodos(['estado' => 'activo']);
            $supervisores = $this->supervisoresModel->obtenerTodos(['estado' => 'activo']);
            $repuestos = $this->repuestosModel->obtenerTodos(['estado' => 'activo']);
            
            // Obtener repuestos de la orden
            $repuestos_orden = $this->obtenerRepuestosOrden($id);
            
            // Obtener técnicos adicionales de la orden
            $tecnicos_orden = $this->obtenerTecnicosOrden($id);
            
        } catch (Exception $e) {
            error_log("Error en OrdenController editar: " . $e->getMessage());
            $orden = null;
            $plantas = [];
            $areas = [];
            $equipos = [];
            $componentes = [];
            $tecnicos = [];
            $supervisores = [];
            $repuestos = [];
            $repuestos_orden = [];
            $tecnicos_orden = [];
            $_SESSION['error'] = 'Error al cargar el formulario: ' . $e->getMessage();
            $this->redirect('/proyecto/ordenes');
            return;
        }
        
        $this->view('ordenes/editar', [
            'orden' => $orden,
            'plantas' => $plantas,
            'areas' => $areas,
            'equipos' => $equipos,
            'componentes' => $componentes,
            'tecnicos' => $tecnicos,
            'supervisores' => $supervisores,
            'repuestos' => $repuestos,
            'repuestos_orden' => $repuestos_orden,
            'tecnicos_orden' => $tecnicos_orden
        ]);
    }

    /**
     * Actualizar orden de trabajo
     * URL: /ordenes/actualizar/{id} (POST)
     */
    public function actualizar($id) {
        $this->requirePost();
        
        $id = (int)$id;
        if ($id <= 0) {
            $_SESSION['error'] = 'ID de orden inválido';
            $this->redirect('/proyecto/ordenes');
            return;
        }
        
        // Verificar permisos
        if (!$this->authHelper->isAdmin() && !$this->authHelper->isSupervisor()) {
            $_SESSION['error'] = 'No tienes permisos para editar órdenes';
            $this->redirect('/proyecto/ordenes');
            return;
        }
        
        $datos = [
            'titulo' => $this->post('titulo', ''),
            'descripcion_mantenimiento' => $this->post('descripcion', ''),
            'prioridad' => $this->post('prioridad', 'Media'),
            'tipo_actividad' => $this->post('tipo_actividad', ''),
            'tipo_mantenimiento' => $this->post('tipo_mantenimiento', ''),
            'id_planta' => $this->post('id_planta', null),
            'id_area' => $this->post('id_area', null),
            'id_equipo' => $this->post('id_equipo', null),
            'id_componente' => $this->post('id_componente', null),
            'tecnico_id' => $this->post('tecnico_id', null),
            'id_supervisor' => $this->post('id_supervisor', null),
            'solicitante' => $this->post('solicitante', ''),
            'fecha_emision' => $this->post('fecha_emision', date('Y-m-d')),
            'fecha_inicio' => $this->post('fecha_inicio', date('Y-m-d')),
            'fecha_estimada' => $this->post('fecha_estimada', null),
            'horas_duracion' => $this->post('horas_duracion', 0),
            'status' => $this->post('status', 'PENDIENTE'),
            'actualizado_por' => $_SESSION['usuario_id'] ?? 1
        ];
        
        if (empty($datos['titulo']) || empty($datos['descripcion_mantenimiento'])) {
            $_SESSION['error'] = 'Título y descripción son obligatorios';
            $this->redirect('/proyecto/ordenes/editar/' . $id);
            return;
        }
        
        try {
            $resultado = $this->ordenModel->actualizar($id, $datos);
            
            if ($resultado) {
                $_SESSION['success'] = 'Orden actualizada correctamente';
                $this->redirect('/proyecto/ordenes/ver/' . $id);
            } else {
                $_SESSION['error'] = 'Error al actualizar la orden';
                $this->redirect('/proyecto/ordenes/editar/' . $id);
            }
            
        } catch (Exception $e) {
            error_log("Error en actualizar orden: " . $e->getMessage());
            $_SESSION['error'] = 'Error al actualizar la orden: ' . $e->getMessage();
            $this->redirect('/proyecto/ordenes/editar/' . $id);
        }
    }

    /**
     * Ver detalle de orden de trabajo
     * URL: /ordenes/ver/{id}
     */
    public function ver($id) {
        $id = (int)$id;
        if ($id <= 0) {
            $_SESSION['error'] = 'ID de orden inválido';
            $this->redirect('/proyecto/ordenes');
            return;
        }
        
        try {
            $orden = $this->ordenModel->obtenerPorId($id);
            
            if (!$orden) {
                $_SESSION['error'] = 'Orden no encontrada';
                $this->redirect('/proyecto/ordenes');
                return;
            }
            
            // Verificar permisos de acceso
            $rol = $this->authHelper->getRole();
            if ($rol === 'tecnico' && $orden['tecnico_id'] != $this->authHelper->getUserId()) {
                $_SESSION['error'] = 'No tienes permiso para ver esta orden';
                $this->redirect('/proyecto/tecnico/mis_ordenes');
                return;
            }
            
            // Obtener repuestos de la orden
            $repuestos = $this->obtenerRepuestosOrden($id);
            
            // Obtener historial de cambios
            $historial = $this->getHistorial($id);
            
            // Obtener documentos relacionados (si existe)
            $documentos = $this->getDocumentosOrden($id);
            
        } catch (Exception $e) {
            error_log("Error en OrdenController ver: " . $e->getMessage());
            $orden = null;
            $repuestos = [];
            $historial = [];
            $documentos = [];
            $_SESSION['error'] = 'Error al cargar la orden: ' . $e->getMessage();
            $this->redirect('/proyecto/ordenes');
            return;
        }
        
        $this->view('ordenes/ver', [
            'orden' => $orden,
            'repuestos' => $repuestos,
            'historial' => $historial,
            'documentos' => $documentos
        ]);
    }

    /**
     * Cerrar orden de trabajo
     * URL: /ordenes/cerrar/{id}
     */
    public function cerrar($id) {
        $id = (int)$id;
        if ($id <= 0) {
            $_SESSION['error'] = 'ID de orden inválido';
            $this->redirect('/proyecto/ordenes');
            return;
        }
        
        try {
            $orden = $this->ordenModel->obtenerPorId($id);
            
            if (!$orden) {
                $_SESSION['error'] = 'Orden no encontrada';
                $this->redirect('/proyecto/ordenes');
                return;
            }
            
            // Verificar permisos
            $rol = $this->authHelper->getRole();
            if ($rol === 'tecnico' && $orden['tecnico_id'] != $this->authHelper->getUserId()) {
                $_SESSION['error'] = 'No tienes permiso para cerrar esta orden';
                $this->redirect('/proyecto/tecnico/mis_ordenes');
                return;
            }
            
            // Verificar que la orden esté en un estado válido
            if ($orden['status'] === 'CERRADA' || $orden['status'] === 'CANCELADA') {
                $_SESSION['error'] = 'Esta orden ya está cerrada o cancelada';
                $this->redirect('/proyecto/ordenes/ver/' . $id);
                return;
            }
            
        } catch (Exception $e) {
            error_log("Error en OrdenController cerrar: " . $e->getMessage());
            $_SESSION['error'] = 'Error al cargar la orden: ' . $e->getMessage();
            $this->redirect('/proyecto/ordenes');
            return;
        }
        
        $this->view('ordenes/cerrar', ['orden' => $orden]);
    }

    /**
     * Procesar cierre de orden de trabajo
     * URL: /ordenes/procesar_cierre/{id} (POST)
     */
    public function procesar_cierre($id) {
        $this->requirePost();
        
        $id = (int)$id;
        if ($id <= 0) {
            $_SESSION['error'] = 'ID de orden inválido';
            $this->redirect('/proyecto/ordenes');
            return;
        }
        
        try {
            $orden = $this->ordenModel->obtenerPorId($id);
            
            if (!$orden) {
                $_SESSION['error'] = 'Orden no encontrada';
                $this->redirect('/proyecto/ordenes');
                return;
            }
            
            // Verificar permisos
            $rol = $this->authHelper->getRole();
            if ($rol === 'tecnico' && $orden['tecnico_id'] != $this->authHelper->getUserId()) {
                $_SESSION['error'] = 'No tienes permiso para cerrar esta orden';
                $this->redirect('/proyecto/tecnico/mis_ordenes');
                return;
            }
            
            $datos = [
                'descripcion_realizada' => $this->post('descripcion_realizada', ''),
                'pasos_ejecutados' => $this->post('pasos_ejecutados', ''),
                'horas_trabajadas' => $this->post('horas_trabajadas', 0),
                'tarifa_tecnico' => $this->post('tarifa_tecnico', 0),
                'costo_repuestos' => $this->post('costo_repuestos', 0),
                'costo_mano_obra' => $this->post('costo_mano_obra', 0),
                'foto_evidencia' => $this->post('foto_evidencia', ''),
                'firma_tecnico' => $this->post('firma_tecnico', ''),
                'observaciones_tecnico' => $this->post('observaciones_tecnico', ''),
                'observaciones_cierre' => $this->post('observaciones_cierre', ''),
                'actualizado_por' => $_SESSION['usuario_id'] ?? 1
            ];
            
            if (empty($datos['descripcion_realizada'])) {
                $_SESSION['error'] = 'La descripción del trabajo realizado es obligatoria';
                $this->redirect('/proyecto/ordenes/cerrar/' . $id);
                return;
            }
            
            // Calcular costos
            $datos['costo_mano_obra'] = $datos['horas_trabajadas'] * $datos['tarifa_tecnico'];
            $datos['costo_total'] = $datos['costo_repuestos'] + $datos['costo_mano_obra'];
            
            // Procesar evidencias (fotos)
            if (isset($_FILES['evidencias']) && !empty($_FILES['evidencias']['name'][0])) {
                $evidencias = $this->subirEvidencias($_FILES['evidencias']);
                if (!empty($evidencias)) {
                    $datos['foto_evidencia'] = implode(',', $evidencias);
                }
            }
            
            $resultado = $this->ordenModel->cerrar($id, $datos);
            
            if ($resultado) {
                // Actualizar inventario (descontar repuestos)
                $this->actualizarInventarioPorCierre($id);
                
                // Registrar en historial
                $this->guardarHistorial($id, $datos, 'CERRADA');
                
                $_SESSION['success'] = 'Orden cerrada exitosamente';
                $this->redirect('/proyecto/ordenes/ver/' . $id);
            } else {
                $_SESSION['error'] = 'Error al cerrar la orden';
                $this->redirect('/proyecto/ordenes/cerrar/' . $id);
            }
            
        } catch (Exception $e) {
            error_log("Error en procesar_cierre: " . $e->getMessage());
            $_SESSION['error'] = 'Error al cerrar la orden: ' . $e->getMessage();
            $this->redirect('/proyecto/ordenes/cerrar/' . $id);
        }
    }

    /**
     * Estadísticas de órdenes
     * URL: /ordenes/estadisticas
     */
    public function estadisticas() {
        $fechaInicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : date('Y-m-01');
        $fechaFin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : date('Y-m-t');
        
        try {
            // Estadísticas generales
            $stats = $this->ordenModel->obtenerEstadisticas();
            
            // Órdenes por mes
            $ordenes_por_mes = $this->ordenModel->obtenerCostosPorMes(date('Y'));
            
            // Órdenes por estado
            $ordenes_por_estado = $this->getOrdenesPorEstado($fechaInicio, $fechaFin);
            
            // Órdenes por prioridad
            $ordenes_por_prioridad = $this->getOrdenesPorPrioridad($fechaInicio, $fechaFin);
            
            // Órdenes por técnico
            $ordenes_por_tecnico = $this->ordenModel->obtenerCostosPorTecnico($fechaInicio, $fechaFin);
            
            // Órdenes por planta
            $ordenes_por_planta = $this->ordenModel->obtenerCostosPorPlanta($fechaInicio, $fechaFin);
            
            // Top técnicos
            $top_tecnicos = $this->tecnicosModel->obtenerTopTecnicos(5);
            
        } catch (Exception $e) {
            error_log("Error en OrdenController estadisticas: " . $e->getMessage());
            $stats = ['total' => 0, 'pendientes' => 0, 'en_proceso' => 0, 'cerradas' => 0, 'canceladas' => 0, 'aprobadas' => 0, 'rechazadas' => 0, 'promedio_horas' => 0, 'promedio_costo' => 0, 'total_costos' => 0];
            $ordenes_por_mes = [];
            $ordenes_por_estado = [];
            $ordenes_por_prioridad = [];
            $ordenes_por_tecnico = [];
            $ordenes_por_planta = [];
            $top_tecnicos = [];
            $_SESSION['error'] = 'Error al cargar las estadísticas';
        }
        
        $this->view('ordenes/estadisticas', [
            'stats' => $stats,
            'ordenes_por_mes' => $ordenes_por_mes,
            'ordenes_por_estado' => $ordenes_por_estado,
            'ordenes_por_prioridad' => $ordenes_por_prioridad,
            'ordenes_por_tecnico' => $ordenes_por_tecnico,
            'ordenes_por_planta' => $ordenes_por_planta,
            'top_tecnicos' => $top_tecnicos,
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin
        ]);
    }

    /**
     * Detalle de orden (formato compacto)
     * URL: /ordenes/detalle/{id}
     */
    public function detalle($id) {
        $id = (int)$id;
        if ($id <= 0) {
            $this->jsonResponse(['error' => 'ID de orden inválido'], 400);
            return;
        }
        
        try {
            $orden = $this->ordenModel->obtenerPorId($id);
            
            if (!$orden) {
                $this->jsonResponse(['error' => 'Orden no encontrada'], 404);
                return;
            }
            
            // Verificar permisos
            $rol = $this->authHelper->getRole();
            if ($rol === 'tecnico' && $orden['tecnico_id'] != $this->authHelper->getUserId()) {
                $this->jsonResponse(['error' => 'No tienes permiso para ver esta orden'], 403);
                return;
            }
            
            $this->jsonResponse($orden);
            
        } catch (Exception $e) {
            error_log("Error en OrdenController detalle: " . $e->getMessage());
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Eliminar orden de trabajo
     * URL: /ordenes/eliminar/{id} (POST)
     */
    public function eliminar($id) {
        $this->requirePost();
        
        $id = (int)$id;
        if ($id <= 0) {
            $_SESSION['error'] = 'ID de orden inválido';
            $this->redirect('/proyecto/ordenes');
            return;
        }
        
        // Verificar permisos (solo admin)
        if (!$this->authHelper->isAdmin()) {
            $_SESSION['error'] = 'No tienes permisos para eliminar órdenes';
            $this->redirect('/proyecto/ordenes');
            return;
        }
        
        try {
            $orden = $this->ordenModel->obtenerPorId($id);
            
            if (!$orden) {
                $_SESSION['error'] = 'Orden no encontrada';
                $this->redirect('/proyecto/ordenes');
                return;
            }
            
            // Verificar que la orden esté pendiente
            if ($orden['status'] !== 'PENDIENTE') {
                $_SESSION['error'] = 'Solo se pueden eliminar órdenes en estado PENDIENTE';
                $this->redirect('/proyecto/ordenes');
                return;
            }
            
            $resultado = $this->ordenModel->eliminar($id);
            
            if ($resultado) {
                // Eliminar repuestos asociados
                $stmt = $this->db->prepare("DELETE FROM ordenes_repuestos WHERE orden_id = ?");
                $stmt->execute([$id]);
                
                // Eliminar técnicos asociados
                $stmt = $this->db->prepare("DELETE FROM ordenes_tecnicos WHERE orden_id = ?");
                $stmt->execute([$id]);
                
                // Eliminar historial
                $stmt = $this->db->prepare("DELETE FROM ordenes_historial WHERE orden_id = ?");
                $stmt->execute([$id]);
                
                $_SESSION['success'] = 'Orden eliminada correctamente';
            } else {
                $_SESSION['error'] = 'Error al eliminar la orden';
            }
            
        } catch (Exception $e) {
            error_log("Error en eliminar orden: " . $e->getMessage());
            $_SESSION['error'] = 'Error al eliminar la orden: ' . $e->getMessage();
        }
        
        $this->redirect('/proyecto/ordenes');
    }

    /**
     * Cambiar estado de orden
     * URL: /ordenes/cambiar_estado/{id} (POST)
     */
    public function cambiar_estado($id) {
        $this->requirePost();
        
        $id = (int)$id;
        if ($id <= 0) {
            $_SESSION['error'] = 'ID de orden inválido';
            $this->redirect('/proyecto/ordenes');
            return;
        }
        
        // Verificar permisos (admin o supervisor)
        if (!$this->authHelper->isAdmin() && !$this->authHelper->isSupervisor()) {
            $_SESSION['error'] = 'No tienes permisos para cambiar el estado';
            $this->redirect('/proyecto/ordenes');
            return;
        }
        
        $estado = $this->post('estado', '');
        $observaciones = $this->post('observaciones', '');
        
        if (empty($estado)) {
            $_SESSION['error'] = 'Estado no válido';
            $this->redirect('/proyecto/ordenes');
            return;
        }
        
        try {
            // Verificar que la orden existe
            $orden = $this->ordenModel->obtenerPorId($id);
            if (!$orden) {
                $_SESSION['error'] = 'Orden no encontrada';
                $this->redirect('/proyecto/ordenes');
                return;
            }
            
            // Verificar transiciones válidas
            $estadosValidos = ['PENDIENTE', 'EN_PROCESO', 'EJECUTADA', 'CERRADA', 'CANCELADA', 'APROBADA', 'RECHAZADA'];
            if (!in_array($estado, $estadosValidos)) {
                $_SESSION['error'] = 'Estado no válido';
                $this->redirect('/proyecto/ordenes');
                return;
            }
            
            $resultado = $this->ordenModel->cambiarEstado($id, $estado, $observaciones);
            
            if ($resultado) {
                // Guardar en historial
                $this->guardarHistorial($id, ['status' => $estado, 'observaciones' => $observaciones], 'CAMBIO_ESTADO');
                
                $_SESSION['success'] = 'Estado actualizado correctamente a: ' . $estado;
            } else {
                $_SESSION['error'] = 'Error al cambiar el estado';
            }
            
        } catch (Exception $e) {
            error_log("Error en cambiar_estado: " . $e->getMessage());
            $_SESSION['error'] = 'Error al cambiar el estado: ' . $e->getMessage();
        }
        
        $this->redirect('/proyecto/ordenes/ver/' . $id);
    }

    /**
     * Asignar técnico a orden
     * URL: /ordenes/asignar_tecnico (POST)
     */
    public function asignar_tecnico() {
        $this->requirePost();
        
        $ordenId = $this->post('orden_id', 0);
        $tecnicoId = $this->post('tecnico_id', 0);
        
        if ($ordenId <= 0 || $tecnicoId <= 0) {
            $_SESSION['error'] = 'Datos inválidos';
            $this->redirect('/proyecto/ordenes');
            return;
        }
        
        // Verificar permisos (admin o supervisor)
        if (!$this->authHelper->isAdmin() && !$this->authHelper->isSupervisor()) {
            $_SESSION['error'] = 'No tienes permisos para asignar técnicos';
            $this->redirect('/proyecto/ordenes');
            return;
        }
        
        try {
            $resultado = $this->ordenModel->asignarTecnico($ordenId, $tecnicoId);
            
            if ($resultado) {
                // Guardar en historial
                $this->guardarHistorial($ordenId, ['tecnico_id' => $tecnicoId], 'ASIGNACION_TECNICO');
                
                $_SESSION['success'] = 'Técnico asignado correctamente';
            } else {
                $_SESSION['error'] = 'Error al asignar el técnico';
            }
            
        } catch (Exception $e) {
            error_log("Error en asignar_tecnico: " . $e->getMessage());
            $_SESSION['error'] = 'Error al asignar el técnico: ' . $e->getMessage();
        }
        
        $this->redirect('/proyecto/ordenes/ver/' . $ordenId);
    }

    // ==========================================
    // MÉTODOS PRIVADOS AUXILIARES
    // ==========================================

    /**
     * Generar número de orden automático
     */
    private function generarNumeroOM() {
        $anio = date('Y');
        $sql = "SELECT MAX(SUBSTRING_INDEX(num_om, '-', -1)) as ultimo FROM ordenes_mantenimiento WHERE num_om LIKE ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['OT-' . $anio . '-%']);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $ultimo = (int)($resultado['ultimo'] ?? 0);
        $nuevo = $ultimo + 1;
        
        return 'OT-' . $anio . '-' . str_pad($nuevo, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Obtener estados disponibles
     */
    private function getEstados() {
        return [
            'PENDIENTE' => 'Pendiente',
            'EN_PROCESO' => 'En Proceso',
            'EJECUTADA' => 'Ejecutada',
            'CERRADA' => 'Cerrada',
            'CANCELADA' => 'Cancelada',
            'APROBADA' => 'Aprobada',
            'RECHAZADA' => 'Rechazada'
        ];
    }

    /**
     * Obtener repuestos de una orden
     */
    private function obtenerRepuestosOrden($orden_id) {
        try {
            $sql = "SELECT orp.*, r.nombre, r.codigo, r.unidad_medida 
                    FROM ordenes_repuestos orp
                    JOIN inventario r ON orp.repuesto_id = r.id
                    WHERE orp.orden_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$orden_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerRepuestosOrden: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener técnicos adicionales de una orden
     */
    private function obtenerTecnicosOrden($orden_id) {
        try {
            $sql = "SELECT * FROM ordenes_tecnicos WHERE orden_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$orden_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerTecnicosOrden: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Guardar repuestos de una orden
     */
    private function guardarRepuestosOrden($orden_id, $repuestos_ids, $cantidades, $costos) {
        try {
            $sql = "INSERT INTO ordenes_repuestos (orden_id, repuesto_id, cantidad, costo_unitario, costo_total) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            
            foreach ($repuestos_ids as $index => $repuesto_id) {
                if (!empty($repuesto_id)) {
                    $cantidad = (int)($cantidades[$index] ?? 1);
                    $costo_unitario = (float)($costos[$index] ?? 0);
                    $costo_total = $cantidad * $costo_unitario;
                    
                    $stmt->execute([$orden_id, $repuesto_id, $cantidad, $costo_unitario, $costo_total]);
                }
            }
        } catch (PDOException $e) {
            error_log("Error en guardarRepuestosOrden: " . $e->getMessage());
        }
    }

    /**
     * Actualizar inventario al cerrar una orden
     */
    private function actualizarInventarioPorCierre($orden_id) {
        try {
            $repuestos = $this->obtenerRepuestosOrden($orden_id);
            
            foreach ($repuestos as $repuesto) {
                $sql = "UPDATE inventario SET cantidad = cantidad - ?, fecha_actualizacion = NOW() WHERE id = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$repuesto['cantidad'], $repuesto['repuesto_id']]);
            }
        } catch (PDOException $e) {
            error_log("Error en actualizarInventarioPorCierre: " . $e->getMessage());
        }
    }

    /**
     * Obtener órdenes por estado
     */
    private function getOrdenesPorEstado($fechaInicio, $fechaFin) {
        try {
            $sql = "SELECT status, COUNT(*) as total 
                    FROM ordenes_mantenimiento 
                    WHERE fecha_creacion BETWEEN ? AND ?
                    GROUP BY status
                    ORDER BY total DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59']);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en getOrdenesPorEstado: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener órdenes por prioridad
     */
    private function getOrdenesPorPrioridad($fechaInicio, $fechaFin) {
        try {
            $sql = "SELECT prioridad, COUNT(*) as total 
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
        } catch (PDOException $e) {
            error_log("Error en getOrdenesPorPrioridad: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener historial de una orden
     */
    private function getHistorial($orden_id) {
        try {
            $sql = "SELECT h.*, u.nombre as usuario_nombre 
                    FROM ordenes_historial h
                    LEFT JOIN usuarios u ON h.usuario_id = u.id
                    WHERE h.orden_id = ?
                    ORDER BY h.fecha DESC
                    LIMIT 50";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$orden_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en getHistorial: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Guardar historial de cambios
     */
    private function guardarHistorial($orden_id, $datos, $accion = 'EDICION') {
        try {
            $sql = "INSERT INTO ordenes_historial 
                    (orden_id, usuario_id, accion, datos_nuevos, fecha) 
                    VALUES (?, ?, ?, ?, NOW())";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $orden_id,
                $_SESSION['usuario_id'] ?? 1,
                $accion,
                json_encode($datos, JSON_UNESCAPED_UNICODE)
            ]);
            
        } catch (PDOException $e) {
            error_log("Error al guardar historial: " . $e->getMessage());
        }
    }

    /**
     * Subir evidencias (fotos)
     */
    private function subirEvidencias($archivos) {
        $rutaBase = __DIR__ . '/../uploads/evidencias/';
        $archivosSubidos = [];
        
        // Crear directorio si no existe
        if (!file_exists($rutaBase)) {
            mkdir($rutaBase, 0777, true);
        }
        
        foreach ($archivos['tmp_name'] as $index => $tmpName) {
            if (empty($tmpName)) {
                continue;
            }
            
            $nombreOriginal = $archivos['name'][$index];
            $extension = pathinfo($nombreOriginal, PATHINFO_EXTENSION);
            $nombreNuevo = 'evidencia_' . date('Ymd_His') . '_' . $index . '.' . $extension;
            $rutaCompleta = $rutaBase . $nombreNuevo;
            
            if (move_uploaded_file($tmpName, $rutaCompleta)) {
                $archivosSubidos[] = $nombreNuevo;
            }
        }
        
        return $archivosSubidos;
    }

    /**
     * Obtener documentos relacionados a una orden
     */
    private function getDocumentosOrden($orden_id) {
        try {
            $sql = "SELECT * FROM ordenes_documentos WHERE orden_id = ? ORDER BY fecha_subida DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$orden_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en getDocumentosOrden: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Verificar si el usuario tiene acceso a una orden
     */
    private function verificarAcceso($orden) {
        $rol = $this->authHelper->getRole();
        $userId = $this->authHelper->getUserId();
        
        if ($rol === 'admin') {
            return true;
        }
        
        if ($rol === 'supervisor') {
            return true;
        }
        
        if ($rol === 'tecnico') {
            return $orden['tecnico_id'] == $userId;
        }
        
        return false;
    }
}