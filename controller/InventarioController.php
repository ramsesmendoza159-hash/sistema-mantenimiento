<?php
// controller/InventarioController.php
// Ubicación: C:\xampp\htdocs\proyecto\controller\InventarioController.php
// VERSIÓN CORREGIDA

// Incluir el controlador base
require_once __DIR__ . '/../helpers/Controller.php';
require_once __DIR__ . '/../helpers/SecurityHelper.php';

class InventarioController extends Controller {
    
    private $db;
    private $inventarioModel;

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
        
        // ✅ CORREGIDO: Usar InventarioModel (o mantener RepuestosModel)
        require_once __DIR__ . '/../model/InventarioModel.php';
        $this->inventarioModel = new InventarioModel();
    }

    /**
     * Listado de inventario
     * URL: /inventario
     */
    public function index() {
        $filtros = [
            'buscar' => isset($_GET['buscar']) ? trim($_GET['buscar']) : '',
            'categoria' => isset($_GET['categoria']) ? trim($_GET['categoria']) : '',
            'tipo' => isset($_GET['tipo']) ? trim($_GET['tipo']) : '',
            'estado' => isset($_GET['estado']) ? trim($_GET['estado']) : ''
        ];
        
        try {
            $items = $this->inventarioModel->obtenerTodos($filtros);
            $estadisticas = $this->inventarioModel->obtenerEstadisticas();
            
            if (!is_array($items)) {
                $items = [];
            }
            
        } catch (Exception $e) {
            error_log("Error en inventario index: " . $e->getMessage());
            $items = [];
            $estadisticas = ['total' => 0, 'total_stock' => 0, 'precio_promedio' => 0, 'valor_total' => 0];
            $_SESSION['error'] = 'Error al cargar el inventario: ' . $e->getMessage();
        }
        
        $this->view('inventario/index', [
            'items' => $items,
            'estadisticas' => $estadisticas,
            'filtros' => $filtros
        ]);
    }

    /**
     * LISTAR INVENTARIO PARA AJAX (JSON)
     * URL: /inventario/list
     */
    public function list() {
        try {
            $filtros = [];
            if (isset($_GET['search']) && !empty($_GET['search'])) {
                $filtros['buscar'] = $_GET['search'];
            }
            if (isset($_GET['categoria']) && !empty($_GET['categoria'])) {
                $filtros['categoria'] = $_GET['categoria'];
            }
            if (isset($_GET['tipo']) && !empty($_GET['tipo'])) {
                $filtros['tipo'] = $_GET['tipo'];
            }
            if (isset($_GET['stock']) && !empty($_GET['stock'])) {
                $filtros['stock'] = $_GET['stock'];
            }
            
            $items = $this->inventarioModel->obtenerTodos($filtros);
            $total = count($items);
            $paginas = ceil($total / 15);
            
            $this->jsonResponse([
                'items' => $items,
                'total' => $total,
                'paginas' => $paginas
            ]);
            
        } catch (Exception $e) {
            error_log("Error en list inventario: " . $e->getMessage());
            $this->jsonResponse([
                'items' => [],
                'total' => 0,
                'paginas' => 0,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear ítem en inventario
     * URL: /inventario/crear
     */
    public function crear() {
        // Solo admin puede crear
        if (!$this->authHelper->isAdmin()) {
            $_SESSION['error'] = 'No tienes permisos para crear ítems';
            $this->redirect('/proyecto/inventario');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // ✅ Verificar CSRF
                $token = $_POST['csrf_token'] ?? '';
                if (!SecurityHelper::verifyCSRFToken($token)) {
                    throw new Exception('Token de seguridad inválido');
                }
                
                // ✅ Limpiar y validar datos
                $datos = [
                    'nombre' => trim($_POST['nombre'] ?? ''),
                    'tipo' => trim($_POST['tipo'] ?? ''),
                    'categoria' => trim($_POST['categoria'] ?? ''),
                    'codigo' => trim($_POST['codigo'] ?? ''),
                    'cantidad' => (int)($_POST['cantidad'] ?? 0),
                    'precio_unitario' => (float)($_POST['precio_unitario'] ?? 0),
                    'ubicacion' => trim($_POST['ubicacion'] ?? ''),
                    'descripcion' => trim($_POST['descripcion'] ?? ''),
                    'activo' => isset($_POST['activo']) ? 1 : 0
                ];
                
                // Validaciones
                if (empty($datos['nombre'])) {
                    throw new Exception("El nombre es obligatorio");
                }
                if (empty($datos['tipo'])) {
                    throw new Exception("El tipo es obligatorio");
                }
                if (empty($datos['categoria'])) {
                    throw new Exception("La categoría es obligatoria");
                }
                if ($datos['cantidad'] < 0) {
                    throw new Exception("La cantidad no puede ser negativa");
                }
                if ($datos['precio_unitario'] < 0) {
                    throw new Exception("El precio no puede ser negativo");
                }
                
                // Verificar código duplicado
                if (!empty($datos['codigo']) && $this->inventarioModel->codigoExiste($datos['codigo'])) {
                    throw new Exception("El código SKU ya está registrado");
                }
                
                // ✅ Procesar imagen
                if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                    $imagen = $_FILES['imagen'];
                    $datos['imagen'] = $this->subirImagen($imagen);
                    if (!$datos['imagen']) {
                        throw new Exception("Error al subir la imagen");
                    }
                }
                
                $id = $this->inventarioModel->crear($datos);
                
                if ($id) {
                    $_SESSION['success'] = "Ítem '" . $datos['nombre'] . "' creado exitosamente";
                    $this->redirect('/proyecto/inventario');
                } else {
                    throw new Exception("Error al crear el ítem");
                }
                
            } catch (Exception $e) {
                $_SESSION['error'] = "Error: " . $e->getMessage();
                $_SESSION['old'] = $_POST;
                $this->redirect('/proyecto/inventario/crear');
            }
        }
        
        $this->view('inventario/crear');
    }

    /**
     * Editar ítem en inventario
     * URL: /inventario/editar/{id}
     */
    public function editar($id) {
        // Solo admin puede editar
        if (!$this->authHelper->isAdmin()) {
            $_SESSION['error'] = 'No tienes permisos para editar ítems';
            $this->redirect('/proyecto/inventario');
        }

        $id = (int)$id;
        if ($id <= 0) {
            $_SESSION['error'] = "ID de ítem inválido";
            $this->redirect('/proyecto/inventario');
        }
        
        $item = $this->inventarioModel->obtenerPorId($id);
        
        if (!$item) {
            $_SESSION['error'] = "Ítem no encontrado";
            $this->redirect('/proyecto/inventario');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // ✅ Verificar CSRF
                $token = $_POST['csrf_token'] ?? '';
                if (!SecurityHelper::verifyCSRFToken($token)) {
                    throw new Exception('Token de seguridad inválido');
                }
                
                $datos = [
                    'nombre' => trim($_POST['nombre'] ?? ''),
                    'tipo' => trim($_POST['tipo'] ?? ''),
                    'categoria' => trim($_POST['categoria'] ?? ''),
                    'codigo' => trim($_POST['codigo'] ?? ''),
                    'cantidad' => (int)($_POST['cantidad'] ?? 0),
                    'precio_unitario' => (float)($_POST['precio_unitario'] ?? 0),
                    'ubicacion' => trim($_POST['ubicacion'] ?? ''),
                    'descripcion' => trim($_POST['descripcion'] ?? ''),
                    'activo' => isset($_POST['activo']) ? 1 : 0
                ];
                
                if (empty($datos['nombre'])) {
                    throw new Exception("El nombre es obligatorio");
                }
                if ($datos['cantidad'] < 0) {
                    throw new Exception("La cantidad no puede ser negativa");
                }
                if ($datos['precio_unitario'] < 0) {
                    throw new Exception("El precio no puede ser negativo");
                }
                
                // Verificar código duplicado (excepto este)
                if (!empty($datos['codigo']) && $this->inventarioModel->codigoExiste($datos['codigo'], $id)) {
                    throw new Exception("El código SKU ya está registrado");
                }
                
                // ✅ Procesar imagen (si se subió nueva)
                if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                    $imagen = $_FILES['imagen'];
                    $datos['imagen'] = $this->subirImagen($imagen);
                    if (!$datos['imagen']) {
                        throw new Exception("Error al subir la imagen");
                    }
                }
                
                if ($this->inventarioModel->actualizar($id, $datos)) {
                    $_SESSION['success'] = "Ítem actualizado exitosamente";
                    $this->redirect('/proyecto/inventario');
                } else {
                    throw new Exception("Error al actualizar el ítem");
                }
                
            } catch (Exception $e) {
                $_SESSION['error'] = "Error: " . $e->getMessage();
                $_SESSION['old'] = $_POST;
                $this->redirect('/proyecto/inventario/editar/' . $id);
            }
        }
        
        $this->view('inventario/editar', ['item' => $item]);
    }

    /**
     * Eliminar ítem del inventario
     * URL: /inventario/eliminar/{id}
     */
    public function eliminar($id) {
        // Solo admin puede eliminar
        if (!$this->authHelper->isAdmin()) {
            $_SESSION['error'] = 'No tienes permisos para eliminar ítems';
            $this->redirect('/proyecto/inventario');
        }

        $id = (int)$id;
        if ($id <= 0) {
            $_SESSION['error'] = "ID de ítem inválido";
            $this->redirect('/proyecto/inventario');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // ✅ Verificar CSRF
                $token = $_POST['csrf_token'] ?? '';
                if (!SecurityHelper::verifyCSRFToken($token)) {
                    throw new Exception('Token de seguridad inválido');
                }
                
                if ($this->inventarioModel->eliminar($id)) {
                    $_SESSION['success'] = "Ítem eliminado exitosamente";
                } else {
                    throw new Exception("Error al eliminar el ítem");
                }
            } catch (Exception $e) {
                $_SESSION['error'] = "Error: " . $e->getMessage();
            }
            
            $this->redirect('/proyecto/inventario');
        }
        
        // Si no es POST, redirigir
        $this->redirect('/proyecto/inventario');
    }

    /**
     * Buscar ítems (para autocomplete)
     * URL: /inventario/buscar
     */
    public function buscar() {
        $term = isset($_GET['term']) ? trim($_GET['term']) : '';
        
        if (strlen($term) < 2) {
            $this->jsonResponse([]);
        }
        
        try {
            $resultados = $this->inventarioModel->buscar($term);
            $this->jsonResponse($resultados);
            
        } catch (Exception $e) {
            error_log("Error en buscar inventario: " . $e->getMessage());
            $this->jsonResponse([]);
        }
    }

    /**
     * Obtener detalles de un ítem por ID (para AJAX)
     * URL: /inventario/detalle/{id}
     */
    public function detalle($id) {
        $id = (int)$id;
        if ($id <= 0) {
            $this->jsonResponse(['error' => 'ID inválido'], 400);
        }
        
        try {
            $item = $this->inventarioModel->obtenerPorId($id);
            
            if ($item) {
                $this->jsonResponse($item);
            } else {
                $this->jsonResponse(['error' => 'Ítem no encontrado'], 404);
            }
            
        } catch (Exception $e) {
            error_log("Error en detalle inventario: " . $e->getMessage());
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * ✅ Subir imagen al servidor
     */
    private function subirImagen($imagen) {
        try {
            $directorio = __DIR__ . '/../public/uploads/inventario/';
            
            if (!is_dir($directorio)) {
                mkdir($directorio, 0777, true);
            }

            $tiposPermitidos = ['image/jpeg', 'image/png', 'image/webp'];
            if (!in_array($imagen['type'], $tiposPermitidos)) {
                return false;
            }

            if ($imagen['size'] > 5 * 1024 * 1024) {
                return false;
            }

            $extension = pathinfo($imagen['name'], PATHINFO_EXTENSION);
            $nombreArchivo = uniqid('inv_') . '.' . $extension;
            $rutaCompleta = $directorio . $nombreArchivo;

            if (move_uploaded_file($imagen['tmp_name'], $rutaCompleta)) {
                return $nombreArchivo;
            }

            return false;

        } catch (Exception $e) {
            error_log("Error al subir imagen: " . $e->getMessage());
            return false;
        }
    }
}
?>