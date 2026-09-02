<?php
// controller/TecnicoController.php
// Controlador de Técnico - VERSIÓN COMPLETA CORREGIDA

require_once __DIR__ . '/../model/OrdenTrabajo.php';
require_once __DIR__ . '/../helpers/AuthHelper.php';

class TecnicoController {
    
    private $ordenModel;
    private $authHelper;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $this->authHelper = new AuthHelper();
        
        // Verificar que el usuario sea técnico
        if (!$this->authHelper->isLoggedIn() || !$this->authHelper->isTecnico()) {
            header('Location: /proyecto/auth/login');
            exit();
        }
        
        $this->ordenModel = new OrdenTrabajo();
    }

    /**
     * ✅ Dashboard del técnico
     * URL: /tecnico
     */
    public function index() {
        $usuarioId = $this->authHelper->getUserId();
        
        // Obtener estadísticas del técnico
        $ordenes = $this->ordenModel->obtenerPorTecnico($usuarioId);
        $estadisticas = $this->calcularEstadisticas($ordenes);
        
        // Últimas órdenes asignadas
        $ordenes_recientes = $this->ordenModel->obtenerPorTecnico($usuarioId, 5, 0);
        
        $titulo = "Panel de Técnico";
        $seccion = "tecnico";  // ✅ Para el sidebar
        require_once __DIR__ . '/../views/tecnico/index.php';
    }

    /**
     * ✅ Mis órdenes del técnico
     * URL: /tecnico/mis_ordenes
     */
    public function mis_ordenes() {
        $usuarioId = $this->authHelper->getUserId();
        
        // Obtener órdenes del técnico con filtros
        $filtros = [];
        if (isset($_GET['estado']) && !empty($_GET['estado'])) {
            $filtros['status'] = $_GET['estado'];
        }
        
        $ordenes = $this->ordenModel->obtenerPorTecnico($usuarioId);
        
        $titulo = "Mis Órdenes de Trabajo";
        $seccion = "mis_ordenes";  // ✅ Para el sidebar
        require_once __DIR__ . '/../views/tecnico/mis_ordenes.php';
    }

    /**
     * ✅ Detalle de una orden
     * URL: /tecnico/detalle_orden/{id}
     */
    public function detalle_orden($id) {
        $usuarioId = $this->authHelper->getUserId();
        
        $orden = $this->ordenModel->obtenerPorId($id);
        
        // Verificar que la orden existe
        if (!$orden) {
            $_SESSION['error'] = 'Orden no encontrada';
            header('Location: /proyecto/tecnico/mis_ordenes');
            exit();
        }
        
        // Verificar que la orden pertenece al técnico
        if ($orden['tecnico_id'] != $usuarioId) {
            $_SESSION['error'] = 'No tienes acceso a esta orden';
            header('Location: /proyecto/tecnico/mis_ordenes');
            exit();
        }
        
        $titulo = "Detalle de Orden";
        $seccion = "tecnico";  // ✅ Para el sidebar
        require_once __DIR__ . '/../views/tecnico/detalle_orden.php';
    }

    /**
     * ✅ Cerrar una orden (mostrar formulario)
     * URL: /tecnico/cerrar_orden/{id}
     */
    public function cerrar_orden($id) {
        $usuarioId = $this->authHelper->getUserId();
        
        $orden = $this->ordenModel->obtenerPorId($id);
        
        if (!$orden) {
            $_SESSION['error'] = 'Orden no encontrada';
            header('Location: /proyecto/tecnico/mis_ordenes');
            exit();
        }
        
        // Verificar que la orden pertenece al técnico
        if ($orden['tecnico_id'] != $usuarioId) {
            $_SESSION['error'] = 'No tienes acceso a esta orden';
            header('Location: /proyecto/tecnico/mis_ordenes');
            exit();
        }
        
        // Verificar que la orden se pueda cerrar
        if (!in_array($orden['status'], ['PENDIENTE', 'EN_PROCESO', 'EJECUTADA'])) {
            $_SESSION['error'] = 'La orden no se puede cerrar en su estado actual';
            header('Location: /proyecto/tecnico/mis_ordenes');
            exit();
        }
        
        $titulo = "Cerrar Orden de Trabajo";
        $seccion = "tecnico";  // ✅ Para el sidebar
        require_once __DIR__ . '/../views/tecnico/cerrar_orden.php';
    }

    /**
     * ✅ Procesar cierre de una orden
     * URL: /tecnico/cerrar/{id} (POST)
     */
    public function cerrar($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /proyecto/tecnico/mis_ordenes');
            exit();
        }
        
        $usuarioId = $this->authHelper->getUserId();
        $orden = $this->ordenModel->obtenerPorId($id);
        
        if (!$orden || $orden['tecnico_id'] != $usuarioId) {
            $_SESSION['error'] = 'No tienes acceso a esta orden';
            header('Location: /proyecto/tecnico/mis_ordenes');
            exit();
        }
        
        // Validar datos
        if (empty($_POST['descripcion_cierre'])) {
            $_SESSION['error'] = 'La descripción del trabajo es obligatoria';
            header('Location: /proyecto/tecnico/cerrar_orden/' . $id);
            exit();
        }
        
        if (empty($_POST['tiempo_invertido']) || $_POST['tiempo_invertido'] <= 0) {
            $_SESSION['error'] = 'El tiempo invertido es obligatorio';
            header('Location: /proyecto/tecnico/cerrar_orden/' . $id);
            exit();
        }
        
        // Preparar datos para cerrar
        $datos = [
            'descripcion_realizada' => $_POST['descripcion_cierre'],
            'pasos_ejecutados' => $_POST['pasos_ejecutados'] ?? '',
            'horas_trabajadas' => (float)$_POST['tiempo_invertido'],
            'repuestos_utilizados' => $_POST['repuestos_utilizados'] ?? '',
            'observaciones_tecnico' => $_POST['observaciones'] ?? '',
            'satisfactorio' => isset($_POST['satisfactorio']) ? 1 : 0,
            'actualizado_por' => $usuarioId
        ];
        
        $resultado = $this->ordenModel->cerrar($id, $datos);
        
        if ($resultado) {
            $_SESSION['mensaje'] = 'Orden cerrada correctamente. Pendiente de revisión por supervisor.';
            $_SESSION['mensaje_tipo'] = 'success';
            header('Location: /proyecto/tecnico/mis_ordenes');
        } else {
            $_SESSION['error'] = 'Error al cerrar la orden';
            header('Location: /proyecto/tecnico/cerrar_orden/' . $id);
        }
        exit();
    }

    /**
     * ✅ Obtener datos del dashboard (API)
     * URL: /tecnico/dashboardData
     */
    public function dashboardData() {
        $usuarioId = $this->authHelper->getUserId();
        
        $ordenes = $this->ordenModel->obtenerPorTecnico($usuarioId);
        $estadisticas = $this->calcularEstadisticas($ordenes);
        
        header('Content-Type: application/json');
        echo json_encode([
            'total' => $estadisticas['total'],
            'pendientes' => $estadisticas['pendientes'],
            'en_progreso' => $estadisticas['en_progreso'],
            'completadas' => $estadisticas['completadas'],
            'ordenes' => array_slice($ordenes, 0, 5)
        ]);
        exit();
    }

    /**
     * Calcular estadísticas del técnico
     */
    private function calcularEstadisticas($ordenes) {
        $total = count($ordenes);
        $pendientes = 0;
        $en_progreso = 0;
        $completadas = 0;
        
        foreach ($ordenes as $orden) {
            $status = $orden['status'] ?? 'PENDIENTE';
            if ($status === 'PENDIENTE') {
                $pendientes++;
            } elseif ($status === 'EN_PROCESO') {
                $en_progreso++;
            } elseif ($status === 'CERRADA' || $status === 'APROBADA' || $status === 'EJECUTADA') {
                $completadas++;
            }
        }
        
        return [
            'total' => $total,
            'pendientes' => $pendientes,
            'en_progreso' => $en_progreso,
            'completadas' => $completadas
        ];
    }
}
?>