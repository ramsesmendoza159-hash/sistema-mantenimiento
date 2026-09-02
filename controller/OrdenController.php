<?php
// controller/OrdenController.php
// Controlador de órdenes - VERSIÓN COMPLETA CON ROLES

require_once __DIR__ . '/../model/OrdenTrabajo.php';
require_once __DIR__ . '/../model/Tecnico.php';
require_once __DIR__ . '/../model/Supervisor.php';
require_once __DIR__ . '/../model/PlantasModel.php';
require_once __DIR__ . '/../model/AreasModel.php';
require_once __DIR__ . '/../model/EquiposModel.php';
require_once __DIR__ . '/../model/ComponentesModel.php';
require_once __DIR__ . '/../helpers/AuthHelper.php';
require_once __DIR__ . '/../helpers/SecurityHelper.php';

class OrdenController {
    
    private $ordenModel;
    private $authHelper;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $this->authHelper = new AuthHelper();
        $this->ordenModel = new OrdenTrabajo();
    }

    /**
     * ✅ Lista de órdenes - Con filtro por rol
     */
    public function index() {
        // Verificar autenticación
        if (!$this->authHelper->isLoggedIn()) {
            header('Location: /proyecto/auth/login');
            exit;
        }
        
        $rol = $this->authHelper->getRole();
        
        // Redirigir según rol
        if ($rol === 'tecnico') {
            header('Location: /proyecto/tecnico/mis_ordenes');
            exit;
        } elseif ($rol === 'supervisor') {
            // Supervisor ve órdenes para revisar
            $filtros = ['status' => 'CERRADA'];
            $ordenes = $this->ordenModel->obtenerTodos($filtros);
        } else {
            // Admin ve todas
            $ordenes = $this->ordenModel->obtenerTodos();
        }
        
        $estadisticas = $this->ordenModel->obtenerEstadisticas();
        
        $titulo = 'Gestión de Órdenes';
        $seccion = 'ordenes';
        require_once __DIR__ . '/../views/ordenes/index.php';
    }

    /**
     * ✅ Ver detalle de orden - Solo si tiene acceso
     */
    public function ver($id) {
        if (!$this->authHelper->isLoggedIn()) {
            header('Location: /proyecto/auth/login');
            exit;
        }
        
        $orden = $this->ordenModel->obtenerPorId($id);
        
        if (!$orden) {
            $_SESSION['error'] = 'Orden no encontrada';
            header('Location: /proyecto/ordenes');
            exit;
        }
        
        // ✅ Verificar acceso según rol
        $rol = $this->authHelper->getRole();
        $usuarioId = $this->authHelper->getUserId();
        
        if ($rol === 'tecnico' && $orden['tecnico_id'] != $usuarioId) {
            $_SESSION['error'] = 'No tienes acceso a esta orden';
            header('Location: /proyecto/tecnico/mis_ordenes');
            exit;
        }
        
        if ($rol === 'supervisor' && $orden['status'] !== 'CERRADA') {
            $_SESSION['error'] = 'Solo puedes ver órdenes cerradas';
            header('Location: /proyecto/supervisor/ordenes');
            exit;
        }
        
        $titulo = 'Detalle de Orden';
        $seccion = 'ordenes';
        require_once __DIR__ . '/../views/ordenes/ver.php';
    }

    /**
     * ✅ Crear orden - SOLO ADMIN
     */
    public function crear() {
        if (!$this->authHelper->isLoggedIn()) {
            header('Location: /proyecto/auth/login');
            exit;
        }
        
        // ✅ Solo admin puede crear
        if (!$this->authHelper->isAdmin()) {
            $_SESSION['error'] = 'No tienes permisos para crear órdenes';
            header('Location: /proyecto/dashboard');
            exit;
        }
        
        // Cargar datos para selects
        $tecnicos = (new Tecnico())->obtenerTodos(['estado' => 'activo']);
        $supervisores = (new Supervisor())->obtenerTodos(['estado' => 'activo']);
        $plantas = (new PlantasModel())->obtenerTodos();
        $areas = (new AreasModel())->obtenerTodos();
        $equipos = (new EquiposModel())->obtenerTodos();
        $componentes = (new ComponentesModel())->obtenerTodos();
        
        $titulo = 'Crear Orden';
        $seccion = 'ordenes';
        require_once __DIR__ . '/../views/ordenes/crear.php';
    }

    /**
     * ✅ Guardar orden - SOLO ADMIN
     */
    public function guardar() {
        if (!$this->authHelper->isLoggedIn()) {
            header('Location: /proyecto/auth/login');
            exit;
        }
        
        // ✅ Solo admin puede guardar
        if (!$this->authHelper->isAdmin()) {
            $_SESSION['error'] = 'No tienes permisos para crear órdenes';
            header('Location: /proyecto/dashboard');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /proyecto/ordenes');
            exit;
        }
        
        // Verificar CSRF
        if (!SecurityHelper::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'Token de seguridad inválido';
            header('Location: /proyecto/ordenes/crear');
            exit;
        }
        
        // Validar datos
        $errores = [];
        
        if (empty($_POST['titulo'])) {
            $errores[] = 'El título es obligatorio';
        }
        
        if (empty($_POST['descripcion_mantenimiento']) && empty($_POST['descripcion'])) {
            $errores[] = 'La descripción del mantenimiento es obligatoria';
        }
        
        if (!empty($errores)) {
            $_SESSION['errores'] = $errores;
            header('Location: /proyecto/ordenes/crear');
            exit;
        }
        
        // Preparar datos
        $datos = [
            'num_om' => $_POST['num_om'] ?? '',
            'titulo' => $_POST['titulo'] ?? '',
            'descripcion_mantenimiento' => $_POST['descripcion_mantenimiento'] ?? $_POST['descripcion'] ?? '',
            'tipo_mantenimiento' => $_POST['tipo_mantenimiento'] ?? 'CORRECTIVO',
            'tipo_actividad' => $_POST['tipo_actividad'] ?? '',
            'prioridad' => $_POST['prioridad'] ?? 'Media',
            'tecnico_id' => $_POST['tecnico_id'] ?? null,
            'id_supervisor' => $_POST['id_supervisor'] ?? null,
            'id_planta' => $_POST['id_planta'] ?? null,
            'id_area' => $_POST['id_area'] ?? null,
            'id_equipo' => $_POST['id_equipo'] ?? null,
            'id_componente' => $_POST['id_componente'] ?? null,
            'solicitante' => $_POST['solicitante'] ?? '',
            'supervisor_solicitante' => $_POST['supervisor_solicitante'] ?? '',
            'fecha_inicio' => $_POST['fecha_inicio'] ?? date('Y-m-d'),
            'fecha_estimada' => $_POST['fecha_estimada'] ?? null,
            'horas_duracion' => $_POST['horas_duracion'] ?? 0,
            'tarifa_tecnico' => $_POST['tarifa_tecnico'] ?? 0,
            'costo_repuestos' => $_POST['costo_repuestos'] ?? 0,
            'status' => 'PENDIENTE',
            'creado_por' => $this->authHelper->getUserId()
        ];
        
        $resultado = $this->ordenModel->crear($datos);
        
        if ($resultado) {
            $_SESSION['mensaje'] = 'Orden creada correctamente';
            $_SESSION['mensaje_tipo'] = 'success';
            header('Location: /proyecto/ordenes');
        } else {
            $_SESSION['error'] = 'Error al crear la orden';
            header('Location: /proyecto/ordenes/crear');
        }
        exit;
    }

    /**
     * ✅ Editar orden - SOLO ADMIN
     */
    public function editar($id) {
        if (!$this->authHelper->isLoggedIn()) {
            header('Location: /proyecto/auth/login');
            exit;
        }
        
        // ✅ Solo admin puede editar
        if (!$this->authHelper->isAdmin()) {
            $_SESSION['error'] = 'No tienes permisos para editar órdenes';
            header('Location: /proyecto/dashboard');
            exit;
        }
        
        $orden = $this->ordenModel->obtenerPorId($id);
        
        if (!$orden) {
            $_SESSION['error'] = 'Orden no encontrada';
            header('Location: /proyecto/ordenes');
            exit;
        }
        
        // Solo se puede editar si está PENDIENTE
        if ($orden['status'] !== 'PENDIENTE') {
            $_SESSION['error'] = 'Solo se pueden editar órdenes en estado PENDIENTE';
            header('Location: /proyecto/ordenes/ver/' . $id);
            exit;
        }
        
        $tecnicos = (new Tecnico())->obtenerTodos(['estado' => 'activo']);
        $supervisores = (new Supervisor())->obtenerTodos(['estado' => 'activo']);
        $plantas = (new PlantasModel())->obtenerTodos();
        $areas = (new AreasModel())->obtenerTodos();
        $equipos = (new EquiposModel())->obtenerTodos();
        $componentes = (new ComponentesModel())->obtenerTodos();
        
        $titulo = 'Editar Orden';
        $seccion = 'ordenes';
        require_once __DIR__ . '/../views/ordenes/editar.php';
    }

    /**
     * ✅ Cerrar orden - TÉCNICO o ADMIN
     */
    public function cerrar($id) {
        if (!$this->authHelper->isLoggedIn()) {
            header('Location: /proyecto/auth/login');
            exit;
        }
        
        $orden = $this->ordenModel->obtenerPorId($id);
        
        if (!$orden) {
            $_SESSION['error'] = 'Orden no encontrada';
            header('Location: /proyecto/ordenes');
            exit;
        }
        
        // ✅ Verificar permisos: técnico asignado o admin
        $rol = $this->authHelper->getRole();
        $usuarioId = $this->authHelper->getUserId();
        
        if ($rol === 'tecnico' && $orden['tecnico_id'] != $usuarioId) {
            $_SESSION['error'] = 'No tienes permisos para cerrar esta orden';
            header('Location: /proyecto/tecnico/mis_ordenes');
            exit;
        }
        
        if (!in_array($orden['status'], ['PENDIENTE', 'EN_PROCESO', 'EJECUTADA'])) {
            $_SESSION['error'] = 'La orden no se puede cerrar en su estado actual';
            header('Location: /proyecto/ordenes/ver/' . $id);
            exit;
        }
        
        $titulo = 'Cerrar Orden';
        $seccion = 'ordenes';
        require_once __DIR__ . '/../views/ordenes/cerrar.php';
    }

    /**
     * ✅ Procesar cierre - TÉCNICO o ADMIN
     */
    public function procesarCierre($id) {
        if (!$this->authHelper->isLoggedIn()) {
            header('Location: /proyecto/auth/login');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /proyecto/ordenes');
            exit;
        }
        
        $orden = $this->ordenModel->obtenerPorId($id);
        
        if (!$orden) {
            $_SESSION['error'] = 'Orden no encontrada';
            header('Location: /proyecto/ordenes');
            exit;
        }
        
        // Verificar permisos
        $rol = $this->authHelper->getRole();
        $usuarioId = $this->authHelper->getUserId();
        
        if ($rol === 'tecnico' && $orden['tecnico_id'] != $usuarioId) {
            $_SESSION['error'] = 'No tienes permisos para cerrar esta orden';
            header('Location: /proyecto/tecnico/mis_ordenes');
            exit;
        }
        
        // Verificar CSRF
        if (!SecurityHelper::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'Token de seguridad inválido';
            header('Location: /proyecto/ordenes/cerrar/' . $id);
            exit;
        }
        
        // Validar datos
        if (empty($_POST['descripcion_realizada'])) {
            $_SESSION['error'] = 'La descripción del trabajo realizado es obligatoria';
            header('Location: /proyecto/ordenes/cerrar/' . $id);
            exit;
        }
        
        if (empty($_POST['horas_trabajadas']) || $_POST['horas_trabajadas'] <= 0) {
            $_SESSION['error'] = 'Las horas trabajadas son obligatorias';
            header('Location: /proyecto/ordenes/cerrar/' . $id);
            exit;
        }
        
        $datos = [
            'descripcion_realizada' => $_POST['descripcion_realizada'] ?? '',
            'pasos_ejecutados' => $_POST['pasos_ejecutados'] ?? '',
            'horas_trabajadas' => (float)($_POST['horas_trabajadas'] ?? 0),
            'tarifa_tecnico' => (float)($_POST['tarifa_tecnico'] ?? 0),
            'costo_repuestos' => (float)($_POST['costo_repuestos'] ?? 0),
            'foto_evidencia' => $_POST['foto_evidencia'] ?? '',
            'firma_tecnico' => $_POST['firma_tecnico'] ?? '',
            'observaciones_tecnico' => $_POST['observaciones_tecnico'] ?? '',
            'observaciones_cierre' => $_POST['observaciones_cierre'] ?? '',
            'actualizado_por' => $usuarioId
        ];
        
        $resultado = $this->ordenModel->cerrar($id, $datos);
        
        if ($resultado) {
            $_SESSION['mensaje'] = 'Orden cerrada correctamente. Pendiente de revisión por supervisor.';
            $_SESSION['mensaje_tipo'] = 'success';
            
            if ($rol === 'tecnico') {
                header('Location: /proyecto/tecnico/mis_ordenes');
            } else {
                header('Location: /proyecto/ordenes');
            }
        } else {
            $_SESSION['error'] = 'Error al cerrar la orden';
            header('Location: /proyecto/ordenes/cerrar/' . $id);
        }
        exit;
    }

    /**
     * ✅ Cambiar estado - SOLO ADMIN o SUPERVISOR
     */
    public function cambiarEstado() {
        if (!$this->authHelper->isLoggedIn()) {
            header('Location: /proyecto/auth/login');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /proyecto/ordenes');
            exit;
        }
        
        // Verificar permisos: admin o supervisor
        if (!$this->authHelper->isAdmin() && !$this->authHelper->isSupervisor()) {
            $_SESSION['error'] = 'No tienes permisos para cambiar estados';
            header('Location: /proyecto/dashboard');
            exit;
        }
        
        $id = $_POST['id'] ?? 0;
        $estado = $_POST['estado'] ?? '';
        $observaciones = $_POST['observaciones'] ?? '';
        
        if (empty($id) || empty($estado)) {
            $_SESSION['error'] = 'Datos incompletos';
            header('Location: /proyecto/ordenes');
            exit;
        }
        
        // Verificar CSRF
        if (!SecurityHelper::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'Token de seguridad inválido';
            header('Location: /proyecto/ordenes');
            exit;
        }
        
        $resultado = $this->ordenModel->cambiarEstado($id, $estado, $observaciones);
        
        if ($resultado) {
            $_SESSION['mensaje'] = 'Estado actualizado correctamente';
            $_SESSION['mensaje_tipo'] = 'success';
        } else {
            $_SESSION['error'] = 'Error al cambiar el estado';
        }
        
        header('Location: /proyecto/ordenes/ver/' . $id);
        exit;
    }
}
?>