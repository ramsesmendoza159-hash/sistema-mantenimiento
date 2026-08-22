<?php
// controller/InventarioController.php
// Ubicación: C:\xampp\htdocs\produmar\controller\InventarioController.php

// Incluir el controlador base
require_once __DIR__ . '/../helpers/Controller.php';

class InventarioController extends Controller {
    
    private $db;
    private $repuestosModel;

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
        
        // Cargar el modelo de repuestos
        require_once __DIR__ . '/../model/RepuestosModel.php';
        $this->repuestosModel = new RepuestosModel();
    }

    /**
     * Listado de inventario
     * URL: /inventario
     */
    public function index() {
        $filtros = [
            'buscar' => isset($_GET['buscar']) ? trim($_GET['buscar']) : '',
            'categoria' => isset($_GET['categoria']) ? trim($_GET['categoria']) : ''
        ];
        
        try {
            $repuestos = $this->repuestosModel->obtenerTodos($filtros);
            $estadisticas = $this->repuestosModel->obtenerEstadisticas();
            
            if (!is_array($repuestos)) {
                $repuestos = [];
            }
            
        } catch (Exception $e) {
            error_log("Error en inventario index: " . $e->getMessage());
            $repuestos = [];
            $estadisticas = ['total' => 0, 'total_stock' => 0, 'precio_promedio' => 0, 'valor_total' => 0];
            $_SESSION['error'] = 'Error al cargar el inventario: ' . $e->getMessage();
        }
        
        $this->view('inventario/index', [
            'repuestos' => $repuestos,
            'estadisticas' => $estadisticas,
            'filtros' => $filtros
        ]);
    }

    /**
     * Crear repuesto
     * URL: /inventario/crear
     */
    public function crear() {
        // Solo admin puede crear
        if (!$this->authHelper->isAdmin()) {
            $_SESSION['error'] = 'No tienes permisos para crear repuestos';
            $this->redirect('/produmar/inventario');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $datos = [
                    'codigo' => isset($_POST['codigo']) ? trim($_POST['codigo']) : '',
                    'nombre' => isset($_POST['nombre']) ? trim($_POST['nombre']) : '',
                    'descripcion' => isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '',
                    'categoria' => isset($_POST['categoria']) ? trim($_POST['categoria']) : '',
                    'cantidad' => isset($_POST['cantidad']) ? (int)$_POST['cantidad'] : 0,
                    'precio_unitario' => isset($_POST['precio_unitario']) ? (float)$_POST['precio_unitario'] : 0,
                    'unidad_medida' => isset($_POST['unidad_medida']) ? trim($_POST['unidad_medida']) : '',
                    'stock_minimo' => isset($_POST['stock_minimo']) ? (int)$_POST['stock_minimo'] : 0,
                    'ubicacion' => isset($_POST['ubicacion']) ? trim($_POST['ubicacion']) : ''
                ];
                
                if (empty($datos['nombre'])) {
                    throw new Exception("El nombre del repuesto es obligatorio");
                }
                
                if ($datos['cantidad'] < 0) {
                    throw new Exception("La cantidad no puede ser negativa");
                }
                
                if ($datos['precio_unitario'] < 0) {
                    throw new Exception("El precio no puede ser negativo");
                }
                
                $id = $this->repuestosModel->crear($datos);
                
                if ($id) {
                    $_SESSION['success'] = "Repuesto '" . $datos['nombre'] . "' creado exitosamente";
                    $this->redirect('/produmar/inventario');
                } else {
                    throw new Exception("Error al crear el repuesto");
                }
                
            } catch (Exception $e) {
                $_SESSION['error'] = "Error: " . $e->getMessage();
                $this->redirect('/produmar/inventario/crear');
            }
        }
        
        $this->view('inventario/crear');
    }

    /**
     * Editar repuesto
     * URL: /inventario/editar/{id}
     */
    public function editar($id) {
        // Solo admin puede editar
        if (!$this->authHelper->isAdmin()) {
            $_SESSION['error'] = 'No tienes permisos para editar repuestos';
            $this->redirect('/produmar/inventario');
        }

        $id = (int)$id;
        if ($id <= 0) {
            $_SESSION['error'] = "ID de repuesto inválido";
            $this->redirect('/produmar/inventario');
        }
        
        $repuesto = $this->repuestosModel->obtenerPorId($id);
        
        if (!$repuesto) {
            $_SESSION['error'] = "Repuesto no encontrado";
            $this->redirect('/produmar/inventario');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $datos = [
                    'codigo' => isset($_POST['codigo']) ? trim($_POST['codigo']) : '',
                    'nombre' => isset($_POST['nombre']) ? trim($_POST['nombre']) : '',
                    'descripcion' => isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '',
                    'categoria' => isset($_POST['categoria']) ? trim($_POST['categoria']) : '',
                    'cantidad' => isset($_POST['cantidad']) ? (int)$_POST['cantidad'] : 0,
                    'precio_unitario' => isset($_POST['precio_unitario']) ? (float)$_POST['precio_unitario'] : 0,
                    'unidad_medida' => isset($_POST['unidad_medida']) ? trim($_POST['unidad_medida']) : '',
                    'stock_minimo' => isset($_POST['stock_minimo']) ? (int)$_POST['stock_minimo'] : 0,
                    'ubicacion' => isset($_POST['ubicacion']) ? trim($_POST['ubicacion']) : '',
                    'estado' => isset($_POST['estado']) ? trim($_POST['estado']) : 'activo'
                ];
                
                if (empty($datos['nombre'])) {
                    throw new Exception("El nombre del repuesto es obligatorio");
                }
                
                if ($datos['cantidad'] < 0) {
                    throw new Exception("La cantidad no puede ser negativa");
                }
                
                if ($datos['precio_unitario'] < 0) {
                    throw new Exception("El precio no puede ser negativo");
                }
                
                if ($this->repuestosModel->actualizar($id, $datos)) {
                    $_SESSION['success'] = "Repuesto actualizado exitosamente";
                    $this->redirect('/produmar/inventario');
                } else {
                    throw new Exception("Error al actualizar el repuesto");
                }
                
            } catch (Exception $e) {
                $_SESSION['error'] = "Error: " . $e->getMessage();
                $this->redirect('/produmar/inventario/editar/' . $id);
            }
        }
        
        $this->view('inventario/editar', ['repuesto' => $repuesto]);
    }

    /**
     * Eliminar repuesto
     * URL: /inventario/eliminar/{id}
     */
    public function eliminar($id) {
        // Solo admin puede eliminar
        if (!$this->authHelper->isAdmin()) {
            $_SESSION['error'] = 'No tienes permisos para eliminar repuestos';
            $this->redirect('/produmar/inventario');
        }

        $id = (int)$id;
        if ($id <= 0) {
            $_SESSION['error'] = "ID de repuesto inválido";
            $this->redirect('/produmar/inventario');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                if ($this->repuestosModel->eliminar($id)) {
                    $_SESSION['success'] = "Repuesto eliminado exitosamente";
                } else {
                    throw new Exception("Error al eliminar el repuesto");
                }
            } catch (Exception $e) {
                $_SESSION['error'] = "Error: " . $e->getMessage();
            }
            
            $this->redirect('/produmar/inventario');
        }
        
        // Si no es POST, redirigir
        $this->redirect('/produmar/inventario');
    }

    /**
     * Buscar repuestos (para autocomplete)
     * URL: /inventario/buscar
     */
    public function buscar() {
        $term = isset($_GET['term']) ? trim($_GET['term']) : '';
        
        if (strlen($term) < 2) {
            $this->jsonResponse([]);
        }
        
        try {
            $sql = "SELECT id, codigo, nombre, cantidad, precio_unitario, unidad_medida 
                    FROM inventario 
                    WHERE (nombre LIKE ? OR codigo LIKE ?)
                    AND cantidad > 0
                    AND estado = 'activo'
                    ORDER BY nombre ASC
                    LIMIT 15";
            
            $stmt = $this->db->prepare($sql);
            $buscar = '%' . $term . '%';
            $stmt->execute([$buscar, $buscar]);
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $this->jsonResponse($resultados);
            
        } catch (Exception $e) {
            error_log("Error en buscar repuestos: " . $e->getMessage());
            $this->jsonResponse([]);
        }
    }

    /**
     * Obtener detalles de un repuesto por ID (para AJAX)
     * URL: /inventario/detalle/{id}
     */
    public function detalle($id) {
        $id = (int)$id;
        if ($id <= 0) {
            $this->jsonResponse(['error' => 'ID inválido'], 400);
        }
        
        try {
            $repuesto = $this->repuestosModel->obtenerPorId($id);
            
            if ($repuesto) {
                $this->jsonResponse($repuesto);
            } else {
                $this->jsonResponse(['error' => 'Repuesto no encontrado'], 404);
            }
            
        } catch (Exception $e) {
            error_log("Error en detalle repuesto: " . $e->getMessage());
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }
}